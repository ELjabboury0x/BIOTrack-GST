<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1d4ed8">
    <meta name="pwa-authenticated" content="{{ auth()->check() ? '1' : '0' }}">
    <meta name="pwa-vapid-public-key" content="{{ config('webpush.vapid.public_key', '') }}">
    <meta name="push-subscribe-url" content="{{ route('push-subscriptions.store') }}">
    <meta name="push-unsubscribe-url" content="{{ route('push-subscriptions.destroy') }}">
    <title>@yield('title', 'GMAO - GST Tanger') | Système de Gestion de Maintenance</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png?v={{ filemtime(public_path('icons/favicon-32x32.png')) }}">
    <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png?v={{ filemtime(public_path('icons/apple-touch-icon.png')) }}">
    <link rel="manifest" href="/manifest.webmanifest">
    
    <!-- Google Fonts – Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Core Libraries -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

    <!-- Animation & Alert Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @php($useViteDashboardAssets = file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    <!-- App Styles -->
    @if($useViteDashboardAssets)
        @vite(['public/css/dashboard.css', 'public/css/modern-ui.css'])
    @else
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/modern-ui.css') }}?v={{ filemtime(public_path('css/modern-ui.css')) }}">
    @endif
    
    <!-- Alpine.js cloak style -->
    <style>[x-cloak] { display: none !important; }</style>

    <!-- Apply saved dark mode immediately to prevent flash -->
    <script>
        (function(){
            if(localStorage.getItem('gst-theme')==='dark'){
                document.documentElement.setAttribute('data-theme','dark');
            }
        })();
    </script>
    
    @yield('styles')
</head>
<body x-data="{ mobileSidebarOpen: false, sidebarHovered: false, sidebarCollapsed: true }" class="antialiased" style="font-family: 'Inter', system-ui, sans-serif;">
        @if(auth()->user()?->role === 'major')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wsUrl = @json((request()->isSecure() ? 'wss' : 'ws') . '://' . request()->getHost() . ':' . (int) env('REALTIME_PORT', 6001) . '/ws');
            if (!wsUrl) {
                return;
            }

            let socket = null;
            let socketConnected = false;
            let reconnectDelay = 3000;
            let lastRefreshAt = 0;

            const isDashboardPage = !!document.getElementById('dashboard-live-config');

            const refreshMajorView = function () {
                if (isDashboardPage || document.hidden) {
                    return;
                }

                const now = Date.now();
                if (now - lastRefreshAt < 3000) {
                    return;
                }

                lastRefreshAt = now;
                window.location.reload();
            };

            const startRealtime = function () {
                try {
                    socket = new WebSocket(wsUrl);

                    socket.onopen = function () {
                        socketConnected = true;
                    };

                    socket.onmessage = function (event) {
                        try {
                            const message = JSON.parse(event.data || '{}');
                            const channel = String(message.channel || '');

                            if (channel === 'gmao.changed' || channel === 'complaint.created' || channel === 'dashboard.metrics') {
                                refreshMajorView();
                            }
                        } catch (error) {
                            console.error('Major realtime listener error:', error);
                        }
                    };

                    socket.onclose = function () {
                        socketConnected = false;
                        setTimeout(startRealtime, reconnectDelay);
                    };

                    socket.onerror = function () {
                        socketConnected = false;
                    };
                } catch (error) {
                    socketConnected = false;
                }
            };

            startRealtime();

            // Fallback polling when websocket is unavailable.
            setInterval(function () {
                if (!socketConnected) {
                    refreshMajorView();
                }
            }, 30000);
        });
        </script>
        @endif

    <!-- Page Loader -->
    <div id="gst-page-loader">
        <div class="gst-spinner"></div>
    </div>

    <!-- Toast Notification Container -->
    <div id="gst-toast-container"></div>

    <!-- Flash Data Bridge (read by modern-ui.js) -->
    <div id="gst-flash-data" style="display:none"
         data-success="{{ session('success') }}"
         data-error="{{ session('error') }}"
         data-warning="{{ session('warning') }}"
         data-info="{{ session('info') }}"></div>

    <div class="flex min-h-screen h-[100dvh] overflow-hidden relative">
                     <div x-show="mobileSidebarOpen"
             x-transition.opacity
                             @click="mobileSidebarOpen = false"
             class="fixed inset-0 bg-black/40 z-30 lg:hidden backdrop-blur-sm"
             style="display: none;"></div>

        <!-- Sidebar -->
        @include('components.sidebar-dashboard')
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Navbar -->
            @include('components.navbar-dashboard')
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="p-4 sm:p-6 lg:p-8">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Modals -->
    @include('components.modal-add-record')
    @include('components.modal-import-excel')
    
    <!-- Scripts -->
    @if($useViteDashboardAssets)
        @vite(['public/js/modern-ui.js', 'public/js/dashboard.js', 'public/js/table.js', 'public/js/charts.js', 'public/js/pwa-push.js'])
    @else
        <script src="{{ asset('js/pwa-push.js') }}?v={{ filemtime(public_path('js/pwa-push.js')) }}" defer></script>
        <script src="/js/modern-ui.js"></script>
        <script src="/js/dashboard.js" defer></script>
        <script src="/js/table.js" defer></script>
        <script src="/js/charts.js" defer></script>
    @endif
    
    @yield('scripts')
</body>
</html>
