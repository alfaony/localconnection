@section('title', 'Manajemen Kategori Produk Toko')

@section('content_header')
    <h1 class="m-0 text-dark">Manajemen Kategori Produk Toko</h1>
@stop

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Kategori Produk Toko</h3>
                        @canAccess('create','category_product_stores')
                        <button wire:click="create" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Tambah Baru
                        </button>
                        @endcanAccess
                    </div>
                </div>

                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nama</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($productSupplierTypes as $type)
                                <tr>
                                    <td>{{ $type->name }}</td>
                                    <td>{{ $type->created_at->format('d M Y') }}</td>
                                    <td>
                                        @canAccess('edit','category_product_stores')
                                        <button wire:click="edit({{ $type->id }})" 
                                                class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Ubah
                                        </button>
                                        @endcanAccess

                                        @canAccess('destroy','category_product_stores')
                                        <button wire:click="delete({{ $type->id }})" 
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Apakah Anda yakin?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                        @endcanAccess
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $productSupplierTypes->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if ($showModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5)" 
         wire:click.self="cancel">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $isEdit ? 'Ubah' : 'Tambah' }} Kategori Produk Toko
                    </h5>
                    <button type="button" class="close" wire:click="cancel">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @canAccess('store','category_product_stores')
                    @canAccess('update','category_product_stores')
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

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="cancel">
                                Batal
                            </button>
                            <button type="submit" class="btn btn-primary">
                                {{ $isEdit ? 'Ubah' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                    @endcanAccess
                    @endcanAccess
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@section('css')
    <style>
        .modal {
            backdrop-filter: blur(3px);
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.on('closeModal', () => {
                $('.modal').modal('hide');
            });
        });

        // Menutup modal ketika tekan ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                Livewire.emit('cancel');
            }
        });
    </script>
@stop
