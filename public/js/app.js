/* ============ GMAO Application JS ============ */

class GMAOApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupScrollAnimations();
        this.setupNavbarInteractions();
        this.setupFormValidation();
        this.setupCounterAnimation();
        this.setupSmoothScroll();
        this.setupObserverPatterns();
    }

    /**
     * Initialize AOS (Animate On Scroll)
     */
    setupScrollAnimations() {
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 1000,
                offset: 100,
                easing: 'ease-out-cubic',
                once: false,
                mirror: false,
            });
        }
    }

    /**
     * Setup Navbar interactions
     */
    setupNavbarInteractions() {
        const navbar = document.getElementById('navbar');
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');

        if (!navbar) return;

        // Scroll effect
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Close mobile menu when clicking on link
        if (mobileMenu) {
            mobileMenu.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    mobileMenu.classList.add('hidden');
                });
            });
        }

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (mobileMenu && !mobileMenu.contains(e.target) && e.target !== mobileMenuBtn) {
                mobileMenu.classList.add('hidden');
            }
        });
    }

    /**
     * Setup form validation and submission
     */
    setupFormValidation() {
        const forms = document.querySelectorAll('form');

        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                const action = (form.getAttribute('action') || '').trim();
                const isRealBackendForm = action !== '' && action !== '#';

                if (isRealBackendForm) {
                    return;
                }

                e.preventDefault();

                // Validate form
                if (!this.validateForm(form)) {
                    this.showNotification('Veuillez remplir tous les champs correctement', 'error');
                    return;
                }

                // Show loading state
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner animate-spin mr-2"></i> Envoi...';
                submitBtn.disabled = true;

                // Simulate form submission
                setTimeout(() => {
                    this.showNotification('Message envoyé avec succès !', 'success');
                    form.reset();
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 1500);
            });
        });
    }

    /**
     * Validate form inputs
     */
    validateForm(form) {
        const inputs = form.querySelectorAll('input[required], textarea[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('border-red-500');
            } else {
                input.classList.remove('border-red-500');
            }

            // Email validation
            if (input.type === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value)) {
                    isValid = false;
                    input.classList.add('border-red-500');
                }
            }
        });

        return isValid;
    }

    /**
     * Show notification toast
     */
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg text-white font-semibold shadow-lg z-50 animate-fade-in ${
            type === 'success' ? 'bg-green-500' :
            type === 'error' ? 'bg-red-500' :
            'bg-blue-500'
        }`;
        notification.innerHTML = `
            <div class="flex items-center gap-2">
                <i class="fas ${
                    type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-circle' :
                    'fa-info-circle'
                }"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'fadeOut 0.3s ease-out forwards';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    /**
     * Counter animation for statistics
     */
    setupCounterAnimation() {
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.counter');
                    counters.forEach(counter => {
                        this.animateCounter(counter);
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        const statsSections = document.querySelectorAll('[class*="from-blue-600"]');
        statsSections.forEach(section => {
            observer.observe(section);
        });
    }

    /**
     * Animate counter numbers
     */
    animateCounter(counter) {
        const target = counter.dataset.target;
        const finalValue = parseFloat(target);
        const isPercentage = target.includes('%');
        const isSpecial = target.includes('+') || target.includes('/');

        if (isSpecial) {
            counter.textContent = target;
            return;
        }

        let current = 0;
        const increment = finalValue / 30;
        const timer = setInterval(() => {
            current += increment;
            if (current >= finalValue) {
                counter.textContent = target;
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current) + (isPercentage ? '%' : '');
            }
        }, 20);
    }

    /**
     * Smooth scrolling for anchor links
     */
    setupSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                const href = (anchor.getAttribute('href') || '').trim();
                if (!href.startsWith('#') || href === '#') {
                    return;
                }

                const element = document.getElementById(href.slice(1)) || document.querySelector(href);
                if (!element) {
                    return;
                }

                e.preventDefault();
                const offsetTop = element.offsetTop - 100;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            });
        });
    }

    /**
     * Setup intersection observers for various effects
     */
    setupObserverPatterns() {
        // Parallax effect for sections
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('aos-animate');
                }
            });
        }, observerOptions);

        document.querySelectorAll('[data-aos]').forEach(el => {
            observer.observe(el);
        });
    }

    /**
     * Utility: Copy to clipboard
     */
    static copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            const toast = document.createElement('div');
            toast.textContent = 'Copié !';
            toast.className = 'fixed bottom-4 left-4 bg-green-500 text-white px-4 py-2 rounded';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
        });
    }
}

/* ============ Initialize App on DOM Ready ============ */
document.addEventListener('DOMContentLoaded', () => {
    window.gmaoApp = new GMAOApp();

    // Add keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Ctrl/Cmd + K for focus on search (if search is added later)
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            // TODO: Focus search input
        }
    });
});

/* ============ Service Worker Registration (PWA) ============ */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // Service worker registration failed, not required
        });
    });
}

/* ============ Performance Monitoring ============ */
if ('PerformanceObserver' in window) {
    try {
        const observer = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
                console.log(`${entry.name}: ${entry.duration.toFixed(2)}ms`);
            }
        });
        observer.observe({ entryTypes: ['measure', 'navigation'] });
    } catch (e) {
        // Performance monitoring not available
    }
}

/* ============ Add smooth fade-out animation ============ */
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(20px);
        }
    }
`;
document.head.appendChild(style);
