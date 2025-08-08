<div>
    <div class="card mt-3">
        <div class="card-header bg-primary">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-white">
                    <i class="fas fa-box mr-2"></i> Manajemen Barang
                </h3>
                @canAccess('create','used_items')
                <a href="{{ route('used-item.create') }}" class="btn btn-success">
                    <i class="fas fa-plus mr-1"></i> Tambah Barang
                </a>
                @endcanAccess
            </div>
            <div class="card-tools mt-2">
                <div class="input-group input-group-sm">
                    <input 
                        type="text" 
                        class="form-control" 
                        placeholder="Cari barang..."
                        wire:model.debounce.300ms="search"
                    >
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gabungan Semua Filter -->
        <div class="card-header bg-light py-2">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="d-flex flex-wrap align-items-center mb-2 mb-md-0">
                    <span class="mr-2 font-weight-bold">Filter:</span>
                    
                    <!-- Filter Status -->
                    <div class="btn-group btn-group-sm mr-3" role="group">
                        <button type="button" 
                                class="btn {{ $statusFilter === '' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                wire:click="$set('statusFilter', '')">
                            Semua Status
                        </button>
                        <button type="button" 
                                class="btn {{ $statusFilter === 'unsold' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                wire:click="$set('statusFilter', 'unsold')">
                            <i class="fas fa-times-circle mr-1"></i> Belum Terjual
                        </button>
                        <button type="button" 
                                class="btn {{ $statusFilter === 'sold' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                wire:click="$set('statusFilter', 'sold')">
                            <i class="fas fa-check-circle mr-1"></i> Terjual
                        </button>
                    </div>
                    
                    <!-- Filter User -->
                    <div class="d-flex align-items-center mr-3">
                        <span class="mr-2 font-weight-bold">User:</span>
                        <select wire:model="userFilter" class="form-control form-control-sm">
                            <option value="">Semua User</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Filter Tanggal -->
                <div class="d-flex align-items-center ml-auto">
                    <span class="mr-2 font-weight-bold">Tanggal:</span>
                    <div class="d-flex flex-wrap">
                        <input 
                            type="date" 
                            wire:model="startDate" 
                            class="form-control form-control-sm mr-2"
                            max="{{ now()->toDateString() }}"
                            style="max-width: 150px;"
                        >
                        <span class="mr-2 align-self-center">s/d</span>
                        <input 
                            type="date" 
                            wire:model="endDate" 
                            class="form-control form-control-sm mr-2"
                            max="{{ now()->toDateString() }}"
                            style="max-width: 150px;"
                        >
                        @if($startDate || $endDate)
                            <button 
                                wire:click="resetDateFilter" 
                                class="btn btn-sm btn-outline-danger"
                            >
                                <i class="fas fa-times"></i> Reset
                            </button>
                        @endif
                    </div>
                </div>
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
                            <th>
                                Serial Number
                            </th>
                            <th>Harga Beli</th>
                            <th>Harga Jual Disarankan</th>
                            <th>Status</th>
                            <th>
                                Dibuat
                            </th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->serial_number }}</td>
                            <td>Rp {{ number_format($item->purchase_price,0,',','.') }}</td>
                            <td class="font-weight-bold text-success">
                                Rp {{ number_format($item->suggested_selling_price,0,',','.') }}
                            </td>
                            <td>
                                @if($item->is_sold)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle mr-1"></i> Terjual
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-clock mr-1"></i> Belum Terjual
                                    </span>
                                @endif
                            </td>
                            <td>{{ $item->created_at->format('d-m-Y H:i') }} ({{ $item->user->name }})</td>
                            <td>
                                <div class="btn-group">
                                    @if($item->qr_code_path)
                                    <a href="{{ Storage::url($item->qr_code_path) }}" download class="btn btn-sm btn-outline-primary mr-1 mb-1">
                                        <i class="fas fa-qrcode mr-1"></i>
                                    </a>
                                    @endif
                                    @canAccess('show','used_items')
                                    <a 
                                        href="{{ route('used-item.show', $item->slug) }}"
                                        class="btn btn-sm btn-info mr-1 mb-1"
                                        title="Detail"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcanAccess
                                    @if($item->isAction())
                                    @canAccess('update','used_items')
                                    <a 
                                        href="{{ route('used-item.edit', $item->slug) }}"
                                        class="btn btn-sm btn-primary mr-1 mb-1"
                                        title="Edit"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcanAccess
                                    @canAccess('destroy','used_items')
                                    <form 
                                        method="POST"
                                        action="{{ route('used-item.destroy', $item->slug) }}"
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
                                <i class="fas fa-box fa-2x mb-2 text-muted"></i>
                                <p class="text-muted">Tidak ada data ditemukan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer clearfix">
            <div class="float-right">
                {{ $items->links() }}
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
</div>