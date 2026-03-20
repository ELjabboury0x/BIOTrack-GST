@extends('layouts.dashboard')

@php
    $isEdit = $ticket !== null;
    $pageTitle = $isEdit ? 'Modifier ticket SAV' : 'Nouveau ticket SAV';
@endphp

@section('page-title', $pageTitle)

@push('styles')
<style>
    .sav-input {
        transition: all .2s ease;
    }
    .sav-input:focus {
        transform: translateY(-1px);
    }
</style>
@endpush

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'GMAO / SAV Sociétés Externes / ' . ($isEdit ? 'Modifier' : 'Créer'),
])

<div class="mb-4">
    <a href="{{ route('sav-tickets.index') }}"
       class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
        <i class="fas fa-arrow-left mr-2"></i>Retour à la liste
    </a>
</div>

<form method="POST"
      action="{{ $isEdit ? route('sav-tickets.update', $ticket) : route('sav-tickets.store') }}"
      class="space-y-8">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Timeline d'intervention SAV externe</h2>
        <p class="text-sm text-slate-500 mt-1">Suivez les étapes de la panne à la résolution avec une saisie chronologique.</p>
    </div>

    <div class="relative">
        <div class="hidden md:block absolute left-6 top-3 bottom-3 w-0.5 bg-slate-200"></div>
        <div class="space-y-6">

    <div class="relative md:pl-16">
        <div class="hidden md:flex absolute left-0 top-2 h-12 w-12 rounded-xl bg-blue-100 text-blue-700 items-center justify-center shadow-sm">
            <i class="fas fa-heartbeat"></i>
        </div>
        <div class="bg-white rounded-2xl shadow-md border border-blue-200 p-6">
            <h2 class="text-base font-bold text-blue-800 mb-4 flex items-center gap-2">
                <i class="fas fa-circle-exclamation text-blue-500"></i>
                1. Détection de panne
            </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Équipement <span class="text-red-500">*</span>
                </label>
                <select name="equipment_id" required
                    class="sav-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400 @error('equipment_id') border-red-400 @enderror">
                    <option value="">— Sélectionner —</option>
                    @foreach($equipments as $eq)
                        <option value="{{ $eq->id }}"
                            {{ old('equipment_id', $ticket?->equipment_id) == $eq->id ? 'selected' : '' }}>
                            {{ $eq->designation ?: '-' }}
                            @if($eq->inventory_number_current) ({{ $eq->inventory_number_current }}) @endif
                        </option>
                    @endforeach
                </select>
                @error('equipment_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Société externe</label>
                <select name="company_id"
                    class="sav-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                    <option value="">— Sélectionner —</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}"
                            {{ old('company_id', $ticket?->company_id) == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                @php
                    $selectedServiceName = old('service_name', $ticket?->service_name ?? $ticket?->equipment?->service?->name);
                    $serviceExistsInList = collect($services ?? [])->contains(fn($srv) => (string) $srv->name === (string) $selectedServiceName);
                @endphp
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    <i class="fas fa-hospital-symbol text-blue-500 mr-1"></i>
                    Service hospitalier
                </label>
                <select name="service_name"
                    class="sav-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                    <option value="">— Sélectionner un service —</option>
                    @if(filled($selectedServiceName) && !$serviceExistsInList)
                        <option value="{{ $selectedServiceName }}" selected>{{ $selectedServiceName }} (personnalisé)</option>
                    @endif
                    @foreach($services as $service)
                        <option value="{{ $service->name }}" {{ (string) $selectedServiceName === (string) $service->name ? 'selected' : '' }}>
                            🏥 {{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Date / heure de détection de la panne</label>
                <input type="datetime-local" name="failure_datetime"
                       value="{{ old('failure_datetime', optional($ticket?->failure_datetime)->format('Y-m-d\TH:i')) }}"
                      class="sav-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">OT/DM lié (optionnel)</label>
                <select name="intervention_id"
                    class="sav-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                    <option value="">— Créer automatiquement —</option>
                    @foreach($interventions as $i)
                        <option value="{{ $i->id }}"
                            {{ old('intervention_id', $ticket?->intervention_id) == $i->id ? 'selected' : '' }}>
                            {{ $i->code }} ({{ optional($i->date_start)->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Statut du ticket</label>
                <select name="status"
                    class="sav-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                    @foreach($statuses as $val => $label)
                        <option value="{{ $val }}"
                            {{ old('status', $ticket?->status ?? $ticket?->intervention_status ?? 'ouvert') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
        </div>
        </div>
    </div>

    <div class="relative md:pl-16">
        <div class="hidden md:flex absolute left-0 top-2 h-12 w-12 rounded-xl bg-amber-100 text-amber-700 items-center justify-center shadow-sm">
            <i class="fas fa-phone"></i>
        </div>
        <div class="bg-white rounded-2xl shadow-md border border-amber-200 p-6">
            <h2 class="text-base font-bold text-amber-800 mb-4 flex items-center gap-2">
                <i class="fas fa-phone-alt text-amber-500"></i>
                2. Premier appel
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    <i class="fas fa-phone-alt text-amber-500 mr-1"></i>
                    Premier appel à la société
                </label>
                <input type="datetime-local" name="first_call_datetime"
                       value="{{ old('first_call_datetime', optional($ticket?->first_call_datetime)->format('Y-m-d\TH:i')) }}"
                       class="sav-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-200 focus:border-amber-400">
            </div>
            </div>
        </div>
    </div>

    <div class="relative md:pl-16">
        <div class="hidden md:flex absolute left-0 top-2 h-12 w-12 rounded-xl bg-violet-100 text-violet-700 items-center justify-center shadow-sm">
            <i class="fas fa-user-hard-hat"></i>
        </div>
        <div class="bg-white rounded-2xl shadow-md border border-violet-200 p-6">
            <h2 class="text-base font-bold text-violet-800 mb-4 flex items-center gap-2">
                <i class="fas fa-user-hard-hat text-violet-500"></i>
                3. Arrivée technicien
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    <i class="fas fa-user-hard-hat text-indigo-500 mr-1"></i>
                    Arrivée du technicien
                </label>
                <input type="datetime-local" name="arrival_datetime"
                       value="{{ old('arrival_datetime', optional($ticket?->arrival_datetime ?? $ticket?->technician_arrival_datetime)->format('Y-m-d\TH:i')) }}"
                       class="sav-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-violet-200 focus:border-violet-400">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nom du technicien société</label>
                <input type="text" name="technician_name"
                       value="{{ old('technician_name', $ticket?->technician_name) }}"
                       placeholder="Prénom Nom"
                       class="sav-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-violet-200 focus:border-violet-400">
            </div>
            </div>
        </div>
    </div>

    <div class="relative md:pl-16">
        <div class="hidden md:flex absolute left-0 top-2 h-12 w-12 rounded-xl bg-emerald-100 text-emerald-700 items-center justify-center shadow-sm">
            <i class="fas fa-screwdriver-wrench"></i>
        </div>
        <div class="bg-white rounded-2xl shadow-md border border-emerald-200 p-6">
            <h2 class="text-base font-bold text-emerald-800 mb-4 flex items-center gap-2">
                <i class="fas fa-wrench text-emerald-500"></i>
                4. Intervention société et résolution
            </h2>

            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Description de l'intervention</label>
                    <textarea name="intervention_description" rows="3"
                              placeholder="Décrire les opérations effectuées par la société..."
                              class="sav-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400">{{ old('intervention_description', $ticket?->intervention_description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Pièces remplacées</label>
                    <textarea name="replaced_parts" rows="2"
                              placeholder="ex: Carte mère, câble d'alimentation..."
                              class="sav-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-400">{{ old('replaced_parts', $ticket?->replaced_parts) }}</textarea>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100">
                <h3 class="text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                    Résolution de la panne
                </h3>
                <p class="text-xs text-gray-500 mb-4">
                    Le calcul automatique du temps d'intervention =
                    <strong>résolution − premier appel</strong>.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <div class="border-l-4 border-emerald-400 pl-3">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            <i class="fas fa-flag-checkered text-emerald-500 mr-1"></i>
                            Date / heure de résolution
                        </label>
                        <input type="datetime-local" name="resolution_datetime"
                               value="{{ old('resolution_datetime', optional($ticket?->resolution_datetime)->format('Y-m-d\TH:i')) }}"
                               class="sav-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-slate-200 focus:border-slate-400">
                    </div>

                    @if($isEdit && $ticket->first_call_datetime && $ticket->resolution_datetime)
                    <div class="rounded-lg bg-purple-50 border border-purple-200 px-4 py-3 flex flex-col justify-center">
                        <p class="text-xs font-semibold text-purple-700 uppercase tracking-wide">Temps d'intervention calculé</p>
                        @php
                            $mins = $ticket->first_call_datetime->diffInMinutes($ticket->resolution_datetime);
                            $h = intdiv((int)$mins, 60);
                            $m = (int)$mins % 60;
                            $durationLabel = $h > 0 ? "{$h} h " . str_pad($m, 2, '0', STR_PAD_LEFT) . " min" : "{$m} min";
                        @endphp
                        <p class="text-2xl font-bold text-purple-900 mt-1">{{ $durationLabel }}</p>
                    </div>
                    @endif
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Notes internes</label>
                    <textarea name="notes" rows="2"
                              placeholder="Observations, remarques internes..."
                              class="sav-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-slate-200 focus:border-slate-400">{{ old('notes', $ticket?->notes) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    </div>
    </div>

    {{-- ── Historique des changements (edit uniquement) ─────────────────── --}}
    @if($isEdit && $ticket->logs && $ticket->logs->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-history text-gray-500"></i>
            Historique du ticket
        </h2>
        <div class="space-y-2">
            @foreach($ticket->logs as $log)
            <div class="flex items-start gap-3 text-sm">
                <span class="mt-0.5 w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></span>
                <div>
                    <span class="font-semibold text-gray-700">{{ $log->action_type }}</span>
                    @if($log->from_status || $log->to_status)
                        <span class="text-gray-500">
                            {{ $log->from_status ? '(' . $log->from_status . ' → ' . $log->to_status . ')' : '→ ' . $log->to_status }}
                        </span>
                    @endif
                    <span class="text-gray-400 text-xs ml-2">
                        {{ optional($log->logged_at)->format('d/m/Y H:i') }}
                        @if($log->user) · {{ $log->user->name }} @endif
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Boutons ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap gap-3">
        <button type="submit"
                class="px-6 py-2.5 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-colors shadow-sm">
            <i class="fas fa-save mr-2"></i>{{ $isEdit ? 'Enregistrer les modifications' : 'Créer le ticket SAV' }}
        </button>
        <a href="{{ route('sav-tickets.index') }}"
           class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
            Annuler
        </a>
    </div>

</form>
@endsection
