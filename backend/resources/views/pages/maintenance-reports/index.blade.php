@extends('layouts.dashboard')

@section('page-title', 'Rapports d\'intervention interne')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Rapports / Intervention interne',
    'addRoute' => 'maintenance-reports.create',
    'addLabel' => 'Nouveau rapport',
    'addIcon' => 'fa-file-medical',
])

<div class="mb-4 flex items-center justify-between">
    <a href="{{ route('rapports') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
        <i class="fas fa-arrow-left mr-2"></i>Retour à la fenêtre Rapports
    </a>
    <div class="text-xs text-gray-500">
        Utilisez la recherche pour filtrer rapidement dans le tableau.
    </div>
</div>

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">{{ session('error') }}</div>
@endif

<div class="mb-4 bg-white rounded-xl shadow-md p-4">
    <form method="GET" action="{{ route('maintenance-reports.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Type</label>
            <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">Tous</option>
                <option value="preventive" {{ ($currentType ?? '') === 'preventive' ? 'selected' : '' }}>Préventive</option>
                <option value="curative" {{ ($currentType ?? '') === 'curative' ? 'selected' : '' }}>Curative</option>
                <option value="diagnostic" {{ ($currentType ?? '') === 'diagnostic' ? 'selected' : '' }}>Diagnostic</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Statut</label>
            <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">Tous</option>
                <option value="draft" {{ ($currentStatus ?? '') === 'draft' ? 'selected' : '' }}>Brouillon</option>
                <option value="submitted" {{ ($currentStatus ?? '') === 'submitted' ? 'selected' : '' }}>Soumis</option>
                <option value="validated" {{ ($currentStatus ?? '') === 'validated' ? 'selected' : '' }}>Validé</option>
                <option value="closed" {{ ($currentStatus ?? '') === 'closed' ? 'selected' : '' }}>Clôturé</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Service</label>
            <select name="service_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">Tous les services</option>
                @foreach(($serviceOptions ?? []) as $service)
                    <option value="{{ $service->id }}" {{ (int) ($selectedServiceId ?? 0) === (int) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date début</label>
            <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date fin</label>
            <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Recherche</label>
            <input type="text" name="q" value="{{ $searchTerm ?? '' }}" placeholder="N° rapport, équipement, technicien..." class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="md:col-span-6 flex flex-wrap gap-2">
            <button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Filtrer</button>
            <a href="{{ route('maintenance-reports.index', ['type' => ($currentType ?? '')]) }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Réinitialiser</a>
        </div>
    </form>
</div>

@if (($currentType ?? null) !== 'curative')
    @include('components.table', [
        'data' => $reportsData ?? [],
        'showAddButton' => false,
        'showImportAction' => false,
        'showExportAction' => false,
        'showEditAction' => false,
        'showDeleteAction' => false,
        'showFillAction' => false,
        'showCloseAction' => false,
        'columns' => [
            ['key' => 'numero', 'label' => 'N° Rapport', 'visible' => true, 'type' => 'text'],
            ['key' => 'type', 'label' => 'Type', 'visible' => true, 'type' => 'text'],
            ['key' => 'date_intervention', 'label' => 'Date intervention', 'visible' => true, 'type' => 'date'],
            ['key' => 'equipement', 'label' => 'Équipement', 'visible' => true, 'type' => 'text'],
            ['key' => 'service', 'label' => 'Service', 'visible' => true, 'type' => 'text'],
            ['key' => 'technicien', 'label' => 'Technicien', 'visible' => true, 'type' => 'text'],
            ['key' => 'duree', 'label' => 'Durée', 'visible' => true, 'type' => 'text'],
            ['key' => 'statut', 'label' => 'Statut', 'visible' => true, 'type' => 'status'],
        ],
    ])
@endif

@if (($currentType ?? null) === 'curative')
    <div class="mt-8">
        <div class="rounded-xl border border-blue-100 bg-blue-50/70 p-4">
            <p class="text-sm text-blue-900 mb-3">Maintenance corrective: import PDF uniquement.</p>
            <form method="POST" action="{{ route('maintenance-reports.import-corrective-pdf') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                @csrf

                <div class="flex items-center gap-2">
                    <label for="document_kind" class="text-sm font-semibold text-gray-700">Type</label>
                    <select id="document_kind" name="document_kind" class="px-4 py-2 border border-gray-300 rounded-lg bg-white" required>
                        <option value="decharge">Décharge</option>
                        <option value="bon_retour">Bon de retour</option>
                        <option value="intervention_technique">Intervention technique</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <input id="corrective_pdf" type="file" name="corrective_pdf" accept="application/pdf" class="hidden" required>
                    <label for="corrective_pdf" class="inline-flex items-center justify-center w-11 h-11 border border-red-200 text-red-600 rounded-lg bg-white hover:bg-red-50 cursor-pointer" title="Importer PDF">
                        <i class="fas fa-file-pdf"></i>
                    </label>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Importer</button>
                </div>

                <p class="text-xs text-gray-600 w-full">Format autorisé: PDF (max 20 MB).</p>
            </form>

            <div class="mt-4 border-t border-blue-100 pt-4">
                <h3 class="text-sm font-semibold text-gray-800 mb-2">PDF importés</h3>

                @if (($correctivePdfDocuments ?? collect())->isEmpty())
                    <p class="text-sm text-gray-600">Aucun PDF importé pour le moment.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm border border-gray-200 rounded-lg bg-white">
                            <thead class="bg-gray-50 text-gray-700">
                                <tr>
                                    <th class="text-left px-3 py-2 border-b">Type</th>
                                    <th class="text-left px-3 py-2 border-b">Fichier PDF</th>
                                    <th class="text-left px-3 py-2 border-b">Date</th>
                                    <th class="text-left px-3 py-2 border-b">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (($correctivePdfDocuments ?? collect()) as $document)
                                    <tr>
                                        <td class="px-3 py-2 border-b">{{ $document['document_label'] ?? '-' }}</td>
                                        <td class="px-3 py-2 border-b">{{ $document['file_name'] ?? '-' }}</td>
                                        <td class="px-3 py-2 border-b">{{ $document['last_modified'] ?? '-' }}</td>
                                        <td class="px-3 py-2 border-b">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ $document['file_url'] ?? '#' }}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-1.5 rounded-md border border-blue-200 text-blue-700 hover:bg-blue-50">
                                                    <i class="fas fa-eye mr-2"></i>Voir PDF
                                                </a>
                                                <form method="POST" action="{{ route('maintenance-reports.delete-corrective-pdf') }}" onsubmit="return confirm('Supprimer ce PDF ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="stored_path" value="{{ $document['stored_path'] ?? '' }}">
                                                    <button type="submit" class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-red-200 text-red-600 hover:bg-red-50" title="Supprimer PDF">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif
@endsection
