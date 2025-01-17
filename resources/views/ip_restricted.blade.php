@extends('adminlte::master')

@section('title', 'Akses Ditolak')

@section('body')
<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg p-4 text-center" style="max-width: 500px; width: 100%;">
        <h3 class="text-danger"><i class="fas fa-ban"></i> Akses Ditolak</h3>
        <p class="mt-3">IP Anda <strong>{{ request()->ip() }}</strong> tidak diizinkan untuk mengakses halaman ini.</p>
        <p>Silakan hubungi administrator untuk mendapatkan akses.</p>

        <div class="mt-4">
            <a href="{{ route('logout') }}" class="btn btn-danger"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    body {
        background-color: #f8f9fa;
    }
</style>
@stop