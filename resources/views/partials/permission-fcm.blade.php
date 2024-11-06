<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cek apakah token FCM di localStorage sama dengan yang ada di server
        const savedToken = localStorage.getItem('fcm_token');


        // Jika token tidak sama atau tidak ada, lakukan registrasi FCM
        // if (savedToken !== serverToken) {
        // }
        initFirebaseMessagingRegistration();

        // Tambahkan listener untuk memantau interaksi pertama pengguna
        document.addEventListener('click', triggerNotificationRequestOnInteraction);
        document.addEventListener('keydown', triggerNotificationRequestOnInteraction);
        document.addEventListener('scroll', triggerNotificationRequestOnInteraction);
    });

    function getBrowserName() 
    {
        const agent = navigator.userAgent.toLowerCase();
        if (agent.indexOf("firefox") > -1) {
            return "Mozilla Firefox";
        } else if (agent.indexOf("safari") > -1 && agent.indexOf("chrome") === -1) {
            return "Safari";
        } else if (agent.indexOf("chrome") > -1) {
            return "Google Chrome";
        } else if (agent.indexOf("edge") > -1) {
            return "Microsoft Edge";
        } else if (agent.indexOf("opera") > -1 || agent.indexOf("opr") > -1) {
            return "Opera";
        } else {
            return "Browser tidak dikenal";
        }
    }

    function initFirebaseMessagingRegistration() 
    {
        const storedToken = localStorage.getItem('fcm_token');
        const messaging = firebase.messaging();
        
        // if (storedToken) {
        //     console.log("Token yang tersimpan:", storedToken);
            
        // } else {
            if (Notification.permission === 'granted') {
                // Jika izin sudah diberikan, ambil token FCM
                messaging.getToken({ vapidKey: "{{ config('services.firebase.vapid_key') }}" })
                    .then((newToken) => {
                        // Mengambil token FCM
                        if (newToken) 
                        {
                            if (!storedToken) {
                                // If no stored token, register the new token
                                sendTokenToServer(newToken);
                                localStorage.setItem('fcm_token', newToken);
                                console.log("New token registered.");
                            } 
                            // else if (storedToken !== newToken) {
                            //     // If token has changed, update the server with the new token
                            //     sendTokenToServer(storedToken, newToken);
                            //     localStorage.setItem('fcm_token', newToken); // Update stored token
                            //     console.log("Token updated.");
                            // } 
                            else 
                            {
                                sendTokenToServer(storedToken, newToken);
                                localStorage.setItem('fcm_token', newToken); // Update stored token
                                console.log("Token updated.");
                            }
                        } else {
                            console.log('No registration token available. Request permission to generate one.');
                        }
                    })
                    .catch((err) => {
                        console.error('An error occurred while retrieving token. ', err);
                    });
            } else if (Notification.permission === 'default') {
                // Panggil fungsi untuk meminta izin notifikasi
                requestNotificationPermission();
            } else {
                alert("Notifikasi telah diblokir di pengaturan browser. Silakan aktifkan notifikasi di pengaturan untuk menerima pemberitahuan.");
            }
    //     }
    }

    function requestNotificationPermission() {
        Notification.requestPermission().then(function(permission) {
            console.log('Notification permission status:', permission);
            if (permission === 'granted') {
                // Jika pengguna memberikan izin, inisialisasi FCM
                initFirebaseMessagingRegistration();
            } else if (permission === 'denied') 
            {
                console.log("Notifikasi ditolak. Silakan aktifkan notifikasi di pengaturan browser untuk menerima pemberitahuan.");
            }
        }).catch(function(err) {
            console.error('Failed to get notification permission:', err);
        });
    }
    
    function sendTokenToServer(token, newToken = null) {
        const browserName = getBrowserName();
        storedToken = localStorage.getItem('fcm_token');

        // if(token != storedToken) 
        // {
            $.ajax({
                url: "{{ route('user.updatefcm') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    token: token,
                    new_token: newToken,
                    browser_name: browserName
                },
                success: function(response) {
                    console.log("Token tersimpan:", response);
                    
                    console.log("Token berhasil disimpan.");
                },
                error: function(err) {
                    console.log($(err).text());
                    
                    console.error("Gagal menyimpan token:", err);
                }
            });
        // }
    }

    // Fungsi untuk memantau interaksi pertama pengguna
    function triggerNotificationRequestOnInteraction() {
        if (Notification.permission === 'default') 
        {
            // Permintaan izin notifikasi akan muncul pada interaksi pertama
            requestNotificationPermission();

            // Hapus event listener setelah permintaan dikirim
            document.removeEventListener('click', triggerNotificationRequestOnInteraction);
            document.removeEventListener('keydown', triggerNotificationRequestOnInteraction);
            document.removeEventListener('scroll', triggerNotificationRequestOnInteraction);
        }
    }

    // Memantau interaksi pengguna di halaman (scroll, klik, atau ketik)

    // window.onload = function() {
    //     // Cek status izin notifikasi
    //     initFirebaseMessagingRegistration();

    //     // Tambahkan listener untuk memantau interaksi pertama pengguna
    //     document.addEventListener('click', triggerNotificationRequestOnInteraction);
    //     document.addEventListener('keydown', triggerNotificationRequestOnInteraction);
    //     document.addEventListener('scroll', triggerNotificationRequestOnInteraction);
    // };
</script>