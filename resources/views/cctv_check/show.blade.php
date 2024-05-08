@extends('adminlte::page')

@section('title', "Kontrol Cctv")

@section('content_header')
<h1>Kontrol Cctv</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Galeri Kontrol Cctv</h3>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <p><strong>Petugas:</strong> {{ $check->user ? $check->user->name : '' }}</p> <!-- Ganti $operatorName dengan variabel yang sesuai dari Controller Anda -->
                <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($check->date)->format('d-m-Y') }}</p> <!-- Format tanggal sesuai kebutuhan -->
                <p><strong>Waktu:</strong> {{ $check->time }}</p> <!-- Waktu sesuai dengan data yang Anda miliki -->
            </div>
            @if($photos->isEmpty())
                <p class="text-center">Belum ada foto yang diunggah untuk sesi ini.</p>
            @else
                <div class="row">
                    @foreach($photos as $photo)
                    <div class="col-md-4 col-sm-6 mb-4">
                        <div class="card">
                            <img src="{{ Storage::url($photo->path) }}" class="card-img-top" alt="Foto Cctv">
                            <div class="card-body">
                                <p class="card-text">{{ $photo->description ?? 'No description available' }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <a href="{{ route('cctv-check.index') }}" class="btn btn-primary mt-3">
        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Kontrol
    </a>
</div>
@endsection
