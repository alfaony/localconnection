@extends('adminlte::page')

@section('content_header')
    <h1>Pricelist Product</h1>
@stop

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h1>Daftar Semua List Product</h1>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <select id="productCategoryFilter" class="form-control">
                        <option value="all" selected>-- All Produk --</option>
                        @foreach($productCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" id="tablePricelist">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Kategori Produk</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        var table = $('#tablePricelist').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("pricelist.datatable") }}',
                type: 'GET',
                data: function(d) {
                    d.product_category_id = $('#productCategoryFilter').val(); // Kirimkan nilai filter kategori produk
                }
            },
            columns: [
                {data: 'name', name: 'name', orderable: true},
                {data: 'product_category_name', name: 'product_category_name', orderable: false, searchable: false},
                {data: 'price_sell', name: 'price_sell', orderable: true, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [[0, 'asc']],
            lengthMenu: [100, 500, 1000], // Set the length menu options
            pageLength: 100 // Set the default page length
        });

        // Reload DataTable when category filter changes
        $('#productCategoryFilter').change(function() {
            table.ajax.reload();
        });
    });
</script>

<script>
    $(document).ready(function () {
        
        $("#btnCreateManager").click(function (e) 
        { 
            e.preventDefault();
            let no = "1";
            let url = "{{ route('work-order.create') }}" + "?nomor="+no;

            window.location.href = url;
            

        });
    });
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }
    .container {
        background-color: #fff;
        padding: 10px;
        border-radius: 5px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    table, th, td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    th {
        background-color: #f2f2f2;
    }
    #buttonSubmit {
        padding: 10px 20px;
        margin-top: 10px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .table {
        width: 100% !important; /* Memastikan tabel mengisi kontainer */
        table-layout: auto; /* Mencegah pemadatan kolom */
    }
    .table-responsive 
    {
        max-height: 400px; /* Atur tinggi maksimal */
        overflow-y: auto; /* Aktifkan scrolling vertikal */
        overflow-x: hidden; /* Sembunyikan scroll horizontal */
    }
</style>
<style>
    #tablePricelist th, #tablePricelist td {
        white-space: nowrap; /* Mencegah teks turun ke bawah */
    }

    #tablePricelist th:nth-child(3), #tablePricelist td:nth-child(3) {
        width: 150px; /* Atur lebar kolom Harga */
        text-align: right; /* Rapi ke kanan */
    }
</style>
@stop