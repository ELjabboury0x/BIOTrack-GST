(function () {
    'use strict';

    var authMeta = document.querySelector('meta[name="pwa-authenticated"]');
    var vapidMeta = document.querySelector('meta[name="pwa-vapid-public-key"]');
    var subscribeUrlMeta = document.querySelector('meta[name="push-subscribe-url"]');
    var unsubscribeUrlMeta = document.querySelector('meta[name="push-unsubscribe-url"]');
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');

    var isAuthenticated = authMeta && authMeta.getAttribute('content') === '1';
    var vapidPublicKey = vapidMeta ? (vapidMeta.getAttribute('content') || '').trim() : '';
    var subscribeUrl = subscribeUrlMeta ? (subscribeUrlMeta.getAttribute('content') || '').trim() : '';
    var unsubscribeUrl = unsubscribeUrlMeta ? (unsubscribeUrlMeta.getAttribute('content') || '').trim() : '';
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    var isSecureContextLike = window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';

    if (!('serviceWorker' in navigator) || !isSecureContextLike) {
        return;
    }

    function urlBase64ToUint8Array(base64String) {
        var padding = '='.repeat((4 - base64String.length % 4) % 4);
        var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        var rawData = window.atob(base64);
        var outputArray = new Uint8Array(rawData.length);
        for (var i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    function postJson(url, payload, method) {
        return fetch(url, {
            method: method || 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload || {})
        });
    }

    function shouldPromptForPermission() {
        if (!isAuthenticated) {
            return false;
        }

        if (!('PushManager' in window) || !('Notification' in window)) {
            return false;
        }

        return Notification.permission === 'default';
    }

    function removeEnableButton() {
        var existing = document.getElementById('gst-push-enable-btn');
        if (existing && existing.parentNode) {
            existing.parentNode.removeChild(existing);
        }
    }

    function showEnableButton(onEnable) {
        if (!isAuthenticated || !('Notification' in window) || Notification.permission !== 'default') {
            removeEnableButton();
            return;
        }

        if (document.getElementById('gst-push-enable-btn')) {
            return;
        }

        var button = document.createElement('button');
        button.id = 'gst-push-enable-btn';
        button.type = 'button';
        button.textContent = 'Activer notifications';
        button.style.position = 'fixed';
        button.style.right = '16px';
        button.style.bottom = '16px';
        button.style.zIndex = '9999';
        button.style.padding = '10px 14px';
        button.style.border = '0';
        button.style.borderRadius = '10px';
        button.style.background = '#0284c7';
        button.style.color = '#ffffff';
        button.style.fontWeight = '600';
        button.style.boxShadow = '0 8px 20px rgba(2,132,199,0.35)';
        button.style.cursor = 'pointer';

        button.addEventListener('click', function () {
            removeEnableButton();

            Notification.requestPermission().then(function () {
                return onEnable();
            });
        });

        document.body.appendChild(button);
    }

    function syncSubscription(registration) {
        if (!isAuthenticated || !vapidPublicKey || !subscribeUrl) {
            return Promise.resolve();
        }

        return registration.pushManager.getSubscription().then(function (existingSubscription) {
            if (Notification.permission === 'denied') {
                if (existingSubscription && unsubscribeUrl) {
                    return postJson(unsubscribeUrl, { endpoint: existingSubscription.endpoint }, 'DELETE')
                        .then(function () { return existingSubscription.unsubscribe(); })
                        .catch(function () { return existingSubscription.unsubscribe(); });
                }
                return Promise.resolve();
            }

            if (Notification.permission !== 'granted') {
                return Promise.resolve();
            }

            if (existingSubscription) {
                return postJson(subscribeUrl, existingSubscription.toJSON(), 'POST');
            }

            return registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
            }).then(function (newSubscription) {
                return postJson(subscribeUrl, newSubscription.toJSON(), 'POST');
            });
        }).catch(function (error) {
            console.warn('PWA push sync failed:', error);
        });
    }

    window.addEventListener('load', function () {
        if (!isSecureContextLike) {
            console.warn('Push notifications require HTTPS on mobile devices (except localhost).');
        }

        navigator.serviceWorker.register('/sw.js?v=20260224b').then(function (registration) {
            if (shouldPromptForPermission()) {
                showEnableButton(function () {
                    return syncSubscription(registration);
                });
            }

            syncSubscription(registration);
        }).catch(function (error) {
            console.warn('Service worker registration failed:', error);
        });

        if (navigator.serviceWorker) {
            navigator.serviceWorker.addEventListener('message', function (event) {
                var data = event && event.data ? event.data : null;
                if (!data || data.type !== 'OPEN_NOTIFICATION_URL' || !data.url) {
                    return;
                }

                try {
                    var target = new URL(data.url, window.location.origin);
                    if (target.origin !== window.location.origin) {
                        return;
                    }

                    var current = window.location.pathname + window.location.search + window.location.hash;
                    var next = target.pathname + target.search + target.hash;
                    if (current !== next) {
                        window.location.assign(next);
                    }
                } catch (e) {
                    console.warn('Unable to open notification target URL:', e);
                }
            });
        }
    });
})();
