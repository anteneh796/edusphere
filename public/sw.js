const CACHE_NAME = 'edusphere-v1';
const APP_SHELL = [
    '/',
    '/manifest.json',
    '/offline.html',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => cache.addAll(APP_SHELL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);

    if (url.origin !== location.origin) {
        return;
    }

    // Never cache admin/API mutated resources.
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/up')) {
        return;
    }

    // Network-first for navigation requests, but serve a primed page (e.g. the
    // cached attendance board) first, then fall back to the cached app shell.
    if (request.mode === 'navigate') {
        event.respondWith(
            caches.match(request).then((cached) => cached || fetch(request)
                .then((response) => {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request.url, copy));
                    caches.open(CACHE_NAME).then((cache) => cache.put('/', response.clone()));
                    return response;
                })
                .catch(() => caches.match('/').then((shell) => shell || caches.match('/offline.html'))))
        );
        return;
    }

    // Cache-first for static assets (Vite hashed build files).
    event.respondWith(
        caches.match(request).then((cached) => {
            if (cached) {
                event.waitUntil(
                    fetch(request).then((network) => {
                        if (network.ok) {
                            const copy = network.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                        }
                    }).catch(() => {})
                );
                return cached;
            }

            return fetch(request).then((response) => {
                if (response.ok) {
                    const copy = response.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
                }
                return response;
            });
        })
    );
});

self.addEventListener('message', (event) => {
    if (event.data === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});