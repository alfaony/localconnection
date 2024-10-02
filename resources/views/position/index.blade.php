@extends('adminlte::page')

@section('content')
    <!-- Success message -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Table for displaying Positions -->
    <div class="card">
        <div class="card-header">
            <h3>Positions</h3>
            <!-- Button to trigger modal for creating a new position -->
             @canAccess('store','positions')
            <button class="btn btn-primary float-right" data-toggle="modal" data-target="#createModal"><i class="fa fa-plus"></i> Posisi</button>
            @endcanAccess
        </div>

        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($positions as $position)
                        <tr>
                            <td>{{ $position->name }}</td>
                            <td>{{ $position->company->name }}</td>
                            <td>
                                <!-- Button to trigger modal for editing -->
                                 @canAccess('edit','positions')
                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal{{ $position->id }}"><i class="fa fa-edit"></i></button>
                                @endcanAccess

                                <!-- Delete form -->
                                @canAccess('destroy','positions')
                                <form action="{{ route('position.destroy', $position->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit"><i class="fa fa-trash"></i></button>
                                </form>
                                @endcanAccess
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        @canAccess('edit','positions')
                        <div class="modal fade" id="editModal{{ $position->id }}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <form action="{{ route('position.update', $position->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editModalLabel">Edit Position</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="name">Position Name</label>
                                                <input type="text" class="form-control" name="name" value="{{ $position->name }}" required>
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
                        @endcanAccess
                    @endforeach
                </tbody>
            </table>
            
            <div class="d-flex justify-content-center mt-2">
                {{ $positions->withQueryString()->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
    </div>

    @canAccess('store','positions')
    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('position.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createModalLabel">Add Position</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Position Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Add Position</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcanAccess


@endsection
