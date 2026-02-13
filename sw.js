/**
 * LifeCook Service Worker
 * Handles background notifications
 */

const CACHE_NAME = 'lifecook-v1';
const ASSETS = [
    '/',
    '/manifest.json'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS))
    );
});

self.addEventListener('fetch', (event) => {
    event.respondWith(
        caches.match(event.request).then((response) => {
            return response || fetch(event.request);
        })
    );
});

self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    const title = data.title || "LifeCook Update";
    const options = {
        body: data.body || "A meal is ready in the kitchen!",
        icon: "https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553",
        badge: "https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553",
        vibrate: [200, 100, 200]
    };
    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow('/')
    );
});
