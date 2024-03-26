@extends('adminlte::page')

@section('content_header')
    <h1>Jumlah Hari Kerja</h1>
@stop
@php

$no = ($manager->currentPage() - 1) * $manager->perPage() + 1;
$totalManager = $totalManager + 1; // Get the total number of projects

@endphp

@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Manager Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Manager Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Manager Berhasil Terhapus</div>
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
    <button class="btn btn-primary mb-3" id="btnCreateManager">Tambah Hari Kerja Baru</button>

    <!-- Search Bar -->
    @canAccess('store','managers')
    <form action="{{ route('manager.index') }}" method="get">
        <div class="d-flex flex-row-reverse">
            <div class="p-2">
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
            </div>
            <div class="p-2">
                <input type="text" name="search" class="form-control" placeholder="Search">
            </div>
        </div>
    </form>
    @endcanAccess

    <!-- Tabel Pembelian -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No. Pembelian</th>
                <th>Nama Manager</th>
                <th>Nama Proyek</th>
                <th>Total Anggaran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($manager as $a)
            <tr>
                <td>{{ $no }}</td>
                <td>{{ $a->name }}</td>
                <td> {{ $a->project  ? $a->project->title : '' }} </td>
                <td>{{ $a->total_job ? 'Rp. '.number_format($a->total_job,0,',','.') : 'Rp. 0' }}</td>
                <td>
                <form method="post" action="{{ route('manager.destroy',$a) }}">
                        @csrf
                        @method('delete')
                        @canAccess('edit','managers')
                        <a href="{{ route('manager.edit',$a->slug).'?nomor='.$no }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                        @endcanAccess
                        @canAccess('destroy','managers')
                        <button onclick="return window.confirm('{{ __('Apakah Anda Yakin ? ') }}')" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i></button>
                        @endcanAccess
                    </form>
                </td>
            </tr>
            @php $no++; @endphp
            @empty
            <tr>
                <td colspan="4">
                    <center>Data Kosong</center>
                </td>
            </tr>
            @endforelse
            <!-- ... Tambahkan baris lain sesuai kebutuhan ... -->
        </tbody>
    </table>

    <!-- Paginasi -->
    {{ $manager->withQueryString()->links('vendor.pagination.bootstrap-4') }}
</div>

<!-- Menambahkan Bootstrap JS dan Popper.js -->


</body>
</html>


@stop
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script>
    $(document).ready(function () {
        
        $("#btnCreateManager").click(function (e) 
        { 
            e.preventDefault();
            let no = "{{ $totalManager }}";
            let url = "{{ route('manager.create') }}" + "?nomor="+no;

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
