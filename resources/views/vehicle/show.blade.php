@extends('adminlte::page')

@section('title', 'Detail Kendaraan')
@section('css')
<style>
.vehicle-photo-card {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
}
.vehicle-photo-card img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    display: block;
}
.vehicle-photo-card .photo-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,.7));
    padding: 8px 10px 6px;
    color: #fff;
    font-size: .75rem;
}
.vehicle-photo-card .btn-delete-photo {
    position: absolute;
    top: 6px;
    right: 6px;
}
.no-photo-badge {
    background: #fff3cd;
    border: 1px dashed #ffc107;
    border-radius: 8px;
    padding: 12px 16px;
    color: #856404;
}
</style>
@stop
@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Detail Kendaraan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vehicle.index') }}">Detail Kendaraan</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $vehicle->vehicle_id ." ".$vehicle->vehicle_type." ".$vehicle->type }}</li>
            </ol>
        </nav>
    </div>
@stop

@section('content')
@include('components.alert')
<div class="card">
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">ID Kendaraan</dt>
            <dd class="col-sm-9">{{ $vehicle->vehicle_id }}</dd>

            <dt class="col-sm-3">Jenis</dt>
            <dd class="col-sm-9">{{ $vehicle->vehicle_type }}</dd>

            <dt class="col-sm-3">Tipe</dt>
            <dd class="col-sm-9">{{ $vehicle->type }}</dd>

            <dt class="col-sm-3">Posisi</dt>
            <dd class="col-sm-9">{{ $vehicle->position }}</dd>

            <dt class="col-sm-3">Penanggung Jawab</dt>
            <dd class="col-sm-9">{{ $vehicle->picUser->name ?? '-' }}</dd>

            <dt class="col-sm-3">STNK Berlaku Sampai</dt>
            <dd class="col-sm-9">
                @if($vehicle->subscription_stnk)
                    <span class="badge bg-{{ $vehicle->getColorStatusFor('subscription_stnk') }}">
                        {{ $vehicle->subscription_stnk }}
                    </span>
                @else
                    <span class="text-muted">-</span>
                @endif
            </dd>

            <dt class="col-sm-3">KIR Berlaku Sampai</dt>
            <dd class="col-sm-9">
                @if($vehicle->subscription_kir)
                    <span class="badge bg-{{ $vehicle->getColorStatusFor('subscription_kir') }}">
                        {{ $vehicle->subscription_kir }}
                    </span>
                @else
                    <span class="text-muted">-</span>
                @endif
            </dd>

            <dt class="col-sm-3">Service Terakhir</dt>
            <dd class="col-sm-9">
                @if($vehicle->service_terakhir)
                    <span class="badge bg-info">{{ $vehicle->service_terakhir }}</span>
                @else
                    <span class="text-muted">-</span>
                @endif
            </dd>
        </dl>

        
        @canAccess('update', 'vehicles')
            <button class="btn btn-warning mb-3" data-bs-toggle="modal" data-bs-target="#modalPerpanjangVehicle">
                Perpanjang Masa Berlaku
            </button>
        @include('vehicle.modal_perpanjang')
        @endcanAccess
    </div>
</div>

{{-- ================ SEKSI FOTO KENDARAAN ================ --}}
<div class="card mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-camera me-1"></i> Foto Kendaraan</span>
        <small class="text-muted">Minimal 1 foto per bulan</small>
    </div>
    <div class="card-body">

        @if(session('photo_uploaded'))
            <div class="alert alert-success"><i class="fas fa-check-circle me-1"></i> Foto berhasil diunggah.</div>
        @endif
        @if(session('photo_deleted'))
            <div class="alert alert-warning"><i class="fas fa-trash me-1"></i> Foto berhasil dihapus.</div>
        @endif

        {{-- Status foto bulan ini --}}
        @php
            $thisMonth = now()->locale('id')->monthName . ' ' . now()->year;
            $hasPhotoThisMonth = $vehicle->hasPhotoThisMonth();
        @endphp
        @if(!$hasPhotoThisMonth)
            <div class="no-photo-badge mb-3">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Kendaraan ini <strong>belum memiliki foto</strong> di bulan <strong>{{ $thisMonth }}</strong>.
                Segera unggah foto kendaraan.
            </div>
        @else
            <div class="alert alert-success py-2 mb-3">
                <i class="fas fa-check-circle me-1"></i>
                Sudah ada foto di bulan <strong>{{ $thisMonth }}</strong>.
            </div>
        @endif

        {{-- Form Upload --}}
        @canAccess('update', 'vehicles')
        <form action="{{ route('vehicle.photo.store', $vehicle->id) }}" method="POST" enctype="multipart/form-data" class="mb-4">
            @csrf
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1 small fw-bold">Pilih Foto <span class="text-danger">*</span></label>
                    <input type="file" name="photo" class="form-control form-control-sm @error('photo') is-invalid @enderror"
                           accept="image/jpeg,image/png,image/jpg" required>
                    @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1 small fw-bold">Keterangan</label>
                    <input type="text" name="description" class="form-control form-control-sm"
                           placeholder="Opsional" value="{{ old('description') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-upload me-1"></i> Upload
                    </button>
                </div>
            </div>
        </form>
        @endcanAccess

        {{-- Galeri Foto --}}
        @php $photos = $vehicle->photos; @endphp
        @if($photos->count())
            {{-- Filter bulan --}}
            <div class="d-flex flex-wrap gap-2 mb-3">
                @php
                    $months = $photos->groupBy(fn($p) => $p->taken_at->format('Y-m'));
                @endphp
                @foreach($months as $ym => $group)
                    @php [$y, $m] = explode('-', $ym); $label = \Carbon\Carbon::createFromDate($y, $m, 1)->locale('id')->monthName . ' ' . $y; @endphp
                    <span class="badge bg-secondary">{{ $label }} ({{ $group->count() }})</span>
                @endforeach
            </div>

            <div class="row g-3">
                @foreach($photos as $photo)
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="vehicle-photo-card">
                        <a href="{{ asset('storage/' . $photo->photo) }}" target="_blank">
                            <img src="{{ asset('storage/' . $photo->photo) }}"
                                 alt="Foto {{ $vehicle->vehicle_id }}">
                        </a>
                        <div class="photo-overlay">
                            <div>{{ $photo->taken_at->format('d M Y') }}</div>
                            @if($photo->description)
                                <div class="text-truncate" title="{{ $photo->description }}">{{ $photo->description }}</div>
                            @endif
                            <div class="text-white-50" style="font-size:.68rem;">{{ $photo->uploader->name ?? '-' }}</div>
                        </div>
                        @canAccess('update', 'vehicles')
                        <form action="{{ route('vehicle.photo.destroy', [$vehicle->id, $photo->id]) }}"
                              method="POST" class="d-inline btn-delete-photo"
                              onsubmit="return confirm('Hapus foto ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm py-0 px-1" title="Hapus">
                                <i class="fas fa-times" style="font-size:.7rem;"></i>
                            </button>
                        </form>
                        @endcanAccess
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <p class="text-muted">Belum ada foto yang diunggah.</p>
        @endif

    </div>
</div>
{{-- ========================================================= --}}

<div class="card mt-4">
    <div class="card-header">Riwayat Perubahan</div>
    <div class="card-body">
        <ul class="list-group">
            @forelse($vehicle->activities->sortByDesc('created_at') as $log)
                <li class="list-group-item">
                    <strong>{{ $log->created_at->format('d M Y H:i') }}</strong> – 
                    {{ $log->description }} oleh {{ $log->causer?->name ?? 'Sistem' }}

                    @if($log->properties['attributes'] ?? false)
                        <ul class="mt-2">
                            @foreach($log->properties['attributes'] as $key => $val)
                                <li>{{ $key }}: 
                                    <span class="text-danger">{{ $log->properties['old'][$key] ?? '–' }}</span> → 
                                    <span class="text-success">{{ $val }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @empty
                <li class="list-group-item">Belum ada perubahan tercatat.</li>
            @endforelse
        </ul>
    </div>
</div>
@stop
@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@stop