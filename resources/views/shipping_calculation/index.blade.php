@extends('adminlte::page')

@section('title', 'Kalkulasi Pengiriman')

@section('content')
<div class="row mt-2">
    <!-- Form Pencarian dan Hasil -->
    <div class="col-md-8">
        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white d-flex align-items-center">
                <i class="fas fa-search-location mr-2"></i>
                <h5 class="mb-0">Cari Tarif Pengiriman</h5>
            </div>
            <div class="card-body">
                <form id="shippingSearchForm">
                    <div class="row g-3">
                        <!-- Asal & Tujuan Section -->
                         @canAccess('select2Origin','shipping_calculations')
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="origin_id" class="form-label font-weight-bold">Lokasi Asal</label>
                                <div class="input-group">
                                    <select name="origin_id" id="origin_id" class="form-control select2-wilayah" required>
                                        <option value="" disabled selected>Pilih Kota/Kabupaten Asal</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        @endcanAccess

                        @canAccess('select2Destination','shipping_calculations')
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="destination_id" class="form-label font-weight-bold">Lokasi Tujuan</label>
                                <div class="input-group">
                                    <select name="destination_id" id="destination_id" class="form-control select2-wilayah w-100"
                                        required>
                                        <option value="" disabled selected>Pilih Kota/Kabupaten Tujuan</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        @endcanAccess

                        <!-- Berat dan Volume Section -->
                         @canAccess('searchRates','shipping_calculations')
                        <div class="col-md-6" id="weightGroup">
                            <div class="form-group">
                                <label for="weight" class="form-label font-weight-bold">Berat Paket (Kg)</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-weight-hanging"></i></span>
                                    </div>
                                    <input type="number" name="weight" id="weight" class="form-control"
                                        placeholder="Masukkan berat dalam kilogram" min="0.1" step="0.1" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">kg</span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Contoh: 2.5 kg</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-switch mt-4 pt-2">
                                    <input type="checkbox" class="custom-control-input" id="useVolume">
                                    <label class="custom-control-label font-weight-bold" for="useVolume">
                                        <i class="fas fa-cube mr-2"></i>Hitung Berdasarkan Volume
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Volume Inputs -->
                        <div id="volumeInputs" class="col-12 d-none">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white py-2">
                                    <i class="fas fa-ruler-combined mr-2"></i>Dimensi Paket
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="length" class="form-label">Panjang</label>
                                                <div class="input-group">
                                                    <input type="number" id="length" name="length" class="form-control volume-input"
                                                        placeholder="0" min="1" step="1">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">cm</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="width" class="form-label">Lebar</label>
                                                <div class="input-group">
                                                    <input type="number" id="width" name="width" class="form-control volume-input"
                                                        placeholder="0" min="1" step="1">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">cm</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="height" class="form-label">Tinggi</label>
                                                <div class="input-group">
                                                    <input type="number" id="height" name="height" class="form-control volume-input"
                                                        placeholder="0" min="1" step="1">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">cm</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo mr-2"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-primary ml-2">
                                    <i class="fas fa-search-dollar mr-2"></i>Cari Tarif
                                </button>
                            </div>
                        </div>
                        @endcanAccess
                    </div>
                </form>
            </div>
        </div>

        <!-- Hasil Pencarian Section -->
        <div class="card mt-4 shadow-sm border-success">
            <div class="card-header bg-success text-white d-flex align-items-center">
                <i class="fas fa-clipboard-list mr-2"></i>
                <h5 class="mb-0">Hasil Perhitungan Tarif</h5>
            </div>
            <div class="card-body">
                <div id="loading" class="text-center d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Mencari tarif pengiriman...</p>
                </div>

                <div id="resultsSection" class="d-none">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="resultsGrid"></div> <!-- Container untuk hasil pencarian -->
                        </div>
                    </div>

                    <div class="mt-3 text-muted text-center">
                        <small>
                            <i class="fas fa-info-circle mr-2"></i>
                            Tarif dapat berubah sesuai kebijakan penyedia layanan
                        </small>
                    </div>
                </div>

                <div id="noResults" class="alert alert-warning d-none">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Tidak menemukan tarif untuk rute ini
                </div>
            </div>
        </div>
    </div>

    @canAccess('detail','shipping_calculations')
    <div class="col-md-4">
        <div class="card shadow-sm border-success">
            <div class="card-header bg-success text-white d-flex justify-content-between">
                <h5 class="mb-0"><i class="fas fa-shopping-cart mr-2"></i>Kalkulasi</h5>
                <button class="btn btn-sm btn-light text-danger" id="clearCart">
                    <i class="fas fa-trash"></i> Reset
                </button>
            </div>
            <div class="card-body">
                <ul id="cartItems" class="list-group mb-3"></ul>
                <h5 class="text-end text-success">Total: <span id="cartTotal">Rp. 0</span></h5>
            </div>
        </div>
    </div>
    @endcanAccess
</div>

@stop

@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script>
$(document).ready(function() {
    let cart = [];

    $('.select2-wilayah').select2({
        width: '100%',
        placeholder: 'Cari wilayah...',
        ajax: {
            url: "{{ route('shipping-calculation.select2Origin') }}",
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.results
                };
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
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.results
                };
            },
            cache: true
        }
    });

    // Toggle Volume Inputs
    $('#useVolume').change(function() {
        if ($(this).is(':checked')) {
            $('#volumeInputs').removeClass('d-none');
            $('#weight').removeAttr('required');
            $('#weight').val('');
            $('#weightGroup').addClass('d-none');
        } else 
        {
            $('#weightGroup').removeClass('d-none');
            $('#volumeInputs').addClass('d-none');
            $('#length, #width, #height').val('');
            $('#volume').val('');
            $('#weight').attr('required', true);
        }
    });

    // Hitung Volume Otomatis
    $('.volume-input').on('input', function() {
        let length = parseFloat($('#length').val()) || 0;
        let width = parseFloat($('#width').val()) || 0;
        let height = parseFloat($('#height').val()) || 0;
        let volume = (length * width * height) / 1000000; // Convert cm³ to m³ (CBM)
        $('#volume').val(volume.toFixed(4));
    });

    $('#shippingSearchForm').on('submit', function(e) {
        e.preventDefault();

        let formData = $(this).serialize();
        let resultsGrid = $('#resultsGrid');
        resultsGrid.empty(); // Clear previous results

        $('#loading').removeClass('d-none'); // Show loading indicator

        $.ajax({
            url: "{{ route('shipping-calculation.searchRates') }}",
            type: 'GET',
            data: formData,
            success: function(response) {
                let resultsGrid = $('#resultsGrid');
                resultsGrid.empty();
                
                $('#loading').addClass('d-none'); // Hide loading indicator after results are processed

                if (response.rates.length > 0) {
                    $('#resultsSection').removeClass('d-none');

                    response.rates.forEach(rate => {
                        let card = `
                        <div class="card shadow-sm mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <!-- Provider & Layanan -->
                                    <div class="col-md-2">
                                        <h6 class="text-primary mb-1">
                                            <i class="fas fa-shipping-fast"></i> ${rate.provider.name}
                                        </h6>
                                        <p class="small text-muted">${rate.service_type.name}</p>
                                    </div>

                                    <!-- Berat & Tarif -->
                                    <div class="col-md-4">
                                        <span class="badge bg-secondary">Berat Dasar: ${rate.base_weight} Kg</span>
                                        <h5 class="text-success mt-2">${formatRupiah(rate.base_price ?? 0, 'Rp. ')}</h5>
                                        <small class="text-muted">Tambahan ${rate.additional_weight} Kg: ${formatRupiah(rate.additional_price ?? 0, 'Rp. ')}</small>
                                    </div>

                                    <!-- Tarif CBM & Estimasi -->
                                    <div class="col-md-2">
                                        <span class="badge bg-warning">Tarif CBM: ${formatRupiah(rate.rate_per_cbm ?? 0, 'Rp. ')}</span>
                                        <h6 class="text-muted mt-2">
                                            <i class="fas fa-clock"></i> ${rate.delivery_time}
                                        </h6>
                                    </div>

                                    <!-- Estimasi Harga -->
                                    <div class="col-md-2">
                                        <h6 class="text-success mt-2">
                                            <i class="fas fa-calculator"></i> Estimasi: ${formatRupiah(rate.estimated_price ?? 0, 'Rp. ')}
                                        </h6>
                                    </div>

                                    <!-- Tombol -->
                                    <div class="col-md-2 text-end">
                                        <button class="btn btn-success btn-sm add-to-cart" data-rate='${JSON.stringify(rate)}'>
                                            <i class="fas fa-cart-plus"></i> Tambah
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                
                `;

                        resultsGrid.append(card);
                    });
                } else {
                    resultsGrid.append(`
            <div class="text-center text-muted py-3">
                <p><i class="fas fa-exclamation-circle"></i> Tidak ada hasil ditemukan</p>
            </div>
            `);
                }
            }
        });
    });

    $(document).on('click', '.add-to-cart', function() {
        let rate = $(this).data('rate');
        
        $.ajax(
            {
            url: "{{ route('shipping-calculation.detail') }}",
            type: "GET",
            data: {
                rate_id: rate.id,
                origin_id: rate.origin_id,
                destination_id: rate.destination_id,
                weight: rate.weight,
                height: rate.height,
                width: rate.width,
                length: rate.length
            },
            success: function(response) {
                console.log(response); 
                if (response.origin && response.destination) 
                {
                    rate.origin = response.origin;
                    rate.destination = response.destination;
                    rate.weight = response.weight;
                    rate.height = response.height;
                    rate.width = response.width;
                    rate.length = response.length;
                    
                    cart.push(rate);
                    updateCart();
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal mengambil data lokasi!',
                    text: 'Silakan coba lagi nanti.'
                });
            }
        });

        Swal.fire({
            icon: 'success',
            title: 'Ditambahkan ke Keranjang!',
            showConfirmButton: false,
            timer: 1500
        });
    });

    // Update Keranjang Belanja
    function updateCart() {
        let total = 0;
        $('#cartItems').empty();

        cart.forEach((item, index) => {            
            total += parseFloat(item.estimated_price);
            let inforVolume = item.length && item.width && item.height ? `
                        <small class="text-muted d-block">
                            <i class="fas fa-cube"></i> Volume: ${item.length} x ${item.width} x ${item.height} cm
                        </small>
            ` : '';
            let cartItem = `
           <li class="list-group-item">
                <div class="d-flex justify-content-between">
                    <!-- Kiri: Detail Layanan -->
                    <div class="cart-item-info flex-grow-1">
                        <h6 class="text-primary mb-1">
                            <i class="fas fa-truck"></i> ${item.provider.name} - ${item.service_type.name}
                        </h6>
                        <small class="text-muted d-block text-truncate cart-location">
                            <i class="fas fa-map-marker-alt"></i> ${item.origin} ➝ ${item.destination}
                        </small>
                        ${inforVolume}
                        <small class="text-muted d-block">
                            <i class="fas fa-weight"></i> Berat: ${item.weight} Kg
                        </small>
                    </div>

                    <!-- Kanan: Harga & Hapus -->
                    <div class="text-end ms-3">
                        <h6 class="text-success mb-1">${formatRupiah(item.estimated_price, 'Rp. ')}</h6>
                        <button class="btn btn-danger btn-sm remove-cart-item" data-index="${index}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </li>
            `;

            $('#cartItems').append(cartItem);
        });

        $('#cartTotal').text(formatRupiah(total, 'Rp. '));
    }

    // Hapus Item dari Keranjang
    $(document).on('click', '.remove-cart-item', function() {
        let index = $(this).data('index');
        cart.splice(index, 1);
        updateCart();
    });

    $('#clearCart').click(() => {
        cart = [];
        updateCart();
    })

});

function formatRupiah(angka, prefix) {
    var number_string = angka.toString().replace('/[^,\d]/g', '').toString(),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    // tambahkan titik jika yang di input sudah menjadi angka ribuan
    if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
}
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
.provider-logo {
    width: 30px;
    height: 30px;
    object-fit: contain;
}

#volumeInputs .card {
    border-radius: 0.5rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    cursor: pointer;
}

.custom-switch .custom-control-label::before {
    border-radius: 1rem;
}

.custom-control-input:checked~.custom-control-label::before {
    background-color: #28a745;
    border-color: #28a745;
}

.select2-container .select2-selection--single {
    height: calc(2.25rem + 2px) !important;
    /* Samakan dengan tinggi input bootstrap */
    display: flex;
    align-items: center;
    padding-left: 0.75rem;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 2.25rem !important;
}
</style>
@stop