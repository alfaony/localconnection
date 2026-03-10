<div>
    <div class="row">
        <div class="col-md-12 mt-2">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">
                    <i class="fas fa-shopping-cart text-primary"></i> Daftar Penjualan
                </h4>
                <button class="btn btn-primary" type="button" id="searchToggleBtn">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>

            <!-- Advanced Filter Panel -->
            <div class="collapse mb-3" id="filterPanel">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-search"></i> Pencarian Lanjutan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- General Search -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-search"></i> Pencarian Umum
                                </label>
                                <input type="text" 
                                       id="tempSearch" 
                                       class="form-control" 
                                       placeholder="Cari kode transaksi, email, status..." 
                                       value="{{ $temp_search }}">
                            </div>

                            <!-- Creator Filter -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-user"></i> Dibuat Oleh
                                </label>
                                <select id="userSelect" class="form-select" style="width: 100%;">
                                    <option value="">Semua User</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ $temp_user_id == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Payment Method Filter -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-credit-card"></i> Metode Pembayaran
                                </label>
                                <select id="paymentMethodSelect" class="form-select">
                                    <option value="">Semua Metode</option>
                                    <option value="cash" {{ $temp_payment_method === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="qris" {{ $temp_payment_method === 'qris' ? 'selected' : '' }}>QRIS</option>
                                    <option value="debit_credit" {{ $temp_payment_method === 'debit_credit' ? 'selected' : '' }}>Debit / Credit</option>
                                </select>
                            </div>

                            <!-- Date Range -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-calendar-alt"></i> Tanggal Mulai
                                </label>
                                <input type="date" 
                                       id="tempStartDate" 
                                       class="form-control" 
                                       value="{{ $temp_start_date }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-calendar-check"></i> Tanggal Akhir
                                </label>
                                <input type="date" 
                                       id="tempEndDate" 
                                       class="form-control" 
                                       value="{{ $temp_end_date }}">
                            </div>

                            <!-- Time Range -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-clock"></i> Waktu Mulai
                                </label>
                                <input type="time" 
                                       id="tempStartTime" 
                                       class="form-control" 
                                       value="{{ $temp_start_time }}">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-clock"></i> Waktu Akhir
                                </label>
                                <input type="time" 
                                       id="tempEndTime" 
                                       class="form-control" 
                                       value="{{ $temp_end_time }}">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-3 d-flex justify-content-between">
                            <button id="clearFiltersBtn" class="btn btn-outline-danger">
                                <i class="fas fa-times-circle"></i> Hapus Semua Filter
                            </button>
                            <button id="applyFiltersBtn" class="btn btn-primary">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Filters Badges -->
            @if($filter_search || $filter_start_date || $filter_end_date || $filter_start_time || $filter_end_time || $filter_user_id || $filter_payment_method)
                <div class="mb-3">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-secondary mr-1">Filter Aktif:</span>
                        
                        @if($filter_search)
                            <span class="badge bg-info d-flex align-items-center gap-1 mr-1">
                                Pencarian: "{{ $filter_search }}"
                                <i class="fas fa-times-circle badge-remove" 
                                   data-filter="search" 
                                   style="cursor: pointer;"></i>
                            </span>
                        @endif

                        @if($filter_user_id)
                            <span class="badge bg-info d-flex align-items-center gap-1 mr-1">
                                User: {{ $users->find($filter_user_id)->name ?? 'Unknown' }}
                                <i class="fas fa-times-circle badge-remove" 
                                   data-filter="user" 
                                   style="cursor: pointer;"></i>
                            </span>
                        @endif

                        @if($filter_start_date)
                            <span class="badge bg-info d-flex align-items-center gap-1 mr-1">
                                Dari: {{ \Carbon\Carbon::parse($filter_start_date)->format('d M Y') }}
                                <i class="fas fa-times-circle badge-remove" 
                                   data-filter="start_date" 
                                   style="cursor: pointer;"></i>
                            </span>
                        @endif

                        @if($filter_end_date)
                            <span class="badge bg-info d-flex align-items-center gap-1 mr-1">
                                Sampai: {{ \Carbon\Carbon::parse($filter_end_date)->format('d M Y') }}
                                <i class="fas fa-times-circle badge-remove" 
                                   data-filter="end_date" 
                                   style="cursor: pointer;"></i>
                            </span>
                        @endif

                        @if($filter_start_time)
                            <span class="badge bg-info d-flex align-items-center gap-1 mr-1">
                                Waktu Mulai: {{ $filter_start_time }}
                                <i class="fas fa-times-circle badge-remove" 
                                   data-filter="start_time" 
                                   style="cursor: pointer;"></i>
                            </span>
                        @endif

                        @if($filter_end_time)
                            <span class="badge bg-info d-flex align-items-center gap-1 mr-1">
                                Waktu Akhir: {{ $filter_end_time }}
                                <i class="fas fa-times-circle badge-remove"
                                   data-filter="end_time"
                                   style="cursor: pointer;"></i>
                            </span>
                        @endif

                        @if($filter_payment_method)
                            @php
                                $pmLabels = ['cash' => 'Cash', 'qris' => 'QRIS', 'debit_credit' => 'Debit / Credit'];
                            @endphp
                            <span class="badge bg-info d-flex align-items-center gap-1 mr-1">
                                Metode: {{ $pmLabels[$filter_payment_method] ?? $filter_payment_method }}
                                <i class="fas fa-times-circle badge-remove"
                                   data-filter="payment_method"
                                   style="cursor: pointer;"></i>
                            </span>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Sales Table Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list"></i> Data Penjualan
                        </h5>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge bg-primary mr-2 mb-1">Total: {{ $sales->total() }} transaksi</span>
                            <span class="badge bg-success mb-1">
                                <i class="fas fa-calculator"></i> Total Jumlah Akhir: Rp {{ number_format($totalFinalAmount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Loading State -->
                    <div wire:loading class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">

                        </div>
                        <p class="mt-2 text-muted">Memuat data...</p>
                    </div>

                    <!-- Empty State -->
                    @if($sales->isEmpty())
                        <div class="text-center py-5" wire:loading.remove>
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada penjualan ditemukan</h5>
                            <p class="text-muted">Coba ubah filter pencarian Anda</p>
                        </div>
                    @else
                        <!-- Sales Table -->
                        <div class="table-responsive" wire:loading.remove>
                            <table class="table table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-barcode"></i> Kode Transaksi</th>
                                        <th><i class="fas fa-envelope"></i> Email Pelanggan</th>
                                        <th><i class="fas fa-money-bill-wave"></i> Jumlah Total</th>
                                        <th><i class="fas fa-calculator"></i> Jumlah Akhir</th>
                                        <th><i class="fas fa-info-circle"></i> Status</th>
                                        <th><i class="fas fa-credit-card"></i> Metode Pembayaran</th>
                                        <th><i class="fas fa-user"></i> Dibuat Oleh</th>
                                        <th><i class="fas fa-calendar"></i> Tanggal</th>
                                        <th><i class="fas fa-cog"></i> Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sales as $sale)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-primary">{{ $sale->transaction_code ?? 'N/A' }}</div>
                                                <small class="text-muted">#{{ $sale->transaction_number ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <i class="fas fa-user-circle text-muted"></i>
                                                {{ $sale->customer_email ?? 'Guest' }}
                                            </td>
                                            <td class="fw-bold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                            <td class="fw-bold text-success">Rp {{ number_format($sale->final_amount, 0, ',', '.') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $sale->status === 'completed' ? 'success' : ($sale->status === 'pending' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($sale->status) }}
                                                </span>
                                            </td>
                                            <td>{!! $sale->payment_method_html !!}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-user-tie text-primary me-2"></i>
                                                    <span>{{ $sale->user->name ?? 'N/A' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div>{{ $sale->created_at->format('d M Y') }}</div>
                                                <small class="text-muted">{{ $sale->created_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @canAccess('show','sales')
                                                    <a href="{{ route('sales.show', $sale->id) }}" 
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @endcanAccess

                                                    @canAccess('destroy','sales')
                                                    <button wire:click="confirmDelete('{{ $sale->id }}')" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    @endcanAccess
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3" wire:loading.remove>
                            <div>
                                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                                    <option value="5">5 per halaman</option>
                                    <option value="10">10 per halaman</option>
                                    <option value="25">25 per halaman</option>
                                    <option value="50">50 per halaman</option>
                                </select>
                            </div>
                            
                            <div>
                                {{ $sales->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .badge {
        font-size: 0.85rem;
        padding: 0.35rem 0.65rem;
    }
    
    .table th {
        font-weight: 600;
        white-space: nowrap;
    }
    
    .card {
        border: none;
        border-radius: 0.5rem;
    }
    
    .card-header {
        border-bottom: 2px solid #dee2e6;
    }
    
    .btn-group .btn {
        margin: 0 2px;
    }
    
    .collapse {
        transition: all 0.3s ease;
    }
    
    .form-label {
        margin-bottom: 0.5rem;
        color: #495057;
    }
    
    .badge.d-flex {
        gap: 0.5rem;
    }
    
    .badge i.badge-remove {
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .badge i.badge-remove:hover {
        transform: scale(1.3);
        color: #fff;
    }
    
    /* Select2 custom styling */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
    
    .select2-container--bootstrap-5 .select2-selection--single {
        padding: 0.375rem 0.75rem;
    }
</style>
@endpush

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function initializeSelect2() {
        if ($('#userSelect').hasClass('select2-hidden-accessible')) {
            $('#userSelect').select2('destroy');
        }

        $('#userSelect').select2({
            theme: 'bootstrap-5',
            placeholder: 'Pilih User',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#filterPanel')
        });

        $('#paymentMethodSelect').select2({
            theme: 'bootstrap-5',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#filterPanel')
        });

        $('#userSelect').off('change').on('change', function() {
            // Skip jika sedang programmatic clear
            if ($(this).data('clearing')) {
                return;
            }
            
            @this.set('temp_user_id', $(this).val());
            
            setTimeout(function() {
                syncFiltersToLivewire();
                @this.call('applyFilters');
            }, 100);
        });
    }

    function removeIndividualFilter(filterType) 
    {
        switch(filterType) {
            case 'search':
                @this.set('filter_search', '').then(() => {
                    @this.set('temp_search', '');
                    $('#tempSearch').val('');
                    @this.call('applyFilters');
                });
                break;
            case 'user':
                $('#userSelect').data('clearing', true);
                @this.set('filter_user_id', '').then(() => {
                    @this.set('temp_user_id', '');
                    $('#userSelect').val(null).trigger('change.select2');
                    $('#userSelect').data('clearing', false);
                    @this.call('applyFilters');
                });
                break;
            case 'start_date':
                @this.set('filter_start_date', '').then(() => {
                    @this.set('temp_start_date', '');
                    $('#tempStartDate').val('');
                    @this.call('applyFilters');
                });
                break;
            case 'end_date':
                @this.set('filter_end_date', '').then(() => {
                    @this.set('temp_end_date', '');
                    $('#tempEndDate').val('');
                    @this.call('applyFilters');
                });
                break;
            case 'start_time':
                @this.set('filter_start_time', '').then(() => {
                    @this.set('temp_start_time', '');
                    $('#tempStartTime').val('');
                    @this.call('applyFilters');
                });
                break;
            case 'end_time':
                @this.set('filter_end_time', '').then(() => {
                    @this.set('temp_end_time', '');
                    $('#tempEndTime').val('');
                    @this.call('applyFilters');
                });
                break;
            case 'payment_method':
                @this.set('filter_payment_method', '').then(() => {
                    @this.set('temp_payment_method', '');
                    $('#paymentMethodSelect').val('');
                    @this.call('applyFilters');
                });
                break;
        }
    }

    function syncFiltersToLivewire() {
        @this.set('temp_search', $('#tempSearch').val());
        @this.set('temp_start_date', $('#tempStartDate').val());
        @this.set('temp_end_date', $('#tempEndDate').val());
        @this.set('temp_start_time', $('#tempStartTime').val());
        @this.set('temp_end_time', $('#tempEndTime').val());
        @this.set('temp_user_id', $('#userSelect').val());
        @this.set('temp_payment_method', $('#paymentMethodSelect').val());
    }

    document.addEventListener('DOMContentLoaded', function() {
        initializeSelect2();
        
        $('#searchToggleBtn').on('click', function() {
            $('#filterPanel').collapse('toggle');
        });

        @if($filter_search || $filter_start_date || $filter_end_date || $filter_start_time || $filter_end_time || $filter_user_id || $filter_payment_method)
            $('#filterPanel').collapse('show');
        @endif

        $(document).on('click', '#applyFiltersBtn', function(e) {
            e.preventDefault();
            syncFiltersToLivewire();
            @this.call('applyFilters');
        });

        $(document).on('click', '#clearFiltersBtn', function(e) {
            e.preventDefault();
            @this.call('clearFilters');
        });

        $(document).on('click', '.badge-remove', function() {
            removeIndividualFilter($(this).data('filter'));
        });

        $(document).on('keypress', '#tempSearch, #tempStartDate, #tempEndDate, #tempStartTime, #tempEndTime', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#applyFiltersBtn').click();
            }
        });
    });

    document.addEventListener('livewire:load', function() {
        Livewire.hook('message.processed', (message, component) => {
            initializeSelect2();
            $('#userSelect').val(@this.temp_user_id).trigger('change.select2');
            $('#tempSearch').val(@this.temp_search);
            $('#tempStartDate').val(@this.temp_start_date);
            $('#tempEndDate').val(@this.temp_end_date);
            $('#tempStartTime').val(@this.temp_start_time);
            $('#tempEndTime').val(@this.temp_end_time);
            $('#paymentMethodSelect').val(@this.temp_payment_method);
        });

        window.addEventListener('filters-applied', function() {
            initializeSelect2();
            $('#userSelect').val(@this.filter_user_id).trigger('change.select2');
            $('#tempSearch').val(@this.filter_search);
            $('#tempStartDate').val(@this.filter_start_date);
            $('#tempEndDate').val(@this.filter_end_date);
            $('#tempStartTime').val(@this.filter_start_time);
            $('#tempEndTime').val(@this.filter_end_time);
            $('#paymentMethodSelect').val(@this.filter_payment_method);
        });

        window.addEventListener('filters-cleared', function() {
            initializeSelect2();
            $('#tempSearch').val('');
            $('#tempStartDate').val('');
            $('#tempEndDate').val('');
            $('#tempStartTime').val('');
            $('#tempEndTime').val('');
            $('#userSelect').val('').trigger('change.select2');
            $('#paymentMethodSelect').val('');
        });

        window.addEventListener('confirm-delete', function(event) {
            Swal.fire({
                title: 'Hapus Penjualan?',
                text: "Anda tidak dapat mengembalikan data ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('deleteSale', event.detail.saleId);
                }
            });
        });

        window.addEventListener('notify', function(event) {
            Swal.fire({
                icon: event.detail.type,
                title: event.detail.type === 'success' ? 'Berhasil!' : 'Error!',
                text: event.detail.message,
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        });
    });
</script>
@endpush