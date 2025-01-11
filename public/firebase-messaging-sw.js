importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.8.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "AIzaSyDDfPtr3fUr556ItfKpO2TVkkUghQ1LwfM",
    authDomain: "antarkanma-bbafa.firebaseapp.com",
    projectId: "antarkanma-bbafa",
    storageBucket: "antarkanma-bbafa.firebasestorage.app",
    messagingSenderId: "833436904161",
    appId: "1:833436904161:web:93a173b3dfd5826767e5a0",
    measurementId: "G-WX8VGMZM8K"
});

const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    console.log('Received background message:', payload);

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/icon.png',
        badge: '/badge.png',
        data: payload.data,
        tag: payload.data.action // Use action as tag to group similar notifications
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    console.log('Notification clicked:', event);
    event.notification.close();

    // Get the action data from the notification
    const data = event.notification.data;
    let url = '/';

    // Customize URL based on the action
    if (data && data.action) {
        switch (data.action) {
            case 'new_transaction':
                url = `/merchant/orders/${data.order_id}`;
                break;
            case 'order_status_update':
                url = `/orders/${data.order_id}`;
                break;
            case 'transaction_canceled':
                url = `/transactions/${data.transaction_id}`;
                break;
            case 'test':
                url = '/test-notification.html';
                break;
            default:
                url = '/';
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

// Optional: Handle notification close
self.addEventListener('notificationclose', (event) => {
    console.log('Notification closed:', event);
});
