@extends('layouts.dashboard')

@section('page-title', 'Ajouter Société Externe')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / Sociétés Externes / Ajouter',
])

<div class="bg-white rounded-xl shadow-md p-6 text-gray-700 max-w-2xl">
    <h2 class="text-xl font-bold text-gray-800 mb-1">Ajouter une société externe</h2>
    <p class="text-sm text-gray-500 mb-5">Ajout manuel d’une société intervenant dans l’hôpital.</p>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('external-companies.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de la société</label>
            <input type="text" name="name" value="{{ old('name') }}" required maxlength="180" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Ex: MOROCCO HEALTHCARE SUPPLIER">
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg">Enregistrer</button>
            <a href="{{ route('external-companies.index') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Annuler</a>
        </div>
    </form>
</div>
@endsection
