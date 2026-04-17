@section('title', 'Master Group Pelanggan Internet')

@section('content_header')
    <div class="mb-0">
        <h5 class="mb-0">Master Group Pelanggan Internet</h5>
    </div>
@stop

<div>

    @include('components.alert')

    {{-- ── Form Create / Edit (muncul di atas) ──────────────────────────── --}}
    @if($showForm)
    <div class="card shadow-sm border-left-primary mb-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-2">
            <h6 class="mb-0">
                <i class="fas fa-{{ $isEdit ? 'edit' : 'plus' }} mr-1"></i>
                {{ $isEdit ? 'Edit Group' : 'Tambah Group Baru' }}
            </h6>
            <button type="button" class="close text-white" wire:click="cancel">
                <span>&times;</span>
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <div class="form-group mb-2">
                        <label class="font-weight-semibold">
                            Nama Group <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               wire:model.defer="name"
                               class="form-control form-control-sm @error('name') is-invalid @enderror"
                               placeholder="Contoh: Cluster A, Zona Utara, RT 05"
                               autofocus>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group mb-2">
                        <label class="font-weight-semibold">Deskripsi</label>
                        <input type="text"
                               wire:model.defer="description"
                               class="form-control form-control-sm @error('description') is-invalid @enderror"
                               placeholder="Keterangan tambahan (opsional)">
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group mb-2 w-100">
                        <button type="button"
                                class="btn btn-primary btn-sm btn-block"
                                wire:click="{{ $isEdit ? 'update' : 'store' }}"
                                wire:loading.attr="disabled"
                                wire:target="{{ $isEdit ? 'update' : 'store' }}">
                            <span wire:loading wire:target="{{ $isEdit ? 'update' : 'store' }}">
                                <i class="fas fa-spinner fa-spin mr-1"></i>
                            </span>
                            {{ $isEdit ? 'Simpan' : 'Tambah' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Tabel Data ──────────────────────────────────────────────────── --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom-0 d-flex justify-content-end align-items-center pb-0">
            @canAccess('create', 'internet_customer_groups')
            <button wire:click="create" class="btn btn-sm btn-primary">
                <i class="fas fa-plus mr-1"></i> Tambah Group
            </button>
            @endcanAccess
        </div>
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <input type="text"
                               wire:model.debounce.400ms="search"
                               class="form-control"
                               placeholder="Cari nama atau deskripsi...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th width="40">#</th>
                            <th>Nama Group</th>
                            <th>Deskripsi</th>
                            <th width="100" class="text-center">Pelanggan</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $i => $group)
                        <tr class="{{ $editingId === $group->id ? 'table-warning' : '' }}">
                            <td>{{ $groups->firstItem() + $i }}</td>
                            <td><strong>{{ $group->name }}</strong></td>
                            <td class="text-muted">{{ $group->description ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge badge-secondary">{{ $group->customers_count }}</span>
                            </td>
                            <td class="text-center">
                                @canAccess('edit', 'internet_customer_groups')
                                <button wire:click="edit('{{ $group->id }}')"
                                        class="btn btn-sm btn-outline-primary mr-1"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @endcanAccess

                                @canAccess('destroy', 'internet_customer_groups')
                                <button wire:click="confirmDelete('{{ $group->id }}')"
                                        class="btn btn-sm btn-danger"
                                        title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcanAccess
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                Belum ada data group.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($groups->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $groups->links() }}
            </div>
            @endif

        </div>
    </div>

    {{-- ── Konfirmasi Hapus (inline) ───────────────────────────────────── --}}
    @if($showDeleteModal)
    <div class="modal fade show" style="display:block; background:rgba(0,0,0,0.5);"
         wire:click.self="cancelDelete">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-trash mr-1"></i> Hapus Group
                    </h5>
                    <button type="button" class="close text-white" wire:click="cancelDelete">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Hapus group ini?</p>
                    <small class="text-muted">Tindakan ini tidak dapat dibatalkan.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="cancelDelete">
                        Batal
                    </button>
                    <button type="button"
                            class="btn btn-danger btn-sm"
                            wire:click="delete"
                            wire:loading.attr="disabled"
                            wire:target="delete">
                        <span wire:loading wire:target="delete">
                            <i class="fas fa-spinner fa-spin mr-1"></i>
                        </span>
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@section('js')
<script>
    document.addEventListener('livewire:load', function () {
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                @this.call('cancel');
                @this.call('cancelDelete');
            }
        });

        window.addEventListener('show-toast', function (e) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: e.detail.type === 'success' ? 'success' : 'error',
                    title: e.detail.message,
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            }
        });
    });
</script>
@stop

@section('css')
<style>
    .table th, .table td { vertical-align: middle; }
    .border-left-primary { border-left: 4px solid #007bff !important; }
</style>
@stop
