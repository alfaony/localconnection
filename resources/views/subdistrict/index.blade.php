@extends('adminlte::page')

@section('title', 'Subdistrict Management')

@section('content_header')
    <h1>{{ isset($subdistrict) ? 'Edit Kelurahan' : 'Kelurahan' }}</h1>
@stop


@section('content')
@include('components.alert')

@canAccess('store', 'subdistricts')
@canAccess('update', 'subdistricts')
<div class="card">
    <div class="card-body">
        @if (isset($subdistrict))
            <form action="{{ route('subdistrict.update', $subdistrict->id) }}" method="POST">
            @method('PUT')
        @else
            <form action="{{ route('subdistrict.store') }}" method="POST">
        @endif
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ $subdistrict->name ?? '' }}" required>
            </div>

            <div class="mb-3">
                <label for="district_id" class="form-label">Kecamatan</label>
                <select name="district_id" id="district" class="form-control select2">
                    <option value="" disabled {{ !isset($subdistrict->district_id) ? 'selected' : '' }}>Select District</option>
                </select>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('subdistrict.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary ml-2">Save</button>
            </div>
        </form>
    </div>
</div>
@endcanAccess
@endcanAccess

<div class="card">
    <div class="card-body">
        <table id="subdistrictTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Nama (Kelurahan)</th>
                    <th>Kecamatan</th>
                    <th>Kabupaten \ Kota</th>
                    <th>Provinsi</th>
                    <th>Actions</th>
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
@canAccess('select2', 'subdistricts')
<script>
    $(document).ready(function () 
    {

        const initialCityId = "{{ @$subdistrict->district_id ?? '' }}";
        const initialCityName = "{{ @$subdistrict->district->full_name ?? '' }}";

        initializeSelect2('#district', "{{ route('district.select2') }}", {} , initialCityId, initialCityName);
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

@canAccess('dataTableJson', 'subdistricts')
<script>
    $(document).ready(function () {
        $('#subdistrictTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('subdistrict.dataTableJson') }}',
            columns: [
                { data: 'name', name: 'name' },
                { data: 'district.name', name: 'district.name', orderable: false },
                { data: 'district.city.name', name: 'district.city.name', orderable: false },
                { data: 'district.city.province.name', name: 'district.city.province.name', orderable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[1, 'asc']],
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