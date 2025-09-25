{{-- resources/views/store-selling/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Store Selling')

@section('content')
<div id="app">
    <div class="row">
        <div class="col-md-8">
            <!-- Scanner Section -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Scan Produk</h3>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" 
                               class="form-control form-control-lg" 
                               placeholder="Scan barcode atau ketik kode produk..."
                               v-model="barcodeInput"
                               @keyup.enter="searchProduct"
                               ref="barcodeInput"
                               autofocus>
                        <div class="input-group-append">
                            <button class="btn btn-primary" @click="searchProduct">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product List -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Daftar Produk</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th width="100">Harga</th>
                                    <th width="120">Qty</th>
                                    <th width="120">Subtotal</th>
                                    <th width="80">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in cartItems" :key="item.id">
                                    <td>
                                        <strong>@{{ item.name }}</strong><br>
                                        <small class="text-muted">@{{ item.code }}</small>
                                    </td>
                                    <td>@{{ formatCurrency(item.price) }}</td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary" 
                                                    @click="updateQuantity(index, item.quantity - 1)"
                                                    :disabled="item.quantity <= 1">-</button>
                                            <input type="number" 
                                                   class="form-control text-center" 
                                                   v-model.number="item.quantity"
                                                   @change="validateQuantity(item)"
                                                   min="1">
                                            <button class="btn btn-outline-secondary" 
                                                    @click="updateQuantity(index, item.quantity + 1)">+</button>
                                        </div>
                                    </td>
                                    <td>@{{ formatCurrency(item.quantity * item.price) }}</td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" @click="removeItem(index)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="cartItems.length === 0">
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Belum ada produk yang ditambahkan
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Summary Section -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6">Total Item:</div>
                        <div class="col-6 text-right">@{{ totalItems }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">Subtotal:</div>
                        <div class="col-6 text-right">@{{ formatCurrency(subtotal) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">Pajak (10%):</div>
                        <div class="col-6 text-right">@{{ formatCurrency(tax) }}</div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-6"><strong>Total:</strong></div>
                        <div class="col-6 text-right"><strong>@{{ formatCurrency(grandTotal) }}</strong></div>
                    </div>
                </div>
            </div>

            <!-- Payment Section -->
            <div class="card card-warning" v-if="cartItems.length > 0">
                <div class="card-header">
                    <h3 class="card-title">Pembayaran</h3>
                </div>
                <div class="card-body">
                    <!-- Payment Method Selection -->
                    <div class="form-group">
                        <label>Metode Pembayaran</label>
                        <select class="form-control" v-model="paymentMethod">
                            <option value="cash">Tunai</option>
                            <option value="debit_credit">Kartu Debit/Kredit</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>

                    <!-- Cash Payment -->
                    <div v-if="paymentMethod === 'cash'" class="form-group">
                        <label>Jumlah Bayar</label>
                        <input type="number" class="form-control" v-model="cashAmount" 
                               placeholder="Masukkan jumlah bayar">
                        <div v-if="cashAmount > 0" class="mt-2">
                            <strong>Kembalian: @{{ formatCurrency(cashAmount - grandTotal) }}</strong>
                        </div>
                    </div>

                    <!-- Card Payment -->
                    <div v-if="paymentMethod === 'debit_credit'">
                        <div class="form-group">
                            <label>Nomor Kartu</label>
                            <input type="text" class="form-control" v-model="paymentDetails.cardNumber"
                                   placeholder="1234 5678 9012 3456">
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>CVV</label>
                                    <input type="text" class="form-control" v-model="paymentDetails.cvv"
                                           placeholder="123" maxlength="3">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label>Expiry</label>
                                    <input type="text" class="form-control" v-model="paymentDetails.expiry"
                                           placeholder="MM/YY">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- QRIS Payment -->
                    <div v-if="paymentMethod === 'qris'" class="form-group">
                        <label>Kode QRIS</label>
                        <input type="text" class="form-control" v-model="paymentDetails.qrisCode"
                               placeholder="Scan atau masukkan kode QRIS">
                    </div>

                    <!-- Customer Email -->
                    <div class="form-group">
                        <label>Email Customer (Opsional)</label>
                        <input type="email" class="form-control" v-model="customerEmail"
                               placeholder="customer@example.com">
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <button class="btn btn-success btn-lg" 
                                @click="processPayment"
                                :disabled="!canProcessPayment">
                            <i class="fas fa-credit-card"></i> Bayar
                        </button>
                        <button class="btn btn-info" @click="saveDraft">
                            <i class="fas fa-save"></i> Simpan Draft
                        </button>
                        <button class="btn btn-secondary" @click="resetTransaction">
                            <i class="fas fa-times"></i> Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">Transaksi Berhasil</h5>
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
</style>
@endsection

@section('js')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js"></script>
<script>
const { createApp, ref, computed, onMounted } = Vue;

createApp({
    setup() {
        const barcodeInput = ref('');
        const cartItems = ref([]);
        const paymentMethod = ref('cash');
        const cashAmount = ref(0);
        const customerEmail = ref('');
        const paymentDetails = ref({
            cardNumber: '',
            cvv: '',
            expiry: '',
            qrisCode: ''
        });
        const transactionResult = ref({});

        // Computed properties
        const totalItems = computed(() => {
            return cartItems.value.reduce((total, item) => total + item.quantity, 0);
        });

        const subtotal = computed(() => {
            return cartItems.value.reduce((total, item) => total + (item.quantity * item.price), 0);
        });

        const tax = computed(() => {
            return subtotal.value * 0.1;
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
                } else {
                    alert('Produk tidak ditemukan');
                }
            } catch (error) {
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
                    price: product.price,
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
                    payment_details: paymentDetails.value
                });

                if (response.data.success) {
                    transactionResult.value = response.data.sale;
                    $('#successModal').modal('show');
                } else {
                    alert('Error processing payment: ' + response.data.message);
                }
            } catch (error) {
                alert('Error processing payment');
            }
        };

        const saveDraft = () => {
            // Implement draft saving logic
            alert('Draft disimpan');
        };

        const resetTransaction = () => {
            if (confirm('Batalkan transaksi?')) {
                cartItems.value = [];
                cashAmount.value = 0;
                customerEmail.value = '';
                paymentDetails.value = {
                    cardNumber: '',
                    cvv: '',
                    expiry: '',
                    qrisCode: ''
                };
            }
        };

        const printReceipt = async () => {
            try {
                const response = await axios.get(`/store-selling/print-receipt/${transactionResult.value.id}`);
                // Implement print logic here
                window.print();
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
            document.querySelector('input[ref="barcodeInput"]').focus();
        });

        return {
            barcodeInput,
            cartItems,
            paymentMethod,
            cashAmount,
            customerEmail,
            paymentDetails,
            transactionResult,
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
            resetTransaction,
            printReceipt,
            sendReceiptByEmail
        };
    }
}).mount('#app');
</script>
@endsection