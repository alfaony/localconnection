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
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h4>Total Pembayaran</h4>
                        <h2 class="text-primary">@{{ formatCurrency(grandTotal) }}</h2>
                    </div>
                    
                    <div class="payment-details">
                        <div class="row">
                            <div class="col-6"><strong>Metode Pembayaran:</strong></div>
                            <div class="col-6 text-right">@{{ getPaymentMethodLabel(paymentMethod) }}</div>
                        </div>
                        <div v-if="paymentMethod === 'cash'" class="row mt-2">
                            <div class="col-6"><strong>Dibayar:</strong></div>
                            <div class="col-6 text-right">@{{ formatCurrency(cashAmount) }}</div>
                        </div>
                        <div v-if="paymentMethod === 'cash'" class="row mt-1">
                            <div class="col-6"><strong>Kembalian:</strong></div>
                            <div class="col-6 text-right">@{{ formatCurrency(cashAmount - grandTotal) }}</div>
                        </div>
                        <div v-if="customerEmail" class="row mt-2">
                            <div class="col-6"><strong>Email Customer:</strong></div>
                            <div class="col-6 text-right">@{{ customerEmail }}</div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i> Pastikan semua data sudah benar sebelum melanjutkan.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fas fa-edit"></i> Perbaiki
                    </button>
                    <button type="button" class="btn btn-success" @click="confirmPayment" :disabled="isLoading">
                        <i class="fas fa-check"></i> Konfirmasi & Bayar
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
                                    <th width="10%">Kode</th>
                                    <th width="20%">Nama</th>
                                    <th width="15%">Varian</th>
                                    <th width="20%">Spesifikasi</th>
                                    <th width="12%">Kategori</th>
                                    <th width="12%">Merk</th>
                                    <th width="11%" class="text-right">Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product in productSelectionList" :key="product.id" 
                                    class="product-row" @click="selectProduct(product)" style="cursor: pointer;">
                                    <td><code>@{{ product.code || '-' }}</code></td>
                                    <td><strong>@{{ product.name }}</strong></td>
                                    <td>@{{ product.variant || '-' }}</td>
                                    <td><small>@{{ product.specification || '-' }}</small></td>
                                    <td>@{{ product.category?.name || '-' }}</td>
                                    <td>@{{ product.brand?.name || '-' }}</td>
                                    <td class="text-right"><strong>@{{ formatCurrency(product.selling_price) }}</strong></td>
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
                                    style="max-width: 120px; height: auto; border-radius: 8px;">
                            </div>
                            @endif
                            
                            <h5><strong>{{ $settingCompany['store_name'] ?? config('app.name') }}</strong></h5>
                            
                            @if(!empty($settingCompany['store_address']))
                            <div class="receipt-address">
                                <small class="text-muted">{{ $settingCompany['store_address'] }}</small>
                            </div>
                            @endif
                            
                            <div class="mt-3">
                                <h6><strong>STRUK PENJUALAN</strong></h6>
                                <p class="mb-1"><strong>@{{ transactionResult.transaction_code }}</strong></p>
                                <small>@{{ new Date().toLocaleString('id-ID') }}</small>
                            </div>
                            <hr>
                        </div>
                        
                        <div class="receipt-operator mb-3">
                            <div class="row">
                                <div class="col-6"><strong>Kasir:</strong></div>
                                <div class="col-6 text-right">{{ Auth::user()->name }}</div>
                            </div>
                            <div class="row">
                                <div class="col-6"><strong>Metode Bayar:</strong></div>
                                <div class="col-6 text-right">@{{ getPaymentMethodLabel(transactionResult.payment_method) }}</div>
                            </div>
                            <hr>
                        </div>
                        
                        <div class="receipt-items mb-3">
                            <div v-for="item in transactionResult.items" :key="item.id" class="receipt-item mb-2">
                                <div class="d-flex justify-content-between">
                                    <div class="item-name">
                                        <strong>@{{ item.product_store.name }}</strong>
                                    </div>
                                    <div class="item-total">@{{ formatCurrency(item.subtotal) }}</div>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">@{{ item.quantity }} x @{{ formatCurrency(item.unit_price) }}</small>
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
                                <strong>TOTAL:</strong>
                                <strong>@{{ formatCurrency(transactionResult.final_amount) }}</strong>
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
<style>
    /* Prevent Vue template flash */
    [v-cloak] {
        display: none !important;
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
        border-top: 2px solid #000;
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
        border-top: 2px solid #333;
        margin-top: 10px;
    }
</style>
@endsection

@section('js')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
        const taxValue = ref('{{ $settingCompany["default_tax"] ?? "" }}');
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

        // Computed properties
        const totalItems = computed(() => {
            return cartItems.value.reduce((total, item) => total + item.quantity, 0);
        });

        const subtotal = computed(() => {
            return cartItems.value.reduce((total, item) => total + (item.quantity * item.price), 0);
        });

        const tax = computed(() => {
            const val = Number(taxValue.value) || 0;
            return subtotal.value * (val / 100);
        });

        const grandTotal = computed(() => {
            return subtotal.value + tax.value;
        });

        const canGoToStep2 = computed(() => {
            return cartItems.value.length > 0;
        });

        const canGoToStep3 = computed(() => {
            if (paymentMethod.value === 'cash') {
                return cashAmount.value >= grandTotal.value;
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

        const nextStep = () => {
            if (currentStep.value === 1 && canGoToStep2.value) {
                currentStep.value = 2;
            } else if (currentStep.value === 2 && canGoToStep3.value) {
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
                        addToCart(product);
                    }
                    barcodeInput.value = '';
                    setTimeout(() => {
                        const barcodeInputEl = document.querySelector('input[v-model="barcodeInput"]');
                        if (barcodeInputEl) barcodeInputEl.focus();
                    }, 100);
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
            const existingItemIndex = cartItems.value.findIndex(item => item.id === product.id);
            
            if (existingItemIndex !== -1) {
                cartItems.value[existingItemIndex].quantity += 1;
            } else {
                cartItems.value.push({
                    id: product.id,
                    code: product.code,
                    name: product.name,
                    price: product.selling_price,
                    originalPrice: product.selling_price, // Store original price
                    quantity: 1
                });
            }
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
            cartItems.value[index].quantity = newQuantity;
        };

        const validateQuantity = (item) => {
            if (item.quantity < 1) item.quantity = 1;
        };

        const removeItem = (index) => {
            cartItems.value.splice(index, 1);
        };

        const confirmPayment = async () => {
            $('#paymentConfirmationModal').modal('hide');
            setLoading(true, 'Memproses pembayaran...');
            await processPayment();
        };

        const processPayment = async () => {
            try {
                const items = cartItems.value.map(item => ({
                    product_store_id: item.id,
                    quantity: item.quantity,
                    unit_price: item.price
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
                // alert('Error processing payment: ' + (error.response?.data?.message || error.message));
                setLoadingResult('Error processing payment', error.response?.data?.message || error.message, false);
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
                    quantity: item.quantity,
                    unit_price: item.price
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
            
            // Focus back to barcode input after selection
            setTimeout(() => {
                const barcodeInputEl = document.querySelector('input[v-model="barcodeInput"]');
                if (barcodeInputEl) barcodeInputEl.focus();
            }, 300);
        };

        const resetTransaction = () => {
            cartItems.value = [];
            cashAmount.value = 0;
            customerEmail.value = '';
            taxValue.value = 0;
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
                const headerImage = '{{ $settingCompany["header_store_image"] ?? "" }}';
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
                                    size: 80mm auto;
                                    margin: 0;
                                }
                                
                                body { 
                                    font-family: 'Courier New', monospace; 
                                    width: 80mm;
                                    margin: 0 auto;
                                    padding: 10mm 5mm;
                                    font-size: 11px; 
                                    line-height: 1.4;
                                    color: #000;
                                }
                                
                                /* Header Section - Matching Modal */
                                .header { 
                                    text-align: center; 
                                    margin-bottom: 15px;
                                }
                                
                                .receipt-logo {
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                    margin-bottom: 10px;
                                }
                                
                                .receipt-logo img {
                                    max-width: 120px;
                                    height: auto;
                                    border-radius: 8px;
                                }
                                
                                .company-name {
                                    font-size: 14px;
                                    font-weight: bold;
                                    margin-bottom: 8px;
                                }
                                
                                .receipt-address {
                                    margin-top: 8px;
                                    font-size: 10px;
                                    color: #666;
                                }
                                
                                .receipt-title {
                                    margin-top: 15px;
                                    margin-bottom: 5px;
                                }
                                
                                .receipt-title h6 {
                                    font-size: 12px;
                                    font-weight: bold;
                                    margin-bottom: 5px;
                                }
                                
                                .transaction-code {
                                    font-weight: bold;
                                    margin-bottom: 3px;
                                }
                                
                                .date-time {
                                    font-size: 10px;
                                    color: #666;
                                }
                                
                                hr {
                                    border: none;
                                    border-top: 1px solid #000;
                                    margin: 10px 0;
                                }
                                
                                /* Operator Section - Matching Modal */
                                .receipt-operator {
                                    margin-bottom: 15px;
                                }
                                
                                .info-row {
                                    display: flex;
                                    justify-content: space-between;
                                    margin-bottom: 3px;
                                    font-size: 10px;
                                }
                                
                                .info-row strong {
                                    font-weight: bold;
                                }
                                
                                /* Items Section - Matching Modal */
                                .receipt-items {
                                    margin-bottom: 15px;
                                }
                                
                                .receipt-item { 
                                    margin-bottom: 10px;
                                }
                                
                                .item-header {
                                    display: flex; 
                                    justify-content: space-between;
                                    margin-bottom: 2px;
                                }
                                
                                .item-name {
                                    font-weight: bold;
                                    flex: 1;
                                    padding-right: 10px;
                                }
                                
                                .item-total {
                                    font-weight: bold;
                                    white-space: nowrap;
                                }
                                
                                .item-details {
                                    display: flex;
                                    justify-content: space-between;
                                    font-size: 9px;
                                    color: #666;
                                }
                                
                                /* Totals Section - Matching Modal */
                                .receipt-totals {
                                    margin-top: 15px;
                                }
                                
                                .total-row {
                                    display: flex;
                                    justify-content: space-between;
                                    margin-bottom: 4px;
                                    font-size: 10px;
                                }
                                
                                .total-line { 
                                    border-top: 2px solid #000; 
                                    padding-top: 8px;
                                    margin-top: 8px;
                                    font-weight: bold;
                                    font-size: 11px;
                                }
                                
                                /* Footer Section - Matching Modal */
                                .footer-section {
                                    margin-top: 15px;
                                    text-align: center;
                                }
                                
                                .footer-message {
                                    font-size: 10px;
                                    line-height: 1.5;
                                }
                                
                                /* Print-specific styles */
                                @media print {
                                    body { 
                                        margin: 0;
                                        padding: 5mm 3mm;
                                    }
                                    .no-print { 
                                        display: none; 
                                    }
                                }
                            </style>
                        </head>
                        <body>
                            <!-- Header dengan Logo - Matching Modal -->
                            <div class="header">
                                ${headerImage ? `
                                    <div class="receipt-logo">
                                        <img src="storage/${headerImage}" alt="${companyName}">
                                    </div>
                                ` : ''}
                                
                                <div class="company-name"><strong>${companyName}</strong></div>
                                
                                ${companyAddress ? `
                                    <div class="receipt-address">
                                        <small>${companyAddress}</small>
                                    </div>
                                ` : ''}
                                
                                <div class="receipt-title">
                                    <h6><strong>STRUK PENJUALAN</strong></h6>
                                    <p class="transaction-code"><strong>${transactionResult.value.transaction_code}</strong></p>
                                    <small class="date-time">${new Date().toLocaleString('id-ID')}</small>
                                </div>
                                <hr>
                            </div>
                            
                            <!-- Operator Info - Matching Modal -->
                            <div class="receipt-operator">
                                <div class="info-row">
                                    <div><strong>Kasir:</strong></div>
                                    <div>{{ Auth::user()->name }}</div>
                                </div>
                                <div class="info-row">
                                    <div><strong>Metode Bayar:</strong></div>
                                    <div>${getPaymentMethodLabel(transactionResult.value.payment_method)}</div>
                                </div>
                                <hr>
                            </div>
                            
                            <!-- Items - Matching Modal -->
                            <div class="receipt-items">
                                ${transactionResult.value.items.map(item => `
                                    <div class="receipt-item">
                                        <div class="item-header">
                                            <div class="item-name">
                                                <strong>${item.product_store.name}</strong>
                                            </div>
                                            <div class="item-total">${formatCurrency(item.subtotal)}</div>
                                        </div>
                                        <div class="item-details">
                                            <small>${item.quantity} x ${formatCurrency(item.unit_price)}</small>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                            
                            <hr>
                            
                            <!-- Totals - Matching Modal -->
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
                                    <strong>TOTAL:</strong>
                                    <strong>${formatCurrency(transactionResult.value.final_amount)}</strong>
                                </div>
                                ${transactionResult.value.payment_method === 'cash' ? `
                                    <div class="total-row" style="margin-top: 8px;">
                                        <span>Dibayar:</span>
                                        <span>${formatCurrency(transactionResult.value.payment_details?.cash_amount || cashAmount.value)}</span>
                                    </div>
                                    <div class="total-row">
                                        <span>Kembalian:</span>
                                        <span>${formatCurrency((transactionResult.value.payment_details?.cash_amount || cashAmount.value) - transactionResult.value.final_amount)}</span>
                                    </div>
                                ` : ''}
                            </div>
                            
                            <!-- Footer Message - Matching Modal -->
                            <div class="footer-section">
                                ${footerMessage ? `
                                    <div class="footer-message">${footerMessage}</div>
                                ` : `
                                    <small>Terima kasih atas kunjungan Anda</small>
                                `}
                            </div>
                        </body>
                    </html>
                `;
                
                printWindow.document.write(receiptContent);
                printWindow.document.close();
                
                // Wait for content to load before printing
                printWindow.onload = function() {
                    printWindow.focus();
                    printWindow.print();
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

        // Keyboard shortcut for space bar
        const handleKeyPress = (event) => {
            if (event.code === 'Space' && event.target.tagName !== 'INPUT' && event.target.tagName !== 'TEXTAREA') {
                event.preventDefault();
                nextStep();
            }
        };

        onMounted(() => {
            // Auto-focus barcode input
            const barcodeInputEl = document.querySelector('input[v-model="barcodeInput"]');
            if (barcodeInputEl) {
                barcodeInputEl.focus();
            }

            // Add keyboard event listener
            document.addEventListener('keypress', handleKeyPress);

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
            totalItems,
            subtotal,
            tax,
            grandTotal,
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
            sendReceiptByEmail
        };
    }
}).mount('#app');
</script>
@endsection