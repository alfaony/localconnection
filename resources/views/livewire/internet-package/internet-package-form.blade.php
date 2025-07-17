<div>
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-wifi me-2"></i>
                {{ $packageId ? 'Edit Paket Internet' : 'Tambah Paket Internet Baru' }}
            </h5>
        </div>
        
        <div class="card-body">
            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Paket <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" wire:model="name" placeholder="Nama paket internet">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="bandwidth" class="form-label">Bandwidth (Mbps) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="bandwidth" wire:model="bandwidth" min="1" placeholder="Contoh: 50">
                            @error('bandwidth') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="jenis" class="form-label">Jenis Paket <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis" id="dedicated" value="dedicated" wire:model="type">
                                <label class="form-check-label" for="dedicated">Dedicated</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis" id="broadband" value="broadband" wire:model="type">
                                <label class="form-check-label" for="broadband">Broadband</label>
                            </div>
                            @error('jenis') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="price" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="internet_cost_input" wire:model="price" min="0" placeholder="Harga normal">
                            @error('price') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="price_nett" class="form-label">Harga Nett (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="internet_cost_input" wire:model="price_nett" min="0" placeholder="Harga setelah diskon">
                            @error('price_nett') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi Paket</label>
                    <textarea class="form-control" id="description" wire:model="description" rows="3" placeholder="Deskripsi fitur paket"></textarea>
                    @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" wire:model="is_active">
                        <label class="form-check-label" for="is_active">Paket Aktif</label>
                    </div>
                    @error('is_active') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="{{ route('internet-package.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Paket
                    </button>
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
        document.addEventListener('livewire:load', function() {
            // Format input biaya
            const costInput = document.getElementById('internet_cost_input');
            if (costInput) {
                IMask(costInput, {
                    mask: Number,
                    scale: 0,
                    thousandsSeparator: '.',
                    padFractionalZeros: false,
                    normalizeZeros: true,
                    radix: ',',
                    mapToRadix: ['.']
                });
            }
        });
    </script>
@endpush