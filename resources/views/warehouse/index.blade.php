@extends('adminlte::page')

@section('title', 'Manajemen Gudang')

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
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Form Tambah/Edit Gudang -->
@canAccess('store','warehouses')
@canAccess('update','warehouses')
<div class="card shadow mb-4 mt-3">
    <div class="card-header bg-primary text-white text-center">
        <h5>{{ isset($warehouse) ? 'Edit Gudang' : 'Tambah Gudang' }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ isset($warehouse) ? route('warehouse.update', $warehouse->id) : route('warehouse.store') }}"
            method="POST">
            @csrf
            @if(isset($warehouse))
            @method('PUT')
            @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Nama Gudang:</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ isset($warehouse) ? $warehouse->name : '' }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Jenis Gudang:</label>
                        <select name="warehouse_type_id" class="form-control" required>
                            @foreach ($warehouse_types as $type)
                            <option value="{{ $type->id }}"
                                {{ isset($warehouse) && $warehouse->warehouse_type_id == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Alamat Gudang:</label>
                <textarea name="location" class="form-control" rows="2"
                    required>{{ isset($warehouse) ? $warehouse->location : '' }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Garis Lintang (Latitude):</label>
                        <input type="text" name="latitude" id="latitude" class="form-control"
                            value="{{ isset($warehouse) ? $warehouse->latitude : '' }}" readonly required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Garis Bujur (Longitude):</label>
                        <input type="text" name="longitude" id="longitude" class="form-control"
                            value="{{ isset($warehouse) ? $warehouse->longitude : '' }}" readonly required>
                    </div>
                </div>
            </div>

            <div class="text-center mb-3">
                <button type="button" id="getLocation" class="btn btn-warning">📍 Ambil Lokasi</button>
            </div>

            <div class="form-group text-center" id="map-container"
                style="{{ isset($warehouse) ? '' : 'display: none;' }}">
                <iframe id="mapFrame" width="100%" height="200" frameborder="0" class="rounded"
                    src="{{ isset($warehouse) ? 'https://maps.google.com/maps?q='.$warehouse->latitude.','.$warehouse->longitude.'&output=embed' : '' }}"></iframe>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary">
                    {{ isset($warehouse) ? '💾 Simpan Perubahan' : '➕ Tambah Gudang' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endcanAccess
@endcanAccess

<!-- Daftar Gudang -->
<div class="card shadow">
    <div class="card-header bg-success text-white text-center">
        <h5>Daftar Gudang</h5>
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
                    <td>{{ $warehouse->latitude }}, {{ $warehouse->longitude }}</td>
                    <td>
                        @canAccess('store','warehouses')
                        <a href="{{ route('warehouse.edit', $warehouse->id) }}" class="btn btn-sm btn-info">
                            ✏️ Edit
                        </a>
                        @endcanAccess

                        @canAccess('store','warehouses')
                        <form action="{{ route('warehouse.destroy', $warehouse->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Apakah Anda yakin ingin menghapus gudang ini?')" type="submit" class="btn btn-sm btn-danger">🗑 Hapus</button>
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
            <!-- Pagination -->
        </div>
    </div>
</div>

@stop

@section('js')
<script>
document.getElementById("getLocation").addEventListener("click", function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById("longitude").value = position.coords.longitude;
            document.getElementById("latitude").value = position.coords.latitude;
            document.getElementById("map-container").style.display = "block";
            document.getElementById("mapFrame").src =
                `https://maps.google.com/maps?q=${position.coords.latitude},${position.coords.longitude}&output=embed`;
        });
    } else {
        alert("Peramban ini tidak mendukung fitur lokasi.");
    }
});
</script>
@stop