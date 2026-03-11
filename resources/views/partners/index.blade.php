@extends('adminlte::page')

@section('title', 'Daftar Mitra')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1><i class="fas fa-handshake"></i> Manajemen Mitra</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Mitra</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')
    <div class="row mb-3">
        <div class="col-12">
            @canAccess('create','partners')
            <a href="{{ route('partner.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Mitra Baru
            </a>
            @endcanAccess
        </div>
    </div>

    <!-- Filters -->
    <div class="card collapsed-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Filter
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('partner.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama atau industri..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="partner_type_id" class="form-control">
                        <option value="">Semua Tipe</option>
                        @foreach($partnerTypes as $type)
                            <option value="{{ $type->id }}" {{ request('partner_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        @foreach(config('partners.partner_status') as $key => $value)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary btn-block">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('partner.index') }}" class="btn btn-default btn-block">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Partners Table -->
    <div class="card">
        <div class="card-body">
            @if($partners->count() > 0)
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th>Industri</th>
                            <th>Status</th>
                            <th>Tersertifikasi</th>
                            <th>Tanggal Kemitraan</th>
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($partners as $partner)
                            <tr>
                                <td>
                                    <strong>{{ $partner->name }}</strong>
                                    @if($partner->website)
                                        <br>
                                        <a href="{{ $partner->website }}" target="_blank" class="text-muted small">
                                            <i class="fas fa-external-link-alt"></i> {{ $partner->website }}
                                        </a>
                                    @endif
                                </td>
                                <td><span class="badge badge-info">{{ $partner->partner_type_name }}</span></td>
                                <td>{{ $partner->industry ?? '-' }}</td>
                                <td>
                                    @if($partner->status == 'active')
                                        <span class="badge badge-success">{{ ucwords($partner->status) }}</span>
                                    @elseif($partner->status == 'inactive')
                                        <span class="badge badge-secondary">{{ ucwords($partner->status) }}</span>
                                    @else
                                        <span class="badge badge-warning">{{ ucwords($partner->status) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($partner->is_certified)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Ya
                                        </span>
                                        @if($partner->certification_level)
                                            <br><small class="text-muted">{{ $partner->certification_level_name }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">Tidak</span>
                                    @endif
                                </td>
                                <td>{{ $partner->partnership_started_at ? $partner->partnership_started_at->format('d M Y') : '-' }}</td>
                                <td>
                                    <div class="btn-group">
                                        @canAccess('dashboard','partner_dashboards')
                                        <a href="{{ route('partner.dashboard', $partner) }}" class="btn btn-sm btn-info mb-1 mr-1" title="Dashboard">
                                            <i class="fas fa-chart-line"></i>
                                        </a>
                                        @endcanAccess
                                        @canAccess('show','partners')
                                        <a href="{{ route('partner.show', $partner) }}" class="btn btn-sm btn-primary mb-1 mr-1" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcanAccess
                                        @canAccess('edit','partners')   
                                        <a href="{{ route('partner.edit', $partner) }}" class="btn btn-sm btn-warning mb-1 mr-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcanAccess
                                        @canAccess('destroy','partners')
                                        <form action="{{ route('partner.destroy', $partner) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger mb-1 mr-1" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcanAccess
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $partners->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    @canAccess('create','partners')
                        <p class="text-muted">Tidak ada mitra ditemukan. <a href="{{ route('partner.create') }}">Tambah mitra pertama Anda</a></p>
                        <p class="text-muted">Tidak ada mitra ditemukan.</p>
                    @endcanAccess
                </div>
            @endif
        </div>
    </div>
@stop