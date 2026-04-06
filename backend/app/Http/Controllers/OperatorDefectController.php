<?php

namespace App\Http\Controllers;

use App\Events\ComplaintCreated;
use App\Models\Complaint;
use App\Models\Equipment;
use App\Models\Service;
use App\Support\ServiceAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OperatorDefectController extends Controller
{
    public function create(Request $request)
    {
        $scopedEquipmentsQuery = $this->biomedicalEquipmentsQuery($request);

        $visibleServiceIds = (clone $scopedEquipmentsQuery)
            ->select('service_id')
            ->whereNotNull('service_id')
            ->distinct()
            ->pluck('service_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        $services = Service::query()
            ->excludeHiddenForUi()
            ->when(!empty($visibleServiceIds), function ($query) use ($visibleServiceIds) {
                $query->whereIn('id', $visibleServiceIds);
            }, function ($query) {
                $query->whereRaw('1=0');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $equipments = (clone $scopedEquipmentsQuery)
            ->orderBy('designation')
            ->get(['id', 'service_id', 'designation', 'inventory_number_current']);

        return view('pages.operator.defects-create', [
            'services' => $services,
            'equipments' => $equipments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'equipment_id' => ['required', 'integer', 'exists:equipments,id'],
            'room_number' => ['nullable', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:4000'],
            'priority' => ['required', 'in:normal,urgent'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $serviceVisibleInScope = $this->biomedicalEquipmentsQuery($request)
            ->where('service_id', (int) $validated['service_id'])
            ->exists();

        if (!$serviceVisibleInScope) {
            abort(403, 'Service non autorisé pour votre profil.');
        }

        $service = Service::query()->excludeHiddenForUi()->findOrFail((int) $validated['service_id']);

        $equipmentBelongsToService = $this->serviceScopedBiomedicalEquipmentsQuery($service, $request)
            ->where('id', $validated['equipment_id'])
            ->exists();

        if (!$equipmentBelongsToService) {
            return back()->withErrors(['equipment_id' => 'Équipement invalide pour ce service.'])->withInput();
        }

        $user = $request->user();

        $attachments = [];
        foreach ($request->file('attachments', []) as $file) {
            $attachments[] = $file->store('complaints', 'public');
        }

        $complaint = Complaint::query()->create([
            'service_id' => (int) $validated['service_id'],
            'equipment_id' => (int) $validated['equipment_id'],
            'reported_by_name' => $user?->name ?: $user?->login ?: 'Operator',
            'room_number' => $validated['room_number'] ?? null,
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'attachment_path' => $attachments,
        ]);

        $complaint->load(['service:id,code,name', 'equipment:id,service_id,inventory_number_current,designation']);

        try {
            event(new ComplaintCreated($complaint));
        } catch (\Throwable $e) {
            Log::warning('ComplaintCreated event failed in operator defect flow', [
                'complaint_id' => (int) $complaint->id,
                'message' => $e->getMessage(),
            ]);
        }

        return redirect()->route('operator.defects.create')->with('success', 'Défaut enregistré avec succès.');
    }

    public function equipmentsByService(Service $service, Request $request)
    {
        $serviceVisibleInScope = $this->biomedicalEquipmentsQuery($request)
            ->where('service_id', (int) $service->id)
            ->exists();

        if (!$serviceVisibleInScope) {
            abort(403, 'Service non autorisé.');
        }

        $items = $this->serviceScopedBiomedicalEquipmentsQuery($service, $request)
            ->orderBy('designation')
            ->get(['id', 'designation', 'inventory_number_current']);

        if ($items->isEmpty()) {
            $items = collect();
        }

        return response()->json([
            'items' => $items,
        ]);
    }

    private function serviceScopedBiomedicalEquipmentsQuery(Service $service, Request $request)
    {
        return $this->biomedicalEquipmentsQuery($request)
            ->where('service_id', $service->id);
    }

    private function biomedicalEquipmentsQuery(Request $request)
    {
        $query = Equipment::query();
        ServiceAccess::applyEquipmentScope($query, $request->user());

        return $query;
    }
}
