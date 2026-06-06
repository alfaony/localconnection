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
        icon: '/logo/icon_1.svg',  // Update path ke icon
        data: { url: payload.data.url || '/' } // URL untuk pengalihan
    };
    
      // Tambahkan suara notifikasi
    //   const audio = new Audio('/audio/notification-sound.mp3'); // Ganti path dengan suara notifikasi yang valid
    //   audio.play().catch(err => console.error('Gagal memutar suara notifikasi:', err));
  
      // Tampilkan notifikasi
      return self.registration.showNotification(notificationTitle, notificationOptions)
          .then(() => console.log('Notifikasi berhasil ditampilkan'))
          .catch(err => console.error('Gagal menampilkan notifikasi:', err));
  });
  
  // Menangani klik pada notifikasi
  self.addEventListener('notificationclick', function(event) {
      console.log('Notifikasi diklik:', event.notification);
  
      event.notification.close(); // Tutup notifikasi
  
      // Pengalihan ke URL yang disertakan di data notifikasi
      if (event.notification.data && event.notification.data.url) {
          event.waitUntil(
              clients.openWindow(event.notification.data.url)
          );
      }
  });