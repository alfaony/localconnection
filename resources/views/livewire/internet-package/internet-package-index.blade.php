<div>
    @section('content_header')
        <div class="d-flex justify-content-between align-items-center">
            <h3>Paket Internet</h3>
            @canAccess('create', 'data_centers')
            <a href="{{ route('internet-package.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus mr-1"></i> Paket Internet
            </a>
            @endcanAccess
        </div>
    @stop
    @include('components.alert')

    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Daftar </h5>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input wire:model.debounce.300ms="search" type="text" class="form-control" placeholder="Cari paket...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model="selectedType" class="form-control">
                        <option value="">Semua Jenis</option>
                        <option value="dedicated">Dedicated</option>
                        <option value="broadband">Broadband</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model="activeFilter" class="form-control">
                        <option value="all">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="inactive">Non-Aktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model="perPage" class="form-control">
                        <option value="10">10 per halaman</option>
                        <option value="25">25 per halaman</option>
                        <option value="50">50 per halaman</option>
                        <option value="100">100 per halaman</option>
                    </select>
                </div>
            </div>

            @if(session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('name')" style="cursor: pointer;">
                                Nama Paket
                                @if($sortField === 'name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </th>
                            <th class="text-center">Bandwidth</th>
                            <th>Jenis</th>
                            <th wire:click="sortBy('price')" style="cursor: pointer;">
                                Harga
                                @if($sortField === 'price')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @endif
                            </th>
                            <th>Harga Nett</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($packages as $package)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $package->name }}</div>
                                    @if($package->description)
                                        <div class="text-muted small mt-1">{{ \Illuminate\Support\Str::limit($package->description, 50) }}</div>
                                    @endif
                                </td>
                                <td class="text-center">{{ $package->bandwidth }} Mbps</td>
                                <td>
                                    <span class="badge bg-{{ $package->jenis === 'Dedicated' ? 'info' : 'success' }}">
                                        {{ $package->jenis }}
                                    </span>
                                </td>
                                <td>Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($package->price_nett, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input" type="checkbox" 
                                            id="status-{{ $package->id }}" 
                                            wire:change="toggleStatus({{ $package->id }})"
                                            {{ $package->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status-{{ $package->id }}">
                                            {{ $package->is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </label>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('internet-package.edit', $package->id) }}" 
                                        class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button wire:click="delete({{ $package->id }})" 
                                            class="btn btn-sm btn-danger" 
                                            title="Hapus"
                                            onclick="return confirm('Yakin menghapus paket?') || event.stopImmediatePropagation()">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-box-open fa-2x mb-3 text-muted"></i>
                                    <h5>Tidak ada paket internet ditemukan</h5>
                                    <p class="text-muted">Coba ubah filter pencarian Anda</p>
                                    <a href="{{ route('internet-package.create') }}" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus me-2"></i>Tambah Paket Baru
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>Menampilkan {{ $packages->firstItem() }} hingga {{ $packages->lastItem() }} dari {{ $packages->total() }} data</div>
                <div>
                    {{ $packages->links() }}
                </div>
            </div>
        </div>
    </div>
</div>