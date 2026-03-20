@extends('layouts.dashboard')

@section('page-title', 'Ajouter une pièce')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-md p-8">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Formulaire Pièce de Rechange</h2>
    <form class="grid grid-cols-1 md:grid-cols-2 gap-6" method="POST" action="{{ route('pieces.store') }}">
        @csrf
        <input name="code" value="{{ old('code') }}" placeholder="Code pièce" class="px-4 py-2 border border-gray-300 rounded-lg" required>
        <input name="name" value="{{ old('name') }}" placeholder="Nom pièce" class="px-4 py-2 border border-gray-300 rounded-lg" required>
        <input type="number" min="0" name="quantity" value="{{ old('quantity') }}" placeholder="Quantité" class="px-4 py-2 border border-gray-300 rounded-lg" required>
        <input type="number" min="0" step="0.01" name="unit_price" value="{{ old('unit_price') }}" placeholder="Prix unitaire" class="px-4 py-2 border border-gray-300 rounded-lg" required>
        <input name="supplier" value="{{ old('supplier') }}" placeholder="Fournisseur" class="px-4 py-2 border border-gray-300 rounded-lg">
        <textarea name="description" placeholder="Description" class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-lg" rows="3">{{ old('description') }}</textarea>
        <div class="md:col-span-2 flex justify-end gap-3">
            <a href="{{ route('pieces') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Annuler</a>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
