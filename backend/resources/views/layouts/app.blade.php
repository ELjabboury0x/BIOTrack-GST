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
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/custom.css">
    <link rel="stylesheet" href="/css/modern-ui.css">

    <script>
        (function(){
            if(localStorage.getItem('gst-theme')==='dark'){
                document.documentElement.setAttribute('data-theme','dark');
            }
        })();
    </script>
    
    @yield('styles')
</head>
<body class="antialiased" style="font-family: 'Inter', system-ui, sans-serif;">
    @include('components.navbar')
    
    <main>
        @yield('content')
    </main>
    
    @include('components.footer')
    
    <!-- Scripts -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="/js/app.js?v=20260215"></script>
    <script src="/js/modern-ui.js"></script>
    <script src="{{ asset('js/pwa-push.js') }}?v={{ filemtime(public_path('js/pwa-push.js')) }}" defer></script>
    
    @yield('scripts')
</body>
</html>
