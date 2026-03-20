@extends('layouts.dashboard')

@section('page-title', 'Maintenance Préventive')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Maintenance Préventive',
    'addRoute' => 'maintenance-preventive.create',
    'addLabel' => 'Ajouter une maintenance préventive',
    'addIcon' => 'fa-plus'
])

<div class="mb-8 grid grid-cols-1 xl:grid-cols-3 gap-4">
    <div class="xl:col-span-2 rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 to-indigo-50 p-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-gray-800">Ajout manuel d’intervention</h3>
                <p class="text-sm text-gray-600 mt-1">Créez rapidement une maintenance préventive depuis le tableau de bord.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('maintenance-preventive.create') }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-1"></i> Nouvelle maintenance
                </a>
                <a href="{{ route('interventions.create') }}" class="px-4 py-2 rounded-lg border border-blue-200 text-blue-700 text-sm font-semibold hover:bg-white transition-colors">
                    <i class="fas fa-screwdriver-wrench mr-1"></i> Intervention curative
                </a>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-700">
            <p class="font-semibold mb-2">Noms des données à renseigner:</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <div class="rounded-lg bg-white/80 border border-gray-200 px-3 py-2">Code</div>
                <div class="rounded-lg bg-white/80 border border-gray-200 px-3 py-2">Équipement</div>
                <div class="rounded-lg bg-white/80 border border-gray-200 px-3 py-2">Périodicité</div>
                <div class="rounded-lg bg-white/80 border border-gray-200 px-3 py-2">Statut</div>
                <div class="rounded-lg bg-white/80 border border-gray-200 px-3 py-2">Dernière Maintenance</div>
                <div class="rounded-lg bg-white/80 border border-gray-200 px-3 py-2">Prochaine Maintenance</div>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <h3 class="text-base font-bold text-gray-800 mb-2">Liens du tableau de bord</h3>
        <p class="text-sm text-gray-600 mb-4">Accès direct aux fonctions liées.</p>
        <div class="space-y-2">
            <a href="{{ route('dashboard') }}" class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <span>Tableau de bord</span><i class="fas fa-arrow-right"></i>
            </a>
            <a href="{{ route('interventions') }}" class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <span>Liste des interventions</span><i class="fas fa-arrow-right"></i>
            </a>
            <a href="{{ route('planning.index') }}" class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <span>Planning sociétés externes</span><i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="rounded-2xl border border-gray-100 bg-white/90">
    <div class="px-5 pt-5 pb-2 border-b border-gray-100">
        <h3 class="text-base font-semibold text-gray-800">Maintenances préventives actives</h3>
        <p class="text-sm text-gray-600">Chaque ligne est modifiable depuis l’action <strong>Modifier</strong>.</p>
    </div>
    @include('components.table', [
        'data' => $maintenanceData ?? [],
        'showAddButton' => false,
        'showImportAction' => false,
        'columns' => [
            ['key' => 'code', 'label' => 'Code', 'visible' => true, 'type' => 'text'],
            ['key' => 'equipement', 'label' => 'Équipement', 'visible' => true, 'type' => 'text'],
            ['key' => 'periodicite', 'label' => 'Périodicité', 'visible' => true, 'type' => 'text'],
            ['key' => 'dernier', 'label' => 'Dernière Maintenance', 'visible' => true, 'type' => 'date'],
            ['key' => 'prochain', 'label' => 'Prochaine', 'visible' => true, 'type' => 'date'],
            ['key' => 'statut', 'label' => 'Statut', 'visible' => true, 'type' => 'status'],
        ]
    ])
</div>

@if (($historicalMaintenanceData ?? collect())->count() > 0)
    <div class="mt-8 rounded-2xl border border-gray-100 bg-white/90">
        <div class="px-5 pt-5 pb-2 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-800">Anciennes maintenances</h3>
            <p class="text-sm text-gray-600">Utilisez <strong>Modifier</strong> pour préremplir une intervention manuelle à partir de la ligne sélectionnée.</p>
        </div>
        @include('components.table', [
            'data' => $historicalMaintenanceData ?? [],
            'showAddButton' => false,
            'showImportAction' => false,
            'showDeleteAction' => false,
            'columns' => [
                ['key' => 'societe', 'label' => 'Société', 'visible' => true, 'type' => 'text'],
                ['key' => 'designation_equipement', 'label' => 'Désignation de l’équipement', 'visible' => true, 'type' => 'text'],
                ['key' => 'marque', 'label' => 'Marque', 'visible' => true, 'type' => 'text'],
                ['key' => 'modele', 'label' => 'Modèle', 'visible' => true, 'type' => 'text'],
                ['key' => 'marche_contrat', 'label' => 'N° de Marché/Contrat', 'visible' => true, 'type' => 'text'],
                ['key' => 'numero_serie', 'label' => 'N° de série', 'visible' => true, 'type' => 'text'],
                ['key' => 'dates_intervention', 'label' => 'Dates d’intervention', 'visible' => true, 'type' => 'text'],
                ['key' => 'details_intervention', 'label' => 'Détails de l’intervention', 'visible' => true, 'type' => 'text'],
                ['key' => 'observations', 'label' => 'Observations', 'visible' => true, 'type' => 'text'],
                ['key' => 'services', 'label' => 'Service(s)', 'visible' => true, 'type' => 'text'],
                ['key' => 'activite_achevee', 'label' => 'Activité achevée OUI/NON', 'visible' => true, 'type' => 'text'],
            ]
        ])
    </div>
@endif

@endsection
