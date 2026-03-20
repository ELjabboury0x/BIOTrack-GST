<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Models\Zone;
use App\Services\AppSettingsService;

class ZoneController extends Controller
{
    public function index()
    {
        $perPage = max(5, min(200, app(AppSettingsService::class)->int('items_per_page', 15)));

        $zones = Zone::query()
            ->withCount('services')
            ->orderBy('name')
            ->paginate($perPage);

        $zones->appends(request()->query());

        return view('pages.zones.index', [
            'zones' => $zones,
        ]);
    }

    public function create()
    {
        return view('pages.zones.create');
    }

    public function store(StoreZoneRequest $request)
    {
        Zone::create($request->validated());

        return redirect()
            ->route('zones.index')
            ->with('success', 'Zone créée avec succès.');
    }

    public function edit(Zone $zone)
    {
        return view('pages.zones.edit', [
            'zone' => $zone,
        ]);
    }

    public function update(UpdateZoneRequest $request, Zone $zone)
    {
        $zone->update($request->validated());

        return redirect()
            ->route('zones.index')
            ->with('success', 'Zone mise à jour avec succès.');
    }

    public function destroy(Zone $zone)
    {
        if ($zone->services()->exists()) {
            return redirect()
                ->route('zones.index')
                ->with('error', 'Impossible de supprimer une zone contenant des services.');
        }

        $zone->delete();

        return redirect()
            ->route('zones.index')
            ->with('success', 'Zone supprimée avec succès.');
    }
}
