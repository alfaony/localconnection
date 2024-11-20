@extends('adminlte::page')

@section('title', 'Pass Checkings')

@section('content_header')
    <h1>Pass Checkings</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Button to Open the Create Modal -->
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createEditModal">
            <i class="fa fa-plus"></i> Pass Checking
        </button>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Pictures</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($passCheckings as $passChecking)
                    <tr>
                        <td>{{ $loop->iteration + $passCheckings->firstItem() - 1 }}</td>
                        <td>{{ $passChecking->user->name ?? 'N/A' }}</td>
                        <td>{{ $passChecking->date ? \Carbon\Carbon::parse($passChecking->date)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $passChecking->start_time ? \Carbon\Carbon::parse($passChecking->start_time)->format('H:i:s') : '-' }}</td>
                        <td>{{ $passChecking->end_time ? \Carbon\Carbon::parse($passChecking->end_time)->format('H:i:s') : '-' }}</td>
                        <td width="40%">
                            @if($passChecking->pictures)
                                @foreach($passChecking->pictures as $picture)
                                    <img src="{{ $picture }}" alt="Picture" class="img-thumbnail" width="200">
                                @endforeach
                            @else
                                No pictures
                            @endif
                        </td>
                        <td>
                            <button 
                                class="btn btn-warning btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#createEditModal"
                                data-id="{{ $passChecking->id }}"
                                data-user_id="{{ $passChecking->user_id }}"
                                data-date="{{ \Carbon\Carbon::parse($passChecking->date)->format('Y-m-d') }}"
                                data-start_time="{{ \Carbon\Carbon::parse($passChecking->start_time)->format('H:i') }}"
                                data-end_time="{{ \Carbon\Carbon::parse($passChecking->end_time)->format('H:i') }}"
                            >
                                <i class="fa fa-edit"></i>
                            </button>
                            <form action="{{ route('pass-checking.destroy', $passChecking->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No Pass Checkings Found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            {{ $passCheckings->links() }}
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="createEditModal" tabindex="-1" aria-labelledby="createEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createEditForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createEditModalLabel">Create Pass Checking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="id" id="passCheckingId">

                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" name="date" id="date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="pictures" class="form-label">Pictures</label>
                        <input type="file" name="pictures[]" id="pictures" class="form-control" multiple accept="image/*">
                        <small class="form-text text-muted">You can upload multiple pictures.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const createEditModal = document.getElementById('createEditModal');
        createEditModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const user_id = button.getAttribute('data-user_id');
            const date = button.getAttribute('data-date');
            const start_time = button.getAttribute('data-start_time');
            const end_time = button.getAttribute('data-end_time');

            const form = createEditModal.querySelector('#createEditForm');

            if (id) {
                // Edit mode
                createEditModal.querySelector('.modal-title').textContent = 'Edit Pass Checking';
                form.action = `/pass-checking/${id}`;
                form.querySelector('#formMethod').value = 'PUT';
                form.querySelector('#passCheckingId').value = id;
                form.querySelector('#user_id').value = user_id;
                form.querySelector('#date').value = date;
                form.querySelector('#start_time').value = start_time;
                form.querySelector('#end_time').value = end_time;
            } else {
                // Create mode
                createEditModal.querySelector('.modal-title').textContent = 'Create Pass Checking';
                form.action = '/pass-checking';
                form.querySelector('#formMethod').value = 'POST';
                form.reset();
            }
        });
    });
</script>
@endsection
