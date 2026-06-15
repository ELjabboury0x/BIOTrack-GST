/* =============================================================
   GMAO GST – Modern UI JavaScript
   Toast, Dark Mode, Page Transitions, Ripple, SweetAlert2
   ============================================================= */

(function () {
    'use strict';

    /* ─── STORAGE ERROR PROTECTION ─── */
    // Global storage wrapper to prevent FILE_ERROR_NO_SPACE crashes
    window.SafeStorage = {
        getItem: function(key) {
            try {
                return localStorage.getItem(key);
            } catch(e) {
                if (e.message && e.message.includes('NO_SPACE')) {
                    console.warn('LocalStorage quota exceeded, clearing cache');
                    try {
                        localStorage.clear();
                    } catch(e2) {
                        console.error('Failed to clear localStorage:', e2);
                    }
                }
                return null;
            }
        },
        setItem: function(key, value) {
            try {
                localStorage.setItem(key, value);
                return true;
            } catch(e) {
                if (e.message && e.message.includes('NO_SPACE')) {
                    console.warn('LocalStorage quota exceeded for key:', key);
                    // Don't fail silently - try to clear old data
                    try {
                        // Remove least important items
                        var keysToRemove = [];
                        for (var i = 0; i < localStorage.length; i++) {
                            var k = localStorage.key(i);
                            if (k && (k.includes('cache') || k.includes('temp') || k.includes('old'))) {
                                keysToRemove.push(k);
                            }
                        }
                        keysToRemove.forEach(function(k) { 
                            try { localStorage.removeItem(k); } catch(e) {} 
                        });
                        // Try again
                        localStorage.setItem(key, value);
                        return true;
                    } catch(e2) {
                        console.warn('Could not store in localStorage:', e2.message);
                        return false;
                    }
                }
                return false;
            }
        },
        removeItem: function(key) {
            try {
                localStorage.removeItem(key);
            } catch(e) {
                console.warn('Could not remove from localStorage:', e);
            }
        }
    };

    /* ─── PAGE LOADER ─── */
    var loaderDismissed = false;

    var isAlpineLayoutReady = function () {
        return !!(document.body && document.body._x_dataStack && document.body._x_dataStack.length > 0);
    };

    var shouldWaitForAlpineLayout = function () {
        return !!(document.body && document.body.hasAttribute('x-data'));
    };

    var dismissPageLoader = function () {
        if (loaderDismissed) {
            return;
        }

        loaderDismissed = true;

        var loader = document.getElementById('gst-page-loader');
        if (loader) {
            loader.classList.add('loaded');
        }

        // Add entrance class to main content
        var main = document.querySelector('main');
        var disableLoader = document.body && document.body.getAttribute('data-disable-page-loader') === '1';
        if (!disableLoader && main && !main.classList.contains('gst-page-enter')) {
            main.classList.add('gst-page-enter');
        }
    };

    var tryDismissPageLoader = function (force) {
        if (loaderDismissed) {
            return;
        }

        if (force) {
            dismissPageLoader();
            return;
        }

        if (shouldWaitForAlpineLayout() && !isAlpineLayoutReady()) {
            return;
        }

        dismissPageLoader();
    };

    // Dismiss once DOM is ready AND Alpine has initialized dashboard layout state.
    if (document.readyState === 'interactive' || document.readyState === 'complete') {
        tryDismissPageLoader(false);
    } else {
        document.addEventListener('DOMContentLoaded', function () {
            tryDismissPageLoader(false);
        }, { once: true });
    }

    document.addEventListener('alpine:initialized', function () {
        tryDismissPageLoader(false);
    }, { once: true });

    // Keep load/pageshow and timeout as safety nets.
    window.addEventListener('load', function () {
        tryDismissPageLoader(true);
    }, { once: true });
    window.addEventListener('pageshow', function () {
        tryDismissPageLoader(true);
    });
    setTimeout(function () {
        tryDismissPageLoader(true);
    }, 1800);

    /* ─── DARK MODE ─── */
    window.GSTDarkMode = {
        key: 'gst-theme',

        init: function () {
            try {
                var saved = window.SafeStorage.getItem(this.key);
                if (saved === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            } catch(e) {
                console.warn('Dark mode init error:', e);
            }
        },

        toggle: function () {
            try {
                var current = document.documentElement.getAttribute('data-theme');
                var next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                window.SafeStorage.setItem(this.key, next);

                // Animate the toggle
                var toggles = document.querySelectorAll('.gst-dark-toggle');
                toggles.forEach(function (t) {
                    t.classList.add('gst-toggle-anim');
                    setTimeout(function () { t.classList.remove('gst-toggle-anim'); }, 300);
                });
            } catch(e) {
                console.warn('Dark mode toggle error:', e);
            }
        },

        isDark: function () {
            return document.documentElement.getAttribute('data-theme') === 'dark';
        }
    };

    // Initialize dark mode immediately
    try {
        GSTDarkMode.init();
    } catch(e) {
        console.warn('Failed to initialize dark mode:', e);
    }

    /* ─── TOAST NOTIFICATION SYSTEM ─── */
    window.GSTToast = {
        container: null,

        ensureContainer: function () {
            if (!this.container) {
                this.container = document.getElementById('gst-toast-container');
                if (!this.container) {
                    this.container = document.createElement('div');
                    this.container.id = 'gst-toast-container';
                    document.body.appendChild(this.container);
                }
            }
            return this.container;
        },

        show: function (options) {
            var defaults = {
                type: 'info',        // success | error | warning | info
                title: '',
                message: '',
                duration: 4000,      // ms, 0 = persistent
                icon: null           // auto-detected if null
            };
            var opts = Object.assign({}, defaults, options);

            var icons = {
                success: 'fas fa-check-circle',
                error: 'fas fa-times-circle',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info-circle'
            };
            var iconClass = opts.icon || icons[opts.type] || icons.info;

            var container = this.ensureContainer();

            var toast = document.createElement('div');
            toast.className = 'gst-toast ' + opts.type;
            toast.innerHTML =
                '<div class="gst-toast-icon"><i class="' + iconClass + '"></i></div>' +
                '<div class="gst-toast-body">' +
                    (opts.title ? '<div class="gst-toast-title">' + opts.title + '</div>' : '') +
                    (opts.message ? '<div class="gst-toast-message">' + opts.message + '</div>' : '') +
                '</div>' +
                '<button class="gst-toast-close" onclick="GSTToast.dismiss(this.parentElement)"><i class="fas fa-times"></i></button>' +
                (opts.duration > 0 ? '<div class="gst-toast-progress" style="animation-duration:' + opts.duration + 'ms"></div>' : '');

            container.appendChild(toast);

            if (opts.duration > 0) {
                setTimeout(function () {
                    GSTToast.dismiss(toast);
                }, opts.duration);
            }

            return toast;
        },

        dismiss: function (toastEl) {
            if (!toastEl || toastEl.classList.contains('gst-toast-exit')) return;
            toastEl.classList.add('gst-toast-exit');
            setTimeout(function () {
                if (toastEl.parentNode) {
                    toastEl.parentNode.removeChild(toastEl);
                }
            }, 350);
        },

        success: function (title, message, duration) {
            return this.show({ type: 'success', title: title, message: message || '', duration: duration || 4000 });
        },

        error: function (title, message, duration) {
            return this.show({ type: 'error', title: title, message: message || '', duration: duration || 5000 });
        },

        warning: function (title, message, duration) {
            return this.show({ type: 'warning', title: title, message: message || '', duration: duration || 4500 });
        },

        info: function (title, message, duration) {
            return this.show({ type: 'info', title: title, message: message || '', duration: duration || 4000 });
        }
    };

    /* ─── SWEETALERT2 HELPERS ─── */
    window.GSTAlert = {
        // Confirm delete action
        confirmDelete: function (options) {
            var defaults = {
                title: 'Confirmer la suppression',
                text: 'Cette action est irréversible. Voulez-vous continuer ?',
                confirmText: 'Oui, supprimer',
                cancelText: 'Annuler',
                onConfirm: null
            };
            var opts = Object.assign({}, defaults, options);

            if (typeof Swal === 'undefined') {
                if (confirm(opts.text)) {
                    if (opts.onConfirm) opts.onConfirm();
                }
                return;
            }

            return Swal.fire({
                title: opts.title,
                text: opts.text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-trash mr-2"></i>' + opts.confirmText,
                cancelButtonText: opts.cancelText,
                customClass: {
                    popup: 'rounded-xl shadow-2xl',
                    confirmButton: 'rounded-lg px-6 py-2.5',
                    cancelButton: 'rounded-lg px-6 py-2.5'
                },
                showClass: {
                    popup: 'animate__animated animate__zoomIn animate__faster'
                },
                hideClass: {
                    popup: 'animate__animated animate__zoomOut animate__faster'
                }
            }).then(function (result) {
                if (result.isConfirmed && opts.onConfirm) {
                    opts.onConfirm();
                }
            });
        },

        // Success celebration
        success: function (title, text) {
            if (typeof Swal === 'undefined') {
                GSTToast.success(title, text);
                return;
            }

            return Swal.fire({
                title: title || 'Succès !',
                text: text || '',
                icon: 'success',
                timer: 2500,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'rounded-xl shadow-2xl'
                },
                showClass: {
                    popup: 'animate__animated animate__bounceIn'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp animate__faster'
                }
            });
        },

        // Error
        error: function (title, text) {
            if (typeof Swal === 'undefined') {
                GSTToast.error(title, text);
                return;
            }

            return Swal.fire({
                title: title || 'Erreur',
                text: text || '',
                icon: 'error',
                confirmButtonColor: '#3b82f6',
                customClass: {
                    popup: 'rounded-xl shadow-2xl',
                    confirmButton: 'rounded-lg px-6 py-2.5'
                }
            });
        }
    };

    /* ─── RIPPLE EFFECT ─── */
    document.addEventListener('click', function (e) {
        var target = e.target.closest('.gst-ripple, button[class*="bg-blue"], button[class*="bg-green"], a[class*="bg-blue"], a[class*="bg-green"]');
        if (!target) return;

        var rect = target.getBoundingClientRect();
        var size = Math.max(rect.width, rect.height);
        var x = e.clientX - rect.left - size / 2;
        var y = e.clientY - rect.top - size / 2;

        var ripple = document.createElement('span');
        ripple.className = 'gst-ripple-effect';
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';

        // Ensure parent is positioned
        var pos = getComputedStyle(target).position;
        if (pos === 'static') target.style.position = 'relative';
        if (getComputedStyle(target).overflow !== 'hidden') target.style.overflow = 'hidden';

        target.appendChild(ripple);

        setTimeout(function () {
            if (ripple.parentNode) ripple.parentNode.removeChild(ripple);
        }, 600);
    });

    /* ─── NOTIFICATION BELL ANIMATION ─── */
    document.addEventListener('DOMContentLoaded', function () {
        // Periodically animate bell when there are notifications
        setInterval(function () {
            var badge = document.querySelector('[class*="-top-1"][class*="-right-1"][class*="bg-red"]');
            if (badge) {
                var bell = badge.closest('.relative').querySelector('.fa-bell');
                if (bell) {
                    bell.parentElement.classList.add('gst-bell-shake');
                    setTimeout(function () {
                        bell.parentElement.classList.remove('gst-bell-shake');
                    }, 600);
                }
            }
        }, 8000);
    });

    /* ─── CONFETTI BURST ─── */
    window.GSTConfetti = function (count) {
        count = count || 30;
        var colors = ['#3b82f6', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
        for (var i = 0; i < count; i++) {
            var dot = document.createElement('div');
            dot.className = 'gst-confetti';
            dot.style.background = colors[Math.floor(Math.random() * colors.length)];
            dot.style.left = (20 + Math.random() * 60) + '%';
            dot.style.setProperty('--x', (Math.random() * 200 - 100) + 'px');
            dot.style.animationDuration = (1 + Math.random()) + 's';
            dot.style.animationDelay = (Math.random() * 0.3) + 's';
            dot.style.width = (4 + Math.random() * 6) + 'px';
            dot.style.height = dot.style.width;
            dot.style.borderRadius = Math.random() > 0.5 ? '50%' : '2px';
            document.body.appendChild(dot);
            (function (el) {
                setTimeout(function () {
                    if (el.parentNode) el.parentNode.removeChild(el);
                }, 2000);
            })(dot);
        }
    };

    /* ─── AUTO-DETECT LARAVEL SESSION FLASH & SHOW TOAST ─── */
    document.addEventListener('DOMContentLoaded', function () {
        // Find session flash messages injected as data attributes
        var flashEl = document.getElementById('gst-flash-data');
        if (flashEl) {
            var success = flashEl.getAttribute('data-success');
            var error = flashEl.getAttribute('data-error');
            var warning = flashEl.getAttribute('data-warning');
            var info = flashEl.getAttribute('data-info');

            if (success) GSTToast.success('Succès', success);
            if (error) GSTToast.error('Erreur', error);
            if (warning) GSTToast.warning('Attention', warning);
            if (info) GSTToast.info('Information', info);
        }
    });

    /* ─── SMOOTH SCROLL ─── */
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            var href = (this.getAttribute('href') || '').trim();
            if (!href.startsWith('#') || href === '#') {
                return;
            }

            var target = document.getElementById(href.slice(1)) || document.querySelector(href);
            if (!target) {
                return;
            }

            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

})();
