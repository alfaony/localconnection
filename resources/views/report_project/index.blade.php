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
        <div class="alert alert-danger mt-3">SPK Tidak Ditemukan</div>
    @endif
    @if(Session::get('dataprojectnotfound'))
        <div class="alert alert-danger mt-3">Proyek Tidak Ditemukan</div>
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

<ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
    @canAccess('dataTableJson','report_projects')
    <li class="nav-item">
        <a class="nav-link active" id="bast-tab" data-toggle="tab" href="#bast" role="tab" aria-controls="bast" aria-selected="true">
            <i class="fas fa-file-alt"></i> Laporan Proyek
        </a>
    </li>
    @endcanAccess

    @canAccess('dataTableJsonWorkOrderWithoutReportProject','report_projects')
    <li class="nav-item">
        <a class="nav-link" id="spk-tab" data-toggle="tab" href="#spk_report" role="tab" aria-controls="spk_report" aria-selected="false">
            <i class="fa fa-clipboard-list"></i> Proyek (Belum Terbuat Laporan)
        </a>
    </li>
    @endcanAccess
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <!-- BAST Tab -->
    @canAccess('dataTableJson','report_projects')
    <div class="tab-pane fade show active" id="bast" role="tabpanel" aria-labelledby="bast-tab">
        <div class="card mt-3 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Laporan Proyek</h3>
                @canAccess('create','report_projects')
                <button class="btn btn-light float-right" id="btnCreateReportProject">
                    <i class="fas fa-plus-circle"></i>
                    Tambah Laporan Proyek
                </button>
                @endcanAccess
            </div>
            <div class="card-body">
                <div class="table-responsive">
                        <!-- Tabel Pembelian -->
                    <table class="table table-bordered" id="datatableLaporanProject">
                        <thead>
                            <tr>
                                <th>Nomor Laporan Proyek</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>SPK</th>
                                <th>Project</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- ... Tambahkan baris lain sesuai kebutuhan ... -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endcanAccess

    @canAccess('dataTableJsonWorkOrderWithoutReportProject','report_projects')
    <!-- SPK Tab -->
    <div class="tab-pane fade" id="spk_report" role="tabpanel" aria-labelledby="spk-tab">
        <div class="card mt-3 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Daftar Proyek</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-bordered" id="dataTableJsonWorkOrderWithoutReportProject" style="width:100%">
                        <thead>
                            <tr>
                                <th>Proyek</th>
                                <th>Nomor SPK</th>
                                <th>Total Anggaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- ... Tambahkan baris lain sesuai kebutuhan ... -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endcanAccess
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
                {data: 'number_result', name: 'number_result', orderable: false},
                {data: 'is_approve', name: 'is_approve', orderable: false},
                {data: 'date', name: 'date', orderable: false},
                {data: 'work_order.number_result', name: 'work_order.number_result'},
                {data: 'project.title', name: 'project_title', orderable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            // order: [[1, 'desc']],
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        var table = $('#dataTableJsonWorkOrderWithoutReportProject').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("report-project.dataTableJsonWorkOrderWithoutReportProject")}}',
                type: 'GET',
                dataSrc: 'data'
            },
            columns: [
                {data: 'title', name: 'title', orderable: false},
                {data: 'work_order.number_result', name: 'work_order.number_result', orderable: false},
                {data: 'work_order.total', name: 'work_order.total', orderable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
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