<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use App\Models\Zone;
use App\Services\AppSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $serviceFilter = trim((string) $request->query('service', ''));
        $perPage = max(5, min(200, app(AppSettingsService::class)->int('items_per_page', 20)));

        $services = Service::query()
            ->excludeHiddenForUi()
            ->when($serviceFilter !== '', function ($query) use ($serviceFilter) {
                $like = '%' . Str::lower($serviceFilter) . '%';
                $query->where(function ($innerQuery) use ($like) {
                    $innerQuery
                        ->whereRaw('LOWER(name) like ?', [$like])
                        ->orWhereRaw('LOWER(code) like ?', [$like]);
                });
            })
            ->orderBy('code')
            ->paginate($perPage);

        $services->appends($request->query());

        return view('pages.services.index', [
            'services' => $services,
            'serviceFilter' => $serviceFilter,
        ]);
    }

    public function create()
    {
        return view('pages.services.create');
    }

    public function store(StoreServiceRequest $request)
    {
        $payload = $request->validated();
        $payload['zone_id'] = (int) ($payload['zone_id'] ?? 0) ?: $this->defaultZoneId();

        Service::create($payload);

        return redirect()
            ->route('services.index')
            ->with('success', 'Service créé avec succès.');
    }

    public function edit(Service $service)
    {
        return view('pages.services.edit', [
            'service' => $service,
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        $payload = $request->validated();
        $payload['zone_id'] = (int) ($payload['zone_id'] ?? 0) ?: ((int) $service->zone_id ?: $this->defaultZoneId());

        $service->update($payload);

        return redirect()
            ->route('services.index')
            ->with('success', 'Service mis à jour avec succès.');
    }

    public function destroy(Service $service)
    {
        if ($service->equipments()->exists() || $service->rooms()->exists()) {
            return redirect()
                ->route('services.index')
                ->with('error', 'Impossible de supprimer un service lié à des équipements ou des salles.');
        }

        $service->delete();

        return redirect()
            ->route('services.index')
            ->with('success', 'Service supprimé avec succès.');
    }

    private function defaultZoneId(): int
    {
        $zone = Zone::query()->firstOrCreate(
            ['name' => 'Non classé'],
            ['description' => 'Zone interne utilisée automatiquement.']
        );

        return (int) $zone->id;
    }
}
