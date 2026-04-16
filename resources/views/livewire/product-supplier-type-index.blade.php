@section('title', 'Manajemen Jenis Supplier Produk')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Jenis Supplier Produk</h1>
@stop

{{-- Satu root element — wajib untuk Livewire v2 --}}
<div>

<div class="container-fluid">
    <div class="row">

        {{-- ── TABEL JENIS SUPPLIER ── --}}
        <div class="{{ $showingTypeId ? 'col-md-5' : 'col-12' }}">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Jenis Supplier Produk</h3>
                    @canAccess('create','supplier_types')
                    <button wire:click="create" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tambah Baru
                    </button>
                    @endcanAccess
                </div>

                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nama</th>
                                    <th>Supplier</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($productSupplierTypes as $type)
                                <tr class="{{ $showingTypeId == $type->id ? 'table-active' : '' }}">
                                    <td>{{ $type->name }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $type->suppliers()->count() }}
                                        </span>
                                    </td>
                                    <td>{{ $type->created_at->format('d M Y') }}</td>
                                    <td>
                                        @canAccess('show','supplier_types')
                                        <button wire:click="showType({{ $type->id }})"
                                                class="btn btn-info btn-sm" title="Lihat Supplier">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @endcanAccess

                                        @canAccess('edit','supplier_types')
                                        <button wire:click="edit({{ $type->id }})"
                                                class="btn btn-warning btn-sm" title="Ubah">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endcanAccess

                                        @canAccess('destroy','supplier_types')
                                        <button wire:click="confirmDelete({{ $type->id }})"
                                                class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcanAccess
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">Tidak ada data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $productSupplierTypes->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- ── PANEL SHOW SUPPLIER ── --}}
        @if($showingTypeId)
        <div class="col-md-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-store mr-1"></i>
                        Supplier — <strong>{{ $showingTypeName }}</strong>
                    </h3>
                    <button wire:click="closeShow" class="btn btn-sm btn-secondary">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                </div>
                <div class="card-body">
                    @if($showingSuppliers->isEmpty())
                        <p class="text-muted text-center py-3">
                            <i class="fas fa-box-open mr-1"></i> Belum ada supplier untuk jenis ini.
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Toko</th>
                                        <th>Nama Pemilik</th>
                                        <th>No. HP</th>
                                        <th>Lokasi</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($showingSuppliers as $i => $supplier)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $supplier->store_name }}</td>
                                        <td>{{ $supplier->owner_name }}</td>
                                        <td>{{ $supplier->phone_number ?? '-' }}</td>
                                        <td>{{ $supplier->location ?? '-' }}</td>
                                        <td>
                                            @canAccess('show','product_suppliers')
                                            <a href="{{ route('product-supplier.show', $supplier->id) }}"
                                               class="btn btn-xs btn-info" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcanAccess
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

    </div>{{-- /.row --}}
</div>{{-- /.container-fluid --}}

{{-- ── MODAL CREATE / EDIT ── --}}
@if ($showModal)
<div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5)"
     wire:click.self="cancel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ $isEdit ? 'Ubah' : 'Tambah' }} Jenis Supplier Produk
                </h5>
                <button type="button" class="close" wire:click="cancel">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">
                    <div class="form-group">
                        <label for="name">Nama</label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               wire:model="name"
                               id="name"
                               placeholder="Masukkan nama">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" wire:click="cancel">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            {{ $isEdit ? 'Ubah' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── MODAL DELETE ── --}}
@if ($showDeleteModal)
<div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5)">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash mr-1"></i> Hapus Jenis Supplier
                </h5>
                <button type="button" class="close text-white" wire:click="cancelDelete">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                @if(!$deleteHasSuppliers)
                    <p>Apakah Anda yakin ingin menghapus jenis supplier ini?</p>
                    <p class="text-muted small mb-0">Tidak ada supplier yang terdampak.</p>
                @else
                    <div class="alert alert-warning py-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Jenis supplier ini memiliki supplier yang terdaftar.
                        Pilih tindakan:
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Pindahkan supplier ke jenis lain sebelum hapus</label>
                        <select wire:model="assignToTypeId" class="form-control mt-1 @error('assignToTypeId') is-invalid @enderror">
                            <option value="">-- Pilih Jenis Supplier Tujuan --</option>
                            @foreach($otherTypes as $ot)
                                <option value="{{ $ot->id }}">{{ $ot->name }}</option>
                            @endforeach
                        </select>
                        @error('assignToTypeId')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Batal</button>
                @if(!$deleteHasSuppliers)
                    <button type="button" class="btn btn-danger" wire:click="deleteDirectly">
                        <i class="fas fa-trash mr-1"></i> Hapus
                    </button>
                @else
                    <button type="button" class="btn btn-danger" wire:click="deleteWithAssign">
                        <i class="fas fa-exchange-alt mr-1"></i> Pindahkan & Hapus
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

</div>{{-- single root element --}}

@section('css')
<style>
    .table th, .table td { vertical-align: middle; }
    .btn-xs { padding: .15rem .4rem; font-size: .75rem; }
</style>
@stop

@section('js')
<script>
    document.addEventListener('livewire:load', function () {
        Livewire.on('closeModal', () => { $('.modal').modal('hide'); });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            @this.call('cancel');
            @this.call('cancelDelete');
        }
    });
</script>
@stop
