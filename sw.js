/**
 * LifeCook Service Worker
 */

const ICON = "https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553";

self.addEventListener('install', (e) => self.skipWaiting());
self.addEventListener('activate', (e) => e.waitUntil(clients.claim()));

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(windowClients => {
            if (windowClients.length > 0) return windowClients[0].focus();
            return clients.openWindow('/');
        })
    );
});
