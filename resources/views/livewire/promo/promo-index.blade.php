<div>
    <div class="row mt-2">
        <div class="col-sm-6">
            <h3>
                <i class="fas fa-tags mr-2"></i>
                Manajemen Promo
            </h3>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item active">Promo</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card card-info">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        @canAccess('create','promos')
                        <a href="{{ route('promo.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-2"></i>
                            Promo
                        </a>
                        @endcanAccess
                        <div class="card-tools">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" wire:model="search" class="form-control float-right" placeholder="Cari promo...">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @include('components.alert')
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead class="bg-light">
                                <tr>
                                    <th class="align-middle">
                                        <i class="fas fa-tag mr-1"></i> Nama Promo
                                    </th>
                                    <th class="align-middle">
                                        <i class="fas fa-network-wired mr-1"></i> Paket
                                    </th>
                                    
                                    <th class="align-middle">
                                        <i class="fas fa-gift mr-1"></i> Jenis
                                    </th>
                                    <th class="align-middle text-center">
                                        Nilai
                                    </th>
                                    <th class="align-middle text-center">
                                        <i class="fas fa-calendar-alt mr-1"></i> Masa Berlaku
                                    </th>
                                    <th class="align-middle">
                                        <i class="fas fa-info-circle mr-1"></i> Status
                                    </th>
                                    <th class="align-middle text-center" style="width: 120px;">
                                        <i class="fas fa-cog mr-1"></i> Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($promos as $promo)
                                <tr>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle bg-primary mr-3">
                                                <i class="fas fa-tag text-white"></i>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $promo->name }}</div>
                                                <small class="text-muted">
                                                    {{ $promo->packageInternets->count() }} paket terhubung
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        @foreach ($promo->packageInternets as $package)
                                            <span class="badge badge-secondary">
                                                {{ $package->name }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge badge-info">
                                            {{ config('custom.promo_type')[$promo->type] ?? $promo->type }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($promo->type == 'percentage')
                                            <span class="font-weight-bold text-success">{{ $promo->value }}%</span>
                                        @elseif($promo->type == 'free_months')
                                            <span class="font-weight-bold text-success">{{ $promo->value }} bulan gratis</span>
                                        @else
                                            <span class="font-weight-bold text-success">Rp{{ number_format($promo->value, 0, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td class="align-middle ">
                                        <div class="d-flex flex-column align-items-center">
                                            @if($promo->start_date != null && $promo->end_date != null)
                                            <span class="font-weight-bold">{{ \Carbon\Carbon::parse($promo->start_date)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($promo->end_date)->format('d M Y') }}</span>
                                            @else
                                            <span class="font-weight-bold">Tanpa Masa Berlaku</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            @if($promo->isActiveTrigger())
                                            @canAccess('is_active','promos')
                                            <input class="form-check-input" type="checkbox"
                                                id="status-{{ $promo->id }}"
                                                onclick="confirmToggleStatus(event, {{ $promo->id }})"
                                                {{ $promo->is_active ? '' : 'disabled' }}
                                                {{ $promo->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status-{{ $promo->id }}">
                                                {{ $promo->is_active ? 'Aktif' : 'Non-Aktif' }}
                                            </label>
                                            @endcanAccess
                                            @else
                                            <span class="badge badge-danger">
                                                Tidak Aktif
                                            </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="btn-group btn-group-sm gap-2">
                                            @canAccess('edit','promos')
                                            <a href="{{ route('promo.edit', ['id' => $promo->id]) }}" 
                                                class="btn btn-info mr-1" 
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcanAccess
                                            @if($promo->isAction())
                                            @canAccess('destroy','promos')
                                            <button wire:click="confirmDelete({{ $promo->id }})"
                                                    class="btn btn-danger" 
                                                    title="Hapus">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            @endcanAccess
                                            @else
                                            <a href="#" class="btn btn-secondary" title="Tidak Bisa Diedit">
                                                <i class="fas fa-lock"></i>
                                            </a> 
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Tidak ada promo ditemukan</h5>
                                            <p class="text-muted">Silahkan tambahkan promo baru</p>
                                            <a href="{{ route('promo.create') }}" class="btn btn-primary mt-2">
                                                <i class="fas fa-plus mr-2"></i> Tambah Promo
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-none d-md-block">
                            <p class="mb-0">Menampilkan {{ $promos->firstItem() }} - {{ $promos->lastItem() }} dari {{ $promos->total() }} promo</p>
                        </div>
                        <div class="ml-auto">
                            {{ $promos->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
<script>
    function confirmToggleStatus(e, id) 
    {
       e.preventDefault(); // prevent checkbox default toggle
       
       Swal.fire({
           title: 'Apakah Anda yakin?',
           text: 'Promo yang sudah dinonaktifkan tidak bisa diaktifkan kembali.',
           icon: 'warning',
           showCancelButton: true,
           confirmButtonColor: '#d33',
           cancelButtonColor: '#6c757d',
           confirmButtonText: 'Ya, Nonaktifkan',
           cancelButtonText: 'Batal'
       }).then((result) => {
           if (result.isConfirmed) {
                Livewire.emit('toggleStatus', id);
           }
       });
   }

    document.addEventListener('livewire:load', function() {
        // Konfirmasi sebelum menghapus

        window.addEventListener('swal:confirm', event => {
            Swal.fire({
                title: 'Hapus Promo?',
                text: "Anda yakin ingin menghapus promo ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log("run");
                    Livewire.emit('deletePromo', event.detail.id);
                }
            });
        });

        // Notifikasi setelah dihapus
        window.addEventListener('swal:deleted', event => {
            Swal.fire({
                title: 'Terhapus!',
                text: 'Promo telah berhasil dihapus.',
                icon: 'success',
                confirmButtonColor: '#1cc88a',
                timer: 2000
            });
        });
    });
</script>
@endpush

@push('css')
<style>
    .icon-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.05);
    }
    
    .card-header {
        background-color: #fff;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    }
    
    .card-title {
        margin-bottom: 0;
    }
    
    .bg-light {
        background-color: #f8f9fc !important;
    }
    
    .btn-group-sm > .btn, .btn-sm {
        border-radius: 4px;
        padding: 0.35rem 0.65rem;
    }
    
    .btn-info {
        background-color: #36b9cc;
        border-color: #36b9cc;
    }
    
    .btn-info:hover {
        background-color: #2c9faf;
        border-color: #2a96a5;
    }
    
    .badge-info {
        background-color: #36b9cc;
    }
    
    .badge-success {
        background-color: #1cc88a;
    }
    
    .badge-secondary {
        background-color: #858796;
    }
    
    .pagination {
        margin-bottom: 0;
    }
    
    .empty-state {
        opacity: 0.7;
    }
</style>
@endpush