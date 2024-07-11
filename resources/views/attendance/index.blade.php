@extends('adminlte::page')

@section('content_header')
    <h2>Daftar Kehadiran</h2>
@stop

@section('content')
@if (session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{ session('success') }}
    </div>
@endif

<div class="card mt-3">
    <div class="card-body row">
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
            <div class="mb-3">
                @if($shift)
                    @if ($needsClockIn)
                        @canAccess('create','attendances')
                        <a href="{{ route('attendance.create') }}" class="btn btn-success">
                            <i class="fa fa-sign-in-alt"></i> Absen Masuk
                        </a>
                        @endcanAccess
                    @elseif ($needsClockOut)
                        @canAccess('create','attendances')
                        <a href="{{ route('attendance.edit', $attendanceId) }}" class="btn btn-success">
                            <i class="fa fa-sign-out-alt"></i> Absen Keluar
                        </a>
                        @endcanAccess
                    @else
                        <button class="btn btn-success" disabled>Kehadiran Hari Ini Sudah Lengkap</button>
                    @endif
                @else
                <button class="btn btn-danger">Tidak Memiliki Shift Pada Hari ini</button>
                @endif
            </div>
            <div class="table-responsive-md">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Karyawan</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Poin</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendances as $attendance)
                        <tr>
                            <td> {{ $attendance->user->name }} </td>
                            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d-m-Y') }}</td>
                            <td>{{ $attendance->clock_in }}</td>
                            <td>{{ $attendance->clock_out }}</td>
                            <td>{{ $attendance->point }}</td>
                            <td>
                                <button class="btn btn-info btn-sm show-attendance" data-attendance="{{ json_encode($attendance) }}" data-shift="{{ json_encode($attendance->schedule ? $attendance->schedule->shiftingOb : '') }}" >
                                    <i class="fa fa-eye"></i> Show
                                </button>
                            </td>
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

<!-- Show Modal -->
<div class="modal fade" id="showModal" tabindex="-1" role="dialog" aria-labelledby="showModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showModalLabel">Detail Kehadiran</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-2 card">
                    <div class="card-body col-md-12">
                        <p><strong>Karyawan:</strong> <span id="showUser"></span></p>
                        <p><strong>Tanggal:</strong> <span id="showDate"></span></p>
                        <p><strong>Nama Shift:</strong> <span id="showShiftName"></span></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <p><strong>Jam Masuk Shift:</strong> <span id="showShiftClockIn"></span></p>
                        <p><strong>Jam Masuk Staff:</strong> <span id="showClockInRealita"></span></p>
                        <p><strong>Foto Masuk:</strong> <span id="showFotoMasuk"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Jam Keluar Shift:</strong> <span id="showShiftClockOut"></span></p>
                        <p><strong>Jam Keluar Staff:</strong> <span id="showClockOutRealita"></span></p>
                        <p><strong>Foto Keluar:</strong> <span id="showFotoKeluar"></span></p>
                        <p><strong>Noted:</strong> <span id="showNoted"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
<script>
$(document).ready(function() {
    $('.show-attendance').click(function() {
        var attendance = $(this).data('attendance');
        var shift = $(this).data('shift');

        // Construct the URL for the pic_in image
        let urlpicin = "{{ Storage::url('attendance/') }}" + attendance.pic_in;
        // Construct the URL for the pic_out image
        let urlpicout = "{{ Storage::url('attendance/') }}" + attendance.pic_out;

        $('#showUser').text(attendance.user.name);
        $('#showDate').text(new Date(attendance.date).toLocaleDateString('id-ID'));
        $('#showShiftName').text(shift.name ? shift.name : '-');
        $('#showShiftClockIn').text(shift.clock_in ? shift.clock_in : '-');
        $('#showShiftClockOut').text(shift.clock_out ? shift.clock_out : '-');
        $('#showOntimeIn').text(attendance.ontime_in ? 'Tepat Waktu' : 'Terlambat');
        $('#showClockInRealita').text(attendance.clock_in);
        $('#showFotoMasuk').html('<img src="'+ urlpicin +'" class="img-fluid" alt="Foto Masuk">');
        $('#showClockOutRealita').text(attendance.clock_out || '-');
        $('#showFotoKeluar').html(attendance.pic_out ? '<img src="'+ urlpicout +'" class="img-fluid" alt="Foto Keluar">' : '-');
        $('#showOntimeOut').text(attendance.ontime_out ? 'Yes' : 'No');
        $('#showNoted').text(attendance.note || '-');

        var modalToggle = document.getElementById('showModal') // relatedTarget
        var myModal = new bootstrap.Modal(modalToggle);
        myModal.show(modalToggle);
    });

    $('input[name="start_date"]').on('change', function() {
        var startDateValue = $(this).val();
        $('input[name="end_date"]').val(startDateValue);
    });
});

</script>
@stop

@section('css')
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
}
.container {
    background-color: #fff;
    border-radius: 5px;
}
.table-responsive-md {
    overflow-x: auto;
}
.modal-body img {
    max-width: 100%;
    height: auto;
    border-radius: 5px;
}
</style>
@stop
