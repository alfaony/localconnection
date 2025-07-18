@section('content_header')
    <h1>{{ $pop ? 'Edit POP' : 'Tambah POP Baru' }}</h1>
@stop

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Form POP</h3>
    </div>
    
    <form wire:submit.prevent="save">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nama POP <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            wire:model="name"
                            class="form-control @error('name') is-invalid @enderror"
                            placeholder="Contoh: POP Jakarta Pusat"
                        >
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Biaya Perbulan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input 
                                type="text" 
                                wire:model="monthly_cost"
                                id="monthly_cost_input"
                                class="form-control @error('monthly_cost') is-invalid @enderror"
                                placeholder="Contoh: 1.000.000"
                            >
                            @error('monthly_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Perpanjang Sewa <span class="text-danger">*</span></label>
                        <input 
                            type="date" 
                            wire:model="lease_expiration_date"
                            class="form-control @error('lease_expiration_date') is-invalid @enderror"
                        >
                        @error('lease_expiration_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label> Data Center <span class="text-danger">*</span></label>
                        <select class="form-control select2" multiple wire:model="selectedDataCenters">
                            @foreach($dataCenters as $dataCenter)
                                <option value="{{ $dataCenter->id }}" >{{ $dataCenter->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Koordinat <span class="text-danger">*</span></label>

                        <div wire:ignore style="height: 400px;">
                            <div id="map" style="height: 100%; width: 100%;"></div>
                        </div>

                        <small class="form-text text-muted">Klik pada peta untuk memilih titik lokasi atau geser pin.</small>

                        <div class="row">
                            <div class="col-md-6">
                                <label>Latitude</label>
                                <input type="text" class="form-control" wire:model="latitude" readonly>
                                @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label>Longitude</label>
                                <input type="text" class="form-control" wire:model="longitude" readonly>
                                @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                    </div>
                </div>
                {{-- 
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kapasitas (MB) <span class="text-danger">*</span></label>
                        <input 
                            type="number" 
                            wire:model="capacity_mb"
                            class="form-control @error('capacity_mb') is-invalid @enderror"
                            placeholder="Contoh: 1000"
                        >
                        @error('capacity_mb') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                --}}
            </div>
            
            <div class="form-group">
                <label>Alamat Lengkap <span class="text-danger">*</span></label>
                <textarea 
                    wire:model="address"
                    rows="3"
                    class="form-control @error('address') is-invalid @enderror"
                    placeholder="Contoh: Jl. Sudirman No. 123, Jakarta Pusat"
                ></textarea>
                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            
        </div>

        <!-- Bagian Input Jalur Masuk (Selalu Tampil) -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Jalur Masuk Data Center</h3>
                <div class="card-tools">
                    <button 
                        type="button" 
                        wire:click="addEntry"
                        class="btn btn-sm btn-light {{ $entryCount >= 5 ? 'disabled' : '' }}"
                        {{ $entryCount >= 5 ? 'disabled' : '' }}
                    >
                        <i class="fas fa-plus mr-1"></i> Tambah Jalur
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($entries as $index => $entry)
                        <div class="col-md-6">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Jalur {{ $index + 1 }}</h3>
                                        <div class="card-tools">
                                            <button 
                                                type="button" 
                                                wire:click="removeEntry({{ $index }})"
                                                class="btn btn-sm btn-danger"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Nama Jalur</label>
                                        <input 
                                            type="text" 
                                            wire:model="entries.{{ $index }}.name"
                                            class="form-control @error('entries.'.$index.'.name') is-invalid @enderror"
                                            placeholder="Contoh: Jalur Utama"
                                        >
                                        @error('entries.'.$index.'.name') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Kapasitas (MB)</label>
                                        <input 
                                            type="number" 
                                            wire:model="entries.{{ $index }}.capacity_mb"
                                            class="form-control @error('entries.'.$index.'.capacity_mb') is-invalid @enderror"
                                            placeholder="Contoh: 500"
                                        >
                                        @error('entries.'.$index.'.capacity_mb') 
                                            <div class="invalid-feedback">{{ $message }}</div> 
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    <!-- Tampilkan jika belum ada jalur -->
                    @if(count($entries) === 0)
                        <div class="col-12 text-center py-4">
                            <i class="fas fa-plug fa-2x text-muted mb-2"></i>
                            <p class="text-muted">Belum ada jalur ditambahkan</p>
                            <button 
                                type="button" 
                                wire:click="addEntry"
                                class="btn btn-primary btn-sm"
                            >
                                <i class="fas fa-plus mr-1"></i> Tambah Jalur Pertama
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan POP
            </button>
            <a href="{{ route('pop.index') }}" class="btn btn-default float-right">
                Batal
            </a>
        </div>
    </form>
</div>

@push('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/imask"></script>
    <script>
        document.addEventListener('livewire:load', () => {
            let map;
            let marker;

            // Fungsi untuk inisialisasi peta
            function initializeMap(lat, lng) {
                const latLng = [lat, lng];

                map = L.map('map').setView(latLng, 13);

                // Tambahkan tile layer dari OpenStreetMap
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                marker = L.marker(latLng, {
                    draggable: true
                }).addTo(map);

                // Set nilai awal ke Livewire
                @this.set('latitude', lat);
                @this.set('longitude', lng);

                // Update saat klik peta
                map.on('click', function(e) {
                    const lat = e.latlng.lat.toFixed(6);
                    const lng = e.latlng.lng.toFixed(6);
                    marker.setLatLng([lat, lng]);
                    @this.set('latitude', lat);
                    @this.set('longitude', lng);
                });

                // Update saat marker digeser
                marker.on('dragend', function(e) {
                    const position = marker.getLatLng();
                    const lat = position.lat.toFixed(6);
                    const lng = position.lng.toFixed(6);
                    @this.set('latitude', lat);
                    @this.set('longitude', lng);
                });
            }

            // Gunakan koordinat dari database saat edit
            @this.on('loadMapWithCoordinates', (lat, lng) => {
                if (lat && lng) {
                    initializeMap(lat, lng);
                }
            });

            // Jika tidak ada data, ambil dari Geolocation
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        initializeMap(lat, lng);
                    },
                    function(error) {
                        console.warn('Gagal ambil lokasi, fallback ke Monas');
                        initializeMap(-6.175392, 106.827153); // fallback: Monas Jakarta
                    }
                );
            } else {
                initializeMap(-6.175392, 106.827153); // fallback: Monas
            }
        });
    </script>
    <script>
        function initSelect2() {
            $('.select2').select2();
            $('.select2').on('change', function (e) {
                let data = $(this).val();
                @this.set('selectedDataCenters', data);
            });
        }

        document.addEventListener("livewire:load", function () {
            initSelect2();

            Livewire.hook('message.processed', function () {
                initSelect2();
            });
        });
    </script>
    <script>
        document.addEventListener('livewire:load', function() {
            // Format input biaya
            const costInput = document.getElementById('monthly_cost_input');
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

            // Geolocation handler
            window.getLocation = function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        position => {
                            const coords = `${position.coords.latitude},${position.coords.longitude}`;
                            @this.set('coordinates', coords);
                        },
                        error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error: ' + error.message,
                            });
                        }
                    );
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Browser tidak mendukung geolocation!',
                    });
                }
            };
        });
    </script>
@endpush

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    />
    <style>
        #map {
            height: 300px;
            width: 100%;
            border-radius: 5px;
        }
    </style>
    <style>
        .select2-container--default .select2-selection--single 
        {
            height: 38px !important;
            padding: 5px 10px !important;
        }
        .select2-selection__choice
        {
            background-color: #007bff !important;
            border: 1px solid #007bff !important;
        }

        .select2-selection__choice__remove
        {
            color: #fe0700 !important;
            border: 1px solid #007bff !important;
        }
    </style>
    <style>
        .custom-control-label::before, 
        .custom-control-label::after {
            top: .25rem;
        }
        .card-header .card-tools {
            position: absolute;
            right: 1.25rem;
            top: 1.25rem;
        }
    </style>
     <style>
        .entry-card {
            transition: all 0.3s ease;
        }
        
        .entry-card:last-child {
            animation: highlight 1s ease;
        }
        
        @keyframes highlight {
            0% { background-color: rgba(0, 123, 255, 0.1); }
            100% { background-color: transparent; }
        }
        
        .card-header .card-tools {
            position: static;
            margin-top: 0;
        }
    </style>
@endpush