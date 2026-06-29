<h3 class="section-title">Data Pribadi</h3>

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" wire:model="name" class="form-control" @if($ktp_input_mode === 'auto' && $isReadingKtp) disabled @endif>
        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Nomor Telepon <span class="text-danger">*</span></label>
        <input type="text" wire:model="phone_number" class="form-control" inputmode="numeric" pattern="[0-9]*" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
        @error('phone_number') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
        <textarea wire:model="address" rows="3" class="form-control" @if($ktp_input_mode === 'auto' && $isReadingKtp) disabled @endif></textarea>
        @error('address') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    {{-- Titik Koordinat --}}
    <div class="col-12">
        <label class="form-label">Titik Lokasi</label>
        @include('partials.location-map-picker', [
            'mapId'  => 'reg-location-map',
            'height' => '260px',
        ])
        @error('latitude')  <small class="text-danger d-block">{{ $message }}</small> @enderror
        @error('longitude') <small class="text-danger d-block">{{ $message }}</small> @enderror
    </div>

    @if($customer_type === 'bisnis')
    <div class="col-md-6">
        <label class="form-label">Nomor NPWP</label>
        <input type="text" wire:model="npwp_number" class="form-control" placeholder="00.000.000.0-000.000">
        @error('npwp_number') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Foto NPWP</label>
        <input type="file" wire:model="npwp_photo" class="form-control" accept="image/*,application/pdf">
        @if($npwp_photo)
            <small class="text-success d-block mt-1">
                <i class="fas fa-check-circle me-1"></i>
                File terpilih: {{ $npwp_photo->getClientOriginalName() }}
            </small>
        @else
            <small class="text-muted d-block mt-1">Format: JPG, PNG, PDF (maks. 2MB)</small>
        @endif
        <div wire:loading wire:target="npwp_photo" class="mt-1">
            <small class="text-warning"><i class="fas fa-spinner fa-spin me-1"></i> Mengunggah...</small>
        </div>
        @error('npwp_photo') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    @endif
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
        wire:target="nextStep"
        class="btn-primary-red"
        @if($ktp_input_mode === 'auto' && $isReadingKtp) disabled @endif
    >
        <span wire:loading.remove wire:target="nextStep">
            Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
        </span>
        <span wire:loading wire:target="nextStep">
            Memproses...
            <span class="spinner-border spinner-border-sm ms-2"></span>
        </span>
    </button>
</div>
