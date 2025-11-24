{{-- edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Edit PPP Secret')

@section('content_header')
    <h1>Edit Secret: {{ $secret['name'] ?? '' }}</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Form Edit Secret</h3>
    </div>
    <form action="{{ route('mikrotik-secret.update', $secret['.id']) }}" method="POST">
        @csrf @method('PUT')
        <div class="card-body">
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="name" class="form-control" value="{{ $secret['name'] ?? '' }}" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="text" name="password" class="form-control" value="{{ $secret['password'] ?? '' }}" required>
            </div>
            <div class="form-group">
                <label>Service</label>
                <select name="service" class="form-control" required>
                    <option value="pppoe" @selected(($secret['service'] ?? '') === 'pppoe')>PPPoE</option>
                    <option value="pptp" @selected(($secret['service'] ?? '') === 'pptp')>PPTP</option>
                    <option value="l2tp" @selected(($secret['service'] ?? '') === 'l2tp')>L2TP</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Profile</label>
                <select name="profile" class="form-control" required>
                    <option value="">-- pilih profile --</option>
                    @foreach($profiles as $p)
                    <option value="{{ $p }}"
                        @selected(old('profile', $secret['profile'] ?? '') === $p)>
                        {{ $p }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Remote Address</label>
                <input type="text" name="remote_address" class="form-control" value="{{ $secret['remote-address'] ?? '' }}">
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-sync-alt"></i> Update
            </button>
            <a href="{{ route('mikrotik-secret.index') }}" class="btn btn-secondary ml-2">
                <i class="fas fa-times"></i> Batal
            </a>
        </div>
    </form>
</div>
@stop