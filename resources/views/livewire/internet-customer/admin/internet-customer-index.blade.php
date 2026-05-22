@canAccess('index', 'internet_customers')
<div class="row">
<div class="col-12">
    @include('components.alert')

    {{-- ══ FILTER CARD ══════════════════════════════════════════════════════ --}}
    <div class="card ic-card ic-filter-card mb-3">
        <div class="card-header ic-card-header d-flex justify-content-between align-items-center flex-wrap py-2 px-3">
            <span class="ic-section-title">
                <i class="fas fa-sliders-h mr-2"></i>Filter Pelanggan
            </span>
            <div class="d-flex align-items-center flex-wrap ml-auto" style="gap:.5rem;">
                <a href="{{ route('internet-customer.create', Auth::user()->company->slug) }}"
                   target="_blank" id="share-link"
                   class="btn btn-sm btn-primary ic-btn ic-btn-primary">
                    <i class="fas fa-user-plus mr-1"></i> Pendaftaran Baru
                </a>
                <button class="btn btn-sm ic-btn ic-btn-outline-success" onclick="copyShareLink()">
                    <i class="fas fa-share-alt mr-1"></i> Share Link
                </button>
                @canAccess('import', 'internet_customers')
                <button type="button" class="btn btn-sm ic-btn ic-btn-outline-warning" wire:click="toggleImportSection">
                    <i class="fas fa-file-import mr-1"></i>
                    {{ $showImportSection ? 'Tutup Import' : 'Import Instalasi' }}
                </button>
                @endcanAccess
                @canAccess('export', 'internet_customers')
                <button type="button" class="btn btn-sm ic-btn" style="background:#217346;color:#fff;border-color:#217346;" onclick="exportInternetCustomer('xlsx')" title="Export Excel">
                    <i class="fas fa-file-excel mr-1"></i>Excel
                </button>
                <button type="button" class="btn btn-sm ic-btn btn-primary" onclick="exportInternetCustomer('csv')" title="Export CSV">
                    <i class="fas fa-file-csv mr-1"></i>CSV
                </button>
                @endcanAccess
                <button class="btn btn-sm ic-btn ic-btn-ghost" wire:click="resetSearch" title="Reset semua filter">
                    <i class="fas fa-undo mr-1 text-muted"></i><span class="text-muted">Reset</span>
                </button>
            </div>
        </div>

        <div class="card-body pt-3 pb-3">
            {{-- Baris 1: Filter utama --}}
            <div class="row no-gutters" style="gap: 0; margin: 0 -6px;">
                <div class="col-md-3 px-2 mb-2">
                    <label class="ic-label">Paket Internet</label>
                    <select wire:model="selectedPackage" class="form-control form-control-sm ic-select">
                        <option value="">Semua Paket</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}">{{ $package->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 px-2 mb-2">
                    <label class="ic-label">Status</label>
                    <select wire:model="statusFilter" class="form-control form-control-sm ic-select">
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="customer_existing">Pelanggan Lama</option>
                        <option value="waiting_payment_confirmation">Menunggu Konfirmasi Pembayaran</option>
                        <option value="waiting_payment_subscription">Menunggu Pembayaran Subscription</option>
                        <option value="process_installation">Proses Instalasi</option>
                        <option value="installed">Terpasang</option>
                        <option value="reactivated">Reaktivasi</option>
                        <option value="disconnected">Tidak Terhubung</option>
                        <option value="active">Aktif</option>
                        <option value="suspended">Dihentikan</option>
                        <option value="inactive">Tidak Aktif</option>
                        <option value="closed">Tutup</option>
                    </select>
                </div>
                <div class="col-md-2 px-2 mb-2">
                    <label class="ic-label">Tipe Pelanggan</label>
                    <select wire:model="customerTypeFilter" class="form-control form-control-sm ic-select">
                        <option value="">Semua Tipe</option>
                        <option value="rumah">Rumah</option>
                        <option value="bisnis">Bisnis</option>
                    </select>
                </div>
                <div class="col-md-4 px-2 mb-2">
                    <label class="ic-label">Cari Pelanggan</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text ic-input-prepend">
                                <i class="fas fa-search" style="font-size:.7rem;"></i>
                            </span>
                        </div>
                        <input wire:model.debounce.300ms="search" type="text"
                               class="form-control ic-search-input"
                               placeholder="Nama, kode, KTP, email, telp…">
                        @if($search)
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary ic-clear-btn" type="button"
                                    wire:click="$set('search', '')" title="Hapus">
                                <i class="fas fa-times" style="font-size:.65rem;"></i>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Baris 2: Filter tanggal --}}
            <div class="ic-date-zone d-flex flex-wrap align-items-end mt-3" style="gap:1.25rem;">
                <div>
                    <label class="ic-label mb-1">
                        <i class="fas fa-calendar-alt mr-1 text-primary" style="opacity:.75;"></i>Filter Tanggal Berdasarkan
                    </label>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="d-none" wire:model="dateType"
                               id="dt_billing" value="billing">
                        <label class="btn ic-radio-btn" for="dt_billing">
                            <i class="fas fa-file-invoice-dollar mr-1"></i>Tagihan
                        </label>
                        <input type="radio" class="d-none" wire:model="dateType"
                               id="dt_suspended" value="suspended">
                        <label class="btn ic-radio-btn" for="dt_suspended">
                            <i class="fas fa-file-invoice-dollar mr-1"></i>Jatuh Tempo
                        </label>
                        <input type="radio" class="d-none" wire:model="dateType"
                               id="dt_installation" value="installation">
                        <label class="btn ic-radio-btn" for="dt_installation">
                            <i class="fas fa-tools mr-1"></i>Pemasangan
                        </label>
                        <input type="radio" class="d-none" wire:model="dateType"
                               id="dt_registration" value="registration">
                        <label class="btn ic-radio-btn" for="dt_registration">
                            <i class="fas fa-user-plus mr-1"></i>Pendaftaran
                        </label>
                    </div>
                </div>

                <div class="d-flex align-items-end" style="gap:.6rem;">
                    <div>
                        <label class="ic-label">Dari</label>
                        <input wire:model="dateFrom" id="ic-date-from" type="date"
                               class="form-control form-control-sm ic-date-input">
                    </div>
                    <span class="text-muted pb-1" style="line-height:2.1;">—</span>
                    <div>
                        <label class="ic-label">Sampai</label>
                        <input wire:model="dateTo" id="ic-date-to" type="date"
                               class="form-control form-control-sm ic-date-input">
                    </div>
                    @if($dateFrom || $dateTo)
                    <div class="pb-1">
                        <button class="btn btn-sm btn-outline-danger ic-btn"
                                wire:click="$set('dateFrom', ''); $set('dateTo', '')"
                                title="Hapus filter tanggal">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{-- ══ END FILTER CARD ══════════════════════════════════════════════════ --}}

    {{-- ══ IMPORT SECTION ══════════════════════════════════════════════════ --}}
    @canAccess('import', 'internet_customers')
    @if($showImportSection)
    <div class="card ic-card mb-3" style="border-left:3px solid #f6c23e !important;">
        <div class="card-header ic-import-header d-flex align-items-center py-2 px-3">
            <i class="fas fa-file-import text-warning mr-2"></i>
            <span class="ic-section-title">Import Massal Instalasi Pelanggan</span>
        </div>
        <div class="card-body px-3 py-3">
            <div class="alert ic-alert-info d-flex py-2 mb-3 small">
                <i class="fas fa-info-circle mt-1 flex-shrink-0 mr-2"></i>
                <div>
                    <strong>Panduan:</strong> Download template → isi data (tanggal format <code>YYYY-MM-DD</code>) → pilih ODP → upload file CSV.
                </div>
            </div>

            <div class="mb-3">
                <button wire:click="downloadImportTemplate" class="btn btn-sm ic-btn ic-btn-outline-secondary">
                    <i class="fas fa-download mr-1"></i> Download Template CSV
                </button>
            </div>

            @if(!$isImporting)
            <form wire:submit.prevent="importCustomers">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="ic-label">Pilih ODP <span class="text-danger">*</span></label>
                        <select wire:model="import_odp_id"
                                class="form-control form-control-sm ic-select @error('import_odp_id') is-invalid @enderror">
                            <option value="">— Pilih ODP —</option>
                            @foreach($importAvailableOdps as $odp)
                                <option value="{{ $odp['id'] }}">{{ $odp['label'] }}</option>
                            @endforeach
                        </select>
                        @error('import_odp_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted">Berlaku untuk semua baris dalam file CSV.</small>
                    </div>
                    <div class="col-md-5 mb-3">
                        <label class="ic-label">File CSV <span class="text-danger">*</span></label>
                        <input type="file"
                               class="form-control form-control-sm @error('csvFile') is-invalid @enderror"
                               wire:model="csvFile" accept=".csv"
                               wire:loading.attr="disabled" wire:target="csvFile">
                        @error('csvFile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted">Format CSV · Maks. 10 MB</small>

                        <div wire:loading wire:target="csvFile" class="mt-2">
                            <div class="alert ic-alert-info py-2 mb-0 d-flex align-items-center small">
                                <div class="spinner-border spinner-border-sm flex-shrink-0 mr-2"></div>
                                <span>Mengupload file… mohon tunggu</span>
                            </div>
                        </div>
                        @if($isFileReady && $csvFile)
                        <div class="mt-2">
                            <div class="alert alert-success py-2 mb-0 d-flex align-items-center small">
                                <i class="fas fa-check-circle text-success flex-shrink-0 mr-2"></i>
                                <span class="flex-grow-1"><strong>Siap:</strong> {{ $csvFile->getClientOriginalName() }}</span>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 ml-2" wire:click="resetImport">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-warning w-100 ic-btn"
                                wire:loading.attr="disabled"
                                wire:target="importCustomers,csvFile"
                                {{ !$isFileReady ? 'disabled' : '' }}>
                            <span wire:loading wire:target="importCustomers">
                                <span class="spinner-border spinner-border-sm mr-1"></span>Memproses…
                            </span>
                            <span wire:loading wire:target="csvFile">
                                <span class="spinner-border spinner-border-sm mr-1"></span>Mengupload…
                            </span>
                            <span wire:loading.remove wire:target="importCustomers,csvFile">
                                <i class="fas fa-upload mr-1"></i>{{ $isFileReady ? 'Mulai Import' : 'Upload & Import' }}
                            </span>
                        </button>
                    </div>
                </div>
            </form>
            @endif

            @if($importProgress)
            <div class="mt-4">
                <hr class="my-3">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-hourglass-split text-primary mr-2"></i>
                    <span class="font-weight-semibold" style="font-size:.9rem;">Progress Import</span>
                </div>
                <div class="progress mb-3 ic-progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-{{ $this->getImportStatusColor($importProgress['status']) }}"
                         role="progressbar" style="width:{{ $importProgress['percentage'] }}%">
                        <strong>{{ $importProgress['percentage'] }}%</strong>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 col-md-3 mb-2">
                        <div class="ic-stat-card">
                            <span class="badge badge-{{ $this->getImportStatusColor($importProgress['status']) }} mb-1">
                                {{ strtoupper($importProgress['status'] ?? 'PROCESSING') }}
                            </span>
                            <div class="ic-stat-label">Status</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="ic-stat-card">
                            <div class="ic-stat-value">{{ $importProgress['total'] }}</div>
                            <div class="ic-stat-label">Total</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="ic-stat-card" style="border-color:#28a745 !important;">
                            <div class="ic-stat-value text-success">{{ $importProgress['success'] }}</div>
                            <div class="ic-stat-label">Berhasil</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 mb-2">
                        <div class="ic-stat-card" style="border-color:#dc3545 !important;">
                            <div class="ic-stat-value text-danger">{{ $importProgress['failed'] }}</div>
                            <div class="ic-stat-label">Gagal</div>
                        </div>
                    </div>
                </div>
                <div class="text-center mb-2">
                    <small class="text-muted">
                        <i class="fas fa-clock mr-1"></i>
                        Update terakhir: {{ \Carbon\Carbon::parse($importProgress['updated_at'])->format('d/m/Y H:i:s') }}
                    </small>
                </div>
                @if(!empty($importProgress['errors']) && count($importProgress['errors']) > 0)
                <div class="alert alert-warning py-2">
                    <div class="small font-weight-bold mb-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Detail Gagal ({{ count($importProgress['errors']) }} baris)
                    </div>
                    <div style="max-height:200px; overflow-y:auto;">
                        @foreach($importProgress['errors'] as $err)
                        @if(is_array($err))
                        <div class="d-flex align-items-start border-bottom pb-2 mb-2" style="gap:.5rem;">
                            <span class="badge badge-danger flex-shrink-0">Baris {{ $err['row'] ?? '?' }}</span>
                            <div class="small">
                                {{ $err['message'] ?? 'Unknown error' }}
                                @if(isset($err['data']))<div class="text-muted">{{ $err['data'] }}</div>@endif
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
    </div>
    @endif
    @endcanAccess
    {{-- ══ END IMPORT SECTION ═══════════════════════════════════════════════ --}}

    {{-- ══ TABLE CARD ══════════════════════════════════════════════════════ --}}
    <div class="card ic-card">
        <div class="card-header ic-table-header d-flex justify-content-between align-items-center py-2 px-3">
            <span class="font-weight-semibold text-white" style="font-size:.9rem;">
                <i class="fas fa-users mr-2"></i>Daftar Pelanggan Internet
            </span>
            <span class="ic-count-badge ml-auto">
                {{ $internetCustomers->total() }} pelanggan
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ic-table mb-0">
                    <thead>
                        <tr>
                            <th class="ic-th ic-th-sortable ps-3" wire:click="sortBy('code')" style="width:11%;">
                                Kode
                                @if($sortField === 'code')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1 ic-sort-icon"></i>
                                @endif
                            </th>
                            <th class="ic-th ic-th-sortable" wire:click="sortBy('name')" style="width:17%;">
                                Nama Pelanggan
                                @if($sortField === 'name')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1 ic-sort-icon"></i>
                                @endif
                            </th>
                            <th class="ic-th" style="width:12%;">Alamat</th>
                            <th class="ic-th" style="width:30%;">Paket &amp; Billing</th>
                            <th class="ic-th text-center" style="width:10%;">Status</th>
                            <th class="ic-th text-center" style="width:11%;">Aksi</th>
                            <th class="ic-th ic-th-sortable" wire:click="sortBy('created_at')" style="width:9%;">
                                Tgl. Daftar
                                @if($sortField === 'created_at')
                                    <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                @else
                                    <i class="fas fa-sort ml-1 ic-sort-icon"></i>
                                @endif
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($internetCustomers as $customer)
                        @php $ct = $customer->customer_type ?? 'rumah'; @endphp
                        <tr wire:key="customer-{{ $customer->id }}" class="ic-tr">

                            {{-- Kode --}}
                            <td class="ic-td pl-3">
                                <div class="ic-code">{{ $customer->code }}</div>
                                <div class="d-flex flex-wrap mt-1" style="gap:.25rem;">
                                    @if($ct === 'bisnis')
                                        <span class="ic-type-badge ic-type-bisnis">
                                            <i class="fas fa-building mr-1"></i>Bisnis
                                        </span>
                                    @else
                                        <span class="ic-type-badge ic-type-rumah">
                                            <i class="fas fa-home mr-1"></i>Rumah
                                        </span>
                                    @endif
                                    @if($customer->grouping_id)
                                        <span class="ic-type-badge ic-type-group" title="Grouping ID">
                                            <i class="fas fa-layer-group mr-1"></i>{{ $customer->grouping_id }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Nama --}}
                            <td class="ic-td">
                                <a href="{{ route('internet-customer.show', $customer->id) }}"
                                   class="ic-name-link">
                                    {{ $customer->name }}
                                </a>
                            </td>

                            {{-- Alamat --}}
                            <td class="ic-td">
                                <span class="ic-address">
                                    {{ Str::limit($customer->address, 65) }}
                                </span>
                                @if(strlen($customer->address) > 65)
                                    <a href="#" wire:click.prevent="showFullAddress({{ $customer->id }})"
                                       class="d-block ic-more-link">
                                        <i class="fas fa-expand-alt mr-1"></i>selengkapnya
                                    </a>
                                @endif
                            </td>

                            {{-- Paket & Billing --}}
                            <td class="ic-td">
                                <div class="ic-package-name mb-1">
                                    {{ $customer->internetPackage->name ?? '—' }}
                                </div>
                                @if($customer->username)
                                <div class="d-flex align-items-center mb-1" style="gap:.3rem;">
                                    <i class="fas fa-barcode text-muted" style="width:12px; font-size:.7rem; opacity:.5;"></i>
                                    <span class="ic-serial">{{ $customer->username }}</span>
                                </div>
                                @endif
                                @if($customer->getOldestUnconfirmed()?->confirmation_finance_at)
                                <div class="ic-billing-row ic-billing-renewal">
                                    <i class="fas fa-rotate mr-1"></i>Perpanjangan
                                    <span class="ic-billing-date ml-1">
                                        {{ \Carbon\Carbon::parse($customer->getOldestUnconfirmed()->confirmation_finance_at)->format('d M Y') }}
                                    </span>
                                </div>
                                @endif
                                @if($customer->userCustomer?->start_billing_date && $customer->userCustomer?->end_billing_date)
                                @php
                                    $startBill = \Carbon\Carbon::parse($customer->userCustomer->start_billing_date);
                                    $endBill   = \Carbon\Carbon::parse($customer->userCustomer->end_billing_date);
                                    $today     = \Carbon\Carbon::today();
                                    $daysLeft  = $today->diffInDays($endBill, false);
                                    if ($daysLeft < 0) {
                                        $dueClass = 'ic-due-overdue';
                                        $dueIcon  = 'fa-exclamation-circle';
                                        $dueTxt   = 'Lewat '.abs((int)$daysLeft).'h';
                                    } elseif ($daysLeft <= 7) {
                                        $dueClass = 'ic-due-warning';
                                        $dueIcon  = 'fa-exclamation-triangle';
                                        $dueTxt   = (int)$daysLeft === 0 ? 'Hari ini' : (int)$daysLeft.'h lagi';
                                    } else {
                                        $dueClass = 'ic-due-ok';
                                        $dueIcon  = 'fa-clock';
                                        $dueTxt   = (int)$daysLeft.'h lagi';
                                    }
                                @endphp
                                {{-- Periode billing --}}
                                <div class="ic-billing-row mt-1">
                                    <i class="fas fa-calendar-check mr-1" style="color:#1a56db; opacity:.7;"></i>
                                    <span class="ic-billing-chip ic-chip-start">
                                        {{ $startBill->format('d M Y') }}
                                    </span>
                                    <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:.55rem;"></i>
                                    <span class="ic-billing-chip ic-chip-end">
                                        {{ $endBill->format('d M Y') }}
                                    </span>
                                </div>
                                {{-- Pembayaran Manual (transfer) --}}
                                @if($customer->latestPurchase && in_array($customer->latestPurchase->payment_method, ['manual_transfer', 'transfer']))
                                @php $lp = $customer->latestPurchase; @endphp
                                <div class="ic-billing-row mt-1 px-1 py-1" style="background:#fffbeb; border:1px solid #fde68a; border-radius:5px; font-size:.68rem;">
                                    <i class="fas fa-money-bill-wave mr-1" style="color:#b45309;"></i>
                                    <span style="color:#92400e; font-weight:600;">Manual Transfer</span>
                                    @if($lp->transfer_date)
                                    <span class="ml-1" style="color:#78350f;">· {{ \Carbon\Carbon::parse($lp->transfer_date)->format('d M Y') }}</span>
                                    @endif
                                    @if($lp->transfer_from_bank)
                                    <span class="ml-1" style="color:#78350f;">· {{ $lp->transfer_from_bank }}</span>
                                    @endif
                                    @if($lp->transfer_from_account_name)
                                    <span class="ml-1 d-block" style="color:#78350f; padding-left:14px;">a/n <strong>{{ $lp->transfer_from_account_name }}</strong></span>
                                    @endif
                                    @if($lp->transfer_notes)
                                    <span class="ml-1 d-block" style="color:#92400e; padding-left:14px; font-style:italic;">{{ Str::limit($lp->transfer_notes, 40) }}</span>
                                    @endif
                                </div>
                                @endif
                                {{-- Jatuh Tempo --}}
                                <div class="ic-billing-row mt-1">
                                    <span class="ic-due-badge {{ $dueClass }}">
                                        <i class="fas {{ $dueIcon }} mr-1"></i>
                                        <span class="ic-due-label">JT:</span>
                                        <span class="ic-due-date">{{ $endBill->format('d M Y') }}</span>
                                        <span class="ic-due-countdown">({{ $dueTxt }})</span>
                                    </span>
                                </div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="ic-td text-center align-middle">
                                {!! $customer->status_badge !!}
                            </td>

                            {{-- Aksi --}}
                            <td class="ic-td text-center align-middle">
                                @switch($customer->status)
                                @case(\App\Schemas\ParamSchema::PENDING)
                                    <div class="d-flex flex-column" style="gap:.3rem;">
                                        <button class="btn btn-xs ic-btn-action ic-btn-action-success"
                                                onclick="return confirm('Setujui pendaftaran ini?') ? @this.call('approvePending', @js($customer->id)) : false">
                                            <i class="fas fa-check mr-1"></i>Approve
                                        </button>
                                        <button class="btn btn-xs ic-btn-action ic-btn-action-danger"
                                                onclick="return confirm('Tutup/batalkan pendaftaran ini?') ? @this.call('closePending', @js($customer->id)) : false">
                                            <i class="fas fa-times mr-1"></i>Tolak
                                        </button>
                                    </div>
                                    @break

                                @case(\App\Schemas\ParamSchema::WAITING_PAYMENT_CONFIRMATION)
                                    @php $purchase = $customer->getOldestUnconfirmedPurchase(); @endphp
                                    @if($purchase && in_array($purchase->payment_method, ['transfer','manual_transfer']))
                                        @if($finance_access)
                                        <div class="d-flex flex-column" style="gap:.3rem;">
                                            @if($purchase->payment_proof)
                                            <button class="btn btn-xs ic-btn-action ic-btn-action-info"
                                                    wire:click="viewPaymentProof(@js($purchase->id))">
                                                <i class="fas fa-eye mr-1"></i>Bukti
                                            </button>
                                            @endif
                                            <button class="btn btn-xs ic-btn-action ic-btn-action-success"
                                                    onclick="confirmPayment('{{ $purchase->id }}')">
                                                <i class="fas fa-check mr-1"></i>Konfirmasi
                                            </button>
                                        </div>
                                        @else
                                            <span class="ic-role-note">Menunggu Finance</span>
                                        @endif
                                    @else
                                        <span class="badge badge-light text-muted border">—</span>
                                    @endif
                                    @break

                                @case(\App\Schemas\ParamSchema::PROCESS_INSTALLATION)
                                @case(\App\Schemas\ParamSchema::CUSTOMER_EXISTING)
                                    @if($technical_access)
                                    <button class="btn btn-xs ic-btn-action ic-btn-action-primary"
                                            wire:click="openInstallationModal(@js($customer->id))">
                                        <i class="fas fa-camera mr-1"></i>Instalasi
                                    </button>
                                    @else
                                        <span class="ic-role-note">Teknisi</span>
                                    @endif
                                    @break

                                @case(\App\Schemas\ParamSchema::INSTALLED)
                                    <span class="ic-action-status text-success">
                                        <i class="fas fa-check-circle mr-1"></i>Terpasang
                                    </span>
                                    @break

                                @case(\App\Schemas\ParamSchema::ACTIVE)
                                    @if($finance_access)
                                    <button class="btn btn-xs ic-btn-action ic-btn-action-outline-danger"
                                            onclick="return confirm('Non-aktifkan pelanggan ini?') ? @this.call('suspend', @js($customer->id)) : false">
                                        <i class="fas fa-pause mr-1"></i>Suspend
                                    </button>
                                    @else
                                        <span class="ic-role-note">Finance</span>
                                    @endif
                                    @break

                                @case(\App\Schemas\ParamSchema::SUSPENDED)
                                    @if($finance_access)
                                    <button class="btn btn-xs ic-btn-action ic-btn-action-outline-success"
                                            onclick="return confirm('Aktifkan kembali pelanggan ini?') ? @this.call('reactivate', @js($customer->id)) : false">
                                        <i class="fas fa-play mr-1"></i>Aktifkan
                                    </button>
                                    @else
                                        <span class="ic-role-note">Finance</span>
                                    @endif
                                    @break

                                @case(\App\Schemas\ParamSchema::CLOSED)
                                    <span class="ic-action-status text-secondary">
                                        <i class="fas fa-ban mr-1"></i>Ditutup
                                    </span>
                                    @break
                                @endswitch
                            </td>

                            {{-- Tanggal Daftar --}}
                            <td class="ic-td">
                                <div class="ic-date-primary">{{ $customer->created_at->format('d M Y') }}</div>
                                <div class="ic-date-secondary">{{ $customer->created_at->format('H:i') }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-5 text-center">
                                <div class="ic-empty-state">
                                    <div class="ic-empty-icon"><i class="fas fa-user-slash"></i></div>
                                    <div class="ic-empty-title">Tidak ada data pelanggan</div>
                                    <div class="ic-empty-sub">Coba ubah filter atau kata kunci pencarian</div>
                                    <button class="btn btn-sm ic-btn ic-btn-outline-primary mt-3" wire:click="resetSearch">
                                        <i class="fas fa-undo mr-1"></i>Reset Filter
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination bar --}}
            <div class="ic-pagination-bar d-flex justify-content-between align-items-center flex-wrap px-3 py-2" style="gap:.5rem;">
                <div class="d-flex align-items-center" style="gap:.5rem;">
                    <span class="small text-muted">Tampilkan</span>
                    <select wire:model="perPage" class="form-control form-control-sm ic-per-page">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="small text-muted">data</span>
                </div>
                <div>
                    {{ $internetCustomers->links() }}
                </div>
                <div class="small text-muted">
                    @if($internetCustomers->total() > 0)
                        {{ $internetCustomers->firstItem() }}–{{ $internetCustomers->lastItem() }}
                        dari <strong>{{ $internetCustomers->total() }}</strong> pelanggan
                    @else
                        Tidak ada data
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{-- ══ END TABLE CARD ═══════════════════════════════════════════════════ --}}

</div>
</div>
@endcanAccess

<!-- Modal Instalasi -->
<div class="modal fade" id="installationModal" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog modal-lg">
        <div class="modal-content ic-modal">

            <div class="modal-header ic-modal-header">
                <h5 class="modal-title"><i class="fas fa-tools mr-2"></i>Proses Instalasi</h5>
                <button type="button" class="btn ic-modal-close" data-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="ic-modal-customer-info mb-4">
                    <div class="ic-modal-customer-name" id="modalCustomerName"></div>
                    <div class="mt-1">
                        Kode: <span class="badge badge-info ic-code-badge" id="modalCustomerCode"></span>
                    </div>
                </div>

                <form id="installationForm">
                    <div class="form-group">
                        <label class="ic-label">Serial Number Perangkat</label>
                        <input type="text" class="form-control ic-input" wire:model="serialNumber" id="modalSerialNumber" required>
                    </div>

                    <div wire:ignore>
                        <div class="form-group">
                            <label class="ic-label">ODP (Optical Distribution Point) <span class="text-danger">*</span></label>
                            <select class="form-control ic-input" id="odpSelect">
                                <option value="">— Pilih ODP —</option>
                            </select>
                            <small class="form-text text-muted">Pilih ODP sesuai lokasi pemasangan pelanggan.</small>
                        </div>
                    </div>

                    <div wire:ignore>
                        <div class="form-group">
                            <label class="ic-label">Group <span class="text-danger">*</span></label>
                            <select class="form-control ic-input" id="groupSelect">
                                <option value="">— Pilih ODP dulu —</option>
                            </select>
                            <small class="form-text text-muted" id="groupSelectHint">Group difilter otomatis dari ODP yang dipilih.</small>
                        </div>

                        <div class="form-group" id="groupingPreviewBox" style="display:none;">
                            <label class="ic-label">Grouping ID <small class="text-muted font-weight-normal">(bisa diubah jika perlu)</small></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                </div>
                                <input type="text" class="form-control ic-input font-weight-bold" id="groupingIdPreview" placeholder="e.g. HN110001">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="useAsUsernameBtn" title="Gunakan sebagai username">
                                        <i class="fas fa-arrow-right"></i> Pakai sbg Username
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">Saran otomatis — bisa diubah jika nomor sudah terpakai.</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="ic-label">Router</label>
                        <select id="routerSelect" class="form-control ic-input"></select>
                        <input type="hidden" id="routerSelectMirror" wire:model="router_id">
                    </div>

                    <div class="form-group">
                        <label class="ic-label">Pilih IP Pool <span class="text-muted">(opsional)</span></label>
                        <select class="form-control ic-input" wire:model="override_pool_id"
                                wire:key="pool-select-{{ $router_id }}-{{ count($availablePools) }}" id="selectPool">
                            <option value="">— Ikuti mapping otomatis —</option>
                            @foreach($availablePools as $pool)
                                <option value="{{ $pool['id'] }}">{{ $pool['label'] }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Kosongkan jika ingin pakai pool default/PPPoE server router.</small>
                    </div>

                    <div class="form-group">
                        <label class="ic-label">Local Address</label>
                        <div class="input-group">
                            <input type="text" class="form-control ic-input" id="local_address" placeholder="192.168.1.1">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <div wire:loading wire:target="local_address">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </div>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="ic-label">Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control ic-input" id="modalUsername" placeholder="username_pppoe">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <div wire:loading wire:target="username">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </div>
                                    <div wire:loading.remove wire:target="username"></div>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="ic-label">Password</label>
                        <input type="password" class="form-control ic-input" wire:model="password" id="modalPassword" required>
                    </div>

                    <div class="form-group">
                        <label class="ic-label">Foto Instalasi <span class="text-muted">(Minimal 1 foto)</span></label>
                        <input type="file" class="form-control-file" wire:model="photos" id="modalPhotos" multiple accept="image/*">
                        <div id="photoPreview" class="mt-2 d-flex flex-wrap" style="gap:.5rem;"></div>
                    </div>

                    <div class="form-group">
                        <label class="ic-label">Catatan Instalasi</label>
                        <textarea class="form-control ic-input" wire:model="installationNotes" id="modalNotes" rows="3"></textarea>
                    </div>

                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary ic-btn" data-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary ic-btn" id="submitInstallation">
                            <i class="fas fa-check-circle mr-1"></i> Selesaikan Instalasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lihat Bukti Bayar -->
<div class="modal fade" id="paymentProofModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content ic-modal">
            <div class="modal-header ic-modal-header-light">
                <h5 class="modal-title"><i class="fas fa-file-image mr-2"></i>Bukti Pembayaran Pelanggan</h5>
                <button type="button" class="btn ic-modal-close-dark" data-dismiss="modal" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="showPaymentProof"></div>
        </div>
    </div>
</div>

<!-- Modal Alamat Lengkap -->
<div class="modal fade" id="addressModal" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog">
        <div class="modal-content ic-modal">
            <div class="modal-header ic-modal-header-light">
                <h5 class="modal-title"><i class="fas fa-map-marker-alt mr-2"></i>Alamat Lengkap</h5>
                <button type="button" class="btn-close ic-modal-close-dark" data-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($selectedCustomer)
                    <p class="mb-1">{{ $selectedCustomer->address }}</p>
                    <p class="text-muted mt-2 small">
                        {{ $selectedCustomer->subdistrict->name }},
                        {{ $selectedCustomer->district->name }},
                        {{ $selectedCustomer->city->name }},
                        {{ $selectedCustomer->province->name }}
                    </p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary ic-btn" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════════════
   IC DESIGN SYSTEM — Bootstrap 4 Compatible
   ═══════════════════════════════════════════════════ */

/* ── Variables ─────────────────────────────────────── */
:root {
    --ic-primary:        #1a56db;
    --ic-primary-light:  #e8efff;
    --ic-primary-dark:   #1341b0;
    --ic-success:        #0e9f6e;
    --ic-success-light:  #def7ec;
    --ic-warning:        #e3a008;
    --ic-warning-light:  #fdf6b2;
    --ic-danger:         #e02424;
    --ic-danger-light:   #fde8e8;
    --ic-info:           #0694a2;
    --ic-info-light:     #d5f5f6;
    --ic-border:         #e5e7eb;
    --ic-bg-soft:        #f9fafb;
    --ic-text-dark:      #1f2937;
    --ic-text-mid:       #4b5563;
    --ic-text-muted:     #9ca3af;
    --ic-radius:         8px;
    --ic-radius-lg:      12px;
    --ic-shadow:         0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.04);
    --ic-shadow-md:      0 4px 6px -1px rgba(0,0,0,.08), 0 2px 4px -1px rgba(0,0,0,.04);
}

/* ── Card base ──────────────────────────────────────── */
.ic-card {
    border: 1px solid var(--ic-border) !important;
    border-radius: var(--ic-radius-lg) !important;
    box-shadow: var(--ic-shadow-md) !important;
    overflow: hidden;
}

/* ── Card headers ───────────────────────────────────── */
.ic-card-header {
    background: #fff;
    border-bottom: 1px solid var(--ic-border);
}

.ic-table-header {
    background: linear-gradient(135deg, var(--ic-primary) 0%, var(--ic-primary-dark) 100%);
    border-bottom: none;
}

.ic-import-header {
    background: rgba(243,186,47,.08);
    border-bottom: 1px solid rgba(230,160,20,.2);
}

/* ── Section title ──────────────────────────────────── */
.ic-section-title {
    font-size: .84rem;
    font-weight: 700;
    color: var(--ic-text-mid);
    letter-spacing: .01em;
}

/* ── Count badge ────────────────────────────────────── */
.ic-count-badge {
    background: rgba(255,255,255,.18);
    color: #fff;
    font-size: .75rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,.25);
    letter-spacing: .02em;
}

/* ── Labels ─────────────────────────────────────────── */
.ic-label {
    display: block;
    font-size: .72rem;
    font-weight: 700;
    color: var(--ic-text-mid);
    margin-bottom: 4px;
    letter-spacing: .04em;
    text-transform: uppercase;
}

/* ── Form controls ──────────────────────────────────── */
.ic-select,
.ic-input {
    border-color: var(--ic-border) !important;
    border-radius: 6px !important;
    font-size: .82rem !important;
    color: var(--ic-text-dark) !important;
    transition: border-color .15s, box-shadow .15s;
}
.ic-select:focus,
.ic-input:focus {
    border-color: var(--ic-primary) !important;
    box-shadow: 0 0 0 3px rgba(26,86,219,.12) !important;
}

.ic-search-input {
    border-left: none !important;
    border-radius: 0 6px 6px 0 !important;
    font-size: .82rem !important;
}
.ic-search-input:focus {
    box-shadow: none !important;
    border-color: var(--ic-primary) !important;
}
.ic-input-prepend {
    background: #fff !important;
    border-right: none !important;
    border-radius: 6px 0 0 6px !important;
    color: var(--ic-text-muted);
}
.ic-clear-btn { border-radius: 0 6px 6px 0 !important; font-size: .68rem; }
.ic-date-input { width: 148px; border-radius: 6px !important; font-size: .8rem !important; }
.ic-per-page { width: 68px; border-radius: 6px !important; font-size: .8rem !important; }

/* ── Date zone ──────────────────────────────────────── */
.ic-date-zone {
    background: linear-gradient(135deg, #f5f7ff 0%, #eef2ff 100%);
    border: 1px solid #dde3ff;
    border-radius: var(--ic-radius);
    padding: 10px 14px;
}

/* ── Radio buttons (date type) ──────────────────────── */
.ic-radio-btn {
    font-size: .78rem !important;
    font-weight: 600 !important;
    padding: 4px 10px !important;
    border: 1px solid #c7d2fe !important;
    color: var(--ic-primary) !important;
    background: #fff !important;
    border-radius: 0 !important;
    transition: all .15s;
}
.ic-radio-btn:first-of-type { border-radius: 6px 0 0 6px !important; }
.ic-radio-btn:last-of-type  { border-radius: 0 6px 6px 0 !important; }

/* Checked state via adjacent sibling */
input[type="radio"].d-none:checked + .ic-radio-btn {
    background: var(--ic-primary) !important;
    color: #fff !important;
    border-color: var(--ic-primary) !important;
}

/* ── Buttons ────────────────────────────────────────── */
.ic-btn {
    font-size: .78rem !important;
    font-weight: 600 !important;
    border-radius: 6px !important;
    padding: 5px 12px !important;
    transition: all .15s;
    letter-spacing: .01em;
}
.ic-btn-primary     { background: var(--ic-primary) !important; border-color: var(--ic-primary) !important; color:#fff!important; }
.ic-btn-primary:hover { background: var(--ic-primary-dark) !important; border-color: var(--ic-primary-dark) !important; }
.ic-btn-outline-success { color: var(--ic-success) !important; border-color: var(--ic-success) !important; background:#fff!important; }
.ic-btn-outline-success:hover { background: var(--ic-success-light) !important; }
.ic-btn-outline-warning { color: var(--ic-warning) !important; border-color: #c9920e !important; background:#fff!important; }
.ic-btn-outline-warning:hover { background: var(--ic-warning-light) !important; }
.ic-btn-outline-primary { color: var(--ic-primary) !important; border-color: var(--ic-primary) !important; background:#fff!important; }
.ic-btn-outline-primary:hover { background: var(--ic-primary-light) !important; }
.ic-btn-outline-secondary { border-color: var(--ic-border) !important; color: var(--ic-text-mid) !important; background:#fff!important; }
.ic-btn-outline-secondary:hover { background: var(--ic-bg-soft) !important; }
.ic-btn-ghost { background: transparent !important; border-color: var(--ic-border) !important; }
.ic-btn-ghost:hover { background: var(--ic-bg-soft) !important; }

/* ── Action buttons (in table) ──────────────────────── */
.ic-btn-action {
    font-size: .73rem !important;
    font-weight: 600 !important;
    border-radius: 5px !important;
    padding: 3px 10px !important;
    line-height: 1.5 !important;
    letter-spacing: .01em;
    transition: all .12s;
    white-space: nowrap;
}
.ic-btn-action-primary         { background: var(--ic-primary) !important; border-color: var(--ic-primary) !important; color:#fff!important; }
.ic-btn-action-success         { background: var(--ic-success) !important; border-color: var(--ic-success) !important; color:#fff!important; }
.ic-btn-action-danger          { background: var(--ic-danger) !important; border-color: var(--ic-danger) !important; color:#fff!important; }
.ic-btn-action-info            { background: var(--ic-info) !important; border-color: var(--ic-info) !important; color:#fff!important; }
.ic-btn-action-outline-danger  { background: #fff!important; border: 1px solid var(--ic-danger)!important; color: var(--ic-danger)!important; }
.ic-btn-action-outline-success { background: #fff!important; border: 1px solid var(--ic-success)!important; color: var(--ic-success)!important; }
.ic-btn-action-outline-danger:hover  { background: var(--ic-danger-light) !important; }
.ic-btn-action-outline-success:hover { background: var(--ic-success-light) !important; }

/* ── Alert overrides ────────────────────────────────── */
.ic-alert-info {
    background: var(--ic-info-light);
    border: 1px solid rgba(6,148,162,.2);
    border-radius: 6px;
    color: #065f62;
}

/* ── Table ──────────────────────────────────────────── */
.ic-table { border-collapse: collapse; }
.ic-th {
    font-size: .7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--ic-text-mid);
    background: var(--ic-bg-soft);
    border-bottom: 2px solid var(--ic-border) !important;
    border-top: none !important;
    padding: 9px 10px !important;
    white-space: nowrap;
}
.ic-th-sortable { cursor: pointer; user-select: none; }
.ic-th-sortable:hover { color: var(--ic-primary); background: var(--ic-primary-light); }
.ic-sort-icon { opacity: .35; }

.ic-tr { transition: background .1s; }
.ic-tr:hover .ic-td { background: #f0f4ff !important; }
.ic-td {
    padding: 9px 10px !important;
    vertical-align: middle !important;
    border-bottom: 1px solid #f3f4f6 !important;
    border-top: none !important;
}

/* ── Table cell content ─────────────────────────────── */
.ic-code {
    font-family: 'SFMono-Regular', 'Consolas', monospace;
    font-size: .8rem;
    font-weight: 700;
    color: var(--ic-info);
    letter-spacing: .03em;
}
.ic-name-link {
    font-size: .84rem;
    font-weight: 600;
    color: var(--ic-text-dark);
    text-decoration: none;
    transition: color .12s;
}
.ic-name-link:hover { color: var(--ic-primary) !important; text-decoration: underline !important; }
.ic-address { font-size: .6rem; color: var(--ic-text-mid); line-height: 1.45; }
.ic-more-link { font-size: .73rem; color: var(--ic-primary); margin-top: 2px; }
.ic-package-name { font-size: .82rem; font-weight: 600; color: var(--ic-text-dark); }
.ic-serial { font-family: 'SFMono-Regular', monospace; font-size: .73rem; color: var(--ic-text-muted); }
.ic-date-primary { font-size: .79rem; font-weight: 600; color: var(--ic-text-dark); }
.ic-date-secondary { font-size: .73rem; color: var(--ic-text-muted); }
.ic-action-status { font-size: .77rem; font-weight: 600; }
.ic-role-note { font-size: .73rem; color: var(--ic-text-muted); }

/* ── Type badges ────────────────────────────────────── */
.ic-type-badge {
    display: inline-flex;
    align-items: center;
    font-size: .67rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 20px;
    line-height: 1.4;
    letter-spacing: .01em;
}
.ic-type-rumah  { background: var(--ic-success-light); color: #065f46; }
.ic-type-bisnis { background: var(--ic-primary-light); color: #1e3a8a; }
.ic-type-group  { background: #f3f4f6; color: var(--ic-text-mid); border: 1px solid #e5e7eb; }

/* ── Billing rows ───────────────────────────────────── */
.ic-billing-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    font-size: .72rem;
    color: var(--ic-text-muted);
    line-height: 1.6;
}
.ic-billing-renewal { color: #92400e; }
.ic-billing-chip {
    font-size: .7rem;
    font-weight: 600;
    padding: 1px 7px;
    border-radius: 20px;
}
.ic-chip-start { background: var(--ic-primary-light); color: #1e3a8a; }
.ic-chip-end   { background: var(--ic-danger-light);  color: #9b1c1c; }

/* ── Jatuh Tempo badge ──────────────────────────────── */
.ic-due-badge {
    display: inline-flex;
    align-items: center;
    font-size: .7rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 20px;
    letter-spacing: .01em;
    line-height: 1.5;
}
.ic-due-ok {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}
.ic-due-warning {
    background: #fffbeb;
    color: #92400e;
    border: 1px solid #fcd34d;
}
.ic-due-overdue {
    background: var(--ic-danger-light);
    color: #991b1b;
    border: 1px solid #fca5a5;
    animation: ic-pulse-danger 2s ease-in-out infinite;
}
@keyframes ic-pulse-danger {
    0%, 100% { box-shadow: 0 0 0 0 rgba(220,38,38,.3); }
    50%       { box-shadow: 0 0 0 4px rgba(220,38,38,0); }
}
.ic-due-label    { opacity: .75; margin-right: 3px; font-weight: 600; }
.ic-due-date     { font-weight: 700; }
.ic-due-countdown {
    font-size: .67rem;
    font-weight: 600;
    opacity: .8;
    margin-left: 3px;
}

/* ── Empty state ────────────────────────────────────── */
.ic-empty-state { padding: 20px; }
.ic-empty-icon  { font-size: 2.2rem; color: #d1d5db; margin-bottom: .75rem; }
.ic-empty-title { font-size: .92rem; font-weight: 700; color: var(--ic-text-mid); margin-bottom: .35rem; }
.ic-empty-sub   { font-size: .8rem; color: var(--ic-text-muted); }

/* ── Pagination bar ─────────────────────────────────── */
.ic-pagination-bar {
    background: var(--ic-bg-soft);
    border-top: 1px solid var(--ic-border);
    min-height: 50px;
}
.ic-pagination-bar .pagination { margin: 0; }
.ic-pagination-bar .page-link {
    font-size: .78rem;
    padding: 4px 9px;
    border-color: var(--ic-border);
    color: var(--ic-primary);
}
.ic-pagination-bar .page-item.active .page-link {
    background: var(--ic-primary);
    border-color: var(--ic-primary);
}

/* ── Progress bar ───────────────────────────────────── */
.ic-progress {
    height: 22px;
    border-radius: 6px;
    overflow: hidden;
    background: #e5e7eb;
}

/* ── Import stat cards ──────────────────────────────── */
.ic-stat-card {
    text-align: center;
    padding: 10px 8px;
    border: 1px solid var(--ic-border);
    border-radius: var(--ic-radius);
    background: #fff;
    box-shadow: var(--ic-shadow);
}
.ic-stat-value { font-size: 1.35rem; font-weight: 800; line-height: 1.2; color: var(--ic-text-dark); }
.ic-stat-label { font-size: .7rem; color: var(--ic-text-muted); margin-top: 3px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }

/* ── Modals ─────────────────────────────────────────── */
.ic-modal { border: none; border-radius: var(--ic-radius-lg); overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,.18); }
.ic-modal-header {
    background: linear-gradient(135deg, var(--ic-primary) 0%, var(--ic-primary-dark) 100%);
    color: #fff;
    border-bottom: none;
    padding: 14px 20px;
}
.ic-modal-header .modal-title { font-size: .95rem; font-weight: 700; color: #fff; }
.ic-modal-close {
    background: rgba(255,255,255,.15) !important;
    color: #fff !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 4px 8px !important;
    line-height: 1 !important;
    font-size: .85rem !important;
    transition: background .15s;
}
.ic-modal-close:hover { background: rgba(255,255,255,.28) !important; }

.ic-modal-header-light {
    background: var(--ic-bg-soft);
    border-bottom: 1px solid var(--ic-border);
    padding: 14px 20px;
}
.ic-modal-close-dark {
    background: #e5e7eb !important;
    color: var(--ic-text-dark) !important;
    border: none !important;
    border-radius: 6px !important;
    padding: 4px 8px !important;
    font-size: .85rem !important;
}

.ic-modal-customer-info {
    background: linear-gradient(135deg, var(--ic-primary-light) 0%, #e8efff 100%);
    border: 1px solid #c7d2fe;
    border-radius: var(--ic-radius);
    padding: 12px 16px;
}
.ic-modal-customer-name { font-size: 1rem; font-weight: 700; color: var(--ic-text-dark); }
.ic-code-badge { font-family: monospace; font-size: .82rem; letter-spacing: .03em; }
</style>
@endpush

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>

    function copyShareLink() {
        const link = document.getElementById('share-link').href;
        navigator.clipboard.writeText(link).then(() => {
            alert('Link berhasil disalin!');
        }).catch(err => {
            console.error('Gagal menyalin link:', err);
        });
    }

    // ── Auto-fill dateTo ke akhir bulan saat dateFrom diisi ──────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const dateFromEl = document.getElementById('ic-date-from');
        const dateToEl   = document.getElementById('ic-date-to');
        if (!dateFromEl || !dateToEl) return;

        dateFromEl.addEventListener('change', function () {
            const val = this.value;
            if (!val) return;

            // Hanya auto-fill jika dateTo masih kosong
            if (dateToEl.value) return;

            const d = new Date(val);
            // Akhir bulan yang sama
            const endOfMonth = new Date(d.getFullYear(), d.getMonth() + 1, 0);
            const yyyy = endOfMonth.getFullYear();
            const mm   = String(endOfMonth.getMonth() + 1).padStart(2, '0');
            const dd   = String(endOfMonth.getDate()).padStart(2, '0');
            const autoVal = `${yyyy}-${mm}-${dd}`;

            dateToEl.value = autoVal;
            // Sync ke Livewire
            @this.set('dateTo', autoVal);
        });
    });

    function confirmPayment(customerId) {
        Swal.fire({
            title: 'Konfirmasi Pembayaran?',
            text: "Pastikan bukti pembayaran sudah valid.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, konfirmasi',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('confirmPayment', customerId);
            }
        });
    }

    window.addEventListener('showPaymentProofModal', function(data) {
        const modal = $('#paymentProofModal').modal('show');
        let content = `<img src="${data.detail.proofUrl}" class="img-fluid rounded">`;
        if (data.detail.transferDetails) {
            const details = data.detail.transferDetails;
            content += `
                <div class="mt-3">
                    <table class="table table-sm table-bordered">
                        <tbody>
                            ${details.date ? `<tr><th width="40%">Tanggal Transfer</th><td>${details.date}</td></tr>` : ''}
                            ${details.bank ? `<tr><th>Bank Pengirim</th><td>${details.bank}</td></tr>` : ''}
                            ${details.account_name ? `<tr><th>Nama Pengirim</th><td>${details.account_name}</td></tr>` : ''}
                            ${details.notes ? `<tr><th>Catatan</th><td>${details.notes}</td></tr>` : ''}
                        </tbody>
                    </table>
                </div>`;
        }
        document.getElementById('showPaymentProof').innerHTML = content;
    });

    window.addEventListener('showSuccessAlert', function(event) {
        Swal.fire({ icon: 'success', title: event.detail.title, text: event.detail.message, showConfirmButton: false, timerProgressBar: true, timer: 3000 });
    });

    window.addEventListener('showErrorAlert', function(event) {
        Swal.fire({ icon: 'error', title: event.detail.title, text: event.detail.message, showConfirmButton: false, timerProgressBar: true, timer: 3000 });
    });

    document.addEventListener('livewire:load', function() {
        let uploadedFiles = [];
        // Track access type & bypass mode untuk validasi submit
        let currentAccessType  = 'pppoe';
        let currentBypassMode  = false;

        window.addEventListener('usernameCheckComplete', function(event) {
            const data = event.detail;
            const inputUsername = document.getElementById('modalUsername');
            const iconContainer = inputUsername.closest('.input-group').querySelector('.input-group-text div:not([wire\\:loading])');
            if (data.available) {
                inputUsername.classList.remove('is-invalid');
                inputUsername.classList.add('is-valid');
                if (iconContainer) iconContainer.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
            } else {
                inputUsername.classList.remove('is-valid');
                inputUsername.classList.add('is-invalid');
                if (iconContainer) iconContainer.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
                if (data.existing) {
                    let errorDiv = inputUsername.parentElement.parentElement.querySelector('.username-error-msg');
                    if (!errorDiv) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback d-block username-error-msg';
                        inputUsername.parentElement.parentElement.appendChild(errorDiv);
                    }
                    errorDiv.innerHTML = `Username sudah digunakan oleh: <strong>${data.existing.code} - ${data.existing.name}</strong>`;
                }
            }
        });

        window.addEventListener('localAddressCheckComplete', function(event) {
            const data = event.detail;
            const inputLocalAddress = document.getElementById('local_address');
            if (data.valid) {
                inputLocalAddress.classList.remove('is-invalid');
                inputLocalAddress.classList.add('is-valid');
                const errorDiv = inputLocalAddress.parentElement.querySelector('.local-address-error-msg');
                if (errorDiv) errorDiv.remove();
            } else {
                inputLocalAddress.classList.remove('is-valid');
                inputLocalAddress.classList.add('is-invalid');
                let errorDiv = inputLocalAddress.parentElement.querySelector('.local-address-error-msg');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback d-block local-address-error-msg';
                    inputLocalAddress.parentElement.appendChild(errorDiv);
                }
                errorDiv.textContent = data.message;
            }
        });

        document.getElementById('modalUsername')?.addEventListener('input', function(e) {
            const iconContainer = this.closest('.input-group').querySelector('.input-group-text div:not([wire\\:loading])');
            if (iconContainer) iconContainer.innerHTML = '';
            this.classList.remove('is-valid', 'is-invalid');
            const errorDiv = this.parentElement.parentElement.querySelector('.username-error-msg');
            if (errorDiv) errorDiv.remove();
            @this.set('username', e.target.value);
        });

        document.getElementById('local_address')?.addEventListener('input', function(e) {
            this.classList.remove('is-valid', 'is-invalid');
            const errorDiv = this.parentElement.querySelector('.local-address-error-msg');
            if (errorDiv) errorDiv.remove();
            @this.set('local_address', e.target.value);
        });

        window.addEventListener('groups-loaded', function(e) {
            const groupSelect = document.getElementById('groupSelect');
            const hint = document.getElementById('groupSelectHint');
            if (!groupSelect) return;
            const groups = e.detail.groups || [];
            groupSelect.innerHTML = '';
            const previewBox = document.getElementById('groupingPreviewBox');
            if (previewBox) previewBox.style.display = 'none';
            if (groups.length === 0) {
                const opt = document.createElement('option');
                opt.value = ''; opt.textContent = '— Tidak ada group untuk ODP ini —';
                groupSelect.appendChild(opt);
                if (hint) hint.classList.add('text-warning');
            } else {
                const placeholder = document.createElement('option');
                placeholder.value = ''; placeholder.textContent = '— Pilih Group —';
                groupSelect.appendChild(placeholder);
                groups.forEach(function(g) {
                    const opt = document.createElement('option');
                    opt.value = g.id;
                    opt.textContent = g.description ? g.name + ' — ' + g.description : g.name;
                    groupSelect.appendChild(opt);
                });
                if (hint) hint.classList.remove('text-warning');
            }
            groupSelect.onchange = function() {
                const val = groupSelect.value || null;
                const previewBox = document.getElementById('groupingPreviewBox');
                if (!val) { if (previewBox) previewBox.style.display = 'none'; return; }
                @this.call('previewGroupingId', val);
            };
        });

        function resetGroupingIdState() {
            const input = document.getElementById('groupingIdPreview');
            if (!input) return;
            input.classList.remove('is-valid', 'is-invalid');
            const err = input.closest('.input-group')?.parentElement?.querySelector('.grouping-id-error-msg');
            if (err) err.remove();
            window._groupingIdAvailable = true;
        }

        window.addEventListener('groupingIdCheckComplete', function(event) {
            const data = event.detail;
            const input = document.getElementById('groupingIdPreview');
            if (!input) return;
            const wrap = input.closest('.input-group')?.parentElement;
            let errorDiv = wrap?.querySelector('.grouping-id-error-msg');
            if (data.available) {
                input.classList.remove('is-invalid'); input.classList.add('is-valid');
                if (errorDiv) errorDiv.remove();
                window._groupingIdAvailable = true;
            } else {
                input.classList.remove('is-valid'); input.classList.add('is-invalid');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback d-block grouping-id-error-msg';
                    wrap.appendChild(errorDiv);
                }
                errorDiv.innerHTML = `Grouping ID sudah digunakan oleh: <strong>${data.existing.code} - ${data.existing.name}</strong>`;
                window._groupingIdAvailable = false;
            }
        });

        let _groupingTimer = null;
        document.addEventListener('input', function(e) {
            if (!e.target || e.target.id !== 'groupingIdPreview') return;
            clearTimeout(_groupingTimer);
            const val = e.target.value.trim();
            if (!val || val.length < 2) { resetGroupingIdState(); return; }
            _groupingTimer = setTimeout(function() {
                @this.call('checkGroupingIdAvailability', val);
            }, 400);
        });

        window.addEventListener('grouping-id-preview', function(e) {
            const preview = e.detail.preview;
            const previewBox = document.getElementById('groupingPreviewBox');
            const previewInput = document.getElementById('groupingIdPreview');
            if (!previewBox || !previewInput) return;
            if (!preview) { previewBox.style.display = 'none'; previewInput.value = ''; resetGroupingIdState(); return; }
            previewInput.value = preview;
            previewBox.style.display = 'block';
            @this.call('checkGroupingIdAvailability', preview);
            const usernameInput = document.getElementById('modalUsername');
            if (usernameInput && !usernameInput.value) {
                usernameInput.value = preview;
                @this.set('username', preview);
            }
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('#useAsUsernameBtn')) {
                const preview = document.getElementById('groupingIdPreview')?.value;
                const usernameInput = document.getElementById('modalUsername');
                if (preview && usernameInput) { usernameInput.value = preview; @this.set('username', preview); }
            }
        });

        window.addEventListener('pools-options', (e) => {
            const select = document.querySelector('select[wire\\:model="override_pool_id"]');
            if (!select) return;
            select.querySelectorAll('option:not(:first-child)').forEach(o => o.remove());
            const options = e.detail.options || [];
            options.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id; opt.textContent = p.label;
                select.appendChild(opt);
            });
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
        
        // Fungsi toggle auth fields (username/password) berdasarkan mode bypassed
        function toggleBypassedMode(isBypassed) {
            currentBypassMode = isBypassed;
            document.getElementById('authFields').style.display    = isBypassed ? 'none' : 'block';
            document.getElementById('bypassedInfo').style.display  = isBypassed ? 'block' : 'none';
            if (isBypassed) {
                // Reset & bersihkan auth fields saat mode bypassed
                document.getElementById('modalUsername').value = '';
                document.getElementById('modalPassword').value = '';
                @this.set('username', '');
                @this.set('password', '');
            }
        }

        // IP Binding type → show/hide detail fields + hide mode for radius
        document.getElementById('ipBindingTypeSelect')?.addEventListener('change', function() {
            const details  = document.getElementById('ipBindingDetails');
            const isDirect = this.value === 'direct';
            details.style.display = this.value ? 'block' : 'none';
            // Mode select (bypassed) hanya relevan untuk 'direct'
            const modeRow = document.getElementById('ipBindingModeSelect')?.closest('.col-md-6');
            if (modeRow) modeRow.style.display = isDirect ? '' : 'none';
            document.getElementById('ipBindingModeSelect').value = 'regular';
            toggleBypassedMode(false);
        });

        // IP Binding mode → toggle auth fields jika bypassed
        document.getElementById('ipBindingModeSelect')?.addEventListener('change', function() {
            toggleBypassedMode(this.value === 'bypassed');
        });

        // Hotspot server change → sync router_id
        document.getElementById('hotspotServerSelect')?.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            const routerId = opt.dataset.routerId || '';
            document.getElementById('hotspotServerMirror').value = this.value;
            document.getElementById('routerSelectMirror').value = routerId;
            @this.set('hotspot_server_id', this.value);
            @this.set('router_id', routerId);
        });

        // Event listener untuk populate modal
        window.addEventListener('open-installation-modal', (e) => {
            const { customerName, customerCode, serialNumber, routers, odps } = e.detail;

            document.getElementById('modalCustomerName').textContent = customerName;
            document.getElementById('modalCustomerCode').textContent = customerCode;
            document.getElementById('modalSerialNumber').value = serialNumber;

            // Populate ODP dropdown
            const odpSelect = document.getElementById('odpSelect');
            if (odpSelect) {
                odpSelect.innerHTML = '<option value="">— Pilih ODP —</option>';
                (odps || []).forEach(odp => {
                    const option = document.createElement('option');
                    option.value = odp.id; option.textContent = odp.label;
                    odpSelect.appendChild(option);
                });
                odpSelect.value = '';
            }

            // Reset group & grouping preview
            const groupSelect = document.getElementById('groupSelect');
            if (groupSelect) groupSelect.innerHTML = '<option value="">— Pilih ODP dulu —</option>';
            const previewBox = document.getElementById('groupingPreviewBox');
            if (previewBox) previewBox.style.display = 'none';
            const previewInput = document.getElementById('groupingIdPreview');
            if (previewInput) previewInput.value = '';
            resetGroupingIdState();

            @this.set('optical_distribution_id', null);
            if (odpSelect) {
                odpSelect.onchange = function() {
                    @this.set('optical_distribution_id', odpSelect.value || null);
                };
            }

            // Populate router dropdown
            const routerSelect = document.getElementById('routerSelect');
            routerSelect.innerHTML = '';
            const defaultOption = document.createElement('option');
            defaultOption.value = ''; defaultOption.textContent = 'Pilih Router';
            routerSelect.appendChild(defaultOption);
            routers.forEach(router => {
                const option = document.createElement('option');
                option.value = router.id; option.disabled = router.disabled; option.textContent = router.name;
                routerSelect.appendChild(option);
            });
            routerSelect.value = '';
            document.getElementById('routerSelectMirror').value = '';
            @this.set('router_id', '');
            @this.set('override_pool_id', '');

            routerSelect.onchange = function(e) {
                const val = e.target.value || '';
                document.getElementById('routerSelectMirror').value = val;
                @this.set('router_id', val);
                @this.set('override_pool_id', '');
                @this.call('loadPoolsForRouter', val);
            };

            // Reset form fields
            document.getElementById('modalUsername').value = '';
            document.getElementById('local_address').value = '';
            document.getElementById('modalPassword').value = '';
            document.getElementById('modalNotes').value = '';
            document.getElementById('photoPreview').innerHTML = '';
            document.getElementById('modalPhotos').value = '';
            uploadedFiles = [];
            @this.set('username', '');
            @this.set('local_address', '');
            @this.set('newUsernameChecked', false);
            @this.set('newUsernameAvailable', false);

            $('#installationModal').modal('show');
        });

        document.getElementById('modalPhotos').addEventListener('change', function(e) {
            const files = e.target.files;
            const preview = document.getElementById('photoPreview');
            preview.innerHTML = '';
            uploadedFiles = Array.from(files);
            for (let file of files) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const imgContainer = document.createElement('div');
                    imgContainer.className = 'position-relative';
                    imgContainer.style.cssText = 'width:90px; height:90px;';
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.className = 'img-thumbnail w-100 h-100';
                    img.style.objectFit = 'cover';
                    imgContainer.appendChild(img);
                    preview.appendChild(imgContainer);
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('submitInstallation').addEventListener('click', async function() {
            const serialNumber     = document.getElementById('modalSerialNumber').value;
            const notes            = document.getElementById('modalNotes').value;
            const files            = document.getElementById('modalPhotos').files;
            const routerId         = document.getElementById('routerSelectMirror').value;
            const odpId            = document.getElementById('odpSelect').value;
            const groupingId       = (document.getElementById('groupingIdPreview')?.value || '').trim() || null;
            const username         = @this.username;
            const password         = document.getElementById('modalPassword').value;
            const override_pool_id = @this.override_pool_id;
            const local_address    = @this.local_address;

            if (!odpId)         return Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'ODP harus dipilih' });
            if (!serialNumber)  return Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Serial number harus diisi' });
            if (files.length === 0) return Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Minimal upload 1 foto instalasi' });
            if (!routerId)      return Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Router harus dipilih' });
            if (!username)      return Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Username harus diisi' });
            if (!password)      return Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Password harus diisi' });
            
            // Konfirmasi
            const result = await Swal.fire({
                icon: 'question', title: 'Konfirmasi',
                text: 'Anda yakin ingin menyelesaikan instalasi ini?',
                showCancelButton: true, confirmButtonText: 'Ya, Selesaikan', cancelButtonText: 'Batal'
            });
            if (!result.isConfirmed) return;

            const submitBtn = document.getElementById('submitInstallation');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;

            try {
                const uploadPromises = [];
                for (let i = 0; i < files.length; i++) {
                    submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-1"></i> Mengupload ${i + 1}/${files.length}…`;
                    const uploadPromise = new Promise((resolve, reject) => {
                        @this.upload(`photos.${i}`, files[i], resolve, reject, () => {});
                    });
                    uploadPromises.push(uploadPromise);
                }
                await Promise.all(uploadPromises);
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan data…';
                await new Promise(resolve => setTimeout(resolve, 500));
                const success = await @this.call('completeInstallation',
                    serialNumber, notes, routerId, username, password,
                    override_pool_id, local_address, odpId, groupingId
                );
                if (success !== false) {
                    $('#installationModal').modal('hide');
                    document.getElementById('modalSerialNumber').value = '';
                    document.getElementById('modalNotes').value = '';
                    document.getElementById('modalPhotos').value = '';
                    document.getElementById('photoPreview').innerHTML = '';
                    document.getElementById('modalPassword').value = '';
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menyimpan instalasi: ' + (error.message || error) });
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });

        window.addEventListener('show-notification', (event) => {
            Swal.mixin({
                toast: true, position: 'top-end',
                showConfirmButton: false, timer: 3000, timerProgressBar: true,
            }).fire({ icon: event.detail.type, title: event.detail.message });
        });

        // ── IMPORT ────────────────────────────────────────────────────────────
        let importProgressInterval = null;

        window.addEventListener('import-started', event => {
            Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 })
                .fire({ icon: 'info', title: 'Import Dimulai', text: `Total ${event.detail.total_rows} data akan diimport` });
        });

        window.addEventListener('start-progress-check', event => {
            if (importProgressInterval) clearInterval(importProgressInterval);
            importProgressInterval = setInterval(() => { @this.call('checkImportProgress'); }, 1000);
        });

        window.addEventListener('import-completed', event => {
            if (importProgressInterval) { clearInterval(importProgressInterval); importProgressInterval = null; }
            const progress = event.detail.progress;
            Swal.fire({
                icon: progress.failed > 0 ? 'warning' : 'success',
                title: 'Import Selesai!',
                html: `<div class="text-left">
                    <p class="mb-2"><strong>Rangkuman Import:</strong></p>
                    <ul class="list-unstyled">
                        <li>📊 <strong>Total Data:</strong> ${progress.total}</li>
                        <li>✅ <strong>Berhasil:</strong> <span class="text-success">${progress.success}</span></li>
                        <li>❌ <strong>Gagal:</strong> <span class="text-danger">${progress.failed}</span></li>
                    </ul>
                    ${progress.failed > 0 && progress.errors?.length > 0 ? `
                        <hr>
                        <p class="text-warning mb-2"><strong>⚠️ Detail Baris Gagal:</strong></p>
                        <div style="max-height:200px; overflow-y:auto;">
                            ${progress.errors.map(err => `
                                <small><strong>Baris ${err.row}:</strong> ${err.message}
                                ${err.data ? `<br><em class="text-muted">Data: ${err.data}</em>` : ''}</small><hr class="my-1">
                            `).join('')}
                        </div>` : ''}
                    </div>`,
                confirmButtonText: 'OK', allowOutsideClick: false, width: '600px',
            }).then(() => { @this.call('resetImport'); });
        });

        window.addEventListener('beforeunload', () => {
            if (importProgressInterval) clearInterval(importProgressInterval);
        });
    });
</script>

<script>
    window.addEventListener('show-address-modal', () => {
        $('#addressModal').modal('show');
    });

    function exportInternetCustomer(format) {
        const params = new URLSearchParams(window.location.search);
        const base = "{{ route('internet-customer.export', ['format' => ':format']) }}".replace(':format', format);
        const qs = params.toString();
        window.location.href = base + (qs ? '?' + qs : '');
    }
</script>
@endpush