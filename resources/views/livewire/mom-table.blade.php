<div class="row">
    <div class="col-md-12 mt-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Daftar Minutes of Meeting</h4>
            <a href="{{ route('mom.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Buat MoM
            </a>
        </div>

        <div class="mb-3">
            <input wire:model.debounce.300ms="search" type="text" class="form-control" placeholder="Cari tanggal, project, atau catatan...">
        </div>

        @forelse($moms as $mom)
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-bold mb-0">
                                {{ $mom->project->title ?? '' }}
                            </p>
                            <p class="mb-1 text-muted">
                                <i class="far fa-calendar-alt me-1"></i>
                                {{ \Carbon\Carbon::parse($mom->mom_date)->locale('id_ID')->translatedFormat('d F Y') }}
                            </p>
                            @if($mom->meeting)
                                <p class="mb-1 text-muted">
                                    <i class="fas fa-users me-1"></i>
                                    {{ $mom->meeting->meeting_name }}
                                </p>
                            @endif
                            <p class="mb-0">{!! \Str::limit($mom->notes, 80) !!}</p>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('mom.show', $mom->id) }}" class="btn btn-sm btn-info mb-1">
                                <i class="fas fa-eye me-1"></i>Lihat
                            </a>
                            <a href="{{ route('mom.edit', $mom->id) }}" class="btn btn-sm btn-warning mb-1">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            <form action="{{ route('mom.destroy', $mom->id) }}" method="POST"
                                style="display:inline;" class="delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger mb-1">
                                    <i class="fas fa-trash me-1"></i>Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-light text-center text-muted mt-4">
                <i class="fas fa-info-circle me-1"></i> Belum ada data MoM.
            </div>
        @endforelse

        <div class="mt-3">
            {{ $moms->links() }}
        </div>
    </div>
</div>