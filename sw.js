const ICON = "https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553";

// Listen for messages from the main app
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SHOW_NOTIFICATION') {
        const options = {
            body: event.data.body,
            icon: ICON,
            badge: ICON,
            vibrate: [200, 100, 200],
            tag: 'lifecook-alert', // Prevents duplicate notifications
            renotify: true
        };
        
        self.registration.showNotification(event.data.title, options);
    }
});

// Standard Push listener (if you ever use remote push)
self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    event.waitUntil(
        self.registration.showNotification(data.title || "LifeCook", {
            body: data.body || "Food is done!",
            icon: ICON
        })
    );
});
