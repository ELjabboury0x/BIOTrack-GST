@extends('layouts.dashboard')

@section('page-title', 'Rapports d\'intervention interne')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Rapports / Intervention interne',
    'addRoute' => null,
    'addLabel' => null,
    'addIcon' => null,
])



@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">{{ session('error') }}</div>
@endif

@if (($currentType ?? null) !== 'curative')
    <div class="mb-4 bg-white rounded-xl shadow-md p-4">
        <form method="GET" action="{{ route('maintenance-reports.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Type</label>
                <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">Tous</option>
                    <option value="preventive" {{ ($currentType ?? '') === 'preventive' ? 'selected' : '' }}>Préventive</option>
                    <option value="curative" {{ ($currentType ?? '') === 'curative' ? 'selected' : '' }}>Corrective</option>
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
@endif

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
            ['key' => 'date_intervention', 'label' => 'Date d\'intervention', 'visible' => true, 'type' => 'date'],
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
        <div class="mb-4 flex flex-wrap justify-end gap-3">
            <a href="{{ route('maintenance-reports.corrective.manual') }}" class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                <i class="fas fa-plus"></i>
                <span>Ajouter manuel</span>
            </a>

            <form id="corrective-import-form" method="POST" action="{{ route('maintenance-reports.import-corrective') }}" enctype="multipart/form-data" class="inline-flex">
                @csrf
                <input id="corrective-import-file" type="file" name="corrective_file" accept=".xlsx,.xls" required class="hidden">
                <button id="corrective-import-trigger" type="button" class="inline-flex h-10 items-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                    <i class="fas fa-file-excel"></i>
                    <span>Importer Excel</span>
                </button>
            </form>

            <a href="{{ route('maintenance-reports.corrective.export-excel', request()->query()) }}" class="inline-flex h-10 items-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                <i class="fas fa-file-excel"></i>
                <span>Exporter Excel</span>
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-100 px-6 py-4">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-800">Bilan maintenance corrective</h3>
                </div>
                <span class="text-sm text-gray-500">{{ ($bilanCorrectiveData ?? collect())->count() }} ligne(s)</span>
            </div>

            <div class="mt-2 border-t border-gray-100 pt-4">
                <div class="mb-3">
                    <input id="corrective-live-filter" type="text" placeholder="Société, équipement, marque, service..." class="w-full md:w-96 px-3 py-2 border border-gray-300 rounded-lg bg-white">
                </div>

                @if (($bilanCorrectiveData ?? collect())->isEmpty())
                    <p class="text-sm text-gray-600">Aucune donnée importée pour le moment.</p>
                @else
                    <div class="overflow-x-auto">
                        <table id="corrective-table" class="min-w-full text-sm border border-gray-200 rounded-lg bg-white">
                            <thead class="bg-gray-50 text-gray-700">
                                <tr>
                                    <th class="text-left px-3 py-2 border-b">Société</th>
                                    <th class="text-left px-3 py-2 border-b">Désignation de l'équipement</th>
                                    <th class="text-left px-3 py-2 border-b">Marque</th>
                                    <th class="text-left px-3 py-2 border-b">Modèle</th>
                                    <th class="text-left px-3 py-2 border-b">N° de série</th>
                                    <th class="text-left px-3 py-2 border-b">N° de Marché/Contrat de maintenance</th>
                                    <th class="text-left px-3 py-2 border-b">Service(s)</th>
                                    <th class="text-left px-3 py-2 border-b">Date d'intervention</th>
                                    <th class="text-left px-3 py-2 border-b">Activité achevée OUI / NON</th>
                                    <th class="text-left px-3 py-2 border-b">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (($bilanCorrectiveData ?? collect()) as $row)
                                    <tr class="corrective-row cursor-pointer hover:bg-emerald-50/60" data-row-id="{{ $row['id'] }}">
                                        <td class="px-3 py-2 border-b">{{ $row['societe'] ?? '-' }}</td>
                                        <td class="px-3 py-2 border-b">{{ $row['equipement'] ?? '-' }}</td>
                                        <td class="px-3 py-2 border-b">{{ $row['marque'] ?? '-' }}</td>
                                        <td class="px-3 py-2 border-b">{{ $row['modele'] ?? '-' }}</td>
                                        <td class="px-3 py-2 border-b">{{ $row['numero_serie'] ?? '-' }}</td>
                                        <td class="px-3 py-2 border-b">{{ $row['marche_contrat'] ?? '-' }}</td>
                                        <td class="px-3 py-2 border-b">{{ $row['services'] ?? '-' }}</td>
                                        <td class="px-3 py-2 border-b">{{ $row['date_intervention'] ?? '-' }}</td>
                                        <td class="px-3 py-2 border-b">
                                            @if (($row['activity_completed'] ?? null) === true)
                                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold border border-emerald-200 bg-emerald-50 text-emerald-700">OUI</span>
                                            @elseif (($row['activity_completed'] ?? null) === false)
                                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold border border-red-200 bg-red-50 text-red-700">NON</span>
                                            @else
                                                <span class="text-sm text-gray-500">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 border-b">
                                            <div class="flex items-center gap-2">
                                                <button type="button" class="toggle-details-btn inline-flex items-center px-3 py-1.5 rounded-md border border-blue-200 text-blue-700 hover:bg-blue-50" data-details-id="{{ $row['id'] }}">
                                                    <i class="fas fa-chevron-down mr-2"></i>Détails
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr class="corrective-details hidden" data-details-id="{{ $row['id'] }}">
                                        <td colspan="10" class="px-4 py-3 border-b bg-emerald-50/40">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                                                <div>
                                                    <p class="font-semibold text-emerald-900 mb-1">Détails de la panne</p>
                                                    <p class="text-emerald-900/90">{{ $row['details_panne'] ?? '-' }}</p>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-emerald-900 mb-1">Observations</p>
                                                    <p class="text-emerald-900/90">{{ $row['observations'] ?? '-' }}</p>
                                                </div>
                                            </div>

                                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <p class="font-semibold text-emerald-900 mb-2">Importer PDF</p>
                                                    <form method="POST" action="{{ route('maintenance-reports.import-corrective-pdf') }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-2">
                                                        @csrf
                                                        <input type="hidden" name="corrective_id" value="{{ $row['id'] }}">
                                                        <div class="flex items-center gap-2">
                                                            <label class="text-sm font-semibold text-emerald-900" for="document_kind_{{ $row['id'] }}">Type</label>
                                                            <select id="document_kind_{{ $row['id'] }}" name="document_kind" class="px-3 py-2 border border-emerald-200 rounded-lg bg-white" required>
                                                                <option value="decharge">Décharge</option>
                                                                <option value="bon_retour">Bon de retour</option>
                                                                <option value="intervention_technique">Intervention technique</option>
                                                            </select>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <input id="corrective_pdf_row_{{ $row['id'] }}" type="file" name="corrective_pdf" accept="application/pdf" class="hidden" required>
                                                            <label for="corrective_pdf_row_{{ $row['id'] }}" class="inline-flex items-center justify-center w-10 h-10 border border-red-200 text-red-600 rounded-lg bg-white hover:bg-red-50 cursor-pointer" title="Importer PDF">
                                                                <i class="fas fa-file-pdf"></i>
                                                            </label>
                                                            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">Importer</button>
                                                        </div>
                                                    </form>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-emerald-900 mb-2">Rapport</p>
                                                    <a href="{{ route('maintenance-reports.create', ['type' => 'curative', 'corrective_id' => $row['id']]) }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                                                        <i class="fas fa-file-medical mr-2"></i>Créer rapport
                                                    </a>
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-emerald-900 mb-2">PDF liés</p>
                                                    @if (empty($row['pdfs']) || ($row['pdfs'] ?? collect())->isEmpty())
                                                        <p class="text-sm text-emerald-800">Aucun PDF rattaché.</p>
                                                    @else
                                                        <div class="space-y-2">
                                                            @foreach (($row['pdfs'] ?? []) as $pdf)
                                                                <div class="flex flex-wrap items-center gap-2 text-sm">
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-md border border-emerald-200 text-emerald-800 bg-white">
                                                                        {{ $pdf['document_label'] ?? '-' }}
                                                                    </span>
                                                                    <span class="text-emerald-900/90">{{ $pdf['original_name'] ?? '-' }}</span>
                                                                    <a href="{{ $pdf['file_url'] ?? '#' }}" target="_blank" rel="noopener" class="inline-flex items-center px-3 py-1 rounded-md border border-blue-200 text-blue-700 hover:bg-blue-50">
                                                                        <i class="fas fa-eye mr-2"></i>Voir PDF
                                                                    </a>
                                                                    <form method="POST" action="{{ route('maintenance-reports.delete-corrective-pdf') }}" onsubmit="return confirm('Supprimer ce PDF ?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <input type="hidden" name="pdf_id" value="{{ $pdf['id'] }}">
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const importForm = document.getElementById('corrective-import-form');
    const importFile = document.getElementById('corrective-import-file');
    const importTrigger = document.getElementById('corrective-import-trigger');

    if (importForm && importFile && importTrigger) {
        importTrigger.addEventListener('click', function () {
            importFile.click();
        });

        importFile.addEventListener('change', function () {
            if (!importFile.files || importFile.files.length === 0) {
                return;
            }

            importForm.submit();
        });
    }

    const input = document.getElementById('corrective-live-filter');
    const table = document.getElementById('corrective-table');

    if (!input || !table) {
        return;
    }

    const rows = Array.from(table.querySelectorAll('tbody .corrective-row'));
    const detailsRows = Array.from(table.querySelectorAll('tbody .corrective-details'));

    const applyFilter = () => {
        const needle = input.value.trim().toLowerCase();
        rows.forEach((row) => {
            const text = row.innerText.toLowerCase();
            const isVisible = needle === '' || text.includes(needle);
            row.style.display = isVisible ? '' : 'none';

            const rowId = row.getAttribute('data-row-id');
            const details = table.querySelector(`.corrective-details[data-details-id="${rowId}"]`);
            if (details) {
                details.style.display = isVisible ? details.style.display : 'none';
            }
        });
    };

    input.addEventListener('input', applyFilter);

    rows.forEach((row) => {
        row.addEventListener('click', (event) => {
            if (event.target.closest('form') || event.target.closest('a') || event.target.closest('button') || event.target.tagName === 'SELECT' || event.target.tagName === 'OPTION') {
                return;
            }

            const rowId = row.getAttribute('data-row-id');
            const details = table.querySelector(`.corrective-details[data-details-id="${rowId}"]`);
            if (!details) {
                return;
            }

            details.classList.toggle('hidden');
        });
    });

    const activitySelects = Array.from(document.querySelectorAll('.corrective-activity-select'));
    activitySelects.forEach((select) => {
        select.addEventListener('change', (event) => {
            const form = event.target.closest('form');
            if (form && typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }
            if (form) {
                form.submit();
            }
        });
    });
});
</script>
@endsection
