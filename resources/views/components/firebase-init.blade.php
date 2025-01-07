<!-- Firebase App (the core Firebase SDK) -->
<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/11.1.0/firebase-app.js";
    import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.1.0/firebase-analytics.js";
    
    const firebaseConfig = {
        apiKey: "{{ config('firebase.web.api_key') }}",
        authDomain: "{{ config('firebase.web.auth_domain') }}",
        projectId: "{{ config('firebase.web.project_id') }}",
        storageBucket: "{{ config('firebase.web.storage_bucket') }}",
        messagingSenderId: "{{ config('firebase.web.messaging_sender_id') }}",
        appId: "{{ config('firebase.web.app_id') }}",
        measurementId: "{{ config('firebase.web.measurement_id') }}"
    };

    // Initialize Firebase
    const app = initializeApp(firebaseConfig);
    const analytics = getAnalytics(app);

    // Make Firebase instance available globally
    window.firebaseApp = app;
</script>
