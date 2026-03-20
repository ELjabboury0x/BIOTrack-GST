@extends('layouts.dashboard')

@section('page-title', 'Ajouter un technicien')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-md p-8">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Formulaire Technicien</h2>
    <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <input placeholder="Nom complet" class="px-4 py-2 border border-gray-300 rounded-lg">
        <input type="email" placeholder="E-mail" class="px-4 py-2 border border-gray-300 rounded-lg">
        <input placeholder="Téléphone" class="px-4 py-2 border border-gray-300 rounded-lg">
        <input placeholder="Spécialité" class="px-4 py-2 border border-gray-300 rounded-lg">
        <div class="md:col-span-2 flex justify-end gap-3">
            <a href="{{ route('techniciens') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Annuler</a>
            <button type="button" class="px-5 py-2 bg-blue-600 text-white rounded-lg">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
