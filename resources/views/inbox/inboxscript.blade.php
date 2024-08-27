<!-- jQuery CDN -->
<script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-database.js"></script>

<script>
    // Ambil konfigurasi Firebase dari Laravel config
    var firebaseConfig = {
        apiKey: "{{ config('services.firebase.api_key') }}",
        authDomain: "{{ config('services.firebase.auth_domain') }}",
        databaseURL: "{{ config('services.firebase.service_database_url') }}",
        projectId: "{{ config('services.firebase.project_id') }}",
        storageBucket: "{{ config('services.firebase.storage_bucket') }}",
        messagingSenderId: "{{ config('services.firebase.messaging_sender_id') }}",
        appId: "{{ config('services.firebase.app_id') }}"
    };
    
    // Inisialisasi Firebase
    firebase.initializeApp(firebaseConfig);

    var userId = '{{ Auth::user()->id }}'; // Gunakan ID pengguna yang sedang login
    var unreadCountRef = firebase.database().ref('notifications/' + userId);

    // Dengarkan perubahan pada data unread messages
    unreadCountRef.on('value', function(snapshot) {
        var unreadCount = 0;
        snapshot.forEach(function(childSnapshot) {
            var isRead = childSnapshot.val().is_read;
            if (!isRead) {
                unreadCount++;
            }
        });

        console.log(unreadCount);
        

        let badge = $('.navbar-badge');

        if (unreadCount > 0) {
            badge.text(unreadCount);
        } else {
            badge.text("");
        }
    });
</script>
