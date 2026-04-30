@canAccess('index','moms')
<div>
    {{-- Hidden inputs — wire:model standar, diupdate oleh JS via dispatchEvent('input') --}}
    <div style="display:none" aria-hidden="true">
        <input type="text" id="h-mom-project"  wire:model="projectId" tabindex="-1">
        <input type="text" id="h-mom-meeting"  wire:model="meetingId" tabindex="-1">
        <input type="text" id="h-mom-user"     wire:model="userId"    tabindex="-1">
        <input type="text" id="h-mom-datefrom" wire:model="dateFrom"  tabindex="-1">
        <input type="text" id="h-mom-dateto"   wire:model="dateTo"    tabindex="-1">
    </div>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 pt-3">
        <h5 class="mb-0 text-primary fw-semibold">
            <i class="fas fa-file-contract me-2"></i>Minutes of Meeting
        </h5>
        @canAccess('create','moms')
        <a href="{{ route('mom.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah MoM
        </a>
        @endcanAccess
    </div>

    <!-- Filter Panel -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-2 px-3">
            <span class="text-muted small fw-semibold">
                <i class="fas fa-filter me-1"></i> Filter & Pencarian
            </span>
            <button wire:click="resetFilters" class="btn btn-link btn-sm text-muted text-decoration-none p-0">
                <i class="fas fa-undo me-1"></i> Reset
            </button>
        </div>
        <div class="card-body p-3">

            <!-- Baris 1: Search | Date Range | Per Page -->
            <div class="row g-2 mb-2">
                <div class="col-12 col-md-5">
                    <label class="filter-label">Pencarian</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                        <input wire:model.debounce.300ms="search"
                               type="text"
                               class="form-control form-control-sm"
                               placeholder="Nama, catatan, project, meeting...">
                    </div>
                </div>

                {{-- wire:ignore agar daterangepicker tidak di-destroy Livewire --}}
                <div class="col-12 col-md-4" wire:ignore>
                    <label class="filter-label">Rentang Tanggal</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="fas fa-calendar-alt text-muted"></i></span>
                        <input type="text"
                               id="mom-daterange"
                               class="form-control form-control-sm"
                               readonly
                               style="cursor: pointer; background: #fff;">
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <label class="filter-label">Tampilkan</label>
                    <select wire:model="perPage" class="form-select form-select-sm">
                        <option value="10">10 data</option>
                        <option value="25">25 data</option>
                        <option value="50">50 data</option>
                    </select>
                </div>
            </div>

            <!-- Baris 2: Project | Meeting | User -->
            {{-- Tidak ada wire:ignore — Livewire re-render options, JS re-init Select2 --}}
            <div class="row g-2 mb-2">
                <div class="col-12 col-md-4">
                    <label class="filter-label">Project</label>
                    <select id="mom-project-select"
                            class="form-select form-select-sm"
                            data-placeholder="Semua Project">
                        <option value=""></option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}"
                                    {{ $projectId == $project->id ? 'selected' : '' }}>
                                {{ $project->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="filter-label">Meeting</label>
                    <select id="mom-meeting-select"
                            class="form-select form-select-sm"
                            data-placeholder="Semua Meeting">
                        <option value=""></option>
                        @foreach($meetings as $meeting)
                            <option value="{{ $meeting->id }}"
                                    {{ $meetingId == $meeting->id ? 'selected' : '' }}>
                                {{ $meeting->meeting_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="filter-label">Dibuat Oleh</label>
                    <select id="mom-user-select"
                            class="form-select form-select-sm"
                            data-placeholder="Semua Anggota">
                        <option value=""></option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                    {{ $userId == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}{{ $user->id == Auth::id() ? ' (Saya)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Sort -->
            <div class="d-flex align-items-center gap-2 mt-2 pt-2 border-top flex-wrap">
                <span class="text-muted small">Urutkan:</span>

                <button wire:click="sortBy('mom_date')"
                        class="btn btn-sort {{ $sortField === 'mom_date' ? 'active' : '' }}">
                    <i class="fas fa-calendar-day me-1"></i>Tgl MoM
                    @if($sortField === 'mom_date')
                        <i class="fas fa-arrow-{{ $sortDir === 'asc' ? 'up' : 'down' }} ms-1"></i>
                    @endif
                </button>

                <button wire:click="sortBy('name')"
                        class="btn btn-sort {{ $sortField === 'name' ? 'active' : '' }}">
                    <i class="fas fa-font me-1"></i>Nama
                    @if($sortField === 'name')
                        <i class="fas fa-arrow-{{ $sortDir === 'asc' ? 'up' : 'down' }} ms-1"></i>
                    @endif
                </button>

                <button wire:click="sortBy('created_at')"
                        class="btn btn-sort {{ $sortField === 'created_at' ? 'active' : '' }}">
                    <i class="fas fa-clock me-1"></i>Dibuat
                    @if($sortField === 'created_at')
                        <i class="fas fa-arrow-{{ $sortDir === 'asc' ? 'up' : 'down' }} ms-1"></i>
                    @endif
                </button>
            </div>
        </div>
    </div>

    <!-- MoM Cards -->
    @forelse($moms as $mom)
        <div class="card mom-card border-0 shadow-sm mb-3">
            <div class="card-body p-0">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-start p-3 border-bottom">
                    <div class="flex-grow-1 me-3">
                        <h6 class="mb-1 fw-bold text-dark">{{ $mom->name }}</h6>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @if($mom->project)
                                <span class="badge badge-project">
                                    <i class="fas fa-project-diagram me-1"></i>{{ $mom->project->title }}
                                </span>
                            @endif
                            @if($mom->meeting)
                                <span class="badge badge-meeting">
                                    <i class="fas fa-users me-1"></i>{{ $mom->meeting->meeting_name }}
                                </span>
                            @endif
                            @if($mom->user)
                                <span class="badge badge-user">
                                    <i class="fas fa-user me-1"></i>{{ $mom->user->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <span class="text-muted small">
                            <i class="far fa-calendar-alt me-1"></i>
                            {{ \Carbon\Carbon::parse($mom->mom_date)->locale('id_ID')->translatedFormat('d F Y') }}
                        </span>
                    </div>
                </div>

                <!-- Body -->
                <div class="p-3">
                    @if($mom->notes)
                        <p class="notes-preview text-muted small mb-3">
                            <i class="fas fa-sticky-note me-1 text-warning"></i>
                            {!! \Str::limit(strip_tags($mom->notes), 200) !!}
                        </p>
                    @endif

                    <!-- Footer -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <span class="text-muted small">
                                <i class="far fa-clock me-1"></i>{{ $mom->created_at->diffForHumans() }}
                            </span>
                            <span class="text-muted small">
                                <i class="fas fa-tasks me-1"></i>{{ $mom->total_tasks }} Tugas
                            </span>
                            <div class="d-flex align-items-center gap-2"
                                 data-bs-toggle="tooltip"
                                 title="{{ number_format($mom->progress, 2) }}% selesai">
                                <div class="progress" style="width: 70px; height: 5px;">
                                    <div class="progress-bar bg-success" style="width: {{ $mom->progress }}%"></div>
                                </div>
                                <span class="text-muted small">{{ number_format($mom->progress, 0) }}%</span>
                            </div>
                        </div>

                        <div class="d-flex gap-1">
                            @canAccess('show','moms')
                            <a href="{{ route('mom.show', $mom->id) }}"
                               class="btn btn-sm btn-outline-primary mom-action-btn"
                               data-bs-toggle="tooltip" title="Lihat detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endcanAccess

                            @canAccess('update','moms')
                            <a href="{{ route('mom.edit', $mom->id) }}"
                               class="btn btn-sm btn-outline-secondary mom-action-btn"
                               data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcanAccess

                            @canAccess('destroy','moms')
                            @if($mom->isDelete())
                            <form action="{{ route('mom.destroy', $mom->id) }}" method="POST" class="delete-form d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-sm btn-outline-danger mom-action-btn"
                                        data-bs-toggle="tooltip" title="Hapus">
                                    <i class="fas fa-trash"></i>
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
        <div class="text-center py-5 bg-white rounded shadow-sm">
            <i class="fas fa-file-contract fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">Tidak ada MoM ditemukan</h5>
            <p class="text-muted small mb-3">Coba ubah filter atau tambah MoM baru</p>
            @canAccess('create','moms')
            <a href="{{ route('mom.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Tambah MoM
            </a>
            @endcanAccess
        </div>
    @endforelse

    <!-- Pagination -->
    @if($moms->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
        <small class="text-muted">
            Menampilkan {{ $moms->firstItem() }}–{{ $moms->lastItem() }} dari {{ $moms->total() }} data
        </small>
        {{ $moms->links() }}
    </div>
    @elseif($moms->total() > 0)
    <div class="mt-2">
        <small class="text-muted">Menampilkan semua {{ $moms->total() }} data</small>
    </div>
    @endif
</div>
@endcanAccess
