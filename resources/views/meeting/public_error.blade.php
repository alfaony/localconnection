@extends('layouts.guest') {{-- atau sesuaikan layoutmu --}}

@section('content')
<div class="container text-center py-5">
    <h3 class="mb-3 text-danger">❌ Gagal Bergabung ke Meeting</h3>
    <p class="text-muted">{{ request('message') ?? 'Terjadi kesalahan yang tidak diketahui.' }}</p>
    <a href="{{ route('home') }}" class="btn btn-primary mt-4">Kembali ke Beranda</a>
</div>
@endsection