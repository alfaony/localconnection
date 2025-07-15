@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Manajemen POP ( Point Of Presence ) </h1>

        @canAccess('create', 'pops')
        <a href="{{ route('pops.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah POP
        </a>
        @endcanAccess
    </div>
@stop

@include('components.alert')

@canAccess('index', 'pops')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <div class="input-group">
                    <input 
                        type="text" 
                        wire:model.debounce.300ms="search"
                        placeholder="Cari POP..."
                        class="form-control"
                    >
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <select wire:model="perPage" class="form-control">
                    <option value="10">10 per halaman</option>
                    <option value="25">25 per halaman</option>
                    <option value="50">50 per halaman</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th wire:click="sortBy('name')" style="cursor: pointer;">
                        Nama POP
                        @if($sortField === 'name')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @else
                            <i class="fas fa-sort"></i>
                        @endif
                    </th>
                    <th>Kapasitas</th>
                    <th>Jalur Masuk</th>
                    <th wire:click="sortBy('monthly_cost')" style="cursor: pointer;">
                        Biaya/Bulan
                        @if($sortField === 'monthly_cost')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @else
                            <i class="fas fa-sort"></i>
                        @endif
                    </th>
                    <th wire:click="sortBy('lease_expiration_date')" style="cursor: pointer;">
                        Perpanjang Sewa
                        @if($sortField === 'lease_expiration_date')
                            <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                        @else
                            <i class="fas fa-sort"></i>
                        @endif
                    </th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pops as $pop)
                    <tr>
                        <td>
                            <div class="font-weight-bold">{{ $pop->name }}</div>
                            <div class="text-muted small">{{ Str::limit($pop->address, 40) }}</div>
                        </td>
                        <td>
                            <span class="badge bg-primary">
                                {{ number_format($pop->capacity_mb) }} MB
                            </span>
                        </td>
                        <td>
                            @if( $pop->entries->count() > 0)
                                <ul class="list-unstyled mb-0">
                                    @foreach($pop->entries as $entry)
                                        <li>
                                            <span class="badge bg-indigo">
                                                {{ $entry->name }}: {{ number_format($entry->capacity_mb) }}MB
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="font-weight-bold">
                                Rp {{ number_format($pop->monthly_cost, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>
                            <div>
                                {{ \Carbon\Carbon::parse($pop->lease_expiration_date)->format('d M Y') }}
                                @if(\Carbon\Carbon::parse($pop->lease_expiration_date)->isPast())
                                    <span class="badge bg-danger">
                                        Expired
                                    </span>
                                @elseif(\Carbon\Carbon::parse($pop->lease_expiration_date)->diffInDays(now()) < 30)
                                    <span class="badge bg-warning text-dark">
                                        {{ \Carbon\Carbon::parse($pop->lease_expiration_date)->diffInDays(now()) }} hari
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="py-0 align-middle">
                            <div class="btn-group btn-group-sm">
                                @canAccess('update','pops')
                                <a href="{{ route('pops.edit', $pop->id) }}" class="btn btn-info mb-1 mr-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcanAccess
                                @canAccess('destroy','pops')
                                <button 
                                    wire:click="confirmDelete({{ $pop->id }})" 
                                    class="btn btn-danger mb-1 mr-1"
                                    title="Delete"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcanAccess
                                <a 
                                    href="https://maps.google.com/?q={{ $pop->latitude }},{{ $pop->longitude }}" 
                                    target="_blank"
                                    class="btn btn-success mb-1 mr-1"
                                    title="View Map"
                                >
                                    <i class="fas fa-map-marked-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Tidak ada data POP ditemukan</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="card-footer clearfix">
        {{ $pops->links() }}
    </div>
</div>
@endcanAccess

@section('css')
    <style>
        .badge {
            font-weight: normal;
            padding: 0.4em 0.6em;
        }
    </style>
@stop

@section('js')
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('livewire:load', function() {
            Livewire.on('popDeleted', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'POP berhasil dihapus.',
                    timer: 3000
                });
            });
        });
        
         window.addEventListener('confirmDelete', event => 
         {
            Swal.fire({
                title: 'Hapus POP?',
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
                    Livewire.emitTo('pop.pop-index', 'delete', event.detail.id);
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
                showConfirmButton: false,
                timer: 1500,
            });
        });
    </script>
    
@stop