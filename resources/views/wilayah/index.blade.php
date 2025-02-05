@extends('adminlte::page')

@section('title', 'Tambah Wilayah')

@section('content_header')
    <h1>Tambah Data Wilayah</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Form Tambah Data Wilayah</h3>
    </div>
    <div class="card-body">
        <!-- Display Validation Errors -->
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Wilayah -->
        <form id="formWilayah" action="{{ route('wilayah.store') }}" method="POST">
            @csrf
            <div class="row">
                <!-- Level Wilayah -->
                <div class="col-md-6 mb-3">
                    <label for="level" class="form-label">Level Wilayah</label>
                    <select name="level" id="level" class="form-control select2" required>
                        <option value="" disabled selected>Pilih Level</option>
                        <option value="province">Provinsi</option>
                        <option value="city">Kota</option>
                        <option value="district">Kecamatan</option>
                        <option value="subdistrict">Kelurahan</option>
                        <option value="postal_code">Kode Pos</option>
                    </select>
                </div>

                <!-- Nama Wilayah -->
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama Wilayah</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Masukkan nama wilayah" required>
                </div>
            </div>

            <!-- Dynamic Fields -->
            <div id="dynamicFields" class="row d-none">
                <!-- Province Field -->
                <div class="col-md-6 mb-3 d-none" id="provinceField">
                    <label for="province_id" class="form-label">Provinsi</label>
                    <select name="province_id" id="province_id" class="form-control select2"></select>
                </div>

                <!-- City Field -->
                <div class="col-md-6 mb-3 d-none" id="cityField">
                    <label for="city_id" class="form-label">Kota</label>
                    <select name="city_id" id="city_id" class="form-control select2"></select>
                </div>

                <!-- District Field -->
                <div class="col-md-6 mb-3 d-none" id="districtField">
                    <label for="district_id" class="form-label">Kecamatan</label>
                    <select name="district_id" id="district_id" class="form-control select2"></select>
                </div>

                <!-- Subdistrict Field -->
                <div class="col-md-6 mb-3 d-none" id="subdistrictField">
                    <label for="subdistrict_id" class="form-label">Kelurahan</label>
                    <select name="subdistrict_id" id="subdistrict_id" class="form-control select2"></select>
                </div>

                <!-- Postal Code -->
                <div class="col-md-6 mb-3 d-none" id="postalCodeField">
                    <label for="postal_code" class="form-label">Kode Pos</label>
                    <input type="text" name="postal_code" id="postal_code" class="form-control" placeholder="Masukkan kode pos">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    .d-none {
        display: none;
    }
</style>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function () {
        // Initialize Select2
        $('.select2').select2({ width: '100%' });

        // Show/Hide Fields Based on Level
        $('#level').change(function () {
            const level = $(this).val();
            $('#dynamicFields > div').addClass('d-none');
            $('#dynamicFields').removeClass('d-none');

            if (level === 'city') {
                $('#provinceField').removeClass('d-none');
            } else if (level === 'district') {
                $('#provinceField, #cityField').removeClass('d-none');
            } else if (level === 'subdistrict') {
                $('#provinceField, #cityField, #districtField').removeClass('d-none');
            } else if (level === 'postal_code') {
                $('#provinceField, #cityField, #districtField, #subdistrictField, #postalCodeField').removeClass('d-none');
            }
        });

        // Dynamic Dropdown Population
        $('#province_id').change(function () {
            fetchDropdownData('{{ route("city.byProvince", ":id") }}', $(this).val(), '#city_id');
        });

        $('#city_id').change(function () {
            fetchDropdownData('{{ route("district.byCity", ":id") }}', $(this).val(), '#district_id');
        });

        $('#district_id').change(function () {
            fetchDropdownData('{{ route("subdistrict.byDistrict", ":id") }}', $(this).val(), '#subdistrict_id');
        });

        function fetchDropdownData(url, id, target) {
            if (!id) return;
            const endpoint = url.replace(':id', id);
            $.get(endpoint, function (data) {
                $(target).empty().append('<option value="" disabled selected>Pilih Opsi</option>');
                $.each(data, function (key, value) {
                    $(target).append(`<option value="${value.id}">${value.name}</option>`);
                });
            });
        }
    });
</script>
@stop