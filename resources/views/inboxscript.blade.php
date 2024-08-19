<!-- jQuery CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        // Function to update the unread inbox count badge
        function updateInboxCount() {
            $.ajax({
                url: '/inbox/unreadcount', // Update with your correct API route
                method: 'GET',
                success: function(data) {
                    let unreadCount = data.unreadCount;
                    let badge = $('.navbar-badge');

                    if (unreadCount > 0) {
                        badge.text(unreadCount);
                    } else {
                        badge.text("");
                    }
                },
                error: function() {
                    console.error('Could not retrieve unread message count.');
                }
            });
        }

        // Call the function immediately to update the badge on page load
        updateInboxCount();
    });
</script>
