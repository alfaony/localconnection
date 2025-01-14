@extends('adminlte::page')

@section('title', 'Manajemen Sensor')

@section('content')
<!-- Notifikasi -->
@if(Session::has('store'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <strong>Berhasil!</strong> Sensor Berhasil Ditambahkan.
</div>
@endif
@if(Session::has('update'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <strong>Berhasil!</strong> Sensor Berhasil Diperbarui.
</div>
@endif
@if(Session::has('delete'))
<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <strong>Berhasil!</strong> Sensor Berhasil Terhapus.
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

<!-- Form Tambah/Edit Sensor -->
 @canAccess('store','sensors')
 @canAccess('update','sensors')
<div class="card shadow mb-4 mt-3">
    <div class="card-header bg-primary text-white text-center">
        <h5>{{ isset($sensor) ? 'Edit Sensor' : 'Tambah Sensor' }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ isset($sensor) ? route('sensor.update', $sensor->id) : route('sensor.store') }}"
            method="POST">
            @csrf
            @if(isset($sensor))
            @method('PUT')
            @endif
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Nama Sensor:</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ isset($sensor) ? $sensor->name : '' }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Jenis Sensor:</label>
                        <select name="type" class="form-control" required>
                            <option value="" disabled selected>Pilih Jenis Sensor</option>
                            @foreach ($sensorType as $key => $value)
                                <option value="{{ $key }}" {{ isset($sensor) && $sensor->type == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="btn btn-primary">
                    {{ isset($sensor) ? '💾 Simpan Perubahan' : '➕ Tambah Sensor' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endcanAccess
@endcanAccess

<!-- Daftar Sensor -->
<div class="card shadow">
    <div class="card-header bg-success text-white text-center">
        <h5>Daftar Sensor</h5>
    </div>
    <div class="card-body">
        <table class="table table-striped table-bordered">
            <thead>
                <tr class="text-center">
                    <th>#</th>
                    <th>Nama</th>
                    <th>Jenis</th>
                    <th>Pengguna</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sensors as $sensor)
                <tr class="text-center">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $sensor->name }}</td>
                    <td>{{ $sensor->type }}</td>
                    <td>{{ $sensor->user->name ?? '-' }}</td>
                    <td>
                         @canAccess('update','sensors')
                        <a href="{{ route('sensor.edit', $sensor->id) }}" class="btn btn-sm btn-info">
                            ✏️ Edit
                        </a>
                        @endcanAccess
                        @canAccess('destroy','sensors')
                        <form action="{{ route('sensor.destroy', $sensor->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Apakah Anda yakin ingin menghapus sensor ini?')" type="submit" class="btn btn-sm btn-danger">🗑 Hapus</button>
                        </form>
                        @endcanAccess
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada data sensor</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="d-flex justify-content-center">
            {{ $sensors->withQueryString()->links('vendor.pagination.bootstrap-4') }}
            <!-- Pagination -->
        </div>
    </div>
</div>

@stop