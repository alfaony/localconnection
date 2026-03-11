@extends('adminlte::page')

@section('title', 'Detail Rack - ' . $rack->name)

@section('content')
@include('components.alert')

@if(session('assign_success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('assign_success') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

@if(session('unassign_success'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    {{ session('unassign_success') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
</div>
@endif

<!-- Header -->
<div class="d-flex align-items-center justify-content-between mb-3 mt-3">
    <div>
        <a href="{{ route('rack.index') }}" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div>
        @canAccess('edit', 'racks')
        <a href="{{ route('rack.edit', $rack->id) }}" class="btn btn-sm btn-info">
            <i class="fa fa-edit"></i> Edit Rack
        </a>
        @endcanAccess
    </div>
</div>

<!-- Info Rack -->
<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fa fa-cube mr-2"></i>Informasi Rack</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <small class="text-muted">Nama Rack</small>
                <p class="font-weight-bold">{{ $rack->name }}</p>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Zone</small>
                <p class="font-weight-bold">{{ $rack->zone?->name ?? '-' }}</p>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Warehouse</small>
                <p class="font-weight-bold">{{ $rack->zone?->warehouse?->name ?? '-' }}</p>
            </div>
        </div>
        @if($rack->description)
        <div class="row">
            <div class="col-12">
                <small class="text-muted">Deskripsi</small>
                <p>{{ $rack->description }}</p>
            </div>
        </div>
        @endif

        @if($rack->sensors->count() > 0)
        <hr>
        <small class="text-muted">Sensor</small>
        <div class="mt-1">
            @foreach($rack->sensors as $sensor)
            <span class="badge badge-info mr-1">{{ $sensor->name }} ({{ $sensor->pivot->sensor_code ?? '-' }})</span>
            @endforeach
        </div>
        @endif
    </div>
</div>

<!-- Section Product Store -->
<div class="card shadow mb-4">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-boxes mr-2"></i>Product Store</h5>
        <span class="badge badge-light text-success">{{ $rack->productStores->count() }} produk</span>
    </div>
    <div class="card-body">

        {{-- Form Assign (selalu tampil selama masih ada product store available) --}}
        @canAccess('assignProductStore', 'racks')
        @if($availableProductStores->count() > 0)
        <form action="{{ route('rack.assign-product-store', $rack->id) }}" method="POST" class="mb-4">
            @csrf
            <div class="row align-items-end">
                <div class="col-md-9">
                    <label class="form-label font-weight-bold">Tambah Product Store ke Rack ini:</label>
                    <select name="product_store_ids[]" class="form-control select2-multi" multiple="multiple" required>
                        @foreach($availableProductStores as $ps)
                        <option value="{{ $ps->id }}">
                            {{ $ps->name }}
                            @if($ps->code) ({{ $ps->code }}) @endif
                            @if($ps->barcode) | {{ $ps->barcode }} @endif
                        </option>
                        @endforeach
                    </select>
                    @error('product_store_ids')
                    <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fa fa-link"></i> Assign
                    </button>
                </div>
            </div>
        </form>
        @else
        <div class="alert alert-warning mb-4">
            <i class="fa fa-exclamation-triangle mr-1"></i>
            Semua product store sudah memiliki rack. Tambah product store baru terlebih dahulu.
        </div>
        @endif
        @endcanAccess

        {{-- Daftar Product Store yang sudah ter-assign --}}
        @if($rack->productStores->count() > 0)
        <table class="table table-bordered table-striped mb-0">
            <thead class="thead-light">
                <tr class="text-center">
                    <th>#</th>
                    <th>Nama Product</th>
                    <th>Kode</th>
                    <th>Barcode</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rack->productStores as $i => $ps)
                <tr class="text-center">
                    <td>{{ $i + 1 }}</td>
                    <td class="text-left">{{ $ps->name }}</td>
                    <td>{{ $ps->code ?? '-' }}</td>
                    <td>{{ $ps->barcode ?? '-' }}</td>
                    <td>
                        <a href="{{ route('product-store.show', $ps->id) }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-eye"></i>
                        </a>
                        @canAccess('unassignProductStore', 'racks')
                        <form action="{{ route('rack.unassign-product-store', $rack->id) }}" method="POST"
                            class="d-inline"
                            onsubmit="return confirm('Lepas \'{{ addslashes($ps->name) }}\' dari rack ini?')">
                            @csrf
                            <input type="hidden" name="product_store_id" value="{{ $ps->id }}">
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fa fa-unlink"></i>
                            </button>
                        </form>
                        @endcanAccess
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-muted mb-0">Belum ada product store yang ter-assign ke rack ini.</p>
        @endif

    </div>
</div>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-multi').select2({
        placeholder: '-- Pilih satu atau lebih Product Store --',
        allowClear: true,
    });
});
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
.select2-container--default .select2-selection--multiple {
    min-height: 38px;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #001f3f !important;
}
</style>
@stop
