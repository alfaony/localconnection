@extends('adminlte::page')

@section('content_header')
    <h1>Pembelian</h1>
@stop

@php

$no = ($quote->currentPage() - 1) * $quote->perPage() + 1;
$totalQuote = $totalQuote + 1; // Get the total number of projects

@endphp

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
    <button class="btn btn-primary mb-3" id="btnCreateSuplier">Tambah Pembelian Baru</button>

    <!-- Search Bar -->
    <form action="{{ route('quote.index') }}" method="get">
        <div class="d-flex flex-row-reverse">
            <div class="p-2">
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
            </div>
            <div class="p-2">
                <input type="text" name="user" class="form-control" placeholder="Search">
            </div>
        </div>
    </form>
    
    <!-- Tabel Pembelian -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nomor</th>
                <th>Tanggal</th>
                <th>Nama Sales</th>
                <th>Total Quote</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quote as $a)
            <tr>
                <td>{{ $a->quote_number_result ?? '' }}</td>
                <td>{{ $a->date }}</td>
                <td>{{ $a->userCreate ? $a->userCreate->name : '' }}</td>
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
    </table>


    

</div>

<!-- Menambahkan Bootstrap JS dan Popper.js -->


</body>
</html>


@stop
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    $(document).ready(function () {
        
        $("#btnCreateSuplier").click(function (e) 
        { 
            e.preventDefault();
            let no = "{{ $no }}";
            let url = "{{ route('quote.create') }}" + "?nomor="+no;

            window.location.href = url;
        });
    });
</script>
@stop
@section('css')
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
