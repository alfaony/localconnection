@section('content_header')
    <h1>{{ $isEdit ? 'Edit Optical Distribution Point' : 'Tambah Optical Distribution Point Baru' }}</h1>
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
                        <label for="name">Nama ODP <span class="text-danger">*</span></label>
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

                        <small class="form-text text-muted">Klik pada peta untuk memilih titik lokasi, geser pin, atau ketik koordinat manual di bawah.</small>

                        <div class="row mt-2">
                            <div class="col-md-6">
                                <label>Latitude <span class="text-danger">*</span></label>
                                <input type="number"
                                       step="0.000001"
                                       class="form-control @error('latitude') is-invalid @enderror"
                                       id="latitude-input"
                                       wire:model.debounce.500ms="latitude"
                                       placeholder="Contoh: -6.175392">
                                @error('latitude') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label>Longitude <span class="text-danger">*</span></label>
                                <input type="number"
                                       step="0.000001"
                                       class="form-control @error('longitude') is-invalid @enderror"
                                       id="longitude-input"
                                       wire:model.debounce.500ms="longitude"
                                       placeholder="Contoh: 106.827153">
                                @error('longitude') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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
                                <img src="{{ s3_asset(true,10, $temp_photo) }}" alt="Current Photo" class="img-thumbnail" style="max-height: 150px;">
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
                    <div id="modal-map" style="height: 500px; width: 100%;"></div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" wire:click="$set('showMapModal', false)">
                        Batal
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="updateLocation(modalMap.getCenter().lat(), modalMap.getCenter().lng())">
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
            let deferredCoords = null;
            let inputDebounce;
            let modalMap;
            let modalMarker;

            const latInput = document.getElementById('latitude-input');
            const lngInput = document.getElementById('longitude-input');

            // Refresh CSRF token setiap 15 menit
            setInterval(() => {
                Livewire.emit('refreshCsrfToken');
            }, 15 * 60 * 1000);

            function updateMarkerPosition(lat, lng, options = {}) {
                const latNum = Number.parseFloat(lat);
                const lngNum = Number.parseFloat(lng);

                if (!Number.isFinite(latNum) || !Number.isFinite(lngNum)) {
                    return;
                }

                if (!map) {
                    deferredCoords = { lat: latNum, lng: lngNum, options };
                    return;
                }

                const latLng = [latNum, lngNum];

                if (!marker) {
                    marker = L.marker(latLng, {
                        draggable: true
                    }).addTo(map);

                    marker.on('dragend', function() {
                        const position = marker.getLatLng();
                        const lat = position.lat.toFixed(6);
                        const lng = position.lng.toFixed(6);
                        updateMarkerPosition(lat, lng, { pan: false });
                        @this.set('latitude', lat);
                        @this.set('longitude', lng);
                    });
                } else {
                    marker.setLatLng(latLng);
                }

                if (options.pan !== false) {
                    const zoomLevel = options.zoom ?? map.getZoom();
                    map.setView(latLng, zoomLevel);
                }
            }

            function initializeMap(lat, lng) {
                const latNum = Number.parseFloat(lat);
                const lngNum = Number.parseFloat(lng);

                if (!Number.isFinite(latNum) || !Number.isFinite(lngNum)) {
                    return;
                }

                if (map) {
                    updateMarkerPosition(latNum, lngNum);
                    return;
                }

                map = L.map('map').setView([latNum, lngNum], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                updateMarkerPosition(latNum, lngNum, { pan: false, zoom: 13 });

                const formattedLat = latNum.toFixed(6);
                const formattedLng = lngNum.toFixed(6);

                if (@this.get('latitude') !== formattedLat) {
                    @this.set('latitude', formattedLat);
                }

                if (@this.get('longitude') !== formattedLng) {
                    @this.set('longitude', formattedLng);
                }

                map.on('click', function(e) {
                    const lat = e.latlng.lat.toFixed(6);
                    const lng = e.latlng.lng.toFixed(6);
                    updateMarkerPosition(lat, lng);
                    @this.set('latitude', lat);
                    @this.set('longitude', lng);
                });

                if (deferredCoords) {
                    updateMarkerPosition(deferredCoords.lat, deferredCoords.lng, deferredCoords.options || {});
                    deferredCoords = null;
                }
            }

            function scheduleMarkerUpdate() {
                if (!latInput || !lngInput) {
                    return;
                }

                clearTimeout(inputDebounce);
                inputDebounce = setTimeout(() => {
                    updateMarkerPosition(latInput.value, lngInput.value);
                }, 300);
            }

            function initModalMap() {
                const container = document.getElementById('modal-map');

                if (!container) {
                    return;
                }

                if (modalMap) {
                    modalMap.remove();
                    modalMap = null;
                    modalMarker = null;
                }

                const lat = Number.parseFloat(@this.get('latitude')) || -6.175392;
                const lng = Number.parseFloat(@this.get('longitude')) || 106.827153;

                modalMap = L.map(container).setView([lat, lng], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(modalMap);

                modalMarker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(modalMap);

                modalMarker.on('dragend', function() {
                    const position = modalMarker.getLatLng();
                    const latValue = position.lat.toFixed(6);
                    const lngValue = position.lng.toFixed(6);
                    updateMarkerPosition(latValue, lngValue);
                    @this.set('latitude', latValue);
                    @this.set('longitude', lngValue);
                });

                modalMap.on('click', function(e) {
                    const latValue = e.latlng.lat.toFixed(6);
                    const lngValue = e.latlng.lng.toFixed(6);

                    if (modalMarker) {
                        modalMarker.setLatLng([latValue, lngValue]);
                    }

                    updateMarkerPosition(latValue, lngValue);
                    @this.set('latitude', latValue);
                    @this.set('longitude', lngValue);
                });

                window.modalMap = modalMap;

                setTimeout(() => {
                    modalMap.invalidateSize();
                }, 100);
            }

            if (latInput) {
                latInput.addEventListener('input', scheduleMarkerUpdate);
                latInput.addEventListener('change', scheduleMarkerUpdate);
            }

            if (lngInput) {
                lngInput.addEventListener('input', scheduleMarkerUpdate);
                lngInput.addEventListener('change', scheduleMarkerUpdate);
            }

            window.addEventListener('ods-map-move-marker', (event) => {
                if (!event.detail) {
                    return;
                }

                updateMarkerPosition(event.detail.lat, event.detail.lng);
            });

            @this.on('loadMapWithCoordinates', (lat, lng) => {
                initializeMap(lat, lng);
            });

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const latDb = "{{ $latitude }}" ?? null;
                        const lngDb = "{{ $longitude }}" ?? null;

                        if (latDb && lngDb) {
                            initializeMap(latDb, lngDb);
                            return;
                        }

                        initializeMap(position.coords.latitude, position.coords.longitude);
                    },
                    function() {
                        console.warn('Gagal ambil lokasi, fallback ke Monas');
                        initializeMap(-6.175392, 106.827153);
                    }
                );
            } else {
                initializeMap(-6.175392, 106.827153);
            }

            window.getLocation = function() 
            {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        position => {
                            const lat = position.coords.latitude.toFixed(6);
                            const lng = position.coords.longitude.toFixed(6);
                            updateMarkerPosition(lat, lng);
                            @this.set('latitude', lat);
                            @this.set('longitude', lng);
                        },
                        error => {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Error: ' + error.message,
                                });
                            } else {
                                alert('Error: ' + error.message);
                            }
                        }
                    );
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Browser tidak mendukung geolocation!',
                        });
                    } else {
                        alert('Browser tidak mendukung geolocation!');
                    }
                }
            };

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

            // Update Select2 when Livewire updates the selected values
            Livewire.hook('message.processed', () => {
                $('.select2').select2({
                    placeholder: "Pilih POP terkait",
                    allowClear: true
                });

                if (@this.get('showMapModal')) {
                    initModalMap();
                }
            });

            // File input label
            $('.custom-file-input').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
        });
    </script>
@endpush
@endcanAccess
@endcanAccess
