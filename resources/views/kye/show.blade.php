@extends('adminlte::page')

@section('title', 'Detail KYE')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Detail KYE</h3>
        <div class="btn-group">
            <a href="{{ route('kye.index') }}" class="btn btn-outline-secondary btn-sm mr-1 mb-1">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
            @if($kye->isEdit())
            @canAccess('update','kyes')
            <a href="{{ route('kye.edit', $kye->id) }}" class="btn btn-outline-warning btn-sm mr-1 mb-1">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcanAccess
            @endif
            <!-- <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button> -->
        </div>
    </div>
@stop

@section('content')
    @include('components.alert')

    {{-- Summary Card --}}
    <div class="kye-summary-card">
        <div class="row align-items-center">
            <div class="col-md-2 text-center">
                @if ($kye->employee_photo)
                    <img src="{{ Storage::url($kye->employee_photo) }}" alt="Employee Photo" class="profile-img">
                @else
                    <div class="profile-img d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.2);">
                        <i class="fas fa-user fa-3x"></i>
                    </div>
                @endif
            </div>
            <div class="col-md-7">
                <h4>{{ $kye->full_name }}</h4>
                <p class="mb-0">
                    {{ $kye->call_name }}
                </p>
                <p class="text-muted mb-2">
                    <i class="fas fa-id-card mr-2"></i>{{ $kye->ktp_number }}
                </p>
                <p class="mb-2">
                    <i class="fas fa-birthday-cake mr-2"></i>{{ $kye->birth_place }}, {{ \Carbon\Carbon::parse($kye->birth_date)->format('d M Y') }}
                </p>
                <p class="mb-0">
                    <i class="fas fa-envelope mr-2"></i>{{ $kye->email }}
                    <span class="ml-3"><i class="fas fa-phone mr-2"></i>{{ $kye->phone_number }}</span>
                </p>
            </div>
            <div class="col-md-3 text-center">
                <div class="mb-3">
                    <small class="d-block mb-2">Status Approval</small>
                    <span class="status-badge status-{{ $kye->approval_status }}">
                        @if($kye->approval_status == 'pending')
                            <i class="fas fa-clock"></i> Pending
                        @elseif($kye->approval_status == 'approved')
                            <i class="fas fa-check-circle"></i> Approved
                        @else
                            <i class="fas fa-times-circle"></i> Rejected
                        @endif
                    </span>

                    @if($kye->approval_status == 'rejected')
                        <p class="text-warning d-block mt-1 b">
                            {{ $kye->approval_note }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs nav-tabs-custom" id="kyeTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="personal-tab" data-toggle="tab" href="#personal" role="tab">
                <i class="fas fa-user mr-2"></i>Data Pribadi
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="documents-tab" data-toggle="tab" href="#documents" role="tab">
                <i class="fas fa-file-alt mr-2"></i>Dokumen
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="photos-tab" data-toggle="tab" href="#photos" role="tab">
                <i class="fas fa-images mr-2"></i>Foto & Gambar
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="location-tab" data-toggle="tab" href="#location" role="tab">
                <i class="fas fa-map-marker-alt mr-2"></i>Lokasi
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab">
                <i class="fas fa-address-book mr-2"></i>Kontak & Bank
            </a>
        </li>
        @canAccess('approvement','kyes')
        <li class="nav-item">
            <a class="nav-link" id="approval-tab" data-toggle="tab" href="#approval" role="tab">
                <i class="fas fa-check-circle mr-2"></i>Approval
            </a>
        </li>
        @endcanAccess
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content" id="kyeTabContent">
        {{-- Personal Data Tab --}}
        <div class="tab-pane fade show active" id="personal" role="tabpanel">
            <div class="info-card card">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-user mr-2"></i>Informasi Pribadi
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-user"></i>Nama Lengkap
                        </div>
                        <div class="value">{{ $kye->full_name }}</div>
                    </div>
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-signature"></i>Nama Panggilan
                        </div>
                        <div class="value">{{ $kye->call_name ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-venus-mars"></i>Jenis Kelamin
                        </div>
                        <div class="value">{{ isset($kye->gender) ? ($kye->gender == 'male' ? 'Laki-laki' : 'Perempuan') : '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-birthday-cake"></i>Tempat, Tanggal Lahir
                        </div>
                        <div class="value">{{ $kye->birth_place }}, {{ \Carbon\Carbon::parse($kye->birth_date)->format('d F Y') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-id-card"></i>Nomor KTP
                        </div>
                        <div class="value">{{ $kye->ktp_number }}</div>
                    </div>
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-file-alt"></i>Nomor NPWP
                        </div>
                        <div class="value">{{ $kye->npwp_number ?? 'Tidak Ada' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-ring"></i>Status Menikah
                        </div>
                        <div class="value">{{ $kye->marital_status ?? '-' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-baby"></i>Jumlah Anak
                        </div>
                        <div class="value">{{ $kye->number_of_children ?? 0 }} Anak</div>
                    </div>
                </div>
            </div>

            <div class="info-card card">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-map-marker-alt mr-2"></i>Alamat
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-home"></i>Alamat Domisili
                        </div>
                        <div class="value">{{ $kye->address_domisili ?? $kye->address }}</div>
                    </div>
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-map-marker-alt"></i>Alamat Tempat Tinggal
                        </div>
                        <div class="value">{{ $kye->address }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Documents Tab --}}
        <div class="tab-pane fade" id="documents" role="tabpanel">
            <div class="info-card card">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-folder-open mr-2"></i>Dokumen & File
                </div>
                <div class="card-body">
                    @if ($kye->npwp_photo)
                    <div class="document-item">
                        <i class="fas fa-file-pdf"></i>
                        <div class="doc-info">
                            <div class="doc-name">Scan NPWP</div>
                            <div class="doc-size">File NPWP - {{ $kye->npwp_number ?? 'No NPWP' }}</div>
                        </div>
                        <a href="{{ Storage::url($kye->npwp_photo) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download"></i> Unduh
                        </a>
                    </div>
                    @endif

                    @if ($kye->skck)
                    <div class="document-item">
                        <i class="fas fa-file-pdf"></i>
                        <div class="doc-info">
                            <div class="doc-name">SKCK</div>
                            <div class="doc-size">Surat Keterangan Catatan Kepolisian</div>
                        </div>
                        <a href="{{ Storage::url($kye->skck) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download"></i> Unduh
                        </a>
                    </div>
                    @endif

                    @if (!$kye->npwp_photo && !$kye->skck)
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-folder-open fa-3x mb-3"></i>
                        <p>Tidak ada dokumen tersedia</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Photos Tab --}}
        <div class="tab-pane fade" id="photos" role="tabpanel">
            <div class="info-card card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-images mr-2"></i>Galeri Foto</span>
                    @if ($kye->employee_photo || $kye->ktp_photo || $kye->selfie_ktp || $kye->ktp_family || $kye->house_photo)
                    <button class="btn btn-light btn-sm" onclick="downloadAllImages()">
                        <i class="fas fa-download mr-2"></i>Download Semua Foto
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="image-gallery">
                        @if ($kye->employee_photo)
                        <div class="image-item">
                            <img src="{{ Storage::url($kye->employee_photo) }}" alt="Foto Karyawan">
                            <div class="image-label">
                                <i class="fas fa-user mr-2"></i>Foto Karyawan
                            </div>
                            <div class="image-actions">
                                <button class="btn btn-view" onclick="openImageModal('{{ Storage::url($kye->employee_photo) }}')">
                                    <i class="fas fa-eye"></i> Lihat
                                </button>
                                <a href="{{ Storage::url($kye->employee_photo) }}" download="Foto_Karyawan_{{ $kye->full_name }}.jpg" class="btn btn-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                        @endif

                        @if ($kye->ktp_photo)
                        <div class="image-item">
                            <img src="{{ Storage::url($kye->ktp_photo) }}" alt="Foto KTP">
                            <div class="image-label">
                                <i class="fas fa-id-card mr-2"></i>Foto KTP
                            </div>
                            <div class="image-actions">
                                <button class="btn btn-view" onclick="openImageModal('{{ Storage::url($kye->ktp_photo) }}')">
                                    <i class="fas fa-eye"></i> Lihat
                                </button>
                                <a href="{{ Storage::url($kye->ktp_photo) }}" download="KTP_{{ $kye->full_name }}.jpg" class="btn btn-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                        @endif

                        @if ($kye->selfie_ktp)
                        <div class="image-item">
                            <img src="{{ Storage::url($kye->selfie_ktp) }}" alt="Selfie KTP">
                            <div class="image-label">
                                <i class="fas fa-camera mr-2"></i>Selfie dengan KTP
                            </div>
                            <div class="image-actions">
                                <button class="btn btn-view" onclick="openImageModal('{{ Storage::url($kye->selfie_ktp) }}')">
                                    <i class="fas fa-eye"></i> Lihat
                                </button>
                                <a href="{{ Storage::url($kye->selfie_ktp) }}" download="Selfie_KTP_{{ $kye->full_name }}.jpg" class="btn btn-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                        @endif

                        @if ($kye->ktp_family)
                        <div class="image-item">
                            <img src="{{ Storage::url($kye->ktp_family) }}" alt="KTP Keluarga">
                            <div class="image-label">
                                <i class="fas fa-users mr-2"></i>KTP Orang Tua/Saudara
                            </div>
                            <div class="image-actions">
                                <button class="btn btn-view" onclick="openImageModal('{{ Storage::url($kye->ktp_family) }}')">
                                    <i class="fas fa-eye"></i> Lihat
                                </button>
                                <a href="{{ Storage::url($kye->ktp_family) }}" download="KTP_Keluarga_{{ $kye->full_name }}.jpg" class="btn btn-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                        @endif

                        @if ($kye->house_photo)
                        <div class="image-item">
                            <img src="{{ Storage::url($kye->house_photo) }}" alt="Foto Rumah">
                            <div class="image-label">
                                <i class="fas fa-home mr-2"></i>Foto Rumah
                            </div>
                            <div class="image-actions">
                                <button class="btn btn-view" onclick="openImageModal('{{ Storage::url($kye->house_photo) }}')">
                                    <i class="fas fa-eye"></i> Lihat
                                </button>
                                <a href="{{ Storage::url($kye->house_photo) }}" download="Foto_Rumah_{{ $kye->full_name }}.jpg" class="btn btn-download">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if (!$kye->employee_photo && !$kye->ktp_photo && !$kye->selfie_ktp && !$kye->ktp_family && !$kye->house_photo)
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-images fa-3x mb-3"></i>
                        <p>Tidak ada foto tersedia</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Location Tab --}}
        <div class="tab-pane fade" id="location" role="tabpanel">
            <div class="info-card card">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-map-marker-alt mr-2"></i>Lokasi Rumah
                </div>
                <div class="card-body">
                    @if ($kye->google_maps)
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-map-pin"></i>Koordinat
                        </div>
                        <div class="value">{{ $kye->google_maps }}</div>
                    </div>
                    <div class="map-container">
                        <iframe 
                            width="100%" 
                            height="450" 
                            frameborder="0" 
                            style="border:0;"
                            src="https://www.google.com/maps?q={{ $kye->google_maps }}&hl=id&z=15&output=embed" 
                            allowfullscreen>
                        </iframe>
                    </div>
                    @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                        <p>Lokasi tidak tersedia</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Contact & Bank Tab --}}
        <div class="tab-pane fade" id="contact" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-card card">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-phone mr-2"></i>Informasi Kontak
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <div class="label">
                                    <i class="fas fa-phone-alt"></i>Nomor Telepon
                                </div>
                                <div class="value">
                                    <a href="tel:{{ $kye->phone_number }}">{{ $kye->phone_number }}</a>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="label">
                                    <i class="fas fa-envelope"></i>Email
                                </div>
                                <div class="value">
                                    <a href="mailto:{{ $kye->email }}">{{ $kye->email }}</a>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="label">
                                    <i class="fas fa-mobile-alt"></i>Kode IMEI HP
                                </div>
                                <div class="value">{{ $kye->imei_number ?? 'Tidak Ada' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="info-card card">
                        <div class="card-header bg-danger text-white">
                            <i class="fas fa-user-shield mr-2"></i>Kontak Darurat
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <div class="label">
                                    <i class="fas fa-user-friends"></i>Nama Kontak
                                </div>
                                <div class="value">{{ $kye->emergency_contact ?? 'Tidak Ada' }}</div>
                            </div>
                            <div class="info-row">
                                <div class="label">
                                    <i class="fas fa-phone"></i>No. Telepon
                                </div>
                                <div class="value">
                                    @if($kye->emergency_phone)
                                    <a href="tel:{{ $kye->emergency_phone }}">{{ $kye->emergency_phone }}</a>
                                    @else
                                    Tidak Ada
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-card card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-university mr-2"></i>Informasi Bank
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-user"></i>Nama Pemilik Rekening
                        </div>
                        <div class="value">{{ $kye->bank_account_name ?? 'Tidak Ada' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-money-check-alt"></i>Nama Bank
                        </div>
                        <div class="value">{{ $kye->bank_name ?? 'Tidak Ada' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-credit-card"></i>No. Rekening
                        </div>
                        <div class="value">{{ $kye->account_number ?? 'Tidak Ada' }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Approval Tab --}}
        @canAccess('approvement','kyes')
        <div class="tab-pane fade" id="approval" role="tabpanel">
            <div class="info-card card">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-clipboard-check mr-2"></i>Status Approval
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-info-circle"></i>Status Saat Ini
                        </div>
                        <div class="value">
                            <span class="status-badge status-{{ $kye->approval_status }}">
                                {{ ucfirst($kye->approval_status) }}
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="label">
                            <i class="fas fa-sticky-note"></i>Catatan Approval
                        </div>
                        <div class="value">{{ $kye->approval_note ?? 'Tidak Ada Catatan' }}</div>
                    </div>
                </div>
            </div>

            <div class="approval-form">
                <h5 class="mb-4"><i class="fas fa-edit mr-2"></i>Update Approval</h5>
                <form action="{{ route('kye.approvement', $kye->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <div class="form-group">
                        <label for="approval_status">
                            <i class="fas fa-clipboard-check mr-2"></i>Status Approval
                        </label>
                        <select name="status" id="approval_status" class="form-control" required>
                            @foreach($status as $key => $value)
                            <option value="{{ $key }}" {{ isset($kye) && $kye->approval_status === $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="approval_note">
                            <i class="fas fa-sticky-note mr-2"></i>Catatan
                        </label>
                        <textarea name="approval_note" id="approval_note" rows="4" class="form-control"
                            placeholder="Masukkan catatan approval...">{{ $kye->approval_note ?? '' }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save mr-2"></i>Simpan Approval
                    </button>
                </form>
            </div>
        </div>
        @endcanAccess
    </div>

    {{-- Image Modal --}}
    <div id="imageModal" class="image-modal">
        <span class="image-modal-close" onclick="closeImageModal()">&times;</span>
        <img class="image-modal-content" id="modalImage">
    </div>
@stop

@section('js')
<script>
    // Image Modal Functions
    function openImageModal(imageSrc) {
        document.getElementById('imageModal').style.display = 'block';
        document.getElementById('modalImage').src = imageSrc;
    }

    function closeImageModal() {
        document.getElementById('imageModal').style.display = 'none';
    }

    // Close modal on outside click
    window.onclick = function(event) {
        const modal = document.getElementById('imageModal');
        if (event.target == modal) {
            closeImageModal();
        }
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeImageModal();
        }
    });

    // Download All Images Function
    function downloadAllImages() {
        const images = [
            @if($kye->employee_photo)
            {
                url: '{{ Storage::url($kye->employee_photo) }}',
                name: 'Foto_Karyawan_{{ $kye->full_name }}.jpg'
            },
            @endif
            @if($kye->ktp_photo)
            {
                url: '{{ Storage::url($kye->ktp_photo) }}',
                name: 'KTP_{{ $kye->full_name }}.jpg'
            },
            @endif
            @if($kye->selfie_ktp)
            {
                url: '{{ Storage::url($kye->selfie_ktp) }}',
                name: 'Selfie_KTP_{{ $kye->full_name }}.jpg'
            },
            @endif
            @if($kye->ktp_family)
            {
                url: '{{ Storage::url($kye->ktp_family) }}',
                name: 'KTP_Keluarga_{{ $kye->full_name }}.jpg'
            },
            @endif
            @if($kye->house_photo)
            {
                url: '{{ Storage::url($kye->house_photo) }}',
                name: 'Foto_Rumah_{{ $kye->full_name }}.jpg'
            }
            @endif
        ];

        if (images.length === 0) {
            alert('Tidak ada foto untuk diunduh');
            return;
        }

        // Show loading notification
        const loadingToast = $('<div class="alert alert-info alert-dismissible fade show" style="position: fixed; top: 70px; right: 20px; z-index: 9999; min-width: 300px;">' +
            '<i class="fas fa-spinner fa-spin mr-2"></i>Mengunduh ' + images.length + ' foto...' +
            '</div>');
        $('body').append(loadingToast);

        // Download each image with delay
        images.forEach((image, index) => {
            setTimeout(() => {
                fetch(image.url)
                    .then(response => response.blob())
                    .then(blob => {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = image.name;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                    })
                    .catch(error => {
                        console.error('Error downloading:', error);
                    });
            }, index * 500); // 500ms delay between downloads
        });

        // Remove loading and show success after all downloads
        setTimeout(() => {
            loadingToast.fadeOut(() => {
                loadingToast.remove();
                const successToast = $('<div class="alert alert-success alert-dismissible fade show" style="position: fixed; top: 70px; right: 20px; z-index: 9999; min-width: 300px;">' +
                    '<i class="fas fa-check-circle mr-2"></i>Semua foto berhasil diunduh!' +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                    '</div>');
                $('body').append(successToast);
                setTimeout(() => successToast.fadeOut(() => successToast.remove()), 3000);
            });
        }, images.length * 500 + 1000);
    }

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
</script>
@stop

@section('css')
<style>
    /* Summary Card Styles */
    .kye-summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }

    .kye-summary-card .profile-img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 5px solid white;
        object-fit: cover;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    .kye-summary-card h4 {
        margin-bottom: 5px;
        font-weight: 700;
    }

    .kye-summary-card .text-muted {
        color: rgba(255,255,255,0.8) !important;
    }

    /* Status Badge */
    .status-badge {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background: #ffc107;
        color: #000;
    }

    .status-approved {
        background: #28a745;
        color: white;
    }

    .status-rejected {
        background: #dc3545;
        color: white;
    }

    /* Tab Styles */
    .nav-tabs-custom {
        border-bottom: 3px solid #e9ecef;
        margin-bottom: 25px;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 600;
        padding: 15px 25px;
        position: relative;
        transition: all 0.3s ease;
    }

    .nav-tabs-custom .nav-link:hover {
        color: #007bff;
        background: #f8f9fa;
    }

    .nav-tabs-custom .nav-link.active {
        color: #007bff;
        background: transparent;
    }

    .nav-tabs-custom .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 0;
        right: 0;
        height: 3px;
        background: #007bff;
    }

    /* Info Card Styles */
    .info-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 20px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .info-card:hover {
        box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }

    .info-card .card-header {
        border-bottom: 2px solid rgba(255,255,255,0.2);
        padding: 15px 20px;
        font-weight: 600;
    }

    .info-card .card-body {
        padding: 20px;
    }

    /* Info Row */
    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
        align-items: center;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-row .label {
        font-weight: 600;
        color: #495057;
        min-width: 200px;
        display: flex;
        align-items: center;
    }

    .info-row .label i {
        margin-right: 10px;
        width: 20px;
        color: #6c757d;
    }

    .info-row .value {
        color: #212529;
        flex: 1;
    }

    /* Image Gallery */
    .image-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .image-item {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .image-item:hover {
        transform: scale(1.03);
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }

    .image-item:hover .image-actions {
        opacity: 1;
        transform: translateY(0);
    }

    .image-item img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        display: block;
    }

    .image-item .image-label {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        color: white;
        padding: 15px;
        padding-bottom: 55px;
        font-weight: 600;
    }

    .image-actions {
        position: absolute;
        bottom: 10px;
        left: 10px;
        right: 10px;
        display: flex;
        gap: 8px;
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
        z-index: 10;
    }

    .image-actions .btn {
        flex: 1;
        font-size: 12px;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        white-space: nowrap;
    }

    .image-actions .btn-view {
        background: rgba(255,255,255,0.95);
        color: #007bff;
        border: none;
    }

    .image-actions .btn-view:hover {
        background: white;
        transform: translateY(-2px);
    }

    .image-actions .btn-download {
        background: rgba(0,123,255,0.95);
        color: white;
        border: none;
    }

    .image-actions .btn-download:hover {
        background: #007bff;
        transform: translateY(-2px);
    }

    /* Map Container */
    .map-container {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        margin-top: 15px;
    }

    /* Document Item */
    .document-item {
        display: flex;
        align-items: center;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .document-item:hover {
        background: #e9ecef;
    }

    .document-item i {
        font-size: 24px;
        margin-right: 15px;
        color: #6c757d;
    }

    .document-item .doc-info {
        flex: 1;
    }

    .document-item .doc-name {
        font-weight: 600;
        margin-bottom: 3px;
    }

    .document-item .doc-size {
        font-size: 12px;
        color: #6c757d;
    }

    /* Approval Form */
    .approval-form {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 25px;
        margin-top: 20px;
    }

    /* Alert Improvements */
    .alert {
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .kye-summary-card {
            text-align: center;
        }

        .info-row {
            flex-direction: column;
            align-items: flex-start;
        }

        .info-row .label {
            margin-bottom: 5px;
            min-width: 100%;
        }

        .image-gallery {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }

        .image-item img {
            height: 150px;
        }

        /* Always show actions on mobile */
        .image-actions {
            opacity: 1;
            transform: translateY(0);
            position: relative;
            bottom: auto;
            left: auto;
            right: auto;
            padding: 8px;
            background: rgba(0,0,0,0.7);
            border-radius: 0 0 12px 12px;
        }

        .image-item .image-label {
            padding-bottom: 15px;
        }

        .image-actions .btn {
            font-size: 11px;
            padding: 5px 8px;
        }

        /* Header button adjustments */
        .card-header .btn-sm {
            font-size: 11px;
            padding: 4px 8px;
        }
    }

    /* Print Styles */
    @media print {
        .btn-group,
        .nav-tabs-custom,
        .approval-form {
            display: none !important;
        }

        .tab-content > .tab-pane {
            display: block !important;
            opacity: 1 !important;
        }
    }

    /* Modal Image Preview */
    .image-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.9);
        animation: fadeIn 0.3s;
    }

    .image-modal-content {
        margin: auto;
        display: block;
        max-width: 90%;
        max-height: 90%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        animation: zoomIn 0.3s;
    }

    .image-modal-close {
        position: absolute;
        top: 30px;
        right: 45px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes zoomIn {
        from { transform: translate(-50%, -50%) scale(0); }
        to { transform: translate(-50%, -50%) scale(1); }
    }
</style>
@stop