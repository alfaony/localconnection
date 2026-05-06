@extends('adminlte::page')

@section('title', 'Penjadwalan')

@section('content_header')
    <h1>Penjadwalan</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    
    @canAccess('create','schedule_obs')
    <div class="mb-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#createModal"><i class="fa fa-plus"></i> Tambah Penjadwalan</button>
    </div>
    @endcanAccess

    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>

    @canAccess('create','schedule_obs')
    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Penjadwalan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('schedule-ob.store') }}" method="POST" id="createScheduleForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="date">Tanggal</label>
                            <input type="text" class="form-control" placeholder="Tanggal" id="date_range" value="" required>
                            <input type="hidden" id="start_date" name="start_date" value="{{ request('start_date') }}">
                            <input type="hidden" id="end_date" name="end_date" value="{{ request('end_date') }}">
                        </div>
                        <div class="form-group">
                            <label for="shifting_ob_id">Shift</label>
                            <select class="form-control select2" name="shifting_ob_id" required>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->clock_in }} - {{ $shift->clock_out ?? "-" }}) </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="user_id">User</label>
                            <select class="form-control select2" name="user_id" required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcanAccess

    <!-- Edit Modal -->
    @canAccess('update','schedule_obs')
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Penjadwalan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                         <div class="form-group">
                            <label for="date">Tanggal</label>
                            <input type="date" class="form-control" name="date" id="edit_date" required>
                        </div>
                        <div class="form-group">
                            <label for="shifting_ob_id">Shift</label>
                            <select class="form-control select2" name="shifting_ob_id" id="edit_shifting_ob_id" required>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}">{{ $shift->name }} - {{ $shift->clock_in }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="user_id">User</label>
                            <select class="form-control select2" name="user_id" id="edit_user_id" required>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcanAccess

    <!-- Delete Modal -->
    @canAccess('destroy','schedule_obs')
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Hapus Penjadwalan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus penjadwalan ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcanAccess

@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.7.2/main.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function () {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih'
        });

        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: {
                url: '{{ route("schedule-ob.calendar") }}',
                method: 'GET',
                failure: function() {
                    alert('Gagal memuat data jadwal.');
                }
            },
            editable: true,
            eventClick: function(info) {
                if (!info.jsEvent.target.closest('.fc-event')) {
                    return;
                }
                var eventObj = info.event;
                var modalToggle = document.getElementById('editModal') // relatedTarget
                var myModal = new bootstrap.Modal(modalToggle);
                myModal.show(modalToggle);
                
                $('#editForm').attr('action', '/schedule-ob/' + eventObj.id);
                $('#edit_user_id').val(eventObj.extendedProps.user_id).trigger('change');
                $('#edit_shifting_ob_id').val(eventObj.extendedProps.shifting_ob_id).trigger('change');
                $('#edit_date').val(moment(eventObj.start).format('YYYY-MM-DD'));
            },
            eventContent: function(info) {
                var deleteButton = document.createElement('button');
                deleteButton.classList.add('btn', 'btn-danger', 'btn-sm', 'ml-2');
                deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
                deleteButton.onclick = function(e) 
                {
                    e.stopPropagation(); // Prevent the edit modal from showing
                    var modalToggle = document.getElementById('deleteModal') // relatedTarget
                    var myModal = new bootstrap.Modal(modalToggle);
                    myModal.show(modalToggle);
                    $('#deleteForm').attr('action', '/schedule-ob/' + info.event.id);
                };

                var content = document.createElement('div');
                content.classList.add('fc-event-content');
                var title = document.createElement('div');
                title.classList.add('fc-title');
                title.innerText = info.event.title;

                content.appendChild(title);
                let deleteAccess = "{{ $deleteAccess }}";
                if(deleteAccess)
                {
                    content.appendChild(deleteButton);
                }

                return { domNodes: [content] };
            }
        });

        calendar.render();

        $('#date').attr('min', new Date().toISOString().split("T")[0]);
    });
</script>
<script>
    $(document).ready(function () {
        // Initialize Daterangepicker
        var currentDate = moment().format('DD-MM-YYYY');
        
        $('#date_range').daterangepicker({
            autoUpdateInput: false, // Prevents the input from being automatically populated
            locale: {
                format: 'DD-MM-YYYY',
                cancelLabel: 'Clear' // Adds a clear button to the picker
            },
            minDate: currentDate // Set the minimum date to the current date
        });

        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
        });

        // Capture the date range selection
        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $('#start_date').val(picker.startDate.format('DD-MM-YYYY'));
            $('#end_date').val(picker.endDate.format('DD-MM-YYYY'));
        });
    });
</script>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.7.2/main.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
    .modal-title {
        font-weight: bold;
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
    .ql-container {
        min-height: 150px;
        height: auto;
    }
</style>
@endsection