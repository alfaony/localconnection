@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Secret</h2>
    <form action="{{ route('mikrotik-secrets.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="text" name="password" class="form-control" required value="{{ old('password') }}">
        </div>
        <div class="mb-3">
            <label>Service</label>
            <select name="service" class="form-control" required>
                <option value="pppoe">PPPoE</option>
                <option value="pptp">PPTP</option>
                <option value="l2tp">L2TP</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Profile</label>
            <input type="text" name="profile" class="form-control" required value="{{ old('profile') }}">
        </div>
        <div class="mb-3">
            <label>Remote Address</label>
            <input type="text" name="remote_address" class="form-control" value="{{ old('remote_address') }}">
        </div>
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('mikrotik-secrets.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection