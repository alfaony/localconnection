@extends('adminlte::page')

@section('content_header')
    <h1>Poin Laporan</h1>
@stop

@section('content')
<div class="container">
<form action="{{ route('report-point.index') }}" method="get">
    <div class="row mb-4 justify-content-end">
        <div class="col-md-8">
            <label for="date-range" class="form-label">Periode</label>
            <div class="input-group">
                <input type="date" class="form-control" name="start_date" placeholder="Mulai Tanggal" value="{{ request('start_date') }}">
                <span class="input-group-text">hingga</span>
                <input type="date" class="form-control" name="end_date" placeholder="Sampai Tanggal" value="{{ request('end_date') }}">
                <button class="btn btn-primary ml-2" type="submit">
                    <i class="fa fa-search"></i> Cari
                </button>
            </div>
        </div>
    </div>
</form>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Total Tugas</th>
                <th>Poin Tugas Selesai</th>
                <th>Poin Tugas Tidak Selesai</th>
                <th>Poin Keterlambatan</th>
                <th>Poin Bonus Kehadiran</th>
                <th>Total Poin</th>
                <th>Kalkulasi</th>
            </tr>
        </thead>
        <tbody> 
            @foreach ($reports as $report)
                <tr>
                    <td>{{ $report['Name'] }}</td>
                    <td>{{ $report['total_task'] }}</td>
                    <td>{{ $report['Complete'] }}</td>
                    <td>{{ $report['Not Complete'] }}</td>
                    <td>{{ $report['Attend Point'] }}</td>
                    <td>{{ $report['Attend Bonus Point'] }}</td>
                    <td>{{ $report['Total'] }}</td>
                    <td>{{ $report['convertion_point'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $users->withQueryString()->links('vendor.pagination.bootstrap-4') }}
    </div>
</div>
@stop
@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
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