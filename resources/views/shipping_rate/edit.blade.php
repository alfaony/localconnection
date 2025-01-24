@extends('adminlte::page')

@section('title', @$shippingRate ? 'Edit Harga Pengiriman' : 'Tambah Harga Pengiriman')

@section('content_header')
<h1>{{ @$shippingRate ? 'Edit Harga Pengiriman' : 'Tambah Harga Pengiriman' }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">{{ @$shippingRate ? 'Form Edit Harga Pengiriman' : 'Form Tambah Harga Pengiriman' }}</h3>
    </div>
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

        <form id="shippingRateForm"
            action="{{ isset($shippingRate) ? route('shipping-rate.update', $shippingRate->id) : route('shipping-rate.store') }}"
            method="POST">
            @csrf
            @if(isset($shippingRate))
            @method('PUT')
            @endif

            <input type="hidden" name="id" id="shippingRateId" value="{{ $shippingRate->id ?? '' }}">

            <div class="row g-3">
                <!-- Provider and Service Type -->
                <div class="col-md-6">
                    <label for="provider_id" class="form-label">Provider</label>
                    <select name="provider_id" id="provider_id" class="form-control select2" required>
                        <option value="" disabled selected>Pilih Provider</option>
                        @foreach($providers as $provider)
                        <option value="{{ $provider->id }}"
                            {{ isset($shippingRate) && $shippingRate->provider_id == $provider->id ? 'selected' : '' }}>
                            {{ $provider->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="service_type_id" class="form-label">Tipe Layanan</label>
                    <select name="service_type_id" id="service_type_id" class="form-control select2" required>
                        <option value="" disabled selected>Pilih Tipe Layanan</option>
                        @foreach($serviceTypes as $serviceType)
                        <option value="{{ $serviceType->id }}"
                            {{ isset($shippingRate) && $shippingRate->service_type_id == $serviceType->id ? 'selected' : '' }}>
                            {{ $serviceType->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Origin and Destination -->
                <div class="col-md-6">
                    <label for="origin_id" class="form-label">Asal</label>
                    <select name="origin_id" id="origin_id" class="form-control select2-wilayah" required>
                        <option value="" disabled selected>Pilih Asal</option>
                        @if(isset($shippingRate->origin_id))
                            <option value="{{ $shippingRate->origin_id }}" selected>{{ $shippingRate->origin->complate_name }}</option>
                        @endif
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="destination_id" class="form-label">Tujuan</label>
                    <select name="destination_id" id="destination_id" class="form-control select2-wilayah" required>
                        <option value="" disabled selected>Pilih Tujuan</option>
                        @if(isset($shippingRate->destination_id))
                            <option value="{{ $shippingRate->destination_id }}" selected>{{ $shippingRate->destination->complate_name }}</option>
                        @endif
                    </select>
                </div>
                <div class="alert alert-danger mt-3" id="duplicate-warning" style="display: none;">
                    Kombinasi Asal dan Tujuan sudah ada!
                </div>

                <!-- Base Weight and Base Price -->
                <div class="col-md-6">
                    <label for="base_weight" class="form-label">Berat Dasar (Kg)</label>
                    <input type="number" name="base_weight" id="base_weight" class="form-control"
                        value="{{ old('base_weight', $shippingRate->base_weight ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label for="base_price" class="form-label">Harga Dasar</label>
                    <input type="text" id="base_price_show" class="form-control" placeholder=""
                        oninput="formatRupiahFormat(this,'base_price')"
                        value="{{ number_format($shippingRate->base_price ?? 0, 0, ',', '.') }}" required>
                    <input type="hidden" name="base_price" id="base_price"
                        value="{{ $shippingRate->base_price ?? '' }}" />
                </div>

                <!-- Additional Weight and Price -->
                <div class="col-md-6">
                    <label for="additional_weight" class="form-label">Berat Tambahan (Kg)</label>
                    <input type="number" name="additional_weight" id="additional_weight" class="form-control"
                        value="{{ old('additional_weight', $shippingRate->additional_weight ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label for="additional_price" class="form-label">Harga Tambahan</label>
                    <input type="text" id="additional_price_show" class="form-control"
                        oninput="formatRupiahFormat(this,'additional_price')"
                        value="{{'Rp. '.number_format($shippingRate->additional_price ?? 0, 0, ',', '.') }}" required>
                    <input type="hidden" name="additional_price" id="additional_price"
                        value="{{ $shippingRate->additional_price ?? '' }}" />
                </div>
                <!-- Rate per CBM and Delivery Time -->
                <div class="col-md-6">
                    <label for="rate_per_cbm" class="form-label">Harga Per CBM</label>
                    <input type="text" id="rate_per_cbm_show" class="form-control" placeholder="Contoh: Rp 50.000"
                        value="{{'Rp. '.number_format($shippingRate->rate_per_cbm ?? 0, 0, ',', '.') }}"
                        oninput="formatRupiahFormat(this,'rate_per_cbm')" required>
                    <input type="hidden" name="rate_per_cbm" id="rate_per_cbm" class="form-control"
                        value="{{ $shippingRate->rate_per_cbm }}" />
                </div>
                <div class="col-md-6">
                    <label for="delivery_time" class="form-label">Estimasi Waktu Pengiriman</label>
                    <input type="text" name="delivery_time" id="delivery_time" class="form-control"
                        placeholder="Contoh: 2-4 Hari" value="{{ $shippingRate->delivery_time }}">
                </div>
            </div>


            <div class="d-flex justify-content-end mt-4">
                <button type="button" id="cancelForm" class="btn btn-secondary me-2" onclick="window.location.href='{{ route('shipping-rate.index') }}'">Batal</button>
                <button type="submit" class="btn btn-primary ml-2">{{ @$shippingRate ? 'Update' : 'Simpan' }}</button>
            </div>
        </form>
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
        placeholder: 'Pilih Opsi',
        allowClear: true,
    });

    $('.select2-wilayah').select2({
        width: '100%',
        placeholder: 'Cari wilayah...',
        ajax: {
            url: "{{ route('wilayah.select2') }}",
            dataType: 'json',
            delay: 250, // Delay untuk debounce
            data: function(params) {
                return {
                    q: params.term, // Query pencarian
                    page: params.page || 1, // Halaman pagination
                };
            },
            processResults: function(data) {
                return {
                    results: data.items, // Items dari response
                    pagination: {
                        more: data.pagination.more, // Pagination flag
                    },
                };
            },
            cache: true, // Caching hasil pencarian
        }
    });


    $('#toggleForm').click(function() {
        $('#createOrEditForm').toggleClass('d-none');
        $('#shippingRateForm').trigger('reset');
        $('#shippingRateId').val('');
    });

    $('#cancelForm').click(function() {
        $('#createOrEditForm').addClass('d-none');
    });
});

function formatRupiahFormat(input, inputNonFormat) {

    let numStr = input.value.toString().replace(/[^,\d]/g, '');
    let split = numStr.split(',');
    let sisa = split[0].length % 3;
    let rupiah = split[0].substr(0, sisa);
    let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] !== undefined ? 'Rp. ' + rupiah + ',' + split[1] : 'Rp. ' + rupiah;

    if (numStr === "" || parseInt(numStr) === 0) {
        input.value = '';
        numStr = '';
    } else {
        // Menghapus angka 0 di depan jika input diawali dengan 0
        rupiah = rupiah.replace(/^0+/, '');
        input.value = rupiah;
    }

    // Update 'salary' input with non-formatted number
    let parsedValue = parseInt(numStr);
    document.getElementById(inputNonFormat).value = isNaN(parsedValue) ? 0 : parsedValue;
}
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
.d-none {
    display: none;
}

.card-header {
    background-color: #007bff;
    color: #fff;
}

.table th,
.table td {
    vertical-align: middle;
}

.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 1rem;
}

.form-label {
    font-weight: bold;
}
</style>
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