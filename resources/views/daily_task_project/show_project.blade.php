@extends('adminlte::page')

@section('title', 'Project Tasks')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('daily_task_project.index') }}">Proyek</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $project->name ?? '' }}</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Proyek : {{ $project->name }}</h5>
                </div>
                <div class="card-body">
                   <div class="mb-3">
                        <form method="GET" action="{{ route('daily_task_project.showproject', $project->slug)  }}"> <!-- Adjust the route as needed -->
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="text" name="task_name" class="form-control" placeholder="Search by Task Name" value="{{ request('task_name') }}">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control select2" id="user" name="user">
                                        <option value="all">All User</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->name }}" {{ request('user') == $user->name ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control" id="status" name="status">
                                        <option value="">Select Status</option>
                                        @foreach ($taskStatuss as $status)
                                            <option value="{{ $status->name }}" {{ request('status') == $status->name ? 'selected' : '' }}>{{ ucfirst($status->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    <a href="{{ route('daily_task_project.showproject',$project->slug) }}" class="btn btn-secondary">Reset</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <table class="table table-bordered table-responsive-sm">
                        <thead>
                            <tr>
                                <th class="col-2">Nama Tugas</th>
                                <th class="col-1">Ditugaskan</th>
                                <th class="col-2">Tanggal</th>
                                <th class="col-1">Status</th>
                                @foreach($customFields as $field)
                                    <th class="col-3">{{ $field->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasks as $task)
                                <tr>
                                    <td>
                                        @canAccess('show','dailytasks')
                                        <a href="{{ route('dailytask.show', $task->slug) }}" class="btn btn-info badge-pill badge-light">{{ $task->name }}</a>
                                        @endcanAccess
                                    </td>
                                    <td>{{ $task->user->name }}</td>
                                    <td>
                                        <span class="{{ $task->isOverdue() ? 'text-danger' : '' }}">
                                            {{ $task->dateShow }}
                                        </span>
                                    </td>
                                    <td>
                                    @switch($task->taskStatus->name)
                                        @case('doing')
                                            <i class="fa fa-hourglass-start"></i>
                                            @break
                                        @case('in review')
                                            <i class="fa fa-eye" style="color: green;"></i>
                                            @break
                                        @case('not complete')
                                            <i class="fa fa-times-circle" style="color: red;"></i>
                                            @break
                                        @case('complete')
                                            <i class="fa fa-check" style="color: green;"></i>
                                            @break
                                        @default
                                            {{ $task->taskStatus->name }}
                                    @endswitch
                                    </td>
                                    @foreach($customFields as $field)
                                        <td>
                                            @php
                                                $value = $task->customFieldValues->where('custom_field_id', $field->id);
                                            @endphp

                                            @foreach($value as $val)
                                                <span class="badge badge-pill badge-info">{{ $val->customFieldValue->value }}</span>
                                            @endforeach
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3 + {{ count($customFields) }}">No tasks found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $tasks->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function () 
    {
        $('.select2').select2();
    });
</script>
@endsection
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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
<style>
    .table-responsive-sm {
        overflow-x: auto;
    }
</style>
@endsection
