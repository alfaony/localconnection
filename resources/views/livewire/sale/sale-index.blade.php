<div>
    {{-- Data untuk JS print --}}
    <div id="saleIndexMeta"
        data-print-url="{{ rtrim(url('/sales/print-receipt'), '/') }}"
        data-header-image="{{ !empty($settingCompany['header_store_image']) ? s3_asset(true, 10, $settingCompany['header_store_image']) : '' }}"
        data-footer-message="{{ $settingCompany['footer_store_message'] ?? 'Terima kasih atas kunjungan Anda' }}"
        data-company-name="{{ $settingCompany['store_name'] ?? config('app.name') }}"
        data-company-address="{{ $settingCompany['store_address'] ?? '' }}"
        style="display:none;">
    </div>

    @if(Session::get('storeWithMessage'))
    <div class="alert alert-info alert-dismissible mt-2">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-inbox mr-1"></i>
        {{ Session::get('storeWithMessage') }}
    </div>
    @endif
    @if(Session::get('error'))
    <div class="alert alert-danger alert-dismissible mt-2">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ Session::get('error') }}
    </div>
    @endif

    <div class="row">
        <div class="col-md-12 mt-2">

            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">
                    <i class="fas fa-shopping-cart text-primary"></i> Daftar Penjualan
                </h4>
            </div>

            <!-- ─── TAB NAVIGATION ───────────────────────────────────────────── -->
            <ul class="nav nav-tabs mb-0" id="saleTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'active' ? 'active' : '' }} tab-btn"
                            wire:click="switchTab('active')"
                            type="button">
                        <i class="fas fa-receipt"></i> Penjualan
                        <span class="badge badge-primary ml-1" style="font-size:0.7rem;">
                            {{ $sales->total() }}
                        </span>
                    </button>
                </li>
                @if($canSeeProductSummary)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'ringkasan' ? 'active' : '' }} tab-btn"
                            wire:click="switchTab('ringkasan')"
                            type="button">
                        <i class="fas fa-chart-bar text-success"></i> Ringkasan Produk
                    </button>
                </li>
                @endif
                @if($canSeeDeleted)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'deleted' ? 'active' : '' }} tab-btn"
                            wire:click="switchTab('deleted')"
                            type="button">
                        <i class="fas fa-trash-alt text-danger"></i>
                        <span class="{{ $activeTab === 'deleted' ? '' : 'text-danger' }}">Penjualan Terhapus</span>
                        @if($deletedTotal > 0)
                        <span class="badge badge-danger ml-1" style="font-size:0.7rem;">
                            {{ $deletedTotal }}
                        </span>
                        @endif
                    </button>
                </li>
                @endif
            </ul>

            <!-- ─── TAB CONTENT ──────────────────────────────────────────────── -->
            <div class="tab-content-wrapper" style="border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 0.5rem 0.5rem;">

                {{-- ══════════════ TAB: PENJUALAN AKTIF ══════════════ --}}
                @if($activeTab === 'active')
                <div class="p-3">

                    <!-- Filter Toggle + Export Buttons -->
                    <div class="d-flex justify-content-end mb-3 gap-2">
                        @canAccess('export', 'sales')
                        @php
                            $exportParams = http_build_query(array_filter([
                                'search'         => $filter_search,
                                'start_date'     => $filter_start_date,
                                'end_date'       => $filter_end_date,
                                'start_time'     => $filter_start_time,
                                'end_time'       => $filter_end_time,
                                'user_id'        => $filter_user_id,
                                'payment_method' => $filter_payment_method,
                            ]));
                        @endphp
                        <a href="{{ route('sales.export') }}?{{ $exportParams }}"
                           class="btn btn-success btn-sm mr-1"
                           title="Export Excel — hasil dikirim ke Inbox">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                        @endcanAccess

                        <button class="btn btn-outline-primary btn-sm" type="button" id="searchToggleBtn">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>

                    <!-- Filter Panel -->
                    <div class="collapse mb-3" id="filterPanel">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white py-2">
                                <h6 class="mb-0"><i class="fas fa-search"></i> Pencarian Lanjutan</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">
                                            <i class="fas fa-search"></i> Pencarian Umum
                                        </label>
                                        <input type="text" id="tempSearch" class="form-control form-control-sm"
                                               placeholder="Cari kode transaksi, email, status..."
                                               value="{{ $temp_search }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">
                                            <i class="fas fa-user"></i> Dibuat Oleh
                                        </label>
                                        <select id="userSelect" class="form-select form-select-sm" style="width:100%;">
                                            <option value="">Semua User</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $temp_user_id == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label fw-bold small">
                                            <i class="fas fa-credit-card"></i> Metode Bayar
                                        </label>
                                        <select id="paymentMethodSelect" class="form-select form-select-sm">
                                            <option value="">Semua Metode</option>
                                            <option value="cash" {{ $temp_payment_method === 'cash' ? 'selected' : '' }}>Cash</option>
                                            <option value="qris" {{ $temp_payment_method === 'qris' ? 'selected' : '' }}>QRIS</option>
                                            <option value="debit_credit" {{ $temp_payment_method === 'debit_credit' ? 'selected' : '' }}>Debit / Credit</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small"><i class="fas fa-calendar-alt"></i> Tanggal Mulai</label>
                                        <input type="date" id="tempStartDate" class="form-control form-control-sm" value="{{ $temp_start_date }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small"><i class="fas fa-calendar-check"></i> Tanggal Akhir</label>
                                        <input type="date" id="tempEndDate" class="form-control form-control-sm" value="{{ $temp_end_date }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small"><i class="fas fa-clock"></i> Waktu Mulai</label>
                                        <input type="time" id="tempStartTime" class="form-control form-control-sm" value="{{ $temp_start_time }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small"><i class="fas fa-clock"></i> Waktu Akhir</label>
                                        <input type="time" id="tempEndTime" class="form-control form-control-sm" value="{{ $temp_end_time }}">
                                    </div>
                                </div>
                                <div class="mt-3 d-flex justify-content-between">
                                    <button id="clearFiltersBtn" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-times-circle"></i> Hapus Filter
                                    </button>
                                    <button id="applyFiltersBtn" class="btn btn-primary btn-sm">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Badges -->
                    @if($filter_search || $filter_start_date || $filter_end_date || $filter_start_time || $filter_end_time || $filter_user_id || $filter_payment_method)
                    <div class="mb-3">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge bg-secondary mr-1">Filter Aktif:</span>
                            @if($filter_search)
                                <span class="badge bg-info d-flex align-items-center gap-1 mr-1">
                                    Pencarian: "{{ $filter_search }}"
                                    <i class="fas fa-times-circle badge-remove" data-filter="search" style="cursor:pointer;"></i>
                                </span>
                            @endif
                            @if($filter_user_id)
                                <span class="badge bg-info d-flex align-items-center gap-1 mr-1">
                                    User: {{ $users->find($filter_user_id)->name ?? 'Unknown' }}
                                    <i class="fas fa-times-circle badge-remove" data-filter="user" style="cursor:pointer;"></i>
                                </span>
                            @endif
                            @if($filter_start_date)
                                <span class="badge bg-info d-flex align-items-center gap-1 mr-1">
                                    Dari: {{ \Carbon\Carbon::parse($filter_start_date)->format('d M Y') }}
                                    <i class="fas fa-times-circle badge-remove" data-filter="start_date" style="cursor:pointer;"></i>
                                </span>
                            @endif
                            @if($filter_end_date)
                                <span class="badge bg-info d-flex align-items-center gap-1 mr-1">
                                    Sampai: {{ \Carbon\Carbon::parse($filter_end_date)->format('d M Y') }}
                                    <i class="fas fa-times-circle badge-remove" data-filter="end_date" style="cursor:pointer;"></i>
                                </span>
                            @endif
                            @if($filter_start_time)
                                <span class="badge bg-info d-flex align-items-center gap-1 mr-1">
                                    Waktu Mulai: {{ $filter_start_time }}
                                    <i class="fas fa-times-circle badge-remove" data-filter="start_time" style="cursor:pointer;"></i>
                                </span>
                            @endif
                            @if($filter_end_time)
                                <span class="badge bg-info d-flex align-items-center gap-1 mr-1">
                                    Waktu Akhir: {{ $filter_end_time }}
                                    <i class="fas fa-times-circle badge-remove" data-filter="end_time" style="cursor:pointer;"></i>
                                </span>
                            @endif
                            @if($filter_payment_method)
                                @php $pmLabels = ['cash'=>'Cash','qris'=>'QRIS','debit_credit'=>'Debit/Credit']; @endphp
                                <span class="badge bg-info d-flex align-items-center mr-2 mb-1">
                                    Metode: {{ $pmLabels[$filter_payment_method] ?? $filter_payment_method }}
                                    <i class="fas fa-times-circle badge-remove" data-filter="payment_method" style="cursor:pointer;"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Summary Card -->
                    <div class="row mb-2">
                        {{-- Baris 1: 4 metric utama --}}
                        <div class="col-md-3 col-sm-6 mb-2">
                            <div class="info-box mb-0 shadow-sm">
                                <span class="info-box-icon bg-primary"><i class="fas fa-receipt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Transaksi</span>
                                    <span class="info-box-number">{{ $sales->total() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <div class="info-box mb-0 shadow-sm">
                                <span class="info-box-icon bg-info"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Subtotal</span>
                                    <span class="info-box-number" style="font-size:0.9rem;">
                                        Rp {{ number_format($totalSubAmount, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <div class="info-box mb-0 shadow-sm">
                                <span class="info-box-icon bg-warning"><i class="fas fa-percent"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total PPN</span>
                                    <span class="info-box-number" style="font-size:0.9rem;">
                                        Rp {{ number_format($totalTaxAmount, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <div class="info-box mb-0 shadow-sm">
                                <span class="info-box-icon bg-secondary"><i class="fas fa-calculator"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Akhir</span>
                                    <span class="info-box-number" style="font-size:0.9rem;">
                                        Rp {{ number_format($totalBeforeDeduction, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Baris 2: Deduction flow + Payment breakdown --}}
                        <div class="col-12 mb-2">
                            <div class="card shadow-sm border-0" style="border-radius:10px; overflow:hidden;">
                                <div class="card-body py-3 px-4">

                                    {{-- Deduction calculation row --}}
                                    <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
                                        {{-- Before --}}
                                        <div class="sale-calc-box" style="background:#f8f9fa; border-radius:8px; padding:10px 18px; text-align:center; min-width:160px;">
                                            <div class="text-muted" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:.05em;">Total Akhir</div>
                                            <div style="font-size:1rem; font-weight:600; color:#495057;">
                                                Rp {{ number_format($totalBeforeDeduction, 0, ',', '.') }}
                                            </div>
                                        </div>

                                        {{-- Minus sign --}}
                                        <div class="text-danger" style="font-size:1.1rem; font-weight:700;">−</div>

                                        {{-- Deduction --}}
                                        <div class="sale-calc-box" style="background:#fff5f5; border:1px solid #fed7d7; border-radius:8px; padding:10px 18px; text-align:center; min-width:160px;">
                                            <div class="text-danger" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:.05em;">Deduction</div>
                                            <div style="font-size:1rem; font-weight:600; color:#e53e3e;">
                                                Rp {{ number_format($totalDeduction, 0, ',', '.') }}
                                            </div>
                                        </div>

                                        {{-- Equals sign --}}
                                        <div class="text-muted" style="font-size:1.1rem; font-weight:700;">=</div>

                                        {{-- Final Total --}}
                                        <div class="sale-calc-box" style="background:#f0fff4; border:2px solid #68d391; border-radius:8px; padding:10px 22px; text-align:center; min-width:180px;">
                                            <div class="text-success" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:.05em; font-weight:700;">
                                                <i class="fas fa-check-circle"></i> Final Total Akhir
                                            </div>
                                            <div style="font-size:1.2rem; font-weight:700; color:#276749;">
                                                Rp {{ number_format($totalFinalAmount, 0, ',', '.') }}
                                            </div>
                                        </div>

                                        {{-- Payment breakdown di kanan --}}
                                        @if($paymentBreakdown !== null)
                                        <div class="ml-auto pl-3" style="border-left:2px solid #e9ecef;">
                                            <div class="text-muted" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; font-weight:600;">Rincian Pembayaran</div>
                                            <div class="d-flex flex-wrap" style="gap:12px; font-size:0.82rem;">
                                                <span>
                                                    <i class="fas fa-money-bill-wave text-success"></i>
                                                    <span class="text-muted">Cash</span>
                                                    <strong class="ml-1">Rp {{ number_format($paymentBreakdown['cash'], 0, ',', '.') }}</strong>
                                                </span>
                                                <span>
                                                    <i class="fas fa-qrcode text-info"></i>
                                                    <span class="text-muted">QRIS</span>
                                                    <strong class="ml-1">Rp {{ number_format($paymentBreakdown['qris'], 0, ',', '.') }}</strong>
                                                </span>
                                                <span>
                                                    <i class="fas fa-credit-card text-primary"></i>
                                                    <span class="text-muted">Debit/Credit</span>
                                                    <strong class="ml-1">Rp {{ number_format($paymentBreakdown['debit_credit'], 0, ',', '.') }}</strong>
                                                </span>
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Penjualan Aktif -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div wire:loading class="text-center py-4">
                                <div class="spinner-border text-primary" role="status"></div>
                                <p class="mt-2 text-muted small">Memuat data...</p>
                            </div>

                            @if($sales->isEmpty())
                                <div class="text-center py-5" wire:loading.remove>
                                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Tidak ada penjualan ditemukan</h6>
                                    <p class="text-muted small">Coba ubah filter pencarian Anda</p>
                                </div>
                            @else
                                <div class="table-responsive" wire:loading.remove>
                                    <table class="table table-hover table-sm mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center" style="width:40px;">No.</th>
                                                <th><i class="fas fa-barcode"></i> Kode Transaksi</th>
                                                <th><i class="fas fa-envelope"></i> Email Pelanggan</th>
                                                <th><i class="fas fa-money-bill-wave"></i> Total</th>
                                                <th><i class="fas fa-calculator"></i> Total Akhir</th>
                                                <th><i class="fas fa-info-circle"></i> Status</th>
                                                <th><i class="fas fa-credit-card"></i> Metode Bayar</th>
                                                <th><i class="fas fa-user"></i> Kasir</th>
                                                <th><i class="fas fa-calendar"></i> Tanggal</th>
                                                <th class="text-center"><i class="fas fa-cog"></i> Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sales as $sale)
                                            <tr>
                                                <td class="text-center small text-muted fw-bold">{{ $sales->firstItem() + $loop->index }}</td>
                                                <td>
                                                    <div class="fw-bold text-primary small">{{ $sale->transaction_code ?? 'N/A' }}</div>
                                                    <small class="text-muted">#{{ $sale->transaction_number ?? 'N/A' }}</small>
                                                </td>
                                                <td class="small">
                                                    <i class="fas fa-user-circle text-muted"></i>
                                                    {{ $sale->customer_email ?? 'Guest' }}
                                                </td>
                                                <td class="small fw-bold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                                <td class="small fw-bold text-success">Rp {{ number_format($sale->final_amount, 0, ',', '.') }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $sale->status === 'completed' ? 'success' : ($sale->status === 'pending' ? 'warning' : 'secondary') }}">
                                                        {{ ucfirst($sale->status) }}
                                                    </span>
                                                </td>
                                                <td>{!! $sale->payment_method_html !!}</td>
                                                <td class="small">
                                                    <i class="fas fa-user-tie text-primary"></i>
                                                    {{ $sale->user->name ?? 'N/A' }}
                                                </td>
                                                <td class="small">
                                                    <div>{{ $sale->created_at->format('d M Y') }}</div>
                                                    <small class="text-muted">{{ $sale->created_at->format('H:i') }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        @canAccess('show','sales')
                                                        <a href="{{ route('sales.show', $sale->id) }}"
                                                           class="btn btn-outline-primary btn-sm"
                                                           title="Lihat Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @endcanAccess

                                                        @canAccess('printReceiptManagement','sales')
                                                        <button type="button"
                                                                class="btn btn-outline-secondary btn-sm btn-print-receipt"
                                                                data-sale-id="{{ $sale->id }}"
                                                                title="Cetak Struk">
                                                            <i class="fas fa-print"></i>
                                                        </button>
                                                        @endcanAccess

                                                        @canAccess('destroy','sales')
                                                        <button wire:click="confirmDelete('{{ $sale->id }}')"
                                                                class="btn btn-outline-danger btn-sm"
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
                                <div class="d-flex justify-content-between align-items-center px-3 py-2" wire:loading.remove>
                                    <select wire:model.live="perPage" class="form-control form-control-sm" style="width:auto;">
                                        <option value="5">5 per halaman</option>
                                        <option value="10">10 per halaman</option>
                                        <option value="25">25 per halaman</option>
                                        <option value="50">50 per halaman</option>
                                    </select>
                                    <div>{{ $sales->links() }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
                @endif

                {{-- ══════════════ TAB: RINGKASAN PRODUK ══════════════ --}}
                @if($activeTab === 'ringkasan' && $canSeeProductSummary)
                <div class="p-3">

                    <!-- Type Toggle + Filter Button -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="btn-group btn-group-sm" role="group">
                            <button wire:click="setRingType('sold')"
                                    type="button"
                                    class="btn mb-1 mr-1 {{ $ring_type === 'sold' ? 'btn-success' : 'btn-outline-success' }}">
                                <i class="fas fa-check-circle"></i> Produk Terjual
                            </button>
                            <button wire:click="setRingType('unsold')"
                                    type="button"
                                    class="btn mb-1 mr-1 {{ $ring_type === 'unsold' ? 'btn-danger' : 'btn-outline-danger' }}">
                                <i class="fas fa-times-circle"></i> Produk Tidak Terjual
                            </button>
                        </div>
                        <button class="btn btn-outline-primary btn-sm" type="button" id="ringFilterToggleBtn">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>

                    <div class="collapse mb-3" id="ringFilterPanel">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-success text-white py-2">
                                <h6 class="mb-0"><i class="fas fa-search"></i> Filter Ringkasan Produk</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small"><i class="fas fa-search"></i> Cari Produk</label>
                                        <input type="text" id="ringSearch" class="form-control form-control-sm"
                                               placeholder="Nama, variant, barcode, kode..."
                                               value="{{ $temp_ring_search }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small"><i class="fas fa-tag"></i> Kategori</label>
                                        <select id="ringCategorySelect" class="form-select form-select-sm" style="width:100%;">
                                            <option value="">Semua Kategori</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ $temp_ring_category_id == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small"><i class="fas fa-trademark"></i> Brand</label>
                                        <select id="ringBrandSelect" class="form-select form-select-sm" style="width:100%;">
                                            <option value="">Semua Brand</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}" {{ $temp_ring_brand_id == $brand->id ? 'selected' : '' }}>
                                                    {{ $brand->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small"><i class="fas fa-user"></i> Kasir</label>
                                        <select id="ringUserSelect" class="form-select form-select-sm" style="width:100%;">
                                            <option value="">Semua Kasir</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $temp_ring_user_id == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small"><i class="fas fa-credit-card"></i> Metode Bayar</label>
                                        <select id="ringPaymentSelect" class="form-select form-select-sm">
                                            <option value="">Semua Metode</option>
                                            <option value="cash" {{ $temp_ring_payment_method === 'cash' ? 'selected' : '' }}>Cash</option>
                                            <option value="qris" {{ $temp_ring_payment_method === 'qris' ? 'selected' : '' }}>QRIS</option>
                                            <option value="debit_credit" {{ $temp_ring_payment_method === 'debit_credit' ? 'selected' : '' }}>Debit / Credit</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small"><i class="fas fa-calendar-alt"></i> Tanggal Mulai</label>
                                        <input type="date" id="ringStartDate" class="form-control form-control-sm" value="{{ $temp_ring_start_date }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small"><i class="fas fa-calendar-check"></i> Tanggal Akhir</label>
                                        <input type="date" id="ringEndDate" class="form-control form-control-sm" value="{{ $temp_ring_end_date }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small"><i class="fas fa-clock"></i> Waktu Mulai</label>
                                        <input type="time" id="ringStartTime" class="form-control form-control-sm" value="{{ $temp_ring_start_time }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small"><i class="fas fa-clock"></i> Waktu Akhir</label>
                                        <input type="time" id="ringEndTime" class="form-control form-control-sm" value="{{ $temp_ring_end_time }}">
                                    </div>
                                </div>
                                <div class="mt-3 d-flex justify-content-between">
                                    <button id="clearRingFiltersBtn" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-times-circle"></i> Reset
                                    </button>
                                    <button id="muatRingkasanBtn" class="btn btn-success btn-sm">
                                        <i class="fas fa-database"></i> Muat Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sort Toggle + Info -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted small">
                            <i class="fas fa-boxes"></i>
                            {{ method_exists($productRingkasan, 'total') ? $productRingkasan->total() : $productRingkasan->count() }}
                            produk ditemukan
                        </span>
                        @if($ring_type === 'sold')
                        <button wire:click="toggleRingkasanSort" class="btn btn-outline-secondary btn-sm">
                            @if($ring_sort === 'desc')
                                <i class="fas fa-sort-amount-down text-success"></i> Terbanyak ↓
                            @else
                                <i class="fas fa-sort-amount-up text-danger"></i> Tersedikit ↑
                            @endif
                        </button>
                        @endif
                    </div>

                    <!-- Breakdown Per Kategori (hanya Produk Terjual) -->
                    @if($ring_type === 'sold' && $ringkasanByCategory->isNotEmpty())
                    <div class="mb-3 p-2 bg-light rounded border d-flex flex-wrap align-items-center" style="gap:0.4rem;">
                        <span class="small fw-bold text-muted mr-2"><i class="fas fa-tags"></i> Per Kategori:</span>
                        @foreach($ringkasanByCategory as $cat)
                        <span class="badge badge-pill"
                              style="background-color:#e0f2fe; color:#0369a1; font-size:0.78rem; padding:0.35em 0.75em;">
                            {{ $cat->category_name ?? 'Tanpa Kategori' }}
                            <strong class="ml-1">({{ number_format($cat->total_qty, 0, ',', '.') }})</strong>
                        </span>
                        @endforeach
                    </div>
                    @endif

                    <!-- Tabel Produk -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div wire:loading class="text-center py-4">
                                <div class="spinner-border text-success" role="status"></div>
                                <p class="mt-2 text-muted small">Memuat data produk...</p>
                            </div>
                            @if($productRingkasan->isEmpty())
                                <div class="text-center py-5" wire:loading.remove>
                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Tidak ada data produk ditemukan</h6>
                                    <p class="text-muted small">Coba ubah filter atau rentang tanggal</p>
                                </div>
                            @else
                                <div class="table-responsive" wire:loading.remove>
                                    <table class="table table-hover table-sm mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center" style="width:40px;">No.</th>
                                                <th><i class="fas fa-box"></i> Produk</th>
                                                <th><i class="fas fa-layer-group"></i> Variant</th>
                                                <th><i class="fas fa-barcode"></i> Barcode/Kode</th>
                                                <th><i class="fas fa-tag"></i> Kategori</th>
                                                <th><i class="fas fa-trademark"></i> Brand</th>
                                                @if($ring_type === 'sold')
                                                <th class="text-center"><i class="fas fa-sort-numeric-down"></i> Terjual</th>
                                                <th class="text-right"><i class="fas fa-dollar-sign"></i> Total Revenue</th>
                                                @else
                                                <th class="text-center"><i class="fas fa-ban text-danger"></i> Status</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($productRingkasan as $i => $ps)
                                            @php
                                                $no  = ($productRingkasan->currentPage() - 1) * $productRingkasan->perPage() + $i + 1;
                                                $prod = $ring_type === 'sold' ? $ps->productStore : $ps;
                                            @endphp
                                            <tr>
                                                <td class="text-center small text-muted fw-bold">{{ $no }}</td>
                                                <td class="small fw-bold">{{ $prod->name ?? '-' }}</td>
                                                <td class="small text-muted">{{ $prod->variant ?? '-' }}</td>
                                                <td class="small">
                                                    <code class="small">{{ $prod->barcode ?? $prod->code ?? '-' }}</code>
                                                </td>
                                                <td class="small">{{ $prod->category->name ?? '-' }}</td>
                                                <td class="small">{{ $prod->brand->name ?? '-' }}</td>
                                                @if($ring_type === 'sold')
                                                <td class="text-center">
                                                    <span class="badge badge-success px-2">
                                                        {{ number_format($ps->total_qty, 0, ',', '.') }}
                                                    </span>
                                                </td>
                                                <td class="text-right small fw-bold text-success">
                                                    Rp {{ number_format($ps->total_revenue, 0, ',', '.') }}
                                                </td>
                                                @else
                                                <td class="text-center">
                                                    <span class="badge badge-secondary px-2">Belum Terjual</span>
                                                </td>
                                                @endif
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Pagination -->
                                <div class="d-flex justify-content-between align-items-center px-3 py-2" wire:loading.remove>
                                    <select wire:model.live="perPage" class="form-control form-control-sm" style="width:auto;">
                                        <option value="10">10 per halaman</option>
                                        <option value="25">25 per halaman</option>
                                        <option value="50">50 per halaman</option>
                                    </select>
                                    <div>{{ $productRingkasan->links() }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
                @endif

                {{-- ══════════════ TAB: PENJUALAN TERHAPUS ══════════════ --}}
                @if($activeTab === 'deleted' && $canSeeDeleted)
                <div class="p-3">

                    <!-- Info Banner -->
                    <div class="alert alert-warning border-left border-warning py-2 px-3 mb-3 d-flex align-items-center gap-2" style="border-left: 4px solid #ffc107 !important;">
                        <i class="fas fa-info-circle text-warning mr-2"></i>
                        <span class="small">
                            Data penjualan terhapus <strong>tidak dihitung</strong> dalam total transaksi maupun laporan keuangan.
                            Anda dapat memulihkan data ini jika terhapus tidak sengaja.
                        </span>
                    </div>

                    <!-- Filter Deleted -->
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-outline-danger btn-sm" type="button" id="deletedFilterToggleBtn">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>

                    <div class="collapse mb-3" id="deletedFilterPanel">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-danger text-white py-2">
                                <h6 class="mb-0"><i class="fas fa-search"></i> Cari Data Terhapus</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small"><i class="fas fa-search"></i> Pencarian</label>
                                        <input type="text" id="deletedTempSearch" class="form-control form-control-sm"
                                               placeholder="Kode transaksi, email, status..."
                                               value="{{ $temp_deleted_search }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small"><i class="fas fa-user"></i> Dibuat Oleh</label>
                                        <select id="deletedUserSelect" class="form-select form-select-sm" style="width:100%;">
                                            <option value="">Semua User</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ $temp_deleted_user_id == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small"><i class="fas fa-calendar-alt"></i> Dihapus Dari</label>
                                        <input type="date" id="deletedTempStartDate" class="form-control form-control-sm" value="{{ $temp_deleted_start_date }}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small"><i class="fas fa-calendar-check"></i> Dihapus Sampai</label>
                                        <input type="date" id="deletedTempEndDate" class="form-control form-control-sm" value="{{ $temp_deleted_end_date }}">
                                    </div>
                                </div>
                                <div class="mt-3 d-flex justify-content-between">
                                    <button id="clearDeletedFiltersBtn" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-times-circle"></i> Hapus Filter
                                    </button>
                                    <button id="applyDeletedFiltersBtn" class="btn btn-danger btn-sm">
                                        <i class="fas fa-search"></i> Cari
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="row mb-3">
                        <div class="col-md-4 col-sm-6">
                            <div class="info-box mb-0 shadow-sm">
                                <span class="info-box-icon bg-danger"><i class="fas fa-trash-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Data Terhapus</span>
                                    <span class="info-box-number">{{ $deletedTotal }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Deleted -->
                    <div class="card shadow-sm border-0 border-danger" style="border-top: 3px solid #dc3545 !important;">
                        <div class="card-body p-0">
                            <div wire:loading class="text-center py-4">
                                <div class="spinner-border text-danger" role="status"></div>
                                <p class="mt-2 text-muted small">Memuat data terhapus...</p>
                            </div>

                            @if($deletedSales->isEmpty())
                                <div class="text-center py-5" wire:loading.remove>
                                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                    <h6 class="text-muted">Tidak ada data terhapus</h6>
                                    <p class="text-muted small">Semua penjualan masih aktif</p>
                                </div>
                            @else
                                <div class="table-responsive" wire:loading.remove>
                                    <table class="table table-hover table-sm mb-0">
                                        <thead style="background-color: #fff5f5;">
                                            <tr>
                                                <th class="text-center" style="width:40px;">No.</th>
                                                <th><i class="fas fa-barcode"></i> Kode Transaksi</th>
                                                <th><i class="fas fa-envelope"></i> Email Pelanggan</th>
                                                <th><i class="fas fa-money-bill-wave"></i> Total</th>
                                                <th><i class="fas fa-calculator"></i> Total Akhir</th>
                                                <th><i class="fas fa-info-circle"></i> Status</th>
                                                <th><i class="fas fa-credit-card"></i> Metode Bayar</th>
                                                <th><i class="fas fa-user"></i> Kasir</th>
                                                <th><i class="fas fa-calendar-times text-danger"></i> Dihapus</th>
                                                <th class="text-center"><i class="fas fa-cog"></i> Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($deletedSales as $sale)
                                            <tr style="opacity: 0.85;">
                                                <td class="text-center small text-muted fw-bold">{{ $deletedSales->firstItem() + $loop->index }}</td>
                                                <td>
                                                    <div class="fw-bold text-danger small">{{ $sale->transaction_code ?? 'N/A' }}</div>
                                                    <small class="text-muted">#{{ $sale->transaction_number ?? 'N/A' }}</small>
                                                </td>
                                                <td class="small">
                                                    <i class="fas fa-user-circle text-muted"></i>
                                                    {{ $sale->customer_email ?? 'Guest' }}
                                                </td>
                                                <td class="small fw-bold text-muted">
                                                    <s>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</s>
                                                </td>
                                                <td class="small fw-bold text-muted">
                                                    <s>Rp {{ number_format($sale->final_amount, 0, ',', '.') }}</s>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary">
                                                        {{ ucfirst($sale->status) }}
                                                    </span>
                                                </td>
                                                <td>{!! $sale->payment_method_html !!}</td>
                                                <td class="small">
                                                    <i class="fas fa-user-tie text-muted"></i>
                                                    {{ $sale->user->name ?? 'N/A' }}
                                                </td>
                                                <td class="small">
                                                    <div class="text-danger fw-bold">{{ $sale->deleted_at->format('d M Y') }}</div>
                                                    <small class="text-muted">{{ $sale->deleted_at->format('H:i') }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <button wire:click="confirmRestore('{{ $sale->id }}')"
                                                            class="btn btn-sm btn-outline-success"
                                                            title="Pulihkan Penjualan">
                                                        <i class="fas fa-undo"></i> Pulihkan
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination Deleted -->
                                <div class="d-flex justify-content-between align-items-center px-3 py-2" wire:loading.remove>
                                    <select wire:model.live="perPage" class="form-control form-control-sm" style="width:auto;">
                                        <option value="5">5 per halaman</option>
                                        <option value="10">10 per halaman</option>
                                        <option value="25">25 per halaman</option>
                                        <option value="50">50 per halaman</option>
                                    </select>
                                    <div>{{ $deletedSales->links() }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
                @endif

            </div>{{-- end tab-content-wrapper --}}

        </div>
    </div>
</div>

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .nav-tabs .nav-link {
        color: #6c757d;
        border-radius: 0.5rem 0.5rem 0 0;
        font-weight: 500;
        font-size: 0.9rem;
        padding: 0.6rem 1.2rem;
        transition: all 0.2s;
        cursor: pointer;
        border: none;
        background: none;
    }
    .nav-tabs .nav-link:hover {
        color: #495057;
        background-color: #f8f9fa;
    }
    .nav-tabs .nav-link.active {
        color: #495057;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-bottom-color: #fff;
        font-weight: 600;
    }
    .tab-content-wrapper {
        background: #fff;
    }
    .badge {
        font-size: 0.8rem;
    }
    .badge.d-flex { gap: 0.5rem; }
    .badge i.badge-remove { cursor: pointer; transition: transform 0.2s; }
    .badge i.badge-remove:hover { transform: scale(1.3); }
    .table th { font-weight: 600; white-space: nowrap; font-size: 0.82rem; }
    .table td { font-size: 0.84rem; vertical-align: middle; }
    .info-box { min-height: 60px; }
    .info-box-icon { width: 60px; line-height: 60px; font-size: 1.3rem; }
    .info-box-content { padding: 8px 10px; }
    .info-box-text { font-size: 0.78rem; }
    .info-box-number { font-size: 1rem; font-weight: 700; }
    .select2-container--bootstrap-5 .select2-selection { min-height: 31px; }
</style>
@endpush

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// ─── Select2 ─────────────────────────────────────────────────────────────────
function initializeSelect2() {
    ['#userSelect', '#deletedUserSelect', '#ringUserSelect'].forEach(function(sel) {
        if (!$(sel).length) return;
        if ($(sel).hasClass('select2-hidden-accessible')) $(sel).select2('destroy');
        $(sel).select2({ theme: 'bootstrap-5', placeholder: 'Pilih User', allowClear: true, width: '100%' });
    });
    ['#paymentMethodSelect', '#ringPaymentSelect'].forEach(function(sel) {
        if (!$(sel).length) return;
        if ($(sel).hasClass('select2-hidden-accessible')) $(sel).select2('destroy');
        $(sel).select2({ theme: 'bootstrap-5', allowClear: true, width: '100%' });
    });
    if ($('#ringCategorySelect').length) {
        if ($('#ringCategorySelect').hasClass('select2-hidden-accessible')) $('#ringCategorySelect').select2('destroy');
        $('#ringCategorySelect').select2({ theme: 'bootstrap-5', placeholder: 'Semua Kategori', allowClear: true, width: '100%' });
    }
    if ($('#ringBrandSelect').length) {
        if ($('#ringBrandSelect').hasClass('select2-hidden-accessible')) $('#ringBrandSelect').select2('destroy');
        $('#ringBrandSelect').select2({ theme: 'bootstrap-5', placeholder: 'Semua Brand', allowClear: true, width: '100%' });
    }
}

// ─── Tab aktif: apply filter ──────────────────────────────────────────────────
function triggerApplyFilters() {
    @this.call('applyFiltersFromInput',
        $('#tempSearch').val(),
        $('#tempStartDate').val(),
        $('#tempEndDate').val(),
        $('#tempStartTime').val(),
        $('#tempEndTime').val(),
        $('#userSelect').val() || '',
        $('#paymentMethodSelect').val() || ''
    );
}

// ─── Tab deleted: apply filter ────────────────────────────────────────────────
function triggerApplyDeletedFilters() {
    @this.call('applyDeletedFiltersFromInput',
        $('#deletedTempSearch').val(),
        $('#deletedTempStartDate').val(),
        $('#deletedTempEndDate').val(),
        $('#deletedUserSelect').val() || ''
    );
}

// ─── Hapus badge filter individual ───────────────────────────────────────────
function removeIndividualFilter(filterType) {
    switch(filterType) {
        case 'search':       $('#tempSearch').val(''); break;
        case 'user':
            $('#userSelect').data('clearing', true);
            $('#userSelect').val(null).trigger('change.select2');
            $('#userSelect').data('clearing', false);
            break;
        case 'start_date':   $('#tempStartDate').val(''); break;
        case 'end_date':     $('#tempEndDate').val(''); break;
        case 'start_time':   $('#tempStartTime').val(''); break;
        case 'end_time':     $('#tempEndTime').val(''); break;
        case 'payment_method': $('#paymentMethodSelect').val(null).trigger('change.select2'); break;
    }
    triggerApplyFilters();
}

function syncInputsFromLivewire() {
    initializeSelect2();
    if ($('#tempSearch').length)          $('#tempSearch').val(@this.temp_search);
    if ($('#tempStartDate').length)       $('#tempStartDate').val(@this.temp_start_date);
    if ($('#tempEndDate').length)         $('#tempEndDate').val(@this.temp_end_date);
    if ($('#tempStartTime').length)       $('#tempStartTime').val(@this.temp_start_time);
    if ($('#tempEndTime').length)         $('#tempEndTime').val(@this.temp_end_time);
    if ($('#userSelect').length)          $('#userSelect').val(@this.temp_user_id || '').trigger('change.select2');
    if ($('#paymentMethodSelect').length) $('#paymentMethodSelect').val(@this.temp_payment_method || '').trigger('change.select2');
    if ($('#deletedTempSearch').length)   $('#deletedTempSearch').val(@this.temp_deleted_search);
    if ($('#deletedUserSelect').length)   $('#deletedUserSelect').val(@this.temp_deleted_user_id || '').trigger('change.select2');
    if ($('#ringSearch').length)          $('#ringSearch').val(@this.temp_ring_search);
    if ($('#ringStartDate').length)       $('#ringStartDate').val(@this.temp_ring_start_date);
    if ($('#ringEndDate').length)         $('#ringEndDate').val(@this.temp_ring_end_date);
    if ($('#ringStartTime').length)       $('#ringStartTime').val(@this.temp_ring_start_time);
    if ($('#ringEndTime').length)         $('#ringEndTime').val(@this.temp_ring_end_time);
    if ($('#ringUserSelect').length)      $('#ringUserSelect').val(@this.temp_ring_user_id || '').trigger('change.select2');
    if ($('#ringPaymentSelect').length)   $('#ringPaymentSelect').val(@this.temp_ring_payment_method || '').trigger('change.select2');
    if ($('#ringCategorySelect').length)  $('#ringCategorySelect').val(@this.temp_ring_category_id || '').trigger('change.select2');
    if ($('#ringBrandSelect').length)     $('#ringBrandSelect').val(@this.temp_ring_brand_id || '').trigger('change.select2');
}

// ─── DOMContentLoaded ─────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    initializeSelect2();

    // Toggle filter panel - tab aktif
    $(document).on('click', '#searchToggleBtn', function() { $('#filterPanel').collapse('toggle'); });
    // Toggle filter panel - tab deleted
    $(document).on('click', '#deletedFilterToggleBtn', function() { $('#deletedFilterPanel').collapse('toggle'); });
    // Toggle filter panel - tab ringkasan
    $(document).on('click', '#ringFilterToggleBtn', function() { $('#ringFilterPanel').collapse('toggle'); });

    @if($filter_search || $filter_start_date || $filter_end_date || $filter_start_time || $filter_end_time || $filter_user_id || $filter_payment_method)
        $('#filterPanel').collapse('show');
    @endif

    // Tombol cari tab aktif
    $(document).on('click', '#applyFiltersBtn', function(e) { e.preventDefault(); triggerApplyFilters(); });
    $(document).on('click', '#clearFiltersBtn', function(e) { e.preventDefault(); @this.call('clearFilters'); });
    $(document).on('keypress', '#tempSearch', function(e) { if (e.which === 13) { e.preventDefault(); triggerApplyFilters(); } });

    // Tombol cari tab deleted
    $(document).on('click', '#applyDeletedFiltersBtn', function(e) { e.preventDefault(); triggerApplyDeletedFilters(); });
    $(document).on('click', '#clearDeletedFiltersBtn', function(e) { e.preventDefault(); @this.call('clearDeletedFilters'); });
    $(document).on('keypress', '#deletedTempSearch', function(e) { if (e.which === 13) { e.preventDefault(); triggerApplyDeletedFilters(); } });

    // Tombol muat data & reset tab ringkasan
    $(document).on('click', '#muatRingkasanBtn', function(e) {
        e.preventDefault();
        @this.call('applyRingkasanFilters',
            $('#ringSearch').val(),
            $('#ringStartDate').val(),
            $('#ringEndDate').val(),
            $('#ringStartTime').val(),
            $('#ringEndTime').val(),
            $('#ringUserSelect').val() || '',
            $('#ringPaymentSelect').val() || '',
            $('#ringCategorySelect').val() || '',
            $('#ringBrandSelect').val() || ''
        );
    });
    $(document).on('click', '#clearRingFiltersBtn', function(e) { e.preventDefault(); @this.call('clearRingkasanFilters'); });
    $(document).on('keypress', '#ringSearch', function(e) { if (e.which === 13) { e.preventDefault(); $('#muatRingkasanBtn').trigger('click'); } });

    // Hapus badge individual
    $(document).on('click', '.badge-remove', function() { removeIndividualFilter($(this).data('filter')); });

    // Auto-fill tanggal
    $(document).on('change', '#tempStartDate', function() {
        if (!$('#tempEndDate').val()) $('#tempEndDate').val($(this).val());
    });
    $(document).on('change', '#tempStartTime', function() {
        if (!$('#tempEndTime').val()) {
            const [h, m] = $(this).val().split(':').map(Number);
            const endH = String((h + 7) % 24).padStart(2, '0');
            $('#tempEndTime').val(`${endH}:${String(m).padStart(2, '0')}`);
        }
    });

    // Cetak struk
    $(document).on('click', '.btn-print-receipt', function() { printSaleReceipt($(this).data('sale-id')); });
});

// ─── Livewire hooks ───────────────────────────────────────────────────────────
document.addEventListener('livewire:load', function() {
    Livewire.hook('message.processed', () => { syncInputsFromLivewire(); });

    window.addEventListener('filters-applied',         function() { syncInputsFromLivewire(); });
    window.addEventListener('deleted-filters-applied', function() { syncInputsFromLivewire(); });
    window.addEventListener('filters-cleared', function() {
        initializeSelect2();
        ['#tempSearch','#tempStartDate','#tempEndDate','#tempStartTime','#tempEndTime'].forEach(s => $(s).val(''));
        $('#userSelect').val(null).trigger('change.select2');
        $('#paymentMethodSelect').val(null).trigger('change.select2');
    });
    window.addEventListener('deleted-filters-cleared', function() {
        ['#deletedTempSearch','#deletedTempStartDate','#deletedTempEndDate'].forEach(s => $(s).val(''));
        if ($('#deletedUserSelect').length) $('#deletedUserSelect').val(null).trigger('change.select2');
    });
    window.addEventListener('ringkasan-filters-cleared', function() {
        ['#ringSearch','#ringStartDate','#ringEndDate','#ringStartTime','#ringEndTime'].forEach(s => $(s).val(''));
        ['#ringUserSelect','#ringPaymentSelect','#ringCategorySelect','#ringBrandSelect'].forEach(function(sel) {
            if ($(sel).length) $(sel).val(null).trigger('change.select2');
        });
    });

    // Konfirmasi hapus
    window.addEventListener('confirm-delete', function(event) {
        Swal.fire({
            title: 'Hapus Penjualan?',
            text: 'Data akan dipindahkan ke daftar terhapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) @this.call('deleteSale', event.detail.saleId);
        });
    });

    // Konfirmasi pulihkan
    window.addEventListener('confirm-restore', function(event) {
        Swal.fire({
            title: 'Pulihkan Penjualan?',
            text: 'Data akan dikembalikan ke daftar penjualan aktif.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Pulihkan!',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) @this.call('restoreSale', event.detail.saleId);
        });
    });

    // Notifikasi
    window.addEventListener('notify', function(event) {
        Swal.fire({
            icon: event.detail.type,
            title: event.detail.type === 'success' ? 'Berhasil!' : 'Error!',
            text: event.detail.message,
            timer: 3000, showConfirmButton: false,
            toast: true, position: 'top-end'
        });
    });
});

// ─── Print struk ─────────────────────────────────────────────────────────────
async function printSaleReceipt(saleId) {
    const meta        = document.getElementById('saleIndexMeta');
    const printUrl    = meta.dataset.printUrl + '/' + saleId;
    const headerImage = meta.dataset.headerImage;
    const footerMsg   = meta.dataset.footerMessage;
    const companyName = meta.dataset.companyName;
    const companyAddr = meta.dataset.companyAddress;

    try {
        const resp = await fetch(printUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!resp.ok) throw new Error('Gagal memuat data struk (status ' + resp.status + ')');
        const json = await resp.json();
        if (!json.success) throw new Error('Data struk tidak ditemukan');

        const sale = json.sale;
        const fmt  = val => 'Rp ' + (parseFloat(val) || 0).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        const pmLabel = { cash: 'Tunai', qris: 'QRIS', debit_credit: 'Debit / Kredit' };

        const itemsHtml = (sale.items || []).map(item => {
            const hasDisc = item.discount_type === 'flat'
                ? parseFloat(item.discount_amount) > 0
                : parseFloat(item.discount_percent) > 0;
            const discLabel = hasDisc
                ? (item.discount_type === 'flat'
                    ? `-${fmt(item.discount_amount)} check`
                    : `-${item.discount_percent}%`)
                : '';
            const priceInfo = hasDisc
                ? `${fmt(item.original_price)}`
                : fmt(item.unit_price);
            const discAmount = hasDisc
                ? (item.discount_type === 'flat'
                    ? parseFloat(item.discount_amount) * item.quantity
                    : parseFloat(item.original_price) * parseFloat(item.discount_percent) / 100 * item.quantity)
                : 0;
            const discLine = hasDisc
                ? `<div class="item-row" style="color:#555;">
                       <span>Diskon ${item.discount_type === 'flat' ? '' : item.discount_percent + '%'}</span>
                       <span>-${fmt(discAmount)}</span>
                   </div>`
                : '';
            return `
            <div class="receipt-item">
                <div class="item-name">${item.product_store ? item.product_store.name : '-'}</div>
                <div class="item-row">
                    <span>${item.quantity} x ${priceInfo}</span>
                    <span class="item-total">${fmt(item.subtotal)}</span>
                </div>
                ${discLine}
            </div>`;
        }).join('');

        const cashChange = sale.payment_method === 'cash' ? `
            <div class="total-row" style="margin-top:4px;">
                <span>Dibayar:</span><span>${fmt(sale.payment_details?.cash_amount ?? 0)}</span>
            </div>
            <div class="total-row">
                <span>Kembalian:</span>
                <span>${fmt((sale.payment_details?.cash_amount ?? 0) - parseFloat(sale.final_amount))}</span>
            </div>` : '';

        const html = `<!DOCTYPE html><html><head>
<meta charset="UTF-8"><title>Struk ${sale.transaction_code}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
@page{size:46mm auto;margin:0}
body{font-family:Arial;width:46mm;margin:0;padding:2mm 2mm 8mm 2mm;font-size:8px;line-height:1.3;color:#000}
.header{text-align:center;margin-bottom:4px}
.receipt-logo{text-align:center;margin-bottom:4px}
.receipt-logo img{width:15mm;height:auto;display:block;margin:0 auto;object-fit:contain}
.company-name{font-size:10px;font-weight:bold;margin-bottom:2px}
.receipt-address{font-size:7px;margin-top:2px}
.receipt-title{margin-top:4px}
.receipt-title h6{font-size:9px;margin-bottom:2px}
.transaction-code{font-size:8px;margin-bottom:1px}
.date-time{font-size:7px}
hr{border:none;border-top:1px dashed #000;margin:4px 0}
.receipt-operator{margin-bottom:4px}
.info-row{display:flex;justify-content:space-between;margin-bottom:1px;font-size:8px}
.receipt-items{margin-bottom:4px}
.receipt-item{margin-bottom:4px;border-bottom:1px dotted #999;padding-bottom:3px}
.receipt-item:last-child{border-bottom:none}
.item-name{font-size:8px;word-break:break-word;margin-bottom:1px}
.item-row{display:flex;justify-content:space-between;font-size:7px}
.item-total{white-space:nowrap}
.receipt-totals{margin-top:2px}
.total-row{display:flex;justify-content:space-between;margin-bottom:2px;font-size:8px}
.total-line{border-top:1px solid #000;padding-top:3px;margin-top:3px;font-size:9px}
.footer-section{margin-top:6px;text-align:center;font-size:7px}
</style></head><body>
<div class="header">
    ${headerImage ? `<div class="receipt-logo"><img src="${headerImage}" alt="${companyName}" crossorigin="anonymous"></div>` : ''}
    <div class="company-name"><strong>${companyName}</strong></div>
    ${companyAddr ? `<div class="receipt-address">${companyAddr}</div>` : ''}
    <div class="receipt-title">
        <h6>STRUK PENJUALAN</h6>
        <p class="transaction-code">${sale.transaction_code}</p>
        <span class="date-time">${new Date(sale.created_at).toLocaleString('id-ID')}</span>
    </div>
</div>
<hr>
<div class="receipt-operator">
    <div class="info-row"><span>Kasir:</span><span>${sale.user ? sale.user.name : '-'}</span></div>
    <div class="info-row"><span>Bayar:</span><span>${pmLabel[sale.payment_method] ?? sale.payment_method}</span></div>
</div>
<hr>
<div class="receipt-items">${itemsHtml}</div>
<hr>
<div class="receipt-totals">
    <div class="total-row"><span>Subtotal:</span><span>${fmt(sale.total_amount)}</span></div>
    <div class="total-row"><span>Pajak (${sale.tax_value}%):</span><span>${fmt(sale.tax_amount)}</span></div>
    <div class="total-row total-line"><p>TOTAL:</p><p>${fmt(sale.final_amount)}</p></div>
    ${cashChange}
</div>
<hr>
<div class="footer-section">${footerMsg || 'Terima kasih atas kunjungan Anda'}</div>
</body></html>`;

        const win = window.open('', '_blank');
        win.document.write(html);
        win.document.close();
        win.onload = function() {
            const imgs = win.document.images;
            if (!imgs.length) { win.focus(); win.print(); return; }
            let n = 0;
            const tryPrint = () => { if (++n >= imgs.length) { win.focus(); win.print(); } };
            for (const img of imgs) {
                if (img.complete) tryPrint();
                else { img.onload = tryPrint; img.onerror = tryPrint; }
            }
        };
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Gagal Cetak', text: err.message, timer: 3000, showConfirmButton: false });
    }
}
</script>

@endpush
