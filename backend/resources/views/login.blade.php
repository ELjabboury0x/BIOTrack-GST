@extends('layouts.app')

@section('title', 'Connexion - GMAO GST Tanger')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 flex items-center justify-center px-4 py-10 relative overflow-hidden">
    
    <!-- Decorative background elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-200/30 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-indigo-200/20 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-cyan-100/10 rounded-full blur-3xl"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <!-- Card Container -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden border border-white/50 animate__animated animate__fadeInUp" data-aos="zoom-in">
            <!-- Single Logo and Title -->
            <div class="flex flex-col items-center justify-center py-8 bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600 rounded-t-3xl">
                <img src="{{ asset('images/logo-gst.png') }}?v={{ filemtime(public_path('images/logo-gst.png')) }}" alt="BioTrack GST" class="h-16 w-auto mb-2 object-contain bg-white/20 rounded-xl p-2 shadow-lg">
                <h1 class="text-xl font-bold text-white mb-1">Connexion à GMAO GST Tanger</h1>
                <p class="text-white/80 text-xs font-medium tracking-widest uppercase">Système de Maintenance</p>
            </div>

            <!-- Content -->
            <div class="px-8 py-8">
                <!-- Title (removed duplicate logo, simplified) -->
                <div class="text-center mb-7">
                    <h2 class="text-2xl font-bold text-gray-900 mb-1">Bienvenue</h2>
                    <p class="text-gray-500 text-sm">Connectez-vous à <span class="font-semibold text-blue-600">GMAO GST Tanger</span></p>
                </div>

                @if (session('success'))
                    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 flex items-center gap-2">
                        <i class="fas fa-check-circle text-green-500"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 animate__animated animate__headShake">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                            <span class="font-semibold">Erreur de connexion</span>
                        </div>
                        <ul class="list-disc list-inside text-xs ml-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Login Form -->
                <form class="space-y-5" method="POST" action="{{ route('login.submit') }}" id="loginForm">
                    @csrf

                    <!-- Login Input -->
                    <div>
                        <label for="login" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-user mr-1.5 text-blue-500 text-xs"></i> Identifiant
                        </label>
                        <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                               placeholder="Votre identifiant"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-300 bg-gray-50/50 @error('login') border-red-400 @enderror">
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-lock mr-1.5 text-blue-500 text-xs"></i> Mot de passe
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required
                                   placeholder="Votre mot de passe"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-300 bg-gray-50/50 @error('password') border-red-400 @enderror">
                            <button type="button" class="absolute right-3.5 top-3.5 text-gray-400 hover:text-blue-500 transition-colors" id="toggle-password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Service Dropdown -->
                    <div>
                        <label for="service_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-hospital mr-1.5 text-blue-500 text-xs"></i> Service
                        </label>
                        <p class="mb-2 text-xs text-gray-500">
                            Obligatoire pour les profils non administrateur.
                        </p>
                        <select id="service_id" name="service_id"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-300 bg-gray-50/50 appearance-none @error('service_id') border-red-400 @enderror">
                            <option value="" {{ old('service_id') ? '' : 'selected' }}>-- Sélectionner un service --</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded-md focus:ring-blue-500 border-gray-300">
                            <span class="text-gray-500 group-hover:text-gray-700 transition-colors">Se souvenir de moi</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn"
                            class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-xl hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-300 hover:scale-[1.02] active:scale-95 relative overflow-hidden group">
                        <span class="relative z-10"><i class="fas fa-sign-in-alt mr-2"></i> Se connecter</span>
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 to-blue-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </button>
                </form>

                <!-- Footer -->
                <p class="text-center text-gray-400 text-sm mt-8">
                    Problème d'accès ? <a href="mailto:support@gst.ma" class="text-blue-600 hover:text-blue-700 font-medium hover:underline">Contactez le support</a>
                </p>
            </div>

            <!-- Bottom Accent -->
            <div class="h-1.5 bg-gradient-to-r from-blue-600 via-indigo-500 to-cyan-400"></div>
        </div>

        <!-- Additional Info (reduced visual clutter) -->
        <div class="mt-6 text-center text-gray-400 text-xs">
            <span>BioTrack GST</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ---- Password visibility toggle ----
    var toggleBtn = document.getElementById('toggle-password');
    var passwordInput = document.getElementById('password');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }

    // ---- Submit spinner ----
    var loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            var submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i> Connexion en cours...';
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-80');
        });
    }
});
</script>
@endsection
