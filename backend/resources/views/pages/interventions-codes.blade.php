@extends('layouts.dashboard')

@section('page-title', 'IW38-BM - Registre OT/DM')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Interventions / IW38-BM',
    'addRoute' => 'interventions.create',
    'addLabel' => 'Ajouter une intervention',
    'addIcon' => 'fa-user-doctor'
])

<div class="mb-4 bg-white rounded-xl shadow-md p-4">
    <form method="GET" action="{{ route('interventions.codes') }}" class="flex flex-col md:flex-row md:items-end gap-4">
        <div class="w-full md:w-80">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Filtrer par statut</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="" {{ ($selectedStatus ?? '') === '' ? 'selected' : '' }}>Tous</option>
                <option value="en_attente" {{ ($selectedStatus ?? '') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                <option value="en_cours" {{ ($selectedStatus ?? '') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                <option value="termine" {{ ($selectedStatus ?? '') === 'termine' ? 'selected' : '' }}>Terminé</option>
            </select>
        </div>

        <div class="w-full md:w-56">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date début (du)</label>
            <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="w-full md:w-56">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date début (au)</label>
            <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="flex gap-2">
            <button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Filtrer</button>
            <a href="{{ route('interventions.codes') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Réinitialiser</a>
            <a href="{{ route('interventions') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Retour</a>
        </div>
    </form>
</div>

@include('components.table', [
    'data' => $codesData ?? [],
    'showAddButton' => false,
    'columns' => [
        ['key' => 'code', 'label' => 'Code Intervention', 'visible' => true, 'type' => 'text'],
        ['key' => 'equipement', 'label' => 'Équipement', 'visible' => true, 'type' => 'text'],
        ['key' => 'type', 'label' => 'Type', 'visible' => true, 'type' => 'text'],
        ['key' => 'statut', 'label' => 'Statut', 'visible' => true, 'type' => 'status'],
        ['key' => 'date_creation', 'label' => 'Date Génération', 'visible' => true, 'type' => 'date'],
    ]
])

@endsection
