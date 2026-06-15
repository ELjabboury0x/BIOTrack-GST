@extends('layouts.dashboard')

@section('page-title', 'Marché ' . $marketData['market_number'])

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Marchés et contrats de maintenance / Marché ' . $marketData['market_number'],
    'addRoute' => null,
    'addLabel' => null,
    'addIcon' => null,
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

<div class="mb-4">
    <a href="{{ route('markets.equipments') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
        <i class="fas fa-arrow-left text-sm"></i>
        Retour à la liste des marchés
    </a>
</div>

<section class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 bg-gray-50 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Marché: {{ $marketData['market_number'] }}</h3>
            <p class="text-sm text-gray-600">Référence: {{ $marketData['reference'] }} | Société: {{ $marketData['company'] }} | Date: {{ $marketData['market_date'] }}</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
            {{ count($marketData['import_lines']) }} lignes importées
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-white">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Ligne</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Objet marché</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Article</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Désignation</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Quantité</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut livraison</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($marketData['import_lines'] as $line)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $line['source_row_index'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $line['market_object'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $line['article'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $line['designation'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $line['quantity'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $line['delivery_status'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-sm text-gray-500 text-center">Aucune ligne importée pour ce marché.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
