@extends('layouts.dashboard')

@section('page-title', 'Rapports et Statistiques')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h2 class="text-2xl font-bold text-gray-800">Rapports d'intervention biomédicale</h2>
    <div class="flex flex-wrap gap-2">
        <details class="relative">
            <summary class="list-none cursor-pointer inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white hover:bg-blue-700 select-none">
                <i class="fas fa-plus"></i>
                <span>Nouveau rapport</span>
                <i class="fas fa-chevron-down text-xs"></i>
            </summary>
            <div class="absolute right-0 mt-2 w-64 bg-white text-gray-800 rounded-xl shadow-lg border border-gray-200 z-30 overflow-hidden">
                <a href="{{ route('maintenance-reports.create', ['type' => 'preventive', 'scope' => 'interne']) }}" class="block px-4 py-2 hover:bg-gray-50">
                    Préventive interne
                </a>
                <a href="{{ route('maintenance-reports.create', ['type' => 'curative', 'scope' => 'interne']) }}" class="block px-4 py-2 hover:bg-gray-50 border-t border-gray-100">
                    Corrective interne
                </a>
                <a href="{{ route('maintenance-reports.create', ['type' => 'preventive', 'scope' => 'externe']) }}" class="block px-4 py-2 hover:bg-gray-50 border-t border-gray-100">
                    Préventive externe
                </a>
                <a href="{{ route('maintenance-reports.create', ['type' => 'curative', 'scope' => 'externe']) }}" class="block px-4 py-2 hover:bg-gray-50 border-t border-gray-100">
                    Corrective externe
                </a>
            </div>
        </details>

        <details class="relative">
            <summary class="list-none cursor-pointer inline-flex h-10 items-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white hover:bg-emerald-700 select-none">
                <i class="fas fa-list"></i>
                <span>Ouvrir la liste</span>
                <i class="fas fa-chevron-down text-xs"></i>
            </summary>
            <div class="absolute right-0 mt-2 w-64 bg-white text-gray-800 rounded-xl shadow-lg border border-gray-200 z-30 overflow-hidden">
                <a href="{{ route('maintenance-reports.index') }}" class="block px-4 py-2 hover:bg-gray-50">
                    Tous les rapports
                </a>
                <a href="{{ route('maintenance-reports.index', ['type' => 'preventive']) }}" class="block px-4 py-2 hover:bg-gray-50 border-t border-gray-100">
                    Préventive interne
                </a>
                <a href="{{ route('maintenance-reports.index', ['type' => 'curative']) }}" class="block px-4 py-2 hover:bg-gray-50 border-t border-gray-100">
                    Corrective interne
                </a>
                <a href="{{ route('maintenance-reports.index', ['type' => 'preventive', 'scope' => 'externe']) }}" class="block px-4 py-2 hover:bg-gray-50 border-t border-gray-100">
                    Préventive externe
                </a>
                <a href="{{ route('maintenance-reports.index', ['type' => 'curative', 'scope' => 'externe']) }}" class="block px-4 py-2 hover:bg-gray-50 border-t border-gray-100">
                    Corrective externe
                </a>
            </div>
        </details>
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


@if (empty($reportsData ?? []))
    <div class="bg-white rounded-xl shadow-md p-8 mb-8"></div>
@endif

<div class="mt-8">
    <div class="mb-3">
        <p class="text-gray-700 text-sm font-semibold">Historique des rapports</p>
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
            ['key' => 'date_intervention', 'label' => 'Date d\'intervention', 'visible' => true, 'type' => 'date'],
            ['key' => 'statut', 'label' => 'Statut', 'visible' => true, 'type' => 'status'],
        ]
    ])
</div>

@endsection
