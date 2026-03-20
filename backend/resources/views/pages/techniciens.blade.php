@extends('layouts.dashboard')

@section('page-title', 'Gestion des utilisateurs')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Utilisateurs',
    'addRoute' => 'technicians.create',
    'addLabel' => 'Ajouter un technicien',
    'addIcon' => 'fa-plus'
])

<div class="space-y-6">
    <div>
        <h2 class="text-lg font-bold text-gray-800 mb-3">Ingénieurs</h2>
        @include('components.table', [
            'data' => $ingenieursData ?? [],
            'showAddButton' => false,
            'showEditAction' => false,
            'showDeleteAction' => false,
            'showImportAction' => false,
            'columns' => [
                ['key' => 'nom', 'label' => 'Nom Complet', 'visible' => true, 'type' => 'text'],
                ['key' => 'email', 'label' => 'E-mail', 'visible' => true, 'type' => 'text'],
                ['key' => 'telephone', 'label' => 'Tél.', 'visible' => true, 'type' => 'text'],
                ['key' => 'specialite', 'label' => 'Spécialité', 'visible' => true, 'type' => 'text'],
                ['key' => 'statut', 'label' => 'Statut', 'visible' => true, 'type' => 'status'],
            ]
        ])
    </div>

    <div>
        <h2 class="text-lg font-bold text-gray-800 mb-3">Techniciens</h2>
        @include('components.table', [
            'data' => $techniciensData ?? [],
            'showAddButton' => false,
            'showEditAction' => false,
            'showDeleteAction' => false,
            'showImportAction' => false,
            'columns' => [
                ['key' => 'nom', 'label' => 'Nom Complet', 'visible' => true, 'type' => 'text'],
                ['key' => 'email', 'label' => 'E-mail', 'visible' => true, 'type' => 'text'],
                ['key' => 'telephone', 'label' => 'Tél.', 'visible' => true, 'type' => 'text'],
                ['key' => 'specialite', 'label' => 'Spécialité', 'visible' => true, 'type' => 'text'],
                ['key' => 'statut', 'label' => 'Statut', 'visible' => true, 'type' => 'status'],
            ]
        ])
    </div>

    <div>
        <h2 class="text-lg font-bold text-gray-800 mb-3">Majors de service</h2>
        @include('components.table', [
            'data' => $majorsData ?? [],
            'showAddButton' => false,
            'showEditAction' => false,
            'showDeleteAction' => false,
            'showImportAction' => false,
            'columns' => [
                ['key' => 'nom', 'label' => 'Nom Complet', 'visible' => true, 'type' => 'text'],
                ['key' => 'email', 'label' => 'E-mail', 'visible' => true, 'type' => 'text'],
                ['key' => 'telephone', 'label' => 'Tél.', 'visible' => true, 'type' => 'text'],
                ['key' => 'specialite', 'label' => 'Spécialité', 'visible' => true, 'type' => 'text'],
                ['key' => 'statut', 'label' => 'Statut', 'visible' => true, 'type' => 'status'],
            ]
        ])
    </div>
</div>

@endsection
