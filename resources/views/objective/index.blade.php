@extends('adminlte::page')

@section('content_header')
    <h2>Objective</h2>
@stop

@section('content')
<div class="card p-3">
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
@if(Session::get('store'))
<div class="alert alert-success mt-3">Objective Berhasil Ditambahkan</div>
@endif
@if(Session::get('update'))
<div class="alert alert-success mt-3">Objective Berhasil Diperbarui</div>
@endif
@if(Session::get('delete'))
<div class="alert alert-success mt-3">Objective Berhasil Terhapus</div>
@endif

<div class="card-body">
    @canAccess('create','objectives')
    <a class="btn btn-primary" href="{{ route('objective.create') }}"><i class="fa fa-plus"></i> Objective</a>
    @endcanAccess
    
    <table class="table mt-3">
        <thead>
        <tr>
            <th>Objective</th>
            <th>Divisi</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        @foreach($objectives as $objective)
            <tr>
                <td>{{ $objective->name }}</td>
                <td>{{ $objective->division->name }}</td>
                <td>{{ $objective->dateShow }}</td>
                <td>
                    @canAccess('show','objectives')
                    <a href="{{ route('objective.show', $objective->slug) }}" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i></a>
                    @endcanAccess
                    @canAccess('edit','objectives')
                    <a type="button" class="btn btn-info btn-sm" href="{{ route('objective.edit', $objective->slug) }}"><i class="fa fa-edit"></i></a>
                    @endcanAccess
                    @canAccess('destroy','objectives')
                    <form action="{{ route('objective.destroy', $objective->slug) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin?')"><i class="fa fa-trash"></i></button>
                    </form>
                    @endcanAccess
                </td>
            </tr>
    
            <!-- Edit Modal -->
            <div class="modal fade" id="editModal{{ $objective->slug }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Edit Objective</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('objective.update', $objective->slug) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label for="name">Nama</label>
                                    <input type="text" class="form-control" name="name" value="{{ $objective->name }}" required>
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
    
    {{ $objectives->withQueryString()->links('vendor.pagination.bootstrap-4') }}
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Buat Objective</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('objective.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">Nama</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="division_id">Divisi</label>
                        <select class="form-control select2" name="division_id" required>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Buat</button>
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
