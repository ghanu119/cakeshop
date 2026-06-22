const SW_VERSION = '5';

function safeAdminUrl(rawUrl) {
    try {
        const fallback = '/admin/dashboard';
        if (!rawUrl || typeof rawUrl !== 'string') {
            return fallback;
        }

        const target = rawUrl.startsWith('/')
            ? new URL(rawUrl, self.location.origin)
            : new URL(rawUrl, self.location.origin);

        if (!target.pathname.startsWith('/admin')) {
            return fallback;
        }

        return `${target.pathname}${target.search}${target.hash}`;
    } catch (e) {
        return '/admin/dashboard';
    }
}

function parsePushPayload(event) {
    const fallback = { title: 'Cake Shop', body: '', data: {} };

    if (!event.data) {
        return fallback;
    }

    try {
        const parsed = event.data.json();

        return {
            title: parsed.title ?? fallback.title,
            body: parsed.body ?? '',
            data: parsed.data ?? {},
            icon: parsed.icon ?? '/favicon.ico',
            requireInteraction: parsed.requireInteraction ?? true,
        };
    } catch (e) {
        return {
            ...fallback,
            body: event.data.text() || '',
        };
    }
}

function buildMessagePayload(payload, title, url) {
    return {
        id: payload.data?.id ?? null,
        type: payload.data?.type ?? null,
        title,
        body: payload.body,
        message: payload.body,
        url,
        data: { url },
    };
}

self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
    const payload = parsePushPayload(event);
    const title = payload.title;
    const url = safeAdminUrl(payload.data?.url ?? payload.url);
    const messagePayload = buildMessagePayload(payload, title, url);
    const tag = payload.data?.id ? `staff-${payload.data.id}` : `staff-${SW_VERSION}`;

    const options = {
        body: payload.body,
        data: { url, id: payload.data?.id ?? null, type: payload.data?.type ?? null },
        icon: payload.icon ?? '/favicon.ico',
        badge: '/favicon.ico',
        silent: false,
        vibrate: [180, 80, 180],
        requireInteraction: payload.requireInteraction ?? true,
        tag,
        renotify: true,
    };

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            const hasVisibleClient = clients.some((client) => client.visibilityState === 'visible');

            if (hasVisibleClient) {
                clients.forEach((client) => {
                    client.postMessage({ type: 'staff-notification', payload: messagePayload });
                });
            }

            // Tab hidden/minimized: service worker shows the OS popup.
            // Tab visible: admin-notifications.js shows it via new Notification().
            if (!hasVisibleClient) {
                return self.registration.showNotification(title, options);
            }

            return undefined;
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = safeAdminUrl(event.notification.data?.url);
    const absoluteUrl = new URL(url, self.location.origin).href;

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if (!client.url.startsWith(self.location.origin)) {
                    continue;
                }

                if ('focus' in client) {
                    return client.focus().then((focusedClient) => {
                        if ('navigate' in focusedClient) {
                            return focusedClient.navigate(absoluteUrl);
                        }

                        return focusedClient;
                    });
                }
            }

            if (self.clients.openWindow) {
                return self.clients.openWindow(absoluteUrl);
            }

            return undefined;
        })
    );
});
