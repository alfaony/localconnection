<h3 class="section-title">Data Pribadi</h3>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" wire:model="name" class="form-control">
        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
        <input type="text" wire:model="phone_number" class="form-control">
        @error('phone_number') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    
    <div class="col-md-12">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input type="email" wire:model="email" class="form-control">
        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    
    <div class="col-12">
        <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
        <textarea wire:model="address" rows="3" class="form-control"></textarea>
        @error('address') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Nomor KTP <span class="text-danger">*</span></label>
        <input type="text" wire:model="ktp_number" class="form-control">
        @error('ktp_number') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    
    <div class="col-md-6">
        <label class="form-label">Foto KTP <span class="text-danger">*</span></label>
        <div class="border rounded p-3 bg-white">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-file-upload fa-2x text-muted"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <input type="file" 
                        wire:model="ktp_photo" 
                        class="form-control" 
                        accept="image/*,application/pdf"
                        id="ktp-photo-input">
                    
                    @if($ktp_photo)
                        <small class="text-success d-block mt-1" id="ktp-success-msg">
                            <i class="fas fa-check-circle me-1"></i> 
                            File terpilih: {{ $ktp_photo->getClientOriginalName() }}
                        </small>
                    @else
                        <small class="text-muted d-block mt-1">
                            Format: JPG, PNG, PDF (maks. 2MB)
                        </small>
                    @endif
                </div>
            </div>
            @error('ktp_photo') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror
            
            <!-- Loading state -->
            <div wire:loading wire:target="ktp_photo" class="mt-2">
                <div class="alert alert-warning mb-0 py-2" id="ktp-uploading-msg">
                    <i class="fas fa-spinner fa-spin me-1"></i> 
                    <strong>Sedang mengunggah file ke server...</strong>
                    <br><small>Mohon jangan refresh atau pindah halaman</small>
                </div>
            </div>
            
            <!-- Upload complete indicator -->
            <div wire:loading.remove wire:target="ktp_photo">
                @if($ktp_photo && $ktpPhotoUploaded)
                    <div class="mt-2">
                        <div class="alert alert-success mb-0 py-2">
                            <i class="fas fa-check-circle me-1"></i> 
                            <strong>Upload berhasil!</strong> File siap untuk diproses.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div wire:poll.3000ms="checkKtpScanResult"></div>

<div class="d-flex justify-content-between mt-4">
     <button wire:click="prevStep" class="btn btn-outline-secondary px-4">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </button>
    
    <button 
        wire:click="nextStep"
        wire:loading.attr="disabled"
        wire:target="nextStep,ktp_photo"
        class="btn-primary-red"
        @if(!$ktp_photo) disabled @endif
    >
        <span wire:loading.remove wire:target="nextStep,ktp_photo">
            Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
        </span>
        <span wire:loading wire:target="ktp_photo">
            Mengunggah...
            <span class="spinner-border spinner-border-sm ms-2"></span>
        </span>
        <span wire:loading wire:target="nextStep">
            Memproses...
            <span class="spinner-border spinner-border-sm ms-2"></span>
        </span>
    </button>
</div>

@push('scripts')
<script>
window.addEventListener('ktp-autofill-success', () => {
    alert('Data KTP berhasil dipindai & diisi otomatis. Silakan cek kembali.');
});
</script>
@endpush