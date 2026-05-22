@extends('layouts.dashboard')

@section('page-title', 'Décharge & Réception des Pièces')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Décharge & Réception des Pièces',
    'addRoute' => 'pieces.create',
    'addLabel' => 'Ajouter un mouvement',
    'addIcon' => 'fa-truck-loading'
])

<div class="bg-white rounded-xl shadow-md p-8 text-gray-600">
    <h2 class="text-xl font-bold text-gray-800 mb-2">Décharge & Réception des Pièces</h2>
    <p class="mb-4">Gestion des mouvements de stock (décharge/réception).</p>

    @include('components.table', [
        'data' => $movementsData ?? [],
        'showAddButton' => false,
        'showImportAction' => false,
        'showExportAction' => true,
        'columns' => [
            ['key' => 'type', 'label' => 'Type', 'visible' => true, 'type' => 'status'],
            ['key' => 'reference_piece', 'label' => 'Référence pièce', 'visible' => true, 'type' => 'text'],
            ['key' => 'quantite', 'label' => 'Quantité', 'visible' => true, 'type' => 'text'],
            ['key' => 'date_mouvement', 'label' => 'Date', 'visible' => true, 'type' => 'date'],
            ['key' => 'description', 'label' => 'Description', 'visible' => true, 'type' => 'text'],
            ['key' => 'auteur', 'label' => 'Créé par', 'visible' => true, 'type' => 'text'],
        ]
    ])
</div>
@endsection
