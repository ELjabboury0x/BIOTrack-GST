@extends('layouts.dashboard')

@section('page-title', 'Ajouter un équipement')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md p-8">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-gray-800">Ajouter un équipement</h2>
        <a href="{{ route('equipements') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Retour</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('equipements.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-6" enctype="multipart/form-data">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Numéro inventaire (unique) <span class="text-red-500">*</span></label>
            <input name="inventory_number_current" value="{{ old('inventory_number_current') }}" placeholder="Ex: MMT-0111/1" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Numéro de série</label>
            <input name="serial_number" value="{{ old('serial_number') }}" placeholder="Ex: 13118/12818" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Désignation <span class="text-red-500">*</span></label>
            <input name="designation" value="{{ old('designation') }}" placeholder="Ex: Bras de distribution plafonnier" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Marque</label>
            <input name="brand_name" value="{{ old('brand_name') }}" placeholder="Ex: Dräger" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Modèle</label>
            <input name="model_name" value="{{ old('model_name') }}" placeholder="Ex: RTM3" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Société (fournisseur / société externe)</label>
            <input name="company_name" value="{{ old('company_name') }}" placeholder="Ex: ACME MED" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Unité</label>
            <input name="unit_name" value="{{ old('unit_name') }}" placeholder="Ex: Réanimation" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Secteur</label>
            <input name="sector_name" value="{{ old('sector_name') }}" placeholder="Ex: Bloc opératoire" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Description secteur</label>
            <input name="sector_description" value="{{ old('sector_description') }}" placeholder="Ex: Bâtiment A - Étage 2" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Marché</label>
            <input name="market_label" value="{{ old('market_label') }}" placeholder="Ex: M04-2020" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Lot</label>
            <input name="lot_number" value="{{ old('lot_number') }}" placeholder="Ex: L55" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Article</label>
            <input name="article" value="{{ old('article') }}" placeholder="Ex: ART-001" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date de réception provisoire</label>
            <input type="date" name="date_reception_provisoire" value="{{ old('date_reception_provisoire') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Durée de garantie</label>
            <input name="duree_garantie" value="{{ old('duree_garantie') }}" placeholder="Ex: 24 mois" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date de réception définitive</label>
            <input type="date" name="date_reception_definitive" value="{{ old('date_reception_definitive') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="md:col-span-2 rounded-lg border border-blue-100 bg-blue-50 p-4">
            <p class="text-sm text-blue-800 font-semibold mb-3">Fichiers partagés par désignation</p>
            <p class="text-xs text-blue-700 mb-4">Ces fichiers seront visibles pour tous les équipements ayant la même désignation.</p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Image équipement</label>
                    <input type="file" name="designation_image" accept=".jpg,.jpeg,.png,.webp" class="w-full text-sm text-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Manuel d'utilisation</label>
                    <input type="file" name="user_manual_file" accept=".pdf" class="w-full text-sm text-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Manuel technique</label>
                    <input type="file" name="technical_manual_file" accept=".pdf" class="w-full text-sm text-gray-700">
                </div>
            </div>
        </div>

        <div class="md:col-span-2 flex justify-end gap-3">
            <a href="{{ route('equipements') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Annuler</a>
            <button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
