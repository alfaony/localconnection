{{-- index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Manajemen PPP Secrets')

@section('content_header')
    <h1>Daftar PPP Secrets</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <div class="card-tools">
            <a href="{{ route('mikrotik-secret.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Tambah Secret
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <i class="icon fas fa-check"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <i class="icon fas fa-ban"></i> {{ session('error') }}
            </div>
        @endif

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Nama</th>
                    <th>Service</th>
                    <th>Profile</th>
                    <th>Remote Address</th>
                    <th>Status</th>
                    <th style="width: 220px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($secrets as $secret)
                <tr>
                    <td>{{ $secret['name'] ?? '-' }}</td>
                    <td>
                        <span class="badge bg-{{ 
                            $secret['service'] == 'pppoe' ? 'primary' : 
                            ($secret['service'] == 'pptp' ? 'info' : 'warning') 
                        }}">
                            {{ $secret['service'] ?? '-' }}
                        </span>
                    </td>
                    <td>{{ $secret['profile'] ?? '-' }}</td>
                    <td>{{ $secret['remote-address'] ?? '-' }}</td>
                    <td>
                        @if(!empty($secret['disabled']) && $secret['disabled'] === 'true')
                            <span class="badge bg-danger">Tidak Aktif</span>
                        @else
                            <span class="badge bg-success">Aktif</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if(!empty($secret['disabled']) && $secret['disabled'] === 'true')
                        <form action="{{ route('mikrotik-secret.reconnect', $secret['name']) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary" title="Reconnect">
                                <i class="fas fa-sync"></i>
                            </button>
                        </form>
                        @else

                        <form action="{{ route('mikrotik-secret.disconnect', $secret['name']) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary" title="Disconnect">
                                <i class="fas fa-plug"></i>
                            </button>
                        </form>
                        @endif
                        <a href="{{ route('mikrotik-secret.edit', $secret['.id']) }}" class="btn btn-sm btn-warning" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('mikrotik-secret.destroy', $secret['.id']) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button onclick="return confirm('Yakin hapus secret ini?')" class="btn btn-sm btn-danger" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada secret</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    </div>
    @if($secrets->hasPages())
    <div class="card-footer clearfix">
        {{ $secrets->links('pagination::bootstrap-4') }}
    </div>
    @endif
</div>
@stop

@section('css')
<style>
    .table td, .table th {
        vertical-align: middle;
    }
    form.d-inline {
        display: inline-block;
    }
</style>
@stop