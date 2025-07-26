@canAccess('index', 'internet_customers')
<div class="row">
    <div class="col-md-12">
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
                            <option value="process_installation">Proses Instalasi</option>
                            <option value="installed">Terpasang</option>
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
                                        <span class="badge bg-success">
                                            {{ $customer->internetPackage->name }}
                                        </span>
                                    </td>
                                    <td>
                                        {!! $customer->status_badge !!}
                                    </td>
                                    <td>
                                            @switch($customer->status)
                                                @case(\App\Schemas\ParamSchema::WAITING_PAYMENT_CONFIRMATION)
                                                    @if($customer->purchase && $customer->purchase->payment_method === 'transfer')
                                                        @if($customer->purchase->payment_method && $finance_access)
                                                            <button class="btn btn-sm btn-outline-primary" wire:click="viewPaymentProof(@js($customer->purchase->payment_proof))">
                                                                Lihat Bukti
                                                            </button>
                                                            <button class="btn btn-sm btn-success mt-1" onclick="confirmPayment('{{ $customer->id }}')">
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
                                                        <span class="text-muted">Proses Instalasi</span>
                                                    @endif
                                                    @break
                                                    
                                                @case(\App\Schemas\ParamSchema::INSTALLED)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i> Sudah Diinstalasi
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
                        {{ $internetCustomers->links('pagination::bootstrap-5') }}
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
    

    document.addEventListener('livewire:load', function() {
        const installationModal = new bootstrap.Modal(document.getElementById('installationModal'));
        let uploadedFiles = [];

        // Handle buka modal
        window.addEventListener('open-installation-modal', (e) => {
            const { customerName, customerCode, serialNumber } = e.detail;
            
            // Set nilai ke modal
            document.getElementById('modalCustomerName').textContent = customerName;
            document.getElementById('modalCustomerCode').textContent = customerCode;
            document.getElementById('modalSerialNumber').value = serialNumber;
            
            // Reset form
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
        
        // Validasi
        if (!serialNumber) {
            alert('Serial number harus diisi');
            return;
        }
        
        if (files.length === 0) {
            alert('Minimal upload 1 foto instalasi');
            return;
        }
        
        // Konfirmasi
        if (!confirm('Anda yakin ingin menyelesaikan instalasi ini?')) {
            return;
        }
        
        // Buat FormData untuk upload file
        const formData = new FormData();
        formData.append('serialNumber', serialNumber);
        formData.append('notes', notes);
        
        // Tambahkan semua file ke FormData
        for (let i = 0; i < files.length; i++) {
            formData.append(`photos[${i}]`, files[i]);
        }
        
        // Kirim ke Livewire dengan FormData
        try {
            await @this.uploadMultiple('photos', files, (uploadedFilename) => {
                return @this.call('completeInstallation', 
                    serialNumber,
                    uploadedFilename,
                    notes
                );
            });
            
            installationModal.hide();
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
        } catch (error) {
            // console.log(error);
            
            // Livewire.dispatch('show-notification', {
            //     type: 'error',
            //     message: 'Gagal mengupload foto: ' + error.message
            // });
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