<div class="row mt-3">
    <div class="col-md-8">
        <!-- Scanner Section -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Scan Produk</h3>
            </div>
            <div class="card-body">
                <div class="input-group">
                    <input v-model="barcodeInput"
                        class="form-control form-control-lg" 
                           placeholder="Scan barcode atau ketik kode produk..."
                    @keyup.enter="searchProduct">
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
          <div class="card card-secondary mb-3">
            <div class="card-header">
                <h3 class="card-title">Informasi Transaksi</h3>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-6">Operator:</div>
                    <div class="col-6 text-right">@{{ operatorName }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-6">Tanggal:</div>
                    <div class="col-6 text-right">@{{ currentDate }}</div>
                </div>
            </div>
        </div>
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
                    <div class="col-6">
                        <label for="taxValue">Pajak (%):</label>
                    </div>
                    <div class="col-6 text-right">
                        <input type="number" v-model.number="taxValue" class="form-control tax-input-group" 
                               id="taxValue" placeholder="10" min="0" max="100">
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-6">Nilai Pajak:</div>
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
                        <input type="text" class="form-control" v-model="paymentDetails.bankName"
                               placeholder="BCA">
                    </div>
                    <div class="form-group">
                        <label>Nomor Kartu</label>
                        <input type="text" class="form-control" v-model="paymentDetails.cardNumber"
                               placeholder="1234 5678 9012 3456">
                    </div>
                    <div class="form-group">
                        <label>Kode Approval EDC</label>
                        <input type="text" class="form-control" v-model="paymentDetails.approvalCode"
                               placeholder="BCA">
                    </div>
                </div>

                <!-- QRIS Payment -->
                <div v-if="paymentMethod === 'qris'" class="form-group">
                    <label>Kode QRIS</label>
                    <input type="text" class="form-control" v-model="paymentDetails.bankName"
                           placeholder="BCA">
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