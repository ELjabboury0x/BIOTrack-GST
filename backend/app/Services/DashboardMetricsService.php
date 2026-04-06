<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Company;
use App\Models\Equipment;
use App\Models\ExternalCompanyPlanning;
use App\Models\Intervention;
use App\Models\Market;
use App\Models\PreventiveMaintenance;
use App\Models\Service;
use App\Models\SparePart;
use App\Models\User;
use App\Support\ServiceAccess;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class DashboardMetricsService
{
    public function build(
        ?User $user = null,
        int $downtimeFilterDays = 30,
        ?string $designation = null,
        ?int $periodMonth = null,
        ?int $periodYear = null,
        ?int $serviceId = null
    ): array
    {
        $cache = $this->metricsCache();
        $downtimeFilterDays = $this->normalizeDowntimeFilterDays($downtimeFilterDays);
        $designationFilter = $this->normalizeDesignationFilter($designation);
        $periodMonth = $this->normalizePeriodMonth($periodMonth);
        $periodYear = $this->normalizePeriodYear($periodYear);
        $serviceId = $serviceId && $serviceId > 0 ? $serviceId : null;
        $metricsVersion = (int) $cache->get('dashboard_metrics:version', 1);
        $ttl = max(10, (int) config('dashboard_metrics.cache_ttl_seconds', 60));

        $cacheKey = sprintf(
            'dashboard_metrics:v:%d:user:%s:downtime_days:%d:designation:%s:pm:%d:py:%d:service:%s',
            $metricsVersion,
            $user?->id ?? 'guest',
            $downtimeFilterDays,
            $designationFilter !== null ? sha1($designationFilter) : 'all',
            $periodMonth ?? 0,
            $periodYear,
            $serviceId ?? 'all'
        );

        return $cache->remember($cacheKey, now()->addSeconds($ttl), function () use ($user, $downtimeFilterDays, $designationFilter, $periodMonth, $periodYear, $serviceId) {
            return $this->computeMetrics($user, $downtimeFilterDays, $designationFilter, $periodMonth, $periodYear, $serviceId);
        });
    }

    public function invalidateCache(): void
    {
        $cache = $this->metricsCache();
        $currentVersion = (int) $cache->get('dashboard_metrics:version', 1);

        try {
            $incremented = $cache->increment('dashboard_metrics:version');

            if (!is_numeric($incremented) || (int) $incremented <= $currentVersion) {
                $cache->forever('dashboard_metrics:version', $currentVersion + 1);
            }
        } catch (Throwable $e) {
            $cache->forever('dashboard_metrics:version', $currentVersion + 1);
        }
    }

    private function metricsCache()
    {
        $store = (string) config('dashboard_metrics.cache_store', config('cache.default'));

        try {
            return Cache::store($store);
        } catch (Throwable $e) {
            return Cache::store(config('cache.default'));
        }
    }

    private function computeMetrics(
        ?User $user,
        int $downtimeFilterDays,
        ?string $designationFilter = null,
        ?int $periodMonth = null,
        ?int $periodYear = null,
        ?int $serviceId = null
    ): array
    {

        if (!$this->databaseAvailable()) {
            return [
                'kpi' => [
                    'total_equipements' => 0,
                    'interventions_en_cours' => 0,
                    'interventions_en_retard' => 0,
                    'disponibilite' => 0,
                    'temps_arret_moyen_heures' => 0,
                    'planning_societes_a_venir' => 0,
                    'planning_prochaine_societe' => 'Aucune société planifiée',
                    'planning_prochaine_date_label' => '—',
                    'reclamations_ouvertes' => 0,
                    'maintenances_preventives_a_venir' => 0,
                    'pieces_stock_faible' => 0,
                    'mttr_heures' => 0,
                    'mtbf_heures' => 0,
                    'mtbf_preventif_heures' => 0,
                    'mtbf_curatif_heures' => 0,
                    'disponibilite_d' => 0,
                ],
                'charts' => [
                    'interventions' => ['labels' => [], 'preventive' => [], 'curative' => []],
                    'maintenance_types' => ['labels' => [], 'data' => []],
                    'equipments_added' => ['labels' => [], 'data' => []],
                    'downtime' => ['labels' => [], 'avg_hours' => []],
                    'reliability' => ['labels' => [], 'mttr' => [], 'mtbf' => [], 'mtbf_preventif' => [], 'mtbf_curatif' => []],
                    'reliability_by_designation' => [
                        'labels' => [],
                        'mttr' => [],
                        'mtbf' => [],
                        'disponibilite' => [],
                        'designations' => [],
                        'selected_designation' => '',
                    ],
                    'external_companies' => [
                        'labels' => [],
                        'score' => [],
                        'respect_planning' => [],
                        'avg_delay_days' => [],
                        'mttr' => [],
                        'reintervention_rate' => [],
                        'availability' => [],
                        'interventions_total' => [],
                        'top5' => [],
                        'top_fastest' => [],
                        'top_failures' => [],
                        'filters' => [
                            'months' => [],
                            'years' => [],
                            'services' => [],
                            'selected_month' => null,
                            'selected_year' => null,
                            'selected_service_id' => null,
                        ],
                    ],
                ],
                'hasData' => false,
            ];
        }

        $equipmentQuery = Equipment::query();
        ServiceAccess::applyEquipmentScope($equipmentQuery, $user);

        $interventionQuery = Intervention::query();
        ServiceAccess::applyInterventionScope($interventionQuery, $user);

        $complaintQuery = Complaint::query();
        ServiceAccess::applyComplaintScope($complaintQuery, $user);

        $totalEquipments = (clone $equipmentQuery)->count();
        $functionalEquipments = (clone $equipmentQuery)->where('operational_status', 'fonctionnel')->count();
        $equipementsPanneTotal = (clone $equipmentQuery)->where('operational_status', 'panne')->count();
        $equipementsPanneCritique = (clone $equipmentQuery)
            ->where('operational_status', 'panne')
            ->where(function (Builder $inner): void {
                $inner
                    ->whereRaw('LOWER(COALESCE(lifecycle_status, "")) LIKE ?', ['%crit%'])
                    ->orWhereRaw('LOWER(COALESCE(category_name, "")) LIKE ?', ['%crit%']);
            })
            ->count();
        $interventionsEnCours = (clone $interventionQuery)->whereIn('status', ['en_attente', 'en_cours'])->count();
        $lateThresholdDays = 2;
        $interventionsEnRetard = (clone $interventionQuery)->where('status', 'en_attente')
            ->whereDate('date_start', '<', now()->subDays($lateThresholdDays)->toDateString())
            ->count();
        $reclamationsOuvertes = (clone $complaintQuery)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        $availabilityRate = $totalEquipments > 0
            ? (int) round(($functionalEquipments / $totalEquipments) * 100)
            : 0;

        $kpi = [
            'total_equipements' => $totalEquipments,
            'interventions_en_cours' => $interventionsEnCours,
            'interventions_en_retard' => $interventionsEnRetard,
            'equipements_panne_total' => $equipementsPanneTotal,
            'equipements_panne_critique' => $equipementsPanneCritique,
            'disponibilite' => $availabilityRate,
            'reclamations_ouvertes' => $reclamationsOuvertes,
            'maintenances_preventives_a_venir' => 0,
            'pieces_stock_faible' => 0,
            'mttr_heures' => 0,
            'mtbf_heures' => 0,
            'mtbf_preventif_heures' => 0,
            'mtbf_curatif_heures' => 0,
            'disponibilite_d' => 0,
        ];

        $months = collect(range(5, 0))->map(function ($offset) {
            return Carbon::now()->subMonths($offset);
        });

        $monthKeys = $months->map(fn ($date) => $date->format('Y-m'));
        $monthLabels = $months->map(fn ($date) => ucfirst($date->translatedFormat('M')))->values()->all();

        $preventiveByMonthQuery = Intervention::query()
            ->where('type', 'Préventive')
            ->selectRaw("DATE_FORMAT(COALESCE(date_start, created_at), '%Y-%m') as ym, COUNT(*) as total")
            ->groupBy('ym');
        ServiceAccess::applyInterventionScope($preventiveByMonthQuery, $user);
        $preventiveByMonth = $preventiveByMonthQuery->pluck('total', 'ym');

        $curativeByMonthQuery = Intervention::query()
            ->whereIn('type', ['Curative', 'Urgente'])
            ->selectRaw("DATE_FORMAT(COALESCE(date_start, created_at), '%Y-%m') as ym, COUNT(*) as total")
            ->groupBy('ym');
        ServiceAccess::applyInterventionScope($curativeByMonthQuery, $user);
        $curativeByMonth = $curativeByMonthQuery->pluck('total', 'ym');

        $equipmentsAddedByMonthQuery = Equipment::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COUNT(*) as total")
            ->groupBy('ym');
        ServiceAccess::applyEquipmentScope($equipmentsAddedByMonthQuery, $user);
        $equipmentsAddedByMonth = $equipmentsAddedByMonthQuery->pluck('total', 'ym');

        $maintenanceTypeCountsQuery = Equipment::query()
            ->selectRaw('operational_status, COUNT(*) as total')
            ->groupBy('operational_status');
        ServiceAccess::applyEquipmentScope($maintenanceTypeCountsQuery, $user);
        $maintenanceTypeCounts = $maintenanceTypeCountsQuery->pluck('total', 'operational_status');

        $downtimeFromDate = Carbon::now()->subDays($downtimeFilterDays)->startOfDay();
        $downtimeMonths = collect();
        $cursor = $downtimeFromDate->copy()->startOfMonth();
        $lastMonth = Carbon::now()->startOfMonth();
        while ($cursor->lessThanOrEqualTo($lastMonth)) {
            $downtimeMonths->push($cursor->copy());
            $cursor->addMonth();
        }

        $downtimeMonthKeys = $downtimeMonths->map(fn (Carbon $date) => $date->format('Y-m'));
        $downtimeMonthLabels = $downtimeMonths->map(fn (Carbon $date) => ucfirst($date->translatedFormat('M y')))->values()->all();

        $closureExpr = "COALESCE(interventions.closed_at, interventions.updated_at, interventions.created_at)";
        $complaintCreatedExpr = "(
            SELECT MAX(c.created_at)
            FROM complaints c
            WHERE c.equipment_id = interventions.equipment_id
              AND c.created_at <= {$closureExpr}
        )";

        $downtimeByMonthQuery = Intervention::query()
            ->where('status', 'termine')
            ->whereDate('date_end', '>=', $downtimeFromDate->toDateString())
            ->whereRaw("{$complaintCreatedExpr} IS NOT NULL")
            ->selectRaw("DATE_FORMAT(COALESCE(interventions.date_end, interventions.updated_at), '%Y-%m') as ym")
            ->selectRaw("AVG(TIMESTAMPDIFF(HOUR, {$complaintCreatedExpr}, {$closureExpr})) as avg_hours")
            ->groupBy('ym');
        ServiceAccess::applyInterventionScope($downtimeByMonthQuery, $user);
        $downtimeByMonth = $downtimeByMonthQuery->pluck('avg_hours', 'ym');

        $downtimeGlobalAvgQuery = Intervention::query()
            ->where('status', 'termine')
            ->whereDate('date_end', '>=', $downtimeFromDate->toDateString())
            ->whereRaw("{$complaintCreatedExpr} IS NOT NULL")
            ->selectRaw("AVG(TIMESTAMPDIFF(HOUR, {$complaintCreatedExpr}, {$closureExpr})) as avg_hours");
        ServiceAccess::applyInterventionScope($downtimeGlobalAvgQuery, $user);
        $downtimeGlobalAvg = (float) ($downtimeGlobalAvgQuery->value('avg_hours') ?? 0);

        $reliabilityFromDate = $months->first()->copy()->startOfMonth();

        $mttrByMonthQuery = Intervention::query()
            ->where('status', 'termine')
            ->whereDate('date_end', '>=', $reliabilityFromDate->toDateString())
            ->whereRaw("{$complaintCreatedExpr} IS NOT NULL")
            ->selectRaw("DATE_FORMAT(COALESCE(interventions.date_end, interventions.updated_at), '%Y-%m') as ym")
            ->selectRaw("AVG(TIMESTAMPDIFF(HOUR, {$complaintCreatedExpr}, {$closureExpr})) as avg_hours")
            ->groupBy('ym');
        ServiceAccess::applyInterventionScope($mttrByMonthQuery, $user);
        $mttrByMonth = $mttrByMonthQuery->pluck('avg_hours', 'ym');

        $mtbfPreventifByMonth = $this->calculateMtbfPreventifByMonth(
            $user,
            $monthKeys->values()->all(),
            $reliabilityFromDate
        );

        $mtbfCuratifByMonth = $this->calculateMtbfCuratifByMonth(
            $complaintQuery,
            $monthKeys->values()->all(),
            $reliabilityFromDate
        );

        $kpi['temps_arret_moyen_heures'] = round($downtimeGlobalAvg, 1);
        $kpi['mttr_heures'] = round($downtimeGlobalAvg, 1);

        $mtbfPreventifHours = $this->calculateMtbfPreventifHours($user);
        $mtbfCuratifHours = $this->calculateMtbfCuratifHours($complaintQuery);

        $kpi['mtbf_preventif_heures'] = round($mtbfPreventifHours, 1);
        $kpi['mtbf_curatif_heures'] = round($mtbfCuratifHours, 1);
        $kpi['mtbf_heures'] = $kpi['mtbf_preventif_heures'];

        $kpi['disponibilite_d'] = ($kpi['mtbf_heures'] + $kpi['mttr_heures']) > 0
            ? round(($kpi['mtbf_heures'] / ($kpi['mtbf_heures'] + $kpi['mttr_heures'])) * 100, 1)
            : 0;

        if (Schema::hasTable('preventive_maintenances')) {
            $kpi['maintenances_preventives_a_venir'] = PreventiveMaintenance::query()
                ->where('status', 'actif')
                ->whereDate('next_maintenance_date', '>=', now()->toDateString())
                ->count();
        }

        if (Schema::hasTable('spare_parts')) {
            $kpi['pieces_stock_faible'] = SparePart::query()
                ->where('quantity', '<=', 5)
                ->count();
        }

        if (Schema::hasTable('external_company_plannings')) {
            $today = now()->toDateString();
            $windowEnd = now()->addDays(14)->toDateString();

            $upcomingPlanningQuery = ExternalCompanyPlanning::query()
                ->with('company')
                ->whereDate('planned_date', '<=', $windowEnd)
                ->where(function ($query) use ($today) {
                    $query->where(function ($inner) use ($today) {
                        $inner->whereNull('planned_date_end')
                            ->whereDate('planned_date', '>=', $today);
                    })->orWhereDate('planned_date_end', '>=', $today);
                })
                ->whereIn('status', ['en_attente', 'en_cours']);

            $upcomingCompaniesCount = (clone $upcomingPlanningQuery)
                ->whereNotNull('company_id')
                ->distinct('company_id')
                ->count('company_id');
            $nextCompanyVisit = (clone $upcomingPlanningQuery)
                ->orderBy('planned_date')
                ->orderBy('id')
                ->first();

            $kpi['planning_societes_a_venir'] = $upcomingCompaniesCount;
            $kpi['planning_prochaine_societe'] = $nextCompanyVisit?->company?->name ?? 'Aucune société planifiée';
            $kpi['planning_prochaine_date_label'] = $nextCompanyVisit?->planned_date
                ? $nextCompanyVisit->planned_date->format('d/m/Y')
                : '—';
        } else {
            $upcomingMarketsQuery = Market::query()
                ->with('company')
                ->whereDate('market_date', '>=', now()->toDateString());

            $upcomingCompaniesCount = (clone $upcomingMarketsQuery)->count();
            $nextCompanyVisit = (clone $upcomingMarketsQuery)
                ->orderBy('market_date')
                ->orderBy('id')
                ->first();

            $kpi['planning_societes_a_venir'] = $upcomingCompaniesCount;
            $kpi['planning_prochaine_societe'] = $nextCompanyVisit?->company?->name ?? 'Aucune société planifiée';
            $kpi['planning_prochaine_date_label'] = $nextCompanyVisit?->market_date
                ? $nextCompanyVisit->market_date->format('d/m/Y')
                : '—';
        }

        $reliabilityByDesignation = $this->calculateReliabilityByDesignation($user, $designationFilter, $downtimeFilterDays);
        $externalCompanies = $this->calculateExternalCompanyKpis($user, $periodMonth, $periodYear, $serviceId);

        $charts = [
            'interventions' => [
                'labels' => $monthLabels,
                'preventive' => $monthKeys->map(fn ($key) => (int) ($preventiveByMonth[$key] ?? 0))->values()->all(),
                'curative' => $monthKeys->map(fn ($key) => (int) ($curativeByMonth[$key] ?? 0))->values()->all(),
            ],
            'maintenance_types' => [
                'labels' => ['Fonctionnel', 'Réserve', 'Panne', 'Hors service'],
                'data' => [
                    (int) ($maintenanceTypeCounts['fonctionnel'] ?? 0),
                    (int) ($maintenanceTypeCounts['reserve'] ?? 0),
                    (int) ($maintenanceTypeCounts['panne'] ?? 0),
                    (int) ($maintenanceTypeCounts['hors_service'] ?? 0),
                ],
            ],
            'equipments_added' => [
                'labels' => $monthLabels,
                'data' => $monthKeys->map(fn ($key) => (int) ($equipmentsAddedByMonth[$key] ?? 0))->values()->all(),
            ],
            'downtime' => [
                'labels' => $downtimeMonthLabels,
                'avg_hours' => $downtimeMonthKeys
                    ->map(fn ($key) => round((float) ($downtimeByMonth[$key] ?? 0), 1))
                    ->values()
                    ->all(),
            ],
            'reliability' => [
                'labels' => $monthLabels,
                'mttr' => $monthKeys
                    ->map(fn ($key) => round((float) ($mttrByMonth[$key] ?? 0), 1))
                    ->values()
                    ->all(),
                'mtbf' => $monthKeys
                    ->map(fn ($key) => round((float) ($mtbfPreventifByMonth[$key] ?? 0), 1))
                    ->values()
                    ->all(),
                'mtbf_preventif' => $monthKeys
                    ->map(fn ($key) => round((float) ($mtbfPreventifByMonth[$key] ?? 0), 1))
                    ->values()
                    ->all(),
                'mtbf_curatif' => $monthKeys
                    ->map(fn ($key) => round((float) ($mtbfCuratifByMonth[$key] ?? 0), 1))
                    ->values()
                    ->all(),
            ],
                    'reliability_by_designation' => $reliabilityByDesignation,
                    'external_companies' => $externalCompanies,
        ];

        $hasKpiData = (
            (int) ($kpi['total_equipements'] ?? 0) > 0 ||
            (int) ($kpi['interventions_en_cours'] ?? 0) > 0 ||
            (int) ($kpi['interventions_en_retard'] ?? 0) > 0 ||
            (int) ($kpi['reclamations_ouvertes'] ?? 0) > 0 ||
            (int) ($kpi['planning_societes_a_venir'] ?? 0) > 0 ||
            (int) ($kpi['maintenances_preventives_a_venir'] ?? 0) > 0 ||
            (int) ($kpi['pieces_stock_faible'] ?? 0) > 0 ||
            (float) ($kpi['mttr_heures'] ?? 0) > 0 ||
            (float) ($kpi['mtbf_heures'] ?? 0) > 0 ||
            (float) ($kpi['mtbf_curatif_heures'] ?? 0) > 0 ||
            (float) ($kpi['disponibilite_d'] ?? 0) > 0
        );

        return [
            'kpi' => $kpi,
            'charts' => $charts,
            'hasData' => $hasKpiData || (clone $interventionQuery)->count() > 0 || (clone $complaintQuery)->count() > 0,
        ];
    }

    private function normalizeDowntimeFilterDays(int $days): int
    {
        $allowed = [7, 30, 90, 180, 365];

        return in_array($days, $allowed, true) ? $days : 30;
    }

    private function normalizeDesignationFilter(?string $designation): ?string
    {
        $value = trim((string) $designation);

        return $value !== '' ? $value : null;
    }

    private function normalizePeriodMonth(?int $month): ?int
    {
        if (!$month) {
            return null;
        }

        return ($month >= 1 && $month <= 12) ? $month : null;
    }

    private function normalizePeriodYear(?int $year): int
    {
        $current = (int) now()->year;
        if (!$year) {
            return $current;
        }

        return ($year >= 2020 && $year <= ($current + 1)) ? $year : $current;
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

    private function calculateMtbfPreventifHours(?User $user): float
    {
        if (!Schema::hasTable('preventive_maintenances')) {
            return 0.0;
        }

        $query = PreventiveMaintenance::query()
            ->select(['last_maintenance_date', 'next_maintenance_date', 'periodicity', 'created_at'])
            ->whereNotNull('equipment_id')
            ->whereNotNull('next_maintenance_date')
            ->where(function ($builder) {
                $builder->whereNotNull('last_maintenance_date')
                    ->orWhereNotNull('periodicity')
                    ->orWhereNotNull('created_at');
            });

        ServiceAccess::applyPreventiveScope($query, $user);

        $rows = $query
            ->get();

        if ($rows->isEmpty()) {
            return 0.0;
        }

        $intervals = [];

        foreach ($rows as $row) {
            if (!$row->next_maintenance_date) {
                continue;
            }

            $hours = $this->resolvePreventiveIntervalHours(
                $row->last_maintenance_date,
                $row->next_maintenance_date,
                $row->periodicity,
                $row->created_at
            );

            if ($hours > 0) {
                $intervals[] = $hours;
            }
        }

        if (empty($intervals)) {
            return 0.0;
        }

        return array_sum($intervals) / count($intervals);
    }

    private function calculateMtbfPreventifByMonth(?User $user, array $monthKeys, Carbon $fromDate): array
    {
        if (!Schema::hasTable('preventive_maintenances')) {
            return [];
        }

        $targetMonths = array_fill_keys($monthKeys, true);
        $monthGaps = [];

        $query = PreventiveMaintenance::query()
            ->select(['last_maintenance_date', 'next_maintenance_date', 'periodicity', 'created_at'])
            ->whereNotNull('equipment_id')
            ->whereNotNull('next_maintenance_date')
            ->where(function ($builder) {
                $builder->whereNotNull('last_maintenance_date')
                    ->orWhereNotNull('periodicity')
                    ->orWhereNotNull('created_at');
            })
            ->where(function ($builder) use ($fromDate) {
                $from = $fromDate->toDateString();
                $builder->whereDate('last_maintenance_date', '>=', $from)
                    ->orWhereDate('next_maintenance_date', '>=', $from)
                    ->orWhereDate('created_at', '>=', $from);
            });

        ServiceAccess::applyPreventiveScope($query, $user);

        $rows = $query
            ->get();

        foreach ($rows as $row) {
            if (!$row->next_maintenance_date) {
                continue;
            }

            $nextDate = Carbon::parse($row->next_maintenance_date);
            $hours = $this->resolvePreventiveIntervalHours(
                $row->last_maintenance_date,
                $row->next_maintenance_date,
                $row->periodicity,
                $row->created_at
            );
            $monthKey = $this->resolvePreventiveReferenceMonth(
                $row->last_maintenance_date,
                $row->created_at,
                $row->next_maintenance_date
            );

            if ($hours > 0 && isset($targetMonths[$monthKey])) {
                $monthGaps[$monthKey][] = $hours;
            }
        }

        $averages = [];
        foreach ($monthGaps as $monthKey => $gaps) {
            if (!empty($gaps)) {
                $averages[$monthKey] = array_sum($gaps) / count($gaps);
            }
        }

        return $averages;
    }

    private function resolvePreventiveIntervalHours($lastMaintenanceDate, $nextMaintenanceDate, $periodicity, $createdAt = null): float
    {
        if ($nextMaintenanceDate && $lastMaintenanceDate) {
            $lastDate = Carbon::parse($lastMaintenanceDate);
            $nextDate = Carbon::parse($nextMaintenanceDate);

            if ($nextDate->greaterThan($lastDate)) {
                return (float) $lastDate->diffInHours($nextDate);
            }
        }

        if ($nextMaintenanceDate && $createdAt) {
            $createdDate = Carbon::parse($createdAt);
            $nextDate = Carbon::parse($nextMaintenanceDate);

            if ($nextDate->greaterThan($createdDate)) {
                return (float) $createdDate->diffInHours($nextDate);
            }
        }

        $periodicityKey = trim((string) $periodicity);
        $daysByPeriodicity = [
            'Mensuel' => 30,
            'Trimestriel' => 90,
            'Semestriel' => 180,
            'Annuel' => 365,
        ];

        if (isset($daysByPeriodicity[$periodicityKey])) {
            return (float) ($daysByPeriodicity[$periodicityKey] * 24);
        }

        return 0.0;
    }

    private function resolvePreventiveReferenceMonth($lastMaintenanceDate, $createdAt, $nextMaintenanceDate): ?string
    {
        if ($lastMaintenanceDate) {
            return Carbon::parse($lastMaintenanceDate)->format('Y-m');
        }

        if ($createdAt) {
            return Carbon::parse($createdAt)->format('Y-m');
        }

        if ($nextMaintenanceDate) {
            return Carbon::parse($nextMaintenanceDate)->format('Y-m');
        }

        return null;
    }

    private function calculateMtbfCuratifHours($complaintBaseQuery): float
    {
        if (!Schema::hasTable('complaints')) {
            return 0.0;
        }

        $complaints = (clone $complaintBaseQuery)
            ->select(['equipment_id', 'created_at'])
            ->whereNotNull('equipment_id')
            ->orderBy('equipment_id')
            ->orderBy('created_at')
            ->get();

        if ($complaints->count() < 2) {
            return 0.0;
        }

        $previousByEquipment = [];
        $gaps = [];

        foreach ($complaints as $complaint) {
            $equipmentId = (int) $complaint->equipment_id;
            if (!$complaint->created_at) {
                continue;
            }

            $current = Carbon::parse($complaint->created_at);

            if (isset($previousByEquipment[$equipmentId])) {
                $hours = $previousByEquipment[$equipmentId]->diffInHours($current);
                if ($hours > 0) {
                    $gaps[] = $hours;
                }
            }

            $previousByEquipment[$equipmentId] = $current;
        }

        if (empty($gaps)) {
            return 0.0;
        }

        return array_sum($gaps) / count($gaps);
    }

    private function calculateMtbfCuratifByMonth($complaintBaseQuery, array $monthKeys, Carbon $fromDate): array
    {
        if (!Schema::hasTable('complaints')) {
            return [];
        }

        $targetMonths = array_fill_keys($monthKeys, true);
        $monthGaps = [];

        $complaints = (clone $complaintBaseQuery)
            ->select(['equipment_id', 'created_at'])
            ->whereNotNull('equipment_id')
            ->whereDate('created_at', '>=', $fromDate->copy()->subMonth()->toDateString())
            ->orderBy('equipment_id')
            ->orderBy('created_at')
            ->get();

        $previousByEquipment = [];

        foreach ($complaints as $complaint) {
            if (!$complaint->created_at) {
                continue;
            }

            $equipmentId = (int) $complaint->equipment_id;
            $currentDate = Carbon::parse($complaint->created_at);

            if (isset($previousByEquipment[$equipmentId])) {
                $hours = $previousByEquipment[$equipmentId]->diffInHours($currentDate);
                $monthKey = $currentDate->format('Y-m');

                if ($hours > 0 && isset($targetMonths[$monthKey])) {
                    $monthGaps[$monthKey][] = $hours;
                }
            }

            $previousByEquipment[$equipmentId] = $currentDate;
        }

        $averages = [];
        foreach ($monthGaps as $monthKey => $gaps) {
            if (!empty($gaps)) {
                $averages[$monthKey] = array_sum($gaps) / count($gaps);
            }
        }

        return $averages;
    }

    private function calculateReliabilityByDesignation(?User $user, ?string $designationFilter, int $periodDays): array
    {
        $periodDays = max(1, $periodDays);
        $fromDate = now()->subDays($periodDays)->startOfDay();
        $periodHours = $periodDays * 24;

        $designationsQuery = Equipment::query()
            ->select('designation')
            ->whereNotNull('designation')
            ->whereRaw("TRIM(designation) <> ''");
        ServiceAccess::applyEquipmentScope($designationsQuery, $user);

        $availableDesignations = $designationsQuery
            ->distinct()
            ->orderBy('designation')
            ->pluck('designation')
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values();

        $selectedDesignation = null;
        if ($designationFilter !== null) {
            $matched = $availableDesignations->first(fn ($item) => mb_strtolower($item) === mb_strtolower($designationFilter));
            $selectedDesignation = $matched ?: null;
        }

        $equipmentByDesignationQuery = Equipment::query()
            ->selectRaw('designation, COUNT(*) as equipment_count')
            ->whereNotNull('designation')
            ->whereRaw("TRIM(designation) <> ''");
        ServiceAccess::applyEquipmentScope($equipmentByDesignationQuery, $user);
        if ($selectedDesignation !== null) {
            $equipmentByDesignationQuery->where('designation', $selectedDesignation);
        }
        $equipmentByDesignation = $equipmentByDesignationQuery
            ->groupBy('designation')
            ->pluck('equipment_count', 'designation');

        $failureByDesignationQuery = Intervention::query()
            ->join('equipments as e', 'e.id', '=', 'interventions.equipment_id')
            ->whereNotNull('e.designation')
            ->whereRaw("TRIM(e.designation) <> ''")
            ->whereIn('interventions.type', ['Curative', 'Urgente'])
            ->where('interventions.status', 'termine')
            ->whereDate(DB::raw('COALESCE(interventions.date_end, interventions.updated_at, interventions.created_at)'), '>=', $fromDate->toDateString())
            ->selectRaw('e.designation as designation, COUNT(*) as failure_count')
            ->groupBy('e.designation');
        ServiceAccess::applyInterventionScope($failureByDesignationQuery, $user);
        if ($selectedDesignation !== null) {
            $failureByDesignationQuery->where('e.designation', $selectedDesignation);
        }
        $failureByDesignation = $failureByDesignationQuery->pluck('failure_count', 'designation');

        $closureExpr = "COALESCE(interventions.closed_at, interventions.updated_at, interventions.created_at)";
        $complaintCreatedExpr = "(
            SELECT MAX(c.created_at)
            FROM complaints c
            WHERE c.equipment_id = interventions.equipment_id
              AND c.created_at <= {$closureExpr}
        )";

        $mttrByDesignationQuery = Intervention::query()
            ->join('equipments as e', 'e.id', '=', 'interventions.equipment_id')
            ->whereNotNull('e.designation')
            ->whereRaw("TRIM(e.designation) <> ''")
            ->where('interventions.status', 'termine')
            ->whereDate(DB::raw('COALESCE(interventions.date_end, interventions.updated_at, interventions.created_at)'), '>=', $fromDate->toDateString())
            ->whereRaw("{$complaintCreatedExpr} IS NOT NULL")
            ->selectRaw('e.designation as designation')
            ->selectRaw("AVG(TIMESTAMPDIFF(HOUR, {$complaintCreatedExpr}, {$closureExpr})) as mttr_hours")
            ->groupBy('e.designation');
        ServiceAccess::applyInterventionScope($mttrByDesignationQuery, $user);
        if ($selectedDesignation !== null) {
            $mttrByDesignationQuery->where('e.designation', $selectedDesignation);
        }
        $mttrByDesignation = $mttrByDesignationQuery->pluck('mttr_hours', 'designation');

        $designationKeys = collect(array_unique(array_merge(
            array_keys($equipmentByDesignation->toArray()),
            array_keys($failureByDesignation->toArray()),
            array_keys($mttrByDesignation->toArray())
        )))->sort()->values();

        $labels = [];
        $mttrSeries = [];
        $mtbfSeries = [];
        $availabilitySeries = [];

        foreach ($designationKeys as $designation) {
            $equipmentCount = (int) ($equipmentByDesignation[$designation] ?? 0);
            $failureCount = (int) ($failureByDesignation[$designation] ?? 0);
            $mttr = round((float) ($mttrByDesignation[$designation] ?? 0), 1);

            $mtbf = $equipmentCount > 0
                ? ($failureCount > 0
                    ? (($equipmentCount * $periodHours) / $failureCount)
                    : ($equipmentCount * $periodHours))
                : 0.0;
            $mtbf = round((float) $mtbf, 1);

            $availability = ($mtbf + $mttr) > 0
                ? round(($mtbf / ($mtbf + $mttr)) * 100, 1)
                : 0.0;

            $labels[] = (string) $designation;
            $mttrSeries[] = $mttr;
            $mtbfSeries[] = $mtbf;
            $availabilitySeries[] = $availability;
        }

        return [
            'labels' => $labels,
            'mttr' => $mttrSeries,
            'mtbf' => $mtbfSeries,
            'disponibilite' => $availabilitySeries,
            'designations' => $availableDesignations->all(),
            'selected_designation' => $selectedDesignation ?? '',
        ];
    }

    private function calculateExternalCompanyKpis(?User $user, ?int $periodMonth, ?int $periodYear, ?int $requestedServiceId): array
    {
        $year = $this->normalizePeriodYear($periodYear);
        $month = $this->normalizePeriodMonth($periodMonth);

        $periodStart = $month
            ? Carbon::create($year, $month, 1)->startOfMonth()
            : Carbon::create($year, 1, 1)->startOfYear();
        $periodEnd = $month
            ? Carbon::create($year, $month, 1)->endOfMonth()
            : Carbon::create($year, 12, 31)->endOfYear();

        $serviceScopeIds = null;
        if ($user) {
            if (in_array($user->role, ['major', 'technicien', 'technician'], true)) {
                $serviceScopeIds = $user->unitScopedServiceIds();
            }
        }

        $selectedServiceId = null;
        if ($requestedServiceId && $requestedServiceId > 0) {
            if (is_array($serviceScopeIds) && !in_array((int) $requestedServiceId, $serviceScopeIds, true)) {
                return $this->emptyExternalCompaniesPayload($month, $year, $requestedServiceId, $serviceScopeIds);
            }
            $selectedServiceId = (int) $requestedServiceId;
        }

        $servicesQuery = Service::query()->excludeHiddenForUi()->select('id', 'name')->orderBy('name');
        if (is_array($serviceScopeIds)) {
            if (empty($serviceScopeIds)) {
                $servicesQuery->whereRaw('1=0');
            } else {
                $servicesQuery->whereIn('id', $serviceScopeIds);
            }
        }
        $services = $servicesQuery->get()->map(fn (Service $service) => [
            'id' => (int) $service->id,
            'name' => (string) $service->name,
        ])->values()->all();

        $equipmentsQuery = Equipment::query()
            ->select(['id', 'company_id', 'service_id'])
            ->whereNotNull('company_id');
        if (is_array($serviceScopeIds)) {
            if (empty($serviceScopeIds)) {
                $equipmentsQuery->whereRaw('1=0');
            } else {
                $equipmentsQuery->whereIn('service_id', $serviceScopeIds);
            }
        }
        if ($selectedServiceId) {
            $equipmentsQuery->where('service_id', $selectedServiceId);
        }
        $equipments = $equipmentsQuery->get();

        $serviceNamesById = collect($services)
            ->mapWithKeys(fn (array $service) => [(int) ($service['id'] ?? 0) => (string) ($service['name'] ?? '')]);

        $scopedServiceNames = is_array($serviceScopeIds)
            ? collect($serviceScopeIds)->map(fn ($id) => (string) ($serviceNamesById[(int) $id] ?? ''))->filter()->values()->all()
            : [];
        $selectedServiceName = $selectedServiceId
            ? (string) ($serviceNamesById[$selectedServiceId] ?? '')
            : '';

        $companies = Company::query()->get(['id', 'name']);
        $companyNames = $companies->pluck('name', 'id');
        $companyIdByNormalizedName = $companies
            ->mapWithKeys(fn (Company $company) => [$this->normalizeNameToken((string) $company->name) => (int) $company->id]);

        $correctiveEvents = collect();
        if (Schema::hasTable('bilan_maintenance_correctives')) {
            $correctiveRows = DB::table('bilan_maintenance_correctives')
                ->orderByDesc('id')
                ->get(['id', 'company_name', 'service_names', 'intervention_date_text', 'created_at']);

            if (is_array($serviceScopeIds)) {
                if (empty($serviceScopeIds)) {
                    $correctiveRows = collect();
                } elseif (!empty($scopedServiceNames)) {
                    $correctiveRows = $correctiveRows->filter(function ($row) use ($scopedServiceNames) {
                        $serviceText = $this->normalizeNameToken((string) ($row->service_names ?? ''));
                        if ($serviceText === '') {
                            return false;
                        }

                        foreach ($scopedServiceNames as $name) {
                            $token = $this->normalizeNameToken((string) $name);
                            if ($token !== '' && str_contains($serviceText, $token)) {
                                return true;
                            }
                        }

                        return false;
                    })->values();
                }
            }

            if ($selectedServiceName !== '') {
                $selectedServiceToken = $this->normalizeNameToken($selectedServiceName);
                $correctiveRows = $correctiveRows->filter(function ($row) use ($selectedServiceToken) {
                    $serviceText = $this->normalizeNameToken((string) ($row->service_names ?? ''));

                    return $selectedServiceToken !== '' && str_contains($serviceText, $selectedServiceToken);
                })->values();
            }

            $correctiveEvents = $correctiveRows
                ->filter(fn ($row) => $this->correctiveDateMatchesPeriod((string) ($row->intervention_date_text ?? ''), (string) ($row->created_at ?? ''), $month, $year))
                ->map(function ($row) use ($companyIdByNormalizedName, $companyNames) {
                    $companyId = $this->resolveCompanyIdFromRawName((string) ($row->company_name ?? ''), $companyIdByNormalizedName, $companyNames);
                    if ($companyId <= 0) {
                        return null;
                    }

                    $eventDate = $this->parseCorrectiveDate((string) ($row->intervention_date_text ?? ''), (string) ($row->created_at ?? ''));

                    return [
                        'company_id' => $companyId,
                        'equipment_id' => -1 * max(1, (int) ($row->id ?? 0)),
                        'type' => 'Curative',
                        'status' => 'termine',
                        'start_at' => $eventDate,
                        'end_at' => $eventDate,
                    ];
                })
                ->filter()
                ->values();
        }

        $equipmentIds = $equipments->pluck('id')->map(fn ($id) => (int) $id)->all();
        $companyIds = $equipments->pluck('company_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        $companyIds = array_values(array_unique(array_merge(
            $companyIds,
            $correctiveEvents->pluck('company_id')->map(fn ($id) => (int) $id)->all()
        )));

        if ($equipments->isEmpty() && $correctiveEvents->isEmpty()) {
            return $this->emptyExternalCompaniesPayload($month, $year, $selectedServiceId, $serviceScopeIds, $services);
        }

        $planningByCompany = ExternalCompanyPlanning::query()
            ->whereIn('company_id', $companyIds)
            ->whereDate('planned_date', '>=', $periodStart->toDateString())
            ->whereDate('planned_date', '<=', $periodEnd->toDateString())
            ->orderBy('planned_date')
            ->get(['id', 'company_id', 'planned_date', 'planned_date_end', 'status'])
            ->groupBy('company_id');

        $interventions = collect();
        $maintenanceReports = collect();
        if (!empty($equipmentIds)) {
            $interventions = Intervention::query()
                ->whereIn('equipment_id', $equipmentIds)
                ->where(function ($query) use ($periodStart, $periodEnd) {
                    $query->whereBetween('date_start', [$periodStart->toDateString(), $periodEnd->toDateString()])
                        ->orWhereBetween('created_at', [$periodStart->toDateTimeString(), $periodEnd->toDateTimeString()]);
                })
                ->orderBy('equipment_id')
                ->orderBy('date_start')
                ->get(['id', 'equipment_id', 'type', 'status', 'date_start', 'date_end', 'created_at', 'updated_at']);

            $maintenanceReports = DB::table('maintenance_reports')
                ->whereIn('equipment_id', $equipmentIds)
                ->where(function ($query) use ($periodStart, $periodEnd) {
                    $query->whereBetween('intervention_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
                        ->orWhereBetween('created_at', [$periodStart->toDateTimeString(), $periodEnd->toDateTimeString()]);
                })
                ->orderBy('equipment_id')
                ->orderBy('intervention_date')
                ->get([
                    'id',
                    'equipment_id',
                    'intervention_type',
                    'status',
                    'intervention_date',
                    'started_at',
                    'ended_at',
                    'created_at',
                    'updated_at',
                ]);
        }

        $companyIdByEquipment = $equipments->pluck('company_id', 'id');

        $interventionEvents = $interventions->map(function (Intervention $intervention) use ($companyIdByEquipment) {
            $startAt = $intervention->date_start
                ? Carbon::parse($intervention->date_start)->startOfDay()
                : Carbon::parse($intervention->created_at);

            $endAt = $intervention->date_end
                ? Carbon::parse($intervention->date_end)->endOfDay()
                : Carbon::parse($intervention->updated_at);

            return [
                'company_id' => (int) ($companyIdByEquipment[(int) $intervention->equipment_id] ?? 0),
                'equipment_id' => (int) $intervention->equipment_id,
                'type' => (string) $intervention->type,
                'status' => (string) $intervention->status,
                'start_at' => $startAt,
                'end_at' => $endAt,
            ];
        });

        $maintenanceEvents = collect($maintenanceReports)->map(function ($report) use ($companyIdByEquipment) {
            $mappedType = in_array((string) $report->intervention_type, ['curative', 'diagnostic'], true)
                ? 'Curative'
                : 'Préventive';

            $mappedStatus = match ((string) $report->status) {
                'closed' => 'termine',
                'submitted', 'validated' => 'en_cours',
                default => 'en_attente',
            };

            $startAt = $report->started_at
                ? Carbon::parse((string) $report->started_at)
                : ($report->intervention_date
                    ? Carbon::parse((string) $report->intervention_date)->startOfDay()
                    : Carbon::parse((string) $report->created_at));

            $endAt = $report->ended_at
                ? Carbon::parse((string) $report->ended_at)
                : Carbon::parse((string) $report->updated_at);

            return [
                'company_id' => (int) ($companyIdByEquipment[(int) $report->equipment_id] ?? 0),
                'equipment_id' => (int) $report->equipment_id,
                'type' => $mappedType,
                'status' => $mappedStatus,
                'start_at' => $startAt,
                'end_at' => $endAt,
            ];
        });

        $allEvents = collect($interventionEvents)
            ->merge(collect($maintenanceEvents))
            ->merge(collect($correctiveEvents))
            ->filter(fn (array $event) => !empty($event['company_id']))
            ->unique(function (array $event) {
                return implode('|', [
                    (int) ($event['company_id'] ?? 0),
                    (int) ($event['equipment_id'] ?? 0),
                    (string) ($event['type'] ?? ''),
                    ($event['start_at'] instanceof Carbon ? $event['start_at']->format('Y-m-d') : ''),
                ]);
            })
            ->values();

        $eventsByCompany = $allEvents->groupBy(fn (array $event) => (int) ($event['company_id'] ?? 0));

        $rows = collect();
        foreach ($companyIds as $companyId) {
            $companyInterventions = collect($eventsByCompany->get($companyId, []))->values();
            $companyPlannings = collect($planningByCompany->get($companyId, []))->values();

            $plannedTotal = $companyPlannings->count();

            $onTimeCount = 0;
            if ($plannedTotal > 0 && $companyInterventions->isNotEmpty()) {
                foreach ($companyPlannings as $planning) {
                    $windowStart = $planning->planned_date ? Carbon::parse($planning->planned_date)->startOfDay() : null;
                    $windowEnd = $planning->planned_date_end
                        ? Carbon::parse($planning->planned_date_end)->endOfDay()
                        : ($windowStart ? $windowStart->copy()->endOfDay() : null);

                    if (!$windowStart || !$windowEnd) {
                        continue;
                    }

                    $matched = $companyInterventions->first(function (array $intervention) use ($windowStart, $windowEnd) {
                        $start = $intervention['start_at'] instanceof Carbon
                            ? $intervention['start_at']
                            : null;

                        if (!$start) {
                            return false;
                        }

                        return $start->betweenIncluded($windowStart, $windowEnd);
                    });

                    if ($matched) {
                        $onTimeCount++;
                    }
                }
            }
            $respectPlanning = $plannedTotal > 0 ? round(($onTimeCount / $plannedTotal) * 100, 1) : 0.0;

            $delayValues = [];
            $planningDates = $companyPlannings
                ->pluck('planned_date')
                ->filter()
                ->map(fn ($date) => Carbon::parse($date)->startOfDay())
                ->sort()
                ->values();
            if ($planningDates->isNotEmpty()) {
                foreach ($companyInterventions as $intervention) {
                    $startDate = ($intervention['start_at'] instanceof Carbon
                        ? $intervention['start_at']
                        : Carbon::now())->copy()->startOfDay();

                    $nearestPlanned = $planningDates
                        ->filter(fn (Carbon $planned) => $planned->lessThanOrEqualTo($startDate))
                        ->last();

                    if (!$nearestPlanned) {
                        $nearestPlanned = $planningDates->first();
                    }

                    if ($nearestPlanned) {
                        $delayValues[] = $nearestPlanned->diffInDays($startDate, false);
                    }
                }
            }
            $avgDelayDays = !empty($delayValues) ? round(array_sum($delayValues) / count($delayValues), 1) : 0.0;

            $repairDurations = [];
            foreach ($companyInterventions as $intervention) {
                if (($intervention['status'] ?? '') !== 'termine') {
                    continue;
                }
                $start = $intervention['start_at'] instanceof Carbon
                    ? $intervention['start_at']->copy()->startOfDay()
                    : null;
                $end = $intervention['end_at'] instanceof Carbon
                    ? $intervention['end_at']->copy()->endOfDay()
                    : null;

                if (!$start || !$end) {
                    continue;
                }

                if ($end->greaterThan($start)) {
                    $repairDurations[] = $start->diffInHours($end);
                }
            }
            $mttr = !empty($repairDurations) ? round(array_sum($repairDurations) / count($repairDurations), 1) : 0.0;

            $reInterventions = 0;
            $companyInterventions
                ->groupBy(fn (array $intervention) => (int) ($intervention['equipment_id'] ?? 0))
                ->each(function ($equipmentInterventions) use (&$reInterventions) {
                    $sorted = collect($equipmentInterventions)->sortBy(function (array $intervention) {
                        $start = $intervention['start_at'] ?? null;
                        return $start instanceof Carbon ? $start->timestamp : 0;
                    })->values();

                    for ($idx = 1; $idx < $sorted->count(); $idx++) {
                        $previous = $sorted[$idx - 1];
                        $current = $sorted[$idx];
                        $prevDate = $previous['start_at'] ?? null;
                        $currDate = $current['start_at'] ?? null;
                        if (!($prevDate instanceof Carbon) || !($currDate instanceof Carbon)) {
                            continue;
                        }

                        if ($prevDate->diffInDays($currDate) <= 30) {
                            $reInterventions++;
                        }
                    }
                });

            $totalInterventions = $companyInterventions->count();
            $reInterventionRate = $totalInterventions > 0
                ? round(($reInterventions / $totalInterventions) * 100, 1)
                : 0.0;

            $curativeGaps = [];
            $companyInterventions
                ->filter(fn (array $intervention) => in_array((string) ($intervention['type'] ?? ''), ['Curative', 'Urgente'], true))
                ->groupBy(fn (array $intervention) => (int) ($intervention['equipment_id'] ?? 0))
                ->each(function ($equipmentInterventions) use (&$curativeGaps) {
                    $sorted = collect($equipmentInterventions)->sortBy(function (array $intervention) {
                        $start = $intervention['start_at'] ?? null;
                        return $start instanceof Carbon ? $start->timestamp : 0;
                    })->values();

                    for ($idx = 1; $idx < $sorted->count(); $idx++) {
                        $previous = $sorted[$idx - 1];
                        $current = $sorted[$idx];
                        $prevDate = $previous['start_at'] ?? null;
                        $currDate = $current['start_at'] ?? null;
                        if (!($prevDate instanceof Carbon) || !($currDate instanceof Carbon)) {
                            continue;
                        }

                        $hours = $prevDate->diffInHours($currDate);
                        if ($hours > 0) {
                            $curativeGaps[] = $hours;
                        }
                    }
                });

            $mtbf = !empty($curativeGaps) ? round(array_sum($curativeGaps) / count($curativeGaps), 1) : 0.0;
            $availability = ($mtbf + $mttr) > 0
                ? round(($mtbf / ($mtbf + $mttr)) * 100, 1)
                : 0.0;

            $reactivity = max(0.0, min(100.0, round(100 - (abs($avgDelayDays) * 10), 1)));
            $technicalQuality = round((max(0, 100 - $reInterventionRate) + $availability) / 2, 1);
            $score = round(($respectPlanning * 0.4) + ($reactivity * 0.3) + ($technicalQuality * 0.3), 1);

            $rows->push([
                'company_id' => (int) $companyId,
                'company' => (string) ($companyNames[$companyId] ?? ('Société #' . $companyId)),
                'respect_planning' => $respectPlanning,
                'avg_delay_days' => $avgDelayDays,
                'mttr' => $mttr,
                'reintervention_rate' => $reInterventionRate,
                'availability' => $availability,
                'interventions_total' => $totalInterventions,
                'score' => $score,
                'badge' => $score >= 80 ? 'success' : ($score >= 60 ? 'warning' : 'danger'),
            ]);
        }

        $ordered = $rows->sortByDesc('score')->values();
        $topFastest = $rows
            ->filter(fn (array $item) => (float) ($item['mttr'] ?? 0) > 0)
            ->sortBy('mttr')
            ->take(5)
            ->values();

        $topFailures = $rows
            ->sortByDesc('interventions_total')
            ->take(5)
            ->values();

        return [
            'labels' => $ordered->pluck('company')->values()->all(),
            'score' => $ordered->pluck('score')->values()->all(),
            'respect_planning' => $ordered->pluck('respect_planning')->values()->all(),
            'avg_delay_days' => $ordered->pluck('avg_delay_days')->values()->all(),
            'mttr' => $ordered->pluck('mttr')->values()->all(),
            'reintervention_rate' => $ordered->pluck('reintervention_rate')->values()->all(),
            'availability' => $ordered->pluck('availability')->values()->all(),
            'interventions_total' => $ordered->pluck('interventions_total')->values()->all(),
            'top5' => $ordered->take(5)->values()->all(),
            'top_fastest' => $topFastest->values()->all(),
            'top_failures' => $topFailures->values()->all(),
            'filters' => [
                'months' => collect(range(1, 12))->map(fn ($value) => [
                    'value' => $value,
                    'label' => Carbon::create(2000, $value, 1)->translatedFormat('F'),
                ])->values()->all(),
                'years' => collect(range(((int) now()->year) - 5, ((int) now()->year + 1)))->reverse()->values()->all(),
                'services' => $services,
                'selected_month' => $month,
                'selected_year' => $year,
                'selected_service_id' => $selectedServiceId,
            ],
        ];
    }

    private function emptyExternalCompaniesPayload(?int $month, int $year, ?int $selectedServiceId, ?array $serviceScopeIds = null, array $services = []): array
    {
        if (empty($services)) {
            $servicesQuery = Service::query()->excludeHiddenForUi()->select('id', 'name')->orderBy('name');
            if (is_array($serviceScopeIds)) {
                if (empty($serviceScopeIds)) {
                    $servicesQuery->whereRaw('1=0');
                } else {
                    $servicesQuery->whereIn('id', $serviceScopeIds);
                }
            }
            $services = $servicesQuery->get()->map(fn (Service $service) => [
                'id' => (int) $service->id,
                'name' => (string) $service->name,
            ])->values()->all();
        }

        return [
            'labels' => [],
            'score' => [],
            'respect_planning' => [],
            'avg_delay_days' => [],
            'mttr' => [],
            'reintervention_rate' => [],
            'availability' => [],
            'interventions_total' => [],
            'top5' => [],
            'top_fastest' => [],
            'top_failures' => [],
            'filters' => [
                'months' => collect(range(1, 12))->map(fn ($value) => [
                    'value' => $value,
                    'label' => Carbon::create(2000, $value, 1)->translatedFormat('F'),
                ])->values()->all(),
                'years' => collect(range(((int) now()->year) - 5, ((int) now()->year + 1)))->reverse()->values()->all(),
                'services' => $services,
                'selected_month' => $month,
                'selected_year' => $year,
                'selected_service_id' => $selectedServiceId,
            ],
        ];
    }

    private function normalizeNameToken(string $value): string
    {
        $ascii = Str::upper(Str::ascii(trim($value)));

        return preg_replace('/[^A-Z0-9]+/', '', $ascii) ?: '';
    }

    private function resolveCompanyIdFromRawName(string $rawName, $companyIdByNormalizedName, $companyNames): int
    {
        $normalizedRaw = $this->normalizeNameToken($rawName);
        if ($normalizedRaw === '') {
            return 0;
        }

        if (isset($companyIdByNormalizedName[$normalizedRaw])) {
            return (int) $companyIdByNormalizedName[$normalizedRaw];
        }

        foreach ($companyNames as $companyId => $companyName) {
            $normalizedCompany = $this->normalizeNameToken((string) $companyName);
            if ($normalizedCompany === '') {
                continue;
            }

            if (str_contains($normalizedCompany, $normalizedRaw) || str_contains($normalizedRaw, $normalizedCompany)) {
                return (int) $companyId;
            }
        }

        return 0;
    }

    private function parseCorrectiveDate(string $dateText, string $fallbackCreatedAt): Carbon
    {
        $trimmed = trim($dateText);

        if ($trimmed !== '') {
            try {
                return Carbon::parse($trimmed)->startOfDay();
            } catch (Throwable $e) {
            }

            if (preg_match('/(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})/', $trimmed, $match) === 1) {
                try {
                    return Carbon::createFromFormat('d/m/Y', sprintf('%02d/%02d/%04d', (int) $match[1], (int) $match[2], (int) $match[3]))->startOfDay();
                } catch (Throwable $e) {
                }
            }

            if (preg_match('/\b(\d{4})\b/', $trimmed, $yearMatch) === 1) {
                return Carbon::create((int) $yearMatch[1], 1, 1)->startOfYear();
            }
        }

        try {
            return Carbon::parse($fallbackCreatedAt)->startOfDay();
        } catch (Throwable $e) {
            return now()->startOfDay();
        }
    }

    private function correctiveDateMatchesPeriod(string $dateText, string $fallbackCreatedAt, ?int $month, int $year): bool
    {
        $date = $this->parseCorrectiveDate($dateText, $fallbackCreatedAt);

        if ((int) $date->year !== (int) $year) {
            return false;
        }

        if ($month !== null && (int) $date->month !== (int) $month) {
            return false;
        }

        return true;
    }
}
