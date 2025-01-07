<?php

return [
    // Android app configuration
    'android' => [
        'project_id' => env('FIREBASE_PROJECT_ID', 'antarkanma'),
        'api_key' => env('FIREBASE_API_KEY', 'AIzaSyD1ADl2NJLFepwYcQltfKh4ytOHzEvabQQ'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID', '140831862137'),
        'server_key' => env('FIREBASE_SERVER_KEY'),
    ],

    // Web app configuration
    'web' => [
        'api_key' => env('FIREBASE_WEB_API_KEY', 'AIzaSyDDfPtr3fUr556ItfKpO2TVkkUghQ1LwfM'),
        'auth_domain' => env('FIREBASE_WEB_AUTH_DOMAIN', 'antarkanma-bbafa.firebaseapp.com'),
        'project_id' => env('FIREBASE_WEB_PROJECT_ID', 'antarkanma-bbafa'),
        'storage_bucket' => env('FIREBASE_WEB_STORAGE_BUCKET', 'antarkanma-bbafa.firebasestorage.app'),
        'messaging_sender_id' => env('FIREBASE_WEB_MESSAGING_SENDER_ID', '833436904161'),
        'app_id' => env('FIREBASE_WEB_APP_ID', '1:833436904161:web:93a173b3dfd5826767e5a0'),
        'measurement_id' => env('FIREBASE_WEB_MEASUREMENT_ID', 'G-WX8VGMZM8K'),
    ],

    // Common configuration
    'fcm_url' => 'https://fcm.googleapis.com/fcm/send',
];
