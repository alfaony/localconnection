@extends('adminlte::page')

@section('content_header')
    <h1>Quote</h1>
@stop



@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Berhasil Menambahkan Quote</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Quote Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Berhasil Menghapus Quote</div>
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
    @canAccess('create','quotes')
    <button class="btn btn-primary mb-3" id="btnCreateSuplier">Tambah Quote Baru</button>
    @endcanAccess
    
    <!-- Search Bar -->
    <!-- <form action="{{ route('quote.index') }}" method="get">
        <div class="d-flex flex-row-reverse">
            <div class="p-2">
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
            </div>
            <div class="p-2">
                <input type="text" name="user" class="form-control" placeholder="Search">
            </div>
        </div>
    </form> -->
    
    <!-- Tabel Pembelian -->
    <table class="table table-bordered" id="tableQuote">
        <thead>
            <tr>
                <th>Nomor Quote</th>
                <th>Total Quote</th>
                <th>Aksi</th>
            </tr>
        </thead>
        {{-- 
        <tbody>
            @forelse($quote as $a)
            <tr>
                <td>{{ $no }}</td>
                <td>{{ $a->number_result ?? '' }}</td>
                <td>{{ 'Rp. '.number_format($a->total,0,',','.')  ?? 'Rp. 0' }}</td>
                <td>
                    <form method="post" action="{{ route('quote.destroy',$a) }}">
                        @csrf
                        @method('delete')
                        <a href="{{ route('quote.download.pdf', ['slug' => $a->slug, 'nomor' => $no]) }}" class="btn btn-primary btn-sm"><i class="fa fa-file-pdf"></i></a>
                        <a href="{{ route('quote.edit',$a->slug).'?nomor='.$no }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                        <button onclick="return window.confirm('{{ __('Apakah Anda Yakin ? ') }}')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @php $no++; @endphp
            @empty
            <tr>
                <td colspan="5">
                    <center>Data Kosong</center>
                </td>
            </tr>

            @endforelse
            <!-- ... Tambahkan baris lain sesuai kebutuhan ... -->
        </tbody>
        {{ $quote->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        --}}
        <tbody>

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
        var table = $('#tableQuote').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("quote.datatable")}}',
                type: 'GET',
                dataSrc: 'data'
            },
            columns: [
                {data: 'number_result', name: 'number_result', orderable: false},
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
            let url = "{{ route('quote.create') }}";

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
