@extends('adminlte::page')

@section('content_header')
    <h1>Surat Perintah Kerja</h1>
@stop

@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Surat Perintah Kerja Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Surat Perintah Kerja Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Surat Perintah Kerja Berhasil Terhapus</div>
    @endif
    @if(Session::get('export'))
    <div class="alert alert-info mt-3">Export Surat Pertintah Kerja Sedang Diproses</div>
    @endif
    @if(Session::get('datanotfound'))
        <div class="alert alert-danger mt-3">Quote Tidak Ditemukan</div>
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
  
<div class="row mb-3 px-2 mt-2">
    <div class="col-md-3">
        <select id="filter_division" class="form-control">
            <option value="">-- Semua Divisi --</option>
            <option value="External">External</option>
            @foreach($divisions as $div)
                <option value="{{ $div->id }}">{{ $div->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <!-- SPK Tab -->
    @canAccess('dataTableJson','work_orders')
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="spk-tab" data-toggle="tab" href="#spk" role="tab" aria-controls="spk" aria-selected="true">
            <i class="fas fa-briefcase"></i> Surat Perintah Kerja
        </a>
    </li>
    @endcanAccess
    @canAccess('dataTableJsonQuoteWithoutWorkOrder','work_orders')
    <!-- Quote yang belum terbentuk SPK Tab -->
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="quote-tab" data-toggle="tab" href="#quote" role="tab" aria-controls="quote" aria-selected="false">
            <i class="fas fa-file-alt"></i> Quote yang belum terbentuk SPK
        </a>
    </li>
    @endcanAccess
</ul>


<!-- Tab Contents -->
<div class="tab-content mt-3" >
    <!-- SPK Content -->
    @canAccess('dataTableJson','work_orders')
    <div class="tab-pane fade show active" id="spk" role="tabpanel" aria-labelledby="spk-tab">
        <div class="card mt-3 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Surat Perintah Kerja</h3>
                <!-- Tombol Tambah Pembelian Baru -->
                @canAccess('create','work_orders')
                <button class="btn btn-light float-right" id="btnCreateManager">
                    <i class="fas fa-plus-circle"></i>
                    Surat Perintah Kerja
                </button>
                @endcanAccess
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <div class="d-flex">
                            <label for="">Export</label>
                        </div>
                        @canAccess('export','work_orders')
                        @canAccess('checkExportStatus','work_orders')
                        @canAccess('clearsession','work_orders')
                        <a href="javascript:void(0)" onclick="exportData('xlsx')" class="btn btn-success ml-2">
                            <i class="fa fa-file-excel"></i> Excel
                        </a>
                        <a href="javascript:void(0)" onclick="exportData('csv')" class="btn btn-primary ml-2">
                            <i class="fa fa-file-csv"></i> CSV
                        </a>
                        @endcanAccess
                        @endcanAccess
                        @endcanAccess
                    </div>
                </div>
                <div class="table-responsive">
                    <!-- Tabel Pembelian -->
                    <table class="table table-bordered" id="datatableSpk">
                        <thead>
                            <tr>
                                <th>Nomor SPK</th>
                                <th>Nomor Quote</th>
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
    @canAccess('dataTableJsonQuoteWithoutWorkOrder','work_orders')
    <!-- Quote yang belum terbentuk SPK Content -->
    <div class="tab-pane fade" id="quote" role="tabpanel" aria-labelledby="quote-tab">
        <div class="card mt-3 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">Quote</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tableQuote" style="width:100%">
                        <thead>
                            <tr>
                                <th>Nomor Quote</th>
                                <th>Total Quote</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
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

            fetch('{{ route('work-order.checkExportStatus') }}')
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
                            fetch('{{ route('work-order.clearsession') }}')
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
        var table = $('#datatableSpk').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("work-order.datatable")}}',
                type: 'GET',
                data: function(d) {
                    d.division = $('#filter_division').val();
                },
                dataSrc: 'data'
            },
            columns: [
                {data: 'number_result', name: 'number_result', orderable: false},
                {data: 'quote.number_result', name: 'quote.number_result', orderable: false},
                {data: 'total', name: 'total', orderable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
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
<script type="text/javascript">
    $(document).ready(function() {
        var table = $('#tableQuote').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: 
            {
                url: '{{ route("work-order.dataTableJsonQuoteWithoutWorkOrder")}}',
                type: 'GET',
                data: function(d) {
                    d.division = $('#filter_division').val();
                },
                dataSrc: 'data'
            },
            columns: [
                {data: 'number_result', name: 'number_result', orderable: false},
                {data: 'total', name: 'total', orderable: false},
                {data: 'budget_transition', name: 'budget_transition', orderable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            // order: [[0, 'desc']],
        });
    });

    $('#filter_division').change(function() {
        if ($.fn.DataTable.isDataTable('#datatableSpk')) {
            $('#datatableSpk').DataTable().ajax.reload();
        }
        if ($.fn.DataTable.isDataTable('#tableQuote')) {
            $('#tableQuote').DataTable().ajax.reload();
        }
    });

    $('#filter_division').select2({
        width: '100%',
    });

    function exportData(format) {
        let division = $('#filter_division').val();
        let url = "{{ route('work-order.export', ['format' => ':format']) }}".replace(':format', format);
        if (division) {
            url += "?division=" + division;
        }
        window.location.href = url;
    }

</script>
@stop
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css"> 
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

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
        .select2-selection__rendered {
            line-height: 31px !important;
        }
        .select2-container .select2-selection--single {
            height: 35px !important;
        }
        .select2-selection__arrow {
            height: 34px !important;
        }

</style>
@stop
