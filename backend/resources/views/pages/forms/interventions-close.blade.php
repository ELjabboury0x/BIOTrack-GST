@extends('layouts.dashboard')

@section('page-title', 'Clôture PM-BIO')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md p-8">
    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
        <i class="fas fa-user-doctor text-green-600 mr-2"></i> Clôture intervention (PM-BIO / SAP-PM)
    </h2>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-500">Code intervention</p>
            <p class="font-semibold text-gray-800">{{ $intervention->code }}</p>
        </div>
        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
            <p class="text-sm text-gray-500">Équipement</p>
            <p class="font-semibold text-gray-800">
                {{ trim(($intervention->equipment?->inventory_number_current ?? '') . ' - ' . ($intervention->equipment?->designation ?? ''), ' -') ?: ('Équipement #' . $intervention->equipment_id) }}
            </p>
        </div>
    </div>

    <form method="POST" action="{{ route('interventions.close', $intervention->id) }}" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date de fin <span class="text-red-500">*</span></label>
            <input type="date" name="date_end" value="{{ old('date_end', now()->toDateString()) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Cause / code panne</label>
            <input type="text" name="failure_cause" value="{{ old('failure_cause') }}" placeholder="Ex: Usure normale, rupture pièce, erreur utilisateur" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Compte-rendu de clôture <span class="text-red-500">*</span></label>
            <textarea name="closure_note" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Décrire les travaux réalisés, tests fonctionnels et validation finale" required>{{ old('closure_note') }}</textarea>
        </div>

        <div class="md:col-span-2 flex justify-end gap-3">
            <a href="{{ route('interventions') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Annuler</a>
            <button type="submit" class="px-5 py-2 bg-green-600 text-white rounded-lg">Clôturer</button>
        </div>
    </form>
</div>
@endsection
