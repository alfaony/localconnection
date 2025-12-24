@extends('adminlte::page')

@section('title', 'Scan Absensi - Office Attendance')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            @include('components.alert')
            <div class="card card-primary card-outline mt-5">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-camera mr-2"></i>
                        Absensi - Foto dan Lokasi
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Status Verifikasi -->
                    <div id="status-container" class="alert alert-info mb-4">
                        <h5 class="alert-heading">
                            <i class="fas fa-sync-alt fa-spin mr-2"></i>
                            Status Verifikasi: <span id="status">Sedang diproses...</span>
                        </h5>
                        <p class="mb-0">Harap tunggu sebentar, sistem sedang memverifikasi QR code Anda.</p>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="loading-spinner" class="text-center mb-4" style="display: none;">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="sr-only">Memverifikasi...</span>
                        </div>
                        <p class="mt-2">Memverifikasi QR code...</p>
                    </div>

                    <!-- Form Foto dan Lokasi - Awalnya disembunyikan -->
                    <form id="attendance-form" action="{{ route('office-attendance.complete', $barcode->code) }}" method="POST" enctype="multipart/form-data" style="display: none;">
                        @csrf
                        @method('PUT')

                        <!-- Webcam Capture Section -->
                        <div class="form-group">
                            <label for="photo" class="font-weight-bold">Ambil Foto Anda</label>
                            <div class="webcam-container bg-light p-3 rounded text-center mb-3 d-flex flex-column align-items-center">
                                <video id="webcam" width="320" height="240" autoplay class="rounded border"></video>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-success btn-action" id="capture-btn">
                                        <i class="fas fa-camera mr-2"></i>Ambil Foto
                                    </button>
                                    <button type="button" class="btn btn-warning btn-action" id="retake-btn" style="display: none;">
                                        <i class="fas fa-redo mr-2"></i>Ambil Ulang
                                    </button>
                                </div>
                                <canvas id="canvas" style="display: none;"></canvas>
                                <img id="photo-preview" class="rounded border mt-3" style="display: none; width: 320px; height: 240px;" />
                                <input type="hidden" name="photo" id="photo">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="latitude" class="font-weight-bold">Latitude Anda</label>
                            <input type="text" class="form-control" id="latitude" name="latitude" required readonly placeholder="Menunggu akses lokasi...">

                            <label for="longitude" class="font-weight-bold mt-2">Longitude Anda</label>
                            <input type="text" class="form-control" id="longitude" name="longitude" required readonly placeholder="Menunggu akses lokasi...">
                            
                            <div class="mt-3 text-center">
                                <button type="button" class="btn btn-info btn-sm" id="get-location-btn">
                                    <i class="fas fa-map-marker-alt mr-2"></i>Dapatkan Lokasi Saya
                                </button>
                                <small class="d-block mt-2 text-muted" id="location-status">
                                    <i class="fas fa-info-circle"></i> Klik tombol untuk mengisi koordinat lokasi
                                </small>
                            </div>
                        </div>

                        <div class="form-group text-center mt-4">
                            <button type="submit" id="submit-btn" class="btn btn-primary btn-sm">
                                <i class="fas fa-paper-plane mr-2"></i>Kirim Absensi
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Pastikan Anda memberikan izin akses kamera dan lokasi untuk proses absensi
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
    <audio id="notification-message-entry" src="/audio/notification-message-entry.mp3" preload="auto"></audio>
    <script>
        const userId = @json(auth()->user()->id);
        
        const host = '{{ config('services.connection_reverb.host') }}';
        const key = '{{ config('services.connection_reverb.key') }}';
        const port = '{{ config('services.connection_reverb.port') }}';  
        
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
        notifSoundEntry = document.getElementById('notification-message-entry');
        
        window.Echo.private(`office.scan.${userId}`)
        .listen('BarcodeVerifiedSuccess', (e) => {
            // Verifikasi berhasil, tampilkan form foto & lokasi
            document.getElementById('status').innerText = 'Verifikasi berhasil!';
            document.getElementById('status-container').classList.remove('alert-info');
            document.getElementById('status-container').classList.add('alert-success');
            document.getElementById('loading-spinner').style.display = 'none';
            document.getElementById('attendance-form').style.display = 'block';
            
            notifSoundEntry?.play();
            
            // Hidupkan webcam setelah verifikasi
            const video = document.getElementById('webcam');
            video.style.display = 'block'; // Tampilkan video (webcam)
            startWebcam(); // Memulai webcam
            
            // Otomatis coba ambil lokasi setelah form muncul
            getLocation();
        });

        // Webcam setup
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const captureBtn = document.getElementById('capture-btn');
        const retakeBtn = document.getElementById('retake-btn');
        const photoInput = document.getElementById('photo');
        const photoPreview = document.getElementById('photo-preview');

        // Akses kamera
        function startWebcam() {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    video.srcObject = stream;
                })
                .catch(err => {
                    alert('Kamera tidak dapat diakses!');
                });
        }

        // Ambil snapshot dan simpan foto
        captureBtn.addEventListener('click', () => {
            // Ukuran canvas mengikuti ukuran video
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert canvas to image data (Base64)
            const imageData = canvas.toDataURL('image/png');
            photoInput.value = imageData; // Set base64 image in hidden input

            // Tampilkan preview foto
            photoPreview.src = imageData;
            photoPreview.style.display = 'block';

            // Sembunyikan webcam setelah mengambil foto
            video.style.display = 'none';

            // Tampilkan tombol "Ambil Ulang"
            retakeBtn.style.display = 'inline-block';
            captureBtn.style.display = 'none';
        });

        // Re-take foto
        retakeBtn.addEventListener('click', () => {
            // Reset form dan sembunyikan preview foto
            photoPreview.style.display = 'none';
            captureBtn.style.display = 'inline-block';
            retakeBtn.style.display = 'none';
            photoInput.value = ''; // Reset hidden input

            // Tampilkan webcam kembali
            video.style.display = 'block';
            startWebcam(); // Mulai webcam lagi

            // Clear canvas for re-capture
            canvas.width = 0;
            canvas.height = 0;
        });

        // Fungsi untuk mendapatkan lokasi
        function getLocation() {
            const locationBtn = document.getElementById('get-location-btn');
            const locationStatus = document.getElementById('location-status');
            
            // Update status dan disable button saat proses
            locationBtn.disabled = true;
            locationBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengambil lokasi...';
            locationStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sedang mengambil koordinat lokasi...';
            locationStatus.className = 'd-block mt-2 text-info';
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    // Set latitude dan longitude terpisah
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    // Isi masing-masing input dengan latitude dan longitude
                    document.getElementById('latitude').value = lat.toFixed(6);
                    document.getElementById('longitude').value = lng.toFixed(6);
                    
                    // Update status sukses
                    locationBtn.disabled = false;
                    locationBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Lokasi Terisi';
                    locationBtn.classList.remove('btn-info');
                    locationBtn.classList.add('btn-success');
                    
                    locationStatus.innerHTML = '<i class="fas fa-check-circle"></i> Koordinat lokasi berhasil didapatkan!';
                    locationStatus.className = 'd-block mt-2 text-success';
                }, function(error) {
                    // Handle error
                    let errorMessage = 'Gagal mendapatkan lokasi. ';
                    
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMessage += 'Anda menolak akses lokasi. Silakan izinkan akses lokasi di pengaturan browser.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMessage += 'Informasi lokasi tidak tersedia.';
                            break;
                        case error.TIMEOUT:
                            errorMessage += 'Permintaan lokasi timeout. Silakan coba lagi.';
                            break;
                        default:
                            errorMessage += 'Terjadi kesalahan tidak diketahui.';
                    }
                    
                    alert(errorMessage);
                    
                    document.getElementById('latitude').value = '';
                    document.getElementById('longitude').value = '';
                    
                    // Update status error
                    locationBtn.disabled = false;
                    locationBtn.innerHTML = '<i class="fas fa-map-marker-alt mr-2"></i>Coba Lagi';
                    locationBtn.classList.remove('btn-success');
                    locationBtn.classList.add('btn-info');
                    
                    locationStatus.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Gagal mendapatkan lokasi. Klik tombol untuk mencoba lagi.';
                    locationStatus.className = 'd-block mt-2 text-danger';
                });
            } else {
                alert('Geolocation tidak didukung oleh browser Anda.');
                document.getElementById('latitude').value = '';
                document.getElementById('longitude').value = '';
                
                // Update status error
                locationBtn.disabled = false;
                locationBtn.innerHTML = '<i class="fas fa-times-circle mr-2"></i>Tidak Didukung';
                locationBtn.classList.remove('btn-success');
                locationBtn.classList.add('btn-danger');
                
                locationStatus.innerHTML = '<i class="fas fa-times-circle"></i> Geolocation tidak didukung browser ini.';
                locationStatus.className = 'd-block mt-2 text-danger';
            }
        }

        // Event listener untuk button get location
        document.getElementById('get-location-btn').addEventListener('click', getLocation);

        // Validasi form sebelum submit
        document.getElementById('attendance-form').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default submit
            
            const latitude = document.getElementById('latitude').value;
            const longitude = document.getElementById('longitude').value;
            const photo = document.getElementById('photo').value;
            
            // Validasi foto
            if (!photo) {
                alert('⚠️ Silakan ambil foto terlebih dahulu sebelum mengirim absensi!');
                return false;
            }
            
            // Validasi lokasi
            if (!latitude || !longitude || latitude === '' || longitude === '' || 
                latitude === 'Lokasi tidak terdeteksi' || longitude === 'Lokasi tidak terdeteksi' ||
                latitude === 'Geolocation tidak didukung browser ini' || longitude === 'Geolocation tidak didukung browser ini') {
                
                alert('⚠️ Koordinat lokasi belum terisi!\n\nSilakan klik tombol "Dapatkan Lokasi Saya" untuk mengisi koordinat lokasi Anda terlebih dahulu.');
                
                // Scroll ke bagian lokasi dan highlight button
                document.getElementById('get-location-btn').scrollIntoView({ behavior: 'smooth', block: 'center' });
                document.getElementById('get-location-btn').classList.add('btn-pulse');
                
                setTimeout(() => {
                    document.getElementById('get-location-btn').classList.remove('btn-pulse');
                }, 2000);
                
                return false;
            }
            
            // Jika semua validasi lolos, submit form
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            
            // Submit form
            this.submit();
        });
    </script>
    
    <style>
        /* Animasi pulse untuk button */
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(23, 162, 184, 0.7);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(23, 162, 184, 0);
            }
        }
        
        .btn-pulse {
            animation: pulse 1s infinite;
        }
    </style>
@endsection