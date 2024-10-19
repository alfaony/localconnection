// Import Firebase scripts untuk Service Worker
importScripts('https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.6.10/firebase-messaging-compat.js');

// Inisialisasi Firebase di Service Worker
firebase.initializeApp({
    apiKey: "{{ config('services.firebase.api_key') }}",
    authDomain: "{{ config('services.firebase.auth_domain') }}",
    projectId: "{{ config('services.firebase.project_id') }}",
    storageBucket: "{{ config('services.firebase.storage_bucket') }}",
    messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
    appId: "{{ config('services.firebase.app_id') }}"
});


const messaging = firebase.messaging();

// Menerima pesan saat aplikasi di background
messaging.onBackgroundMessage((payload) => {
    console.log(payload);
    
    // console.log(payload);
    // console.log('Received background message ', payload);
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/logo/logo-thrive.png',  // Update path ke icon
    };
    
    return self.registration.showNotification(notificationTitle, notificationOptions)
        .then(() => console.log('Notifikasi berhasil ditampilkan'))
        .catch(err => console.error('Gagal menampilkan notifikasi:', err));
    
});
