@extends('adminlte::page')

@section('content_header')
    <h1>Laporan Proyek</h1>
@stop

@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Laporan Proyek Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Laporan Proyek Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Laporan Proyek Berhasil Terhapus</div>
    @endif
    @if(Session::get('datanotfound'))
        <div class="alert alert-danger mt-3">Data Tidak Ditemukan</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
<div class="container">    
    
    <!-- Tombol Tambah Pembelian Baru -->
    @canAccess('create','report_projects')
    <button class="btn btn-primary mb-3" id="btnCreateReportProject">Tambah Laporan Proyek</button>
    @endcanAccess

    <!-- Tabel Pembelian -->
    <table class="table table-bordered" id="datatableLaporanProject">
        <thead>
            <tr>
                <th>Nomor Laporan Proyek</th>
                <th>Date</th>
                <th>Aksi</th>
            </tr>
        </thead>
        </tbody>
            <!-- ... Tambahkan baris lain sesuai kebutuhan ... -->
        </tbody>
    </table>
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
        // datatableLaporanProject
        var table = $('#datatableLaporanProject').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("report-project.datatable")}}',
                type: 'GET',
                dataSrc: 'data'
            },
            columns: [
                {data: 'number_result', name: 'number_result', orderable: true},
                {data: 'date', name: 'date', orderable: true},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [[1, 'desc']],
        });
    });
</script>

<script>
    $(document).ready(function () {
        
        $("#btnCreateReportProject").click(function (e) 
        { 
            e.preventDefault();
            let url = "{{ route('report-project.create') }}";

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
        #buttonSubmit {
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
