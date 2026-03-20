<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MaintenanceReport;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MttrMtbfController extends Controller
{
    /**
     * Show the MTTR/MTBF KPI dashboard page.
     */
    public function index(): \Illuminate\View\View
    {
        $services = Service::query()
            ->excludeHiddenForUi()
            ->orderBy('name')
            ->get(['id', 'name']);

        // Default date range: last 12 months
        $defaultStart = now()->subMonths(12)->toDateString();
        $defaultEnd   = now()->toDateString();

        return view('pages.mttr-mtbf', compact('services', 'defaultStart', 'defaultEnd'));
    }

    /**
     * AJAX endpoint: return MTTR / MTBF / Disponibilité grouped by designation.
     */
    public function data(Request $request): JsonResponse
    {
        $startDate   = $request->input('start_date', now()->subMonths(12)->toDateString());
        $endDate     = $request->input('end_date', now()->toDateString());
        $serviceId   = $request->input('service_id');
        $designation = $request->input('designation');

        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->endOfDay();

        // Observation window in hours
        $observationHours = max($start->diffInHours($end), 1);

        $timeUnit = $request->input('time_unit', 'hours'); // 'hours', 'days', 'months', 'years'

        // Get total count of equipment for each designation in this scope
        // This is needed to calculate the individual average MTBF (Total hours * Nb Equipments)
        $eqCountQuery = DB::table('equipments')
            ->select('designation', DB::raw('COUNT(id) as total_equipments'))
            ->groupBy('designation');
            
        if ($serviceId) {
            $eqCountQuery->where('service_id', $serviceId);
        }
        $eqCounts = $eqCountQuery->pluck('total_equipments', 'designation');

        // Build base query on maintenance_reports joined with equipments
        $query = DB::table('maintenance_reports as mr')
            ->join('equipments as eq', 'mr.equipment_id', '=', 'eq.id')
            ->whereBetween('mr.intervention_date', [$start->toDateString(), $end->toDateString()])
            ->where('mr.intervention_type', 'curative')
            ->whereIn('mr.status', [MaintenanceReport::STATUS_VALIDATED, MaintenanceReport::STATUS_CLOSED])
            ->select(
                'eq.designation',
                DB::raw('COUNT(mr.id) as nb_pannes'),
                DB::raw('SUM(COALESCE(mr.duration_minutes, 0)) as total_downtime_minutes'),
                DB::raw('AVG(COALESCE(mr.duration_minutes, 0)) as avg_duration_minutes')
            )
            ->groupBy('eq.designation')
            ->orderBy('eq.designation');

        if ($serviceId) {
            $query->where('eq.service_id', $serviceId);
        }

        if ($designation) {
            $query->where('eq.designation', 'like', '%' . $designation . '%');
        }

        $rows = $query->get();
        $source = 'maintenance_reports';

        $data = [];

        // Define multipliers directly
        $unitMultiplier = match($timeUnit) {
            'days'   => 1 / 24,
            'months' => 1 / (24 * 30.416),
            'years'  => 1 / (24 * 365),
            default  => 1, // hours
        };

        foreach ($rows as $row) {
            $nbPannes            = max((int) $row->nb_pannes, 1);
            $totalDowntimeHours  = round((float) $row->total_downtime_minutes / 60, 4);
            $mttrHours           = round((float) $row->avg_duration_minutes / 60, 4);

            // Get equipment quantity for this designation, fallback to 1
            $qty = $eqCounts->get($row->designation, 1);

            // Uptime = (observation window * equipment quantity) minus total downtime
            $uptimeHours = max(($observationHours * $qty) - $totalDowntimeHours, 0);
            
            // Average MTBF across all equipments of this type
            $mtbfHours   = round($uptimeHours / $nbPannes, 4);

            // Disponibilité  = MTBF / (MTBF + MTTR) * 100
            $dispo = ($mtbfHours + $mttrHours) > 0
                ? round($mtbfHours / ($mtbfHours + $mttrHours) * 100, 2)
                : 0;

            // Apply selected time unit conversion
            $mttrValue = round($mttrHours * $unitMultiplier, 2);
            $mtbfValue = round($mtbfHours * $unitMultiplier, 2);
            $downtimeValue = round($totalDowntimeHours * $unitMultiplier, 2);

            $data[] = [
                'designation'      => $row->designation,
                'qty'              => $qty,
                'nb_pannes'        => (int) $row->nb_pannes,
                'mttr_hours'       => $mttrValue,
                'mtbf_hours'       => $mtbfValue,
                'disponibilite'    => $dispo,
                'downtime_hours'   => $downtimeValue,
                'observation_hours'=> $observationHours,
            ];
        }

        if (empty($data) && Schema::hasTable('bilan_maintenance_correctives')) {
            $source = 'bilan_corrective_fallback';

            $selectedServiceName = null;
            if (!empty($serviceId)) {
                $selectedServiceName = Service::query()->where('id', (int) $serviceId)->value('name');
                $selectedServiceName = is_string($selectedServiceName) ? mb_strtolower(trim($selectedServiceName)) : null;
            }

            $bilanRows = DB::table('bilan_maintenance_correctives')
                ->select('equipment_designation', 'service_names', 'intervention_date_text')
                ->when($designation, function ($innerQuery) use ($designation) {
                    $innerQuery->where('equipment_designation', 'like', '%' . $designation . '%');
                })
                ->get();

            $groupedCounts = [];

            foreach ($bilanRows as $bilanRow) {
                $rowDesignation = trim((string) ($bilanRow->equipment_designation ?? ''));
                if ($rowDesignation === '') {
                    continue;
                }

                if ($selectedServiceName) {
                    $rowServices = mb_strtolower(trim((string) ($bilanRow->service_names ?? '')));
                    if ($rowServices === '' || !str_contains($rowServices, $selectedServiceName)) {
                        continue;
                    }
                }

                $parsedDate = $this->parseInterventionTextDate((string) ($bilanRow->intervention_date_text ?? ''));
                if ($parsedDate !== null && ($parsedDate->lt($start) || $parsedDate->gt($end))) {
                    continue;
                }

                if (!array_key_exists($rowDesignation, $groupedCounts)) {
                    $groupedCounts[$rowDesignation] = 0;
                }

                $groupedCounts[$rowDesignation]++;
            }

            foreach ($groupedCounts as $rowDesignation => $count) {
                $nbPannes = max((int) $count, 1);
                $qty = (int) ($eqCounts->get($rowDesignation, 0));
                $effectiveQty = max($qty, 1);
                $mtbfHours = round(($observationHours * $effectiveQty) / $nbPannes, 4);
                $mtbfValue = round($mtbfHours * $unitMultiplier, 2);

                $data[] = [
                    'designation' => $rowDesignation,
                    'qty' => $qty,
                    'nb_pannes' => (int) $count,
                    'mttr_hours' => null,
                    'mtbf_hours' => $mtbfValue,
                    'disponibilite' => null,
                    'downtime_hours' => null,
                    'observation_hours' => $observationHours,
                ];
            }
        }

        if ($source === 'maintenance_reports') {
            usort($data, fn($a, $b) => $a['disponibilite'] <=> $b['disponibilite']);
        } else {
            usort($data, fn($a, $b) => $b['nb_pannes'] <=> $a['nb_pannes']);
        }

        // Summary statistics
        $summary = [];
        if (!empty($data)) {
            $dispoValues = array_values(array_filter(array_column($data, 'disponibilite'), fn ($value) => $value !== null));
            $mttrValues = array_values(array_filter(array_column($data, 'mttr_hours'), fn ($value) => $value !== null));
            $mtbfValues = array_values(array_filter(array_column($data, 'mtbf_hours'), fn ($value) => $value !== null));

            $avgDispo  = !empty($dispoValues) ? round(array_sum($dispoValues) / count($dispoValues), 2) : null;
            $avgMttr   = !empty($mttrValues) ? round(array_sum($mttrValues) / count($mttrValues), 2) : null;
            $avgMtbf   = !empty($mtbfValues) ? round(array_sum($mtbfValues) / count($mtbfValues), 2) : null;
            $totalPannes = array_sum(array_column($data, 'nb_pannes'));

            $summary = [
                'avg_disponibilite' => $avgDispo,
                'avg_mttr'          => $avgMttr,
                'avg_mtbf'          => $avgMtbf,
                'total_pannes'      => $totalPannes,
                'nb_designations'   => count($data),
            ];
        }

        // Designations list for filter autocomplete (unfiltered by designation)
        $allDesignations = collect();
        if ($source === 'maintenance_reports') {
            $allDesignations = DB::table('maintenance_reports as mr')
                ->join('equipments as eq', 'mr.equipment_id', '=', 'eq.id')
                ->whereBetween('mr.intervention_date', [$start->toDateString(), $end->toDateString()])
                ->where('mr.intervention_type', 'curative')
                ->when($serviceId, fn($q) => $q->where('eq.service_id', $serviceId))
                ->distinct()
                ->orderBy('eq.designation')
                ->pluck('eq.designation');
        } elseif (Schema::hasTable('bilan_maintenance_correctives')) {
            $allDesignations = DB::table('bilan_maintenance_correctives')
                ->whereNotNull('equipment_designation')
                ->where('equipment_designation', '!=', '')
                ->distinct()
                ->orderBy('equipment_designation')
                ->pluck('equipment_designation');
        }

        return response()->json([
            'data'             => $data,
            'summary'          => $summary,
            'all_designations' => $allDesignations,
            'source'           => $source,
            'observation_hours'=> $observationHours,
            'time_unit'        => $timeUnit,
            'period'           => [
                'start' => $start->format('d/m/Y'),
                'end'   => $end->format('d/m/Y'),
            ],
        ]);
    }

    private function parseInterventionTextDate(string $value): ?Carbon
    {
        $text = trim($value);
        if ($text === '') {
            return null;
        }

        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'Y/m/d'];
        foreach ($formats as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $text);
                if ($parsed !== false) {
                    return $parsed->startOfDay();
                }
            } catch (\Throwable $e) {
            }
        }

        return null;
    }
}
