@extends('adminlte::page')
@section('content')
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
                                <label for="project_id" class="form-label">
                                    <i class="fas fa-project-diagram"></i>Project
                                </label>
                                <select class="form-select" id="project_id" name="project_id" required onchange="loadMeetingsFromProject(this)">
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
                                <select class="form-select" id="meeting_id" name="meeting_id" onchange="showMeetingInfo()">
                                    <option value="">-- Pilih Meeting --</option>
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
                        <button type="button" class="btn btn-info" onclick="previewMom()">
                            <i class="fas fa-eye me-1"></i>Preview
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Simpan MoM
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
    // Global variables
    let agendaIndex = 0;
    let sortableAgendas;

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
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
                    <textarea name="agendas[${index}][discussion_notes]" class="form-control" 
                            rows="3" placeholder="Masukkan catatan diskusi">${data?.discussion_notes || ''}</textarea>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Task</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addTask(this, ${index})">
                        <i class="fas fa-plus me-1"></i>Tambah Task
                    </button>
                </div>
                
                <div class="task-container" id="taskContainer-${index}">
                    ${data?.tasks ? renderTasks(data.tasks, index) : `
                    <div class="alert alert-light border text-center py-4">
                        <i class="fas fa-info-circle me-2"></i>Belum ada task untuk agenda ini
                    </div>`}
                </div>
            </div>
        </div>`;
        
        agendaList.insertAdjacentHTML('beforeend', agendaHtml);
        saveDraft();
    }

    function renderTasks(tasks, agendaIndex) {
        if (!tasks || tasks.length === 0) {
            return `<div class="alert alert-light border text-center py-4">
                        <i class="fas fa-info-circle me-2"></i>Belum ada task untuk agenda ini
                    </div>`;
        }
        
        return tasks.map((task, taskIndex) => `
            <div class="task-item">
                <div class="mb-3">
                    <label class="form-label">Judul Task</label>
                    <input type="text" name="agendas[${agendaIndex}][tasks][${taskIndex}][title]" 
                        class="form-control" value="${task.title || ''}" placeholder="Judul task" required>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="agendas[${agendaIndex}][tasks][${taskIndex}][end_date]" 
                            class="form-control" value="${task.end_date || ''}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Penanggung Jawab</label>
                        <select name="agendas[${agendaIndex}][tasks][${taskIndex}][user_id]" class="form-select select2">
                         ${renderUserOptions(task.user_id)}
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email Eksternal (opsional)</label>
                    <input type="email" name="agendas[${agendaIndex}][tasks][${taskIndex}][external_email]" 
                        class="form-control" value="${task.external_email || ''}" 
                        placeholder="email@example.com">
                </div>
                
                <div class="task-actions">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.task-item').remove(); saveDraft();">
                        <i class="fas fa-trash-alt me-1"></i>Hapus Task
                    </button>
                </div>
            </div>
        `).join('');

        
        // Initialize select2
        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih User Internal'
        });
    }

    function renderUserOptions(selectedId = null) 
    {
        let html = '<option value="">-- Pilih User Internal --</option>';
        internalUsers = @json($users);
        internalUsers.forEach(user => {
            const selected = String(user.id) === String(selectedId) ? 'selected' : '';
            html += `<option value="${user.id}" ${selected}>${user.name}</option>`;
        });
        return html;
    }

    function addTask(btn, agendaIndex) 
    {
        const container = btn.previousElementSibling || btn.parentElement.nextElementSibling;
        const taskContainer = container.closest('.task-container') || container;
        const taskIndex = taskContainer.querySelectorAll('.task-item').length;
        
        if (taskContainer.querySelector('.alert')) {
            taskContainer.innerHTML = '';
        }
        
        const taskHtml = `
        <div class="task-item">
            <div class="mb-4">
                <label class="form-label">
                    <i class="fas fa-heading me-2"></i>Judul Task
                </label>
                <input type="text" name="agendas[${agendaIndex}][tasks][${taskIndex}][title]" 
                    class="form-control" placeholder="Masukkan judul task" required>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="far fa-calendar-alt me-2"></i>Tanggal Selesai
                    </label>
                    <input type="date" name="agendas[${agendaIndex}][tasks][${taskIndex}][end_date]" 
                        class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="fas fa-user-tie me-2"></i>Penanggung Jawab
                    </label>
                    <select name="agendas[${agendaIndex}][tasks][${taskIndex}][user_id]" class="form-select select2">
                        <option value="">-- Pilih User Internal --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} </option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <label class="form-label">
                    <i class="fas fa-envelope me-2"></i>Email Eksternal (opsional)
                </label>
                <input type="email" name="agendas[${agendaIndex}][tasks][${taskIndex}][external_email]" 
                    class="form-control" placeholder="client@perusahaan.com">
                <div class="form-text">Masukkan email jika task melibatkan pihak eksternal</div>
            </div>
            
            <div class="task-actions">
                <button type="button" class="btn btn-outline-danger" onclick="this.closest('.task-item').remove(); saveDraft();">
                    <i class="fas fa-trash-alt me-2"></i>Hapus Task
                </button>
            </div>
        </div>`;
        
        taskContainer.insertAdjacentHTML('beforeend', taskHtml);

        
        // Initialize select2
        $('.select2').select2({
            width: '100%',
            placeholder: 'Pilih User Internal'
        });
        saveDraft();
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
            const tasks = item.querySelectorAll('.task-item');
            
            let taskHtml = '';
            if (tasks.length > 0) {
                tasks.forEach(task => {
                    const taskTitle = task.querySelector('input[name*="[title]"]').value || 'Task tanpa judul';
                    const endDate = task.querySelector('input[name*="[end_date]"]').value || 'Belum ditentukan';
                    const user = task.querySelector('select[name*="[user_id]"]').options[task.querySelector('select[name*="[user_id]"]').selectedIndex].text || 'Belum ditentukan';
                    const email = task.querySelector('input[name*="[external_email]"]').value || 'Tidak ada';
                    
                    taskHtml += `
                    <li class="list-group-item mb-2 border rounded p-3">
                        <div class="fw-bold">${taskTitle}</div>
                        <div class="d-flex justify-content-between mt-2">
                            <div>
                                <i class="fas fa-calendar-day me-2"></i>
                                <span>Tanggal: ${endDate}</span>
                            </div>
                            <div>
                                <i class="fas fa-user me-2"></i>
                                <span>PJ: ${user}</span>
                            </div>
                        </div>
                        ${email ? `
                        <div class="mt-2">
                            <i class="fas fa-envelope me-2"></i>
                            <span>Email: ${email}</span>
                        </div>` : ''}
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
            const discussion_notes = item.querySelector(`textarea[name="agendas[${index}][discussion_notes]"]`).value;
            
            const tasks = [];
            item.querySelectorAll('.task-item').forEach(task => {
                const title = task.querySelector(`input[name$="[title]"]`).value;
                const end_date = task.querySelector(`input[name$="[end_date]"]`).value;
                const user_id = task.querySelector(`select[name$="[user_id]"]`).value;
                const external_email = task.querySelector(`input[name$="[external_email]"]`).value;
                
                tasks.push({ title, end_date, user_id, external_email });
            });
            
            agendas.push({ title, discussion_notes, tasks });
        });
        
        data.agendas = agendas;
        localStorage.setItem('mom_draft', JSON.stringify(data));
    }

    function loadDraft() {
        const saved = localStorage.getItem('mom_draft');
        const momDb = @json(@$mom) ?? null;
        
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
            
            // Add agendas from draft
            if (data.agendas && data.agendas.length > 0) {
                data.agendas.forEach(agenda => {
                    console.log(agenda);
                    
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
                placeholder: 'Pilih User Internal'
            });
            showDraftIndicator('Draft berhasil dimuat!');

            console.log("dari 1");
            
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
                placeholder: 'Pilih User Internal'
            });
            showDraftIndicator('Draft berhasil dimuat!');

            console.log("dari 2");
        } else {
            alert('Tidak ada draft yang tersimpan.');
        }
    }

    function clearDraft() {
        if (confirm('Apakah Anda yakin ingin menghapus draft? Semua data yang belum disimpan akan hilang.')) {
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
        document.getElementById('momForm').submit();
        
        // In a real app, you would submit the form to the server here
    });
</script>
@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3c8dbc;
            --secondary-color: #f4f4f4;
            --accent-color: #00c0ef;
            --danger-color: #dd4b39;
            --success-color: #00a65a;
            --warning-color: #f39c12;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 60px;
        }
        
        .header-container {
            background: linear-gradient(120deg, var(--primary-color), #2672a8);
            color: white;
            padding: 25px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
        
        .meeting-info {
            background-color: #f0f8ff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
            border-left: 4px solid var(--accent-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
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
        
        .participant-badge i {
            margin-right: 5px;
            font-size: 0.9em;
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
        
        .task-item {
            background-color: #f9f9f9;
            border-left: 3px solid var(--accent-color);
            margin-bottom: 15px;
            padding: 18px;
            border-radius: 8px;
            transition: all 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.03);
        }
        
        .task-item:hover {
            background-color: #f0f8ff;
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
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
        
        .btn-success {
            background: linear-gradient(to right, var(--success-color), #00c853);
            border: none;
        }
        
        .btn-info {
            background: linear-gradient(to right, var(--accent-color), #00bcd4);
            border: none;
        }
        
        .btn-warning {
            background: linear-gradient(to right, var(--warning-color), #ff9800);
            border: none;
            color: white;
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
    </style>
    <style>
        /* PERBAIKAN UTAMA PADA TASK ITEM */
        .task-item {
            background-color: #f9f9f9;
            border-left: 3px solid var(--accent-color);
            margin-bottom: 20px;
            padding: 25px;
            border-radius: 10px;
            transition: all 0.2s;
            box-shadow: 0 3px 8px rgba(0,0,0,0.05);
        }
        
        .task-item:hover {
            background-color: #f0f8ff;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .task-item .form-control,
        .task-item .form-select {
            padding: 14px 18px;
            font-size: 1.05rem;
            border-radius: 8px;
            border: 1px solid #d1d1d1;
            transition: all 0.3s;
        }
        
        .task-item .form-control:focus,
        .task-item .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(60, 141, 188, 0.2);
        }
        
        .task-item .form-label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.05rem;
            margin-bottom: 12px;
        }
        
        .task-actions {
            margin-top: 25px;
            display: flex;
            justify-content: flex-end;
        }
        
        .task-actions .btn {
            padding: 10px 25px;
            font-weight: 600;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
        }
        
        /* PERBAIKAN TAMBAHAN UNTUK KESELARASAN */
        :root {
            --primary-color: #3c8dbc;
            --accent-color: #00c0ef;
        }
        
        .agenda-item {
            margin-bottom: 30px;
        }
        
        .agenda-header {
            padding: 18px 25px;
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