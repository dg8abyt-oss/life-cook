const ICON = "https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553";

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SHOW_NOTIFICATION') {
        self.registration.showNotification(event.data.title, {
            body: event.data.body,
            icon: ICON,
            badge: ICON,
            vibrate: [200, 100, 200]
        });
    }
});
