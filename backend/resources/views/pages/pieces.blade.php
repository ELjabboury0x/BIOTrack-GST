@extends('layouts.dashboard')

@section('page-title', 'Pièces de Rechange')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Pièces de Rechange',
    'addRoute' => 'pieces.create',
    'addLabel' => 'Ajouter une pièce',
    'addIcon' => 'fa-plus'
])

@include('components.table', [
    'data' => $piecesData ?? [],
    'showAddButton' => false,
    'columns' => [
        ['key' => 'code', 'label' => 'Code', 'visible' => true, 'type' => 'text'],
        ['key' => 'nom', 'label' => 'Nom', 'visible' => true, 'type' => 'text'],
        ['key' => 'description', 'label' => 'Description', 'visible' => false, 'type' => 'text'],
        ['key' => 'quantite', 'label' => 'Qté Stock', 'visible' => true, 'type' => 'text'],
        ['key' => 'prix_unitaire', 'label' => 'Prix Unitaire', 'visible' => true, 'type' => 'text'],
        ['key' => 'fournisseur', 'label' => 'Fournisseur', 'visible' => true, 'type' => 'text'],
    ]
])

@endsection
