<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Hospital;
use App\Models\Intervention;
use App\Models\Complaint;
use App\Models\Service;
use App\Models\Structure;
use App\Models\User;
use App\Services\HierarchyEquipmentIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HierarchieController extends Controller
{
    private const ALLOWED_FLOOR_LEVELS = [-1, 0, 1, 2, 3, 4];
    private const EQUIPMENT_PREVIEW_LIMIT = 2;

    public function index(Request $request)
    {
        $includeEquipments = $request->boolean('with_equipments', false);

        $payload = $this->buildHierarchyPayload($request, $includeEquipments);
        $floorCatalog = $this->buildFloorCatalog($payload['floors'] ?? $payload['tree'] ?? []);
        $serviceCatalog = Service::query()
            ->excludeHiddenForUi()
            ->select('id', 'name', 'code')
            ->orderBy('code')
            ->orderBy('name')
            ->get()
            ->map(fn (Service $service): array => [
                'id' => (int) $service->id,
                'name' => (string) $service->name,
                'code' => trim((string) ($service->code ?? '')),
            ])
            ->values()
            ->all();

        return view('pages.hierarchie.index', [
            'floors' => $payload['floors'] ?? $payload['tree'] ?? [],
            'tree' => $payload['tree'],
            'scopeNotice' => $payload['scopeNotice'],
            'floorFilter' => $payload['filters']['floor'] ?? '',
            'serviceFilter' => $payload['filters']['service'] ?? '',
            'withEquipments' => $includeEquipments,
            'availableFloors' => $payload['availableFloors'] ?? [],
            'availableServices' => $payload['availableServices'] ?? [],
            'createNodeUrl' => route('hierarchie.import-excel'),
            'updateServiceUrl' => route('hierarchie.import-excel'),
            'reloadTreeUrl' => route('hierarchie.export-json'),
            'floorCatalog' => $floorCatalog,
            'serviceCatalog' => $serviceCatalog,
        ]);
    }

    public function importExcel(Request $request, HierarchyEquipmentIntegrationService $integrationService)
    {
        if ($request->input('form_mode') === 'update_service_node') {
            $validated = $request->validate([
                'structure_service_id' => 'required|integer|exists:structures,id',
                'service_id' => 'nullable|integer|exists:services,id',
                'name' => 'required|string|max:180',
                'code' => 'required|string|max:80',
                'parent_floor_level' => 'required|integer|in:-1,0,1,2,3,4',
            ]);

            $serviceNode = Structure::query()->find((int) $validated['structure_service_id']);
            if (!$serviceNode || (string) ($serviceNode->type ?? '') !== 'service') {
                throw ValidationException::withMessages([
                    'structure_service_id' => 'Le service à modifier est introuvable dans la hiérarchie.',
                ]);
            }

            $name = trim((string) ($validated['name'] ?? ''));
            $code = trim((string) ($validated['code'] ?? ''));
            if ($name === '') {
                throw ValidationException::withMessages([
                    'name' => 'Le nom du service est obligatoire.',
                ]);
            }

            if ($code === '') {
                throw ValidationException::withMessages([
                    'code' => 'Le code du service est obligatoire.',
                ]);
            }

            $parentFloorLevel = (int) ($validated['parent_floor_level'] ?? 0);
            $parentFloor = $this->findFloorNodeByLevel($parentFloorLevel);
            if (!$parentFloor) {
                $parentFloor = $this->createFloorNodeFromLevel($parentFloorLevel);
            }

            $serviceNode->parent_id = (int) ($parentFloor->id ?? 0);
            $serviceNode->name = $name;
            $serviceNode->code = $code;
            if (Schema::hasColumn('structures', 'nom')) {
                $serviceNode->nom = $name;
            }
            $serviceNode->save();

            $serviceId = (int) ($validated['service_id'] ?? 0);
            if ($serviceId > 0) {
                $serviceModel = Service::query()->find($serviceId);
                if ($serviceModel) {
                    $normalizedCode = mb_strtolower(trim($code));
                    if ($normalizedCode !== '') {
                        $codeTaken = Service::query()
                            ->where('id', '!=', (int) $serviceModel->id)
                            ->whereRaw('LOWER(TRIM(code)) = ?', [$normalizedCode])
                            ->exists();

                        if ($codeTaken) {
                            throw ValidationException::withMessages([
                                'code' => 'Ce code service est déjà utilisé.',
                            ]);
                        }
                    }

                    $serviceModel->name = $name;
                    $serviceModel->code = $code;
                    $serviceModel->save();
                }
            }

            $payload = $this->buildHierarchyPayload($request, false);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Service modifié avec succès.',
                    'floors' => $payload['floors'] ?? $payload['tree'] ?? [],
                    'tree' => $payload['tree'] ?? [],
                ]);
            }

            return redirect()
                ->route('hierarchie.index')
                ->with('success', 'Service modifié avec succès.');
        }

        if ($request->input('form_mode') === 'tree_node') {
            $validated = $request->validate([
                'node_type' => 'required|in:etage,service',
                'floor_level' => 'nullable|integer|in:-1,0,1,2,3,4',
                'service_id' => 'nullable|integer|exists:services,id',
                'parent_floor_level' => 'nullable|integer|in:-1,0,1,2,3,4',
            ]);

            $nodeType = (string) ($validated['node_type'] ?? 'etage');

            if ($nodeType === 'etage') {
                if (!array_key_exists('floor_level', $validated) || $validated['floor_level'] === null) {
                    throw ValidationException::withMessages([
                        'floor_level' => 'Veuillez sélectionner un étage entre -1 et 4.',
                    ]);
                }

                $validated['name'] = 'Étage ' . (int) $validated['floor_level'];
                $validated['parent_floor_level'] = null;
            } else {
                $selectedServiceId = (int) ($validated['service_id'] ?? 0);
                if ($selectedServiceId <= 0) {
                    throw ValidationException::withMessages([
                        'service_id' => 'Veuillez sélectionner un service existant.',
                    ]);
                }

                $selectedService = Service::query()
                    ->excludeHiddenForUi()
                    ->select('id', 'name', 'code')
                    ->find($selectedServiceId);

                if (!$selectedService) {
                    throw ValidationException::withMessages([
                        'service_id' => 'Le service sélectionné est introuvable.',
                    ]);
                }

                $validated['name'] = trim((string) $selectedService->name);
                $validated['code'] = trim((string) ($selectedService->code ?? ''));

                if (!array_key_exists('parent_floor_level', $validated) || $validated['parent_floor_level'] === null) {
                    throw ValidationException::withMessages([
                        'parent_floor_level' => 'Veuillez sélectionner un étage parent.',
                    ]);
                }
            }

            $node = $this->createHierarchyNode($validated);

            $payload = $this->buildHierarchyPayload($request, false);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $nodeType === 'etage'
                        ? 'Étage ajouté avec succès.'
                        : 'Service ajouté avec succès.',
                    'node' => $node,
                    'floors' => $payload['floors'] ?? $payload['tree'] ?? [],
                    'tree' => $payload['tree'] ?? [],
                ]);
            }

            return redirect()
                ->route('hierarchie.index')
                ->with('success', $nodeType === 'etage'
                    ? 'Étage ajouté avec succès.'
                    : 'Service ajouté avec succès.');
        }

        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:51200',
            'hospital_code' => 'nullable|string|max:10',
        ]);

        $hospitalCode = mb_strtoupper(trim((string) ($validated['hospital_code'] ?? 'HME')));
        if ($hospitalCode === '') {
            $hospitalCode = 'HME';
        }

        $uploaded = $validated['excel_file'];
        $storedPath = $uploaded->store('imports/hierarchie');
        $absolutePath = storage_path('app/' . $storedPath);

        try {
            $stats = $integrationService->importFromExcel($absolutePath, [
                'hospital_code' => $hospitalCode,
                'replace_hospital_structure' => true,
                'detach_old_hierarchy_equipments' => true,
                'reassign_complaints' => true,
            ]);

            $created = (int) ($stats['equipments_created'] ?? 0);
            $updated = (int) ($stats['equipments_updated'] ?? 0);
            $detached = (int) ($stats['equipments_detached'] ?? 0);
            $reassigned = (int) ($stats['complaints_reassigned'] ?? 0);
            $nodesCreated = (int) ($stats['structure_nodes_created'] ?? 0);

            return redirect()
                ->route('hierarchie.index')
                ->with('success', "Import hiérarchie terminé ({$hospitalCode}). Équipements créés: {$created}, mis à jour: {$updated}, anciens retirés de la hiérarchie: {$detached}, pannes réaffectées: {$reassigned}, nœuds structure créés: {$nodesCreated}.");
        } catch (\Throwable $e) {
            return redirect()
                ->route('hierarchie.index')
                ->with('error', 'Import hiérarchie impossible: ' . $e->getMessage());
        } finally {
            if (is_file($absolutePath)) {
                @unlink($absolutePath);
            }
        }
    }

    public function exportJson(Request $request): JsonResponse
    {
        return response()->json($this->buildHierarchyPayload($request, true));
    }

    public function exportExcel(Request $request)
    {
        $payload = $this->buildHierarchyPayload($request, true);
        $rows = $this->flattenTreeForExport($payload['tree'] ?? []);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Hôpital',
            'Spécialité',
            'Étage',
            'Unité',
            'Secteur',
            'Local',
            'Désignation',
        ];

        $sheet->fromArray($headers, null, 'A1');

        foreach ($rows as $index => $row) {
            $sheet->fromArray([
                $row['hospital'],
                $row['speciality'],
                $row['floor'],
                $row['unit'],
                $row['sector'],
                $row['local'],
                $row['designation'],
            ], null, 'A' . ($index + 2));
        }

        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $fileName = 'hierarchie-equipements-' . now()->format('Y-m-d-His') . '.xlsx';
        $tempFile = storage_path('app/' . uniqid('hierarchie_export_', true) . '.xlsx');

        (new Xlsx($spreadsheet))->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function exportPayloadForCommand(Request $request): array
    {
        return $this->buildHierarchyPayload($request, true);
    }

    private function buildHierarchyPayload(Request $request, bool $includeEquipments): array
    {
        $user = $request->user();

        $floorFilter = trim((string) $request->query('floor', ''));
        $serviceFilter = trim((string) $request->query('service', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
            $dateFrom = '';
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
            $dateTo = '';
        }

        if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }

        if (!Schema::hasTable('structures')) {
            $emptyPayload = [
                'tree' => [],
                'scopeNotice' => 'Le module Hiérarchie CHU nécessite la migration de la table structures.',
                'filters' => ['floor' => $floorFilter, 'service' => $serviceFilter, 'date_from' => $dateFrom, 'date_to' => $dateTo],
                'availableFloors' => [],
                'availableServices' => [],
            ];

            return $emptyPayload;
        }

        $structureColumns = ['id', 'parent_id', 'name', 'type', 'code', 'responsable', 'order'];
        if (Schema::hasColumn('structures', 'nom')) {
            $structureColumns[] = 'nom';
        }
        if (Schema::hasColumn('structures', 'ordre')) {
            $structureColumns[] = 'ordre';
        }

        $structures = Structure::query()
            ->ordered()
            ->get($structureColumns);

        $structuresById = $structures->keyBy('id');

        $services = Service::query()
            ->excludeHiddenForUi()
            ->select('id', 'name', 'code')
            ->get();

        $hospitals = Hospital::query()->get(['id', 'code', 'name']);
        $normalizeText = static function (?string $value): string {
            $text = Str::ascii(trim((string) $value));
            $text = mb_strtolower($text);

            return preg_replace('/\s+/u', ' ', $text) ?: '';
        };

        $normalizeCode = static function (?string $value): string {
            $text = Str::ascii(trim((string) $value));
            $text = mb_strtolower($text);

            return preg_replace('/[^a-z0-9]/u', '', $text) ?: '';
        };

        $hiddenServiceNamesNormalized = collect(Service::UI_HIDDEN_NAMES)
            ->map(fn (string $name) => $normalizeText($name))
            ->filter()
            ->unique()
            ->values();

        $singleHospitalId = $hospitals->count() === 1
            ? (int) ($hospitals->first()->id ?? 0)
            : null;

        $hospitalIdByNormalizedName = $hospitals
            ->mapWithKeys(fn (Hospital $hospital) => [$normalizeText((string) $hospital->name) => (int) $hospital->id]);

        $hospitalIdByCode = $hospitals
            ->mapWithKeys(fn (Hospital $hospital) => [$normalizeText((string) $hospital->code) => (int) $hospital->id]);

        $servicesByName = $services
            ->mapWithKeys(fn (Service $service) => [mb_strtolower(trim((string) $service->name)) => $service]);

        $servicesByCode = $services
            ->filter(fn (Service $service) => trim((string) $service->code) !== '')
            ->mapWithKeys(fn (Service $service) => [mb_strtolower(trim((string) $service->code)) => $service]);

        $servicesByNormalizedCode = $services
            ->filter(fn (Service $service) => trim((string) $service->code) !== '')
            ->mapWithKeys(fn (Service $service) => [$normalizeCode((string) $service->code) => $service]);

        $resolveHospitalIdForStructure = function (int $structureId) use ($structuresById, $hospitalIdByNormalizedName, $hospitalIdByCode, $normalizeText, $singleHospitalId): ?int {
            $cursor = $structureId;

            while ($cursor && isset($structuresById[$cursor])) {
                $node = $structuresById[$cursor];
                if ((string) ($node->type ?? '') === 'hopital') {
                    $name = trim((string) ($node->name ?? ''));
                    if ($name === '' && isset($node->nom)) {
                        $name = trim((string) $node->nom);
                    }

                    $normalizedName = $normalizeText($name);
                    $normalizedCode = $normalizeText((string) ($node->code ?? ''));

                    if ($normalizedCode !== '' && isset($hospitalIdByCode[$normalizedCode])) {
                        return (int) $hospitalIdByCode[$normalizedCode];
                    }

                    if ($normalizedName !== '' && isset($hospitalIdByNormalizedName[$normalizedName])) {
                        return (int) $hospitalIdByNormalizedName[$normalizedName];
                    }

                    foreach ($hospitalIdByNormalizedName as $hospitalName => $hospitalId) {
                        if ($hospitalName === '') {
                            continue;
                        }

                        if (str_contains($normalizedName, $hospitalName) || str_contains($hospitalName, $normalizedName)) {
                            return (int) $hospitalId;
                        }
                    }

                    if (str_contains($normalizedName, 'special')) {
                        return (int) ($hospitalIdByCode['hsp'] ?? 0) ?: null;
                    }

                    if (str_contains($normalizedName, 'mere') || str_contains($normalizedName, 'enfant')) {
                        return (int) ($hospitalIdByCode['hme'] ?? 0) ?: null;
                    }

                    return (int) ($hospitalIdByCode['ho'] ?? 0) ?: null;
                }

                $cursor = (int) ($node->parent_id ?? 0);
            }

            return $singleHospitalId ?: null;
        };

        $nodes = $structures->map(function (Structure $structure) use ($servicesByName, $servicesByCode, $servicesByNormalizedCode, $services, $normalizeText, $normalizeCode, $resolveHospitalIdForStructure, $hiddenServiceNamesNormalized) {
            $service = null;

            $displayName = trim((string) ($structure->name ?? ''));
            if ($displayName === '' && isset($structure->nom)) {
                $displayName = trim((string) $structure->nom);
            }

            $displayOrder = (int) ($structure->order ?? 0);
            if ($displayOrder === 0 && isset($structure->ordre)) {
                $displayOrder = (int) $structure->ordre;
            }

            if ($structure->type === 'service') {
                $normalizedDisplayName = $normalizeText($displayName);
                if ($normalizedDisplayName !== '' && $hiddenServiceNamesNormalized->contains($normalizedDisplayName)) {
                    return null;
                }
            }

            if ($structure->type === 'service') {
                $lookupCode = mb_strtolower(trim((string) $structure->code));
                $lookupNormalizedCode = $normalizeCode((string) $structure->code);
                $lookupName = mb_strtolower($displayName);
                $lookupNormalizedName = $normalizeText($displayName);

                $service = ($lookupCode !== '' ? $servicesByCode->get($lookupCode) : null)
                    ?: $servicesByName->get($lookupName)
                    ?: ($lookupNormalizedCode !== '' ? $servicesByNormalizedCode->get($lookupNormalizedCode) : null)
                    ?: $services->first(function (Service $serviceCandidate) use ($lookupNormalizedName, $normalizeText) {
                        $candidateName = $normalizeText((string) $serviceCandidate->name);

                        if ($lookupNormalizedName === '' || $candidateName === '') {
                            return false;
                        }

                        return str_contains($candidateName, $lookupNormalizedName)
                            || str_contains($lookupNormalizedName, $candidateName);
                    });
            }

            return [
                'id' => (int) $structure->id,
                'parent_id' => $structure->parent_id ? (int) $structure->parent_id : null,
                'name' => $displayName,
                'type' => $structure->type,
                'code' => $structure->code,
                'responsable' => $structure->responsable,
                'order' => $displayOrder,
                'service_id' => $service?->id,
                'service_code' => $service?->code,
                'hospital_id' => $resolveHospitalIdForStructure((int) $structure->id),
                'equipment_count' => 0,
                'service_interventions_base_count' => 0,
                'interventions_count' => 0,
                'active_breakdowns_count' => 0,
            ];
        })->filter()->values();

        $serviceNodes = $nodes
            ->filter(fn (array $node) => $node['type'] === 'service' && !empty($node['service_id']))
            ->values();

        $serviceIds = $serviceNodes
            ->pluck('service_id')
            ->unique()
            ->values();

        $serviceHospitalPairs = $serviceNodes
            ->map(fn (array $node) => [
                'service_id' => (int) $node['service_id'],
                'hospital_id' => (int) ($node['hospital_id'] ?? 0),
            ])
            ->unique(fn (array $pair) => $pair['service_id'] . '|' . $pair['hospital_id'])
            ->values();

        $hospitalIds = $serviceHospitalPairs
            ->pluck('hospital_id')
            ->filter(fn ($id) => (int) $id > 0)
            ->unique()
            ->values();

        $equipmentCountsByPair = collect();
        $equipmentCountsByService = collect();
        $breakdownCountsByPair = collect();
        $breakdownCountsByService = collect();
        $openTicketCountsByService = collect();
        $affectedEquipmentCountsByService = collect();
        $equipmentPreviewByPair = collect();
        $equipmentPreviewByService = collect();

        $equipmentsByService = collect();
        $equipmentsByServiceId = collect();

        if (!$serviceIds->isEmpty()) {
            $equipmentPreviewRows = collect();

            try {
                $rankedPreviewRows = Equipment::query()
                    ->selectRaw("id, service_id, hospital_id, inventory_number_current, designation, operational_status, ROW_NUMBER() OVER (PARTITION BY service_id, hospital_id ORDER BY designation, id) as preview_rank")
                    ->whereIn('service_id', $serviceIds)
                    ->when(!$hospitalIds->isEmpty(), fn ($query) => $query->whereIn('hospital_id', $hospitalIds));

                $equipmentPreviewRows = DB::query()
                    ->fromSub($rankedPreviewRows, 'ranked_equipments')
                    ->where('preview_rank', '<=', self::EQUIPMENT_PREVIEW_LIMIT)
                    ->orderBy('service_id')
                    ->orderBy('hospital_id')
                    ->orderBy('preview_rank')
                    ->get(['id', 'service_id', 'hospital_id', 'inventory_number_current', 'designation', 'operational_status']);
            } catch (\Throwable $previewQueryException) {
                $equipmentPreviewRows = Equipment::query()
                    ->whereIn('service_id', $serviceIds)
                    ->when(!$hospitalIds->isEmpty(), fn ($query) => $query->whereIn('hospital_id', $hospitalIds))
                    ->orderBy('designation')
                    ->get(['id', 'service_id', 'hospital_id', 'inventory_number_current', 'designation', 'operational_status']);
            }

            $equipmentPreviewByPair = collect($equipmentPreviewRows)
                ->groupBy(fn ($equipment) => ((int) ($equipment->service_id ?? 0)) . '|' . ((int) ($equipment->hospital_id ?? 0)))
                ->map(function ($items) {
                    return collect($items)
                        ->take(self::EQUIPMENT_PREVIEW_LIMIT)
                        ->map(fn ($equipment) => $this->formatEquipmentPreviewItem($equipment))
                        ->values()
                        ->all();
                });

            $equipmentPreviewByService = collect($equipmentPreviewRows)
                ->groupBy(fn ($equipment) => (int) ($equipment->service_id ?? 0))
                ->map(function ($items) {
                    return collect($items)
                        ->take(self::EQUIPMENT_PREVIEW_LIMIT)
                        ->map(fn ($equipment) => $this->formatEquipmentPreviewItem($equipment))
                        ->values()
                        ->all();
                });

            $equipmentCountRows = Equipment::query()
                ->selectRaw('service_id, hospital_id, COUNT(*) as aggregate')
                ->whereIn('service_id', $serviceIds)
                ->when(!$hospitalIds->isEmpty(), fn ($query) => $query->whereIn('hospital_id', $hospitalIds))
                ->groupBy('service_id', 'hospital_id')
                ->get();

            $equipmentCountsByPair = $equipmentCountRows
                ->mapWithKeys(fn ($row) => [((int) $row->service_id) . '|' . ((int) $row->hospital_id) => (int) $row->aggregate]);

            $equipmentCountsByService = $equipmentCountRows
                ->groupBy('service_id')
                ->map(fn ($items) => (int) $items->sum('aggregate'));

            $breakdownRows = Equipment::query()
                ->selectRaw('service_id, hospital_id, COUNT(*) as aggregate')
                ->whereIn('service_id', $serviceIds)
                ->where(function ($query) {
                    $query
                        ->whereRaw("LOWER(TRIM(COALESCE(operational_status, ''))) LIKE ?", ['%panne%'])
                        ->orWhereRaw("LOWER(REPLACE(TRIM(COALESCE(operational_status, '')), '_', ' ')) = ?", ['hors service']);
                })
                ->when(!$hospitalIds->isEmpty(), fn ($query) => $query->whereIn('hospital_id', $hospitalIds))
                ->groupBy('service_id', 'hospital_id')
                ->get();

            $breakdownCountsByPair = $breakdownRows
                ->mapWithKeys(fn ($row) => [((int) $row->service_id) . '|' . ((int) $row->hospital_id) => (int) $row->aggregate]);

            $breakdownCountsByService = $breakdownRows
                ->groupBy('service_id')
                ->map(fn ($items) => (int) $items->sum('aggregate'));

            $openTicketCountsByService = Complaint::query()
                ->selectRaw('service_id, COUNT(*) as aggregate')
                ->whereIn('service_id', $serviceIds)
                ->whereIn('status', ['open', 'in_progress'])
                ->groupBy('service_id')
                ->pluck('aggregate', 'service_id')
                ->map(fn ($value) => (int) $value);

            $breakdownEquipmentIdsQuery = Equipment::query()
                ->selectRaw('service_id, id as equipment_id')
                ->whereIn('service_id', $serviceIds)
                ->where(function ($query) {
                    $query
                        ->whereRaw("LOWER(TRIM(COALESCE(operational_status, ''))) LIKE ?", ['%panne%'])
                        ->orWhereRaw("LOWER(REPLACE(TRIM(COALESCE(operational_status, '')), '_', ' ')) = ?", ['hors service']);
                })
                ->when(!$hospitalIds->isEmpty(), fn ($query) => $query->whereIn('hospital_id', $hospitalIds));

            $ticketEquipmentIdsQuery = Complaint::query()
                ->selectRaw('service_id, equipment_id')
                ->whereIn('service_id', $serviceIds)
                ->whereIn('status', ['open', 'in_progress'])
                ->whereNotNull('equipment_id');

            $affectedEquipmentCountsByService = DB::query()
                ->fromSub(
                    $breakdownEquipmentIdsQuery->union($ticketEquipmentIdsQuery),
                    'affected_equipment_rows'
                )
                ->selectRaw('service_id, COUNT(DISTINCT equipment_id) as aggregate')
                ->groupBy('service_id')
                ->pluck('aggregate', 'service_id')
                ->map(fn ($value) => (int) $value);

            if ($includeEquipments) {
                $equipmentRowsByService = Equipment::query()
                    ->whereIn('service_id', $serviceIds)
                    ->orderBy('designation')
                    ->get(['id', 'service_id', 'hospital_id', 'inventory_number_current', 'designation', 'operational_status']);

                $equipmentsByServiceId = $equipmentRowsByService
                    ->groupBy(fn (Equipment $equipment) => (int) $equipment->service_id);

                $equipmentsByService = $equipmentRowsByService
                    ->groupBy(fn (Equipment $equipment) => ((int) $equipment->service_id) . '|' . ((int) $equipment->hospital_id));
            }
        }

        $nodes = $nodes->map(function (array $node) use ($equipmentCountsByPair, $equipmentCountsByService, $breakdownCountsByPair, $breakdownCountsByService, $openTicketCountsByService, $affectedEquipmentCountsByService, $equipmentPreviewByPair, $equipmentPreviewByService, $equipmentsByService, $equipmentsByServiceId, $includeEquipments) {
            if ($node['type'] !== 'service' || empty($node['service_id'])) {
                return $node;
            }

            $serviceId = (int) $node['service_id'];
            $hospitalId = (int) ($node['hospital_id'] ?? 0);
            $countKey = $serviceId . '|' . $hospitalId;

            $equipmentCount = (int) (($equipmentCountsByPair[$countKey] ?? 0) ?: ($equipmentCountsByService[$serviceId] ?? 0));
            $breakdownCount = (int) (($breakdownCountsByPair[$countKey] ?? 0) ?: ($breakdownCountsByService[$serviceId] ?? 0));
            $openTicketCount = (int) ($openTicketCountsByService[$serviceId] ?? 0);
            $affectedEquipmentCount = (int) ($affectedEquipmentCountsByService[$serviceId] ?? 0);

            $node['equipment_count'] = $equipmentCount;
            $node['affected_equipment_count'] = $affectedEquipmentCount;
            $node['breakdown_count'] = $breakdownCount;
            $node['open_ticket_count'] = $openTicketCount;
            $equipmentPreview = $equipmentPreviewByPair->get($countKey) ?: $equipmentPreviewByService->get($serviceId, []);
            $node['equipment_preview'] = is_array($equipmentPreview) ? $equipmentPreview : [];
            $node['has_more_equipments'] = $equipmentCount > count($node['equipment_preview']);
            $node['equipments_url'] = $serviceId > 0
                ? route('equipements', ['service_id' => $serviceId])
                : route('equipements', ['structure_id' => (int) ($node['id'] ?? 0)]);

            // Keep backward-compatible keys used in legacy templates/exports.
            $node['service_interventions_base_count'] = $breakdownCount;
            $node['interventions_count'] = $breakdownCount;
            $node['active_breakdowns_count'] = $breakdownCount;

            if ($includeEquipments) {
                $equipmentCollection = ($equipmentsByService->get($countKey) ?? collect());
                if ($equipmentCollection->isEmpty()) {
                    $equipmentCollection = ($equipmentsByServiceId->get($serviceId) ?? collect());
                }

                $node['equipments'] = $equipmentCollection
                    ->map(fn (Equipment $equipment) => [
                        'id' => (int) $equipment->id,
                        'inventory_number' => (string) $equipment->inventory_number_current,
                        'name' => (string) $equipment->designation,
                        'status' => (string) ($equipment->operational_status ?? 'fonctionnel'),
                    ])
                    ->values()
                    ->all();
            }

            return $node;
        })->values();

        $childrenByParent = [];
        $nodesById = [];

        foreach ($nodes as $node) {
            $nodesById[$node['id']] = $node;
            $childrenByParent[$node['parent_id']][] = $node;
        }

        foreach ($childrenByParent as $parentId => $children) {
            usort($childrenByParent[$parentId], function (array $a, array $b) {
                return [$a['order'], $a['name']] <=> [$b['order'], $b['name']];
            });
        }

        $visibleIds = null;
        $scopeNotice = null;

        if ($user && $user->role === User::ROLE_MAJOR) {
            $visibleIds = $this->resolveMajorScope($user, $nodesById, $childrenByParent);
            $scopeNotice = $visibleIds === []
                ? 'Aucune structure liée à votre service n\'a été trouvée.'
                : 'Affichage limité à votre structure (profil Major).';
        }

        $visibleNodes = collect($nodes)->filter(function (array $node) use ($visibleIds) {
            return $visibleIds === null || in_array($node['id'], $visibleIds, true);
        })->values();

        $availableFloors = $visibleNodes
            ->filter(fn (array $node) => $node['type'] === 'etage')
            ->pluck('name')
            ->unique()
            ->sort()
            ->values()
            ->all();

        $availableServices = $visibleNodes
            ->filter(fn (array $node) => $node['type'] === 'service')
            ->pluck('name')
            ->unique()
            ->sort()
            ->values()
            ->all();

        $tree = $this->buildFloorServiceTree(
            $visibleNodes->all(),
            $floorFilter,
            $serviceFilter,
            $includeEquipments
        );

        $floors = $tree;

        $totals = [
            'services' => (int) collect($floors)->sum(fn (array $floorNode) => count($floorNode['children'] ?? [])),
            'floors' => count($floors),
            'equipments' => (int) collect($floors)->sum('equipment_count'),
            'affected_equipments' => (int) collect($floors)->sum('affected_equipment_count'),
            'breakdowns' => (int) collect($floors)->sum('breakdown_count'),
            'open_tickets' => (int) collect($floors)->sum('open_ticket_count'),
        ];

        $payload = [
            'floors' => $floors,
            'tree' => $floors,
            'scopeNotice' => $scopeNotice,
            'filters' => [
                'floor' => $floorFilter,
                'service' => $serviceFilter,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'availableFloors' => $availableFloors,
            'availableServices' => $availableServices,
            'totals' => $totals,
        ];

        return $payload;
    }

    private function buildFloorServiceTree(array $visibleNodes, string $floorFilter, string $serviceFilter, bool $includeEquipments): array
    {
        $nodesById = [];
        foreach ($visibleNodes as $node) {
            $nodesById[(int) ($node['id'] ?? 0)] = $node;
        }

        $normalizedFloorFilter = $this->normalizeTreeFilter($floorFilter);
        $normalizedServiceFilter = $this->normalizeTreeFilter($serviceFilter);

        $floors = collect(self::ALLOWED_FLOOR_LEVELS)
            ->mapWithKeys(function (int $level): array {
                $order = ($level + 2) * 10;
                $syntheticId = 1000 + ($level + 1);

                return [$level => [
                    'id' => $syntheticId,
                    'parent_id' => null,
                    'name' => 'Étage ' . $level,
                    'type' => 'etage',
                    'code' => (string) $level,
                    'order' => $order,
                    'equipment_count' => 0,
                    'affected_equipment_count' => 0,
                    'breakdown_count' => 0,
                    'open_ticket_count' => 0,
                    'interventions_count' => 0,
                    'active_breakdowns_count' => 0,
                    'children' => [],
                ]];
            });

        if ($normalizedFloorFilter !== '') {
            $floors = $floors->filter(function (array $floorNode) use ($normalizedFloorFilter) {
                return $this->matchesTreeFilter(
                    (string) ($floorNode['name'] ?? '') . ' ' . (string) ($floorNode['code'] ?? ''),
                    $normalizedFloorFilter
                );
            });
        }

        $serviceNodes = collect($visibleNodes)
            ->filter(fn (array $node) => ($node['type'] ?? '') === 'service')
            ->sortByDesc(fn (array $node) => (int) ($node['id'] ?? 0))
            ->values();

        $seenServiceKeys = [];

        foreach ($serviceNodes as $serviceNode) {
            $serviceId = (int) ($serviceNode['id'] ?? 0);
            if ($serviceId <= 0) {
                continue;
            }

            $serviceKey = $this->buildServiceDedupKey($serviceNode);
            if ($serviceKey !== '' && array_key_exists($serviceKey, $seenServiceKeys)) {
                continue;
            }

            if ($serviceKey !== '') {
                $seenServiceKeys[$serviceKey] = true;
            }

            $ancestorFloor = $this->resolveAncestorNodeOfType($serviceId, 'etage', $nodesById);
            if ($ancestorFloor === null) {
                continue;
            }

            $resolvedLevel = $this->extractFloorLevel((string) ($ancestorFloor['name'] ?? ''));
            if ($resolvedLevel === null || !in_array($resolvedLevel, self::ALLOWED_FLOOR_LEVELS, true)) {
                continue;
            }

            if (!$floors->has($resolvedLevel)) {
                continue;
            }

            if ($normalizedServiceFilter !== '' && !$this->matchesTreeFilter(
                (string) ($serviceNode['name'] ?? '') . ' ' . (string) ($serviceNode['service_code'] ?? $serviceNode['code'] ?? ''),
                $normalizedServiceFilter
            )) {
                continue;
            }

            $floorNode = $floors->get($resolvedLevel);
            $floorId = (int) ($floorNode['id'] ?? 0);

            $equipmentChildren = [];
            if ($includeEquipments && !empty($serviceNode['equipments']) && is_array($serviceNode['equipments'])) {
                foreach ($serviceNode['equipments'] as $equipment) {
                    $equipmentId = (int) ($equipment['id'] ?? 0);
                    $inventory = trim((string) ($equipment['inventory_number'] ?? ''));
                    $designation = trim((string) ($equipment['name'] ?? ''));
                    $equipmentLabel = trim($inventory . ' - ' . $designation, ' -');

                    $equipmentChildren[] = [
                        'id' => $equipmentId > 0 ? (-1 * $equipmentId) : (-1 * (($serviceId * 10000) + count($equipmentChildren) + 1)),
                        'parent_id' => $serviceId,
                        'name' => $equipmentLabel !== '' ? $equipmentLabel : 'Équipement',
                        'type' => 'equipement',
                        'code' => $inventory,
                        'children' => [],
                    ];
                }

                usort($equipmentChildren, fn (array $a, array $b) => [$a['name']] <=> [$b['name']]);
            }

            $serviceChild = [
                'id' => $serviceId,
                'structure_id' => $serviceId,
                'service_id' => (int) ($serviceNode['service_id'] ?? 0),
                'parent_id' => $floorId,
                'parent_floor_level' => $resolvedLevel,
                'name' => (string) ($serviceNode['name'] ?? 'Service'),
                'type' => 'service',
                'code' => (string) ($serviceNode['code'] ?? ''),
                'service_code' => (string) ($serviceNode['service_code'] ?? $serviceNode['code'] ?? ''),
                'responsable' => (string) ($serviceNode['responsable'] ?? ''),
                'order' => (int) ($serviceNode['order'] ?? 0),
                'equipment_count' => (int) ($serviceNode['equipment_count'] ?? 0),
                'affected_equipment_count' => (int) ($serviceNode['affected_equipment_count'] ?? 0),
                'breakdown_count' => (int) ($serviceNode['breakdown_count'] ?? 0),
                'open_ticket_count' => (int) ($serviceNode['open_ticket_count'] ?? 0),
                'interventions_count' => (int) ($serviceNode['breakdown_count'] ?? 0),
                'active_breakdowns_count' => (int) ($serviceNode['breakdown_count'] ?? 0),
                'equipment_preview' => is_array($serviceNode['equipment_preview'] ?? null) ? $serviceNode['equipment_preview'] : [],
                'has_more_equipments' => (bool) ($serviceNode['has_more_equipments'] ?? false),
                'equipments_url' => (string) ($serviceNode['equipments_url'] ?? route('equipements', ['structure_id' => $serviceId])),
                'children' => $equipmentChildren,
            ];

            $floor = $floors->get($resolvedLevel);
            $floor['children'][] = $serviceChild;
            $floors->put($resolvedLevel, $floor);
        }

        $tree = $floors
            ->map(function (array $floorNode) use ($normalizedServiceFilter) {
                usort($floorNode['children'], fn (array $a, array $b) => [($a['order'] ?? 0), ($a['name'] ?? '')] <=> [($b['order'] ?? 0), ($b['name'] ?? '')]);

                $floorNode['equipment_count'] = (int) collect($floorNode['children'])->sum('equipment_count');
                $floorNode['affected_equipment_count'] = (int) collect($floorNode['children'])->sum('affected_equipment_count');
                $floorNode['breakdown_count'] = (int) collect($floorNode['children'])->sum('breakdown_count');
                $floorNode['open_ticket_count'] = (int) collect($floorNode['children'])->sum('open_ticket_count');
                $floorNode['interventions_count'] = $floorNode['breakdown_count'];
                $floorNode['active_breakdowns_count'] = $floorNode['breakdown_count'];

                return $floorNode;
            })
            ->filter()
            ->values()
            ->all();

        usort($tree, fn (array $a, array $b) => [($a['order'] ?? 0), ($a['name'] ?? '')] <=> [($b['order'] ?? 0), ($b['name'] ?? '')]);

        return $tree;
    }

    private function createHierarchyNode(array $validated): Structure
    {
        $type = (string) ($validated['node_type'] ?? '');
        $name = trim((string) ($validated['name'] ?? ''));
        $code = trim((string) ($validated['code'] ?? ''));
        $floorLevel = array_key_exists('floor_level', $validated) && $validated['floor_level'] !== null
            ? (int) $validated['floor_level']
            : null;
        $order = (int) (Structure::query()->max('order') ?? 0) + 10;

        $parentId = null;
        if ($type === 'etage') {
            if ($floorLevel === null || !in_array($floorLevel, self::ALLOWED_FLOOR_LEVELS, true)) {
                throw ValidationException::withMessages([
                    'floor_level' => 'Veuillez sélectionner un étage valide entre -1 et 4.',
                ]);
            }

            $name = 'Étage ' . $floorLevel;
            $code = (string) $floorLevel;
            $order = ($floorLevel + 2) * 10;

            $existingFloor = $this->findFloorNodeByLevel($floorLevel);

            if ($existingFloor) {
                throw ValidationException::withMessages([
                    'floor_level' => 'Cet étage existe déjà. Un seul étage est autorisé pour chaque niveau de -1 à 4.',
                ]);
            }
        } elseif ($type === 'service') {
            $parentFloorLevel = array_key_exists('parent_floor_level', $validated) && $validated['parent_floor_level'] !== null
                ? (int) $validated['parent_floor_level']
                : null;

            if ($parentFloorLevel === null || !in_array($parentFloorLevel, self::ALLOWED_FLOOR_LEVELS, true)) {
                throw ValidationException::withMessages([
                    'parent_floor_level' => 'Veuillez sélectionner un étage parent valide.',
                ]);
            }

            $parentFloor = $this->findFloorNodeByLevel($parentFloorLevel);
            if (!$parentFloor) {
                $parentFloor = $this->createFloorNodeFromLevel($parentFloorLevel);
            }

            $parentId = (int) ($parentFloor->id ?? 0);

            if ($parentId <= 0 || (string) ($parentFloor->type ?? '') !== 'etage') {
                throw ValidationException::withMessages([
                    'parent_floor_level' => 'Veuillez sélectionner un étage parent valide.',
                ]);
            }

            $existingAssignment = $this->findExistingServiceAssignment(
                (int) ($validated['service_id'] ?? 0),
                $code,
                $name
            );

            if ($existingAssignment) {
                $existingFloorName = $this->resolveFloorNameForStructureId((int) $existingAssignment->id) ?: 'un étage';

                throw ValidationException::withMessages([
                    'service_id' => 'Ce service est déjà affecté à ' . $existingFloorName . '.',
                ]);
            }
        }

        $attributes = [
            'parent_id' => $parentId,
            'name' => $name,
            'type' => $type,
            'code' => $code !== '' ? $code : null,
            'order' => $order,
            'responsable' => null,
        ];

        if (Schema::hasColumn('structures', 'nom')) {
            $attributes['nom'] = $name;
        }

        if (Schema::hasColumn('structures', 'ordre')) {
            $attributes['ordre'] = $order;
        }

        return Structure::query()->create($attributes);
    }

    private function buildFloorCatalog(array $floors): array
    {
        $existingByLevel = [];

        $floorColumns = ['id', 'name'];
        if (Schema::hasColumn('structures', 'nom')) {
            $floorColumns[] = 'nom';
        }

        $existingFloors = Structure::query()
            ->where('type', 'etage')
            ->get($floorColumns);

        foreach ($existingFloors as $floor) {
            $displayName = trim((string) ($floor->name ?? ''));
            if ($displayName === '' && isset($floor->nom)) {
                $displayName = trim((string) $floor->nom);
            }

            $level = $this->extractFloorLevel($displayName);
            $id = (int) ($floor->id ?? 0);

            if ($level === null || !in_array($level, self::ALLOWED_FLOOR_LEVELS, true) || $id <= 0) {
                continue;
            }

            if (!array_key_exists($level, $existingByLevel)) {
                $existingByLevel[$level] = $id;
            }
        }

        $catalog = [];
        foreach (self::ALLOWED_FLOOR_LEVELS as $level) {
            $exists = array_key_exists($level, $existingByLevel);

            $catalog[] = [
                'level' => $level,
                'label' => 'Étage ' . $level,
                'id' => $exists ? (int) $existingByLevel[$level] : null,
                'exists' => $exists,
            ];
        }

        return $catalog;
    }

    private function findFloorNodeByLevel(int $level): ?Structure
    {
        if (!in_array($level, self::ALLOWED_FLOOR_LEVELS, true)) {
            return null;
        }

        $floorColumns = ['id', 'name', 'type'];
        if (Schema::hasColumn('structures', 'nom')) {
            $floorColumns[] = 'nom';
        }

        return Structure::query()
            ->where('type', 'etage')
            ->get($floorColumns)
            ->first(function (Structure $floor) use ($level): bool {
                $displayName = trim((string) ($floor->name ?? ''));
                if ($displayName === '' && isset($floor->nom)) {
                    $displayName = trim((string) $floor->nom);
                }

                return $this->extractFloorLevel($displayName) === $level;
            });
    }

    private function createFloorNodeFromLevel(int $level): Structure
    {
        $order = ($level + 2) * 10;
        $name = 'Étage ' . $level;

        $attributes = [
            'parent_id' => null,
            'name' => $name,
            'type' => 'etage',
            'code' => (string) $level,
            'order' => $order,
            'responsable' => null,
        ];

        if (Schema::hasColumn('structures', 'nom')) {
            $attributes['nom'] = $name;
        }

        if (Schema::hasColumn('structures', 'ordre')) {
            $attributes['ordre'] = $order;
        }

        return Structure::query()->create($attributes);
    }

    private function formatEquipmentPreviewItem($equipment): array
    {
        $status = $this->classifyEquipmentStatus((string) ($equipment->operational_status ?? ''));

        return [
            'id' => (int) ($equipment->id ?? 0),
            'name' => trim((string) ($equipment->designation ?? '')),
            'inventory_number' => trim((string) ($equipment->inventory_number_current ?? '')),
            'status_label' => $status['label'],
            'status_class' => $status['class'],
        ];
    }

    private function classifyEquipmentStatus(string $status): array
    {
        $normalized = mb_strtolower(Str::ascii(trim($status)));

        if ($normalized !== '' && (
            str_contains($normalized, 'panne')
            || str_contains($normalized, 'hors service')
            || str_contains($normalized, 'hs')
        )) {
            return [
                'label' => 'en panne',
                'class' => 'state-panne',
            ];
        }

        if ($normalized !== '' && (
            str_contains($normalized, 'maintenance')
            || str_contains($normalized, 'maint')
        )) {
            return [
                'label' => 'maintenance',
                'class' => 'state-maintenance',
            ];
        }

        return [
            'label' => 'fonctionnel',
            'class' => 'state-fonctionnel',
        ];
    }

    private function findExistingServiceAssignment(int $serviceId, string $serviceCode, string $serviceName): ?Structure
    {
        $normalizedCode = mb_strtolower(trim($serviceCode));
        $normalizedName = $this->normalizeTreeFilter($serviceName);

        return Structure::query()
            ->where('type', 'service')
            ->get(['id', 'name', 'code'])
            ->first(function (Structure $node) use ($serviceId, $normalizedCode, $normalizedName): bool {
                if ($serviceId > 0 && $normalizedCode !== '') {
                    $nodeCode = mb_strtolower(trim((string) ($node->code ?? '')));
                    if ($nodeCode !== '' && $nodeCode === $normalizedCode) {
                        return true;
                    }
                }

                if ($normalizedName !== '') {
                    $nodeName = $this->normalizeTreeFilter((string) ($node->name ?? ''));
                    if ($nodeName !== '' && $nodeName === $normalizedName) {
                        return true;
                    }
                }

                return false;
            });
    }

    private function resolveFloorNameForStructureId(int $structureId): ?string
    {
        if ($structureId <= 0) {
            return null;
        }

        $nodes = Structure::query()
            ->get(['id', 'parent_id', 'name', 'type'])
            ->keyBy('id');

        $cursor = $structureId;
        while ($cursor > 0 && isset($nodes[$cursor])) {
            $node = $nodes[$cursor];
            if ((string) ($node->type ?? '') === 'etage') {
                $label = trim((string) ($node->name ?? ''));
                return $label !== '' ? $label : null;
            }

            $cursor = (int) ($node->parent_id ?? 0);
        }

        return null;
    }

    private function buildServiceDedupKey(array $serviceNode): string
    {
        $serviceRefId = (int) ($serviceNode['service_id'] ?? 0);
        if ($serviceRefId > 0) {
            return 'sid:' . $serviceRefId;
        }

        $serviceCode = mb_strtolower(trim((string) ($serviceNode['service_code'] ?? $serviceNode['code'] ?? '')));
        if ($serviceCode !== '') {
            return 'code:' . $serviceCode;
        }

        $serviceName = $this->normalizeTreeFilter((string) ($serviceNode['name'] ?? ''));
        if ($serviceName !== '') {
            return 'name:' . $serviceName;
        }

        return '';
    }

    private function extractFloorLevel(string $value): ?int
    {
        $normalized = mb_strtolower(Str::ascii(trim($value)));
        if ($normalized === '') {
            return null;
        }

        if (preg_match('/-?\d+/', $normalized, $matches) !== 1) {
            return null;
        }

        return (int) $matches[0];
    }

    private function resolveAncestorNodeOfType(int $startNodeId, string $targetType, array $nodesById): ?array
    {
        $cursor = $startNodeId;

        while ($cursor > 0 && isset($nodesById[$cursor])) {
            $node = $nodesById[$cursor];
            if (($node['type'] ?? '') === $targetType) {
                return $node;
            }

            $cursor = (int) ($node['parent_id'] ?? 0);
        }

        return null;
    }

    private function normalizeTreeFilter(string $value): string
    {
        $normalized = mb_strtolower(Str::ascii(trim($value)));

        return preg_replace('/\s+/u', ' ', $normalized) ?: '';
    }

    private function matchesTreeFilter(string $haystack, string $needle): bool
    {
        if ($needle === '') {
            return true;
        }

        return str_contains($this->normalizeTreeFilter($haystack), $needle);
    }

    private function filterTree(array $nodes, string $floorFilter, string $serviceFilter, bool $inMatchedFloor): array
    {
        $normalizedFloor = mb_strtolower(trim($floorFilter));
        $normalizedService = mb_strtolower(trim($serviceFilter));

        $filtered = [];

        foreach ($nodes as $node) {
            $nodeText = mb_strtolower(trim((string) ($node['name'] ?? '')) . ' ' . trim((string) ($node['code'] ?? '')));

            $isMatchingFloorNode = $normalizedFloor !== ''
                && ($node['type'] ?? '') === 'etage'
                && str_contains($nodeText, $normalizedFloor);

            $insideFloorScope = $normalizedFloor === '' || $inMatchedFloor || $isMatchingFloorNode;

            $children = $this->filterTree(
                $node['children'] ?? [],
                $floorFilter,
                $serviceFilter,
                $insideFloorScope
            );

            $isMatchingServiceNode = $normalizedService === ''
                || (($node['type'] ?? '') === 'service' && str_contains($nodeText, $normalizedService));

            $passesFloor = $normalizedFloor === '' || $insideFloorScope || $children !== [];
            $passesService = $normalizedService === '' || $isMatchingServiceNode || $children !== [];

            if ($passesFloor && $passesService) {
                $node['children'] = $children;
                $filtered[] = $node;
            }
        }

        return $filtered;
    }

    private function flattenTreeForExport(array $tree): array
    {
        $rows = [];

        $walk = function (array $nodes, array $ctx) use (&$walk, &$rows) {
            foreach ($nodes as $node) {
                $type = (string) ($node['type'] ?? '');

                $next = $ctx;
                if ($type === 'hopital') {
                    $next['hospital'] = (string) ($node['name'] ?? '');
                } elseif ($type === 'service') {
                    $next['speciality'] = (string) ($node['name'] ?? '');
                } elseif ($type === 'etage') {
                    $next['floor'] = (string) ($node['name'] ?? '');
                } elseif ($type === 'unite') {
                    $next['unit'] = (string) ($node['name'] ?? '');
                } elseif ($type === 'secteur') {
                    $next['sector'] = (string) ($node['name'] ?? '');
                } elseif ($type === 'local') {
                    $next['local'] = (string) ($node['name'] ?? '');
                } elseif ($type === 'equipement') {
                    $rawName = trim((string) ($node['name'] ?? ''));
                    $designation = $rawName;
                    if (str_contains($rawName, ' - ')) {
                        $parts = explode(' - ', $rawName, 2);
                        $designation = trim((string) ($parts[1] ?? $rawName));
                    }

                    $rows[] = [
                        'hospital' => $next['hospital'] ?? '-',
                        'speciality' => $next['speciality'] ?? '-',
                        'floor' => $next['floor'] ?? '-',
                        'unit' => $next['unit'] ?? '-',
                        'sector' => $next['sector'] ?? '-',
                        'local' => $next['local'] ?? '-',
                        'designation' => $designation !== '' ? $designation : '-',
                    ];
                }

                $walk($node['children'] ?? [], $next);
            }
        };

        $walk($tree, []);

        return $rows;
    }

    private function buildTree(?int $parentId, array $childrenByParent, ?array $visibleIds, bool $inSanitaryBranch): array
    {
        $children = $childrenByParent[$parentId] ?? [];
        $branch = [];

        foreach ($children as $node) {
            if ($visibleIds !== null && !in_array($node['id'], $visibleIds, true)) {
                continue;
            }

            $branchName = mb_strtolower(Str::ascii(trim((string) ($node['name'] ?? ''))));
            $branchCode = mb_strtolower(trim((string) ($node['code'] ?? '')));
            $isSanitaryBranchNode = ($node['type'] ?? '') === 'branche'
                && ($branchCode === 'san' || str_contains($branchName, 'sanitaire'));

            $currentSanitaryScope = $inSanitaryBranch || $isSanitaryBranchNode;

            $node['in_sanitary_branch'] = $currentSanitaryScope;
            $node['children'] = $this->buildTree($node['id'], $childrenByParent, $visibleIds, $currentSanitaryScope);
            $branch[] = $node;
        }

        return $branch;
    }

    private function applyInterventionPeriodFilter($query, string $dateFrom, string $dateTo): void
    {
        if ($dateFrom !== '') {
            $query->where(function ($periodQuery) use ($dateFrom) {
                $periodQuery
                    ->whereDate('interventions.date_start', '>=', $dateFrom)
                    ->orWhere(function ($fallbackQuery) use ($dateFrom) {
                        $fallbackQuery
                            ->whereNull('interventions.date_start')
                            ->whereDate('interventions.created_at', '>=', $dateFrom);
                    });
            });
        }

        if ($dateTo !== '') {
            $query->where(function ($periodQuery) use ($dateTo) {
                $periodQuery
                    ->whereDate('interventions.date_start', '<=', $dateTo)
                    ->orWhere(function ($fallbackQuery) use ($dateTo) {
                        $fallbackQuery
                            ->whereNull('interventions.date_start')
                            ->whereDate('interventions.created_at', '<=', $dateTo);
                    });
            });
        }
    }

    private function extractInventoryKeyFromNode(array $node): string
    {
        $code = mb_strtolower(trim((string) ($node['code'] ?? '')));
        if ($code !== '') {
            return $code;
        }

        $name = trim((string) ($node['name'] ?? ''));
        if ($name === '') {
            return '';
        }

        $firstChunk = trim((string) explode(' - ', $name, 2)[0]);

        return mb_strtolower($firstChunk);
    }

    private function aggregateTreeInterventions(array $nodes): array
    {
        $aggregated = [];

        foreach ($nodes as $node) {
            [$computedNode] = $this->aggregateNodeInterventions($node);
            $aggregated[] = $computedNode;
        }

        return $aggregated;
    }

    private function aggregateNodeInterventions(array $node): array
    {
        $children = [];
        $childrenTotal = 0;

        foreach (($node['children'] ?? []) as $child) {
            [$aggregatedChild, $childTotal] = $this->aggregateNodeInterventions($child);
            $children[] = $aggregatedChild;
            $childrenTotal += $childTotal;
        }

        $ownCount = (int) ($node['interventions_count'] ?? 0);
        $serviceBaseCount = (int) ($node['service_interventions_base_count'] ?? 0);

        $total = $ownCount + $childrenTotal;
        if (($node['type'] ?? '') === 'service' && $childrenTotal === 0 && $serviceBaseCount > 0) {
            $total = $serviceBaseCount;
        }

        $node['children'] = $children;
        $node['interventions_count'] = $total;
        $node['active_breakdowns_count'] = $total;

        return [$node, $total];
    }

    private function resolveMajorScope(User $user, array $nodesById, array $childrenByParent): array
    {
        if (!$user->service_id) {
            return [];
        }

        $service = Service::query()->find($user->service_id);
        if (!$service) {
            return [];
        }

        $targetId = null;
        $targetName = mb_strtolower(trim((string) $service->name));
        $targetCode = mb_strtolower(trim((string) $service->code));

        foreach ($nodesById as $id => $node) {
            if ($node['type'] !== 'service') {
                continue;
            }

            $nodeName = mb_strtolower(trim((string) $node['name']));
            $nodeCode = mb_strtolower(trim((string) ($node['code'] ?? '')));

            if (($targetCode !== '' && $nodeCode === $targetCode) || $nodeName === $targetName) {
                $targetId = $id;
                break;
            }
        }

        if (!$targetId) {
            return [];
        }

        $ids = [$targetId];

        $cursor = $nodesById[$targetId]['parent_id'] ?? null;
        while ($cursor !== null && isset($nodesById[$cursor])) {
            $ids[] = $cursor;
            $cursor = $nodesById[$cursor]['parent_id'] ?? null;
        }

        $stack = [$targetId];
        while ($stack !== []) {
            $current = array_pop($stack);
            foreach (($childrenByParent[$current] ?? []) as $child) {
                $ids[] = $child['id'];
                $stack[] = $child['id'];
            }
        }

        return array_values(array_unique($ids));
    }

    private function ensureSeeded(): void
    {
        if (Structure::query()->exists()) {
            return;
        }

        $hasNomColumn = Schema::hasColumn('structures', 'nom');
        $hasOrdreColumn = Schema::hasColumn('structures', 'ordre');

        DB::transaction(function () use ($hasNomColumn, $hasOrdreColumn) {
            $order = 0;

            $createStructure = function (array $attributes) use ($hasNomColumn, $hasOrdreColumn) {
                if ($hasNomColumn && !array_key_exists('nom', $attributes)) {
                    $attributes['nom'] = (string) ($attributes['name'] ?? '');
                }

                if ($hasOrdreColumn && !array_key_exists('ordre', $attributes)) {
                    $attributes['ordre'] = (int) ($attributes['order'] ?? 0);
                }

                return Structure::query()->create($attributes);
            };

            $gst = $createStructure([
                'parent_id' => null,
                'name' => 'Groupement Sanitaire Territorial (GST)',
                'type' => 'gst',
                'code' => 'GST',
                'responsable' => 'Direction Générale GST',
                'order' => ++$order,
            ]);

            $adminBranch = $createStructure([
                'parent_id' => $gst->id,
                'name' => 'Branche Administrative',
                'type' => 'branche',
                'code' => 'ADMIN',
                'order' => 10,
            ]);

            $adminDirections = [
                'Direction Capital Humain',
                'Direction Finances',
                'Direction Achats',
                'Direction Ingénierie',
            ];

            foreach ($adminDirections as $index => $directionName) {
                $createStructure([
                    'parent_id' => $adminBranch->id,
                    'name' => $directionName,
                    'type' => 'direction',
                    'order' => ($index + 1) * 10,
                ]);
            }

            $sanitaryBranch = $createStructure([
                'parent_id' => $gst->id,
                'name' => 'Branche Sanitaire',
                'type' => 'branche',
                'code' => 'SAN',
                'order' => 20,
            ]);

            $hospitalNames = [
                'Hôpital Mère-Enfants',
                'Hôpital des Spécialités',
            ];

            $buildings = ['B', 'C', 'D', 'E', 'F', 'H'];
            $floors = ['-1', '0', '1', '2', '3', '4'];
            $floorNodes = [];

            foreach ($hospitalNames as $hospitalIndex => $hospitalName) {
                $hospital = $createStructure([
                    'parent_id' => $sanitaryBranch->id,
                    'name' => $hospitalName,
                    'type' => 'hopital',
                    'order' => ($hospitalIndex + 1) * 10,
                ]);

                foreach ($buildings as $buildingIndex => $buildingName) {
                    $building = $createStructure([
                        'parent_id' => $hospital->id,
                        'name' => 'Bâtiment ' . $buildingName,
                        'type' => 'batiment',
                        'code' => $buildingName,
                        'order' => ($buildingIndex + 1) * 10,
                    ]);

                    foreach ($floors as $floorIndex => $floorName) {
                        $floor = $createStructure([
                            'parent_id' => $building->id,
                            'name' => 'Étage ' . $floorName,
                            'type' => 'etage',
                            'code' => $floorName,
                            'order' => ($floorIndex + 1) * 10,
                        ]);
                        $floorNodes[] = $floor;
                    }
                }
            }

            $services = Service::query()
                ->excludeHiddenForUi()
                ->with(['units:id,name,service_id'])
                ->orderBy('name')
                ->get(['id', 'name', 'code']);

            if ($services->isEmpty()) {
                $services = collect([
                    (object) ['id' => null, 'name' => 'Réanimation pédiatrique', 'code' => null, 'units' => collect([(object) ['name' => 'UTA'], (object) ['name' => 'NEO']])],
                    (object) ['id' => null, 'name' => 'Urgences pédiatriques', 'code' => null, 'units' => collect([(object) ['name' => 'UOP']])],
                    (object) ['id' => null, 'name' => 'Bloc opératoire', 'code' => null, 'units' => collect()],
                ]);
            }

            $slotCount = count($floorNodes);
            foreach ($services->values() as $index => $service) {
                $slot = $floorNodes[$index % max(1, $slotCount)] ?? null;
                if (!$slot) {
                    continue;
                }

                $serviceNode = $createStructure([
                    'parent_id' => $slot->id,
                    'name' => (string) $service->name,
                    'type' => 'service',
                    'code' => $service->code,
                    'responsable' => 'Chef de service',
                    'order' => 1000 + $index,
                ]);

                $units = collect($service->units ?? [])->values();
                if ($units->isEmpty()) {
                    $units = collect([(object) ['name' => 'Unité générale']]);
                }

                foreach ($units as $unitIndex => $unit) {
                    $createStructure([
                        'parent_id' => $serviceNode->id,
                        'name' => (string) $unit->name,
                        'type' => 'unite',
                        'order' => ($unitIndex + 1) * 10,
                    ]);
                }
            }
        });
    }
}
