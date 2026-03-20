<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Equipment;
use App\Models\ExternalIntervention;
use App\Models\Service;
use App\Support\ServiceAccess;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CompanyPerformanceReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->resolveFilters($request);
        $user = $request->user();

        $tableAvailable = Schema::hasTable('external_interventions');

        $rows = $tableAvailable
            ? $this->buildTableRows($this->buildRowsQuery($filters)->paginate(30)->withQueryString())
            : collect();

        $kpis = $tableAvailable
            ? $this->buildKpis($filters)
            : collect();

        $topFastest = $kpis
            ->filter(fn ($row) => $row['mttr_minutes'] !== null)
            ->sortBy('mttr_minutes')
            ->take(5)
            ->values();

        $topFailures = $kpis
            ->sortByDesc('pannes_count')
            ->take(5)
            ->values();

        $equipmentsQuery = Equipment::query()
            ->select(['id', 'designation', 'inventory_number_current'])
            ->orderBy('designation');
        ServiceAccess::applyEquipmentScope($equipmentsQuery, $user);

        $servicesQuery = Service::query()->excludeHiddenForUi()->orderBy('name');
        if ($user && !$user->hasGlobalAccess()) {
            $serviceIds = $user->isUnitRestricted()
                ? ($user->service_id ? [(int) $user->service_id] : [])
                : $user->allowedServiceIds();
            $servicesQuery->whereIn('id', $serviceIds);
        }

        return view('reports.company-performance', [
            'tableAvailable' => $tableAvailable,
            'filters' => $filters,
            'rows' => $rows,
            'kpis' => $kpis,
            'topFastest' => $topFastest,
            'topFailures' => $topFailures,
            'chartInterventions' => [
                'labels' => $kpis->pluck('company_name')->values(),
                'values' => $kpis->pluck('total_interventions')->values(),
            ],
            'chartMttr' => [
                'labels' => $kpis->pluck('company_name')->values(),
                'values' => $kpis->map(fn ($row) => $row['mttr_minutes'] !== null ? round($row['mttr_minutes'] / 60, 2) : 0)->values(),
            ],
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'services' => $servicesQuery->get(['id', 'name']),
            'equipments' => $equipmentsQuery->get(),
        ]);
    }

    public function exportExcel(Request $request)
    {
        if (!Schema::hasTable('external_interventions')) {
            return redirect()->route('company-performance.index')->with('error', 'Table external_interventions introuvable.');
        }

        $filters = $this->resolveFilters($request);
        $rows = $this->buildTableRows($this->buildRowsQuery($filters)->get());

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Interventions sociétés');

        $headers = [
            'Ticket',
            'Équipement',
            'Service',
            'Société',
            'Date panne',
            'Premier appel',
            'Résolution',
            'Temps intervention',
            'Statut',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $line = 2;
        foreach ($rows as $row) {
            $sheet->fromArray([
                $row['ticket_id'],
                $row['equipment'],
                $row['hospital_service'],
                $row['external_company'],
                $row['breakdown_date'],
                $row['first_call'],
                $row['resolution_date'],
                $row['intervention_time'],
                $row['intervention_status'],
            ], null, 'A' . $line);
            $line++;
        }

        $tmpFile = storage_path('app/' . uniqid('company_performance_', true) . '.xlsx');
        (new Xlsx($spreadsheet))->save($tmpFile);

        return response()->download($tmpFile, 'rapport-interventions-societes-externes.xlsx')->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        if (!Schema::hasTable('external_interventions')) {
            return redirect()->route('company-performance.index')->with('error', 'Table external_interventions introuvable.');
        }

        $filters = $this->resolveFilters($request);
        $rows = $this->buildTableRows($this->buildRowsQuery($filters)->get());
        $kpis = $this->buildKpis($filters);

        $pdf = Pdf::loadView('reports.company-performance-pdf', [
            'rows' => $rows,
            'kpis' => $kpis,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('rapport-interventions-societes-externes.pdf');
    }

    private function resolveFilters(Request $request): array
    {
        return [
            'company_id' => (int) $request->integer('company_id'),
            'service_id' => (int) $request->integer('service_id'),
            'equipment_id' => (int) $request->integer('equipment_id'),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];
    }

    private function buildRowsQuery(array $filters)
    {
        $query = ExternalIntervention::query()
            ->with([
                'intervention:id,code,equipment_id,status,date_start',
                'equipment:id,designation,inventory_number_current,service_id,company_id',
                'equipment.service:id,name',
                'equipment.company:id,name',
                'company:id,name',
            ])
            ->orderByDesc('id');

        if (($filters['company_id'] ?? 0) > 0) {
            $companyId = (int) $filters['company_id'];
            $query->where(function ($innerQuery) use ($companyId) {
                $innerQuery->where('company_id', $companyId)
                    ->orWhereHas('equipment', fn ($eqQuery) => $eqQuery->where('company_id', $companyId));
            });
        }

        if (($filters['service_id'] ?? 0) > 0) {
            $serviceId = (int) $filters['service_id'];
            $query->whereHas('equipment', fn ($eqQuery) => $eqQuery->where('service_id', $serviceId));
        }

        if (($filters['equipment_id'] ?? 0) > 0) {
            $query->where('equipment_id', (int) $filters['equipment_id']);
        }

        if (($filters['date_from'] ?? '') !== '') {
            $query->where(function ($innerQuery) use ($filters) {
                $innerQuery->whereDate('failure_datetime', '>=', $filters['date_from'])
                    ->orWhereHas('intervention', fn ($iQuery) => $iQuery->whereDate('date_start', '>=', $filters['date_from']));
            });
        }

        if (($filters['date_to'] ?? '') !== '') {
            $query->where(function ($innerQuery) use ($filters) {
                $innerQuery->whereDate('failure_datetime', '<=', $filters['date_to'])
                    ->orWhereHas('intervention', fn ($iQuery) => $iQuery->whereDate('date_start', '<=', $filters['date_to']));
            });
        }

        return $query;
    }

    private function buildTableRows($rows): LengthAwarePaginator|\Illuminate\Support\Collection
    {
        $map = function (ExternalIntervention $row): array {
            $equipment = $row->equipment;
            $intervention = $row->intervention;
            $companyName = $row->company?->name ?: ($equipment?->company?->name ?: '-');
            $serviceName = $equipment?->service?->name ?: '-';

            $interventionMinutes = $row->intervention_duration_hours !== null
                ? (float) $row->intervention_duration_hours * 60
                : null;

            if ($interventionMinutes === null && $row->first_call_datetime && $row->resolution_datetime) {
                $interventionMinutes = $row->resolution_datetime->greaterThanOrEqualTo($row->first_call_datetime)
                    ? $row->first_call_datetime->diffInMinutes($row->resolution_datetime)
                    : null;
            }

            return [
                'ticket_id' => $row->ticket_number ?: ($intervention?->code ?: ('INT-' . (int) ($intervention?->id ?? 0))),
                'equipment' => $equipment?->designation ?: '-',
                'inventory_number' => $equipment?->inventory_number_current ?: '-',
                'hospital_service' => $row->service_name ?: $serviceName,
                'external_company' => $companyName,
                'breakdown_date' => optional($row->failure_datetime)->format('Y-m-d H:i') ?: (optional($intervention?->date_start)->format('Y-m-d') ?: '-'),
                'first_call' => optional($row->first_call_datetime)->format('Y-m-d H:i') ?: '-',
                'resolution_date' => optional($row->resolution_datetime)->format('Y-m-d H:i') ?: '-',
                'intervention_time' => $this->formatMinutes($interventionMinutes),
                'intervention_status' => ($row->status ?: ($row->intervention_status ?: ($intervention?->status ?: '-'))),
                'intervention_time_minutes' => $interventionMinutes,
            ];
        };

        if ($rows instanceof LengthAwarePaginator) {
            $rows->setCollection($rows->getCollection()->map($map));
            return $rows;
        }

        return collect($rows)->map($map)->values();
    }

    private function buildKpis(array $filters): \Illuminate\Support\Collection
    {
        $statusExpression = Schema::hasColumn('external_interventions', 'status')
            ? 'COALESCE(NULLIF(ei.status, ""), NULLIF(ei.intervention_status, ""), i.status)'
            : 'COALESCE(NULLIF(ei.intervention_status, ""), i.status)';

        $query = DB::table('external_interventions as ei')
            ->join('interventions as i', 'i.id', '=', 'ei.intervention_id')
            ->join('equipments as e', 'e.id', '=', 'ei.equipment_id')
            ->leftJoin('companies as c', 'c.id', '=', 'ei.company_id')
            ->leftJoin('companies as ec', 'ec.id', '=', 'e.company_id')
            ->selectRaw('COALESCE(c.id, ec.id) as company_id')
            ->selectRaw('MIN(COALESCE(c.name, ec.name, "Non assignée")) as company_name')
            ->selectRaw('COUNT(*) as total_interventions')
            ->selectRaw('COUNT(*) as pannes_count')
            ->selectRaw('SUM(CASE WHEN ' . $statusExpression . ' IN ("resolved", "resolu", "termine", "closed", "validated", "ferme") THEN 1 ELSE 0 END) as resolved_count')
            ->selectRaw('AVG(CASE WHEN ei.intervention_duration_hours IS NOT NULL THEN (ei.intervention_duration_hours * 60) WHEN ei.first_call_datetime IS NOT NULL AND ei.resolution_datetime IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, ei.first_call_datetime, ei.resolution_datetime) END) as mttr_minutes')
            ->groupByRaw('COALESCE(c.id, ec.id)')
            ->orderBy('company_name');

        if (($filters['company_id'] ?? 0) > 0) {
            $companyId = (int) $filters['company_id'];
            $query->where(function ($innerQuery) use ($companyId) {
                $innerQuery->where('ei.company_id', $companyId)
                    ->orWhere('e.company_id', $companyId);
            });
        }

        if (($filters['service_id'] ?? 0) > 0) {
            $query->where('e.service_id', (int) $filters['service_id']);
        }

        if (($filters['equipment_id'] ?? 0) > 0) {
            $query->where('ei.equipment_id', (int) $filters['equipment_id']);
        }

        if (($filters['date_from'] ?? '') !== '') {
            $query->where(function ($innerQuery) use ($filters) {
                $innerQuery->whereDate('ei.failure_datetime', '>=', $filters['date_from'])
                    ->orWhereDate('i.date_start', '>=', $filters['date_from']);
            });
        }

        if (($filters['date_to'] ?? '') !== '') {
            $query->where(function ($innerQuery) use ($filters) {
                $innerQuery->whereDate('ei.failure_datetime', '<=', $filters['date_to'])
                    ->orWhereDate('i.date_start', '<=', $filters['date_to']);
            });
        }

        $repeatByCompany = $this->buildRepeatFailuresMap($filters);
        $availabilityByCompany = $this->buildEquipmentAvailabilityMap($filters);

        return $query->get()->map(function ($row) use ($repeatByCompany, $availabilityByCompany) {
            $total = (int) ($row->total_interventions ?? 0);
            $resolved = (int) ($row->resolved_count ?? 0);
            $mttrMinutes = isset($row->mttr_minutes) ? (float) $row->mttr_minutes : null;
            $resolutionRate = $total > 0 ? round(($resolved / $total) * 100, 2) : 0;
            $companyId = (int) ($row->company_id ?? 0);

            return [
                'company_id' => $companyId,
                'company_name' => $row->company_name ?: 'Non assignée',
                'total_interventions' => $total,
                'pannes_count' => (int) ($row->pannes_count ?? 0),
                'mttr_minutes' => $mttrMinutes,
                'mttr_label' => $this->formatMinutes($mttrMinutes),
                'resolved_count' => $resolved,
                'resolution_rate' => $resolutionRate,
                'repeat_failures_count' => (int) ($repeatByCompany[$companyId] ?? 0),
                'equipment_availability_rate' => (float) ($availabilityByCompany[$companyId] ?? 0),
            ];
        })->values();
    }

    private function buildRepeatFailuresMap(array $filters): array
    {
        $query = DB::table('external_interventions as ei')
            ->join('equipments as e', 'e.id', '=', 'ei.equipment_id')
            ->leftJoin('companies as c', 'c.id', '=', 'ei.company_id')
            ->leftJoin('companies as ec', 'ec.id', '=', 'e.company_id')
            ->selectRaw('COALESCE(c.id, ec.id) as company_id')
            ->selectRaw('ei.equipment_id as equipment_id')
            ->selectRaw('COUNT(*) as interventions_per_equipment')
            ->groupByRaw('COALESCE(c.id, ec.id), ei.equipment_id');

        if (($filters['company_id'] ?? 0) > 0) {
            $companyId = (int) $filters['company_id'];
            $query->where(function ($innerQuery) use ($companyId) {
                $innerQuery->where('ei.company_id', $companyId)
                    ->orWhere('e.company_id', $companyId);
            });
        }

        if (($filters['service_id'] ?? 0) > 0) {
            $query->where('e.service_id', (int) $filters['service_id']);
        }

        if (($filters['equipment_id'] ?? 0) > 0) {
            $query->where('ei.equipment_id', (int) $filters['equipment_id']);
        }

        if (($filters['date_from'] ?? '') !== '') {
            $query->whereDate('ei.failure_datetime', '>=', $filters['date_from']);
        }

        if (($filters['date_to'] ?? '') !== '') {
            $query->whereDate('ei.failure_datetime', '<=', $filters['date_to']);
        }

        return $query->get()
            ->filter(fn ($row) => (int) ($row->interventions_per_equipment ?? 0) > 1)
            ->groupBy('company_id')
            ->map(fn ($rows) => $rows->count())
            ->toArray();
    }

    private function buildEquipmentAvailabilityMap(array $filters): array
    {
        $query = DB::table('equipments as e')
            ->whereNotNull('e.company_id')
            ->selectRaw('e.company_id as company_id')
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(CASE WHEN e.operational_status = "fonctionnel" THEN 1 ELSE 0 END) as functional_count')
            ->groupBy('e.company_id');

        if (($filters['company_id'] ?? 0) > 0) {
            $query->where('e.company_id', (int) $filters['company_id']);
        }

        if (($filters['service_id'] ?? 0) > 0) {
            $query->where('e.service_id', (int) $filters['service_id']);
        }

        if (($filters['equipment_id'] ?? 0) > 0) {
            $query->where('e.id', (int) $filters['equipment_id']);
        }

        return $query->get()->mapWithKeys(function ($row) {
            $total = (int) ($row->total_count ?? 0);
            $functional = (int) ($row->functional_count ?? 0);
            $rate = $total > 0 ? round(($functional / $total) * 100, 2) : 0;

            return [
                (int) ($row->company_id ?? 0) => $rate,
            ];
        })->toArray();
    }

    private function formatMinutes($minutes): string
    {
        if ($minutes === null) {
            return '-';
        }

        $totalMinutes = max(0, (int) round((float) $minutes));
        $hours = intdiv($totalMinutes, 60);
        $mins = $totalMinutes % 60;

        if ($hours === 0) {
            return $mins . ' min';
        }

        return $hours . ' h ' . str_pad((string) $mins, 2, '0', STR_PAD_LEFT) . ' min';
    }
}
