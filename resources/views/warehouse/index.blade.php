@extends('adminlte::page')

@section('title', 'Manajemen Gudang')

@section('content_header')
    <!-- Link Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map {
            height: 400px;
            width: 100%;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .leaflet-popup-content {
            font-size: 14px;
        }
    </style>
@endsection

@section('content')
<!-- Notifikasi -->
@if(Session::has('store'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <strong>Berhasil!</strong> Gudang Berhasil Ditambahkan.
</div>
@endif
@if(Session::has('update'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <strong>Berhasil!</strong> Gudang Berhasil Diperbarui.
</div>
@endif
@if(Session::has('delete'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <strong>Berhasil!</strong> Gudang Berhasil Terhapus.
</div>
@endif
@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
    <strong>Kesalahan!</strong> Periksa kembali input Anda.
    <ul class="mt-2">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Form Tambah/Edit Gudang -->
@canAccess('store','warehouses')
@canAccess('update','warehouses')
<div class="card shadow mb-4 mt-3">
    <div class="card-header bg-primary text-white text-center">
        <h5>{{ isset($warehouseEdit) ? 'Edit Gudang' : 'Tambah Gudang' }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ isset($warehouseEdit) ? route('warehouse.update', $warehouseEdit->id) : route('warehouse.store') }}"
            method="POST">
            @csrf
            @if(isset($warehouseEdit))
            @method('PUT')
            @endif
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Nama Gudang:</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ isset($warehouseEdit) ? $warehouseEdit->name : '' }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Jenis Gudang:</label>
                        <select name="warehouse_type_id" class="form-control" required>
                            @foreach ($warehouse_types as $type)
                            <option value="{{ $type->id }}"
                                {{ isset($warehouseEdit) && $warehouseEdit->warehouse_type_id == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Alamat Gudang:</label>
                <textarea name="location" id="location" class="form-control" rows="2"
                    required>{{ isset($warehouseEdit) ? $warehouseEdit->location : '' }}</textarea>
            </div>

            <!-- Tombol Aksi Peta -->
            <div class="text-center mb-3">
                <button type="button" id="getLocation" class="btn btn-warning">
                    <i class="fa fa-location-arrow"></i> Ambil Lokasi Saya
                </button>
                {{-- 
                <button type="button" id="searchAddress" class="btn btn-info">
                    <i class="fa fa-search"></i> Cari dari Alamat
                </button>
                <button type="button" id="setManually" class="btn btn-secondary">
                    <i class="fa fa-pencil"></i> Set Koordinat Manual
                </button>
                --}}
            </div>

            <!-- Peta Interaktif -->
            <div class="form-group mb-3">
                <label class="form-label">
                    Pilih Lokasi di Peta: 
                    <small class="text-muted">(Klik peta atau drag marker untuk mengubah lokasi)</small>
                </label>
                <div id="map"></div>
            </div>

            <!-- Koordinat -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Garis Lintang (Latitude):</label>
                        <input type="number" step="any" name="latitude" id="latitude" class="form-control"
                            value="{{ isset($warehouseEdit) ? $warehouseEdit->latitude : '' }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Garis Bujur (Longitude):</label>
                        <input type="number" step="any" name="longitude" id="longitude" class="form-control"
                            value="{{ isset($warehouseEdit) ? $warehouseEdit->longitude : '' }}" required>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-save"></i>
                    {{ isset($warehouseEdit) ? 'Simpan Perubahan' : 'Tambah Gudang' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endcanAccess
@endcanAccess

<!-- Daftar Gudang -->
<div class="card shadow">
    <div class="card-header bg-success text-white">
        <div class="row align-items-center">
            <div class="col-md-9 col-sm-12 text-center text-md-start">
                <h5 class="mb-0">Daftar Gudang</h5>
            </div>
            <div class="col-md-3 col-sm-12">
                <form method="GET" action="{{ route('warehouse.index') }}">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" placeholder="Search..."
                            value="{{ request('search') }}">
                        <button class="btn btn-light px-3 btn-sm ml-2" type="submit">
                            <i class="fa fa-search"></i> Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped table-bordered">
            <thead>
                <tr class="text-center">
                    <th>#</th>
                    <th>Nama Gudang</th>
                    <th>Jenis</th>
                    <th>Alamat</th>
                    <th>Koordinat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($warehouses as $warehouse)
                <tr class="text-center">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $warehouse->name }}</td>
                    <td>{{ $warehouse->warehouseType->name }}</td>
                    <td>{{ $warehouse->location }}</td>
                    <td>
                        <a href="https://www.openstreetmap.org/?mlat={{ $warehouse->latitude }}&mlon={{ $warehouse->longitude }}&zoom=15" 
                           target="_blank" class="text-primary">
                            {{ number_format($warehouse->latitude, 6) }}, {{ number_format($warehouse->longitude, 6) }}
                        </a>
                    </td>
                    <td>
                        @canAccess('store','warehouses')
                        <a href="{{ route('warehouse.edit', $warehouse->id) }}" class="btn btn-sm btn-info">
                            <i class="fa fa-edit"></i> Edit
                        </a>
                        @endcanAccess

                        @canAccess('store','warehouses')
                        <form action="{{ route('warehouse.destroy', $warehouse->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Apakah Anda yakin ingin menghapus gudang ini?')"
                                type="submit" class="btn btn-sm btn-danger">
                                <i class="fa fa-trash"></i> Hapus
                            </button>
                        </form>
                        @endcanAccess
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada data gudang</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            {{ $warehouses->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>

@stop

@section('js')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let map, marker;

    // Inisialisasi koordinat default (Jakarta) atau dari data warehouse
    let defaultLat = {{ isset($warehouseEdit) && $warehouseEdit->latitude ? $warehouseEdit->latitude : '-6.2088' }};
    let defaultLng = {{ isset($warehouseEdit) && $warehouseEdit->longitude ? $warehouseEdit->longitude : '106.8456' }};

    // Inisialisasi peta
    function initMap() {
        map = L.map('map').setView([defaultLat, defaultLng], 13);
        
        // Gunakan OpenStreetMap tiles (gratis)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Tambahkan marker
        marker = L.marker([defaultLat, defaultLng], {
            draggable: true
        }).addTo(map);
        
        // Update koordinat saat marker di-drag
        marker.on('dragend', function(e) {
            let position = marker.getLatLng();
            updateCoordinates(position.lat, position.lng);
        });
        
        // Update koordinat saat peta di-klik
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateCoordinates(e.latlng.lat, e.latlng.lng);
        });
    }

    // Update input koordinat dan peta
    function updateCoordinates(lat, lng) {
        document.getElementById('latitude').value = lat.toFixed(8);
        document.getElementById('longitude').value = lng.toFixed(8);
        marker.setLatLng([lat, lng]);
        map.panTo([lat, lng]);
    }

    // Tombol: Ambil Lokasi Saya
    document.getElementById('getLocation').addEventListener('click', function() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    let lat = position.coords.latitude;
                    let lng = position.coords.longitude;
                    updateCoordinates(lat, lng);
                    map.setView([lat, lng], 15);
                    alert('Lokasi berhasil didapatkan!');
                },
                function(error) {
                    alert('Gagal mendapatkan lokasi: ' + error.message);
                }
            );
        } else {
            alert('Browser Anda tidak mendukung Geolocation.');
        }
    });



    // Event listener untuk input manual koordinat
    document.getElementById('latitude').addEventListener('change', function() {
        let lat = parseFloat(this.value);
        let lng = parseFloat(document.getElementById('longitude').value);
        if (!isNaN(lat) && !isNaN(lng)) {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], 13);
        }
    });

    document.getElementById('longitude').addEventListener('change', function() {
        let lat = parseFloat(document.getElementById('latitude').value);
        let lng = parseFloat(this.value);
        if (!isNaN(lat) && !isNaN(lng)) {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], 13);
        }
    });

    // Inisialisasi peta saat halaman dimuat
    document.addEventListener('DOMContentLoaded', initMap);
</script>
@stop