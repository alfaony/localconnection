@extends('adminlte::page')

@section('title', 'Aktivasi KYE')

@section('content_header')
<h3>Aktivasi KYE</h3>
@stop

@section('css')
<style>
    /* Stepper Styles */
    .stepper-wrapper {
        margin: 30px auto;
        display: flex;
        justify-content: space-between;
        position: relative;
    }
    
    .stepper-wrapper::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 3px;
        background: #e0e0e0;
        z-index: 0;
    }
    
    .stepper-item {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        z-index: 1;
    }
    
    .stepper-item::before {
        content: attr(data-step);
        display: flex;
        justify-content: center;
        align-items: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e0e0e0;
        color: #666;
        font-weight: bold;
        margin-bottom: 8px;
        transition: all 0.3s ease;
        border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .stepper-item.active::before {
        background: #007bff;
        color: white;
        transform: scale(1.1);
    }
    
    .stepper-item.completed::before {
        background: #28a745;
        color: white;
        content: '✓';
    }
    
    .step-name {
        font-size: 13px;
        color: #666;
        text-align: center;
        margin-top: 5px;
        font-weight: 500;
    }
    
    .stepper-item.active .step-name {
        color: #007bff;
        font-weight: 600;
    }
    
    .stepper-item.completed .step-name {
        color: #28a745;
    }
    
    /* Form Step Styles */
    .form-step {
        display: none;
        animation: fadeIn 0.3s ease;
    }
    
    .form-step.active {
        display: block;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Navigation Buttons */
    .step-navigation {
        display: flex;
        justify-content: space-between;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #e9ecef;
    }
    
    /* Card Improvements */
    .form-card {
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border-radius: 10px;
        overflow: hidden;
    }
    
    .form-card .card-header {
        border-bottom: 3px solid rgba(255,255,255,0.3);
        padding: 15px 20px;
    }
    
    .form-card .card-body {
        padding: 25px;
    }
    
    /* Form Group Improvements */
    .form-group label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }
    
    .form-group label i {
        margin-right: 8px;
        color: #6c757d;
    }
    
    .form-control, .form-control-file {
        border-radius: 6px;
        border: 1.5px solid #ced4da;
        /* padding: 10px 15px; */
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
    }
    
    /* Photo Preview Improvements */
    .photo-preview-container {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-top: 10px;
    }
    
    .photo-preview-box {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 10px;
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
    }
    
    .photo-preview-box img {
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    /* Button Improvements */
    .btn {
        border-radius: 6px;
        padding: 10px 24px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,123,255,0.4);
    }
    
    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        border: none;
    }
    
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40,167,69,0.4);
    }
    
    /* Progress Bar */
    .progress-container {
        margin-bottom: 30px;
    }
    
    .progress {
        height: 8px;
        border-radius: 10px;
        background: #e9ecef;
    }
    
    .progress-bar {
        border-radius: 10px;
        transition: width 0.4s ease;
        background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .stepper-wrapper {
            flex-direction: column;
        }
        
        .stepper-wrapper::before {
            width: 3px;
            height: 100%;
            left: 20px;
            top: 0;
        }
        
        .stepper-item {
            flex-direction: row;
            justify-content: flex-start;
            margin-bottom: 20px;
        }
        
        .step-name {
            margin-left: 15px;
            margin-top: 0;
            text-align: left;
        }
    }
    
    /* Alert Improvements */
    .alert {
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    /* Map Preview */
    #map-preview iframe {
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>
@stop

@section('content')
<div class="container-fluid">
    <div class="col-md-12">
        @include('components.alert')
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="card form-card">
        <div class="card-body">
            <!-- Progress Bar -->
            <div class="progress-container">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 16.66%;" id="progressBar"></div>
                </div>
                <small class="text-muted mt-2 d-block">Step <span id="currentStepNum">1</span> of 6</small>
            </div>

            <!-- Stepper -->
            <div class="stepper-wrapper">
                <div class="stepper-item active" data-step="1">
                    <span class="step-name">Data Pribadi</span>
                </div>
                <div class="stepper-item" data-step="2">
                    <span class="step-name">Dokumen KTP</span>
                </div>
                <div class="stepper-item" data-step="3">
                    <span class="step-name">Dokumen Lainnya</span>
                </div>
                <div class="stepper-item" data-step="4">
                    <span class="step-name">Alamat & Lokasi</span>
                </div>
                <div class="stepper-item" data-step="5">
                    <span class="step-name">Kontak & Bank</span>
                </div>
                <div class="stepper-item" data-step="6">
                    <span class="step-name">Review</span>
                </div>
            </div>

            @canAccess('create','kyes')
            @canAccess('edit','kyes')
            <form action="{{ @$kye ? route('kye.update', @$kye->id) : route('kye.store') }}" method="POST" enctype="multipart/form-data" id="kyeForm">
                @csrf
                @if(@$kye) @method('PUT') @endif

                <!-- STEP 1: Data Pribadi -->
                <div class="form-step active" data-step="1">
                    <div class="card border-0">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-user"></i> Data Pribadi</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="full_name"><i class="fas fa-user"></i> Nama Lengkap</label>
                                        <input type="text" name="full_name" id="full_name" class="form-control"
                                            placeholder="Masukkan nama lengkap" value="{{ @$kye->full_name ?? old('full_name') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="call_name"><i class="fas fa-signature"></i> Nama Panggilan</label>
                                        <input type="text" name="call_name" id="call_name" class="form-control"
                                            placeholder="Masukan nama panggilan" value="{{ @$kye->call_name ?? old('call_name') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="gender"><i class="fas fa-transgender"></i> Jenis Kelamin</label>
                                        <div class="d-flex gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="gender" id="male" value="male"
                                                    {{ @$kye->gender == 'male' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="male">Laki-laki</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="gender" id="female" value="female"
                                                    {{ @$kye->gender == 'female' ? 'checked' : '' }}>
                                                <label class="form-check-label" for="female">Perempuan</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="birth_place"><i class="fas fa-map-marker-alt"></i> Tempat Lahir</label>
                                        <input type="text" name="birth_place" id="birth_place" class="form-control"
                                            placeholder="Tempat Lahir" value="{{ @$kye->birth_place ?? old('birth_place') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="birth_date"><i class="fas fa-birthday-cake"></i> Tanggal Lahir</label>
                                        <input type="date" name="birth_date" id="birth_date" class="form-control"
                                            value="{{ @$kye->birth_date ? Carbon\Carbon::parse(@$kye->birth_date)->format('Y-m-d') : old('birth_date') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="marital_status"><i class="fas fa-ring"></i> Status Menikah</label>
                                        <select name="marital_status" id="marital_status" class="form-control">
                                            <option value="" selected disabled>Pilih Status Menikah</option>
                                            @foreach($maritalStatus as $key => $value)
                                            <option value="{{ $key }}" {{ (@$kye->marital_status ?? old('marital_status')) == $key ? 'selected' : '' }}>{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="number_of_children"><i class="fas fa-baby"></i> Jumlah Anak</label>
                                        <input type="number" name="number_of_children" id="number_of_children" class="form-control" 
                                            placeholder="Masukkan jumlah anak" value="{{ @$kye->number_of_children ?? old('number_of_children') ?? 0 }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="employee_photo"><i class="fas fa-camera"></i> Foto Karyawan</label>
                                <div class="photo-preview-container">
                                    <button type="button" id="employee_photo_btn" class="btn btn-outline-primary btn-sm"
                                        onclick="openCamera('employee_photo', 'employee_photo_preview'); this.disabled = true;">
                                        <i class="fas fa-camera"></i> Ambil Foto
                                    </button>
                                    <div id="employee_photo_preview" class="photo-preview-box">
                                        @if(@$kye->employee_photo)
                                        <img src="{{ asset('storage/' . @$kye->employee_photo) }}" alt="Employee Photo" class="img-thumbnail" width="100">
                                        @else
                                        <p class="text-muted mb-0"><i class="fas fa-image"></i> Belum ada foto</p>
                                        @endif
                                    </div>
                                </div>
                                <input type="hidden" name="employee_photo" id="employee_photo">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Dokumen KTP -->
                <div class="form-step" data-step="2">
                    <div class="card border-0">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-id-card"></i> Dokumen KTP</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="ktp_number"><i class="fas fa-id-card"></i> Nomor KTP/Paspor</label>
                                <input type="number" name="ktp_number" id="ktp_number" class="form-control"
                                    placeholder="Masukkan nomor KTP" value="{{ @$kye->ktp_number ?? old('ktp_number') }}" required>
                            </div>

                            <div class="form-group">
                                <label for="ktp_photo"><i class="fas fa-id-card"></i> Foto KTP</label>
                                <div class="photo-preview-container">
                                    <button type="button" id="ktp_photo_btn" class="btn btn-outline-primary btn-sm"
                                        onclick="openCamera('ktp_photo', 'ktp_photo_preview'); this.disabled = true;">
                                        <i class="fas fa-camera"></i> Ambil Foto
                                    </button>
                                    <div id="ktp_photo_preview" class="photo-preview-box">
                                        @if(@$kye->ktp_photo)
                                        <img src="{{ asset('storage/' . @$kye->ktp_photo) }}" alt="KTP Photo" class="img-thumbnail" width="100">
                                        @else
                                        <p class="text-muted mb-0"><i class="fas fa-image"></i> Belum ada foto</p>
                                        @endif
                                    </div>
                                </div>
                                <input type="hidden" name="ktp_photo" id="ktp_photo">
                            </div>

                            <div class="form-group">
                                <label for="selfie_ktp"><i class="fas fa-camera"></i> Selfie dengan KTP</label>
                                <div class="photo-preview-container">
                                    <button type="button" id="selfie_ktp_btn" class="btn btn-outline-primary btn-sm"
                                        onclick="openCamera('selfie_ktp', 'selfie_ktp_preview'); this.disabled = true;">
                                        <i class="fas fa-camera"></i> Ambil Selfie
                                    </button>
                                    <div id="selfie_ktp_preview" class="photo-preview-box">
                                        @if(@$kye->selfie_ktp)
                                        <img src="{{ asset('storage/' . @$kye->selfie_ktp) }}" alt="Selfie KTP" class="img-thumbnail" width="120">
                                        @else
                                        <p class="text-muted mb-0"><i class="fas fa-image"></i> Belum ada foto</p>
                                        @endif
                                    </div>
                                </div>
                                <input type="hidden" name="selfie_ktp" id="selfie_ktp">
                            </div>

                            <div class="form-group">
                                <label for="ktp_family"><i class="fas fa-id-card"></i> Foto KTP Orang Tua/Saudara</label>
                                <input type="file" name="ktp_family" id="ktp_family" class="form-control-file"
                                    accept=".jpeg,.jpg,.png" onchange="previewImage('ktp_family', 'ktp_family_preview')">
                                <div class="photo-preview-container">
                                    <div id="ktp_family_preview" class="photo-preview-box">
                                        @if(@$kye->ktp_family)
                                        <img src="{{ asset('storage/' . @$kye->ktp_family) }}" alt="KTP Keluarga" class="img-thumbnail" width="120">
                                        @else
                                        <p class="text-muted mb-0"><i class="fas fa-image"></i> Belum ada foto</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Dokumen Lainnya -->
                <div class="form-step" data-step="3">
                    <div class="card border-0">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Dokumen Lainnya</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="npwp_number"><i class="fas fa-file-alt"></i> Nomor NPWP</label>
                                <input type="number" name="npwp_number" id="npwp_number" class="form-control"
                                    placeholder="Masukkan nomor NPWP (Opsional)" value="{{ @$kye->npwp_number ?? old('npwp_number') }}">
                            </div>

                            <div class="form-group">
                                <label for="npwp_photo"><i class="fas fa-id-card"></i> Foto Scan NPWP</label>
                                <input type="file" name="npwp_photo" id="npwp_photo" class="form-control-file"
                                    accept=".jpeg,.jpg,.png" onchange="previewImage('npwp_photo', 'npwp_photo_preview')">
                                <div class="photo-preview-container">
                                    <div id="npwp_photo_preview" class="photo-preview-box">
                                        @if(@$kye->npwp_photo)
                                        <img src="{{ asset('storage/' . @$kye->npwp_photo) }}" alt="NPWP" class="img-thumbnail" width="120">
                                        @else
                                        <p class="text-muted mb-0"><i class="fas fa-image"></i> Belum ada foto</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="skck"><i class="fas fa-file-alt"></i> Surat Keterangan Catatan Kepolisian (SKCK)</label>
                                <input type="file" name="skck" id="skck" class="form-control-file"
                                    accept=".pdf,.jpeg,.jpg,.png,.gif,.bmp,.tiff,.svg">
                                <small class="form-text text-muted">Format: PDF atau gambar</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Alamat & Lokasi -->
                <div class="form-step" data-step="4">
                    <div class="card border-0">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Alamat & Lokasi</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="address_domisili"><i class="fas fa-map-marker-alt"></i> Alamat Domisili</label>
                                <textarea name="address_domisili" id="address_domisili" rows="3" class="form-control"
                                    placeholder="Masukkan alamat domisili lengkap">{{ @$kye->address_domisili ?? old('address_domisili') }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="address"><i class="fas fa-home"></i> Alamat Tempat Tinggal</label>
                                <textarea name="address" id="address" rows="3" class="form-control"
                                    placeholder="Masukkan alamat tempat tinggal lengkap" required>{{ @$kye->address ?? old('address') }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="google_maps"><i class="fas fa-map-marker-alt"></i> Lokasi Rumah (Google Maps)</label>
                                <div class="input-group mb-3">
                                    <input type="text" name="google_maps" id="google_maps" class="form-control"
                                        placeholder="Latitude, Longitude atau URL Google Maps" 
                                        value="{{ @$kye->google_maps ?? old('google_maps') }}">
                                    <button type="button" class="btn btn-outline-secondary" onclick="fetchLocation()">
                                        <i class="fas fa-location-arrow"></i> Ambil Lokasi
                                    </button>
                                </div>
                                <div id="map-preview" class="mt-3" style="display: none;">
                                    <iframe id="google-maps-iframe" width="100%" height="300" frameborder="0" style="border:0"
                                        src="" allowfullscreen></iframe>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="house_photo"><i class="fas fa-home"></i> Foto Rumah Saat Ini</label>
                                <input type="file" name="house_photo" id="house_photo" class="form-control-file"
                                    accept=".jpeg,.jpg,.png" onchange="previewImage('house_photo', 'house_photo_preview')">
                                <div class="photo-preview-container">
                                    <div id="house_photo_preview" class="photo-preview-box">
                                        @if(@$kye->house_photo)
                                        <img src="{{ asset('storage/' . @$kye->house_photo) }}" alt="Foto Rumah" class="img-thumbnail" width="120">
                                        @else
                                        <p class="text-muted mb-0"><i class="fas fa-image"></i> Belum ada foto</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 5: Kontak & Bank -->
                <div class="form-step" data-step="5">
                    <div class="card border-0">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-address-book"></i> Informasi Kontak & Bank</h5>
                        </div>
                        <div class="card-body">
                            <h6 class="border-bottom pb-2 mb-3"><i class="fas fa-phone"></i> Kontak</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone_number"><i class="fas fa-phone-alt"></i> Nomor Telepon</label>
                                        <input type="tel" name="phone_number" id="phone_number" class="form-control" 
                                            placeholder="Contoh: 081234567890" value="{{ @$kye->phone_number ?? old('phone_number') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email"><i class="fas fa-envelope"></i> Email Pribadi</label>
                                        <input type="email" name="email" id="email" class="form-control" 
                                            placeholder="contoh@email.com" value="{{ @$kye->email ?? old('email') }}" required
                                            oninput="verifyEmail(this.value)">
                                        <input type="hidden" id="current_user_id" value="{{ @$kye->id }}">
                                        <small id="email-error" class="text-danger d-none"></small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="imei_number"><i class="fas fa-mobile-alt"></i> Kode IMEI HP</label>
                                <input type="text" name="imei_number" id="imei_number" class="form-control" 
                                    placeholder="Ketik *#06# untuk melihat IMEI" value="{{ @$kye->imei_number ?? old('imei_number') }}" required>
                            </div>

                            <h6 class="border-bottom pb-2 mb-3 mt-4"><i class="fas fa-user-shield"></i> Kontak Darurat</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="emergency_contact"><i class="fas fa-user-friends"></i> Nama Kontak Darurat</label>
                                        <input type="text" name="emergency_contact" id="emergency_contact" class="form-control" 
                                            placeholder="Nama orang tua/keluarga" value="{{ @$kye->emergency_contact ?? old('emergency_contact') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="emergency_phone"><i class="fas fa-phone"></i> No. Telepon Darurat</label>
                                        <input type="tel" name="emergency_phone" id="emergency_phone" class="form-control" 
                                            placeholder="Contoh: 081234567890" value="{{ @$kye->emergency_phone ?? old('emergency_phone') }}" required>
                                    </div>
                                </div>
                            </div>

                            <h6 class="border-bottom pb-2 mb-3 mt-4"><i class="fas fa-university"></i> Informasi Bank</h6>
                            <div class="form-group">
                                <label for="bank_account_name"><i class="fas fa-user"></i> Nama Pemilik Rekening</label>
                                <input type="text" name="bank_account_name" id="bank_account_name" class="form-control" 
                                    placeholder="Sesuai dengan buku tabungan" value="{{ @$kye->bank_account_name ?? old('bank_account_name') }}" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bank_name"><i class="fas fa-money-check-alt"></i> Nama Bank</label>
                                        <input type="text" name="bank_name" id="bank_name" class="form-control" 
                                            placeholder="Contoh: BCA, Mandiri, BRI" value="{{ @$kye->bank_name ?? old('bank_name') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="account_number"><i class="fas fa-credit-card"></i> No. Rekening</label>
                                        <input type="text" name="account_number" id="account_number" class="form-control" 
                                            placeholder="Nomor rekening bank" value="{{ @$kye->account_number ?? old('account_number') }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 6: Review -->
                <div class="form-step" data-step="6">
                    <div class="card border-0">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="fas fa-check-circle"></i> Review Data Anda</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Pastikan semua data yang Anda masukkan sudah benar sebelum submit.
                            </div>
                            
                            <div id="reviewSummary">
                                <!-- Review summary will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="step-navigation">
                    <button type="button" class="btn btn-secondary" id="prevBtn" onclick="changeStep(-1)" style="display: none;">
                        <i class="fas fa-arrow-left"></i> Sebelumnya
                    </button>
                    <div></div>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="changeStep(1)">
                        Selanjutnya <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                        <i class="fas fa-save"></i> Submit Data
                    </button>
                </div>
            </form>
            @endcanAccess
            @endcanAccess
        </div>
    </div>
</div>
@stop

@section('js')
<script>
let currentStep = 1;
const totalSteps = 6;

function updateStepper() {
    // Update stepper items
    document.querySelectorAll('.stepper-item').forEach((item, index) => {
        item.classList.remove('active', 'completed');
        if (index + 1 < currentStep) {
            item.classList.add('completed');
        } else if (index + 1 === currentStep) {
            item.classList.add('active');
        }
    });

    // Update form steps
    document.querySelectorAll('.form-step').forEach((step, index) => {
        step.classList.remove('active');
        if (index + 1 === currentStep) {
            step.classList.add('active');
        }
    });

    // Update progress bar
    const progress = (currentStep / totalSteps) * 100;
    document.getElementById('progressBar').style.width = progress + '%';
    document.getElementById('currentStepNum').textContent = currentStep;

    // Update buttons
    document.getElementById('prevBtn').style.display = currentStep === 1 ? 'none' : 'inline-block';
    document.getElementById('nextBtn').style.display = currentStep === totalSteps ? 'none' : 'inline-block';
    document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'inline-block' : 'none';

    // Populate review on last step
    if (currentStep === totalSteps) {
        populateReview();
    }

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function changeStep(direction) {
    // Validate current step before moving forward
    if (direction === 1 && !validateStep(currentStep)) {
        return;
    }

    currentStep += direction;
    if (currentStep < 1) currentStep = 1;
    if (currentStep > totalSteps) currentStep = totalSteps;

    updateStepper();
}

function validateStep(step) {
    const currentStepElement = document.querySelector(`.form-step[data-step="${step}"]`);
    const requiredFields = currentStepElement.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            field.classList.add('is-invalid');
            
            // Add validation feedback if not exists
            if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('invalid-feedback')) {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'Field ini wajib diisi';
                field.parentNode.insertBefore(feedback, field.nextSibling);
            }
        } else {
            field.classList.remove('is-invalid');
        }
    });

    if (!isValid) {
        alert('Mohon lengkapi semua field yang wajib diisi!');
    }

    return isValid;
}

function populateReview() {
    const reviewSummary = document.getElementById('reviewSummary');
    
    const fullName = document.getElementById('full_name').value;
    const email = document.getElementById('email').value;
    const phone = document.getElementById('phone_number').value;
    const ktpNumber = document.getElementById('ktp_number').value;
    const address = document.getElementById('address').value;
    const bankName = document.getElementById('bank_name').value;
    const accountNumber = document.getElementById('account_number').value;

    reviewSummary.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr><th width="40%">Nama Lengkap:</th><td>${fullName || '-'}</td></tr>
                    <tr><th>Email:</th><td>${email || '-'}</td></tr>
                    <tr><th>No. Telepon:</th><td>${phone || '-'}</td></tr>
                    <tr><th>No. KTP:</th><td>${ktpNumber || '-'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr><th width="40%">Alamat:</th><td>${address || '-'}</td></tr>
                    <tr><th>Nama Bank:</th><td>${bankName || '-'}</td></tr>
                    <tr><th>No. Rekening:</th><td>${accountNumber || '-'}</td></tr>
                </table>
            </div>
        </div>
    `;
}

// Initialize stepper on page load
document.addEventListener('DOMContentLoaded', function() {
    updateStepper();
});

// Image preview function
function previewImage(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" class="img-thumbnail" width="120">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Camera functions
let currentStream;

function openCamera(inputId, previewId) {
    const constraints = {
        video: { facingMode: "environment" },
        audio: false,
    };

    navigator.mediaDevices.getUserMedia(constraints)
        .then((stream) => {
            currentStream = stream;

            const cameraModal = document.createElement("div");
            cameraModal.id = "cameraModal";
            cameraModal.innerHTML = `
                <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.7);">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="fas fa-camera"></i> Ambil Foto</h5>
                                <button type="button" class="btn-close" onclick="closeModal()"></button>
                            </div>
                            <div class="modal-body text-center">
                                <video id="videoStream" autoplay playsinline style="width: 100%; max-height: 400px; border-radius: 8px;"></video>
                                <div class="mt-3">
                                    <button class="btn btn-primary me-2" onclick="capturePhoto('${inputId}', '${previewId}')">
                                        <i class="fas fa-camera"></i> Ambil Foto
                                    </button>
                                    <button class="btn btn-secondary" onclick="switchCamera()">
                                        <i class="fas fa-sync-alt"></i> Ganti Kamera
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(cameraModal);
            
            const video = document.getElementById("videoStream");
            video.srcObject = stream;
        })
        .catch((error) => {
            alert("Error membuka kamera: " + error.message);
            document.getElementById(inputId + '_btn').disabled = false;
        });
}

function switchCamera() {
    const video = document.getElementById("videoStream");
    const currentFacingMode = video.srcObject.getVideoTracks()[0].getSettings().facingMode;
    const newFacingMode = currentFacingMode === "environment" ? "user" : "environment";

    if (currentStream) {
        currentStream.getTracks().forEach((track) => track.stop());
    }

    navigator.mediaDevices.getUserMedia({ video: { facingMode: newFacingMode } })
        .then((stream) => {
            currentStream = stream;
            video.srcObject = stream;
        })
        .catch((error) => alert("Error mengganti kamera: " + error.message));
}

function capturePhoto(inputId, previewId) {
    const video = document.getElementById("videoStream");
    const canvas = document.createElement("canvas");
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;

    canvas.getContext("2d").drawImage(video, 0, 0);

    const photoData = canvas.toDataURL("image/png");
    document.getElementById(inputId).value = photoData;

    const previewContainer = document.getElementById(previewId);
    previewContainer.innerHTML = `<img src="${photoData}" alt="Captured Photo" class="img-thumbnail" width="150">`;

    closeModal();
}

function closeModal() {
    if (currentStream) {
        currentStream.getTracks().forEach((track) => track.stop());
    }
    const cameraModal = document.getElementById("cameraModal");
    if (cameraModal) {
        cameraModal.remove();
    }
}

// Location functions
function fetchLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const location = `${lat},${lng}`;

                document.getElementById('google_maps').value = location;
                showMapPreview(location);
            },
            (error) => {
                alert('Tidak dapat mengambil lokasi. Silakan coba lagi.');
            }
        );
    } else {
        alert('Geolocation tidak didukung di browser ini.');
    }
}

function showMapPreview(location) {
    const mapPreview = document.getElementById('map-preview');
    const mapIframe = document.getElementById('google-maps-iframe');

    mapIframe.src = `https://www.google.com/maps?q=${location}&output=embed`;
    mapPreview.style.display = 'block';
}

document.getElementById('google_maps').addEventListener('input', function () {
    const location = this.value;
    if (location.includes(',')) {
        showMapPreview(location);
    }
});

window.onload = function () {
    const googleMapsInput = document.getElementById('google_maps').value;
    if (googleMapsInput && googleMapsInput.includes(',')) {
        showMapPreview(googleMapsInput);
    }
};

@if(!@$kye)
// Form submission validation
document.getElementById('kyeForm').addEventListener('submit', function(event) {
    const employeePhoto = document.getElementById('employee_photo').value;
    const ktpPhoto = document.getElementById('ktp_photo').value;
    const selfieKtp = document.getElementById('selfie_ktp').value;

    if (!employeePhoto) {
        alert('Foto Karyawan wajib diunggah!');
        currentStep = 1;
        updateStepper();
        event.preventDefault();
        return false;
    }

    if (!ktpPhoto) {
        alert('Foto KTP wajib diunggah!');
        currentStep = 2;
        updateStepper();
        event.preventDefault();
        return false;
    }

    if (!selfieKtp) {
        alert('Foto Selfie KTP wajib diunggah!');
        currentStep = 2;
        updateStepper();
        event.preventDefault();
        return false;
    }
});
@endif

function verifyEmail(email) {
    const currentUserId = document.getElementById('current_user_id').value;
    const emailErrorElement = document.getElementById('email-error');

    return truew
}
</script>
@stop