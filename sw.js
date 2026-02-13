const ICON = "https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553";

self.addEventListener('push', function(event) {
    const data = event.data ? event.data.json() : {};
    event.waitUntil(self.registration.showNotification(data.title || "LifeCook", {
        body: data.body || "Food is done!",
        icon: ICON
    }));
});
