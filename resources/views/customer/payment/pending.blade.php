@extends('adminlte::page')

@section('title', 'Menunggu Pembayaran')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header Section -->
            <div class="text-center mb-5">
                <div class="mb-3">
                    <i class="fas fa-clock text-warning" style="font-size: 3.5rem;"></i>
                </div>
                <h2 class="fw-bold mb-2">Menunggu Pembayaran</h2>
                <p class="text-muted">
                    Pesanan Anda telah dibuat. Silakan selesaikan pembayaran untuk mengaktifkan langganan.
                </p>
            </div>

            <div class="row g-4">
                <!-- Left Column - Order Info & Payment Instructions -->
                <div class="col-lg-7">
                    <!-- Order Details Card -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-receipt me-2"></i>Detail Pesanan
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-hashtag text-muted me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Nomor Pesanan</small>
                                            <strong class="text-dark">{{ $subscription->order_number }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-laptop-code text-muted me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Software</small>
                                            <strong class="text-dark">{{ $subscription->masterAccount->software->nama }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-box text-muted me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Paket</small>
                                            <strong class="text-dark">{{ $subscription->package->nama_paket }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-money-bill-wave text-muted me-2 mt-1"></i>
                                        <div>
                                            <small class="text-muted d-block">Total Pembayaran</small>
                                            <strong class="text-primary fs-5">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Instructions Card -->
                    <div class="card shadow-sm border-0 border-start border-info border-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">
                                <i class="fas fa-info-circle text-info me-2"></i>Instruksi Pembayaran
                            </h5>
                            <p class="mb-3">Silakan transfer ke rekening berikut:</p>

                            <div class="bg-light rounded p-3 mb-3">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="text-muted">Bank</span>
                                            <strong>{{ $payment->manual_transfer_bank_name ?? '' }}</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="text-muted">Nomor Rekening</span>
                                            <strong class="text-primary">{{ $payment->manual_transfer_account_number ?? '-' }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <span class="text-muted">Atas Nama</span>
                                            <strong>{{ $payment->manual_transfer_account_name ?? '-' }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center py-2">
                                            <span class="text-muted">Jumlah Transfer</span>
                                            <strong class="text-success fs-5">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <small>Pastikan jumlah transfer sesuai dengan nominal yang tertera untuk mempercepat verifikasi.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Upload Form -->
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-upload me-2"></i>Upload Bukti Transfer
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($payment->manual_transfer_proof)
                                <!-- Existing Proof -->
                                <div class="alert alert-success border-0">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Bukti sudah diupload!</strong>
                                    <p class="mb-0 mt-2 small">Menunggu verifikasi admin.</p>
                                </div>
                                
                                <div class="text-center mb-3">
                                    <img src="{{ s3_asset(true,10,$payment->manual_transfer_proof) }}" 
                                         alt="Bukti Transfer" 
                                         class="img-fluid rounded border shadow-sm"
                                         style="max-height: 300px;">
                                </div>

                                @if($payment->manual_transfer_sender_name)
                                <div class="bg-light rounded p-3 mb-3">
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Nama Pengirim</small>
                                        <strong>{{ $payment->manual_transfer_sender_name }}</strong>
                                    </div>
                                    <div>
                                        <small class="text-muted d-block">Bank Pengirim</small>
                                        <strong>{{ $payment->manual_transfer_sender_bank }}</strong>
                                    </div>
                                </div>
                                @endif

                                <div class="alert alert-info border-0 text-center">
                                    <i class="fas fa-hourglass-half me-2"></i>
                                    <small>Pembayaran Anda sedang dalam proses verifikasi. Kami akan menghubungi Anda segera.</small>
                                </div>
                            @else
                                <!-- No Proof Yet -->
                                <div class="alert alert-info border-0 mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small>Upload bukti transfer untuk mempercepat proses verifikasi pembayaran Anda.</small>
                                </div>

                                <!-- Upload Form -->
                                <form action="{{ route('customer-software.payment.upload-proof', $payment->id) }}" 
                                      method="POST" 
                                      enctype="multipart/form-data"
                                      id="uploadProofForm">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="sender_name" class="form-label fw-semibold">
                                            Nama Pengirim <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" 
                                                   class="form-control @error('sender_name') is-invalid @enderror" 
                                                   id="sender_name" 
                                                   name="sender_name" 
                                                   value="{{ old('sender_name', $payment->manual_transfer_sender_name) }}"
                                                   placeholder="Nama sesuai rekening"
                                                   required>
                                        </div>
                                        <small class="form-text text-muted">Sesuai dengan nama di rekening bank</small>
                                        @error('sender_name')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="sender_bank" class="form-label fw-semibold">
                                            Bank Pengirim <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-university"></i></span>
                                            <input type="text" 
                                                   class="form-control @error('sender_bank') is-invalid @enderror" 
                                                   id="sender_bank" 
                                                   name="sender_bank" 
                                                   value="{{ old('sender_bank', $payment->manual_transfer_sender_bank) }}"
                                                   placeholder="Contoh: BCA, Mandiri"
                                                   required>
                                        </div>
                                        <small class="form-text text-muted">Bank yang digunakan untuk transfer</small>
                                        @error('sender_bank')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="transfer_proof" class="form-label fw-semibold">
                                            Bukti Transfer <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" 
                                               class="form-control @error('transfer_proof') is-invalid @enderror" 
                                               id="transfer_proof" 
                                               name="transfer_proof" 
                                               accept="image/jpeg,image/png,image/jpg"
                                               required>
                                        <small class="form-text text-muted">JPG, PNG (Max: 2MB)</small>
                                        @error('transfer_proof')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Image Preview -->
                                    <div id="imagePreview" class="mb-3 text-center" style="display: none;">
                                        <img id="previewImg" src="" alt="Preview" class="img-fluid rounded border shadow-sm" style="max-height: 250px;">
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success btn-md">
                                            <i class="fas fa-upload me-2"></i>Upload Bukti Transfer
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center mt-5">
                <a href="{{ route('customer-software.subscription.index') }}" class="btn btn-primary btn-md me-2">
                    <i class="fas fa-list me-2"></i>Lihat Langganan Saya
                </a>
                <a href="{{ route('customer-software.software.index') }}" class="btn btn-outline-secondary btn-md">
                    <i class="fas fa-home me-2"></i>Kembali ke Beranda
                </a>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('transfer_proof');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const uploadForm = document.getElementById('uploadProofForm');

    // Image preview on file select
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (file) {
            // Validate file size (2MB = 2048KB)
            const maxSize = 2048 * 1024; // 2MB in bytes
            if (file.size > maxSize) {
                alert('Ukuran file terlalu besar! Maksimal 2MB.');
                fileInput.value = '';
                imagePreview.style.display = 'none';
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                alert('Format file tidak valid! Gunakan JPG, JPEG, atau PNG.');
                fileInput.value = '';
                imagePreview.style.display = 'none';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(event) {
                previewImg.src = event.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            imagePreview.style.display = 'none';
        }
    });

    // Form submission confirmation
    uploadForm.addEventListener('submit', function(e) {
        const senderName = document.getElementById('sender_name').value.trim();
        const senderBank = document.getElementById('sender_bank').value.trim();
        
        if (!senderName || !senderBank) {
            e.preventDefault();
            alert('Silakan lengkapi nama pengirim dan bank pengirim.');
            return false;
        }

        if (!fileInput.files.length) {
            e.preventDefault();
            alert('Silakan pilih file bukti transfer terlebih dahulu.');
            return false;
        }

        if (!confirm('Pastikan data yang Anda masukkan sudah benar. Lanjutkan upload?')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>
@endpush
@endsection
