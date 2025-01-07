// Give the service worker access to Firebase Messaging.
// Note that you can only use Firebase Messaging here.
importScripts('https://www.gstatic.com/firebasejs/11.1.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/11.1.0/firebase-messaging-compat.js');

// Initialize the Firebase app in the service worker
firebase.initializeApp({
    apiKey: "AIzaSyDDfPtr3fUr556ItfKpO2TVkkUghQ1LwfM",
    authDomain: "antarkanma-bbafa.firebaseapp.com",
    projectId: "antarkanma-bbafa",
    storageBucket: "antarkanma-bbafa.firebasestorage.app",
    messagingSenderId: "833436904161",
    appId: "1:833436904161:web:93a173b3dfd5826767e5a0",
    measurementId: "G-WX8VGMZM8K"
});

// Retrieve an instance of Firebase Messaging
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    console.log('Received background message:', payload);

    // Customize notification here
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/icon.png', // Add your app icon path here
        badge: '/badge.png', // Add your badge icon path here
        data: payload.data
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    console.log('Notification clicked:', event);

    // Close all notifications
    event.notification.close();

    // Get the action data from the notification
    const data = event.notification.data;
    let url = '/products';

    // Customize the URL based on the action
    if (data) {
        switch (data.action) {
            case 'created':
            case 'updated':
            case 'restored':
                url = `/products/${data.product_id}`;
                break;
            case 'deleted':
                url = '/products';
                break;
        }
    }

    // Open or focus the appropriate page
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(windowClients => {
            // Check if there is already a window/tab open with the target URL
            for (let client of windowClients) {
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }
            // If no window/tab is open, open a new one
            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
