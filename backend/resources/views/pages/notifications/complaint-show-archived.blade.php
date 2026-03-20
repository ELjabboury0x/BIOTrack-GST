@extends('layouts.dashboard')

@section('page-title', 'Notification Réclamation (Archive)')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md p-8">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Ancienne réclamation (archive notification)</h2>
            <p class="text-sm text-gray-500 mt-1">La réclamation d'origine n'est plus disponible en base, mais les informations de notification sont conservées.</p>
        </div>
    </div>

    <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
        Cette fiche provient de l'archive des notifications. Certaines données détaillées (pièces jointes, historique complet) peuvent ne plus être disponibles.
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
            <p class="text-gray-500">ID réclamation</p>
            <p class="font-semibold text-gray-800 mt-1">{{ (int) ($notificationData['complaint_id'] ?? 0) > 0 ? $notificationData['complaint_id'] : '-' }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
            <p class="text-gray-500">Date notification</p>
            <p class="font-semibold text-gray-800 mt-1">{{ $notificationData['created_at'] ?? '-' }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
            <p class="text-gray-500">Service</p>
            <p class="font-semibold text-gray-800 mt-1">{{ $notificationData['service_name'] ?? '-' }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
            <p class="text-gray-500">Équipement</p>
            <p class="font-semibold text-gray-800 mt-1">{{ $notificationData['equipment_label'] ?? '-' }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
            <p class="text-gray-500">Déclarant</p>
            <p class="font-semibold text-gray-800 mt-1">{{ $notificationData['reported_by_name'] ?? '-' }}</p>
        </div>
        <div class="p-4 rounded-lg bg-gray-50 border border-gray-200">
            <p class="text-gray-500">Statut / Priorité</p>
            <p class="font-semibold text-gray-800 mt-1 uppercase">{{ $notificationData['status'] ?? '-' }} / {{ $notificationData['priority'] ?? '-' }}</p>
        </div>
    </div>

    <div class="mt-6 p-4 rounded-lg bg-gray-50 border border-gray-200">
        <p class="text-gray-500 text-sm">Description (notification)</p>
        <p class="text-gray-800 mt-2 whitespace-pre-line">{{ $notificationData['description'] ?: '-' }}</p>
    </div>

    <div class="mt-8">
        <a href="{{ route('reclamations.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold">
            Retour à la liste des réclamations
        </a>
    </div>
</div>
@endsection
