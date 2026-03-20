self.addEventListener('install', function (event) {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', function (event) {
    if (!event.data) {
        return;
    }

    var payload = {};
    try {
        payload = event.data.json();
    } catch (error) {
        payload = {
            title: 'Notification GMAO',
            body: event.data.text()
        };
    }

    var title = payload.title || 'Notification GMAO';
    var options = {
        body: payload.body || '',
        icon: payload.icon || '/favicon.svg',
        badge: payload.badge || '/favicon.svg',
        tag: payload.tag || 'gmao-notification',
        data: payload.data || {}
    };

    if (payload.url) {
        options.data = options.data || {};
        options.data.url = payload.url;
    }

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    var targetUrl = '/dashboard?source=pwa';
    var appRoot = '/dashboard?source=pwa';
    if (event.notification && event.notification.data && event.notification.data.url) {
        targetUrl = event.notification.data.url;
    }

    var targetPath = '/dashboard?source=pwa';
    try {
        var parsed = new URL(targetUrl, self.location.origin);
        if (parsed.origin === self.location.origin) {
            targetPath = parsed.pathname + parsed.search + parsed.hash;
        }
    } catch (error) {
        targetPath = '/dashboard?source=pwa';
    }

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
            var pwaClient = null;
            var anySameOriginClient = null;

            for (var i = 0; i < clientList.length; i++) {
                var client = clientList[i];

                if (client.url.indexOf(self.location.origin) === 0) {
                    anySameOriginClient = anySameOriginClient || client;
                    if (client.url.indexOf('source=pwa') !== -1) {
                        pwaClient = client;
                    }
                }
            }

            var preferredClient = pwaClient || anySameOriginClient;

            if (preferredClient && 'focus' in preferredClient) {
                return preferredClient.focus().then(function () {
                    if ('postMessage' in preferredClient) {
                        preferredClient.postMessage({
                            type: 'OPEN_NOTIFICATION_URL',
                            url: targetPath
                        });
                    }

                    if ('navigate' in preferredClient && preferredClient.url.indexOf(self.location.origin + appRoot) !== 0) {
                        return preferredClient.navigate(appRoot);
                    }

                    return null;
                });
            }

            if (clients.openWindow) {
                return clients.openWindow(appRoot);
            }

            return null;
        }).then(function () {
            return clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
                for (var i = 0; i < clientList.length; i++) {
                    var client = clientList[i];
                    if (client.url.indexOf(self.location.origin) === 0 && 'postMessage' in client) {
                        client.postMessage({
                            type: 'OPEN_NOTIFICATION_URL',
                            url: targetPath
                        });
                    }
                }
            });
        })
    );
});
