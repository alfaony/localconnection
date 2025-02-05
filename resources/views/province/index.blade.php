@extends('adminlte::page')

@section('title', 'Provinces')

@section('content_header')
    <h1>Province</h1>
@stop

@section('content')
@include('components.alert')
<div class="card">
    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @canAccess('store','provinces')
        @canAccess('update','provinces')
        <form action="{{ isset($province) ? route('province.update', $province->id) : route('province.store') }}" method="POST">
            @csrf
            @if(isset($province)) @method('PUT') @endif
            <div class="mb-3">
                <label for="country_id" class="form-label">Negara</label>
                <select name="country_id" id="country_id" class="form-control select2" required>
                    <option value="" disabled selected>Pilih Negara</option>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}" {{ isset($province) && $province->country_id == $country->id ? 'selected' : '' }}>
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Nama</label>
                <input type="text" name="name" id="name" class="form-control" 
                    value="{{ old('name', $province->name ?? '') }}" required>
            </div>

            @if(isset($province) && count($defaultCity) != 0)
            <div class="mb-3">
                <label for="default_city" class="form-label">Default Kota</label>
                <select name="default_city_id" id="default_city" class="form-control select2" required>
                    <option value="" disabled {{ !$province->defaultCity ? 'selected' : '' }}>Tidak</option>
                    @foreach($defaultCity as $city)
                        <option value="{{ $city->id }}" {{ $province->default_city_id == $city->id ? 'selected' : '' }}>
                            {{ $city->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="d-flex justify-content-end">
                <a href="{{ route('province.index') }}" class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary ml-2">
                    <i class="fas fa-save"></i> {{ isset($province) ? 'Simpan Perubahan' : 'Simpan' }}
                </button>
            </div>
        </form>
        @endcanAccess
        @endcanAccess
    </div>
</div>
<div class="card">
    <div class="card-body">
        <table id="provinceTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Default Kabupaten / Kota</th>
                    <th>Nama (Provinsi)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@stop

@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih opsi',
            allowClear: true
        });
    });
</script>

@canAccess('dataTableJson','provinces')
<script>
    $(document).ready(function () {
        $('#provinceTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('province.dataTableJson') }}',
            columns: [
                { data: 'default_city.name', name: 'default_city.name'},
                { data: 'name', name: 'name' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
        });
    });
</script>
@endcanAccess
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
.select2-selection__rendered {
    line-height: 31px !important;
}

.select2-container .select2-selection--single {
    height: 35px !important;
}

.select2-selection__arrow {
    height: 34px !important;
}

.ql-container {
    min-height: 150px;
    height: auto;
}
</style>
@stop