@extends('adminlte::page')

@section('title', isset($vehicle) ? 'Edit Kendaraan' : 'Tambah Kendaraan')
@section('content_header')
    <h1>{{ isset($vehicle) ? 'Edit Kendaraan' : 'Tambah Kendaraan' }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ isset($vehicle) 
                ? route('vehicle.update', $vehicle->id)
                : route('vehicle.store') }}"
            method="POST">

            @csrf
            @if(isset($vehicle))
                @method('PUT')
            @endif

            {{-- Error Display --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-3">
                <label>ID Kendaraan <span class="text-danger">*</span></label>
                <input type="text" name="vehicle_id" class="form-control"
                    value="{{ old('vehicle_id', $vehicle->vehicle_id ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label>Jenis Kendaraan <span class="text-danger">*</span></label>
                <select name="vehicle_type" class="form-control" required>
                    <option value="">-- Pilih Jenis Kendaraan --</option>
                    @foreach($typeVehicles as $type => $value)
                        <option value="{{ $type }}"
                            {{ old('vehicle_type', $vehicle->vehicle_type ?? '') == $type ? 'selected' : '' }}>
                            {{ ucfirst($value) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Tipe <span class="text-danger">*</span></label>
                <input type="text" name="type" class="form-control"
                    value="{{ old('type', $vehicle->type ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label>Posisi <span class="text-danger">*</span></label>
                <input type="text" name="position" class="form-control"
                    value="{{ old('position', $vehicle->position ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label>Penanggung Jawab <span class="text-danger">*</span></label>
                <select name="pic_user_id" class="form-control" required>
                    <option value="">-- Pilih PIC --</option>
                    @foreach($users as $id => $name)
                        <option value="{{ $id }}"
                            {{ (old('pic_user_id', $vehicle->pic_user_id ?? '') == $id) ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Tanggal Service Terakhir</label>
                <input type="date" name="service_terakhir" class="form-control"
                    value="{{ old('service_terakhir', $vehicle->service_terakhir ?? '') }}">
            </div>

            <div class="mb-3">
                <label>Batas Berlaku STNK <span class="text-danger">*</span></label>
                <input type="date" name="subscription_stnk" class="form-control"
                    value="{{ old('subscription_stnk', $vehicle->subscription_stnk ?? '') }}"
                    required>
            </div>

            <div class="mb-3">
                <label>Batas Berlaku KIR </label>
                <input type="date" name="subscription_kir" class="form-control"
                    value="{{ old('subscription_kir', $vehicle->subscription_kir ?? '') }}"
                    >
            </div>

            <button type="submit" class="btn btn-success">
                {{ isset($vehicle) ? 'Update' : 'Simpan' }}
            </button>
            <a href="{{ route('vehicle.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@stop