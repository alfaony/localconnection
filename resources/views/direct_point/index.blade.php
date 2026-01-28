@extends('adminlte::page')

@section('content_header')
    <h1>Direct Point</h1>
@stop

@section('content')
<div class="container-fluid">
    @include('components.alert')
    <div class="card shadow-sm">
        <div class="card-header bg-gradient-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0"><i class="fas fa-gift"></i> Direct Point</h3>
                @canAccess('create','direct_points')
                <a href="{{ route('direct-point.create') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-plus"></i> Buat Direct Point
                </a>
                @endcanAccess
            </div>
        </div>
        
        <div class="card-body">
            {{-- Filters --}}
            <div class="card bg-light mb-4">
                <div class="card-body pb-2">
                    <form action="{{ route('direct-point.index') }}" method="GET">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-bold small">Status</label>
                                <select name="status" class="form-control form-control-sm">
                                    <option value="">Semua Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                        <i class="fas fa-clock"></i> Pending
                                    </option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                                        <i class="fas fa-check"></i> Approved
                                    </option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                        <i class="fas fa-times"></i> Rejected
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="font-weight-bold small">Divisi</label>
                                <select name="division_id" class="form-control form-control-sm">
                                    <option value="">Semua Divisi</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                            {{ $division->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="font-weight-bold small">Dari Tanggal</label>
                                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="font-weight-bold small">Sampai Tanggal</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-2 mb-3 align-self-end">
                                <button type="submit" class="btn btn-primary btn-sm btn-block">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                @if(request()->anyFilled(['status', 'division_id', 'start_date', 'end_date']))
                                    <a href="{{ route('direct-point.index') }}" class="btn btn-secondary btn-sm btn-block mt-1">
                                        <i class="fas fa-redo"></i> Reset
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th width="150">Tanggal</th>
                            <th>Dari</th>
                            <th>Kepada</th>
                            <th>Divisi</th>
                            <th width="100" class="text-center">Point</th>
                            <th width="120" class="text-center">Status</th>
                            <th width="80" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($directPoints as $dp)
                            <tr>
                                <td>
                                    <small class="text-muted">
                                        <i class="far fa-calendar-alt"></i> 
                                        {{ $dp->created_at->format('d M Y') }}
                                    </small><br>
                                    <small class="text-muted">
                                        <i class="far fa-clock"></i> 
                                        {{ $dp->created_at->format('H:i') }}
                                    </small>
                                </td>
                                <td>
                                    <i class="fas fa-user text-secondary"></i> 
                                    {{ $dp->fromUser->name }}
                                </td>
                                <td>
                                    <i class="fas fa-user-check text-success"></i> 
                                    {{ $dp->toUser->name }}
                                </td>
                                <td>
                                    <i class="fas fa-building text-info"></i> 
                                    {{ $dp->division->name }}
                                </td>
                                <td class="text-center">
                                    <h5 class="mb-0">
                                        <span class="badge badge-pill badge-info">
                                            <i class="fas fa-coins"></i> {{ $dp->point }}
                                        </span>
                                    </h5>
                                </td>
                                <td class="text-center">{!! $dp->status_badge !!}</td>
                                <td class="text-center">
                                    <a href="{{ route('direct-point.show', $dp->id) }}" 
                                       class="btn btn-sm btn-outline-info" 
                                       title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Tidak ada data Direct Point</p>
                                    <a href="{{ route('direct-point.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Buat Direct Point
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($directPoints->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $directPoints->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .table thead th {
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@stop
