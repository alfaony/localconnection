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
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="font-weight-semibold">
                            Nama Group <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               wire:model.defer="name"
                               class="form-control form-control-sm @error('name') is-invalid @enderror"
                               placeholder="Contoh: HN11, BDG01"
                               autofocus>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">Kode ini menjadi prefix grouping_id pelanggan</small>
                    </div>
                </div>
                <div class="col-md-3">
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
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="font-weight-semibold">Hari Awal Pembayaran <span class="text-danger">*</span></label>
                        <input type="number"
                               wire:model.defer="start_day"
                               class="form-control form-control-sm @error('start_day') is-invalid @enderror"
                               placeholder="Start Day">
                        @error('start_day')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="font-weight-semibold">Hari Akhir Pembayaran <span class="text-danger">*</span></label>
                        <input type="number"
                               wire:model.defer="end_day"
                               class="form-control form-control-sm @error('end_day') is-invalid @enderror"
                               placeholder="Start Day">
                        @error('end_day')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group mb-2">
                        <label class="font-weight-semibold">
                            Last Number
                            <small class="text-muted font-weight-normal">(seq terakhir)</small>
                        </label>
                        <input type="number"
                               wire:model.defer="last_number"
                               class="form-control form-control-sm @error('last_number') is-invalid @enderror"
                               min="0"
                               placeholder="0">
                        @error('last_number')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                        <small class="text-muted">Nomor urut terakhir yang sudah dipakai</small>
                    </div>
                </div>
            </div>

            {{-- ODP Assignment --}}
            <div class="row mt-2 mb-2">
                <div class="col-12">
                    <label class="font-weight-semibold">
                        ODP yang Dilayani
                        <small class="text-muted font-weight-normal ml-1">(grup hanya muncul saat ODP terpilih)</small>
                    </label>
                    @error('selectedOdpIds')
                        <div class="text-danger small mb-1">{{ $message }}</div>
                    @enderror
                    @if($availableOdps->isEmpty())
                        <p class="text-muted small mb-0">Belum ada ODP terdaftar.</p>
                    @else
                    <div wire:ignore>
                        <select id="odpSelect2"
                                multiple
                                class="form-control"
                                style="width:100%"
                                placeholder="Cari dan pilih ODP...">
                            @foreach($availableOdps as $odp)
                                <option value="{{ $odp->id }}">{{ $odp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
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
                            <th>Billing</th>
                            <th>ODP</th>
                            <th width="80" class="text-center">Pelanggan</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $i => $group)
                        <tr class="{{ $editingId === $group->id ? 'table-warning' : '' }}">
                            <td>{{ $groups->firstItem() + $i }}</td>
                            <td>
                                <strong>{{ $group->name }}</strong>
                                @if($group->description)
                                    <span class="text-muted"> — {{ $group->description }}</span>
                                @endif
                            </td>
                            <td>
                                {{ $group->start_day }} - {{ $group->end_day }}
                            </td>
                            <td>
                                @forelse($group->odps as $odp)
                                    <span class="badge badge-info mr-1">{{ $odp->name }}</span>
                                @empty
                                    <span class="text-muted small">—</span>
                                @endforelse
                            </td>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    function initOdpSelect2(selectedIds) {
        var $el = $('#odpSelect2');
        if (!$el.length) return;

        // Destroy existing instance before re-init
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }

        $el.select2({
            placeholder: 'Cari dan pilih ODP...',
            allowClear: true,
            width: '100%',
            closeOnSelect: false,
            language: {
                noResults: function() { return 'ODP tidak ditemukan'; },
                searching: function() { return 'Mencari...'; },
            },
        });

        // Set pre-selected values without triggering Livewire sync
        $el.val(selectedIds || []).trigger('change.select2');

        // Sync to Livewire on user change
        $el.off('change.livewire').on('change.livewire', function() {
            var selected = $(this).val() || [];
            @this.set('selectedOdpIds', selected);
        });
    }

    document.addEventListener('livewire:load', function () {

        // Keyboard shortcuts
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                @this.call('cancel');
                @this.call('cancelDelete');
            }
        });

        // Toast notifications
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

        // PHP fires this event on create() and edit() to populate Select2
        window.addEventListener('odp-select2-init', function (e) {
            // Small delay to let Livewire finish rendering the form DOM first
            setTimeout(function() {
                initOdpSelect2(e.detail.ids || []);
            }, 100);
        });
    });
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
    .table th, .table td { vertical-align: middle; }
    .border-left-primary { border-left: 4px solid #007bff !important; }

    .select2-container--default .select2-selection--multiple {
        min-height: 36px;
        border-color: #ced4da;
        border-radius: 4px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        border-color: #0056b3;
        color: #fff;
        border-radius: 3px;
        padding: 2px 8px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: rgba(255,255,255,.7);
        margin-right: 4px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fff;
    }
</style>
@stop
