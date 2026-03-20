@extends('layouts.dashboard')

@section('page-title', 'Ajouter un mouvement')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-md p-8">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Formulaire Décharge & Réception</h2>
    <form class="grid grid-cols-1 md:grid-cols-2 gap-6" method="POST" action="{{ route('stock.store') }}">
        @csrf
        <select name="movement_type" class="px-4 py-2 border border-gray-300 rounded-lg" required>
            <option value="">Type (Décharge/Réception)</option>
            <option value="decharge" {{ old('movement_type') === 'decharge' ? 'selected' : '' }}>Décharge</option>
            <option value="reception" {{ old('movement_type') === 'reception' ? 'selected' : '' }}>Réception</option>
        </select>
        <input name="part_reference" value="{{ old('part_reference') }}" placeholder="Référence pièce" class="px-4 py-2 border border-gray-300 rounded-lg" required>
        <input type="number" min="1" name="quantity" value="{{ old('quantity') }}" placeholder="Quantité" class="px-4 py-2 border border-gray-300 rounded-lg" required>
        <input type="date" name="movement_date" value="{{ old('movement_date') }}" class="px-4 py-2 border border-gray-300 rounded-lg" required>
        <textarea name="description" placeholder="Description" class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-lg" rows="3">{{ old('description') }}</textarea>
        <div class="md:col-span-2 flex justify-end gap-3">
            <a href="{{ route('stock.movements') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Annuler</a>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
