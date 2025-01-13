@extends('adminlte::page')

@section('title', 'Manajemen Gudang')
@section('content')
<div class="row">
    <div class="col-md-12">
        @if(Session::get('store'))
        <div class="alert alert-success mt-3">Gudang Berhasil Ditambahkan</div>
        @endif
        @if(Session::get('update'))
        <div class="alert alert-success mt-3">Gudang Berhasil Diperbarui</div>
        @endif
        @if(Session::get('delete'))
        <div class="alert alert-success mt-3">Gudang Berhasil Terhapus</div>
        @endif
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">
            <ul>
                <li>{{ session('error') }}</li>
            </ul>
        </div>
    @endif
    </div>
</div>
<div class="row mt-3">
    <!-- Card 1: Form Tambah/Edit Gudang -->
    @canAccess('store','warehouses')
    @canAccess('update','warehouses')
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white text-center font-weight-bold">
                {{ isset($warehouse) ? 'Edit Gudang' : 'Tambah Gudang' }}
            </div>
            <div class="card-body">
                <form action="{{ isset($warehouse) ? route('warehouse.update', $warehouse->id) : route('warehouse.store') }}" method="POST">
                    @csrf
                    @if(isset($warehouse))
                        @method('PUT')
                    @endif

                    <div class="form-group">
                        <label>Nama Gudang:</label>
                        <input type="text" name="name" class="form-control" value="{{ isset($warehouse) ? $warehouse->name : '' }}" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis Gudang:</label>
                        <select name="warehouse_type_id" class="form-control" required>
                            @foreach ($warehouse_types as $type)
                                <option value="{{ $type->id }}" {{ isset($warehouse) && $warehouse->warehouse_type_id == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Alamat Gudang:</label>
                        <textarea name="location" class="form-control" required>{{ isset($warehouse) ? $warehouse->location : '' }}</textarea>
                    </div>
                    <div class="form-group text-center">
                        <button type="button" id="getLocation" class="btn btn-warning">📍 Ambil Lokasi Saat Ini</button>
                    </div>
                    <div class="form-group">
                        <label>Garis Lintang (Latitude):</label>
                        <input type="text" name="latitude" id="latitude" class="form-control" value="{{ isset($warehouse) ? $warehouse->latitude : '' }}" readonly required>
                    </div>
                    <div class="form-group">
                        <label>Garis Bujur (Longitude):</label>
                        <input type="text" name="longitude" id="longitude" class="form-control" value="{{ isset($warehouse) ? $warehouse->longitude : '' }}" readonly required>
                    </div>
                    <div class="form-group text-center" id="map-container" style="{{ isset($warehouse) ? '' : 'display: none;' }}">
                        <iframe id="mapFrame" width="100%" height="200" frameborder="0" class="rounded"
                            src="{{ isset($warehouse) ? 'https://maps.google.com/maps?q='.$warehouse->latitude.','.$warehouse->longitude.'&output=embed' : '' }}"></iframe>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        {{ isset($warehouse) ? '💾 Simpan Perubahan' : '➕ Tambah Gudang' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endcanAccess
    @endcanAccess

    <!-- Card 2: Daftar Gudang -->
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white text-center font-weight-bold">
                Daftar Gudang
            </div>
            <div class="card-body">
                <table class="table table-hover table-bordered">
                    <thead class="thead-light">
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
                        @foreach ($warehouses as $warehouse)
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
                                    <button type="submit" class="btn btn-sm btn-danger">🗑 Hapus</button>
                                </form>
                                @endcanAccess
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex justify-content-center">
                    {{ $warehouses->links() }} <!-- Pagination -->
                </div>
            </div>
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