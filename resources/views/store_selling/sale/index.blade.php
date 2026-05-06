@extends('adminlte::page')

@section('title', 'Store Selling - Point of Sale')

@section('content')
<div id="app" v-cloak>
    <!-- Loading Overlay -->
    <div v-if="isLoading" class="loading-overlay">
        <div class="loading-content">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <h5 class="text-white">@{{ loadingMessage }}</h5>
        </div>
    </div>

    <!-- Progress Steps -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="steps">
                <div class="step" :class="{ 'active': currentStep === 1, 'completed': currentStep > 1 }">
                    <div class="step-number">1</div>
                    <div class="step-label">Pilih Produk</div>
                </div>
                <div class="step-line" :class="{ 'completed': currentStep > 1 }"></div>
                <div class="step" :class="{ 'active': currentStep === 2, 'completed': currentStep > 2 }">
                    <div class="step-number">2</div>
                    <div class="step-label">Pembayaran</div>
                </div>
                <div class="step-line" :class="{ 'completed': currentStep > 2 }"></div>
                <div class="step" :class="{ 'active': currentStep === 3, 'completed': currentStep > 3 }">
                    <div class="step-number">3</div>
                    <div class="step-label">Selesai</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card">
                <div class="info-item">
                    <i class="fas fa-calendar-alt text-primary"></i>
                    <span class="info-text">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-user text-success"></i>
                    <span class="info-text">{{ Auth::user()->name }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-3" id="saleTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="new-tab" data-toggle="tab" href="#new" role="tab" 
               @click="switchToNewTransaction()">
                <i class="fas fa-plus-circle"></i> Transaksi Baru
            </a>
        </li>
        @canAccess('loadDraft','store_sellings')
        @canAccess('getDrafts','store_sellings')
        <li class="nav-item" v-for="draft in drafts" :key="draft.id">
            <a class="nav-link" :id="'draft-' + draft.id + '-tab'" data-toggle="tab" 
               :href="'#draft-' + draft.id" role="tab" @click="loadDraft(draft)">
                <i class="fas fa-file-alt"></i> @{{ draft.transaction_code }}
                @canAccess('deleteDraft','store_sellings')
                <button type="button" class="close ml-2" @click.stop="deleteDraft(draft.id)">&times;</button>
                @endcanAccess
            </a>
        </li>
        @endcanAccess
        @endcanAccess
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="saleTabsContent">
        <!-- All tabs will use the same transaction form -->
        <div class="tab-pane fade show active" id="new" role="tabpanel" aria-labelledby="new-tab">
            @include('store_selling.sale.partials.transaction-form')
        </div>
        
        <div class="tab-pane fade" v-for="draft in drafts" :key="draft.id" 
             :id="'draft-' + draft.id" role="tabpanel" :aria-labelledby="'draft-' + draft.id + '-tab'">
            @include('store_selling.sale.partials.transaction-form')
        </div>
    </div>

    <!-- Payment Confirmation Modal -->
    <div class="modal fade" id="paymentConfirmationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-exclamation-triangle"></i> Konfirmasi Pembayaran
                    </h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    {{-- Loading cek stok --}}
                    <div v-if="isCheckingStock" class="text-center py-3">
                        <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                        <p class="mt-2 text-muted">Memeriksa ketersediaan stok...</p>
                    </div>

                    <template v-if="!isCheckingStock">

                        {{-- Ringkasan bayar --}}
                        <div class="text-center mb-3">
                            <h4 class="mb-0">Total Pembayaran</h4>
                            <h2 class="text-primary font-weight-bold">@{{ formatCurrency(paymentMethod === 'cash' ? cashRoundedTotal : grandTotal) }}</h2>
                        </div>

                        <div class="payment-details mb-3">
                            <div class="row">
                                <div class="col-6"><p>Metode Pembayaran:</p></div>
                                <div class="col-6 text-right">@{{ getPaymentMethodLabel(paymentMethod) }}</div>
                            </div>
                            <div v-if="paymentMethod === 'cash'" class="row mt-1">
                                <div class="col-6"><p>Dibayar:</p></div>
                                <div class="col-6 text-right">@{{ formatCurrency(cashAmount) }}</div>
                            </div>
                            <div v-if="paymentMethod === 'cash'" class="row mt-1">
                                <div class="col-6"><p>Kembalian:</p></div>
                                <div class="col-6 text-right text-success font-weight-bold">@{{ formatCurrency(cashAmount - cashRoundedTotal) }}</div>
                            </div>
                            <div v-if="customerEmail" class="row mt-1">
                                <div class="col-6"><p>Email Customer:</p></div>
                                <div class="col-6 text-right">@{{ customerEmail }}</div>
                            </div>
                        </div>

                        <hr class="my-2">

                        {{-- Hasil cek stok per item --}}
                        @canAccess('checkStock','store_sellings')
                        <div class="mb-1">
                            <strong><i class="fas fa-boxes mr-1"></i> Info Stok</strong>
                            <small class="text-muted ml-1">(preview — validasi final saat proses bayar)</small>
                        </div>

                        {{-- Gagal ambil data stok --}}
                        <div v-if="stockCheckFailed" class="alert alert-secondary py-2 mb-0">
                            <i class="fas fa-info-circle mr-1"></i>
                            Info stok tidak dapat dimuat. Lanjutkan pembayaran, sistem akan memvalidasi saat proses.
                        </div>
                        @endcanAccess

                        {{-- Hasil per item --}}
                        <template v-if="!stockCheckFailed && stockCheckResults.length > 0">
                            <div v-for="item in stockCheckResults" :key="item.product_store_id"
                                 class="d-flex align-items-center justify-content-between py-1 px-2 rounded mb-1"
                                 :class="!item.ok ? 'bg-danger-light' : 'bg-light'">
                                <div>
                                    <span class="font-weight-bold" style="font-size:0.9rem;">@{{ item.name }}</span>
                                    <span class="text-muted ml-2" style="font-size:0.82rem;">
                                        beli: <strong>@{{ item.requested }} @{{ item.unit }}</strong>
                                    </span>
                                </div>
                                <div class="text-right" style="min-width:180px;">
                                    <span v-if="!item.ok" class="badge badge-danger px-2 py-1">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Kurang — tersedia @{{ item.stock }} @{{ item.unit }}
                                    </span>
                                    <span v-else-if="!item.has_inventory" class="badge badge-secondary px-2 py-1">
                                        <i class="fas fa-question mr-1"></i> Belum didata
                                    </span>
                                    <span v-else class="badge badge-success px-2 py-1">
                                        <i class="fas fa-check mr-1"></i>
                                        Aman (@{{ item.stock }} @{{ item.unit }})
                                    </span>
                                </div>
                            </div>

                            {{-- Warning jika ada stok kurang, tapi TIDAK block tombol --}}
                            <div v-if="!stockCheckAllOk" class="alert alert-warning py-2 mt-2 mb-0">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                <strong>Perhatian:</strong> Beberapa item mungkin stoknya tidak cukup.
                                Sistem akan memvalidasi ulang saat proses pembayaran.
                            </div>
                            <div v-else class="alert alert-success py-2 mt-2 mb-0">
                                <i class="fas fa-check-circle mr-1"></i>
                                Semua stok tersedia. Siap diproses.
                            </div>
                        </template>

                    </template>
                </div>
                <div class="modal-footer">
                    <small class="text-muted mr-auto"><kbd>Spasi</kbd> untuk Konfirmasi & Bayar</small>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-edit"></i> Perbaiki
                    </button>
                    <button type="button" class="btn btn-success"
                            @click="confirmPayment"
                            :disabled="isLoading || isCheckingStock">
                        <i class="fas fa-check" v-if="!isLoading"></i>
                        <i class="fas fa-spinner fa-spin" v-if="isLoading"></i>
                        Konfirmasi & Bayar
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Product Selection Modal -->
    <div class="modal fade" id="productSelectionModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-list"></i> Pilih Produk
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Barcode yang di-scan digunakan oleh beberapa produk. Silakan pilih produk yang sesuai.
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%"></th>
                                    <th width="9%">Kode</th>
                                    <th width="16%">Nama</th>
                                    <th width="12%">Varian</th>
                                    <th width="16%">Spesifikasi</th>
                                    <th width="9%">Kategori</th>
                                    <th width="9%">Merk</th>
                                    <th width="12%" class="text-right">Harga</th>
                                    <th width="12%" class="text-center">Stok</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(product, index) in productSelectionList" :key="product.id"
                                    class="product-row"
                                    :class="[
                                        !product.inventory ? 'table-secondary' : (product.inventory.quantity <= 0 ? 'table-danger' : ''),
                                        index === selectedProductIndex ? 'selected-product-row' : ''
                                    ]"
                                    @click="selectProduct(product)"
                                    style="cursor: pointer;"
                                    :title="!product.inventory ? 'Stok belum didata — tidak bisa dijual' : ''">
                                    <td class="text-center align-middle p-1">
                                        <img v-if="product.primary_media"
                                             :src="product.primary_media.file_url"
                                             class="rounded"
                                             style="width:40px;height:40px;object-fit:cover;"
                                             alt="">
                                        <div v-else
                                             class="rounded bg-light d-flex align-items-center justify-content-center mx-auto"
                                             style="width:40px;height:40px;font-size:1rem;color:#bbb;">
                                            <i class="fas fa-box"></i>
                                        </div>
                                    </td>
                                    <td class="align-middle"><code>@{{ product.code || '-' }}</code></td>
                                    <td class="align-middle"><strong>@{{ product.name }}</strong></td>
                                    <td class="align-middle">@{{ product.variant || '-' }}</td>
                                    <td class="align-middle"><small>@{{ product.specification || '-' }}</small></td>
                                    <td class="align-middle">@{{ product.category?.name || '-' }}</td>
                                    <td class="align-middle">@{{ product.brand?.name || '-' }}</td>
                                    <td class="text-right align-middle"><strong>@{{ formatCurrency(product.selling_price) }}</strong></td>
                                    <td class="text-center align-middle">
                                        <span v-if="!product.inventory" class="badge badge-secondary">
                                            <i class="fas fa-question"></i> Belum didata
                                        </span>
                                        <span v-else-if="product.inventory.quantity <= 0" class="badge badge-danger">
                                            <i class="fas fa-times"></i> Habis
                                        </span>
                                        <span v-else-if="product.inventory.quantity <= 5" class="badge badge-warning">
                                            <i class="fas fa-exclamation"></i> @{{ product.inventory.quantity }} @{{ product.inventory.unit }}
                                        </span>
                                        <span v-else class="badge badge-success">
                                            @{{ product.inventory.quantity }} @{{ product.inventory.unit }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-check-circle"></i> Transaksi Berhasil
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h3>Pembayaran Berhasil Diproses</h3>
                        <p class="lead">Kode Transaksi: <strong>@{{ transactionResult.transaction_code }}</strong></p>
                    </div>
                    
                    <div class="receipt-preview bg-light p-4 rounded">
                        <!-- Header dengan Logo -->
                        <div class="text-center mb-3">
                            @if(!empty($settingCompany['header_store_image']))
                            <div class="receipt-logo mb-3">
                                <img src="{{ s3_asset(true,10,$settingCompany['header_store_image']) }}" 
                                    alt="{{ $settingCompany['store_name'] ?? 'Store Logo' }}"
                                    style="max-width: 75px; height: auto; border-radius: 8px;">
                            </div>
                            @endif
                            
                            <h5>{{ $settingCompany['store_name'] ?? config('app.name') }}</h5>
                            
                            @if(!empty($settingCompany['store_address']))
                            <div class="receipt-address">
                                <small class="text-muted">{{ $settingCompany['store_address'] }}</small>
                            </div>
                            @endif
                            
                            <div class="mt-3">
                                <h6><p>STRUK PENJUALAN</p></h6>
                                <p class="mb-1">@{{ transactionResult.transaction_code }}</p>
                                <small>@{{ new Date().toLocaleString('id-ID') }}</small>
                            </div>
                            <hr>
                        </div>
                        
                        <div class="receipt-operator mb-3">
                            <div class="row">
                                <div class="col-6"><p>Kasir:</p></div>
                                <div class="col-6 text-right">{{ Auth::user()->name }}</div>
                            </div>
                            <div class="row">
                                <div class="col-6"><p>Metode Bayar:</p></div>
                                <div class="col-6 text-right">@{{ getPaymentMethodLabel(transactionResult.payment_method) }}</div>
                            </div>
                            <hr>
                        </div>
                        
                        <div class="receipt-items mb-3">
                            <div v-for="item in transactionResult.items" :key="item.id" class="receipt-item mb-2">
                                <div class="d-flex justify-content-between">
                                    <div class="item-name">
                                        <p>@{{ item.product_store.name }}</p>
                                    </div>
                                    <div class="item-total">@{{ formatCurrency(item.subtotal) }}</div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <template v-if="getSnapshotItem(item.product_store_id) && getSnapshotItem(item.product_store_id).discountPercent > 0">
                                        <small class="text-muted">
                                            @{{ item.quantity }} x
                                            <del>@{{ formatCurrency(getSnapshotItem(item.product_store_id).originalPrice) }}</del>
                                            <span class="text-success ml-1">-@{{ getSnapshotItem(item.product_store_id).discountPercent }}%</span>
                                            = @{{ formatCurrency(item.unit_price) }}
                                        </small>
                                    </template>
                                    <template v-else>
                                        <small class="text-muted">@{{ item.quantity }} x @{{ formatCurrency(item.unit_price) }}</small>
                                    </template>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="receipt-totals">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span>@{{ formatCurrency(transactionResult.total_amount) }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Pajak (@{{ transactionResult.tax_value }}%):</span>
                                <span>@{{ formatCurrency(transactionResult.tax_amount) }}</span>
                            </div>
                            <div class="d-flex justify-content-between total-line">
                                <span>TOTAL:</span>
                                <span>@{{ formatCurrency(transactionResult.final_amount) }}</span>
                            </div>
                            <div v-if="transactionResult.payment_method === 'cash'" class="d-flex justify-content-between mt-2">
                                <span>Dibayar:</span>
                                <span>@{{ formatCurrency(transactionResult.payment_details?.cash_amount || cashAmount) }}</span>
                            </div>
                            <div v-if="transactionResult.payment_method === 'cash'" class="d-flex justify-content-between">
                                <span>Kembalian:</span>
                                <span>@{{ formatCurrency((transactionResult.payment_details?.cash_amount || cashAmount) - transactionResult.final_amount) }}</span>
                            </div>
                        </div>
                        
                        <!-- Footer Message -->
                        <div class="text-center mt-3">
                            @if(!empty($settingCompany['footer_store_message']))
                            <div style="font-size: 0.9em;">{!! $settingCompany['footer_store_message'] !!}</div>
                            @else
                            <small class="text-muted">Terima kasih atas kunjungan Anda</small>
                            @endif
                        </div>
                        
                    </div>
                </div>
                <div class="modal-footer">
                    @canAccess('printReceipt','store_sellings')
                    <small class="text-muted mr-auto"><kbd>Spasi</kbd> untuk Cetak Struk</small>
                    <button class="btn btn-primary" @click="printReceipt" :disabled="isLoading">
                        <i class="fas fa-print"></i> Cetak Struk
                    </button>
                    @endcanAccess

                    @canAccess('sendReceiptByEmail','store_sellings')
                    <button class="btn btn-success" @click="sendReceiptByEmail" 
                            :disabled="!transactionResult.customer_email || isLoading">
                        <i class="fas fa-envelope"></i> Kirim Email
                    </button>
                    @endcanAccess
                    
                    @canAccess('index','store_sellings')
                    <button class="btn btn-outline-secondary" @click="startNewTransaction">
                        <i class="fas fa-plus"></i> Transaksi Baru
                    </button>
                    @endcanAccess
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFGL54KgFHzqc0QLb3+gfRS0lM9F9NTv4M78HaRh4VM3YEH46Q" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* Prevent Vue template flash */
    [v-cloak] {
        display: none !important;
    }

    .bg-danger-light {
        background-color: #fdecea;
        border-left: 3px solid #dc3545;
    }

    .selected-product-row {
        background-color: #cfe2ff !important;
        outline: 2px solid #0d6efd;
    }

    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    
    .loading-content {
        text-align: center;
        background: rgba(0, 0, 0, 0.8);
        padding: 2rem;
        border-radius: 10px;
    }

    /* Progress Steps */
    .steps {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        padding: 1rem 0;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
    }
    
    .step-line {
        width: 100px;
        height: 3px;
        background-color: #dee2e6;
        transition: background-color 0.3s ease;
        margin: 0 10px;
    }
    
    .step-line.completed {
        background-color: #28a745;
    }
    
    .step-number {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background-color: #dee2e6;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .step.active .step-number {
        background-color: #007bff;
        color: white;
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        animation: pulse 2s infinite;
    }
    
    .step.completed .step-number {
        background-color: #28a745;
        color: white;
        transform: scale(1.05);
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3); }
        50% { box-shadow: 0 4px 25px rgba(0, 123, 255, 0.5); }
        100% { box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3); }
    }
    
    .step-label {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 500;
        text-align: center;
    }
    
    .step.active .step-label {
        color: #007bff;
        font-weight: 600;
    }
    
    .step.completed .step-label {
        color: #28a745;
        font-weight: 600;
    }

    /* Info Card */
    .info-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 10px;
        padding: 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .info-item {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
        padding: 0.5rem;
        border-radius: 5px;
        background: rgba(0,0,0,0.02);
    }
    
    .info-item:last-child {
        margin-bottom: 0;
    }
    
    .info-item i {
        margin-right: 0.75rem;
        font-size: 1.1rem;
    }
    
    .info-text {
        font-weight: 600;
    }

    /* Step Content */
    .step-content {
        display: none;
        animation: fadeIn 0.5s ease-in-out;
    }
    
    .step-content.active {
        display: block;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Cards Enhancement */
    .card {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
        border-radius: 10px;
        transition: transform 0.2s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
    }
    
    .card-header {
        border-radius: 10px 10px 0 0 !important;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }

    /* Table Styles */
    .table td {
        vertical-align: middle;
        border-top: 1px solid rgba(0,0,0,0.05);
        padding: 0.75rem;
    }
    
    .table thead th {
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        padding: 0.75rem;
    }
    
    .input-group input[type="number"] {
        -moz-appearance: textfield;
    }
    
    .input-group input[type="number"]::-webkit-outer-spin-button,
    .input-group input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Price Input Styles */
    .price-input {
        font-weight: 600;
        background-color: #f8fff9;
        border: 1px solid #d4edda;
        transition: all 0.2s ease;
    }
    
    .price-input:focus {
        background-color: #ffffff;
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.15);
        outline: none;
    }
    
    .price-input:hover {
        background-color: #ffffff;
        border-color: #28a745;
        cursor: pointer;
    }

    /* Receipt Styles */
    .receipt-preview {
        font-family: 'Courier New', monospace;
        background: white !important;
        border: 2px solid #dee2e6;
    }
    
    .receipt-item {
        border-bottom: 1px dashed #dee2e6;
        padding: 8px 0;
    }
    
    .receipt-item:last-child {
        border-bottom: none;
    }
    
    .total-line {
        border-top: 1px solid #edededfc;
        padding-top: 8px;
        margin-top: 8px;
        font-size: 1.1em;
    }

    /* Navigation Tabs */
    .nav-tabs .close {
        font-size: 1.2rem;
        line-height: 1;
        margin-left: 5px;
        transition: color 0.2s ease;
    }
    
    .nav-tabs .close:hover {
        color: #dc3545;
    }

    /* Payment Method Cards */
    .payment-method-card {
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 15px;
        background: white;
    }
    
    .payment-method-card:hover {
        border-color: #007bff;
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
    }
    
    .payment-method-card.active {
        border-color: #007bff;
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
    }
    
    .payment-method-card i {
        font-size: 1.8rem;
        margin-right: 15px;
    }

    /* Button Enhancements */
    .btn {
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .btn-lg {
        padding: 12px 24px;
        font-weight: 600;
    }

    /* Animations */
    .animate__animated {
        animation-duration: 0.5s;
    }
    
    .animate__fadeIn {
        animation-name: fadeIn;
    }

    /* Responsive Improvements */
    @media (max-width: 768px) {
        .steps {
            flex-direction: column;
        }
        
        .step-line {
            width: 3px;
            height: 30px;
            margin: 10px 0;
        }
        
        .payment-method-card {
            padding: 15px;
        }
        
        .payment-method-card i {
            font-size: 1.5rem;
            margin-right: 10px;
        }
    }

    /* Product Selection Table Styles */
    .product-row {
        transition: all 0.2s ease;
    }
    
    .product-row:hover {
        background-color: #e3f2fd !important;
        transform: translateX(5px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>
<style scoped>
    .receipt-logo {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .receipt-address {
        margin-top: 8px;
        /* padding: 5px 15px; */
        /* background: #f8f9fa; */
        border-radius: 5px;
        display: inline-block;
    }

    .receipt-preview {
        max-width: 400px;
        margin: 0 auto;
        font-family: 'Courier New', monospace;
    }

    .receipt-item {
        border-bottom: 1px dashed #dee2e6;
        padding-bottom: 8px;
    }

    .receipt-item:last-child {
        border-bottom: none;
    }

    .total-line {
        font-size: 1.2em;
        padding-top: 10px;
        border-top: 1px solid #edededfc;
        margin-top: 10px;
    }
</style>
@endsection

@section('js')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const { createApp, ref, computed, onMounted, watch } = Vue;

createApp({
    setup() {
        const currentStep = ref(1);
        const barcodeInput = ref('');
        const cartItems = ref([]);
        const paymentMethod = ref('cash');
        const cashAmount = ref(0);
        const customerEmail = ref('');
        const defaultTaxValue = '{{ $settingCompany["default_tax"] ?? "" }}';
        const taxValue = ref(defaultTaxValue);
        const paymentDetails = ref({
            cardNumber: '',
            bankName: '',
            cardEdcApprover : '',
            qrisBank: ''
        });
        const transactionResult = ref({});
        const drafts = ref(@json($drafts));
        const currentDraftId = ref(null);
        const isLoading = ref(false);
        const loadingMessage = ref('Memproses...');
        const productSelectionList = ref([]);
        const stockCheckResults  = ref([]);
        const stockCheckAllOk    = ref(true);
        const stockCheckFailed   = ref(false);
        const stockCheckFailedMsg = ref('');
        const isCheckingStock    = ref(false);
        const selectedProductIndex = ref(-1);
        const cartSnapshot = ref([]);

        // Computed properties
        const totalItems = computed(() => {
            return cartItems.value.reduce((total, item) => total + item.quantity, 0);
        });

        const effectiveItemPrice = (item) => {
            const disc = item.discountPercent || 0;
            return item.price * (1 - disc / 100);
        };

        const subtotal = computed(() => {
            return cartItems.value.reduce((total, item) => {
                return total + (item.quantity * effectiveItemPrice(item));
            }, 0);
        });

        const tax = computed(() => {
            const val = Number(taxValue.value) || 0;
            return subtotal.value * (val / 100);
        });

        const grandTotal = computed(() => {
            return subtotal.value + tax.value;
        });

        // For cash: round down to nearest 100 (ratusan)
        const cashRoundedTotal = computed(() => {
            return Math.floor(grandTotal.value / 100) * 100;
        });

        const cashDeduction = computed(() => {
            return grandTotal.value - cashRoundedTotal.value;
        });

        const canGoToStep2 = computed(() => {
            return cartItems.value.length > 0;
        });

        const canGoToStep3 = computed(() => {
            if (paymentMethod.value === 'cash') {
                return cashAmount.value >= cashRoundedTotal.value;
            } else if (paymentMethod.value === 'debit_credit') {
                return (
                    paymentDetails.value.cardNumber &&
                    paymentDetails.value.bankName &&
                    paymentDetails.value.cardEdcApprover
                );
            } else if (paymentMethod.value === 'qris') {
                return paymentDetails.value.bankName;
            }
            return true;
        });

        // Methods
        const setLoading = (loading, message = 'Memproses...') => {
            isLoading.value = loading;
            loadingMessage.value = message;
        };

        const setLoadingResult = (header, message, success) => {
            setLoading(false);

            Swal.fire({
                title: header,
                text: message,
                icon: success ? 'success' : 'error',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        }

        const formatCurrency = (amount) => {
            if (!amount) return 'Rp 0';
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(amount);
        };

        const getPaymentMethodLabel = (method) => {
            const labels = {
                'cash': 'Tunai',
                'debit_credit': 'Kartu Debit/Kredit',
                'qris': 'QRIS'
            };
            return labels[method] || method;
        };

        const nextStep = async () => {
            if (currentStep.value === 1 && canGoToStep2.value) {
                currentStep.value = 2;
            } else if (currentStep.value === 2 && canGoToStep3.value) {
                // Check stock dari DB sebelum buka modal konfirmasi
                isCheckingStock.value  = true;
                stockCheckResults.value = [];
                stockCheckAllOk.value  = true;
                stockCheckFailed.value = false;

                try {
                    const items = cartItems.value.map(item => ({
                        product_store_id: item.id,
                        quantity: item.quantity,
                    }));
                    const response = await axios.post('/store-selling/checkStock', { items });
                    stockCheckResults.value = response.data.results;
                    stockCheckAllOk.value   = response.data.all_ok;
                } catch (e) {
                    // checkStock gagal (500, network, dll) → tetap buka modal
                    // processPayment adalah gatekeeper yang sebenarnya
                    const status  = e?.response?.status;
                    const errBody = e?.response?.data;
                    console.error('[checkStock] status:', status, 'body:', errBody, 'error:', e);
                    stockCheckFailed.value     = true;
                    stockCheckFailedMsg.value  = `HTTP ${status ?? 'network'}: ${JSON.stringify(errBody ?? e?.message)}`;
                    stockCheckAllOk.value  = true; // jangan block tombol
                } finally {
                    isCheckingStock.value = false;
                }

                $('#paymentConfirmationModal').modal('show');
            }
        };

        const prevStep = () => {
            if (currentStep.value > 1) {
                currentStep.value--;
            }
        };

        const searchProduct = async () => {
            if (!barcodeInput.value.trim()) return;

            setLoading(true, 'Mencari produk...');

            try {
                const response = await axios.post('/store-selling/searchProduct', {
                    barcode: barcodeInput.value.trim()
                });

                if (response.data.success) {
                    // Check if multiple products returned
                    if (response.data.multiple) {
                        productSelectionList.value = response.data.products;
                        $('#productSelectionModal').modal('show');
                    } else {
                        // Single product, add directly to cart
                        const product = response.data.product;
                        const result  = addToCart(product);

                        // Notifikasi sukses dengan gambar produk — hanya jika produk berhasil masuk cart
                        if (result && product.inventory) {
                            const sisa = product.inventory.quantity - 1;
                            const imageUrl = product.primary_media?.file_url ?? null;
                            Swal.fire({
                                icon: 'success',
                                title: product.name,
                                html: `Ditambahkan ke keranjang.<br>` +
                                      `Stok tersedia: <b>${product.inventory.quantity} ${result.unit}</b> &nbsp;|&nbsp; ` +
                                      `Sisa setelah ini: <b>${sisa} ${result.unit}</b>`,
                                ...(imageUrl ? {
                                    imageUrl: imageUrl,
                                    imageWidth: 200,
                                    imageHeight: 200,
                                    imageAlt: product.name,
                                } : {}),
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                                position: 'center',
                            });
                        }
                    }
                    barcodeInput.value = '';
                    setTimeout(() => focusBarcodeInput(), 100);
                } else {
                    setLoadingResult('Produk tidak ditemukan', response.data.message, false);
                }
            } catch (error) {
                console.error('Error mencari produk:', error);
                setLoadingResult('Error mencari produk',null, false);
            } finally {
                setLoading(false);
            }
        };

        const addToCart = (product) => {
            // Blok produk yang belum punya data stok (inventory null)
            if (!product.inventory) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stok Belum Didata',
                    html: `<b>${product.name}</b> belum memiliki data stok.<br><br>` +
                          `Silakan input stok produk ini terlebih dahulu di menu <b>Manajemen Stok</b> sebelum bisa dijual.`,
                    confirmButtonText: 'OK, Mengerti',
                    confirmButtonColor: '#f39c12',
                });
                return;
            }

            const stock = product.inventory.quantity;
            const unit  = product.inventory.unit ?? 'pcs';
            const existingItemIndex = cartItems.value.findIndex(item => item.id === product.id);

            if (existingItemIndex !== -1) {
                const newQty = cartItems.value[existingItemIndex].quantity + 1;
                if (newQty > stock) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stok Tidak Cukup',
                        html: `Stok tersedia hanya <b>${stock} ${unit}</b>.`,
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end',
                    });
                    return;
                }
                cartItems.value[existingItemIndex].quantity = newQty;
                return;
            }

            // Produk baru — blok jika stok habis
            if (stock <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Stok Habis',
                    html: `<b>${product.name}</b> saat ini tidak memiliki stok tersedia.`,
                    timer: 2500,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                });
                return;
            }

            cartItems.value.push({
                id:              product.id,
                code:            product.code,
                name:            product.name,
                price:           product.selling_price,
                originalPrice:   product.selling_price,
                quantity:        1,
                stock:           stock,
                unit:            unit,
                image:           product.primary_media?.file_url ?? null,
                discountPercent: 0,
            });

            return { stock, unit }; // digunakan oleh caller untuk toast
        };

        const updatePrice = (index, newPrice) => {
            if (newPrice < 0) {
                cartItems.value[index].price = 0;
                return;
            }
            cartItems.value[index].price = parseFloat(newPrice) || 0;
        };

        const updateQuantity = (index, newQuantity) => {
            if (newQuantity < 1) newQuantity = 1;
            // Biarkan user input melebihi stok — indikator inline yang akan memberi tahu
            cartItems.value[index].quantity = newQuantity;
        };

        const validateQuantity = (item) => {
            if (item.quantity < 1) item.quantity = 1;
            // Tidak clamp ke stok — biarkan melebihi, indikator inline yang menginformasikan
        };

        const removeItem = (index) => {
            cartItems.value.splice(index, 1);
        };

        const updateDiscount = (index, val) => {
            let v = parseFloat(val) || 0;
            if (v < 0) v = 0;
            if (v > 100) v = 100;
            cartItems.value[index].discountPercent = v;
        };

        const getSnapshotItem = (productStoreId) => {
            return cartSnapshot.value.find(s => s.product_store_id === productStoreId) || null;
        };

        const focusBarcodeInput = () => {
            document.getElementById('barcodeSearchInput')?.focus();
        };

        const handleProductModalKeydown = (e) => {
            const list = productSelectionList.value;
            if (!list.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedProductIndex.value = Math.min(selectedProductIndex.value + 1, list.length - 1);
                const rows = document.querySelectorAll('#productSelectionModal .product-row');
                if (rows[selectedProductIndex.value]) {
                    rows[selectedProductIndex.value].scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedProductIndex.value = Math.max(selectedProductIndex.value - 1, 0);
                const rows = document.querySelectorAll('#productSelectionModal .product-row');
                if (rows[selectedProductIndex.value]) {
                    rows[selectedProductIndex.value].scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'Enter' && selectedProductIndex.value >= 0) {
                e.preventDefault();
                selectProduct(list[selectedProductIndex.value]);
            }
        };

        const confirmPayment = async () => {
            $('#paymentConfirmationModal').modal('hide');
            setLoading(true, 'Memproses pembayaran...');
            await processPayment();
        };

        const processPayment = async () => {
            try {
                cartSnapshot.value = cartItems.value.map(item => ({
                    product_store_id: item.id,
                    originalPrice:    item.price,
                    discountPercent:  item.discountPercent || 0,
                    effectivePrice:   effectiveItemPrice(item),
                }));

                const items = cartItems.value.map(item => ({
                    product_store_id: item.id,
                    quantity:         item.quantity,
                    unit_price:       effectiveItemPrice(item),
                    original_price:   item.price,
                    discount_percent: item.discountPercent || 0,
                }));

                if (paymentMethod.value === 'cash') {
                    paymentDetails.value.cash_amount = cashAmount.value;
                }

                const response = await axios.post('/store-selling/processPayment', {
                    items: items,
                    payment_method: paymentMethod.value,
                    customer_email: customerEmail.value,
                    payment_details: paymentDetails.value,
                    tax_value: taxValue.value,
                    draft_id: currentDraftId.value
                });

                if (response.data.success) {
                    transactionResult.value = response.data.sale;
                    
                    // Remove draft tab if processing from draft
                    if (currentDraftId.value) {
                        drafts.value = drafts.value.filter(draft => draft.id !== currentDraftId.value);
                        
                        // Switch to new transaction tab
                        setTimeout(() => {
                            $('#new-tab').tab('show');
                        }, 100);
                    }
                    
                    currentStep.value = 3;
                    setTimeout(() => {
                        $('#successModal').modal('show');
                    }, 500);
                } else {
                    // alert('Error processing payment: ' + response.data.message);
                    setLoadingResult('Error processing payment', response.data.message, false);
                }
            } catch (error) {
                console.error('Error processing payment:', error);
                const errMsg = error.response?.data?.message || error.message;
                // Tampilkan dengan html jika ada tag <br> (misal: error stok)
                if (errMsg && errMsg.includes('<br>')) {
                    setLoading(false);
                    Swal.fire({
                        title: 'Stok Tidak Mencukupi',
                        html: errMsg,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                } else {
                    setLoadingResult('Error memproses pembayaran', errMsg, false);
                }
            } finally {
                setLoading(false);
            }
        };

        const saveDraft = async () => {
            if (cartItems.value.length === 0) {
                // alert('Tidak ada item untuk disimpan sebagai draft');
                setLoadingResult('Tidak ada item untuk disimpan sebagai draft', null, false);
                return;
            }

            setLoading(true, 'Menyimpan draft...');

            try {
                const items = cartItems.value.map(item => ({
                    product_store_id: item.id,
                    quantity:         item.quantity,
                    unit_price:       effectiveItemPrice(item),
                    original_price:   item.price,
                    discount_percent: item.discountPercent || 0,
                }));

                const response = await axios.post('/store-selling/saveDraft', {
                    items: items,
                    payment_method: paymentMethod.value,
                    customer_email: customerEmail.value,
                    payment_details: paymentDetails.value,
                    tax_value: taxValue.value,
                    draft_id: currentDraftId.value
                });

                if (response.data.success) {
                    // Update existing draft or add new draft
                    if (currentDraftId.value) {
                        const existingIndex = drafts.value.findIndex(d => d.id === currentDraftId.value);
                        if (existingIndex !== -1) {
                            drafts.value[existingIndex] = response.data.draft;
                        }
                    } else {
                        drafts.value.unshift(response.data.draft);
                        currentDraftId.value = response.data.draft.id;
                        setTimeout(() => {
                            $(`#draft-${response.data.draft.id}-tab`).tab('show');
                        }, 100);
                    }
                    // alert('Draft berhasil disimpan');
                    setLoadingResult('Draft berhasil disimpan', null, true);
                }
            } catch (error) {
                console.error('Error saving draft:', error);
                // alert('Error menyimpan draft');
                setLoadingResult('Error menyimpan draft', null, false);
            } finally {
                setLoading(false);
            }
        };

        const loadDraft = async (draft) => {
            setLoading(true, 'Memuat draft...');
            
            try {
                const response = await axios.get(`/store-selling/loadDraft/${draft.id}`);
                if (response.data.success) {
                    const draftData = response.data.draft;
                    
                    cartItems.value = draftData.items.map(item => ({
                        id: item.product_store_id,
                        code: item.product_store.code,
                        name: item.product_store.name,
                        price: item.unit_price,
                        quantity: item.quantity
                    }));
                    
                    paymentMethod.value = draftData.payment_method;
                    customerEmail.value = draftData.customer_email || '';
                    taxValue.value = draftData.tax_value;
                    currentDraftId.value = draftData.id;
                    
                    if (draftData.payment_details) {
                        paymentDetails.value = { ...draftData.payment_details };
                        if (draftData.payment_method === 'cash') {
                            cashAmount.value = draftData.payment_details.cash_amount || 0;
                        }
                    }
                    
                    currentStep.value = 1;
                }
            } catch (error) {
                console.error('Error loading draft:', error);
                // alert('Error loading draft');
                setLoadingResult('Error loading draft', null, false);
            } finally {
                setLoading(false);
            }
        };

        const deleteDraft = async (draftId) => {
            if (!confirm('Hapus draft ini?')) return;

            setLoading(true, 'Menghapus draft...');

            try {
                const response = await axios.delete(`/store-selling/deleteDraft/${draftId}`);
                if (response.data.success) {
                    drafts.value = drafts.value.filter(draft => draft.id !== draftId);
                    if (currentDraftId.value === draftId) {
                        resetTransaction();
                        $('#new-tab').tab('show');
                    }
                    // alert('Draft berhasil dihapus');
                    setLoadingResult('Draft berhasil dihapus', null, true);
                }
            } catch (error) {
                console.error('Error deleting draft:', error);
                // alert('Error menghapus draft');
                setLoadingResult('Error menghapus draft', null, false);
            } finally {
                setLoading(false);
            }
        };

        const switchToNewTransaction = () => {
            resetTransaction();
            currentStep.value = 1;
        };

        const startNewTransaction = () => {
            $('#successModal').modal('hide');
            resetTransaction();
            currentStep.value = 1;
            $('#new-tab').tab('show');
        };

        const selectProduct = (product) => {
            addToCart(product);
            $('#productSelectionModal').modal('hide');
            productSelectionList.value = [];
            // Focus dikembalikan otomatis via hidden.bs.modal event
        };

        const resetTransaction = () => {
            cartItems.value = [];
            cashAmount.value = 0;
            customerEmail.value = '';
            taxValue.value = defaultTaxValue;
            paymentMethod.value = 'cash';
            paymentDetails.value = {
                cardNumber: '',
                bankName: '',
                cardEdcApprover : '',
                qrisBank: ''
            };
            currentDraftId.value = null;
        };

        // const printReceipt = async () => {
        //     setLoading(true, 'Menyiapkan struk...');
            
        //     try {
        //         const response = await axios.get(`/store-selling/printReceipt/${transactionResult.value.id}`);
        //         const printWindow = window.open('', '_blank');
        //         const receiptContent = `
        //             <html>
        //                 <head>
        //                     <title>Struk ${transactionResult.value.transaction_code}</title>
        //                     <style>
        //                         body { 
        //                             font-family: 'Courier New', monospace; 
        //                             margin: 20px; 
        //                             font-size: 12px; 
        //                             line-height: 1.4;
        //                         }
        //                         .header { 
        //                             text-align: center; 
        //                             margin-bottom: 20px; 
        //                             border-bottom: 2px solid #000; 
        //                             padding-bottom: 10px; 
        //                         }
        //                         .info-section {
        //                             margin-bottom: 15px;
        //                             border-bottom: 1px dashed #000;
        //                             padding-bottom: 10px;
        //                         }
        //                         .info-row {
        //                             display: flex;
        //                             justify-content: space-between;
        //                             margin-bottom: 3px;
        //                         }
        //                         .item { 
        //                             margin-bottom: 8px; 
        //                             border-bottom: 1px dotted #ccc;
        //                             padding-bottom: 5px;
        //                         }
        //                         .item-header {
        //                             display: flex; 
        //                             justify-content: space-between;
        //                             font-weight: bold;
        //                             margin-bottom: 2px;
        //                         }
        //                         .item-details {
        //                             display: flex;
        //                             justify-content: space-between;
        //                             font-size: 10px;
        //                             color: #666;
        //                         }
        //                         .totals { 
        //                             margin-top: 15px;
        //                             border-top: 1px dashed #000;
        //                             padding-top: 10px;
        //                         }
        //                         .total-row {
        //                             display: flex;
        //                             justify-content: space-between;
        //                             margin-bottom: 3px;
        //                         }
        //                         .grand-total { 
        //                             font-weight: bold; 
        //                             border-top: 2px solid #000; 
        //                             padding-top: 5px; 
        //                             margin-top: 5px; 
        //                             font-size: 14px;
        //                         }
        //                         .payment-info {
        //                             margin-top: 15px;
        //                             border-top: 1px dashed #000;
        //                             padding-top: 10px;
        //                         }
        //                         .thank-you { 
        //                             text-align: center; 
        //                             margin-top: 20px; 
        //                             font-style: italic; 
        //                             font-size: 10px;
        //                         }
        //                         @media print {
        //                             body { margin: 0; font-size: 10px; }
        //                             .no-print { display: none; }
        //                         }
        //                     </style>
        //                 </head>
        //                 <body>
        //                     <div class="header">
        //                         <h2>STRUK PENJUALAN</h2>
        //                         <p><strong>${transactionResult.value.transaction_code}</strong></p>
        //                         <p>${new Date().toLocaleString('id-ID')}</p>
        //                     </div>
                            
        //                     <div class="info-section">
        //                         <div class="info-row">
        //                             <span>Kasir:</span>
        //                             <span>{{ Auth::user()->name }}</span>
        //                         </div>
        //                         <div class="info-row">
        //                             <span>Metode Bayar:</span>
        //                             <span>${getPaymentMethodLabel(transactionResult.value.payment_method)}</span>
        //                         </div>
        //                     </div>
                            
        //                     <div class="items-section">
        //                         ${transactionResult.value.items.map(item => `
        //                             <div class="item">
        //                                 <div class="item-header">
        //                                     <span>${item.product_store.name}</span>
        //                                     <span>${formatCurrency(item.subtotal)}</span>
        //                                 </div>
        //                                 <div class="item-details">
        //                                     <span>${item.quantity} x ${formatCurrency(item.unit_price)}</span>
        //                                 </div>
        //                             </div>
        //                         `).join('')}
        //                     </div>
                            
        //                     <div class="totals">
        //                         <div class="total-row">
        //                             <span>Subtotal:</span>
        //                             <span>${formatCurrency(transactionResult.value.total_amount)}</span>
        //                         </div>
        //                         <div class="total-row">
        //                             <span>Pajak (${transactionResult.value.tax_value}%):</span>
        //                             <span>${formatCurrency(transactionResult.value.tax_amount)}</span>
        //                         </div>
        //                         <div class="total-row grand-total">
        //                             <span>TOTAL:</span>
        //                             <span>${formatCurrency(transactionResult.value.final_amount)}</span>
        //                         </div>
        //                     </div>
                            
        //                     ${transactionResult.value.payment_method === 'cash' ? `
        //                         <div class="payment-info">
        //                             <div class="total-row">
        //                                 <span>Dibayar:</span>
        //                                 <span>${formatCurrency(transactionResult.value.payment_details?.cash_amount || cashAmount.value)}</span>
        //                             </div>
        //                             <div class="total-row">
        //                                 <span>Kembalian:</span>
        //                                 <span>${formatCurrency((transactionResult.value.payment_details?.cash_amount || cashAmount.value) - transactionResult.value.final_amount)}</span>
        //                             </div>
        //                         </div>
        //                     ` : ''}
                            
        //                     <div class="thank-you">
        //                         <p>Terima kasih atas kunjungan Anda</p>
        //                         <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
        //                     </div>
        //                 </body>
        //             </html>
        //         `;
        //         printWindow.document.write(receiptContent);
        //         printWindow.document.close();
        //         printWindow.print();
        //     } catch (error) {
        //         // alert('Error printing receipt');
        //         Log.error('Error printing receipt', error);
        //         setLoadingResult('Error printing receipt', null, false);
        //     } finally {
        //         setLoading(false);
        //     }
        // };
        // const printReceipt = async () => {
        //     setLoading(true, 'Menyiapkan struk...');
            
        //     try {
        //         // Get settings from PHP (already loaded in blade template)
        //         const headerImage = '{{ $settingCompany["header_store_image"] ?? "" }}';
        //         const footerMessage = `{!! $settingCompany["footer_store_message"] ?? "Terima kasih atas kunjungan Anda" !!}`;
        //         const companyName = '{{ $settingCompany["store_name"] ?? "" }}';
        //         const companyAddress = '{{ $settingCompany["store_address"] ?? "" }}';
                
        //         const printWindow = window.open('', '_blank');
        //         const receiptContent = `
        //             <!DOCTYPE html>
        //             <html>
        //                 <head>
        //                     <title>Struk ${transactionResult.value.transaction_code}</title>
        //                     <meta charset="UTF-8">
        //                     <style>
        //                         * {
        //                             margin: 0;
        //                             padding: 0;
        //                             box-sizing: border-box;
        //                         }
                                
        //                         @page {
        //                             size: 80mm auto;
        //                             margin: 0;
        //                         }
                                
        //                         body { 
        //                             font-family: 'Courier New', monospace; 
        //                             width: 80mm;
        //                             margin: 0 auto;
        //                             padding: 10mm 5mm;
        //                             font-size: 11px; 
        //                             line-height: 1.4;
        //                             color: #000;
        //                         }
                                
        //                         /* Header Section */
        //                         .header { 
        //                             text-align: center; 
        //                             margin-bottom: 15px;
        //                             padding-bottom: 10px;
        //                             border-bottom: 2px dashed #000;
        //                         }
                                
        //                         .header-image {
        //                             margin: 0 auto 10px;
        //                             max-width: 50mm;
        //                         }
                                
        //                         .header-image img {
        //                             width: 100%;
        //                             height: auto;
        //                             display: block;
        //                             border: 2px solid #000;
        //                             padding: 3mm;
        //                             background: #fff;
        //                         }
                                
        //                         .company-name {
        //                             font-size: 16px;
        //                             font-weight: bold;
        //                             margin-bottom: 5px;
        //                             text-transform: uppercase;
        //                         }
                                
        //                         .receipt-title {
        //                             font-size: 12px;
        //                             font-weight: bold;
        //                             margin-bottom: 8px;
        //                             letter-spacing: 1px;
        //                         }

        //                         .receipt-address {
        //                             margin-top: 8px;
        //                             border-radius: 5px;
        //                             display: inline-block;
        //                         }
                                
        //                         .transaction-code {
        //                             font-size: 11px;
        //                             font-weight: bold;
        //                             margin: 5px 0;
        //                         }
                                
        //                         .date-time {
        //                             font-size: 10px;
        //                             margin-top: 3px;
        //                         }
                                
        //                         /* Info Section */
        //                         .info-section {
        //                             margin: 12px 0;
        //                             padding: 8px 0;
        //                             border-top: 1px dashed #000;
        //                             border-bottom: 1px dashed #000;
        //                         }
                                
        //                         .info-row {
        //                             display: flex;
        //                             justify-content: space-between;
        //                             margin-bottom: 3px;
        //                             font-size: 10px;
        //                         }
                                
        //                         .info-label {
        //                             font-weight: bold;
        //                         }
                                
        //                         /* Items Section */
        //                         .items-section {
        //                             margin: 12px 0;
        //                         }
                                
        //                         .section-title {
        //                             font-weight: bold;
        //                             font-size: 11px;
        //                             margin-bottom: 8px;
        //                             text-align: center;
        //                             text-transform: uppercase;
        //                             border-top: 1px solid #000;
        //                             border-bottom: 1px solid #000;
        //                             padding: 4px 0;
        //                         }
                                
        //                         .item { 
        //                             margin-bottom: 10px;
        //                             padding-bottom: 8px;
        //                             border-bottom: 1px dotted #666;
        //                         }
                                
        //                         .item:last-child {
        //                             border-bottom: none;
        //                         }
                                
        //                         .item-header {
        //                             display: flex; 
        //                             justify-content: space-between;
        //                             font-weight: bold;
        //                             margin-bottom: 3px;
        //                             font-size: 11px;
        //                         }
                                
        //                         .item-name {
        //                             flex: 1;
        //                             padding-right: 5px;
        //                         }
                                
        //                         .item-details {
        //                             display: flex;
        //                             justify-content: space-between;
        //                             font-size: 9px;
        //                             color: #333;
        //                         }
                                
        //                         /* Totals Section */
        //                         .totals { 
        //                             margin-top: 12px;
        //                             border-top: 1px solid #000;
        //                             padding-top: 8px;
        //                         }
                                
        //                         .total-row {
        //                             display: flex;
        //                             justify-content: space-between;
        //                             margin-bottom: 4px;
        //                             font-size: 10px;
        //                         }
                                
        //                         .grand-total { 
        //                             font-weight: bold; 
        //                             border-top: 2px solid #000; 
        //                             border-bottom: 2px solid #000;
        //                             padding: 6px 0;
        //                             margin: 6px 0;
        //                             font-size: 12px;
        //                         }
                                
        //                         /* Payment Section */
        //                         .payment-info {
        //                             margin: 12px 0;
        //                             padding: 8px 0;
        //                             border-top: 1px dashed #000;
        //                             border-bottom: 1px dashed #000;
        //                         }
                                
        //                         .payment-method {
        //                             font-weight: bold;
        //                             margin-bottom: 6px;
        //                             font-size: 11px;
        //                         }
                                
        //                         /* Footer Section */
        //                         .footer-section {
        //                             margin-top: 15px;
        //                             padding-top: 10px;
        //                             border-top: 2px dashed #000;
        //                         }
                                
        //                         .thank-you-title { 
        //                             text-align: center; 
        //                             font-weight: bold;
        //                             font-size: 12px;
        //                             margin-bottom: 10px;
        //                             text-transform: uppercase;
        //                         }
                                
        //                         .footer-message {
        //                             text-align: center;
        //                             font-size: 10px;
        //                             line-height: 1.6;
        //                             margin: 10px 0;
        //                             padding: 8px;
        //                             border: 1px dashed #666;
        //                         }
                                
        //                         .footer-note { 
        //                             text-align: center;
        //                             font-size: 9px;
        //                             line-height: 1.5;
        //                             margin-top: 10px;
        //                             font-style: italic;
        //                         }
                                
        //                         .footer-note p {
        //                             margin: 3px 0;
        //                         }
                                
        //                         /* Print-specific styles */
        //                         @media print {
        //                             body { 
        //                                 margin: 0;
        //                                 padding: 5mm 3mm;
        //                             }
        //                             .no-print { 
        //                                 display: none; 
        //                             }
        //                         }
        //                     </style>
        //                 </head>
        //                 <body>
        //                     <!-- Header -->
        //                     <div class="header">
        //                         ${headerImage ? `
        //                             <div class="header-image">
        //                                 <img src="${headerImage}" alt="Company Logo">
        //                             </div>
        //                         ` : ''}
        //                         <div class="company-name">${companyName}</div>
        //                         <div class="receipt-title">${companyAddress}</div>
        //                         <div class="receipt-title">STRUK PEMBELIAN</div>
        //                         <div class="transaction-code">${transactionResult.value.transaction_code}</div>
        //                         <div class="date-time">${new Date().toLocaleString('id-ID', {
        //                             day: '2-digit',
        //                             month: 'long',
        //                             year: 'numeric',
        //                             hour: '2-digit',
        //                             minute: '2-digit'
        //                         })}</div>
        //                     </div>
                            
        //                     <!-- Transaction Info -->
        //                     <div class="info-section">
        //                         <div class="info-row">
        //                             <span class="info-label">Kasir</span>
        //                             <span>{{ Auth::user()->name }}</span>
        //                         </div>
        //                         <div class="info-row">
        //                             <span class="info-label">Metode Bayar</span>
        //                             <span>${getPaymentMethodLabel(transactionResult.value.payment_method)}</span>
        //                         </div>
        //                     </div>
                            
        //                     <!-- Items -->
        //                     <div class="items-section">
        //                         <div class="section-title">Detail Pembelian</div>
        //                         ${transactionResult.value.items.map(item => `
        //                             <div class="item">
        //                                 <div class="item-header">
        //                                     <span class="item-name">${item.product_store.name}</span>
        //                                     <span>${formatCurrency(item.subtotal)}</span>
        //                                 </div>
        //                                 <div class="item-details">
        //                                     <span>${item.quantity} x ${formatCurrency(item.unit_price)}</span>
        //                                 </div>
        //                             </div>
        //                         `).join('')}
        //                     </div>
                            
        //                     <!-- Totals -->
        //                     <div class="totals">
        //                         <div class="total-row">
        //                             <span>Subtotal</span>
        //                             <span>${formatCurrency(transactionResult.value.total_amount)}</span>
        //                         </div>
        //                         <div class="total-row">
        //                             <span>Pajak (${transactionResult.value.tax_value}%)</span>
        //                             <span>${formatCurrency(transactionResult.value.tax_amount)}</span>
        //                         </div>
        //                         <div class="total-row grand-total">
        //                             <span>TOTAL</span>
        //                             <span>${formatCurrency(transactionResult.value.final_amount)}</span>
        //                         </div>
        //                     </div>
                            
        //                     <!-- Payment Details -->
        //                     ${transactionResult.value.payment_method === 'cash' ? `
        //                         <div class="payment-info">
        //                             <div class="payment-method">PEMBAYARAN TUNAI</div>
        //                             <div class="total-row">
        //                                 <span>Dibayar</span>
        //                                 <span>${formatCurrency(transactionResult.value.payment_details?.cash_amount || cashAmount.value)}</span>
        //                             </div>
        //                             <div class="total-row">
        //                                 <span>Kembalian</span>
        //                                 <span>${formatCurrency((transactionResult.value.payment_details?.cash_amount || cashAmount.value) - transactionResult.value.final_amount)}</span>
        //                             </div>
        //                         </div>
        //                     ` : ''}
                            
        //                     <!-- Footer -->
        //                     <div class="footer-section">                                
        //                         ${footerMessage ? `
        //                             <div class="footer-message">
        //                                 ${footerMessage}
        //                             </div>
        //                         ` : ''}
        //                     </div>
        //                 </body>
        //             </html>
        //         `;
                
        //         printWindow.document.write(receiptContent);
        //         printWindow.document.close();
                
        //         // Wait for content to load before printing
        //         printWindow.onload = function() {
        //             printWindow.focus();
        //             printWindow.print();
        //         };
                
        //     } catch (error) {
        //         console.error('Error printing receipt:', error);
        //         setLoadingResult('Error printing receipt', error.message, false);
        //     } finally {
        //         setLoading(false);
        //     }
        // };
        const printReceipt = async () => {
            setLoading(true, 'Menyiapkan struk...');
            
            try {
                // Get settings from PHP (already loaded in blade template)
                const headerImage = '{{ !empty($settingCompany["header_store_image"]) ? s3_asset(true, 1440, $settingCompany["header_store_image"]) : "" }}';
                const footerMessage = `{!! $settingCompany["footer_store_message"] ?? "Terima kasih atas kunjungan Anda" !!}`;
                const companyName = '{{ $settingCompany["store_name"] ?? config("app.name") }}';
                const companyAddress = '{{ $settingCompany["store_address"] ?? "" }}';
                
                const printWindow = window.open('', '_blank');
                const receiptContent = `
                    <!DOCTYPE html>
                    <html>
                        <head>
                            <title>Struk ${transactionResult.value.transaction_code}</title>
                            <meta charset="UTF-8">
                            <style>
                                * {
                                    margin: 0;
                                    padding: 0;
                                    box-sizing: border-box;
                                }

                                @page {
                                    size: 46mm auto;
                                    margin: 0;
                                }

                                body {
                                    font-family: Arial;
                                    width: 46mm;
                                    margin: 0;
                                    padding: 2mm 2mm 8mm 2mm;
                                    font-size: 8px;
                                    line-height: 1.3;
                                    color: #000;
                                }

                                .header {
                                    text-align: center;
                                    margin-bottom: 4px;
                                }

                                .receipt-logo {
                                    text-align: center;
                                    margin-bottom: 4px;
                                }

                                .receipt-logo img {
                                    width: 15mm;
                                    height: auto;
                                    display: block;
                                    margin: 0 auto;
                                    object-fit: contain;
                                }

                                .company-name {
                                    font-size: 10px;
                                    font-weight: bold;
                                    margin-bottom: 2px;
                                }

                                .receipt-address {
                                    font-size: 7px;
                                    margin-top: 2px;
                                }

                                .receipt-title {
                                    margin-top: 4px;
                                }

                                .receipt-title h6 {
                                    font-size: 9px;
                                    margin-bottom: 2px;
                                }

                                .transaction-code {
                                    font-size: 8px;
                                    margin-bottom: 1px;
                                }

                                .date-time {
                                    font-size: 7px;
                                }

                                hr {
                                    border: none;
                                    border-top: 1px dashed #000;
                                    margin: 4px 0;
                                }

                                .receipt-operator {
                                    margin-bottom: 4px;
                                }

                                .info-row {
                                    display: flex;
                                    justify-content: space-between;
                                    margin-bottom: 1px;
                                    font-size: 8px;
                                }

                                .receipt-items {
                                    margin-bottom: 4px;
                                }

                                .receipt-item {
                                    margin-bottom: 4px;
                                    border-bottom: 1px dotted #999;
                                    padding-bottom: 3px;
                                }

                                .receipt-item:last-child {
                                    border-bottom: none;
                                }

                                .item-name {
                                    font-size: 8px;
                                    word-break: break-word;
                                    margin-bottom: 1px;
                                }

                                .item-row {
                                    display: flex;
                                    justify-content: space-between;
                                    font-size: 7px;
                                }

                                .item-total {
                                    white-space: nowrap;
                                }

                                .receipt-totals {
                                    margin-top: 2px;
                                }

                                .total-row {
                                    display: flex;
                                    justify-content: space-between;
                                    margin-bottom: 2px;
                                    font-size: 8px;
                                }

                                .total-line {
                                    border-top: 1px solid #000;
                                    padding-top: 3px;
                                    margin-top: 3px;
                                    font-size: 9px;
                                }

                                .footer-section {
                                    margin-top: 6px;
                                    text-align: center;
                                    font-size: 7px;
                                }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                ${headerImage ? `
                                    <div class="receipt-logo">
                                        <img src="${headerImage}" alt="${companyName}" crossorigin="anonymous">
                                    </div>
                                ` : ''}
                                <div class="company-name"><strong>${companyName}</strong></div>
                                ${companyAddress ? `<div class="receipt-address">${companyAddress}</div>` : ''}
                                <div class="receipt-title">
                                    <h6>STRUK PENJUALAN</h6>
                                    <p class="transaction-code">${transactionResult.value.transaction_code}</p>
                                    <span class="date-time">${new Date().toLocaleString('id-ID')}</span>
                                </div>
                            </div>

                            <hr>

                            <div class="receipt-operator">
                                <div class="info-row">
                                    <span>Kasir:</span>
                                    <span>{{ Auth::user()->name }}</span>
                                </div>
                                <div class="info-row">
                                    <span>Bayar:</span>
                                    <span>${getPaymentMethodLabel(transactionResult.value.payment_method)}</span>
                                </div>
                            </div>

                            <hr>

                            <div class="receipt-items">
                                ${transactionResult.value.items.map(item => {
                                    const snap = cartSnapshot.value.find(s => s.product_store_id === item.product_store_id);
                                    const hasDisc = snap && snap.discountPercent > 0;
                                    const priceLabel = hasDisc
                                        ? `<del>${formatCurrency(snap.originalPrice)}</del> -${snap.discountPercent}%`
                                        : formatCurrency(item.unit_price);
                                    const discLine = hasDisc
                                        ? `<div class="item-row" style="color:#555;">
                                               <span>Diskon ${snap.discountPercent}%</span>
                                               <span>-${formatCurrency(snap.originalPrice * snap.discountPercent / 100 * item.quantity)}</span>
                                           </div>`
                                        : '';
                                    return `
                                        <div class="receipt-item">
                                            <div class="item-name">${item.product_store.name}</div>
                                            <div class="item-row">
                                                <span>${item.quantity} x ${priceLabel}</span>
                                                <span class="item-total">${formatCurrency(item.subtotal)}</span>
                                            </div>
                                            ${discLine}
                                        </div>
                                    `;
                                }).join('')}
                            </div>

                            <hr>

                            <div class="receipt-totals">
                                <div class="total-row">
                                    <span>Subtotal:</span>
                                    <span>${formatCurrency(transactionResult.value.total_amount)}</span>
                                </div>
                                <div class="total-row">
                                    <span>Pajak (${transactionResult.value.tax_value}%):</span>
                                    <span>${formatCurrency(transactionResult.value.tax_amount)}</span>
                                </div>
                                <div class="total-row total-line">
                                    <p>TOTAL:</p>
                                    <p>${formatCurrency(transactionResult.value.final_amount)}</p>
                                </div>
                                ${transactionResult.value.payment_method === 'cash' ? `
                                    <div class="total-row" style="margin-top: 4px;">
                                        <span>Dibayar:</span>
                                        <span>${formatCurrency(transactionResult.value.payment_details?.cash_amount || cashAmount.value)}</span>
                                    </div>
                                    <div class="total-row">
                                        <span>Kembalian:</span>
                                        <span>${formatCurrency((transactionResult.value.payment_details?.cash_amount || cashAmount.value) - transactionResult.value.final_amount)}</span>
                                    </div>
                                ` : ''}
                            </div>

                            <hr>

                            <div class="footer-section">
                                ${footerMessage ? `<div>${footerMessage}</div>` : `<span>Terima kasih atas kunjungan Anda</span>`}
                            </div>
                        </body>
                    </html>
                `;
                
                printWindow.document.write(receiptContent);
                printWindow.document.close();

                // Wait for all images to load before printing
                printWindow.onload = function() {
                    const images = printWindow.document.images;
                    if (images.length === 0) {
                        printWindow.focus();
                        printWindow.print();
                        return;
                    }

                    let loadedCount = 0;
                    const total = images.length;
                    const tryPrint = () => {
                        loadedCount++;
                        if (loadedCount >= total) {
                            printWindow.focus();
                            printWindow.print();
                        }
                    };

                    for (let img of images) {
                        if (img.complete) {
                            tryPrint();
                        } else {
                            img.onload = tryPrint;
                            img.onerror = tryPrint; // tetap print meski gambar gagal load
                        }
                    }
                };
                
            } catch (error) {
                console.error('Error printing receipt:', error);
                setLoadingResult('Error printing receipt', error.message, false);
            } finally {
                setLoading(false);
            }
        };

        const sendReceiptByEmail = async () => {
            if (!transactionResult.value.customer_email) {
                setLoadingResult('Email customer diperlukan untuk mengirim struk', null, false);
                return;
            }
            
            setLoading(true, 'Mengirim email...');
            
            try {
                const response = await axios.post('/store-selling/sendReceiptByEmail', {
                    sale_id: transactionResult.value.id,
                    customer_email: transactionResult.value.customer_email
                });

                if (response.data.success) {
                    setLoadingResult('Struk berhasil dikirim ke ' + transactionResult.value.customer_email, null, true);
                } else {
                    setLoadingResult('Gagal mengirim email', response.data.message, false);
                }
            } catch (error) {
                console.error('Error sending email:', error);
                setLoadingResult(
                    'Error mengirim email', 
                    error.response?.data?.message || 'Terjadi kesalahan saat mengirim email', 
                    false
                );
            } finally {
                setLoading(false);
            }
        };

        // Payment methods order for keyboard navigation
        const paymentMethodsList = ['cash', 'debit_credit', 'qris'];

        // Focus first input of the currently selected payment method
        const focusFirstPaymentField = () => {
            let id = null;
            if (paymentMethod.value === 'cash') id = 'cashAmountInput';
            else if (paymentMethod.value === 'debit_credit') id = 'cardNumberInput';
            else if (paymentMethod.value === 'qris') id = 'qrisBankInput';
            if (id) document.getElementById(id)?.focus();
        };

        // Clear the focused payment input and its Vue ref, then blur it
        const clearPaymentField = (el) => {
            const id = el?.id;
            if (id === 'cashAmountInput') cashAmount.value = 0;
            else if (id === 'cardNumberInput') paymentDetails.value.cardNumber = '';
            else if (id === 'cardBankInput') paymentDetails.value.bankName = '';
            else if (id === 'cardEdcInput') paymentDetails.value.cardEdcApprover = '';
            else if (id === 'qrisBankInput') paymentDetails.value.bankName = '';
            el?.blur();
        };

        // Unified keyboard handler
        const handleKeyDown = (event) => {
            const tag = event.target.tagName;
            const isInInput = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';
            const isProductModalOpen = document.getElementById('productSelectionModal')?.classList.contains('show');
            const isConfirmModalOpen  = document.getElementById('paymentConfirmationModal')?.classList.contains('show');

            // Product modal keyboard nav is handled by handleProductModalKeydown — skip here
            if (isProductModalOpen) return;

            // ── SPACE ──────────────────────────────────────────────────────────────
            if (event.code === 'Space') {
                if (isInInput) return; // let normal typing/behaviour happen
                event.preventDefault();
                const isSuccessModalOpen = document.getElementById('successModal')?.classList.contains('show');
                // Block Space while payment is processing
                if (isLoading.value) return;
                if (isSuccessModalOpen) {
                    printReceipt();
                } else if (isConfirmModalOpen) {
                    if (!isCheckingStock.value) confirmPayment();
                } else {
                    nextStep();
                }
                return;
            }

            // ── ESCAPE ─────────────────────────────────────────────────────────────
            if (event.code === 'Escape') {
                if (currentStep.value === 1 && isInInput) {
                    event.target.blur(); // exit barcode input focus
                } else if (currentStep.value === 2 && isInInput) {
                    event.preventDefault();
                    clearPaymentField(event.target); // clear & blur the focused payment field
                }
                return;
            }

            // ── ENTER — Step 2 ────────────────────────────────────────────────────
            if (event.code === 'Enter' && currentStep.value === 2 && !isConfirmModalOpen) {
                event.preventDefault();
                if (isInInput) {
                    // Keluar dari payment field → user bisa tekan Space untuk lanjut
                    event.target.blur();
                } else {
                    // Belum di input → fokus ke field pertama metode yang dipilih
                    focusFirstPaymentField();
                }
                return;
            }

            // ── ARROW KEYS — Step 2 payment method navigation ─────────────────────
            if (currentStep.value === 2 && !isInInput && !isConfirmModalOpen) {
                const currentIdx = paymentMethodsList.indexOf(paymentMethod.value);
                if (event.code === 'ArrowDown') {
                    event.preventDefault();
                    paymentMethod.value = paymentMethodsList[Math.min(currentIdx + 1, paymentMethodsList.length - 1)];
                } else if (event.code === 'ArrowUp') {
                    event.preventDefault();
                    paymentMethod.value = paymentMethodsList[Math.max(currentIdx - 1, 0)];
                }
            }
        };

        onMounted(() => {
            // Auto-focus barcode input
            const barcodeInputEl = document.getElementById('barcodeSearchInput');
            if (barcodeInputEl) barcodeInputEl.focus();

            // Unified keyboard handler (replaces old keypress handler)
            document.addEventListener('keydown', handleKeyDown);

            // Product modal keyboard navigation
            $('#productSelectionModal').on('shown.bs.modal', () => {
                selectedProductIndex.value = 0;
                document.addEventListener('keydown', handleProductModalKeydown);
            });
            $('#productSelectionModal').on('hidden.bs.modal', () => {
                document.removeEventListener('keydown', handleProductModalKeydown);
                selectedProductIndex.value = -1;
                focusBarcodeInput();
            });

            // Load drafts
            setLoading(true, 'Memuat data...');
            axios.get('/store-selling/drafts').then(response => {
                if (response.data.success) {
                    drafts.value = response.data.drafts;
                }
            }).finally(() => {
                setLoading(false);
            });
        });

        return {
            currentStep,
            barcodeInput,
            cartItems,
            paymentMethod,
            cashAmount,
            customerEmail,
            taxValue,
            paymentDetails,
            transactionResult,
            drafts,
            currentDraftId,
            isLoading,
            loadingMessage,
            productSelectionList,
            stockCheckResults,
            stockCheckAllOk,
            stockCheckFailed,
            stockCheckFailedMsg,
            isCheckingStock,
            totalItems,
            subtotal,
            tax,
            grandTotal,
            cashRoundedTotal,
            cashDeduction,
            canGoToStep2,
            canGoToStep3,
            setLoading,
            setLoadingResult,
            formatCurrency,
            getPaymentMethodLabel,
            nextStep,
            prevStep,
            searchProduct,
            selectProduct,
            updateQuantity,
            validateQuantity,
            removeItem,
            confirmPayment,
            processPayment,
            saveDraft,
            loadDraft,
            deleteDraft,
            switchToNewTransaction,
            startNewTransaction,
            resetTransaction,
            printReceipt,
            sendReceiptByEmail,
            selectedProductIndex,
            cartSnapshot,
            effectiveItemPrice,
            updateDiscount,
            getSnapshotItem,
            focusBarcodeInput,
            focusFirstPaymentField,
            clearPaymentField,
            paymentMethodsList,
        };
    }
}).mount('#app');
</script>
@endsection