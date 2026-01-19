<!-- Step 1: Product Selection -->
<div class="step-content" :class="{ 'active': currentStep === 1 }">
    <div class="row">
        <div class="col-md-8">
            <!-- Scanner Section -->
            @canAccess('searchProduct','store_sellings')
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-barcode"></i> Scan Produk
                        <small class="float-right">Tekan <kbd>Spasi</kbd> untuk lanjut</small>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="input-group input-group-lg">
                        <input v-model="barcodeInput"
                            class="form-control" 
                            placeholder="Scan barcode atau ketik kode produk..."
                            @keyup.enter="searchProduct"
                            autofocus>
                        <div class="input-group-append">
                            <button class="btn btn-primary" @click="searchProduct">
                                <i class="fas fa-search"></i> Cari
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endcanAccess

            <!-- Product List -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shopping-cart"></i> Daftar Produk
                        <span class="badge badge-light ml-2">@{{ totalItems }} item</span>
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Produk</th>
                                    <th width="120" class="text-right">Harga</th>
                                    <th width="140">Qty</th>
                                    <th width="120" class="text-right">Subtotal</th>
                                    <th width="80" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in cartItems" :key="item.id" class="animate__animated animate__fadeIn">
                                    <td>
                                        <div class="font-weight-bold">@{{ item.name }}</div>
                                        {{--<small class="text-muted">SKU: @{{ item.code }}</small>--}}
                                    </td>
                                    <td class="text-right">@{{ formatCurrency(item.price) }}</td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <button class="btn btn-outline-secondary" 
                                                    @click="updateQuantity(index, item.quantity - 1)"
                                                    :disabled="item.quantity <= 1">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" 
                                                   class="form-control text-center" 
                                                   v-model.number="item.quantity"
                                                   @change="validateQuantity(item)"
                                                   min="1">
                                            <button class="btn btn-outline-secondary" 
                                                    @click="updateQuantity(index, item.quantity + 1)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="text-right font-weight-bold">@{{ formatCurrency(item.quantity * item.price) }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-danger btn-sm" @click="removeItem(index)"
                                                title="Hapus item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="cartItems.length === 0">
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                        <p>Belum ada produk yang ditambahkan</p>
                                        <small>Gunakan scanner atau input kode produk manual</small>
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
                    <h3 class="card-title"><i class="fas fa-receipt"></i> Ringkasan Transaksi</h3>
                </div>
                <div class="card-body">
                    <div class="summary-item d-flex justify-content-between mb-2">
                        <span>Total Item:</span>
                        <strong>@{{ totalItems }}</strong>
                    </div>
                    <div class="summary-item d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong>@{{ formatCurrency(subtotal) }}</strong>
                    </div>
                    
                    <div class="tax-section mb-3 p-2 bg-light rounded">
                        <div class="form-group mb-2">
                            <label class="mb-1"><small>Pajak (%)</small></label>
                            <input type="number" v-model.number="taxValue" 
                                   class="form-control form-control-sm" 
                                   min="0" max="100" step="0.1">
                        </div>
                        <div class="summary-item d-flex justify-content-between">
                            <span>Nilai Pajak:</span>
                            <strong>@{{ formatCurrency(tax) }}</strong>
                        </div>
                    </div>
                    
                    <hr>
                    <div class="summary-item d-flex justify-content-between mb-0">
                        <span class="font-weight-bold">Grand Total:</span>
                        <strong class="text-success" style="font-size: 1.2em;">@{{ formatCurrency(grandTotal) }}</strong>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @canAccess('processPayment','store_sellings')
                        <button class="btn btn-primary btn-md mb-2" 
                                @click="nextStep"
                                :disabled="!canGoToStep2">
                            <i class="fas fa-arrow-right"></i> Lanjut ke Pembayaran
                            <kbd class="ml-2">Spasi</kbd>
                        </button>
                        @endcanAccess

                        @canAccess('saveDraft','store_sellings')
                        <button class="btn btn-info mb-2 mr-1" @click="saveDraft"
                                :disabled="cartItems.length === 0">
                            <i class="fas fa-save"></i> Simpan Draft
                        </button>
                        @endcanAccess
                        <button class="btn btn-outline-secondary mb-2" @click="resetTransaction">
                            <i class="fas fa-times"></i> Batalkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Step 2: Payment -->
<div class="step-content" :class="{ 'active': currentStep === 2 }">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-credit-card"></i> Pembayaran</h3>
                </div>
                <div class="card-body">
                    <!-- Payment Summary -->
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Total yang harus dibayar:</span>
                            <strong class="h4 mb-0">@{{ formatCurrency(grandTotal) }}</strong>
                        </div>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="form-group">
                        <label class="font-weight-bold">Pilih Metode Pembayaran</label>
                        <div class="payment-methods">
                            <div class="payment-method-card" 
                                 :class="{ 'active': paymentMethod === 'cash' }"
                                 @click="paymentMethod = 'cash'">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave text-success"></i>
                                    <div>
                                        <h6 class="mb-1">Tunai</h6>
                                        <small class="text-muted">Bayar dengan uang tunai</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="payment-method-card" 
                                 :class="{ 'active': paymentMethod === 'debit_credit' }"
                                 @click="paymentMethod = 'debit_credit'">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-credit-card text-primary"></i>
                                    <div>
                                        <h6 class="mb-1">Kartu Debit/Kredit</h6>
                                        <small class="text-muted">Bayar dengan kartu bank</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="payment-method-card" 
                                 :class="{ 'active': paymentMethod === 'qris' }"
                                 @click="paymentMethod = 'qris'">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-qrcode text-info"></i>
                                    <div>
                                        <h6 class="mb-1">QRIS</h6>
                                        <small class="text-muted">Scan QR code untuk pembayaran</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Details -->
                    <div class="payment-details mt-4">
                        <!-- Cash Payment -->
                        <div v-if="paymentMethod === 'cash'" class="form-group">
                            <label class="font-weight-bold">Jumlah Bayar</label>
                            <input type="number" class="form-control form-control-lg" 
                                   v-model="cashAmount" 
                                   placeholder="Masukkan jumlah bayar"
                                   :min="grandTotal">
                            <div v-if="cashAmount > 0" class="mt-3 p-3 bg-light rounded">
                                <div class="d-flex justify-content-between">
                                    <span>Kembalian:</span>
                                    <strong class="h5" 
                                            :class="(cashAmount - grandTotal) >= 0 ? 'text-success' : 'text-danger'">
                                        @{{ formatCurrency(cashAmount - grandTotal) }}
                                    </strong>
                                </div>
                                <div v-if="(cashAmount - grandTotal) < 0" class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-triangle"></i> Jumlah bayar kurang
                                </div>
                            </div>
                        </div>

                        <!-- Card Payment -->
                        <div v-if="paymentMethod === 'debit_credit'">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label>Nomor Kartu</label>
                                        <input type="text" class="form-control" 
                                               v-model="paymentDetails.cardNumber"
                                               placeholder="1234 5678 9012 3456" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Nama Bank</label>
                                        <input type="text" class="form-control" 
                                               v-model="paymentDetails.bankName"
                                               placeholder="BCA" maxlength="3" required>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group">
                                        <label>Nomor EDC Approver</label>
                                        <input type="text" class="form-control" 
                                               v-model="paymentDetails.cardEdcApprover"
                                               placeholder="1234" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- QRIS Payment -->
                        <div v-if="paymentMethod === 'qris'" class="form-group">
                            <label>Nama Bank QRIS</label>
                            <input type="text" class="form-control" 
                                   v-model="paymentDetails.bankName"
                                   placeholder="BCA" required>
                            <small class="text-muted">Gunakan scanner untuk scan QR code</small>
                        </div>
                    </div>

                    <!-- Customer Email -->
                    <div class="form-group mt-4">
                        <label class="font-weight-bold">Email Customer (Opsional)</label>
                        <input type="email" class="form-control" 
                               v-model="customerEmail"
                               placeholder="customer@example.com">
                        <small class="text-muted">Struk akan dikirim ke email ini</small>
                    </div>

                    <!-- Navigation -->
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-success btn-lg " 
                                @click="nextStep"
                                :disabled="!canGoToStep3">
                            <i class="fas fa-check-circle"></i> Konfirmasi Pembayaran
                            <kbd class="ml-2">Spasi</kbd>
                        </button>
                        <button class="btn btn-secondary" @click="prevStep">
                            <i class="fas fa-arrow-left"></i> Kembali ke Produk
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>