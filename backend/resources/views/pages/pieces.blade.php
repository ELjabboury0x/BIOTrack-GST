@extends('layouts.dashboard')

@section('page-title', 'Mouvements Pièces de Rechange')

@section('content')
@php
    $rows = collect($piecesData ?? []);
    $total = $rows->count();
    $decharges = $rows->where('phase', 'Decharge')->count();
    $retours = $rows->where('phase', 'Reception / Retour')->count();
    $pdfImports = $rows->where('mode_saisie', 'Import PDF')->count();
@endphp

@include('components.module-page-header', [
    'breadcrumb' => 'Stock / Pièces de Rechange / Mouvements',
    'addRoute' => 'pieces.create',
    'addLabel' => 'Nouveau mouvement',
    'addIcon' => 'fa-plus'
])

<div class="mb-6 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <div class="rounded-xl bg-white shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
        <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">Total mouvements</p>
        <p class="text-2xl font-bold text-gray-900">{{ $total }}</p>
    </div>
    <div class="rounded-xl bg-rose-50 shadow-sm border border-rose-100 p-4 hover:shadow-md transition-shadow">
        <p class="text-xs uppercase tracking-wide text-rose-600 mb-1">Décharge</p>
        <p class="text-2xl font-bold text-rose-700">{{ $decharges }}</p>
    </div>
    <div class="rounded-xl bg-emerald-50 shadow-sm border border-emerald-100 p-4 hover:shadow-md transition-shadow">
        <p class="text-xs uppercase tracking-wide text-emerald-600 mb-1">Réception / Retour</p>
        <p class="text-2xl font-bold text-emerald-700">{{ $retours }}</p>
    </div>
    <div class="rounded-xl bg-blue-50 shadow-sm border border-blue-100 p-4 hover:shadow-md transition-shadow">
        <p class="text-xs uppercase tracking-wide text-blue-600 mb-1">Imports PDF</p>
        <p class="text-2xl font-bold text-blue-700">{{ $pdfImports }}</p>
    </div>
</div>

@if ($total === 0)
    <div class="mb-6 rounded-xl border border-indigo-100 bg-indigo-50 p-4 text-sm text-indigo-700">
        <div class="font-semibold mb-1"><i class="fas fa-circle-info mr-2"></i>Aucun mouvement enregistré</div>
        Utilise le bouton <strong>Nouveau mouvement</strong> pour créer une Décharge ou une Réception/Retour, soit via PDF, soit via formulaire structuré.
    </div>
@endif

@include('components.table', [
    'data' => $piecesData ?? [],
    'showAddButton' => false,
    'columns' => [
        ['key' => 'phase', 'label' => 'Phase', 'visible' => true, 'type' => 'text'],
        ['key' => 'date_mouvement', 'label' => 'Date', 'visible' => true, 'type' => 'text'],
        ['key' => 'code', 'label' => 'Code', 'visible' => true, 'type' => 'text'],
        ['key' => 'nom', 'label' => 'Nom', 'visible' => true, 'type' => 'text'],
        ['key' => 'sn', 'label' => 'SN', 'visible' => false, 'type' => 'text'],
        ['key' => 'description', 'label' => 'Description', 'visible' => false, 'type' => 'text'],
        ['key' => 'quantite', 'label' => 'Qté Stock', 'visible' => true, 'type' => 'text'],
        ['key' => 'fournisseur', 'label' => 'Fournisseur', 'visible' => true, 'type' => 'text'],
        ['key' => 'etat', 'label' => 'État', 'visible' => false, 'type' => 'text'],
        ['key' => 'mode_saisie', 'label' => 'Mode', 'visible' => true, 'type' => 'text'],
        ['key' => 'pdf', 'label' => 'PDF', 'visible' => true, 'type' => 'text'],
    ]
])

@endsection
