<!-- Firebase harus di-include di layout utama sebelum popup ini -->
@canAccess('update','employee_checkings')
<div id="checkinPopup" class="" style="display: none !important;">
    <div class="popup-content">
        <h2>Time to Check-in </h2>
        <p class="mb-0" id="show_time_checkin"></p>
        <p class="mb-0">Please confirm your presence within:</p>

        <!-- Timer Countdown -->
        <div class="timer form-group ">
            <span class="countdown" id="countdown"></span>
        </div>

        <!-- Foto (Muncul jika divisi memerlukan) -->
        <div id="photoSection" class="form-group" style="margin-top: 15px;">
            <video id="videoFeed" autoplay playsinline style="width: 100%; height: auto;"></video>
            <canvas id="canvas" style="display:none;"></canvas>
            <button id="takePhotoButton" class="btn btn-secondary mt-2" onclick="takePhoto()">Take Photo</button>
            <img id="photo-preview" src="#" alt="Photo Preview" style="display:none;" class="img-thumbnail mt-3">
            <input type="file" id="photo" name="photo" style="display: none;"> <!-- Hidden file input for form submission -->
        </div>
        <span id="photo-warning" style="color: red; font-size: 12px;"></span> <!-- Peringatan foto -->

        <!-- Share Location (Muncul jika divisi memerlukan) -->
        <div id="locationSection" class="form-group" style="display: none; margin-top: 15px;">
            <button class="form-control" onclick="getLocationNow()">Share Location</button>
            <p id="locationStatus"></p>
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">
            <span id="location-warning" style="color: red; font-size: 12px;"></span> <!-- Peringatan lokasi -->
        </div>

        <!-- Google reCAPTCHA -->
        <div id="captchaSection" style="margin-top: 15px;">
            <div class="g-recaptcha" data-sitekey="{{ config('captcha.sitekey') }}" 
            data-callback="onRecaptchaSuccess"
            data-expired-callback="onRecaptchaExpired"
            data-error-callback="onRecaptchaError">
        </div>
            <span id="captcha-warning" style="color: red; font-size: 12px;"></span> <!-- Pesan peringatan -->
        </div>

        <!-- Submit Button -->
        <button id="submitCheckin" class="btn btn-primary mt-4" onclick="onSubmit()">Submit Check-in</button>
    </div>
</div>
<audio id="checkinAudio" src="/audio/notification-sound.mp3" preload="auto"></audio>
<link rel="stylesheet" href="{{ asset('css/popup_backup.css') }}">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let intervalId; // Variabel global untuk menyimpan ID timer
    let intervalIdMap = {}; // Peta untuk menyimpan ID interval berdasarkan localId
    let recaptchaToken = '';

    // Fungsi untuk mendengarkan perubahan data check-in secara real-time
    function checkScheduledTime(scheduledTime, localId, entry) {   
        const currentTime = Math.floor(Date.now() / 1000);
        let times = parseInt("{{ config('services.checking_setting.duration') }}");
        
        const timeLeft = (scheduledTime + times) - currentTime;

        if (timeLeft > 0 && currentTime >= scheduledTime && entry.is_active) 
        {
            let showTimeCheckin = document.getElementById('show_time_checkin');

            showTimeCheckin.textContent = entry.scheduled_time;
            console.log("Show time: " + entry.local_id + " "+entry.is_active+" "+ entry.scheduled_time );
            
            showCheckinPopup(timeLeft, localId);
        }
    }

    function monitorCheckin() 
    {
        const userId = "{{ Auth::user()->id }}";
        const checkin = firebase.app().database("{{ config('services.firebase.service_database_checkin_url') }}");

        checkin.ref('employee_checkins/' + userId).on('value', (snapshot) => {
            const data = snapshot.val();

            // Hentikan interval sebelumnya
            if (intervalId) clearInterval(intervalId);

            
            if(data)
            {
                Object.keys(data).forEach((key) => 
                {
                    const entry = data[key];
                    if (entry && entry.local_id && entry.is_active) {
    
                        
                        const scheduledTimeStr = entry.scheduled_time;
                        const localId = entry.local_id;
                        const scheduledTime = new Date(scheduledTimeStr).getTime() / 1000;
                        const currentTime = Math.floor(Date.now() / 1000);
                        
                        let times = parseInt("{{ config('services.checking_setting.duration') }}");
                        
                        const timeLeft = (scheduledTime + times) - currentTime;
    
                        console.log("Show time: " + entry.local_id + " "+entry.is_active+" "+ entry.scheduled_time );
                        if (timeLeft) 
                        {
                            let showTimeCheckin = document.getElementById('show_time_checkin');
    
                            showTimeCheckin.textContent = entry.scheduled_time;
                            
                            showCheckinPopup(timeLeft, localId, entry.requires_photo, entry.requires_location);
                        }
                    }
                });
            }else
            {
                closePopup();
            }
        });
    }


    function monitorCheckinData() 
    {
        const userId = "{{ Auth::user()->id }}";
        const checkin = firebase.app().database("{{ config('services.firebase.service_database_checkin_url') }}");

        checkin.ref('employee_checkins/' + userId).on('value', (snapshot) => {
            const data = snapshot.val();

            Object.keys(intervalIdMap).forEach((localId) => 
            {
                clearInterval(intervalIdMap[localId]);
                delete intervalIdMap[localId]; // Hapus ID interval dari peta setelah dihentikan
            });

            Object.keys(data).forEach((key) => {
                const entry = data[key];

                console.log(entry.local_id +"  "+ entry.is_active + "  "+ entry.scheduled_time);
                if (entry && entry.local_id && entry.is_active) {

                    
                    const scheduledTimeStr = entry.scheduled_time;
                    const localId = entry.local_id;
                    const scheduledTime = new Date(scheduledTimeStr).getTime() / 1000;


                    // Cek waktu secara berkala setiap 6 detik
                    intervalIdMap[localId] = setInterval(() => {
                        checkScheduledTime(scheduledTime, localId, entry);
                    }, 1000);
                }
            });

            console.log("Data Updated");
            
        });
    }

    // Fungsi untuk menampilkan popup check-in
    function showCheckinPopup(remainingTime, localId, requiresPhoto = false, requiresLocation = false) {
        const popup = document.getElementById('checkinPopup');
        const overlay = document.getElementById('overlay');

        const photoInput = document.getElementById('photo');
        const photoSection = document.getElementById('photoSection');
        const locationSection = document.getElementById('locationSection');
        const locationButton = document.querySelector('#locationSection button');
        
        if (popup.style.display === 'flex') 
        {
            return; // Jika sudah terbuka, jangan mulai countdown baru
        }

        if (requiresPhoto) 
        {
            photoSection.style.display = 'block';
            photoInput.setAttribute('required', 'required');

            openCamera();
        } else {
            photoSection.style.display = 'none';
            photoInput.removeAttribute('required');
        }

        // Atur visibilitas dan required pada lokasi
        if (requiresLocation) {
            locationSection.style.display = 'block';
            locationButton.setAttribute('required', 'required');
        } else {
            locationSection.style.display = 'none';
            locationButton.removeAttribute('required');
        }

        if (popup) {
            popup.style.display = 'flex';
            popup.dataset.checkinId = localId;
        }
        
        if (overlay) {
            overlay.style.display = 'block';
        }   

        startCountdown(remainingTime, localId);
    }

    // Fungsi untuk memulai hitungan mundur
    function startCountdown(duration, localId) {
        let countdownEl = document.getElementById('countdown');
        let timer = duration;
        const audio = document.getElementById('checkinAudio'); // Referensi ke elemen audio

        // Hentikan timer jika sudah berjalan sebelumnya
        if (intervalId) clearInterval(intervalId);

        if (audio) 
        {
            audio.currentTime = 0; // Mulai dari awal
            audio.play().catch(error => {
                console.error("Audio playback failed:", error);
            });
        }
        // Mulai timer baru
        intervalId = setInterval(() => {
            timer--;
            countdownEl.textContent = timer;
            if (timer <= 0) {
                clearInterval(intervalId);
                closePopup();
            }
        }, 1000);
    }

    // Fungsi untuk menutup popup
    function closePopup() {
        const popup = document.getElementById('checkinPopup');
        const overlay = document.getElementById('overlay');
        
        popup.style.setProperty('display', 'none', 'important'); 

        if (intervalId) clearInterval(intervalId); // Pastikan interval countdown dihentikan
        updateStatus(); // Panggil fungsi untuk memperbarui status di server
    }

    // Fungsi untuk memperbarui status check-in di server (contoh)
    // function updateStatus() {
    //     // Logika untuk memperbarui status setelah check-in selesai
    //     console.log('Status check-in diperbarui.');
    // }

    // Memantau data saat halaman dimuat
    window.onload = monitorCheckin;
    // Buka popup dan aktifkan kamera secara otomatis

    // Fungsi untuk membuka kamera
    function openCamera() 
    {
        const video = document.getElementById('videoFeed');
        
        // Akses kamera dan tampilkan video feed
        navigator.mediaDevices.getUserMedia({ video: true })
            .then((stream) => {
                video.srcObject = stream;
            })
            .catch((err) => {
                console.error("Error accessing camera: ", err);
                alert("Could not access camera. Please allow camera access.");
            });
    }

    // Fungsi untuk mengambil foto saat tombol "Take Photo" ditekan
    function takePhoto() {
        const video = document.getElementById('videoFeed');
        const canvas = document.getElementById('canvas');
        const photoInput = document.getElementById('photo');
        const photoPreview = document.getElementById('photo-preview');
        
        // Ambil foto dari video feed
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const context = canvas.getContext('2d');
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Konversi canvas ke Blob dan tampilkan preview
        canvas.toBlob((blob) => {
            const file = new File([blob], "checkin_photo.jpg", { type: "image/jpeg" });

            // Simpan file ke input type="file" agar bisa dikirim bersama form
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            photoInput.files = dataTransfer.files;

            // Tampilkan preview gambar dan hentikan video feed
            photoPreview.src = URL.createObjectURL(blob);
            photoPreview.style.display = 'block';
            video.style.display = 'none';
            document.getElementById('takePhotoButton').style.display = 'none';

            // Hentikan stream kamera setelah foto diambil
            video.srcObject.getTracks().forEach(track => track.stop());
        }, "image/jpeg", 0.7);
    }

    function getLocationNow() 
   {
       // Reset pesan peringatan lokasi
       const locationWarning = document.getElementById('location-warning');
       if (locationWarning) {
           locationWarning.textContent = '';
           locationWarning.style.color = '';
       }

       // Cek apakah browser mendukung geolocation
       if (navigator.geolocation) {
           // Minta izin pengguna untuk mendapatkan lokasi saat ini
           navigator.geolocation.getCurrentPosition(
               function(position) {
                   // Jika lokasi berhasil didapatkan
                   document.getElementById('latitude').value = position.coords.latitude;
                   document.getElementById('longitude').value = position.coords.longitude;
                   document.getElementById('locationStatus').textContent = "Lokasi berhasil didapatkan!";
                   document.getElementById('locationStatus').style.color = 'green';
               },
               function(error) {
                   // Menangani kesalahan saat mendapatkan lokasi
                   switch (error.code) {
                       case error.PERMISSION_DENIED:
                           document.getElementById('locationStatus').textContent = "Akses lokasi ditolak. Silakan aktifkan izin lokasi di browser Anda.";
                           break;
                       case error.POSITION_UNAVAILABLE:
                           document.getElementById('locationStatus').textContent = "Informasi lokasi tidak tersedia.";
                           break;
                       case error.TIMEOUT:
                           document.getElementById('locationStatus').textContent = "Permintaan lokasi melebihi batas waktu.";
                           break;
                       case error.UNKNOWN_ERROR:
                           document.getElementById('locationStatus').textContent = "Terjadi kesalahan tidak diketahui.";
                           break;
                   }
                   document.getElementById('locationStatus').style.color = 'red';
               }
           );
       } else {
           // Jika geolocation tidak didukung oleh browser
           document.getElementById('locationStatus').textContent = "Geolocation tidak didukung oleh browser ini.";
           document.getElementById('locationStatus').style.color = 'red';
       }
   }

   function onRecaptchaSuccess(token) 
    {
        recaptchaToken = token;
        document.getElementById('captcha-warning').textContent = ''; // Reset pesan peringatan
    }

    // Fungsi callback yang dipanggil saat reCAPTCHA token kadaluarsa
    function onRecaptchaExpired() {
        recaptchaToken = ''; // Hapus token
        document.getElementById('captcha-warning').textContent = 'Captcha telah kadaluarsa. Silakan ulangi lagi.';
    }

    // Fungsi callback yang dipanggil jika ada error pada reCAPTCHA
    function onRecaptchaError() {
        document.getElementById('captcha-warning').textContent = 'Terjadi kesalahan pada reCAPTCHA. Silakan coba lagi.';
    }

    function onSubmit() 
    {
        // Ambil elemen input foto, lokasi, dan elemen untuk menampilkan peringatan
        const photoInput = document.getElementById('photo');
        const latitude = document.getElementById('latitude').value;
        const longitude = document.getElementById('longitude').value;
        const photoWarning = document.getElementById('photo-warning');
        const locationWarning = document.getElementById('location-warning');
        const recaptchaToken = grecaptcha.getResponse();

        // Reset pesan peringatan
        if (photoWarning) photoWarning.textContent = '';
        if (locationWarning) locationWarning.textContent = '';

        // Validasi input foto jika diperlukan
        if (photoInput.hasAttribute('required') && !photoInput.files[0]) {
            if (photoWarning) {
                photoWarning.textContent = 'Foto diperlukan sebelum melakukan check-in.';
                photoWarning.style.color = 'red';
            }
            return; // Hentikan eksekusi jika foto belum diambil
        }

        // Validasi lokasi jika diperlukan
        if (document.getElementById('locationSection').style.display === 'block' && (!latitude || !longitude)) {
            if (locationWarning) {
                locationWarning.textContent = 'Lokasi diperlukan sebelum melakukan check-in.';
                locationWarning.style.color = 'red';
            }
            return; // Hentikan eksekusi jika lokasi belum diambil
        }

        if (!recaptchaToken) 
        {
            // Tampilkan pesan peringatan di halaman
            const captchaWarning = document.getElementById('captcha-warning');
            if (captchaWarning) {
                captchaWarning.textContent = 'Captcha belum terverifikasi. Silakan selesaikan reCAPTCHA sebelum melakukan check-in.';
                captchaWarning.style.color = 'red';
            }
            return; // Hentikan eksekusi jika reCAPTCHA belum terverifikasi
        }

        // Meminta reCAPTCHA token sebelum submit
        submitCheckin(recaptchaToken);
    }



    function submitCheckin(recaptchaToken) 
    {
        const localId = document.getElementById('checkinPopup').dataset.checkinId;
        const latitude = document.getElementById('latitude').value;
        const longitude = document.getElementById('longitude').value;
        const photo = document.getElementById('photo').files[0];
        const recaptcha = recaptchaToken;
        const storedToken = localStorage.getItem('fcm_token');
        
        // Prepare FormData for AJAX request
        let formData = new FormData();
        formData.append('latitude', latitude);
        formData.append('longitude', longitude);
        formData.append('recaptcha', recaptcha);
        formData.append('fcm_token', storedToken);
        formData.append('_method', 'PUT');
        
        if (photo) {
            formData.append('photo', photo);
        }
        let url = "{{ route('employee-checking.update',':id') }}";
        url = url.replace(":id",localId)
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function (response) {
                if (intervalId) clearInterval(intervalId); // Hentikan timer
                closePopup(); // Tutup popup

                Swal.fire({
                    icon: 'success',
                    title: 'Check-in successful!',
                    text: 'Your check-in has been successfully submitted.',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                })
            },
            error: function (xhr, status, error) {
                // console.log('Error:', error);              // Error umum (e.g., "Internal Server Error")
                // console.log('Status:', status);            // Status text (e.g., "error")
                console.log('Response Text:', xhr.responseText); // Response lengkap dari server
                // alert('Failed to submit check-in.');
                if (intervalId) clearInterval(intervalId); // Hentikan timer
                closePopup(); // Tutup popup setelah alert sukses

                Swal.fire({
                    icon: 'error',
                    title: 'Failed to submit check-in',
                    text: xhr.responseText, // Display server response in alert
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }
        });
    }

    function updateStatus() 
    {
        const localId = document.getElementById('checkinPopup').dataset.checkinId;
        
        if(!localId) return;
        let url = "{{ route('employee-checking.updatestatus',':id') }}";
        url = url.replace(":id",localId)
        
        let formData = new FormData();
        formData.append('_method', 'PUT');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function (response) {
                location.reload();
            }
        });
    }
</script>

<script>
    function compressAndPreviewImageCheckin() 
    {
        const fileInput = document.getElementById('photo');
        const preview = document.getElementById('photo-preview');

        if (!fileInput.files[0]) {
            preview.src = "";
            return;
        }

        const reader = new FileReader();
        reader.readAsDataURL(fileInput.files[0]);
        reader.onload = function (event) {
            const imgElement = document.createElement("img");
            imgElement.src = event.target.result;
            imgElement.onload = function (e) {
                const canvas = document.createElement("canvas");
                const MAX_WIDTH = 150;

                const scaleSize = MAX_WIDTH / e.target.width;
                canvas.width = MAX_WIDTH;
                canvas.height = e.target.height * scaleSize;

                const ctx = canvas.getContext("2d");
                ctx.drawImage(e.target, 0, 0, canvas.width, canvas.height);
                ctx.canvas.toBlob((blob) => {
                    const file = new File([blob], "compressed_image.jpg", {
                        type: 'image/jpeg',
                        quality: 0.8 // Lowering the quality to reduce file size
                    });

                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;

                    const reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onloadend = function () {
                        preview.src = reader.result;
                        preview.style.display = 'block';
                    }
                }, 'image/jpeg', 0.6);
            }
        }
    }
</script>
@endcanAccess