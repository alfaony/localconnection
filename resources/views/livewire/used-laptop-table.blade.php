<div>
    <div class="card mt-3">
        <div class="card-header bg-primary">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-white">
                    <i class="fas fa-laptop mr-2"></i> Manajemen Laptop Bekas
                </h3>
                @canAccess('create','used_laptops')
                <a href="{{ route('used-laptop.create') }}" class="btn btn-success">
                    <i class="fas fa-plus mr-1"></i> Tambah Laptop
                </a>
                @endcanAccess
            </div>
            <div class="card-tools mt-2">
                <div class="input-group input-group-sm">
                    <input 
                        type="text" 
                        class="form-control" 
                        placeholder="Cari laptop..."
                        wire:model.debounce.300ms="search"
                    >
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
                {{-- Loading indikator saat search --}}
                <div class="mt-1 text-center" wire:loading.delay wire:target="search">
                    <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                    <small class="text-muted">Mencari...</small>
                </div>
            </div>
        </div>

        <!-- Filter Status Terjual/Belum Terjual -->
        <div class="card-header bg-light py-2">
            <div class="d-flex align-items-center flex-wrap">
                <span class="mr-2 font-weight-bold">Filter Status:</span>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" 
                            class="btn {{ $statusFilter === '' ? 'btn-primary' : 'btn-outline-secondary' }}"
                            wire:click="$set('statusFilter', '')"
                            wire:loading.attr="disabled">
                        Semua
                    </button>
                    <button type="button" 
                            class="btn {{ $statusFilter === 'unsold' ? 'btn-primary' : 'btn-outline-secondary' }}"
                            wire:click="$set('statusFilter', 'unsold')"
                            wire:loading.attr="disabled">
                        <i class="fas fa-times-circle mr-1"></i> Belum Terjual
                    </button>
                    <button type="button" 
                            class="btn {{ $statusFilter === 'sold' ? 'btn-primary' : 'btn-outline-secondary' }}"
                            wire:click="$set('statusFilter', 'sold')"
                            wire:loading.attr="disabled">
                        <i class="fas fa-check-circle mr-1"></i> Terjual
                    </button>
                </div>
            </div>
            {{-- Loading indikator saat filter status diubah --}}
            <div class="mt-2" wire:loading.delay wire:target="statusFilter">
                <span class="spinner-border spinner-border-sm text-secondary" role="status"></span>
                <small class="text-muted">Memfilter data...</small>
            </div>
        </div>
        
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="bg-light">
                        <tr>
                            <th wire:click="sortBy('name')" style="cursor: pointer;">
                                Nama
                                @if($sortField === 'name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} float-right mt-1"></i>
                                @else
                                    <i class="fas fa-sort float-right mt-1"></i>
                                @endif
                            </th>
                            <th>Processor</th>
                            <th>RAM</th>
                            <th>SSD</th>
                            <th>Serial Number</th>
                            <th>Harga Beli</th>
                            <th>Harga Jual Disarankan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laptops as $laptop)
                        <tr>
                            <td>{{ $laptop->name }}</td>
                            <td>{{ $laptop->processor }}</td>
                            <td>{{ $laptop->ram }}</td>
                            <td>{{ $laptop->ssd }}</td>
                            <td>{{ $laptop->serial_number }}</td>
                            <td>Rp {{ number_format($laptop->purchase_price,0,',','.') }}</td>
                            <td class="font-weight-bold text-success">
                                Rp {{ number_format($laptop->suggested_selling_price,0,',','.') }}
                            </td>
                            <td>
                                @if($laptop->is_sold)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle mr-1"></i> Terjual
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-clock mr-1"></i> Belum Terjual
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    @if($laptop->qr_code_path)
                                    <a href="{{ Storage::url($laptop->qr_code_path) }}" download class="btn btn-sm btn-primary mr-1 mb-1">
                                        <i class="fas fa-qrcode mr-1"></i>
                                    </a>
                                    @endif
                                    @canAccess('show','used_laptops')
                                    <a 
                                        href="{{ route('used-laptop.show', $laptop->slug) }}"
                                        class="btn btn-sm btn-info mr-1 mb-1"
                                        title="Detail"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcanAccess
                                    @if($laptop->isAction())
                                    @canAccess('update','used_laptops')
                                    <a 
                                        href="{{ route('used-laptop.edit', $laptop->slug) }}"
                                        class="btn btn-sm btn-primary mr-1 mb-1"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcanAccess
                                    @canAccess('destroy','used_laptops')
                                    <form 
                                        method="POST"
                                        action="{{ route('used-laptop.destroy', $laptop->slug) }}"
                                        class="d-inline"
                                        title="Hapus"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger mb-1">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcanAccess
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-laptop fa-2x mb-2 text-muted"></i>
                                <p class="text-muted">Tidak ada data laptop ditemukan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer clearfix">
            <div class="float-right">
                {{ $laptops->links() }}
            </div>
            <div class="float-left mt-1">
                <select wire:model="perPage" class="form-control form-control-sm">
                    <option value="10">10 per halaman</option>
                    <option value="25">25 per halaman</option>
                    <option value="50">50 per halaman</option>
                    <option value="100">100 per halaman</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Form Modal -->
    <div class="modal fade" id="formModal" tabindex="-1" role="dialog" 
         aria-labelledby="formModalLabel" aria-hidden="true" wire:ignore.self>
        <!-- ... (modal content tetap sama) ... -->
    </div>

    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" 
         aria-labelledby="detailModalLabel" aria-hidden="true" wire:ignore.self>
        <!-- ... (modal content tetap sama) ... -->
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" 
         aria-labelledby="deleteModalLabel" aria-hidden="true" wire:ignore.self>
        <!-- ... (modal content tetap sama) ... -->
    </div>
</div>