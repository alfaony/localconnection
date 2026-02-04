@section('title', $customer->company->name)

<div class="row mb-4">
    @include('components.alert')
    @if ($statusMessage)
    <div class="alert alert-{{ $statusMessage['type'] }} alert-dismissible fade show" role="alert">
        {!! $statusMessage['text'] !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
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
                                    {{-- 
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
                                    --}}
                                      @if($customer->coupons->count() > 0)
                                        <tr>
                                            <td colspan="6">
                                                <div class="alert alert-success mb-0">
                                                    <strong><i class="fas fa-ticket-alt mr-2"></i>Kupon Tersedia: {{ $customer->coupons->count() }}</strong>
                                                    <div class="mt-2">
                                                        @foreach($customer->coupons as $coupon)
                                                            <span class="badge badge-lg badge-primary mr-1 mb-1">
                                                                {{ $coupon->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
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
                                                <th>Bukti Pembayaran</th>
                                                <th>Konfirmasi Pembayaran</th>
                                                <th>Invoice</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($purchases as $purchase)
                                            <tr>
                                                <td>
                                                    @if($purchase->period_start && $purchase->period_end)
                                                        {{ $purchase->period_start->format('d M Y') }} - {{ $purchase->period_end->format('d M Y') }}
                                                        <br>
                                                        <small class="text-muted">({{ $purchase->payment_months }} bulan)</small>
                                                    @else
                                                        {{ \Carbon\Carbon::parse($purchase->created_at)->format('F Y') }}
                                                    @endif
                                                </td>
                                                <td>{!! $purchase->status_badge!!}</td>
                                                <td>
                                                    @if($purchase->isConfirmed())
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check-circle mr-1"></i>Lunas
                                                        </span>
                                                    @else
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-times-circle mr-1"></i>Belum Lunas
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>Rp {{ number_format($purchase->amount_paid, 0, ',', '.') }}</strong>
                                                    @if($purchase->discount_amount > 0)
                                                        <br>
                                                        <small class="text-success">
                                                            (Diskon: Rp {{ number_format($purchase->discount_amount, 0, ',', '.') }})
                                                        </small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($purchase->payment_proof)
                                                        <button wire:click="viewPaymentProof('{{ $purchase->id }}')" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye mr-1"></i>Lihat
                                                        </button>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if(!$purchase->isConfirmed() && ($customer->status == \App\Schemas\ParamSchema::WAITING_PAYMENT_SUBSCRIPTION || $customer->status == \App\Schemas\ParamSchema::SUSPENDED ) && $customer->getOldestUnconfirmed()->id == $purchase->id)
                                                        <button class="btn btn-sm btn-success" wire:click="showPaymentModal({{ $purchase->id }})">
                                                            <i class="fas fa-money-bill-wave mr-1"></i>Bayar Sekarang
                                                        </button>
                                                    @elseif($customer->status == \App\Schemas\ParamSchema::WAITING_PAYMENT_CONFIRMATION && $customer->getOldestUnconfirmed()->id == $purchase->id)
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            Menunggu
                                                        </span>
                                                    @elseif(isset($purchase->user_finance_id) || isset($purchase->payment_method))
                                                        <span class="text-success">
                                                            <i class="fas fa-check-circle mr-1"></i>
                                                            {{ \Carbon\Carbon::parse($purchase->confirmation_finance_at)->format('d M Y H:i:s') }}
                                                    @else
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-clock mr-1"></i>
                                                            Expired
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($purchase->isConfirmed() || $purchase->xendit_paid_at || $purchase->midtrans_paid_at)
                                                        <a href="{{ route('internet-customer.download-invoice', $purchase->id) }}" 
                                                           class="btn btn-sm btn-primary" 
                                                           target="_blank"
                                                           title="Download Invoice">
                                                            <i class="fas fa-file-pdf mr-1"></i>Download
                                                        </a>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        {{-- 
                                        <tbody>
                                            @foreach($purchases as $purchase)
                                            <tr>
                                                <td>{{ $purchase->id }}{{ \Carbon\Carbon::parse($purchase->created_at)->format('F Y') }}</td>
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
                                        --}}
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
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="fas fa-money-bill-wave mr-2"></i>Konfirmasi Pembayaran
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Column - Payment Options -->
                    <div class="col-md-5">
                        <!-- Period Selection -->
                        <div class="mb-4">
                            <h6 class="font-weight-bold mb-3">
                                <i class="fas fa-calendar-alt mr-2"></i>Pilih Periode Pembayaran
                            </h6>

                            <!-- Info Alert -->
                            <div class="alert alert-info py-2 px-3 mb-3">
                                <small>
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Periode aktif berakhir: <strong id="current-period-end">-</strong>
                                    <br>
                                    <span id="next-period-preview">Pembayaran untuk periode: <strong>-</strong></span>
                                </small>
                            </div>

                            <!-- Custom Month Input -->
                            <div class="custom-months-input mb-3">
                                <label class="font-weight-bold mb-2">
                                    <i class="fas fa-keyboard mr-1"></i>
                                    Jumlah Bulan (1-24)
                                </label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary" type="button" onclick="decreaseMonths()">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           class="form-control text-center font-weight-bold" 
                                           id="custom-months-input" 
                                           min="1" 
                                           max="24" 
                                           value="1"
                                           wire:model.lazy="payment_months"
                                           onchange="updateCustomMonths(this.value)">
                                    <button class="btn btn-outline-secondary" type="button" onclick="increaseMonths()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <div class="input-group-append">
                                        <span class="input-group-text">Bulan</span>
                                    </div>
                                </div>
                                <small class="text-muted">Minimal 1 bulan, maksimal 24 bulan</small>
                            </div>

                            <!-- Quick Selection Buttons -->
                            <div class="quick-selection mb-3" id="discount-quick-selection" style="display: none;">
                                <label class="font-weight-bold mb-2">
                                    <i class="fas fa-bolt mr-1"></i>
                                    Pilihan Cepat (Dengan Diskon)
                                </label>
                                <div class="row">
                                    <!-- Will be populated by JavaScript based on discount tiers -->
                                </div>
                            </div>

                            <!-- Popular Choices -->
                            <div class="quick-selection mb-3">
                                <label class="font-weight-bold mb-2">
                                    <i class="fas fa-star mr-1"></i>
                                    Pilihan Populer
                                </label>
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <button type="button" class="btn btn-outline-primary btn-block" onclick="updateCustomMonths(1)">
                                            1 Bulan
                                        </button>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <button type="button" class="btn btn-outline-primary btn-block" onclick="updateCustomMonths(3)">
                                            3 Bulan
                                        </button>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <button type="button" class="btn btn-outline-primary btn-block" onclick="updateCustomMonths(6)">
                                            6 Bulan
                                        </button>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <button type="button" class="btn btn-outline-primary btn-block" onclick="updateCustomMonths(12)">
                                            12 Bulan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Summary -->
                        <div class="payment-summary bg-light p-3 rounded border">
                            <h6 class="font-weight-bold mb-3">
                                <i class="fas fa-file-invoice mr-2"></i>Ringkasan Pembayaran
                            </h6>
                            <div class="summary-row">
                                <span>Paket Internet:</span>
                                <span id="summary-package">-</span>
                            </div>
                            <div class="summary-row">
                                <span>Periode:</span>
                                <span id="summary-period">1 Bulan</span>
                            </div>
                            <div class="summary-row">
                                <span>Harga per Bulan:</span>
                                <span id="summary-monthly">Rp 0</span>
                            </div>
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="summary-subtotal">Rp 0</span>
                            </div>
                            <div class="summary-row discount-row" id="discount-row" style="display: none;">
                                <span class="text-success">
                                    <i class="fas fa-tag mr-1"></i>
                                    Diskon <span id="summary-discount-percent">(0%)</span>:
                                </span>
                                <span class="text-success font-weight-bold">
                                    - <span id="summary-discount">Rp 0</span>
                                </span>
                            </div>
                            <div class="summary-row" id="amount-before-tax-row">
                                <span>Harga sebelum pajak:</span>
                                <span id="summary-amount-before-tax">Rp 0</span>
                            </div>
                            <div class="summary-row text-muted" id="tax-row">
                                <span>PPN (<span id="summary-tax-rate">11</span>%):</span>
                                <span id="summary-tax-amount">Rp 0</span>
                            </div>
                            <hr class="my-2">
                            <div class="summary-row summary-total">
                                <span class="font-weight-bold">Total Pembayaran:</span>
                                <span class="font-weight-bold text-success h5 mb-0" id="summary-total">Rp 0</span>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Periode: <span id="summary-period-detail">-</span>
                            </small>
                        </div>
                    </div>

                    <!-- Right Column - Payment Method -->
                    <div class="col-md-7">
                        <!-- Payment Method Selection -->
                        <div class="mb-4" id="payment-method-selection">
                            <h6 class="font-weight-bold mb-3">
                                <i class="fas fa-wallet mr-2"></i>Pilih Metode Pembayaran
                            </h6>
                            <div class="row">
                                @if($manualPaymentEnabled)
                                <div class="col-md-4 mb-2">
                                    <div class="card payment-method-card" onclick="selectPaymentMethod('manual')" id="manual-card">
                                        <div class="card-body text-center py-4">
                                            <i class="fas fa-university fa-3x text-primary mb-2"></i>
                                            <h6 class="mb-1">Transfer Bank Manual</h6>
                                            <small class="text-muted">Upload bukti transfer</small>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div class="col-md-4 mb-2" id="xendit-method-wrapper" style="display: none;">
                                    <div class="card payment-method-card" onclick="selectPaymentMethod('xendit')" id="xendit-card">
                                        <div class="card-body text-center py-4">
                                            <i class="fas fa-credit-card fa-3x text-success mb-2"></i>
                                            <h6 class="mb-1">Pembayaran Digital</h6>
                                            <small class="text-muted">VA, E-Wallet, Credit Card</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2" id="midtrans-method-wrapper" style="display: none;">
                                    <div class="card payment-method-card" onclick="selectPaymentMethod('midtrans')" id="midtrans-card">
                                        <div class="card-body text-center py-4">
                                            <i class="fas fa-credit-card fa-3x text-warning mb-2"></i>
                                            <h6 class="mb-1">Midtrans Payment</h6>
                                            <small class="text-muted">Berbagai metode pembayaran</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Transfer Section -->
                        @if($manualPaymentEnabled)
                        <div id="manual-payment-section">
                            <!-- Bank Info -->
                            <div class="bank-info bg-info bg-opacity-10 p-3 rounded mb-3 border border-info">
                                <h6 class="font-weight-bold mb-3">
                                    <i class="fas fa-info-circle mr-2"></i>Informasi Transfer Bank
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td width="35%">Bank</td>
                                        <td width="5%">:</td>
                                        <td><strong id="modal-bank">-</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Nomor Rekening</td>
                                        <td>:</td>
                                        <td>
                                            <strong id="modal-account" class="text-primary">-</strong>
                                            <button type="button" class="btn btn-sm btn-outline-secondary ml-2" onclick="copyAccountNumber()" title="Salin nomor rekening">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Atas Nama</td>
                                        <td>:</td>
                                        <td><strong id="modal-account-name">-</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Jumlah Transfer</td>
                                        <td>:</td>
                                        <td>
                                            <strong class="text-success h6 mb-0" id="modal-amount">Rp 0</strong>
                                        </td>
                                    </tr>
                                </table>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <small>
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Transfer sesuai <strong>nominal yang tertera</strong> untuk mempermudah verifikasi
                                    </small>
                                </div>
                            </div>

                            <!-- Upload Form -->
                            <form id="payment-proof-form">
                                <!-- Transfer Details Section -->
                                <div class="mb-4">
                                    <h6 class="font-weight-bold mb-3">
                                        <i class="fas fa-file-alt mr-2"></i>Detail Transfer
                                    </h6>
                                    
                                    <!-- Transfer Date -->
                                    <div class="form-group">
                                        <label for="transfer_date" class="font-weight-bold">
                                            Tanggal Transfer <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" 
                                               id="transfer_date" 
                                               class="form-control @error('transfer_date') is-invalid @enderror" 
                                               wire:model="transfer_date"
                                               max="{{ date('Y-m-d') }}">
                                        @error('transfer_date')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Tanggal saat Anda melakukan transfer</small>
                                    </div>

                                    <!-- Transfer From Bank -->
                                    <div class="form-group">
                                        <label for="transfer_from_bank" class="font-weight-bold">
                                            Nama Bank Pengirim
                                        </label>
                                        <input type="text" 
                                               id="transfer_from_bank" 
                                               class="form-control @error('transfer_from_bank') is-invalid @enderror" 
                                               wire:model="transfer_from_bank"
                                               placeholder="Contoh: BCA, Mandiri, BNI">
                                        @error('transfer_from_bank')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Bank yang Anda gunakan untuk transfer</small>
                                    </div>

                                    <!-- Transfer From Account Name -->
                                    <div class="form-group">
                                        <label for="transfer_from_account_name" class="font-weight-bold">
                                            Nama Pemilik Rekening Pengirim
                                        </label>
                                        <input type="text" 
                                               id="transfer_from_account_name" 
                                               class="form-control @error('transfer_from_account_name') is-invalid @enderror" 
                                               wire:model="transfer_from_account_name"
                                               placeholder="Nama sesuai rekening Anda">
                                        @error('transfer_from_account_name')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Nama pemilik rekening yang digunakan untuk transfer</small>
                                    </div>

                                    <!-- Transfer Notes -->
                                    <div class="form-group">
                                        <label for="transfer_notes" class="font-weight-bold">
                                            Catatan (Opsional)
                                        </label>
                                        <textarea id="transfer_notes" 
                                                  class="form-control @error('transfer_notes') is-invalid @enderror" 
                                                  wire:model="transfer_notes"
                                                  rows="3"
                                                  placeholder="Catatan tambahan (jika ada)"></textarea>
                                        @error('transfer_notes')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Informasi tambahan terkait transfer (maksimal 500 karakter)</small>
                                    </div>
                                </div>

                                <div>
                                    <h6 class="font-weight-bold mb-3">
                                        <i class="fas fa-upload mr-2"></i>Upload Bukti Pembayaran <span class="text-danger">*</span>
                                    </h6>
                                    
                                    <div class="file-upload-area mb-3">
                                        <div id="payment-drop-area" 
                                             class="border border-2 border-dashed rounded p-4 text-center"
                                             style="cursor: pointer; border-color: #ddd;">
                                            <div class="mb-2">
                                                <i class="fas fa-cloud-upload-alt fa-3x text-muted"></i>
                                            </div>
                                            <p class="mb-1 font-weight-bold">Klik untuk upload atau drag & drop</p>
                                            <p class="text-muted small mb-0">PNG, JPG, GIF (Maksimal 2MB)</p>
                                            <input id="payment_proof" 
                                                   type="file" 
                                                   class="d-none"
                                                   accept="image/*"
                                                   wire:model="payment_proof">
                                        </div>
                                        <div id="payment-preview" class="mt-3"></div>
                                    </div>
                                </div>
                                
                                <div class="modal-footer border-top pt-3">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        <i class="fas fa-times mr-1"></i>Batal
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane mr-1"></i>Kirim Bukti Pembayaran
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif

                        <!-- Xendit Payment Section -->
                        <div id="xendit-payment-section" style="display: none;">
                            <div class="text-center p-4">
                                <i class="fas fa-credit-card fa-4x text-success mb-3"></i>
                                <h5 class="mb-3">Pembayaran Digital via Xendit</h5>
                                <p class="text-muted mb-4">
                                    Anda akan diarahkan ke halaman pembayaran Xendit untuk menyelesaikan transaksi
                                </p>
                                <div class="mb-4">
                                    <small class="text-muted d-block mb-2">Metode pembayaran yang tersedia:</small>
                                    <div class="d-flex justify-content-center flex-wrap">
                                        <span class="badge badge-info m-1 p-2">
                                            <i class="fas fa-university mr-1"></i>Virtual Account
                                        </span>
                                        <span class="badge badge-info m-1 p-2">
                                            <i class="fas fa-wallet mr-1"></i>E-Wallet
                                        </span>
                                        <span class="badge badge-info m-1 p-2">
                                            <i class="fas fa-credit-card mr-1"></i>Credit Card
                                        </span>
                                        <span class="badge badge-info m-1 p-2">
                                            <i class="fas fa-qrcode mr-1"></i>QRIS
                                        </span>
                                    </div>
                                </div>
                                
                                <button type="button" 
                                        class="btn btn-success btn-lg px-5" 
                                        onclick="initiateXenditPayment()"
                                        id="xendit-pay-button">
                                    <i class="fas fa-arrow-right mr-2"></i>Lanjutkan ke Pembayaran
                                </button>
                                
                                <div id="xendit-loading" class="mt-3" style="display: none;">
                                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                                    <p class="mt-2 text-muted">Membuat invoice pembayaran...</p>
                                </div>

                                <div class="mt-4">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt mr-1"></i>
                                        Pembayaran dilindungi dan diproses oleh Xendit
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Midtrans Payment Section -->
                        <div id="midtrans-payment-section" style="display: none;">
                            <div class="text-center p-4">
                                <i class="fas fa-credit-card fa-4x text-warning mb-3"></i>
                                <h5 class="mb-3">Pembayaran via Midtrans</h5>
                                <p class="text-muted mb-4">
                                    Anda akan diarahkan ke halaman pembayaran Midtrans untuk menyelesaikan transaksi
                                </p>
                                <div class="mb-4">
                                    <small class="text-muted d-block mb-2">Metode pembayaran yang tersedia:</small>
                                    <div class="d-flex justify-content-center flex-wrap">
                                        <span class="badge badge-warning m-1 p-2">
                                            <i class="fas fa-university mr-1"></i>Virtual Account
                                        </span>
                                        <span class="badge badge-warning m-1 p-2">
                                            <i class="fas fa-wallet mr-1"></i>E-Wallet
                                        </span>
                                        <span class="badge badge-warning m-1 p-2">
                                            <i class="fas fa-credit-card mr-1"></i>Credit Card
                                        </span>
                                        <span class="badge badge-warning m-1 p-2">
                                            <i class="fas fa-qrcode mr-1"></i>QRIS
                                        </span>
                                    </div>
                                </div>
                                
                                <button type="button" 
                                        class="btn btn-warning btn-lg px-5" 
                                        onclick="initiateMidtransPayment()"
                                        id="midtrans-pay-button">
                                    <i class="fas fa-arrow-right mr-2"></i>Lanjutkan ke Pembayaran
                                </button>
                                
                                <div id="midtrans-loading" class="mt-3" style="display: none;">
                                    <i class="fas fa-spinner fa-spin fa-2x text-warning"></i>
                                    <p class="mt-2 text-muted">Membuat transaksi pembayaran...</p>
                                </div>

                                <div class="mt-4">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt mr-1"></i>
                                        Pembayaran dilindungi dan diproses oleh Midtrans
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
    // Global variables
    let paymentModal = null;
    let currentPurchaseId = null;
    let currentPaymentMethod = 'manual';
    let selectedMonths = 1;
    let xenditActive = false;
    let midtransActive = false;
    let monthlyPrice = 0;
    let packageName = '';
    let discountEnabled = false;
    let discountTiers = [];
    let minMonths = 1;
    let maxMonths = 24;
    let currentBillingEnd = '';
    let nextPeriodStart = '';

    // Format Rupiah
    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(number);
    };

    // Get discount percentage for given months
    function getDiscountPercentage(months) {
        if (!discountEnabled || discountTiers.length === 0) {
            return 0;
        }

        let applicableDiscount = 0;
        discountTiers.forEach(tier => {
            if (months >= tier.months) {
                applicableDiscount = tier.discount;
            }
        });

        return applicableDiscount;
    }

    // Calculate payment
    function calculatePayment(months) {
        const subtotal = monthlyPrice * months;
        const discountPercent = getDiscountPercentage(months);
        const discountAmount = subtotal * (discountPercent / 100);
        const amountBeforeTax = Math.round(subtotal - discountAmount);
        
        // Always calculate PPN (11%)
        const taxRate = 11;
        const taxAmount = Math.round((amountBeforeTax * taxRate) / 100);
        const total = Math.round(amountBeforeTax + taxAmount);

        return {
            months: months,
            subtotal: subtotal,
            discountPercent: discountPercent,
            discountAmount: discountAmount,
            amountBeforeTax: amountBeforeTax,
            taxRate: taxRate,
            taxAmount: taxAmount,
            total: total
        };
    }

    // Calculate period dates
    function calculatePeriodDates(months) {
        // Simple calculation for display
        // Backend will calculate the exact dates
        if (months === 1) {
            return `1 bulan ke depan`;
        } else if (months < 12) {
            return `${months} bulan ke depan`;
        } else {
            const years = Math.floor(months / 12);
            const remainingMonths = months % 12;
            if (remainingMonths === 0) {
                return `${years} tahun`;
            } else {
                return `${years} tahun ${remainingMonths} bulan`;
            }
        }
    }

    // Update summary display
    function updateSummary() {
        const calc = calculatePayment(selectedMonths);

        document.getElementById('summary-package').textContent = packageName;
        document.getElementById('summary-period').textContent = selectedMonths + ' Bulan';
        document.getElementById('summary-monthly').textContent = formatRupiah(monthlyPrice);
        document.getElementById('summary-subtotal').textContent = formatRupiah(calc.subtotal);
        
        // Display PPN breakdown
        document.getElementById('summary-amount-before-tax').textContent = formatRupiah(calc.amountBeforeTax);
        document.getElementById('summary-tax-rate').textContent = calc.taxRate;
        document.getElementById('summary-tax-amount').textContent = formatRupiah(calc.taxAmount);
        
        document.getElementById('summary-total').textContent = formatRupiah(calc.total);
        const modalAmount1 = document.getElementById('modal-amount');
        if (modalAmount1) modalAmount1.textContent = formatRupiah(calc.total);
        
        // Update period detail
        document.getElementById('summary-period-detail').textContent = calculatePeriodDates(selectedMonths);
        document.getElementById('next-period-preview').innerHTML = `Pembayaran untuk periode: <strong>${calculatePeriodDates(selectedMonths)}</strong>`;

        // Show/hide discount row
        const discountRow = document.getElementById('discount-row');
        if (calc.discountPercent > 0) {
            discountRow.style.display = 'flex';
            document.getElementById('summary-discount-percent').textContent = `(${calc.discountPercent}%)`;
            document.getElementById('summary-discount').textContent = formatRupiah(calc.discountAmount);
        } else {
            discountRow.style.display = 'none';
        }

        // Sync with Livewire
        @this.set('payment_months', selectedMonths);
    }

    // Update custom months from input or buttons
    window.updateCustomMonths = function(months) {
        months = parseInt(months);
        
        // Validate range
        if (months < minMonths) months = minMonths;
        if (months > maxMonths) months = maxMonths;
        
        selectedMonths = months;
        
        // Update input value
        const input = document.getElementById('custom-months-input');
        if (input) {
            input.value = months;
        }
        
        updateSummary();
    };

    // Increase months
    window.increaseMonths = function() {
        const currentValue = parseInt(document.getElementById('custom-months-input').value);
        updateCustomMonths(currentValue + 1);
    };

    // Decrease months
    window.decreaseMonths = function() {
        const currentValue = parseInt(document.getElementById('custom-months-input').value);
        updateCustomMonths(currentValue - 1);
    };

    // Select payment method
    window.selectPaymentMethod = function(method) {
        // Prevent selecting manual if it doesn't exist (disabled)
        const manualCard = document.getElementById('manual-card');
        if (method === 'manual' && !manualCard) {
            console.warn('Manual payment is disabled, selecting alternative method');
            // Try to select xendit or midtrans instead
            const xenditSection = document.getElementById('xendit-payment-section');
            const midtransSection = document.getElementById('midtrans-payment-section');
            if (xenditSection) {
                method = 'xendit';
            } else if (midtransSection) {
                method = 'midtrans';
            } else {
                console.error('No payment method available');
                return;
            }
        }
        
        currentPaymentMethod = method;
        
        const xenditCard = document.getElementById('xendit-card');
        const midtransCard = document.getElementById('midtrans-card');
        
        // Reset all cards (only if they exist)
        if (manualCard) {
            manualCard.classList.remove('border-primary', 'bg-light');
            manualCard.style.borderWidth = '1px';
        }
        if (xenditCard) {
            xenditCard.classList.remove('border-success', 'bg-light');
            xenditCard.style.borderWidth = '1px';
        }
        if (midtransCard) {
            midtransCard.classList.remove('border-warning', 'bg-light');
            midtransCard.style.borderWidth = '1px';
        }
        
        // Get payment section elements (might not exist if disabled)
        const manualSection = document.getElementById('manual-payment-section');
        const xenditSection = document.getElementById('xendit-payment-section');
        const midtransSection = document.getElementById('midtrans-payment-section');
        
        // Apply active style to selected method
        if (method === 'manual' && manualCard) {
            manualCard.classList.add('border-primary', 'bg-light');
            manualCard.style.borderWidth = '3px';
            if (manualSection) manualSection.style.display = 'block';
            if (xenditSection) xenditSection.style.display = 'none';
            if (midtransSection) midtransSection.style.display = 'none';
        } else if (method === 'xendit') {
            if (xenditCard) {
                xenditCard.classList.add('border-success', 'bg-light');
                xenditCard.style.borderWidth = '3px';
            }
            if (manualSection) manualSection.style.display = 'none';
            if (xenditSection) xenditSection.style.display = 'block';
            if (midtransSection) midtransSection.style.display = 'none';
        } else if (method === 'midtrans') {
            if (midtransCard) {
                midtransCard.classList.add('border-warning', 'bg-light');
                midtransCard.style.borderWidth = '3px';
            }
            if (manualSection) manualSection.style.display = 'none';
            if (xenditSection) xenditSection.style.display = 'none';
            if (midtransSection) midtransSection.style.display = 'block';
        }
    };

    // Show payment modal
    window.addEventListener('show-payment-modal', function(event) {
        currentPurchaseId = event.detail.purchaseId || null;
        xenditActive = event.detail.xenditActive || false;
        midtransActive = event.detail.midtransActive || false;
        monthlyPrice = event.detail.monthlyPrice || 0;
        packageName = event.detail.packageName || '-';
        discountEnabled = event.detail.discountEnabled || false;
        discountTiers = event.detail.discountTiers || [];
        minMonths = event.detail.minMonths || 1;
        maxMonths = event.detail.maxMonths || 24;
        currentBillingEnd = event.detail.currentBillingEnd || '-';
        nextPeriodStart = event.detail.nextPeriodStart || '-';
        
        // Update bank info (only if elements exist - manual payment might be disabled)
        const modalBank = document.getElementById('modal-bank');
        const modalAccount = document.getElementById('modal-account');
        const modalAccountName = document.getElementById('modal-account-name');
        const currentPeriodEnd = document.getElementById('current-period-end');
        
        if (modalBank) modalBank.textContent = event.detail.bank;
        if (modalAccount) modalAccount.textContent = event.detail.account;
        if (modalAccountName) modalAccountName.textContent = event.detail.accountName;
        if (currentPeriodEnd) currentPeriodEnd.textContent = currentBillingEnd;
        
        // Update month input limits
        const monthInput = document.getElementById('custom-months-input');
        if (monthInput) {
            monthInput.min = minMonths;
            monthInput.max = maxMonths;
        }
        
        // Show/hide discount quick selection
        const discountQuickSelection = document.getElementById('discount-quick-selection');
        if (discountEnabled && discountTiers.length > 0) {
            discountQuickSelection.style.display = 'block';
            
            // Build discount buttons
            let buttonsHtml = '';
            discountTiers.forEach(tier => {
                buttonsHtml += `
                    <div class="col-6 mb-2">
                        <button type="button" class="btn btn-outline-success btn-block" onclick="updateCustomMonths(${tier.months})">
                            ${tier.months} Bulan
                            <br>
                            <small>${tier.label}</small>
                        </button>
                    </div>
                `;
            });
            discountQuickSelection.querySelector('.row').innerHTML = buttonsHtml;
        } else {
            discountQuickSelection.style.display = 'none';
        }
        
        // Show/hide Xendit option
        if (xenditActive) {
            document.getElementById('xendit-method-wrapper').style.display = 'block';
        } else {
            document.getElementById('xendit-method-wrapper').style.display = 'none';
        }
        
        
        // Show/hide Midtrans option
        console.log('Midtrans Status Check:', {
            midtransActive: midtransActive,
            wrapperElement: document.getElementById('midtrans-method-wrapper'),
            xenditActive: xenditActive
        });
        
        if (midtransActive) {
            document.getElementById('midtrans-method-wrapper').style.display = 'block';
            console.log('✅ Midtrans wrapper shown');
        } else {
            document.getElementById('midtrans-method-wrapper').style.display = 'none';
            console.log('❌ Midtrans wrapper hidden');
        }
        
        // Reset selections
        selectedMonths = 1;
        updateCustomMonths(1);
        
        // Smart default payment method selection
        const manualCard = document.getElementById('manual-card');
        if (manualCard) {
            // Manual payment is enabled, select it
            selectPaymentMethod('manual');
        } else if (xenditActive) {
            // Manual disabled, but xendit active
            selectPaymentMethod('xendit');
        } else if (midtransActive) {
            // Manual disabled, xendit disabled, but midtrans active
            selectPaymentMethod('midtrans');
        } else {
            console.error('No payment method available!');
        }
        
        // Show modal
        paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        paymentModal.show();
    });

    // Hide payment modal
    window.addEventListener('hide-payment-modal', function() {
        if (paymentModal) {
            paymentModal.hide();
        }
        resetPaymentForm();
    });

    // Listen to Livewire payment calculated event
    window.addEventListener('payment-calculated', function(event) {
        const calc = event.detail.calculation;
        
        // Update summary from Livewire calculation
        document.getElementById('summary-monthly').textContent = formatRupiah(calc.monthly_price);
        document.getElementById('summary-subtotal').textContent = formatRupiah(calc.subtotal);
        document.getElementById('summary-total').textContent = formatRupiah(calc.total);
        const modalAmount2 = document.getElementById('modal-amount');
        if (modalAmount2) modalAmount2.textContent = formatRupiah(calc.total);
        
        const discountRow = document.getElementById('discount-row');
        if (calc.discount_percentage > 0) {
            discountRow.style.display = 'flex';
            document.getElementById('summary-discount-percent').textContent = `(${calc.discount_percentage}%)`;
            document.getElementById('summary-discount').textContent = formatRupiah(calc.discount_amount);
        } else {
            discountRow.style.display = 'none';
        }
    });

    // Initiate Xendit payment
    window.initiateXenditPayment = function() {
        const button = document.getElementById('xendit-pay-button');
        const loadingDiv = document.getElementById('xendit-loading');
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
        loadingDiv.style.display = 'block';
        
        @this.call('payWithXendit')
            .then(() => {
                console.log('Redirecting to Xendit...');
            })
            .catch(error => {
                console.error('Error:', error);
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-arrow-right mr-2"></i>Lanjutkan ke Pembayaran';
                loadingDiv.style.display = 'none';
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
    };

    // Initiate Midtrans payment
    window.initiateMidtransPayment = function() {
        const button = document.getElementById('midtrans-pay-button');
        const loadingDiv = document.getElementById('midtrans-loading');
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
        loadingDiv.style.display = 'block';
        
        @this.call('payWithMidtrans')
            .then(() => {
                console.log('Redirecting to Midtrans...');
            })
            .catch(error => {
                console.error('Error:', error);
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-arrow-right mr-2"></i>Lanjutkan ke Pembayaran';
                loadingDiv.style.display = 'none';
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
    };

    // Copy account number
    window.copyAccountNumber = function() {
        const accountNumber = document.getElementById('modal-account').textContent;
        navigator.clipboard.writeText(accountNumber).then(function() {
            // Show toast notification
            const toast = document.createElement('div');
            toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
            toast.innerHTML = `
                <i class="fas fa-check-circle mr-2"></i>Nomor rekening berhasil disalin!
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            `;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        });
    };

    // Reset payment form
    function resetPaymentForm() {
        document.getElementById('payment-proof-form').reset();
        document.getElementById('payment-preview').innerHTML = '';
        currentPurchaseId = null;
        currentPaymentMethod = 'manual';
        selectedMonths = 1;
    }

    // Handle form submission (only if manual payment is enabled)
    const paymentProofForm = document.getElementById('payment-proof-form');
    if (paymentProofForm) {
        paymentProofForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            console.log('Form submit triggered');
            
            const fileInput = document.getElementById('payment_proof');
            const file = fileInput.files[0];
            
            const transferDateInput = document.getElementById('transfer_date');
            const transferDate = transferDateInput ? transferDateInput.value : '';
            
            console.log('File:', file);
            console.log('Transfer Date:', transferDate);
            
            if (!file) {
                alert('Silakan pilih file bukti pembayaran');
                return;
            }
            
            if (!transferDate) {
                alert('Silakan isi tanggal transfer');
                transferDateInput.focus();
                return;
            }
            
            const submitBtn = document.querySelector('#payment-proof-form button[type="submit"]');
            const originalText = submitBtn ? submitBtn.innerHTML : '';
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Mengupload...';
            }
            
            // Listen for Livewire upload finish event
            const uploadFinishHandler = (event) => {
                console.log('Upload finished, syncing form data...');
                
                // Manually sync all form field values to Livewire
                const transferDate = document.getElementById('transfer_date')?.value || '';
                const transferFromBank = document.getElementById('transfer_from_bank')?.value || '';
                const transferFromAccountName = document.getElementById('transfer_from_account_name')?.value || '';
                const transferNotes = document.getElementById('transfer_notes')?.value || '';
                
                console.log('Syncing data:', {
                    transferDate,
                    transferFromBank,
                    transferFromAccountName,
                    transferNotes
                });
                
                // Set values in Livewire
                @this.set('transfer_date', transferDate);
                @this.set('transfer_from_bank', transferFromBank);
                @this.set('transfer_from_account_name', transferFromAccountName);
                @this.set('transfer_notes', transferNotes);
                
                // Wait a bit for Livewire to sync, then call submit
                setTimeout(() => {
                    console.log('Calling submitPaymentProof...');
                    
                    // Call the backend method
                    @this.call('submitPaymentProof').then(() => {
                        console.log('submitPaymentProof completed');
                        
                        // Reload page to show updated status
                        window.location.reload();
                    }).catch((error) => {
                        console.error('Error submitting payment proof:', error);
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                        alert('Gagal mengirim bukti pembayaran: ' + (error.message || 'Unknown error'));
                    });
                }, 300); // Small delay to ensure sync completes
                
                // Remove listener after use
                window.removeEventListener('livewire-upload-finish', uploadFinishHandler);
            };
            
            // Add listener
            window.addEventListener('livewire-upload-finish', uploadFinishHandler);
            
            // Trigger Livewire file upload manually
            @this.upload('payment_proof', file, (uploadedFilename) => {
                // Upload completed successfully
                console.log('File uploaded:', uploadedFilename);
                // Dispatch custom event to trigger submit
                window.dispatchEvent(new CustomEvent('livewire-upload-finish'));
            }, (error) => {
                // Upload failed
                console.error('Upload failed:', error);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
                alert('Gagal mengupload file: ' + error);
                window.removeEventListener('livewire-upload-finish', uploadFinishHandler);
            }, (event) => {
                // Upload progress
                let progress = Math.round((event.detail.progress || 0));
                if (submitBtn) {
                    submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-1"></i>Uploading ${progress}%`;
                }
            });
        });
    }

    // File preview (only if manual payment is enabled)
    const paymentProofInput = document.getElementById('payment_proof');
    if (paymentProofInput) {
        paymentProofInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('payment-preview');
        
        if (file) {
            if (file.type.match('image.*')) {
                if (file.size > 2 * 1024 * 1024) {
                    preview.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Ukuran file terlalu besar! Maksimal 2MB.
                        </div>
                    `;
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <img src="${e.target.result}" class="img-fluid rounded" style="max-height: 300px;">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="clearFileInput()">
                                        <i class="fas fa-times mr-1"></i>Hapus File
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        File harus berupa gambar (JPG, PNG, GIF)
                    </div>
                `;
                this.value = '';
            }
        } else {
            preview.innerHTML = '';
        }
        });
    }

    // Clear file input
    window.clearFileInput = function() {
        const input = document.getElementById('payment_proof');
        const preview = document.getElementById('payment-preview');
        if (input) input.value = '';
        if (preview) preview.innerHTML = '';
    };

    // Drag and drop
    const dropArea = document.getElementById('payment-drop-area');
    if (dropArea) {
        dropArea.addEventListener('click', function() {
            document.getElementById('payment_proof').click();
        });

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, function(e) {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, function() {
                this.classList.add('border-primary', 'bg-light');
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, function() {
                this.classList.remove('border-primary', 'bg-light');
            }, false);
        });
        
        dropArea.addEventListener('drop', function(e) {
            const files = e.dataTransfer.files;
            if (files.length) {
                const fileInput = document.getElementById('payment_proof');
                fileInput.files = files;
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        }, false);
    }

    // Handle keyboard input on custom months
    const monthsInput = document.getElementById('custom-months-input');
    if (monthsInput) {
        monthsInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                updateCustomMonths(this.value);
            }
        });

        // Prevent non-numeric input
        monthsInput.addEventListener('keypress', function(e) {
            if (!/[0-9]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') {
                e.preventDefault();
            }
        });
    }

    // Image modals
    window.addEventListener('showImageModal', function(event) {
        const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        
        document.getElementById('modalTitle').innerText = event.detail.title;
        
        let content = `<img src="${event.detail.imageUrl}" class="img-fluid" alt="${event.detail.title}">`;
        
        // Add transfer details if available
        if (event.detail.transferDetails) {
            const details = event.detail.transferDetails;
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
        
        document.getElementById('modalContent').innerHTML = content;
        modal.show();
    });
    
    window.addEventListener('showGalleryModal', function(event) {
        const modal = new bootstrap.Modal(document.getElementById('galleryModal'));
        const carouselInner = document.getElementById('carouselInner');
        
        document.getElementById('galleryModalTitle').innerText = event.detail.title;
        carouselInner.innerHTML = '';
        
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
        
        modal.show();
    });
});
</script>
@endpush


@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
@include('livewire.internet-customer.steps.styles')
<style>
    /* Custom Months Input */
    .custom-months-input .form-control {
        font-size: 20px;
        height: 50px;
    }

    .custom-months-input .btn {
        height: 50px;
        width: 50px;
    }

    .custom-months-input .input-group-text {
        height: 50px;
    }

    /* Quick Selection Buttons */
    .quick-selection .btn {
        padding: 10px 15px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .quick-selection .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .quick-selection .btn-outline-primary:hover {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }

    .quick-selection .btn-outline-success:hover {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }

    /* Payment Summary */
    .payment-summary {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px solid #dee2e6 !important;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #dee2e6;
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .summary-total {
        padding-top: 15px;
        font-size: 18px;
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        margin: 0 -15px -15px -15px;
        padding: 15px 15px 15px 15px;
        border-radius: 0 0 5px 5px;
    }

    .discount-row {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        margin: 0 -15px;
        padding: 10px 15px !important;
        border-bottom: none !important;
    }

    /* Payment Method Cards */
    .payment-method-card {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #ddd;
        height: 100%;
    }

    .payment-method-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    }

    .payment-method-card.border-primary {
        border-color: #007bff !important;
        background: linear-gradient(135deg, #f8f9ff 0%, #e3f2fd 100%);
    }

    .payment-method-card.border-success {
        border-color: #28a745 !important;
        background: linear-gradient(135deg, #f1f8f4 0%, #d4edda 100%);
    }

    /* Bank Info */
    .bank-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    }

    .bank-info table td {
        padding: 8px 0;
    }

    /* Drop Area */
    .border-dashed {
        border-style: dashed !important;
    }

    #payment-drop-area {
        transition: all 0.3s ease;
    }

    #payment-drop-area:hover {
        border-color: #007bff !important;
        background-color: #f8f9fa !important;
    }

    #payment-drop-area.border-primary {
        border-color: #007bff !important;
        background-color: rgba(0, 123, 255, 0.05) !important;
    }

    /* Modal */
    .modal-xl {
        max-width: 1200px;
    }

    .modal-body {
        max-height: 80vh;
        overflow-y: auto;
    }

    /* Alert Info */
    .alert-info {
        background-color: #e7f3ff;
        border-color: #b8daff;
        color: #004085;
    }

    /* Badges */
    .badge {
        padding: 6px 12px;
        font-size: 12px;
    }

    /* Input number - hide spinner */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    input[type="number"] {
        -moz-appearance: textfield;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modal-xl {
            max-width: 95%;
        }
        
        .custom-months-input .form-control {
            font-size: 16px;
            height: 45px;
        }
        
        .custom-months-input .btn {
            height: 45px;
            width: 45px;
        }
        
        .quick-selection .btn {
            font-size: 12px;
            padding: 8px 10px;
        }
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Toast notification positioning */
    .position-fixed {
        position: fixed !important;
    }

    /* Loading Animation */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .fa-spinner {
        animation: spin 1s linear infinite;
    }
</style>
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
    
    .bg-primary {
        background-color: #DB2328 !important;
    }
    

    .text-primary {
        color: #DB2328 !important;
    }
</style>
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
    }

    .container {
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