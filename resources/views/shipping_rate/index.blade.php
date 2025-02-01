@extends('adminlte::page')

@section('title', 'Daftar Harga Pengiriman')

@section('content_header')
<h1 class="text-primary">Daftar Harga Pengiriman</h1>
@stop

@section('content')
@include('components.alert')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
        <h5 class="mb-0">List Harga Pengiriman</h5>
        @canAccess('store','shipping_rates')
        <button id="toggleForm" class="btn btn-light btn-sm">
            <i class="fas fa-plus"></i> Tambah
        </button>
        @endcanAccess
        @canAccess('import','shipping_rates')
        <button id="toggleImportForm" class="btn btn-warning btn-sm">
            <i class="fas fa-upload"></i> Import
        </button>
        @endcanAccess
    </div>
    <div class="card-body">
        <!-- Hide/Show Form -->
        @canAccess('store','shipping_rates')
        <div id="createOrEditForm" class="mb-4 p-4 bg-light border rounded d-none">
            <h5 id="formTitle" class="text-center mb-4 text-primary">Tambah Data Harga Pengiriman</h5>
            <form id="shippingRateForm" method="POST">
                <input type="hidden" name="id" id="shippingRateId">

                <div class="row g-3">
                    <!-- Provider and Service Type -->
                    <div class="col-md-6">
                        <label for="provider_id" class="form-label">Provider</label>
                        <select name="provider_id" id="provider_id" class="form-control select2" required>
                            <option value="" disabled selected>Pilih Provider</option>
                            @foreach($providers as $provider)
                            <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="service_type_id" class="form-label">Tipe Layanan</label>
                        <select name="service_type_id" id="service_type_id" class="form-control select2" required>
                            <option value="" disabled selected>Pilih Tipe Layanan</option>
                            @foreach($serviceTypes as $serviceType)
                            <option value="{{ $serviceType->id }}">{{ $serviceType->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Origin and Destination -->
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
                    <div class="alert alert-danger mt-3" id="duplicate-warning" style="display: none;">
                        Kombinasi Asal dan Tujuan sudah ada!
                    </div>

                    <!-- Base Weight and Base Price -->
                    <div class="col-md-6">
                        <label for="base_weight" class="form-label">Berat Dasar (Kg)</label>
                        <input type="number" name="base_weight" id="base_weight" class="form-control"
                            placeholder="Contoh: 5" required>
                    </div>
                    <div class="col-md-6">
                        <label for="base_price" class="form-label">Harga Dasar</label>
                        <input type="text" id="base_price_show" class="form-control" placeholder=""
                            oninput="formatRupiahFormat(this,'base_price')" required>
                        <input type="hidden" name="base_price" id="base_price" class="form-control" />
                    </div>

                    <!-- Additional Weight and Price -->
                    <div class="col-md-6">
                        <label for="additional_weight" class="form-label">Berat Tambahan (Kg)</label>
                        <input type="number" name="additional_weight" id="additional_weight" class="form-control"
                            placeholder="Contoh: 1">
                    </div>
                    <div class="col-md-6">
                        <label for="additional_price" class="form-label">Harga Tambahan</label>
                        <input type="text" id="additional_price_show" class="form-control"
                            placeholder="Contoh: Rp 50.000" oninput="formatRupiahFormat(this,'additional_price')"
                            required>
                        <input type="hidden" name="additional_price" id="additional_price" class="form-control" />
                    </div>

                    <!-- Rate per CBM and Delivery Time -->
                    <div class="col-md-6">
                        <label for="rate_per_cbm" class="form-label">Harga Per CBM</label>
                        <input type="text" id="rate_per_cbm_show" class="form-control" placeholder="Contoh: Rp 50.000"
                            oninput="formatRupiahFormat(this,'rate_per_cbm')" required>
                        <input type="hidden" name="rate_per_cbm" id="rate_per_cbm" class="form-control" />
                    </div>
                    <div class="col-md-6">
                        <label for="delivery_time" class="form-label">Estimasi Waktu Pengiriman</label>
                        <input type="text" name="delivery_time" id="delivery_time" class="form-control"
                            placeholder="Contoh: 2-4 Hari">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="reset" id="cancelForm" class="btn btn-secondary me-2">Batal</button>
                    <button type="submit" class="btn btn-primary ml-2">Simpan</button>
                </div>
            </form>
        </div>
        @endcanAccess
        @canAccess('import','shipping_rates')
        <div id="importFormContainer" class="mb-4 p-4 bg-light border rounded d-none">
            <h5 class="text-center mb-4 text-warning">Import Data Harga Pengiriman</h5>
            <!-- <form action="{{ route('shipping-rate.import') }}" enctype="multipart/form-data" method="POST">
            @csrf -->
            <form id="importForm" enctype="multipart/form-data" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <label for="provider_id" class="form-label">Provider</label>
                        <select name="provider_id" id="provider_id" class="form-control select2" required>
                            <option value="" disabled selected>Pilih Provider</option>
                            @foreach($providers as $provider)
                            <option value="{{ $provider->id }}">{{ $provider->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="service_type_id" class="form-label">Tipe Layanan</label>
                        <select name="service_type_id" id="service_type_id" class="form-control select2" required>
                            <option value="" disabled selected>Pilih Tipe Layanan</option>
                            @foreach($serviceTypes as $serviceType)
                            <option value="{{ $serviceType->id }}">{{ $serviceType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label for="importFile" class="form-label">Upload File (.csv,)</label>
                        <input type="file" name="import_file" id="importFile" class="form-control" accept=".csv"
                            required>
                    </div>
                </div>
                <div class="progress mt-3 d-none" id="progressBarContainer">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%
                    </div>
                </div>
                <div id="importErrors" class="alert alert-danger d-none mt-3">
                    <h5>Kesalahan pada Baris:</h5>
                    <ul id="errorList"></ul>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <button type="reset" id="cancelImport" class="btn btn-secondary me-2">Batal</button>
                    <button type="submit" id="importButton" class="btn btn-warning ml-2" disabled>Upload & Import</button>
                </div>
            </form>
        </div>
        @endcanAccess
        <form method="GET" action="{{ route('shipping-rate.index') }}" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari Provider, Kota, atau Harga" value="{{ request()->search }}">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </div>
        </form>
        <div class="table-responsive">
            <table id="" class="table table-striped table-bordered table-hover">
                <thead class="bg-light">
                    <tr>
                        <th>Provider</th>
                        <th>Tipe Layanan</th>
                        <th>Asal</th>
                        <th>Tujuan</th>
                        <th>Berat Dasar</th>
                        <th>Harga Dasar</th>
                        <th>Berat Selanjutnya</th>
                        <th>Harga Berat Selanjutnya</th>
                        <th>Estimasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shippingRates as $index => $rate)
                        <tr>
                            <td class="align-middle text-center">{{ $rate->provider->name ?? '-' }}</td>
                            <td class="align-middle text-center">{{ $rate->serviceType->name ?? '-' }}</td>
                            <td class="align-middle">
                                <strong>{{ $rate->origin->full_name }}</strong>
                            </td>
                            <td class="align-middle">
                                <strong>{{ $rate->destination->full_name }}</strong>
                            </td>
                            <td class="align-middle text-center">{{ $rate->base_weight }}</td>
                            <td class="align-middle text-right">Rp {{ number_format($rate->base_price, 0, ',', '.') }}</td>
                            <td class="align-middle text-center">{{ $rate->additional_weight ?? '-' }}</td>
                            <td class="align-middle text-right">Rp {{ number_format($rate->additional_price, 0, ',', '.') }}</td>
                            <td class="align-middle text-center">{{ $rate->delivery_time }}</td>
                            <td class="align-middle text-center">
                                @canAccess('edit','shipping_rates')
                                <a href="{{ route('shipping-rate.edit', $rate->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcanAccess

                                @canAccess('destroy','shipping_rates')
                                <form action="{{ route('shipping-rate.destroy', $rate->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus?')" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                                @endcanAccess
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $shippingRates->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
@canAccess('import','shipping_rates')
@canAccess('validateCsv','shipping_rates')
@canAccess('progress','shipping_rates')
@canAccess('checkDuplicate','shipping_rates')
<script>
    document.getElementById('importFile').addEventListener('change', function(e) {
        const formData = new FormData();
        const file = e.target.files[0];

        if (!file) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Tidak ada file yang dipilih.',
            });
            return;
        }

        formData.append('import_file', file);

        // Kirim file untuk validasi
        $.ajax({
            url: "{{ route('shipping-rate.validateCsv') }}",
            method: 'POST',
            data: formData,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: response.message,
                    text: 'File valid. Anda dapat melanjutkan proses import.',
                });

                // Enable tombol "Import" setelah validasi berhasil
                document.getElementById('importButton').disabled = false;
            },
            error: function(xhr) {
                document.getElementById('importButton').disabled = true;
                if (xhr.status === 422) {
                    let errorMessage = "Gagal memvalidasi file. Periksa kembali:\n";

                // Jika ada pesan error utama dari server
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += `<strong>${xhr.responseJSON.message}</strong><br>`;
                }

                // Tangkap error validasi dari response
                let errors = xhr.responseJSON.errors || {};
                let errorList = '<ul class="text-left">'; // Agar lebih rapi dalam list

                Object.keys(errors).forEach(key => {
                    errors[key].forEach(error => { 
                        errorList += `<li>${error}</li>`;  
                    });
                });

                errorList += '</ul>';

                // Tampilkan error dalam modal alert dengan Swal
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Validasi',
                    html: errorMessage + errorList, // Menggunakan `html` agar mendukung format list
                });
                } else {
                    console.log(xhr);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan saat memvalidasi file.',
                    });
                }
            },
        });
    });

    $(document).ready(function() {
        // Toggle Import Form
        $('#toggleImportForm').on('click', function() {
            $('#importFormContainer').toggleClass('d-none');
            $('#createOrEditForm').addClass('d-none');
        });

        $('#cancelImport').on('click', function() {
            $('#importFormContainer').addClass('d-none');
            $('#importForm').trigger('reset');
            $('#progressBarContainer').addClass('d-none');
        });

        // Handle Import Submit
        $('#importForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            $('#progressBarContainer').removeClass('d-none');
            $('#progressBar').css('width', '0%').text('0%');

            $.ajax({
                url: "{{ route('shipping-rate.import') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.batch_id) {
                        // Memulai pengecekan progres jika batch_id tersedia
                        checkProgress(response.batch_id);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal memulai proses pengunggahan file.',
                        });
                        $('#progressBarContainer').addClass('d-none');
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengunggah atau memproses file.',
                    });
                    $('#progressBarContainer').addClass('d-none');
                },
            });
        });

        // Check Progress
    });

    document.addEventListener('DOMContentLoaded', function () 
    {
        let batchId = "{{ session('import_batch_id') }}";
        if (batchId) {
            checkProgress(batchId);
        }
    });

    function checkProgress(batchId) 
    {
        const url = `{{ route('shipping-rate.progress', ':id') }}`.replace(':id', batchId);

        $.get(url, function(response) {
            const progress = Math.round(response.progress || 0);
            $('#progressBarContainer').removeClass('d-none');
            $('#progressBar').css('width', `${progress}%`).text(`${progress}%`);

            if (progress < 100) {
                setTimeout(() => checkProgress(batchId), 1000);
            }else {
                console.log(response);
                
            if (response.errors) {
                $('#importErrors').removeClass('d-none');
                response.errors.forEach(error => {
                    $('#errorList').append(`<li>Line :${error.row}: Error :${error.error}</li>`);
                });
                $('#shippingRateTable').DataTable().ajax.reload();
            } else {
                $('#shippingRateTable').DataTable().ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Import Selesai!',
                    text: 'Semua data berhasil diimpor.',
                });
            }
        }
        }).fail(function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal memeriksa progres.',
            });
            $('#progressBarContainer').addClass('d-none');
        });
    }
</script>
@endcanAccess
@endcanAccess
@endcanAccess
@endcanAccess

@canAccess('store','shipping_rates')
@canAccess('checkDuplicate','shipping_rates')
@canAccess('dataTableJson','shipping_rates')
<script>
$(document).ready(function() {
    // Setup CSRF Token for AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
    });

    // Submit Form (Create or Update)
    $('#shippingRateForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();
        const id = $('#shippingRateId').val();
        const url = id ? `/shipping-rate/${id}` : "{{ route('shipping-rate.store') }}";
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: formData,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: response.message,
                    showConfirmButton: false,
                    timer: 1500,
                });
                $('#provider_id').val(null).trigger('change');
                $('#origin_id').val(null).trigger('change');
                $('#destination_id').val(null).trigger('change');
                $('#service_type_id').val(null).trigger('change');

                $('#shippingRateForm').trigger('reset'); // Reset form
                $('#shippingRateId').val(''); // Clear ID
                $('#createOrEditForm').addClass('d-none'); // Hide form
                $('#shippingRateTable').DataTable().ajax.reload(); // Reload table
            },
            error: function(xhr) {
                console.log(xhr);
                
                if (xhr.status === 422) {
                    // Show validation errors
                    let errors = xhr.responseJSON.errors;
                    let errorMessage = '';
                    $.each(errors, function(key, value) {
                        errorMessage += `${value[0]}<br>`;
                    });
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorMessage,
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Terjadi kesalahan. Silakan coba lagi.',
                    });
                }
            },
        });
    });

    // Reset Form
    $('#cancelForm').on('click', function() {
        $('#shippingRateForm').trigger('reset');
        $('#shippingRateId').val('');
        $('.select2').val(null).trigger('change');
    });
});

$(document).ready(function() {
    function validateCombination() {
        const providerId = $('#provider_id').val();
        const originId = $('#origin_id').val();
        const destinationId = $('#destination_id').val();

        // Pastikan ketiga field sudah diisi
        if (providerId && originId && destinationId) {
            $.ajax({
                url: "{{ route('shipping-rate.checkDuplicate') }}",
                method: 'POST',
                data: {
                    provider_id: providerId,
                    origin_id: originId,
                    destination_id: destinationId,
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
                success: function(response) {
                    if (response.exists) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Duplicate Entry',
                            text: 'Kombinasi Asal, Tujuan, dan Provider sudah ada!',
                        });
                        // Reset the dropdowns or handle duplicate state
                        $('#provider_id').val(null).trigger('change');
                        $('#origin_id').val(null).trigger('change');
                        $('#destination_id').val(null).trigger('change');
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memvalidasi kombinasi data.',
                    });
                },
            });
        }
    }

    // Attach the validation to change events
    $('#provider_id, #origin_id, #destination_id').on('change', validateCombination);
});

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

    const table = $('#shippingRateTable').DataTable({
        processing: true,
        serverSide: true,
        scrollX: true, // Pastikan ini di-set ke true
        autoWidth: false, // Pastikan autoWidth di-disable
        ajax: "{{ route('shipping-rate.dataTableJson') }}",
        columnDefs: 
        [
            { width: '50px', targets: 0 },  // Provider
            { width: '50px', targets: 1 },  // Tipe Layanan
            { width: '200px', targets: 2 },  // Asal
            { width: '200px', targets: 3 },  // Tujuan
            { width: '120px', targets: 4 },  // Berat Dasar
            { width: '150px', targets: 5 },  // Harga Dasar
            { width: '150px', targets: 6 },  // Berat Selanjutnya
            { width: '180px', targets: 7 },  // Harga Berat Selanjutnya
            { width: '120px', targets: 8 },  // Estimasi
            { width: '100px', targets: 9 }   // Aksi
        ],
        columns: [{
                data: 'provider.name',
                name: 'provider.name',
                orderable: false,
                searchable: false
            },
            {
                data: 'service_type.name',
                name: 'service_type.name',
                orderable: false,
                searchable: false
            },
            {
                data: 'origin',
                name: 'origin',
                orderable: false,
                searchable: false
            },
            {
                data: 'destination',
                name: 'destination',
                orderable: false,
                searchable: false
            },
            {
                data: 'base_weight',
                name: 'base_weight',
                orderable: false,
                searchable: false
            },
            {
                data: 'base_price',
                name: 'base_price',
                orderable: false,
                searchable: false
            },
            {
                data: 'additional_weight',
                name: 'additional_weight',
                orderable: false,
                searchable: false
            },
            {
                data: 'additional_price',
                name: 'additional_price',
                orderable: false,
                searchable: false
            },
            {
                data: 'delivery_time',
                name: 'delivery_time',
                orderable: false,
                searchable: false
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
        ],
        scrollX: true, // Aktifkan scroll horizontal
        responsive: true, // Aktifkan dukungan responsif
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
@endcanAccess
@endcanAccess
@endcanAccess
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
    /* Pastikan header tabel tetap visible saat di-scroll */
.dataTables_scrollHeadInner 
{
    width: 100% !important;
}

.dataTables_scrollHead table {
    width: 100% !important;
}

/* Styling scrollbar */
::-webkit-scrollbar {
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
<style>
    .select2-selection__rendered 
    {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single 
    {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }
    .ql-container 
    {
        min-height: 150px;
        height: auto;
    }
</style>
<style>
    /* Membuat tabel dapat di-scroll secara horizontal jika terlalu lebar */
    .table-responsive {
        overflow-x: auto;
    }
    .table th, .table td {
        white-space: nowrap; /* Mencegah wrapping teks dalam tabel */
    }
    .btn-sm {
        padding: 4px 8px;
    }
</style>
@stop