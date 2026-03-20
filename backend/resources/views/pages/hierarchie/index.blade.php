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

    <section class="hierarchy-page">
        <div class="hierarchy-hero">
            <div class="hierarchy-hero-logo-wrap">
                    <img src="{{ asset('images/logo-gst.png') }}?v={{ filemtime(public_path('images/logo-gst.png')) }}" alt="GST" class="hierarchy-hero-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="hierarchy-hero-logo-fallback" style="display:none;">
                    <i class="fas fa-heartbeat"></i>
                </div>
            </div>

            <div>
                <h1 class="hierarchy-title">Hiérarchie CHU GST</h1>
                <p class="hierarchy-subtitle">Arborescence dynamique du Groupement Sanitaire Territorial de Tanger</p>
            </div>
        </div>

        <div class="hierarchy-card">
            <form method="POST" action="{{ route('hierarchie.import-excel') }}" enctype="multipart/form-data" class="mb-4 p-4 border border-blue-100 rounded-xl bg-blue-50/60">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Importer fichier Excel hiérarchie</label>
                        <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" required class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Code hôpital</label>
                        <input type="text" name="hospital_code" value="HME" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white uppercase" placeholder="HME">
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-600">Cet import remplace la structure hiérarchique de l'hôpital ciblé, retire les anciens équipements de la hiérarchie, puis réaffecte les pannes quand la correspondance d'équipement est possible.</p>
                <div class="mt-3">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">Importer et synchroniser</button>
                </div>
            </form>

            <form method="GET" action="{{ route('hierarchie.index') }}" class="mb-4 flex flex-col gap-3 p-4 border border-gray-100 rounded-xl bg-gray-50">
                <div class="flex flex-col md:flex-row gap-3">
                <div class="w-full md:w-60">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Filtrer par étage</label>
                    <select name="floor" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white">
                        <option value="">Tous les étages</option>
                        @foreach(($availableFloors ?? []) as $floor)
                            <option value="{{ $floor }}" {{ ($floorFilter ?? '') === $floor ? 'selected' : '' }}>{{ $floor }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-72">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Filtrer par service</label>
                    <select name="service" class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white">
                        <option value="">Tous les services</option>
                        @foreach(($availableServices ?? []) as $serviceName)
                            <option value="{{ $serviceName }}" {{ ($serviceFilter ?? '') === $serviceName ? 'selected' : '' }}>{{ $serviceName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2 md:items-end">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">Appliquer</button>
                    <a href="{{ route('hierarchie.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-sm text-gray-700 hover:bg-white">Réinitialiser</a>
                    <a href="{{ route('hierarchie.export-excel', ['floor' => $floorFilter ?? '', 'service' => $serviceFilter ?? '', 'with_equipments' => 1]) }}" class="px-4 py-2 rounded-lg border border-emerald-300 text-emerald-700 text-sm hover:bg-emerald-50">Exporter Excel</a>
                </div>
                </div>
            </form>

            @if (empty($tree))
                <div class="hierarchy-empty">
                    <i class="fas fa-sitemap"></i>
                    <p>Aucune donnée hiérarchique disponible.</p>
                </div>
            @else
                <div class="hierarchy-toolbar">
                    <div class="hierarchy-search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" id="hierarchy-search" placeholder="Rechercher une structure, un service, un code...">
                        <button type="button" id="hierarchy-search-clear" title="Effacer"><i class="fas fa-times"></i></button>
                    </div>
                </div>

                <ul class="hierarchy-tree" id="hierarchy-tree">
                    @foreach ($tree as $node)
                        @include('pages.hierarchie.partials.node', ['node' => $node, 'level' => 1])
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('js/hierarchie-tree.js') }}?v={{ filemtime(public_path('js/hierarchie-tree.js')) }}" defer></script>
@endsection
