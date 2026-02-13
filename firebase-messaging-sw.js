/**
 * LifeCook Background Messaging Protocol
 */

importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

// USE YOUR FIREBASE CONFIG
firebase.initializeApp({
    apiKey: "AIzaSyAyK9WfVuk84ipyVUEEZJPPvBE3C5TnLXY",
    messagingSenderId: "747296045983",
    appId: "1:747296045983:web:215127e502eca87eafdbaa",
    projectId: "lifecook-41e6d"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    console.log('[LifeCook SW] Background Message Received');
    
    const notificationTitle = payload.notification.title || 'LifeCook Alert';
    const notificationOptions = {
        body: payload.notification.body || 'Food is done!',
        icon: 'https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553',
        badge: 'https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553',
        vibrate: [300, 100, 300],
        tag: 'lifecook-food-alert',
        renotify: true
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
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
