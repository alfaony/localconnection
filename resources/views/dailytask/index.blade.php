@extends('adminlte::page')

@section('content_header')
    <h1>Daftar Tugas Harian</h1>
@stop

@section('content')
<div class="card p-3">
    <div class="card-body col-md-12">
        @if(Session::get('store'))
        <div class="alert alert-success mt-3">Tugas Berhasil Ditambahkan</div>
        @endif
        @if(Session::get('import'))
        <div class="alert alert-success mt-3">Import Tugas Berhasil</div>
        @endif
        @if(Session::get('update'))
        <div class="alert alert-success mt-3">Tugas Berhasil Diperbarui</div>
        @endif
        @if(Session::get('report'))
        <div class="alert alert-success mt-3">Tugas Berhasil Ditambahkan</div>
        @endif
        @if(Session::get('delete'))
        <div class="alert alert-success mt-3">Tugas Berhasil Terhapus</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <div class="col-md-12">
        @canAccess('create','dailytasks')
        <a href="{{ route('dailytask.create') }}" class="btn btn-primary mb-3 col-md-2"><i class="fa fa-plus"></i><span> Tugas</span></a>
        @endcanAccess
        @canAccess('template','dailytasks')
        <a href="{{ route('dailytask.template') }}" class="btn btn-info mb-3 col-md-2"><i class="fa fa-plus"></i><span> Import Tugas</span></a>
        @endcanAccess
    </div>
    @canAccess('index','dailytasks')
    <form method="GET" action="{{ route('dailytask.index') }}" class="mb-3">
        <div class="row align-items-end gy-2">
            <div class="col-12 col-md-2">
                <div class="form-group">
                    <label for="task">Task</label>
                    <select class="form-control" id="task" name="task">
                        <option value="all">All</option>
                        @foreach ($taskTimeFrame as $status => $value)
                            <option value="{{ $status }}" {{ request('task') == $status ? 'selected' : '' }}>{{ ucfirst($value) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="user">User</label>
                    <select class="form-control UserSelect2" id="user" name="user">
                        <option value="" selected disabled>-- Select User--</option>
                        <option value="all">All User</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->name }}" {{ request('user') == $user->name ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="division">Division</label>
                    <select class="form-control select2" id="user" name="division">
                        <option value="">-- Divisi --</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->name }}" {{ request('division') == $division->name ? 'selected' : '' }}>{{ $division->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control select2" id="status" name="status">
                        <option value="">Select Status</option>
                        @foreach ($taskStatuss as $status)
                            <option value="{{ $status->name }}" {{ request('status') == $status->name ? 'selected' : '' }}>{{ ucfirst($status->name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label for="date_range">Date Range</label>
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Tanggal" id="date_range" value="{{ request('start_date') && request('end_date') ? request('start_date').' - '.request('end_date') : '' }}">
                        <input type="hidden" id="start_date" name="start_date" value="{{ request('start_date') }}">
                        <input type="hidden" id="end_date" name="end_date" value="{{ request('end_date') }}">
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-2">
                <div class="form-group">
                    <label for="search">Search</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Search" value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="form-group">
                    <label for="user">Order By</label>
                    @php
                        $order = request('sort', 'desc');
                    @endphp
                        <select name="sort" class="form-control">
                            <option value="asc" {{ $order == 'asc' ? 'selected' : '' }} >A - Z Created By</option>
                            <option value="desc" {{ $order == 'desc' ? 'selected' : '' }}>Z - A Created By</option>
                        </select>
                </div>
            </div>

            <div class="col-12 col-md-auto">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Search</button>
                        <button type="button" onclick="window.location.href='{{ route('dailytask.index') }}?task=all&user=all'" class="btn btn-secondary"><i class="fa fa-times"></i> Show All</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endcanAccess
    

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="col-auto">Tanggal</th>
                    <th class="col-auto">Status</th>
                    <th class="col-auto">Tugas</th>
                    <th class="col-auto">Proyek</th>
                    <th class="col-auto">Poin</th>
                    <th class="col-auto">Dibuat</th>
                    <th class="col-auto">Ditugaskan</th>
                    <th class="col-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dailyTasks as $dailytask)
                    @php
                        $startDate = \Carbon\Carbon::parse($dailytask->start_date);
                        $endDate = \Carbon\Carbon::parse($dailytask->end_date);
                        $isOverdue = $dailytask->isOverdue();
                    @endphp
                    <tr>
                        <td>
                            <span class="{{ $isOverdue ? 'text-danger' : '' }}">
                                {{ $dailytask->dateShow }}
                            </span>
                        </td>
                        <td>
                        @switch($dailytask->taskStatus->name)
                            @case('todo')
                                <i class="fa fa-list-alt"></i> Todo
                                @break
                            @case('doing')
                                <i class="fa fa-hourglass-start"></i> Doing
                                @break
                            @case('in review')
                                <i class="fa fa-eye" style="color: green;"></i> In Review
                                @break
                            @case('not complete')
                                <i class="fa fa-times-circle" style="color: red;"></i> Not Complete
                                @break
                            @case('complete')
                                <i class="fa fa-check" style="color: green;"></i> Complete
                                @break
                            @default
                                {{ $dailytask->taskStatus->name }}
                        @endswitch
                        </td>
                        <td class="name-cell">
                            <p>{!! $dailytask->head ? $dailytask->nameShow.'  <i class="fa fa-arrow-left"></i>  '. Str::limit($dailytask->head->name,50) : $dailytask->nameShow !!}</p>
                        </td>
                        <td class="name-cell">
                            {{ $dailytask->dataProject ? $dailytask->dataProject->title : '' }}
                        </td>
                        <td>{{ $dailytask->point == 0 ? "-" : $dailytask->point }}</td>
                        <td class="name-cell">{{ $dailytask->user->name ?? '' }}</td>
                        <td class="name-cell">{{ $dailytask->assign->name ?? '' }}</td>
                        <td>
                            @if(!$dailytask->approved)
                            <form action="{{ route('dailytask.destroy', $dailytask->slug) }}" method="POST" style="display:inline-block;">
                                @canAccess('show','dailytasks')
                                <a href="{{ route('dailytask.show', $dailytask->slug) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
                                @endcanAccess
                                @if(($dailytask->user_id == Auth::user()->id) || (Auth::user()->role->name == \App\Schemas\RoleSchema::MANAGER && $dailytask->taskStatus->name == \App\Schemas\ParamSchema::COMPLATE))
                                @canAccess('edit','dailytasks')
                                <a href="{{ route('dailytask.edit', $dailytask->slug) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                                @endcanAccess
                                @csrf
                                @method('DELETE')
                                @canAccess('destroy','dailytasks')
                                <button type="button" class="btn btn-danger delete-button btn-sm"><i class="fa fa-trash"></i></button>
                                @endcanAccess
                                @endif
                            </form>
                            @else
                            @canAccess('show','dailytasks')
                            <a href="{{ route('dailytask.show', $dailytask->slug) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i></a>
                            @endcanAccess
                            @canAccess('edit','dailytasks')
                            @canAccess('approvement','dailytasks')
                            <a href="{{ route('dailytask.edit', $dailytask->slug) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit"></i></a>
                            @endcanAccess
                            @endcanAccess

                            @if(Auth::user()->role->name == \App\Schemas\RoleSchema::ROOT || Auth::user()->role->name == \App\Schemas\RoleSchema::ADMIN || Auth::user()->role->name == \App\Schemas\RoleSchema::MANAGER)
                            <form action="{{ route('dailytask.destroy', $dailytask->slug) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                @canAccess('destroy','dailytasks')
                                <button type="button" class="btn btn-danger delete-button btn-sm"><i class="fa fa-trash"></i></button>
                                @endcanAccess
                            </form>
                            @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{ $dailyTasks->withQueryString()->links('vendor.pagination.bootstrap-4') }}
    </div>

</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        // Initialize Select2
        $('.select2').select2();
        $('.UserSelect2').select2();

        // Initialize Daterangepicker
        $('#date_range').daterangepicker({
            autoUpdateInput: false, // Prevents the input from being automatically populated
            locale: {
                format: 'DD-MM-YYYY',
                cancelLabel: 'Clear' // Adds a clear button to the picker
            }
        });

        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
        });

        $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Capture the date range selection
        $('#date_range').on('apply.daterangepicker', function(ev, picker) {
            $('#start_date').val(picker.startDate.format('DD-MM-YYYY'));
            $('#end_date').val(picker.endDate.format('DD-MM-YYYY'));
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.delete-button').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                const form = this.closest('form');

                Swal.fire({
                    title: 'Apakah Anda Yakin Hapus Data?',
                    text: "Data ini akan dihapus beserta child tasknya!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }
    .container {
        background-color: #fff;
        border-radius: 5px;
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
</style>
<!-- Improvement CSS -->
<style>
    .table-responsive td, .table-responsive th {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table-responsive p {
        margin-bottom: 0;
    }
</style>
@endsection
