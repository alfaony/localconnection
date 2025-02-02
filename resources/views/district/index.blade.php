@extends('adminlte::page')

@section('title', isset($district) ? 'Edit District' : 'Kecamatan')

@section('content_header')
    <h1>{{ isset($district) ? 'Edit District' : 'Kecamatan' }}</h1>
@stop

@section('content')
@include('components.alert')

@canAccess('store', 'districts')
@canAccess('update', 'districts')
<div class="card mt-3">
    <div class="card-body">
        <form action="{{ isset($district) ? route('district.update', $district->id) : route('district.store') }}" method="POST">
            @csrf
            @if(isset($district)) @method('PUT') @endif
            <div class="mb-3">
                <label for="city_id" class="form-label">Kabupaten \ Kota</label>
                <select name="city_id" id="city" class="form-control select2" required>
                    <option value="" disabled selected>Select City</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Kecamatan</label>
                <input type="text" name="name" id="name" class="form-control" 
                       value="{{ old('name', $district->name ?? '') }}" required>
            </div>
            @if(@$district && count($district->subdistricts) != 0)
            <div class="mb-3">
                <label for="default_subdistrict" class="form-label">Default Kelurahan</label>
                <select name="default_subdistrict_id" id="default_subdistrict" class="form-control select2">
                    <option value="" disabled selected>Select Kelurahan</option>
                    @foreach($listSubdistrict as $subdistrict)
                        <option value="{{ $subdistrict->id }}" {{ isset($district) && $district->default_subdistrict_id == $subdistrict->id ? 'selected' : '' }}>
                            {{ $subdistrict->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="d-flex justify-content-end">
                <a href="{{ route('district.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary ml-2">Save</button>
            </div>
        </form>
    </div>
</div>
@endcanAccess
@endcanAccess

<div class="card">
    <div class="card-body">
        <table id="districtTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Default Kelurahan</th>
                    <th>Nama ( Kecamatan ) </th>
                    <th>Kabupaten \ Kota</th>
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

@canAccess('select2', 'districts')
<script>
    $(document).ready(function () {
        $('.select2').select2({
            width: '100%',
        });

        const initialCityId = "{{ @$district->city_id ?? '' }}";
        const initialCityName = "{{ @$district->city->full_name ?? '' }}";

        initializeSelect2('#city', "{{ route('city.select2') }}", {} , initialCityId, initialCityName);
    });

    function initializeSelect2(selector, url, params = {}, selectedId = null, selectedText = null) {
        $(selector).select2({
            placeholder: $(selector).attr('placeholder'),
            allowClear: true,
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term, ...params };
                },
                processResults: function (data) {
                    return { results: data.results };
                },
            },
        });

        // Set selected value if provided
        if (selectedId && selectedText) {
            const option = new Option(selectedText, selectedId, true, true);
            $(selector).append(option).trigger('change');
        }
    }
</script>
@endcanAccess

@canAccess('dataTableJson', 'districts')
<script>
    $(document).ready(function () {
        $('#districtTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('district.dataTableJson') }}',
            columns: 
            [
                { data: 'default_subdistrict.name', name: 'default_subdistrict.name', orderable: true },
                { data: 'name', name: 'name', orderable: true },
                { data: 'city.name', name: 'city.name', orderable: true },
                { data: 'city.province.name', name: 'city.province.name', orderable: true },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            columnDefs: [
                { targets: 1, orderData: [0, 1] },
            ],
        });
    });
</script>
@endcanAccess
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