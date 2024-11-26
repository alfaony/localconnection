@extends('adminlte::page')

@section('title', 'Detail KYE')

@section('content_header')
    <h3>Detail KYE</h3>
@stop

@section('content')
    @if (session('error'))
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif
    @if (session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4><i class="fas fa-id-card"></i> Identitas Karyawan</h4>
            </div>
            <div class="card-body">
                <p><strong><i class="fas fa-user"></i> Nama Lengkap:</strong> {{ $kye->full_name }}</p>
                <p><strong><i class="fas fa-birthday-cake"></i> Tempat & Tanggal Lahir:</strong> {{ $kye->birth_place }}, {{ $kye->birth_date }}</p>
                <p><strong><i class="fas fa-map-marker-alt"></i> Alamat:</strong> {{ $kye->address }}</p>
                <p><strong><i class="fas fa-id-card"></i> Nomor KTP:</strong> {{ $kye->ktp_number }}</p>
                <div>
                    <strong><i class="fas fa-camera"></i> Foto Karyawan:</strong>
                    <div class="mt-2">
                        @if ($kye->employee_photo)
                            <img src="{{ asset( $kye->employee_photo) }}" alt="Employee Photo" class="img-thumbnail" width="150">
                        @else
                            <p class="text-muted"><i class="fas fa-image"></i> Belum ada foto.</p>
                        @endif
                    </div>
                </div>
                <div class="mt-3">
                    <strong><i class="fas fa-id-card"></i> Foto KTP:</strong>
                    <div class="mt-2">
                        @if ($kye->ktp_photo)
                            <img src="{{ asset( $kye->ktp_photo) }}" alt="KTP Photo" class="img-thumbnail" width="150">
                        @else
                            <p class="text-muted"><i class="fas fa-image"></i> Belum ada foto.</p>
                        @endif
                    </div>
                </div>
                <div class="mt-3">
                    <strong><i class="fas fa-camera"></i> Selfie dengan KTP:</strong>
                    <div class="mt-2">
                        @if ($kye->selfie_ktp)
                            <img src="{{ asset( $kye->selfie_ktp) }}" alt="Selfie KTP" class="img-thumbnail" width="150">
                        @else
                            <p class="text-muted"><i class="fas fa-image"></i> Belum ada foto.</p>
                        @endif
                    </div>
                </div>
                <div class="mt-3">
                    <strong><i class="fas fa-id-card"></i> Foto KTP Orang Tua/Saudara:</strong>
                    <div class="mt-2">
                        @if ($kye->ktp_family)
                            <img src="{{ asset( $kye->ktp_family) }}" alt="KTP Orang Tua/Saudara" class="img-thumbnail" width="150">
                        @else
                            <p class="text-muted"><i class="fas fa-image"></i> Belum ada foto.</p>
                        @endif
                    </div>
                </div>
                <p class="mt-3"><strong><i class="fas fa-file-alt"></i> Nomor NPWP:</strong> {{ $kye->npwp_number ?? 'Tidak Ada' }}</p>
                <div class="mt-4">
                    <strong><i class="fas fa-map-marker-alt"></i> Lokasi Rumah:</strong>
                    <div class="mt-2">
                        @if ($kye->google_maps)
                        <iframe 
                            width="100%" 
                            height="350" 
                            frameborder="0" 
                            style="border:0; border-radius: 8px;"
                            src="https://www.google.com/maps?q={{ $kye->google_maps }}&hl=id&z=14&output=embed" 
                            allowfullscreen>
                        </iframe>
                        @else
                            <p class="text-muted"><i class="fas fa-map-marker-alt"></i> Lokasi tidak tersedia.</p>
                        @endif
                    </div>
                </div>
                <div>
                    <strong><i class="fas fa-home"></i> Foto Rumah:</strong>
                    <div class="mt-2">
                        @if ($kye->house_photo)
                            <img src="{{ asset( $kye->house_photo) }}" alt="House Photo" class="img-thumbnail" width="150">
                        @else
                            <p class="text-muted"><i class="fas fa-image"></i> Belum ada foto.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <h4><i class="fas fa-address-book"></i> Informasi Kontak</h4>
            </div>
            <div class="card-body">
                <p><strong><i class="fas fa-phone-alt"></i> Nomor Telepon:</strong> {{ $kye->phone_number }}</p>
                <p><strong><i class="fas fa-envelope"></i> Email:</strong> {{ $kye->email }}</p>
                <p><strong><i class="fas fa-mobile-alt"></i> Kode IMEI HP:</strong> {{ $kye->imei_number ?? 'Tidak Ada' }}</p>
                <p><strong><i class="fas fa-phone"></i> No. Telepon Darurat:</strong> {{ $kye->emergency_phone ?? 'Tidak Ada' }}</p>
                <p><strong><i class="fas fa-user-friends"></i> Nama Kontak Darurat:</strong> {{ $kye->emergency_contact ?? 'Tidak Ada' }}</p>
                <p><strong><i class="fas fa-university"></i> Nama Account Bank:</strong> {{ $kye->bank_account_name ?? 'Tidak Ada' }}</p>
                <p><strong><i class="fas fa-money-check-alt"></i> Nama Bank:</strong> {{ $kye->bank_name ?? 'Tidak Ada' }}</p>
                <p><strong><i class="fas fa-credit-card"></i> No. Rekening:</strong> {{ $kye->account_number ?? 'Tidak Ada' }}</p>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-warning text-white">
                <h4><i class="fas fa-check-circle"></i> Approval</h4>
            </div>
            <div class="card-body">
                <p><strong><i class="fas fa-clipboard-check"></i> Approval Status:</strong> {{ ucfirst($kye->approval_status) }}</p>
                <p><strong><i class="fas fa-sticky-note"></i> Approval Notes:</strong> {{ $kye->approval_note ?? 'Tidak Ada' }}</p>
            </div>
        </div>

        @canAccess('approvement','kyes')
        <form action="{{ route('kye.approvement', $kye->id) }}" method="POST" class="mt-3">
        @csrf
        @method('PATCH')
            <div class="card mt-4">
                <div class="card-header bg-warning text-white">
                    <h4><i class="fas fa-check-circle"></i> Approval</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="approval_status"><i class="fas fa-clipboard-check"></i> Approval Status</label>
                        <select name="status" id="approval_status" class="form-control" required>
                            <option value="pending"
                                {{ isset($kye) && $kye->approval_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved"
                                {{ isset($kye) && $kye->approval_status === 'approved' ? 'selected' : '' }}>Approved
                            </option>
                            <option value="rejected"
                                {{ isset($kye) && $kye->approval_status === 'rejected' ? 'selected' : '' }}>Rejected
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="approval_note"><i class="fas fa-sticky-note"></i> Approval Notes</label>
                        <textarea name="approval_note" id="approval_note" rows="3" class="form-control"
                            placeholder="Masukkan catatan">{{ $kye->approval_note ?? '' }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </div>
        </form>
        @endcanAccess

        <div class="text-center mt-4">
            <a href="{{ route('kye.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>

            @canAccess('update','kyes')
            <a href="{{ route('kye.edit', $kye->id) }}" class="btn btn-outline-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcanAccess
        </div>
@stop
