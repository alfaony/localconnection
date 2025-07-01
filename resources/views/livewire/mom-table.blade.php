<div class="row">
    <div class="col-md-12 mt-1">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i> Daftar Minutes of Meeting</h5>
            <a href="{{ route('mom.create') }}" class="btn btn-primary d-none d-md-block">
                <i class="fas fa-plus me-2"></i> Mom
            </a>
        </div>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input wire:model.debounce.300ms="search" type="text" class="form-control" placeholder="Cari nama, tanggal, project, atau catatan...">
        </div>

        <div class="row">
            <div class="col-md-12">
                @forelse($moms as $mom)
                    <div class="card-mom">
                        <div class="mom-header d-flex justify-content-between align-items-center p-2">
                            <div>
                                <h3 class="mom-title">{{ $mom->name }}</h3>
                                <div class="d-flex flex-wrap gap-2">
                                    @if($mom->project)
                                        <span class="badge-project">
                                            <i class="fas fa-project-diagram me-1"></i>{{ $mom->project->title }}
                                        </span>
                                    @endif
                                    @if($mom->meeting)
                                        <span class="badge-meeting">
                                            <i class="fas fa-users me-1"></i>{{ $mom->meeting->meeting_name }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-md-end">
                                <p class="mom-subtitle mb-0">
                                    <i class="far fa-calendar-alt me-1"></i>
                                    {{ \Carbon\Carbon::parse($mom->mom_date)->locale('id_ID')->translatedFormat('d F Y') }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="p-3">
                            <div class="detail-item">
                                <div class="detail-icon">
                                    <i class="fas fa-sticky-note"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="mom-notes p-3">
                                        {!! \Str::limit($mom->notes, 120) !!}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="action-container mt-3">
                                <a href="{{ route('mom.show', $mom->id) }}" class="btn btn-info btn-action">
                                    <i class="fas fa-eye me-1"></i>Lihat
                                </a>
                                <a href="{{ route('mom.edit', $mom->id) }}" class="btn btn-warning btn-action">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                <form action="{{ route('mom.destroy', $mom->id) }}" method="POST" style="display:inline;" class="delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-action">
                                        <i class="fas fa-trash me-1"></i>Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-file-contract"></i>
                        <h4 class="mb-3">Belum ada data MoM</h4>
                        <p class="text-muted mb-4">Mulai dengan membuat MoM baru untuk pertemuan Anda</p>
                        <a href="{{ route('mom.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>
                        </a>
                    </div>
                @endforelse

                <div class="pagination-container">
                    {{ $moms->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
