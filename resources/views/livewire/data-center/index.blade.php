@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h3>Data Centers</h3>
        @canAccess('create', 'data_centers')
        <a href="{{ route('data-center.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i> Add New
        </a>
        @endcanAccess
    </div>
@stop
@include('components.alert')

@canAccess('index', 'data_centers')
<div class="card card-primary card-outline card-outline-tabs">    
    <div class="card-body">
        <div class="tab-content">
            <div class="tab-pane fade show active" id="dataCenterTab" role="tabpanel">
                
                {{-- Search Box --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input 
                                type="text" 
                                class="form-control" 
                                placeholder="Cari berdasarkan nama..." 
                                wire:model.debounce.500ms="search"
                            >
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <select class="form-control" wire:model="perPage">
                                <option value="10">10 per halaman</option>
                                <option value="25">25 per halaman</option>
                                <option value="50">50 per halaman</option>
                                <option value="100">100 per halaman</option>
                            </select>
                        </div>
                    </div>
                    @if($search)
                    <div class="col-md-6 text-right">
                        <button class="btn btn-sm btn-outline-danger" wire:click="clearSearch">
                            <i class="fas fa-times mr-1"></i> Hapus Pencarian
                        </button>
                    </div>
                    @endif
                </div>
                
                @if($dataCenters->isEmpty())
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle mr-2"></i> 
                        @if($search)
                            Tidak ditemukan data center dengan nama "{{ $search }}"
                        @else
                            Belum ada data center tersedia
                        @endif
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th wire:click="sortBy('name')" style="cursor: pointer;">
                                        Nama 
                                        @if($sortField === 'name')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th class="text-right" wire:click="sortBy('capacity_mb')" style="cursor: pointer;">
                                        Kapasitas (MB)
                                        @if($sortField === 'capacity_mb')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th class="text-right">Biaya/Bulan</th>
                                    <th wire:click="sortBy('tanggal_tagihan')" style="cursor: pointer;">
                                        Tanggal Tagihan
                                        @if($sortField === 'tanggal_tagihan')
                                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                        @else
                                            <i class="fas fa-sort"></i>
                                        @endif
                                    </th>
                                    <th class="text-center" style="width: 120px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dataCenters as $index => $dc)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $dc->name }}</td>
                                    <td class="text-right">{{ number_format($dc->capacity_mb,0,',','.') }}</td>
                                    <td class="text-right">Rp{{ number_format($dc->cost_per_month, 2, ',', '.') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($dc->tanggal_tagihan)->locale('id_ID')->isoFormat('D MMMM Y') }}</td>
                                    <td class="text-center py-1">
                                        @canAccess('update', 'data_centers')
                                        @canAccess('edit', 'data_centers')
                                        <a href="{{ route('data-center.edit', $dc) }}" 
                                            class="btn btn-sm btn-warning" 
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcanAccess
                                        @endcanAccess

                                        @canAccess('destroy', 'data_centers')
                                        <button wire:click="confirmDelete({{ $dc->id }})" 
                                                class="btn btn-sm btn-danger"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
    
    @if($dataCenters->hasPages())
    <div class="card-footer clearfix">
        <div class="row">
            <div class="col-md-6">
                <p>Menampilkan {{ $dataCenters->firstItem() }} - {{ $dataCenters->lastItem() }} dari {{ $dataCenters->total() }} data</p>
            </div>
            <div class="col-md-6 text-right">
                {{ $dataCenters->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endcanAccess

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
<script>
    // Konfirmasi sebelum menghapus
    window.addEventListener('confirmDelete', event => {
        Swal.fire({
            title: 'Hapus Data Center?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) 
            {
                Livewire.emitTo('data-center.index', 'delete', event.detail.id);
            }
        });
    });

    // Notifikasi setelah berhasil menghapus
    window.addEventListener('showDeleteNotification', event => {
        console.log("coba");
        
        Swal.fire({
            title: 'Terhapus!',
            text: event.detail.message,
            icon: 'success',
            confirmButtonColor: '#3085d6',
        });
    });
</script>
@endpush
