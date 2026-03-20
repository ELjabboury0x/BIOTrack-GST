@extends('layouts.dashboard')

@section('page-title', 'Marché ' . $marketData['market_number'])

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Marchés & Équipements / Marché ' . $marketData['market_number'],
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
            {{ count($marketData['equipments']) }} équipements
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-white">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">N° Inventaire</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nom équipement</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">SN</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">N° intervention</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Service</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($marketData['equipments'] as $equipment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $equipment['inventory_number'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $equipment['designation'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $equipment['serial_number'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $equipment['intervention_code'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $equipment['service_name'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            @if(auth()->user()?->role !== 'major')
                            <details class="group">
                                <summary class="list-none cursor-pointer px-3 py-1 border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 inline-block">
                                    Modifier
                                </summary>
                                <form method="POST"
                                      action="{{ route('markets.equipments.update-equipment', $equipment['id']) }}"
                                      class="mt-2 w-[22rem] space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="inventory_number_current" value="{{ $equipment['inventory_number'] }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="N° inventaire" required>
                                    <input type="text" name="designation" value="{{ $equipment['designation'] }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Nom équipement" required>
                                    <input type="text" name="serial_number" value="{{ $equipment['serial_number'] === '-' ? '' : $equipment['serial_number'] }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="N° série">
                                    <input type="text" name="intervention_code" value="{{ $equipment['intervention_code'] === '-' ? '' : $equipment['intervention_code'] }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="N° intervention">
                                    <input type="text" name="service_name" value="{{ $equipment['service_name'] === '-' ? '' : $equipment['service_name'] }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Service">
                                    <button type="submit" class="w-full px-3 py-2 bg-blue-600 text-white rounded-lg">Enregistrer</button>
                                </form>
                            </details>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-sm text-gray-500 text-center">Aucun équipement pour ce marché.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
