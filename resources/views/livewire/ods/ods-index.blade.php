
@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Optical Distribution Center</h5>
        @canAccess('create', 'optical_distributions')
        <a href="{{ route('optical-distribution.create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus mr-1"></i> Add New
        </a>
        @endcanAccess
    </div>
@stop
@include('components.alert')

@canAccess('index', 'optical_distributions')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <!-- Search and Filter Section -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search by name..." id="search-input">
                            <button class="btn btn-outline-secondary" type="button" id="search-button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        {{-- 
                        <div class="d-flex justify-content-end">
                            <div class="me-2">
                                <select class="form-select form-select-sm" id="capacity-filter">
                                    <option value="">All Capacities</option>
                                    <option value="100">100 MB</option>
                                    <option value="500">500 MB</option>
                                    <option value="1000">1 GB</option>
                                </select>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary" id="reset-filters">
                                <i class="fas fa-sync-alt"></i> Reset
                            </button>
                        </div>
                        --}}
                    </div>
                </div>

                <!-- ODS Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Kapasitas</th>
                                <th>Penanggung Jawab</th>
                                <th>Lokasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($opticalDistributions as $index => $ods)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($ods->location_photo)
                                        <img src="{{ s3_asset(true,10, $ods->location_photo) }}" 
                                                alt="ODS Photo" 
                                                class="rounded-circle me-2" 
                                                width="30" 
                                                height="30"
                                                data-bs-toggle="tooltip" 
                                                data-bs-placement="top" 
                                                title="View Photo">
                                        @else
                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px;">
                                            <i class="fas fa-image text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                        @endif
                                        <span>{{ $ods->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $ods->capacity_mb }} MB</td>
                                <td>{{ $ods->assignedUser->name ?? 'Unassigned' }}</td>
                                <td>
                                    @if($ods->latitude && $ods->longitude)
                                    <a href="https://www.google.com/maps?q={{ $ods->latitude }},{{ $ods->longitude }}" 
                                        target="_blank" 
                                        class="text-primary" 
                                        data-bs-toggle="tooltip" 
                                        data-bs-placement="top" 
                                        title="View on Map">
                                        <i class="fas fa-map-marker-alt"></i> View Map
                                    </a>
                                    @else
                                    <span class="text-muted">No location</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex">
                                        {{-- 
                                        <a href="{{ route('optical-distribution.show', $ods->id) }}" 
                                            class="btn btn-sm btn-outline-info me-1" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        --}}
                                        @canAccess('edit', 'optical_distributions')
                                        <a href="{{ route('optical-distribution.edit', $ods->id) }}" 
                                            class="btn btn-sm btn-outline-primary me-1 mb-1 mr-1" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="top" 
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcanAccess

                                        @canAccess('destroy', 'optical_distributions')
                                        <button wire:click="confirmDelete({{ $ods->id }})" 
                                                class="btn btn-sm btn-danger mb-1 mr-1"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endcanAccess
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">Tidak ada sistem distribusi optik yang ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($opticalDistributions->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $opticalDistributions->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endcanAccess

<!-- Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="photoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="photoModalLabel">ODS Location Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalPhoto" src="" alt="ODS Photo" class="img-fluid">
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 

@canAccess('destroy', 'optical_distributions')
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
                Livewire.emitTo('ods.ods-index', 'delete', event.detail.id);
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
@endcanAccess
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Photo modal functionality
        document.querySelectorAll('img[alt="ODS Photo"]').forEach(img => {
            img.addEventListener('click', function() {
                document.getElementById('modalPhoto').src = this.src;
                var photoModal = new bootstrap.Modal(document.getElementById('photoModal'));
                photoModal.show();
            });
        });

        // Search functionality
        document.getElementById('search-button').addEventListener('click', function() {
            var searchValue = document.getElementById('search-input').value.toLowerCase();
            filterTable(searchValue);
        });

    });

    function filterTable(searchValue) {
        var rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            var name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            if (name.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function filterTableByCapacity(capacityValue) {
        var rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            var capacity = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            if (!capacityValue || capacity.includes(capacityValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endpush

@push('css')
<style>
    .card-header {
        padding: 1rem 1.5rem;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    .img-thumbnail {
        max-width: 100px;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .img-thumbnail:hover {
        transform: scale(1.05);
    }
    #search-input {
        max-width: 300px;
    }
</style>
@endpush