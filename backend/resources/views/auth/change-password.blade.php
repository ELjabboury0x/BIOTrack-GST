@extends('layouts.dashboard')

@section('title', 'Changer le mot de passe')

@section('content')
<div class="max-w-xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
    <h1 class="text-xl md:text-2xl font-bold text-gray-900">Sécurisation du compte</h1>
    <p class="mt-2 text-sm text-gray-600">Le mot de passe temporaire a été imposé par l’ingénieur biomédical. Merci de le remplacer par un mot de passe personnel sécurisé.</p>

    @if(session('warning'))
        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3 text-sm">
            {{ session('warning') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf

        <div>
            <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-1.5">Mot de passe actuel</label>
            <input id="current_password" name="current_password" type="password" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none" />
        </div>

        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Nouveau mot de passe</label>
            <input id="password" name="password" type="password" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none" />
            <p class="mt-1 text-xs text-gray-500">
                {{ $passwordPolicy['min'] ?? 12 }}+ caractères
                @if(($passwordPolicy['uppercase'] ?? true)) avec majuscule @endif
                @if(($passwordPolicy['numbers'] ?? true)), chiffre @endif
                @if(($passwordPolicy['symbols'] ?? true)) et symbole @endif
                .
            </p>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">Confirmer le nouveau mot de passe</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition-all duration-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none" />
        </div>

        <div class="pt-2">
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-white text-sm font-medium hover:bg-blue-700">
                Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
