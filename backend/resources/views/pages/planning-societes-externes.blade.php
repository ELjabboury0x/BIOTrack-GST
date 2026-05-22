@extends('layouts.dashboard')

@section('page-title', 'Maintenance Préventive')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Maintenance Préventive',
    'addRoute' => 'maintenance-preventive.create',
    'addLabel' => 'Ajouter une maintenance préventive',
    'addIcon' => 'fa-calendar-plus'
])

<div class="mb-4 flex flex-wrap justify-end gap-3">
    <form id="maintenance-import-form" method="POST" action="{{ route('maintenance-preventive.import-excel') }}" enctype="multipart/form-data" class="inline-flex">
        @csrf
        <input id="maintenance-import-file" type="file" name="contracts_file" accept=".xlsx,.xls" required class="hidden">
        <button id="maintenance-import-trigger" type="button" class="inline-flex h-10 items-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300">
            <i class="fas fa-file-import"></i>
            <span>Importer Excel</span>
        </button>
    </form>

    <form method="POST" action="{{ route('maintenance-preventive.sync-contracts') }}" class="inline-flex">
        @csrf
        <button type="submit" class="inline-flex h-10 items-center gap-2 rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-300">
            <i class="fas fa-sync-alt"></i>
            <span>Synchroniser contrats</span>
        </button>
    </form>
</div>

<div class="mb-4 bg-white rounded-xl shadow-md p-4">
    <form method="GET" action="{{ route('maintenance-preventive') }}" class="flex flex-col md:flex-row md:items-end gap-4" id="maintenance-filter-form">
        <div class="w-full md:w-72">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Société</label>
            <select name="company_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">Toutes les sociétés</option>
                @foreach(($companies ?? collect()) as $company)
                    <option value="{{ $company->id }}" {{ (int) ($selectedCompanyId ?? 0) === (int) $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-full md:w-56">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date début</label>
            <input id="date_from" type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="w-full md:w-56">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date fin</label>
            <input id="date_to" type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="flex gap-2">
            <button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Filtrer</button>
            <a href="{{ route('maintenance-preventive') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Réinitialiser</a>
        </div>
    </form>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
        {{ session('error') }}
    </div>
@endif

@include('components.table', [
    'data' => $planningData ?? [],
    'showAddButton' => false,
    'showImportAction' => false,
    'showExportAction' => true,
    'deleteEntityLabel' => 'cette maintenance',
    'columns' => [
        ['key' => 'societe', 'label' => 'Société', 'visible' => true, 'type' => 'text'],
        ['key' => 'trimestre', 'label' => 'Trimestre', 'visible' => true, 'type' => 'text'],
        ['key' => 'date_prevue', 'label' => 'Date prévue', 'visible' => true, 'type' => 'text'],
        ['key' => 'intervenant', 'label' => 'Intervenant', 'visible' => true, 'type' => 'text'],
        ['key' => 'description', 'label' => 'Description', 'visible' => true, 'type' => 'text'],
        ['key' => 'statut', 'label' => 'Statut', 'visible' => true, 'type' => 'status'],
    ]
])
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('maintenance-import-form');
    const fileInput = document.getElementById('maintenance-import-file');
    const triggerBtn = document.getElementById('maintenance-import-trigger');

    if (!form || !fileInput || !triggerBtn) {
        return;
    }

    triggerBtn.addEventListener('click', function () {
        fileInput.click();
    });

    fileInput.addEventListener('change', function () {
        if (!fileInput.files || fileInput.files.length === 0) {
            return;
        }

        form.submit();
    });
});
</script>
@endsection
