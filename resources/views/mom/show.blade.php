@extends('adminlte::page')

@section('title', 'Detail Minutes of Meeting (MoM)')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Minutes of Meeting (MoM)</h1>
        <div>
            <a href="{{ route('mom.edit', $mom->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('mom.index') }}" class="btn btn-secondary ml-2">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')
<div class="container-fluid">
    <!-- General Information Card -->
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-info-circle mr-2"></i>Meeting Information
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-box bg-light">
                        <span class="info-box-icon bg-info"><i class="far fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Meeting Date</span>
                            <span class="info-box-number">{{ $mom->mom_date }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-box bg-light">
                        <span class="info-box-icon bg-success"><i class="fas fa-project-diagram"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Project</span>
                            <span class="info-box-number">{{ $mom->project?->title ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="callout callout-info">
                        <h5><i class="fas fa-sticky-note mr-2"></i>Meeting Notes</h5>
                        <div class="p-3 border rounded bg-light">{!! $mom->notes !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Agenda & Tasks Section -->
    <div class="card card-success card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tasks mr-2"></i>Meeting Agenda
            </h3>
            <div class="card-tools">
                <span class="badge bg-primary">{{ $mom->agendas->count() }} Agendas</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="accordion" id="agendaAccordion">
                @foreach ($mom->agendas as $index => $agenda)
                <div class="card mb-2">
                    <div class="card-header bg-light" id="heading{{ $index }}">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-dark font-weight-bold d-flex justify-content-between w-100" 
                                    type="button" 
                                    data-toggle="collapse" 
                                    data-target="#collapse{{ $index }}" 
                                    aria-expanded="true" 
                                    aria-controls="collapse{{ $index }}">
                                <span>
                                    <i class="fas fa-clipboard-list mr-2"></i>
                                    {{ $agenda->title }}
                                </span>
                                <span>
                                    <span class="badge bg-info">{{ $agenda->tasks->count() }} Tasks</span>
                                    <i class="fas fa-chevron-down ml-2"></i>
                                </span>
                            </button>
                        </h5>
                    </div>

                    <div id="collapse{{ $index }}" 
                         class="collapse {{ $index === 0 ? 'show' : '' }}" 
                         aria-labelledby="heading{{ $index }}" 
                         data-parent="#agendaAccordion">
                         <div class="d-flex justify-content-end align-items-center card-body pb-0 pt-0">
                            <button 
                                class="btn btn-sm btn-outline-primary mt-3"
                                onclick="openAddTaskModal('{{ $agenda->id }}', '{{ $agenda->title }}')">
                                <i class="fas fa-plus-circle mr-1"></i> Tambah Task
                            </button>
                         </div>
                        <div class="card-body">
                            <div class="callout callout-info mb-4">
                                <h5><i class="fas fa-comments mr-2"></i>Discussion Notes</h5>
                                <div class="p-3 border rounded bg-light">{!! $agenda->discussion_notes !!}</div>
                            </div>

                            @if ($agenda->tasks->count())
                            <div class="mt-4">
                                <h5><i class="fas fa-list-check mr-2"></i>Meeting Tasks</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Task</th>
                                                <th>Assigned To</th>
                                                <th>Due Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($agenda->tasks as $task)
                                            <tr>
                                                <td>
                                                    @if($task->dailyTask)
                                                    <a href="{{ route('dailytask.show', $task->dailyTask->slug) }}" class="btn btn-sm btn-info badge">
                                                        <i class="fa fa-eye"></i> {{$task->dailyTask->name}}
                                                    </a>
                                                    @else
                                                        {{ $task->title }}
                                                    @endif

                                                </td>
                                                <td>
                                                    @if ($task->dailyTask && $task->dailyTask->assignment_user_id)
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-user mr-1"></i> {{ $task->dailyTask->assign->name }}
                                                    </span>
                                                    @elseif ($task->external_email)
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-envelope mr-1"></i> {{ $task->external_email }}
                                                    </span>
                                                    @else
                                                    <span class="text-muted">Not assigned</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="{{ $task->isOverdue() ? 'text-danger' : 'text-success' }}">
                                                        {{ $task->date_show }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @switch($task->taskStatus->name)
                                                        @case('backlog')
                                                            <i class="fa fa-clipboard-list"></i> Backlog
                                                            @break
                                                        @case('todo')
                                                            <i class="fa fa-list-alt"></i> Todo
                                                            @break
                                                        @case('doing')
                                                            <i class="fa fa-hourglass-start"></i> Doing
                                                            @break
                                                        @case('in review')
                                                            <i class="fa fa-eye" style="color: green;"></i> In Review
                                                            @break
                                                        @case('not complete')
                                                            <i class="fa fa-times-circle" style="color: red;"></i> Not Complete
                                                            @break
                                                        @case('complete')
                                                            <i class="fa fa-check" style="color: green;"></i> Complete
                                                            @break
                                                        @default
                                                            {{ $dailytask->taskStatus->name }}
                                                    @endswitch
                                                </td>
                                                <td>
                                                    <div class="task-actions d-flex gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-warning mr-2 mt-1"
                                                                data-task-id="{{ $task->id }}"
                                                                data-agenda-id="{{ $agenda->id }}"
                                                                data-title="{{ $task->title }}"
                                                                data-description="{{ $task->description }}"
                                                                data-start-date="{{ $task->start_date }}"
                                                                data-end-date="{{ $task->end_date }}"
                                                                data-user-id="{{ $task->dailyTask ? $task->dailyTask->assignment_user_id : null }}"
                                                                data-objective-id="{{ $task->dailyTask ? $task->dailyTask->objective_id : null }}"
                                                                data-key-result-id="{{ $task->key_result_id }}"
                                                                data-external-email="{{ $task->external_email }}"
                                                                data-dailytask-id="{{ $task->dailyTask ? $task->dailyTask->id : null }}"
                                                                onclick="openEditTaskModal(this)">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <form action="{{ route('mom.deleteTask', $task->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Yakin ingin menghapus task ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger mt-1">
                                                                <i class="fas fa-trash-alt me-1"></i>Hapus
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>   
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>No action items for this agenda.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Meeting Participants -->
    @if (isset($mom->meeting->combined_participants) && count($mom->meeting->combined_participants) > 0)
    <div class="card card-info card-outline mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">
                <i class="fas fa-users mr-2"></i>Meeting Participants
                <span class="badge bg-primary ml-2">{{ count($mom->meeting->combined_participants) }} Participants</span>
            </h3>
            <div class="btn-group">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($mom->meeting->combined_participants as $participant)
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="card participant-card shadow-sm h-100 border-top-3 
                        {{ $participant['status'] === 'internal' ? 'border-primary' : 'border-warning' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-start">
                                <div class="avatar-circle mr-3 bg-{{ $participant['status'] === 'internal' ? 'primary' : 'warning' }}">
                                    {{ strtoupper(substr($participant['name'], 0, 1)) }}
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-1">{{ $participant['name'] }}</h5>
                                    <p class="text-muted mb-2 small">
                                        <i class="fas fa-envelope mr-1"></i> {{ $participant['email'] }}
                                    </p>
                                    
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-{{ $participant['status'] === 'internal' ? 'primary' : 'warning' }}">
                                            <i class="fas fa-{{ $participant['status'] === 'internal' ? 'user-tie' : 'globe' }} mr-1"></i>
                                            {{ $participant['status'] }}
                                        </span>
                                        
                                        @if(isset($participant['is_attended']))
                                        <span class="badge bg-{{ $participant['is_attended'] ? 'success' : 'danger' }}">
                                            <i class="fas fa-{{ $participant['is_attended'] ? 'check-circle' : 'times-circle' }} mr-1"></i>
                                            {{ $participant['is_attended'] ? 'Hadir' : 'Tidak Hadir' }}
                                        </span>
                                        @endif
                                    </div>
                                    
                                    @if(isset($participant['join_time']) && $participant['join_time'])
                                    <div class="mt-2 text-sm text-muted">
                                        <i class="fas fa-clock mr-1"></i> Joined at {{ $participant['join_time'] }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal Create-->
<div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog" aria-labelledby="addTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="addTaskForm" action="{{ route('mom.storeTask',$mom->id) }}" method="POST">
        @csrf
        @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Task ke Agenda: <span id="modalAgendaTitle"></span></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="agenda_id" id="modalAgendaId">

          <div class="form-group">
            <label>Judul Task <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" placeholder="Masukkan judul task" required>
          </div>

          <div class="form-group">
            <label>Deskripsi</label>
            <input class="thriveEditor form-control" id="description_description" data-ids="description" name="description" placeholder="yang akan dicetak di perjanjian"/>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Tanggal Mulai <span class="text-danger">*</span></label>
              <input type="date" name="start_date" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label>Tanggal Selesai <span class="text-danger">*</span></label>
              <input type="date" name="end_date" class="form-control" required>
            </div>
          </div>

          <div class="form-group">
            <label>User Internal</label>
            <select name="user_id" id="taskResponsible" class="form-control selectUser2" style="width: 100%;">
            <option value="">-- Pilih User Internal --</option>
              @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
              @endforeach
            </select>
          </div>
           <!-- MODIFIKASI BAGIAN INI -->
            <div id="internal-fields" style="display: none;">
                <div class="mb-3">
                    <label class="form-label">Objective</label>
                    <select id="taskObjective" name="objective_id" class="form-select objective-select selectUser2" 
                            onchange="loadKeyResults(this)">
                        <option value="" disabled selected>-- Pilih Objective --</option>
                        @foreach($objectives as $objective)
                            <option value="{{ $objective->id }}">{{ ucfirst($objective->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="keyresult-fields-container"></div>
            </div>

          <div class="form-group">
            <label>Email Eksternal (opsional)</label>
            <input type="email" name="external_email" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Simpan</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Task -->
<div class="modal fade" id="editTaskModal" tabindex="-1" role="dialog" aria-labelledby="editTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="editTaskForm" method="POST">
        @csrf
        @method('PUT')
      <div class="modal-content">
        <div class="modal-header bg-warning text-white">
          <h5 class="modal-title">Edit Task</h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="task_id" id="editTaskId">
          <input type="hidden" name="agenda_id" id="editAgendaId">

          <div class="form-group">
            <label>Judul Task <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" id="editTitle" placeholder="Masukkan judul task" required>
          </div>

          <div class="form-group" id="description_notes_div">
            <label>Deskripsi</label>
            <input class="thriveEditor form-control" id="description_editDescription" data-ids="editDescription" name="description" placeholder="yang akan dicetak di perjanjian"/>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Tanggal Mulai <span class="text-danger">*</span></label>
              <input type="date" name="start_date" id="editStartDate" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
              <label>Tanggal Selesai <span class="text-danger">*</span></label>
              <input type="date" name="end_date" id="editEndDate" class="form-control" required>
            </div>
          </div>

          <div class="form-group">
            <label>User Internal</label>
            <select name="user_id" id="editUserInternal" class="form-control select2" style="width: 100%;">
              <option value="">-- Pilih User Internal --</option>
              @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
              @endforeach
            </select>
          </div>
           
          <div id="editInternalFields" style="display: none;">
            <div class="mb-3">
                <label class="form-label">Objective</label>
                <select id="editTaskObjective" name="objective_id" class="form-select objective-select select2">
                    <option value="" disabled selected>-- Pilih Objective --</option>
                    @foreach($objectives as $objective)
                        <option value="{{ $objective->id }}">{{ ucfirst($objective->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div id="editKeyresultContainer"></div>
          </div>

          <div class="form-group">
            <label>Email Eksternal (opsional)</label>
            <input type="email" name="external_email" id="editExternalEmail" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Update Task</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>
      </div>
    </form>
  </div>
</div>
@stop
@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
    // Fungsi untuk membuka modal edit dengan data dari button
    function openEditTaskModal(button) 
    {
        // Ambil semua data dari atribut data-*
        const taskId = button.dataset.taskId;
        const agendaId = button.dataset.agendaId;
        const title = button.dataset.title;
        const description = button.dataset.description;
        const startDate = button.dataset.startDate;
        const endDate = button.dataset.endDate;
        const userId = button.dataset.userId;
        const objectiveId = button.dataset.objectiveId;
        const keyResultId = button.dataset.keyResultId;
        const externalEmail = button.dataset.externalEmail;
        const dailyTaskId = button.dataset.dailytaskId;

        const form = document.getElementById('editTaskForm');

        // Isi nilai form
        document.getElementById('editTaskId').value = taskId;
        document.getElementById('editAgendaId').value = agendaId;
        document.getElementById('editTitle').value = title;
        document.getElementById('editStartDate').value = startDate;
        document.getElementById('editEndDate').value = endDate;
        document.getElementById('editExternalEmail').value = externalEmail;

        const descriptionNoteDiv = document.getElementById('description_notes_div');
        if (descriptionNoteDiv) {
            descriptionNoteDiv.querySelectorAll('.ql-snow').forEach(element => {
                element.remove();
            });
        }

        generateThriveEditor("editDescription",description);
        
        // Set user internal
        $('#editUserInternal').val(userId).trigger('change');
        
        // Tampilkan/sembunyikan bagian internal fields
        const internalFields = document.getElementById('editInternalFields');
        if (userId) {
            internalFields.style.display = 'block';
            
            // Set objective jika ada
            if (objectiveId) {
                // console.log(objectiveId);
                // die;
                
                $('#editTaskObjective').val(objectiveId).trigger('change');
                
                // Load key results untuk objective yang dipilih
                if(dailyTaskId)
                {
                    loadKeyResultsForEdit(objectiveId, dailyTaskId);
                }
            }
        } else {
            internalFields.style.display = 'none';
        }
        
        // Set action form

        
        // Tampilkan modal
         const modal = new bootstrap.Modal(document.getElementById('editTaskModal'));
        modal.show();

        let url ="{{ route('mom.updateTask',':id') }}";
        url = url.replace(':id', taskId);
        form.action = url;
    }

    // Fungsi untuk memuat key results untuk modal edit
    function loadKeyResultsForEdit(objectiveId, dailyTaskId = null) {
        const container = document.getElementById('editKeyresultContainer');
        
        if (!objectiveId) {
            container.innerHTML = '';
            return;
        }
        
        // Tampilkan loading
        container.innerHTML = '<p class="text-center py-2">Memuat key results...</p>';
        
        // Kirim permintaan untuk mendapatkan key results
        $.ajax({
            url: '{{ url('objective/getresult') }}/' + objectiveId + '?is_required=true',
            method: 'GET',
            data: 
            {
                dailyTaskId: dailyTaskId // Passing dailyTaskId to the server
            },
            success: function(data) {
                const container = document.getElementById('editKeyresultContainer');
                container.innerHTML = data;
                

                
                // Inisialisasi ulang select2
                $(container).find('.select2').select2();
                initializeSelect2ForContainer(0);
            },
            error: function(xhr, status, error) {
                const container = document.getElementById('editKeyresultContainer');
                container.innerHTML = '<p class="text-danger text-center py-2">Gagal memuat key results</p>';
                console.error('Error:', error);
            }
        });
    }

    // Event listener untuk perubahan objective di modal edit
    $(document).on('change', '#editTaskObjective', function() 
    {
            console.log('Objective changed to:', this.value);
            const objectiveId = this.value;
            loadKeyResultsForEdit(objectiveId, null);
        });

    // Event listener untuk perubahan user internal di modal edit
    $(document).on('change', '#editUserInternal', function() {
        const internalFields = document.getElementById('editInternalFields');
            
        if (this.value) {
            internalFields.style.display = 'block';
            $('#editTaskObjective').prop('required', true);
        } else {
            internalFields.style.display = 'none';
            $('#editTaskObjective').prop('required', false);
            $('#editTaskObjective').val(null).trigger('change');
            document.getElementById('editKeyresultContainer').innerHTML = '';
        }
    });

    // Validasi tanggal di modal edit
    document.getElementById('editTaskForm')?.addEventListener('change', function(e) {
        if (e.target.name === 'start_date' || e.target.name === 'end_date') {
            const startDate = document.getElementById('editStartDate').value;
            const endDate = document.getElementById('editEndDate').value;
            
            if (startDate && endDate && startDate > endDate) {
                alert('Tanggal selesai harus lebih besar atau sama dengan tanggal mulai');
                document.getElementById('editEndDate').value = startDate;
            }
        }
    });
</script>
<script>
    $(`#taskResponsible`).on('change', function() {
        const internalFields = document.getElementById(`internal-fields`);
        
        if (this.value) {
            // Jika user internal dipilih, tampilkan bagian Objective & Key Result
            internalFields.style.display = 'block';
            
            // Buat field Objective menjadi required
            $(`#taskObjective`).prop('required', true);
        } else {
            // Jika tidak, sembunyikan dan hapus required
            internalFields.style.display = 'none';
            $(`#taskObjective`).prop('required', false);
            
            // Reset nilai
            $(`#taskObjective`).val(null).trigger('change');
            $(`#keyresult-fields-container`).html('');
        }
    });

    function loadKeyResults(select) 
    {
        const objectiveId = select.value;
        const container = document.getElementById(`keyresult-fields-container`);
        const responsible = document.getElementById(`taskResponsible`).value;
        agendaIndex = 1;
        
        if (objectiveId && responsible) {
            // Tampilkan loading
            container.innerHTML = '<p class="text-center py-2">Memuat key results...</p>';
            var url = '{{ url('objective/getresult') }}/' + objectiveId +"?is_required=true`";
            
            // Kirim parameter is_required
            fetch(url)
                .then(response => response.text())
                .then(data => {
                    container.innerHTML = data;
                    // Inisialisasi ulang select2
                    $(container).find('.select2').select2();
                    initializeSelect2ForContainer(0);
                })
                .catch(error => {
                    container.innerHTML = '<p class="text-danger text-center py-2">Gagal memuat key results</p>';
                    console.error('Error:', error);
                });
        } else {
            container.innerHTML = '';
        }
    }

    function initializeSelect2ForContainer(index) 
    {
        $('.select2-single-'+index+', .select2-multiple-'+index+'').select2({
            width: '100%' // Adjust width as needed
        });
    }
</script>
<script>
  $(document).ready(function() {
    $('.select2').select2(
      {
        placeholder: 'Pilih',
        allowClear: true,
        width: '100%'
      }
    );

    $('.selectUser2').select2(
      {
        dropdownParent: $('#addTaskModal'),
        placeholder: 'Pilih',
        allowClear: true,
        width: '100%'
      }
    );
});
</script>
<script>
  function openAddTaskModal(agendaId, agendaTitle) 
  {
    document.getElementById('modalAgendaId').value = agendaId;
    document.getElementById('modalAgendaTitle').textContent = agendaTitle;
    const modal = new bootstrap.Modal(document.getElementById('addTaskModal'));
    modal.show();

  }

  document.addEventListener('DOMContentLoaded', function () 
  {
        const form = document.getElementById('addTaskForm');
        if (form) {
            form.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(form);
            console.log(Object.fromEntries(formData.entries()));
            $('#addTaskModal').modal('hide');
            });
        }

    });

    $(document).ready(function () {
        $('#addTaskForm').submit(function(e) 
        {
            e.preventDefault();
            this.submit();  // Submit the form
        });


        $('#addTaskForm').on('change', 'input[name="start_date"]', function() {
            const startDate = $(this).val();
            const endDateInput = $('#addTaskForm input[name="end_date"]');
            if (startDate) {
                endDateInput.val(startDate);
                if (endDateInput.val() < startDate) {
                    endDateInput.val(startDate);
                }
            }
        });

        $('#addTaskForm').on('change', 'input[name="end_date"]', function() {
            const endDate = $(this).val();
            const startDateInput = $('#addTaskForm input[name="start_date"]');
            const endDateInput = $('#addTaskForm input[name="end_date"]');
            if (endDate && startDateInput.val() > endDate) {
                alert('Tanggal selesai harus lebih besar atau sama dari tanggal mulai.');
                endDateInput.val(startDateInput.val());
                endDateInput.trigger('change');
            }
        });
    });
</script>
@stop

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .info-box {
        border-radius: .25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125);
    }
    .card-outline {
        border-top: 3px solid #007bff;
    }
    .card-primary.card-outline {
        border-top-color: #007bff;
    }
    .card-success.card-outline {
        border-top-color: #28a745;
    }
    .card-info.card-outline {
        border-top-color: #17a2b8;
    }
    .accordion .card-header {
        padding: 0;
    }
    .accordion .btn-link {
        text-decoration: none;
        padding: 15px 20px;
    }
    .widget-user .widget-user-header {
        height: 120px;
        padding: 1rem;
        border-top-left-radius: .25rem;
        border-top-right-radius: .25rem;
    }
    .widget-user .widget-user-image {
        position: absolute;
        top: 85px;
        left: 50%;
        margin-left: -45px;
    }
    .widget-user img {
        width: 90px;
        height: 90px;
        border: 3px solid #fff;
    }
</style>
<style>
.participant-card {
    border-radius: 10px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}

.participant-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    z-index: 10;
}

.avatar-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: bold;
    color: white;
    flex-shrink: 0;
}

.border-top-3 {
    border-top-width: 3px !important;
}
</style>
<style>
        body 
        {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            border-radius: 5px;
        }
        .select2-selection__rendered 
        {
            line-height: 31px !important;
        }
        .select2-container .select2-selection--single 
        {
            height: 35px !important;
        }
        .select2-selection__arrow {
            height: 34px !important;
        }
        .select2-selection__choice
    {
        background-color: #007bff !important;
        border: 1px solid #007bff !important;
    }

    .select2-selection__choice__remove
    {
        color: #fe0700 !important;
        border: 1px solid #007bff !important;
    }
</style>

@stop