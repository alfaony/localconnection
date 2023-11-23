
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
            <table class="table table-bordered" id="tablePricelist">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@stop
@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        var table = $('#tablePricelist').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("pricelist.datatable")}}',
                type: 'GET',
                dataSrc: 'data'
            },
            columns: [
                {data: 'name', name: 'name', orderable: true},
                {data: 'price_sell', name: 'price_sell', orderable: true},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [[0, 'asc']],
            lengthMenu: [100, 500, 1000], // Set the length menu options
            pageLength: 100 // Set the default page length
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
            /* padding: 20px; */
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
        #buttonSubmit 
        {
            padding: 10px 20px;
            margin-top: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

</style>
@stop