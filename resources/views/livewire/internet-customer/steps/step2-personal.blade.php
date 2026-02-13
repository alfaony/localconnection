<h3 class="section-title">Data Pribadi</h3>

<div class="mb-3">
    <label class="form-label">Metode Pengisian Data</label>

    <div class="form-check">
        <input class="form-check-input"
               type="radio"
               wire:model="ktp_input_mode"
               value="manual"
               id="modeManual">
        <label class="form-check-label" for="modeManual">
            Isi Manual
        </label>
    </div>

    <div class="form-check">
        <input class="form-check-input"
               type="radio"
               wire:model="ktp_input_mode"
               value="auto"
               id="modeAuto">
        <label class="form-check-label" for="modeAuto">
            Isi Otomatis dari KTP
        </label>
    </div>
</div>

<div class="col-12 mb-3">
    <label class="form-label">Foto KTP <span class="text-danger">*</span></label>
    <div class="border rounded p-3 bg-white">
        
        <input type="file" 
            wire:model="ktp_photo" 
            class="form-control"
            accept="image/*,application/pdf"
            @if($isReadingKtp) disabled @endif>

        @if($ktp_photo)
            <small class="text-success d-block mt-1">
                <i class="fas fa-check-circle me-1"></i> 
                File terpilih: {{ $ktp_photo->getClientOriginalName() }}
            </small>
        @else
            <small class="text-muted d-block mt-1">
                Format: JPG, PNG, PDF (maks. 2MB)
            </small>
        @endif

        {{-- LOADING KHUSUS AUTO MODE --}}
        @if($ktp_input_mode === 'auto')
            <!-- <div wire:loading wire:target="ktp_photo" class="mt-2">
                <div class="alert alert-warning py-2 mb-0">
                    <i class="fas fa-spinner fa-spin me-1"></i>
                    <strong>Sedang mengunggah file ke server...</strong>
                    <br><small>Mohon jangan refresh atau pindah halaman</small>
                </div>
            </div> -->

            <!-- @if($isReadingKtp)
                <div class="alert alert-info mt-2 py-2 mb-0">
                    <i class="fas fa-spinner fa-spin me-1"></i>
                    Sedang membaca data KTP... mohon tunggu
                </div>
            @endif -->
        @endif

        @error('ktp_photo') 
            <small class="text-danger d-block mt-2">{{ $message }}</small> 
        @enderror

        <div wire:loading wire:target="ktp_photo" class="mt-2">
            <div class="alert alert-warning mb-0 py-2">
                <i class="fas fa-spinner fa-spin me-1"></i>
                <strong>Sedang mengunggah file ke server...</strong>
                <br><small>Mohon jangan refresh atau pindah halaman</small>
            </div>
        </div>

        {{-- Reading KTP (AUTO MODE ONLY) --}}
        @if($ktp_input_mode === 'auto' && $isReadingKtp)
            <div class="alert alert-info mt-2 mb-0 py-2">
                <i class="fas fa-spinner fa-spin me-1"></i>
                Sedang membaca data KTP... mohon tunggu
            </div>
        @endif

        {{-- Upload success indicator (untuk validasi submit) --}}
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


<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" wire:model="name" class="form-control" @if($ktp_input_mode === 'auto' && $isReadingKtp) disabled @endif>
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
        <textarea wire:model="address" rows="3" class="form-control" @if($ktp_input_mode === 'auto' && $isReadingKtp) disabled @endif></textarea>
        @error('address') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    
    <div class="col-12">
        <label class="form-label">Nomor KTP <span class="text-danger">*</span></label>
        <input type="text" wire:model="ktp_number" class="form-control" @if($ktp_input_mode === 'auto' && $isReadingKtp) disabled @endif>
        @error('ktp_number') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
</div>

@if($step === 2)
    <div wire:poll.3000ms="checkKtpScanResult"></div>
@endif

<div class="d-flex justify-content-between mt-4">
     <button wire:click="prevStep" class="btn btn-outline-secondary px-4">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </button>
    
    <button 
        wire:click="nextStep"
        wire:loading.attr="disabled"
        wire:target="nextStep,ktp_photo"
        class="btn-primary-red"
        @if(!$ktp_photo || ($ktp_input_mode === 'auto' && $isReadingKtp)) disabled @endif
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