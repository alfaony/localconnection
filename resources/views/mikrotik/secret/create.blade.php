{{-- create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Tambah PPP Secret')

@section('content_header')
    <h1>Tambah PPP Secret</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Form Tambah Secret</h3>
    </div>
    <form action="{{ route('mikrotik-secret.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name') }}" placeholder="Nama pengguna">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="text" name="password" class="form-control" required value="{{ old('password') }}" placeholder="Password akses">
            </div>
            <div class="form-group">
                <label>Service</label>
                <select name="service" class="form-control" required>
                    <option value="pppoe" @selected(old('service') === 'pppoe')>PPPoE</option>
                    <option value="pptp" @selected(old('service') === 'pptp')>PPTP</option>
                    <option value="l2tp" @selected(old('service') === 'l2tp')>L2TP</option>
                </select>
            </div>
                <div class="mb-3">
                <label class="form-label">Profile</label>
                <select name="profile" class="form-control" required>
                    <option value="">-- pilih profile --</option>
                    @foreach($profiles as $p)
                    <option value="{{ $p }}" @selected(old('profile')===$p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Remote Address</label>
                <input type="text" name="remote_address" class="form-control" value="{{ old('remote_address') }}" placeholder="Alamat IP remote">
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan
            </button>
            <a href="{{ route('mikrotik-secret.index') }}" class="btn btn-secondary ml-2">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </form>
</div>
@stop