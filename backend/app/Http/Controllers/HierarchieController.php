<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Hospital;
use App\Models\Intervention;
use App\Models\Service;
use App\Models\Structure;
use App\Models\User;
use App\Services\HierarchyEquipmentIntegrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HierarchieController extends Controller
{
    public function index(Request $request)
    {
        $includeEquipments = $request->boolean('with_equipments', false);

        $payload = $this->buildHierarchyPayload($request, $includeEquipments);

        return view('pages.hierarchie.index', [
            'tree' => $payload['tree'],
            'scopeNotice' => $payload['scopeNotice'],
            'floorFilter' => $payload['filters']['floor'] ?? '',
            'serviceFilter' => $payload['filters']['service'] ?? '',
            'withEquipments' => $includeEquipments,
            'availableFloors' => $payload['availableFloors'] ?? [],
            'availableServices' => $payload['availableServices'] ?? [],
        ]);
    }

    public function importExcel(Request $request, HierarchyEquipmentIntegrationService $integrationService)
    {
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

        $this->ensureSeeded();

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

        $serviceIds = $nodes
            ->filter(fn (array $node) => $node['type'] === 'service' && !empty($node['service_id']))
            ->pluck('service_id')
            ->unique()
            ->values();

        $serviceHospitalPairs = $nodes
            ->filter(fn (array $node) => $node['type'] === 'service' && !empty($node['service_id']))
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

        $equipmentCounts = collect();
        $equipmentCountsByService = collect();
        if (!$serviceIds->isEmpty()) {
            $equipmentCountsByService = Equipment::query()
                ->selectRaw('service_id, COUNT(*) as aggregate')
                ->whereIn('service_id', $serviceIds)
                ->groupBy('service_id')
                ->pluck('aggregate', 'service_id')
                ->map(fn ($value) => (int) $value);

            $equipmentCountRows = Equipment::query()
                ->selectRaw('service_id, hospital_id, COUNT(*) as aggregate')
                ->whereIn('service_id', $serviceIds)
                ->when(!$hospitalIds->isEmpty(), fn ($query) => $query->whereIn('hospital_id', $hospitalIds))
                ->groupBy('service_id', 'hospital_id')
                ->get();

            $equipmentCounts = $equipmentCountRows->mapWithKeys(function ($row) {
                return [((int) $row->service_id) . '|' . ((int) $row->hospital_id) => (int) $row->aggregate];
            });
        }

        $interventionCountsByService = collect();
        $interventionCountsByInventory = collect();
        if (!$serviceIds->isEmpty()) {
            $interventionRows = Intervention::query()
                ->join('equipments', 'equipments.id', '=', 'interventions.equipment_id')
                ->selectRaw("equipments.service_id as service_id, LOWER(TRIM(COALESCE(equipments.inventory_number_current, ''))) as inventory_key, COUNT(interventions.id) as aggregate")
                ->whereIn('equipments.service_id', $serviceIds)
                ->when(!$hospitalIds->isEmpty(), fn ($query) => $query->whereIn('equipments.hospital_id', $hospitalIds));

            $this->applyInterventionPeriodFilter($interventionRows, $dateFrom, $dateTo);

            $interventionRows = $interventionRows
                ->groupBy('equipments.service_id', 'inventory_key')
                ->get();

            $interventionCountsByService = $interventionRows
                ->groupBy('service_id')
                ->map(fn ($items) => (int) $items->sum('aggregate'));

            $interventionCountsByInventory = $interventionRows
                ->groupBy(fn ($row) => (string) ($row->inventory_key ?? ''))
                ->map(fn ($items) => (int) $items->sum('aggregate'));
        }

        $equipmentsByService = collect();
        $equipmentsByServiceId = collect();
        if ($includeEquipments && !$serviceIds->isEmpty()) {
            $equipmentRowsByService = Equipment::query()
                ->whereIn('service_id', $serviceIds)
                ->orderBy('designation')
                ->get(['id', 'service_id', 'hospital_id', 'inventory_number_current', 'designation', 'operational_status']);

            $equipmentsByServiceId = $equipmentRowsByService->groupBy(fn (Equipment $equipment) => (int) $equipment->service_id);

            $equipmentsByService = Equipment::query()
                ->whereIn('service_id', $serviceIds)
                ->when(!$hospitalIds->isEmpty(), fn ($query) => $query->whereIn('hospital_id', $hospitalIds))
                ->orderBy('designation')
                ->get(['id', 'service_id', 'hospital_id', 'inventory_number_current', 'designation', 'operational_status'])
                ->groupBy(fn (Equipment $equipment) => ((int) $equipment->service_id) . '|' . ((int) $equipment->hospital_id));
        }

        $nodes = $nodes->map(function (array $node) use ($equipmentCounts, $equipmentCountsByService, $interventionCountsByService, $interventionCountsByInventory, $equipmentsByService, $equipmentsByServiceId, $includeEquipments) {
            if ($node['type'] === 'service' && !empty($node['service_id'])) {
                $serviceId = (int) $node['service_id'];
                $hospitalId = (int) ($node['hospital_id'] ?? 0);
                $countKey = $serviceId . '|' . $hospitalId;

                $node['equipment_count'] = (int) (($equipmentCounts[$countKey] ?? 0) ?: ($equipmentCountsByService[$serviceId] ?? 0));
                $node['service_interventions_base_count'] = (int) ($interventionCountsByService[$serviceId] ?? 0);
                $node['interventions_count'] = 0;
                $node['active_breakdowns_count'] = (int) $node['service_interventions_base_count'];

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
            } elseif ($node['type'] === 'equipement') {
                $inventoryKey = $this->extractInventoryKeyFromNode($node);
                $node['interventions_count'] = (int) ($interventionCountsByInventory[$inventoryKey] ?? 0);
                $node['active_breakdowns_count'] = (int) $node['interventions_count'];
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
            ->filter(fn (array $node) => $node['type'] === 'service' && !empty($node['service_id']))
            ->pluck('name')
            ->unique()
            ->sort()
            ->values()
            ->all();

        $tree = $this->buildTree(null, $childrenByParent, $visibleIds, false);
        $tree = $this->filterTree($tree, $floorFilter, $serviceFilter, false);
        $tree = $this->aggregateTreeInterventions($tree);

        $payload = [
            'tree' => $tree,
            'scopeNotice' => $scopeNotice,
            'filters' => [
                'floor' => $floorFilter,
                'service' => $serviceFilter,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'availableFloors' => $availableFloors,
            'availableServices' => $availableServices,
            'totals' => [
                'services' => count($availableServices),
                'floors' => count($availableFloors),
            ],
        ];

        return $payload;
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
