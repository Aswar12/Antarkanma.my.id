importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.23.0/firebase-messaging-compat.js');

// Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyCHqeswHvbCbYllfkvzCbQAKNLUx11hs3Q",
    authDomain: "antarkanma-98fde.firebaseapp.com",
    projectId: "antarkanma-98fde",
    storageBucket: "antarkanma-98fde.firebasestorage.app",
    messagingSenderId: "786441533391",
    appId: "1:786441533391:web:b2f8e7de1c5cfe9a0704ba",
    measurementId: "G-0DJ37WCT04"
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// Fetch order data from API
const fetchOrderData = async (orderId) => {
    try {
        const response = await fetch(`/api/orders/${orderId}`, {
            headers: {
                'Accept': 'application/json',
            }
        });
        if (!response.ok) throw new Error('Failed to fetch order data');
        return await response.json();
    } catch (error) {
        console.error('Error fetching order data:', error);
        throw error;
    }
};

// Handle background messages
messaging.onBackgroundMessage(async (payload) => {
    console.log('Received background message:', payload);

    if (!payload.data) return;

    const { type, id, timestamp, status } = payload.data;
    let notificationTitle = '';
    let notificationOptions = {
        icon: '/icon.png',
        timestamp: new Date(timestamp).getTime()
    };

    try {
        // Check if payload contains order items
        if (payload.data.items) {
            const items = JSON.parse(payload.data.items);
            const itemsList = items.map(item => `${item.quantity}x ${item.name}`).join(', ');
            notificationOptions.body = itemsList;
        }

        // Use notification title and body from the payload if available
        notificationTitle = payload.notification?.title || 'Notifikasi Baru';
        notificationOptions.body = payload.notification?.body || notificationOptions.body || 'Ada pembaruan untuk Anda';
        
        // Add order ID to notification data if available
        if (payload.data.order_id) {
            notificationOptions.data = {
                orderId: payload.data.order_id,
                url: `/orders/${payload.data.order_id}`
            };
        }

            case 'order_canceled':
                notificationTitle = 'Pesanan Dibatalkan';
                notificationOptions.body = `Pesanan #${id} telah dibatalkan oleh pelanggan`;
                notificationOptions.data = {
                    type: 'order_canceled',
                    orderId: id,
                    url: `/orders/${id}`
                };
                break;

            case 'status_update':
                const statusMessages = {
                    'ACCEPTED': 'diterima oleh merchant',
                    'REJECTED': 'ditolak oleh merchant',
                    'PROCESSING': 'sedang diproses',
                    'SHIPPED': 'sedang dalam pengiriman',
                    'DELIVERED': 'telah sampai',
                    'COMPLETED': 'telah selesai',
                    'CANCELED': 'telah dibatalkan'
                };
                
                notificationTitle = 'Status Pesanan Diperbarui';
                notificationOptions.body = `Pesanan #${id} ${statusMessages[status] || 'telah diperbarui'}`;
                notificationOptions.data = {
                    type: 'status_update',
                    orderId: id,
                    status: status,
                    url: `/orders/${id}`
                };
                break;

            case 'test':
                notificationTitle = 'Notifikasi Test';
                notificationOptions.body = 'Ini adalah notifikasi test dari Antarkanma';
                notificationOptions.data = {
                    type: 'test',
                    url: '/'
                };
                break;
        }

        // Show notification
        return self.registration.showNotification(notificationTitle, notificationOptions);
    } catch (error) {
        console.error('Error handling background notification:', error);
        // Show a fallback notification
        return self.registration.showNotification(
            'Notifikasi Baru',
            {
                body: 'Silakan buka aplikasi untuk melihat detail',
                icon: '/icon.png',
                data: {
                    type: type,
                    orderId: id,
                    url: `/orders/${id}`
                }
            }
        );
    }
});

// Handle notification click
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    
    // Get the notification data
    const data = event.notification.data;
    if (!data || !data.url) return;

    // Open or focus the appropriate page
    event.waitUntil(
        clients.matchAll({ type: 'window' }).then(windowClients => {
            // Check if there is already a window/tab open with the target URL
            for (let client of windowClients) {
                if (client.url === data.url && 'focus' in client) {
                    return client.focus();
                }
            }
            // If no window/tab is open, open a new one
            if (clients.openWindow) {
                return clients.openWindow(data.url);
            }
        })
    );
});
