/**
 * LifeCook Service Worker
 * Logic: System-Level Notification Bridge
 */

const ICON = "https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553";

// Ensure SW activates immediately
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

// The Brain: Handle direct NOTIFY messages from the app
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'NOTIFY') {
        const options = {
            body: event.data.body,
            icon: ICON,
            badge: ICON,
            vibrate: [300, 100, 300],
            tag: 'lifecook-alert',
            renotify: true,
            data: { url: '/' }
        };

        event.waitUntil(
            self.registration.showNotification(event.data.title, options)
        );
    }
});

// Click Handling
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            if (clientList.length > 0) {
                return clientList[0].focus();
            }
            return clients.openWindow('/');
        })
    );
});
