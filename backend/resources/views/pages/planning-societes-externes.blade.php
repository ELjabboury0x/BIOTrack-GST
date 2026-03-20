@extends('layouts.dashboard')

@section('page-title', 'Planning Sociétés Externes')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Planning Sociétés Externes',
    'addRoute' => 'planning.create',
    'addLabel' => 'Ajouter un planning',
    'addIcon' => 'fa-calendar-plus'
])

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
        {{ session('error') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-md p-8 text-gray-600">
    <h2 class="text-xl font-bold text-gray-800 mb-2">Planning Sociétés Externes</h2>
    <p class="mb-4">Planification des interventions des sociétés externes.</p>
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <a href="{{ route('dashboard') }}" class="px-3 py-1.5 text-xs rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Tableau de bord</a>
        <a href="{{ route('planning.index', ['date_from' => now()->format('Y-m-d'), 'date_to' => now()->addDays(14)->format('Y-m-d')]) }}" class="px-3 py-1.5 text-xs rounded-lg border border-amber-300 text-amber-700 bg-amber-50 hover:bg-amber-100">Vue 2 semaines</a>
    </div>

    <div class="mb-6 p-4 border border-gray-200 rounded-xl bg-gray-50">
        <div class="text-sm font-semibold text-gray-700 mb-3">Sélecteur d’intervalle de dates</div>
        <div class="mb-3 flex flex-wrap gap-2">
            <button type="button" class="range-chip px-3 py-1.5 text-xs rounded-full border border-gray-300 bg-white hover:bg-gray-100" data-range="30">30 jours</button>
            <button type="button" class="range-chip px-3 py-1.5 text-xs rounded-full border border-gray-300 bg-white hover:bg-gray-100" data-range="90">90 jours</button>
            <button type="button" class="range-chip px-3 py-1.5 text-xs rounded-full border border-gray-300 bg-white hover:bg-gray-100" data-range="180">180 jours</button>
            <button type="button" class="range-chip px-3 py-1.5 text-xs rounded-full border border-gray-300 bg-white hover:bg-gray-100" data-range="365">1 an</button>
        </div>

        <div class="mb-4 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-3">
            <form id="planning-filter-form" method="GET" action="{{ route('planning.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Société</label>
                    <select name="company_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">Toutes les sociétés</option>
                        @foreach(($companies ?? collect()) as $company)
                            <option value="{{ $company->id }}" {{ (int) ($selectedCompanyId ?? 0) === (int) $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date début</label>
                    <input id="date_from" type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date fin</label>
                    <input id="date_to" type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div class="flex items-end gap-2">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Filtrer</button>
                    <a href="{{ route('planning.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700">Réinitialiser</a>
                </div>
            </form>

            <div class="flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('planning.import-excel') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                    @csrf
                    <input type="file" name="contracts_file" accept=".xlsx,.xls" required class="text-sm text-gray-700 max-w-[260px] border border-gray-300 rounded-lg px-2 py-1.5 bg-white">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 inline-flex items-center">
                        <i class="fas fa-file-import mr-2"></i>Importer Excel
                    </button>
                </form>

                <form method="POST" action="{{ route('planning.sync-contracts') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 inline-flex items-center">
                        <i class="fas fa-sync-alt mr-2"></i>Synchroniser depuis Contrats
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if(!empty($dateFrom) || !empty($dateTo))
        <div class="mb-4 text-xs text-gray-500">
            Période sélectionnée: {{ $dateFrom ?: '...'}} → {{ $dateTo ?: '...'}}
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
        <div class="p-4 rounded-xl bg-blue-50 border border-blue-100">
            <p class="text-xs text-blue-700 uppercase tracking-wide">Total interventions planifiées</p>
            <p class="text-2xl font-bold text-blue-800 mt-1">{{ count($planningData ?? []) }}</p>
        </div>
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-100">
            <p class="text-xs text-emerald-700 uppercase tracking-wide">Sociétés dans la période</p>
            <p class="text-2xl font-bold text-emerald-800 mt-1">{{ (int) ($companiesInPeriodCount ?? 0) }}</p>
        </div>
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-100">
            <p class="text-xs text-amber-700 uppercase tracking-wide">Trimestres proches (2 semaines)</p>
            <p class="text-2xl font-bold text-amber-800 mt-1">{{ count($upcomingRows ?? []) }}</p>
        </div>
        <div class="p-4 rounded-xl bg-indigo-50 border border-indigo-100">
            <p class="text-xs text-indigo-700 uppercase tracking-wide">Vue trimestrielle</p>
            <p class="text-2xl font-bold text-indigo-800 mt-1">{{ count($quarterDashboardRows ?? []) }} sociétés</p>
        </div>
    </div>

    <div class="mb-6 p-4 border border-gray-200 rounded-xl">
        <h3 class="font-semibold text-gray-800 mb-3">Trimestres proches (tableau de bord)</h3>
        @if(count($upcomingRows ?? []) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                @foreach(($upcomingRows ?? []) as $row)
                    <a href="{{ $row['edit_url'] ?? route('planning.index') }}" class="block p-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition-colors" title="Ouvrir le planning">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-gray-800 text-sm">{{ $row['societe'] }}</p>
                            <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700">{{ $row['trimestre'] }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">{{ $row['date'] }}</p>
                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $row['description'] }}</p>
                        <p class="text-xs mt-2 @if(($row['jours'] ?? 9999) <= 7) text-red-600 font-semibold @elseif(($row['jours'] ?? 9999) <= 14) text-amber-600 font-semibold @else text-emerald-700 font-semibold @endif">
                            @if(($row['jours'] ?? null) !== null)
                                J-{{ max(0, (int) $row['jours']) }}
                            @endif
                        </p>
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-500">Aucun trimestre proche dans les 14 prochains jours.</p>
        @endif
    </div>

    <div class="mb-6 p-4 border border-gray-200 rounded-xl overflow-x-auto">
        <h3 class="font-semibold text-gray-800 mb-3">Données Contrats de maintenance (ordre officiel)</h3>
        <p class="text-xs text-gray-500 mb-3">
            Format import supporte: <strong>SOCIETE, MARQUE, MODELE, N° SERIE, DATE ORDRE DE SERVICE, TRIMESTRE1..TRIMESTRE8, SERVICE(S)</strong>.
            Dates attendues au format <strong>YYYY-MM-DD</strong>.
        </p>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border border-gray-200">
                <tr>
                    <th class="px-3 py-2 text-left">Société</th>
                    <th class="px-3 py-2 text-left">Marque</th>
                    <th class="px-3 py-2 text-left">Modèle</th>
                    <th class="px-3 py-2 text-left">N° de série</th>
                    <th class="px-3 py-2 text-left">Date d'ordre de service</th>
                    <th class="px-3 py-2 text-left">Trimestre 1</th>
                    <th class="px-3 py-2 text-left">Trimestre 2</th>
                    <th class="px-3 py-2 text-left">Trimestre 3</th>
                    <th class="px-3 py-2 text-left">Trimestre 4</th>
                    <th class="px-3 py-2 text-left">Trimestre 5</th>
                    <th class="px-3 py-2 text-left">Trimestre 6</th>
                    <th class="px-3 py-2 text-left">Trimestre 7</th>
                    <th class="px-3 py-2 text-left">Trimestre 8</th>
                    <th class="px-3 py-2 text-left">Service(s)</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($contractRows ?? []) as $row)
                    <tr class="border-b border-gray-200">
                        <td class="px-3 py-2">{{ $row['societe'] }}</td>
                        <td class="px-3 py-2">{{ $row['marque'] }}</td>
                        <td class="px-3 py-2">{{ $row['modele'] }}</td>
                        <td class="px-3 py-2">{{ $row['numero_serie'] }}</td>
                        <td class="px-3 py-2">{{ $row['date_ordre_service'] }}</td>
                        <td class="px-3 py-2">{{ $row['trimestre_1'] }}</td>
                        <td class="px-3 py-2">{{ $row['trimestre_2'] }}</td>
                        <td class="px-3 py-2">{{ $row['trimestre_3'] }}</td>
                        <td class="px-3 py-2">{{ $row['trimestre_4'] }}</td>
                        <td class="px-3 py-2">{{ $row['trimestre_5'] }}</td>
                        <td class="px-3 py-2">{{ $row['trimestre_6'] }}</td>
                        <td class="px-3 py-2">{{ $row['trimestre_7'] }}</td>
                        <td class="px-3 py-2">{{ $row['trimestre_8'] }}</td>
                        <td class="px-3 py-2">{{ $row['services'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="px-3 py-4 text-center text-gray-500">Aucune donnée contrat trouvée pour la période sélectionnée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mb-3 text-sm text-gray-600 bg-blue-50 border border-blue-100 rounded-lg px-4 py-3">
        Pour supprimer une ligne de planning: cliquez sur l'icone <i class="fas fa-trash text-red-600"></i> dans la colonne <strong>Actions</strong>, puis confirmez.
    </div>

    @include('components.table', [
        'data' => $planningData ?? [],
        'showAddButton' => false,
        'showImportAction' => false,
        'showExportAction' => true,
        'deleteEntityLabel' => 'ce planning',
        'columns' => [
            ['key' => 'societe', 'label' => 'Société', 'visible' => true, 'type' => 'text'],
            ['key' => 'trimestre', 'label' => 'Trimestre', 'visible' => true, 'type' => 'text'],
            ['key' => 'date_prevue', 'label' => 'Date prévue', 'visible' => true, 'type' => 'text'],
            ['key' => 'intervenant', 'label' => 'Intervenant', 'visible' => true, 'type' => 'text'],
            ['key' => 'description', 'label' => 'Description', 'visible' => true, 'type' => 'text'],
            ['key' => 'statut', 'label' => 'Statut', 'visible' => true, 'type' => 'status'],
        ]
    ])
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chips = Array.from(document.querySelectorAll('.range-chip'));
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');

    function formatDate(value) {
        const year = value.getFullYear();
        const month = String(value.getMonth() + 1).padStart(2, '0');
        const day = String(value.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            const days = parseInt(chip.dataset.range || '0', 10);
            if (!days || !dateFrom || !dateTo) {
                return;
            }

            const today = new Date();
            const end = new Date(today);
            end.setDate(end.getDate() + days);

            dateFrom.value = formatDate(today);
            dateTo.value = formatDate(end);
        });
    });
});
</script>
@endsection
@endsection
