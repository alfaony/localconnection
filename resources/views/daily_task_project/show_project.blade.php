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
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    @if(Session::get('dailytaskstore'))
        <div class="alert alert-success mt-3">Tugas Berhasil Ditambahkan</div>
    @endif
    <div class="row">
        <div class="col-md-12">
            @canAccess('createdailytask','daily_task_projects')
            <a href="{{ route('daily_task_project.createdailytask',$project->slug) }}" class="btn btn-primary mb-3"><i class="fa fa-plus"></i> Tugas Harian</a>
            @endcanAccess
            <div class="card">
                <div class="card-header d-flex justify-content-between">
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
                                    <select class="form-control selectSearch" id="user" name="user">
                                        <option value="all">All User</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->name }}" {{ request('user') == $user->name ? 'selected' : '' }}>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-control selectSearch" id="status" name="status">
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
                                <th class="col-4">Nama Tugas</th>
                                <th class="col-2">Dibuat</th>
                                <th class="col-2">Ditugaskan</th>
                                <th class="col-2">Tanggal</th>
                                <th class="col-1">Status</th>
                                @foreach($customFields as $field)
                                    <th class="col-2">{{ $field->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tasks as $task)
                                <tr>
                                    <td>
                                        @canAccess('show','dailytasks')
                                        <a href="{{ route('dailytask.show', $task->slug) }}" class="btn btn-info badge-light">{{ $task->name }}</a>
                                        @endcanAccess
                                    </td>
                                    <td>{{ $task->user->name }}</td>
                                    <td>{{ $task->assign->name }}</td>
                                    <td>
                                        <span class="{{ $task->isOverdue() ? 'text-danger' : '' }}">
                                            {{ $task->dateShow }}
                                        </span>
                                    </td>
                                    <td>
                                    @switch($task->taskStatus->name)
                                        @case('todo')
                                            <i class="fa fa-list-alt"></i> Todo
                                            @break
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
                                                <span class="badge badge-pill badge-info">{{ $val->customFieldValue ? $val->customFieldValue->value : "" }}</span>
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
                    <div class="mt-2">
                        {{ $tasks->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>

<script>
    $(document).ready(function() 
    {
        // initializeSelect2();
        loadCustomFields('{{ $project->id }}');

        $('.select2').select2({
            dropdownParent: $('#createTaskModal'),
            placeholder: 'Pilih',
            allowClear: true,
            width: '100%' // Adjust width as needed
        });
        $('.category-select2').select2({
            dropdownParent: $('#createTaskModal'),
            width: '100%' // Adjust width as needed
        });

        $('#dynamic-form-fields').on('change', '.project-select', function() {
            var projectId = $(this).val();
            var index = $(this).closest('.dynamic-field').index();
            console.log(index);

            if (projectId) {
                $.ajax({
                    url: '{{ url('daily_task_project/getcustomfield') }}/' + projectId,
                    data:
                    {
                        index:index
                    },
                    type: 'GET',
                    success: function(data) 
                    {
                        $('#custom-fields-container-' + index).html(data);
                        initializeSelect2ForContainer(index);
                    }
                });
            } else {
                $('#custom-fields-container-' + index).html('');
            }
        });


        $('#dynamic-form-fields').on('change', '.objective-select', function() {
            var objective = $(this).val();
            var index = $(this).closest('.dynamic-field').index();
            console.log(index);

            if (objective) {
                $.ajax({
                    url: '{{ url('objective/getresult') }}/' + objective,
                    data:
                    {
                        index:index
                    },
                    type: 'GET',
                    success: function(data) 
                    {
                        $('#keyresult-fields-container-' + index).html(data);
                        initializeSelect2ForContainer(index);
                    }
                });
            } else {
                $('#keyresult-fields-container-' + index).html('');
            }
        });

            $('#dynamic-form-fields').on('change', '.start-date', function() {
                var startDateValue = $(this).val();
                $(this).closest('.dynamic-field').find('.end-date').val(startDateValue);
            });

            $('input[name="start_date"]').on('change', function() {
                var startDateValue = $(this).val();
                $('input[name="end_date"]').val(startDateValue);
            });

    });

        function initializeSelect2() 
        {
            $('.select3').select2({
                placeholder: 'Pilih',
                width: '100%' // Adjust width as needed
            });
            $('.category-select3').select2({
                width: '100%' // Adjust width as needed
            });
        }

        function initializeSelect2ForContainer(index) 
        {
            $('.select2-single-'+index+', .select2-multiple-'+index+'').select2({
                width: '100%' // Adjust width as needed
            });
        }

        function generateRandomString(length) 
        {
            var result = '';
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        }

        function loadCustomFields(projectId) 
        {
            var projectId = projectId
            var url = '{{ url('daily_task_project/getcustomfield') }}/' + projectId;
            

            console.log(url);
            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) 
                {
                    $('#custom-fields-container-0').html(data);
                    $('.select2-single, .select2-multiple').select2({
                        width: '100%' // Adjust width as needed
                    }); // Re-initialize select2
                }
            });
        }

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
    .select2-selection__choice
    {
        background-color: #007bff !important;
        border: 1px solid #007bff !important;
    }

    .select2-selection__choice__remove
    {
        color: #fe0700 !important;
        border: 1px solid #007bff !important;
    }
</style>
<style>
    .table-responsive-sm {
        overflow-x: auto;
    }
</style>
@endsection
