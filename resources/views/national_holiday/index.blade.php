@extends('adminlte::page')

@section('content_header')
    <h1>List Natioal Holidays</h1>
@stop
@section('content')
<div class="card">
    <div class="card-body">
        <!-- Success message -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
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
        <!-- Button to Open the Create Modal -->
         @canAccess('create','national_holidays')
        <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#createModal">
            <i class="fa fa-plus"></i> New Holiday
        </button>
        @endcanAccess
    
        <!-- National Holidays Table -->
         <div class="table-responsive">
             <table class="table table-bordered">
                 <thead>
                     <tr>
                         <th>Name</th>
                         <th>Date</th>
                         <th>Actions</th>
                     </tr>
                 </thead>
                 <tbody>
                     @foreach($holidays as $holiday)
                     <tr>
                         <td>{{ $holiday->name }}</td>
                         <td>{{ $holiday->date ? \Carbon\Carbon::parse($holiday->date)->locale('id')->translatedFormat('l, d F Y') : '' }}</td>
                         <td>
                            @canAccess('edit','national_holidays')
                             <!-- Edit Button (Opens Edit Modal) -->
                             <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editModal-{{ $holiday->id }}">
                                <i class="fa fa-edit"></i>
                            </button>
                            @endcanAccess

                            @canAccess('destroy','national_holidays')
                            <!-- Delete Button with Confirmation -->
                            <form action="{{ route('national-holiday.destroy', $holiday->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this holiday?')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                            @endcanAccess
                         </td>
                     </tr>
         
                     <!-- Edit Modal -->
                     <div class="modal fade" id="editModal-{{ $holiday->id }}" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('national-holiday.update', $holiday->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit National Holiday</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Holiday Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ $holiday->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="date" class="form-label">Date</label>
                                            <input type="date" name="date" class="form-control" value="{{ $holiday->date }}" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                     @endforeach
                 </tbody>
             </table>
         </div>

        <div class="d-flex justify-content-center">
            {{ $holidays->withQueryString()->links('vendor.pagination.bootstrap-4') }}

        </div>

        <!-- Create Modal -->
        <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('national-holiday.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Add New National Holiday</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="name" class="form-label">Holiday Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="date" class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Add Holiday</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
