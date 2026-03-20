<nav class="navbar fixed w-full top-0 z-50 transition-all duration-300" id="navbar">
    <div class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
            <!-- Logo Section -->
            <div class="flex items-center gap-8">
                <!-- GST Logo -->
                <a href="/" class="flex items-center gap-2 group">
                    <img src="{{ asset('icons/icon-512x512-logo-only.png') }}?v={{ filemtime(public_path('icons/icon-512x512-logo-only.png')) }}" alt="BioTrack GST" class="h-10 sm:h-11 w-auto object-contain drop-shadow-sm group-hover:scale-[1.02] transition-transform duration-300">
                    <span class="font-bold text-gray-800 hidden sm:inline-block">BioTrack GST</span>
                </a>
            </div>

            <!-- Navigation Menu -->
            <div class="hidden md:flex items-center gap-12">
                <a href="/" class="nav-link text-gray-700 hover:text-blue-600 font-medium transition-colors duration-300 relative">
                    Accueil
                </a>
                <a href="#about" class="nav-link text-gray-700 hover:text-blue-600 font-medium transition-colors duration-300 relative">
                    À propos
                </a>
                <a href="#features" class="nav-link text-gray-700 hover:text-blue-600 font-medium transition-colors duration-300 relative">
                    Fonctionnalités
                </a>
                <a href="#contact" class="nav-link text-gray-700 hover:text-blue-600 font-medium transition-colors duration-300 relative">
                    Contact
                </a>
            </div>

            <!-- CTA & Mobile Menu Button -->
            <div class="flex items-center gap-4">
                <button type="button"
                        onclick="if (window.GSTDarkMode) { GSTDarkMode.toggle(); }"
                        class="gst-dark-toggle gst-dark-toggle-switch transition-all duration-300"
                        title="Mode sombre">
                    <i class="fas fa-moon dark-icon"></i>
                    <i class="fas fa-sun light-icon"></i>
                </button>

                <a href="/login" class="hidden sm:block px-6 py-2 rounded-full font-semibold text-blue-600 border-2 border-blue-600 hover:bg-blue-600 hover:text-white transition-all duration-300">
                    Connexion
                </a>

                <!-- Mobile Menu Toggle -->
                <button id="mobile-menu-btn" class="md:hidden text-gray-700 hover:text-blue-600 transition-colors duration-300">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden mt-4 bg-white rounded-xl shadow-lg p-4">
            <button type="button"
                    onclick="if (window.GSTDarkMode) { GSTDarkMode.toggle(); }"
                    class="gst-dark-toggle gst-dark-toggle-inline w-full mb-3 py-2 px-4 text-left text-gray-700 hover:bg-blue-50 rounded-lg transition-colors duration-300 flex items-center gap-2">
                <i class="fas fa-moon dark-icon"></i>
                <i class="fas fa-sun light-icon"></i>
                <span>Mode sombre</span>
            </button>
            <a href="/" class="block py-2 px-4 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors duration-300">Accueil</a>
            <a href="#about" class="block py-2 px-4 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors duration-300">À propos</a>
            <a href="#features" class="block py-2 px-4 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors duration-300">Fonctionnalités</a>
            <a href="#contact" class="block py-2 px-4 text-gray-700 hover:bg-blue-50 rounded-lg transition-colors duration-300">Contact</a>
            <a href="/login" class="block mt-4 py-2 px-4 text-center bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors duration-300">Connexion</a>
        </div>
    </div>
</nav>

<style>
    .navbar {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(219, 234, 254, 0.5);
    }

    .navbar.scrolled {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .nav-link {
        position: relative;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: -4px;
        left: 0;
        width: 0;
        height: 2px;
        background: linear-gradient(to right, #3b82f6, #2563eb);
        transition: width 0.3s ease;
    }

    .nav-link:hover::after {
        width: 100%;
    }
</style>

