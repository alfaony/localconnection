<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail MomTask</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-primary">
                <i class="bi bi-card-checklist me-2"></i>Detail MomTask
            </h1>
        </div>

        @include('components.alert')

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Task Details Card -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0">Detail Tugas</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">{{ $task->title }}</label>
                            <p class="card-text">
                                {!! $task->description !!}
                            </p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Deskripsi Tugas</label>
                            <p class="card-text">
                                {!! $task->description !!}
                            </p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small mb-1">Tanggal Dibuat</label>
                                <p class="mb-0">{{ \Carbon\Carbon::parse($task->start_date)->locale('id')->translatedFormat('d F Y') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted small mb-1">Deadline</label>
                                <p class="mb-0">{{ \Carbon\Carbon::parse($task->end_date)->locale('id')->translatedFormat('d F Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Agenda Details Card -->
                 @if ($task->agenda)
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0">Detail Agenda</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">{{ $task->agenda->title }}</label>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">Deskripsi Agenda</label>
                            <p class="mb-0">
                                {!! $task->agenda->discussion_notes !!}
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- MoM Details Card -->
                 @if ($task->agenda->mom)
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0">Detail Minutes of Meeting (MoM)</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted small mb-1">{{ $task->agenda->mom->name ?? '' }}</label>
                        </div>
                        
                        <!-- Project Section -->
                         @if ($task->agenda->mom->project)
                        <div class="mt-4 pt-3 border-top">
                            <label class="form-label text-muted small mb-1">Proyek Terkait</label>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-info me-2">{{ $task->agenda->mom->project->title ?? '' }}</span>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Meeting Section -->
                         @if ($task->agenda->mom->meeting)
                        <div class="mt-3">
                            <label class="form-label text-muted small mb-1">Detail Rapat</label>
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-info me-2">{{ $task->agenda->mom->meeting->meeting_name ?? '' }}</span>
                            </div>
                            
                            <label class="form-label text-muted small mb-1">Peserta Rapat</label>

                            <div class="d-flex flex-wrap gap-2">
                                @foreach($task->agenda->mom->meeting->combined_participants as $participant)
                                <span class="badge bg-light text-dark border">{{ $participant['name'] }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status Card -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0">Status Tugas</h2>
                        <div id="current-status-badge" class="badge p-2 fs-6">DOING</div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Status Saat Ini:</span>
                            <span id="current-status-badge-copy" class="badge bg-warning text-dark py-2 px-3">DOING</span>
                        </div>
                        
                        <div class="progress-container mb-3">
                            <div id="progress-fill" class="progress-fill bg-warning" style="width: 50%"></div>
                        </div>
                        
                        <!-- Status Indicators -->
                        <div class="d-flex justify-content-between position-relative mb-4 small">
                            <div class="status-step text-center">
                                <div class="status-icon">
                                    <i id="todo-icon" class="bi bi-circle-fill text-secondary"></i>
                                </div>
                                <div class="status-label mt-1">TODO</div>
                            </div>
                            <div class="status-step text-center">
                                <div class="status-icon">
                                    <i id="doing-icon" class="bi bi-circle-fill text-warning"></i>
                                </div>
                                <div class="status-label mt-1">DOING</div>
                            </div>
                            <div class="status-step text-center">
                                <div class="status-icon">
                                    <i id="inreview-icon" class="bi bi-circle text-muted"></i>
                                </div>
                                <div class="status-label mt-1">INREVIEW</div>
                            </div>
                            <div class="status-step text-center">
                                <div class="status-icon">
                                    <i id="complete-icon" class="bi bi-circle text-muted"></i>
                                </div>
                                <div class="status-label mt-1">COMPLETE</div>
                            </div>
                        </div>
                            
                        
                        <!-- Action Buttons based on Status -->
                        <div class="mt-4">
                            <!-- TODO Status Action -->
                            <div class="status-action" data-status="todo">
                                <form action="{{ route('external.task.submit', $task->token) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="method" value="todo">
                                    <button class="btn btn-primary w-100">
                                        <i class="bi bi-play-circle me-2"></i>Lakukan Pekerjaan
                                    </button>
                                </form>
                            </div>
                            
                            <!-- DOING Status Action -->
                            <div class="status-action" data-status="doing">
                                <h6 class="mb-3">Buat Laporan</h6>
                                <form action="{{ route('external.task.submit', $task->token) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="method" value="doing">
                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <input class="thriveEditor form-control" id="description_notes" data-ids="notes" name="description" placeholder="yang akan dicetak di perjanjian"/>

                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Lampiran</label>
                                        <input type="file" name="attachment" class="form-control">
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-send-check me-2"></i>Kirim Laporan
                                    </button>
                                </form>
                            </div>
                            
                            <!-- INREVIEW Status Action -->
                            <div class="status-action" data-status="in review">
                                <div class="col-md-12 mb-4">
                                    <div class="card h-100">
                                        <div class="card-header bg-light">
                                            <h4 class="h6 mb-0">Laporan yang Dikirim</h4>
                                        </div>
                                        <div class="card-body">
                                            @if ($task->external_note)
                                            <div class="mb-3">
                                                <label class="form-label text-muted small mb-1">Deskripsi</label>
                                                <div class="card card-body bg-white">
                                                    {!! $task->external_note !!}
                                                </div>
                                            </div>
                                            @endif
                                            
                                            @if ($task->attachment)
                                            <div>
                                                <label class="form-label text-muted small mb-1">Lampiran</label>
                                                <div class="card attachment-card mb-2">
                                                    <div class="card-body py-2 d-flex align-items-center">
                                                        <i class="bi bi-file-earmark-pdf fs-4 text-danger me-3"></i>
                                                        <div class="flex-grow-1">
                                                            <div class="fw-medium">{{ "Lampiran" }}</div>
                                                        </div>
                                                        <a href="{{ s3_asset(true,10,$task->attachment) }}" class="btn btn-sm btn-outline-primary" download>
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-hourglass-split me-2"></i>
                                    Sedang dalam proses review
                                </div>
   
                                <!-- Review Actions -->
                                @auth
                                <div class="col-md-12 mt-3">
                                    <form action="{{ route('external.task.approve', $task->token) }}" method="POST">
                                        @csrf
                                        <div class="card h-100">
                                            <div class="card-header bg-light">
                                                <h4 class="h6 mb-0">Tindakan Review</h4>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-4">
                                                    <label class="form-label">Status Review</label>
                                                    <select class="form-control" name="status" id="review-status" required>
                                                        <option value="" selected disabled>Pilih status review</option>
                                                        <option value="approve">Approve</option>
                                                        <option value="decline">Not Approve</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Komentar Review</label>
                                                    <input class="thriveEditor form-control" id="description_reject_reason" data-ids="reject_reason" name="reject_reason" placeholder="yang akan dicetak di perjanjian"/>

                                                </div>
                                                
                                                <div class="d-flex gap-2 mt-4">
                                                    <button class="btn btn-primary action-btn flex-grow-1 py-2" id="submit-btn" onclick="return confirm('Apakah Anda yakin?')">
                                                        <i class="bi bi-send me-2"></i>Submit
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                @endauth
                            </div>
                            
                            <!-- COMPLETE Status Action -->
                            <div class="status-action" data-status="complete">
                                <div class="alert alert-success mb-0">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Tugas telah selesai
                                </div>
                            </div>
                            
                            <!-- NOT COMPLETE Status Action -->
                            <div class="status-action" data-status="not complete">
                                <div class="alert alert-danger mb-3">
                                    <i class="bi bi-exclamation-circle me-2"></i>
                                    Tugas belum selesai
                                </div>
                        
                                <div class="alert alert-warning mb-3">
                                    {!! $task->reject_reason ?? "-" !!}
                                </div>
                                <form action="{{ route('external.task.submit', $task->token) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="method" value="todo">
                                    <button class="btn btn-primary w-100">
                                        <i class="bi bi-play-circle me-2"></i>Lakukan Pekerjaan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Info Card -->
                 {{-- 
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0">Informasi</h2>
                    </div>
                    <div class="card-body">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <i class="bi bi-person-circle text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="d-block text-muted">Ditugaskan Kepada</small>
                                <span>Jane Smith</span>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <i class="bi bi-calendar-check text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="d-block text-muted">Tanggal Mulai</small>
                                <span>20 Juni 2023</span>
                            </div>
                        </div>
                        
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-clock-history text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="d-block text-muted">Estimasi Waktu</small>
                                <span>10 Hari</span>
                            </div>
                        </div>
                    </div>
                </div>
                --}}
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="{{ asset('js/thriveEditor.js') }}"></script>
    <script>
        const statusConfig = {
            'todo': {
                progress: 25,
                color: 'secondary',
                icon: 'circle-fill'
            },
            'doing': {
                progress: 50,
                color: 'warning',
                icon: 'circle-fill'
            },
            'in review': {
                progress: 75,
                color: 'primary',
                icon: 'circle-fill'
            },
            'complete': {
                progress: 100,
                color: 'success',
                icon: 'check-circle-fill'
            },
            'not complete': {
                progress: 25,
                color: 'danger',
                icon: 'exclamation-circle-fill'
            }
        };

        // Function to update status display
        function updateStatusDisplay(status) 
        {
            const config = statusConfig[status];
            console.log(config, status, statusConfig, config.color);
            
            
            // Update progress bar
            const progressFill = document.getElementById('progress-fill');
            progressFill.style.width = `${config.progress}%`;
            progressFill.className = `progress-fill bg-${config.color}`;
            
            // Update current status badge
            const statusBadge = document.getElementById('current-status-badge');
            const statusBadgeCopy = document.getElementById('current-status-badge-copy');
            statusBadge.className = `badge p-2 fs-6 bg-${config.color}`;
            statusBadge.textContent = status.toUpperCase();
            statusBadgeCopy.textContent = status.toUpperCase();
            
            // Reset all icons
            document.getElementById('todo-icon').className = 'bi bi-circle text-muted';
            document.getElementById('doing-icon').className = 'bi bi-circle text-muted';
            document.getElementById('inreview-icon').className = 'bi bi-circle text-muted';
            document.getElementById('complete-icon').className = 'bi bi-circle text-muted';
            
            // Update icons based on status
            switch(status) {
                case 'todo':
                    document.getElementById('todo-icon').className = `bi bi-${config.icon} text-${config.color}`;
                    break;
                case 'doing':
                    document.getElementById('todo-icon').className = 'bi bi-check-circle-fill text-success';
                    document.getElementById('doing-icon').className = `bi bi-${config.icon} text-${config.color}`;
                    break;
                case 'in review':
                    document.getElementById('todo-icon').className = 'bi bi-check-circle-fill text-success';
                    document.getElementById('doing-icon').className = 'bi bi-check-circle-fill text-success';
                    document.getElementById('inreview-icon').className = `bi bi-${config.icon} text-${config.color}`;
                    break;
                case 'complete':
                    document.getElementById('todo-icon').className = 'bi bi-check-circle-fill text-success';
                    document.getElementById('doing-icon').className = 'bi bi-check-circle-fill text-success';
                    document.getElementById('inreview-icon').className = 'bi bi-check-circle-fill text-success';
                    document.getElementById('complete-icon').className = `bi bi-${config.icon} text-${config.color}`;
                    break;
                case 'not complete':
                    document.getElementById('todo-icon').className = `bi bi-${config.icon} text-${config.color}`;
                    break;
            }
        }
    </script>
    <script>
        // Script untuk menampilkan aksi sesuai status
        document.addEventListener('DOMContentLoaded', function() {
            const currentStatus = "{{ $task->status->name }}" ; // Ganti status di sini untuk melihat perubahan
            updateStatusDisplay(currentStatus);
            
            // Sembunyikan semua aksi status
            document.querySelectorAll('.status-action').forEach(action => {
                action.style.display = 'none';
            });
            
            // Tampilkan aksi sesuai status
            const currentAction = document.querySelector(`.status-action[data-status="${currentStatus}"]`);
            if (currentAction) {
                currentAction.style.display = 'block';
            }
        });
    </script>
</body>
</html>