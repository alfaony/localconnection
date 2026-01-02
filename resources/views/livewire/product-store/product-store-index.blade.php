<div>
    <div class="row">
        <div class="col-md-12 mt-3">
            @include('components.alert')
            <div class="card shadow-sm">
                <!-- Header -->
                <div class="card-header bg-gradient-primary">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3 class="card-title text-white mb-0">
                                <i class="fas fa-boxes mr-2"></i> Manajemen Produk Toko
                            </h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <button 
                                wire:click="exportProducts" 
                                wire:loading.attr="disabled"
                                wire:target="exportProducts"
                                class="btn btn-success"
                                title="Export data sesuai filter yang aktif"
                                {{ $isExporting ? 'disabled' : '' }}>
                                <i class="fas fa-file-excel" wire:loading.remove wire:target="exportProducts"></i>
                                <i class="fas fa-spinner fa-spin" wire:loading wire:target="exportProducts"></i>
                                <span wire:loading.remove wire:target="exportProducts">
                                    Export to Excel
                                    @if($search || $categoryFilter || $warehouseFilter || $zoneFilter)
                                        <small>(Filtered)</small>
                                    @endif
                                </span>
                                <span wire:loading wire:target="exportProducts">Processing...</span>
                            </button>
                            @canAccess('import','product_stores')
                            <button type="button" 
                                    class="btn btn-success" 
                                    wire:click="toggleImportSection">
                                <i class="bi bi-upload"></i> 
                                {{ $showImportSection ? 'Hide Import' : 'Import CSV' }}
                            </button>
                            @endcanAccess

                            @canAccess('create','product_stores')
                            <a href="{{ route('product-store.create') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-plus-circle mr-1"></i> Tambah Produk
                            </a>
                            @endcanAccess

                            @canAccess('print','product_stores')
                            <a href="{{ route('product-store.print') }}" class="btn btn-info btn-sm ml-2">
                                <i class="fas fa-print mr-1"></i> Print Barcode
                            </a>
                            @endcanAccess
                        </div>
                    </div>
                </div>

                @canAccess('import','product_stores')
                @if($showImportSection)
                <div class="card-body">
                    <!-- Panduan -->
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Panduan Import:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Download template CSV terlebih dahulu</li>
                            <li>Isi data sesuai format template</li>
                            <li>Upload file CSV yang sudah diisi</li>
                            <li>Tunggu proses import selesai</li>
                        </ol>
                    </div>

                    <!-- Download Template -->
                    <div class="mb-3">
                        <button wire:click="downloadTemplate" class="btn btn-outline-success">
                            <i class="bi bi-download"></i> Download Template CSV
                        </button>
                    </div>
                    <!-- Upload Form -->
                    <!-- Upload Form -->
                    @if(!$isImporting)
                    <form wire:submit.prevent="import">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="csvFile" class="form-label">Pilih File CSV</label>
                                    <input type="file" 
                                        class="form-control @error('csvFile') is-invalid @enderror" 
                                        wire:model="csvFile" 
                                        accept=".csv"
                                        id="csvFileInput"
                                        {{ $uploadingFile ? 'disabled' : '' }}>
                                    @error('csvFile')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Format: CSV, Maksimal 10MB</div>
                                    
                                    <!-- Loading saat file sedang diupload dan divalidasi -->
                                    @if($uploadingFile && !$isFileReady)
                                    <div class="mt-2" wire:poll.500ms="checkFileReady">
                                        <div class="alert alert-info py-2 mb-0 d-flex align-items-center">
                                            <div class="spinner-border spinner-border-sm me-2"></div>
                                            <div>
                                                <strong>Mengupload dan memvalidasi file...</strong>
                                                <small class="d-block text-muted">Mohon tunggu, sedang memproses file Anda</small>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- File uploaded and ready -->
                                    @if($isFileReady && $csvFile)
                                    <div class="mt-2">
                                        <div class="alert alert-success py-2 mb-0 d-flex align-items-center">
                                            <i class="bi bi-check-circle-fill me-2 text-success" style="font-size: 1.2rem;"></i>
                                            <div class="flex-grow-1">
                                                <strong>File siap diimport!</strong>
                                                <small class="d-block">{{ $csvFile->getClientOriginalName() }}</small>
                                            </div>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    wire:click="resetImport"
                                                    title="Hapus file">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">&nbsp;</label>
                                
                                <button type="submit" 
                                        class="btn btn-primary w-100" 
                                        wire:loading.attr="disabled"
                                        wire:target="import"
                                        {{ !$isFileReady || $uploadingFile ? 'disabled' : '' }}>
                                    
                                    <!-- Loading saat import -->
                                    <span wire:loading wire:target="import">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        Memproses import...
                                    </span>
                                    
                                    <!-- Default text -->
                                    <span wire:loading.remove wire:target="import">
                                        @if($uploadingFile)
                                            <span class="spinner-border spinner-border-sm me-2"></span>
                                            Menunggu file...
                                        @elseif($isFileReady)
                                            <i class="bi bi-upload"></i> Mulai Import
                                        @else
                                            <i class="bi bi-upload"></i> Upload & Import
                                        @endif
                                    </span>
                                </button>
                                
                                <!-- Helper text -->
                                <div class="mt-2 text-center">
                                    @if($uploadingFile && !$isFileReady)
                                        <small class="text-warning d-flex align-items-center justify-content-center">
                                            <span class="spinner-border spinner-border-sm me-2"></span>
                                            <span>Memvalidasi file...</span>
                                        </small>
                                    @elseif($isFileReady)
                                        <small class="text-success">
                                            <i class="bi bi-check-circle-fill"></i> File valid dan siap diimport
                                        </small>
                                    @else
                                        <small class="text-muted">
                                            <i class="bi bi-arrow-up"></i> Pilih file CSV terlebih dahulu
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                    @endif

                    <!-- Progress Section -->
                    @if($isImporting && $importProgress)
                    <div class="mt-4">
                        <hr>
                        <h6 class="mb-3">
                            <i class="bi bi-hourglass-split"></i> Progress Import
                        </h6>
                        
                        <!-- Progress Bar -->
                        <div class="progress mb-3" style="height: 35px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated 
                                        bg-{{ $this->getStatusColor($importProgress['status']) }}" 
                                    role="progressbar" 
                                    style="width: {{ $importProgress['percentage'] }}%"
                                    aria-valuenow="{{ $importProgress['percentage'] }}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                <strong>{{ $importProgress['percentage'] }}%</strong>
                            </div>
                        </div>

                        <!-- Status Cards -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body py-3">
                                        <span class="badge bg-{{ $this->getStatusColor($importProgress['status']) }} fs-6">
                                            {{ strtoupper($importProgress['status']) }}
                                        </span>
                                        <div class="text-muted small mt-1">Status</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body py-3">
                                        <h4 class="mb-0">{{ $importProgress['total'] }}</h4>
                                        <div class="text-muted small">Total Data</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center border-success">
                                    <div class="card-body py-3">
                                        <h4 class="mb-0 text-success">{{ $importProgress['success'] }}</h4>
                                        <div class="text-muted small">Berhasil</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center border-danger">
                                    <div class="card-body py-3">
                                        <h4 class="mb-0 text-danger">{{ $importProgress['failed'] }}</h4>
                                        <div class="text-muted small">Gagal</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mb-3">
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> 
                                Terakhir update: {{ \Carbon\Carbon::parse($importProgress['updated_at'])->format('d/m/Y H:i:s') }}
                            </small>
                        </div>

                        <!-- Error List -->
                        @if(!empty($importProgress['errors']) && is_array($importProgress['errors']) && count($importProgress['errors']) > 0)
                        <div class="alert alert-warning">
                            <h6 class="alert-heading">
                                <i class="bi bi-exclamation-triangle"></i> 
                                Detail Error ({{ count($importProgress['errors']) }} baris)
                            </h6>
                            <div style="max-height: 250px; overflow-y: auto;">
                                @foreach($importProgress['errors'] as $error)
                                    @if(is_array($error))
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex">
                                            <div class="badge bg-danger me-2">Baris {{ $error['row'] ?? '?' }}</div>
                                            <div class="flex-grow-1">
                                                <strong>{{ $error['message'] ?? 'Unknown error' }}</strong>
                                                @if(isset($error['data']))
                                                    <br><small class="text-muted">Data: {{ $error['data'] }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
                @endcanAccess

                <!-- Filters Section -->
                <div class="card-body border-bottom bg-light">
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <i class="icon fas fa-check mr-2"></i> {{ session('message') }}
                        </div>
                    @endif

                    <div class="row">
                        <!-- Search -->
                        <div class="col-md-3 mb-3">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                                <input type="text" wire:model.debounce.300ms="search" 
                                       class="form-control" 
                                       placeholder="Cari nama, barcode...">
                            </div>
                            <div class="mt-1" wire:loading.delay wire:target="search">
                                <small class="text-muted d-inline-flex align-items-center">
                                    <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                                    <span>Mencari...</span>
                                </small>
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="col-md-2 mb-3">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-th-large"></i>
                                    </span>
                                </div>
                                <select wire:model="categoryFilter" class="form-control form-control-sm">
                                    <option value="">Semua Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Warehouse Filter -->
                        <div class="col-md-2 mb-3">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-warehouse"></i>
                                    </span>
                                </div>
                                <select wire:model="warehouseFilter" class="form-control form-control-sm">
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
                                <select wire:model="zoneFilter" class="form-control form-control-sm" 
                                        {{ !$warehouseFilter ? 'disabled' : '' }}>
                                    <option value="">Semua Zone</option>
                                    @foreach($zones as $zone)
                                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Per Page -->
                        <div class="col-md-3 mb-3">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-list"></i>
                                    </span>
                                </div>
                                <select wire:model="perPage" class="form-control form-control-sm">
                                    <option value="10">10 per halaman</option>
                                    <option value="25">25 per halaman</option>
                                    <option value="50">50 per halaman</option>
                                    <option value="100">100 per halaman</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Active Filters Badge -->
                    @if($search || $categoryFilter || $warehouseFilter || $zoneFilter)
                    <div class="d-flex align-items-center flex-wrap mt-2 pt-2 border-top">
                        <small class="text-muted mr-2">Filter aktif:</small>
                        @if($search)
                            <span class="badge badge-info mr-1 mb-1">
                                <i class="fas fa-search mr-1"></i> "{{ $search }}"
                                <button type="button" class="close ml-1" wire:click="$set('search', '')">×</button>
                            </span>
                        @endif
                        @if($categoryFilter)
                            <span class="badge badge-primary mr-1 mb-1">
                                <i class="fas fa-th-large mr-1"></i> Kategori
                                <button type="button" class="close ml-1" wire:click="$set('categoryFilter', '')">×</button>
                            </span>
                        @endif
                        @if($warehouseFilter)
                            <span class="badge badge-success mr-1 mb-1">
                                <i class="fas fa-warehouse mr-1"></i> Warehouse
                                <button type="button" class="close ml-1" wire:click="$set('warehouseFilter', '')">×</button>
                            </span>
                        @endif
                        @if($zoneFilter)
                            <span class="badge badge-secondary mr-1 mb-1">
                                <i class="fas fa-map-marker-alt mr-1"></i> Zone
                                <button type="button" class="close ml-1" wire:click="$set('zoneFilter', '')">×</button>
                            </span>
                        @endif
                        <button type="button" class="btn btn-link btn-sm text-danger" wire:click="resetFilters">
                            <i class="fas fa-times"></i> Reset Semua
                        </button>
                    </div>
                    @endif

                    <!-- Loading Indicator -->
                    <div class="text-center mt-2" wire:loading.delay wire:target="categoryFilter, warehouseFilter, zoneFilter">
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
                                    <th wire:click="sortBy('name')" style="cursor: pointer; min-width: 200px;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Produk</span>
                                            @if($sortField === 'name')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort text-muted"></i>
                                            @endif
                                        </div>
                                    </th>
                                    <th style="min-width: 120px;">Barcode</th>
                                    <th style="min-width: 100px;">Kategori</th>
                                    <th style="min-width: 100px;">Merk</th>
                                    <th style="min-width: 200px;">
                                        <i class="fas fa-map-marked-alt text-primary mr-1"></i> Lokasi
                                    </th>
                                    <th style="min-width: 100px;">Varian</th>
                                    <th class="text-right" wire:click="sortBy('selling_price')" style="cursor: pointer; min-width: 120px;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Harga Jual</span>
                                            @if($sortField === 'selling_price')
                                                <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort text-muted"></i>
                                            @endif
                                        </div>
                                    </th>
                                    <th class="text-center" style="min-width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $index => $product)
                                <tr>
                                    <!-- No -->
                                    <td class="text-center text-muted align-middle">
                                        {{ $products->firstItem() + $index }}
                                    </td>

                                    <!-- Produk Info -->
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light border rounded mr-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px; min-width: 40px;">
                                                <i class="fas fa-box text-muted"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $product->name }}</div>
                                                @if($product->specification || $product->code)
                                                    <small class="text-muted">
                                                        {{ Str::limit($product->specification, 30) }}  {{ $product->code }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Barcode -->
                                    <td class="align-middle">
                                        <code class="bg-light px-2 py-1 rounded">
                                            {{ $product->barcode ?? '-' }}
                                        </code>
                                    </td>

                                    <!-- Kategori -->
                                    <td class="align-middle">
                                        <span class="badge badge-info px-2 py-1">
                                            {{ $product->category->name ?? '-' }}
                                        </span>
                                    </td>

                                    <!-- Merk -->
                                    <td class="align-middle">
                                        <span class="badge badge-secondary px-2 py-1">
                                            {{ $product->brand->name ?? '-' }}
                                        </span>
                                    </td>

                                    <!-- Lokasi: Warehouse → Zone → Rack -->
                                    <td class="align-middle">
                                        @if($product->rack)
                                            <div class="small">
                                                <div class="mb-1">
                                                    <span class="badge badge-primary">
                                                        <i class="fas fa-warehouse mr-1"></i>
                                                        {{ $product->rack->zone->warehouse->name }}
                                                    </span>
                                                </div>
                                                <div class="mb-1">
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                                        {{ $product->rack->zone->name }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-th mr-1"></i>
                                                        {{ $product->rack->name }}
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

                                    <!-- Varian -->
                                    <td class="align-middle">
                                        {{ $product->variant ?? '-' }}
                                    </td>

                                    <!-- Harga Jual -->
                                    <td class="text-right align-middle">
                                        <span class="font-weight-bold text-success">
                                            Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="text-center align-middle">
                                        <div class="btn-group" role="group">
                                            @if($isShow)
                                            <a href="{{ route('product-store.show', $product->id) }}" 
                                               class="btn btn-sm btn-outline-info mb-1 mr-1" 
                                               title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endif

                                            @if($isEdit)
                                            <a href="{{ route('product-store.edit', $product->id) }}" 
                                               class="btn btn-sm btn-outline-warning mb-1 mr-1" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif

                                            @if($isDestroy)
                                            <button wire:click="confirmDelete('{{ $product->id }}')" 
                                                    class="btn btn-sm btn-outline-danger mb-1 mr-1" 
                                                    title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <h5>Tidak ada produk</h5>
                                            <p class="mb-0">
                                                @if($search || $categoryFilter || $warehouseFilter || $zoneFilter)
                                                    Coba ubah filter pencarian Anda
                                                @else
                                                    Belum ada produk yang ditambahkan
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
                        <div class="col-md-6">
                            <small class="text-muted">
                                Menampilkan {{ $products->firstItem() ?? 0 }} sampai {{ $products->lastItem() ?? 0 }} 
                                dari {{ $products->total() }} produk
                            </small>
                        </div>
                        <div class="col-md-6">
                            <div class="float-right">
                                {{ $products->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($isExporting)
<div class="alert alert-info alert-dismissible fade show position-fixed" 
     style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" 
     role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-spinner fa-spin mr-2"></i>
        <div>
            <strong>Export sedang diproses...</strong><br>
            <small>Anda akan menerima notifikasi di inbox ketika selesai.</small>
        </div>
    </div>
</div>
@endif
@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('livewire:load', function () {
        
        window.addEventListener('export-started', event => {
            Swal.fire({
                icon: 'success',
                title: 'Export Dimulai',
                html: event.detail.message,
                timer: 5000,
                timerProgressBar: true,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        });

        // Event ketika export gagal
        window.addEventListener('export-failed', event => {
            Swal.fire({
                icon: 'error',
                title: 'Export Gagal',
                text: event.detail.message,
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33'
            });
        });
        // File ready notification
        window.addEventListener('file-ready', event => {
            console.log('File ready:', event.detail);
            
            // Toast notification
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
            
            Toast.fire({
                icon: 'success',
                title: `File siap!`,
                text: event.detail.filename
            });
        });

        // File input change - reset on new file selection
        const fileInput = document.getElementById('csvFileInput');
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                console.log('New file selected, waiting for upload...');
            });
        }

        // Import started
        window.addEventListener('import-started', event => {
            Swal.fire({
                icon: 'info',
                title: 'Import Dimulai',
                text: `Total ${event.detail.total_rows} data akan diimport`,
                timer: 2000,
                showConfirmButton: false
            });
        });

        // Import Progress
        let progressInterval = null;

        window.addEventListener('start-progress-check', event => {
            if (progressInterval) {
                clearInterval(progressInterval);
            }
            
            progressInterval = setInterval(() => {
                @this.call('checkProgress');
            }, 1000);
        });

        // Import completed
        window.addEventListener('import-completed', event => {
            // Clear interval
            if (progressInterval) {
                clearInterval(progressInterval);
                progressInterval = null;
            }
            
            const progress = event.detail.progress;
            
            // Clear file input
            if (fileInput) {
                fileInput.value = '';
            }
            
            Swal.fire({
                icon: progress.failed > 0 ? 'warning' : 'success',
                title: 'Import Selesai!',
                html: `
                    <div class="text-start">
                        <p class="mb-2"><strong>Rangkuman Import:</strong></p>
                        <ul class="list-unstyled">
                            <li>📊 <strong>Total Data:</strong> ${progress.total}</li>
                            <li>✅ <strong>Berhasil:</strong> <span class="text-success">${progress.success}</span></li>
                            <li>❌ <strong>Gagal:</strong> <span class="text-danger">${progress.failed}</span></li>
                        </ul>
                        ${progress.failed > 0 && progress.errors && progress.errors.length > 0 ? `
                            <hr>
                            <p class="text-warning mb-2"><strong>⚠️ Detail Error:</strong></p>
                            <div style="max-height: 200px; overflow-y: auto; text-align: left;">
                                ${progress.errors.map(err => `
                                    <small>
                                        <strong>Baris ${err.row}:</strong> ${err.message}
                                        ${err.data ? `<br><em class="text-muted">Data: ${err.data}</em>` : ''}
                                    </small>
                                    <hr class="my-1">
                                `).join('')}
                            </div>
                        ` : ''}
                    </div>
                `,
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                width: '600px'
            }).then(() => {
                @this.call('$refresh');
            });
        });

        // Cleanup
        window.addEventListener('beforeunload', () => {
            if (progressInterval) {
                clearInterval(progressInterval);
            }
        });

        // Delete confirmation
        window.addEventListener('swal:confirm', function (event) {
            Swal.fire({
                title: event.detail.title || 'Apakah Anda yakin?',
                text: event.detail.text || "Data yang dihapus tidak dapat dikembalikan!",
                icon: event.detail.type || 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emit('deleteProduct', event.detail.id);
                }
            });
        });

        Livewire.on('productDeleted', () => {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Produk berhasil dihapus',
                timer: 2000,
                showConfirmButton: false
            });
        });
    });
</script>
@endpush

@push('css')
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
    .badge {
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

    /* Active filter badges */
    .badge .close {
        font-size: 1.2rem;
        line-height: 1;
        opacity: 0.7;
    }

    .badge .close:hover {
        opacity: 1;
    }

    /* Gradient header */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
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
        background: #007bff;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #0056b3;
    }

    /* Code tag styling */
    code {
        font-size: 0.875rem;
        color: #495057;
    }
</style>
@endpush
