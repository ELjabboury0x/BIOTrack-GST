@extends('layouts.dashboard')

@section('page-title', 'Paramètres Système')

@section('content')
@php
    $activeTab = old('section', $activeTab ?? session('active_tab', request('tab', 'general')));
@endphp

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-md overflow-hidden sticky top-8">
            <nav class="space-y-0">
                <a href="{{ route('parametres', ['tab' => 'general']) }}" class="block w-full text-left px-6 py-4 border-l-4 border-transparent hover:bg-gray-50 transition-colors {{ $activeTab === 'general' ? 'bg-blue-50 border-blue-500' : '' }}">
                    <i class="fas fa-sliders-h text-blue-500 mr-3"></i>
                    <span class="font-semibold text-gray-800">Général</span>
                </a>
                <a href="{{ route('parametres', ['tab' => 'security']) }}" class="block w-full text-left px-6 py-4 border-l-4 border-transparent hover:bg-gray-50 transition-colors {{ $activeTab === 'security' ? 'bg-blue-50 border-blue-500' : '' }}">
                    <i class="fas fa-shield-alt text-emerald-500 mr-3"></i>
                    <span class="font-semibold text-gray-800">Sécurité</span>
                </a>
                <a href="{{ route('parametres', ['tab' => 'notifications']) }}" class="block w-full text-left px-6 py-4 border-l-4 border-transparent hover:bg-gray-50 transition-colors {{ $activeTab === 'notifications' ? 'bg-blue-50 border-blue-500' : '' }}">
                    <i class="fas fa-bell text-yellow-500 mr-3"></i>
                    <span class="font-semibold text-gray-800">Notifications</span>
                </a>
                <a href="{{ route('parametres', ['tab' => 'integrations']) }}" class="block w-full text-left px-6 py-4 border-l-4 border-transparent hover:bg-gray-50 transition-colors {{ $activeTab === 'integrations' ? 'bg-blue-50 border-blue-500' : '' }}">
                    <i class="fas fa-plug text-red-500 mr-3"></i>
                    <span class="font-semibold text-gray-800">Intégrations</span>
                </a>
                <a href="{{ route('parametres', ['tab' => 'system']) }}" class="block w-full text-left px-6 py-4 border-l-4 border-transparent hover:bg-gray-50 transition-colors {{ $activeTab === 'system' ? 'bg-blue-50 border-blue-500' : '' }}">
                    <i class="fas fa-cogs text-indigo-500 mr-3"></i>
                    <span class="font-semibold text-gray-800">Système</span>
                </a>
            </nav>
        </div>
    </div>

    <div class="lg:col-span-3">
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

        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($activeTab === 'general')
            <div class="bg-white rounded-xl shadow-md p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Paramètres Généraux</h2>
                <form class="space-y-6" method="POST" action="{{ route('parametres.general.update') }}">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de l'Entreprise</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? 'GST Tanger') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">E-mail de support</label>
                        <input type="email" name="support_email" value="{{ old('support_email', $settings['support_email'] ?? 'support@gst.ma') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fuseau Horaire</label>
                        <select name="timezone" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="Africa/Casablanca" {{ old('timezone', $settings['timezone'] ?? 'Africa/Casablanca') === 'Africa/Casablanca' ? 'selected' : '' }}>GMT+1 (Maroc)</option>
                            <option value="UTC" {{ old('timezone', $settings['timezone'] ?? 'Africa/Casablanca') === 'UTC' ? 'selected' : '' }}>GMT+0 (UTC)</option>
                            <option value="Europe/Paris" {{ old('timezone', $settings['timezone'] ?? 'Africa/Casablanca') === 'Europe/Paris' ? 'selected' : '' }}>GMT+1 (Paris)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Langue</label>
                        <select name="language" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="fr" {{ old('language', $settings['language'] ?? 'fr') === 'fr' ? 'selected' : '' }}>Français</option>
                            <option value="en" {{ old('language', $settings['language'] ?? 'fr') === 'en' ? 'selected' : '' }}>Anglais</option>
                            <option value="ar" {{ old('language', $settings['language'] ?? 'fr') === 'ar' ? 'selected' : '' }}>العربية</option>
                        </select>
                    </div>
                    <div class="flex gap-4 pt-6 border-t border-gray-200">
                        <button type="reset" class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold">Réinitialiser</button>
                        <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:shadow-lg font-semibold">Enregistrer</button>
                    </div>
                </form>
            </div>
        @endif

        @if ($activeTab === 'security')
            <div class="bg-white rounded-xl shadow-md p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Sécurité</h2>
                <form class="space-y-6" method="POST" action="{{ route('parametres.panel.update') }}">
                    @csrf
                    <input type="hidden" name="section" value="security">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Longueur minimale mot de passe</label>
                            <input type="number" min="8" max="32" name="password_min_length" value="{{ old('password_min_length', $settings['password_min_length'] ?? 12) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Expiration session (minutes)</label>
                            <input type="number" min="5" max="1440" name="session_timeout_minutes" value="{{ old('session_timeout_minutes', $settings['session_timeout_minutes'] ?? 120) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="require_uppercase" value="1" {{ old('require_uppercase', $settings['require_uppercase'] ?? true) ? 'checked' : '' }} class="w-4 h-4">
                            <span class="ml-3 text-sm font-semibold text-gray-700">Majuscule obligatoire</span>
                        </label>
                        <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="require_numbers" value="1" {{ old('require_numbers', $settings['require_numbers'] ?? true) ? 'checked' : '' }} class="w-4 h-4">
                            <span class="ml-3 text-sm font-semibold text-gray-700">Chiffre obligatoire</span>
                        </label>
                        <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="require_symbols" value="1" {{ old('require_symbols', $settings['require_symbols'] ?? true) ? 'checked' : '' }} class="w-4 h-4">
                            <span class="ml-3 text-sm font-semibold text-gray-700">Symbole obligatoire</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rotation forcée du mot de passe (jours)</label>
                        <input type="number" min="0" max="365" name="force_password_rotation_days" value="{{ old('force_password_rotation_days', $settings['force_password_rotation_days'] ?? 90) }}" class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div class="pt-6 border-t border-gray-200">
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:shadow-lg font-semibold">Enregistrer Sécurité</button>
                    </div>
                </form>
            </div>
        @endif

        @if ($activeTab === 'notifications')
            <div class="bg-white rounded-xl shadow-md p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Paramètres de Notifications</h2>
                <form class="space-y-4" method="POST" action="{{ route('parametres.panel.update') }}">
                    @csrf
                    <input type="hidden" name="section" value="notifications">

                    <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="notifications_email" value="1" {{ old('notifications_email', $settings['notifications_email'] ?? true) ? 'checked' : '' }} class="w-4 h-4">
                        <span class="ml-4 flex-1">
                            <p class="font-semibold text-gray-800">Notifications e-mail</p>
                            <p class="text-xs text-gray-600">Recevoir les alertes par e-mail</p>
                        </span>
                    </label>

                    <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="notifications_urgent_interventions" value="1" {{ old('notifications_urgent_interventions', $settings['notifications_urgent_interventions'] ?? true) ? 'checked' : '' }} class="w-4 h-4">
                        <span class="ml-4 flex-1">
                            <p class="font-semibold text-gray-800">Alertes interventions urgentes</p>
                            <p class="text-xs text-gray-600">Notifie immédiatement les urgences</p>
                        </span>
                    </label>

                    <label class="flex items-center p-4 border border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="notifications_monthly_reports" value="1" {{ old('notifications_monthly_reports', $settings['notifications_monthly_reports'] ?? false) ? 'checked' : '' }} class="w-4 h-4">
                        <span class="ml-4 flex-1">
                            <p class="font-semibold text-gray-800">Rapports mensuels automatiques</p>
                            <p class="text-xs text-gray-600">Envoie un récapitulatif mensuel</p>
                        </span>
                    </label>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Heure du récapitulatif quotidien (0-23)</label>
                        <input type="number" min="0" max="23" name="notification_digest_hour" value="{{ old('notification_digest_hour', $settings['notification_digest_hour'] ?? 8) }}" class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div class="pt-6 border-t border-gray-200">
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:shadow-lg font-semibold">Enregistrer Notifications</button>
                    </div>
                </form>
            </div>
        @endif

        @if ($activeTab === 'integrations')
            <div class="bg-white rounded-xl shadow-md p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Intégrations</h2>
                <form class="space-y-6" method="POST" action="{{ route('parametres.panel.update') }}">
                    @csrf
                    <input type="hidden" name="section" value="integrations">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Host</label>
                            <input type="text" name="smtp_host" value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Port</label>
                            <input type="number" min="1" max="65535" name="smtp_port" value="{{ old('smtp_port', $settings['smtp_port'] ?? 587) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">SMTP Username</label>
                        <input type="text" name="smtp_username" value="{{ old('smtp_username', $settings['smtp_username'] ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">API Base URL</label>
                        <input type="url" name="external_api_base_url" value="{{ old('external_api_base_url', $settings['external_api_base_url'] ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Clé API</label>
                        <input type="text" name="external_api_key" value="{{ old('external_api_key', $settings['external_api_key'] ?? '') }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div class="pt-6 border-t border-gray-200">
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:shadow-lg font-semibold">Enregistrer Intégrations</button>
                    </div>
                </form>
            </div>
        @endif

        @if ($activeTab === 'system')
            <div class="bg-white rounded-xl shadow-md p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Système</h2>
                <form class="space-y-6" method="POST" action="{{ route('parametres.panel.update') }}">
                    @csrf
                    <input type="hidden" name="section" value="system">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Format de date</label>
                            <select name="date_format" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="d/m/Y" {{ old('date_format', $settings['date_format'] ?? 'd/m/Y') === 'd/m/Y' ? 'selected' : '' }}>d/m/Y</option>
                                <option value="Y-m-d" {{ old('date_format', $settings['date_format'] ?? 'd/m/Y') === 'Y-m-d' ? 'selected' : '' }}>Y-m-d</option>
                                <option value="m/d/Y" {{ old('date_format', $settings['date_format'] ?? 'd/m/Y') === 'm/d/Y' ? 'selected' : '' }}>m/d/Y</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Éléments par page</label>
                            <input type="number" min="5" max="200" name="items_per_page" value="{{ old('items_per_page', $settings['items_per_page'] ?? 25) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Message mode maintenance</label>
                        <textarea rows="4" name="maintenance_mode_message" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('maintenance_mode_message', $settings['maintenance_mode_message'] ?? '') }}</textarea>
                    </div>

                    <div class="pt-6 border-t border-gray-200">
                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:shadow-lg font-semibold">Enregistrer Système</button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
