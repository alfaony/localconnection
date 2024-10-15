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
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        var table = $('#datatableSpk').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("work-order.datatable")}}',
                type: 'GET',
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
