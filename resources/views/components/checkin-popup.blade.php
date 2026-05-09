<!-- Employee Check-in Popup — via WebSocket (Laravel Reverb) + localStorage -->
@canAccess('update','employee_checkings')
<div id="checkinPopup" class="" style="display: none !important;">
    <div class="popup-content">
        <h2>Time to Check-in </h2>
        <p class="mb-0" id="show_time_checkin"></p>
        <p class="mb-0">Please confirm your presence within:</p>

        <div class="timer form-group">
            <span class="countdown" id="countdown"></span>
        </div>

        <div id="photoSection" class="form-group" style="margin-top: 15px;">
            <video id="videoFeed" autoplay playsinline style="width: 100%; height: auto;"></video>
            <canvas id="canvas" style="display:none;"></canvas>
            <button id="takePhotoButton" class="btn btn-secondary mt-2" onclick="takePhoto()">Take Photo</button>
            <button id="toggleCameraButton" class="btn btn-info mt-2" onclick="toggleCamera()">Switch Camera</button>
            <img id="photo-preview" src="#" alt="Photo Preview" style="display:none;" class="img-thumbnail mt-3">
            <input type="file" id="photo" name="photo" style="display: none;">
        </div>
        <span id="photo-warning" style="color: red; font-size: 12px;"></span>

        <div id="locationSection" class="form-group" style="display: none; margin-top: 15px;">
            <button class="form-control" onclick="getLocationNow()">Share Location</button>
            <p id="locationStatus"></p>
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">
            <span id="location-warning" style="color: red; font-size: 12px;"></span>
        </div>

        <div id="captchaSection" style="margin-top: 15px;">
            <div class="g-recaptcha" data-sitekey="{{ config('captcha.sitekey') }}"
                data-callback="onRecaptchaSuccess"
                data-expired-callback="onRecaptchaExpired"
                data-error-callback="onRecaptchaError">
            </div>
            <span id="captcha-warning" style="color: red; font-size: 12px;"></span>
        </div>

        <div class="form-group" id="footerSubmitCheckin">
            <button id="submitCheckin" class="btn btn-primary mt-4" onclick="onSubmit()">Submit Check-in</button>
        </div>
    </div>
</div>
<audio id="checkinAudio" src="/audio/notification-sound.mp3" preload="auto"></audio>
<link rel="stylesheet" href="{{ asset('css/popup_backup.css') }}">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>

<script>
    let intervalId    = null;
    let recaptchaToken = '';

    const checkinUserId   = "{{ Auth::user()->id }}";
    const checkinDuration = parseInt("{{ config('services.checking_setting.duration') }}");

    // Key localStorage per-user — aman meski ganti akun di browser yang sama
    const CHECKIN_LS_KEY = 'checkin_active_' + checkinUserId;

    // ═══════════════════════════════════════════════════════════════
    // LOCALSTORAGE HELPERS
    // ═══════════════════════════════════════════════════════════════

    function lsSave(data, expiresAt) {
        try {
            localStorage.setItem(CHECKIN_LS_KEY, JSON.stringify({
                local_id          : data.local_id,
                scheduled_time    : data.scheduled_time,
                expires_at        : expiresAt,          // Unix timestamp (detik)
                requires_photo    : data.requires_photo    || false,
                requires_location : data.requires_location || false,
            }));
        } catch (e) {
            console.warn('[Checkin LS] Gagal simpan:', e);
        }
    }

    function lsClear() {
        try {
            localStorage.removeItem(CHECKIN_LS_KEY);
        } catch (e) {}
    }

    function lsRead() {
        try {
            const raw = localStorage.getItem(CHECKIN_LS_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            lsClear();
            return null;
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // INISIALISASI ECHO (reuse jika sudah ada dari komponen lain)
    // ═══════════════════════════════════════════════════════════════

    function initCheckinEcho() {
        if (window.Echo) return window.Echo;

        window.Pusher = Pusher;
        window.Echo   = new Echo.default({
            broadcaster       : 'reverb',
            key               : '{{ config('services.connection_reverb.key') }}',
            wsHost            : '{{ config('services.connection_reverb.host') }}',
            wsPort            : 8080,
            wssPort           : '{{ config('services.connection_reverb.port') }}',
            forceTLS          : true,
            enabledTransports : ['ws', 'wss'],
            authEndpoint      : '/broadcasting/authorize',
            disableStats      : true,
        });

        return window.Echo;
    }

    // ═══════════════════════════════════════════════════════════════
    // MONITOR — entry point saat halaman dimuat
    // ═══════════════════════════════════════════════════════════════

    function monitorCheckin() {
        const echo = initCheckinEcho();

        // ── LAPIS 1: WebSocket push real-time ──────────────────────
        echo.private('employee-checkin.' + checkinUserId)

            .listen('EmployeeCheckinActivated', (data) => {
                console.log('[Checkin L1] WS Activated:', data.local_id);
                if (!data.local_id || !data.is_active) return;

                const scheduledTs = new Date(data.scheduled_time).getTime() / 1000;
                const expiresAt   = scheduledTs + checkinDuration;
                const timeLeft    = Math.max(0, expiresAt - Math.floor(Date.now() / 1000));

                if (timeLeft <= 0) return;

                // Simpan ke localStorage agar bisa di-recover saat refresh / tab baru
                lsSave(data, expiresAt);

                document.getElementById('show_time_checkin').textContent = data.scheduled_time;
                showCheckinPopup(timeLeft, data.local_id, data.requires_photo, data.requires_location);
            })

            .listen('EmployeeCheckinDeactivated', (data) => {
                console.log('[Checkin L1] WS Deactivated:', data.local_id);
                // Hapus localStorage agar tab lain ikut tutup via storage event
                lsClear();
                const popup = document.getElementById('checkinPopup');
                if (popup && popup.dataset.checkinId == data.local_id) {
                    forceClosePopup(false); // false = jangan clear LS lagi (sudah di atas)
                }
            });

        // ── LAPIS 2: Page load / refresh — baca localStorage ──────
        // Zero request ke server, murni client-side
        checkFromLocalStorage();

        // ── LAPIS 3: WS connected (sudah maupun baru connect) — cek server jika LS kosong
        // Menangani skenario: device baru login, atau offline saat event dikirim
        // Bug fix: bind('connected') tidak fire jika WS sudah connected sebelum handler dipasang
        // (terjadi ketika window.Echo di-reuse dari komponen lain di halaman yang sama)
        const pusherConn = echo.connector.pusher.connection;

        const runLapis3 = () => {
            console.log('[Checkin L3] WS connected/reconnected');

            if (lsRead()) {
                // localStorage ada → Lapis 2 sudah tangani, skip server request
                console.log('[Checkin L3] localStorage ada, skip server request');
                return;
            }

            // localStorage kosong → kemungkinan device baru / beda browser
            // Lakukan 1x request ke server
            console.log('[Checkin L3] localStorage kosong → cek server 1x');
            checkFromServer();
        };

        if (pusherConn.state === 'connected') {
            // WS sudah connect sebelum handler dipasang → jalankan langsung
            runLapis3();
        } else {
            // WS belum connect → tunggu event
            pusherConn.bind('connected', runLapis3);
        }

        // ── CROSS-TAB SYNC via storage event ──────────────────────
        // Misal Tab A submit → clear LS → Tab B deteksi → tutup popup
        // Misal Tab A terima WS → simpan LS → Tab B deteksi → buka popup
        window.addEventListener('storage', (e) => {
            if (e.key !== CHECKIN_LS_KEY) return;

            if (e.newValue === null) {
                // localStorage di-hapus tab lain → tutup popup di tab ini
                console.log('[Checkin Cross-tab] LS cleared dari tab lain → tutup popup');
                const popup = document.getElementById('checkinPopup');
                if (popup && popup.style.display === 'flex') {
                    forceClosePopup(false);
                }
            } else {
                // localStorage di-set tab lain → coba tampilkan popup di tab ini
                console.log('[Checkin Cross-tab] LS diisi dari tab lain → cek popup');
                checkFromLocalStorage();
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // CEK LOCALSTORAGE (Lapis 2 + cross-tab)
    // ═══════════════════════════════════════════════════════════════

    function checkFromLocalStorage() {
        const popup = document.getElementById('checkinPopup');
        if (popup && popup.style.display === 'flex') return; // sudah muncul

        const data = lsRead();
        if (!data) return;

        const now      = Math.floor(Date.now() / 1000);
        const timeLeft = data.expires_at - now;

        if (timeLeft > 0) {
            console.log('[Checkin L2] Restore dari localStorage, sisa:', timeLeft, 'detik');
            document.getElementById('show_time_checkin').textContent = data.scheduled_time;
            showCheckinPopup(timeLeft, data.local_id, data.requires_photo, data.requires_location);
        } else {
            // Window sudah habis, bersihkan
            console.log('[Checkin L2] localStorage expired, cleared');
            lsClear();
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // CEK SERVER 1x (Lapis 3 — hanya saat WS connect & LS kosong)
    // ═══════════════════════════════════════════════════════════════

    function checkFromServer() {
        const popup = document.getElementById('checkinPopup');
        if (popup && popup.style.display === 'flex') return;

        $.ajax({
            url    : "{{ route('employee-checking.currentActive') }}",
            type   : 'GET',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function (res) {
                if (!res.active || res.time_left_seconds <= 0) return;

                console.log('[Checkin L3] Server: ada checkin aktif', res.local_id);

                // Simpan ke localStorage agar refresh berikutnya tidak perlu ke server lagi
                const expiresAt = new Date(res.scheduled_time).getTime() / 1000 + checkinDuration;
                lsSave(res, expiresAt);

                document.getElementById('show_time_checkin').textContent = res.scheduled_time;
                showCheckinPopup(res.time_left_seconds, res.local_id, res.requires_photo, res.requires_location);
            },
            error: function () {
                console.warn('[Checkin L3] Gagal cek server.');
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // TAMPILKAN POPUP
    // ═══════════════════════════════════════════════════════════════

    function showCheckinPopup(remainingTime, localId, requiresPhoto = false, requiresLocation = false) {
        const popup           = document.getElementById('checkinPopup');
        const photoInput      = document.getElementById('photo');
        const photoSection    = document.getElementById('photoSection');
        const locationSection = document.getElementById('locationSection');
        const locationButton  = document.querySelector('#locationSection button');
        const audio           = document.getElementById('checkinAudio');

        if (popup.style.display === 'flex') return; // sudah terbuka

        if (audio) { audio.currentTime = 0; audio.play().catch(() => {}); }

        if (requiresPhoto) {
            photoSection.style.display = 'block';
            photoInput.setAttribute('required', 'required');
            openCamera();
        } else {
            photoSection.style.display = 'none';
            photoInput.removeAttribute('required');
        }

        if (requiresLocation) {
            locationSection.style.display = 'block';
            locationButton.setAttribute('required', 'required');
            getLocationNow();
        } else {
            locationSection.style.display = 'none';
            locationButton.removeAttribute('required');
        }

        popup.style.display     = 'flex';
        popup.dataset.checkinId = localId;

        const overlay = document.getElementById('overlay');
        if (overlay) overlay.style.display = 'block';

        startCountdown(remainingTime, localId);
    }

    // ═══════════════════════════════════════════════════════════════
    // COUNTDOWN
    // ═══════════════════════════════════════════════════════════════

    function startCountdown(duration, localId) {
        const countdownEl = document.getElementById('countdown');
        let timer         = Math.floor(duration);

        if (intervalId) clearInterval(intervalId);

        intervalId = setInterval(() => {
            timer--;
            countdownEl.textContent = timer;
            if (timer <= 0) {
                clearInterval(intervalId);
                // Window habis tanpa submit → hapus LS + tutup popup
                lsClear();
                closePopup();
            }
        }, 1000);
    }

    // ═══════════════════════════════════════════════════════════════
    // TUTUP POPUP
    // ═══════════════════════════════════════════════════════════════

    // Hentikan semua track kamera + reset UI foto agar popup bisa dibuka ulang bersih
    function stopCamera() {
        const video = document.getElementById('videoFeed');
        if (video && video.srcObject) {
            video.srcObject.getTracks().forEach(t => t.stop());
            video.srcObject = null;
        }
        // Reset UI foto ke kondisi awal
        const photoInput   = document.getElementById('photo');
        const photoPreview = document.getElementById('photo-preview');
        const takeBtn      = document.getElementById('takePhotoButton');
        if (photoInput)   photoInput.value       = '';
        if (photoPreview) { photoPreview.src = '#'; photoPreview.style.display = 'none'; }
        if (video)          video.style.display  = 'block';
        if (takeBtn)        takeBtn.style.display = 'inline-block';
    }

    function stopAudio() {
        const audio = document.getElementById('checkinAudio');
        if (audio && !audio.paused) {
            audio.pause();
            audio.currentTime = 0;
        }
    }

    // Tutup karena countdown habis / user dismiss — beritahu server
    function closePopup() {
        const popup = document.getElementById('checkinPopup');
        popup.style.setProperty('display', 'none', 'important');
        if (intervalId) clearInterval(intervalId);
        stopCamera();
        stopAudio();
        updateStatus(); // beritahu server: is_active = false
    }

    // Tutup paksa dari WS event / storage event — server sudah tahu, tidak perlu lapor lagi
    // clearLS: false jika LS sudah di-clear sebelum memanggil fungsi ini
    function forceClosePopup(clearLS = true) {
        const popup   = document.getElementById('checkinPopup');
        const overlay = document.getElementById('overlay');

        popup.style.setProperty('display', 'none', 'important');
        if (overlay) overlay.style.display = 'none';
        if (intervalId) clearInterval(intervalId);
        if (clearLS) lsClear();
        stopCamera();
        stopAudio();
    }

    // ═══════════════════════════════════════════════════════════════
    // SUBMIT
    // ═══════════════════════════════════════════════════════════════

    function onRecaptchaSuccess(token)  { recaptchaToken = token; document.getElementById('captcha-warning').textContent = ''; }
    function onRecaptchaExpired()       { recaptchaToken = ''; document.getElementById('captcha-warning').textContent = 'Captcha kadaluarsa, ulangi lagi.'; }
    function onRecaptchaError()         { document.getElementById('captcha-warning').textContent = 'Error reCAPTCHA, coba lagi.'; }

    function onSubmit() {
        const photoInput      = document.getElementById('photo');
        const latitude        = document.getElementById('latitude').value;
        const longitude       = document.getElementById('longitude').value;
        const photoWarning    = document.getElementById('photo-warning');
        const locationWarning = document.getElementById('location-warning');
        const recaptcha       = grecaptcha.getResponse();

        if (photoWarning)    photoWarning.textContent    = '';
        if (locationWarning) locationWarning.textContent = '';

        if (photoInput.hasAttribute('required') && !photoInput.files[0]) {
            photoWarning.textContent = 'Foto diperlukan.';
            photoWarning.style.color = 'red';
            return;
        }

        if (document.getElementById('locationSection').style.display === 'block' && (!latitude || !longitude)) {
            locationWarning.textContent = 'Lokasi diperlukan.';
            locationWarning.style.color = 'red';
            return;
        }

        if (!recaptcha) {
            document.getElementById('captcha-warning').textContent = 'Selesaikan reCAPTCHA terlebih dahulu.';
            document.getElementById('captcha-warning').style.color = 'red';
            return;
        }

        document.getElementById('footerSubmitCheckin').classList.add('d-flex', 'justify-content-center', 'mt-3', 'align-items-center');
        document.getElementById('footerSubmitCheckin').insertAdjacentHTML('beforeend',
            `<div class="spinner-border text-muted" role="status"><span class="sr-only">Memproses...</span></div>`
        );
        document.getElementById('submitCheckin').disabled      = true;
        document.getElementById('submitCheckin').style.display = 'none';

        submitCheckin(recaptcha);
    }

    function submitCheckin(recaptchaToken) {
        const localId     = document.getElementById('checkinPopup').dataset.checkinId;
        const latitude    = document.getElementById('latitude').value;
        const longitude   = document.getElementById('longitude').value;
        const photo       = document.getElementById('photo').files[0];
        const storedToken = localStorage.getItem('fcm_token');

        let formData = new FormData();
        formData.append('latitude',  latitude);
        formData.append('longitude', longitude);
        formData.append('recaptcha', recaptchaToken);
        formData.append('fcm_token', storedToken);
        formData.append('_method',   'PUT');
        if (photo) formData.append('photo', photo);

        let url = "{{ route('employee-checking.update', ':id') }}".replace(':id', localId);

        $.ajax({
            url        : url,
            type       : 'POST',
            data       : formData,
            contentType: false,
            processData: false,
            headers    : { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function () {
                clearInterval(intervalId);
                // Hapus localStorage → cross-tab sync tutup popup di tab lain (browser sama)
                // Server broadcast EmployeeCheckinDeactivated → device lain tutup popup via WS
                lsClear();
                forceClosePopup(false);
                Swal.fire({
                    icon: 'success', title: 'Check-in berhasil!',
                    text: 'Kehadiran Anda telah tercatat.',
                    timer: 3000, timerProgressBar: true, showConfirmButton: false,
                });
            },
            error: function (xhr) {
                clearInterval(intervalId);
                lsClear();
                forceClosePopup(false);
                Swal.fire({
                    icon: 'error', title: 'Gagal Check-in',
                    text: xhr.responseText,
                    timer: 3000, timerProgressBar: true, showConfirmButton: false,
                });
            }
        });
    }

    function updateStatus() {
        const localId = document.getElementById('checkinPopup').dataset.checkinId;
        if (!localId) return;

        let url = "{{ route('employee-checking.updatestatus', ':id') }}".replace(':id', localId);
        let formData = new FormData();
        formData.append('_method', 'PUT');

        $.ajax({
            url        : url,
            type       : 'POST',
            data       : formData,
            contentType: false,
            processData: false,
            headers    : { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function () { /* server sudah broadcast Deactivated → device lain tutup via WS */ }
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // KAMERA & LOKASI
    // ═══════════════════════════════════════════════════════════════

    let currentFacingMode = 'environment';

    function openCamera() {
        const video = document.getElementById('videoFeed');
        navigator.mediaDevices.getUserMedia({ video: { facingMode: currentFacingMode } })
            .then(stream => { video.srcObject = stream; })
            .catch(err   => { console.error('Camera error:', err); alert('Tidak bisa akses kamera.'); });
    }

    function toggleCamera() {
        currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
        const video = document.getElementById('videoFeed');
        if (video.srcObject) video.srcObject.getTracks().forEach(t => t.stop());
        openCamera();
    }

    function takePhoto() {
        const video        = document.getElementById('videoFeed');
        const canvas       = document.getElementById('canvas');
        const photoInput   = document.getElementById('photo');
        const photoPreview = document.getElementById('photo-preview');

        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob(blob => {
            const file = new File([blob], 'checkin_photo.jpg', { type: 'image/jpeg' });
            const dt   = new DataTransfer();
            dt.items.add(file);
            photoInput.files = dt.files;

            photoPreview.src           = URL.createObjectURL(blob);
            photoPreview.style.display = 'block';
            video.style.display        = 'none';
            document.getElementById('takePhotoButton').style.display = 'none';
            video.srcObject.getTracks().forEach(t => t.stop());
        }, 'image/jpeg', 0.7);
    }

    function getLocationNow() {
        const warn = document.getElementById('location-warning');
        if (warn) warn.textContent = '';

        if (!navigator.geolocation) {
            document.getElementById('locationStatus').textContent = 'Geolocation tidak didukung.';
            return;
        }
        navigator.geolocation.getCurrentPosition(
            pos => {
                document.getElementById('latitude').value             = pos.coords.latitude;
                document.getElementById('longitude').value            = pos.coords.longitude;
                document.getElementById('locationStatus').textContent  = 'Lokasi berhasil didapatkan!';
                document.getElementById('locationStatus').style.color  = 'green';
            },
            err => {
                const msgs = { 1: 'Akses ditolak.', 2: 'Lokasi tidak tersedia.', 3: 'Timeout.' };
                document.getElementById('locationStatus').textContent = msgs[err.code] ?? 'Error tidak diketahui.';
                document.getElementById('locationStatus').style.color = 'red';
            }
        );
    }

    // ═══════════════════════════════════════════════════════════════
    // BOOT
    // ═══════════════════════════════════════════════════════════════
    window.onload = monitorCheckin;
</script>

<script>
    function compressAndPreviewImageCheckin() {
        const fileInput = document.getElementById('photo');
        const preview   = document.getElementById('photo-preview');
        if (!fileInput.files[0]) { preview.src = ''; return; }

        const reader = new FileReader();
        reader.readAsDataURL(fileInput.files[0]);
        reader.onload = function (event) {
            const imgEl  = document.createElement('img');
            imgEl.src    = event.target.result;
            imgEl.onload = function (e) {
                const canvas  = document.createElement('canvas');
                const MAX_W   = 150;
                const scale   = MAX_W / e.target.width;
                canvas.width  = MAX_W;
                canvas.height = e.target.height * scale;
                canvas.getContext('2d').drawImage(e.target, 0, 0, canvas.width, canvas.height);
                canvas.toBlob(blob => {
                    const file = new File([blob], 'compressed_image.jpg', { type: 'image/jpeg' });
                    const dt   = new DataTransfer();
                    dt.items.add(file);
                    fileInput.files = dt.files;
                    const r2 = new FileReader();
                    r2.readAsDataURL(file);
                    r2.onloadend = function () { preview.src = r2.result; preview.style.display = 'block'; };
                }, 'image/jpeg', 0.6);
            };
        };
    }
</script>
@endcanAccess
