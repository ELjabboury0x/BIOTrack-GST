<?php

namespace App\Http\Controllers;

use App\Imports\EquipmentsHierarchyImport;
use App\Models\Category;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Models\Equipment;
use App\Models\EquipmentDesignationAsset;
use App\Models\Hospital;
use App\Models\MaintenanceReport;
use App\Models\Market;
use App\Models\Room;
use App\Models\Service;
use App\Models\Structure;
use App\Services\DashboardMetricsService;
use App\Services\RealtimeMetricsBroadcaster;
use App\Support\ServiceAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class EquipmentController extends Controller
{
    public function __construct(
        private DashboardMetricsService $dashboardMetricsService,
        private RealtimeMetricsBroadcaster $realtimeMetricsBroadcaster
    ) {
    }

    public function index(Request $request)
    {
        $selectedHospitalId = $request->integer('hospital_id');
        $selectedServiceId = $request->integer('service_id');
        $selectedCategoryId = 0;
        $structureId = $request->integer('structure_id');

        if ($selectedServiceId <= 0 && $structureId > 0) {
            $selectedServiceId = $this->resolveServiceIdFromStructure($structureId);
        }

        if ($selectedHospitalId <= 0 && $selectedServiceId > 0) {
            $selectedHospitalId = (int) Service::query()->where('id', $selectedServiceId)->value('hospital_id');
        }

        $search = trim((string) $request->query('q', ''));
        $sortDirection = strtolower((string) $request->query('sort', 'desc')) === 'asc' ? 'asc' : 'desc';
        $user = $request->user();

        $hospitalsQuery = Hospital::query()->select('id', 'code', 'name')->orderBy('name');
        if ($user && !$user->hasGlobalAccess()) {
            $allowedServiceIds = $user->isUnitRestricted()
                ? ($user->service_id ? [(int) $user->service_id] : [])
                : $user->allowedServiceIds();

            $hospitalsQuery->whereHas('services', function (Builder $query) use ($allowedServiceIds): void {
                if ($allowedServiceIds !== []) {
                    $query->whereIn('id', $allowedServiceIds);
                }
            });
        }

        $rawHospitals = $hospitalsQuery->withCount(['equipments', 'services'])->get();
        $selectedHospitalId = $this->resolveCanonicalHospitalId($selectedHospitalId, $rawHospitals);
        $selectedHospitalIds = $this->resolveCanonicalHospitalIds($selectedHospitalId, $rawHospitals);
        $hospitals = $this->deduplicateHospitals($rawHospitals);

        $equipmentQuery = $this->buildFilteredEquipmentQuery($selectedHospitalId, $selectedServiceId, $selectedCategoryId, $search, $sortDirection, $user, $selectedHospitalIds);

        $equipments = $equipmentQuery->paginate(200)->withQueryString();
        $designationValues = $equipments->getCollection()
            ->pluck('designation')
            ->filter(fn ($value) => trim((string) $value) !== '')
            ->unique()
            ->values();

        $assetsByDesignation = EquipmentDesignationAsset::query()
            ->whereIn('designation', $designationValues)
            ->get()
            ->keyBy(fn (EquipmentDesignationAsset $asset) => mb_strtolower(trim((string) $asset->designation)));

        $equipmentIds = $equipments->getCollection()
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $currentMonth = now()->format('Y-m');
        $reportKpiByEquipment = collect();

        if ($equipmentIds->isNotEmpty()) {
            $reportKpiByEquipment = MaintenanceReport::query()
                ->selectRaw('equipment_id')
                ->selectRaw('COUNT(*) as total_reports')
                ->selectRaw("SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_reports")
                ->selectRaw("SUM(CASE WHEN status <> 'closed' THEN 1 ELSE 0 END) as open_reports")
                ->selectRaw("SUM(CASE WHEN intervention_type = 'preventive' THEN 1 ELSE 0 END) as preventive_reports")
                ->selectRaw("SUM(CASE WHEN intervention_type = 'curative' THEN 1 ELSE 0 END) as curative_reports")
                ->selectRaw("SUM(CASE WHEN intervention_type = 'diagnostic' THEN 1 ELSE 0 END) as diagnostic_reports")
                ->selectRaw("SUM(CASE WHEN DATE_FORMAT(COALESCE(intervention_date, DATE(created_at)), '%Y-%m') = ? THEN 1 ELSE 0 END) as this_month_reports", [$currentMonth])
                ->whereIn('equipment_id', $equipmentIds)
                ->groupBy('equipment_id')
                ->get()
                ->keyBy('equipment_id');
        }

        $equipments->getCollection()->transform(function (Equipment $equipment) use ($assetsByDesignation, $reportKpiByEquipment) {
                $assetKey = mb_strtolower(trim((string) $equipment->designation));
                $asset = $assetKey !== '' ? $assetsByDesignation->get($assetKey) : null;
                $equipmentId = (int) $equipment->id;
                $kpiRow = $reportKpiByEquipment->get($equipmentId);

                $kpiPayload = [
                    'total_reports' => (int) ($kpiRow->total_reports ?? 0),
                    'open_reports' => (int) ($kpiRow->open_reports ?? 0),
                    'closed_reports' => (int) ($kpiRow->closed_reports ?? 0),
                    'this_month_reports' => (int) ($kpiRow->this_month_reports ?? 0),
                    'by_type' => [
                        'preventive' => (int) ($kpiRow->preventive_reports ?? 0),
                        'curative' => (int) ($kpiRow->curative_reports ?? 0),
                        'diagnostic' => (int) ($kpiRow->diagnostic_reports ?? 0),
                    ],
                ];

                return [
                    'id' => $equipment->id,
                    'barcode' => $equipment->inventory_number_current,
                    'equipment_description' => $equipment->designation,
                    'unit' => $equipment->unit_name ?: ($equipment->service_name ?: '-'),
                    'sector' => $equipment->sector_name ?: '-',
                    'sector_description' => $equipment->sector_description ?: ($equipment->exact_location ?: '-'),
                    'brand' => $equipment->brand_name ?: '-',
                    'model' => $equipment->model_name ?: '-',
                    'market' => $equipment->market_label ?: ($equipment->market?->market_number ?: '-'),
                    'lot' => $equipment->lot_number ?: '-',
                    'code' => $equipment->inventory_number_current,
                    'nom' => $equipment->designation,
                    'numero_serie' => $equipment->serial_number ?: '-',
                    'serial_number' => $equipment->serial_number ?: '-',
                    'inventory_number_current' => $equipment->inventory_number_current,
                    'designation' => $equipment->designation,
                    'marque' => $equipment->brand_name ?: '-',
                    'brand_name' => $equipment->brand_name ?: '-',
                    'model_name' => $equipment->model_name ?: '-',
                    'unit_name' => $equipment->unit_name ?: '-',
                    'sector_name' => $equipment->sector_name ?: '-',
                    'sector_description_value' => $equipment->sector_description ?: '-',
                    'market_label' => $equipment->market_label ?: '-',
                    'lot_number' => $equipment->lot_number ?: '-',
                    'zone' => $equipment->zone?->name ?: '-',
                    'service' => $equipment->service?->name ?: '-',
                    'salle' => $equipment->room?->room_number ?: '-',
                    'date_fabrication' => optional($equipment->manufacture_date)->toDateString(),
                    'manufacture_date' => optional($equipment->manufacture_date)->toDateString() ?: '-',
                    'icon_class' => $equipment->icon_class ?: '-',
                    'category_name' => $equipment->category_name ?: '-',
                    'lifecycle_status' => $equipment->lifecycle_status ?: '-',
                    'description' => $equipment->description ?: '-',
                    'serial_label_removed' => $equipment->serial_label_removed,
                    'serial_label_comment' => $equipment->serial_label_comment ?: '-',
                    'service_name_imported' => $equipment->service_name ?: '-',
                    'exact_location' => $equipment->exact_location ?: '-',
                    'hospital_code' => $equipment->hospital?->code ?: '-',
                    'hospital_name' => $equipment->hospital?->name ?: '-',
                    'store_name' => $equipment->store?->name ?: '-',
                    'company_name' => $equipment->company?->name ?: '-',
                    'market_number' => $equipment->market?->market_number ?: '-',
                    'market_reference' => $equipment->market?->reference ?: '-',
                    'market_date' => optional($equipment->market?->market_date)->toDateString() ?: '-',
                    'created_at' => optional($equipment->created_at)->toDateString() ?: '-',
                    'article' => $equipment->article ?: '-',
                    'date_reception_provisoire' => optional($equipment->date_reception_provisoire)->toDateString() ?: '-',
                    'duree_garantie' => $equipment->duree_garantie ?: '-',
                    'status' => $equipment->lifecycle_status ?: '-',
                    'date_reception_definitive' => optional($equipment->date_reception_definitive)->toDateString() ?: '-',
                    'statut_etat' => $equipment->operational_status ?: '-',
                    'statut' => $equipment->operational_status,
                    'designation_image_url' => $asset && $asset->image_path
                        ? route('equipements.assets.file', ['asset' => $asset->id, 'type' => 'image'])
                        : null,
                    'user_manual_url' => $asset && $asset->user_manual_path
                        ? route('equipements.assets.file', ['asset' => $asset->id, 'type' => 'user-manual'])
                        : null,
                    'technical_manual_url' => $asset && $asset->technical_manual_path
                        ? route('equipements.assets.file', ['asset' => $asset->id, 'type' => 'technical-manual'])
                        : null,
                    'kpi' => $kpiPayload,
                    'edit_url' => route('equipements.edit', $equipment->id),
                    'delete_url' => route('equipements.destroy', $equipment->id),
                ];
            });

        $servicesQuery = Service::query()
            ->excludeHiddenForUi()
            ->select('id', 'hospital_id', 'name', 'code')
            ->when($selectedHospitalId > 0, function (Builder $query) use ($selectedHospitalIds) {
                if ($selectedHospitalIds !== []) {
                    $query->whereIn('hospital_id', $selectedHospitalIds);
                    return;
                }

                $query->where('hospital_id', $selectedHospitalId);
            })
            ->orderBy('name');
        if ($user && !$user->hasGlobalAccess()) {
            $serviceIds = $user->isUnitRestricted()
                ? ($user->service_id ? [(int) $user->service_id] : [])
                : $user->allowedServiceIds();
            $servicesQuery->whereIn('id', $serviceIds);
        }

        $services = $servicesQuery
            ->withCount('equipments')
            ->get();

        $categories = collect();
        if ($selectedServiceId > 0) {
            $categories = Category::query()
                ->where('service_id', $selectedServiceId)
                ->withCount('equipments')
                ->orderBy('name')
                ->get(['id', 'service_id', 'name']);
        }

        $selectedHospitalName = $selectedHospitalId > 0
            ? optional(collect($hospitals)->firstWhere('id', $selectedHospitalId))->name
            : null;
        $selectedServiceName = $selectedServiceId > 0
            ? Service::query()->where('id', $selectedServiceId)->value('name')
            : null;
        $selectedCategoryName = $selectedCategoryId > 0
            ? Category::query()->where('id', $selectedCategoryId)->value('name')
            : null;

        $breadcrumb = 'Dashboard > Équipements';
        if ($selectedHospitalName) {
            $breadcrumb .= ' > ' . $selectedHospitalName;
        }
        if ($selectedServiceName) {
            $breadcrumb .= ' > ' . $selectedServiceName;
        }
        if ($selectedCategoryName) {
            $breadcrumb .= ' > ' . $selectedCategoryName;
        }

        return view('pages.equipements', [
            'equipementsData' => $equipments,
            'breadcrumb' => $breadcrumb,
            'hospitals' => $hospitals,
            'services' => $services,
            'categories' => $categories,
            'selectedHospitalId' => $selectedHospitalId,
            'selectedServiceId' => $selectedServiceId,
            'selectedCategoryId' => $selectedCategoryId,
            'selectedHospitalName' => $selectedHospitalName,
            'selectedServiceName' => $selectedServiceName,
            'selectedCategoryName' => $selectedCategoryName,
            'searchTerm' => $search,
            'sortDirection' => $sortDirection,
        ]);
    }

    public function create(Request $request)
    {
        return view('pages.forms.equipments-create');
    }

    public function store(StoreEquipmentRequest $request)
    {
        $inventoryNumber = trim((string) $request->validated('inventory_number_current'));
        $alreadyExists = Equipment::query()
            ->where('inventory_number_current', $inventoryNumber)
            ->exists();

        if ($alreadyExists) {
            return back()
                ->withErrors([
                    'inventory_number_current' => 'Cet équipement existe déjà (numéro inventaire).',
                ])
                ->withInput();
        }

        $serviceContext = $this->resolveServiceContext(
            (string) ($request->validated('unit_name') ?? ''),
            (string) ($request->validated('sector_name') ?? '')
        );
        $hospitalId = (int) ($serviceContext['hospital_id'] ?? 0);
        if ($hospitalId <= 0) {
            $hospital = Hospital::query()->first()
                ?? Hospital::query()->create([
                    'code' => 'HSP',
                    'name' => 'Hôpital Principal',
                ]);
            $hospitalId = (int) $hospital->id;
        }
        $marketId = $this->resolveMarketIdFromLabel($request->validated('market_label'));

        Equipment::create([
            'inventory_number_current' => $inventoryNumber,
            'serial_number' => $request->validated('serial_number'),
            'designation' => $request->validated('designation'),
            'brand_name' => $request->validated('brand_name'),
            'model_name' => $request->validated('model_name'),
            'unit_name' => $request->validated('unit_name'),
            'sector_name' => $request->validated('sector_name'),
            'sector_description' => $request->validated('sector_description'),
            'market_label' => $request->validated('market_label'),
            'market_id' => $marketId,
            'lot_number' => $request->validated('lot_number'),
            'article' => $request->validated('article'),
            'date_reception_provisoire' => $request->validated('date_reception_provisoire'),
            'duree_garantie' => $request->validated('duree_garantie'),
            'date_reception_definitive' => $request->validated('date_reception_definitive'),
            'service_name' => $request->validated('unit_name'),
            'exact_location' => $request->validated('sector_description'),
            'zone_id' => $serviceContext['zone_id'],
            'service_id' => $serviceContext['service_id'],
            'category_id' => $serviceContext['category_id'],
            'room_id' => $serviceContext['room_id'],
            'hospital_id' => $hospitalId,
            'operational_status' => 'fonctionnel',
        ]);

        $this->persistDesignationAssets((string) $request->validated('designation'), $request);

        $this->realtimeMetricsBroadcaster->broadcastDashboardMetrics(
            $this->dashboardMetricsService->build()
        );

        return redirect()
            ->route('equipements')
            ->with('success', 'Équipement ajouté avec succès.');
    }

    public function edit(int $id)
    {
        $equipment = Equipment::query()->findOrFail($id);

        $user = request()->user();
        Gate::authorize('view-equipment', $equipment);

        $servicesQuery = Service::query()->excludeHiddenForUi()->select('id', 'zone_id', 'name')->orderBy('name');
        if ($user && !$user->hasGlobalAccess()) {
            $serviceIds = $user->isUnitRestricted()
                ? ($user->service_id ? [(int) $user->service_id] : [])
                : $user->allowedServiceIds();
            $servicesQuery->whereIn('id', $serviceIds);
        }

        $designationAsset = EquipmentDesignationAsset::query()
            ->where('designation', (string) $equipment->designation)
            ->first();

        return view('pages.forms.equipments-edit', [
            'equipment' => $equipment,
            'services' => $servicesQuery->get(),
            'designationAssetImageUrl' => $designationAsset && $designationAsset->image_path
                ? route('equipements.assets.file', ['asset' => $designationAsset->id, 'type' => 'image'])
                : null,
            'designationUserManualUrl' => $designationAsset && $designationAsset->user_manual_path
                ? route('equipements.assets.file', ['asset' => $designationAsset->id, 'type' => 'user-manual'])
                : null,
            'designationTechnicalManualUrl' => $designationAsset && $designationAsset->technical_manual_path
                ? route('equipements.assets.file', ['asset' => $designationAsset->id, 'type' => 'technical-manual'])
                : null,
        ]);
    }

    public function update(UpdateEquipmentRequest $request, int $id)
    {
        $equipment = Equipment::query()->findOrFail($id);
        Gate::authorize('view-equipment', $equipment);

        $serviceContext = $this->resolveServiceContext(
            (string) ($request->validated('unit_name') ?? ''),
            (string) ($request->validated('sector_name') ?? '')
        );
        $resolvedHospitalId = (int) ($serviceContext['hospital_id'] ?? 0);
        $marketId = $this->resolveMarketIdFromLabel($request->validated('market_label'));

        $equipment->update([
            'inventory_number_current' => $request->validated('inventory_number_current'),
            'serial_number' => $request->validated('serial_number'),
            'designation' => $request->validated('designation'),
            'brand_name' => $request->validated('brand_name'),
            'model_name' => $request->validated('model_name'),
            'unit_name' => $request->validated('unit_name'),
            'sector_name' => $request->validated('sector_name'),
            'sector_description' => $request->validated('sector_description'),
            'market_label' => $request->validated('market_label'),
            'market_id' => $marketId,
            'lot_number' => $request->validated('lot_number'),
            'article' => $request->validated('article'),
            'date_reception_provisoire' => $request->validated('date_reception_provisoire'),
            'duree_garantie' => $request->validated('duree_garantie'),
            'date_reception_definitive' => $request->validated('date_reception_definitive'),
            'service_name' => $request->validated('unit_name'),
            'exact_location' => $request->validated('sector_description'),
            'zone_id' => $serviceContext['zone_id'] ?? $equipment->zone_id,
            'service_id' => $serviceContext['service_id'] ?? $equipment->service_id,
            'category_id' => $serviceContext['category_id'] ?? $equipment->category_id,
            'room_id' => $serviceContext['room_id'] ?? $equipment->room_id,
            'hospital_id' => $resolvedHospitalId > 0 ? $resolvedHospitalId : $equipment->hospital_id,
        ]);

        $this->persistDesignationAssets((string) $request->validated('designation'), $request);

        return redirect()
            ->route('equipements')
            ->with('success', 'Équipement modifié avec succès.');
    }

    public function show(int $id)
    {
        return redirect()->route('equipements');
    }

    public function destroy(Request $request, int $id)
    {
        $equipment = Equipment::query()->findOrFail($id);

        Gate::authorize('view-equipment', $equipment);

        try {
            $equipment->delete();

            $this->realtimeMetricsBroadcaster->broadcastDashboardMetrics(
                $this->dashboardMetricsService->build()
            );

            return redirect()
                ->route('equipements')
                ->with('success', 'Équipement supprimé avec succès.')
                ->with('deleted_message', 'Équipement supprimé avec succès.');
        } catch (QueryException $exception) {
            return redirect()
                ->route('equipements')
                ->with('error', 'Suppression impossible : cet équipement est lié à des données existantes (interventions, rapports ou autres).');
        }
    }

    /**
     * Quick status update via AJAX (fonctionnel, en panne, reforme)
     */
    public function updateStatus(Request $request, int $id)
    {
        $equipment = Equipment::query()->findOrFail($id);
        Gate::authorize('view-equipment', $equipment);

        $validated = $request->validate([
            'status' => 'required|in:fonctionnel,en panne,reforme',
        ]);

        $equipment->update([
            'operational_status' => $validated['status'],
        ]);

        $this->realtimeMetricsBroadcaster->broadcastDashboardMetrics(
            $this->dashboardMetricsService->build()
        );

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès.',
            'status' => $validated['status'],
        ]);
    }

    /**
     * Bulk update equipment by designation
     * Fields: duree_garantie, brand_name, company_name (via company_id), model_name, market_label, unit_name, sector_name, lot_number, article
     */
    public function bulkUpdateByDesignation(Request $request)
    {
        $validated = $request->validate([
            'designation' => 'required|string|max:500',
            'duree_garantie' => 'nullable|string|max:255',
            'brand_name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'model_name' => 'nullable|string|max:255',
            'market_label' => 'nullable|string|max:255',
            'unit_name' => 'nullable|string|max:255',
            'sector_name' => 'nullable|string|max:255',
            'lot_number' => 'nullable|string|max:255',
            'article' => 'nullable|string|max:255',
            'user_manual_file' => 'nullable|file|mimes:pdf|max:15360',
            'technical_manual_file' => 'nullable|file|mimes:pdf|max:15360',
        ]);

        $designation = trim($validated['designation']);
        $updateData = [];

        foreach (['duree_garantie', 'brand_name', 'model_name', 'market_label', 'unit_name', 'sector_name', 'lot_number', 'article'] as $field) {
            if (isset($validated[$field]) && $validated[$field] !== '') {
                $updateData[$field] = $validated[$field];
            }
        }

        // Handle company_name -> company_id
        if (!empty($validated['company_name'])) {
            $company = \App\Models\Company::query()
                ->where('name', 'LIKE', '%' . $validated['company_name'] . '%')
                ->first();
            if ($company) {
                $updateData['company_id'] = $company->id;
            }
        }

        // Handle market_label -> market_id
        if (!empty($validated['market_label'])) {
            $marketId = $this->resolveMarketIdFromLabel($validated['market_label']);
            if ($marketId) {
                $updateData['market_id'] = $marketId;
            }
        }

        $hasManualUpload = $request->hasFile('user_manual_file') || $request->hasFile('technical_manual_file');

        if (empty($updateData) && !$hasManualUpload) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun champ à mettre à jour.',
            ], 422);
        }

        $affectedCount = 0;
        if (!empty($updateData)) {
            $affectedCount = Equipment::query()
                ->where('designation', $designation)
                ->update($updateData);
        }

        if ($hasManualUpload) {
            $this->persistDesignationAssets($designation, $request);
        }

        $this->realtimeMetricsBroadcaster->broadcastDashboardMetrics(
            $this->dashboardMetricsService->build()
        );

        return response()->json([
            'success' => true,
            'message' => $hasManualUpload
                ? "{$affectedCount} équipement(s) mis à jour. Manuels PDF mis à jour pour la désignation."
                : "{$affectedCount} équipement(s) mis à jour.",
            'affected_count' => $affectedCount,
        ]);
    }

    public function importExcelFile(Request $request)
    {
        @ini_set('max_execution_time', '0');
        @set_time_limit(0);

        $validated = $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:51200',
            'replace_existing' => 'nullable|boolean',
            'service_id' => 'nullable|integer|exists:services,id',
            'hospital_id' => 'nullable|integer|exists:hospitals,id',
        ]);

        $replaceExisting = (bool) ($validated['replace_existing'] ?? false);
        $targetServiceId = (int) ($validated['service_id'] ?? 0);
        $targetHospitalId = (int) ($validated['hospital_id'] ?? 0);
        $filePath = $validated['excel_file']->getRealPath();

        $redirectQuery = [];
        if ($targetServiceId > 0) {
            $targetService = Service::query()->select('id', 'hospital_id')->find($targetServiceId);
            if ($targetService) {
                $redirectQuery = [
                    'hospital_id' => (int) ($targetService->hospital_id ?? 0),
                    'service_id' => (int) $targetService->id,
                ];
            }
        } elseif ($targetHospitalId > 0) {
            $redirectQuery = ['hospital_id' => $targetHospitalId];
        }

        if (!$filePath) {
            return redirect()->route('equipements', $redirectQuery)->with('error', 'Fichier Excel invalide.');
        }

        try {
            $hierarchyImport = new EquipmentsHierarchyImport(
                $targetServiceId > 0 ? $targetServiceId : null,
                $targetHospitalId > 0 ? $targetHospitalId : null
            );
            Excel::import($hierarchyImport, $validated['excel_file']);
            $summary = $hierarchyImport->summary();

            if (($summary['created'] + $summary['updated']) > 0) {
                $this->realtimeMetricsBroadcaster->broadcastDashboardMetrics(
                    $this->dashboardMetricsService->build()
                );

                return redirect()->route('equipements', $redirectQuery)->with(
                    'success',
                    "Import hiérarchique terminé. {$summary['created']} créés, {$summary['updated']} mis à jour, {$summary['skipped']} ignorés."
                );
            }

            \Illuminate\Support\Facades\DB::connection()->disableQueryLog();

            $exitCode = Artisan::call('equipements:import-file', [
                '--file' => $filePath,
                '--replace-existing' => $replaceExisting ? 1 : 0,
            ]);

            $output = trim(Artisan::output());
            $lines = preg_split('/\r\n|\r|\n/', $output);
            $jsonLine = trim((string) end($lines));
            $result = json_decode($jsonLine, true);

            if ($exitCode !== 0) {
                $errorMessage = null;

                if (is_array($result) && isset($result['message']) && trim((string) $result['message']) !== '') {
                    $errorMessage = (string) $result['message'];
                }

                if ($errorMessage === null && $output !== '') {
                    $errorMessage = $output;
                }

                return redirect()->route('equipements', $redirectQuery)->with('error', 'Erreur import Excel: ' . ($errorMessage ?: 'échec de la commande d\'import'));
            }

            if (!is_array($result) || !($result['ok'] ?? false)) {
                return redirect()->route('equipements', $redirectQuery)->with('error', 'Erreur import Excel: ' . ($result['message'] ?? ($jsonLine ?: 'réponse invalide')));
            }

            $this->realtimeMetricsBroadcaster->broadcastDashboardMetrics(
                $this->dashboardMetricsService->build()
            );

            $deleted = (int) ($result['deleted'] ?? 0);
            $created = (int) ($result['created'] ?? 0);
            $updated = (int) ($result['updated'] ?? 0);
            $skipped = (int) ($result['skipped'] ?? 0);

            return redirect()->route('equipements', $redirectQuery)->with('success', "Import terminé. {$deleted} supprimés, {$created} créés, {$updated} mis à jour, {$skipped} ignorés.");
        } catch (Throwable $e) {
            return redirect()->route('equipements', $redirectQuery)->with('error', 'Erreur import Excel: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        $selectedHospitalId = $request->integer('hospital_id');
        $selectedServiceId = $request->integer('service_id');
        $selectedCategoryId = $request->integer('category_id');
        $search = trim((string) $request->query('q', ''));
        $sortDirection = strtolower((string) $request->query('sort', 'desc')) === 'asc' ? 'asc' : 'desc';
        $selectedHospitalIds = $this->resolveCanonicalHospitalIds(
            $selectedHospitalId,
            Hospital::query()->select('id', 'code', 'name')->get()
        );

        $equipments = $this->buildFilteredEquipmentQuery($selectedHospitalId, $selectedServiceId, $selectedCategoryId, $search, $sortDirection, $request->user(), $selectedHospitalIds)->get();
        $rows = $this->formatEquipmentsForExport($equipments);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $headers = [
            'N° inventaire',
            'Désignation',
            'N° de série',
            'Unité',
            'Secteur',
            'Description secteur',
            'Marque',
            'Modèle',
            'Marché',
            'Lot',
            'Article',
            'Date réception provisoire',
            'Durée garantie',
            'Date réception définitive',
            'Statut',
        ];

        $sheet->fromArray($headers, null, 'A1');

        foreach ($rows as $index => $row) {
            $sheet->fromArray([
                $row['barcode'],
                $row['designation'],
                $row['serial_number'],
                $row['unit_name'],
                $row['sector_name'],
                $row['sector_description'],
                $row['brand_name'],
                $row['model_name'],
                $row['market_label'],
                $row['lot_number'],
                $row['article'],
                $row['date_reception_provisoire'],
                $row['duree_garantie'],
                $row['date_reception_definitive'],
                $row['operational_status'],
            ], null, 'A' . ($index + 2));
        }

        foreach (range('A', 'O') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $fileName = 'equipements-complet-' . now()->format('Y-m-d-His') . '.xlsx';
        $tempFile = storage_path('app/' . uniqid('equipements_export_', true) . '.xlsx');
        (new Xlsx($spreadsheet))->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $selectedHospitalId = $request->integer('hospital_id');
        $selectedServiceId = $request->integer('service_id');
        $selectedCategoryId = $request->integer('category_id');
        $search = trim((string) $request->query('q', ''));
        $sortDirection = strtolower((string) $request->query('sort', 'desc')) === 'asc' ? 'asc' : 'desc';
        $selectedHospitalIds = $this->resolveCanonicalHospitalIds(
            $selectedHospitalId,
            Hospital::query()->select('id', 'code', 'name')->get()
        );

        $equipments = $this->buildFilteredEquipmentQuery($selectedHospitalId, $selectedServiceId, $selectedCategoryId, $search, $sortDirection, $request->user(), $selectedHospitalIds)->get();
        $rows = $this->formatEquipmentsForExport($equipments);

        return response()->view('pages.exports.equipements-print', [
            'rows' => $rows,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    public function servicesByZone(Request $request)
    {
        $zoneId = $request->integer('zone_id');
        $user = $request->user();

        $servicesQuery = Service::query()
            ->excludeHiddenForUi()
            ->where('zone_id', $zoneId)
            ->with('zone:id,name')
            ->select('id', 'zone_id', 'name')
            ->orderBy('name');

        if ($user && !$user->hasGlobalAccess()) {
            $serviceIds = $user->isUnitRestricted()
                ? ($user->service_id ? [(int) $user->service_id] : [])
                : $user->allowedServiceIds();
            $servicesQuery->whereIn('id', $serviceIds);
        }

        $services = $servicesQuery->get();

        return response()->json(
            $services->map(function (Service $service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'zone_id' => $service->zone_id,
                    'zone_name' => $service->zone?->name,
                ];
            })->values()
        );
    }

    public function roomsByService(Request $request)
    {
        $serviceId = $request->integer('service_id');
        Gate::authorize('access-service', $serviceId);

        $rooms = Room::query()
            ->where('service_id', $serviceId)
            ->select('id', 'room_number')
            ->orderBy('room_number')
            ->get();

        return response()->json($rooms);
    }

    public function formations(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $assetsQuery = EquipmentDesignationAsset::query()
            ->select(['id', 'designation', 'user_manual_path', 'technical_manual_path', 'updated_at'])
            ->where(function ($query) {
                $query
                    ->whereNotNull('user_manual_path')
                    ->orWhereNotNull('technical_manual_path');
            });

        if ($search !== '') {
            $assetsQuery->where('designation', 'like', '%' . $search . '%');
        }

        $assets = $assetsQuery
            ->orderBy('designation')
            ->get();

        $normalizeFileName = static function (?string $path): string {
            if (!$path) {
                return '-';
            }

            return basename(str_replace('\\', '/', $path));
        };

        $technicalDocuments = $assets
            ->filter(fn (EquipmentDesignationAsset $asset) => (string) $asset->technical_manual_path !== '')
            ->map(function (EquipmentDesignationAsset $asset) use ($normalizeFileName) {
                return [
                    'designation' => $asset->designation,
                    'file_name' => $normalizeFileName($asset->technical_manual_path),
                    'updated_at' => optional($asset->updated_at)->format('d/m/Y H:i') ?: '-',
                    'view_url' => route('equipements.assets.file', ['asset' => $asset->id, 'type' => 'technical-manual']),
                ];
            })
            ->values();

        $userDocuments = $assets
            ->filter(fn (EquipmentDesignationAsset $asset) => (string) $asset->user_manual_path !== '')
            ->map(function (EquipmentDesignationAsset $asset) use ($normalizeFileName) {
                return [
                    'designation' => $asset->designation,
                    'file_name' => $normalizeFileName($asset->user_manual_path),
                    'updated_at' => optional($asset->updated_at)->format('d/m/Y H:i') ?: '-',
                    'view_url' => route('equipements.assets.file', ['asset' => $asset->id, 'type' => 'user-manual']),
                ];
            })
            ->values();

        return view('pages.formations.index', [
            'search' => $search,
            'technicalDocuments' => $technicalDocuments,
            'userDocuments' => $userDocuments,
        ]);
    }

    public function importFormationPdf(Request $request)
    {
        $validated = $request->validate([
            'designation' => 'required|string|max:500',
            'document_kind' => 'required|in:user_manual,technical_manual',
            'formation_pdf' => 'required|file|mimes:pdf|max:15360',
        ]);

        $designation = trim((string) $validated['designation']);
        $documentKind = (string) $validated['document_kind'];
        $uploadedPdf = $request->file('formation_pdf');

        $asset = EquipmentDesignationAsset::query()->firstOrCreate([
            'designation' => $designation,
        ]);

        $storageDirectory = $documentKind === 'technical_manual'
            ? 'equipments/designation-assets/technical-manuals'
            : 'equipments/designation-assets/user-manuals';

        $originalName = (string) $uploadedPdf->getClientOriginalName();
        $originalBaseName = (string) pathinfo($originalName, PATHINFO_FILENAME);
        $safeBaseName = preg_replace('/[^A-Za-z0-9._-]+/', '-', $originalBaseName);
        $safeBaseName = trim((string) $safeBaseName, '.-_');
        if ($safeBaseName === '') {
            $safeBaseName = $documentKind === 'technical_manual' ? 'formation-technique' : 'formation-utilisateur';
        }

        $extension = strtolower((string) $uploadedPdf->getClientOriginalExtension());
        if ($extension === '') {
            $extension = 'pdf';
        }

        $fileName = $safeBaseName . '.' . $extension;
        $counter = 1;
        while (Storage::disk('public')->exists($storageDirectory . '/' . $fileName)) {
            $counter++;
            $fileName = $safeBaseName . '-' . $counter . '.' . $extension;
        }

        $storedPath = $uploadedPdf->storeAs($storageDirectory, $fileName, 'public');

        if ($documentKind === 'technical_manual') {
            if ($asset->technical_manual_path) {
                Storage::disk('public')->delete($asset->technical_manual_path);
            }
            $asset->technical_manual_path = $storedPath;
        } else {
            if ($asset->user_manual_path) {
                Storage::disk('public')->delete($asset->user_manual_path);
            }
            $asset->user_manual_path = $storedPath;
        }

        $asset->save();

        return redirect()
            ->route('formations.index')
            ->with('success', 'PDF importé avec succès pour la désignation: ' . $designation . '.');
    }

    public function exportFormationsPdf(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $assetsQuery = EquipmentDesignationAsset::query()
            ->select(['designation', 'user_manual_path', 'technical_manual_path', 'updated_at'])
            ->where(function ($query) {
                $query
                    ->whereNotNull('user_manual_path')
                    ->orWhereNotNull('technical_manual_path');
            });

        if ($search !== '') {
            $assetsQuery->where('designation', 'like', '%' . $search . '%');
        }

        $assets = $assetsQuery->orderBy('designation')->get();

        $rows = $assets->map(function (EquipmentDesignationAsset $asset) {
            return [
                'designation' => $asset->designation ?: '-',
                'technical_file' => $asset->technical_manual_path ? basename(str_replace('\\', '/', $asset->technical_manual_path)) : '-',
                'user_file' => $asset->user_manual_path ? basename(str_replace('\\', '/', $asset->user_manual_path)) : '-',
                'updated_at' => optional($asset->updated_at)->format('d/m/Y H:i') ?: '-',
            ];
        })->values();

        $pdf = Pdf::loadView('pdf.formations', [
            'rows' => $rows,
            'search' => $search,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('formations-' . now()->format('Y-m-d-His') . '.pdf');
    }

    public function designationAssetFile(EquipmentDesignationAsset $asset, string $type)
    {
        $path = match ($type) {
            'image' => $asset->image_path,
            'user-manual' => $asset->user_manual_path,
            'technical-manual' => $asset->technical_manual_path,
            default => null,
        };

        if (!$path) {
            abort(404);
        }

        $fullPath = storage_path('app/public/' . ltrim(str_replace('\\', '/', $path), '/'));
        if (!is_file($fullPath)) {
            abort(404);
        }

        if ($type === 'image') {
            return response()->file($fullPath);
        }

        $extension = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            return response('<div style="font-family:Arial,sans-serif;padding:16px;color:#374151;">Ce manuel n\'est pas en PDF. Pour lecture dans la fenêtre GMAO, veuillez uploader un manuel au format PDF.</div>', 200)
                ->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return response()->file($fullPath, [
            'Content-Disposition' => 'inline; filename="' . basename($fullPath) . '"',
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function phpExecutablePath(): string
    {
        $configuredPath = 'C:\\Users\\Dell\\AppData\\Local\\Microsoft\\WinGet\\Packages\\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\\php.exe';

        if (is_file($configuredPath)) {
            return $configuredPath;
        }

        return PHP_BINARY;
    }

    private function phpExtensionDir(): string
    {
        $configuredDir = 'C:\\Users\\Dell\\AppData\\Local\\Microsoft\\WinGet\\Packages\\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\\ext';

        if (is_dir($configuredDir)) {
            return $configuredDir;
        }

        return dirname(PHP_BINARY) . DIRECTORY_SEPARATOR . 'ext';
    }

    private function buildFilteredEquipmentQuery(int $selectedHospitalId, int $selectedServiceId, int $selectedCategoryId, string $search, string $sortDirection, $user, array $selectedHospitalIds = []): Builder
    {
        $query = Equipment::query()
            ->with([
                'hospital:id,code,name',
                'store:id,name',
                'company:id,name',
                'market:id,market_date,market_number,reference',
                'zone:id,name',
                'service:id,zone_id,name',
                'category:id,service_id,name',
                'room:id,service_id,room_number',
            ])
            ->select([
                'id',
                'inventory_number_current',
                'serial_number',
                'designation',
                'brand_name',
                'model_name',
                'unit_name',
                'sector_name',
                'sector_description',
                'market_label',
                'lot_number',
                'article',
                'date_reception_provisoire',
                'duree_garantie',
                'date_reception_definitive',
                'manufacture_date',
                'icon_class',
                'category_name',
                'lifecycle_status',
                'description',
                'serial_label_removed',
                'serial_label_comment',
                'service_name',
                'exact_location',
                'zone_id',
                'service_id',
                'category_id',
                'room_id',
                'hospital_id',
                'store_id',
                'company_id',
                'market_id',
                'operational_status',
                'created_at',
            ])
            ->when($selectedHospitalId > 0, function ($innerQuery) use ($selectedHospitalId, $selectedHospitalIds) {
                if ($selectedHospitalIds !== []) {
                    $innerQuery->whereIn('hospital_id', $selectedHospitalIds);
                    return;
                }

                $innerQuery->where('hospital_id', $selectedHospitalId);
            })
            ->when($selectedServiceId > 0, function ($innerQuery) use ($selectedServiceId) {
                $innerQuery->where('service_id', $selectedServiceId);
            })
            ->when($selectedCategoryId > 0, function ($innerQuery) use ($selectedCategoryId) {
                $innerQuery->where('category_id', $selectedCategoryId);
            })
            ->when($search !== '', function ($innerQuery) use ($search) {
                $innerQuery->where(function ($searchQuery) use ($search) {
                    $like = '%' . $search . '%';
                    $searchQuery
                        ->where('inventory_number_current', 'like', $like)
                        ->orWhere('designation', 'like', $like)
                        ->orWhere('serial_number', 'like', $like)
                        ->orWhere('unit_name', 'like', $like)
                        ->orWhere('sector_name', 'like', $like)
                        ->orWhere('sector_description', 'like', $like)
                        ->orWhere('brand_name', 'like', $like)
                        ->orWhere('model_name', 'like', $like)
                        ->orWhere('market_label', 'like', $like)
                        ->orWhere('lot_number', 'like', $like);
                });
            })
            ->orderBy('id', $sortDirection);

        ServiceAccess::applyEquipmentScope($query, $user);

        return $query;
    }

    private function resolveServiceIdFromStructure(int $structureId): int
    {
        if (!class_exists(Structure::class) || !\Illuminate\Support\Facades\Schema::hasTable('structures')) {
            return 0;
        }

        $structure = Structure::query()->find($structureId);
        if (!$structure || $structure->type !== 'service') {
            return 0;
        }

        $code = trim((string) ($structure->code ?? ''));
        $name = trim((string) ($structure->name ?? ''));

        if ($code !== '') {
            $byCode = Service::query()->whereRaw('LOWER(code) = ?', [mb_strtolower($code)])->value('id');
            if ($byCode) {
                return (int) $byCode;
            }
        }

        if ($name !== '') {
            $byName = Service::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->value('id');
            if ($byName) {
                return (int) $byName;
            }
        }

        return 0;
    }

    private function resolveMarketIdFromLabel(?string $label): ?int
    {
        $value = trim((string) $label);

        if ($value === '') {
            return null;
        }

        $market = Market::query()
            ->where('market_number', $value)
            ->orWhere('reference', $value)
            ->first();

        if (!$market && preg_match('/\b\d{1,3}\/\d{4}\b/', $value, $match)) {
            $market = Market::query()->where('market_number', $match[0])->first();
        }

        if (!$market && preg_match('/\bM\d{1,3}-\d{4}\b/i', $value, $match)) {
            $market = Market::query()->where('reference', 'like', strtoupper($match[0]) . '%')->first();
        }

        return $market?->id;
    }

    private function formatEquipmentsForExport($equipments): array
    {
        return $equipments->map(function (Equipment $equipment) {
            return [
                'barcode' => $equipment->inventory_number_current ?: '-',
                'designation' => $equipment->designation ?: '-',
                'serial_number' => $equipment->serial_number ?: '-',
                'unit_name' => $equipment->unit_name ?: ($equipment->service_name ?: '-'),
                'sector_name' => $equipment->sector_name ?: '-',
                'sector_description' => $equipment->sector_description ?: ($equipment->exact_location ?: '-'),
                'brand_name' => $equipment->brand_name ?: '-',
                'model_name' => $equipment->model_name ?: '-',
                'market_label' => $equipment->market_label ?: ($equipment->market?->market_number ?: '-'),
                'lot_number' => $equipment->lot_number ?: '-',
                'article' => $equipment->article ?: '-',
                'date_reception_provisoire' => optional($equipment->date_reception_provisoire)->toDateString() ?: '-',
                'duree_garantie' => $equipment->duree_garantie ?: '-',
                'date_reception_definitive' => optional($equipment->date_reception_definitive)->toDateString() ?: '-',
                'operational_status' => $equipment->operational_status ?: '-',
            ];
        })->all();
    }

    private function persistDesignationAssets(string $designation, Request $request): void
    {
        $designation = trim($designation);
        if ($designation === '') {
            return;
        }

        if (!$request->hasFile('designation_image') && !$request->hasFile('user_manual_file') && !$request->hasFile('technical_manual_file')) {
            return;
        }

        $asset = EquipmentDesignationAsset::query()->firstOrCreate([
            'designation' => $designation,
        ]);

        if ($request->hasFile('designation_image')) {
            if ($asset->image_path) {
                Storage::disk('public')->delete($asset->image_path);
            }
            $asset->image_path = $request->file('designation_image')->store('equipments/designation-assets/images', 'public');
        }

        if ($request->hasFile('user_manual_file')) {
            if ($asset->user_manual_path) {
                Storage::disk('public')->delete($asset->user_manual_path);
            }
            $asset->user_manual_path = $request->file('user_manual_file')->store('equipments/designation-assets/user-manuals', 'public');
        }

        if ($request->hasFile('technical_manual_file')) {
            if ($asset->technical_manual_path) {
                Storage::disk('public')->delete($asset->technical_manual_path);
            }
            $asset->technical_manual_path = $request->file('technical_manual_file')->store('equipments/designation-assets/technical-manuals', 'public');
        }

        $asset->save();
    }

    private function resolveServiceContext(?string $unitName, ?string $sectorName): array
    {
        $unitName = trim((string) $unitName);
        $sectorName = trim((string) $sectorName);

        if ($unitName === '') {
            return [
                'zone_id' => null,
                'service_id' => null,
                'category_id' => null,
                'hospital_id' => null,
                'room_id' => null,
            ];
        }

        $service = Service::query()
            ->whereRaw('LOWER(name) = LOWER(?)', [$unitName])
            ->orWhereRaw('LOWER(code) = LOWER(?)', [$unitName])
            ->first();

        if (!$service) {
            return [
                'zone_id' => null,
                'service_id' => null,
                'category_id' => null,
                'hospital_id' => null,
                'room_id' => null,
            ];
        }

        $categoryId = null;
        if ($sectorName !== '') {
            $categoryId = Category::query()
                ->where('service_id', $service->id)
                ->whereRaw('LOWER(name) = LOWER(?)', [$sectorName])
                ->value('id');
        }

        if (!$categoryId) {
            $serviceCategoryIds = Category::query()
                ->where('service_id', $service->id)
                ->pluck('id');

            if ($serviceCategoryIds->count() === 1) {
                $categoryId = (int) $serviceCategoryIds->first();
            }
        }

        $roomId = null;
        if ($sectorName !== '') {
            $roomId = Room::query()
                ->where('service_id', $service->id)
                ->whereRaw('LOWER(room_number) = LOWER(?)', [$sectorName])
                ->value('id');
        }

        return [
            'zone_id' => $service->zone_id,
            'service_id' => $service->id,
            'category_id' => $categoryId,
            'hospital_id' => $service->hospital_id,
            'room_id' => $roomId,
        ];
    }

    private function deduplicateHospitals($hospitals)
    {
        return collect($hospitals)
            ->groupBy(function ($hospital) {
                return $this->hospitalCanonicalKey(
                    (string) ($hospital->code ?? ''),
                    (string) ($hospital->name ?? '')
                );
            })
            ->map(function ($group) {
                $items = $group->values()->all();
                usort($items, function ($a, $b) {
                    $aServices = (int) ($a->services_count ?? 0);
                    $bServices = (int) ($b->services_count ?? 0);
                    if ($aServices !== $bServices) {
                        return $bServices <=> $aServices;
                    }

                    $aEquipments = (int) ($a->equipments_count ?? 0);
                    $bEquipments = (int) ($b->equipments_count ?? 0);
                    if ($aEquipments !== $bEquipments) {
                        return $bEquipments <=> $aEquipments;
                    }

                    return (int) ($a->id ?? 0) <=> (int) ($b->id ?? 0);
                });

                return $items[0] ?? null;
            })
            ->filter()
            ->values();
    }

    private function resolveCanonicalHospitalId(int $selectedHospitalId, $hospitals): int
    {
        if ($selectedHospitalId <= 0) {
            return 0;
        }

        $selected = collect($hospitals)->firstWhere('id', $selectedHospitalId);
        if (!$selected) {
            return 0;
        }

        $targetKey = $this->hospitalCanonicalKey(
            (string) ($selected->code ?? ''),
            (string) ($selected->name ?? '')
        );

        $canonical = collect($hospitals)
            ->filter(function ($hospital) use ($targetKey) {
                return $this->hospitalCanonicalKey(
                    (string) ($hospital->code ?? ''),
                    (string) ($hospital->name ?? '')
                ) === $targetKey;
            })
            ->values()
            ->all();

        usort($canonical, function ($a, $b) {
            $aServices = (int) ($a->services_count ?? 0);
            $bServices = (int) ($b->services_count ?? 0);
            if ($aServices !== $bServices) {
                return $bServices <=> $aServices;
            }

            $aEquipments = (int) ($a->equipments_count ?? 0);
            $bEquipments = (int) ($b->equipments_count ?? 0);
            if ($aEquipments !== $bEquipments) {
                return $bEquipments <=> $aEquipments;
            }

            return (int) ($a->id ?? 0) <=> (int) ($b->id ?? 0);
        });

        $canonicalHospital = $canonical[0] ?? null;

        return (int) ($canonicalHospital->id ?? $selectedHospitalId);
    }

    private function resolveCanonicalHospitalIds(int $selectedHospitalId, $hospitals): array
    {
        if ($selectedHospitalId <= 0) {
            return [];
        }

        $selected = collect($hospitals)->firstWhere('id', $selectedHospitalId);
        if (!$selected) {
            return [$selectedHospitalId];
        }

        $targetKey = $this->hospitalCanonicalKey(
            (string) ($selected->code ?? ''),
            (string) ($selected->name ?? '')
        );

        return collect($hospitals)
            ->filter(function ($hospital) use ($targetKey) {
                return $this->hospitalCanonicalKey(
                    (string) ($hospital->code ?? ''),
                    (string) ($hospital->name ?? '')
                ) === $targetKey;
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();
    }

    private function hospitalCanonicalKey(string $code, string $name): string
    {
        $normalizedCode = mb_strtoupper(trim($code));
        if (in_array($normalizedCode, ['HO', 'HSP'], true)) {
            return 'hopital-des-specialites';
        }
        if ($normalizedCode === 'HME') {
            return 'hopital-mere-enfants';
        }

        $normalizedName = mb_strtolower(trim($name));
        $asciiName = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalizedName);
        if (is_string($asciiName) && $asciiName !== '') {
            $normalizedName = $asciiName;
        }
        $normalizedName = preg_replace('/[^a-z0-9]+/', '-', $normalizedName) ?: '';
        $normalizedName = trim($normalizedName, '-');

        if (str_contains($normalizedName, 'specialit')) {
            return 'hopital-des-specialites';
        }
        if (str_contains($normalizedName, 'mere') || str_contains($normalizedName, 'enfants') || str_contains($normalizedName, 'enfant')) {
            return 'hopital-mere-enfants';
        }

        return $normalizedName !== '' ? $normalizedName : 'hospital-' . md5($code . '|' . $name);
    }

}
