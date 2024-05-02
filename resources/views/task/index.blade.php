
@extends('adminlte::page')

@section('content_header')
    <h1>{{ isset($task) ? 'Pekerjaan' : 'Pekerjaan' }}</h1>
@stop
@php
$no = 1;


@endphp
@section('content')
<div class="col-md-12">
    @if(Session::get('store'))
    <div class="alert alert-success mt-3">Pekerjaan Berhasil Ditambahkan</div>
    @endif
    @if(Session::get('update'))
    <div class="alert alert-success mt-3">Pekerjaan Berhasil Diperbarui</div>
    @endif
    @if(Session::get('delete'))
    <div class="alert alert-success mt-3">Pekerjaan Berhasil Terhapus</div>
    @endif

</div>

<div class="container p-3">
    <div class="card">
        @canAccess('store','tasks')
        <div class="container">
            <form action="{{ isset($task) ? route('task.update', $task->slug) : route('task.store') }}" method="POST">
                @csrf
                @if(isset($task))
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Pekerjaan</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $task->name ?? '') }}" required>
                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="task_type_id" class="form-label">Jenis</label>
                    <select class="form-control @error('task_type_id') is-invalid @enderror" id="task_type_id" name="task_type_id" required>
                        @foreach ($taskTypes as $type)
                            <option value="{{ $type->id }}" @if(old('task_type_id', $task->task_type_id ?? '') == $type->id) selected @endif>{{ ucfirst($type->name) }}</option>
                        @endforeach
                    </select>
                    @error('task_type_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="point" class="form-label">Poin</label>
                    <input type="number" class="form-control @error('point') is-invalid @enderror" id="point" name="point" value="{{ old('point', $task->point ?? '') }}" required>
                    @error('point')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">{{ isset($task) ? 'Ubah' : 'Simpan' }}</button>
            </form>
        </div>
        @endcanAccess
    </div>
    <div class="card mt-4">
        <div class="card-body">
            <form action="{{ route('task.index') }}" method="get">
                <div class="d-flex flex-row-reverse">
                    <div class="p-2">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                    </div>
                    <div class="p-2">
                        <input type="text" name="task" class="form-control" placeholder="Search">
                    </div>
                    <div class="p-2">
                    @php
                        $order = request('order', 'desc');
                    @endphp
                        <select name="order" class="form-control">
                            <option value="asc" {{ $order == 'asc' ? 'selected' : '' }} >A - Z Created By</option>
                            <option value="desc" {{ $order == 'desc' ? 'selected' : '' }}>Z - A Created By</option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="table-responsive-md">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pekerjaan</th>
                            <th>Jenis Pekerjaan</th>
                            <th>Poin</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $task)
                        <tr>
                            <td>{{ $task->name }}</td>
                            <td>{{ $task->taskType->name }}</td>
                            <td>{{ $task->point }}</td>
                            <td>
                                @canAccess('edit','tasks')
                                <a href="{{ route('task.edit', $task->slug) }}" class="btn btn-info"><i class="fa fa-edit"></i></a>
                                @endcanAccess
                                @canAccess('destroy','tasks')
                                <form action="{{ route('task.destroy', $task->slug) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                                </form>
                                @endcanAccess
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $tasks->withQueryString()->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
    </div>

</div>
@stop
@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

@stop
@section('css')
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
    </style>
@stop

