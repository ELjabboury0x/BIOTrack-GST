@extends('layouts.app')

@section('title', 'Tableau de Bord - GMAO')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-white pt-32 pb-16">
    <div class="container mx-auto px-4">
        <!-- Welcome Section -->
        <div class="mb-16 text-center">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Tableau de Bord GMAO</h1>
            <p class="text-xl text-gray-600">Bienvenue dans votre système de gestion de maintenance</p>
        </div>

        <!-- Coming Soon Message -->
        <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-xl p-12 text-center">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-wrench text-4xl text-white"></i>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Module en Construction</h2>
            <p class="text-lg text-gray-600 mb-8">
                Le tableau de bord complet avec tous les modules sera bientôt disponible. 
                Continuez l'intégration des fonctionnalités principales du système GMAO.
            </p>
            
            <div class="space-y-3 text-left bg-gray-50 rounded-lg p-6 mb-8">
                <h3 class="font-bold text-gray-900 mb-4">Modules en cours de développement:</h3>
                <ul class="space-y-2">
                    <li class="text-gray-700"><i class="fas fa-check-circle text-green-500 mr-3"></i>  Gestion des Équipements</li>
                    <li class="text-gray-700"><i class="fas fa-check-circle text-green-500 mr-3"></i> Gestion OT/DM (PM-BIO)</li>
                    <li class="text-gray-700"><i class="fas fa-check-circle text-green-500 mr-3"></i> Planification de Maintenance</li>
                    <li class="text-gray-700"><i class="fas fa-check-circle text-green-500 mr-3"></i> Rapports et Statistiques</li>
                </ul>
            </div>

            <a href="/" class="inline-block px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-500 text-white font-bold rounded-lg hover:shadow-lg transition-all duration-300">
                <i class="fas fa-arrow-left mr-2"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</div>
@endsection
