@extends('adminlte::page')

@section('title', 'Software Catalog')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-12">
            <h1 class="text-center">Pilih Software Langganan Anda</h1>
            <p class="text-center text-muted">Berbagai software premium dengan harga terjangkau</p>
        </div>
    </div>
@stop

@section('content')
    {{-- Search --}}
    <div class="row mb-4">
        <div class="col-md-6 mx-auto">
            <form method="GET">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari software..." value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Software Cards --}}
    <div class="row">
        @forelse($softwares as $software)
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="card card-outline {{ $software->has_available_slots ? 'card-primary' : 'card-secondary' }}">
                <div class="card-body text-center">
                    @if($software->logo)
                    <img src="{{ s3_asset(true,10,$software->logo) }}" alt="{{ $software->nama }}" class="img-fluid mb-3" style="max-height: 100px;">
                    @else
                    <div class="bg-light mb-3" style="height: 100px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-desktop fa-3x text-muted"></i>
                    </div>
                    @endif
                    
                    <h4>{{ $software->nama }}</h4>
                    <p class="text-muted">{{ $software->tipe_paket }}</p>
                    
                    @if($software->description)
                    <p class="small">{{ Str::limit($software->description, 100) }}</p>
                    @endif
                    
                    {{-- Package Options --}}
                    @if($software->activePackages->isNotEmpty())
                    <div class="mb-3">
                        <small class="text-muted">Mulai dari:</small>
                        <br>
                        <strong class="text-primary h5">
                            Rp {{ number_format($software->activePackages->min('harga'), 0, ',', '.') }}
                        </strong>
                        <small class="text-muted">/ {{ $software->activePackages->where('harga', $software->activePackages->min('harga'))->first()->nama_paket }}</small>
                    </div>
                    @endif
                    
                    {{-- Slot Availability --}}
                    @if($software->has_available_slots)
                    <span class="badge badge-success mb-3">
                        <i class="fas fa-check-circle"></i> Slot Tersedia
                    </span>
                    @else
                    <span class="badge badge-danger mb-3">
                        <i class="fas fa-times-circle"></i> Slot Penuh
                    </span>
                    @endif
                    
                    <br>
                    
                    <a href="{{ route('customer.software.show', $software->slug) }}" class="btn {{ $software->has_available_slots ? 'btn-primary' : 'btn-secondary' }} btn-block">
                        <i class="fas fa-info-circle"></i> Lihat Detail
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center">
            <div class="callout callout-info">
                <h5><i class="fas fa-info-circle"></i> Tidak ada software tersedia</h5>
                <p>Silakan coba kata kunci pencarian lain atau hubungi admin.</p>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($softwares->hasPages())
    <div class="row">
        <div class="col-12 d-flex justify-content-center">
            {{ $softwares->links() }}
        </div>
    </div>
    @endif
@stop

@section('css')
@stop

@section('js')
@stop
