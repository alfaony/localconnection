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
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
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
                        <label class="form-label">Dari Tanggal</label>
                        <input wire:model="dateFrom" type="date" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sampai Tanggal</label>
                        <input wire:model="dateTo" type="date" class="form-control">
                    </div>
                    <div class="col-md-3">
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
                                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Anda yakin ingin menon-aktifkan pelanggan ini?') ? @this.call('suspend', @js($customer->id)) : false">
                                                        <i class="fas fa-pause me-1"></i> Suspend
                                                    </button>
                                                    @break

                                                @case(\App\Schemas\ParamSchema::SUSPENDED)
                                                    <button class="btn btn-sm btn-outline-success" onclick="return confirm('Anda yakin ingin mengaktifkan kembali pelanggan ini?') ? @this.call('reactivate', @js($customer->id)) : false">
                                                        <i class="fas fa-play me-1"></i> Aktifkan
                                                    </button>
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

                    {{-- BARU: Field ODP --}}
                    <div class="mb-3">
                        <label class="form-label">ODP (Optical Distribution Point) <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model="optical_distribution_id" id="odpSelect">
                            <option value="">— Pilih ODP —</option>
                            @foreach($availableOdps as $odp)
                                <option value="{{ $odp['id'] }}">{{ $odp['label'] }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            Pilih ODP sesuai lokasi pemasangan pelanggan.
                        </div>
                    </div>

                    {{-- BARU: Field Grouping --}}
                    <div class="mb-3">
                        <label class="form-label">Grouping/Cluster</label>
                        <input type="text" class="form-control" wire:model="grouping_id" id="groupingInput" 
                               placeholder="Contoh: Cluster A, Zona 1, RT 05, dll">
                        <div class="form-text">
                            Isi dengan nama grouping/cluster/RT lokasi pelanggan (opsional).
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
                
                // Reset ODP selection
                odpSelect.value = '';
                @this.set('optical_distribution_id', '');
            } else {
                console.error('❌ ODP select element not found!'); // Debug log
            }

            // ✅ RESET GROUPING INPUT
            const groupingInput = document.getElementById('groupingInput');
            if (groupingInput) {
                groupingInput.value = '';
                @this.set('grouping_id', '');
            }

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
            const odpId = document.getElementById('odpSelect').value;
            const grouping = document.getElementById('groupingInput').value;
            // const odpId = @this.optical_distribution_id;
            const username = @this.username;
            const password = document.getElementById('modalPassword').value;
            const override_pool_id = @this.override_pool_id;
            const local_address = @this.local_address;

            console.log('📝 Form data:', { // Debug log
                serialNumber,
                routerId,
                odpId,
                grouping,
                username,
                filesCount: files.length
            });

            // ✅ VALIDASI ODP
            if (!odpId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian',
                    text: 'ODP harus dipilih'
                });
                return;
            }

            // Validasi lainnya
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
                    grouping
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
    });
</script>
@endpush