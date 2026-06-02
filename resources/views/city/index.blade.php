@extends('adminlte::page')

@section('title', 'Kabupaten / Kota')

@section('content_header')
    <h1>Kabupaten / Kota</h1>
@stop

@section('content')
@include('components.alert')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Kabupaten \ Kota</h3>
    </div>
    <div class="card-body">
        <form action="{{ isset($city) ? route('city.update', $city->id) : route('city.store') }}" method="POST">
            @csrf
            @if(isset($city)) @method('PUT') @endif
            <div class="mb-3">
                <label for="province_id" class="form-label">Provinsi</label>
                <select name="province_id" id="province_id" class="form-control select2" required>
                    <option value="" disabled {{ isset($city) ? '' : 'selected' }}>Select Province</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province->id }}" {{ isset($city) && $city->province_id == $province->id ? 'selected' : '' }}>
                            {{ $province->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Kabupaten \ Kota</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $city->name ?? '') }}" required>
            </div>

            @if(@$city && count($city->districts) != 0)
            <div class="mb-3">
                <label for="default_district" class="form-label">Default Kecamatan</label>
                <select name="default_district_id" id="default_district" class="form-control select2" required>
                    <option value="" disabled {{ !$city->defaultDistrict ? 'selected' : '' }}>Select District</option>
                    @foreach($defaultDistrict as $district)
                        <option value="{{ $district->id }}" {{ $city->default_district_id == $district->id ? 'selected' : '' }}>
                            {{ $district->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="d-flex justify-content-end">
                <a href="{{ route('city.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary ml-2">Save</button>
            </div>
        </form>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <table id="cityTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Default (Kecamatan)</th>
                    <th>Nama (Kabupaten \ Kota)</th>
                    <th>Provinsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        $('.select2').select2({
            width: '100%',
        });
    });
</script>
<script>
    $(document).ready(function () {
        $('#cityTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('city.dataTableJson') }}",
            columns: [
                { data: 'default_district.name', name: 'default_district.name', orderable: false },
                { data: 'name', name: 'name', orderable: false },
                { data: 'province.name', name: 'province.name', orderable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
        });
    });
</script>
@stop

@section('css')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
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
