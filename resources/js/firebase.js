// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
import { getMessaging, onMessage, getToken } from "firebase/messaging";

// Your web app's Firebase configuration
const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_WEB_API_KEY || "AIzaSyDDfPtr3fUr556ItfKpO2TVkkUghQ1LwfM",
    authDomain: import.meta.env.VITE_FIREBASE_WEB_AUTH_DOMAIN || "antarkanma-bbafa.firebaseapp.com",
    projectId: import.meta.env.VITE_FIREBASE_WEB_PROJECT_ID || "antarkanma-bbafa",
    storageBucket: import.meta.env.VITE_FIREBASE_WEB_STORAGE_BUCKET || "antarkanma-bbafa.firebasestorage.app",
    messagingSenderId: import.meta.env.VITE_FIREBASE_WEB_MESSAGING_SENDER_ID || "833436904161",
    appId: import.meta.env.VITE_FIREBASE_WEB_APP_ID || "1:833436904161:web:93a173b3dfd5826767e5a0",
    measurementId: import.meta.env.VITE_FIREBASE_WEB_MEASUREMENT_ID || "G-WX8VGMZM8K"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);
const messaging = getMessaging(app);

// Handle FCM messages
export const initializeFCM = async (updateCallback) => {
    try {
        const token = await getToken(messaging, {
            vapidKey: import.meta.env.VITE_FIREBASE_WEB_VAPID_KEY
        });

        // Register FCM token with backend
        if (token) {
            await fetch('/api/fcm/token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    token: token,
                    device_type: 'web'
                })
            });
        }

        // Handle foreground messages
        onMessage(messaging, (payload) => {
            console.log('Received foreground message:', payload);
            
            // Handle different types of updates
            if (payload.data) {
                switch (payload.data.action) {
                    case 'created':
                    case 'updated':
                    case 'deleted':
                    case 'restored':
                        // Call the provided callback to update frontend data
                        updateCallback(payload.data);
                        break;
                }
            }
        });
    } catch (error) {
        console.error('Error initializing FCM:', error);
    }
};

// Example usage in your frontend components:
/*
import { initializeFCM } from './firebase';

// In your component's mounted/setup function:
initializeFCM((data) => {
    // Handle the update based on the action
    switch (data.action) {
        case 'created':
            // Fetch new product data or update list
            fetchProducts();
            break;
        case 'updated':
            // Update specific product in the list
            updateProduct(data.product_id);
            break;
        case 'deleted':
            // Remove product from list
            removeProduct(data.product_id);
            break;
        case 'restored':
            // Add product back to list
            addProduct(data.product);
            break;
    }
});
*/

export { app, analytics };
