<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePreventiveMaintenanceRequest;
use App\Http\Requests\UpdatePreventiveMaintenanceRequest;
use App\Models\Equipment;
use App\Models\PreventiveMaintenance;
use App\Support\ServiceAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PreventiveMaintenanceController extends Controller
{
    public function index()
    {
        $rows = PreventiveMaintenance::query()
            ->with('equipment:id,inventory_number_current,designation')
            ->orderBy('next_maintenance_date')
            ->orderBy('code')
            ->get()
            ->map(function (PreventiveMaintenance $item) {
                $equipmentLabel = trim((string) ($item->equipment?->inventory_number_current . ' - ' . $item->equipment?->designation));

                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'equipement' => $equipmentLabel !== '-' ? $equipmentLabel : ($item->equipment?->designation ?: '-'),
                    'periodicite' => $item->periodicity,
                    'dernier' => optional($item->last_maintenance_date)->format('Y-m-d') ?: '-',
                    'prochain' => optional($item->next_maintenance_date)->format('Y-m-d') ?: '-',
                    'statut' => $item->status,
                    'edit_url' => route('maintenance-preventive.edit', $item),
                    'delete_url' => route('maintenance-preventive.destroy', $item),
                ];
            })
            ->values();

        $historicalRows = collect();

        if (Schema::hasTable('bilan_maintenance_preventives')) {
            $historicalRows = DB::table('bilan_maintenance_preventives')
                ->orderByDesc('created_at')
                ->limit(1000)
                ->get()
                ->map(function ($item) {
                    $historicalCode = 'MP-HIST-' . (string) $item->id;

                    return [
                        'societe' => (string) ($item->company_name ?: '-'),
                        'designation_equipement' => (string) ($item->equipment_designation ?: '-'),
                        'marque' => (string) ($item->brand_name ?: '-'),
                        'modele' => (string) ($item->model_name ?: '-'),
                        'marche_contrat' => (string) ($item->market_or_contract_ref ?: '-'),
                        'numero_serie' => (string) ($item->serial_number ?: '-'),
                        'dates_intervention' => (string) ($item->intervention_dates_text ?: '-'),
                        'details_intervention' => (string) ($item->intervention_details ?: '-'),
                        'observations' => (string) ($item->observations ?: '-'),
                        'services' => (string) ($item->service_names ?: '-'),
                        'activite_achevee' => is_null($item->activity_completed)
                            ? '-'
                            : ((bool) $item->activity_completed ? 'OUI' : 'NON'),
                        'edit_url' => route('maintenance-preventive.create', [
                            'source' => 'historical',
                            'historical_id' => $item->id,
                            'code' => $historicalCode,
                            'equipment_search' => (string) ($item->equipment_designation ?: ''),
                            'periodicity' => 'Annuel',
                            'status' => 'actif',
                        ]),
                    ];
                })
                ->values();
        }

        return view('pages.maintenance-preventive', [
            'maintenanceData' => $rows,
            'historicalMaintenanceData' => $historicalRows,
        ]);
    }

    public function create(Request $request)
    {
        $equipmentId = (int) $request->query('equipment_id', 0);
        $equipmentSearch = trim((string) $request->query('equipment_search', ''));

        $equipmentQuery = Equipment::query()
            ->select('id', 'inventory_number_current', 'designation')
            ->orderBy('designation');
        ServiceAccess::applyEquipmentScope($equipmentQuery, $request->user());

        if ($equipmentId <= 0 && $equipmentSearch !== '') {
            $match = (clone $equipmentQuery)
                ->select('id')
                ->where(function ($query) use ($equipmentSearch) {
                    $query->where('designation', 'like', '%' . $equipmentSearch . '%')
                        ->orWhere('inventory_number_current', 'like', '%' . $equipmentSearch . '%');
                })
                ->first();

            if ($match) {
                $equipmentId = (int) $match->id;
            }
        }

        $prefill = [
            'source' => (string) $request->query('source', ''),
            'historical_id' => (string) $request->query('historical_id', ''),
            'code' => (string) $request->query('code', ''),
            'equipment_id' => $equipmentId > 0 ? $equipmentId : null,
            'equipment_search' => $equipmentSearch,
            'periodicity' => (string) $request->query('periodicity', 'Mensuel'),
            'status' => (string) $request->query('status', 'actif'),
            'last_maintenance_date' => (string) $request->query('last_maintenance_date', ''),
            'next_maintenance_date' => (string) $request->query('next_maintenance_date', ''),
        ];

        return view('pages.forms.maintenance-create', [
            'equipments' => $equipmentQuery->get(),
            'prefill' => $prefill,
        ]);
    }

    public function store(StorePreventiveMaintenanceRequest $request)
    {
        $equipmentQuery = Equipment::query()->select('id');
        ServiceAccess::applyEquipmentScope($equipmentQuery, $request->user());
        $isAllowed = (clone $equipmentQuery)
            ->where('id', (int) $request->validated('equipment_id'))
            ->exists();

        if (!$isAllowed) {
            return back()->withInput()->with('error', 'Équipement invalide pour votre périmètre.');
        }

        PreventiveMaintenance::query()->create($request->validated());

        return redirect()
            ->route('maintenance-preventive')
            ->with('success', 'Maintenance préventive ajoutée avec succès.');
    }

    public function edit(PreventiveMaintenance $maintenance_preventive)
    {
        $equipmentQuery = Equipment::query()
            ->select('id', 'inventory_number_current', 'designation')
            ->orderBy('designation');
        ServiceAccess::applyEquipmentScope($equipmentQuery, auth()->user());

        return view('pages.forms.maintenance-edit', [
            'maintenance' => $maintenance_preventive,
            'equipments' => $equipmentQuery->get(),
        ]);
    }

    public function update(UpdatePreventiveMaintenanceRequest $request, PreventiveMaintenance $maintenance_preventive)
    {
        $equipmentQuery = Equipment::query()->select('id');
        ServiceAccess::applyEquipmentScope($equipmentQuery, $request->user());
        $isAllowed = (clone $equipmentQuery)
            ->where('id', (int) $request->validated('equipment_id'))
            ->exists();

        if (!$isAllowed) {
            return back()->withInput()->with('error', 'Équipement invalide pour votre périmètre.');
        }

        $maintenance_preventive->update($request->validated());

        return redirect()
            ->route('maintenance-preventive')
            ->with('success', 'Maintenance préventive modifiée avec succès.');
    }

    public function destroy(PreventiveMaintenance $maintenance_preventive)
    {
        $maintenance_preventive->delete();

        return redirect()
            ->route('maintenance-preventive')
            ->with('success', 'Maintenance préventive supprimée avec succès.');
    }
}
