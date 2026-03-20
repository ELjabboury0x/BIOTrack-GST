@extends('layouts.dashboard')

@section('page-title', 'Rapport des interventions des sociétés externes')

@push('styles')
<style>
    .cp-card {
        transition: transform .18s ease, box-shadow .18s ease;
    }
    .cp-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px -12px rgba(15, 23, 42, .35);
    }
    .cp-input {
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .cp-input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, .14);
        outline: none;
    }
    .cp-table-row {
        transition: background-color .15s ease;
    }
    .cp-table-row:hover {
        background: #f8fafc;
    }
    .cp-canvas-wrap {
        position: relative;
        height: 260px;
    }
</style>
@endpush

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Rapports / Interventions sociétés externes',
])

@if(!$tableAvailable)
    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-700">
        Table <strong>external_interventions</strong> introuvable. Exécutez les migrations pour activer ce rapport.
    </div>
@endif

<div class="mb-4 rounded-2xl bg-gradient-to-r from-slate-900 via-blue-900 to-indigo-900 p-5 text-white shadow-xl">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.2em] text-blue-100">Centre analytique SAV</p>
            <h2 class="text-xl font-bold">Performance des sociétés externes</h2>
            <p class="text-sm text-blue-100 mt-1">Vue comparative des délais d’intervention, résolution et fiabilité.</p>
        </div>
        <div class="inline-flex items-center gap-2 rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-xs">
            <i class="fas fa-chart-line"></i>
            <span>Suivi opérationnel multi-indicateurs</span>
        </div>
    </div>
</div>

<div class="mb-4 bg-white rounded-2xl shadow-md p-4 cp-card border border-slate-100">
    <form method="GET" action="{{ route('company-performance.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Société</label>
            <select name="company_id" class="w-full px-4 py-2 border border-gray-300 rounded-xl bg-white cp-input">
                <option value="">Toutes les sociétés</option>
                @foreach(($companies ?? collect()) as $company)
                    <option value="{{ $company->id }}" {{ (int) ($filters['company_id'] ?? 0) === (int) $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Service</label>
            <select name="service_id" class="w-full px-4 py-2 border border-gray-300 rounded-xl bg-white cp-input">
                <option value="">Tous les services</option>
                @foreach(($services ?? collect()) as $service)
                    <option value="{{ $service->id }}" {{ (int) ($filters['service_id'] ?? 0) === (int) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Équipement</label>
            <select name="equipment_id" class="w-full px-4 py-2 border border-gray-300 rounded-xl bg-white cp-input">
                <option value="">Tous les équipements</option>
                @foreach(($equipments ?? collect()) as $equipment)
                    <option value="{{ $equipment->id }}" {{ (int) ($filters['equipment_id'] ?? 0) === (int) $equipment->id ? 'selected' : '' }}>
                        {{ ($equipment->inventory_number_current ?: '-') . ' - ' . ($equipment->designation ?: '-') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date début</label>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-xl cp-input">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date fin</label>
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-xl cp-input">
        </div>

        <div class="md:col-span-5 flex flex-wrap gap-2">
            <button class="px-5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold shadow-sm hover:shadow-md transition">
                <i class="fas fa-filter mr-2"></i>Filtrer
            </button>
            <a href="{{ route('company-performance.index') }}" class="px-5 py-2 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition">Réinitialiser</a>
            <a href="{{ route('company-performance.export-excel', request()->query()) }}" class="px-5 py-2 border border-emerald-200 text-emerald-700 rounded-xl hover:bg-emerald-50 transition">
                <i class="fas fa-file-excel mr-2"></i>Exporter Excel
            </a>
            <a href="{{ route('company-performance.export-pdf', request()->query()) }}" class="px-5 py-2 border border-red-200 text-red-700 rounded-xl hover:bg-red-50 transition">
                <i class="fas fa-file-pdf mr-2"></i>Exporter PDF
            </a>
        </div>
    </form>
</div>

@php
    $totalInterventions = collect($kpis ?? [])->sum('total_interventions');
    $averageMttr = collect($kpis ?? [])->filter(fn($item) => $item['mttr_minutes'] !== null)->avg('mttr_minutes');
    $averageResolution = collect($kpis ?? [])->avg('resolution_rate');
    $repeatFailuresTotal = collect($kpis ?? [])->sum('repeat_failures_count');
    $averageAvailability = collect($kpis ?? [])->avg('equipment_availability_rate');
@endphp

<div class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-3">
    <div class="rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50 to-white px-4 py-3 cp-card">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Total interventions</p>
            <i class="fas fa-ticket-alt text-blue-500"></i>
        </div>
        <p class="text-2xl font-bold text-blue-900 mt-1">{{ (int) $totalInterventions }}</p>
    </div>
    <div class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white px-4 py-3 cp-card">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide">MTTR sociétés</p>
            <i class="fas fa-stopwatch text-amber-500"></i>
        </div>
        <p class="text-2xl font-bold text-amber-900 mt-1">
            @if($averageMttr !== null)
                {{ intval($averageMttr / 60) }} h {{ str_pad((string) (intval($averageMttr) % 60), 2, '0', STR_PAD_LEFT) }} min
            @else
                -
            @endif
        </p>
    </div>
    <div class="rounded-2xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-white px-4 py-3 cp-card">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wide">Pannes par société</p>
            <i class="fas fa-tools text-indigo-500"></i>
        </div>
        <p class="text-2xl font-bold text-indigo-900 mt-1">{{ collect($kpis ?? [])->sum('pannes_count') }}</p>
    </div>
    <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white px-4 py-3 cp-card">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Taux de résolution moyen</p>
            <i class="fas fa-check-circle text-emerald-500"></i>
        </div>
        <p class="text-2xl font-bold text-emerald-900 mt-1">{{ round((float) $averageResolution, 2) }}%</p>
    </div>
    <div class="rounded-2xl border border-rose-200 bg-gradient-to-br from-rose-50 to-white px-4 py-3 cp-card">
        <div class="flex items-center justify-between">
            <p class="text-xs font-semibold text-rose-700 uppercase tracking-wide">Pannes répétées</p>
            <i class="fas fa-sync-alt text-rose-500"></i>
        </div>
        <p class="text-2xl font-bold text-rose-900 mt-1">{{ (int) $repeatFailuresTotal }}</p>
        <p class="text-[11px] text-rose-700 mt-1">Disponibilité liée: {{ round((float) $averageAvailability, 2) }}%</p>
    </div>
</div>

<div class="mb-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-2xl shadow-md p-4 cp-card border border-slate-100">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Interventions par société</h3>
        <div class="cp-canvas-wrap">
            <canvas id="interventionsByCompanyChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-4 cp-card border border-slate-100">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">MTTR par société (heures)</h3>
        <div class="cp-canvas-wrap">
            <canvas id="mttrByCompanyChart"></canvas>
        </div>
    </div>
</div>

<div class="mb-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="bg-white rounded-2xl shadow-md p-4 cp-card border border-slate-100">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Top sociétés les plus rapides</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border border-slate-200">
                    <tr>
                        <th class="px-3 py-2 text-left">Société</th>
                        <th class="px-3 py-2 text-left">MTTR</th>
                        <th class="px-3 py-2 text-left">Interventions</th>
                        <th class="px-3 py-2 text-left">Taux résolution</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($topFastest ?? []) as $item)
                        <tr class="border-b border-gray-200 cp-table-row">
                            <td class="px-3 py-2">{{ $item['company_name'] }}</td>
                            <td class="px-3 py-2">{{ $item['mttr_label'] }}</td>
                            <td class="px-3 py-2">{{ $item['total_interventions'] }}</td>
                            <td class="px-3 py-2">{{ $item['resolution_rate'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-4 text-center text-gray-500">Aucune donnée disponible.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-md p-4 cp-card border border-slate-100">
        <h3 class="text-sm font-semibold text-gray-800 mb-3">Sociétés avec le plus de pannes</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border border-slate-200">
                    <tr>
                        <th class="px-3 py-2 text-left">Société</th>
                        <th class="px-3 py-2 text-left">Pannes</th>
                        <th class="px-3 py-2 text-left">Pannes répétées</th>
                        <th class="px-3 py-2 text-left">Disponibilité équipements</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($topFailures ?? []) as $item)
                        <tr class="border-b border-gray-200 cp-table-row">
                            <td class="px-3 py-2">{{ $item['company_name'] }}</td>
                            <td class="px-3 py-2">{{ $item['pannes_count'] }}</td>
                            <td class="px-3 py-2">{{ $item['repeat_failures_count'] }}</td>
                            <td class="px-3 py-2">{{ number_format((float) $item['equipment_availability_rate'], 2) }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-4 text-center text-gray-500">Aucune donnée disponible.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-md p-4 cp-card border border-slate-100">
    <h3 class="text-sm font-semibold text-gray-800 mb-3">Tableau complet des interventions</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border border-slate-200">
                <tr>
                    <th class="px-3 py-2 text-left">Ticket</th>
                    <th class="px-3 py-2 text-left">Équipement</th>
                    <th class="px-3 py-2 text-left">Service</th>
                    <th class="px-3 py-2 text-left">Société</th>
                    <th class="px-3 py-2 text-left">Date panne</th>
                    <th class="px-3 py-2 text-left">Premier appel</th>
                    <th class="px-3 py-2 text-left">Résolution</th>
                    <th class="px-3 py-2 text-left">Temps intervention</th>
                    <th class="px-3 py-2 text-left">Statut</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($rows ?? []) as $row)
                    <tr class="border-b border-gray-200 cp-table-row">
                        <td class="px-3 py-2">{{ $row['ticket_id'] }}</td>
                        <td class="px-3 py-2">{{ $row['equipment'] }}</td>
                        <td class="px-3 py-2">{{ $row['hospital_service'] }}</td>
                        <td class="px-3 py-2">{{ $row['external_company'] }}</td>
                        <td class="px-3 py-2">{{ $row['breakdown_date'] }}</td>
                        <td class="px-3 py-2">{{ $row['first_call'] }}</td>
                        <td class="px-3 py-2">{{ $row['resolution_date'] }}</td>
                        <td class="px-3 py-2">{{ $row['intervention_time'] }}</td>
                        <td class="px-3 py-2">{{ $row['intervention_status'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-3 py-4 text-center text-gray-500">Aucune intervention trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($rows instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-4">
            {{ $rows->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    (function () {
        const interventionsChart = document.getElementById('interventionsByCompanyChart');
        const mttrChart = document.getElementById('mttrByCompanyChart');

        const interventionsData = @json($chartInterventions ?? ['labels' => [], 'values' => []]);
        const mttrData = @json($chartMttr ?? ['labels' => [], 'values' => []]);

        if (interventionsChart && interventionsData.labels.length > 0) {
            new Chart(interventionsChart, {
                type: 'bar',
                data: {
                    labels: interventionsData.labels,
                    datasets: [{
                        label: 'Interventions',
                        data: interventionsData.values,
                        backgroundColor: 'rgba(59,130,246,0.7)',
                        borderColor: '#2563eb',
                        borderWidth: 1,
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        if (mttrChart && mttrData.labels.length > 0) {
            new Chart(mttrChart, {
                type: 'bar',
                data: {
                    labels: mttrData.labels,
                    datasets: [{
                        label: 'MTTR (h)',
                        data: mttrData.values,
                        backgroundColor: 'rgba(16,185,129,0.7)',
                        borderColor: '#059669',
                        borderWidth: 1,
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    })();
</script>
@endsection
