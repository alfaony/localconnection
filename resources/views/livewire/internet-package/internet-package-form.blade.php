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
            @include('components.alert')
            <form wire:submit.prevent="save">
                <div class="row">
                    <!-- Left Column - Package Details -->
                    <div class="col-md-8">

                        {{-- INFORMASI PAKET --}}
                        <div class="card mb-4">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-1 text-primary"></i> Informasi Paket</h6>
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

                        {{-- PENGATURAN JARINGAN --}}
                        <div class="card mb-4">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0"><i class="fas fa-network-wired me-1 text-info"></i> Pengaturan Jaringan</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="access_type" class="form-label">Tipe Akses <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fas fa-network-wired"></i></span>
                                                <select class="form-control" id="access_type" wire:model="access_type">
                                                    <option value="" selected>Pilih Tipe</option>
                                                    <option value="pppoe">PPPoE</option>
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
                            </div>
                        </div>

                        {{-- WILAYAH PAKET --}}
                        <div class="card mb-4">
                            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="fas fa-map-marker-alt me-1 text-success"></i> Wilayah Paket <span class="text-muted small">(Opsional — kosongkan = Global)</span></h6>
                                <span class="badge badge-secondary">{{ count($regions) }} Wilayah</span>
                            </div>
                            <div class="card-body">

                                {{-- Tabel region yang sudah di-assign --}}
                                @if(count($regions) > 0)
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Tipe</th>
                                                <th>Wilayah</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($regions as $idx => $region)
                                            <tr class="{{ $region['is_active'] ? '' : 'table-secondary text-muted' }}">
                                                <td>
                                                    <span class="badge badge-{{ $region['region_type_badge'] }}">
                                                        {{ $region['region_type_label'] }}
                                                    </span>
                                                </td>
                                                <td>{{ $region['region_label'] }}</td>
                                                <td class="text-center">
                                                    <button type="button"
                                                        wire:click="toggleRegionActive({{ $idx }})"
                                                        class="btn btn-xs btn-{{ $region['is_active'] ? 'success' : 'secondary' }}"
                                                        title="{{ $region['is_active'] ? 'Aktif — klik untuk nonaktifkan' : 'Nonaktif — klik untuk aktifkan' }}">
                                                        <i class="fas fa-{{ $region['is_active'] ? 'check' : 'times' }}"></i>
                                                    </button>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" wire:click="removeRegion({{ $idx }})"
                                                        class="btn btn-xs btn-danger"
                                                        onclick="return confirm('Hapus wilayah ini?') || event.stopImmediatePropagation()">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-light border text-muted small mb-3">
                                    <i class="fas fa-globe me-1"></i>
                                    Belum ada wilayah spesifik — paket ini bersifat <strong>Global</strong> (muncul di semua daerah dengan harga utama).
                                </div>
                                @endif

                                {{-- Form tambah region baru --}}
                                <div class="border rounded p-3 bg-light">
                                    <h6 class="mb-3 text-sm font-weight-bold"><i class="fas fa-plus-circle me-1 text-success"></i> Tambah Wilayah</h6>

                                    <div class="row">
                                        {{-- Tipe Wilayah --}}
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label small">Tingkat Wilayah</label>
                                                <select class="form-control form-control-sm" wire:model="region_type" id="region_type">
                                                    <option value="province">Provinsi</option>
                                                    <option value="city">Kabupaten/Kota</option>
                                                    <option value="district">Kecamatan</option>
                                                </select>
                                                @error('region_type') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                        </div>

                                        {{-- Provinsi --}}
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label small">Provinsi <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" wire:model="region_province_id" id="region_province_id">
                                                    <option value="">-- Pilih Provinsi --</option>
                                                    @foreach($allProvinces as $prov)
                                                        <option value="{{ $prov['id'] }}">{{ $prov['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                @error('region_province_id') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                        </div>

                                        {{-- Kabupaten --}}
                                        @if(in_array($region_type, ['city', 'district']))
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label small">Kabupaten/Kota <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" wire:model="region_city_id" id="region_city_id">
                                                    <option value="">-- Pilih Kabupaten --</option>
                                                    @foreach($regionCities as $city)
                                                        <option value="{{ $city['id'] }}">{{ $city['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                @error('region_city_id') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        @endif

                                        {{-- Kecamatan --}}
                                        @if($region_type === 'district')
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label small">Kecamatan <span class="text-danger">*</span></label>
                                                <select class="form-control form-control-sm" wire:model="region_district_id" id="region_district_id">
                                                    <option value="">-- Pilih Kecamatan --</option>
                                                    @foreach($regionDistricts as $dist)
                                                        <option value="{{ $dist['id'] }}">{{ $dist['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                @error('region_district_id') <div class="text-danger small">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    {{-- Info harga --}}
                                    <div class="alert alert-info py-1 px-2 small mb-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Harga menggunakan <strong>harga utama paket</strong>. Untuk harga berbeda per wilayah, buat paket baru.
                                    </div>

                                    <button type="button" wire:click="addRegion" class="btn btn-success btn-sm">
                                        <i class="fas fa-plus me-1"></i> Tambah Wilayah
                                    </button>
                                </div>

                                @error('region_province_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>
                        {{-- END WILAYAH --}}

                    </div>

                    <!-- Right Column - Pricing & Settings -->
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0"><i class="fas fa-tag me-1 text-warning"></i> Harga & Jenis Paket</h6>
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
                                    <label for="price" class="form-label">Harga Global (Rp) <span class="text-danger">*</span></label>
                                    <p class="text-muted small mb-1">Digunakan jika tidak ada harga khusus wilayah</p>
                                    <input type="hidden" wire:model="price" id="price_hidden">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control" id="internet_cost_input" wire:ignore placeholder="Harga normal">
                                    </div>
                                    @error('price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="price_nett" class="form-label">Harga Nett Global (Rp) <span class="text-danger">*</span></label>
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
<script src="https://cdn.jsdelivr.net/npm/imask"></script>
<script>
    document.addEventListener('livewire:load', function () {
        let masks = {};

        function initMask(inputId, hiddenId, wireKey) {
            const input = document.getElementById(inputId);
            const hidden = document.getElementById(hiddenId);
            if (!input || !hidden) return;

            if (masks[inputId]) masks[inputId].destroy();

            masks[inputId] = IMask(input, {
                mask: Number,
                scale: 0,
                thousandsSeparator: '.',
                normalizeZeros: true,
                radix: ',',
                mapToRadix: ['.'],
                min: 0,
                max: 999999999
            });

            if (hidden.value) {
                masks[inputId].unmaskedValue = hidden.value;
            }

            masks[inputId].on('accept', () => {
                hidden.value = masks[inputId].unmaskedValue;
                @this.set(wireKey, masks[inputId].unmaskedValue);
            });
        }

        function initAllMasks() {
            initMask('internet_cost_input',      'price_hidden',           'price');
            initMask('internet_cost_input_nett', 'price_nett_hidden',      'price_nett');
            initMask('region_price_input',       'region_price_hidden',    'region_price');
            initMask('region_price_nett_input',  'region_price_nett_hidden', 'region_price_nett');
        }

        initAllMasks();

        Livewire.hook('message.processed', (message, component) => {
            setTimeout(() => initAllMasks(), 100);
        });
    });
</script>
@endpush

@endcanAccess
@endcanAccess