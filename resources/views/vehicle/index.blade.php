@extends('adminlte::page')

@section('title', 'Kendaraan')
@section('content_header')
    <h1>Daftar Kendaraan</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>List Kendaraan</span>
        <div class="ml-auto">
            @canAccess('create','vehicles')
            <a href="{{ route('vehicle.create') }}" class="btn btn-sm btn-primary">+ Tambah Kendaraan</a>
            @endcanAccess
        </div>
    </div>
    
    <div class="card-body">
        @include('components.alert')
        
        <div class="d-flex justify-content-end mb-3">
            <form action="{{ route('vehicle.index') }}" method="get" class="mt-3">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" value="{{ request()->query('search') }}" placeholder="Cari...">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID Kendaraan</th>
                        <th>Jenis</th>
                        <th>Posisi</th>
                        <th>PIC</th>
                        <th>STNK</th>
                        <th>KIR</th>
                        <th>Service</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($vehicles as $vehicle)
                    <tr>
                        <td>{{ $vehicle->vehicle_id }}</td>
                        <td>{{ ucfirst($vehicle->vehicle_type) ?? "-" }}</td>
                        <td>{{ $vehicle->position }}</td>
                        <td>{{ $vehicle->picUser->name ?? '-' }}</td>
                        <td>{{ $vehicle->subscription_stnk ?? '-' }}</td>
                        <td>{{ $vehicle->subscription_kir ?? '-' }}</td>
                        <td>{{ $vehicle->service_terakhir ?? '-' }}</td>
                        <td>
                            @canAccess('show', 'vehicles')
                            <a href="{{ route('vehicle.show', $vehicle->id) }}" class="btn btn-sm btn-info"><i class="fa fa-eye"></i></a>
                            @endcanAccess
                            @canAccess('edit', 'vehicles')
                            <a href="{{ route('vehicle.edit', $vehicle->id) }}" class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></a>
                            @endcanAccess
                            @canAccess('destroy', 'vehicles')
                            @endcanAccess
                            <form action="{{ route('vehicle.destroy', $vehicle->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus kendaraan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">Belum ada kendaraan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $vehicles->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>
@stop