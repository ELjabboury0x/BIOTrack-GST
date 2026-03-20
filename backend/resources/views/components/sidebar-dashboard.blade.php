<!-- Sidebar -->
@php
    $authUser = auth()->user();
    $displayName = $authUser?->name ?: $authUser?->login ?: 'Utilisateur';
    $role = $authUser?->role;
    $isAdmin = $role === 'admin';
    $isMajor = $role === 'major';
    $isAdminLike = in_array($role, ['admin', 'manager', 'major', 'ingenieur', 'technicien', 'technician'], true);
    $canAccessMarkets = in_array($role, ['admin', 'manager', 'major', 'ingenieur', 'technicien', 'technician'], true);
    $canAccessComplaints = in_array($role, ['admin', 'ingenieur', 'major', 'technicien', 'technician'], true);
    $canAccessCurativeMaintenance = in_array($role, ['ingenieur', 'technicien', 'technician'], true);
    $isOperator = $role === 'operator';
    $isTechnician = in_array($role, ['technician', 'technicien'], true);
    $canManageUsers = in_array($role, ['admin', 'ingenieur'], true);
    $homeRoute = $isOperator ? 'operator.defects.create' : 'dashboard';
    $displayRole = match ($authUser?->role) {
        'admin' => 'Administrateur',
        'manager' => 'Gestionnaire',
        'operator' => 'Opérateur',
        'technician' => 'Technicien',
        'major' => 'Major',
        'technicien' => 'Technicien',
        'ingenieur' => 'Ingénieur',
        default => 'Utilisateur',
    };
@endphp

<aside class="gst-sidebar fixed inset-y-0 left-0 z-40 text-white shadow-lg overflow-y-auto transform transition-[width,transform] duration-350 ease-[cubic-bezier(0.22,1,0.36,1)] will-change-transform lg:static lg:translate-x-0"
       id="sidebar"
       @mouseenter="if (window.innerWidth >= 1024) sidebarHovered = true"
       @mouseleave="if (window.innerWidth >= 1024) sidebarHovered = false"
    :class="[
        mobileSidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full lg:translate-x-0',
        (sidebarCollapsed && !sidebarHovered) ? 'lg:w-20 collapsed' : 'lg:w-64'
    ]">
    
    <div class="p-5">
        <!-- Logo -->
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route($homeRoute) }}" class="flex flex-col items-center w-full group">
                <div class="flex items-center gap-3 w-full">
                    <img src="{{ asset('icons/icon-512x512-logo-only.png') }}?v={{ filemtime(public_path('icons/icon-512x512-logo-only.png')) }}" alt="GST Logo" class="gst-logo-icon h-12 w-12 object-contain bg-white/20 rounded-xl p-2 shadow-lg animate-pop-up">
                    <div class="gst-sidebar-header-text text-left">
                        <h1 class="font-bold text-white tracking-wide text-base group-hover:text-blue-200 transition-colors">BioTrack GST</h1>
                        <p class="text-blue-300 text-[10px] uppercase tracking-widest font-medium">SYSTÈME GMAO</p>
                    </div>
                </div>
            </a>

            <div class="lg:hidden flex items-center gap-2">
                <button @click="mobileSidebarOpen = false" class="text-blue-200 hover:text-white transition-colors w-8 h-8 rounded-lg hover:bg-white/10 flex items-center justify-center" title="Fermer le menu">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- User Info Card -->
        <div class="mb-6 px-4 py-3 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm gst-user-card">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-cyan-300 flex items-center justify-center text-sm font-bold text-blue-900 shadow-md">
                    {{ strtoupper(mb_substr($displayName, 0, 1)) }}
                </div>
                <div class="min-w-0 gst-user-meta">
                    <p class="text-sm font-semibold text-white truncate">{{ $displayName }}</p>
                    <span class="inline-flex items-center mt-0.5 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-500/30 text-blue-200 border border-blue-400/20">
                        {{ $displayRole }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="space-y-1 gst-sidebar-nav">
            <!-- Section label -->
            <p class="px-3 text-[10px] uppercase tracking-widest text-blue-300/60 font-semibold mb-2 gst-section-label">Navigation</p>

            <!-- Home -->
            <a href="{{ route($homeRoute) }}" 
                    @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }"
               class="gst-nav-link {{ request()->routeIs('dashboard') || request()->routeIs('operator.defects.*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200">
                <i class="fas fa-home w-5 text-center"></i>
                <span class="ml-3 text-sm">Tableau de Bord</span>
            </a>

            @if($isAdminLike)
            <a href="{{ route('hierarchie.index') }}"
               @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }"
               class="gst-nav-link {{ request()->routeIs('hierarchie.*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200">
                <i class="fas fa-sitemap w-5 text-center"></i>
                <span class="ml-3 text-sm">Hiérarchie CHU</span>
            </a>
            @endif

            @if($isAdminLike)
                <a href="{{ route('equipements') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('equipements*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-tools w-5 text-center"></i><span class="ml-3 text-sm">Équipements</span></a>
                <a href="{{ route('formations.index') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('formations.*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-graduation-cap w-5 text-center"></i><span class="ml-3 text-sm">Formations</span></a>
                <a href="{{ route('interventions') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('interventions*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-notes-medical w-5 text-center"></i><span class="ml-3 text-sm">OT/DM (PM-BIO)</span></a>
                @if($isMajor)
                <a href="{{ route('operator.defects.create') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('operator.defects.*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-triangle-exclamation w-5 text-center"></i><span class="ml-3 text-sm">Nouvelle Réclamation</span></a>
                @endif
                @if($canAccessComplaints)
                <a href="{{ route('reclamations.index') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('reclamations.*') || request()->routeIs('dashboard.notifications.complaints.*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-clipboard-list w-5 text-center"></i><span class="ml-3 text-sm">Historique Réclamations</span></a>
                @endif
                <a href="{{ route('markets.equipments') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('markets.equipments') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-file-signature w-5 text-center"></i><span class="ml-3 text-sm">Marchés & Équipements</span></a>
                <a href="{{ route('maintenance-preventive') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('maintenance-preventive*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-calendar-alt w-5 text-center"></i><span class="ml-3 text-sm">Maintenance Préventive</span></a>
                <a href="{{ route('maintenance-reports.index', ['type' => 'curative']) }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('maintenance-reports.index') && request()->query('type') === 'curative' ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-calendar-alt w-5 text-center"></i><span class="ml-3 text-sm">Maintenance corrective</span></a>
                @if(!$isMajor)
                <a href="{{ route('techniciens') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('techniciens*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-users w-5 text-center"></i><span class="ml-3 text-sm">Utilisateurs</span></a>
                @endif
                <a href="{{ route('pieces') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('pieces*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-boxes w-5 text-center"></i><span class="ml-3 text-sm">Pièces de Rechange</span></a>
                <a href="{{ route('services.index') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('services*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-hospital w-5 text-center"></i><span class="ml-3 text-sm">Services</span></a>
                <a href="{{ route('external-companies.index') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('external-companies.*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-building w-5 text-center"></i><span class="ml-3 text-sm">Sociétés Externes</span></a>
                <a href="{{ route('sav-tickets.index') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('sav-tickets.*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-life-ring w-5 text-center"></i><span class="ml-3 text-sm">Tickets SAV Externes</span></a>
                <a href="{{ route('company-performance.index') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('company-performance.*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-chart-line w-5 text-center"></i><span class="ml-3 text-sm">Performance Sociétés</span></a>
                <a href="{{ route('rapports') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('rapports*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-chart-bar w-5 text-center"></i><span class="ml-3 text-sm">Rapports</span></a>
                <a href="{{ route('mttr-mtbf') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('mttr-mtbf*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-tachometer-alt w-5 text-center"></i><span class="ml-3 text-sm">KPI MTTR / MTBF</span></a>
                <a href="{{ route('planning.index') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('planning.*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-calendar-alt w-5 text-center"></i><span class="ml-3 text-sm">Planning Sociétés</span></a>
                <a href="{{ route('stock.movements') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('stock.*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-truck-loading w-5 text-center"></i><span class="ml-3 text-sm">Décharge & Réception</span></a>
                @if(!$isMajor)
                <a href="{{ route('parametres') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('parametres*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-cog w-5 text-center"></i><span class="ml-3 text-sm">Paramètres</span></a>
                @endif
            @endif

            @if($canAccessMarkets && !$isAdminLike)
                <a href="{{ route('markets.equipments') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('markets.equipments') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-file-signature w-5 text-center"></i><span class="ml-3 text-sm">Marchés & Équipements</span></a>
            @endif


            @if($canManageUsers)
                <div class="pt-3 mt-3 border-t border-white/10">
                    <p class="px-3 text-[10px] uppercase tracking-widest text-blue-300/60 font-semibold mb-2 gst-section-label">Administration</p>
                    @if($isAdmin)
                    <a href="{{ route('admin.security.index') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('admin.security.*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-user-shield w-5 text-center"></i><span class="ml-3 text-sm">Authentification & sécurité</span></a>
                    @endif
                    <a href="{{ route('admin.users.index') }}" @click="if (window.innerWidth < 1024) { mobileSidebarOpen = false }" class="gst-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }} flex items-center px-4 py-2.5 rounded-xl transition-all duration-200"><i class="fas fa-users-cog w-5 text-center"></i><span class="ml-3 text-sm">Gestion des utilisateurs</span></a>
                </div>
            @endif

        </nav>
    </div>

    <!-- Sidebar Footer -->
    <div class="mt-auto p-5 border-t border-white/5">
        <div class="flex items-center gap-2 text-[10px] text-blue-300/40">
            <i class="fas fa-heartbeat"></i>
            <span class="gst-footer-text">GST GMAO v2.0</span>
        </div>
    </div>
</aside>
