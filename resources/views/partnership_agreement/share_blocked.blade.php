@extends('adminlte::master')

@section('body')
<div class="d-flex justify-content-center align-items-center" style="min-height: 100vh; background-color: #f4f6f9;">
    <div class="card shadow" style="max-width: 450px; width: 100%;">
        <div class="card-body text-center p-5">
            <div class="mb-4">
                <i class="fas fa-lock fa-4x text-danger"></i>
            </div>
            <h4 class="font-weight-bold text-danger mb-2">Akses Diblokir</h4>
            <p class="text-muted mb-0">
                Anda telah melebihi batas maksimal percobaan password (3x).
            </p>
            <p class="text-muted">
                Akses ke dokumen ini telah diblokir. Silakan hubungi pengirim dokumen untuk mendapatkan bantuan.
            </p>
            <hr>
            <p class="text-muted small mb-0">
                <i class="fas fa-shield-alt"></i>
                Dokumen dilindungi oleh sistem keamanan Thrive.
            </p>
        </div>
    </div>
</div>
@stop

@section('adminlte_css')
<style>
    .main-header, .main-sidebar, .main-footer { display: none !important; }
    .content-wrapper { margin-left: 0 !important; background-color: #f4f6f9; }
</style>
@stop
