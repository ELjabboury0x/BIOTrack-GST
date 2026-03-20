<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Equipment;
use App\Models\EquipmentVerification;
use App\Models\Hospital;
use App\Models\Intervention;
use App\Models\MaintenanceReport;
use App\Models\Market;
use App\Models\MarketEquipmentImportLine;
use App\Models\Store;
use App\Models\User;
use App\Services\DashboardMetricsService;
use App\Services\RealtimeMetricsBroadcaster;
use App\Support\ServiceAccess;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class BiomedDataController extends Controller
{
    public function __construct(
        private DashboardMetricsService $dashboardMetricsService,
        private RealtimeMetricsBroadcaster $realtimeMetricsBroadcaster
    ) {}

    public function dashboard(Request $request)
    {
        $downtimeFilterDays = $this->normalizeDowntimeFilterDays($request->integer('downtime_days', 30));
        $designation = trim((string) $request->query('designation', ''));
        $periodMonth = $request->integer('period_month');
        $periodYear = $request->integer('period_year');
        $serviceId = $request->integer('service_id');
        $metrics = $this->dashboardMetricsService->build(
            auth()->user(),
            $downtimeFilterDays,
            $designation !== '' ? $designation : null,
            $periodMonth > 0 ? $periodMonth : null,
            $periodYear > 0 ? $periodYear : null,
            $serviceId > 0 ? $serviceId : null
        );

        return view('pages.dashboard', [
            'kpi' => $metrics['kpi'],
            'charts' => $metrics['charts'],
            'hasData' => $metrics['hasData'],
            'downtimeFilterDays' => $downtimeFilterDays,
            'selectedDesignation' => $designation,
        ]);
    }

    public function liveMetrics(Request $request): JsonResponse
    {
        $downtimeFilterDays = $this->normalizeDowntimeFilterDays($request->integer('downtime_days', 30));
        $designation = trim((string) $request->query('designation', ''));
        $periodMonth = $request->integer('period_month');
        $periodYear = $request->integer('period_year');
        $serviceId = $request->integer('service_id');
        $metrics = $this->dashboardMetricsService->build(
            auth()->user(),
            $downtimeFilterDays,
            $designation !== '' ? $designation : null,
            $periodMonth > 0 ? $periodMonth : null,
            $periodYear > 0 ? $periodYear : null,
            $serviceId > 0 ? $serviceId : null
        );

        return response()->json([
            'kpi' => $metrics['kpi'],
            'charts' => $metrics['charts'],
            'hasData' => $metrics['hasData'],
            'downtime_days' => $downtimeFilterDays,
            'designation' => $designation,
            'period_month' => $periodMonth > 0 ? $periodMonth : null,
            'period_year' => $periodYear > 0 ? $periodYear : null,
            'service_id' => $serviceId > 0 ? $serviceId : null,
            'updated_at' => now()->toDateTimeString(),
        ]);
    }

    private function normalizeDowntimeFilterDays(int $days): int
    {
        $allowed = [7, 30, 90, 180, 365];

        return in_array($days, $allowed, true) ? $days : 30;
    }

    public function equipements()
    {
        if (!$this->databaseAvailable()) {
            return view('pages.equipements', [
                'equipementsData' => [],
            ]);
        }

        $equipmentQuery = Equipment::query()->latest('id');
        ServiceAccess::applyEquipmentScope($equipmentQuery, auth()->user());
        $equipmentQuery->whereNotNull('service_id');

        $equipments = $equipmentQuery
            ->get()
            ->map(function (Equipment $equipment) {
                return [
                    'id' => $equipment->id,
                    'code' => $equipment->inventory_number_current,
                    'nom' => $equipment->designation,
                    'type' => $equipment->service_name ?: '-',
                    'localisation' => $equipment->exact_location ?: '-',
                    'statut' => $equipment->operational_status,
                    'date_installation' => optional($equipment->created_at)->toDateString(),
                ];
            })
            ->values();

        return view('pages.equipements', [
            'equipementsData' => $equipments,
        ]);
    }

    public function rapports()
    {
        if (!$this->databaseAvailable()) {
            return view('pages.rapports', [
                'reportsData' => [],
            ]);
        }

        $today = now()->toDateString();
        $rows = [];

        $reportQuery = MaintenanceReport::query()
            ->selectRaw('intervention_type as type, COUNT(*) as total, MAX(COALESCE(closed_at, validated_at, submitted_at, updated_at, created_at)) as latest_date')
            ->whereIn('intervention_type', [MaintenanceReport::TYPE_PREVENTIVE, MaintenanceReport::TYPE_CURATIVE])
            ->groupBy('intervention_type');
        ServiceAccess::applyReportScope($reportQuery, auth()->user());
        $reportsByType = $reportQuery->get()->keyBy('type');

        foreach ([MaintenanceReport::TYPE_PREVENTIVE, MaintenanceReport::TYPE_CURATIVE] as $index => $maintenanceType) {
            $bucket = $reportsByType->get($maintenanceType);
            $count = (int) ($bucket->total ?? 0);
            $latestDate = $bucket && !empty($bucket->latest_date)
                ? Carbon::parse((string) $bucket->latest_date)->toDateString()
                : $today;
            $typeLabel = $maintenanceType === MaintenanceReport::TYPE_PREVENTIVE ? 'Préventive interne' : 'Curative interne';

            $rows[] = [
                'id' => 10 + $index,
                'nom' => 'Rapports ' . $typeLabel . ' (' . $count . ')',
                'type' => $typeLabel,
                'date_generation' => $latestDate,
                'createur' => 'Système',
                'statut' => $count > 0 ? 'termine' : 'en_attente',
                'edit_url' => route('maintenance-reports.index', ['type' => $maintenanceType]),
                'fill_url' => route('maintenance-reports.create', ['type' => $maintenanceType]),
            ];
        }

        $historyQuery = MaintenanceReport::query()
            ->with(['equipment:id,inventory_number_current,designation', 'service:id,name'])
            ->latest('id');
        ServiceAccess::applyReportScope($historyQuery, auth()->user());

        $reportHistoryData = $historyQuery
            ->get()
            ->map(function (MaintenanceReport $report) {
                $typeLabel = $report->intervention_type === MaintenanceReport::TYPE_PREVENTIVE
                    ? 'Préventive'
                    : 'Curative';
                $scopeLabel = strtolower(trim((string) ($report->intervention_scope ?: 'interne'))) === 'externe'
                    ? 'externe'
                    : 'interne';

                return [
                    'id' => $report->id,
                    'numero' => $report->report_number,
                    'type' => $typeLabel . ' ' . $scopeLabel,
                    'equipement' => trim(((string) ($report->equipment?->inventory_number_current ?: '-')) . ' - ' . ((string) ($report->equipment?->designation ?: '-'))),
                    'service' => $report->service?->name ?: '-',
                    'date_intervention' => optional($report->intervention_date)->toDateString(),
                    'statut' => $report->status,
                    'edit_url' => route('maintenance-reports.edit', $report),
                ];
            })
            ->values();

        $statsQuery = MaintenanceReport::query();
        ServiceAccess::applyReportScope($statsQuery, auth()->user());

        $stats = (clone $statsQuery)
            ->selectRaw(
                'COUNT(*) as total_reports,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as closed_reports,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as submitted_reports,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as draft_reports,
                MAX(intervention_date) as latest_intervention_date',
                [
                    MaintenanceReport::STATUS_CLOSED,
                    MaintenanceReport::STATUS_SUBMITTED,
                    MaintenanceReport::STATUS_DRAFT,
                ]
            )
            ->first();

        return view('pages.rapports', [
            'reportsData' => $rows,
            'reportHistoryData' => $reportHistoryData,
            'reportStats' => [
                'total' => (int) ($stats->total_reports ?? 0),
                'closed' => (int) ($stats->closed_reports ?? 0),
                'submitted' => (int) ($stats->submitted_reports ?? 0),
                'draft' => (int) ($stats->draft_reports ?? 0),
                'latest_date' => !empty($stats->latest_intervention_date)
                    ? Carbon::parse((string) $stats->latest_intervention_date)->format('d/m/Y')
                    : '-',
            ],
        ]);
    }

    public function techniciens()
    {
        $mapUser = function (User $user): array {
            return [
                'id' => $user->id,
                'nom' => $user->name ?: $user->login,
                'email' => $user->email ?: '-',
                'telephone' => '-',
                'specialite' => $user->unit?->name ?: $user->primaryService?->name ?: 'Biomédical',
                'statut' => $user->is_active ? 'actif' : 'inactif',
            ];
        };

        $usersBaseQuery = User::query()
            ->with(['primaryService:id,name', 'unit:id,name'])
            ->orderBy('name');

        $ingenieursData = (clone $usersBaseQuery)
            ->where('role', 'ingenieur')
            ->get()
            ->map($mapUser)
            ->values();

        $techniciensData = (clone $usersBaseQuery)
            ->whereIn('role', ['technicien', 'technician'])
            ->get()
            ->map($mapUser)
            ->values();

        $majorsData = (clone $usersBaseQuery)
            ->where('role', 'major')
            ->get()
            ->map($mapUser)
            ->values();

        return view('pages.techniciens', [
            'ingenieursData' => $ingenieursData,
            'techniciensData' => $techniciensData,
            'majorsData' => $majorsData,
        ]);
    }

    public function pieces()
    {
        return view('pages.pieces', [
            'piecesData' => [],
        ]);
    }

    public function marketsEquipments(Request $request)
    {
        if (!$this->databaseAvailable()) {
            return view('pages.markets-equipments', [
                'marketsData' => [],
                'marketsPagination' => null,
                'marketNumberFilter' => '',
                'companyFilter' => '',
            ]);
        }

        $marketNumberFilter = trim((string) $request->query('market_number', ''));
        $companyFilter = trim((string) $request->query('company', ''));
        $normalizedMarketNumberFilter = preg_replace('/\s+/', '', $marketNumberFilter) ?? $marketNumberFilter;

        $query = MarketEquipmentImportLine::query()
            ->with([
                'market.company',
            ])
            ->whereHas('market');

        if ($marketNumberFilter !== '') {
            $query->whereHas('market', function ($marketQuery) use ($marketNumberFilter, $normalizedMarketNumberFilter) {
                if (preg_match('/^\d+\/\d{4}$/', $normalizedMarketNumberFilter) === 1) {
                    $marketQuery->whereRaw('REPLACE(market_number, " ", "") = ?', [$normalizedMarketNumberFilter]);
                    return;
                }

                $marketQuery->where('market_number', 'like', '%' . $marketNumberFilter . '%');
            });
        }

        if ($companyFilter !== '') {
            $query->whereHas('market.company', function ($companyQuery) use ($companyFilter) {
                $companyQuery->where('name', 'like', '%' . $companyFilter . '%');
            });
        }

        $marketSummary = [
            'lines_total' => (clone $query)->count(),
            'markets_total' => (clone $query)->distinct('market_id')->count('market_id'),
            'companies_total' => (clone $query)
                ->join('markets as summary_markets', 'summary_markets.id', '=', 'market_equipment_import_lines.market_id')
                ->whereNotNull('summary_markets.company_id')
                ->distinct('summary_markets.company_id')
                ->count('summary_markets.company_id'),
            'delivery_filled' => (clone $query)
                ->whereRaw('TRIM(COALESCE(delivery_status, "")) <> ""')
                ->count(),
            'complaint_filled' => (clone $query)
                ->whereRaw('TRIM(COALESCE(market_complaint_status, "")) <> ""')
                ->count(),
            'no_status_lines' => (clone $query)
                ->whereRaw('TRIM(COALESCE(delivery_status, "")) = ""')
                ->whereRaw('TRIM(COALESCE(market_complaint_status, "")) = ""')
                ->count(),
        ];

        $deliveryDistributionRaw = (clone $query)
            ->selectRaw('LOWER(TRIM(COALESCE(delivery_status, ""))) as status_key, COUNT(*) as total')
            ->groupBy('status_key')
            ->pluck('total', 'status_key');

        $complaintDistributionRaw = (clone $query)
            ->selectRaw('LOWER(TRIM(COALESCE(market_complaint_status, ""))) as status_key, COUNT(*) as total')
            ->groupBy('status_key')
            ->pluck('total', 'status_key');

        $formatDistribution = function ($distributionRaw) use ($marketSummary) {
            return collect($distributionRaw)
                ->map(function ($count, $statusKey) use ($marketSummary) {
                    $statusKey = trim((string) $statusKey);
                    $label = $statusKey === '' ? 'Vide' : (string) Str::of($statusKey)->replace('_', ' ')->title();
                    $countInt = (int) $count;
                    $pct = ($marketSummary['lines_total'] ?? 0) > 0
                        ? (int) round(($countInt / (int) $marketSummary['lines_total']) * 100)
                        : 0;

                    return [
                        'key' => $statusKey,
                        'label' => $label,
                        'count' => $countInt,
                        'pct' => $pct,
                    ];
                })
                ->sortByDesc('count')
                ->values()
                ->all();
        };

        $marketSummary['delivery_distribution'] = $formatDistribution($deliveryDistributionRaw);
        $marketSummary['complaint_distribution'] = $formatDistribution($complaintDistributionRaw);

        $marketsPagination = $query
            ->orderBy('market_id')
            ->orderBy('source_row_index')
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        $marketsData = $marketsPagination->getCollection()->map(function (MarketEquipmentImportLine $line) {
            $market = $line->market;

            $deliveryStatus = trim((string) ($line->delivery_status ?? ''));
            $deliveryDate = optional($line->delivery_date)->format('Y-m-d') ?: null;

            $complaintStatus = trim((string) ($line->market_complaint_status ?? ''));
            $complaintDate = optional($line->market_complaint_date)->format('Y-m-d') ?: null;

            return [
                'line_id' => $line->id,
                'market_id' => $market?->id,
                'market_number' => $market?->market_number ?: '-',
                'market_object' => $line->market_object ?: ($market?->reference ?: '-'),
                'company' => $market?->company?->name ?: '-',
                'lot_number' => $line->lot_number ?: '-',
                'article' => $line->article ?: '-',
                'designation' => $line->designation ?: '-',
                'quantity' => $line->quantity !== null ? rtrim(rtrim(number_format((float) $line->quantity, 2, '.', ''), '0'), '.') : '-',
                'delivery_status' => $deliveryStatus !== '' ? $deliveryStatus : '-',
                'delivery_date' => $deliveryDate ?: '-',
                'complaint_status' => $complaintStatus !== '' ? $complaintStatus : '-',
                'complaint_date' => $complaintDate ?: '-',
                'observations' => $line->observations ?: '-',
                'recommendations' => $line->recommendations ?: '-',
            ];
        })->values();

        return view('pages.markets-equipments', [
            'marketsData' => $marketsData,
            'marketsPagination' => $marketsPagination,
            'marketSummary' => $marketSummary,
            'marketNumberFilter' => $marketNumberFilter,
            'companyFilter' => $companyFilter,
        ]);
    }

    public function showMarket(Market $market)
    {
        $market->load([
            'company',
            'equipments' => function ($equipmentsQuery) {
                $equipmentsQuery->with(['interventions' => function ($interventionsQuery) {
                    $interventionsQuery->latest('id');
                }]);
                $equipmentsQuery->orderBy('inventory_number_current');
            },
        ]);

        $marketData = [
            'id' => $market->id,
            'reference' => $market->reference ?: '-',
            'market_number' => $market->market_number ?: '-',
            'company' => $market->company?->name ?: '-',
            'market_date' => optional($market->market_date)->format('Y-m-d') ?: '-',
            'source_file_name' => $market->source_file_name ?: '-',
            'equipments' => $market->equipments->map(function (Equipment $equipment) {
                return [
                    'id' => $equipment->id,
                    'inventory_number' => $equipment->inventory_number_current ?: '-',
                    'designation' => $equipment->designation ?: '-',
                    'serial_number' => $equipment->serial_number ?: '-',
                    'intervention_code' => $equipment->interventions->first()?->code ?: '-',
                    'service_name' => $equipment->service_name ?: '-',
                ];
            })->values()->all(),
        ];

        return view('pages.market-show', [
            'marketData' => $marketData,
        ]);
    }

    public function editMarket(Market $market)
    {
        $market->load(['company'])->loadCount('equipments');

        return view('pages.forms.market-edit', [
            'market' => $market,
        ]);
    }

    public function updateMarket(Request $request, Market $market)
    {
        $validated = $request->validate([
            'reference' => 'nullable|string|max:255',
            'market_number' => 'nullable|string|max:120',
            'market_date' => 'nullable|date',
            'company_name' => 'nullable|string|max:180',
        ]);

        $reference = trim((string) ($validated['reference'] ?? '')) ?: null;
        $marketNumber = trim((string) ($validated['market_number'] ?? '')) ?: null;
        $companyName = trim((string) ($validated['company_name'] ?? '')) ?: null;

        if ($reference) {
            $referenceExists = Market::query()
                ->where('id', '!=', $market->id)
                ->where('reference', $reference)
                ->exists();

            if ($referenceExists) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'La référence existe déjà pour un autre marché.');
            }
        }

        $company = null;
        if ($companyName) {
            $company = Company::firstOrCreate(['name' => $companyName]);
        }

        $market->update([
            'reference' => $reference,
            'market_number' => $marketNumber,
            'market_date' => $validated['market_date'] ?? null,
            'company_id' => $company?->id,
        ]);

        return redirect()
            ->route('markets.equipments')
            ->with('success', 'Marché modifié avec succès.');
    }

    public function destroyMarket(Market $market)
    {
        $linkedEquipments = $market->equipments()->count();

        if ($linkedEquipments > 0) {
            $market->equipments()->update(['market_id' => null]);
        }

        $market->delete();

        return redirect()
            ->route('markets.equipments')
            ->with('success', $linkedEquipments > 0
                ? 'Marché supprimé avec succès. ' . $linkedEquipments . ' équipement(s) ont été dissociés.'
                : 'Marché supprimé avec succès.');
    }

    public function importMarketsEquipmentsExcel(Request $request)
    {
        @ini_set('max_execution_time', '0');
        @set_time_limit(0);

        $validated = $request->validate([
            'excel_files' => 'nullable|array|min:1',
            'excel_files.*' => 'file|mimes:xlsx,xls|max:51200',
            'excel_file' => 'nullable|file|mimes:xlsx,xls|max:51200',
        ]);

        $uploads = $validated['excel_files'] ?? [];
        if (empty($uploads) && isset($validated['excel_file'])) {
            $uploads = [$validated['excel_file']];
        }

        if (empty($uploads)) {
            return redirect()
                ->route('markets.equipments')
                ->with('import_result', [
                    'type' => 'error',
                    'title' => 'Aucun fichier sélectionné',
                    'icon' => 'fas fa-file-excel',
                    'message' => 'Sélectionnez au moins un fichier Excel pour lancer l\'import.',
                    'details' => [],
                    'tips' => [
                        'Vous pouvez sélectionner plusieurs fichiers en même temps.',
                    ],
                ]);
        }

        $selectedCount = count($uploads);
        $replacementDetails = [];
        $blockedDetails = [];

        // ── Store the file with its ORIGINAL name so the import command can parse it ──
        $excelRoot = base_path('excel');
        $marketsDir = $excelRoot . DIRECTORY_SEPARATOR . 'marches-equipements';

        if (!is_dir($marketsDir) && !mkdir($marketsDir, 0775, true) && !is_dir($marketsDir)) {
            return redirect()
                ->route('markets.equipments')
                ->with('error', 'Import impossible: dossier excel/marches-equipements introuvable ou non accessible.');
        }

        $absolutePaths = [];
        $selectedNames = [];

        foreach ($uploads as $upload) {
            $originalFileName = $upload->getClientOriginalName();
            $selectedNames[] = $originalFileName;

            $existingMarket = Market::with('company')->withCount('equipments')->where('source_file_name', $originalFileName)->first();
            if ($existingMarket) {
                if ($this->canReplaceEmptyImportedMarket($existingMarket)) {
                    $replacementDetails[] = $originalFileName . ' : ancien marché vide remplacé (même fichier).';
                    $existingMarket->delete();
                } else {
                    $blockedDetails[] = $originalFileName . ' : fichier déjà importé avec équipements liés.';
                    continue;
                }
            }

            $fileBaseName = pathinfo($originalFileName, PATHINFO_FILENAME);
            $existingByRef = Market::with('company')->withCount('equipments')->where('reference', $fileBaseName)->first();
            if ($existingByRef) {
                if ($this->canReplaceEmptyImportedMarket($existingByRef)) {
                    $replacementDetails[] = $originalFileName . ' : ancien marché vide remplacé (même référence).';
                    $existingByRef->delete();
                } else {
                    $blockedDetails[] = $originalFileName . ' : référence déjà existante avec équipements liés.';
                    continue;
                }
            }

            // Keep original filename; prefix with timestamp only if already on disk
            $targetFileName = $originalFileName;
            if (is_file($marketsDir . DIRECTORY_SEPARATOR . $targetFileName)) {
                $targetFileName = now()->format('Ymd-His-u') . '-' . $originalFileName;
            }

            $storedFile = $upload->move($marketsDir, $targetFileName);
            $absolutePaths[] = $storedFile->getPathname();
        }

        if (empty($absolutePaths)) {
            return redirect()
                ->route('markets.equipments')
                ->with('import_result', [
                    'type' => 'warning',
                    'title' => 'Aucun fichier importé',
                    'icon' => 'fas fa-exclamation-triangle',
                    'message' => 'Aucun fichier sélectionné n\'a pu être importé.',
                    'details' => $blockedDetails,
                    'tips' => [
                        'Le remplacement automatique est possible seulement si le marché existant a 0 équipement.',
                    ],
                ]);
        }

        // ── Run import command ──
        try {
            $exitCode = Artisan::call('gmao:import-biomedical-excel', [
                '--file' => $absolutePaths,
            ]);

            $output = trim(Artisan::output());

            // Parse the output into a user-friendly message
            $result = $this->parseImportOutput(
                $output,
                count($absolutePaths) === 1 ? basename((string) ($selectedNames[0] ?? 'fichier')) : count($absolutePaths) . ' fichiers',
                count($absolutePaths)
            );

            if (!empty($replacementDetails)) {
                $result['details'] = array_values(array_merge($replacementDetails, $result['details'] ?? []));
            }

            if (!empty($blockedDetails)) {
                $result['details'] = array_values(array_merge($result['details'] ?? [], $blockedDetails));
                $result['tips'] = array_values(array_unique(array_merge($result['tips'] ?? [], [
                    'Certains fichiers ont été ignorés car déjà importés avec des équipements liés.',
                ])));
                if (($result['type'] ?? 'success') === 'success') {
                    $result['type'] = 'warning';
                    $result['title'] = 'Import partiel terminé';
                }
            }

            if ($selectedCount > 1 && ($result['type'] ?? '') === 'success') {
                $result['details'] = array_values(array_merge([
                    count($absolutePaths) . ' fichier(s) importé(s) sur ' . $selectedCount . ' sélectionné(s).',
                ], $result['details'] ?? []));
            }

            if ($exitCode !== 0 || $result['type'] === 'error') {
                return redirect()
                    ->route('markets.equipments')
                    ->with('import_result', $result);
            }

            // Avoid heavy dashboard recomputation in this HTTP request to prevent timeout.
            // Metrics will refresh naturally on next dashboard poll/load.

            return redirect()
                ->route('markets.equipments')
                ->with('import_result', $result);
        } catch (Throwable $exception) {
            foreach ($absolutePaths as $absolutePath) {
                if (is_file($absolutePath)) {
                    @unlink($absolutePath);
                }
            }

            return redirect()
                ->route('markets.equipments')
                ->with('import_result', [
                    'type' => 'error',
                    'title' => 'Erreur inattendue',
                    'icon' => 'fas fa-bomb',
                    'message' => 'Une erreur est survenue lors de l\'import.',
                    'details' => [
                        'Fichiers concernés : ' . count($absolutePaths),
                        'Erreur : ' . Str::limit($exception->getMessage(), 200),
                    ],
                    'tips' => [
                        'Vérifiez que le fichier n\'est pas corrompu.',
                        'Assurez-vous qu\'il s\'agit d\'un fichier Excel (.xlsx) valide.',
                        'N\'utilisez pas de fichier "Copie" ou renommé manuellement.',
                    ],
                ]);
        }
    }

    /**
     * Parse the Artisan import output into a structured result array for the UI.
     */
    private function parseImportOutput(string $output, string $fileName, int $fileCount = 1): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $filesIgnored = 0;
        $unreadable = false;

        if (preg_match('/Cr[ée]+s?:\s*(\d+)/u', $output, $m)) $created = (int) $m[1];
        if (preg_match('/Mis [àa] jour:\s*(\d+)/u', $output, $m)) $updated = (int) $m[1];
        if (preg_match('/Ignor[ée]+s?:\s*(\d+)/u', $output, $m)) $skipped = (int) $m[1];
        if (preg_match('/Fichiers ignor[ée]+s?:\s*(\d+)/u', $output, $m)) $filesIgnored = (int) $m[1];
        if (str_contains($output, 'lecture impossible')) $unreadable = true;

        $subject = $fileCount > 1
            ? 'Les fichiers sélectionnés'
            : 'Le fichier « ' . $fileName . ' »';
        $verbRead = $fileCount > 1 ? 'n\'ont pas pu être lus' : 'n\'a pas pu être lu';
        $verbFound = $fileCount > 1 ? 'ont été lus' : 'a été lu';
        $verbImported = $fileCount > 1 ? 'ont été importés' : 'a été importé';

        // Case 1: File could not be read at all
        if ($unreadable || $filesIgnored > 0) {
            return [
                'type' => 'error',
                'title' => 'Fichier illisible',
                'icon' => 'fas fa-file-excel',
                'message' => $subject . ' ' . $verbRead . ' par le système.',
                'details' => [
                    'Aucun équipement n\'a été importé.',
                ],
                'tips' => [
                    'Le fichier est peut-être corrompu ou dans un format non supporté.',
                    'Vérifiez qu\'il s\'agit d\'un vrai fichier Excel (.xlsx), pas un fichier renommé.',
                    'Les fichiers « Copie » ou « Copy » de Windows peuvent être endommagés.',
                    'Essayez d\'ouvrir le fichier dans Excel et de le ré-enregistrer en .xlsx.',
                    'Assurez-vous que le fichier n\'est pas protégé par un mot de passe.',
                ],
            ];
        }

        // Case 2: No equipment found in the file
        if ($created === 0 && $updated === 0 && $skipped === 0) {
            return [
                'type' => 'warning',
                'title' => 'Fichier vide',
                'icon' => 'fas fa-inbox',
                'message' => $subject . ' ' . $verbFound . ' mais aucun équipement n\'a été trouvé.',
                'details' => [
                    'Le fichier ne contient pas de lignes d\'équipements valides.',
                ],
                'tips' => [
                    'Vérifiez que le fichier contient les colonnes : N° inventaire, Désignation.',
                    'Les en-têtes doivent être sur les premières lignes du fichier.',
                ],
            ];
        }

        // Case 3: Success
        $details = [];
        if ($created > 0) $details[] = $created . ' équipement(s) créé(s)';
        if ($updated > 0) $details[] = $updated . ' équipement(s) mis à jour';
        if ($skipped > 0) $details[] = $skipped . ' ligne(s) ignorée(s) (sans N° inventaire valide)';

        return [
            'type' => 'success',
            'title' => 'Import réussi !',
            'icon' => 'fas fa-check-circle',
            'message' => $subject . ' ' . $verbImported . ' avec succès.',
            'details' => $details,
            'tips' => [],
        ];
    }

    private function canReplaceEmptyImportedMarket(Market $market): bool
    {
        return (int) ($market->equipments_count ?? 0) === 0;
    }

    public function updateMarketEquipment(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'inventory_number_current' => 'required|string|max:80',
            'designation' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:120',
            'service_name' => 'nullable|string|max:120',
            'intervention_code' => 'nullable|string|max:80',
        ]);

        $exists = Equipment::query()
            ->where('inventory_number_current', $validated['inventory_number_current'])
            ->where('id', '!=', $equipment->id)
            ->exists();

        if ($exists) {
            return redirect()
                ->back()
                ->with('error', 'Le numéro d\'inventaire existe déjà pour un autre équipement.');
        }

        $interventionCode = trim((string) ($validated['intervention_code'] ?? ''));
        $latestIntervention = $equipment->interventions()->latest('id')->first();

        if ($interventionCode !== '') {
            $interventionExists = Intervention::query()
                ->where('code', $interventionCode)
                ->when($latestIntervention, fn ($query) => $query->where('id', '!=', $latestIntervention->id))
                ->exists();

            if ($interventionExists) {
                return redirect()
                    ->back()
                    ->with('error', 'Le numéro d\'intervention existe déjà.');
            }
        }

        $equipment->update([
            'inventory_number_current' => trim($validated['inventory_number_current']),
            'designation' => trim($validated['designation']),
            'serial_number' => isset($validated['serial_number']) ? trim((string) $validated['serial_number']) ?: null : null,
            'service_name' => isset($validated['service_name']) ? trim((string) $validated['service_name']) ?: null : null,
        ]);

        if ($interventionCode !== '') {
            if ($latestIntervention) {
                $latestIntervention->update(['code' => $interventionCode]);
            } else {
                Intervention::create([
                    'code' => $interventionCode,
                    'equipment_id' => $equipment->id,
                    'type' => 'Curative',
                    'status' => 'en_attente',
                    'date_start' => now()->toDateString(),
                ]);
            }
        }

        return redirect()
            ->back()
            ->with('success', 'Colonnes de l\'équipement modifiées avec succès.');
    }

    public function quickActionMarketImportLine(Request $request, MarketEquipmentImportLine $line)
    {
        $validated = $request->validate([
            'action' => 'required|in:mark_delivered,mark_complaint,clear_statuses',
        ]);

        $action = (string) $validated['action'];

        if ($action === 'mark_delivered') {
            $line->update([
                'delivery_status' => 'Oui',
                'delivery_date' => $line->delivery_date ?: now()->toDateString(),
            ]);

            $this->synchronizeDeliveryStatusForMarket((int) $line->market_id);

            return redirect()->back()->with('success', 'Statut de livraison mis à jour.');
        }

        if ($action === 'mark_complaint') {
            $line->update([
                'market_complaint_status' => 'Oui',
                'market_complaint_date' => $line->market_complaint_date ?: now()->toDateString(),
            ]);

            return redirect()->back()->with('success', 'Statut de réclamation mis à jour.');
        }

        $line->update([
            'delivery_status' => null,
            'delivery_date' => null,
            'market_complaint_status' => null,
            'market_complaint_date' => null,
        ]);

        $this->synchronizeDeliveryStatusForMarket((int) $line->market_id);

        return redirect()->back()->with('success', 'Statuts réinitialisés pour la ligne.');
    }

    public function destroyMarketImportLine(MarketEquipmentImportLine $line)
    {
        $marketId = (int) $line->market_id;
        $line->delete();

        $this->synchronizeDeliveryStatusForMarket($marketId);

        return redirect()->back()->with('success', 'Ligne importée supprimée avec succès.');
    }

    private function synchronizeDeliveryStatusForMarket(int $marketId): void
    {
        if ($marketId <= 0) {
            return;
        }

        $baseQuery = MarketEquipmentImportLine::query()->where('market_id', $marketId);

        if (!(clone $baseQuery)->exists()) {
            return;
        }

        $hasOui = (clone $baseQuery)
            ->whereRaw('LOWER(TRIM(COALESCE(delivery_status, ""))) = "oui"')
            ->exists();

        if ($hasOui) {
            (clone $baseQuery)->update(['delivery_status' => 'Oui']);
            return;
        }

        (clone $baseQuery)->update(['delivery_status' => null]);
    }

    private function marketSortKey(?string $marketNumber, ?string $sourceFileName): string
    {
        $marketNumber = trim((string) $marketNumber);

        if (preg_match('/^(\d+)\/(\d{4})$/', $marketNumber, $matches) === 1) {
            $market = str_pad((string) ((int) $matches[1]), 8, '0', STR_PAD_LEFT);
            $year = $matches[2];
            return $year . '-' . $market . '-' . strtolower((string) $sourceFileName);
        }

        return '9999-99999999-' . strtolower((string) $sourceFileName);
    }

    public function storeEquipement(Request $request)
    {
        if (!$this->databaseAvailable()) {
            return redirect()->route('equipements')->with('error', 'Base de données indisponible. Vérifiez la configuration MySQL.');
        }

        $validated = $request->validate([
            'designation' => 'required|string|max:255',
            'inventory_number_current' => 'required|string|max:80',
            'serial_number' => 'nullable|string|max:120',
            'brand_name' => 'nullable|string|max:120',
            'manufacture_date' => 'nullable|date',
            'icon_class' => 'nullable|string|max:80',
            'category_name' => 'nullable|string|max:120',
            'lifecycle_status' => 'required|in:actif,inactif,en_maintenance',
            'description' => 'nullable|string',
            'service_name' => 'nullable|string|max:120',
            'exact_location' => 'nullable|string|max:255',
            'operational_status' => 'required|in:fonctionnel,reserve,panne,hors_service',
            'hospital_code' => 'required|in:HSP,HME,HO',
            'store_name' => 'nullable|string|max:120',
            'company_name' => 'nullable|string|max:180',
            'market_date' => 'nullable|date',
            'verification_status' => 'nullable|in:oui,non',
            'serial_label_comment' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $this->upsertEquipmentFromData($validated);
        });

        return redirect()->route('equipements')->with('success', 'Équipement enregistré avec succès.');
    }

    public function importEquipements(Request $request): JsonResponse
    {
        if (!$this->databaseAvailable()) {
            return response()->json([
                'message' => 'Base de données indisponible. Vérifiez la configuration MySQL.',
            ], 503);
        }

        $validated = $request->validate([
            'rows' => 'required|array|min:1',
            'rows.*' => 'array',
            'replace_existing' => 'nullable|boolean',
        ]);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $deleted = 0;

        $replaceExisting = (bool) ($validated['replace_existing'] ?? false);

        try {
            DB::transaction(function () use ($validated, $replaceExisting, &$created, &$updated, &$skipped, &$deleted) {
                if ($replaceExisting) {
                    $deleted = Equipment::query()->delete();
                }

                foreach ($validated['rows'] as $row) {
                    $normalized = $this->normalizeImportRow($row);

                    if (!$normalized) {
                        $skipped++;
                        continue;
                    }

                    $result = $this->upsertEquipmentFromData($normalized);
                    if ($result === 'created') {
                        $created++;
                    } else {
                        $updated++;
                    }
                }
            });
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Impossible de remplacer les équipements existants. Certains enregistrements sont liés à des interventions/rapports.',
            ], 409);
        }

        // Broadcast real-time KPI update after import
        try {
            $metrics = $this->dashboardMetricsService->build(auth()->user(), 30);
            $this->realtimeMetricsBroadcaster->broadcastDashboardMetrics($metrics);
        } catch (Throwable $e) {}

        return response()->json([
            'message' => 'Import terminé.',
            'deleted' => $deleted,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        ]);
    }

    private function databaseAvailable(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function normalizeImportRow(array $row): ?array
    {
        $inventory = trim((string) ($row['inventory_number_current'] ?? ''));
        $designation = trim((string) ($row['designation'] ?? ''));

        if ($inventory === '' && $designation === '') {
            return null;
        }

        if ($inventory === '') {
            return null;
        }

        return [
            'inventory_number_current' => $inventory,
            'designation' => $designation !== '' ? $designation : 'Équipement sans désignation',
            'serial_number' => $this->nullableTrim($row['serial_number'] ?? null),
            'unit_name' => $this->nullableTrim($row['unit_name'] ?? null),
            'sector_name' => $this->nullableTrim($row['sector_name'] ?? null),
            'sector_description' => $this->nullableTrim($row['sector_description'] ?? null),
            'brand_name' => $this->nullableTrim($row['brand_name'] ?? null),
            'model_name' => $this->nullableTrim($row['model_name'] ?? null),
            'market_label' => $this->nullableTrim($row['market_label'] ?? null),
            'lot_number' => $this->nullableTrim($row['lot_number'] ?? null),
            'manufacture_date' => $this->normalizeDate($row['manufacture_date'] ?? null),
            'icon_class' => $this->nullableTrim($row['icon_class'] ?? null),
            'category_name' => $this->nullableTrim($row['category_name'] ?? null),
            'lifecycle_status' => $this->mapLifecycleStatus($row['lifecycle_status'] ?? null),
            'description' => $this->nullableTrim($row['description'] ?? null),
            'service_name' => $this->nullableTrim($row['service_name'] ?? ($row['unit_name'] ?? null)),
            'exact_location' => $this->nullableTrim($row['exact_location'] ?? ($row['sector_description'] ?? null)),
            'operational_status' => $this->mapOperationalStatus($row['operational_status'] ?? null),
            'hospital_code' => $this->mapHospitalCode($row['hospital_code'] ?? null),
            'store_name' => $this->nullableTrim($row['store_name'] ?? null),
            'company_name' => $this->nullableTrim($row['company_name'] ?? null),
            'market_date' => $this->normalizeDate($row['market_date'] ?? null),
            'verification_status' => $this->mapVerificationStatus($row['verification_status'] ?? null),
            'serial_label_comment' => $this->nullableTrim($row['serial_label_comment'] ?? null),
        ];
    }

    private function upsertEquipmentFromData(array $data): string
    {
        $hospital = Hospital::firstOrCreate(
            ['code' => $data['hospital_code'] ?? 'HSP'],
            ['name' => $this->defaultHospitalName($data['hospital_code'] ?? 'HSP')]
        );

        $company = null;
        if (!empty($data['company_name'])) {
            $company = Company::firstOrCreate(['name' => $data['company_name']]);
        }

        $market = $this->resolveMarketFromLabel($data['market_label'] ?? null);
        if (!$market && !empty($data['market_date']) && $company) {
            $market = Market::firstOrCreate(
                ['reference' => null, 'market_date' => $data['market_date'], 'company_id' => $company->id],
                ['market_date' => $data['market_date'], 'company_id' => $company->id]
            );
        }

        $store = null;
        if (!empty($data['store_name'])) {
            $store = Store::firstOrCreate(
                ['hospital_id' => $hospital->id, 'name' => $data['store_name']],
                ['hospital_id' => $hospital->id, 'name' => $data['store_name']]
            );
        }

        $payload = [
            'serial_number' => $data['serial_number'] ?? null,
            'designation' => $data['designation'] ?? 'Équipement',
            'brand_name' => $data['brand_name'] ?? null,
            'model_name' => $data['model_name'] ?? null,
            'unit_name' => $data['unit_name'] ?? null,
            'sector_name' => $data['sector_name'] ?? null,
            'sector_description' => $data['sector_description'] ?? null,
            'market_label' => $data['market_label'] ?? null,
            'lot_number' => $data['lot_number'] ?? null,
            'manufacture_date' => $data['manufacture_date'] ?? null,
            'icon_class' => $data['icon_class'] ?? null,
            'category_name' => $data['category_name'] ?? null,
            'lifecycle_status' => $data['lifecycle_status'] ?? 'actif',
            'description' => $data['description'] ?? null,
            'serial_label_removed' => !empty($data['serial_label_comment']),
            'serial_label_comment' => $data['serial_label_comment'] ?? null,
            'service_name' => $data['service_name'] ?? null,
            'exact_location' => $data['exact_location'] ?? null,
            'operational_status' => $data['operational_status'] ?? 'fonctionnel',
            'hospital_id' => $hospital->id,
            'store_id' => $store?->id,
            'company_id' => $company?->id ?? $market?->company_id,
            'market_id' => $market?->id,
        ];

        $existing = Equipment::where('inventory_number_current', $data['inventory_number_current'])->first();
        $result = $existing ? 'updated' : 'created';

        $equipment = Equipment::updateOrCreate(
            ['inventory_number_current' => $data['inventory_number_current']],
            $payload
        );

        if (!empty($data['verification_status'])) {
            EquipmentVerification::updateOrCreate(
                ['equipment_id' => $equipment->id],
                [
                    'status' => $data['verification_status'],
                    'verified_at' => now(),
                    'source_market_id' => $market?->id,
                    'note' => $data['serial_label_comment'] ?? null,
                ]
            );
        }

        return $result;
    }

    private function resolveMarketFromLabel(?string $label): ?Market
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

        return $market;
    }

    private function mapHospitalCode($value): string
    {
        $raw = strtoupper(trim((string) $value));

        if ($raw === '' || str_contains($raw, 'HSP')) {
            return 'HSP';
        }

        if (str_contains($raw, 'HME')) {
            return 'HME';
        }

        if (str_contains($raw, 'HO')) {
            return 'HO';
        }

        return 'HSP';
    }

    private function mapOperationalStatus($value): string
    {
        $raw = strtolower(trim((string) $value));

        if (in_array($raw, ['panne', 'en panne'], true)) {
            return 'panne';
        }

        if (in_array($raw, ['hors_service', 'hors service'], true)) {
            return 'hors_service';
        }

        if (in_array($raw, ['reserve', 'réserve', 'fonctionnel avec réserve'], true)) {
            return 'reserve';
        }

        return 'fonctionnel';
    }

    private function mapLifecycleStatus($value): string
    {
        $raw = strtolower(trim((string) $value));

        if (in_array($raw, ['inactif', 'inactive'], true)) {
            return 'inactif';
        }

        if (in_array($raw, ['en maintenance', 'maintenance', 'en_maintenance'], true)) {
            return 'en_maintenance';
        }

        return 'actif';
    }

    private function mapVerificationStatus($value): ?string
    {
        $raw = strtolower(trim((string) $value));

        if (in_array($raw, ['oui', 'yes', 'true', '1'], true)) {
            return 'oui';
        }

        if (in_array($raw, ['non', 'no', 'false', '0'], true)) {
            return 'non';
        }

        return null;
    }

    private function normalizeDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::createFromTimestamp(($value - 25569) * 86400)->toDateString();
            }

            return Carbon::parse((string) $value)->toDateString();
        } catch (Throwable $e) {
            return null;
        }
    }

    private function nullableTrim($value): ?string
    {
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function defaultHospitalName(string $code): string
    {
        return match ($code) {
            'HME' => 'Hôpital Mère-Enfant',
            'HO' => 'Hôpital Oncologie',
            default => 'Hôpital Spécialisé',
        };
    }
}
