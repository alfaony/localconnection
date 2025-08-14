
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Secret</h2>
    <form action="{{ route('mikrotik-secrets.update', $secret['.id']) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="name" class="form-control" value="{{ $secret['name'] ?? '' }}" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="text" name="password" class="form-control" value="{{ $secret['password'] ?? '' }}" required>
        </div>
        <div class="mb-3">
            <label>Service</label>
            <select name="service" class="form-control" required>
                <option value="pppoe" @selected(($secret['service'] ?? '') === 'pppoe')>PPPoE</option>
                <option value="pptp" @selected(($secret['service'] ?? '') === 'pptp')>PPTP</option>
                <option value="l2tp" @selected(($secret['service'] ?? '') === 'l2tp')>L2TP</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Profile</label>
            <input type="text" name="profile" class="form-control" value="{{ $secret['profile'] ?? '' }}" required>
        </div>
        <div class="mb-3">
            <label>Remote Address</label>
            <input type="text" name="remote_address" class="form-control" value="{{ $secret['remote-address'] ?? '' }}">
        </div>
        <button class="btn btn-primary">Update</button>
        <a href="{{ route('mikrotik-secrets.index') }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection