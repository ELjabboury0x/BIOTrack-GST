@extends('layouts.dashboard')

@section('page-title', 'Suivi SAV – Tickets sociétés externes')

@push('styles')
<style>
    .sav-bio-hero {
        background:
            radial-gradient(circle at 10% 20%, rgba(14,165,233,.25), transparent 40%),
            radial-gradient(circle at 85% 20%, rgba(16,185,129,.20), transparent 35%),
            linear-gradient(125deg, #eef7ff 0%, #eafaf6 52%, #f1f5ff 100%);
        border: 1px solid #dbeafe;
    }

    .sav-med-chip {
        backdrop-filter: blur(2px);
    }

    .sav-kpi-card,
    .sav-action-btn,
    .sav-panel,
    .sav-table-row,
    .sav-filter-input {
        transition: all .22s ease;
    }

    .sav-kpi-card:hover,
    .sav-panel:hover {
        transform: translateY(-2px);
    }

    .sav-filter-input:focus {
        transform: translateY(-1px);
    }

    .sav-action-btn:hover {
        transform: translateY(-1px);
    }

    .sav-kpi-card {
        box-shadow: 0 8px 18px -14px rgba(15, 23, 42, .4);
    }

    .sav-panel {
        box-shadow: 0 8px 20px -16px rgba(15, 23, 42, .35);
    }

    .sav-table-row:hover {
        background: #f8fafc;
    }
</style>
@endpush

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'GMAO / SAV Sociétés Externes',
])

@php
    $isMajor = auth()->user()?->role === 'major';
    $totalTickets = (int) ($kpi['total'] ?? 0);
    $openTickets = (int) ($kpi['ouverts'] ?? 0);
    $activeTickets = (int) ($kpi['en_cours'] ?? 0);
    $resolvedTickets = (int) ($kpi['resolus'] ?? 0);
    $resolvedRate = $totalTickets > 0 ? round(($resolvedTickets / $totalTickets) * 100, 1) : 0;

    $companyStats = collect($tickets->items())
        ->groupBy(function ($ticket) {
            return $ticket->company?->name ?: $ticket->equipment?->company?->name ?: 'Non assignée';
        })
        ->map(fn ($rows) => $rows->count())
        ->sortDesc()
        ->take(6);

    $maxCompanyCount = (int) ($companyStats->max() ?? 1);
@endphp

<div class="sav-bio-hero mb-5 rounded-2xl px-5 py-4 text-slate-800 shadow-lg">
    <div class="flex flex-wrap gap-3 items-center justify-between">
        <div>
            <p class="text-lg font-bold text-slate-900">Centre de supervision SAV</p>
        </div>
        <div class="flex flex-col items-end gap-2">
            @if(!$isMajor)
            <a href="{{ route('sav-tickets.create') }}"
               class="sav-action-btn inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 shadow-md hover:shadow-lg">
                <i class="fas fa-plus-circle"></i>
                + Nouveau ticket SAV
            </a>
            @endif
        </div>
    </div>
</div>

{{-- ── KPI Rapides ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
    <div class="sav-kpi-card rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50 to-white px-4 py-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] font-semibold text-blue-700 uppercase tracking-wide">Total tickets</p>
                <p class="text-2xl font-bold text-blue-900 mt-1">{{ $totalTickets }}</p>
                <p class="text-[11px] text-blue-600 mt-1"><i class="fas fa-arrow-trend-up mr-1"></i>Vue globale SAV</p>
            </div>
            <span class="h-9 w-9 rounded-lg bg-blue-100 text-blue-700 inline-flex items-center justify-center">
                <i class="fas fa-ticket-alt"></i>
            </span>
        </div>
    </div>

    <div class="sav-kpi-card rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white px-4 py-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] font-semibold text-amber-700 uppercase tracking-wide">Ouverts</p>
                <p class="text-2xl font-bold text-amber-900 mt-1">{{ $openTickets }}</p>
                <p class="text-[11px] text-amber-600 mt-1"><i class="fas fa-triangle-exclamation mr-1"></i>En attente d'action</p>
            </div>
            <span class="h-9 w-9 rounded-lg bg-amber-100 text-amber-700 inline-flex items-center justify-center">
                <i class="fas fa-folder-open"></i>
            </span>
        </div>
    </div>

    <div class="sav-kpi-card rounded-2xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-white px-4 py-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] font-semibold text-indigo-700 uppercase tracking-wide">En cours</p>
                <p class="text-2xl font-bold text-indigo-900 mt-1">{{ $activeTickets }}</p>
                <p class="text-[11px] text-indigo-600 mt-1"><i class="fas fa-stethoscope mr-1"></i>Interventions actives</p>
            </div>
            <span class="h-9 w-9 rounded-lg bg-indigo-100 text-indigo-700 inline-flex items-center justify-center">
                <i class="fas fa-user-doctor"></i>
            </span>
        </div>
    </div>

    <div class="sav-kpi-card rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white px-4 py-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wide">Résolus</p>
                <p class="text-2xl font-bold text-emerald-900 mt-1">{{ $resolvedTickets }}</p>
                <p class="text-[11px] text-emerald-600 mt-1"><i class="fas fa-arrow-trend-up mr-1"></i>{{ $resolvedRate }}% de résolution</p>
            </div>
            <span class="h-9 w-9 rounded-lg bg-emerald-100 text-emerald-700 inline-flex items-center justify-center">
                <i class="fas fa-check-circle"></i>
            </span>
        </div>
    </div>

    <div class="sav-kpi-card rounded-2xl border border-purple-200 bg-gradient-to-br from-purple-50 to-white px-4 py-4 shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-[11px] font-semibold text-purple-700 uppercase tracking-wide">MTTR moyen</p>
                <p class="text-xl font-bold text-purple-900 mt-1">{{ $kpi['mttr_label'] }}</p>
                <p class="text-[11px] text-purple-600 mt-1"><i class="fas fa-clock mr-1"></i>Performance délai SAV</p>
            </div>
            <span class="h-9 w-9 rounded-lg bg-purple-100 text-purple-700 inline-flex items-center justify-center">
                <i class="fas fa-stopwatch"></i>
            </span>
        </div>
    </div>
</div>

<div class="sav-panel mb-6 bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">
            <i class="fas fa-chart-column text-indigo-500 mr-2"></i>Interventions par société (vue actuelle)
        </h3>
        <span class="text-xs text-slate-400">Top {{ $companyStats->count() }} sociétés</span>
    </div>

    <div class="space-y-3">
        @forelse($companyStats as $company => $count)
            @php
                $width = $maxCompanyCount > 0 ? max(8, (int) round(($count / $maxCompanyCount) * 100)) : 8;
            @endphp
            <div>
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="font-medium text-slate-700 truncate pr-3">{{ $company }}</span>
                    <span class="font-semibold text-indigo-700">{{ $count }}</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-blue-500" style="width: {{ $width }}%"></div>
                </div>
            </div>
        @empty
            <p class="text-sm text-slate-400">Aucune donnée disponible pour la visualisation.</p>
        @endforelse
    </div>
</div>

{{-- ── Filtres ─────────────────────────────────────────────────────────── --}}
<div class="sav-panel mb-5 bg-white rounded-2xl shadow-sm border border-gray-200 p-4">
    <form method="GET" action="{{ route('sav-tickets.index') }}" class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-6 gap-3 items-end">
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Statut</label>
            <select name="status" class="sav-filter-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                <option value="">Tous</option>
                @foreach($statuses as $val => $label)
                    <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Société</label>
            <select name="company_id" class="sav-filter-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                <option value="">Toutes</option>
                @foreach($companies as $c)
                    <option value="{{ $c->id }}" {{ (int)($filters['companyId'] ?? 0) === (int)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Service</label>
            <select name="service_id" class="sav-filter-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                <option value="">Tous</option>
                @foreach($services as $s)
                    <option value="{{ $s->id }}" {{ (int)($filters['serviceId'] ?? 0) === (int)$s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Date début</label>
            <input type="date" name="date_from" value="{{ $filters['dateFrom'] ?? '' }}" class="sav-filter-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Date fin</label>
            <input type="date" name="date_to" value="{{ $filters['dateTo'] ?? '' }}" class="sav-filter-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="sav-action-btn flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">
                <i class="fas fa-search mr-1"></i>Filtrer
            </button>
            <a href="{{ route('sav-tickets.index') }}" class="sav-action-btn px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

{{-- ── Lien rapport --}}
<div class="mb-4 flex justify-end">
    <a href="{{ route('company-performance.index') }}" class="sav-action-btn inline-flex items-center gap-2 px-4 py-2 border border-indigo-200 text-indigo-700 rounded-lg text-sm hover:bg-indigo-50 transition-colors">
        <i class="fas fa-chart-bar"></i> Voir le rapport performance sociétés
    </a>
</div>

{{-- ── Tableau ─────────────────────────────────────────────────────────── --}}
<div class="sav-panel bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600">N° Ticket</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600">Équipement</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600">Service</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600">Société</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600">Timeline intervention</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600">Durée</th>
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600">Statut</th>
                    @if(!$isMajor)
                    <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tickets as $ticket)
                @php
                    $statusDisplay = match($ticket->status ?: $ticket->intervention_status) {
                        'ouvert', 'en_attente'            => ['label' => 'Ouvert',    'class' => 'bg-amber-100 text-amber-700'],
                        'en_cours'                        => ['label' => 'En cours',  'class' => 'bg-indigo-100 text-indigo-700'],
                        'resolu', 'resolved', 'termine'   => ['label' => 'Résolu',    'class' => 'bg-emerald-100 text-emerald-700'],
                        'critique', 'critical'            => ['label' => 'Critique',  'class' => 'bg-red-100 text-red-700'],
                        'ferme'                           => ['label' => 'Fermé',     'class' => 'bg-gray-200 text-gray-600'],
                        default                           => ['label' => $ticket->status ?: '-', 'class' => 'bg-gray-100 text-gray-600'],
                    };

                    $companyName = $ticket->company?->name ?: $ticket->equipment?->company?->name ?: '-';
                    $serviceName = $ticket->service_name ?: $ticket->equipment?->service?->name ?: '-';

                    $durationLabel = '-';
                    if ($ticket->intervention_duration_hours !== null && $ticket->intervention_duration_hours > 0) {
                        $totalMin = (int) round($ticket->intervention_duration_hours * 60);
                        $h = intdiv($totalMin, 60);
                        $m = $totalMin % 60;
                        $durationLabel = $h > 0 ? "{$h}h " . str_pad($m, 2, '0', STR_PAD_LEFT) . 'min' : "{$m}min";
                    } elseif ($ticket->first_call_datetime && $ticket->resolution_datetime) {
                        $mins = $ticket->first_call_datetime->diffInMinutes($ticket->resolution_datetime);
                        $h = intdiv((int)$mins, 60);
                        $m = (int)$mins % 60;
                        $durationLabel = $h > 0 ? "{$h}h " . str_pad($m, 2, '0', STR_PAD_LEFT) . 'min' : "{$m}min";
                    }
                @endphp
                <tr class="sav-table-row hover:bg-slate-50/70 transition-colors">
                    <td class="px-3 py-2.5 font-mono text-xs text-blue-700">
                        {{ $ticket->ticket_number ?: ('SAV-' . $ticket->id) }}
                    </td>
                    <td class="px-3 py-2.5">
                        <p class="font-semibold text-gray-800 text-xs">{{ $ticket->equipment?->designation ?: '-' }}</p>
                        <p class="text-gray-400 text-[11px]">{{ $ticket->equipment?->inventory_number_current ?: '' }}</p>
                    </td>
                    <td class="px-3 py-2.5 text-xs text-gray-700">{{ $serviceName }}</td>
                    <td class="px-3 py-2.5 text-xs font-semibold text-gray-800">{{ $companyName }}</td>
                    <td class="px-3 py-2.5 text-xs text-gray-600 min-w-[320px]">
                        @php
                            $stepFailure = $ticket->failure_datetime ?? $ticket->intervention?->date_start;
                            $stepCall = $ticket->first_call_datetime;
                            $stepArrival = $ticket->arrival_datetime ?? $ticket->technician_arrival_datetime;
                            $stepIntervention = filled($ticket->intervention_description) ? true : false;
                            $stepResolution = $ticket->resolution_datetime;
                        @endphp
                        <div class="flex items-start gap-2">
                            <div class="flex-1">
                                <div class="flex items-center gap-1">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $stepFailure ? 'bg-blue-500' : 'bg-gray-300' }}"></span>
                                    <span class="text-[11px] font-semibold text-blue-700">Panne</span>
                                </div>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ optional($stepFailure)->format('d/m H:i') ?: '-' }}</p>
                            </div>

                            <div class="mt-1 h-px w-5 bg-gray-300"></div>

                            <div class="flex-1">
                                <div class="flex items-center gap-1">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $stepCall ? 'bg-amber-500' : 'bg-gray-300' }}"></span>
                                    <span class="text-[11px] font-semibold text-amber-700">Appel</span>
                                </div>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ optional($stepCall)->format('d/m H:i') ?: '-' }}</p>
                            </div>

                            <div class="mt-1 h-px w-5 bg-gray-300"></div>

                            <div class="flex-1">
                                <div class="flex items-center gap-1">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $stepArrival ? 'bg-violet-500' : 'bg-gray-300' }}"></span>
                                    <span class="text-[11px] font-semibold text-violet-700">Arrivée</span>
                                </div>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ optional($stepArrival)->format('d/m H:i') ?: '-' }}</p>
                            </div>

                            <div class="mt-1 h-px w-5 bg-gray-300"></div>

                            <div class="flex-1">
                                <div class="flex items-center gap-1">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $stepIntervention ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                                    <span class="text-[11px] font-semibold text-emerald-700">Interv.</span>
                                </div>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ $stepIntervention ? 'Effectuée' : '-' }}</p>
                            </div>

                            <div class="mt-1 h-px w-5 bg-gray-300"></div>

                            <div class="flex-1">
                                <div class="flex items-center gap-1">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $stepResolution ? 'bg-green-600' : 'bg-gray-300' }}"></span>
                                    <span class="text-[11px] font-semibold text-green-700">Résol.</span>
                                </div>
                                <p class="text-[11px] text-gray-500 mt-0.5">{{ optional($stepResolution)->format('d/m H:i') ?: '-' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-2.5 text-xs font-semibold text-purple-700">{{ $durationLabel }}</td>
                    <td class="px-3 py-2.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $statusDisplay['class'] }}">
                            {{ $statusDisplay['label'] }}
                        </span>
                    </td>
                    @if(!$isMajor)
                    <td class="px-3 py-2.5">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('sav-tickets.edit', $ticket) }}"
                               class="inline-flex items-center px-3 py-1.5 text-xs border border-blue-200 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors">
                                <i class="fas fa-edit mr-1"></i>Éditer
                            </a>
                            <form method="POST" action="{{ route('sav-tickets.destroy', $ticket) }}"
                                  onsubmit="return confirm('Supprimer ce ticket SAV ?');">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center px-3 py-1.5 text-xs border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isMajor ? 7 : 8 }}" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-ticket-alt text-3xl mb-3 block"></i>
                            Aucun ticket SAV trouvé. Créez-en un ou attendez la détection automatique de pannes sur équipements sous contrat.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tickets->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $tickets->links() }}
        </div>
    @endif
</div>

@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
     class="fixed bottom-5 right-5 z-50 bg-emerald-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm">
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif
@endsection
