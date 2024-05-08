@extends('adminlte::page')

@section('content_header')
    <h1>Daftar Penugasan</h1>
@stop
@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Penugasan Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Penugasan Berhasil Diperbarui</div>
    @endif
    @if(Session::get('report'))
    <div class="alert alert-success mt-3">Report Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Penugasan Berhasil Terhapus</div>
    @endif


</div>
<div class="container p-3">
    @canAccess('store','task_assigns')
    <a href="{{ route('task-assign.create') }}" class="btn btn-primary mb-3"><i class="fa fa-plus"></i><span> Penugasan</span></a>
    @endcanAccess
    @canAccess('index','task_assigns')
    <form method="GET" action="{{ route('task-assign.index') }}" class="mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-2">
                <select class="form-control" id="task" name="task">
                    @if(!request('task'))
                    <option value="today" selected>Pekerjaan Hari Ini</option>
                    <option value="all" {{ request('task') == "all" ? 'selected' : '' }}>Semua Pekerjaan</option>
                    @else
                    <option value="all" {{ request('task') == "all" ? 'selected' : '' }}>Semua Pekerjaan</option>
                    <option value="today" {{ request('task') == "today" ? 'selected' : '' }}>Pekerjaan Hari Ini</option>
                    @endif
                </select>
            </div>
            @if(Auth::user()->role->name != \App\Schemas\RoleSchema::OB)
            <div class="col-12 col-md-3">
                <select class="form-control" id="user" name="user">
                    <option value="">Select User</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->name }}" {{ request('user') == $user->name ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-12 col-md-3">
                <select class="form-control" id="status" name="status">
                    <option value="">Select Status</option>
                    @foreach ($taskStatuss as $status)
                        <option value="{{ $status->name }}" {{ request('status') == $status->name ? 'selected' : '' }}>{{ ucfirst($status->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <input type="date" class="form-control" id="date" name="date" value="{{ request('date') }}">
            </div>
            <div class="col-12 col-md-auto mt-2">
                <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Search</button>
                <button type="button" onclick="window.location.href='{{ route('task-assign.index') }}?task=all'" class="btn btn-secondary"><i class="fa fa-times"></i> Show All</button>
            </div>
        </div>
    </form>
    @endcanAccess
    <div class="table-responsive-md">
        <table class="table table-striped">
            <thead>
                <tr>
                    @if(request('task') == "all" )
                    <th>Tanggal</th>
                    @endif
                    <th>Status</th>
                    <th>Pekerjaan</th>
                    @if((Auth::user()->role->name != \App\Schemas\RoleSchema::OB))
                    <th>Poin</th>
                    @endif
                    @if((Auth::user()->role->name != \App\Schemas\RoleSchema::OB))
                    <th>Penugasan</th>
                    @endif
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($assigns as $taskAssign)
                <tr>
                    @if(request('task') == "all" )
                    <td>{{ \Carbon\Carbon::parse($taskAssign->date)->format('d-m-Y') }}</td>
                    @endif
                    <td>
                        @switch($taskAssign->taskStatus->name)
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
                                {{ $taskAssign->taskStatus->name }}
                        @endswitch
                    </td>
                    <td> {{ $taskAssign->task->name }} </td>
                    @if((Auth::user()->role->name != \App\Schemas\RoleSchema::OB))
                    <td>{{ $taskAssign->point  }}</td>
                    @endif
                    @if((Auth::user()->role->name != \App\Schemas\RoleSchema::OB))
                    <td>{{ $taskAssign->assign ? $taskAssign->assign->name : "" }}</td>
                    @endif
                    <td>
                        <form action="{{ route('task-assign.destroy', $taskAssign->slug) }}" method="POST" style="display: inline-block;">
                            @canAccess('show','task_assigns')
                            <a href="{{ route('task-assign.show', $taskAssign->slug) }}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a>
                            @endcanAccess
                            @canAccess('edit','task_assigns')
                            <a href="{{ route('task-assign.edit', $taskAssign->slug) }}" class="btn btn-sm btn-info"><i class="fa fa-edit"></i></a>
                            @endcanAccess
                            @csrf
                            @method('DELETE')
                            @canAccess('destroy','task_assigns')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                            @endcanAccess
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center">
        {{ $assigns->withQueryString()->links('vendor.pagination.bootstrap-4') }}
    </div>
</div>
@endsection
@section('css')
    <style>
        body 
        {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            border-radius: 5px;
        }

        .btn-custom {
            background-color: #007bff;
            color: #ffffff;
            border-radius: 4px;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .pagination > li > a {
            color: #007bff;
            background-color: transparent;
            border: none;
        }

        .pagination > .active > a {
            background-color: #007bff;
            color: #ffffff;
        }
    </style>
@stop
