<div>
    <div class="card mt-3 shadow-sm">
        <!-- Header -->
        <div class="card-header bg-gradient-primary">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title text-white mb-0">
                    <i class="fas fa-laptop mr-2"></i> Manajemen Laptop Bekas
                </h3>
                @canAccess('create','used_laptops')
                <a href="{{ route('used-laptop.create') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-plus mr-1"></i> Tambah Laptop
                </a>
                @endcanAccess
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card-body border-bottom bg-light">
            <div class="row">
                <!-- Search -->
                <div class="col-md-4 mb-3">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                        <input 
                            type="text" 
                            class="form-control" 
                            placeholder="Cari nama, brand, serial number..."
                            wire:model.debounce.300ms="search"
                        >
                    </div>
                    <div class="mt-1" wire:loading.delay wire:target="search">
                        <small class="text-muted">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            Mencari...
                        </small>
                    </div>
                </div>

                <!-- Warehouse Filter -->
                <div class="col-md-3 mb-3">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-warehouse"></i>
                            </span>
                        </div>
                        <select class="form-control form-control-sm" wire:model="warehouseFilter">
                            <option value="">Semua Warehouse</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Zone Filter -->
                <div class="col-md-2 mb-3">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                        </div>
                        <select class="form-control form-control-sm" wire:model="zoneFilter" 
                                {{ !$warehouseFilter ? 'disabled' : '' }}>
                            <option value="">Semua Zone</option>
                            @foreach($zones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-md-3 mb-3">
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <button type="button" 
                                class="btn {{ $statusFilter === '' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                wire:click="$set('statusFilter', '')">
                            Semua
                        </button>
                        <button type="button" 
                                class="btn {{ $statusFilter === 'unsold' ? 'btn-warning' : 'btn-outline-secondary' }}"
                                wire:click="$set('statusFilter', 'unsold')">
                            Belum Terjual
                        </button>
                        <button type="button" 
                                class="btn {{ $statusFilter === 'sold' ? 'btn-success' : 'btn-outline-secondary' }}"
                                wire:click="$set('statusFilter', 'sold')">
                            Terjual
                        </button>
                        <button type="button" 
                                class="btn {{ $statusFilter === 'inventory' ? 'btn-info' : 'btn-outline-secondary' }}"
                                wire:click="$set('statusFilter', 'inventory')">
                            Inventory
                        </button>
                    </div>
                </div>
            </div>

            <!-- Active Filters Badge -->
            @if($search || $warehouseFilter || $zoneFilter || $statusFilter)
            <div class="d-flex align-items-center flex-wrap mt-2">
                <small class="text-muted mr-2">Filter aktif:</small>
                @if($search)
                    <span class="badge badge-primary mr-1 mb-1">
                        <i class="fas fa-search mr-1"></i> "{{ $search }}"
                        <button type="button" class="close ml-1" wire:click="$set('search', '')">×</button>
                    </span>
                @endif
                @if($warehouseFilter)
                    <span class="badge badge-info mr-1 mb-1">
                        <i class="fas fa-warehouse mr-1"></i> Warehouse
                        <button type="button" class="close ml-1" wire:click="$set('warehouseFilter', '')">×</button>
                    </span>
                @endif
                @if($zoneFilter)
                    <span class="badge badge-info mr-1 mb-1">
                        <i class="fas fa-map-marker-alt mr-1"></i> Zone
                        <button type="button" class="close ml-1" wire:click="$set('zoneFilter', '')">×</button>
                    </span>
                @endif
                @if($statusFilter)
                    <span class="badge badge-secondary mr-1 mb-1">
                        <i class="fas fa-filter mr-1"></i> {{ ucfirst($statusFilter) }}
                        <button type="button" class="close ml-1" wire:click="$set('statusFilter', '')">×</button>
                    </span>
                @endif
                <button type="button" class="btn btn-link btn-sm text-danger" wire:click="resetFilters">
                    <i class="fas fa-times"></i> Reset Semua
                </button>
            </div>
            @endif

            <!-- Loading Indicator -->
            <div class="text-center mt-2" wire:loading.delay wire:target="statusFilter, warehouseFilter, zoneFilter">
                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                <small class="text-muted ml-1">Memfilter data...</small>
            </div>
        </div>
        
        <!-- Table Section -->
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light sticky-top">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th wire:click="sortBy('name')" style="cursor: pointer; min-width: 180px;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Laptop</span>
                                    @if($sortField === 'name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort text-muted"></i>
                                    @endif
                                </div>
                            </th>
                            <th style="min-width: 150px;">Spesifikasi</th>
                            <th style="min-width: 120px;">Serial Number</th>
                            <th style="min-width: 200px;">
                                <i class="fas fa-map-marked-alt text-primary mr-1"></i> Lokasi
                            </th>
                            <th class="text-right" style="min-width: 120px;">Harga Beli</th>
                            <th class="text-right" style="min-width: 120px;">
                                <i class="fas fa-map-marker-alt text-success mr-1"></i> Jakarta
                            </th>
                            <th class="text-right" style="min-width: 120px;">
                                <i class="fas fa-map-marker-alt text-danger mr-1"></i> Jambi
                            </th>
                            <th class="text-center" style="min-width: 100px;">Status</th>
                            <th class="text-center" style="min-width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laptops as $index => $laptop)
                        <tr>
                            <!-- No -->
                            <td class="text-center text-muted">
                                {{ $laptops->firstItem() + $index }}
                            </td>

                            <!-- Laptop Info -->
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($laptop->media->first())
                                        <img src="{{ Storage::url($laptop->media->first()->file_path) }}" 
                                             alt="{{ $laptop->name }}"
                                             class="img-thumbnail mr-2"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-light border rounded mr-2 d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-laptop text-muted"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-weight-bold">{{ $laptop->name }}</div>
                                        <small class="text-muted">
                                            <i class="fas fa-tag mr-1"></i>{{ $laptop->brand }}
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <!-- Spesifikasi -->
                            <td>
                                <small>
                                    <div><i class="fas fa-microchip text-primary mr-1"></i> {{ Str::limit($laptop->processor, 20) }}</div>
                                    <div><i class="fas fa-memory text-success mr-1"></i> {{ $laptop->ram }}</div>
                                    <div><i class="fas fa-hdd text-info mr-1"></i> {{ $laptop->ssd }}</div>
                                </small>
                            </td>

                            <!-- Serial Number -->
                            <td>
                                <code class="bg-light px-2 py-1 rounded">{{ $laptop->serial_number }}</code>
                            </td>

                            <!-- Lokasi: Warehouse → Zone → Rack -->
                            <td>
                                @if($laptop->rack)
                                    <div class="small">
                                        <div class="mb-1">
                                            <span class="badge badge-primary">
                                                <i class="fas fa-warehouse mr-1"></i>
                                                {{ $laptop->rack->zone->warehouse->name }}
                                            </span>
                                        </div>
                                        <div class="mb-1">
                                            <span class="badge badge-info">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                {{ $laptop->rack->zone->name }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-th mr-1"></i>
                                                {{ $laptop->rack->name }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted small">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        Belum ditentukan
                                    </span>
                                @endif
                            </td>

                            <!-- Harga Beli -->
                            <td class="text-right">
                                <span class="font-weight-bold">
                                    Rp {{ number_format($laptop->purchase_price, 0, ',', '.') }}
                                </span>
                            </td>

                            <!-- Harga Jakarta -->
                            <td class="text-right">
                                <span class="font-weight-bold text-success">
                                    Rp {{ number_format($laptop->jakarta_price, 0, ',', '.') }}
                                </span>
                            </td>

                            <!-- Harga Jambi -->
                            <td class="text-right">
                                <span class="font-weight-bold text-danger">
                                    Rp {{ number_format($laptop->jambi_price, 0, ',', '.') }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="text-center">
                                @if($laptop->is_sold === 1)
                                    <span class="badge badge-success badge-pill px-3 py-2">
                                        <i class="fas fa-check-circle mr-1"></i> Terjual
                                    </span>
                                @elseif($laptop->is_sold === 0)
                                    <span class="badge badge-warning badge-pill px-3 py-2">
                                        <i class="fas fa-clock mr-1"></i> Belum Terjual
                                    </span>
                                @else
                                    <span class="badge badge-info badge-pill px-3 py-2">
                                        <i class="fas fa-warehouse mr-1"></i> Inventory
                                    </span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    @if($laptop->qr_code_path)
                                    <a href="{{ Storage::url($laptop->qr_code_path) }}" 
                                       download 
                                       class="btn btn-sm btn-outline-primary mb-1 mr-1"
                                       title="Download QR Code">
                                        <i class="fas fa-qrcode"></i>
                                    </a>
                                    @endif

                                    @if($isShow)
                                    <a href="{{ route('used-laptop.show', $laptop->slug) }}"
                                       class="btn btn-sm btn-outline-info mb-1 mr-1"
                                       title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endif

                                    @if($laptop->isAction())
                                        @if($isEdit)
                                        <a href="{{ route('used-laptop.edit', $laptop->slug) }}"
                                           class="btn btn-sm btn-outline-warning mb-1 mr-1"
                                           title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif

                                        @if($isDestroy)
                                        <form method="POST"
                                              action="{{ route('used-laptop.destroy', $laptop->slug) }}"
                                              class="d-inline mb-1 mr-1"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus laptop {{ $laptop->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <h5>Tidak ada data laptop</h5>
                                    <p class="mb-0">
                                        @if($search || $warehouseFilter || $zoneFilter || $statusFilter)
                                            Coba ubah filter pencarian Anda
                                        @else
                                            Belum ada laptop yang ditambahkan
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Footer with Pagination -->
        <div class="card-footer bg-light">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <select wire:model="perPage" class="form-control form-control-sm">
                        <option value="10">10 per halaman</option>
                        <option value="25">25 per halaman</option>
                        <option value="50">50 per halaman</option>
                        <option value="100">100 per halaman</option>
                    </select>
                </div>
                <div class="col-md-6 text-center">
                    <small class="text-muted">
                        Menampilkan {{ $laptops->firstItem() ?? 0 }} sampai {{ $laptops->lastItem() ?? 0 }} 
                        dari {{ $laptops->total() }} laptop
                    </small>
                </div>
                <div class="col-md-3">
                    <div class="float-right">
                        {{ $laptops->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Sticky header */
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Table hover effect */
    .table-hover tbody tr:hover {
        background-color: rgba(0,123,255,0.05);
        transition: background-color 0.2s ease;
    }

    /* Badge styling */
    .badge-pill {
        font-size: 0.85rem;
        font-weight: 500;
    }

    /* Button group spacing */
    .btn-group .btn {
        margin: 0 1px;
    }

    /* Loading overlay */
    [wire\:loading] {
        opacity: 0.6;
        pointer-events: none;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .btn-group {
            flex-direction: column;
        }
        
        .btn-group .btn {
            margin: 2px 0;
        }
    }

    /* Custom scrollbar */
    .table-responsive::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>