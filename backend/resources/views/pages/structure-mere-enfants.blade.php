@extends('layouts.dashboard')

@section('page-title', 'Structure Mère-Enfants')

@section('content')
    @include('components.module-page-header', [
        'breadcrumb' => 'Organisation / Structure Mère-Enfants',
    ])

    <section class="space-y-4">
        <div class="rounded-xl border border-gray-200 bg-white p-4">
            <h1 class="text-xl font-bold text-gray-900">{{ $building?->name ?? 'Hôpital Mère-Enfants' }}</h1>
            <p class="mt-1 text-sm text-gray-600">Structure officielle fixe: Building → Floors → Services</p>
            <div class="mt-3 flex flex-wrap gap-2 text-sm">
                <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700">{{ (int) ($totals['equipments'] ?? 0) }} équipements</span>
                <span class="rounded-full {{ (int) ($totals['breakdowns'] ?? 0) > 0 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }} px-3 py-1">
                    {{ (int) ($totals['breakdowns'] ?? 0) }} pannes
                </span>
            </div>
        </div>

        @forelse($floors as $floor)
            <div class="rounded-xl border border-gray-200 bg-white p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $floor['name'] }}</h2>
                    <div class="flex gap-2 text-xs md:text-sm">
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700">{{ (int) $floor['equipments_total'] }} équipements</span>
                        <span class="rounded-full {{ (int) $floor['breakdowns_total'] > 0 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }} px-3 py-1">
                            {{ (int) $floor['breakdowns_total'] }} pannes
                        </span>
                    </div>
                </div>

                <ul class="mt-3 space-y-2">
                    @foreach($floor['services'] as $service)
                        <li class="rounded-lg border border-gray-100 bg-gray-50 p-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $service['name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $service['code'] ?: 'Code non défini' }}</p>
                                </div>
                                <div class="flex flex-wrap gap-2 text-xs md:text-sm">
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-blue-700">{{ (int) $service['equipments_count'] }} équipements</span>
                                    <span class="rounded-full {{ (int) $service['breakdowns_count'] > 0 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }} px-3 py-1">
                                        {{ (int) $service['breakdowns_count'] }} pannes
                                    </span>
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">
                                        {{ $service['availability'] === null ? 'Disponibilité -' : ('Disponibilité ' . (int) $service['availability'] . '%') }}
                                    </span>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @empty
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                Aucune structure Mère-Enfants trouvée. Lancez le seeder dédié pour initialiser la structure fixe.
            </div>
        @endforelse
    </section>
@endsection
