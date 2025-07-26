@section('content_header')
    <h1>{{ $isEdit ? 'Edit Optical Distribution System' : 'Tambah Optical Distribution System Baru' }}</h1>
@stop

@canAccess('store', 'optical_distributions')
@canAccess('update', 'optical_distributions')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Form ODS</h3>
    </div>
    
    <form wire:submit.prevent="save">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Nama ODS <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" wire:model.defer="name" placeholder="Contoh: ODS Gedung A">
                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="capacity_mb">Kapasitas (MB) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('capacity_mb') is-invalid @enderror" 
                                id="capacity_mb" wire:model.defer="capacity_mb" min="1">
                        @error('capacity_mb') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="user_assign_id">Teknisi Penanggung Jawab <span class="text-danger">*</span></label>
                        <select class="form-control select2 @error('user_assign_id') is-invalid @enderror" 
                                id="user_assign_id" wire:model.defer="user_assign_id">
                            <option value="">Pilih Teknisi</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('user_assign_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="selectedPop">POP Terkait <span class="text-danger">*</span></label>
                        <select multiple class="form-control select2 @error('selectedPop') is-invalid @enderror" 
                                id="selectedPop" wire:model="selectedPop" style="width: 100%;">
                            @foreach($pops as $pop)
                                <option value="{{ $pop->id }}">{{ $pop->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedPop') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="address">Alamat</label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                    id="address" wire:model.defer="address" placeholder="Alamat lengkap">
                        </div>
                        @error('address') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

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
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="location_photo">Foto Lokasi</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('location_photo') is-invalid @enderror" 
                                    id="location_photo" wire:model="location_photo">
                            <label class="custom-file-label" for="location_photo">
                                @if($location_photo)
                                    {{ $location_photo->getClientOriginalName() }}
                                @else
                                    Pilih file gambar
                                @endif
                            </label>
                        </div>
                        @error('location_photo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        
                        @if($temp_photo && !$location_photo)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $temp_photo) }}" alt="Current Photo" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan
            </button>
            <a href="{{ route('optical-distribution.index') }}" class="btn btn-default float-right">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </form>
</div>

@if($showMapModal)
    <div class="modal fade show" id="mapModal" style="display: block; padding-right: 17px;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Pilih Lokasi di Peta</h4>
                    <button type="button" class="close" wire:click="$set('showMapModal', false)">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="map" style="height: 500px; width: 100%;"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" wire:click="$set('showMapModal', false)">
                        Batal
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="updateLocation(map.getCenter().lat(), map.getCenter().lng())">
                        Gunakan Lokasi Ini
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
@endif

@push('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            padding: .375rem .75rem;
        }
        .select2-container .select2-selection--multiple {
            min-height: 38px;
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
@endpush

@push('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('livewire:load', function() 
        {
            let map;
            let marker;

            // Refresh CSRF token setiap 15 menit
            setInterval(() => {
                Livewire.emit('refreshCsrfToken');
            }, 15 * 60 * 1000); // 15 menit

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
                        const latDb = "{{ $latitude }}" ?? null;
                        const lngDb = "{{ $longitude }}" ?? null;
                        if (latDb && lngDb) 
                        {
                            initializeMap(latDb, lngDb);
                            return;
                        }

                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        initializeMap(lat, lng);
                    },
                    function(error) {
                        console.warn('Gagal ambil lokasi, fallback ke Monas');
                        initializeMap(-6.175392, 106.827153); // fallback: Monas Jakarta
                    }
                );
            } else 
            {
                initializeMap(-6.175392, 106.827153); // fallback: Monas
            }
            // Initialize Select2
            $('.select2').select2({
                placeholder: "Pilih POP terkait atau teknisi",
                allowClear: true,
                width: 'resolve'
            }).on('change', function (e) {
                let id = $(this).attr('id');
                let value = $(this).val();

                if (id === 'selectedPop') {
                    @this.set('selectedPop', value);
                } else if (id === 'user_assign_id') {
                    @this.set('user_assign_id', value);
                }
            });

            window.getLocation = function() 
            {
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
            

            // Update Select2 when Livewire updates the selected values
            Livewire.hook('message.processed', () => {
                $('.select2').select2({
                    placeholder: "Pilih POP terkait",
                    allowClear: true
                });
            });

            // File input label
            $('.custom-file-input').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            // Initialize map when modal is shown
            Livewire.on('showMapModal', () => {
                initMap();
            });

            function initMap() {
                if (document.getElementById('map')) {
                    // Initialize map
                    var map = L.map('map').setView([-2.5489, 118.0149], 5);
                    
                    // Add tile layer
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);
                    
                    // Add marker if latitude and longitude exist
                    if (@this.latitude && @this.longitude) {
                        var marker = L.marker([@this.latitude, @this.longitude]).addTo(map);
                        map.setView([@this.latitude, @this.longitude], 15);
                    }
                    
                    // Add click event to place marker
                    map.on('click', function(e) {
                        if (marker) {
                            map.removeLayer(marker);
                        }
                        marker = L.marker(e.latlng).addTo(map);
                        @this.set('latitude', e.latlng.lat);
                        @this.set('longitude', e.latlng.lng);
                    });
                    
                    // Make map available globally for the updateLocation function
                    window.map = map;
                }
            }
        });
    </script>
@endpush
@endcanAccess
@endcanAccess