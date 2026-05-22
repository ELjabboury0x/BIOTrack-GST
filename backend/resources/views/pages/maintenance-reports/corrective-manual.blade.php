@extends('layouts.dashboard')

@section('page-title', 'Manuel - Maintenance corrective')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Rapports / Maintenance corrective / Manuel'
])

<div class="max-w-5xl mx-auto bg-white rounded-xl shadow-md p-6 space-y-6">
    <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800 text-sm">
        Ce manuel décrit le flux d'import et d'exploitation du fichier <strong>Maintenance corrective.xlsx</strong>.
    </div>

    <section>
        <h2 class="text-lg font-bold text-gray-800 mb-2">1) Colonnes prises en charge</h2>
        <p class="text-sm text-gray-600 mb-3">Le système charge les colonnes du fichier de maintenance corrective vers la table dédiée.</p>
        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
            <li>Société</li>
            <li>Désignation équipement</li>
            <li>Marque</li>
            <li>Modèle</li>
            <li>N° série</li>
            <li>N° marché/contrat</li>
            <li>Détails panne</li>
            <li>Observations</li>
            <li>Services</li>
            <li>Date d'intervention</li>
            <li>Métadonnées source: fichier, feuille, ligne</li>
        </ul>
    </section>

    <section>
        <h2 class="text-lg font-bold text-gray-800 mb-2">2) Actions Excel</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <a href="{{ route('maintenance-reports.corrective.template-excel') }}" class="inline-flex items-center justify-center px-4 py-2 border border-emerald-200 text-emerald-700 rounded-lg hover:bg-emerald-50 text-sm font-semibold">
                <i class="fas fa-file-excel mr-2"></i>Télécharger le modèle
            </a>
            <a href="{{ route('maintenance-reports.corrective.export-excel', request()->query()) }}" class="inline-flex items-center justify-center px-4 py-2 border border-green-200 text-green-700 rounded-lg hover:bg-green-50 text-sm font-semibold">
                <i class="fas fa-file-export mr-2"></i>Exporter données
            </a>
            <a href="{{ route('maintenance-reports.index', ['type' => 'curative']) }}" class="inline-flex items-center justify-center px-4 py-2 border border-blue-200 text-blue-700 rounded-lg hover:bg-blue-50 text-sm font-semibold">
                <i class="fas fa-table mr-2"></i>Ouvrir l'écran maintenance corrective
            </a>
        </div>
    </section>

    <section>
        <h2 class="text-lg font-bold text-gray-800 mb-2">3) Filtres disponibles</h2>
        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
            <li>Filtres rapport: type, statut, service, période, recherche générale</li>
            <li>Filtres du fichier de maintenance corrective: société, service texte, date texte, recherche globale</li>
        </ul>
    </section>

    <div class="pt-2">
        <a href="{{ route('maintenance-reports.index', ['type' => 'curative']) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold">
            Retour à la maintenance corrective
        </a>
    </div>
</div>
@endsection
