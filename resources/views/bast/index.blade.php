@extends('adminlte::page')

@section('content_header')
    <h1>Daftar BAST dan SPK</h1>
@stop

@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
        <div class="alert alert-success mt-3">BAST Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
        <div class="alert alert-success mt-3">BAST Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
        <div class="alert alert-success mt-3">BAST Berhasil Terhapus</div>
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
    <!-- Nav tabs -->
    <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="bast-tab" data-toggle="tab" href="#bast" role="tab" aria-controls="bast" aria-selected="true">
                <i class="fas fa-file-alt"></i> BAST
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="spk-tab" data-toggle="tab" href="#spk" role="tab" aria-controls="spk" aria-selected="false">
                <i class="fas fa-project-diagram"></i> SPK
            </a>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <!-- BAST Tab -->
        <div class="tab-pane fade show active" id="bast" role="tabpanel" aria-labelledby="bast-tab">
            <div class="card mt-3 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">BAST List</h3>
                    @canAccess('create','basts')
                    <button class="btn btn-light float-right" id="btnCreateManager">
                        <i class="fas fa-plus-circle"></i> Tambah BAST
                    </button>
                    @endcanAccess
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped" id="datatableBast">
                            <thead class="bg-light">
                                <tr>
                                    <th>Nomor BAST</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Baris data akan terisi melalui DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- SPK Tab -->
        <div class="tab-pane fade" id="spk" role="tabpanel" aria-labelledby="spk-tab">
            <div class="card mt-3 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">List SPK</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Nomor SPK</th>
                                    <th>Nama Proyek</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>SPK-001</td>
                                    <td>Proyek A</td>
                                    <td>2024-01-10</td>
                                    <td>2024-02-15</td>
                                </tr>
                                <tr>
                                    <td>SPK-002</td>
                                    <td>Proyek B</td>
                                    <td>2024-01-20</td>
                                    <td>2024-03-01</td>
                                </tr>
                                <!-- Tambahkan baris data SPK sesuai kebutuhan -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // Initialize DataTable for BAST
        var table = $('#datatableBast').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("bast.datatable") }}',
                type: 'GET',
                dataSrc: 'data'
            },
            columns: [
                {data: 'number_result', name: 'number_result', orderable: true},
                {data: 'date', name: 'date', orderable: true},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            order: [[0, 'desc']],
        });
    });

    $("#btnCreateManager").click(function(e) {
        e.preventDefault();
        let url = "{{ route('bast.create') }}";
        window.location.href = url;
    });
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

<style>
    body {
        background-color: #f4f4f4;
    }
    .container {
        padding: 20px;
    }
    .card {
        background-color: #fff;
        border-radius: 8px;
    }
    .table {
        margin-bottom: 0;
    }
    .btn-light {
        color: #007bff;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    .btn-light:hover {
        color: #0056b3;
        background-color: #d6d8db;
    }
</style>
@stop
