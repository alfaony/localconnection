@section('title', $customer->company->name)

<div class="row mb-4">
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
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if($customer->purchase)
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h4 class="text-primary mb-3">
                            <i class="fas fa-credit-card mr-2"></i>Pembayaran
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th width="25%">Metode Pembayaran</th>
                                        <td>{{ ucfirst($customer->purchase->payment_method) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status Pembayaran</th>
                                        <td>
                                            @if($customer->is_paid)
                                            <span class="badge badge-success">Lunas</span>
                                            @elseif($customer->status == \App\Schemas\ParamSchema::WAITING_PAYMENT_CONFIRMATION)
                                                <span class="badge badge-warning">Menunggu Konfirmasi Pembayaran</span>
                                            @else
                                                <span class="badge badge-danger">Belum Lunas</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($paymentProofUrl)
                                    <tr>
                                        <th>Bukti Pembayaran</th>
                                        <td>
                                            <button wire:click="viewPaymentProof" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye mr-1"></i>Lihat Bukti Pembayaran
                                            </button>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

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
@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script>
    document.addEventListener('livewire:load', function () {  
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
@endpush