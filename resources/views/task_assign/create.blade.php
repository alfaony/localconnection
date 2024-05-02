@extends('adminlte::page')

@section('content_header')
    <h1>{{ isset($taskAssign) ? 'Edit Penugasan' : 'Membuat Penugasan' }}</h1>
@stop
@section('content')
<div class="col-md-12">
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
<div class="container py-3">
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ isset($taskAssign) ? route('task-assign.update', $taskAssign->slug) : route('task-assign.store') }}" method="POST">
                @csrf
                @if(isset($taskAssign))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="date" class="form-label">Tanggal</label>
                    <input type="date" class="form-control" data-date-format="DD/MMM/YYYY" id="date" name="date" required>
                    @error('date')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="task_status_id" class="form-label">Status</label>
                    <select class="form-control" id="task_status_id" name="task_status_id" required>
                         @foreach ($taskStatuss as $taskStatus)
                            <option value="{{ $taskStatus->id }}" 
                                @if(isset($taskAssign))
                                    {{ $taskAssign->task_status_id == $taskStatus->id ? 'selected' : '' }}
                                @else
                                    {{ $taskStatus->name == 'doing' ? 'selected' : '' }}
                                @endif
                            >{{ ucfirst($taskStatus->name) }}</option>
                        @endforeach
                    </select>
                    @error('task_status_id')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div id="task-user-fields" class="mb-3">
                    <div class="task-user-field row align-items-center">
                        <div class="col-md-5">
                            <label for="task_id[]" class="form-label">Pekerjaan</label>
                            <select class="form-control" name="task_id[]" required>
                                @foreach ($tasks as $task)
                                    <option value="{{ $task->id }}">{{ $task->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="user_assign_task[]" class="form-label">Penugasan</label>
                            <select class="form-control" name="user_assign_task[]" required>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex justify-content-center align-items-center">
                            <button type="button" class="btn btn-danger remove-task-user"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>

                @canAccess('create','task_assigns')
                <button type="button" id="add-task-user" class="btn btn-info mb-2"><i class="fa fa-plus"></i> Pekerjaan</button>
                <button type="submit" class="btn btn-success mb-2"><i class="fa fa-save"></i> {{ isset($taskAssign) ? 'Update' : 'Simpan' }}</button>
                @endcanAccess
            </form>
        </div>
    </div>
</div>


@endsection
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const taskUserContainer = document.getElementById('task-user-fields');
    const addTaskButton = document.getElementById('add-task-user');

    // Fungsi untuk menambahkan task dan user fields
    function addTaskField() {
        const newField = document.createElement('div');
        newField.classList.add('task-user-field', 'row', 'align-items-center', 'mb-3');
        newField.innerHTML = `
            <div class="col-md-5">
                <label for="task_id[]" class="form-label">Pekerjaan</label>
                <select class="form-control" name="task_id[]" required>
                    ${tasksOptions}
                </select>
            </div>
            <div class="col-md-5">
                <label for="user_assign_task[]" class="form-label">Penugasan</label>
                <select class="form-control" name="user_assign_task[]" required>
                    ${usersOptions}
                </select>
            </div>
            <div class="col-md-2 text-center">
                <button type="button" class="btn btn-danger remove-task-user"><i class="fa fa-trash"></i></button>
            </div>
        `;

        // Add remove button functionality
        newField.querySelector('.remove-task-user').addEventListener('click', function() {
            this.parentElement.parentElement.remove();
        });

        taskUserContainer.appendChild(newField);
    }

    // Event listener for adding new task and user assignment fields
    addTaskButton.addEventListener('click', addTaskField);

    // Generate options from server-side
    const tasksOptions = `@foreach ($tasks as $task)<option value="{{ $task->id }}">{{ $task->name }}</option>@endforeach`;
    const usersOptions = `@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach`;
});
</script>
@stop
@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        body 
        {
            font-family: Arial, sans-serif;
            /* padding: 20px; */
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
        @media (max-width: 768px) 
        {
            .btn { width: 100%; margin-bottom: 10px; }
            .form-control { width: 100%; }
        }

    </style>
@stop
