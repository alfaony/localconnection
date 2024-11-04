<script>
    var userId = '{{ Auth::user()->id }}'; // Gunakan ID pengguna yang sedang login
    const inbox = firebase.app().database("{{ config('services.firebase.service_database_inbox_url') }}");
    var unreadCountRef = inbox.ref('notifications/' + userId);

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
