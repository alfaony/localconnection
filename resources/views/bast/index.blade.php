@extends('adminlte::page')

@section('content_header')
    <h1>Daftar BAST</h1>
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
    @if(Session::get('export'))
    <div class="alert alert-info mt-3">Export BAST Sedang Diproses</div>
    @endif
    @if(Session::get('datanotfound'))
        <div class="alert alert-danger mt-3">SPK Tidak Ditemukan</div>
    @endif
    @if(Session::get('dataprojectnotfound'))
        <div class="alert alert-danger mt-3">Proyek Tidak Ditemukan</div>
    @endif
    
    @error('message')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror

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

<!-- Nav tabs -->
<ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
    @canAccess('dataTableJson','basts')
    <li class="nav-item">
        <a class="nav-link active" id="bast-tab" data-toggle="tab" href="#bast" role="tab" aria-controls="bast" aria-selected="true">
            <i class="fas fa-file-alt"></i> BAST
        </a>
    </li>
    @endcanAccess
    @canAccess('dataTableJsonWorkOrderWithoutBast','basts')
    <li class="nav-item">
        <a class="nav-link" id="spk-tab" data-toggle="tab" href="#spk_bast" role="tab" aria-controls="spk" aria-selected="false">
            <i class="fa fa-clipboard-list"></i> SPK ( Belum Terbuat BAST )
        </a>
    </li>
    @endcanAccess
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <!-- BAST Tab -->
    @canAccess('dataTableJson','basts')
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
                <div class="row mb-2">
                    <div class="col-md-4">
                        @canAccess('export','basts')
                        @canAccess('checkExportStatus','basts')
                        @canAccess('clearsession','basts')
                        <div class="d-flex">
                            <label for="">Export</label>
                        </div>
                        <a href="{{ route('bast.export', ['format' => 'xlsx']) }}" class="btn btn-success ml-2">
                            <i class="fa fa-file-excel"></i> Excel
                        </a>
                        <a href="{{ route('bast.export', ['format' => 'csv']) }}" class="btn btn-primary ml-2">
                            <i class="fa fa-file-csv"></i> CSV
                        </a>
                        @endcanAccess
                        @endcanAccess
                        @endcanAccess
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped" id="datatableBast">
                        <thead class="bg-light">
                            <tr>
                                <th>Nomor BAST</th>
                                <th>Tanggal</th>
                                <th>Nomor SPK</th>
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
    @endcanAccess

    @canAccess('dataTableJsonWorkOrderWithoutBast','basts')
    <!-- SPK Tab -->
    <div class="tab-pane fade" id="spk_bast" role="tabpanel" aria-labelledby="spk-tab">
        <div class="card mt-3 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">List SPK</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTableJsonWorkOrderWithoutBast" style="width:100%">
                        <thead>
                            <tr>
                                <th>Nomor SPK</th>
                                <th>Total Anggaran</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        </tbody>
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
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
@if(Session::get('export'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let isDownloaded = false; // Track if file has been downloaded
        const loadingOverlay = document.createElement('div');
        
        // Add a loading overlay
        loadingOverlay.innerHTML = `
            <div id="loading-overlay" style="display: flex; justify-content: center; align-items: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 9999; color: white; font-size: 20px;">
                <div>
                    <div class="spinner-border text-light" role="status"></div>
                    <p>Exporting your file, please wait...</p>
                </div>
            </div>
        `;
        document.body.appendChild(loadingOverlay);

        const checkExportStatus = () => {
            if (isDownloaded) return; // Stop if already downloaded

            fetch('{{ route('bast.checkExportStatus') }}')
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    
                    if (data.ready) {
                        isDownloaded = true; // Mark as downloaded

                        // Create a hidden download link to trigger download
                        const downloadLink = document.createElement('a');
                        downloadLink.href = data.download_url;
                        downloadLink.style.display = 'none';
                        downloadLink.download = ''; // Optional: specify a filename
                        
                        document.body.appendChild(downloadLink);
                        
                        // Add onload callback to clear session after download
                        downloadLink.onclick = () => {
                            // Clear export session AFTER file download starts
                            fetch('{{ route('bast.clearsession') }}')
                                .then(() => {
                                    // Hide the loading overlay
                                    document.getElementById('loading-overlay').remove();
                                })
                                .catch(error => console.error('Error clearing session:', error));
                        };

                        // Trigger download
                        downloadLink.click();

                        // Remove the link element after triggering download
                        document.body.removeChild(downloadLink);
                    } else {
                        setTimeout(checkExportStatus, 3000); // Retry every 3 seconds
                    }
                })
                .catch(error => {
                    console.error('Error checking export status:', error);
                    // Hide loading overlay if error occurs
                    document.getElementById('loading-overlay').remove();
                });
        };

        checkExportStatus();
    });
</script>
@endif
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
                {data: 'number_result', name: 'basts.number_result', orderable: true},
                {data: 'date', name: 'date', orderable: true},
                {data: 'work_order.number_result', name: 'work_order'}, // Fetch work_order number_result as work_order_number
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

<script type="text/javascript">
    $(document).ready(function() {
        var table = $('#dataTableJsonWorkOrderWithoutBast').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("bast.dataTableJsonWorkOrderWithoutBast")}}',
                type: 'GET',
                dataSrc: 'data'
            },
            columns: [
                {data: 'number_result', name: 'number_result', orderable: false},
                {data: 'total', name: 'total', orderable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
        });
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
