@extends('layouts.dashboard')

@section('page-title', 'Maintenance Préventive')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Maintenance Préventive',
    'addRoute' => 'maintenance-preventive.create',
    'addLabel' => 'Ajouter une maintenance préventive',
    'addIcon' => 'fa-plus'
])

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">{{ session('error') }}</div>
@endif

<div class="mb-4 flex flex-wrap justify-end gap-3">
    @if(auth()->user()?->role !== 'major')
        <form id="maintenance-import-form" method="POST" action="{{ route('maintenance-preventive.import-excel') }}" enctype="multipart/form-data" class="inline-flex">
            @csrf
            <input id="maintenance-import-file" type="file" name="excel_file" accept=".xlsx,.xls,.csv" required class="hidden">
            <button id="maintenance-import-trigger" type="button" class="inline-flex h-10 items-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                <i class="fas fa-file-excel"></i>
                <span>Importer</span>
            </button>
        </form>
    @endif

    <div x-data="{ open: false }" class="relative">
        <button type="button"
                @click="open = !open"
                class="inline-flex h-10 items-center gap-2 rounded-lg bg-slate-700 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300">
            <i class="fas fa-file-export"></i>
            <span>Exporter</span>
        </button>
        <div x-show="open"
             @click.away="open = false"
             x-transition
             class="absolute right-0 z-50 mt-2 w-44 overflow-hidden rounded-lg border border-gray-100 bg-white shadow-xl">
            <a href="{{ route('maintenance-preventive.export.excel') }}"
               @click="open = false"
               class="block w-full px-4 py-2 text-left text-sm text-gray-700 transition-colors hover:bg-gray-50">
                Excel
            </a>
            <a href="{{ route('maintenance-preventive.export.pdf') }}"
               target="_blank"
               rel="noopener"
               @click="open = false"
               class="block w-full border-t border-gray-100 px-4 py-2 text-left text-sm text-gray-700 transition-colors hover:bg-gray-50">
                PDF
            </a>
        </div>
    </div>
</div>

<div class="mb-4 bg-white rounded-xl shadow-md border border-gray-100 px-6 py-4 flex items-center justify-between gap-4">
    <div>
        <h3 class="text-base font-semibold text-gray-800">Maintenances préventives actives</h3>
    </div>
    <span class="text-sm text-gray-500">{{ ($maintenanceData ?? collect())->count() }} maintenance(s)</span>
</div>

@include('components.table', [
    'data' => $maintenanceData ?? [],
    'showAddButton' => false,
    'showImportAction' => false,
    'showExportAction' => false,
    'showColumnsToggle' => false,
    'buttonStyle' => 'equipments',
    'columns' => [
        ['key' => 'code', 'label' => 'Code', 'visible' => true, 'type' => 'text'],
        ['key' => 'equipement', 'label' => 'Équipement', 'visible' => true, 'type' => 'text'],
        ['key' => 'periodicite', 'label' => 'Périodicité', 'visible' => true, 'type' => 'text'],
        ['key' => 'dernier', 'label' => 'Dernière maintenance', 'visible' => true, 'type' => 'date'],
        ['key' => 'prochain', 'label' => 'Prochaine maintenance', 'visible' => true, 'type' => 'date'],
        ['key' => 'statut', 'label' => 'Statut', 'visible' => true, 'type' => 'status'],
    ]
])

@if (($historicalMaintenanceData ?? collect())->count() > 0)
    <div class="mt-8 mb-4 bg-white rounded-xl shadow-md border border-gray-100 px-6 py-4 flex items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-semibold text-gray-800">Historique importé</h3>
        </div>
        <span class="text-sm text-gray-500">{{ ($historicalMaintenanceData ?? collect())->count() }} ligne(s)</span>
    </div>

    @include('components.table', [
        'data' => $historicalMaintenanceData ?? [],
        'showAddButton' => false,
        'showImportAction' => false,
        'showExportAction' => false,
        'showColumnsToggle' => false,
        'showDeleteAction' => false,
        'buttonStyle' => 'equipments',
        'columns' => [
            ['key' => 'societe', 'label' => 'Société', 'visible' => true, 'type' => 'text'],
            ['key' => 'designation_equipement', 'label' => 'Désignation équipement', 'visible' => true, 'type' => 'text'],
            ['key' => 'marque', 'label' => 'Marque', 'visible' => true, 'type' => 'text'],
            ['key' => 'modele', 'label' => 'Modèle', 'visible' => true, 'type' => 'text'],
            ['key' => 'marche_contrat', 'label' => 'Marché/Contrat', 'visible' => true, 'type' => 'text'],
            ['key' => 'numero_serie', 'label' => 'N° série', 'visible' => true, 'type' => 'text'],
            ['key' => 'dates_intervention', 'label' => 'Dates intervention', 'visible' => true, 'type' => 'text'],
            ['key' => 'details_intervention', 'label' => 'Détails intervention', 'visible' => true, 'type' => 'text'],
            ['key' => 'observations', 'label' => 'Observations', 'visible' => true, 'type' => 'text'],
            ['key' => 'services', 'label' => 'Service(s)', 'visible' => true, 'type' => 'text'],
            ['key' => 'activite_achevee', 'label' => 'Activité achevée', 'visible' => true, 'type' => 'text'],
        ]
    ])
@endif

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
