@extends('layouts.dashboard')

@section('page-title', 'Hiérarchie CHU GST')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/hierarchie.css') }}?v={{ filemtime(public_path('css/hierarchie.css')) }}">
@endsection

@section('content')
    @include('components.module-page-header', [
        'breadcrumb' => 'Organisation / Hiérarchie CHU GST',
    ])

    @if ($scopeNotice)
        <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
            {{ $scopeNotice }}
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <section
        id="hierarchy-module"
        class="hierarchy-module"
        data-disable-auto-refresh="1"
        data-create-url="{{ $createNodeUrl ?? route('hierarchie.import-excel') }}"
        data-update-service-url="{{ $updateServiceUrl ?? route('hierarchie.import-excel') }}"
        data-reload-url="{{ $reloadTreeUrl ?? route('hierarchie.export-json') }}"
        data-csrf-token="{{ csrf_token() }}"
        x-data="hierarchyTreeShell(@js($floors ?? []), @js(['createUrl' => $createNodeUrl ?? route('hierarchie.import-excel'), 'updateServiceUrl' => $updateServiceUrl ?? route('hierarchie.import-excel'), 'reloadUrl' => $reloadTreeUrl ?? route('hierarchie.export-json'), 'csrf' => csrf_token()]))"
    >
        <div class="mb-4 flex flex-wrap justify-end gap-3">
            <button
                id="hierarchy-add-btn"
                type="button"
                class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300"
                @click="openModal('etage')"
            >
                <i class="fas fa-plus"></i>
                <span>Ajouter</span>
            </button>
        </div>

        <div class="hierarchy-search-wrap">
            <i class="fas fa-search text-slate-400"></i>
            <input
                type="text"
                placeholder="Rechercher un étage ou un service"
                x-model.debounce.200ms="searchQuery"
                class="hierarchy-search-input"
            >
            <button
                type="button"
                class="hierarchy-search-clear"
                x-show="searchQuery !== ''"
                x-transition.opacity
                @click="clearSearch()"
                title="Effacer"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="hierarchy-tree-panel">
            @if (empty($floors ?? []))
                <div class="hierarchy-empty">
                    <i class="fas fa-sitemap"></i>
                    <p>Aucun étage disponible, veuillez ajouter un étage.</p>
                </div>
            @else
                <ul class="hierarchy-tree-list">
                    @foreach (($floors ?? []) as $node)
                        @include('pages.hierarchie.partials.node', ['node' => $node, 'level' => 1])
                    @endforeach
                </ul>

                <p class="hierarchy-no-result" style="display:none;">
                    Aucun étage trouvé.
                </p>
            @endif
        </div>

        <div
            id="hierarchy-node-modal"
            data-hierarchy-modal="1"
            x-show="showModal"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"
            style="display: none;"
        >
            <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl" @click.away="closeModal()">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <h3 class="text-base font-semibold text-slate-900">Ajouter un élément</h3>
                    <button id="hierarchy-modal-close-x" type="button" class="rounded-md p-1 text-slate-500 hover:bg-slate-100" @click="closeModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="hierarchy-node-form" class="space-y-4" @submit.prevent="submitNodeForm()">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Type</label>
                        <select id="hierarchy-node-type" name="node_type" x-model="form.node_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="etage">Étage</option>
                            <option value="service">Service</option>
                        </select>
                    </div>

                    <div id="hierarchy-floor-level-wrap" x-show="form.node_type === 'etage'">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Étage</label>
                        <select id="hierarchy-floor-level" name="floor_level" x-model="form.floor_level" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Sélectionner un étage</option>
                            @foreach (($floorCatalog ?? []) as $floorOption)
                                <option value="{{ (int) ($floorOption['level'] ?? 0) }}" @disabled(($floorOption['exists'] ?? false))>
                                    {{ (string) ($floorOption['label'] ?? 'Étage') }}{{ ($floorOption['exists'] ?? false) ? ' (déjà créé)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="hierarchy-service-select-wrap" x-show="form.node_type === 'service'" style="display: none;">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Nom du service</label>
                        <select id="hierarchy-service-id" name="service_id" x-model="form.service_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Sélectionner un service</option>
                            @forelse (($serviceCatalog ?? []) as $service)
                                @php
                                    $serviceCode = trim((string) ($service['code'] ?? ''));
                                @endphp
                                <option value="{{ (int) ($service['id'] ?? 0) }}">
                                    {{ $serviceCode !== '' ? $serviceCode . ' - ' : '' }}{{ (string) ($service['name'] ?? 'Service') }}
                                </option>
                            @empty
                                <option value="" disabled>Aucun service disponible</option>
                            @endforelse
                        </select>
                    </div>

                    <div id="hierarchy-parent-floor-wrap" x-show="form.node_type === 'service'" style="display: none;">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Étage parent</label>
                        <select id="hierarchy-parent-floor" name="parent_floor_level" x-model="form.parent_floor_level" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Sélectionner un étage</option>
                            @forelse (($floorCatalog ?? []) as $floor)
                                <option value="{{ (int) ($floor['level'] ?? 0) }}">{{ (string) ($floor['label'] ?? 'Étage') }}</option>
                            @empty
                                <option value="" disabled>Aucun étage disponible entre -1 et 4</option>
                            @endforelse
                        </select>
                    </div>

                    <p id="hierarchy-form-error" x-show="formError !== ''" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" x-text="formError" style="display: none;"></p>

                    <div class="flex justify-end gap-2 pt-2">
                        <button id="hierarchy-modal-cancel" type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="closeModal()">Annuler</button>
                        <button id="hierarchy-modal-submit" type="submit" class="rounded-xl bg-blue-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-blue-700 disabled:opacity-60" :disabled="isSaving">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>

        <div
            id="hierarchy-edit-service-modal"
            class="absolute z-[70] hidden pointer-events-none"
            style="display: none;"
        >
            <div id="hierarchy-edit-service-dialog" class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl max-h-[82vh] overflow-y-auto pointer-events-auto">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <h3 class="text-base font-semibold text-slate-900">Modifier le service</h3>
                    <button id="hierarchy-edit-modal-close-x" type="button" class="rounded-md p-1 text-slate-500 hover:bg-slate-100">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="hierarchy-edit-service-form" class="space-y-4">
                    <input id="hierarchy-edit-structure-id" type="hidden">
                    <input id="hierarchy-edit-service-id" type="hidden">

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Nom du service</label>
                        <input id="hierarchy-edit-service-name" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Code service</label>
                        <input id="hierarchy-edit-service-code" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Étage parent</label>
                        <select id="hierarchy-edit-parent-floor-level" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                            <option value="">Sélectionner un étage</option>
                            @foreach (($floorCatalog ?? []) as $floor)
                                <option value="{{ (int) ($floor['level'] ?? 0) }}">{{ (string) ($floor['label'] ?? 'Étage') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <p id="hierarchy-edit-form-error" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" style="display: none;"></p>

                    <div class="flex justify-end gap-2 pt-2">
                        <button id="hierarchy-edit-modal-cancel" type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Annuler</button>
                        <button id="hierarchy-edit-modal-submit" type="submit" class="rounded-xl bg-blue-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-blue-700 disabled:opacity-60">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/hierarchie-tree.js') }}?v={{ filemtime(public_path('js/hierarchie-tree.js')) }}" defer></script>
@endsection
