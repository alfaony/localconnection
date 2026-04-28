@canAccess('index', 'internet_customers')
<div class="row">
    <div class="col-md-12">
        @include('components.alert')
        <div class="card mb-4 mt-2">
           <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <button class="btn btn-sm btn-outline-secondary float-end" 
                            wire:click="resetSearch">
                        Reset Filter
                    </button>
                </h6>
                <div class="ml-auto d-flex align-items-center gap-2">
                    <a href="{{ route('internet-customer.create', Auth::user()->company->slug) }}"
                    target="_blank"
                    id="share-link"
                    class="btn btn-sm btn-outline-primary">
                        Pendaftaran Baru
                    </a>

                    <button class="btn btn-sm btn-outline-success ml-1" onclick="copyShareLink()">
                        Share Link Pendaftaran
                    </button>

                    @canAccess('import', 'internet_customers')
                    <button type="button"
                            class="btn btn-sm btn-outline-warning ml-1"
                            wire:click="toggleImportSection">
                        <i class="fas fa-file-import mr-1"></i>
                        {{ $showImportSection ? 'Tutup Import' : 'Import Instalasi' }}
                    </button>
                    @endcanAccess
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Paket Internet</label>
                        <select wire:model="selectedPackage" class="form-control">
                            <option value="">Semua Paket</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}">{{ $package->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select wire:model="statusFilter" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="customer_existing">Pelanggan Lama</option>
                            <option value="waiting_payment_confirmation">Menunggu Konfirmasi Pembayaran</option>
                            <option value="waiting_payment_subscription">Menunggu Pembayaran Subscription</option>
                            <option value="process_installation">Proses Instalasi</option>
                            <option value="installed">Terpasang</option>
                            <option value="reactivated">Reaktivasi</option>
                            <option value="active">Aktif</option>
                            <option value="suspended">Dihentikan</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="closed">Tutup</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tipe Pelanggan</label>
                        <select wire:model="customerTypeFilter" class="form-control">
                            <option value="">Semua Tipe</option>
                            <option value="rumah">Rumah</option>
                            <option value="bisnis">Bisnis</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Dari Tanggal</label>
                        <input wire:model="dateFrom" type="date" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sampai Tanggal</label>
                        <input wire:model="dateTo" type="date" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search"></i>
                            </span>
                            <input wire:model.debounce.300ms="search" type="text" class="form-control"
                                    placeholder="Cari pelanggan...">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- ── IMPORT SECTION ─────────────────────────────────────────────── --}}
        @canAccess('import', 'internet_customers')
        @if($showImportSection)
        <div class="card shadow-sm mb-4 border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">
                    <i class="fas fa-file-import mr-2"></i> Import Massal Instalasi Pelanggan
                </h6>
            </div>
            <div class="card-body">

                {{-- Panduan --}}
                <div class="alert alert-info">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Panduan Import:</strong>
                    <ol class="mb-0 mt-2">
                        <li>Download template CSV terlebih dahulu</li>
                        <li>Isi data sesuai format: <code>email, phone, username, password, grouping, serial_number, router, pppoe_pool, start_billing_date, end_billing_date</code></li>
                        <li>Tanggal menggunakan format <code>YYYY-MM-DD</code></li>
                        <li>Pastikan pelanggan sudah terdaftar di sistem (cari berdasarkan email/telepon)</li>
                        <li>Pilih ODP, upload file CSV, lalu klik <strong>Mulai Import</strong></li>
                    </ol>
                </div>

                {{-- Download Template --}}
                <div class="mb-3">
                    <button wire:click="downloadImportTemplate" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-download mr-1"></i> Download Template CSV
                    </button>
                </div>

                @if(!$isImporting)
                <form wire:submit.prevent="importCustomers">
                    <div class="row">

                        {{-- Pilih ODP --}}
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-broadcast-tower mr-1 text-danger"></i>
                                Pilih ODP <span class="text-danger">*</span>
                            </label>
                            <select wire:model="import_odp_id"
                                    class="form-control @error('import_odp_id') is-invalid @enderror">
                                <option value="">-- Pilih ODP --</option>
                                @foreach($importAvailableOdps as $odp)
                                    <option value="{{ $odp['id'] }}">{{ $odp['label'] }}</option>
                                @endforeach
                            </select>
                            @error('import_odp_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">ODP ini akan diterapkan ke semua baris dalam file CSV.</small>
                        </div>

                        {{-- Upload File --}}
                        <div class="col-md-5 mb-3">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-file-csv mr-1 text-success"></i>
                                File CSV <span class="text-danger">*</span>
                            </label>
                            <input type="file"
                                   class="form-control @error('csvFile') is-invalid @enderror"
                                   wire:model="csvFile"
                                   accept=".csv"
                                   wire:loading.attr="disabled"
                                   wire:target="csvFile">
                            @error('csvFile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: CSV, Maks. 10 MB</small>

                            {{-- Uploading state: shown by Livewire while XHR upload is in progress --}}
                            <div class="mt-2" wire:loading wire:target="csvFile">
                                <div class="alert alert-info py-2 mb-0 d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm mr-2"></div>
                                    <div>
                                        <strong>Mengupload file…</strong>
                                        <small class="d-block text-muted">Mohon tunggu</small>
                                    </div>
                                </div>
                            </div>

                            {{-- File ready --}}
                            @if($isFileReady && $csvFile)
                            <div class="mt-2">
                                <div class="alert alert-success py-2 mb-0 d-flex align-items-center">
                                    <i class="fas fa-check-circle mr-2 text-success"></i>
                                    <div class="flex-grow-1">
                                        <strong>File siap diimport!</strong>
                                        <small class="d-block">{{ $csvFile->getClientOriginalName() }}</small>
                                    </div>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger ml-2"
                                            wire:click="resetImport">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Tombol Import --}}
                        <div class="col-md-3 mb-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <button type="submit"
                                    class="btn btn-warning btn-block"
                                    wire:loading.attr="disabled"
                                    wire:target="importCustomers,csvFile"
                                    {{ !$isFileReady ? 'disabled' : '' }}>

                                <span wire:loading wire:target="importCustomers">
                                    <span class="spinner-border spinner-border-sm mr-1"></span>
                                    Memproses…
                                </span>
                                <span wire:loading wire:target="csvFile">
                                    <span class="spinner-border spinner-border-sm mr-1"></span>
                                    Mengupload…
                                </span>
                                <span wire:loading.remove wire:target="importCustomers,csvFile">
                                    @if($isFileReady)
                                        <i class="fas fa-upload mr-1"></i> Mulai Import
                                    @else
                                        <i class="fas fa-upload mr-1"></i> Upload & Import
                                    @endif
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
                @endif

                {{-- ── PROGRESS SECTION: tampil selama importing atau setelah selesai --}}
                @if($importProgress)
                <div class="mt-4">
                    <hr>
                    <h6 class="mb-3">
                        <i class="fas fa-hourglass-split mr-1"></i> Progress Import
                    </h6>

                    {{-- Progress Bar --}}
                    <div class="progress mb-3" style="height: 35px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated
                                    bg-{{ $this->getImportStatusColor($importProgress['status']) }}"
                             role="progressbar"
                             style="width: {{ $importProgress['percentage'] }}%"
                             aria-valuenow="{{ $importProgress['percentage'] }}"
                             aria-valuemin="0" aria-valuemax="100">
                            <strong>{{ $importProgress['percentage'] }}%</strong>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="card text-center">
                                <div class="card-body py-3">
                                    <span class="badge badge-{{ $this->getImportStatusColor($importProgress['status']) }} fs-6">
                                        {{ strtoupper($importProgress['status'] ?? 'PROCESSING') }}
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
                            <i class="fas fa-clock mr-1"></i>
                            Terakhir update: {{ \Carbon\Carbon::parse($importProgress['updated_at'])->format('d/m/Y H:i:s') }}
                        </small>
                    </div>

                    {{-- Error list --}}
                    @if(!empty($importProgress['errors']) && count($importProgress['errors']) > 0)
                    <div class="alert alert-warning">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Detail Gagal ({{ count($importProgress['errors']) }} baris)
                        </h6>
                        <div style="max-height: 250px; overflow-y: auto;">
                            @foreach($importProgress['errors'] as $err)
                                @if(is_array($err))
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex align-items-start">
                                        <span class="badge badge-danger mr-2 mt-1">Baris {{ $err['row'] ?? '?' }}</span>
                                        <div>
                                            <strong>{{ $err['message'] ?? 'Unknown error' }}</strong>
                                            @if(isset($err['data']))
                                                <br><small class="text-muted">Data: {{ $err['data'] }}</small>
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
                {{-- ── END PROGRESS SECTION ────────────────────────────────── --}}

            </div>
        </div>
        @endif
        @endcanAccess
        {{-- ── END IMPORT SECTION ──────────────────────────────────────────── --}}

        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>Daftar Pelanggan Internet
                    </h5>
                    <div class="d-flex">
                        
                    </div>
                </div>
            </div>
        
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th wire:click="sortBy('code')" style="cursor: pointer; width: 5%;">
                                    Kode
                                    @if($sortField === 'code')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('name')" style="cursor: pointer; width: 15%;">
                                    Nama
                                    @if($sortField === 'name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted"></i>
                                    @endif
                                </th>
                                <th style="width: 20%;">Alamat</th>
                                <th style="width: 30%;">Internet</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 10%;">Aksi</th>
                                <th wire:click="sortBy('created_at')" style="cursor: pointer; width: 15%;">
                                    Tanggal Daftar
                                    @if($sortField === 'created_at')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted"></i>
                                    @endif
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($internetCustomers as $customer)
                                <tr wire:key="customer-{{ $customer->id }}" 
                                    class="{{ $loop->odd ? 'bg-light' : '' }}">
                                    <td>
                                        <span class="badge bg-info">{{ $customer->code }}</span>
                                        @php $ct = $customer->customer_type ?? 'home'; @endphp
                                        @if($ct === 'bisnis')
                                            <span class="badge badge-primary" title="Bisnis"><i class="fas fa-building mr-1"></i>Bisnis</span>
                                        @else
                                            <span class="badge badge-success" title="Home"><i class="fas fa-home mr-1"></i>Rumah</span>
                                        @endif
                                        @if($customer->installation && ($customer->installation->grouping_id || $customer->installation->grouping_id))
                                            <div class="mt-1 d-flex gap-1">
                                                <span class="badge bg-light text-primary border border-primary-subtle" title="Grouping">
                                                    <i class="fas fa-users me-1"></i> {{ $customer->installation->grouping_id }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold">
                                            <a href="{{ route('internet-customer.show', $customer->id) }}" class="text-decoration-none">
                                                {{ $customer->name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        {{ Str::limit($customer->address, 50) }}
                                        @if(strlen($customer->address) > 50)
                                            <a href="#" wire:click.prevent="showFullAddress({{ $customer->id }})" 
                                                class="text-primary">selengkapnya</a>
                                        @endif
                                    </td>
                                    <td>
                                        <!-- Package Name -->
                                        <div class="fw-bold mb-1">
                                            {{ $customer->internetPackage->name ?? '-' }}
                                        </div>

                                        <!-- Compact Info Items -->
                                        <div class="d-flex flex-column gap-1">
                                            <!-- Serial Number -->
                                            @if($customer->installation && $customer->installation->device_serial_number)
                                            <div class="small text-secondary d-flex align-items-center gap-1">
                                                <i class="fas fa-barcode" style="width: 14px;"></i>
                                                <span>{{ $customer->installation->device_serial_number }}</span>
                                            </div>
                                            @endif

                                            <!-- Grouping -->
                                            @if($customer->grouping_id)
                                            <div class="small text-muted d-flex align-items-center">
                                                <i class="fas fa-users" style="width: 14px;"></i>
                                                <span> Group {{ $customer->grouping_id }}</span>
                                            </div>
                                            @endif  
                                            
                                             <!-- Billing Period -->
                                            @if($customer->getOldestUnconfirmed() && $customer->getOldestUnconfirmed()->confirmation_finance_at)
                                            <div class="pt-1 border-top border-light">
                                                <div class="small text-muted mb-1">
                                                    <i class="fas fa-calendar-alt me-1"></i><span> </span> Pembayaran Perpanjangan
                                                </div>
                                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                                    <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2" style="font-size: 0.7rem;">
                                                        {{ \Carbon\Carbon::parse($customer->getOldestUnconfirmed()->confirmation_finance_at)->format('d M Y H:i:s') }}
                                                    </span>
                                                </div>
                                            </div>
                                            @endif
                                            <!-- Billing Period -->
                                            @if($customer->userCustomer && $customer->userCustomer->start_billing_date && $customer->userCustomer->end_billing_date)
                                            <div class="pt-1 border-top border-light">
                                                <div class="small text-muted mb-1">
                                                    <i class="fas fa-calendar-alt me-1"></i><span> </span> Penagihan Selanjutnya
                                                </div>
                                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                                    <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2" style="font-size: 0.7rem;">
                                                        {{ \Carbon\Carbon::parse($customer->userCustomer->start_billing_date)->format('d M Y') }}
                                                    </span>
                                                    <i class="fas fa-arrow-right text-muted" style="font-size: 0.65rem;"></i>
                                                    <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-2" style="font-size: 0.7rem;">
                                                        {{ \Carbon\Carbon::parse($customer->userCustomer->end_billing_date)->format('d M Y') }}
                                                    </span>
                                                </div>
                                            </div>
                                            @endif

                                        </div>
                                    </td>
                                    <td>
                                        {!! $customer->status_badge !!}
                                    </td>
                                    <td>
                                            @switch($customer->status)
                                             @case(\App\Schemas\ParamSchema::PENDING)
                                                <div class="d-flex gap-1 flex-column">
                                                    <button class="btn btn-sm btn-success mb-2" 
                                                            onclick="return confirm('Anda yakin ingin menyetujui pendaftaran pelanggan ini?') ? @this.call('approvePending', @js($customer->id)) : false">
                                                        <i class="fas fa-check me-1"></i> Approve
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Anda yakin ingin menutup/membatalkan pendaftaran pelanggan ini?') ? @this.call('closePending', @js($customer->id)) : false">
                                                        <i class="fas fa-times me-1"></i> Close
                                                    </button>
                                                </div>
                                                @break
                                                @case(\App\Schemas\ParamSchema::WAITING_PAYMENT_CONFIRMATION)
                                                    @if($customer->getOldestUnconfirmedPurchase() && ($customer->getOldestUnconfirmedPurchase()->payment_method === 'transfer' || $customer->getOldestUnconfirmedPurchase()->payment_method === 'manual_transfer'))
                                                        @if($customer->getOldestUnconfirmedPurchase()->payment_method && $finance_access)

                                                            @if($customer->getOldestUnconfirmedPurchase()->payment_proof)
                                                            <button class="btn btn-sm btn-outline-primary" wire:click="viewPaymentProof(@js($customer->getOldestUnconfirmedPurchase()->id))">
                                                                Lihat Bukti
                                                            </button>
                                                            @endif
                                                            <button class="btn btn-sm btn-success mt-1" onclick="confirmPayment('{{ $customer->getOldestUnconfirmedPurchase()->id }}')">
                                                                Konfirmasi
                                                            </button>
                                                        @else
                                                            <span class="text-muted">Proses Konfirmasi Pembayaran</span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-secondary">-</span>
                                                    @endif
                                                    @break
                                                    
                                                @case(\App\Schemas\ParamSchema::PROCESS_INSTALLATION)
                                                    @if($technical_access)
                                                    <button class="btn btn-sm btn-primary" wire:click="openInstallationModal( @js($customer->id) )">
                                                        <i class="fas fa-camera me-1"></i> Input Instalasi
                                                    </button>
                                                    @else
                                                        <span class="text-muted">Teknisi Internet</span>
                                                    @endif
                                                    @break
                                                @case(\App\Schemas\ParamSchema::CUSTOMER_EXISTING)
                                                    @if($technical_access)
                                                    <button class="btn btn-sm btn-primary" wire:click="openInstallationModal( @js($customer->id) )">
                                                        <i class="fas fa-camera me-1"></i> Input Instalasi
                                                    </button>
                                                    @else
                                                        <span class="text-muted">Teknisi Internet</span>
                                                    @endif
                                                    @break
                                                    
                                                @case(\App\Schemas\ParamSchema::INSTALLED)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i> Sudah Diinstalasi
                                                    </span>
                                                    @break
                                                @case(\App\Schemas\ParamSchema::ACTIVE)
                                                    @if($finance_access)
                                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Anda yakin ingin menon-aktifkan pelanggan ini?') ? @this.call('suspend', @js($customer->id)) : false">
                                                        <i class="fas fa-pause me-1"></i> Suspend
                                                    </button>
                                                    @else
                                                    <span class="text-muted">Finance</span>
                                                    @endif
                                                    @break

                                                @case(\App\Schemas\ParamSchema::SUSPENDED)
                                                    @if($finance_access)
                                                    <button class="btn btn-sm btn-outline-success" onclick="return confirm('Anda yakin ingin mengaktifkan kembali pelanggan ini?') ? @this.call('reactivate', @js($customer->id)) : false">
                                                        <i class="fas fa-play me-1"></i> Aktifkan
                                                    </button>
                                                    @else
                                                    <span class="text-muted">Finance</span>
                                                    @endif
                                                    @break
                                                {{-- BARU: Case untuk status CLOSED --}}
                                                @case(\App\Schemas\ParamSchema::CLOSED)
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-ban me-1"></i> Ditutup
                                                    </span>
                                                    @break
                                            @endswitch
                                        </td>
                                    <td>
                                        {{ $customer->created_at->format('d M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                                            <h5>Tidak ada data pelanggan ditemukan</h5>
                                            @if($search)
                                                <button class="btn btn-sm btn-outline-primary mt-2" 
                                                        wire:click="resetSearch">
                                                    Reset pencarian
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
        
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="d-flex align-items-center">
                        <label class="me-2">Items per page:</label>
                        <select wire:model="perPage" class="form-control form-control-sm" style="width: 80px;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    
                    <div>
                        {{ $internetCustomers->links() }}
                    </div>
                    
                    <div class="text-muted">
                        Menampilkan {{ $internetCustomers->firstItem() }} - {{ $internetCustomers->lastItem() }} 
                        dari {{ $internetCustomers->total() }} pelanggan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endcanAccess

<!-- Modal Instalasi -->
<div class="modal fade" id="installationModal" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Proses Instalasi</h5>
                <button type="button" class="btn-close btn-close-white btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6>Pelanggan: <strong id="modalCustomerName"></strong></h6>
                    <p>Kode: <span class="badge bg-info" id="modalCustomerCode"></span></p>
                </div>
                
                <form id="installationForm">
                    <div class="mb-3">
                        <label class="form-label">Serial Number Perangkat</label>
                        <input type="text" class="form-control" wire:model="serialNumber" id="modalSerialNumber" required>
                    </div>

                    {{-- ODP: wire:ignore so Livewire never wipes JS-injected options --}}
                    <div wire:ignore>
                        <div class="mb-3">
                            <label class="form-label">ODP (Optical Distribution Point) <span class="text-danger">*</span></label>
                            <select class="form-control" id="odpSelect">
                                <option value="">— Pilih ODP —</option>
                            </select>
                            <div class="form-text">Pilih ODP sesuai lokasi pemasangan pelanggan.</div>
                        </div>
                    </div>

                    {{-- Group: wire:ignore — JS-populated via groups-loaded event --}}
                    <div wire:ignore>
                        <div class="mb-3">
                            <label class="form-label">Group <span class="text-danger">*</span></label>
                            <select class="form-control" id="groupSelect">
                                <option value="">— Pilih ODP dulu —</option>
                            </select>
                            <div class="form-text" id="groupSelectHint">Group difilter otomatis dari ODP yang dipilih.</div>
                        </div>

                        {{-- Grouping ID preview --}}
                        <div class="mb-3" id="groupingPreviewBox" style="display:none;">
                            <label class="form-label">Grouping ID <small class="text-muted font-weight-normal">(bisa diubah jika perlu)</small></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                </div>
                                <input type="text" class="form-control font-weight-bold" id="groupingIdPreview" placeholder="e.g. HN110001">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="useAsUsernameBtn" title="Gunakan sebagai username">
                                        <i class="fas fa-arrow-right"></i> Pakai sbg Username
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Saran otomatis — bisa diubah jika nomor sudah terpakai.</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Router</label>
                        <select id="routerSelect" class="form-control">
                            <!-- Opsi akan diisi melalui JavaScript -->
                        </select>
                        <input type="hidden" id="routerSelectMirror" wire:model="router_id">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pilih IP Pool (opsional)</label>
                        <select class="form-control" wire:model="override_pool_id" 
                                wire:key="pool-select-{{ $router_id }}-{{ count($availablePools) }}" id="selectPool">
                            <option value="">— Ikuti mapping otomatis —</option>
                            @foreach($availablePools as $pool)
                                <option value="{{ $pool['id'] }}">{{ $pool['label'] }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            Kosongkan jika ingin pakai pool default/PPPoE server router.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Local Address</label>
                        <div class="input-group">
                            <input type="text" 
                                class="form-control" 
                                id="local_address"
                                placeholder="192.168.1.1">
                            <span class="input-group-text">
                                <div wire:loading wire:target="local_address">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </div>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" 
                                class="form-control" 
                                id="modalUsername"
                                placeholder="username_pppoe">
                            <span class="input-group-text">
                                <div wire:loading wire:target="username">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </div>
                                <div wire:loading.remove wire:target="username">
                                    {{-- Icon akan diisi oleh JavaScript --}}
                                </div>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" wire:model="password" id="modalPassword" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Foto Instalasi (Minimal 1 foto)</label>
                        <input type="file" class="form-control" wire:model="photos" id="modalPhotos" multiple accept="image/*">
                        <div id="photoPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan Instalasi</label>
                        <textarea class="form-control"  wire:model="installationNotes" id="modalNotes" rows="3"></textarea>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="submitInstallation">
                            <i class="fas fa-check-circle me-1"></i> Selesaikan Instalasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Lihat Bukti Bayar -->
<div class="modal fade" id="paymentProofModal" tabindex="-1" aria-hidden="true" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bukti Pembayaran Pelanggan</h5>
                <button type="button" class="btn-close btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body" id="showPaymentProof">
            </div>
        </div>
    </div>
</div>

<!-- Modal for full address -->
<div class="modal fade" id="addressModal" tabindex="-1" wire:ignore.self>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alamat Lengkap</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($selectedCustomer)
                    <p>{{ $selectedCustomer->address }}</p>
                    <p class="text-muted mt-2">
                        {{ $selectedCustomer->subdistrict->name }}, 
                        {{ $selectedCustomer->district->name }}, 
                        {{ $selectedCustomer->city->name }}, 
                        {{ $selectedCustomer->province->name }}
                    </p>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

    function copyShareLink() {
        const link = document.getElementById('share-link').href;
        navigator.clipboard.writeText(link).then(() => {
            alert('Link berhasil disalin!');
        }).catch(err => {
            console.error('Gagal menyalin link:', err);
        });
    }

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
        const modal = new bootstrap.Modal(document.getElementById('paymentProofModal'));
        let content = `<img src="${data.detail.proofUrl}" class="img-fluid">`;
        
        // Add transfer details if available
        if (data.detail.transferDetails) {
            const details = data.detail.transferDetails;
            content += `
                <div class="mt-3 text-left">
                    <table class="table table-sm table-bordered">
                        <tbody>
                            ${details.date ? `<tr><th width="40%">Tanggal Transfer</th><td>${details.date}</td></tr>` : ''}
                            ${details.bank ? `<tr><th>Bank Pengirim</th><td>${details.bank}</td></tr>` : ''}
                            ${details.account_name ? `<tr><th>Nama Pengirim</th><td>${details.account_name}</td></tr>` : ''}
                            ${details.notes ? `<tr><th>Catatan</th><td>${details.notes}</td></tr>` : ''}
                        </tbody>
                    </table>
                </div>
            `;
        }
        document.getElementById('showPaymentProof').innerHTML = content;
        modal.show();
    });

    window.addEventListener('showSuccessAlert', function(event) {
        Swal.fire({
            icon: 'success',
            title: event.detail.title,
            text: event.detail.message,
            showConfirmButton: false,
            timerProgressBar: true,
            timer: 3000,
        });
    });

    window.addEventListener('showErrorAlert', function(event) {
        Swal.fire({
            icon: 'error',
            title: event.detail.title,
            text: event.detail.message,
            showConfirmButton: false,
            timerProgressBar: true,
            timer: 3000,
        });
    });
    
    document.addEventListener('livewire:load', function() {
        const installationModal = new bootstrap.Modal(document.getElementById('installationModal'));
        let uploadedFiles = [];

        // Listen untuk update status username check
        window.addEventListener('usernameCheckComplete', function(event) {
            const data = event.detail;
            const inputUsername = document.getElementById('modalUsername');
            const iconContainer = inputUsername.closest('.input-group').querySelector('.input-group-text div:not([wire\\:loading])');
            
            if (data.available) {
                inputUsername.classList.remove('is-invalid');
                inputUsername.classList.add('is-valid');
                if (iconContainer) {
                    iconContainer.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
                }
            } else {
                inputUsername.classList.remove('is-valid');
                inputUsername.classList.add('is-invalid');
                if (iconContainer) {
                    iconContainer.innerHTML = '<i class="fas fa-times-circle text-danger"></i>';
                }
                
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

        // Listen untuk update status local address check
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

        // Clear icon saat user mulai mengetik
        document.getElementById('modalUsername')?.addEventListener('input', function(e) {
            const iconContainer = this.closest('.input-group').querySelector('.input-group-text div:not([wire\\:loading])');
            if (iconContainer) {
                iconContainer.innerHTML = '';
            }
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

        // Populate group select when Livewire sends groups after ODP change
        window.addEventListener('groups-loaded', function (e) {
            const groupSelect = document.getElementById('groupSelect');
            const hint        = document.getElementById('groupSelectHint');
            if (!groupSelect) return;

            const groups = e.detail.groups || [];
            groupSelect.innerHTML = '';

            // Hide preview when group list reloads
            const previewBox = document.getElementById('groupingPreviewBox');
            if (previewBox) previewBox.style.display = 'none';

            if (groups.length === 0) {
                const opt    = document.createElement('option');
                opt.value    = '';
                opt.textContent = '— Tidak ada group untuk ODP ini —';
                groupSelect.appendChild(opt);
                if (hint) hint.classList.add('text-warning');
            } else {
                const placeholder    = document.createElement('option');
                placeholder.value    = '';
                placeholder.textContent = '— Pilih Group —';
                groupSelect.appendChild(placeholder);

                groups.forEach(function (g) {
                    const opt    = document.createElement('option');
                    opt.value    = g.id;
                    opt.textContent = g.description ? g.name + ' — ' + g.description : g.name;
                    groupSelect.appendChild(opt);
                });

                if (hint) hint.classList.remove('text-warning');
            }

            // When group changes: request grouping_id preview from Livewire
            groupSelect.onchange = function () {
                const val = groupSelect.value || null;
                const previewBox = document.getElementById('groupingPreviewBox');
                if (!val) {
                    if (previewBox) previewBox.style.display = 'none';
                    return;
                }
                @this.call('previewGroupingId', val);
            };
        });

        // ── Grouping ID duplicate check ──────────────────────────────────────
        function resetGroupingIdState() {
            const input = document.getElementById('groupingIdPreview');
            if (!input) return;
            input.classList.remove('is-valid', 'is-invalid');
            const err = input.closest('.input-group')?.parentElement?.querySelector('.grouping-id-error-msg');
            if (err) err.remove();
            window._groupingIdAvailable = true;
        }

        window.addEventListener('groupingIdCheckComplete', function(event) {
            const data  = event.detail;
            const input = document.getElementById('groupingIdPreview');
            if (!input) return;

            const wrap = input.closest('.input-group')?.parentElement;
            let errorDiv = wrap?.querySelector('.grouping-id-error-msg');

            if (data.available) {
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                if (errorDiv) errorDiv.remove();
                window._groupingIdAvailable = true;
            } else {
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback d-block grouping-id-error-msg';
                    wrap.appendChild(errorDiv);
                }
                errorDiv.innerHTML = `Grouping ID sudah digunakan oleh: <strong>${data.existing.code} - ${data.existing.name}</strong>`;
                window._groupingIdAvailable = false;
            }
        });

        // Debounced manual-edit check on groupingIdPreview
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

        // Show grouping_id preview returned by Livewire
        window.addEventListener('grouping-id-preview', function (e) {
            const preview    = e.detail.preview;
            const previewBox = document.getElementById('groupingPreviewBox');
            const previewInput = document.getElementById('groupingIdPreview');
            if (!previewBox || !previewInput) return;

            if (!preview) {
                previewBox.style.display = 'none';
                previewInput.value = '';
                resetGroupingIdState();
                return;
            }

            previewInput.value       = preview;
            previewBox.style.display = 'block';
            // Auto-check the server-generated ID too
            @this.call('checkGroupingIdAvailability', preview);

            // Auto-suggest as username if username field is empty
            const usernameInput = document.getElementById('modalUsername');
            if (usernameInput && !usernameInput.value) {
                usernameInput.value = preview;
                @this.set('username', preview);
            }
        });

        // "Pakai sbg Username" button
        document.addEventListener('click', function (e) {
            if (e.target.closest('#useAsUsernameBtn')) {
                const preview     = document.getElementById('groupingIdPreview')?.value;
                const usernameInput = document.getElementById('modalUsername');
                if (preview && usernameInput) {
                    usernameInput.value = preview;
                    @this.set('username', preview);
                }
            }
        });

        // Listen pools-options event
        window.addEventListener('pools-options', (e) => {
            const select = document.querySelector('select[wire\\:model="override_pool_id"]');
            if (!select) return;

            select.querySelectorAll('option:not(:first-child)').forEach(o => o.remove());
            const options = e.detail.options || [];
            options.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.label;
                select.appendChild(opt);
            });
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
        
        // ✅ FIX: Event listener untuk populate modal dengan data ODP
        window.addEventListener('open-installation-modal', (e) => {
            const { customerName, customerCode, serialNumber, routers, odps } = e.detail;
            
            console.log('🔍 Modal opened with data:', e.detail); // Debug log
            console.log('📦 ODPs received:', odps); // Debug log
            
            // Set customer info
            document.getElementById('modalCustomerName').textContent = customerName;
            document.getElementById('modalCustomerCode').textContent = customerCode;
            document.getElementById('modalSerialNumber').value = serialNumber;

            // ✅ POPULATE ODP DROPDOWN
            const odpSelect = document.getElementById('odpSelect');
            if (odpSelect) {
                console.log('🎯 Populating ODP dropdown...'); // Debug log
                
                // Clear existing options except first (placeholder)
                odpSelect.innerHTML = '<option value="">— Pilih ODP —</option>';
                
                // Add ODP options
                if (odps && odps.length > 0) {
                    odps.forEach(odp => {
                        const option = document.createElement('option');
                        option.value = odp.id;
                        option.textContent = odp.label;
                        odpSelect.appendChild(option);
                        console.log('✅ Added ODP option:', odp.label); // Debug log
                    });
                    console.log(`✅ Total ${odps.length} ODPs added to dropdown`); // Debug log
                } else {
                    console.warn('⚠️ No ODPs available!'); // Debug log
                }
                
                // Reset ODP selection (DOM only — Livewire reset handled below)
                odpSelect.value = '';
            } else {
                console.error('❌ ODP select element not found!'); // Debug log
            }

            // Reset group select + preview
            const groupSelect = document.getElementById('groupSelect');
            if (groupSelect) {
                groupSelect.innerHTML = '<option value="">— Pilih ODP dulu —</option>';
            }
            const previewBox = document.getElementById('groupingPreviewBox');
            if (previewBox) previewBox.style.display = 'none';
            const previewInput = document.getElementById('groupingIdPreview');
            if (previewInput) previewInput.value = '';
            resetGroupingIdState();

            // Reset Livewire ODP + group state
            @this.set('optical_distribution_id', null);

            // ODP onchange: notify Livewire → updatedOpticalDistributionId → groups-loaded event
            odpSelect.onchange = function () {
                const val = odpSelect.value || null;
                @this.set('optical_distribution_id', val);
            };

            // Populate Router dropdown
            const routerSelect = document.getElementById('routerSelect');
            routerSelect.innerHTML = '';
            
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Pilih Router';
            routerSelect.appendChild(defaultOption);
            
            routers.forEach(router => {
                const option = document.createElement('option');
                option.value = router.id;
                option.disabled = router.disabled;
                option.textContent = router.name;
                routerSelect.appendChild(option);
            });

            // Reset router and pools
            routerSelect.value = '';
            document.getElementById('routerSelectMirror').value = '';
            @this.set('router_id', '');
            @this.set('override_pool_id', '');

            // Reset username & local_address
            document.getElementById('modalUsername').value = '';
            document.getElementById('local_address').value = '';
            @this.set('username', '');
            @this.set('local_address', '');
            @this.set('newUsernameChecked', false);
            @this.set('newUsernameAvailable', false);
            
            // Router change listener
            routerSelect.onchange = function (e) {
                const val = e.target.value || '';
                document.getElementById('routerSelectMirror').value = val;
                @this.set('router_id', val);
                @this.set('override_pool_id', '');
                @this.call('loadPoolsForRouter', val);
            };
            
            // Reset form lainnya
            document.getElementById('modalPassword').value = '';
            document.getElementById('modalNotes').value = '';
            document.getElementById('photoPreview').innerHTML = '';
            document.getElementById('modalPhotos').value = '';
            uploadedFiles = [];
            
            // Show modal
            installationModal.show();
        });

        // Preview foto
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
                    imgContainer.style.width = '100px';
                    imgContainer.style.height = '100px';
                    
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.className = 'img-thumbnail w-100 h-100';
                    
                    imgContainer.appendChild(img);
                    preview.appendChild(imgContainer);
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle submit
        document.getElementById('submitInstallation').addEventListener('click', async function() {
            const serialNumber = document.getElementById('modalSerialNumber').value;
            const notes = document.getElementById('modalNotes').value;
            const files = document.getElementById('modalPhotos').files;
            const routerId = document.getElementById('routerSelectMirror').value;
            const odpId   = document.getElementById('odpSelect').value;
            const groupId = document.getElementById('groupSelect').value;
            const manualGroupingId = (document.getElementById('groupingIdPreview')?.value || '').trim();
            const username = @this.username;
            const password = document.getElementById('modalPassword').value;
            const override_pool_id = @this.override_pool_id;
            const local_address = @this.local_address;

            console.log('📝 Form data:', {
                serialNumber,
                routerId,
                odpId,
                groupId,
                username,
                filesCount: files.length
            });

            // Validasi ODP
            if (!odpId) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'ODP harus dipilih' });
                return;
            }

            // Validasi Group
            if (!groupId) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Group harus dipilih' });
                return;
            }

            if (!serialNumber) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Serial number harus diisi'
                });
                return;
            }
            
            if (files.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Minimal upload 1 foto instalasi'
                });
                return;
            }

            if (!routerId || routerId === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Router harus dipilih'
                });
                return;
            }
            
            if (!username) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Username harus diisi'
                });
                return;
            }
            
            if (!password) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'Password harus diisi'
                });
                return;
            }

            if (manualGroupingId && window._groupingIdAvailable === false) {
                Swal.fire({
                    icon: 'error',
                    title: 'Grouping ID Duplikat',
                    text: 'Grouping ID sudah digunakan pelanggan lain. Silakan ganti terlebih dahulu.'
                });
                return;
            }

            // Konfirmasi
            const result = await Swal.fire({
                icon: 'question',
                title: 'Konfirmasi',
                text: 'Anda yakin ingin menyelesaikan instalasi ini?',
                showCancelButton: true,
                confirmButtonText: 'Ya, Selesaikan',
                cancelButtonText: 'Batal'
            });
            
            if (!result.isConfirmed) {
                return;
            }
                
            // Disable button dan tampilkan progress
            const submitBtn = document.getElementById('submitInstallation');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            
            try {
                // Upload files
                const uploadPromises = [];
                
                for (let i = 0; i < files.length; i++) {
                    submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Uploading ${i + 1}/${files.length}...`;
                    
                    const uploadPromise = new Promise((resolve, reject) => {
                        @this.upload(`photos.${i}`, files[i], 
                            (uploadedName) => {
                                console.log(`File ${i} uploaded successfully:`, uploadedName);
                                resolve(uploadedName);
                            },
                            (error) => {
                                console.error(`File ${i} upload failed:`, error);
                                reject(error);
                            },
                            (event) => {
                                console.log(`File ${i} progress:`, event.detail.progress);
                            }
                        );
                    });
                    
                    uploadPromises.push(uploadPromise);
                }
                
                await Promise.all(uploadPromises);
                console.log('All files uploaded, photos property:', @this.photos);
                
                // Submit installation
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan data...';
                await new Promise(resolve => setTimeout(resolve, 500));
                
                const success = await @this.call('completeInstallation',
                    serialNumber,
                    notes,
                    routerId,
                    username,
                    password,
                    override_pool_id,
                    local_address,
                    odpId,
                    groupId,
                    manualGroupingId
                );
                
                console.log('completeInstallation result:', success);
                
                if (success !== false) {
                    installationModal.hide();
                    
                    // Reset form
                    document.getElementById('modalSerialNumber').value = '';
                    document.getElementById('modalNotes').value = '';
                    document.getElementById('modalPhotos').value = '';
                    document.getElementById('photoPreview').innerHTML = '';
                    document.getElementById('modalPassword').value = '';
                }
                
            } catch (error) {
                console.error('Installation error:', error);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal menyimpan instalasi: ' + (error.message || error)
                });
                
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });

        // Notifikasi
        window.addEventListener('show-notification', (event) => {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });

            Toast.fire({
                icon: event.detail.type,
                title: event.detail.message
            });
        });

        // ── IMPORT PELANGGAN ─────────────────────────────────────────────────
        let importProgressInterval = null;

        window.addEventListener('import-started', event => {
            Swal.fire({
                icon: 'info',
                title: 'Import Dimulai',
                text: `Total ${event.detail.total_rows} data akan diimport`,
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
            });
        });

        window.addEventListener('start-progress-check', event => {
            if (importProgressInterval) {
                clearInterval(importProgressInterval);
            }
            importProgressInterval = setInterval(() => {
                @this.call('checkImportProgress');
            }, 1000);
        });

        window.addEventListener('import-completed', event => {
            if (importProgressInterval) {
                clearInterval(importProgressInterval);
                importProgressInterval = null;
            }

            const progress = event.detail.progress;

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
                            <p class="text-warning mb-2"><strong>⚠️ Detail Baris Gagal:</strong></p>
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
                width: '600px',
            }).then(() => {
                @this.call('resetImport');
            });
        });

        window.addEventListener('beforeunload', () => {
            if (importProgressInterval) {
                clearInterval(importProgressInterval);
            }
        });
        // ── END IMPORT PELANGGAN ─────────────────────────────────────────────

    });
</script>
@endpush