@canAccess('index','moms')
<div class="row">
    <div class="col-md-12 mt-1">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 text-primary">
                <i class="fas fa-file-contract me-2"></i> Daftar Minutes of Meeting
            </h5>
            @canAccess('create','moms')
            <a href="{{ route('mom.create') }}" class="btn btn-primary btn-sm d-none d-md-inline-flex align-items-center">
                <i class="fas fa-plus me-2"></i> Buat MoM Baru
            </a>
            @endcanAccess
        </div>

        <!-- Search Box -->
        <div class="search-box mb-4">
            <i class="fas fa-search"></i>
            <input wire:model.debounce.300ms="search" type="text" class="form-control form-control-sm" 
                   placeholder="Cari nama, meeting, project, atau catatan...">
        </div>

        <!-- MoM Cards -->
        <div class="row">
            <div class="col-md-12">
                @forelse($moms as $mom)
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-body p-0">
                            <!-- Card Header -->
                            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                <div>
                                    <h5 class="mb-1 fw-bold text-dark">{{ $mom->name }}</h5>
                                    <div class="d-flex flex-wrap gap-2 mt-1">
                                        @if($mom->project)
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                <i class="fas fa-project-diagram me-1"></i>{{ $mom->project->title }}
                                            </span>
                                        @endif
                                        @if($mom->meeting)
                                            <span class="badge bg-info bg-opacity-10 text-info gap-2 ml-1">
                                                <i class="fas fa-users me-1"></i>{{ $mom->meeting->meeting_name }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="text-muted small">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        {{ \Carbon\Carbon::parse($mom->mom_date)->locale('id_ID')->translatedFormat('d F Y') }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Card Content -->
                            <div class="p-3">
                                @if($mom->notes)
                                <div class="mb-3">
                                    <div class="d-flex">
                                        <div class="me-2 text-muted">
                                            <i class="fas fa-sticky-note"></i>
                                        </div>
                                        <div class="notes-preview text-muted">
                                            {!! \Str::limit($mom->notes, 200) !!}
                                        </div>
                                    </div>
                                </div>
                                @endif
                                
                                <!-- Card Footer -->
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <!-- Status Indicators -->
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="text-muted small">
                                            <i class="far fa-clock me-1"></i>
                                            {{ $mom->created_at->diffForHumans() }}
                                        </span>
                                        
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="fas fa-tasks text-muted small"></i>
                                            <span class="text-muted small">{{ $mom->total_tasks }} Tugas</span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center gap-2" 
                                             data-bs-toggle="tooltip" 
                                             title="{{ number_format($mom->progress, 2) }}% Complete">
                                            <div class="progress" style="width: 80px; height: 6px;">
                                                <div class="progress-bar bg-success" 
                                                     style="width: {{ $mom->progress }}%">
                                                </div>
                                            </div>
                                            <span class="text-muted small">{{ number_format($mom->progress, 0) }}%</span>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-flex gap-2">
                                        @canAccess('show','moms')
                                        <a href="{{ route('mom.show', $mom->id) }}" 
                                           class="btn btn-sm btn-outline-primary rounded-pill px-3 btn-action"
                                           data-bs-toggle="tooltip" title="Lihat detail">
                                            <i class="fas fa-eye"></i>
                                            <span class="d-none d-md-inline ms-1">Lihat</span>
                                        </a>
                                        @endcanAccess
                                        
                                        @canAccess('update','moms')
                                        <a href="{{ route('mom.edit', $mom->id) }}" 
                                           class="btn btn-sm btn-outline-secondary rounded-pill px-3 btn-action"
                                           data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                            <span class="d-none d-md-inline ms-1">Edit</span>
                                        </a>
                                        @endcanAccess
                                        
                                        @canAccess('destroy','moms')
                                        @if($mom->isDelete())
                                        <form action="{{ route('mom.destroy', $mom->id) }}" method="POST" class="delete-form d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-action"
                                                    data-bs-toggle="tooltip" title="Hapus"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus MoM ini?')">
                                                <i class="fas fa-trash"></i>
                                                <span class="d-none d-md-inline ms-1">Hapus</span>
                                            </button>
                                        </form>
                                        @endif
                                        @endcanAccess
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-file-contract fa-3x text-muted"></i>
                        </div>
                        <h4 class="mb-2">Belum ada data MoM</h4>
                        <p class="text-muted mb-4">Mulai dengan membuat MoM baru untuk pertemuan Anda</p>
                        @canAccess('create','moms')
                        <a href="{{ route('mom.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Buat MoM Baru
                        </a>
                        @endcanAccess
                    </div>
                @endforelse

                <!-- Pagination -->
                @if($moms->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $moms->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endcanAccess