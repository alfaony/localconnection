@extends('adminlte::page')

@section('title', 'Daftar Aktivasi KYE')


@section('content')
@include('components.alert')
<div class="row">
    <div class="col-md-12">
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-list"></i> Daftar Aktivasi KYE</h4>
                <div class="ml-auto">
                    <form action="{{ route('kye.index') }}" method="GET" class="d-flex align-items-center">
                        <div class="input-group">
                            <input 
                            type="text" 
                            name="search" 
                            class="form-control form-control-sm" 
                            placeholder="Cari nama, email, atau KTP" 
                            value="{{ request('search') }}" 
                            aria-label="Search">
                            <button type="submit" class="btn btn-sm btn-primary ml-2">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body">
                @if($kyeRecords->isEmpty())
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-info-circle"></i> Tidak ada data ditemukan.
                    </div>
                @else
                    @canAccess('export','kyes')
                    <a href="{{ route('kye.export', request()->all()) }}" class="btn btn-sm btn-success mb-2">
                        <i class="fas fa-file-export"></i> Export
                    </a>
                    @endcanAccess
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. KTP</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kyeRecords as $index => $kye)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $kye->full_name }}</td>
                                    <td>{{ $kye->email }}</td>
                                    <td>{{ $kye->ktp_number }}</td>
                                    <td>
                                        <span class="badge 
                                            {{ $kye->approval_status === 'pending' ? 'bg-warning' : 
                                                ($kye->approval_status === 'approved' ? 'bg-success' : 'bg-danger') }}">
                                            {{ ucfirst($kye->approval_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @canAccess('show','kyes')
                                        <a href="{{ route('kye.show', $kye->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcanAccess
                                        @canAccess('update','kyes')
                                        @if($kye->isEdit())
                                        <a href="{{ route('kye.edit', $kye->id) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        @endcanAccess

                                        @canAccess('destroy','kyes')
                                        @if($kye->isDestroy())
                                        <form action="{{ route('kye.destroy', $kye->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @endcanAccess
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
        
                    <div class="d-flex justify-content-center mt-4">
                        {{ $kyeRecords->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
