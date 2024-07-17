@extends('adminlte::page')

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('daily_task_project.index') }}">Proyek</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{ $project->name ?? '' }}</li>
    </ol>
</nav>
@if(Session::get('assign'))
    <div class="alert alert-success mt-3">Tugas Berhasil Ditugaskan</div>
@endif
@if(Session::get('dailytaskstore'))
    <div class="alert alert-success mt-3">Tugas Berhasil Ditambahkan</div>
@endif
@if($errors->any())
    <div class="alert alert-danger mt-3">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="col-md-12">
    @canAccess('createdailytask','daily_task_projects')
    <a href="{{ route('daily_task_project.createdailytask',['slug'=>$project->slug,'redirect' => 'daily_task_project.kanban']) }}" class="btn btn-primary mb-3"><i class="fa fa-plus"></i> Tugas Harian</a>
    @endcanAccess
    @canAccess('showproject','daily_task_projects')
    <a href="{{ route('daily_task_project.showproject', $project->slug) }}" class="btn btn-warning mb-3"><i class="fa fa-tasks"></i> List Tugas Harian</a>
    @endcanAccess
</div>
<div class="card">
    <div class="card-body">
        <div class="kanban-container">
            <div class="kanban-row">
                @foreach ($tasksByStatus as $status => $taskGroup)
                    @canAccess('updatestatus','dailytasks')
                    <div class="kanban-column">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                {{ ucfirst($status) }}
                            </div>
                            <div class="card-body kanban-column-body" data-status="{{ $status }}">
                                @if ($taskGroup->isEmpty())
                                    <div class="empty-status-placeholder">Drop tasks here</div>
                                @else
                                    @foreach ($taskGroup->sortBy('start_date') as $task)
                                    <div class="card mb-2 task-card" data-user-id="{{ $task->assignment_user_id }}" data-start-date="{{ $task->start_date }}" data-end-date="{{ $task->end_date }}" data-task-id="{{ $task->id }}">
                                        <div class="card-body task-card-body">
                                            <h5 class="card-title">{{ $task->nameShow }} {{ $task->head ? "< ". Str::limit($task->head->name,10) : '' }}</h5>
                                            <p class="card-text assign-text">{{ $task->assign ? $task->assign->name : '' }}</p>
                                            <p class="card-text">
                                                <span class="{{ $task->isOverdue() ? 'text-danger' : '' }}">
                                                    {{ $task->dateShow }}
                                                </span>
                                            </p>
                                            <a href="{{ route('dailytask.show', $task->slug) }}" class="btn btn-info btn-sm"><i class="fa fa-eye"></i> Lihat</a>
                                            @if(!$task->assign)
                                                @canAccess('assign','dailytasks')
                                                <button class="btn btn-secondary btn-sm assign-button" data-task-id="{{ $task->id }}" data-task-slug="{{ $task->slug }}"><i class="fa fa-user"></i> Assign</button>
                                                @endcanAccess
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    @endcanAccess
                @endforeach
            </div>
        </div>
    </div>
</div>

@canAccess('assign','dailytasks')
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignModalLabel">Assign Task</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="assignForm" method="post">
                    @csrf
                    @method("PUT")
                    <div class="form-group">
                        <label for="assignment_user_id">Assign User</label>
                        <select name="assignment_user_id" id="assignment_user_id" class="form-control select2" required>
                            <option value="" selected disabled>Select User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" required>
                    </div>
                    <input type="hidden" id="modal_task_id">
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcanAccess
@endsection

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .kanban-container {
        overflow-x: auto;
        white-space: nowrap;
    }
    .kanban-row {
        display: flex;
        flex-wrap: nowrap;
    }
    .kanban-column {
        flex: 0 0 auto;
        width: 300px; /* Adjust the width as needed */
        margin-right: 20px; /* Adjust spacing between columns as needed */
    }
    .kanban-column-body {
        max-height: 500px; /* Adjust the max height as needed */
        overflow-y: auto;
    }
    .card-header {
        font-size: 1.25rem;
        font-weight: bold;
    }
    .task-card-body {
        padding: 10px; /* Adjust padding as needed */
        word-wrap: break-word; /* Ensure text wraps within the card */
        overflow: hidden; /* Hide any overflow */
    }
    .task-card {
        height: auto;
        max-height: 200px; /* Adjust the max height as needed */
        overflow: hidden;
    }
    .task-card-body {
        height: 100%;
        overflow-y: auto;
    }
    .card-title {
        font-size: 1rem;
        margin-bottom: 0.5rem;
        line-height: 1.2;
        white-space: pre-wrap; /* Preserve whitespace and allow wrapping */
    }
    .card-text {
        font-size: 0.875rem;
        margin-bottom: 0.25rem;
        word-wrap: break-word;
    }
    .assign-text {
        color: #007bff;
        font-weight: bold;
    }
    .card .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .card-text span {
        display: block;
        word-wrap: break-word;
    }
    .hovered {
        border: 2px dashed #007bff;
    }
    .empty-status-placeholder {
        padding: 20px;
        text-align: center;
        color: #999;
        border: 2px dashed #ddd;
        margin-bottom: 10px;
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

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('input[name="start_date"]').on('change', function() {
            var startDateValue = $(this).val();
            $('input[name="end_date"]').val(startDateValue);
        });

        $('.select2').select2({
            'placeholder': 'Select User', // Adjust the placeholder as needed
            dropdownParent: $('#assignModal'),
            width: '100%'
        });

        // Make the task cards draggable and sortable
        $(".kanban-column-body").sortable({
            connectWith: ".kanban-column-body",
            placeholder: "ui-state-highlight",
            items: ".task-card",
            start: function(event, ui) {
                $(ui.helper).addClass('dragging');
            },
            receive: function(event, ui) {
                var taskCard = $(ui.item); // Updated to use ui.item
                var taskId = taskCard.data('task-id');
                var userId = taskCard.data('user-id');
                var startDate = taskCard.data('start-date');
                var endDate = taskCard.data('end-date');
                
                var newStatus = $(this).data('status');

                console.log("SortAble");
                console.log(taskCard);
                console.log(taskId);
                console.log(userId);

                // Remove the placeholder if it exists
                $(this).find('.empty-status-placeholder').remove();

                // Make an AJAX request to update the task status
                $.ajax({
                    url: '{{ route('dailytask.updatestatus') }}', // Adjust the route accordingly
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        taskId: taskId,
                        newStatus: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Task status updated successfully!',
                                timer: 1000,
                                didOpen: () => {
                                    Swal.showLoading();
                                    const b = Swal.getHtmlContainer().querySelector('b');
                                    timerInterval = setInterval(() => {
                                        b.textContent = Swal.getTimerLeft();
                                    }, 100);
                                },
                                willClose: () => {
                                    clearInterval(timerInterval);
                                    location.reload(); // Reload the page after the delay
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to update task status.',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                willClose: () => {
                                    location.reload(); // Reload the page after the delay
                                }
                            });
                            $(ui.sender).sortable('cancel');
                        }
                    },

                    error: function() {
                        $(ui.sender).sortable('cancel');
                        // alert('An error occurred while updating the task status.');
                    }
                });
            }
        }).disableSelection();

        // Add a placeholder to empty columns
        $('.kanban-column-body').each(function() {
            if ($(this).find('.task-card').length === 0) {
                $(this).append('<div class="empty-status-placeholder">Drop tasks here</div>');
            }
        });

        // Enable droppable functionality with visual feedback
        $('.kanban-column-body').droppable({
            accept: '.task-card',
            hoverClass: 'hovered',
            drop: function(event, ui) {
                var taskCard = $(ui.helper);
                var taskId = taskCard.data('task-id');
                var userId = taskCard.data('user-id');
                var startDate = taskCard.data('start-date');
                var endDate = taskCard.data('end-date');
                
                if(!userId || !startDate || !endDate) {
                    // swal.fire({
                    //     icon: 'error',
                    //     title: 'Error',
                    //     text: 'Harap pilih pengguna, tanggal mulai, dan tanggal selesai terlebih dahulu!',
                    //     timer: 3000,
                    //     timerProgressBar: true,
                    //     showConfirmButton: false
                    // });

                    $(ui.sender).droppable('cancel');
                    return false;
                }

                // Append the task card to the new column
                $(this).append(taskCard);
            }
        });

        // Show assign modal when "Assign" button is clicked
        $(document).on('click', '.assign-button', function() {
            var taskId = $(this).data('task-id');
            var taskSlug = $(this).data('task-slug');

            $('#modal_task_id').val(taskId);
            $('#assignForm').attr('action', '{{ url('dailytask/assign') }}/' + taskSlug);

            var modalToggle = document.getElementById('assignModal') // relatedTarget
            var myModal = new bootstrap.Modal(modalToggle);
            myModal.show(modalToggle);
        });
    });
</script>
@endsection