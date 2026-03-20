@extends('layouts.dashboard')

@section('page-title', 'Rapports et Statistiques')

@section('content')
<div class="mb-6">
    <div class="mb-3">
        <a href="javascript:history.back()" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i>Retour
        </a>
    </div>

    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-md">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-blue-100 text-sm">Modèle / Rapports</p>
                <h2 class="text-2xl font-bold mt-1">Rapports d'intervention biomédicale</h2>
                <p class="text-blue-100 text-sm mt-1">Vue rapide, accès direct et historique centralisé des rapports.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <details class="relative">
                    <summary class="list-none cursor-pointer px-4 py-2 bg-white text-blue-700 rounded-lg font-semibold hover:bg-blue-50 select-none">
                        <i class="fas fa-plus mr-2"></i>Nouveau rapport <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </summary>
                    <div class="absolute right-0 mt-2 w-64 bg-white text-gray-800 rounded-xl shadow-lg border border-gray-200 z-30 overflow-hidden">
                        <a href="{{ route('maintenance-reports.create', ['type' => 'preventive', 'scope' => 'interne']) }}" class="block px-4 py-2 hover:bg-gray-50">
                            Préventive interne
                        </a>
                        <a href="{{ route('maintenance-reports.create', ['type' => 'curative', 'scope' => 'interne']) }}" class="block px-4 py-2 hover:bg-gray-50 border-t border-gray-100">
                            Curative interne
                        </a>
                        <a href="{{ route('maintenance-reports.create', ['type' => 'preventive', 'scope' => 'externe']) }}" class="block px-4 py-2 hover:bg-gray-50 border-t border-gray-100">
                            Préventive externe
                        </a>
                        <a href="{{ route('maintenance-reports.create', ['type' => 'curative', 'scope' => 'externe']) }}" class="block px-4 py-2 hover:bg-gray-50 border-t border-gray-100">
                            Curative externe
                        </a>
                    </div>
                </details>

                <details class="relative">
                    <summary class="list-none cursor-pointer px-4 py-2 border border-blue-200 text-white rounded-lg font-semibold hover:bg-white/10 select-none">
                        <i class="fas fa-list mr-2"></i>Ouvrir la liste <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </summary>
                    <div class="absolute right-0 mt-2 w-64 bg-white text-gray-800 rounded-xl shadow-lg border border-gray-200 z-30 overflow-hidden">
                        <a href="{{ route('maintenance-reports.index') }}" class="block px-4 py-2 hover:bg-gray-50">
                            Tous les rapports
                        </a>
                        <a href="{{ route('maintenance-reports.index', ['type' => 'preventive']) }}" class="block px-4 py-2 hover:bg-gray-50 border-t border-gray-100">
                            Préventive interne
                        </a>
                        <a href="{{ route('maintenance-reports.index', ['type' => 'curative']) }}" class="block px-4 py-2 hover:bg-gray-50 border-t border-gray-100">
                            Curative interne
                        </a>
                        <a href="{{ route('maintenance-reports.index', ['type' => 'preventive', 'scope' => 'externe']) }}" class="block px-4 py-2 hover:bg-gray-50 border-t border-gray-100">
                            Préventive externe
                        </a>
                        <a href="{{ route('maintenance-reports.index', ['type' => 'curative', 'scope' => 'externe']) }}" class="block px-4 py-2 hover:bg-gray-50 border-t border-gray-100">
                            Curative externe
                        </a>
                    </div>
                </details>
            </div>
        </div>
    </div>
</div>

@php
    $stats = $reportStats ?? ['total' => 0, 'closed' => 0, 'submitted' => 0, 'draft' => 0, 'latest_date' => '-'];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Total rapports</p>
        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Clôturés</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['closed'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Soumis</p>
        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['submitted'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Brouillons</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['draft'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Dernière intervention</p>
        <p class="text-xl font-bold text-gray-800 mt-1">{{ $stats['latest_date'] }}</p>
    </div>
</div>

<div class="mb-8 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <h3 class="text-sm font-bold text-gray-800 mb-2">Comment utiliser cette fenêtre</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-gray-600">
        <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
            <p class="font-semibold text-gray-700">1) Nouveau rapport</p>
            <p>Sélectionnez le type: Préventive/Curative + Interne/Externe, puis remplissez le formulaire.</p>
        </div>
        <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
            <p class="font-semibold text-gray-700">2) Ouvrir la liste</p>
            <p>Accédez à la liste détaillée des OT/DM pour modifier, soumettre, valider ou clôturer les rapports.</p>
        </div>
        <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
            <p class="font-semibold text-gray-700">3) Historique</p>
            <p>Consultez les derniers rapports en bas et ouvrez-les directement depuis l’action d’édition.</p>
        </div>
    </div>
</div>

@if (empty($reportsData ?? []))
    <div class="bg-white rounded-xl shadow-md p-8 mb-8 text-center text-gray-500">
        <i class="fas fa-chart-line text-3xl mb-3"></i>
        <p>Aucun rapport disponible pour le moment. Importez vos données réelles pour commencer.</p>
    </div>
@endif

<div class="mt-8">
    <div class="mb-3">
        <p class="text-gray-700 text-sm font-semibold">Historique des rapports</p>
        <p class="text-gray-500 text-xs">Derniers rapports créés avec accès direct à l'édition.</p>
    </div>

    @include('components.table', [
        'data' => $reportHistoryData ?? [],
        'showImportAction' => false,
        'showExportAction' => false,
        'showCloseAction' => false,
        'showDeleteAction' => false,
        'showFillAction' => false,
        'showEditAction' => true,
        'columns' => [
            ['key' => 'numero', 'label' => 'N° Rapport', 'visible' => true, 'type' => 'text'],
            ['key' => 'type', 'label' => 'Type', 'visible' => true, 'type' => 'text'],
            ['key' => 'equipement', 'label' => 'Équipement', 'visible' => true, 'type' => 'text'],
            ['key' => 'service', 'label' => 'Service', 'visible' => true, 'type' => 'text'],
            ['key' => 'date_intervention', 'label' => 'Date intervention', 'visible' => true, 'type' => 'date'],
            ['key' => 'statut', 'label' => 'Statut', 'visible' => true, 'type' => 'status'],
        ]
    ])
</div>

@endsection
