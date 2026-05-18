@extends('adminlte::page')

@section('title', 'Report Link')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark"><i class="fas fa-link mr-2"></i>Report Link</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Report Link</li>
            </ol>
        </nav>
    </div>
@stop

@section('css')
<style>
.report-link-card {
    border-radius: 10px;
    overflow: hidden;
    transition: box-shadow .2s, transform .2s;
    border: 1px solid #e3e6f0;
}
.report-link-card:hover {
    box-shadow: 0 6px 20px rgba(0,0,0,.12);
    transform: translateY(-2px);
}
.report-link-card .card-thumb {
    height: 180px;
    width: 100%;
    object-fit: cover;
    display: block;
}
.report-link-card .card-thumb-placeholder {
    height: 180px;
    background: #f4f6f9;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
    font-size: 2.5rem;
}
.thumb-wrapper {
    position: relative;
    display: block;
    overflow: hidden;
}
.thumb-wrapper .badge-count {
    position: absolute;
    top: 8px;
    right: 8px;
    font-size: .7rem;
    z-index: 1;
}
.thumb-wrapper .overlay-hover {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.25);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity .2s;
}
.report-link-card:hover .overlay-hover { opacity: 1; }
.overlay-hover i { color: #fff; font-size: 1.8rem; }

.report-link-card .card-title {
    font-size: .95rem;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.report-link-card .link-url {
    font-size: .78rem;
    color: #6c757d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}
</style>
@stop

@section('content')
@include('components.alert')

<div class="mb-3 d-flex justify-content-between align-items-center">
    <small class="text-muted">Daftar laporan beserta link terkait</small>
    @canAccess('store', 'report_links')
    <a href="{{ route('report-link.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus-circle mr-1"></i>Tambah Report Link
    </a>
    @endcanAccess
</div>

@if($reportLinks->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="fas fa-inbox fa-3x mb-3"></i>
        <p class="mb-0">Belum ada Report Link.</p>
    </div>
@else
    <div class="row">
        @foreach($reportLinks as $item)
        @php $firstImage = $item->images->first(); @endphp
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
            <div class="card report-link-card h-100">

                {{-- Thumbnail --}}
                <a href="{{ route('report-link.show', $item->id) }}" class="thumb-wrapper">
                    @if($firstImage)
                        <img src="{{ asset('storage/' . $firstImage->path) }}"
                             class="card-thumb" alt="{{ $item->name }}">
                        @if($item->images->count() > 1)
                            <span class="badge badge-dark badge-count">
                                <i class="fas fa-images mr-1"></i>{{ $item->images->count() }}
                            </span>
                        @endif
                    @else
                        <div class="card-thumb-placeholder">
                            <i class="fas fa-image"></i>
                        </div>
                    @endif
                    <div class="overlay-hover">
                        <i class="fas fa-eye"></i>
                    </div>
                </a>

                <div class="card-body d-flex flex-column py-3 px-3">
                    <a href="{{ route('report-link.show', $item->id) }}"
                       class="card-title text-dark text-decoration-none d-block mb-1"
                       title="{{ $item->name }}">{{ $item->name }}</a>
                    <small class="text-muted mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>{{ $item->date->format('d M Y') }}
                    </small>
                    <a href="{{ $item->link }}" target="_blank" class="link-url mb-3" title="{{ $item->link }}">
                        <i class="fas fa-external-link-alt mr-1"></i>{{ $item->link }}
                    </a>
                    <small class="text-muted mt-auto">
                        <i class="fas fa-user mr-1"></i>{{ $item->user->name ?? '-' }}
                    </small>
                </div>

                <div class="card-footer bg-white border-top py-2 px-3 d-flex justify-content-between align-items-center">
                    @canAccess('show', 'report_links')
                    <a href="{{ route('report-link.show', $item->id) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-eye mr-1"></i>Detail
                    </a>
                    @endcanAccess
                    <div class="ml-auto">
                        @canAccess('update', 'report_links')
                        <a href="{{ route('report-link.edit', $item->id) }}"
                           class="btn btn-sm btn-outline-primary" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcanAccess
                        @canAccess('destroy', 'report_links')
                        <form action="{{ route('report-link.destroy', $item->id) }}" method="POST"
                              class="d-inline" onsubmit="return confirm('Hapus laporan ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        @endcanAccess
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($reportLinks->hasPages())
        <div class="d-flex justify-content-center mt-2">
            {{ $reportLinks->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
    @endif
@endif
@stop
