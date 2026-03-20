@extends('layouts.dashboard')

@section('page-title', 'Authentification et sécurité')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6 md:p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Authentification et sécurité</h2>
    <p class="text-sm text-gray-600 mb-6">Configuration sécurité, contrôle de session et permissions par rôle.</p>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('parametres.panel.update') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="section" value="security">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Longueur minimale mot de passe</label>
                <input type="number" name="password_min_length" min="8" max="32" value="{{ old('password_min_length', $settings['password_min_length'] ?? 12) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Expiration de session (minutes)</label>
                <input type="number" name="session_timeout_minutes" min="5" max="1440" value="{{ old('session_timeout_minutes', $settings['session_timeout_minutes'] ?? 120) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <label class="flex items-center gap-2 p-3 border border-gray-300 rounded-lg"><input type="checkbox" name="require_uppercase" value="1" {{ old('require_uppercase', $settings['require_uppercase'] ?? true) ? 'checked' : '' }}> <span>Majuscule obligatoire</span></label>
            <label class="flex items-center gap-2 p-3 border border-gray-300 rounded-lg"><input type="checkbox" name="require_numbers" value="1" {{ old('require_numbers', $settings['require_numbers'] ?? true) ? 'checked' : '' }}> <span>Chiffre obligatoire</span></label>
            <label class="flex items-center gap-2 p-3 border border-gray-300 rounded-lg"><input type="checkbox" name="require_symbols" value="1" {{ old('require_symbols', $settings['require_symbols'] ?? true) ? 'checked' : '' }}> <span>Symbole obligatoire</span></label>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Rotation mot de passe (jours)</label>
            <input type="number" name="force_password_rotation_days" min="0" max="365" value="{{ old('force_password_rotation_days', $settings['force_password_rotation_days'] ?? 90) }}" class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="pt-4 border-t border-gray-200">
            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">Enregistrer la sécurité</button>
        </div>
    </form>

    <div class="mt-8 border-t border-gray-200 pt-6">
        <h3 class="text-lg font-bold text-gray-800 mb-3">Permissions d’accès par page</h3>
        <div class="space-y-2 text-sm text-gray-700">
            <p><span class="font-semibold">Admin</span> → accès complet</p>
            <p><span class="font-semibold">Opérateur</span> → saisie des défauts uniquement</p>
            <p><span class="font-semibold">Technicien</span> → état PLC et journaux</p>
        </div>
    </div>
</div>
@endsection
