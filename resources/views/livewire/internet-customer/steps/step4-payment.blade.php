<div class="bg-white p-4 rounded-lg shadow-sm">
    <h3 class="section-title">Pembayaran</h3>
    
    @if($hasFreeMonthsPromo)
        <div class="alert alert-success border-start-4 border-success d-flex align-items-center mb-4">
            <i class="fas fa-gift fa-2x me-3"></i>
            <div>
                <h4 class="alert-heading mb-1">Promo Free Months Aktif!</h4>
                <p class="mb-0">
                    Anda mendapatkan <strong>{{ $freeMonthsDetails->value }} bulan gratis</strong>.
                    Pembayaran akan dimulai pada bulan <strong>{{ $paymentStartMonth }}</strong>.
                </p>
            </div>
        </div>
    @endif
    
    <div class="card border-0 bg-light-subtle mb-4">
        <div class="card-body">
            <h3 class="h5 card-title mb-3 fw-semibold">Detail Tagihan</h3>
            
            <div class="d-flex justify-content-between mb-2">
                <span>Paket Internet:</span>
                <span class="fw-semibold">{{ $selectedPackage->name ?? '-' }}</span>
            </div>
            
            <div class="d-flex justify-content-between mb-2">
                <span>Harga per Bulan:</span>
                <span class="fw-semibold">Rp {{ number_format($monthlyPrice, 0, ',', '.') }}</span>
            </div>

            @if(!$hasFreeMonthsPromo)
                <div class="d-flex justify-content-between mb-2">
                    <span>Jumlah Bulan:</span>
                    <span class="fw-semibold">{{ $payment_months }} bulan</span>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span class="fw-semibold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>

                @if($discountAmount > 0)
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Diskon ({{ $discountPercentage }}%):</span>
                        <span class="fw-semibold">- Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                    </div>
                @endif
                
                {{-- Always show PPN breakdown for transparency --}}
                @if($taxRate > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Harga sebelum pajak:</span>
                        <span class="fw-semibold">Rp {{ number_format($amountBeforeTax, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-muted">
                        <span>PPN ({{ $taxRate }}%):</span>
                        <span class="fw-semibold">Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                    </div>
                @endif
                
                <div class="d-flex justify-content-between fw-bold mt-3 pt-3 border-top">
                    <span>Total Pembayaran:</span>
                    <span class="text-primary h5 mb-0">
                        Rp {{ number_format($totalAmount, 0, ',', '.') }}
                    </span>
                </div>
                
                
                @if($payment_method === 'xendit' && $xenditPayWithPpn && $taxRate > 0)
                    <div class="alert alert-info mt-3 mb-0 py-2">
                        <small>
                            <i class="fas fa-info-circle"></i>
                            PPN {{ $taxRate }}% (Rp {{ number_format($taxAmount, 0, ',', '.') }}) akan otomatis ditambahkan oleh Xendit saat pembayaran
                        </small>
                    </div>
                @elseif($payment_method === 'midtrans' && $midtransPayWithPpn && $taxRate > 0)
                    <div class="alert alert-info mt-3 mb-0 py-2">
                        <small>
                            <i class="fas fa-info-circle"></i>
                            PPN {{ $taxRate }}% (Rp {{ number_format($taxAmount, 0, ',', '.') }}) akan otomatis ditambahkan oleh Midtrans saat pembayaran
                        </small>
                    </div>
                @elseif(($payment_method === 'xendit' && !$xenditPayWithPpn) || ($payment_method === 'midtrans' && !$midtransPayWithPpn))
                    <div class="alert alert-success mt-3 mb-0 py-2">
                        <small>
                            <i class="fas fa-check-circle"></i>
                            PPN {{ $taxRate }}% sudah termasuk dalam total pembayaran
                        </small>
                    </div>
                @endif
            @else
                <div class="d-flex justify-content-between fw-bold mt-3 pt-3 border-top">
                    <span>Total Pembayaran:</span>
                    <span class="text-success h5 mb-0">GRATIS</span>
                </div>
            @endif
        </div>
    </div>
    
    @if(!$hasFreeMonthsPromo)
        <!-- Custom Month Input -->
        <div class="mb-4">
            <label class="font-weight-bold mb-2">
                <i class="fas fa-calendar-alt mr-1"></i>
                Jumlah Bulan Pembayaran
            </label>
            <div class="input-group">
                <button class="btn btn-outline-secondary" type="button" wire:click="$set('payment_months', {{ max(1, $payment_months - 1) }})">
                    <i class="fas fa-minus"></i>
                </button>
                <input type="number" 
                       class="form-control text-center font-weight-bold" 
                       min="1" 
                       max="24" 
                       wire:model.lazy="payment_months">
                <button class="btn btn-outline-secondary" type="button" wire:click="$set('payment_months', {{ min(24, $payment_months + 1) }})">
                    <i class="fas fa-plus"></i>
                </button>
                <div class="input-group-append">
                    <span class="input-group-text">Bulan</span>
                </div>
            </div>
            <small class="text-muted">Minimal 1 bulan, maksimal 24 bulan. Bayar lebih banyak untuk diskon!</small>
            @error('payment_months') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
        </div>

        <!-- Payment Method Selection -->
        <div class="mb-4">
            <label class="form-label fw-semibold mb-3">Metode Pembayaran</label>
            <div class="row g-3">
                <!-- Manual Transfer -->
                <div class="col-md-6">
                    <div class="card h-100 payment-method-card {{ $payment_method === 'manual_transfer' ? 'border-primary border-3 bg-light' : '' }}"
                        wire:click="$set('payment_method', 'manual_transfer')" style="cursor: pointer;">
                        <div class="card-body d-flex flex-column text-center py-4">
                            <i class="fas fa-university fa-3x text-primary mb-3"></i>
                            <h6 class="mb-1">Transfer Bank Manual</h6>
                            <small class="text-muted">Upload bukti transfer</small>
                            @if($payment_method === 'manual_transfer')
                                <div class="mt-2">
                                    <span class="badge badge-primary">Dipilih</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Xendit Payment -->
                @if($xenditActive)
                <div class="col-md-6">
                    <div class="card h-100 payment-method-card {{ $payment_method === 'xendit' ? 'border-success border-3 bg-light' : '' }}"
                        wire:click="$set('payment_method', 'xendit')" style="cursor: pointer;">
                        <div class="card-body d-flex flex-column text-center py-4">
                            <i class="fas fa-credit-card fa-3x text-success mb-3"></i>
                            <h6 class="mb-1">Pembayaran Digital</h6>
                            <small class="text-muted">VA, E-Wallet, Credit Card, QRIS</small>
                            @if($payment_method === 'xendit')
                                <div class="mt-2">
                                    <span class="badge badge-success">Dipilih</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                
                <!-- Midtrans Payment -->
                @if($midtransActive)
                <div class="col-md-6">
                    <div class="card h-100 payment-method-card {{ $payment_method === 'midtrans' ? 'border-info border-3 bg-light' : '' }}"
                        wire:click="$set('payment_method', 'midtrans')" style="cursor: pointer;">
                        <div class="card-body d-flex flex-column text-center py-4">
                            <i class="fas fa-wallet fa-3x text-info mb-3"></i>
                            <h6 class="mb-1">Midtrans SNAP</h6>
                            <small class="text-muted">VA, E-Wallet, Credit Card, QRIS</small>
                            @if($payment_method === 'midtrans')
                                <div class="mt-2">
                                    <span class="badge badge-info">Dipilih</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @error('payment_method') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror
        </div>
        
        <!-- Manual Transfer Details -->
        @if($payment_method === 'manual_transfer')
            <div class="card border-warning border-2 mb-4">
                <div class="card-header bg-warning-subtle border-bottom-0">
                    <h4 class="h6 card-title mb-0 fw-semibold">
                        <i class="fas fa-info-circle me-2"></i>
                        Instruksi Transfer
                    </h4>
                </div>
                <div class="card-body">
                    <p class="mb-3">Silakan transfer ke rekening berikut:</p>
                    
                    <div class="border rounded p-3 mb-4 bg-white">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Bank</label>
                                    <p class="fw-semibold mb-0">{{ $settingCompany['nama_bank'] ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Nomor Rekening</label>
                                    <p class="fw-semibold mb-0">{{ $settingCompany['rekening_number'] ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label text-muted small mb-1">Atas Nama</label>
                                    <p class="fw-semibold mb-0">{{ $settingCompany['atas_nama'] ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-light p-3 rounded mt-2">
                            <p class="fw-bold mb-0 text-center">
                                Jumlah: Rp {{ number_format($totalAmount, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                    
                    <h5 class="h6 fw-semibold mb-3">Informasi Transfer Anda</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Bank Pengirim</label>
                                <input type="text" wire:model="nama_bank" class="form-control" placeholder="Contoh: BCA">
                                @error('nama_bank') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nama Pemilik Rekening</label>
                                <input type="text" wire:model="holder_name" class="form-control" placeholder="Nama sesuai rekening">
                                @error('holder_name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nomor Rekening Pengirim</label>
                                <input type="text" wire:model="account_number" class="form-control" placeholder="Contoh: 1234567890">
                                @error('account_number') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cabang Bank</label>
                                <input type="text" wire:model="branch_office" class="form-control" placeholder="Contoh: Cabang Sudirman">
                                @error('branch_office') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-semibold">Upload Bukti Transfer <span class="text-danger">*</span></label>
                        <div class="border rounded p-3 bg-white">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-file-upload fa-2x text-muted"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <input type="file" 
                                           wire:model="payment_proof" 
                                           class="form-control"
                                           accept="image/*,application/pdf"
                                           id="payment-proof-input">
                                    
                                    @if($payment_proof)
                                        <small class="text-success d-block mt-1" id="payment-success-msg">
                                            <i class="fas fa-check-circle me-1"></i> 
                                            File terpilih: {{ $payment_proof->getClientOriginalName() }}
                                        </small>
                                    @else
                                        <small class="text-muted d-block mt-1">
                                            Format: JPG, PNG, PDF (maks. 2MB)
                                        </small>
                                    @endif
                                </div>
                            </div>
                            @error('payment_proof') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror
                            
                            <!-- Loading state -->
                            <div wire:loading wire:target="payment_proof" class="mt-2">
                                <div class="alert alert-warning mb-0 py-2" id="payment-uploading-msg">
                                    <i class="fas fa-spinner fa-spin me-1"></i> 
                                    <strong>Sedang mengunggah file ke server...</strong>
                                    <br><small>Mohon jangan refresh atau pindah halaman</small>
                                </div>
                            </div>
                            
                            <!-- Upload complete indicator -->
                            <div wire:loading.remove wire:target="payment_proof">
                                @if($payment_proof && $paymentProofUploaded)
                                    <div class="mt-2">
                                        <div class="alert alert-success mb-0 py-2">
                                            <i class="fas fa-check-circle me-1"></i> 
                                            <strong>Upload berhasil!</strong> File siap untuk diproses.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Xendit Payment Info -->
        @if($payment_method === 'xendit')
            <div class="card border-success border-2 mb-4">
                <div class="card-body text-center p-4">
                    <i class="fas fa-credit-card fa-4x text-success mb-3"></i>
                    <h5 class="mb-3">Pembayaran Digital via Xendit</h5>
                    <p class="text-muted mb-4">
                        Anda akan diarahkan ke halaman pembayaran Xendit untuk menyelesaikan transaksi
                    </p>
                    <div class="mb-3">
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
                    <div class="alert alert-info text-left">
                        <small>
                            <i class="fas fa-info-circle mr-1"></i>
                            Setelah klik tombol di bawah, Anda akan diarahkan ke halaman Xendit untuk memilih metode pembayaran dan menyelesaikan transaksi.
                        </small>
                    </div>
                </div>
            </div>
        @endif

        <!-- Midtrans Payment Info -->
        @if($payment_method === 'midtrans')
            <div class="card border-info border-2 mb-4">
                <div class="card-body text-center p-4">
                    <i class="fas fa-wallet fa-4x text-info mb-3"></i>
                    <h5 class="mb-3">Pembayaran Digital via Midtrans SNAP</h5>
                    <p class="text-muted mb-4">
                        Anda akan diarahkan ke halaman pembayaran Midtrans untuk menyelesaikan transaksi
                    </p>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">Metode pembayaran yang tersedia:</small>
                        <div class="d-flex justify-content-center flex-wrap">
                            <span class="badge badge-info m-1 p-2">
                                <i class="fas fa-university mr-1"></i>Virtual Account
                            </span>
                            <span class="badge badge-info m-1 p-2">
                                <i class="fas fa-wallet mr-1"></i>E-Wallet (GoPay, OVO, Dana)
                            </span>
                            <span class="badge badge-info m-1 p-2">
                                <i class="fas fa-credit-card mr-1"></i>Credit Card
                            </span>
                            <span class="badge badge-info m-1 p-2">
                                <i class="fas fa-qrcode mr-1"></i>QRIS
                            </span>
                        </div>
                    </div>
                    <div class="alert alert-info text-left">
                        <small>
                            <i class="fas fa-info-circle mr-1"></i>
                            Setelah klik tombol di bawah, Anda akan diarahkan ke halaman Midtrans untuk memilih metode pembayaran dan menyelesaikan transaksi.
                        </small>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="alert alert-info d-flex align-items-center mb-4">
            <i class="fas fa-info-circle fa-2x me-3"></i>
            <div>
                <h4 class="alert-heading mb-1">Pembayaran Tidak Diperlukan Sekarang</h4>
                <p class="mb-0">
                    Anda tidak perlu melakukan pembayaran untuk bulan ini karena mendapatkan promo gratis.
                    Pembayaran akan dimulai pada bulan <strong>{{ $paymentStartMonth }}</strong>.
                </p>
            </div>
        </div>
    @endif

    <div class="d-flex justify-content-between mt-4">
        <button wire:click="prevStep" class="btn btn-outline-secondary px-4 py-2 fw-semibold">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </button>
        
        <button 
            wire:click="nextStep"
            wire:loading.attr="disabled"
            wire:target="nextStep,payment_proof"
            class="btn-primary-red px-4 py-2 fw-semibold"
            @if($payment_method === 'manual_transfer' && !$payment_proof) disabled @endif
        >
            <span wire:loading.remove wire:target="nextStep,payment_proof">
                @if($payment_method === 'xendit' && !$hasFreeMonthsPromo)
                    Lanjut ke Pembayaran Xendit <i class="fas fa-arrow-right ms-2"></i>
                @elseif($payment_method === 'midtrans' && !$hasFreeMonthsPromo)
                    Lanjut ke Pembayaran Midtrans <i class="fas fa-arrow-right ms-2"></i>
                @else
                    Selesaikan Pendaftaran <i class="fas fa-check ms-2"></i>
                @endif
            </span>
            <span wire:loading wire:target="payment_proof">
                Mengunggah...
                <span class="spinner-border spinner-border-sm ms-2"></span>
            </span>
            <span wire:loading wire:target="nextStep">
                Memproses...
                <span class="spinner-border spinner-border-sm ms-2"></span>
            </span>
        </button>
    </div>
</div>