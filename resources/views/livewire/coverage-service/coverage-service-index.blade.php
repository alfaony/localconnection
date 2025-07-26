@section('title', 'Cakupan Wilayah Layanan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h3>Cakupan Wilayah Layanan</h3>
        @canAccess('create', 'coverage_services')
        <a href="{{ route('coverage-service.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Cakupan Layanan
        </a>
        @endcanAccess
    </div>
@stop

@canAccess('index', 'coverage_services')
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <div class="input-group">
                    <input wire:model.debounce.300ms="search" type="text" class="form-control" placeholder="Search...">
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <select wire:model="perPage" class="form-control">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        @include('components.alert')
        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Area</th>
                        <th>ODP Terkait</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coverageServices as $service)
                        <tr>
                            <td>
                                {{ $service->province->name }} /
                                {{ $service->city->name }} /
                                {{ $service->district->name }} /
                                {{ $service->subdistrict->name }}
                            </td>
                            <td>
                                @foreach($service->coverageServiceOds as $ods)
                                    <span class="badge bg-success">{{ $ods->ods->name }}</span>
                                @endforeach
                            </td>
                            <td class="text-center">
                                @canAccess('edit', 'coverage_services')
                                <a href="{{ route('coverage-service.edit', $service->id) }}" 
                                class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcanAccess

                                @canAccess('destroy', 'coverage_services')
                                <button wire:click="delete({{ $service->id }})" 
                                        class="btn btn-sm btn-danger" 
                                        title="Delete"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus cakupan layanan ini?')"
                                        >
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endcanAccess
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-4">
                                <i class="fas fa-database fa-2x mb-2"></i>
                                <h5>Tidak ada layanan cakupan ditemukan</h5>
                                <a href="{{ route('coverage-service.create') }}" class="btn btn-primary mt-2">
                                    <i class="fa fa-plus"></i> Cakupan Layanan
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
            <div>Showing {{ $coverageServices->firstItem() }} to {{ $coverageServices->lastItem() }} of {{ $coverageServices->total() }} entries</div>
            <div>
                {{ $coverageServices->links() }}
            </div>
        </div>
    </div>
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
                Livewire.emitTo('coverage-service.coverage-service-index', 'delete', event.detail.id);
            }
        });
    });

    // Notifikasi setelah berhasil menghapus
    window.addEventListener('showDeleteNotification', event => {
        Swal.fire({
            title: 'Terhapus!',
            text: event.detail.message,
            icon: 'success',
            confirmButtonColor: '#3085d6',
        });
    });
</script>
@endpush

@section('css')
    <style>
        .card-footer .pagination {
            margin: 0;
            justify-content: center;
        }
        @media (max-width: 768px) {
            .card-header .row > div {
                margin-bottom: 10px;
            }
        }
    </style>
@stop