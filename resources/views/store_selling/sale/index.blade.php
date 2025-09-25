@extends('adminlte::page')

@section('title', 'Store Selling')

@section('content')
<div id="app">
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs" id="saleTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" @click="resetTransaction" id="new-tab" data-toggle="tab" href="#new" role="tab" aria-controls="new" aria-selected="true">
                Transaksi Baru
            </a>
        </li>
        <li class="nav-item" v-for="draft in drafts" :key="draft.id">
            <a class="nav-link" :id="'draft-' + draft.id + '-tab'" data-toggle="tab" 
               :href="'#draft-' + draft.id" role="tab" :aria-controls="'draft-' + draft.id" 
               aria-selected="false" @click="loadDraft(draft)">
                Draft @{{ draft.transaction_code }}
                <button type="button" class="close ml-2" @click.stop="deleteDraft(draft.id)">&times;</button>
            </a>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="saleTabsContent">
        <!-- New Transaction Tab -->
        <div class="tab-pane fade show active" id="new" role="tabpanel" aria-labelledby="new-tab">
            @include('store_selling.sale.partials.transaction-form')
        </div>

        <!-- Draft Tabs -->
        <div class="tab-pane fade" v-for="draft in drafts" :key="draft.id" 
             :id="'draft-' + draft.id" role="tabpanel" :aria-labelledby="'draft-' + draft.id + '-tab'">
            @include('store_selling.sale.partials.transaction-form')
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">Transaksi Berhasil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                    <h4>@{{ formatCurrency(transactionResult.final_amount) }}</h4>
                    <p>Kode Transaksi: <strong>@{{ transactionResult.transaction_code }}</strong></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" @click="printReceipt">
                        <i class="fas fa-print"></i> Cetak Struk
                    </button>
                    <button class="btn btn-success" @click="sendReceiptByEmail">
                        <i class="fas fa-envelope"></i> Kirim Email
                    </button>
                    <button class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .table td {
        vertical-align: middle;
    }
    .input-group input[type="number"] {
        -moz-appearance: textfield;
    }
    .input-group input[type="number"]::-webkit-outer-spin-button,
    .input-group input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .nav-tabs .close {
        font-size: 1.2rem;
        line-height: 1;
    }
    .tax-input-group {
        max-width: 120px;
    }
</style>
@endsection

@section('js')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
<!-- jQuery (wajib sebelum Bootstrap JS) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap Bundle (sudah termasuk Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const { createApp, ref, computed, onMounted } = Vue;

createApp({
    setup() {
        const operatorName = ref('');
        const barcodeInput = ref('');
        const cartItems = ref([]);
        const paymentMethod = ref('cash');
        const cashAmount = ref(0);
        const customerEmail = ref('');
        const taxValue = ref(10); // Default tax 10%
        const paymentDetails = ref({
            cardNumber: '',
            cvv: '',
            expiry: '',
            qrisCode: ''
        });
        const transactionResult = ref({});
        const drafts = ref(@json($drafts));
        const currentDraftId = ref(null);

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

        const canProcessPayment = computed(() => {
            if (cartItems.value.length === 0) return false;
            if (paymentMethod.value === 'cash') {
                return cashAmount.value >= grandTotal.value;
            }
            return true;
        });

        // Methods
        const formatCurrency = (amount) => {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(amount);
        };

        const searchProduct = async () => {
            if (!barcodeInput.value.trim()) return;

            try {
                const response = await axios.post('/store-selling/search-product', {
                    barcode: barcodeInput.value.trim()
                });

                if (response.data.success) {
                    const product = response.data.product;
                    addToCart(product);
                    barcodeInput.value = '';
                    // Auto-focus kembali ke input barcode
                    // document.querySelector('input[v-model="barcodeInput"]').focus();
                    if(barcodeInput.value) 
                    {
                        barcodeInput.value.focus();   
                    }
                } else {
                    alert('Produk tidak ditemukan');
                }
            } catch (error) {
                console.error('Error mencari produk:', error);
                alert('Error mencari produk');
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
                    quantity: 1
                });
            }
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

        const processPayment = async () => {
            try {
                const items = cartItems.value.map(item => ({
                    product_store_id: item.id,
                    quantity: item.quantity,
                    unit_price: item.price
                }));

                const response = await axios.post('/store-selling/process-payment', {
                    items: items,
                    payment_method: paymentMethod.value,
                    customer_email: customerEmail.value,
                    payment_details: paymentDetails.value,
                    tax_value: taxValue.value
                });

                if (response.data.success) {
                    transactionResult.value = response.data.sale;
                    $('#successModal').modal('show');
                    
                    // Reset form setelah sukses
                    resetTransaction();
                    
                    // Refresh drafts list
                    loadDrafts();
                } else {
                    alert('Error processing payment: ' + response.data.message);
                }
            } catch (error) {
                console.error('Error processing payment:', error);
                alert('Error processing payment: ' + error.response?.data?.message || error.message);
            }
        };

        const saveDraft = async () => {
            if (cartItems.value.length === 0) {
                alert('Tidak ada item untuk disimpan sebagai draft');
                return;
            }
            
            console.log(taxValue.value);
            

            try {
                const items = cartItems.value.map(item => ({
                    product_store_id: item.id,
                    quantity: item.quantity,
                    unit_price: item.price
                }));

                const response = await axios.post('/store-selling/save-draft', {
                    items: items,
                    payment_method: paymentMethod.value,
                    customer_email: customerEmail.value,
                    payment_details: paymentDetails.value,
                    tax_value: taxValue.value
                });

                if (response.data.success) {
                    // Tambahkan draft ke list
                    drafts.value.unshift(response.data.draft);
                    
                    // Reset current transaction
                    resetTransaction();
                    
                    // Switch to the new draft tab
                    setTimeout(() => {
                        $(`#draft-${response.data.draft.id}-tab`).tab('show');
                    }, 100);
                    
                    alert('Draft berhasil disimpan');
                } else {
                    alert('Error menyimpan draft: ' + response.data.message);
                }
            } catch (error) {
                console.error('Error saving draft:', error);
                alert('Error menyimpan draft: ' + error.response?.data?.message || error.message);
            }
        };

        const loadDraft = async (draft) => {
            try {
                const response = await axios.get(`/store-selling/load-draft/${draft.id}`);
                if (response.data.success) {
                    const draftData = response.data.draft;
                    console.log('draftData', draftData);
                    
                    
                    // Load data dari draft
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
                    
                    // Load payment details jika ada
                    if (draftData.payment_details) {
                        paymentDetails.value = { ...draftData.payment_details };
                    }
                }
            } catch (error) {
                console.error('Error loading draft:', error);
                alert('Error loading draft');
            }
        };

        const deleteDraft = async (draftId) => {
            if (!confirm('Hapus draft ini?')) return;

            try {
                const response = await axios.delete(`/store-selling/delete-draft/${draftId}`);
                if (response.data.success) {
                    // Remove dari list drafts
                    drafts.value = drafts.value.filter(draft => draft.id !== draftId);
                    
                    // Jika draft yang dihapus sedang aktif, reset form
                    if (currentDraftId.value === draftId) {
                        resetTransaction();
                        $('#new-tab').tab('show');
                    }
                    
                    alert('Draft berhasil dihapus');
                }
            } catch (error) {
                console.error('Error deleting draft:', error);
                alert('Error menghapus draft');
            }
        };

        const loadDrafts = async () => {
            try {
                const response = await axios.get('/store-selling/drafts');
                if (response.data.success) {
                    drafts.value = response.data.drafts;
                }
            } catch (error) {
                console.error('Error loading drafts:', error);
            }
        };

        const resetTransaction = () => {
            cartItems.value = [];
            cashAmount.value = 0;
            customerEmail.value = '';
            taxValue.value = 10;
            paymentMethod.value = 'cash';
            paymentDetails.value = {
                cardNumber: '',
                cvv: '',
                expiry: '',
                qrisCode: ''
            };
            currentDraftId.value = null;
        };

        const printReceipt = async () => {
            try {
                const response = await axios.get(`/store-selling/print-receipt/${transactionResult.value.id}`);
                // Implement print logic here
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Struk ${transactionResult.value.transaction_code}</title>
                            <style>
                                body { font-family: Arial, sans-serif; margin: 20px; }
                                .header { text-align: center; margin-bottom: 20px; }
                                .item { margin-bottom: 10px; }
                                .total { font-weight: bold; margin-top: 10px; }
                            </style>
                        </head>
                        <body>
                            <div class="header">
                                <h2>STRUK PENJUALAN</h2>
                                <p>${transactionResult.value.transaction_code}</p>
                            </div>
                            <!-- Isi struk -->
                        </body>
                    </html>
                `);
                printWindow.print();
            } catch (error) {
                alert('Error printing receipt');
            }
        };

        const sendReceiptByEmail = async () => {
            if (!customerEmail.value) {
                alert('Email customer diperlukan untuk mengirim struk');
                return;
            }
            // Implement email sending logic
            alert('Struk dikirim ke ' + customerEmail.value);
        };

        onMounted(() => {
            // Auto-focus barcode input
            const barcodeInputEl = document.querySelector('input[v-model="barcodeInput"]');
            if (barcodeInputEl) {
                barcodeInputEl.focus();
            }

            // Load drafts on mount
            loadDrafts();
        });

        return {
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
            totalItems,
            subtotal,
            tax,
            grandTotal,
            canProcessPayment,
            formatCurrency,
            searchProduct,
            updateQuantity,
            validateQuantity,
            removeItem,
            processPayment,
            saveDraft,
            loadDraft,
            deleteDraft,
            loadDrafts,
            resetTransaction,
            printReceipt,
            sendReceiptByEmail
        };
    }
}).mount('#app');
</script>
@endsection