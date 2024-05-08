@extends('adminlte::page')

@section('content_header')
    <h2>Daftar Kehadiran</h2>
@stop

@section('content')
<div class="container mt-3">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('attendance.index') }}" method="get">
                <div class="row mb-4 justify-content-end">
                    <div class="col-md-8">
                        <label for="date-range" class="form-label">Periode</label>
                        <div class="input-group">
                            <input type="date" class="form-control" name="start_date" placeholder="Mulai Tanggal" value="{{ request('start_date') }}">
                            <span class="input-group-text">hingga</span>
                            <input type="date" class="form-control" name="end_date" placeholder="Sampai Tanggal" value="{{ request('end_date') }}">
                            @if(Auth::user()->role->name != \App\Schemas\RoleSchema::OB)
                                <select class="form-control ml-2" id="user" name="user">
                                    <option value="">Select User</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->name }}" {{ request('user') == $user->name ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                            <button class="btn btn-primary ml-2" type="submit">
                                <i class="fa fa-search"></i> Cari
                            </button>
                        </div>
                    </div>
                    
                </div>
            </form>
            <div class="table-responsive-md">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendances as $attendance)
                        <tr>
                            <td> {{ $attendance->user->name }} </td>
                            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d-m-Y') }}</td>
                            <td>{{ $attendance->clock_in }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $attendances->withQueryString()->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
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
            border-radius: 5px;
        }
</style>
@stop