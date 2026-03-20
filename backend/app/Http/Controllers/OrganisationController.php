<?php

namespace App\Http\Controllers;

use App\Models\Floor;
use App\Models\Gst;
use App\Models\Hospital;
use App\Models\Intervention;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class OrganisationController extends Controller
{
    public function index()
    {
        $payload = Cache::remember('organisation.gst.tree.v1', now()->addMinutes(5), function () {
            $gstName = Schema::hasTable('gsts')
                ? (string) (Gst::query()->value('name') ?? 'GST Tanger–Tétouan–Al Hoceima')
                : 'GST Tanger–Tétouan–Al Hoceima';

            $hospitals = Hospital::query()
                ->whereIn('code', ['HME', 'HSP'])
                ->orderByRaw("FIELD(code, 'HME', 'HSP')")
                ->get(['id', 'code', 'name']);

            $hme = $hospitals->firstWhere('code', 'HME');
            $hsp = $hospitals->firstWhere('code', 'HSP');

            $floorCards = collect();
            if ($hme && Schema::hasTable('floors') && Schema::hasColumn('floors', 'hospital_id')) {
                $floorCards = Floor::query()
                    ->where('hospital_id', (int) $hme->id)
                    ->orderByDesc('display_order')
                    ->with([
                        'services' => function ($query) {
                            $query
                                ->orderBy('name')
                                ->withCount('equipments')
                                ->withCount('interventions')
                                ->withCount([
                                    'equipments as functional_equipments_count' => fn ($q) => $q->where('operational_status', 'fonctionnel'),
                                ]);
                        },
                    ])
                    ->get();
            }

            if ($floorCards->isNotEmpty()) {
                $serviceIds = $floorCards->flatMap(fn ($floor) => $floor->services->pluck('id'))->map(fn ($id) => (int) $id)->values()->all();
                $mttrByService = $this->mttrByService($serviceIds);

                $floors = $floorCards->map(function (Floor $floor) use ($mttrByService) {
                    $services = $floor->services->map(function (Service $service) use ($mttrByService) {
                        $equipmentsTotal = (int) ($service->equipments_count ?? 0);
                        $breakdownsTotal = (int) ($service->interventions_count ?? 0);
                        $functionalTotal = (int) ($service->functional_equipments_count ?? 0);
                        $mttr = isset($mttrByService[(int) $service->id]) ? round((float) $mttrByService[(int) $service->id], 1) : null;
                        $mtbf = ($breakdownsTotal > 0 && $equipmentsTotal > 0)
                            ? round(($equipmentsTotal * 720) / $breakdownsTotal, 1)
                            : null;
                        $availability = $equipmentsTotal > 0
                            ? round(($functionalTotal / $equipmentsTotal) * 100, 1)
                            : null;

                        return [
                            'id' => (int) $service->id,
                            'name' => (string) $service->name,
                            'code' => (string) ($service->code ?? ''),
                            'kpi' => [
                                'equipments_total' => $equipmentsTotal,
                                'breakdowns_total' => $breakdownsTotal,
                                'mttr_hours' => $mttr,
                                'mtbf_hours' => $mtbf,
                                'availability' => $availability,
                                'severity' => $this->severity($breakdownsTotal),
                            ],
                        ];
                    })->values();

                    return [
                        'id' => (int) $floor->id,
                        'name' => (string) $floor->name,
                        'services' => $services,
                        'totals' => [
                            'equipments_total' => (int) $services->sum('kpi.equipments_total'),
                            'breakdowns_total' => (int) $services->sum('kpi.breakdowns_total'),
                        ],
                    ];
                })->values();
            } else {
                $floors = $this->buildOfficialFallbackFloors();
            }

            return [
                'gst_name' => $gstName,
                'direction_generale' => true,
                'branche_sanitaire' => true,
                'hospitals' => [
                    'hme' => [
                        'id' => (int) ($hme?->id ?? 0),
                        'name' => (string) ($hme?->name ?? 'Hôpital Mère-Enfants'),
                        'code' => 'HME',
                        'floors' => $floors,
                    ],
                    'hsp' => [
                        'id' => (int) ($hsp?->id ?? 0),
                        'name' => (string) ($hsp?->name ?? 'Hôpital des Spécialités'),
                        'code' => 'HSP',
                    ],
                ],
            ];
        });

        return view('pages.organisation.gst', [
            'tree' => $payload,
        ]);
    }

    private function mttrByService(array $serviceIds): array
    {
        if ($serviceIds === []) {
            return [];
        }

        return Intervention::query()
            ->join('equipments', 'equipments.id', '=', 'interventions.equipment_id')
            ->selectRaw('equipments.service_id as service_id, AVG(TIMESTAMPDIFF(HOUR, COALESCE(interventions.date_start, interventions.created_at), COALESCE(interventions.date_end, interventions.closed_at, interventions.updated_at, interventions.created_at))) as mttr')
            ->whereIn('equipments.service_id', $serviceIds)
            ->where('interventions.status', 'termine')
            ->groupBy('equipments.service_id')
            ->pluck('mttr', 'service_id')
            ->map(fn ($value) => $value !== null ? (float) $value : null)
            ->all();
    }

    private function severity(int $breakdowns): string
    {
        if ($breakdowns > 5) {
            return 'red';
        }

        if ($breakdowns > 0) {
            return 'orange';
        }

        return 'green';
    }

    private function buildOfficialFallbackFloors()
    {
        $official = [
            'ETG4' => ['Gynécologie', 'Obstétrique'],
            'ETG3' => ['Pédiatrie', 'Unité Oncologie pédiatrique', 'CH. INF. Viscérale', 'CH. INF. Traumatologie'],
            'ETG2' => ['Direction générale'],
            'ETG1' => ['U.T.A', 'Réa Pédiatrique', 'Unité Réa Néonatale', 'U.S.I.C', 'Unité Rééducation cardiaque'],
            'ETG0' => ['SAUV Adultes 1 et 2', 'UHTCD Adultes 1 et 2', 'SAUV Enfants', 'UHTCD Enfants', 'Radiologie des urgences', 'Bloc porte'],
            'ETG-1' => ['Locaux Techniques'],
        ];

        $allServiceNames = collect($official)->flatten()->values()->all();

        $serviceRows = Service::query()
            ->whereIn('name', $allServiceNames)
            ->withCount('equipments')
            ->withCount('interventions')
            ->withCount([
                'equipments as functional_equipments_count' => fn ($q) => $q->where('operational_status', 'fonctionnel'),
            ])
            ->get()
            ->keyBy('name');

        $serviceIds = $serviceRows->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $mttrByService = $this->mttrByService($serviceIds);

        return collect($official)->map(function ($servicesList, $floorName) use ($serviceRows, $mttrByService) {
            $services = collect($servicesList)->map(function ($serviceName) use ($serviceRows, $mttrByService) {
                $service = $serviceRows->get($serviceName);

                $equipmentsTotal = (int) ($service?->equipments_count ?? 0);
                $breakdownsTotal = (int) ($service?->interventions_count ?? 0);
                $functionalTotal = (int) ($service?->functional_equipments_count ?? 0);
                $serviceId = (int) ($service?->id ?? 0);
                $mttr = $serviceId > 0 && isset($mttrByService[$serviceId]) ? round((float) $mttrByService[$serviceId], 1) : null;
                $mtbf = ($breakdownsTotal > 0 && $equipmentsTotal > 0)
                    ? round(($equipmentsTotal * 720) / $breakdownsTotal, 1)
                    : null;
                $availability = $equipmentsTotal > 0
                    ? round(($functionalTotal / $equipmentsTotal) * 100, 1)
                    : null;

                return [
                    'id' => $serviceId,
                    'name' => (string) $serviceName,
                    'code' => (string) ($service?->code ?? ''),
                    'kpi' => [
                        'equipments_total' => $equipmentsTotal,
                        'breakdowns_total' => $breakdownsTotal,
                        'mttr_hours' => $mttr,
                        'mtbf_hours' => $mtbf,
                        'availability' => $availability,
                        'severity' => $this->severity($breakdownsTotal),
                    ],
                ];
            })->values();

            return [
                'id' => 0,
                'name' => (string) $floorName,
                'services' => $services,
                'totals' => [
                    'equipments_total' => (int) $services->sum('kpi.equipments_total'),
                    'breakdowns_total' => (int) $services->sum('kpi.breakdowns_total'),
                ],
            ];
        })->values();
    }
}
