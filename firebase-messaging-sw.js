importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyAyK9WfVuk84ipyVUEEZJPPvBE3C5TnLXY",
    messagingSenderId: "747296045983",
    appId: "1:747296045983:web:215127e502eca87eafdbaa",
    projectId: "lifecook-41e6d"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: 'https://ik.imagekit.io/migbb/image.jpeg?updatedAt=1770995065553'
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
