@canAccess('unreadcount','inboxes')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<audio id="notification-message-entry" src="/audio/notification-message-entry.mp3" preload="auto"></audio>
<audio id="notification-message-high" src="/audio/notification-message-high.mp3" preload="auto"></audio>
<audio id="notification-message-email" src="/audio/notification-message-email.mp3" preload="auto"></audio>
<audio id="notification-message-download" src="/audio/notification-message-download.mp3" preload="auto"></audio>
<script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
<script>
    async function getUnreadCount() {
        let response = await $.ajax({
            url: '{{ route('inbox.unreadcount') }}',
            type: 'GET'
        });
        let unreadCount = response.unreadCount;

        let badge = $('.navbar-badge');

        if (unreadCount > 0) {
            badge.text(unreadCount);
        } else {
            badge.text("");
        }
    }

    getUnreadCount();
</script>
<script>

    host = '{{ config('services.connection_reverb.host')}}';
    key = '{{ config('services.connection_reverb.key')}}';
    port = '{{ config('services.connection_reverb.port')}}';  
    userId = "{{ Auth::user()->id }}";
    notifSoundEntry = document.getElementById('notification-message-entry');
    notifSoundHigh = document.getElementById('notification-message-high');
    notifSoundEmail = document.getElementById('notification-message-email');
    notifSoundDownload = document.getElementById('notification-message-download');

    window.Pusher = Pusher;

    window.Echo = new Echo.default({
        broadcaster: 'reverb',
        key: key,
        wsHost: host,
        wsPort: 8080,
        wssPort: port,
        forceTLS: true,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/authorize',
        disableStats: true,
    });

    window.Echo.private('user.' + userId)
    .listen('InboxReceived', (e) => {
        // Menampilkan pesan sementara
        console.log(e);
        if (e.download_url) 
        {
            // Ada download_url = auto-download
            handleDownloadNotification(e);
        }else
        {
            showToast(`📩 ${e.user_from}: ${e.message}`, e.direct_url);
        }
        
        getUnreadCount();
        
        if(e.category == "high") 
        {
            notifSoundHigh?.play();
        }
        else if(e.category == "download")
        {
            notifSoundDownload?.play();
        }
        else if(e.category == "email")
        {
            notifSoundEmail?.play();
        }
        else
        {
            notifSoundEntry?.play();
        }
    });

    function showToast(message, directUrl = null) 
    {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "timeOut": directUrl ? 180000 : 5000,
            "extendedTimeOut": 1000,
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };

        if (directUrl) 
        {
            toastr.info(`
                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1 pr-2">${message}</div>
                    <a href="${directUrl}" class="btn btn-sm btn-primary font-weight-bold shadow-sm">
                        <i class="fas fa-external-link-alt mr-1"></i>
                    </a>
                </div>
            `);
        } else {
            toastr.success(message);
        }
    }
    function handleDownloadNotification(e) {
        const message = `📩 ${e.user_from}: ${e.message}`;
        const downloadUrl = e.download_url;
        
        console.log('Download notification detected:', downloadUrl);
        // showToast(message, downloadUrl);
        setTimeout(() => autoDownloadFile(downloadUrl), 2000);
    }

    function autoDownloadFile(url) 
    {
        try {
            console.log('Auto-downloading file from:', url);
            
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.style.visibility = 'hidden';
            iframe.src = url;
            document.body.appendChild(iframe);
            
            toastr.success('📥 Download dimulai otomatis...', 'Download', {
                timeOut: 3000,
                progressBar: true
            });
            
            setTimeout(() => {
                try {
                    document.body.removeChild(iframe);
                } catch (e) {
                    console.log('Iframe already removed');
                }
            }, 5000);
            
            console.log('File download initiated successfully');
            
        } catch (error) {
            console.error('Auto-download failed:', error);
            toastr.warning(
                `Download otomatis gagal. <a href="${url}" class="btn btn-sm btn-light ml-2" download>
                    <i class="fas fa-download"></i> Download Manual
                </a>`, 
                'Download', 
                {
                    timeOut: 10000,
                    closeButton: true,
                    progressBar: true,
                    escapeHtml: false
                }
            );
        }
    }
</script>
@endcanAccess
