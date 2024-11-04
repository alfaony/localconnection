@extends('adminlte::page')

@section('content_header')
    <h2>Divisi</h2>
@stop

@section('content')
<div class="card p-3">
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<div class="card-body">
    @canAccess('store','divisions')
    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#createModal"><i class="fa fa-plus"></i> Divisi</button>
    @endcanAccess
    
    <table class="table mt-3">
        <thead>
        <tr>
            <th>Divisi</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        @foreach($divisions as $division)
            <tr>
                <td>{{ $division->name }}</td>
                <td>
                    @canAccess('show','divisions')
                    <a href="{{ route('division.show', $division->slug) }}" class="btn btn-success btn-sm"><i class="fa fa-eye"></i></a>
                    @endcanAccess
                    @canAccess('edit','divisions')
                    <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#editModal{{ $division->slug }}"><i class="fa fa-edit"></i></button>
                    @endcanAccess
                    @canAccess('destroy','divisions')
                    <form action="{{ route('division.destroy', $division->slug) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin?')"><i class="fa fa-trash"></i></button>
                    </form>
                    @endcanAccess
                </td>
            </tr>
    
            <!-- Edit Modal -->
            <div class="modal fade" id="editModal{{ $division->slug }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Edit Divisi</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="{{ route('division.update', $division->slug) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label for="name">Nama</label>
                                    <input type="text" class="form-control" name="name" value="{{ $division->name }}" required>
                                </div>
                                <!-- Checkbox -->
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="manual_checkin" value="1" {{ $division->manual_checkin ? 'checked' : '' }}>
                                        <label class="form-check-label">Manual Check-In</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="requires_photo" value="1" {{ $division->requires_photo ? 'checked' : '' }}>
                                        <label class="form-check-label">Require Check-In Photo</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="requires_location" value="1" {{ $division->requires_location ? 'checked' : '' }}>
                                        <label class="form-check-label">Required Check-In Location</label>
                                    </div>
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
    
    {{ $divisions->withQueryString()->links('vendor.pagination.bootstrap-4') }}
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Buat Divisi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{ route('division.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="name">Nama</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <!-- Checkbox -->
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="manual_checkin" value="1">
                            <label class="form-check-label">Manual Check-In</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="requires_photo" value="1">
                            <label class="form-check-label">Require Check-In Photo</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="requires_location" value="1">
                            <label class="form-check-label">Required Check-In Location</label>
                        </div>
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
