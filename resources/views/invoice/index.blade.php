@extends('adminlte::page')

@section('content_header')
    <h1>Invoice</h1>
@stop



@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Berhasil Menambahkan Invoice</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Invoice Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Berhasil Menghapus Invoice</div>
    @endif
    @if(Session::get('xero'))
    <div class="alert alert-success mt-3">Berhasil Terhubung Xero</div>
    @endif
    @if(Session::get('AUTHORISED'))
    <div class="alert alert-success mt-3">Invoice Dalam Proses Pembayaran</div>
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
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
</div>
<div class="card">    
    <div class="card-body">    
        <!-- Tombol Tambah Pembelian Baru -->
        @canAccess('create','invoices')
        <button class="btn btn-primary mb-3" id="btnCreateSuplier">Tambah Invoice Baru</button>
        @endcanAccess
        
        <!-- Search Bar -->
        <form action="{{ route('invoice.index') }}" method="get">
            <div class="d-flex flex-row-reverse">
                <div class="p-2">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                    <a href="{{ route('invoice.index') }}" class="btn btn-secondary">Reset</a>
                </div>
                <div class="p-2">
                    <input type="text" name="search" class="form-control" placeholder="Search" value="{{ request('search') }}">
                </div>
                <div class="p-2">
                @php
                    $order = request('order', 'desc');
                @endphp
                    <select name="order" class="form-control">
                        <option value="asc" {{ $order == 'asc' ? 'selected' : '' }} >A - Z Created By</option>
                        <option value="desc" {{ $order == 'desc' ? 'selected' : '' }}>Z - A Created By</option>
                    </select>
                </div>
                <div class="p-2">
                    <select name="status" class="form-control">
                        <option value="">Semua Status</option>
                        @foreach($searchByStatus as $key => $value)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="p-2">
                    <input type="text" class="form-control" placeholder="Tanggal" id="date_range" value="{{ request('start_date') && request('end_date') ? request('start_date').' - '.request('end_date') : '' }}">
                    <input type="hidden" id="start_date" name="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" id="end_date" name="end_date" value="{{ request('end_date') }}">
                </div>
            </div>
        </form>
        
        <!-- Tabel Pembelian -->
        <table class="table table-bordered" id="">
            <thead>
                <tr>
                    <th>Nomor Invoice</th>
                    <th>BAST</th>
                    <th>Status</th>
                    <th>Total Invoice</th>
                    <th>Status Connect</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice as $a)
                <tr>
                    <td>{{ $a->number_result ?? '' }}</td>
                    <td>
                        {{ $a->bast->number_result }}
                    </td>
                    <td>
                    @switch($a->status)
                        @case('DRAFT')
                            <span class="badge badge-secondary">DRAFT</span>
                            @break

                        @case('SUBMITTED')
                            <span class="badge badge-warning">SUBMITTED</span>
                            @break

                        @case('AUTHORISED')
                            <span class="badge badge-success">WAITING PAYMENT</span>
                            @break

                        @case('PAID')
                            <span class="badge badge-success">PAID</span>
                            @break

                        @default
                            <span class="badge badge-danger">{{ $a->status }}</span>
                    @endswitch
                    </td>
                    <td>{{ 'Rp. '.number_format($a->total,0,',','.')  ?? 'Rp. 0' }}</td>
                    <td>
                        @if($a->connecting)
                            <span class="badge bg-success">Connected</span>
                        @else
                            <span class="badge bg-danger">Not Connected</span>
                        @endif
                    </td>
                    <td>
                        {{ $a->start_date ? \Carbon\Carbon::parse($a->start_date)->format('d-m-Y') : '' }}
                    </td>
                    <td>
                        {{ $a->end_date ? \Carbon\Carbon::parse($a->end_date)->format('d-m-Y') : '' }}
                    </td>
                    <td>
                        <form method="post" action="{{ route('invoice.destroy', $a->slug) }}">
                            @csrf
                            @method('delete')

                            <!-- Kebab menu (three dots) as the dropdown button -->
                            <div class="btn-group">
                                <button type="button" class="btn btn-secondary btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <!-- Dropdown items -->
                                     @canAccess('history', 'invoices')
                                    <a href="{{ route('invoices.history', $a->slug) }}" class="dropdown-item">
                                        <i class="fa fa-history"></i> View History
                                    </a>
                                    @endcanAccess
                                    @canAccess('downloadPdf', 'invoices')
                                    <a href="{{ route('invoice.download.pdf', ['slug' => $a->slug]) }}" class="dropdown-item">
                                        <i class="fa fa-file-pdf"></i> Download PDF
                                    </a>
                                    @endcanAccess
                                    @canAccess('show', 'invoices')
                                    <a href="{{ route('invoice.show', $a->slug) }}" class="dropdown-item">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                    @endcanAccess
                                    @if(($a->status != 'PAID') && ($a->status != 'DELETED') && ($a->status != 'VOID') && ($a->status != 'AUTHORISED'))
                                    @canAccess('edit', 'invoices')
                                    <a href="{{ route('invoice.edit', $a->slug) }}" class="dropdown-item">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    @endcanAccess
                                    @canAccess('destroy', 'invoices')
                                    <button type="submit" onclick="return window.confirm('{{ __('Apakah Anda Yakin?') }}')" class="dropdown-item text-danger">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                    @endcanAccess
                                    @endif
                                </div>
                            </div>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">
                        <center>Data Kosong</center>
                    </td>
                </tr>

                @endforelse
                <!-- ... Tambahkan baris lain sesuai kebutuhan ... -->
            </tbody>
        </table>
        {{ $invoice->withQueryString()->links('vendor.pagination.bootstrap-4') }}
    </div>
</div>

@stop
@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        var table = $('#tableQuote').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("invoice.datatable")}}',
                type: 'GET',
                dataSrc: 'data'
            },
            columns: [
                {data: 'number_result', name: 'number_result', orderable: false},
                {data: 'status', name: 'status', orderable: false},
                {data: 'total', name: 'total', orderable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            // order: [[0, 'desc']],
        });
    });
</script>
<script>
    $(document).ready(function () {
        
        $("#btnCreateSuplier").click(function (e) 
        { 
            e.preventDefault();
            let url = "{{ route('invoice.create') }}";

            window.location.href = url;
        });
    });
</script>
<script>
    // Initialize Daterangepicker
    $('#date_range').daterangepicker({
        autoUpdateInput: false, // Prevents the input from being automatically populated
        locale: {
            format: 'DD-MM-YYYY',
            cancelLabel: 'Clear', // Adds a clear button to the picker
        },
    });

    $('#date_range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
    });

    // Clear the date range when the user clicks on 'Clear'
    $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        $('#start_date').val(null); // Set start_date to null
        $('#end_date').val(null); // Set end_date to null
    });


    // Capture the date range selection
    $('#date_range').on('apply.daterangepicker', function(ev, picker) {
        $('#start_date').val(picker.startDate.format('DD-MM-YYYY'));
        $('#end_date').val(picker.endDate.format('DD-MM-YYYY'));
    });
</script>
@stop
@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css"> 
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
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
        
        /* table {
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
        } */
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
