@extends('adminlte::page')

@section('content_header')
    <h1>{{ isset($equipment) ? 'Ubah Perlengkapan' : 'Tambah Perlengkapan' }}</h1>
@stop
@section('content')
<div class="container">
    <form action="{{ isset($equipment) ? route('equipment.update', $equipment->slug) : route('equipment.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($equipment))
            @method('PUT')
        @endif
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $equipment->name ?? '') }}" required>
            @error('name')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="form-group">
            <label for="total_stock">Total Stock</label>
            <input type="number" class="form-control @error('total_stock') is-invalid @enderror" id="total_stock" name="total_stock" value="{{ old('total_stock', $equipment->total_stock ?? '') }}" required min="0">
            @error('total_stock')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        @canAccess('store','equipment')
        <button type="submit" class="btn btn-primary">{{ isset($equipment) ? 'Update' : 'Save' }}</button>
        @endcanAccess
    </form>
</div>
@endsection
@section('css')
<style>
   body {
            font-family: Arial, sans-serif;
            /* padding: 20px; */
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            border-radius: 5px;
        }
        

</style>
@stop
