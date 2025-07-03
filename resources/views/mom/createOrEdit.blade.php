@extends('adminlte::page')
@section('content')
@include('components.alert')
    <div class="row">
        <div class="col-md-12 mx-auto mt-4">
            <form id="momForm" action="{{ route('mom.store') }}" method="POST">
                @csrf
                <!-- MoM Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-info-circle me-2"></i>Informasi Minutes of Meeting
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="mom_date" class="form-label">
                                    <i class="far fa-calendar-alt"></i>Tanggal
                                </label>
                                <input type="date" class="form-control" id="mom_date" name="mom_date" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="name" class="form-label">
                                    <i class="fas fa-heading"></i>Nama
                                </label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama MoM" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="project_id" class="form-label">
                                    <i class="fas fa-project-diagram"></i>Project
                                </label>
                                <select class="form-select select2" id="project_id" name="project_id" onchange="loadMeetingsFromProject(this)">
                                    <option value="" disabled selected>Pilih project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" data-meetings='@json($project->meetings_json)'>
                                            {{ $project->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="meeting_id" class="form-label">
                                    <i class="fas fa-users"></i>Meeting
                                </label>
                                <select class="form-select select2" id="meeting_id" name="meeting_id" onchange="showMeetingInfo()">
                                    <option value="">-- Pilih Meeting --</option>
                                    @foreach($meetings as $meeting)
                                        <option value="{{ $meeting->id }}" data-participants='@json($meeting->participantRelasion->map(fn($p) => ["id" => $p->id, "name" => $p->name]))'>
                                            {{ $meeting->meeting_name }}
                                        </option>
                                    @endforeach

                                </select>
                                <div id="meetingLoading" class="mt-3 text-white bg-primary p-3 rounded" style="display: none;">
                                    <span class="loading-spinner"></span> Memuat data meeting...
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <label class="form-label">
                                    <i class="fas fa-user-friends"></i>Peserta Meeting
                                    <span id="participantCount" class="participant-count">0</span>
                                </label>
                                <div id="participantContainer" class="d-flex flex-wrap">
                                    <div class="empty-meeting w-100">Pilih meeting untuk melihat peserta</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3" id="description_notes_div">
                            <label for="notes" class="form-label">
                                <i class="fas fa-sticky-note"></i>Catatan Umum
                            </label>
                            <input class="thriveEditor form-control" id="description_notes" data-ids="notes" name="notes" placeholder="yang akan dicetak di perjanjian"/>
                        </div>
                    </div>
                </div>
    
                <!-- Agenda List -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-list-ul me-2"></i>Agenda
                        </div>
                        <button type="button" class="btn btn-sm btn-success ml-auto" onclick="addAgenda()">
                            <i class="fas fa-plus me-1"></i>Tambah Agenda
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="agendaList" class="agenda-container">
                            <div class="empty-state">
                                <i class="fas fa-clipboard-list"></i>
                                <h5 class="mt-3">Belum ada agenda</h5>
                                <p class="mb-0">Klik "Tambah Agenda" untuk menambahkan agenda pertama</p>
                            </div>
                        </div>
                    </div>
                </div>
    
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary" onclick="loadDraft()">
                            <i class="fas fa-file-download me-1"></i>Muat Draft
                        </button>
                        <button type="button" class="btn btn-warning" onclick="clearDraft()">
                            <i class="fas fa-trash-alt me-1"></i>Hapus Draft
                        </button>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Preview -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>Preview MoM</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="previewContent" class="preview-content"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" onclick="printPreview()">
                        <i class="fas fa-print me-1"></i>Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Draft Indicator -->
    <div class="draft-indicator" id="draftIndicator">
        <i class="fas fa-save"></i>
        <span>Draft berhasil disimpan!</span>
    </div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
    $(document).ready(function () {
        // Initialize Select2 on form-select elements
        document.addEventListener('change', function(e) {
            if (e.target.id.startsWith('taskStartDate-')) {
                const agendaIndex = e.target.id.split('-')[1];
                const endDateInput = document.getElementById(`taskEndDate-${agendaIndex}`);
                if (e.target.value && !endDateInput.value) {
                    endDateInput.value = e.target.value;
                }
            }
        });

        $('.select2').select2({
            width: '100%',
            placeholder: '-- Pilih --',
            allowClear: true
        });
    });
</script>
<script>
    // Global variables
    let agendaIndex = 0;
    let taskCounter = {}; // Untuk melacak jumlah task per agenda
    let sortableAgendas;
    let internalUsers = @json($users);
    let objectives = @json($objectives);

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize task counter
        taskCounter = {};
        
        // Initialize drag and drop for agendas
        const agendaList = document.getElementById('agendaList');
        sortableAgendas = new Sortable(agendaList, {
            handle: '.agenda-handle',
            animation: 250,
            ghostClass: 'drag-over',
            onEnd: saveDraft
        });
        
        // Set today as default date
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('mom_date').value = today;
        
        // Load draft if exists
        loadDraft();
        
        // Auto-save draft on input
        document.getElementById('momForm').addEventListener('input', () => {
            saveDraft();
            showDraftIndicator();
        });
    });

    function addAgenda(data = null) {
        const index = agendaIndex++;
        const agendaList = document.getElementById('agendaList');
        
        // Inisialisasi counter untuk agenda ini
        // PERBAIKAN 1: Inisialisasi taskCounter dengan benar
        taskCounter[index] = data?.tasks?.length || 0;
        
        // Remove empty state if it's the first agenda
        if (agendaList.querySelector('.empty-state')) {
            agendaList.innerHTML = '';
        }
        
        const agendaHtml = `
        <div class="agenda-item" data-index="${index}">
            <div class="agenda-header">
                <div class="d-flex align-items-center">
                    <div class="agenda-handle">
                        <i class="fas fa-grip-vertical"></i>
                    </div>
                    <span class="fw-bold">Agenda</span>
                </div>
                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteAgenda(this)">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Judul Agenda</label>
                    <input type="text" name="agendas[${index}][title]" class="form-control" 
                        placeholder="Masukkan judul agenda" value="${data?.title || ''}" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Catatan Diskusi</label>
                    <input class="thriveEditor form-control" id="description_${index}_discussion_notes" data-ids="${index}_discussion_notes" name="agendas[${index}][discussion_notes]"  placeholder="yang akan dicetak di perjanjian"/>
                </div>
                
                <!-- TASK SECTION BARU -->
                <div class="task-list-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Task</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addTaskBtn-${index}" onclick="showTaskForm(${index})">
                            <i class="fas fa-plus me-1"></i>Tambah Task
                        </button>
                    </div>
                    
                    <!-- Task Form -->
                    <div class="task-form mb-4" id="taskForm-${index}" style="display: none;">
                        <h5 id="taskFormTitle-${index}" class="mb-4">Tambah Task Baru</h5>
                        
                        <div class="mb-3">
                            <label class="form-label">Judul Task</label>
                            <input type="text" id="taskTitle-${index}" class="form-control" placeholder="Masukkan judul task">
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" id="taskStartDate-${index}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Selesai</label>
                                <input type="date" id="taskEndDate-${index}" class="form-control">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Penanggung Jawab</label>
                            <select id="taskResponsible-${index}" class="form-select select2">
                                <option value="">-- Pilih User Internal --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- MODIFIKASI BAGIAN INI -->
                        <div id="internal-fields-${index}" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Objective</label>
                                <select id="taskObjective-${index}" class="form-select objective-select select2" 
                                        onchange="loadKeyResults(this, ${index})">
                                    <option value="" disabled selected>-- Pilih Objective --</option>
                                    @foreach($objectives as $objective)
                                        <option value="{{ $objective->id }}">{{ ucfirst($objective->name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="keyresult-fields-container-${index}"></div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" onclick="hideTaskForm(${index})">Batal</button>
                            <button type="button" id="saveTaskBtn-${index}" class="btn btn-primary" onclick="addTaskToList(${index})">Tambah Task</button>
                        </div>
                    </div>
                    
                    <!-- Task List -->
                    <ul id="taskList-${index}" class="task-list">
                        ${data?.tasks ? renderTasks(data.tasks, index) : `
                        <div class="alert alert-light border text-center py-4">
                            <i class="fas fa-info-circle me-2"></i>Belum ada task untuk agenda ini
                        </div>`}
                    </ul>
                </div>
            </div>
        </div>`;
        console.log(data);
        
        agendaList.insertAdjacentHTML('beforeend', agendaHtml);

        initializeSelect2();
        generateThriveEditor(`${index}_discussion_notes`,data?.discussion_notes || '');
        
        
        // Initialize select2 for responsible select

        
        // Tambahkan event listener untuk responsible select
        $(`#taskResponsible-${index}`).on('change', function() {
            const internalFields = document.getElementById(`internal-fields-${index}`);
            
            if (this.value) {
                // Jika user internal dipilih, tampilkan bagian Objective & Key Result
                internalFields.style.display = 'block';
                
                // Buat field Objective menjadi required
                $(`#taskObjective-${index}`).prop('required', true);
            } else {
                // Jika tidak, sembunyikan dan hapus required
                internalFields.style.display = 'none';
                $(`#taskObjective-${index}`).prop('required', false);
                
                // Reset nilai
                $(`#taskObjective-${index}`).val(null).trigger('change');
                $(`#keyresult-fields-container-${index}`).html('');
            }
        });
        
        saveDraft();
    }

    function renderTasks(tasks, agendaIndex) 
    {
        if (!tasks || tasks.length === 0) {
            return `<div class="alert alert-light border text-center py-4">
                        <i class="fas fa-info-circle me-2"></i>Belum ada task untuk agenda ini
                    </div>`;
        }

        // Reset task counter untuk agenda ini
        taskCounter[agendaIndex] = tasks.length;
        
        let tasksHtml = '';
        
        // PERBAIKAN: Gunakan indeks berurutan mulai dari 0
        tasks.forEach((task, taskIndex) => {
            const userName = getUserName(task.user_id);
            const objectiveName = task.objective_id ? getObjectiveName(task.objective_id) : '';
            
            let keyResultsHtml = '';
            if (task.key_result_ids && task.key_result_ids.length > 0) {
                task.key_result_ids.forEach(krId => {
                    keyResultsHtml += `
                        <input type="hidden" name="agendas[${agendaIndex}][tasks][${taskIndex}][key_result_ids][]" value="${krId}">
                    `;
                });
            }
            
            tasksHtml += `
                <li class="task-item">
                    <div class="task-header">
                        <h5 class="task-title">${task.title}</h5>
                        <div class="task-actions">
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                    onclick="editTask(this, ${agendaIndex})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="deleteTask(this, ${agendaIndex})">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="task-details">
                        ${task.start_date ? `<div><i class="far fa-calendar-alt me-2"></i> Mulai: ${task.start_date}</div>` : ''}
                        ${task.end_date ? `<div><i class="far fa-calendar-check me-2"></i> Selesai: ${task.end_date}</div>` : ''}
                        ${task.user_id ? `<div><i class="fas fa-user-tie me-2"></i> PJ: ${userName}</div>` : ''}
                        ${task.external_email ? `<div><i class="fas fa-envelope me-2"></i> Email: ${task.external_email}</div>` : ''}
                        ${task.objective_id ? `<div><i class="fas fa-bullseye me-2"></i> Objective: ${objectiveName}</div>` : ''}
                    </div>
                    <input type="hidden" name="agendas[${agendaIndex}][tasks][${taskIndex}][title]" value="${task.title}">
                    <input type="hidden" name="agendas[${agendaIndex}][tasks][${taskIndex}][start_date]" value="${task.start_date}">
                    <input type="hidden" name="agendas[${agendaIndex}][tasks][${taskIndex}][end_date]" value="${task.end_date}">
                    <input type="hidden" name="agendas[${agendaIndex}][tasks][${taskIndex}][user_id]" value="${task.user_id}">
                    <input type="hidden" name="agendas[${agendaIndex}][tasks][${taskIndex}][external_email]" value="${task.external_email}">
                    <input type="hidden" name="agendas[${agendaIndex}][tasks][${taskIndex}][objective_id]" value="${task.objective_id || ''}">
                    ${keyResultsHtml}
                </li>
            `;
        });
        
        return tasksHtml;
    }

    function getObjectiveName(objectiveId) {
        if (!objectiveId) return '';
        const objective = objectives.find(o => o.id == objectiveId);
        return objective ? objective.name : 'Objective tidak ditemukan';
    }

    function getUserName(userId) {
        if (!userId) return 'Belum ditentukan';
        const user = internalUsers.find(u => u.id == userId);
        return user ? user.name : 'Belum ditentukan';
    }

    function showTaskForm(agendaIndex) {
        const taskForm = document.getElementById(`taskForm-${agendaIndex}`);
        const addTaskBtn = document.getElementById(`addTaskBtn-${agendaIndex}`);
        const startDateInput = document.getElementById(`taskStartDate-${agendaIndex}`);
        const endDateInput = document.getElementById(`taskEndDate-${agendaIndex}`);

        startDateInput.addEventListener('change', function() {
            if (!endDateInput.value) {
                endDateInput.value = this.value;
            }
        })
        taskForm.style.display = 'block';
        addTaskBtn.style.display = 'none';
    }

    function hideTaskForm(agendaIndex) {
        const taskForm = document.getElementById(`taskForm-${agendaIndex}`);
        const addTaskBtn = document.getElementById(`addTaskBtn-${agendaIndex}`);
        taskForm.style.display = 'none';
        addTaskBtn.style.display = 'block';
        
        // Reset form
        document.getElementById(`taskTitle-${agendaIndex}`).value = '';
        document.getElementById(`taskStartDate-${agendaIndex}`).value = '';
        document.getElementById(`taskEndDate-${agendaIndex}`).value = '';
        $(`#taskResponsible-${agendaIndex}`).val(null).trigger('change');
        $(`#taskObjective-${agendaIndex}`).val(null).trigger('change');
        document.getElementById(`keyresult-fields-container-${agendaIndex}`).innerHTML = '';
        
        // Reset form title and button
        document.getElementById(`taskFormTitle-${agendaIndex}`).textContent = 'Tambah Task Baru';
        document.getElementById(`saveTaskBtn-${agendaIndex}`).textContent = 'Tambah Task';
    }

    function addTaskToList(agendaIndex) 
    {
        const title = document.getElementById(`taskTitle-${agendaIndex}`).value;
        const startDate = document.getElementById(`taskStartDate-${agendaIndex}`).value;
        const endDate = document.getElementById(`taskEndDate-${agendaIndex}`).value;
        const responsible = document.getElementById(`taskResponsible-${agendaIndex}`).value;
        const externalEmail = "";
        
        if (!title) {
            alert('Judul task harus diisi!');
            return;
        }
        
        // Validasi untuk task internal
        let objective = '';
        let keyResults = [];
        
        if (responsible) {
            objective = document.getElementById(`taskObjective-${agendaIndex}`).value;
            
            if (!objective) {
                alert('Objective harus dipilih untuk task internal!');
                return;
            }
            
            const keyResultContainer = document.getElementById(`keyresult-fields-container-${agendaIndex}`);
            const keyResultSelect = keyResultContainer.querySelector('select.keyresult-select');
            
            if (keyResultSelect) {
                const selectedOptions = Array.from(keyResultSelect.selectedOptions);
                
                if (selectedOptions.length === 0) {
                    alert('Pilih minimal satu Key Result untuk task internal!');
                    return;
                }
                
                keyResults = selectedOptions.map(option => option.value);
            } else {
                alert('Key Result tidak tersedia untuk objective ini!');
                return;
            }
        }
        
        const taskList = document.getElementById(`taskList-${agendaIndex}`);
        
        // Remove placeholder if exists
        if (taskList.querySelector('.alert')) {
            taskList.innerHTML = '';
        }
        
        const userName = $(`#taskResponsible-${agendaIndex} option:selected`).text() || 'Belum ditentukan';
        const objectiveName = objective ? getObjectiveName(objective) : '';
        
        // PERBAIKAN: Gunakan indeks berdasarkan jumlah task yang ada
        const taskIndex = taskList.querySelectorAll('.task-item').length;
        
        // PERBAIKAN: Simpan semua key result
        let keyResultsHtml = '';
        keyResults.forEach(krId => {
            keyResultsHtml += `
                <input type="hidden" name="agendas[${agendaIndex}][tasks][${taskIndex}][key_result_ids][]" value="${krId}">
            `;
        });
        
        const taskHtml = `
            <li class="task-item">
                <div class="task-header">
                    <h5 class="task-title">${title}</h5>
                    <div class="task-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="editTask(this, ${agendaIndex})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="deleteTask(this, ${agendaIndex})">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="task-details">
                    ${startDate ? `<div><i class="far fa-calendar-alt me-2"></i> Mulai: ${startDate}</div>` : ''}
                    ${endDate ? `<div><i class="far fa-calendar-check me-2"></i> Selesai: ${endDate}</div>` : ''}
                    ${responsible ? `<div><i class="fas fa-user-tie me-2"></i> PJ: ${userName}</div>` : ''}
                    ${externalEmail ? `<div><i class="fas fa-envelope me-2"></i> Email: ${externalEmail}</div>` : ''}
                    ${objective ? `<div><i class="fas fa-bullseye me-2"></i> Objective: ${objectiveName}</div>` : ''}
                </div>
                <input type="hidden" name="agendas[${agendaIndex}][tasks][${taskIndex}][title]" value="${title}">
                <input type="hidden" name="agendas[${agendaIndex}][tasks][${taskIndex}][start_date]" value="${startDate}">
                <input type="hidden" name="agendas[${agendaIndex}][tasks][${taskIndex}][end_date]" value="${endDate}">
                <input type="hidden" name="agendas[${agendaIndex}][tasks][${taskIndex}][user_id]" value="${responsible}">
                <input type="hidden" name="agendas[${agendaIndex}][tasks][${taskIndex}][external_email]" value="${externalEmail}">
                <input type="hidden" name="agendas[${agendaIndex}][tasks][${taskIndex}][objective_id]" value="${objective}">
                ${keyResultsHtml}
            </li>
        `;
        
        taskList.insertAdjacentHTML('beforeend', taskHtml);
        hideTaskForm(agendaIndex);
        saveDraft();
    }

    function editTask(btn, agendaIndex) {
        const taskItem = btn.closest('.task-item');
        const title = taskItem.querySelector('.task-title').textContent;
        
        // Get values from hidden inputs
        const hiddenInputs = taskItem.querySelectorAll('input[type="hidden"]');
        const taskData = {
            title: taskItem.querySelector('input[name$="[title]"]').value,
            start_date: taskItem.querySelector('input[name$="[start_date]"]').value,
            end_date: taskItem.querySelector('input[name$="[end_date]"]').value,
            user_id: taskItem.querySelector('input[name$="[user_id]"]').value,
            external_email: taskItem.querySelector('input[name$="[external_email]"]').value,
            objective_id: taskItem.querySelector('input[name$="[objective_id]"]')?.value || '',
            key_result_ids: []
        };
        
        // Collect key result IDs
        const keyResultInputs = taskItem.querySelectorAll('input[name$="[key_result_ids][]"]');
        keyResultInputs.forEach(input => {
            taskData.key_result_ids.push(input.value);
        });
        
        // Fill the form
        document.getElementById(`taskTitle-${agendaIndex}`).value = taskData.title;
        document.getElementById(`taskStartDate-${agendaIndex}`).value = taskData.start_date || '';
        document.getElementById(`taskEndDate-${agendaIndex}`).value = taskData.end_date || '';
        $(`#taskResponsible-${agendaIndex}`).val(taskData.user_id).trigger('change');
        
        // Handle objective and key results
        const internalFields = document.getElementById(`internal-fields-${agendaIndex}`);
        
        if (taskData.user_id) {
            internalFields.style.display = 'block';
            
            if (taskData.objective_id) {
                $(`#taskObjective-${agendaIndex}`).val(taskData.objective_id).trigger('change');
                
                // Load key results after a delay to allow AJAX to complete
                setTimeout(() => {
                    const keyResultContainer = document.getElementById(`keyresult-fields-container-${agendaIndex}`);
                    const keyResultSelect = keyResultContainer.querySelector('.keyresult-select');
                    
                    if (keyResultSelect) {
                        $(keyResultSelect).val(taskData.key_result_ids).trigger('change');
                    }
                }, 500);
            }
        } else {
            internalFields.style.display = 'none';
        }
        
        // Change form title and button
        document.getElementById(`taskFormTitle-${agendaIndex}`).textContent = 'Edit Task';
        document.getElementById(`saveTaskBtn-${agendaIndex}`).textContent = 'Update Task';
        
        // Show the form
        showTaskForm(agendaIndex);
        
        // Remove the old task item
        taskItem.remove();
        
        // Save draft after removal
        saveDraft();
    }

    function deleteTask(btn, agendaIndex) {
        if (confirm('Apakah Anda yakin ingin menghapus task ini?')) {
            const taskItem = btn.closest('.task-item');
            taskItem.remove();
            
            const taskList = document.getElementById(`taskList-${agendaIndex}`);
            if (taskList.children.length === 0) {
                taskList.innerHTML = `
                <div class="alert alert-light border text-center py-4">
                    <i class="fas fa-info-circle me-2"></i>Belum ada task untuk agenda ini
                </div>`;
            }
            
            saveDraft();
        }
    }

    function loadMeetingsFromProject(select) {
        const meetingSelect = document.getElementById('meeting_id');
        const participantContainer = document.getElementById('participantContainer');
        const meetingLoading = document.getElementById('meetingLoading');
        
        // Show loading indicator
        meetingLoading.style.display = 'block';
        meetingSelect.disabled = true;
        participantContainer.innerHTML = '<div class="empty-meeting">Memuat data meeting...</div>';
        document.getElementById('participantCount').textContent = '0';
        
        // Simulate loading delay
        setTimeout(() => {
            const selectedOption = select.options[select.selectedIndex];
            const meetings = JSON.parse(selectedOption.getAttribute('data-meetings') || '[]');
            
            // Clear and repopulate meeting select
            meetingSelect.innerHTML = '<option value="">-- Pilih Meeting --</option>';
            
            if (meetings.length === 0) {
                meetingSelect.innerHTML += '<option value="" disabled>Tidak ada meeting untuk project ini</option>';
            } else {
                meetings.forEach(meeting => {
                    const option = document.createElement('option');
                    option.value = meeting.id;
                    option.textContent = meeting.meeting_name;
                    option.dataset.participants = JSON.stringify(meeting.participants || []);
                    meetingSelect.appendChild(option);
                });
            }
            
            // Hide loading indicator
            meetingLoading.style.display = 'none';
            meetingSelect.disabled = false;
            
            // Clear participant container
            participantContainer.innerHTML = '<div class="empty-meeting">Pilih meeting untuk melihat peserta</div>';
            document.getElementById('participantCount').textContent = '0';
            
            // If only one meeting, select it automatically
            if (meetings.length === 1) {
                meetingSelect.value = meetings[0].id;
                showMeetingInfo();
            }
        }, 800);
    }

    function initializeSelect2() 
    {
        $('.select3').select2({
            placeholder: 'Pilih',
            allowClear: true,
            width: '100%',            
        });

        $('.select2').select2({
            placeholder: 'Pilih',
            allowClear: true,
            width: '100%',            
        });

        $('.category-select3').select2();

        $('.attachment-input').on('change', function() {
            validateAttachments(this);
        });
    }

    function showMeetingInfo() {
        const meetingSelect = document.getElementById('meeting_id');
        const selectedOption = meetingSelect.options[meetingSelect.selectedIndex];
        const participantContainer = document.getElementById('participantContainer');
        
        if (meetingSelect.value && selectedOption.dataset.participants) {
            const participants = JSON.parse(selectedOption.dataset.participants || '[]');
            
            if (participants.length > 0) {
                let participantsHtml = '';
                participants.forEach(participant => {
                    participantsHtml += `
                    <span class="participant-badge">
                        <i class="fas fa-user"></i>${participant.name}
                    </span>`;
                });
                participantContainer.innerHTML = participantsHtml;
                document.getElementById('participantCount').textContent = participants.length;
            } else {
                participantContainer.innerHTML = '<div class="empty-meeting">Tidak ada peserta yang terdaftar untuk meeting ini</div>';
                document.getElementById('participantCount').textContent = '0';
            }
        } else {
            participantContainer.innerHTML = '<div class="empty-meeting">Pilih meeting untuk melihat peserta</div>';
            document.getElementById('participantCount').textContent = '0';
        }
    }

    function confirmDeleteAgenda(btn) {
        if (confirm('Apakah Anda yakin ingin menghapus agenda ini? Semua task terkait juga akan dihapus.')) {
            btn.closest('.agenda-item').remove();
            saveDraft();
            
            // Show empty state if no agendas left
            const agendaList = document.getElementById('agendaList');
            if (agendaList.children.length === 0) {
                agendaList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h5 class="mt-3">Belum ada agenda</h5>
                    <p class="mb-0">Klik "Tambah Agenda" untuk menambahkan agenda pertama</p>
                </div>`;
            }
        }
    }

    function previewMom() {
        const formData = new FormData(document.getElementById('momForm'));
        const projectSelect = document.getElementById('project_id');
        const meetingSelect = document.getElementById('meeting_id');
        
        if (!projectSelect.value) {
            alert('Pilih project terlebih dahulu!');
            return;
        }
        
        const projectName = projectSelect.options[projectSelect.selectedIndex].text;
        const meetingName = meetingSelect.value ? 
            meetingSelect.options[meetingSelect.selectedIndex].text : 'Tidak ada meeting terkait';
        
        // Get participants
        let participants = [];
        if (meetingSelect.value) {
            participants = JSON.parse(meetingSelect.options[meetingSelect.selectedIndex].dataset.participants || '[]');
        }
        
        let agendaHtml = '';
        const agendaItems = document.querySelectorAll('.agenda-item');
        
        agendaItems.forEach((item, index) => {
            const title = item.querySelector('input[name*="[title]"]').value;
            const notes = item.querySelector('textarea').value;
            const taskList = item.querySelector('.task-list');
            
            let taskHtml = '';
            if (taskList && taskList.children.length > 0) {
                taskList.querySelectorAll('.task-item').forEach(task => {
                    const taskTitle = task.querySelector('.task-title').textContent;
                    const taskDetails = task.querySelector('.task-details').innerHTML;
                    
                    taskHtml += `
                    <li class="list-group-item mb-2 border rounded p-3">
                        <div class="fw-bold">${taskTitle}</div>
                        <div class="task-details mt-2">
                            ${taskDetails}
                        </div>
                    </li>`;
                });
            } else {
                taskHtml = '<li class="list-group-item text-center py-4 text-muted">Tidak ada task untuk agenda ini</li>';
            }
            
            agendaHtml += `
            <div class="preview-section">
                <h5><span class="agenda-number">${index + 1}</span> ${title || 'Agenda Tanpa Judul'}</h5>
                <p class="text-muted mt-3">${notes || 'Tidak ada catatan diskusi'}</p>
                
                <div class="mt-4">
                    <h6><i class="fas fa-tasks me-2"></i>Task:</h6>
                    <ul class="list-group mt-3">
                        ${taskHtml}
                    </ul>
                </div>
            </div>`;
        });
        
        const previewContent = `
        <div class="preview-section">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Minutes of Meeting</h3>
                <div class="text-muted">${formData.get('mom_date') || 'Tanggal tidak ditentukan'}</div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <div class="section-icon">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Project</h5>
                            <p class="mb-0">${projectName}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center mb-3">
                        <div class="section-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Meeting</h5>
                            <p class="mb-0">${meetingName}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <div class="d-flex align-items-start">
                    <div class="section-icon">
                        <i class="fas fa-sticky-note"></i>
                    </div>
                    <div>
                        <h5 class="mb-2">Catatan Umum</h5>
                        <p class="mb-0">${formData.get('notes') || 'Tidak ada catatan umum'}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="preview-section">
            <div class="d-flex align-items-center mb-4">
                <div class="section-icon">
                    <i class="fas fa-list-ul"></i>
                </div>
                <h4 class="mb-0">Agenda</h4>
            </div>
            
            ${agendaItems.length > 0 ? agendaHtml : `
            <div class="text-center py-5 bg-light rounded">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Tidak ada agenda</h5>
            </div>`}
        </div>
        
        <div class="preview-section">
            <div class="d-flex align-items-center mb-4">
                <div class="section-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <h4 class="mb-0">Peserta</h4>
            </div>
            
            <div class="d-flex flex-wrap gap-2 mt-3">
                ${participants.length > 0 ? 
                    participants.map(p => `<span class="participant-badge"><i class="fas fa-user"></i>${p.name}</span>`).join('') : 
                    '<div class="alert alert-light text-center w-100 py-4">Tidak ada peserta yang terdaftar</div>'}
            </div>
        </div>`;
        
        document.getElementById('previewContent').innerHTML = previewContent;
        
        // Show modal
        const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
        previewModal.show();
    }

    function saveDraft() {
        const formData = new FormData(document.getElementById('momForm'));
        const data = Object.fromEntries(formData.entries());
        
        // Get agendas data
        const agendas = [];
        document.querySelectorAll('.agenda-item').forEach(item => {
            const index = item.dataset.index;
            const title = item.querySelector(`input[name="agendas[${index}][title]"]`).value;
            const discussion_notes = item.querySelector(`input[name="agendas[${index}][discussion_notes]"]`).value;

            
            const tasks = [];

             // PERBAIKAN 4: Gunakan indeks berurutan untuk task
            let taskIdx = 0;
            item.querySelectorAll('.task-item').forEach(task => {
                console.log(task);
                
                const title = task.querySelector('.task-title').textContent;
                const start_date = task.querySelector(`input[name$="[start_date]"]`).value;
                const end_date = task.querySelector(`input[name$="[end_date]"]`).value;
                const user_id = task.querySelector(`input[name$="[user_id]"]`).value;
                const external_email = task.querySelector(`input[name$="[external_email]"]`).value;
                const objective_id = task.querySelector(`input[name$="[objective_id]"]`)?.value || '';
                
                const key_result_ids = [];
                task.querySelectorAll('input[name$="[key_result_ids][]"]').forEach(input => {
                    key_result_ids.push(input.value);
                });
                
                tasks.push({ 
                    title, 
                    start_date, 
                    end_date, 
                    user_id, 
                    external_email,
                    objective_id,
                    key_result_ids
                });
                
                taskIdx++;
            });
            
            agendas.push({ title, discussion_notes, tasks });
        });
        
        data.agendas = agendas;
        localStorage.setItem('mom_draft', JSON.stringify(data));
    }

    function loadDraft() {
        const saved = localStorage.getItem('mom_draft');
        const momDb = @json(@$mom) ?? null;
        taskCounter = {};
        
        if(momDb)
        {
            data = momDb;
            // Set form fields
            Object.entries(data).forEach(([key, val]) => {
                if (key !== 'agendas') {
                    const field = document.querySelector(`[name="${key}"]`);
                    if (field) field.value = val;
                }
            });
            
            
            const descriptionNoteDiv = document.getElementById('description_notes_div');
            if (descriptionNoteDiv) {
                descriptionNoteDiv.querySelectorAll('.ql-snow').forEach(element => {
                    element.remove();
                });
            }

            generateThriveEditor("notes",data.notes);

            // Clear existing agendas
            document.getElementById('agendaList').innerHTML = '';
            agendaIndex = 0;
    
            // PERBAIKAN 5: Reset taskCounter saat memuat draft
            taskCounter = {};

            
            // Add agendas from draft
            if (data.agendas && data.agendas.length > 0) {
                data.agendas.forEach(agenda => {
                    addAgenda(agenda);
                });
            } else {
                document.getElementById('agendaList').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h5 class="mt-3">Belum ada agenda</h5>
                    <p class="mb-0">Klik "Tambah Agenda" untuk menambahkan agenda pertama</p>
                </div>`;
            }
            
            // Update meeting info if needed
            const meetingSelect = document.getElementById('meeting_id');
            if (meetingSelect.value) {
                showMeetingInfo();
            }
            
            $('.select2').select2({
                width: '100%',
                placeholder: 'Pilih User Internal',
                allowClear: true
            });
            showDraftIndicator('Draft berhasil dimuat!');
        }
        else if (saved) 
        {
            data = JSON.parse(saved);
            
            // Set form fields
            Object.entries(data).forEach(([key, val]) => {
                if (key !== 'agendas') {
                    const field = document.querySelector(`[name="${key}"]`);
                    if (field) field.value = val;
                }
            });
            
            
            const descriptionNoteDiv = document.getElementById('description_notes_div');
            if (descriptionNoteDiv) {
                descriptionNoteDiv.querySelectorAll('.ql-snow').forEach(element => {
                    element.remove();
                });
            }

            generateThriveEditor("notes",data.notes);

            // Clear existing agendas
            document.getElementById('agendaList').innerHTML = '';
            agendaIndex = 0;
            
            // Add agendas from draft
            if (data.agendas && data.agendas.length > 0) {
                data.agendas.forEach(agenda => {
                    addAgenda(agenda);
                });
            } else {
                document.getElementById('agendaList').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h5 class="mt-3">Belum ada agenda</h5>
                    <p class="mb-0">Klik "Tambah Agenda" untuk menambahkan agenda pertama</p>
                </div>`;
            }
            
            // Update meeting info if needed
            const meetingSelect = document.getElementById('meeting_id');
            if (meetingSelect.value) {
                showMeetingInfo();
            }
            
            $('.select2').select2({
                width: '100%',
                allowClear: true,
                placeholder: 'Pilih User Internal'
            });
            showDraftIndicator('Draft berhasil dimuat!');
        } else {
            console.log('Tidak ada draft yang tersimpan.');
        }
    }

    function clearDraft(noConfirm = false) {
        if (!noConfirm && !confirm('Apakah Anda yakin ingin menghapus draft? Semua data yang belum disimpan akan hilang.')) {
            return;
        }
        
        localStorage.removeItem('mom_draft');
        document.getElementById('momForm').reset();
        document.getElementById('agendaList').innerHTML = `
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h5 class="mt-3">Belum ada agenda</h5>
            <p class="mb-0">Klik "Tambah Agenda" untuk menambahkan agenda pertama</p>
        </div>`;
        agendaIndex = 0;
        
        // Reset meeting info
        document.getElementById('participantContainer').innerHTML = '<div class="empty-meeting w-100">Pilih meeting untuk melihat peserta</div>';
        document.getElementById('participantCount').textContent = '0';
        
        showDraftIndicator('Draft berhasil dihapus!');
    }

    function showDraftIndicator(message = 'Draft berhasil disimpan!') {
        const indicator = document.getElementById('draftIndicator');
        indicator.querySelector('span').textContent = message;
        indicator.classList.add('show');
        
        setTimeout(() => {
            indicator.classList.remove('show');
        }, 3000);
    }

    function printPreview() {
        const printContent = document.getElementById('previewContent').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = `
        <div class="container my-4">
            ${printContent}
            <div class="text-center mt-4 text-muted">
                <small>Dicetak pada ${new Date().toLocaleString()}</small>
            </div>
        </div>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 1000px; margin: 0 auto; }
            .preview-section { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
            .agenda-number { background-color: #3c8dbc; color: white; width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 15px; }
            .section-icon { background-color: #f0f8ff; width: 40px; height: 40px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-right: 15px; color: #3c8dbc; }
            .participant-badge { background-color: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; padding: 8px 15px; border-radius: 20px; display: inline-block; margin: 5px; }
            .list-group-item { margin-bottom: 10px; border-radius: 8px; }
            @media print {
                .no-print { display: none; }
                body { padding: 20px; }
            }
        </style>`;
        
        window.print();
        document.body.innerHTML = originalContent;
        
        // Reinitialize modal
        const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
        previewModal.show();
    }

    function loadKeyResults(select, agendaIndex) {
        const objectiveId = select.value;
        const container = document.getElementById(`keyresult-fields-container-${agendaIndex}`);
        const responsible = document.getElementById(`taskResponsible-${agendaIndex}`).value;
        
        if (objectiveId && responsible) {
            // Tampilkan loading
            container.innerHTML = '<p class="text-center py-2">Memuat key results...</p>';
            
            // Kirim parameter is_required
            fetch(`/objective/getresult/${objectiveId}?index=${agendaIndex}&is_required=true`)
                .then(response => response.text())
                .then(data => {
                    container.innerHTML = data;
                    // Inisialisasi ulang select2
                    $(container).find('.select2').select2();
                    initializeSelect2ForContainer(agendaIndex);
                })
                .catch(error => {
                    container.innerHTML = '<p class="text-danger text-center py-2">Gagal memuat key results</p>';
                    console.error('Error:', error);
                });
        } else {
            container.innerHTML = '';
        }
    }

    function initializeSelect2() 
    {
        $('.select3').select2({
            placeholder: 'Pilih',
        });
        $('.category-select3').select2();

        $('.attachment-input').on('change', function() {
            validateAttachments(this);
        });
    }

    function initializeSelect2ForContainer(index) 
    {
        $('.select2-single-'+index+', .select2-multiple-'+index+'').select2({
            width: '100%' // Adjust width as needed
        });
    }

    // Form submission
    document.getElementById('momForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Simple validation
        const agendaItems = document.querySelectorAll('.agenda-item');
        if (agendaItems.length === 0) {
            alert('Minimal harus ada satu agenda!');
            return;
        }
        
        // Save data
        saveDraft();
        
        // Show success message
        // document.getElementById('momForm').submit();       
        const formData = new FormData(this);
        formData.append('_token', '{{ csrf_token() }}');
        
        $.ajax({
            url: "{{ route('mom.store') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                if (data.success) {
                    const toast = $(`
                        <div class="toast align-items-center position-fixed top-0 end-0 m-3 show" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="toast-header bg-success text-white">
                                <strong class="me-auto"><i class="fas fa-check-circle me-2"></i> Berhasil</strong>
                                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <div class="toast-body bg-white text-dark">Data MoM berhasil disimpan.</div>
                        </div>
                    `).appendTo('body');

                    toast.toast({ delay: 2000 }).toast('show');

                    clearDraft(true);
                    setTimeout(() => {
                        window.location.href = "{{ route('mom.index') }}";
                    }, 2000);
                } else {
                    alert('Gagal menyimpan data MOM!');
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
            }
        });
        
        
    });
</script>
@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        /* CSS untuk Task Form dan List */
        .task-list-container {
            margin-top: 20px;
        }
        
        .task-form {
            background-color: #eef7ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .task-list {
            list-style: none;
            padding: 0;
        }
        
        .task-item {
            background-color: #f8f9fa;
            border-left: 3px solid #3c8dbc;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .task-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .task-actions {
            display: flex;
            gap: 8px;
        }
        
        /* Existing styles */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 60px;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 7px 20px rgba(0,0,0,0.12);
        }
        
        .card-header {
            background: linear-gradient(to right, var(--primary-color), #4aa3df);
            color: white;
            font-weight: 600;
            padding: 15px 25px;
            border-radius: 0 !important;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .form-label {
            font-weight: 500;
            color: #444;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .participant-badge {
            background-color: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            margin: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .empty-meeting {
            color: #78909c;
            font-style: italic;
            padding: 15px;
            text-align: center;
            background-color: #f9f9f9;
            border-radius: 8px;
            border: 1px dashed #e0e0e0;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
            margin-right: 10px;
            vertical-align: middle;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .agenda-item {
            position: relative;
            background-color: white;
            margin-bottom: 25px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border-left: 4px solid var(--primary-color);
        }
        
        .agenda-header {
            background-color: #f8fafc;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .agenda-handle {
            cursor: move;
            color: #777;
            padding: 5px 15px;
            background-color: #f5f5f5;
            border-radius: 4px;
            margin-right: 10px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 30px;
            color: #78909c;
            background-color: #fafafa;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px dashed #e0e0e0;
        }
        
        .empty-state i {
            font-size: 3.5rem;
            margin-bottom: 20px;
            color: #cfd8dc;
        }
        
        .draft-indicator {
            position: fixed;
            bottom: 25px;
            right: 25px;
            background: linear-gradient(to right, var(--accent-color), #00a7d0);
            color: white;
            padding: 12px 20px;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 1000;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.4s ease;
            font-weight: 500;
        }
        
        .draft-indicator.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .section-icon {
            background-color: rgba(60, 141, 188, 0.12);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary-color);
            font-size: 1.1em;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 12px 18px;
            border: 1px solid #ddd;
            font-size: 1em;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(60, 141, 188, 0.25);
        }
        
        .action-buttons {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-top: 35px;
            flex-wrap: wrap;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .action-buttons .btn-group {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .drag-over {
            background-color: rgba(60, 141, 188, 0.15);
            border: 2px dashed var(--primary-color);
        }
        
        .preview-content {
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .preview-section {
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eee;
        }
        
        .agenda-number {
            background-color: var(--primary-color);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            font-size: 1.1em;
        }
        
        .participant-count {
            background-color: var(--accent-color);
            color: white;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 0.85em;
            margin-left: 8px;
        }
        
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }
            
            .action-buttons .btn-group {
                width: 100%;
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
        
        :root {
            --primary-color: #3c8dbc;
            --accent-color: #00c0ef;
        }
        .select2-container--default .select2-selection--single 
        {
            height: 38px !important;
            padding: 5px 10px !important;
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
@endsection