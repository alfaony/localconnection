@extends('adminlte::page')

@section('title', 'Detail Kendaraan')
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