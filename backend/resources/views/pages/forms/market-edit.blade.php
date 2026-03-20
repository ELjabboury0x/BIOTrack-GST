@extends('layouts.dashboard')

@section('page-title', 'Modifier marché')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Marchés & Équipements / Modifier',
    'addRoute' => null,
    'addLabel' => null,
    'addIcon' => null,
])

@if (session('error'))
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
        <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<section class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 max-w-3xl">
    <form method="POST" action="{{ route('markets.update', $market->id) }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">N° marché</label>
                <input type="text" name="market_number" value="{{ old('market_number', $market->market_number) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Référence</label>
                <input type="text" name="reference" value="{{ old('reference', $market->reference) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Société</label>
                <input type="text" name="company_name" value="{{ old('company_name', $market->company?->name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Date marché</label>
                <input type="date" name="market_date" value="{{ old('market_date', optional($market->market_date)->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" />
            </div>
        </div>

        <div class="text-sm text-gray-600">
            Équipements liés actuellement : <span class="font-semibold">{{ (int) ($market->equipments_count ?? 0) }}</span>
        </div>

        <div class="flex gap-2 pt-2">
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg">Enregistrer</button>
            <a href="{{ route('markets.equipments') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Annuler</a>
        </div>
    </form>
</section>
@endsection
