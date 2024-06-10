@extends('adminlte::page')

@section('content_header')
    <h2>Main Proyek Tugas Harian</h2>
@stop

@section('content')
<div class="card p-3">
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<div class="card-bdoy">
    {{-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createModal"><i class="fa fa-plus"></i> Project</button>--}}
    @canAccess('create','daily_task_projects')
    <a class="btn btn-primary" href="{{ route('daily_task_project.create') }}"><i class="fa fa-plus"></i> Main Project</a>
    @endcanAccess
    <table class="table mt-3">
        <thead>
        <tr>
            <th>Proyek</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($projects as $project)
            <tr>
                <td>{{ $project->name }}</td>
                <td>
                    @canAccess('showproject','daily_task_projects')
                    <a href="{{ route('daily_task_project.showproject', $project->slug) }}" class="btn btn-sm btn-warning"><i class="fa fa-tasks"></i></a>
                    @endcanAccess
                    @canAccess('show','daily_task_projects')
                    <a href="{{ route('daily_task_project.show', $project->slug) }}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a>
                    @endcanAccess
                    @canAccess('edit','daily_task_projects')
                    <a href="{{ route('daily_task_project.edit',$project->slug) }}" class="btn btn-info btn-sm" ><i class="fa fa-edit"></i></a>
                    @endcanAccess
                    @canAccess('destroy','daily_task_projects')
                    <form action="{{ route('daily_task_project.destroy', $project->slug) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                    </form>
                    @endcanAccess
                </td>
            </tr>
    
            <!-- Edit Modal -->
            <div class="modal fade" id="editModal{{ $project->slug }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Edit Project</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('daily_task_project.update', $project->slug) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" class="form-control" name="name" value="{{ $project->name }}" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        </tbody>
    </table>
    
    {{ $projects->withQueryString()->links('vendor.pagination.bootstrap-4') }}
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Create Project</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('daily_task_project.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Create</button>
                </form>
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
@endsection