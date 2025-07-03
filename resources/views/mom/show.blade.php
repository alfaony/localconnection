@extends('adminlte::page')

@section('title', 'Detail Minutes of Meeting (MoM)')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Minutes of Meeting (MoM)</h1>
        <div>
            @canAccess('update','moms')
            <a href="{{ route('mom.edit', $mom->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endcanAccess
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
            
            @if($mom->notes)
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="callout callout-info">
                        <h5><i class="fas fa-sticky-note mr-2"></i>Meeting Notes</h5>
                        <div class="p-3 border rounded bg-light">{!! $mom->notes !!}</div>
                    </div>
                </div>
            </div>
            @endif
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
                @canAccess('storeTask','moms')
                <button type="button" class="btn btn-sm btn-success ml-2" data-toggle="modal" data-target="#addAgendaModal">
                    <i class="fas fa-plus mr-1"></i> Tambah Agenda
                </button>
                @endcanAccess
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
                         <div class="d-flex justify-content-between align-items-center card-body pb-0 pt-0">
                            <div>
                                @canAccess('storeTask','moms')
                                    <button 
                                        class="btn btn-sm btn-outline-primary mt-3"
                                        onclick="openAddTaskModal('{{ $agenda->id }}', '{{ $agenda->title }}')">
                                        <i class="fas fa-plus-circle mr-1"></i> Tambah Task
                                    </button>
                                @endcanAccess
                            </div>
                            
                            @if($agenda->is_delete)
                            <div class="d-flex ml-auto">
                                @canAccess('editTask','moms')
                                <button 
                                    class="btn btn-sm btn-outline-warning mt-3 ml-2"
                                    onclick="openEditAgendaModal({{ $agenda->id }}, '{{ $agenda->title }}', `{{ $agenda->discussion_notes }}`)">
                                    <i class="fas fa-edit mr-1"></i> Edit Agenda
                                </button>
                                @endcanAccess
                                @canAccess('deleteAgenda','moms')
                                
                                <form action="{{ route('mom.deleteAgenda', $agenda->id) }}" method="POST" class="ml-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger mt-3" onclick="return confirm('Yakin ingin menghapus agenda ini? Semua task di dalamnya juga akan terhapus.')">
                                        <i class="fas fa-trash mr-1"></i> Hapus Agenda
                                    </button>
                                </form>
                                @endcanAccess
                            </div>
                            @endif
                         </div>
                        <div class="card-body">
                            <div class="callout callout-info mb-4">
                                <h5><i class="fas fa-comments mr-2"></i>Discussion Notes</h5>
                                <div class="p-3 border rounded bg-light">{!! $agenda->discussion_notes !!}</div>
                            </div>

                            <!-- ... Bagian task tetap ... -->
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
                                                        @else
                                                            @if($task->token)
                                                                <button type="button" class="btn btn-sm btn-outline-secondary copy-link-btn" 
                                                                        onclick="copyLinkAndAlert(event, '{{ route('external.task.view', $task->token) }}')">
                                                                    <i class="fas fa-copy"></i> Copy Link
                                                                </button>
                                                            @else
                                                            <span class="text-muted">Not assigned</span>
                                                            @endif
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
                                                        @if($task->isAction())
                                                        <div class="task-actions d-flex gap-2">
                                                            @canAccess('editTask','moms')
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
                                                            @endcanAccess

                                                            @canAccess('deleteTask','moms')
                                                            <form action="{{ route('mom.deleteTask', $task->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Yakin ingin menghapus task ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger mt-1">
                                                                    <i class="fas fa-trash-alt me-1"></i>Hapus
                                                                </button>
                                                            </form>
                                                            @endcanAccess
                                                        </div>
                                                        @else
                                                        <span class="text-muted"><i class="fas fa-running mr-2"></i></span>
                                                        @endif
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
@canAccess('storeTask','moms')
<div class="modal fade" id="addTaskModal" tabindex="-1" role="dialog" aria-labelledby="addTaskModalLabel"
    aria-hidden="true">
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
                        <input class="thriveEditor form-control" id="description_description" data-ids="description"
                            name="description" placeholder="yang akan dicetak di perjanjian" />
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
                        <select name="user_id" id="taskResponsible" class="form-control selectUserTask2"
                            style="width: 100%;" onchange="toggleObjectiveFieldsAddTask(this.value)">
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
                            <select id="taskObjective" name="objective_id"
                                class="form-select objective-select selectObjectiveTask2" onchange="loadKeyResults(this)">
                                <option value="" disabled selected>-- Pilih Objective --</option>
                                @foreach($objectives as $objective)
                                <option value="{{ $objective->id }}">{{ ucfirst($objective->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="keyresult-fields-container"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                </div>
            </div> <!-- /.modal-content -->
        </form>
    </div> <!-- /.modal-dialog -->
</div> <!-- /.modal -->
@endcanAccess

<!-- Modal Edit Task -->
@canAccess('editTask','moms')
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
            <select name="user_id" id="editUserInternal" class="form-control selectUserUpdate2" style="width: 100%;">
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

          {{-- 
          <div class="form-group">
            <label>Email Eksternal (opsional)</label>
            <input type="email" name="external_email" id="editExternalEmail" class="form-control">
          </div>
          --}}
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Update Task</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endcanAccess

<!-- Modal Tambah Agenda -->
@canAccess('storeAgenda','moms')
<div class="modal fade" id="addAgendaModal" tabindex="-1" role="dialog" aria-labelledby="addAgendaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addAgendaModalLabel">Tambah Agenda Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addAgendaForm" action="{{ route('mom.storeAgenda', $mom->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="agendaTitle">Judul Agenda <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="agendaTitle" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="agendaNotes">Catatan Diskusi</label>
                        <input class="thriveEditor form-control" id="description_agendaNotes" data-ids="agendaNotes" name="discussion_notes" placeholder="Masukkan catatan diskusi"/>
                    </div>
                    
                    <!-- Task Section -->
                    <div class="task-section mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Task</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="showTaskFormInModal()">
                                <i class="fas fa-plus me-1"></i>Tambah Task
                            </button>
                        </div>
                        
                        <!-- Task Form -->
                        <div class="task-form mb-4" id="taskFormInModal" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Judul Task <span class="text-danger">*</span></label>
                                <input type="text" id="taskTitleInModal" class="form-control" 
                                        placeholder="Masukkan judul task" >
                            </div>
                            
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" id="taskStartDateInModal" class="form-control"  onchange="setEndDate(this.value)">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="date" id="taskEndDateInModal" class="form-control" >
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Penanggung Jawab</label>
                                <select id="taskUserInModal" class="form-control selectUserAgenda2" onchange="toggleObjectiveFields(this.value)">
                                    <option value="">-- Pilih User Internal --</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="objectiveFieldsContainer" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Objective</label>
                                    <select id="taskObjectiveInModal" class="form-control selectObjective2" onchange="loadKeyResultsForModal(this.value)">
                                        <option value="">-- Pilih Objective --</option>
                                        @foreach($objectives as $objective)
                                            <option value="{{ $objective->id }}">{{ $objective->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Container untuk Key Results -->
                                <div id="keyresultFieldsContainer"></div>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" 
                                        onclick="hideTaskFormInModal()"><i class="fas fa-times mr-1"></i> Batal</button>
                                <button type="button" class="btn btn-primary ml-1" 
                                        onclick="addTaskToListInModal()"><i class="fas fa-plus mr-1"></i> Task</button>
                            </div>
                        </div>
                        
                        <!-- Task List -->
                        <ul id="taskListInModal" class="task-list">
                            <li class="alert alert-light border text-center py-4 mb-0">
                                <i class="fas fa-info-circle me-2"></i>Belum ada task untuk agenda ini
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcanAccess

<!-- Modal Edit Agenda -->
@canAccess('updateAgenda','moms')
<div class="modal fade" id="editAgendaModal" tabindex="-1" role="dialog" aria-labelledby="editAgendaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAgendaModalLabel">Edit Agenda</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editAgendaForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editAgendaTitle">Judul Agenda</label>
                        <input type="text" class="form-control" id="editAgendaTitle" name="title" required>
                    </div>
                    <div class="form-group" id="description_editAgendaNotes_div">
                        <label for="editAgendaNotes">Catatan Diskusi</label>
                        <input class="thriveEditor form-control" id="description_editAgendaNotes" data-ids="editAgendaNotes" name="discussion_notes" placeholder="Masukkan catatan diskusi"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcanAccess

@stop
@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
    $(document).ready(function () {
        $('.selectUser2').select2({
            dropdownParent: $('#addAgendaModal'),
            placeholder: 'Pilih',
            allowClear: true,
            width: '100%',});
    });

    function initializeSelect2ForContainer(index) 
    {
        $('.select2-single-'+index+', .select2-multiple-'+index+'').select2({
            width: '100%' // Adjust width as needed
        });
    }
    // Fungsi untuk mengisi end_date sama dengan start_date
    function setEndDate(startDate) 
    {
        document.getElementById('taskEndDateInModal').value = startDate;
    }

    // Fungsi untuk modal tambah agenda dengan task
    function showTaskFormInModal() {
        document.getElementById('taskFormInModal').style.display = 'block';
    }

    function hideTaskFormInModal() {
        document.getElementById('taskFormInModal').style.display = 'none';
        // Reset form
        document.getElementById('taskTitleInModal').value = '';
        document.getElementById('taskStartDateInModal').value = '';
        document.getElementById('taskEndDateInModal').value = '';
        document.getElementById('taskUserInModal').value = '';
        document.getElementById('taskObjectiveInModal').value = '';
        document.getElementById('keyresultFieldsContainer').innerHTML = '';
    }

    // Fungsi untuk load key results dalam modal
    function loadKeyResultsForModal(objectiveId) {
        const container = document.getElementById('keyresultFieldsContainer');
        
        if (!objectiveId) {
            container.innerHTML = '';
            return;
        }
        
        // Tampilkan loading
        container.innerHTML = '<p>Memuat key results...</p>';
        
        let url = '{{ route('getresult','id') }}';
        url = url.replace('id', objectiveId);

        $.ajax({
            url : url,
            type: 'GET',
            data: { is_required: true },
            success: function(response) {
                container.innerHTML = response;
                // Inisialisasi select2
                initializeSelect2ForContainer(0);
            },
            error: function() {
                container.innerHTML = '<p class="text-danger">Gagal memuat key results</p>';
            }
        });
    }

    // Function untuk menambahkan task ke list di dalam modal
    function addTaskToListInModal() {
        const title = document.getElementById('taskTitleInModal').value;
        const startDate = document.getElementById('taskStartDateInModal').value;
        const endDate = document.getElementById('taskEndDateInModal').value;
        const userId = document.getElementById('taskUserInModal').value;
        const objectiveId = document.getElementById('taskObjectiveInModal').value;
        let keyResultIds = [];
        
        // Validasi
        if (!title) {
            alert('Judul task harus diisi!');
            return;
        }
        
        if (!startDate || !endDate) {
            alert('Tanggal mulai dan selesai harus diisi!');
            return;
        }
        
        // Ambil key result yang dipilih
        const keyResultSelect = document.getElementById('keyresultFieldsContainer').querySelector('select');
        if (keyResultSelect) {
            const selectedOptions = Array.from(keyResultSelect.selectedOptions);
            keyResultIds = selectedOptions.map(option => option.value);
        }
        
        const taskList = document.getElementById('taskListInModal');
        
        // Hapus placeholder jika ada
        if (taskList.querySelector('.alert')) {
            taskList.innerHTML = '';
        }

        const index = taskList.children.length;
        
        // Generate input hidden untuk task
        let taskInputs = '';
        taskInputs += `<input type="hidden" name="tasks[${index}][title]" value="${title}">`;
        taskInputs += `<input type="hidden" name="tasks[${index}][start_date]" value="${startDate}">`;
        taskInputs += `<input type="hidden" name="tasks[${index}][end_date]" value="${endDate}">`;
        taskInputs += `<input type="hidden" name="tasks[${index}][user_id]" value="${userId}">`;
        taskInputs += `<input type="hidden" name="tasks[${index}][objective_id]" value="${objectiveId}">`;
        keyResultIds.forEach(krId => {
            taskInputs += `<input type="hidden" name="tasks[${index}][key_result_ids][]" value="${krId}">`;
        });
        
        // Dapatkan nama user dan objective untuk ditampilkan
        const userName = $('#taskUserInModal option:selected').text() || 'Belum ditentukan';
        const objectiveName = $('#taskObjectiveInModal option:selected').text() || 'Belum dipilih';
        
        const taskHtml = `
            <li class="task-item mb-3 p-3 border rounded position-relative">
                <div class="task-header d-flex justify-content-between align-items-center">
                    <h5 class="task-title mb-0">${title}</h5>
                    <button type="button" class="btn btn-sm btn-outline-danger" 
                            onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
                <div class="task-details mt-2">
                    <div><i class="far fa-calendar-alt me-2"></i> Mulai: ${startDate}</div>
                    <div><i class="far fa-calendar-check me-2"></i> Selesai: ${endDate}</div>
                    <div><i class="fas fa-user-tie me-2"></i> PJ: ${userName}</div>
                    <div><i class="fas fa-bullseye me-2"></i> Objective: ${objectiveName}</div>
                </div>
                ${taskInputs}
            </li>
        `;
        
        taskList.insertAdjacentHTML('beforeend', taskHtml);
        hideTaskFormInModal();
    }

    function toggleObjectiveFields(userId) 
    {
     
        const objectiveFields = document.getElementById('objectiveFieldsContainer');
        if (userId) {
            objectiveFields.style.display = 'block';
        } else {
            objectiveFields.style.display = 'none';
            // Reset nilai objective dan key results
            document.getElementById('taskObjectiveInModal').value = '';
            document.getElementById('keyresultFieldsContainer').innerHTML = '';
        }
    }
</script>
<script>
    function toggleObjectiveFieldsAddTask(userId) 
    {
        const objectiveFields = document.getElementById('internal-fields');
        if (userId) {
            objectiveFields.style.display = 'block';
        } else {
            objectiveFields.style.display = 'none';
            // Reset nilai objective dan key results
            document.getElementById('taskObjectiveInModal').value = '';
            document.getElementById('keyresultFieldsContainer').innerHTML = '';
        }
    }
    // Fungsi untuk membuka modal tambah agenda
    function openAddAgendaModal() {
        $('#addAgendaModal').modal('show');
    }

    // Fungsi untuk membuka modal edit agenda
    function openEditAgendaModal(agendaId, title, notes) {
        // Set nilai form
        document.getElementById('editAgendaTitle').value = title;
        
        // Set action form
        const form = document.getElementById('editAgendaForm');
        form.action = "{{ route('mom.updateAgenda', ':id') }}".replace(':id', agendaId);
        
        // Generate editor dengan nilai notes
        const descriptionNoteDiv = document.getElementById('description_editAgendaNotes_div');
        if (descriptionNoteDiv) {
            descriptionNoteDiv.querySelectorAll('.ql-snow').forEach(element => {
                element.remove();
            });
        }
        generateThriveEditor("editAgendaNotes", notes);
        
        // Tampilkan modal
        
         const modalEditModal = new bootstrap.Modal(document.getElementById('editAgendaModal'));
        modalEditModal.show();
    }

    // Inisialisasi editor untuk modal tambah agenda
    document.addEventListener('DOMContentLoaded', function() {
        // Pastikan modal tambah agenda sudah ada
        if (document.getElementById('addAgendaModal')) {
            // Generate editor untuk catatan diskusi
            // generateThriveEditor("agendaNotes", '');
        }
    });
</script>
@canAccess('editTask','moms')
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
@endcanAccess

@canAccess('storeTask','moms')
<script>
    $(`#taskResponsible`).on('change', function() {
        console.log('User internal changed to:', this.value);
        
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
@endcanAccess
<script>
  $(document).ready(function() {
    $('.select2').select2(
      {
        placeholder: 'Pilih',
        allowClear: true,
        width: '100%'
      }
    );


    $('.selectObjectiveTask2').select2(
      {
        dropdownParent: $('#addTaskModal'),
        placeholder: 'Pilih',
        allowClear: true,
        width: '100%'
      }
    );

    $('.selectUserTask2').select2(
      {
        dropdownParent: $('#addTaskModal'),
        placeholder: 'Pilih',
        allowClear: true,
        width: '100%'
      }
    );

    $('.selectUserTask2').select2(
      {
        dropdownParent: $('#addTaskModal'),
        placeholder: 'Pilih',
        allowClear: true,
        width: '100%'
      }
    );

    $('.selectUserUpdate2').select2(
      {
        dropdownParent: $('#editTaskModal'),
        placeholder: 'Pilih',
        allowClear: true,
        width: '100%'
      }
    );

    $('.selectUserAgenda2').select2(
      {
        dropdownParent: $('#addAgendaModal'),
        placeholder: 'Pilih',
        allowClear: true,
        width: '100%'
      }
    );

    $('.selectObjective2').select2(
      {
        dropdownParent: $('#addAgendaModal'),
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

    function copyLinkAndAlert(event, link) 
    {
        event.preventDefault();

        // Gunakan Clipboard API
        navigator.clipboard.writeText(link)
            .then(() => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Link berhasil disalin!',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Gagal menyalin link!',
                });
            });
    }
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