const CACHE_NAME = 'bhavesh-portfolio-v1';
const OFFLINE_URL = '/offline';

const PRECACHE_ASSETS = [
    '/',
    OFFLINE_URL,
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png'
];

// Install: Pre-cache shell and offline page
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// Activate: Clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch: Bypass /admin/* and handle caching
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // 1. Only handle GET requests and http/https schemes
    if (request.method !== 'GET' || !url.protocol.startsWith('http')) {
        return;
    }

    // 2. BYPASS ALL ADMIN ROUTES & AUTH ROUTES
    if (url.pathname.startsWith('/admin') || 
        url.pathname.startsWith('/login') || 
        url.pathname.startsWith('/register') || 
        url.pathname.startsWith('/logout')) {
        return; // Direct network fetch, no caching
    }

    // 3. Navigation requests (HTML pages)
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Update cache for successful navigation requests
                    if (response.status === 200) {
                        const copy = response.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, copy);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    // Offline fallback: try cache, then /offline page
                    return caches.match(request).then((cachedResponse) => {
                        return cachedResponse || caches.match(OFFLINE_URL);
                    });
                })
        );
        return;
    }

    // 4. Static assets (Vite build assets, fonts, icons, images)
    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                // Return cached version and update in background (Stale-While-Revalidate)
                fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, networkResponse));
                    }
                }).catch(() => {/* Ignore network errors on background refresh */});
                return cachedResponse;
            }

            return fetch(request).then((response) => {
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                const responseToCache = response.clone();
                caches.open(CACHE_NAME).then((cache) => {
                    cache.put(request, responseToCache);
                });
                return response;
            });
        })
    );
});
