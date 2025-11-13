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
                            <option value="waiting_payment_confirmation">Menunggu Pembayaran</option>
                            <option value="waiting_payment_subscription">Menunggu Pembayaran Subscription</option>
                            <option value="process_installation">Proses Instalasi</option>
                            <option value="installed">Terpasang</option>
                            <option value="reactivated">Reaktivasi</option>
                            <option value="active">Aktif</option>
                            <option value="suspended">Dihentikan</option>
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
                                <th wire:click="sortBy('code')" style="cursor: pointer; width: 10%;">
                                    Kode
                                    @if($sortField === 'code')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted"></i>
                                    @endif
                                </th>
                                <th wire:click="sortBy('name')" style="cursor: pointer; width: 20%;">
                                    Nama
                                    @if($sortField === 'name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                    @else
                                        <i class="fas fa-sort ms-1 text-muted"></i>
                                    @endif
                                </th>
                                <th style="width: 25%;">Alamat</th>
                                <th style="width: 15%;">Paket Internet</th>
                                <th style="width: 15%;">Status</th>
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
                                    </td>
                                    <td>
                                        <a href="{{ route('internet-customer.show', $customer->id) }}">
                                            {{ $customer->name }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ Str::limit($customer->address, 50) }}
                                        @if(strlen($customer->address) > 50)
                                            <a href="#" wire:click.prevent="showFullAddress({{ $customer->id }})" 
                                                class="text-primary">selengkapnya</a>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $customer->status  }}">
                                            {{ $customer->internetPackage->name }}
                                        </span>
                                    </td>
                                    <td>
                                        {!! $customer->status_badge !!}
                                    </td>
                                    <td>
                                            @switch($customer->status)
                                                @case(\App\Schemas\ParamSchema::WAITING_PAYMENT_CONFIRMATION)
                                                    @if($customer->getOldestUnconfirmedPurchase() && ($customer->getOldestUnconfirmedPurchase()->payment_method === 'transfer' || $customer->getOldestUnconfirmedPurchase()->payment_method === 'manual_transfer'))
                                                        @if($customer->getOldestUnconfirmedPurchase()->payment_method && $finance_access)

                                                            @if($customer->getOldestUnconfirmedPurchase()->payment_proof)
                                                            <button class="btn btn-sm btn-outline-primary" wire:click="viewPaymentProof(@js($customer->getOldestUnconfirmedPurchase()->payment_proof))">
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
                                            @endswitch
                                        </td>
                                    <td>
                                        {{ $customer->created_at->format('d M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
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
<div class="modal fade" id="installationModal" tabindex="-1">
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
                        <input type="text" class="form-control" wire:model="local_address" id="local_address">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" wire:model="username" id="modalUsername" required>
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

     function confirmPayment(customerId) 
     {
        Swal.fire({
            title: 'Konfirmasi Pembayaran?',
            text: "Pastikan bukti pembayaran sudah valid.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, konfirmasi',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Panggil ke Livewire
                @this.call('confirmPayment', customerId);
            }
        });
    }
    // Cara pertama: Gunakan window.addEventListener untuk broadcase event
    window.addEventListener('showPaymentProofModal', function(url) {
        const modal = new bootstrap.Modal(document.getElementById('paymentProofModal'));
        let img = `<img src="${url.detail.proofUrl}" class="img-fluid">`;
        document.getElementById('showPaymentProof').innerHTML = img;

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
            icon: 'success',
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

        // Handle buka modal
        // Di JavaScript - Tambahkan kode untuk mengisi select
        window.addEventListener('pools-options', (e) => {
            const select = document.querySelector('select[wire\\:model="override_pool_id"]');
            if (!select) return;

            // Hapus semua option kecuali yang pertama (placeholder)
            select.querySelectorAll('option:not(:first-child)').forEach(o => o.remove());

            const options = e.detail.options || [];
            options.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.label;
                select.appendChild(opt);
            });

            // trigger change untuk sync ke Livewire kalau perlu
            select.dispatchEvent(new Event('change', { bubbles: true }));
        });
        
        window.addEventListener('open-installation-modal', (e) => {
            const { customerName, customerCode, serialNumber, routers } = e.detail;
            
            // Set nilai ke modal
            document.getElementById('modalCustomerName').textContent = customerName;
            document.getElementById('modalCustomerCode').textContent = customerCode;
            document.getElementById('modalSerialNumber').value = serialNumber;

            // Isi select router
            const routerSelect = document.getElementById('routerSelect');
            routerSelect.innerHTML = ''; // Kosongkan dulu
            
            // Tambahkan opsi default
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Pilih Router';
            routerSelect.appendChild(defaultOption);
            
            // Isi dengan router yang tersedia
            routers.forEach(router => {
                // console.log(router);
                const option = document.createElement('option');
                option.value = router.id;
                option.disabled = router.disabled;
                option.textContent = router.name;
                routerSelect.appendChild(option);
            });

            // Reset router select and mirror, and Livewire property
            routerSelect.value = '';
            document.getElementById('routerSelectMirror').value = '';
            @this.set('router_id', '');
            @this.set('override_pool_id', '');

            // Assign change listener to update Livewire and hidden input, and reset pool
            routerSelect.onchange = function (e) {
                const val = e.target.value || '';
                document.getElementById('routerSelectMirror').value = val; // keep mirror in sync
                @this.set('router_id', val);
                @this.set('override_pool_id', ''); // reset pool when router changes
                @this.call('loadPoolsForRouter', val);
            };
            
            // Reset form lainnya
            document.getElementById('modalNotes').value = '';
            document.getElementById('photoPreview').innerHTML = '';
            uploadedFiles = [];
            
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
            const username = document.getElementById('modalUsername').value;
            const password = document.getElementById('modalPassword').value;
            const override_pool_id = document.getElementById('selectPool').value;
            const local_address = document.getElementById('local_address').value;
        
            // Validasi
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

            if (routerId === '') {
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
            
                
                // Validasi
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
                    // STEP 1: Upload semua files ke Livewire property DULU
                    const uploadPromises = [];
                    
                    for (let i = 0; i < files.length; i++) {
                        submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Uploading ${i + 1}/${files.length}...`;
                        
                        // Buat promise untuk setiap upload dan tunggu sampai selesai
                        const uploadPromise = new Promise((resolve, reject) => {
                            @this.upload(`photos.${i}`, files[i], 
                                // onFinish callback
                                (uploadedName) => {
                                    console.log(`File ${i} uploaded successfully:`, uploadedName);
                                    resolve(uploadedName);
                                },
                                // onError callback  
                                (error) => {
                                    console.error(`File ${i} upload failed:`, error);
                                    reject(error);
                                },
                                // onProgress callback
                                (event) => {
                                    console.log(`File ${i} progress:`, event.detail.progress);
                                }
                            );
                        });
                        
                        uploadPromises.push(uploadPromise);
                    }
                    
                    // Tunggu SEMUA upload selesai
                    await Promise.all(uploadPromises);
                    
                    console.log('All files uploaded, photos property:', @this.photos);
                    
                    // STEP 2: Setelah SEMUA file terupload, baru panggil completeInstallation
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan data...';
                    
                    // Tunggu sebentar untuk memastikan Livewire property sudah ter-update
                    await new Promise(resolve => setTimeout(resolve, 500));
                    
                    const success = await @this.call('completeInstallation',
                        serialNumber,
                        notes,
                        routerId,
                        username,
                        password,
                        override_pool_id,
                        local_address
                    );
                    
                    console.log('completeInstallation result:', success);
                    
                    if (success !== false) {
                        // Tutup modal
                        installationModal.hide();
                        
                        // Reset form
                        document.getElementById('modalSerialNumber').value = '';
                        document.getElementById('modalNotes').value = '';
                        document.getElementById('modalPhotos').value = '';
                        document.getElementById('photoPreview').innerHTML = '';
                    }
                    
                } catch (error) {
                    console.error('Installation error:', error);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal menyimpan instalasi: ' + (error.message || error)
                    });
                    
                } finally {
                    // Restore button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
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
</script>
@endpush