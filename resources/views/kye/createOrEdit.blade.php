@extends('adminlte::page')

@section('title', 'Aktivasi KYE')

@section('content_header')
<h3>Aktivasi KYE</h3>
@stop

@section('content')
<div class="col-md-12">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
<div class="card">
    <div class="card-body">
        @canAccess('create','kyes')
        @canAccess('edit','kyes')
        <form action="{{ @$kye ? route('kye.update', @$kye->id) : route('kye.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @if(@$kye) @method('PUT') @endif

            {{-- Identitas Karyawan --}}
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-id-card"></i> Identitas Karyawan</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="full_name"><i class="fas fa-user"></i> Nama Lengkap</label>
                        <input type="text" name="full_name" id="full_name" class="form-control"
                            placeholder="Masukkan nama lengkap" value="{{ @$kye->full_name ?? old('full_name') }}" required>
                    </div>
    
                    <div class="form-group">
                        <label for="birth_date"><i class="fas fa-birthday-cake"></i> Tempat & Tanggal Lahir</label>
                        <div class="input-group">
                            <input type="text" name="birth_place" id="birth_place" class="form-control"
                                placeholder="Tempat Lahir" value="{{ @$kye->birth_place ?? old('birth_place') }}" required>
                            <input type="date" name="birth_date" id="birth_date" class="form-control"
                                value="{{ @$kye->birth_date ? Carbon\Carbon::parse(@$kye->birth_date)->format('Y-m-d') : old('birth_date') }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address"><i class="fas fa-map-marker-alt"></i> Alamat Tempat Tinggal</label>
                        <textarea name="address" id="address" rows="3" class="form-control"
                            placeholder="Masukkan alamat lengkap" required>{{ @$kye->address ?? old('address') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="employee_photo"><i class="fas fa-camera"></i> Foto Karyawan</label>
                        <input type="hidden" name="employee_photo" id="employee_photo">
                        <div class="d-flex align-items-center mt-2">
                            <button type="button" id="employee_photo_btn" class="btn btn-outline-primary btn-sm me-2"
                                onclick="openCamera('employee_photo', 'employee_photo_preview'); this.disabled = true;">
                                <i class="fas fa-camera"></i> Ambil Foto
                            </button>
                            <div id="employee_photo_preview" class="ms-2">
                                @if(@$kye->employee_photo)
                                <img src="{{ asset('storage/' . @$kye->employee_photo) }}" alt="Employee Photo"
                                    class="img-thumbnail" width="100">
                                @else
                                <p class="text-muted mb-0"><i class="fas fa-image"></i> Belum ada foto.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ktp_number"><i class="fas fa-id-card"></i> Nomor KTP/Paspor</label>
                        <input type="number" name="ktp_number" id="ktp_number" class="form-control"
                            placeholder="Masukkan nomor KTP" value="{{ @$kye->ktp_number ?? old('ktp_number') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="ktp_photo"><i class="fas fa-id-card"></i> Foto KTP</label>
                        <input type="hidden" name="ktp_photo" id="ktp_photo">
                        <div class="d-flex align-items-center mt-2">
                            <button type="button" id="ktp_photo_btn" class="btn btn-outline-primary btn-sm me-2"
                                onclick="openCamera('ktp_photo', 'ktp_photo_preview'); this.disabled = true;">
                                <i class="fas fa-camera"></i> Ambil Foto
                            </button>
                            <div id="ktp_photo_preview" class="ms-2">
                                @if(@$kye->ktp_photo)
                                <img src="{{ asset('storage/' . @$kye->ktp_photo) }}" alt="KTP Photo" class="img-thumbnail"
                                    width="100">
                                @else
                                <p class="text-muted mb-0"><i class="fas fa-image"></i> Belum ada foto.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="selfie_ktp">
                            <i class="fas fa-camera"></i> Selfie dengan KTP
                        </label>
                        <input type="hidden" name="selfie_ktp" id="selfie_ktp">
                        <div class="d-flex align-items-center mt-2">
                            <button type="button" id="selfie_ktp_btn" class="btn btn-outline-primary btn-sm me-3"
                                onclick="openCamera('selfie_ktp', 'selfie_ktp_preview'); this.disabled = true;">
                                <i class="fas fa-camera"></i> Ambil Selfie
                            </button>
                            <div id="selfie_ktp_preview">
                                @if(@$kye->selfie_ktp)
                                <img src="{{ asset('storage/' . @$kye->selfie_ktp) }}" alt="Selfie KTP" class="img-thumbnail"
                                    width="120">
                                @else
                                <p class="text-muted mb-0"><i class="fas fa-image"></i> Belum ada foto.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ktp_family">
                            <i class="fas fa-id-card"></i> Foto KTP Orang Tua/Saudara
                        </label>
                        <input type="hidden" name="ktp_family" id="ktp_family">
                        <div class="d-flex align-items-center mt-2">
                            <button type="button" id="ktp_family_btn" class="btn btn-outline-primary btn-sm me-3"
                                onclick="openCamera('ktp_family', 'ktp_family_preview'); this.disabled = true;">
                                <i class="fas fa-camera"></i> Ambil Foto
                            </button>
                            <div id="ktp_family_preview">
                                @if(@$kye->ktp_family)
                                <img src="{{ asset('storage/' . @$kye->ktp_family) }}" alt="KTP Orang Tua/Saudara"
                                    class="img-thumbnail mt-2" width="120">
                                @else
                                <p class="text-muted mb-0"><i class="fas fa-image"></i> Belum ada foto.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="npwp_number"><i class="fas fa-file-alt"></i> Nomor NPWP</label>
                        <input type="number" name="npwp_number" id="npwp_number" class="form-control"
                            placeholder="Masukkan nomor NPWP" value="{{ @$kye->npwp_number ?? old('npwp_number') }}"
                            {{ @$kye ? '' : 'required' }}
                            >
                    </div>

                    <div class="form-group">
                        <label for="google_maps"><i class="fas fa-map-marker-alt"></i> Shareloc Google Maps Rumah</label>
                        <div class="input-group">
                            <input type="text" name="google_maps" id="google_maps" class="form-control"
                                placeholder="Masukkan lokasi" value="{{ @$kye->google_maps ?? old('google_maps') }}">
                            <button type="button" class="btn btn-outline-secondary" onclick="fetchLocation()">
                                <i class="fas fa-map-marker-alt"></i> Ambil Lokasi
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="house_photo"><i class="fas fa-home"></i> Foto Rumah Saat Ini</label>
                        <input type="hidden" name="house_photo" id="house_photo">
                        <div class="d-flex align-items-center mt-2">
                            <button type="button" id="house_photo_btn" class="btn btn-outline-primary btn-sm me-2"
                                onclick="openCamera('house_photo', 'house_photo_preview'); this.disabled = true;">
                                <i class="fas fa-camera"></i> Ambil Foto Rumah
                            </button>
                            <div id="house_photo_preview" class="ms-2">
                                @if(@$kye->house_photo)
                                <img src="{{ asset('storage/' . @$kye->house_photo) }}" alt="House Photo"
                                    class="img-thumbnail" width="100">
                                @else
                                <p class="text-muted mb-0"><i class="fas fa-image"></i> Belum ada foto.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="skck"><i class="fas fa-file-alt"></i> Surat Keterangan Catatan Kepolisian (SKCK)</label>
                            <input type="file" name="skck" id="skck" class="form-control-file" {{ @$kye ? '' : 'required' }}
                                accept=".pdf,.jpeg,.jpg,.png,.gif,.bmp,.tiff,.svg">
                    </div>
                </div>
            </div>

            {{-- Informasi Kontak --}}
            <div class="card mt-4">
                <div class="card-header bg-success text-white">
                    <h5><i class="fas fa-address-book"></i> Informasi Kontak</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="phone_number"><i class="fas fa-phone-alt"></i> Nomor Telepon</label>
                        <input type="tel" name="phone_number" id="phone_number" class="form-control" 
                            placeholder="Masukkan nomor telepon" value="{{ @$kye->phone_number ?? old('phone_number') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" id="email" class="form-control" 
                            placeholder="Masukkan email" value="{{ @$kye->email ?? old('email') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="imei_number"><i class="fas fa-mobile-alt"></i> Kode IMEI HP</label>
                        <input type="text" name="imei_number" id="imei_number" class="form-control" 
                            placeholder="Masukkan kode IMEI HP" value="{{ @$kye->imei_number ?? old('imei_number') }}">
                    </div>

                    <div class="form-group">
                        <label for="emergency_phone"><i class="fas fa-phone"></i> No. Telepon Darurat</label>
                        <input type="tel" name="emergency_phone" id="emergency_phone" class="form-control" 
                            placeholder="Masukkan nomor telepon darurat" value="{{ @$kye->emergency_phone ?? old('emergency_phone') }}">
                    </div>

                    <div class="form-group">
                        <label for="emergency_contact"><i class="fas fa-user-friends"></i> Nama Contact Darurat</label>
                        <input type="text" name="emergency_contact" id="emergency_contact" class="form-control" 
                            placeholder="Masukkan nama kontak darurat" value="{{ @$kye->emergency_contact ?? old('emergency_contact') }}">
                    </div>

                    <div class="form-group">
                        <label for="bank_account_name"><i class="fas fa-university"></i> Nama Account Bank</label>
                        <input type="text" name="bank_account_name" id="bank_account_name" class="form-control" 
                            placeholder="Masukkan nama pemilik rekening bank" value="{{ @$kye->bank_account_name ?? old('bank_account_name') }}">
                    </div>

                    <div class="form-group">
                        <label for="bank_name"><i class="fas fa-money-check-alt"></i> Nama Bank</label>
                        <input type="text" name="bank_name" id="bank_name" class="form-control" 
                            placeholder="Masukkan nama bank" value="{{ @$kye->bank_name ?? old('bank_name') }}">
                    </div>

                    <div class="form-group">
                        <label for="account_number"><i class="fas fa-credit-card"></i> No. Rekening</label>
                        <input type="text" name="account_number" id="account_number" class="form-control" 
                            placeholder="Masukkan nomor rekening" value="{{ @$kye->account_number ?? old('account_number') }}">
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" id="submitBtn" class="btn btn-primary">
                    <i class="fas fa-save"></i> Submit
                </button>
            </div>
        </form>
        @endcanAccess
        @endcanAccess
    </div>
</div>
@stop
@section('js')
@if(!@$kye)
<script>
    document.getElementById('submitBtn').addEventListener('click', function(event) {
        // Ambil elemen input foto
        const employeePhoto = document.getElementById('employee_photo').value;
        const ktpPhoto = document.getElementById('ktp_photo').value;
        const selfieKtp = document.getElementById('selfie_ktp').value;
        const ktpFamily = document.getElementById('ktp_family').value;
        const housePhoto = document.getElementById('house_photo').value;

        // Validasi jika foto wajib belum diisi
        if (!employeePhoto) {
            alert('Foto Karyawan wajib diunggah!');
            event.preventDefault(); // Cegah formulir dikirim
            return false;
        }

        if (!ktpPhoto) {
            alert('Foto KTP wajib diunggah!');
            event.preventDefault(); // Cegah formulir dikirim
            return false;
        }

        if (!selfieKtp) {
            alert('Foto Selfie KTP wajib diunggah!');
            event.preventDefault(); // Cegah formulir dikirim
            return false;
        }

        if (!ktpFamily) 
        {
            alert('Foto KTP Orang Tua wajib diunggah!');
            event.preventDefault(); // Cegah formulir dikirim
            return false;
        }

        if (!housePhoto) {
            alert('Foto Rumah Saat Ini wajib diunggah!');
            event.preventDefault(); // Cegah formulir dikirim
            return false;
        }


    });
</script>
@endif
<script>
let currentStream;

// Open camera and capture photo
function openCamera(inputId, previewId) {
    const constraints = {
        video: {
            facingMode: "environment"
        }, // Default to back camera
        audio: false,
    };

    navigator.mediaDevices
        .getUserMedia(constraints)
        .then((stream) => {
            currentStream = stream;

            // Create modal for camera preview and photo capture
            const cameraModal = document.createElement("div");
            cameraModal.id = "cameraModal";
            cameraModal.innerHTML = `
                <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Ambil Foto</h5>
                                <button type="button" class="btn-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
                            </div>
                            <div class="modal-body text-center">
                                <video id="videoStream" autoplay style="width: 100%; height: auto;"></video>
                                <button class="btn btn-primary mt-3" onclick="capturePhoto('${inputId}', '${previewId}')">Ambil Foto</button>
                                <button class="btn btn-secondary mt-3" onclick="switchCamera()">Ganti Kamera</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(cameraModal);
            document.getElementById(inputId + '_btn').disabled = false;
            
            // Start video stream
            const video = document.getElementById("videoStream");
            video.srcObject = stream;
        })
        .catch((error) => alert("Error membuka kamera: " + error.message));
}

// Switch between front and back cameras
function switchCamera() {
    const video = document.getElementById("videoStream");
    const currentFacingMode = video.srcObject.getVideoTracks()[0].getSettings().facingMode;
    const newFacingMode = currentFacingMode === "environment" ? "user" : "environment";

    if (currentStream) {
        currentStream.getTracks().forEach((track) => track.stop());
    }

    navigator.mediaDevices
        .getUserMedia({
            video: {
                facingMode: newFacingMode
            }
        })
        .then((stream) => {
            currentStream = stream;
            video.srcObject = stream;
        })
        .catch((error) => alert("Error mengganti kamera: " + error.message));
}

// Capture photo and display preview
function capturePhoto(inputId, previewId) {
    const video = document.getElementById("videoStream");
    const canvas = document.createElement("canvas");
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    // Draw video frame to canvas
    canvas.getContext("2d").drawImage(video, 0, 0);

    // Get base64 image data and set to hidden input
    const photoData = canvas.toDataURL("image/png");
    document.getElementById(inputId).value = photoData;

    // Display image preview
    const previewContainer = document.getElementById(previewId);
    previewContainer.innerHTML = `<img src="${photoData}" alt="Captured Photo" class="img-thumbnail mt-2" width="150">`;

    // Stop camera and close modal
    closeModal();
}

// Close the camera modal and stop the video stream
function closeModal() {
    if (currentStream) {
        currentStream.getTracks().forEach((track) => track.stop());
    }
    const cameraModal = document.getElementById("cameraModal");
    if (cameraModal) {
        cameraModal.remove();
    }
}

function fetchLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            position => {
                const {
                    latitude,
                    longitude
                } = position.coords;
                document.getElementById('google_maps').value = `${latitude},${longitude}`;
            },
            error => alert('Lokasi tidak ditemukan: ' + error.message)
        );
    } else {
        alert('Browser tidak mendukung geolocation.');
    }
}
</script>
@stop