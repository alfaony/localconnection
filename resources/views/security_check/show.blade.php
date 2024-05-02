@extends('adminlte::page')

@section('title', $type == 'check_in' ? 'Kontrol Pagi' : 'Kontrol Sore')

@section('content_header')
    <h1>{{ $type == 'check_in' ? 'Kontrol Pagi' : 'Kontrol Sore' }}</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Galeri Foto {{ $type == 'check_in' ? 'Pagi' : 'Sore' }}</h3>
        </div>
        <div class="card-body">
            @if($photos->isEmpty())
                <p class="text-center">Belum ada foto yang diunggah untuk sesi ini.</p>
            @else
                <div class="mb-4">
                    <p><strong>Petugas:</strong> {{ $securityCheck->user ? $securityCheck->user->name : '' }}</p> <!-- Ganti $operatorName dengan variabel yang sesuai dari Controller Anda -->
                    <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($securityCheck->date)->format('d-m-Y') }}</p> <!-- Format tanggal sesuai kebutuhan -->
                    <p><strong>Waktu:</strong> {{ $securityCheck->time }}</p> <!-- Waktu sesuai dengan data yang Anda miliki -->
                </div>
                <div class="row">
                    @foreach($photos as $photo)
                        <div class="col-md-4 col-sm-6 mb-4">
                            <a href="{{ Storage::url($photo->path) }}" target="_blank">
                                <img src="{{ Storage::url($photo->path) }}" class="img-thumbnail" alt="Foto {{ $type == 'check_in' ? 'Pagi' : 'Sore' }}">
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <a href="{{ route('security-check.index') }}" class="btn btn-primary mt-3">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Kontrol
    </a>
</div>
@endsection
