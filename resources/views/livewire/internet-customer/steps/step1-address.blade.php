<h3 class="section-title">Data Alamat & Paket</h3>

<!-- Segmen Pelanggan -->
<div class="mb-4">
    <label class="form-label fw-bold">Segmen Pelanggan <span class="text-danger">*</span></label>
    <div class="d-flex gap-3">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" id="ct_rumah" name="customer_type" value="rumah" wire:model="customer_type">
            <label class="form-check-label" for="ct_rumah">
                <i class="fas fa-home me-1 text-success"></i> <strong>Rumah</strong>
            </label>
        </div>
    </div>
    @error('customer_type') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="row g-3">
    <!-- Province -->
    <div class="col-md-6">
        <label class="form-label">Provinsi <span class="text-danger">*</span></label>
        <select wire:model="province_id" 
            id="province_id" 
            class="form-control select2-single @error('province_id') is-invalid @enderror"
            required>
            <option value="">Select Province</option>
            @foreach($provinces as $province)
                <option value="{{ $province->id }}">{{ $province->name }}</option>
            @endforeach
        </select>
        @error('province_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <!-- City -->
    <div class="col-md-6">
        <label class="form-label">Kota/Kabupaten <span class="text-danger">*</span></label>
        <select wire:model="city_id" id="city_id" class="form-select select2-single" {{ !$province_id ? 'disabled' : '' }}>
            <option value="">Pilih Kota/Kabupaten</option>
            @foreach($cities as $city)
                <option value="{{ $city->id }}">{{ $city->name }}</option>
            @endforeach
        </select>
        @error('city_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <!-- District -->
    <div class="col-md-6">
        <label class="form-label">Kecamatan <span class="text-danger">*</span></label>
        <select wire:model="district_id" id="district_id" class="form-select select2-single" {{ !$city_id ? 'disabled' : '' }}>
            <option value="">Pilih Kecamatan</option>
            @foreach($districts as $district)
                <option value="{{ $district->id }}">{{ $district->name }}</option>
            @endforeach
        </select>
        @error('district_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>

    <!-- Subdistrict -->
    <div class="col-md-6">
        <label class="form-label">Kelurahan <span class="text-danger">*</span></label>
        <select wire:model="subdistrict_id" id="subdistrict_id" class="form-select select2-single" {{ !$district_id ? 'disabled' : '' }}>
            <option value="">Pilih Kelurahan</option>
            @foreach($subdistricts as $subdistrict)
                <option value="{{ $subdistrict->id }}">{{ $subdistrict->name }}</option>
            @endforeach
        </select>
        @error('subdistrict_id') <small class="text-danger">{{ $message }}</small> @enderror
    </div>
</div>

<!-- Coverage Check -->
@if($coverageMessage)
    <div class="alert {{ $coverageAvailable ? 'alert-success' : 'alert-danger' }} mt-3">
        {{ $coverageMessage }}
    </div>
@endif

<!-- Coverage Status -->
<div class="mt-3">
    @if ($province_id && $city_id && $district_id && $subdistrict_id)
        @if ($isAvailableArea)
            <span class="alert-badge success">✓ Layanan tersedia di area Anda</span>
        @else
            <span class="alert-badge danger">✗ Layanan belum tersedia di area ini</span>
        @endif
    @endif
</div>

<!-- Internet Package -->
<div class="mt-3">
    <label class="form-label">Paket Internet <span class="text-danger">*</span></label>
    <select wire:model="internet_package_id" id="internet_package_id" class="form-select select2-single">
        <option value="">Pilih Paket Internet</option>
        @foreach($internetPackages as $package)
            @php
                $priceData = $package->getPriceForRegion($province_id, $city_id, $district_id, $subdistrict_id);
                $displayPrice = $priceData['price_nett'];
                $isRegionPrice = $priceData['region_type'] !== 'global';
            @endphp
            <option {{ $isAvailableArea ? '' : 'disabled'}} value="{{ $package->id }}">
                {{ $package->name }} -
                Rp {{ number_format($displayPrice, 0, ',', '.') }}
                @if($isRegionPrice) (Wilayah) @endif
            </option>
        @endforeach
    </select>
    @error('internet_package_id') <small class="text-danger">{{ $message }}</small> @enderror
</div>


<div class="d-flex justify-content-end mt-4">
    <button 
        wire:click="nextStep"
        wire:loading.attr="disabled"
        wire:target="nextStep"
        class="btn-primary-red"
    >
        <span wire:loading.remove wire:target="nextStep">
            Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
        </span>
        <span wire:loading wire:target="nextStep">
            Memproses...
            <span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>
        </span>
    </button>
</div>