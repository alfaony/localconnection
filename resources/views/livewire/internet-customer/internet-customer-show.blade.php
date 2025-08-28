@section('title', $customer->company->name)

<div class="row mb-4">
    @include('components.alert')
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="fas fa-user mr-2"></i>Detail Pelanggan Internet
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-id-card"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Kode Pelanggan</span>
                                <span class="info-box-number">{{ $customer->code }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-light">
                            <span class="info-box-icon bg-success">
                                <i class="fas fa-signal"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">Status</span>
                                <span class="info-box-number">
                                    {!! $customer->status_badge !!}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-user-circle mr-2"></i>Data Pribadi
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th width="25%">Nama Lengkap</th>
                                        <td>{{ $customer->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $customer->userCustomer->email ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Nomor Telepon</th>
                                        <td>{{ $customer->userCustomer->phone_number ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Nomor KTP</th>
                                        <td>
                                            {{ $customer->ktp_number }}
                                            @if($ktpPhotoUrl)
                                                <button wire:click="viewKtpPhoto" class="btn btn-sm btn-info ml-2">
                                                    <i class="fas fa-eye mr-1"></i>Lihat KTP
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Alamat Lengkap</th>
                                        <td>{{ $customer->address }}</td>
                                    </tr>
                                    <tr>
                                        <th>Lokasi</th>
                                        <td>
                                            {{ $customer->subdistrict->name ?? '-' }}, 
                                            {{ $customer->district->name ?? '-' }}, 
                                            {{ $customer->city->name ?? '-' }}, 
                                            {{ $customer->province->name ?? '-' }}
                                        </td>
                                    </tr>
                                     <tr>
                                        <th>Tanggal Pembayaran Selanjutnya</th>
                                        <td>
                                            @if($customer->userCustomer->start_billing_date)
                                                <span class="badge badge-success">{{ \Carbon\Carbon::parse($customer->userCustomer->start_billing_date)->format('d M Y') }}</span>
                                            @else
                                                <span class="badge badge-secondary">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Batas Pembayaran Selanjutnya</th>
                                        <td>
                                            @if($customer->userCustomer->end_billing_date)
                                                <span class="badge badge-warning">{{ \Carbon\Carbon::parse($customer->userCustomer->end_billing_date)->format('d M Y') }}</span>
                                            @else
                                                <span class="badge badge-secondary">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-wifi mr-2"></i>Paket Internet
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th width="25%">Nama Paket</th>
                                        <td>{{ $customer->internetPackage->name ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Deskripsi Paket</th>
                                        <td>{{ $customer->internetPackage->description ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Harga</th>
                                        <td>Rp {{ number_format($customer->internetPackage->price_nett, 0, ',', '.') }}</td>
                                    </tr>
                                    @if($customer->promo)
                                    <tr>
                                        <th>Promo</th>
                                        <td>{{ $customer->promo ? $customer->promo->name : '-' }}</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if($customer->installation)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-cogs mr-2"></i>Data Instalasi
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th width="25%">Tanggal Instalasi</th>
                                        <td>{{ \Carbon\Carbon::parse($customer->installation->installed_at)->format('d F Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Serial Number Perangkat</th>
                                        <td>{{ $customer->installation->device_serial_number }}</td>
                                    </tr>
                                    <tr>
                                        <th>Catatan Instalasi</th>
                                        <td>{{ $customer->installation->notes ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Foto Instalasi</th>
                                        <td>
                                            @if(!empty($installationPhotos))
                                                <button wire:click="viewInstallationPhotos" class="btn btn-sm btn-info">
                                                    <i class="fas fa-images mr-1"></i>Lihat Foto Instalasi
                                                </button>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                @if($purchases->count() > 0)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card shadow">
                            <div class="card-header bg-light">
                                <h4 class="text-primary mb-3">
                                    <i class="fas fa-credit-card mr-2"></i>Riwayat Pembayaran
                                </h4>
                            </div>
                            <div classa="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead> {{-- Tambahkan header untuk tabel --}}
                                            <tr>
                                                <th>Periode</th>
                                                <th>Metode</th>
                                                <th>Status</th>
                                                <th>Jumlah Bayar</th>
                                                <th>Pembayaran</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($purchases as $purchase)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($purchase->created_at)->format('F Y') }}</td>
                                                <td>{{ ucfirst($purchase->payment_method ?? '-') }}</td>
                                                <td>
                                                    @if($purchase->user_finance_id && $purchase->confirmation_finance_at)
                                                        <span class="badge badge-success">Lunas</span>
                                                    @else
                                                        <span class="badge badge-danger">Belum Lunas</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    Rp {{ number_format($purchase->amount_paid, 0, ',', '.') }}
                                                </td>
                                                <td>
                                                    @switch($customer->status)
                                                        @case(\App\Schemas\ParamSchema::WAITING_PAYMENT_CONFIRMATION)
                                                            @if($purchase->user_finance_id && $purchase->confirmation_finance_at)
                                                            <button wire:click="viewPaymentProof('{{ $purchase->id }}')" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye mr-1"></i>Lihat
                                                            </button>
                                                            @else
                                                                <span class="badge badge-warning">Menunggu Konfirmasi</span>
                                                            @endif
                                                            @break
                                                        @case(\App\Schemas\ParamSchema::WAITING_PAYMENT_SUBSCRIPTION || \App\Schemas\ParamSchema::SUSPENDED)
                                                            @if($purchase->user_finance_id && $purchase->confirmation_finance_at)
                                                            <button wire:click="viewPaymentProof('{{ $purchase->id }}')" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye mr-1"></i>Lihat
                                                            </button>
                                                            @else
                                                                <button class="btn btn-sm btn-success mt-1" wire:click="showPaymentModal({{ $purchase->id }})">
                                                                    Konfirmasi
                                                                </button>
                                                            @endif
                                                            @break
                                                    @endswitch
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer">
                                    <div class="float-right">
                                        {{ $purchases->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($agreementFields)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-file-contract mr-2"></i>Perjanjian Kerjasama
                        </h4>
                        <div class="card border-primary">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Detail Perjanjian</h5>
                            </div>
                            <div class="card-body">
                                @php
                                    $agreement = $customer->partnershipAgreement;
                                @endphp
                                @if(view()->exists('partnership_agreement.pdf.' . $agreement->type->name_format))
                                <div class="card scrollable" id="printThis">
                                    @include('partnership_agreement.pdf.' . $agreement->type->name_format, ['agreement' => $agreement])
                                </div>
                                <div class="d-flex justify-content-center mt-3">
                                    <button type="button" id="downloadWorkOrder" class="btn btn-info mb-2 mr-2"><i class="fa fa-file-pdf"></i> Download</button>
                                </div>
                                @else
                                <div class="d-flex justify-content-center">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h5><i class="fa fa-exclamation-circle"></i> Tidak Ada Template Yang Tersedia</h5>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="card-footer text-muted">
                                <small>Dibuat pada: {{ \Carbon\Carbon::parse($customer->partnershipAgreement->created_at)->locale('id')->translatedFormat('d F Y') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Single Modal for all image previews -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" id="modalContent">
                <!-- Image will be inserted here -->
            </div>
        </div>
    </div>
</div>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="galleryModalTitle"></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="carouselGallery" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner" id="carouselInner">
                        <!-- Slides will be inserted here -->
                    </div>
                    <a class="carousel-control-prev" href="#carouselGallery" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselGallery" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Konfirmasi Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Detail Tagihan -->
                <div class="payment-detail bg-light p-3 rounded mb-3">
                    <h6 class="fw-bold mb-3">Detail Tagihan</h6>
                    <div class="row mb-2">
                        <div class="col-sm-5">Paket Internet:</div>
                        <div class="col-sm-7"><strong id="modal-package">-</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5">Harga Bulanan:</div>
                        <div class="col-sm-7" id="modal-monthly-price">-</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-5">Total Pembayaran:</div>
                        <div class="col-sm-7"><strong id="modal-total">-</strong></div>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="mb-3">
                    <h6 class="fw-bold">Metode Pembayaran</h6>
                    <p class="mb-0" id="modal-method">-</p>
                </div>

                <!-- Informasi Bank -->
                <div class="bank-info bg-info bg-opacity-10 p-3 rounded mb-3">
                    <h6 class="fw-bold mb-3">Informasi Transfer</h6>
                    <div class="row mb-2">
                        <div class="col-sm-5">Bank:</div>
                        <div class="col-sm-7"><strong id="modal-bank">-</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5">Nomor Rekening:</div>
                        <div class="col-sm-7"><strong id="modal-account">-</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5">Atas Nama:</div>
                        <div class="col-sm-7"><strong id="modal-account-name">-</strong></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-5">Jumlah:</div>
                        <div class="col-sm-7"><strong class="text-success" id="modal-amount">-</strong></div>
                    </div>
                </div>

                <!-- Form Upload Bukti Pembayaran -->
                <form id="payment-proof-form">
                    <div>
                        <h6 class="fw-bold mb-3">Upload Bukti Pembayaran</h6>
                        
                        <!-- File Upload Area -->
                        <div class="file-upload-area mb-3">
                            <div id="payment-drop-area" class="border-dashed border-2 border-gray-300 rounded p-5 text-center"
                                 style="cursor: pointer;">
                                <div class="mb-2">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                                </div>
                                <p class="mb-1">Klik untuk upload atau drag & drop</p>
                                <p class="text-muted small">PNG, JPG, GIF (Maks. 2MB)</p>
                                <input id="payment_proof" 
                                       type="file" 
                                       class="d-none"
                                       accept="image/*">
                            </div>
                            
                            <!-- Preview Area -->
                            <div id="payment-preview" class="mt-3"></div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i>Kirim Bukti Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@if($customer->partnershipAgreement)
<script>
    function prinsts() 
    {
        let name = "{{$customer->partnershipAgreement->number_result}}" + " {{ $customer->partnershipAgreement->type->name}}";
        let printContents = document.getElementById("printThis").innerHTML;
        let originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.addEventListener("beforeprint", (event) => {
            document.title = name;
        });

        window.print();
        document.body.innerHTML = originalContents;
    }

    $(document).ready(function () {
        $("#downloadWorkOrder").click(function(e) {
            e.preventDefault();
            prinsts();
        });
    });
</script>
@endif
<script>
    document.addEventListener('livewire:load', function() {
        // Format angka ke Rupiah
        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        };

        // Variabel untuk menyimpan data modal
        let paymentModal = null;
        let currentPurchaseId = null;

        // Event untuk menampilkan modal pembayaran
        window.addEventListener('show-payment-modal', function(event) {
            // Simpan data untuk penggunaan nanti
            currentPurchaseId = event.detail.purchaseId || null;
            
            // Isi data ke dalam modal
            document.getElementById('modal-package').textContent = event.detail.packageName;
            document.getElementById('modal-monthly-price').textContent = formatRupiah(event.detail.amount);
            document.getElementById('modal-total').textContent = formatRupiah(event.detail.amount);
            document.getElementById('modal-method').textContent = event.detail.method;
            document.getElementById('modal-bank').textContent = event.detail.bank;
            document.getElementById('modal-account').textContent = event.detail.account;
            document.getElementById('modal-account-name').textContent = event.detail.accountName;
            document.getElementById('modal-amount').textContent = formatRupiah(event.detail.amount);
            
            // Tampilkan modal
            paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
            paymentModal.show();
        });

        // Event untuk menyembunyikan modal pembayaran
        window.addEventListener('hide-payment-modal', function() {
            if (paymentModal) {
                paymentModal.hide();
            }
            
            // Reset form
            document.getElementById('payment-proof-form').reset();
            document.getElementById('payment-preview').innerHTML = '';
        });

        // Handle form submission
        document.getElementById('payment-proof-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Dapatkan file yang diupload
            const fileInput = document.getElementById('payment_proof');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Silakan pilih file bukti pembayaran');
                return;
            }
            
            // Kirim ke Livewire
            @this.upload('payment_proof', file, function() {
                // Set purchase_id jika diperlukan
                if (currentPurchaseId) {
                    @this.set('purchase_id', currentPurchaseId);
                }
                
                // Panggil method submit
                @this.call('submitPaymentProof');
            });
        });

        // Handle file preview
        document.getElementById('payment_proof').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('payment-preview');
            
            if (file) {
                if (file.type.match('image.*')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.innerHTML = `
                            <div class="text-center">
                                <img src="${e.target.result}" class="img-fluid rounded" style="max-height: 200px;">
                                <button type="button" class="btn btn-sm btn-danger mt-2" onclick="clearFileInput()">
                                    <i class="fas fa-times me-1"></i>Hapus
                                </button>
                            </div>
                        `;
                    };
                    
                    reader.readAsDataURL(file);
                } else {
                    preview.innerHTML = `
                        <div class="alert alert-warning">
                            File harus berupa gambar (JPG, PNG, GIF)
                        </div>
                    `;
                    document.getElementById('payment_proof').value = '';
                }
            } else {
                preview.innerHTML = '';
            }
        });

        // Fungsi untuk menghapus file input
        window.clearFileInput = function() {
            document.getElementById('payment_proof').value = '';
            document.getElementById('payment-preview').innerHTML = '';
        };

        // Handle drag and drop
        const dropArea = document.getElementById('payment-drop-area');
        if (dropArea) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                dropArea.classList.add('highlight');
            }
            
            function unhighlight() {
                dropArea.classList.remove('highlight');
            }
            
            dropArea.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files.length) {
                    document.getElementById('payment_proof').files = files;
                    const event = new Event('change', { bubbles: true });
                    document.getElementById('payment_proof').dispatchEvent(event);
                }
            }
        }
    });
</script>
<script>
    document.addEventListener('livewire:load', function () {  
        function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length) {
            const fileInput = document.getElementById('payment_proof');

            // Buat DataTransfer baru dan isi file-nya
            const dataTransfer = new DataTransfer();
            for (let i = 0; i < files.length; i++) {
                dataTransfer.items.add(files[i]);
            }

            // Set file ke input
            fileInput.files = dataTransfer.files;

            // Penting: Trigger event agar Livewire tahu file sudah dipilih
            fileInput.dispatchEvent(new Event('input', { bubbles: true }));
            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

        window.addEventListener('show-payment-modal', () => {

        // Format angka ke Rupiah
        const formatRupiah = (number) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        };
        
        // Isi data ke dalam modal
        document.getElementById('modal-package').textContent = event.detail.packageName;
        document.getElementById('modal-monthly-price').textContent = formatRupiah(event.detail.amount);
        document.getElementById('modal-total').textContent = formatRupiah(event.detail.amount);
        document.getElementById('modal-method').textContent = event.detail.method;
        document.getElementById('modal-bank').textContent = event.detail.bank;
        document.getElementById('modal-account').textContent = event.detail.account;
        document.getElementById('modal-account-name').textContent = event.detail.accountName;
        document.getElementById('modal-amount').textContent = formatRupiah(event.detail.amount);
        
        // Tampilkan modal
        const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        paymentModal.show();
        
        });
    
    // Event untuk menyembunyikan modal
    window.addEventListener('hide-payment-modal', () => {
        var paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
        paymentModal.hide();
    });
    
    // Drag and drop functionality
    const dropArea = document.querySelector('.file-upload-area .border-dashed');
    if (dropArea) 
        {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            dropArea.classList.add('bg-light');
        }
        
        function unhighlight() {
            dropArea.classList.remove('bg-light');
        }
        
        dropArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length) {
                document.getElementById('payment_proof').files = files;
                // Trigger Livewire file upload
                const event = new Event('change', { bubbles: true });
                document.getElementById('payment_proof').dispatchEvent(event);
            }
        }
    }
        window.addEventListener('showImageModal', function(event) {
            console.log("showImageModal");
            
            const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
            
            // Set modal title and content
            document.getElementById('modalTitle').innerText = event.detail.title;
            document.getElementById('modalContent').innerHTML = `
                <img src="${event.detail.imageUrl}" class="img-fluid" alt="${event.detail.title}">
            `;
            
            // Show modal
            modal.show();
        });

        // Gallery modal handler
        window.addEventListener('showGalleryModal', function(event) {
            const modal = new bootstrap.Modal(document.getElementById('galleryModal'));
            const carouselInner = document.getElementById('carouselInner');
            
            // Set title
            document.getElementById('galleryModalTitle').innerText = event.detail.title;
            
            // Clear previous items
            carouselInner.innerHTML = '';
            
            // Add new items
            event.detail.images.forEach((image, index) => {
                const item = document.createElement('div');
                item.className = `carousel-item ${index === 0 ? 'active' : ''}`;
                item.innerHTML = `
                    <div class="text-center">
                        <img src="${image}" class="d-block w-100" style="max-height: 70vh; object-fit: contain;">
                        <div class="mt-2">Foto ${index + 1}/${event.detail.images.length}</div>
                    </div>
                `;
                carouselInner.appendChild(item);
            });
            
            // Initialize carousel
            const carousel = new bootstrap.Carousel(document.getElementById('carouselGallery'));
            modal.show();
        });
    });
</script>
@endpush
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .img-signature
    {
        background-color: transparent !important; 
        border: 0px solid #dee2e6 !important;
        box-shadow: 0px 0px 0px 0px rgba(0,0,0,0.0) !important;       
        max-height: 100px !important; 
    }
    .signature-container {
        width: fit-content;
    }
    .signature-canvas {
        /* width: 100%; */
        /* height: 200px; */
        border: 1px solid #dee2e6;
        border-radius: 4px;
        background-color: white;
        touch-action: none;
    }
    .custom-file-label::after {
        content: "Browse";
    }
    #ktpPreviewImg {
        max-height: 200px;
    }
</style>
<style>
   .small-text 
    {
        text-align: justify;
        font-size: 0.79rem;
    }

    .text-ads a, 
    .text-ads li, 
    .text-ads p, 
    .text-ads div, 
    .text-ads span, 
    .text-ads h1, 
    .text-ads h2, 
    .text-ads h3, 
    .text-ads h4, 
    .text-ads h5, 
    .text-ads h6 
    {
        font-size: 0.92rem;
    }
    .small-header
    {
        font-size: 1rem;
        font-weight: bold;
    }
    @media print {
        #printItem {
            margin-left: 50px;
            margin-right: 50px;
        }
    }

    body {
        font-family: Arial;
        /* font-size : 12px; */
        /* padding: 20px; */
        /* background-color: #f4f4f4; */
    }

    .container {
        /* background-color: #fff; */
        padding: 10px;
        border-radius: 5px;
    }

    .select2-selection__rendered {
        line-height: 31px !important;
    }

    .select2-container .select2-selection--single {
        height: 35px !important;
    }

    .select2-selection__arrow {
        height: 34px !important;
    }

    hr {
        border: 1px solid black;
        border-radius: 5px;
    }

    .select2-selection__rendered {
        line-height: 31px !important;
    }

    .select2-container .select2-selection--single {
        height: 35px !important;
    }

    .select2-selection__arrow {
        height: 34px !important;
    }

    /* li */
    .margin {
        margin-bottom: 15px;
    }

    .noMargin {
        margin-bottom: 0px;
    }

    .scrollable {
        width: 100%;
        height: 650px;
        overflow: auto;
        border: 1px solid #ccc;
    }
</style>
<style>
    .info-box {
        border-radius: 5px;
        margin-bottom: 15px;
        box-shadow: 0 0 1px rgba(0,0,0,0.1);
    }
    .info-box-content {
        padding: 10px 15px;
    }
    .info-box-text {
        font-size: 14px;
        color: #6c757d;
    }
    .info-box-number {
        font-size: 20px;
        font-weight: 600;
    }
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,.125);
    }
    .modal.show {
        background: rgba(0,0,0,0.5);
        display: block;
        overflow: auto;
    }

    /* Add to your styles */
    .carousel-item {
        transition: transform 0.6s ease-in-out;
    }

    .carousel-control-prev, .carousel-control-next {
        width: 5%;
        background: rgba(0,0,0,0.3);
    }

    .carousel-control-prev-icon, .carousel-control-next-icon {
        width: 2rem;
        height: 2rem;
    }
</style>
<style>
.border-dashed {
    border-style: dashed !important;
}
#payment-drop-area:hover {
    border-color: #0d6efd !important;
    background-color: #f8f9fa;
}
#payment-drop-area.highlight {
    border-color: #0d6efd !important;
    background-color: rgba(13, 110, 253, 0.1);
}
</style>
@endpush