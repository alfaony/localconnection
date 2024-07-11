@extends('adminlte::page')

@section('title', 'Daftar Shifting')

@section('content_header')
    <h1>Daftar Shifting</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @canAccess('create','shifting_obs')
            <div class="mb-3">
                <button class="btn btn-primary" data-toggle="modal" data-target="#createModal"><i class="fa fa-plus"></i> Shifting</button>
            </div>
            @endcanAccess
        
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shifts as $shift)
                            <tr>
                                <td>{{ $shift->name }}</td>
                                <td>{{ $shift->clock_in }}</td>
                                <td>{{ $shift->clock_out ?? "-" }}</td>
                                <td>
                                    @canAccess('update','shifting_obs')
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal{{ $shift->slug }}"><i class="fa fa-edit"></i></button>
                                    @endcanAccess
                                    @canAccess('destroy','shifting_obs')
                                    <form action="{{ route('shifting-ob.destroy', $shift->slug) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')" title="Delete"><i title="Delete" class="fa fa-trash"></i></button>
                                    </form>
                                    @endcanAccess
                                </td>
                            </tr>
                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal{{ $shift->slug }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel{{ $shift->id }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel{{ $shift->id }}">Edit Shifting</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="{{ route('shifting-ob.update', $shift->slug) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label for="name">Nama</label>
                                                    <input type="text" class="form-control" name="name" value="{{ $shift->name }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="clock_in">Jam Masuk</label>
                                                    <input type="time" class="form-control" name="clock_in" value="{{ $shift->clock_in }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="clock_in">Jam Keluar</label>
                                                    <input type="time" class="form-control" name="clock_out" value="{{ $shift->clock_out }}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $shifts->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Shifting</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('shifting-ob.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nama</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="clock_in">Jam Masuk</label>
                            <input type="time" class="form-control" name="clock_in" required>
                        </div>
                        <div class="form-group">
                            <label for="clock_in">Jam Masuk</label>
                            <input type="time" class="form-control" name="clock_out" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@stop

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
@stop
