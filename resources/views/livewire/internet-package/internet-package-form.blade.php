@canAccess('store', 'internet_packages')
@canAccess('update', 'internet_packages')
<div>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0">
                <i class="fas fa-wifi me-2"></i>
                {{ $packageId ? 'Edit Paket Internet' : 'Tambah Paket Internet Baru' }}
            </h5>
        </div>
        
        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="row">
                    @include('components.alert')
                    <!-- Left Column - Package Details -->
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0">Informasi Paket</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Nama Paket <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                                <input type="text" class="form-control" id="name" wire:model="name" placeholder="Contoh: Paket Internet 50Mbps">
                                            </div>
                                            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bandwidth" class="form-label">Bandwidth (Mbps) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                                <input type="number" class="form-control" id="bandwidth" wire:model="bandwidth" min="1" placeholder="50">
                                            </div>
                                            @error('bandwidth') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi Paket</label>
                                    <textarea class="form-control" id="description" wire:model="description" rows="2" placeholder="Deskripsi fitur paket internet"></textarea>
                                    @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Network Settings Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0">Pengaturan Jaringan</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="access_type" class="form-label">Tipe Akses <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-network-wired"></i></span>
                                                <select class="form-control" id="access_type" wire:model="access_type">
                                                    <option value=""  selected>Pilih Tipe</option>
                                                    <option value="pppoe">PPPoE</option>
                                                    <!-- <option value="hotspot">Hotspot</option> -->
                                                    <!-- <option value="ipoe">IPoE</option> -->
                                                </select>
                                            </div>
                                            @error('access_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="rate_down_mbps" class="form-label">Rate Download (Mbps)</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-download"></i></span>
                                                <input type="number" class="form-control" id="rate_down_mbps" wire:model="rate_down_mbps" placeholder="50">
                                            </div>
                                            @error('rate_down_mbps') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="rate_up_mbps" class="form-label">Rate Upload (Mbps)</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-upload"></i></span>
                                                <input type="number" class="form-control" id="rate_up_mbps" wire:model="rate_up_mbps" placeholder="10">
                                            </div>
                                            @error('rate_up_mbps') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- 
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="fup_rate_down_mbps" class="form-label">FUP Rate Download (Mbps)</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-tachometer-alt"></i></span>
                                                <input type="number" class="form-control" id="fup_rate_down_mbps" wire:model="fup_rate_down_mbps" placeholder="1">
                                            </div>
                                            @error('fup_rate_down_mbps') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                                --}}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column - Pricing & Settings -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0">Harga & Jenis Paket</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Jenis Paket <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="jenis" id="dedicated" value="dedicated" wire:model="type">
                                            <label class="form-check-label" for="dedicated">
                                                <i class="fas fa-server me-1"></i> Dedicated
                                            </label>
                                        </div>
                                        <div class="form-check ml-3">
                                            <input class="form-check-input" type="radio" name="jenis" id="broadband" value="broadband" wire:model="type">
                                            <label class="form-check-label" for="broadband">
                                                <i class="fas fa-users me-1"></i> Broadband
                                            </label>
                                        </div>
                                    </div>
                                    @error('jenis') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="price" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                                    <input type="hidden" wire:model="price" id="price_hidden">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control" id="internet_cost_input" wire:ignore placeholder="Harga normal">
                                    </div>
                                    @error('price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="price_nett" class="form-label">Harga Nett (Rp) <span class="text-danger">*</span></label>
                                    <input type="hidden" wire:model="price_nett" id="price_nett_hidden">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control" id="internet_cost_input_nett" wire:ignore placeholder="Harga setelah diskon">
                                    </div>
                                    @error('price_nett') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" wire:model="is_active">
                                        <label class="form-check-label" for="is_active">
                                            <i class="fas fa-power-off me-1"></i> Paket Aktif
                                        </label>
                                    </div>
                                    @error('is_active') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <a href="{{ route('internet-package.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Simpan Paket
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/imask"></script>
<script>
    document.addEventListener('livewire:load', function () {
        // Harga Normal
        const priceInput = document.getElementById('internet_cost_input');
        const priceHidden = document.getElementById('price_hidden');
        let priceMask = null;

        if (priceInput && priceHidden) {
            priceMask = IMask(priceInput, {
                mask: Number,
                scale: 0,
                thousandsSeparator: '.',
                padFractionalZeros: false,
                normalizeZeros: true,
                radix: ',',
                mapToRadix: ['.']
            });

            // Set nilai awal dari hidden input ke field yang diformat
            if (priceHidden.value) {
                priceMask.value = priceHidden.value;
            }

            // Sync ke Livewire saat input berubah
            priceMask.on('accept', () => {
                priceHidden.value = priceMask.unmaskedValue;
                priceHidden.dispatchEvent(new Event('input'));
            });
        }

        // Harga Nett
        const priceNettInput = document.getElementById('internet_cost_input_nett');
        const priceNettHidden = document.getElementById('price_nett_hidden');
        let priceNettMask = null;

        if (priceNettInput && priceNettHidden) {
            priceNettMask = IMask(priceNettInput, {
                mask: Number,
                scale: 0,
                thousandsSeparator: '.',
                padFractionalZeros: false,
                normalizeZeros: true,
                radix: ',',
                mapToRadix: ['.']
            });

            if (priceNettHidden.value) {
                priceNettMask.value = priceNettHidden.value;
            }

            priceNettMask.on('accept', () => {
                priceNettHidden.value = priceNettMask.unmaskedValue;
                priceNettHidden.dispatchEvent(new Event('input'));
            });
        }
    });
</script>
@endpush

@endcanAccess
@endcanAccess