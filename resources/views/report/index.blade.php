@extends('adminlte::page')

@section('content_header')
    <h1>Laporan</h1>
@stop

@php
$no = ($project->currentPage() - 1) * $project->perPage() + 1;
@endphp

@section('content')
<div class="container">
    <form action="{{ route('report.index') }}" method="get">
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">Periode</span>
                </div>
                <input type="date" class="form-control" name="start_date">
                <div class="input-group-append">
                    <span class="input-group-text">hingga</span>
                </div>
                <input type="date" class="form-control" name="end_date" >
            </div>
        </div>
        <div class="col-md-4 ml-auto">
            <div class="input-group">
                <input type="text" name="project" class="form-control" placeholder="Search">
                <div class="input-group-append">
                    <button class="btn btn-secondary" type="submit">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    </form>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>No. Proyek</th>
            <th>Nama Proyek</th>
            <th>Anggaran</th>
            <th>Pengeluaran</th>
            <th>Keuntungan</th>
        </tr>
        </thead>
        <tbody>
        <!-- Contoh baris tabel -->
        @forelse($project as $a)
        <tr>
            <td>{{ $no++ }}</td>
            <td>{{ $a->title }}</td>
            {{-- 
            <td>
                {{ dd() }}
            </td>
            --}}
            <td>
                {{ 'Rp. '.number_format($a->budget,0,',','.') }}
            </td>
            <td>
                {{ 'Rp. '.number_format($a->purchase,0,',','.') }}
            </td>
            <td>
                {{ 'Rp. '.number_format($a->profit,0,',','.') }}
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5">
                <center>Data Kosong</center>
            </td> 
        </tr>
        @endforelse
        <!-- Ulangi untuk baris lainnya -->
        </tbody>
    </table>

    {{ $project->withQueryString()->links('vendor.pagination.bootstrap-4') }}
</div>
@stop
@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<script>
$(document).ready(function() {
    $('input[name="start_date"]').on('change', function() {
        var startDateValue = $(this).val(); // Ambil nilai dari startDate
        $('input[name="end_date"]').val(startDateValue); // Set nilai startDate ke endDate
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