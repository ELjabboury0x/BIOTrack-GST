@extends('layouts.app')

@section('title', 'GMAO - Gestion de Maintenance GST Tanger')

@section('content')
    <!-- Hero Section -->
    <section class="hero-section relative min-h-screen flex items-center justify-center overflow-hidden pt-20" style="background-image: url('/images/btata.webp'); background-size: cover; background-position: center; background-attachment: fixed;">
        <!-- Overlay for better text readability -->
        <div class="absolute inset-0 bg-black bg-opacity-10 z-0"></div>

        <!-- Content -->
        <div class="container mx-auto px-4 py-20 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <img src="{{ asset('images/logo-gst.png') }}?v={{ filemtime(public_path('images/logo-gst.png')) }}" alt="BioTrack GST" class="h-40 md:h-64 w-auto mx-auto mb-6 object-contain animate-pop-up">
                
                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mt-12"
                     style="animation: fadeInUp 1s ease-out 0.3s both;">
                    <a href="#features" class="btn btn-primary px-8 py-4 rounded-full font-semibold transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                        <i class="fas fa-arrow-right mr-2"></i> Découvrir le projet
                    </a>
                    <a href="#contact" class="btn btn-secondary px-8 py-4 rounded-full font-semibold transition-all duration-300 hover:scale-105">
                        <i class="fas fa-envelope mr-2"></i> Nous Contacter
                    </a>
                </div>
            </div>

            <!-- Scroll Indicator -->
            <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-24 bg-gradient-to-b from-white to-blue-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4" data-aos="fade-up">À propos du Projet</h2>
                <div class="w-20 h-1 bg-gradient-to-r from-blue-400 to-blue-600 mx-auto" data-aos="zoom-in" data-aos-delay="200"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mb-20">
                <div data-aos="fade-right">
                    <p class="text-gray-700 text-lg leading-relaxed mb-4">
                        Ce projet représente une <strong>solution complète et professionnelle</strong> pour la gestion de maintenance. Développé comme Projet de Fin d'Études (PFE) pour <strong>GST Tanger</strong>, il incarne l'excellence académique et l'innovation technologique.
                    </p>
                    <p class="text-gray-700 text-lg leading-relaxed">
                        Le système GMAO offre une plateforme intuitive et puissante permettant aux entreprises d'optimiser leurs processus de maintenance, réduire les temps d'arrêt et améliorer la productivité globale.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" data-aos="fade-left" data-aos-delay="200">
                    <!-- Feature Cards -->
                    @php
                        $features = [
                            ['icon' => 'fa-wrench', 'title' => 'Équipements', 'desc' => 'Gestion centralisée des équipements'],
                            ['icon' => 'fa-notes-medical', 'title' => 'OT/DM (PM-BIO)', 'desc' => 'Suivi complet des OT/DM biomédicaux'],
                            ['icon' => 'fa-calendar-check', 'title' => 'Planification', 'desc' => 'Planification préventive intelligente'],
                            ['icon' => 'fa-chart-bar', 'title' => 'Rapports', 'desc' => 'Statistiques et analytics détaillés'],
                        ];
                    @endphp

                    @foreach($features as $feature)
                    <div class="bg-white rounded-xl p-6 shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-2 cursor-pointer group"
                         data-aos="zoom-in" data-aos-delay="{{ $loop->index * 100 }}">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                            <i class="fas {{ $feature['icon'] }} text-white text-xl"></i>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2">{{ $feature['title'] }}</h3>
                        <p class="text-gray-600 text-sm">{{ $feature['desc'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Project Info -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-8 text-white text-center" data-aos="zoom-in" data-aos-delay="400">
                <h3 class="text-2xl font-bold mb-2">Projet Académique de Haut Niveau</h3>
                <p class="text-blue-100">Développé selon les standards internationaux avec une architecture MVC robuste et une interface utilisateur moderne</p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4" data-aos="fade-up">Fonctionnalités Principales</h2>
                <div class="w-20 h-1 bg-gradient-to-r from-blue-400 to-blue-600 mx-auto" data-aos="zoom-in" data-aos-delay="200"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @php
                    $mainFeatures = [
                        ['icon' => 'fa-database', 'title' => 'Base de Données Centralisée', 'desc' => 'Tous les données de maintenance au même endroit'],
                        ['icon' => 'fa-bell', 'title' => 'Notifications Automatiques', 'desc' => 'Alertes pour les maintenances préventives'],
                        ['icon' => 'fa-users', 'title' => 'Gestion d\'Équipes', 'desc' => 'Attribution et suivi des tâches par équipe'],
                        ['icon' => 'fa-mobile-alt', 'title' => 'Application Mobile Ready', 'desc' => 'Accès depuis n\'importe quel appareil'],
                        ['icon' => 'fa-lock', 'title' => 'Sécurité Renforcée', 'desc' => 'Protection des données avec authentification avancée'],
                        ['icon' => 'fa-sync', 'title' => 'Synchronisation Temps Réel', 'desc' => 'Mise à jour instantanée des informations'],
                    ];
                @endphp

                @foreach($mainFeatures as $feature)
                <div class="feature-card bg-gradient-to-br from-blue-50 to-white rounded-xl p-8 border border-blue-100 hover:border-blue-400 transition-all duration-300"
                     data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center mb-6 feature-icon">
                        <i class="fas {{ $feature['icon'] }} text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $feature['title'] }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $feature['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-24 bg-gradient-to-r from-blue-600 to-blue-400 text-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                @php
                    $stats = [
                        ['number' => '5+', 'label' => 'Modules Intégrés'],
                        ['number' => '100%', 'label' => 'Responsive Design'],
                        ['number' => '24/7', 'label' => 'Support Disponible'],
                        ['number' => '99.9%', 'label' => 'Disponibilité'],
                    ];
                @endphp

                @foreach($stats as $stat)
                <div class="stat-card" data-aos="zoom-in" data-aos-delay="{{ $loop->index * 100 }}">
                    <h3 class="text-5xl font-bold mb-2 counter" data-target="{{ $stat['number'] }}">0</h3>
                    <p class="text-blue-100 text-lg">{{ $stat['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-24 bg-gradient-to-b from-white to-blue-50">
        <div class="container mx-auto px-4 max-w-2xl">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4" data-aos="fade-up">Nous Contacter</h2>
                <p class="text-gray-600 text-lg" data-aos="fade-up" data-aos-delay="200">
                    Vous avez des questions ? Nous serions ravis de vous aider.
                </p>
                <div class="w-20 h-1 bg-gradient-to-r from-blue-400 to-blue-600 mx-auto mt-4" data-aos="zoom-in" data-aos-delay="400"></div>
            </div>

            <form class="bg-white rounded-2xl shadow-xl p-8" data-aos="zoom-in" data-aos-delay="300">
                @csrf
                <div class="mb-6">
                    <div class="relative">
                        <input type="text" id="nom" name="nom" placeholder=" " required
                               class="w-full px-6 py-4 border-2 border-blue-200 rounded-lg focus:outline-none focus:border-blue-600 transition-colors duration-300 peer bg-transparent">
                        <label for="nom" class="absolute left-6 top-4 text-gray-400 transition-all duration-300 peer-focus:text-blue-600 peer-focus:-top-6 peer-focus:text-sm peer-[:not(:placeholder-shown)]:-top-6 peer-[:not(:placeholder-shown)]:text-sm">
                            Nom Complet
                        </label>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="relative">
                        <input type="email" id="email" name="email" placeholder=" " required
                               class="w-full px-6 py-4 border-2 border-blue-200 rounded-lg focus:outline-none focus:border-blue-600 transition-colors duration-300 peer bg-transparent">
                        <label for="email" class="absolute left-6 top-4 text-gray-400 transition-all duration-300 peer-focus:text-blue-600 peer-focus:-top-6 peer-focus:text-sm peer-[:not(:placeholder-shown)]:-top-6 peer-[:not(:placeholder-shown)]:text-sm">
                            Adresse Email
                        </label>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="relative">
                        <textarea id="message" name="message" placeholder=" " rows="5" required
                                  class="w-full px-6 py-4 border-2 border-blue-200 rounded-lg focus:outline-none focus:border-blue-600 transition-colors duration-300 peer bg-transparent resize-none"></textarea>
                        <label for="message" class="absolute left-6 top-4 text-gray-400 transition-all duration-300 peer-focus:text-blue-600 peer-focus:-top-6 peer-focus:text-sm peer-[:not(:placeholder-shown)]:-top-6 peer-[:not(:placeholder-shown)]:text-sm">
                            Votre Message
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full btn btn-primary py-4 rounded-lg font-bold transition-all duration-300 hover:scale-105">
                    <i class="fas fa-paper-plane mr-2"></i> Envoyer le Message
                </button>
            </form>
        </div>
    </section>
@endsection

@section('scripts')
@section('head')
<style>
@keyframes popUpLogo {
    0% {
        transform: scale(0.7);
        opacity: 0;
    }
    60% {
        transform: scale(1.15);
        opacity: 1;
    }
    80% {
        transform: scale(0.95);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}
.animate-pop-up {
    animation: popUpLogo 0.8s cubic-bezier(0.23, 1.2, 0.32, 1) 0.2s both;
}
</style>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({
            duration: 1000,
            offset: 100,
            easing: 'ease-out-cubic'
        });

        // Counter animation
        const observerOptions = {
            threshold: 0.5
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        const statsSection = document.querySelector('.bg-gradient-to-r.from-blue-600');
        if (statsSection) observer.observe(statsSection);
    });

    function animateCounters() {
        const counters = document.querySelectorAll('.counter');
        counters.forEach(counter => {
            const target = counter.dataset.target;
            const finalValue = parseFloat(target);
            const isPercentage = target.includes('%');
            const isSpecial = target.includes('+') || target.includes('/');
            
            counter.textContent = target;
        });
    }

    // Smooth scroll for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const element = document.querySelector(href);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });
</script>
@endsection
