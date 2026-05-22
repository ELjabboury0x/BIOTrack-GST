@extends('layouts.dashboard')

@section('page-title', 'Maintenance corrective - Saisie manuelle')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Rapports / Maintenance corrective / Saisie manuelle'
])

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $isEdit = !empty($row);
    $actionRoute = $isEdit
        ? route('maintenance-reports.corrective.update', $row->id)
        : route('maintenance-reports.corrective.store');
@endphp

<form method="POST" action="{{ $actionRoute }}" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="bg-white rounded-xl shadow-md p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Société</label>
            <input type="text" name="company_name" value="{{ old('company_name', $row->company_name ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Désignation de l'équipement</label>
            <input type="text" name="equipment_designation" value="{{ old('equipment_designation', $row->equipment_designation ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Marque</label>
            <input type="text" name="brand_name" value="{{ old('brand_name', $row->brand_name ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Modèle</label>
            <input type="text" name="model_name" value="{{ old('model_name', $row->model_name ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">N° de série</label>
            <input type="text" name="serial_number" value="{{ old('serial_number', $row->serial_number ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">N° de Marché/Contrat de maintenance</label>
            <input type="text" name="market_or_contract_ref" value="{{ old('market_or_contract_ref', $row->market_or_contract_ref ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Service(s)</label>
            <input type="text" name="service_names" value="{{ old('service_names', $row->service_names ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date d'intervention</label>
            <input type="text" name="intervention_date_text" value="{{ old('intervention_date_text', $row->intervention_date_text ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="JJ/MM/AAAA">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Activité achevée</label>
            @php
                $activityValue = old('activity_completed');
                if ($activityValue === null) {
                    if (isset($row->activity_completed)) {
                        $activityValue = $row->activity_completed ? 'oui' : 'non';
                    }
                }
            @endphp
            <select name="activity_completed" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white">
                <option value="" {{ $activityValue === null ? 'selected' : '' }}>-</option>
                <option value="oui" {{ $activityValue === 'oui' ? 'selected' : '' }}>OUI</option>
                <option value="non" {{ $activityValue === 'non' ? 'selected' : '' }}>NON</option>
            </select>
        </div>
        <div class="md:col-span-3">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Détails de la panne</label>
            <textarea name="failure_details" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('failure_details', $row->failure_details ?? '') }}</textarea>
        </div>
        <div class="md:col-span-3">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Observations</label>
            <textarea name="observations" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('observations', $row->observations ?? '') }}</textarea>
        </div>
    </div>

    @if ($isEdit)
        <div class="bg-emerald-50/60 rounded-xl border border-emerald-100 p-6 space-y-4">
            <div>
                <h3 class="text-sm font-semibold text-emerald-900">Importer PDF pour cette ligne</h3>
                <form method="POST" action="{{ route('maintenance-reports.corrective.pdf.upload', $row->id) }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-2 mt-2">
                    @csrf
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-semibold text-emerald-900" for="document_kind_edit">Type</label>
                        <select id="document_kind_edit" name="document_kind" class="px-3 py-2 border border-emerald-200 rounded-lg bg-white" required>
                            <option value="decharge">Décharge</option>
                            <option value="bon_retour">Bon de retour</option>
                            <option value="intervention_technique">Intervention technique</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="corrective_pdf_edit" type="file" name="corrective_pdf" accept="application/pdf" class="hidden" required>
                        <label for="corrective_pdf_edit" class="inline-flex items-center justify-center w-10 h-10 border border-red-200 text-red-600 rounded-lg bg-white hover:bg-red-50 cursor-pointer" title="Importer PDF">
                            <i class="fas fa-file-pdf"></i>
                        </label>
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Importer</button>
                    </div>
                </form>
            </div>

            <div>
                <h4 class="text-sm font-semibold text-emerald-900 mb-2">PDF liés</h4>
                @if (($pdfs ?? collect())->isEmpty())
                    <p class="text-sm text-emerald-800">Aucun PDF rattaché.</p>
                @else
                    <div class="space-y-2">
                        @foreach (($pdfs ?? []) as $pdf)
                            <div class="flex flex-wrap items-center gap-2 text-sm">
                                <span class="inline-flex items-center px-2 py-1 rounded-md border border-emerald-200 text-emerald-800 bg-white">
                                    {{ $pdf['document_label'] ?? '-' }}
                                </span>
                                <span class="text-emerald-900/90">{{ $pdf['original_name'] ?? '-' }}</span>
                                <a href="{{ $pdf['file_url'] ?? '#' }}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-1 rounded-md border border-blue-200 text-blue-700 hover:bg-blue-50">
                                    <i class="fas fa-eye mr-2"></i>Voir PDF
                                </a>
                                <form method="POST" action="{{ route('maintenance-reports.corrective.pdf.delete', $pdf['id']) }}" onsubmit="return confirm('Supprimer ce PDF ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-md border border-red-200 text-red-600 hover:bg-red-50" title="Supprimer PDF">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="px-5 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
            {{ $isEdit ? 'Mettre à jour' : 'Enregistrer' }}
        </button>
        <a href="{{ route('maintenance-reports.index', ['type' => 'curative']) }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50">
            Retour
        </a>
    </div>
</form>
@endsection
