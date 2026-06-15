@extends('layouts.dashboard')

@section('page-title', 'Gestion des Équipements')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => $breadcrumb ?? 'Dashboard > Équipements'
])

<div class="mb-4 flex flex-wrap justify-end gap-3">
    @if(auth()->user()?->role !== 'major')
    <form id="equipments-import-form" method="POST" action="{{ route('equipements.import-excel') }}" enctype="multipart/form-data" class="inline-flex">
        @csrf
        <input type="hidden" name="replace_existing" value="0">
        <input id="equipments-import-file" type="file" name="excel_file" accept=".xlsx,.xls" required class="hidden">
        <button id="equipments-import-trigger" type="button" class="inline-flex h-10 items-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300">
            <i class="fas fa-file-excel"></i>
            <span>Importer</span>
        </button>
    </form>

    <a href="{{ route('equipments.create') }}"
       class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
        <i class="fas fa-plus"></i>
        <span>Ajouter manuellement</span>
    </a>
    @endif

    <div x-data="{ open: false }" class="relative">
        <button type="button"
                @click="open = !open"
                class="inline-flex h-10 items-center gap-2 rounded-lg bg-slate-700 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300">
            <i class="fas fa-file-export"></i>
            <span>Exporter</span>
        </button>
        <div x-show="open"
             @click.away="open = false"
             x-transition
             class="absolute right-0 mt-2 w-44 overflow-hidden rounded-lg border border-gray-100 bg-white shadow-xl z-50">
            <button type="button"
                    @click="open = false; exportTableToExcel()"
                    class="w-full px-4 py-2 text-left text-sm text-gray-700 transition-colors hover:bg-gray-50">
                Excel
            </button>
            <button type="button"
                    @click="open = false; exportTableToPdf()"
                    class="w-full border-t border-gray-100 px-4 py-2 text-left text-sm text-gray-700 transition-colors hover:bg-gray-50">
                PDF
            </button>
        </div>
    </div>
</div>

@if(auth()->user()?->role !== 'major')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('equipments-import-form');
    const fileInput = document.getElementById('equipments-import-file');
    const triggerBtn = document.getElementById('equipments-import-trigger');

    if (!form || !fileInput || !triggerBtn) {
        return;
    }

    triggerBtn.addEventListener('click', function () {
        fileInput.click();
    });

    fileInput.addEventListener('change', function () {
        if (!fileInput.files || fileInput.files.length === 0) {
            return;
        }

        form.submit();
    });
});
</script>
@endif

<div class="mb-4 bg-white rounded-xl shadow-md p-4">
    <form method="GET" action="{{ route('equipements') }}" class="flex flex-col md:flex-row md:items-end gap-4" id="equipments-filter-form">
        <div class="w-full md:w-72">
            <label for="equipments-hospital-select" class="block text-sm font-semibold text-gray-700 mb-2">Hôpital</label>
            <select name="hospital_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg" id="equipments-hospital-select">
                <option value="">Tous les hôpitaux</option>
                @foreach (collect($hospitals ?? [])->filter() as $hospital)
                    <option value="{{ data_get($hospital, 'id') }}" {{ (int) ($selectedHospitalId ?? 0) === (int) data_get($hospital, 'id') ? 'selected' : '' }}>
                        {{ data_get($hospital, 'name') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="w-full md:w-72">
            <label for="equipments-service-select" class="block text-sm font-semibold text-gray-700 mb-2">Service</label>
            <select name="service_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg" id="equipments-service-select">
                <option value="">Tous les services</option>
                @foreach (collect($services ?? [])->filter() as $service)
                    <option value="{{ data_get($service, 'id') }}" {{ (int) ($selectedServiceId ?? 0) === (int) data_get($service, 'id') ? 'selected' : '' }}>
                        {{ data_get($service, 'name') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="w-full md:w-72">
            <label for="equipments-company-select" class="block text-sm font-semibold text-gray-700 mb-2">Société</label>
            <select name="company_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg" id="equipments-company-select">
                <option value="">Toutes les sociétés</option>
                <option value="-1" {{ (int) ($selectedCompanyId ?? 0) === -1 ? 'selected' : '' }}>Inconnue</option>
                @foreach (collect($companies ?? [])->filter() as $company)
                    <option value="{{ $company->id }}" {{ (int) ($selectedCompanyId ?? 0) === (int) $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-full md:flex-1">
            <label for="equipments-search-input" class="block text-sm font-semibold text-gray-700 mb-2">Recherche</label>
            <input type="text" name="q" value="{{ $searchTerm ?? '' }}" placeholder="N° inventaire, désignation, N° série..." class="w-full px-4 py-2 border border-gray-300 rounded-lg" id="equipments-search-input" autocomplete="off">
        </div>
        <input type="hidden" name="sort" value="{{ ($sortDirection ?? 'desc') === 'asc' ? 'asc' : 'desc' }}" id="equipments-sort-input">
        <div class="flex gap-2">
            @php $nextSort = (($sortDirection ?? 'desc') === 'asc') ? 'desc' : 'asc'; @endphp
            <button type="submit" name="sort" value="{{ $nextSort }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">
                {{ (($sortDirection ?? 'desc') === 'asc') ? 'Plus récent en haut' : 'Plus ancien en haut' }}
            </button>
            <button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Filtrer</button>
            <a href="{{ route('equipements') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Réinitialiser</a>
        </div>
    </form>
</div>

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

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="bg-white rounded-xl shadow-md border border-gray-100">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4">
        <h3 class="text-base font-semibold text-gray-800">Liste des équipements</h3>
        <div class="flex items-center gap-3">
            <span id="equipments-selection-count" class="hidden px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">0 sélectionné(s)</span>
            <button type="button" id="bulk-delete-trigger" onclick="openBulkDeleteModalFromSelection()" class="hidden px-3 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-r from-rose-600 to-red-600 text-white hover:from-rose-700 hover:to-red-700">
                <i class="fas fa-trash-alt mr-1"></i> Supprimer la sélection
            </button>
            <span id="equipments-count" class="text-sm text-gray-500">{{ ($equipementsData->total() ?? 0) }} équipement(s)</span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[960px]">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase w-10" onclick="event.stopPropagation()">
                        <input id="select-all-equipments" type="checkbox" onchange="toggleSelectAllEquipments(event)" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">N° inventaire</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Désignation</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">N° de série</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Statut</th>
                </tr>
            </thead>
            <tbody id="equipments-table-body">
                @forelse(($equipementsData ?? collect()) as $equipment)
                    @php
                        $statusValue = strtolower(trim((string) ($equipment['statut_etat'] ?? $equipment['status'] ?? '-')));
                        $statusTone = 'bg-indigo-50 text-indigo-700 border-indigo-200';

                        if (in_array($statusValue, ['actif', 'fonctionnel', 'en service', 'opérationnel', 'ok'], true)) {
                            $statusTone = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        } elseif (in_array($statusValue, ['reserve', 'réserve', 'en maintenance', 'maintenance', 'en_attente', 'en attente'], true)) {
                            $statusTone = 'bg-amber-50 text-amber-700 border-amber-200';
                        } elseif (in_array($statusValue, ['panne', 'hors_service', 'hors service'], true)) {
                            $statusTone = 'bg-rose-50 text-rose-700 border-rose-200';
                        } elseif (in_array($statusValue, ['inactif', 'inactive', 'unknown', 'inconnu', '-'], true)) {
                            $statusTone = 'bg-slate-100 text-slate-700 border-slate-200';
                        }
                    @endphp
                    @php
                        $sid = $equipment['id'];
                        $sval = strtolower(trim((string)($equipment['statut_etat'] ?? $equipment['status'] ?? '')));
                        if (in_array($sval, ['fonctionnel','actif','en service','ok'])) {
                            $sBg='bg-emerald-50'; $sTxt='text-emerald-700'; $sBdr='border-emerald-300'; $sDot='bg-emerald-500'; $sIcon='fa-circle-check'; $sLabel='Fonctionnel';
                        } elseif (in_array($sval, ['en panne','panne','hors service','hors_service'])) {
                            $sBg='bg-rose-50'; $sTxt='text-rose-700'; $sBdr='border-rose-300'; $sDot='bg-rose-500'; $sIcon='fa-circle-xmark'; $sLabel='En panne';
                        } elseif (in_array($sval, ['reforme','réformé','reformé'])) {
                            $sBg='bg-slate-100'; $sTxt='text-slate-600'; $sBdr='border-slate-300'; $sDot='bg-slate-400'; $sIcon='fa-ban'; $sLabel='Réformé';
                        } elseif (in_array($sval, ['en maintenance','maintenance','reserve','réserve'])) {
                            $sBg='bg-amber-50'; $sTxt='text-amber-700'; $sBdr='border-amber-300'; $sDot='bg-amber-500'; $sIcon='fa-screwdriver-wrench'; $sLabel=$equipment['statut_etat'] ?? $equipment['status'] ?? '-';
                        } else {
                            $sBg='bg-gray-100'; $sTxt='text-gray-500'; $sBdr='border-gray-300'; $sDot='bg-gray-400'; $sIcon='fa-circle-question'; $sLabel=$equipment['statut_etat'] ?? $equipment['status'] ?? '-';
                        }
                    @endphp
                    <tr onclick='openEquipmentDetails(this, @json($equipment))' class="border-b border-gray-100 hover:bg-blue-50/40 cursor-pointer transition-colors equipment-row" data-equipment-id="{{ (int) ($equipment['id'] ?? 0) }}" data-search="{{ trim(implode(' ', [
                        (string) ($equipment['barcode'] ?? ''),
                        (string) ($equipment['equipment_description'] ?? ''),
                        (string) ($equipment['serial_number'] ?? ''),
                        (string) ($equipment['unit_name'] ?? ''),
                        (string) ($equipment['sector_name'] ?? ''),
                        (string) ($equipment['sector_description_value'] ?? ''),
                        (string) ($equipment['brand_name'] ?? ''),
                        (string) ($equipment['model_name'] ?? ''),
                        (string) ($equipment['market_label'] ?? ''),
                        (string) ($equipment['lot_number'] ?? ''),
                    ])) }}">
                        <td class="px-4 py-3 text-sm" onclick="event.stopPropagation()">
                            <input type="checkbox" class="equipment-select-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" value="{{ (int) ($equipment['id'] ?? 0) }}" onchange="toggleEquipmentSelection({{ (int) ($equipment['id'] ?? 0) }}, event)">
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-800 font-medium">{{ $equipment['barcode'] ?? '-' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700">{{ $equipment['equipment_description'] ?? '-' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-700">{{ $equipment['serial_number'] ?? '-' }}</td>
                        <td class="px-6 py-3 text-sm" onclick="event.stopPropagation()">
                            <button type="button"
                                    id="status-btn-{{ $sid }}"
                                    onclick="openStatusDropdown(event, {{ $sid }})"
                                    class="status-btn group inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold border-2 cursor-pointer transition-all duration-200 hover:scale-105 hover:shadow-md active:scale-95 {{ $sBg }} {{ $sTxt }} {{ $sBdr }}"
                                    data-equipment-id="{{ $sid }}">
                                <span class="relative flex h-2.5 w-2.5 flex-shrink-0">
                                    <span class="{{ in_array($sval, ['fonctionnel','actif','en service','ok']) ? 'animate-ping' : '' }} absolute inline-flex h-full w-full rounded-full opacity-60 {{ $sDot }}"></span>
                                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full {{ $sDot }}"></span>
                                </span>
                                <i class="fas {{ $sIcon }} text-[11px]"></i>
                                <span class="status-text">{{ $sLabel }}</span>
                                <svg class="w-3 h-3 flex-shrink-0 opacity-60 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                            Aucun équipement trouvé.
                        </td>
                    </tr>
                @endforelse
                <tr id="no-live-results" class="hidden">
                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                        Aucun équipement ne correspond à la recherche.
                    </td>
                </tr>

                <tr id="equipment-inline-details-row" class="hidden bg-gradient-to-r from-blue-50/80 via-indigo-50/70 to-purple-50/70 border-b border-blue-100 opacity-0 -translate-y-1 scale-[0.99] transition-all duration-300 ease-in-out">
                    <td colspan="5" class="px-6 py-5">
                        <div class="rounded-2xl bg-white/95 backdrop-blur border border-blue-100 shadow-lg overflow-hidden">
                            <div class="px-5 py-4 bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 text-white flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-sm md:text-base font-semibold">Détails équipement</h3>
                                    <p class="text-xs text-white/80 mt-1">Vue rapide technique et administrative</p>
                                </div>
                                <button type="button" onclick="closeEquipmentDetails()" class="text-white/90 hover:text-white text-lg leading-none">&times;</button>
                            </div>

                            <div class="p-5">
                                <div class="flex flex-wrap gap-2 mb-5">
                                    <span id="detail-pill-inv-badge" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        Inventaire: <span id="detail-pill-inv" class="ml-1">-</span>
                                    </span>
                                    <span id="detail-pill-status-badge" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        Statut : <span id="detail-pill-status" class="ml-1">-</span>
                                    </span>
                                    <span id="detail-pill-state-badge" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-violet-50 text-violet-700 border border-violet-200">
                                        État: <span id="detail-pill-state" class="ml-1">-</span>
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                                    <div class="rounded-xl border border-gray-200 p-4 bg-gradient-to-br from-white to-blue-50/40">
                                        <p class="text-sm font-semibold text-gray-800 mb-3">Informations équipement</p>
                                        <div class="space-y-2">
                                            <div><span class="font-semibold text-gray-700">N° inventaire:</span> <span id="detail-barcode" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">Description:</span> <span id="detail-description" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">N° de série:</span> <span id="detail-serial" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">Unité:</span> <span id="detail-unit" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">Secteur:</span> <span id="detail-sector" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">Description secteur:</span> <span id="detail-sector-description" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">Marque:</span> <span id="detail-brand" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">Modèle:</span> <span id="detail-model" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">Société:</span> <span id="detail-company" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">Marché:</span> <span id="detail-market" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">Lot:</span> <span id="detail-lot" class="text-gray-800">-</span></div>
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-gray-200 p-4 bg-gradient-to-br from-white to-indigo-50/40">
                                        <p class="text-sm font-semibold text-gray-800 mb-3">Réception / Garantie / État</p>
                                        <div class="space-y-2">
                                            <div><span class="font-semibold text-gray-700">Article:</span> <span id="detail-article" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">Date de réception provisoire:</span> <span id="detail-reception-provisoire" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">Durée de garantie:</span> <span id="detail-duree-garantie" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">Date de réception définitive:</span> <span id="detail-reception-definitive" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">Statut :</span> <span id="detail-status" class="text-gray-800">-</span></div>
                                            <div><span class="font-semibold text-gray-700">Statut / État:</span> <span id="detail-statut-etat" class="text-gray-800">-</span></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 border-t border-gray-200 pt-4">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Image et manuels (partagés par désignation)</h4>

                                    <div id="detail-image-wrapper" class="mb-4 hidden">
                                        <div class="inline-block rounded-lg border border-gray-200 bg-white p-2">
                                            <img id="detail-image" src="" alt="Image équipement" class="block max-w-full max-h-[26rem] object-contain rounded">
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <button id="detail-user-manual-link" type="button" class="hidden px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-200">
                                            Lire manuel d'utilisation
                                        </button>
                                        <button id="detail-technical-manual-link" type="button" class="hidden px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-200">
                                            Lire manuel technique
                                        </button>
                                        <span id="detail-no-docs" class="text-sm text-gray-500">Aucun fichier associé à cette désignation.</span>
                                    </div>
                                </div>

                                <div class="mt-6 border-t border-gray-200 pt-4">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-3">KPI maintenance de cet équipement</h4>

                                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
                                        <div class="rounded-lg border border-blue-100 bg-blue-50 px-3 py-2">
                                            <p class="text-[11px] text-blue-700 font-semibold uppercase tracking-wide">Rapports total</p>
                                            <p id="detail-kpi-total" class="text-xl font-bold text-blue-900">0</p>
                                        </div>
                                        <div class="rounded-lg border border-amber-100 bg-amber-50 px-3 py-2">
                                            <p class="text-[11px] text-amber-700 font-semibold uppercase tracking-wide">Ouverts</p>
                                            <p id="detail-kpi-open" class="text-xl font-bold text-amber-900">0</p>
                                        </div>
                                        <div class="rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2">
                                            <p class="text-[11px] text-emerald-700 font-semibold uppercase tracking-wide">Clôturés</p>
                                            <p id="detail-kpi-closed" class="text-xl font-bold text-emerald-900">0</p>
                                        </div>
                                        <div class="rounded-lg border border-violet-100 bg-violet-50 px-3 py-2">
                                            <p class="text-[11px] text-violet-700 font-semibold uppercase tracking-wide">Ce mois</p>
                                            <p id="detail-kpi-month" class="text-xl font-bold text-violet-900">0</p>
                                        </div>
                                    </div>

                                    <div class="rounded-xl border border-gray-200 bg-white p-3">
                                        <div style="position:relative; height:260px;">
                                            <canvas id="detail-kpi-chart"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5 pt-4 border-t border-gray-100 flex justify-end gap-2">
                                    <button type="button" onclick="openBulkUpdateModal(event)" id="detail-bulk-update-btn" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-lg hover:from-amber-600 hover:to-orange-600 text-sm">
                                        <i class="fas fa-edit mr-1"></i> Modifier en masse
                                    </button>
                                    <a id="detail-edit-url" href="#" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 text-sm">Modifier</a>
                                    <button type="button" id="detail-delete-btn" onclick="confirmDeleteEquipment()" class="px-4 py-2 bg-gradient-to-r from-rose-600 to-red-600 text-white rounded-lg hover:from-rose-700 hover:to-red-700 text-sm">
                                        <i class="fas fa-trash-alt mr-1"></i> Supprimer
                                    </button>
                                    <button type="button" onclick="closeEquipmentDetails()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">Fermer</button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div id="equipments-pagination" class="px-6 py-4 border-t border-gray-100">
        {{ $equipementsData->links() }}
    </div>

    <div class="px-6 py-3 text-xs text-gray-500 border-t border-gray-100">
        Cliquez sur une ligne pour afficher les détails juste en dessous.
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-confirm-modal" class="fixed inset-0 bg-black/50 z-[10000] hidden items-center justify-center px-4">
    <div class="w-full max-w-lg bg-white rounded-xl shadow-2xl overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-rose-600 to-red-600 text-white flex items-center justify-between">
            <div>
                <h3 id="delete-confirm-title" class="text-lg font-semibold">Confirmer la suppression</h3>
                <p class="text-sm text-white/80">Cette action est irréversible.</p>
            </div>
            <button type="button" onclick="closeDeleteConfirmModal()" class="text-white/90 hover:text-white text-2xl leading-none">&times;</button>
        </div>
        <div class="p-6">
            <p id="delete-confirm-message" class="text-sm text-gray-700 leading-relaxed">
                Voulez-vous vraiment supprimer cet équipement ?
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" onclick="closeDeleteConfirmModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Annuler</button>
                <button type="button" id="delete-confirm-submit" onclick="confirmDeleteAction()" class="px-5 py-2 bg-gradient-to-r from-rose-600 to-red-600 text-white rounded-lg hover:from-rose-700 hover:to-red-700 text-sm font-semibold">
                    <i class="fas fa-trash-alt mr-1"></i> Supprimer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Status Dropdown (Global - Position Fixed) -->
<div id="status-dropdown" class="fixed z-[9999] hidden" style="min-width:220px">
    <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden" style="box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
        <div class="px-3 py-2 bg-gray-50 border-b border-gray-100">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Changer le statut</p>
        </div>
        <div class="p-1.5 space-y-0.5">
            <button type="button" onclick="selectStatus('fonctionnel')" class="status-option w-full text-left px-3 py-2.5 rounded-xl text-sm font-semibold hover:bg-emerald-50 text-emerald-700 flex items-center gap-3 transition-all duration-150 group">
                <span class="w-8 h-8 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-200 transition-colors">
                    <i class="fas fa-circle-check text-emerald-600 text-sm"></i>
                </span>
                <div>
                    <div class="font-semibold">Fonctionnel</div>
                    <div class="text-[10px] text-emerald-500 font-normal">Équipement opérationnel</div>
                </div>
            </button>
            <button type="button" onclick="selectStatus('en panne')" class="status-option w-full text-left px-3 py-2.5 rounded-xl text-sm font-semibold hover:bg-rose-50 text-rose-700 flex items-center gap-3 transition-all duration-150 group">
                <span class="w-8 h-8 rounded-xl bg-rose-100 flex items-center justify-center flex-shrink-0 group-hover:bg-rose-200 transition-colors">
                    <i class="fas fa-circle-xmark text-rose-600 text-sm"></i>
                </span>
                <div>
                    <div class="font-semibold">En panne</div>
                    <div class="text-[10px] text-rose-500 font-normal">Hors service</div>
                </div>
            </button>
            <button type="button" onclick="selectStatus('reforme')" class="status-option w-full text-left px-3 py-2.5 rounded-xl text-sm font-semibold hover:bg-slate-100 text-slate-600 flex items-center gap-3 transition-all duration-150 group">
                <span class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0 group-hover:bg-slate-200 transition-colors">
                    <i class="fas fa-ban text-slate-500 text-sm"></i>
                </span>
                <div>
                    <div class="font-semibold">Réformé</div>
                    <div class="text-[10px] text-slate-400 font-normal">Retiré du service</div>
                </div>
            </button>
        </div>
    </div>
</div>

<!-- Bulk Update Modal (popover mode anchored to equipment details) -->
<div id="bulk-update-modal" class="hidden z-[10000]">
    <div id="bulk-update-overlay" class="fixed inset-0 bg-black/45 hidden"></div>
    <div id="bulk-update-panel" class="absolute hidden w-full max-w-2xl bg-white rounded-xl shadow-2xl max-h-[90vh] overflow-y-auto border border-amber-100">
        <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-t-xl flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold">Modification en masse</h3>
                <p class="text-sm text-white/80">Appliquer à tous les équipements de même désignation</p>
            </div>
            <button id="bulk-close-btn" type="button" data-bulk-close="1" onclick="closeBulkUpdateModal()" class="text-white/90 hover:text-white text-2xl leading-none">&times;</button>
        </div>
        <form id="bulk-update-form" class="p-6">
            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <p class="text-sm text-amber-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Désignation : <strong id="bulk-designation-display" class="text-amber-900"></strong>
                </p>
                <p class="text-xs text-amber-700 mt-1">Tous les équipements avec cette désignation seront modifiés.</p>
            </div>
            <input type="hidden" name="designation" id="bulk-designation-input">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="bulk-duree-garantie" class="block text-sm font-semibold text-gray-700 mb-1">Durée de garantie</label>
                    <input id="bulk-duree-garantie" type="text" name="duree_garantie" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ex: 24 mois">
                </div>
                <div>
                    <label for="bulk-brand-name" class="block text-sm font-semibold text-gray-700 mb-1">Marque</label>
                    <input id="bulk-brand-name" type="text" name="brand_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ex: Philips">
                </div>
                <div>
                    <label for="bulk-company-id" class="block text-sm font-semibold text-gray-700 mb-1">Société</label>
                    <select id="bulk-company-id" name="company_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">-- Sélectionner une société --</option>
                        @foreach(($companies ?? []) as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="bulk-model-name" class="block text-sm font-semibold text-gray-700 mb-1">Modèle</label>
                    <input id="bulk-model-name" type="text" name="model_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ex: XR-2000">
                </div>
                <div>
                    <label for="bulk-market-label" class="block text-sm font-semibold text-gray-700 mb-1">Marché</label>
                    <input id="bulk-market-label" type="text" name="market_label" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ex: Marché 2024-001">
                </div>
                <div>
                    <label for="bulk-unit-name" class="block text-sm font-semibold text-gray-700 mb-1">Unité</label>
                    <input id="bulk-unit-name" type="text" name="unit_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ex: Réanimation">
                </div>
                <div>
                    <label for="bulk-sector-name" class="block text-sm font-semibold text-gray-700 mb-1">Secteur</label>
                    <input id="bulk-sector-name" type="text" name="sector_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ex: Bloc A">
                </div>
                <div>
                    <label for="bulk-lot-number" class="block text-sm font-semibold text-gray-700 mb-1">Lot</label>
                    <input id="bulk-lot-number" type="text" name="lot_number" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ex: LOT-001">
                </div>
                <div class="md:col-span-2">
                    <label for="bulk-article" class="block text-sm font-semibold text-gray-700 mb-1">Article</label>
                    <input id="bulk-article" type="text" name="article" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Ex: ART-12345">
                </div>

                <div class="md:col-span-2">
                    <p class="block text-sm font-semibold text-gray-700 mb-2">Importer des PDF par désignation</p>
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex items-center gap-2">
                            <input id="bulk-user-manual-file" type="file" name="user_manual_file" accept="application/pdf" class="hidden">
                            <label for="bulk-user-manual-file" class="inline-flex items-center justify-center w-10 h-10 border border-blue-200 text-blue-700 rounded-lg bg-white hover:bg-blue-50 cursor-pointer" title="PDF de formation utilisateur">
                                <i class="fas fa-file-pdf"></i>
                            </label>
                            <span class="text-xs text-gray-600">Formation utilisateur</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="bulk-technical-manual-file" type="file" name="technical_manual_file" accept="application/pdf" class="hidden">
                            <label for="bulk-technical-manual-file" class="inline-flex items-center justify-center w-10 h-10 border border-indigo-200 text-indigo-700 rounded-lg bg-white hover:bg-indigo-50 cursor-pointer" title="PDF de formation technique">
                                <i class="fas fa-file-pdf"></i>
                            </label>
                            <span class="text-xs text-gray-600">Formation technique</span>
                        </div>
                    </div>
                    <p id="bulk-uploaded-files-hint" class="mt-2 text-xs text-gray-500">Aucun PDF sélectionné.</p>
                </div>
            </div>
            
            <p class="text-xs text-gray-500 mt-4">Laissez les champs vides pour ne pas les modifier.</p>
            
            <div class="mt-6 flex justify-end gap-3">
                <button id="bulk-cancel-btn" type="button" data-bulk-close="1" onclick="closeBulkUpdateModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">Annuler</button>
                <button type="submit" class="px-5 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-lg hover:from-amber-600 hover:to-orange-600 text-sm font-semibold">
                    <i class="fas fa-save mr-1"></i> Appliquer les modifications
                </button>
            </div>
        </form>
    </div>
</div>

@if (session('deleted_message'))
    <div x-data="{ open: true }"
         x-show="open"
         x-transition.opacity
         class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center px-4"
         style="display: none;">
        <div @click.away="open = false" class="w-full max-w-md bg-white rounded-xl shadow-xl p-6">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                    <i class="fas fa-check"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800">Suppression réussie</h3>
                    <p class="text-sm text-gray-600 mt-1">{{ session('deleted_message') }}</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="button"
                        @click="open = false"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Fermer
                </button>
            </div>
        </div>
    </div>
@endif

@endsection

@section('scripts')
<script>
    function exportTableToExcel() {
        const form = document.getElementById('equipments-filter-form');
        if (!form) {
            return;
        }

        const params = new URLSearchParams(new FormData(form));
        window.location.href = `{{ route('equipements.export.excel') }}?${params.toString()}`;
    }

    function exportTableToPdf() {
        const form = document.getElementById('equipments-filter-form');
        if (!form) {
            return;
        }

        const params = new URLSearchParams(new FormData(form));
        window.open(`{{ route('equipements.export.pdf') }}?${params.toString()}`, '_blank');
    }

    let equipmentKpiChart = null;
    let currentEquipmentDetails = null;
    let pendingDeleteAction = null;
    let isDeleteActionRunning = false;
    const selectedEquipmentIds = new Set();

    function getEquipmentCountValue() {
        const countLabel = document.getElementById('equipments-count');
        if (!countLabel) {
            return 0;
        }

        const text = (countLabel.textContent || '').trim();
        const numeric = text.replace(/[^0-9]/g, '');
        return Number.parseInt(numeric || '0', 10);
    }

    function setEquipmentCountValue(nextValue) {
        const countLabel = document.getElementById('equipments-count');
        if (!countLabel) {
            return;
        }

        const safeValue = Math.max(0, Number.parseInt(String(nextValue), 10) || 0);
        countLabel.textContent = `${safeValue} équipement(s)`;
    }

    function normalizeSelectedEquipmentSet() {
        const visibleIds = new Set(
            Array.from(document.querySelectorAll('.equipment-select-checkbox'))
                .map((checkbox) => Number.parseInt(checkbox.value || '0', 10))
                .filter((id) => id > 0)
        );

        Array.from(selectedEquipmentIds).forEach((id) => {
            if (!visibleIds.has(id)) {
                selectedEquipmentIds.delete(id);
            }
        });
    }

    function updateSelectionUi() {
        normalizeSelectedEquipmentSet();

        const checkboxes = Array.from(document.querySelectorAll('.equipment-select-checkbox'));
        const checkedCount = checkboxes.reduce((count, checkbox) => count + (checkbox.checked ? 1 : 0), 0);

        const selectAll = document.getElementById('select-all-equipments');
        if (selectAll) {
            selectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }

        const selectionLabel = document.getElementById('equipments-selection-count');
        if (selectionLabel) {
            selectionLabel.textContent = `${selectedEquipmentIds.size} sélectionné(s)`;
            selectionLabel.classList.toggle('hidden', selectedEquipmentIds.size === 0);
        }

        const bulkDeleteTrigger = document.getElementById('bulk-delete-trigger');
        if (bulkDeleteTrigger) {
            bulkDeleteTrigger.classList.toggle('hidden', selectedEquipmentIds.size === 0);
        }
    }

    function toggleEquipmentSelection(equipmentId, event) {
        const numericId = Number.parseInt(String(equipmentId), 10);
        if (!Number.isInteger(numericId) || numericId <= 0) {
            return;
        }

        if (event?.target?.checked) {
            selectedEquipmentIds.add(numericId);
        } else {
            selectedEquipmentIds.delete(numericId);
        }

        updateSelectionUi();
    }

    function toggleSelectAllEquipments(event) {
        const shouldSelect = Boolean(event?.target?.checked);
        const checkboxes = Array.from(document.querySelectorAll('.equipment-select-checkbox'));

        checkboxes.forEach((checkbox) => {
            checkbox.checked = shouldSelect;
            const equipmentId = Number.parseInt(checkbox.value || '0', 10);

            if (!Number.isInteger(equipmentId) || equipmentId <= 0) {
                return;
            }

            if (shouldSelect) {
                selectedEquipmentIds.add(equipmentId);
            } else {
                selectedEquipmentIds.delete(equipmentId);
            }
        });

        updateSelectionUi();
    }

    function clearEquipmentSelection() {
        selectedEquipmentIds.clear();
        document.querySelectorAll('.equipment-select-checkbox').forEach((checkbox) => {
            checkbox.checked = false;
        });
        updateSelectionUi();
    }

    function renderEquipmentKpi(detailsData) {
        const kpi = detailsData?.kpi || {};

        const totalReports = Number(kpi.total_reports || 0);
        const openReports = Number(kpi.open_reports || 0);
        const closedReports = Number(kpi.closed_reports || 0);
        const thisMonthReports = Number(kpi.this_month_reports || 0);

        const setText = (id, value) => {
            const element = document.getElementById(id);
            if (element) {
                element.textContent = String(value);
            }
        };

        setText('detail-kpi-total', totalReports);
        setText('detail-kpi-open', openReports);
        setText('detail-kpi-closed', closedReports);
        setText('detail-kpi-month', thisMonthReports);

        const chartCanvas = document.getElementById('detail-kpi-chart');
        if (!chartCanvas || typeof Chart === 'undefined') {
            return;
        }

        const byType = kpi.by_type || {};

        if (equipmentKpiChart) {
            equipmentKpiChart.destroy();
            equipmentKpiChart = null;
        }

        equipmentKpiChart = new Chart(chartCanvas, {
            type: 'bar',
            data: {
                labels: ['Préventive', 'Corrective', 'Diagnostic', 'Ce mois'],
                datasets: [
                    {
                        label: 'KPI du rapport',
                        data: [
                            Number(byType.preventive || 0),
                            Number(byType.curative || 0),
                            Number(byType.diagnostic || 0),
                            thisMonthReports,
                        ],
                        backgroundColor: ['rgba(59,130,246,0.75)', 'rgba(245,158,11,0.75)', 'rgba(139,92,246,0.75)', 'rgba(16,185,129,0.75)'],
                        borderColor: ['rgb(37,99,235)', 'rgb(217,119,6)', 'rgb(124,58,237)', 'rgb(5,150,105)'],
                        borderWidth: 1,
                        borderRadius: 8,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        },
                        title: {
                            display: true,
                            text: 'Nombre de rapports'
                        }
                    }
                }
            }
        });
    }

    function openEquipmentDetails(equipment) {
        function normalizeBadgeToken(value) {
            return (value || '').toString().trim().toLowerCase();
        }

        function resolveBadgeTone(value) {
            const token = normalizeBadgeToken(value);

            if (['actif', 'fonctionnel', 'en service', 'opérationnel', 'ok'].includes(token)) {
                return 'good';
            }

            if (['reserve', 'réserve', 'en maintenance', 'maintenance', 'en_attente', 'en attente'].includes(token)) {
                return 'warning';
            }

            if (['panne', 'hors_service', 'hors service'].includes(token)) {
                return 'danger';
            }

            if (['inactif', 'inactive', 'unknown', 'inconnu'].includes(token)) {
                return 'muted';
            }

            return 'default';
        }

        function applyBadgeTone(badgeElement, tone) {
            if (!badgeElement) {
                return;
            }

            const toneClasses = [
                'bg-blue-50', 'text-blue-700', 'border-blue-200',
                'bg-indigo-50', 'text-indigo-700', 'border-indigo-200',
                'bg-violet-50', 'text-violet-700', 'border-violet-200',
                'bg-emerald-50', 'text-emerald-700', 'border-emerald-200',
                'bg-amber-50', 'text-amber-700', 'border-amber-200',
                'bg-rose-50', 'text-rose-700', 'border-rose-200',
                'bg-slate-100', 'text-slate-700', 'border-slate-200',
            ];

            badgeElement.classList.remove(...toneClasses);

            const palette = {
                good: ['bg-emerald-50', 'text-emerald-700', 'border-emerald-200'],
                warning: ['bg-amber-50', 'text-amber-700', 'border-amber-200'],
                danger: ['bg-rose-50', 'text-rose-700', 'border-rose-200'],
                muted: ['bg-slate-100', 'text-slate-700', 'border-slate-200'],
                default: ['bg-indigo-50', 'text-indigo-700', 'border-indigo-200'],
            };

            badgeElement.classList.add(...(palette[tone] || palette.default));
        }

        const inlineRow = document.getElementById('equipment-inline-details-row');
        const triggerRow = arguments[0] instanceof HTMLElement ? arguments[0] : null;
        const detailsData = triggerRow ? arguments[1] : equipment;

        if (!inlineRow || !triggerRow) {
            return;
        }

        currentEquipmentDetails = detailsData || null;

        const isAlreadyOpenForSameRow = inlineRow.dataset.openRowId === (triggerRow.rowIndex + ':' + (detailsData?.id ?? '')) && !inlineRow.classList.contains('hidden');
        if (isAlreadyOpenForSameRow) {
            closeEquipmentDetails();
            return;
        }

        document.querySelectorAll('tr.equipment-row').forEach(function (row) {
            row.classList.remove('bg-blue-50');
        });

        triggerRow.classList.add('bg-blue-50');

        const parent = triggerRow.parentNode;
        if (parent) {
            parent.insertBefore(inlineRow, triggerRow.nextSibling);
        }

        inlineRow.dataset.openRowId = triggerRow.rowIndex + ':' + (detailsData?.id ?? '');

        document.getElementById('detail-barcode').textContent = detailsData?.barcode || '-';
        document.getElementById('detail-description').textContent = detailsData?.equipment_description || '-';
        document.getElementById('detail-serial').textContent = detailsData?.serial_number || '-';
        document.getElementById('detail-unit').textContent = detailsData?.unit || '-';
        document.getElementById('detail-sector').textContent = detailsData?.sector || '-';
        document.getElementById('detail-sector-description').textContent = detailsData?.sector_description || '-';
        document.getElementById('detail-brand').textContent = detailsData?.brand || '-';
        document.getElementById('detail-model').textContent = detailsData?.model || '-';
        document.getElementById('detail-company').textContent = detailsData?.company_name || '-';
        document.getElementById('detail-market').textContent = detailsData?.market || '-';
        document.getElementById('detail-lot').textContent = detailsData?.lot || '-';
        document.getElementById('detail-article').textContent = detailsData?.article || '-';
        document.getElementById('detail-reception-provisoire').textContent = detailsData?.date_reception_provisoire || '-';
        document.getElementById('detail-duree-garantie').textContent = detailsData?.duree_garantie || '-';
        document.getElementById('detail-status').textContent = detailsData?.status || '-';
        document.getElementById('detail-reception-definitive').textContent = detailsData?.date_reception_definitive || '-';
        document.getElementById('detail-statut-etat').textContent = detailsData?.statut_etat || '-';
        document.getElementById('detail-pill-inv').textContent = detailsData?.barcode || '-';
        document.getElementById('detail-pill-status').textContent = detailsData?.status || '-';
        document.getElementById('detail-pill-state').textContent = detailsData?.statut_etat || '-';
        document.getElementById('detail-edit-url').href = detailsData?.edit_url || '#';

        const deleteButton = document.getElementById('detail-delete-btn');
        const deleteUrl = detailsData?.delete_url || '';

        if (deleteButton) {
            const equipmentId = Number.parseInt(String(detailsData?.id ?? '0'), 10);
            const canDelete = deleteUrl !== '' && Number.isInteger(equipmentId) && equipmentId > 0;
            deleteButton.dataset.deleteUrl = deleteUrl;
            deleteButton.dataset.equipmentId = String(equipmentId > 0 ? equipmentId : '');
            deleteButton.disabled = !canDelete;
            deleteButton.classList.toggle('opacity-50', !canDelete);
            deleteButton.classList.toggle('cursor-not-allowed', !canDelete);
        }

        renderEquipmentKpi(detailsData);

        applyBadgeTone(document.getElementById('detail-pill-status-badge'), resolveBadgeTone(detailsData?.status));
        applyBadgeTone(document.getElementById('detail-pill-state-badge'), resolveBadgeTone(detailsData?.statut_etat));

        const imageWrapper = document.getElementById('detail-image-wrapper');
        const image = document.getElementById('detail-image');
        const userManualLink = document.getElementById('detail-user-manual-link');
        const technicalManualLink = document.getElementById('detail-technical-manual-link');
        const noDocs = document.getElementById('detail-no-docs');

        const imageUrl = detailsData?.designation_image_url || null;
        const userManualUrl = detailsData?.user_manual_url || null;
        const technicalManualUrl = detailsData?.technical_manual_url || null;

        if (imageUrl) {
            image.src = imageUrl;
            imageWrapper.classList.remove('hidden');
        } else {
            image.src = '';
            imageWrapper.classList.add('hidden');
        }

        if (userManualUrl) {
            userManualLink.classList.remove('hidden');
        } else {
            userManualLink.classList.add('hidden');
        }

        if (technicalManualUrl) {
            technicalManualLink.classList.remove('hidden');
        } else {
            technicalManualLink.classList.add('hidden');
        }

        const hasAnyDoc = Boolean(imageUrl || userManualUrl || technicalManualUrl);
        noDocs.classList.toggle('hidden', hasAnyDoc);

        if (userManualUrl) {
            userManualLink.onclick = function () {
                window.open(`${userManualUrl}#toolbar=0&navpanes=0`, '_blank', 'noopener,noreferrer');
            };
        } else {
            userManualLink.onclick = null;
        }

        if (technicalManualUrl) {
            technicalManualLink.onclick = function () {
                window.open(`${technicalManualUrl}#toolbar=0&navpanes=0`, '_blank', 'noopener,noreferrer');
            };
        } else {
            technicalManualLink.onclick = null;
        }

        inlineRow.classList.remove('hidden');
        requestAnimationFrame(function () {
            inlineRow.classList.remove('opacity-0', '-translate-y-1');
            inlineRow.classList.remove('scale-[0.99]');
            inlineRow.classList.add('opacity-100', 'translate-y-0', 'scale-100');
        });
    }

    function closeEquipmentDetails() {
        const inlineRow = document.getElementById('equipment-inline-details-row');
        if (inlineRow) {
            inlineRow.classList.remove('opacity-100', 'translate-y-0', 'scale-100');
            inlineRow.classList.add('opacity-0', '-translate-y-1', 'scale-[0.99]');

            window.setTimeout(function () {
                inlineRow.classList.add('hidden');
                inlineRow.dataset.openRowId = '';
            }, 300);
        }

        document.querySelectorAll('tr.equipment-row').forEach(function (row) {
            row.classList.remove('bg-blue-50');
        });

        currentEquipmentDetails = null;
    }

    function confirmDeleteEquipment() {
        const deleteButton = document.getElementById('detail-delete-btn');
        const deleteUrl = (deleteButton?.dataset?.deleteUrl || currentEquipmentDetails?.delete_url || '').trim();
        const equipmentId = Number.parseInt(deleteButton?.dataset?.equipmentId || String(currentEquipmentDetails?.id || '0'), 10);

        if (!deleteUrl || !Number.isInteger(equipmentId) || equipmentId <= 0) {
            showNotification('Suppression indisponible pour cet équipement.', 'error');
            return;
        }

        const inventory = (currentEquipmentDetails?.barcode || document.getElementById('detail-barcode')?.textContent || '').trim();
        const designation = (currentEquipmentDetails?.equipment_description || document.getElementById('detail-description')?.textContent || '').trim();

        const labelParts = [];
        if (inventory && inventory !== '-') {
            labelParts.push(inventory);
        }
        if (designation && designation !== '-') {
            labelParts.push(designation);
        }

        const targetLabel = labelParts.length > 0 ? labelParts.join(' - ') : 'cet équipement';
        openDeleteConfirmModal({
            mode: 'single',
            equipmentIds: [equipmentId],
            deleteUrl,
            label: targetLabel,
        });
    }

    function openBulkDeleteModalFromSelection() {
        if (selectedEquipmentIds.size === 0) {
            showNotification('Sélectionnez au moins un équipement.', 'error');
            return;
        }

        openDeleteConfirmModal({
            mode: 'bulk',
            equipmentIds: Array.from(selectedEquipmentIds),
            label: `${selectedEquipmentIds.size} équipement(s) sélectionné(s)`,
        });
    }

    function openDeleteConfirmModal(actionContext) {
        const modal = document.getElementById('delete-confirm-modal');
        const title = document.getElementById('delete-confirm-title');
        const message = document.getElementById('delete-confirm-message');

        if (!modal || !title || !message) {
            return;
        }

        pendingDeleteAction = actionContext;

        if (actionContext?.mode === 'bulk') {
            title.textContent = 'Supprimer plusieurs équipements';
            message.textContent = `Voulez-vous vraiment supprimer ${actionContext.label || 'la sélection'} ?`;
        } else {
            title.textContent = 'Supprimer un équipement';
            message.textContent = `Voulez-vous vraiment supprimer ${actionContext?.label || 'cet équipement'} ?`;
        }

        setDeleteActionLoading(false);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteConfirmModal(forceClose = false) {
        if (isDeleteActionRunning && !forceClose) {
            return;
        }

        const modal = document.getElementById('delete-confirm-modal');
        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        pendingDeleteAction = null;
    }

    function setDeleteActionLoading(loading) {
        isDeleteActionRunning = loading;
        const submitButton = document.getElementById('delete-confirm-submit');
        if (!submitButton) {
            return;
        }

        submitButton.disabled = loading;
        submitButton.classList.toggle('opacity-70', loading);
        submitButton.classList.toggle('cursor-not-allowed', loading);
        submitButton.innerHTML = loading
            ? '<i class="fas fa-spinner fa-spin mr-1"></i> Suppression...'
            : '<i class="fas fa-trash-alt mr-1"></i> Supprimer';
    }

    function removeDeletedRowsFromUi(deletedIds) {
        if (!Array.isArray(deletedIds) || deletedIds.length === 0) {
            return;
        }

        const deletedIdSet = new Set(
            deletedIds
                .map((id) => Number.parseInt(String(id), 10))
                .filter((id) => Number.isInteger(id) && id > 0)
        );

        if (deletedIdSet.size === 0) {
            return;
        }

        document.querySelectorAll('tr.equipment-row').forEach((row) => {
            const rowId = Number.parseInt(row.dataset.equipmentId || '0', 10);
            if (deletedIdSet.has(rowId)) {
                row.remove();
            }
        });

        deletedIdSet.forEach((id) => selectedEquipmentIds.delete(id));
        updateSelectionUi();

        const currentDetailsId = Number.parseInt(String(currentEquipmentDetails?.id || '0'), 10);
        if (deletedIdSet.has(currentDetailsId)) {
            closeEquipmentDetails();
        }

        const noResultsRow = document.getElementById('no-live-results');
        const remainingRows = document.querySelectorAll('tr.equipment-row').length;
        if (noResultsRow) {
            noResultsRow.classList.toggle('hidden', remainingRows > 0);
        }

        const nextCount = getEquipmentCountValue() - deletedIdSet.size;
        setEquipmentCountValue(nextCount);
    }

    async function executeSingleDelete(actionContext) {
        const response = await fetch(actionContext.deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
        });

        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Suppression impossible pour cet équipement.');
        }

        const deletedId = Number.parseInt(String(result.deleted_id || actionContext.equipmentIds?.[0] || '0'), 10);
        removeDeletedRowsFromUi(Number.isInteger(deletedId) && deletedId > 0 ? [deletedId] : []);
        showNotification(result.message || 'Équipement supprimé avec succès.', 'success');
    }

    async function executeBulkDelete(actionContext) {
        const response = await fetch('{{ route("equipements.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                equipment_ids: actionContext.equipmentIds || [],
            }),
        });

        const result = await response.json().catch(() => ({}));
        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Suppression multiple impossible.');
        }

        const deletedIds = Array.isArray(result.deleted_ids) ? result.deleted_ids : [];
        removeDeletedRowsFromUi(deletedIds);

        if (result.success) {
            showNotification(result.message || 'Suppression effectuée.', 'success');
            return;
        }

        throw new Error(result.message || 'Suppression multiple impossible.');
    }

    async function confirmDeleteAction() {
        if (!pendingDeleteAction || isDeleteActionRunning) {
            return;
        }

        setDeleteActionLoading(true);

        try {
            if (pendingDeleteAction.mode === 'bulk') {
                await executeBulkDelete(pendingDeleteAction);
            } else {
                await executeSingleDelete(pendingDeleteAction);
            }

            closeDeleteConfirmModal(true);
        } catch (error) {
            showNotification(error.message || 'Erreur lors de la suppression.', 'error');
        } finally {
            setDeleteActionLoading(false);
        }
    }

    window.openEquipmentDetails = openEquipmentDetails;
    window.closeEquipmentDetails = closeEquipmentDetails;
    window.confirmDeleteEquipment = confirmDeleteEquipment;
    window.openBulkDeleteModalFromSelection = openBulkDeleteModalFromSelection;
    window.toggleEquipmentSelection = toggleEquipmentSelection;
    window.toggleSelectAllEquipments = toggleSelectAllEquipments;
    window.confirmDeleteAction = confirmDeleteAction;
    window.closeDeleteConfirmModal = closeDeleteConfirmModal;
    window.exportTableToExcel = exportTableToExcel;
    window.exportTableToPdf = exportTableToPdf;

    // Current equipment designation for bulk update
    let currentDesignation = '';

    // Global status dropdown variables
    let currentStatusDropdownId = null;
    let currentStatusButton = null;

    // Open status dropdown at button position
    function openStatusDropdown(event, equipmentId) {
        event.stopPropagation();
        event.preventDefault();
        
        const dropdown = document.getElementById('status-dropdown');
        const button = event.currentTarget
            || event.target?.closest('.status-btn')
            || document.getElementById(`status-btn-${equipmentId}`);
        if (!dropdown || !button) {
            return;
        }
        const rect = button.getBoundingClientRect();

        // Ensure dropdown is rendered at body level to avoid transformed/scroll container offsets.
        if (dropdown.parentElement !== document.body) {
            document.body.appendChild(dropdown);
        }
        
        // Toggle close if same button
        if (currentStatusDropdownId === equipmentId && !dropdown.classList.contains('hidden')) {
            closeStatusDropdown();
            return;
        }
        
        currentStatusDropdownId = equipmentId;
        currentStatusButton = button;

        const viewportPadding = 8;
        const preferredGap = 8;
        const minWidth = 220;

        dropdown.style.position = 'fixed';
        dropdown.style.minWidth = minWidth + 'px';
        dropdown.classList.remove('hidden');
        dropdown.style.visibility = 'hidden';

        const dropdownWidth = Math.max(minWidth, dropdown.offsetWidth || minWidth);
        const dropdownHeight = dropdown.offsetHeight || 180;

        // Anchor under the clicked status button.
        let left = rect.left;
        let top = rect.bottom + preferredGap;

        const viewportRight = window.innerWidth;
        const viewportBottom = window.innerHeight;

        if (left + dropdownWidth > viewportRight - viewportPadding) {
            left = viewportRight - dropdownWidth - viewportPadding;
        }
        if (left < viewportPadding) {
            left = viewportPadding;
        }

        if (top + dropdownHeight > viewportBottom - viewportPadding) {
            top = rect.top - dropdownHeight - preferredGap;
        }
        if (top < viewportPadding) {
            top = viewportPadding;
        }

        dropdown.style.top = Math.round(top) + 'px';
        dropdown.style.left = Math.round(left) + 'px';
        dropdown.style.visibility = 'visible';
        dropdown.style.boxShadow = '';
        
        // Animate in
        dropdown.style.opacity = '0';
        dropdown.style.transform = 'translateY(-8px) scale(0.95)';
        dropdown.style.transition = 'opacity 0.15s ease, transform 0.15s ease';
        requestAnimationFrame(() => {
            dropdown.style.opacity = '1';
            dropdown.style.transform = 'translateY(0) scale(1)';
        });
    }
    window.openStatusDropdown = openStatusDropdown;

    // Close status dropdown
    function closeStatusDropdown() {
        const dropdown = document.getElementById('status-dropdown');
        if (!dropdown) {
            currentStatusDropdownId = null;
            currentStatusButton = null;
            return;
        }
        dropdown.classList.add('hidden');
        dropdown.style.visibility = '';
        currentStatusDropdownId = null;
        currentStatusButton = null;
    }
    window.closeStatusDropdown = closeStatusDropdown;

    // Handle click outside to close dropdown
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('status-dropdown');
        if (!dropdown) {
            return;
        }
        if (!dropdown.contains(event.target) && !event.target.closest('.status-btn')) {
            closeStatusDropdown();
        }
    });

    // Select a status and update
    function selectStatus(newStatus) {
        if (!currentStatusDropdownId || !currentStatusButton) return;
        
        const equipmentId = currentStatusDropdownId;
        const button = currentStatusButton;
        
        closeStatusDropdown();
        
        // Update via AJAX
        fetch(`{{ url('/dashboard/equipements') }}/${equipmentId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button icon, text and colors
                const statusText = button.querySelector('.status-text');
                const statusIcon = button.querySelector('i.fas');
                const dots = button.querySelectorAll('span.rounded-full');
                const pingDot = dots[0];
                const solidDot = dots[1];

                const configs = {
                    'fonctionnel': {
                        label: 'Fonctionnel',
                        icon: 'fa-circle-check',
                        bg: 'bg-emerald-50', txt: 'text-emerald-700', bdr: 'border-emerald-300',
                        dot: 'bg-emerald-500', ping: true
                    },
                    'en panne': {
                        label: 'En panne',
                        icon: 'fa-circle-xmark',
                        bg: 'bg-rose-50', txt: 'text-rose-700', bdr: 'border-rose-300',
                        dot: 'bg-rose-500', ping: false
                    },
                    'reforme': {
                        label: 'Réformé',
                        icon: 'fa-ban',
                        bg: 'bg-slate-100', txt: 'text-slate-600', bdr: 'border-slate-300',
                        dot: 'bg-slate-400', ping: false
                    }
                };
                
                const cfg = configs[newStatus] || configs['fonctionnel'];
                
                // Update text & icon
                if (statusText) statusText.textContent = cfg.label;
                if (statusIcon) {
                    statusIcon.className = `fas ${cfg.icon} text-[11px]`;
                }

                // Update dots
                const allDotColors = ['bg-emerald-500','bg-rose-500','bg-slate-400','bg-amber-500','bg-gray-400'];
                if (pingDot) { pingDot.classList.remove(...allDotColors); pingDot.classList.add(cfg.dot); pingDot.className = pingDot.className.replace('animate-ping',''); if(cfg.ping) pingDot.classList.add('animate-ping'); }
                if (solidDot) { solidDot.classList.remove(...allDotColors); solidDot.classList.add(cfg.dot); }

                // Update button colors
                const allBg=['bg-emerald-50','bg-rose-50','bg-slate-100','bg-amber-50','bg-gray-100'];
                const allTxt=['text-emerald-700','text-rose-700','text-slate-600','text-amber-700','text-gray-500'];
                const allBdr=['border-emerald-300','border-rose-300','border-slate-300','border-amber-300','border-gray-300'];
                button.classList.remove(...allBg,...allTxt,...allBdr);
                button.classList.add(cfg.bg, cfg.txt, cfg.bdr);
                
                showNotification('Statut mis à jour : ' + cfg.label, 'success');
            } else {
                showNotification(data.message || 'Erreur', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Erreur de connexion', 'error');
        });
    }
    window.selectStatus = selectStatus;

    // Bulk update modal functions
    let bulkIgnoreOutsideClickUntil = 0;
    function openBulkUpdateModal(event) {
        try {
            console.debug('openBulkUpdateModal called', { event });
            if (event && typeof event.stopPropagation === 'function') {
                event.stopPropagation();
            }

            bulkIgnoreOutsideClickUntil = Date.now() + 300;

            const designation = document.getElementById('detail-description')?.textContent || '';
            if (!designation || designation === '-') {
                showNotification('Désignation non disponible', 'error');
                return;
            }

            currentDesignation = designation;
            document.getElementById('bulk-designation-display').textContent = designation;
            document.getElementById('bulk-designation-input').value = designation;
            document.getElementById('bulk-update-form').reset();
            document.getElementById('bulk-designation-input').value = designation;

            const filesHint = document.getElementById('bulk-uploaded-files-hint');
            if (filesHint) {
                filesHint.textContent = 'Aucun PDF sélectionné.';
            }

            const modal = document.getElementById('bulk-update-modal');
            const panel = document.getElementById('bulk-update-panel');
            const overlay = document.getElementById('bulk-update-overlay');
            if (!modal || !panel) {
                console.error('bulk update elements not found');
                return;
            }

            if (panel.dataset.open === '1') {
                console.debug('bulk panel already open');
                return;
            }

            // Ensure panel and overlay are appended to body to avoid clipping/transform issues
            try {
                console.debug('bulk update modal elements', { modalExists: !!modal, panelExists: !!panel, overlayExists: !!overlay });
                if (panel.parentNode !== document.body) {
                    document.body.appendChild(panel);
                }
                if (overlay && overlay.parentNode !== document.body) {
                    document.body.appendChild(overlay);
                }
            } catch (e) {
                console.debug('append to body failed', e);
            }

            // Bind close buttons once (after appending to body)
            if (!panel.dataset.buttonsBound) {
                const closeBtn = document.getElementById('bulk-close-btn');
                const cancelBtn = document.getElementById('bulk-cancel-btn');
                try {
                    if (closeBtn) {
                        closeBtn.addEventListener('click', closeBulkUpdateModal);
                    }
                    if (cancelBtn) {
                        cancelBtn.addEventListener('click', closeBulkUpdateModal);
                    }
                } catch (e) {
                    console.debug('binding bulk close buttons failed', e);
                }
                panel.dataset.buttonsBound = '1';
            }

            // Position the panel near the equipment inline details row using fixed positioning
            const inlineRow = document.getElementById('equipment-inline-details-row');
            let anchorRect = null;
            if (inlineRow) {
                anchorRect = inlineRow.getBoundingClientRect();
            } else if (event && event.target) {
                anchorRect = event.target.getBoundingClientRect();
            }

            // Make panel visible but hidden to measure its size
            panel.style.position = 'fixed';
            panel.style.visibility = 'hidden';
            panel.style.display = 'block';
            panel.classList.remove('hidden');
            modal.classList.remove('hidden');

            const viewportW = window.innerWidth;
            const viewportH = window.innerHeight;
            const panelRect = panel.getBoundingClientRect();
            console.debug('panelRect measured', panelRect);
            const panelW = Math.min(panelRect.width || 720, viewportW - 32);
            const panelH = panelRect.height || 400;

            let top = 16;
            let left = Math.max(8, (viewportW - panelW) / 2);

            if (anchorRect) {
                const belowTop = anchorRect.bottom + 8;
                const aboveTop = anchorRect.top - panelH - 8;

                if (belowTop + panelH + 8 <= viewportH) {
                    top = belowTop;
                } else if (aboveTop >= 8) {
                    top = aboveTop;
                } else {
                    top = Math.max(8, Math.min(belowTop, viewportH - panelH - 8));
                }

                left = Math.round(anchorRect.left + (anchorRect.width / 2) - (panelW / 2));
                left = Math.max(8, Math.min(left, viewportW - panelW - 8));
            }

            panel.style.width = panelW + 'px';
            panel.style.top = `${top}px`;
            panel.style.left = `${left}px`;
            panel.style.visibility = 'visible';
            panel.style.display = 'block';
            panel.style.zIndex = '12000';
            panel.dataset.open = '1';

            if (overlay) {
                overlay.classList.add('hidden');
            }

            console.debug('bulk panel positioned', { top, left, panelW, panelH });
        } catch (err) {
            console.error('openBulkUpdateModal error', err);
            showNotification('Erreur interne lors de l\'ouverture du panneau', 'error');
        }
    }
    window.openBulkUpdateModal = openBulkUpdateModal;

    function closeBulkUpdateModal() {
        const modal = document.getElementById('bulk-update-modal');
        const panel = document.getElementById('bulk-update-panel');
        const overlay = document.getElementById('bulk-update-overlay');
        if (!modal || !panel) {
            return;
        }

        panel.classList.add('hidden');
        panel.style.display = 'none';
        panel.style.visibility = '';
        modal.classList.add('hidden');
        if (overlay) {
            overlay.classList.add('hidden');
        }
        panel.dataset.open = '0';
        panel.style.zIndex = '';
        // restore body scrolling if it was disabled previously
        document.body.classList.remove('overflow-hidden');
    }
    window.closeBulkUpdateModal = closeBulkUpdateModal;

    // Ensure close buttons always work, even if inline handlers fail
    document.addEventListener('click', function (event) {
        const closeTrigger = event.target?.closest?.('[data-bulk-close="1"]');
        if (!closeTrigger) {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        closeBulkUpdateModal();
    }, true);

    // Close when clicking overlay (if overlay is visible) or clicking outside the panel
    document.getElementById('bulk-update-overlay')?.addEventListener('click', function (event) {
        closeBulkUpdateModal();
    });

    document.addEventListener('click', function (event) {
        if (Date.now() < bulkIgnoreOutsideClickUntil) {
            return;
        }
        const panel = document.getElementById('bulk-update-panel');
        if (!panel || panel.classList.contains('hidden')) {
            return;
        }

        if (!panel.contains(event.target)) {
            // click outside the panel closes it
            closeBulkUpdateModal();
        }
    });

    // Bulk update form submission
    document.getElementById('bulk-update-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        const designationValue = (formData.get('designation') || '').toString().trim();
        let hasNonDesignationField = false;
        for (const [key, value] of formData.entries()) {
            if (key === 'designation') {
                continue;
            }

            if (value instanceof File) {
                if (value.size > 0) {
                    hasNonDesignationField = true;
                    break;
                }
                continue;
            }

            if ((value || '').toString().trim() !== '') {
                hasNonDesignationField = true;
                break;
            }
        }

        if (designationValue === '' || !hasNonDesignationField) {
            showNotification('Veuillez remplir au moins un champ', 'error');
            return;
        }

        // Trim textual fields and keep files untouched.
        const payload = new FormData();
        for (const [key, value] of formData.entries()) {
            if (value instanceof File) {
                if (value.size > 0) {
                    payload.append(key, value);
                }
                continue;
            }

            const trimmed = (value || '').toString().trim();
            if (trimmed !== '') {
                payload.append(key, trimmed);
            }
        }

        fetch('{{ route("equipements.bulk-update-designation") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: payload
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification(result.message, 'success');
                closeBulkUpdateModal();
                // Reload page to see changes
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showNotification(result.message || 'Erreur', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Erreur de connexion', 'error');
        });
    });

    function syncBulkUploadedFilesHint() {
        const userFileInput = document.getElementById('bulk-user-manual-file');
        const techFileInput = document.getElementById('bulk-technical-manual-file');
        const hint = document.getElementById('bulk-uploaded-files-hint');
        if (!hint) {
            return;
        }

        const fileNames = [];
        if (userFileInput?.files?.[0]?.name) {
            fileNames.push('Utilisateur: ' + userFileInput.files[0].name);
        }
        if (techFileInput?.files?.[0]?.name) {
            fileNames.push('Technique: ' + techFileInput.files[0].name);
        }

        hint.textContent = fileNames.length > 0
            ? fileNames.join(' | ')
            : 'Aucun PDF sélectionné.';
    }

    document.getElementById('bulk-user-manual-file')?.addEventListener('change', syncBulkUploadedFilesHint);
    document.getElementById('bulk-technical-manual-file')?.addEventListener('change', syncBulkUploadedFilesHint);

    // Simple notification function
    function showNotification(message, type) {
        const existing = document.getElementById('toast-notification');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'toast-notification';
        toast.className = `fixed top-4 right-4 z-[9999] px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium transition-all duration-300 ${type === 'success' ? 'bg-emerald-600' : 'bg-rose-600'}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    updateSelectionUi();

    (function setupFilters() {
        const form = document.getElementById('equipments-filter-form');
        const hospitalSelect = document.getElementById('equipments-hospital-select');
        const serviceSelect = document.getElementById('equipments-service-select');
        const categorySelect = document.getElementById('equipments-category-select');
        const companySelect = document.getElementById('equipments-company-select');
        const searchInput = document.getElementById('equipments-search-input');
        const tableBody = document.getElementById('equipments-table-body');
        const countLabel = document.getElementById('equipments-count');
        const paginationContainer = document.getElementById('equipments-pagination');
        let liveSearchTimer = null;
        let liveSearchController = null;

        if (!form) {
            return;
        }

        async function applyLiveSearch() {
            const params = new URLSearchParams(new FormData(form));
            const url = `${form.action}?${params.toString()}`;

            if (liveSearchController) {
                liveSearchController.abort();
            }

            liveSearchController = new AbortController();

            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    signal: liveSearchController.signal,
                });

                if (!response.ok) {
                    return;
                }

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const nextBody = doc.getElementById('equipments-table-body');
                const nextCount = doc.getElementById('equipments-count');
                const nextPagination = doc.getElementById('equipments-pagination');

                if (tableBody && nextBody) {
                    tableBody.innerHTML = nextBody.innerHTML;
                }

                if (countLabel && nextCount) {
                    countLabel.textContent = nextCount.textContent;
                }

                if (paginationContainer && nextPagination) {
                    paginationContainer.innerHTML = nextPagination.innerHTML;
                }

                currentEquipmentDetails = null;
                clearEquipmentSelection();

                window.history.replaceState({}, '', url);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Live search failed:', error);
                }
            }
        }

        [hospitalSelect, serviceSelect, categorySelect].filter(Boolean).forEach(function (select) {
            select.addEventListener('change', function () {
                form.submit();
            });
        });

        if (companySelect) {
            companySelect.addEventListener('change', function () {
                applyLiveSearch();
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                if (liveSearchTimer) {
                    clearTimeout(liveSearchTimer);
                }

                liveSearchTimer = setTimeout(() => {
                    applyLiveSearch();
                }, 300);
            });

            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    applyLiveSearch();
                }
            });
        }
    })();
</script>
@endsection
