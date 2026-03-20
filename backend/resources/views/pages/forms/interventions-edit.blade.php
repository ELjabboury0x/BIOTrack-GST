@extends('layouts.dashboard')

@section('page-title', 'Modifier intervention')

@section('content')
@php
    $currentUser = auth()->user();
    $isTechnician = in_array((string) ($currentUser?->role ?? ''), ['technicien', 'technician'], true);
    $defaultTechnicianName = old('technician_name', $isTechnician ? (string) ($currentUser?->name ?? $currentUser?->login ?? '') : (string) ($intervention->technician_name ?? ''));
    $equipmentOptions = collect($equipments ?? [])->filter()->map(function ($equipment) {
        $inv = trim((string) data_get($equipment, 'inventory_number_current'));
        $name = trim((string) data_get($equipment, 'designation'));
        $label = trim($inv . ' - ' . $name, ' -');

        return [
            'id' => (int) data_get($equipment, 'id'),
            'label' => $label !== '' ? $label : ('Équipement #' . data_get($equipment, 'id')),
            'icon_class' => trim((string) data_get($equipment, 'icon_class')) ?: 'fas fa-stethoscope',
        ];
    })->values();
    $selectedEquipmentId = (int) old('equipment_id', (int) ($intervention->equipment_id ?? 0));
    $hasExternalTicket = (int) ($intervention->externalIntervention?->id ?? 0) > 0;
    $externalTicketId = (int) ($intervention->externalIntervention?->id ?? 0);
    $externalTicketNumber = trim((string) ($intervention->externalIntervention?->ticket_number ?? ''));
@endphp
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-md p-8">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Modifier Intervention <span class="text-blue-600">{{ $intervention->code }}</span></h2>
    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="grid grid-cols-1 md:grid-cols-2 gap-6" method="POST" action="{{ route('interventions.update', $intervention->id) }}">
        @csrf
        @method('PUT')

        <input value="{{ $intervention->code }}" class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600" readonly>

        <div class="relative">
            <input type="hidden" name="equipment_id" id="equipment_id_edit" value="{{ $selectedEquipmentId > 0 ? $selectedEquipmentId : '' }}" required>
            <div class="flex items-center gap-2">
                <input type="text" id="equipment_search_edit" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Sélectionner un équipement" autocomplete="off" required>
                <div id="equipment_icon_preview" class="w-10 h-10 rounded-lg border border-gray-300 bg-gray-50 flex items-center justify-center text-blue-600">
                    <i id="equipment_icon_preview_i" class="fas fa-stethoscope"></i>
                </div>
            </div>
            <div id="equipment_results_edit" class="hidden absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-72 overflow-y-auto z-30"></div>
        </div>

        <input name="technician_name" value="{{ $defaultTechnicianName }}" placeholder="Technicien" class="px-4 py-2 border border-gray-300 rounded-lg {{ $isTechnician ? 'bg-gray-100 text-gray-600' : '' }}" {{ $isTechnician ? 'readonly' : '' }}>

        <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg" required>
            @foreach (['Préventive','Curative','Corrective','Prédictive','Améliorative','Systématique','Urgente'] as $type)
                <option value="{{ $type }}" {{ old('type', $intervention->type) === $type ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
        </select>

        <select id="maintenance_scope_edit" name="maintenance_scope" class="px-4 py-2 border border-gray-300 rounded-lg" required>
            <option value="interne" {{ old('maintenance_scope', $intervention->maintenance_scope) === 'interne' ? 'selected' : '' }}>Interne</option>
            <option value="externe" {{ old('maintenance_scope', $intervention->maintenance_scope) === 'externe' ? 'selected' : '' }}>Externe</option>
        </select>

        <select name="statut" class="px-4 py-2 border border-gray-300 rounded-lg" required>
            <option value="en_attente" {{ old('statut', $intervention->status) === 'en_attente' ? 'selected' : '' }}>En attente</option>
            <option value="en_cours" {{ old('statut', $intervention->status) === 'en_cours' ? 'selected' : '' }}>En cours</option>
            <option value="termine" {{ old('statut', $intervention->status) === 'termine' ? 'selected' : '' }}>Terminé</option>
        </select>

        <input type="date" name="date_start" value="{{ old('date_start', optional($intervention->date_start)->toDateString()) }}" class="px-4 py-2 border border-gray-300 rounded-lg">

        <div id="external_call_box_edit" class="md:col-span-2 hidden rounded-lg border border-blue-200 bg-blue-50 px-4 py-3">
            @if($hasExternalTicket)
                <p class="text-sm font-semibold text-blue-900">Ticket SAV déjà lié à cette intervention.</p>
                <p class="mt-1 text-xs text-blue-700">
                    Ticket: {{ $externalTicketNumber !== '' ? $externalTicketNumber : ('#' . $externalTicketId) }}
                    <a href="{{ route('sav-tickets.edit', $externalTicketId) }}" class="font-semibold underline">(ouvrir)</a>
                </p>
            @else
                <label class="inline-flex items-center gap-2 text-sm font-semibold text-blue-900">
                    <input type="checkbox" name="external_call_made" value="1" {{ old('external_call_made') ? 'checked' : '' }} class="rounded border-blue-300 text-blue-600 focus:ring-blue-500">
                    Appel effectué à la société externe (créer automatiquement un ticket SAV)
                </label>
                <p class="mt-1 text-xs text-blue-700">Si coché, un ticket SAV externe est ouvert automatiquement à la mise à jour.</p>
            @endif
        </div>

        <div class="md:col-span-2 flex justify-end gap-3">
            <a href="{{ route('interventions') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Annuler</a>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg">Mettre à jour</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const options = @json($equipmentOptions);
    const form = document.querySelector('form[action="{{ route('interventions.update', $intervention->id) }}"]');
    const hiddenInput = document.getElementById('equipment_id_edit');
    const searchInput = document.getElementById('equipment_search_edit');
    const resultsBox = document.getElementById('equipment_results_edit');
    const previewIcon = document.getElementById('equipment_icon_preview_i');
    const maintenanceScopeSelect = document.getElementById('maintenance_scope_edit');
    const externalCallBox = document.getElementById('external_call_box_edit');

    if (!form || !hiddenInput || !searchInput || !resultsBox || !previewIcon || !maintenanceScopeSelect || !externalCallBox) {
        return;
    }

    function toggleExternalCallOption() {
        const isExternal = maintenanceScopeSelect.value === 'externe';
        externalCallBox.classList.toggle('hidden', !isExternal);
    }

    function normalize(value) {
        return (value || '').trim().toLowerCase();
    }

    function applySelection(item) {
        if (!item) {
            return;
        }

        hiddenInput.value = item.id;
        searchInput.value = item.label;
        setPreviewIcon(item.icon_class);
        searchInput.setCustomValidity('');
        resultsBox.classList.add('hidden');
    }

    function setPreviewIcon(iconClass) {
        const cssClass = (iconClass || '').trim() || 'fas fa-stethoscope';
        previewIcon.className = cssClass;
    }

    function resolveFromInput() {
        const query = normalize(searchInput.value);
        if (query === '') {
            hiddenInput.value = '';
            setPreviewIcon('fas fa-stethoscope');
            return null;
        }

        const exact = options.find(function (item) {
            return normalize(item.label) === query;
        });

        if (exact) {
            hiddenInput.value = exact.id;
            searchInput.value = exact.label;
            setPreviewIcon(exact.icon_class);
            return exact;
        }

        const partialMatches = options.filter(function (item) {
            return normalize(item.label).includes(query);
        });

        if (partialMatches.length === 1) {
            hiddenInput.value = partialMatches[0].id;
            searchInput.value = partialMatches[0].label;
            setPreviewIcon(partialMatches[0].icon_class);
            return partialMatches[0];
        }

        return null;
    }

    function renderResults(term) {
        const query = (term || '').trim().toLowerCase();
        const filtered = options
            .filter(function (item) {
                return query === '' || item.label.toLowerCase().includes(query);
            })
            .slice(0, 30);

        resultsBox.innerHTML = '';

        if (filtered.length === 0) {
            resultsBox.classList.add('hidden');
            return;
        }

        filtered.forEach(function (item) {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'w-full text-left px-3 py-2 hover:bg-blue-50 text-sm text-gray-700 flex items-center gap-2';
            option.innerHTML = `<i class="${item.icon_class || 'fas fa-stethoscope'}"></i><span>${item.label}</span>`;
            option.addEventListener('mousedown', function (event) {
                event.preventDefault();
                applySelection(item);
            });
            option.addEventListener('click', function () {
                applySelection(item);
            });
            resultsBox.appendChild(option);
        });

        resultsBox.classList.remove('hidden');
    }

    const initialId = parseInt(hiddenInput.value || '0', 10);
    if (initialId > 0) {
        const selected = options.find(function (item) { return item.id === initialId; });
        if (selected) {
            searchInput.value = selected.label;
            setPreviewIcon(selected.icon_class);
        }
    }

    searchInput.addEventListener('focus', function () {
        renderResults(searchInput.value);
    });

    searchInput.addEventListener('input', function () {
        hiddenInput.value = '';
        searchInput.setCustomValidity('');
        setPreviewIcon('fas fa-stethoscope');
        renderResults(searchInput.value);
    });

    searchInput.addEventListener('blur', function () {
        resolveFromInput();
        setTimeout(function () {
            resultsBox.classList.add('hidden');
        }, 250);
    });

    resultsBox.addEventListener('mousedown', function (event) {
        event.preventDefault();
    });

    form.addEventListener('submit', function (event) {
        if (!hiddenInput.value) {
            resolveFromInput();
        }

        if (!hiddenInput.value) {
            event.preventDefault();
            searchInput.setCustomValidity('Veuillez sélectionner un équipement dans la liste.');
            searchInput.reportValidity();
            renderResults(searchInput.value);
            return;
        }

        searchInput.setCustomValidity('');
    });

    maintenanceScopeSelect.addEventListener('change', toggleExternalCallOption);
    toggleExternalCallOption();
});
</script>
@endsection
