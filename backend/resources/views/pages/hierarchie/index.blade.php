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

    @if(auth()->user()?->role !== 'major')
        <form id="hierarchy-import-form" method="POST" action="{{ route('hierarchie.import-excel') }}" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="hidden" name="hospital_code" value="HME">
            <input id="hierarchy-import-file" type="file" name="excel_file" accept=".xlsx,.xls,.csv" required>
        </form>
    @endif

    <div class="mb-4 bg-white rounded-xl shadow-md p-4">
        <form method="GET" action="{{ route('hierarchie.index') }}" class="flex flex-col gap-3">
            <div class="flex flex-col md:flex-row md:items-end gap-3">
                <div class="w-full md:w-60">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Filtrer par étage</label>
                    <select name="floor" class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white">
                        <option value="">Tous les étages</option>
                        @foreach(($availableFloors ?? []) as $floor)
                            <option value="{{ $floor }}" {{ ($floorFilter ?? '') === $floor ? 'selected' : '' }}>{{ $floor }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-72">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Filtrer par service</label>
                    <select name="service" class="w-full h-10 px-3 border border-gray-300 rounded-lg bg-white">
                        <option value="">Tous les services</option>
                        @foreach(($availableServices ?? []) as $serviceName)
                            <option value="{{ $serviceName }}" {{ ($serviceFilter ?? '') === $serviceName ? 'selected' : '' }}>{{ $serviceName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" class="inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <i class="fas fa-filter"></i>
                    <span>Appliquer</span>
                </button>
                <a href="{{ route('hierarchie.index') }}" class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 px-4 text-sm font-semibold text-gray-700 transition-all duration-200 hover:bg-gray-50">
                    <i class="fas fa-rotate-left"></i>
                    <span>Réinitialiser</span>
                </a>
                <a href="{{ route('hierarchie.export-excel', ['floor' => $floorFilter ?? '', 'service' => $serviceFilter ?? '', 'with_equipments' => 1]) }}" class="inline-flex h-10 items-center gap-2 rounded-lg bg-slate-700 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300">
                    <i class="fas fa-file-export"></i>
                    <span>Exporter Excel</span>
                </a>
                @if(auth()->user()?->role !== 'major')
                    <button id="hierarchy-import-trigger" type="button" class="inline-flex h-10 items-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                        <i class="fas fa-file-excel"></i>
                        <span>Importer</span>
                    </button>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-md border border-gray-100 p-4">
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
@endsection

@section('scripts')
    <script src="{{ asset('js/hierarchie-tree.js') }}?v={{ filemtime(public_path('js/hierarchie-tree.js')) }}" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('hierarchy-import-form');
            const fileInput = document.getElementById('hierarchy-import-file');
            const trigger = document.getElementById('hierarchy-import-trigger');

            if (!form || !fileInput || !trigger) {
                return;
            }

            trigger.addEventListener('click', function () {
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
@endsection
