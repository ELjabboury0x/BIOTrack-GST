@extends('layouts.dashboard')

@section('page-title', 'Ajouter une maintenance préventive')

@section('content')
@php
    $prefill = $prefill ?? [];
    $equipmentOptions = collect($equipments ?? [])->map(function ($equipment) {
        return [
            'id' => (int) $equipment->id,
            'label' => trim(((string) ($equipment->inventory_number_current ?: 'N/A')) . ' - ' . ((string) ($equipment->designation ?: 'Sans désignation'))),
        ];
    })->values();
    $selectedEquipmentId = (int) old('equipment_id', $prefill['equipment_id'] ?? 0);
    $prefillEquipmentSearch = (string) ($prefill['equipment_search'] ?? '');
@endphp
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-md p-8">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Formulaire Maintenance Préventive</h2>
    @if (($prefill['source'] ?? '') === 'historical')
        <div class="mb-6 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm text-indigo-700">
            Données préremplies depuis une tâche importée (ID: {{ $prefill['historical_id'] ?: '-' }}). Vérifiez puis enregistrez pour créer l’intervention manuelle.
        </div>
    @endif
    <form class="grid grid-cols-1 md:grid-cols-2 gap-6" method="POST" action="{{ route('maintenance-preventive.store') }}">
        @csrf
        <input name="code" value="{{ old('code', $prefill['code'] ?? '') }}" placeholder="Code maintenance (ex: MP-001)" class="px-4 py-2 border border-gray-300 rounded-lg" required>
        <div class="relative md:col-span-1">
            <input type="hidden" name="equipment_id" id="equipment_id_create" value="{{ $selectedEquipmentId > 0 ? $selectedEquipmentId : '' }}" required>
            <input type="text" id="equipment_search_create" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Rechercher un équipement..." autocomplete="off">
            <div id="equipment_results_create" class="hidden absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto z-30"></div>
        </div>
        <select name="periodicity" class="px-4 py-2 border border-gray-300 rounded-lg" required>
            <option value="Mensuel" {{ old('periodicity', $prefill['periodicity'] ?? '') === 'Mensuel' ? 'selected' : '' }}>Mensuel</option>
            <option value="Trimestriel" {{ old('periodicity', $prefill['periodicity'] ?? '') === 'Trimestriel' ? 'selected' : '' }}>Trimestriel</option>
            <option value="Semestriel" {{ old('periodicity', $prefill['periodicity'] ?? '') === 'Semestriel' ? 'selected' : '' }}>Semestriel</option>
            <option value="Annuel" {{ old('periodicity', $prefill['periodicity'] ?? '') === 'Annuel' ? 'selected' : '' }}>Annuel</option>
        </select>
        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg" required>
            <option value="actif" {{ old('status', $prefill['status'] ?? 'actif') === 'actif' ? 'selected' : '' }}>Actif</option>
            <option value="inactif" {{ old('status', $prefill['status'] ?? 'actif') === 'inactif' ? 'selected' : '' }}>Inactif</option>
        </select>
        <input type="date" name="last_maintenance_date" value="{{ old('last_maintenance_date', $prefill['last_maintenance_date'] ?? '') }}" class="px-4 py-2 border border-gray-300 rounded-lg">
        <input type="date" name="next_maintenance_date" value="{{ old('next_maintenance_date', $prefill['next_maintenance_date'] ?? '') }}" class="px-4 py-2 border border-gray-300 rounded-lg" required>
        <div class="md:col-span-2 flex justify-end gap-3">
            <a href="{{ route('maintenance-preventive') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Annuler</a>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg">Enregistrer</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const options = @json($equipmentOptions);
    const hiddenInput = document.getElementById('equipment_id_create');
    const searchInput = document.getElementById('equipment_search_create');
    const resultsBox = document.getElementById('equipment_results_create');

    function renderResults(term) {
        const query = (term || '').trim().toLowerCase();
        const filtered = options
            .filter(function (item) {
                return query === '' || item.label.toLowerCase().includes(query);
            })
            .slice(0, 15);

        resultsBox.innerHTML = '';

        if (filtered.length === 0) {
            resultsBox.classList.add('hidden');
            return;
        }

        filtered.forEach(function (item) {
            const option = document.createElement('button');
            option.type = 'button';
            option.className = 'w-full text-left px-3 py-2 hover:bg-blue-50 text-sm text-gray-700';
            option.textContent = item.label;
            option.addEventListener('click', function () {
                hiddenInput.value = item.id;
                searchInput.value = item.label;
                resultsBox.classList.add('hidden');
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
        }
    } else if ("{{ addslashes($prefillEquipmentSearch) }}") {
        searchInput.value = "{{ addslashes($prefillEquipmentSearch) }}";
    }

    searchInput.addEventListener('focus', function () {
        renderResults(searchInput.value);
    });

    searchInput.addEventListener('input', function () {
        hiddenInput.value = '';
        renderResults(searchInput.value);
    });

    searchInput.addEventListener('blur', function () {
        setTimeout(function () {
            resultsBox.classList.add('hidden');
        }, 150);
    });
});
</script>
@endsection
