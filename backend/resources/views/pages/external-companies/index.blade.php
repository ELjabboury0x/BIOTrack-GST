@extends('layouts.dashboard')

@section('page-title', 'Sociétés Externes')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Sociétés Externes',
    'addRoute' => 'external-companies.create',
    'addLabel' => 'Ajouter manuellement',
    'addIcon' => 'fa-building-circle-check'
])

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-xl shadow-md p-6 text-gray-700">
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <form method="GET" action="{{ route('external-companies.index') }}" class="flex items-end gap-2">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Recherche</label>
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Nom de société..." class="w-72 max-w-[75vw] px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Filtrer</button>
            <a href="{{ route('external-companies.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700">Reset</a>
        </form>

        <form method="POST" action="{{ route('external-companies.import-excel') }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
            @csrf
            <input type="file" name="companies_file" accept=".xlsx,.xls" required class="text-sm text-gray-700 max-w-[260px] border border-gray-300 rounded-lg px-2 py-1.5 bg-white">
            <button type="submit" class="inline-flex items-center px-3 py-2 border border-green-200 text-green-700 rounded-lg text-sm hover:bg-green-50">
                <i class="fas fa-file-import mr-2"></i>Importer Excel
            </button>
        </form>
    </div>

    <div class="mb-3 text-sm text-gray-500">
        Total sociétés externes: <strong>{{ ($companiesData ?? collect())->count() }}</strong>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border border-gray-200">
                <tr>
                    <th class="px-3 py-2 text-left">Nom de la société</th>
                    <th class="px-3 py-2 text-left">Équipements liés</th>
                    <th class="px-3 py-2 text-left">Créée le</th>
                    <th class="px-3 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($companiesData ?? collect()) as $company)
                    <tr class="border-b border-gray-200">
                        <td class="px-3 py-2">{{ $company->name }}</td>
                        <td class="px-3 py-2">{{ (int) ($company->linked_equipments_count ?? 0) }}</td>
                        <td class="px-3 py-2">{{ optional($company->created_at)->format('Y-m-d H:i') ?: '-' }}</td>
                        <td class="px-3 py-2">
                            <form method="POST" action="{{ route('external-companies.destroy', $company) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-red-200 text-red-700 rounded-lg text-xs hover:bg-red-50">
                                    <i class="fas fa-trash mr-1.5"></i>Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-4 text-center text-gray-500">Aucune société externe trouvée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
