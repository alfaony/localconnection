@extends('adminlte::page')

@section('title', 'Verifikasi Absensi')

@section('content')
    <div class="container text-center">
        <h3><i class="fas fa-sync-alt fa-spin"></i> Verifikasi Absensi Sedang Berlangsung...</h3>
        <p class="text-muted">QR code sedang diverifikasi, harap tunggu beberapa saat.</p>
        <p>Proses ini mungkin memerlukan beberapa detik...</p>

        <div class="mt-4">
            <small><i class="fas fa-clock mr-1"></i> Jangan tutup halaman ini hingga verifikasi selesai.</small>
        </div>
    </div>
@endsection