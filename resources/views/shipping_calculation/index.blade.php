@extends('adminlte::page')

@section('title', 'Kalkulasi Pengiriman')

@section('content_header')
    <h1 class="text-primary">Kalkulasi Pengiriman</h1>
@stop

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Cari Tarif Pengiriman</h5>
    </div>
    <div class="card-body">
        <form id="shippingSearchForm">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="origin_id" class="form-label">Asal</label>
                    <select name="origin_id" id="origin_id" class="form-control select2-wilayah" required>
                        <option value="" disabled selected>Pilih Asal</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="destination_id" class="form-label">Tujuan</label>
                    <select name="destination_id" id="destination_id" class="form-control select2-wilayah" required>
                        <option value="" disabled selected>Pilih Tujuan</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="weight" class="form-label">Berat (Kg)</label>
                    <input type="number" name="weight" id="weight" class="form-control" placeholder="Masukkan Berat">
                </div>
                <div class="col-md-6">
                    <label for="volume" class="form-label">Volume (CBM)</label>
                    <input type="number" name="volume" id="volume" class="form-control" placeholder="Masukkan Volume">
                </div>
                <div class="col-md-12 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Cari Tarif
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4 shadow-sm">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">Hasil Pencarian</h5>
    </div>
    <div class="card-body">
        <table id="resultTable" class="table table-striped table-bordered d-none">
            <thead>
                <tr>
                    <th>Provider</th>
                    <th>Tipe Layanan</th>
                    <th>Harga Dasar</th>
                    <th>Harga Tambahan</th>
                    <th>Harga Per CBM</th>
                    <th>Estimasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
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
$(document).ready(function () {
    $('.select2-wilayah').select2({
        width: '100%',
        placeholder: 'Cari wilayah...',
        ajax: {
            url: "{{ route('shipping-calculation.select2Origin') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data.results };
            },
            cache: true
        }
    });

    $('#destination_id').select2({
        width: '100%',
        placeholder: 'Cari tujuan...',
        ajax: {
            url: "{{ route('shipping-calculation.select2Destination') }}",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data.results };
            },
            cache: true
        }
    });

    $('#shippingSearchForm').on('submit', function (e) {
        e.preventDefault();

        let formData = $(this).serialize();

        $.ajax({
            url: "{{ route('shipping-calculation.searchRates') }}",
            type: 'GET',
            data: formData,
            success: function (response) {
                let tableBody = $('#resultTable tbody');
                tableBody.empty();

                if (response.rates.length > 0) {
                    $('#resultTable').removeClass('d-none');

                    response.rates.forEach(rate => {
                        let row = `
                            <tr>
                                <td>${rate.provider.name}</td>
                                <td>${rate.service_type.name}</td>
                                <td>${rate.base_price}</td>
                                <td>${rate.additional_price}</td>
                                <td>${rate.rate_per_cbm}</td>
                                <td>${rate.delivery_time}</td>
                                <td>
                                    <button class="btn btn-success btn-sm add-to-cart">
                                        <i class="fas fa-cart-plus"></i> Tambah ke Cart
                                    </button>
                                </td>
                            </tr>`;
                        tableBody.append(row);
                    });
                } else {
                    tableBody.append(`<tr><td colspan="7" class="text-center">Tidak ada hasil ditemukan</td></tr>`);
                }
            }
        });
    });

    $(document).on('click', '.add-to-cart', function () {
        Swal.fire({
            icon: 'success',
            title: 'Ditambahkan ke Keranjang!',
            showConfirmButton: false,
            timer: 1500
        });
    });
});
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    #shippingRateTable_wrapper {
        overflow-x: auto;
    }

    #shippingRateTable {
        width: 100%; /* Pastikan tabel menggunakan lebar penuh */
        white-space: nowrap; /* Hindari pemotongan teks */
    }

    .dataTables_scroll {
        overflow-x: auto;
        display: block;
    }
</style>
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