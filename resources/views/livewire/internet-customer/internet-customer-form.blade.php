@section('title', $settingCompany['name'])
<div class="mt-2">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-body p-5">
                    <!-- Progress Bar -->
                    <div class="mb-5">
                        @include('components.alert')
                        <div class="progress-steps">
                            @foreach(['Alamat', 'Data Pribadi', 'Pembayaran', 'Persetujuan', 'Konfirmasi'] as $index => $title)
                                <div class="step-item {{ $step === $index + 1 ? 'active' : ($step > $index + 1 ? 'completed' : '') }}">
                                    <span class="step-number">Step {{ $index + 1 }}</span>
                                    <span class="step-title">{{ $title }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ ($step - 1) * 25 }}%" aria-valuenow="{{ ($step - 1) * 25 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Step Content -->
                    <div>
                        @if($step === 1)
                            <!-- STEP 1: ALAMAT & PAKET -->
                            <h3 class="section-title">Data Alamat & Paket</h3>
                            
                            <div class="row g-3">
                                <!-- Province -->
                                <div class="col-md-6">
                                    <label class="form-label">Provinsi</label>
                                    <select wire:model="province_id" 
                                        id="province_id" 
                                        class="form-control select2-single @error('province_id') is-invalid @enderror"
                                        {{ !$provinces ? 'disabled' : '' }}
                                        required>
                                    <option value="">Select Province</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}">{{ $province->name }}</option>
                                    @endforeach

                                </select>
                                    @error('province_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- City -->
                                <div class="col-md-6">
                                    <label class="form-label">Kota/Kabupaten</label>
                                    <select wire:model="city_id" id="city_id" class="form-select select2-single" {{ !$province_id ? 'disabled' : '' }}>
                                        <option value="">Pilih Kota/Kabupaten</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('city_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- District -->
                                <div class="col-md-6">
                                    <label class="form-label">Kecamatan</label>
                                    <select wire:model="district_id" id="district_id" class="form-select select2-single" {{ !$city_id ? 'disabled' : '' }}>
                                        <option value="">Pilih Kecamatan</option>
                                        @foreach($districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('district_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <!-- Subdistrict -->
                                <div class="col-md-6">
                                    <label class="form-label">Kelurahan</label>
                                    <select wire:model="subdistrict_id" id="subdistrict_id" class="form-select select2-single" {{ !$district_id ? 'disabled' : '' }}>
                                        <option value="">Pilih Kelurahan</option>
                                        @foreach($subdistricts as $subdistrict)
                                            <option value="{{ $subdistrict->id }}">{{ $subdistrict->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('subdistrict_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>

                            <!-- Coverage Check -->
                            @if($coverageMessage)
                                <div class="alert {{ $coverageAvailable ? 'alert-success' : 'alert-danger' }} mt-3">
                                    {{ $coverageMessage }}
                                </div>
                            @endif

                            <!-- Internet Package -->
                             <div class="mt-3">
                                @if ($province_id && $city_id && $district_id && $subdistrict_id)
                                    @if ($isAvailableArea)
                                        <span class="alert-badge success">✓ Layanan tersedia di area Anda</span>
                                    @else
                                        <span class="alert-badge danger">✗ Layanan belum tersedia di area ini</span>
                                    @endif
                                @endif
                            </div>
                            <div class="mt-1">
                                <label class="form-label">Paket Internet</label>
                                <select wire:model="internet_package_id" id="internet_package_id" class="form-select select2-single">
                                    <option value="">Pilih Paket Internet</option>
                                    @foreach($internetPackages as $package)
                                        <option {{ $isAvailableArea ? '' : 'disabled'}} value="{{ $package->id }}">{{ $package->name }} - Rp {{ number_format($package->price_nett, 0, ',', '.') }}</option>
                                    @endforeach
                                </select>
                                @error('internet_package_id') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button 
                                    wire:click="nextStep"
                                    wire:loading.attr="disabled"
                                    wire:target="nextStep"
                                    class="btn-primary-red"
                                >
                                    <span wire:loading.remove wire:target="nextStep">
                                        Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                    </span>
                                    <span wire:loading wire:target="nextStep">
                                        Memproses...
                                        <span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>
                                    </span>
                                </button>
                            </div>
                        @endif

                        @if($step === 2)
                            <!-- STEP 2: DATA PRIBADI -->
                            <h3 class="section-title">Data Pribadi</h3>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" wire:model="name" class="form-control">
                                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input type="text" wire:model="phone_number" class="form-control">
                                    @error('phone_number') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label">Email</label>
                                    <input type="email" wire:model="email" class="form-control">
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                
                                {{-- 
                                <div class="col-md-6">
                                    <label class="form-label">Password</label>
                                    <input type="password" wire:model="password" class="form-control">
                                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Konfirmasi Password</label>
                                    <input type="password" wire:model="password_confirmation" class="form-control">
                                </div>
                                --}}
                                
                                <div class="col-12">
                                    <label class="form-label">Alamat Lengkap</label>
                                    <textarea wire:model="address" rows="3" class="form-control"></textarea>
                                    @error('address') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Nomor KTP</label>
                                    <input type="text" wire:model="ktp_number" class="form-control">
                                    @error('ktp_number') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Foto KTP</label>
                                    <div class="border rounded p-3 bg-white">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-file-upload fa-2x text-muted"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <input type="file" wire:model="ktp_photo" class="form-control">
                                                @if($ktp_photo)
                                                    <small class="text-success d-block mt-1">
                                                        <i class="fas fa-check-circle me-1"></i> 
                                                        File terpilih: {{ $ktp_photo->getClientOriginalName() }}
                                                    </small>
                                                @else
                                                    <small class="text-muted d-block mt-1">
                                                        Format: JPG, PNG, PDF (maks. 2MB)
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                        @error('ktp_photo') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror
                                        <div wire:loading wire:target="ktp_photo" class="alert-success mt-2">
                                            <i class="fas fa-spinner fa-spin me-1"></i> Sedang mengunggah file...
                                        </div>
                                    </div>
                                    
                                </div>
                                
                                
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button wire:click="prevStep" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Kembali
                                </button>
                                <button 
                                    wire:click="nextStep"
                                    wire:loading.attr="disabled"
                                    wire:target="nextStep"
                                    class="btn-primary-red"
                                >
                                    <span wire:loading.remove wire:target="nextStep">
                                        Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                    </span>
                                    <span wire:loading wire:target="nextStep">
                                        Memproses...
                                        <span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>
                                    </span>
                                </button>
                            </div>
                        @endif

                        @if($step === 3)
                            <!-- STEP 3: PEMBAYARAN -->
                            <div class="bg-white p-4 rounded-lg shadow-sm">
                                <h3 class="section-title">Pembayaran</h3>
                                
                                 @if($hasFreeMonthsPromo)
                                    <div class="alert-badge success border-start-4 border-success d-flex align-items-center mb-4">
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
                                            <span class="fw-semibold">{{ $selectedPackage->name }}</span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Harga Bulanan:</span>
                                            <span class="fw-semibold">Rp {{ number_format($selectedPackage->price_nett, 0, ',', '.') }}</span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-between fw-bold mt-3 pt-3 border-top">
                                            <span>Total Pembayaran:</span>
                                            <span class="text-primary">
                                                Rp {{ number_format($selectedPackage->price_nett, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Tampilkan metode pembayaran hanya jika ada pembayaran -->
                                @if(!$hasFreeMonthsPromo)
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold mb-3">Metode Pembayaran</label>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <div class="card h-100 payment-method-card {{ $payment_method === 'transfer' ? 'border-primary border-2' : '' }}"
                                                    wire:click="$set('payment_method', 'transfer')">
                                                    <div class="card-body d-flex flex-column">
                                                        <div class="form-check d-flex align-items-center mb-0">
                                                            <input class="form-check-input" type="radio" 
                                                                wire:model="payment_method" value="transfer" id="transfer">
                                                            <label class="form-check-label d-block ms-2" for="transfer">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="fas fa-university fs-4 me-2 text-primary"></i>
                                                                    <span class="fw-semibold">Transfer Bank</span>
                                                                </div>
                                                            </label>
                                                        </div>
                                                        <div class="mt-2">
                                                            <small class="text-muted">BCA, BRI, BNI, Mandiri, dll</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            {{-- 
                                            <div class="col-md-4">
                                                <div class="card h-100 payment-method-card {{ $payment_method === 'qris' ? 'border-primary border-2' : '' }}"
                                                    wire:click="$set('payment_method', 'qris')">
                                                    <div class="card-body d-flex flex-column">
                                                        <div class="form-check d-flex align-items-center mb-0">
                                                            <input class="form-check-input" type="radio" 
                                                                wire:model="payment_method" value="qris" id="qris">
                                                            <label class="form-check-label d-block ms-2" for="qris">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="fas fa-qrcode fs-4 me-2 text-success"></i>
                                                                    <span class="fw-semibold">QRIS</span>
                                                                </div>
                                                            </label>
                                                        </div>
                                                        <div class="mt-2">
                                                            <small class="text-muted">Bayar dengan QRIS</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-4">
                                                <div class="card h-100 payment-method-card {{ $payment_method === 'e-wallet' ? 'border-primary border-2' : '' }}"
                                                    wire:click="$set('payment_method', 'e-wallet')">
                                                    <div class="card-body d-flex flex-column">
                                                        <div class="form-check d-flex align-items-center mb-0">
                                                            <input class="form-check-input" type="radio" 
                                                                wire:model="payment_method" value="e-wallet" id="e-wallet">
                                                            <label class="form-check-label d-block ms-2" for="e-wallet">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="fas fa-wallet fs-4 me-2 text-warning"></i>
                                                                    <span class="fw-semibold">E-Wallet</span>
                                                                </div>
                                                            </label>
                                                        </div>
                                                        <div class="mt-2">
                                                            <small class="text-muted">Gopay, OVO, Dana, LinkAja</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            --}}
                                        </div>
                                        @error('payment_method') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror
                                    </div>
                                    
                                    @if($payment_method === 'transfer')
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
                                                            Jumlah: Rp {{ number_format($selectedPackage->price_nett, 0, ',', '.') }}
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
                                                    <label class="form-label fw-semibold">Upload Bukti Transfer</label>
                                                    <div class="border rounded p-3 bg-white">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <i class="fas fa-file-upload fa-2x text-muted"></i>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <input type="file" wire:model="payment_proof" class="form-control">
                                                                @if($payment_proof)
                                                                    <small class="text-success d-block mt-1">
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
                                                        <div wire:loading wire:target="payment_proof" class="text-primary mt-2">
                                                            <i class="fas fa-spinner fa-spin me-1"></i> Sedang mengunggah file...
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="border-start-4 border-info d-flex align-items-center mb-4">
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
                                        wire:target="nextStep"
                                        class="btn-primary-red px-4 py-2 fw-semibold"
                                    >
                                        <span wire:loading.remove wire:target="nextStep">
                                            Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                        </span>
                                        <span wire:loading wire:target="nextStep">
                                            Memproses...
                                            <span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        @endif

                        @if($step === 4)
                            <!-- STEP 4: PERSETUJUAN & TANDA TANGAN (BARU) -->
                            <h3 class="section-title">Persetujuan dan Tanda Tangan</h3>

                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Ringkasan Pendaftaran</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Nama:</strong> {{ $name }}</p>
                                            <p><strong>Email:</strong> {{ $email }}</p>
                                            <p><strong>Alamat:</strong> {{ $address }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Paket Internet:</strong> {{ $selectedPackage->name }}</p>
                                            <p><strong>Harga:</strong> Rp {{ number_format($selectedPackage->price_nett, 0, ',', '.') }}</p>
                                            <p><strong>Metode Pembayaran:</strong> {{ $payment_method ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Syarat dan Ketentuan</h5>
                                    <div style="max-height: 300px; overflow-y: auto; border: 1px solid #eee; padding: 15px;">
                                        @if($agreement)
                                            @if(view()->exists('partnership_agreement.pdf.perjanjian_berlangganan_internet'))
                                                @include('partnership_agreement.pdf.perjanjian_berlangganan_internet', ['agreement' => $agreement])
                                            @else
                                                <p>Syarat dan ketentuan tidak tersedia.</p>
                                            @endif
                                        @else
                                            <p>Syarat dan ketentuan tidak tersedia.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Tanda Tangan</h5>
                                    
                                    <!-- Canvas Area (ditampilkan saat belum ada signature) -->
                                    <div id="signature-canvas-container" class="{{ $signature ? 'd-none' : '' }}">
                                        <div class="alert alert-info mb-3">
                                            <i class="fas fa-info-circle me-2"></i> Silakan gambar tanda tangan Anda pada area persegi di bawah
                                        </div>
                                        
                                        <div class="d-flex flex-column align-items-center">
                                            <div id="signature-pad-container" 
                                                class="border rounded bg-light position-relative mx-auto"
                                                style="width: 400px; height: 300px;">
                                                <canvas id="signature-canvas" 
                                                        style="width: 100%; height: 100%; touch-action: none; background-color: white;"></canvas>
                                                <div class="position-absolute bottom-0 start-0 w-100 p-2 bg-light border-top">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger" id="clear-signature">
                                                            <i class="fas fa-eraser me-1"></i> Hapus
                                                        </button>
                                                        <small class="text-muted">Gambar di dalam area kotak</small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-4">
                                                <button type="button" id="save-signature" class="btn btn-success px-4 py-2">
                                                    <i class="fas fa-save me-2"></i> Simpan Tanda Tangan
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Preview Area (ditampilkan setelah signature disimpan) -->
                                    <div id="signature-preview-container" class="{{ $signature ? '' : 'd-none' }}">
                                        <div class="alert alert-success mb-3">
                                            <i class="fas fa-check-circle me-2"></i> Tanda tangan Anda telah disimpan
                                        </div>
                                        
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="mb-3 text-center">
                                                <h6>Preview Tanda Tangan:</h6>
                                                <p class="text-muted small">Tanda tangan akan muncul di dokumen perjanjian</p>
                                            </div>
                                            
                                            <div class="border rounded bg-white p-2 shadow-sm" style="width: 300px; height: 150px;">
                                                <img id="signature-preview-image" 
                                                    src="{{ $signature }}" 
                                                    class="img-fluid h-100 w-100 object-contain">
                                            </div>
                                            
                                            <div class="mt-4">
                                                <button type="button" id="re-sign" class="btn btn-outline-secondary px-4 py-2">
                                                    <i class="fas fa-redo me-2"></i> Gambar Ulang
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @error('signature') 
                                        <div class="alert alert-danger mt-3">
                                            <i class="fas fa-exclamation-circle me-2"></i> {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button wire:click="prevStep" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Kembali
                                </button>
                                <button 
                                    wire:click="nextStep"
                                    wire:loading.attr="disabled"
                                    wire:target="nextStep"
                                    class="btn-primary-red px-4"
                                >
                                    <span wire:loading.remove wire:target="nextStep">
                                        Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                    </span>
                                    <span wire:loading wire:target="nextStep">
                                        Memproses...
                                        <span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>
                                    </span>
                                </button>
                            </div>
                        @endif

                        @if($step === 5)
                            <!-- STEP 4: KONFIRMASI -->
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <div class="d-inline-flex bg-success bg-opacity-10 p-3 rounded-circle">
                                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                                    </div>
                                </div>
                                <h2 class="mb-3">Pendaftaran Berhasil!</h2>
                                <p class="lead text-muted mb-4">Terima kasih telah mendaftar sebagai pelanggan kami. Teknisi kami akan segera menghubungi Anda.</p>
                                
                                <div class="card border-0 shadow-sm mb-4 mx-auto" style="max-width: 500px;">
                                    <div class="card-body">
                                        <h3 class="h5 card-title mb-3">Detail Pendaftaran</h3>
                                        <div class="text-start">
                                            <p><strong>Nama:</strong> {{ $name }}</p>
                                            <p><strong>Paket:</strong> {{ $selectedPackage->name }}</p>
                                            <p><strong>Total Pembayaran:</strong> Rp {{ number_format($selectedPackage->price_nett, 0, ',', '.') }}</p>
                                            <p><strong>Nomor Pelanggan:</strong> {{ $code }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Tombol untuk menuju laman detail pelanggan -->
                                <div class="mt-4">
                                    <a href="{{ route('internet-customer.customer.show', $code) }}" 
                                    class="btn-primary-red">
                                        Lihat Detail Pelanggan
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Syarat dan Ketentuan -->
 @if($agreement)
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Syarat dan Ketentuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @if(view()->exists('partnership_agreement.pdf.perjanjian_berlangganan_internet'))
            @include('partnership_agreement.pdf.perjanjian_berlangganan_internet', ['agreement' => $agreement])
            @endif
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Saya Mengerti</button>
            </div>
        </div>
    </div>
</div>
@endif

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
            .main-content {
                background: radial-gradient(#DB2328, #ffff);  
            }
        /* Optimasi untuk touch devices */
        * {
            -webkit-tap-highlight-color: rgba(0,0,0,0);
            -webkit-touch-callout: none;
        }

        /* Clean white background untuk content */
        .registration-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 40px;
            margin: 0 auto;
            max-width: 1000px;
        }

        /* Progress Steps - minimal dan clean */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 35px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
            flex-wrap: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .step-item {
            flex: 1 1 auto;
            text-align: center;
            color: #999;
            font-size: 14px;
            min-width: 80px;
            white-space: nowrap;
        }

        .step-item.active {
            color: #DB2328;
            font-weight: 600;
        }

        .step-item.completed {
            color: #666;
        }

        .step-number {
            font-size: 11px;
            display: block;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .step-title {
            font-size: 13px;
        }

        /* Section Title */
        .section-title {
            /* font-size: 20px; */
            font-weight: 400;
            /* margin-bottom: 25px; */
            /* color: #2c3e50; */
        }

        /* Form Elements - minimal clean style */
        .form-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #DB2328;
            box-shadow: 0 0 0 2px rgba(219, 35, 40, 0.1);
            outline: none;
        }

        /* Buttons - hanya tombol utama yang merah */
        .btn-primary-red {
            background-color: #DB2328;
            border: none;
            color: white;
            padding: 10px 35px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
            transition: background-color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary-red:hover {
            background-color: #c01f24;
            color: white;
        }

        .btn-secondary-gray {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            color: #666;
            padding: 10px 30px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
        }

        .btn-secondary-gray:hover {
            background-color: #e8e8e8;
            border-color: #ccc;
            color: #333;
        }

        /* Alert Badges */
        .alert-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 500;
            margin-top: 10px;
        }

        .alert-badge.success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-badge.danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .text-success
        {
            color: #155724;
        }

        .text-primary
        {
            color: #721c24;
        }

        /* Payment Method Cards */
        .payment-card {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 18px;
            cursor: pointer;
            transition: all 0.2s;
            background: white;
        }

        .payment-card:hover {
            border-color: #DB2328;
            box-shadow: 0 2px 8px rgba(219, 35, 40, 0.15);
        }

        .payment-card.selected {
            border-color: #DB2328;
            background-color: #fff5f5;
        }

        .payment-card .form-check-input {
            cursor: pointer;
        }

        /* Confirmation Cards */
        .confirm-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 16px;
        }

        .confirm-section h5 {
            color: #2c3e50;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #DB2328;
            display: inline-block;
        }

        .confirm-section table {
            font-size: 14px;
        }

        .confirm-section table td {
            padding: 6px 0;
        }

        /* Signature Pad */
        .signature-wrapper {
            border: 2px dashed #ddd;
            border-radius: 6px;
            padding: 15px;
            background: #fafafa;
            margin-top: 10px;
        }

        #signature-canvas {
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            cursor: crosshair;
            display: block;
            width: 100%;
        }

        .signature-preview {
            max-width: 100%;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }

        /* Agreement Text */
        .agreement-box {
            max-height: 350px;
            overflow-y: auto;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 20px;
            background: #fafafa;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Select2 Styling */
        .select2-container--default .select2-selection--single {
            border: 1px solid #ddd;
            border-radius: 4px;
            height: 42px;
            padding: 6px 12px;
        }

        /* === 1. Warna teks hasil pilihan (tampil di input Select2) === */
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #DB2328 !important;          /* teks merah */
            font-weight: 500;
        }

        /* === 2. Warna item yang sedang dipilih di dropdown list === */
        .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: #fff5f5 !important; /* merah muda lembut */
            color: #DB2328 !important;            /* teks merah */
            font-weight: 600;
        }

        /* === 3. Warna item yang sedang di-hover atau difokuskan === */
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #DB2328 !important;
            color: #fff !important;
        }

        /* === 4. Hilangkan warna biru bawaan di border dan caret === */
        .select2-container--default .select2-selection--single:focus,
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #DB2328 !important;
            box-shadow: 0 0 0 2px rgba(219, 35, 40, 0.15) !important;
            outline: none !important;
        }

        /* === 5. Warna caret (panah kecil di kanan) === */
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #DB2328 transparent transparent transparent !important;
        }

        /* === 6. Warna background Select2 saat sudah dipilih === */
        .select2-container--default .select2-selection--single {
            background-color: #fff !important;
            border: 1px solid #DB2328 !important;
            border-radius: 4px;
            transition: all 0.2s ease-in-out;
        }

        /* === 7. Hover state biar tetap konsisten === */
        .select2-container--default .select2-selection--single:hover {
            border-color: #c01f24 !important;
        }
        

        

        /* Info Alert */
        .info-alert {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 6px;
            padding: 14px;
            margin-top: 20px;
            font-size: 13px;
        }

        .info-alert i {
            color: #856404;
        }

        /* Responsive */
        @media (max-width: 768px) {
            /* Card padding lebih kecil di mobile */
            .registration-card {
                padding: 20px 15px;
                margin: 10px;
            }

            /* Progress steps - compact untuk mobile */
            .progress-steps {
                flex-wrap: nowrap;
                gap: 5px;
                margin-bottom: 25px;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none; /* Firefox */
                -ms-overflow-style: none; /* IE/Edge */
            }

            .progress-steps::-webkit-scrollbar {
                display: none; /* Chrome/Safari */
            }

            .step-item {
                flex: 0 0 auto;
                min-width: 70px;
                font-size: 10px;
                padding: 0 5px;
            }

            .step-number {
                font-size: 9px;
                margin-bottom: 3px;
            }

            .step-title {
                font-size: 10px;
                line-height: 1.2;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: normal;
                max-width: 70px;
                margin: 0 auto;
            }

            /* Sembunyikan beberapa step title, hanya tampilkan yang aktif dan terdekat */
            .step-item:not(.active):not(.completed) .step-title {
                display: none;
            }

            /* Title lebih kecil */
            .section-title {
                font-size: 18px;
                margin-bottom: 20px;
            }

            /* Form elements lebih compact */
            .form-label {
                font-size: 13px;
                margin-bottom: 5px;
            }

            .form-control,
            .form-select {
                font-size: 14px;
                padding: 10px 12px;
            }

            /* Buttons full width atau stack */
            .btn-primary-red,
            .btn-secondary-gray {
                font-size: 14px;
                padding: 10px 25px;
                width: 100%;
            }

            /* Button group responsive */
            .d-flex.justify-content-between {
                flex-direction: column-reverse;
                gap: 10px;
            }

            .d-flex.justify-content-between .btn-primary-red,
            .d-flex.justify-content-between .btn-secondary-gray {
                width: 100%;
                justify-content: center;
            }

            .d-flex.justify-content-end {
                justify-content: stretch !important;
            }

            .d-flex.justify-content-end .btn-primary-red {
                width: 100%;
                justify-content: center;
            }

            /* Payment cards full width */
            .payment-card {
                padding: 15px;
                margin-bottom: 10px;
            }

            .payment-card .form-check-label {
                font-size: 13px;
            }

            .payment-card img {
                max-width: 60px !important;
            }

            /* Confirmation tables - stack vertically */
            .confirm-section {
                padding: 15px;
                margin-bottom: 12px;
            }

            .confirm-section h5 {
                font-size: 14px;
                margin-bottom: 10px;
            }

            .confirm-section table {
                font-size: 12px;
            }

            .confirm-section table td {
                display: block;
                padding: 3px 0 !important;
                width: 100% !important;
            }

            .confirm-section table td:first-child {
                font-weight: 600;
                padding-bottom: 2px !important;
                border-bottom: none;
            }

            .confirm-section table td:last-child {
                padding-bottom: 10px !important;
                color: #666;
            }

            .confirm-section table tr {
                display: block;
                margin-bottom: 10px;
                border-bottom: 1px solid #f0f0f0;
                padding-bottom: 5px;
            }

            /* Alert badges */
            .alert-badge {
                font-size: 12px;
                padding: 6px 12px;
                display: block;
                text-align: center;
                margin-top: 8px;
            }

            /* Agreement box */
            .agreement-box {
                max-height: 250px;
                padding: 15px;
                font-size: 13px;
                line-height: 1.5;
            }

            /* Signature canvas */
            #signature-canvas {
                height: 150px !important;
            }

            .signature-wrapper {
                padding: 12px;
            }

            .signature-wrapper .d-flex {
                flex-direction: column;
                gap: 8px;
            }

            .signature-wrapper .btn-primary-red,
            .signature-wrapper .btn-secondary-gray {
                width: 100%;
            }

            /* Info alert */
            .info-alert {
                padding: 12px;
                font-size: 12px;
            }

            /* Row spacing */
            .row.g-3 {
                row-gap: 15px !important;
            }

            /* Checkbox styling */
            .form-check {
                margin-bottom: 15px;
            }

            .form-check-input {
                margin-top: 0.15em;
            }

            .form-check-label {
                font-size: 13px;
                line-height: 1.4;
            }

            /* File input styling */
            input[type="file"] {
                font-size: 13px;
                padding: 8px 10px;
            }

            /* Textarea */
            textarea.form-control {
                min-height: 80px;
            }
        }

        /* Extra small devices (phones in portrait, less than 576px) */
        @media (max-width: 576px) {
            .registration-card {
                padding: 15px 10px;
                border-radius: 8px;
            }

            .section-title {
                font-size: 16px;
                margin-bottom: 15px;
            }

            .progress-steps {
                margin-bottom: 20px;
                padding-bottom: 10px;
            }

            .step-item {
                min-width: 60px;
            }

            .step-title {
                max-width: 60px;
            }

            /* Form elements */
            .form-control,
            .form-select {
                font-size: 14px;
                padding: 9px 10px;
            }

            .form-label {
                font-size: 12px;
            }

            /* Buttons */
            .btn-primary-red,
            .btn-secondary-gray {
                font-size: 13px;
                padding: 9px 20px;
            }

            /* Make all payment cards full width */
            .col-md-6 {
                padding-left: 0;
                padding-right: 0;
            }

            /* Adjust select2 dropdown for small screens */
            .select2-container--default .select2-selection--single {
                height: 40px;
                padding: 5px 10px;
            }

            .select2-container--default .select2-selection--single .select2-selection__rendered {
                font-size: 13px;
                line-height: 28px;
            }

            /* Confirmation section more compact */
            .confirm-section {
                padding: 12px;
            }

            .confirm-section h5 {
                font-size: 13px;
            }

            .confirm-section table {
                font-size: 11px;
            }
        }

        /* Landscape mode optimization */
        @media (max-width: 768px) and (orientation: landscape) {
            .registration-card {
                padding: 15px;
            }

            .section-title {
                font-size: 16px;
                margin-bottom: 15px;
            }

            .progress-steps {
                margin-bottom: 20px;
            }

            #signature-canvas {
                height: 120px !important;
            }

            .agreement-box {
                max-height: 180px;
            }
        }

        .progress-bar {
            background: #DB2328;
        }

        .select2-container--default .select2-selection--single {
            border-color: #DB2328;
        }

            .select2-results__option,
            .select2-results__option--selectable,
            .select2-results__option--selected,
            .select2-results__option--highlighted {
                padding: 6px 12px;
            }

            .select2-results__option--selectable:hover,
            .select2-results__option--highlighted {
                background-color: #DB2328 !important;
                color: white;
            }

            .select2-results__option--selected {
                background-color: #e9ecef !important;
                font-weight: bold;
            }
            .select2-search--dropdown .select2-search__field {
                border-color: #ced4da;
            }
            .alert-badge 
            {
                display: inline-block;
                padding: 8px 16px;
                border-radius: 4px;
                font-size: 13px;
                font-weight: 500;
                margin-top: 10px;
            }

            .alert-badge.success {
                background-color: #d4edda;
                color: #155724;
            }

            .alert-badge.danger {
                background-color: #f8d7da;
                color: #721c24;
            }

            /* === 1. Hilangkan outline biru global di semua elemen === */
            *:focus {
                outline: none !important;
                box-shadow: none !important;
            }

            /* === 2. Ganti efek fokus semua input, select, textarea, button jadi merah === */
            input:focus,
            textarea:focus,
            select:focus,
            button:focus {
                border-color: #DB2328 !important;
                box-shadow: 0 0 0 2px rgba(219, 35, 40, 0.15) !important;
                outline: none !important;
                transition: all 0.2s ease-in-out;
            }

            /* === 3. Untuk elemen dengan role focusable (div editable, select2, dsb) === */
            [tabindex]:focus,
            div[contenteditable="true"]:focus {
                border-color: #DB2328 !important;
                box-shadow: 0 0 0 2px rgba(219, 35, 40, 0.15) !important;
            }

            /* === 4. Tambahan khusus Select2 biar tetap nyatu sama gaya global === */
            .select2-container--default .select2-selection--single:focus,
            .select2-container--default.select2-container--focus .select2-selection--single,
            .select2-container--default.select2-container--open .select2-selection--single {
                border-color: #DB2328 !important;
                box-shadow: 0 0 0 2px rgba(219, 35, 40, 0.15) !important;
                outline: none !important;
            }

            /* === 5. Hover state opsional biar smooth === */
            input:hover,
            textarea:hover,
            select:hover,
            button:hover,
            .select2-container--default .select2-selection--single:hover {
                border-color: #DB2328 !important;
            }

            /* === 6. Untuk button agar glow-nya juga merah saat fokus === */
            button:focus-visible {
                box-shadow: 0 0 0 3px rgba(219, 35, 40, 0.25) !important;
            }

            /* === 7. Khusus div wrapper yang dapat fokus (misal card aktif) === */
            div:focus {
                outline: none !important;
                border-color: #DB2328 !important;
                box-shadow: 0 0 0 2px rgba(219, 35, 40, 0.15) !important;
            }
    </style>
    <style>
        #signature-canvas-container, #signature-preview-container {
            transition: all 0.3s ease;
        }

        #signature-preview-container {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            background-color: #f8f9fa;
        }
        
    </style>
@endpush

@push('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        document.addEventListener('livewire:load', function () {
            console.log('livewire loaded');

            document.getElementById('save-signature')?.addEventListener('click', function() {
                if (signaturePad && !signaturePad.isEmpty()) {
                    const signatureData = signaturePad.toDataURL();
                                                console.log("save 2");
                    @this.call('saveSignature', signatureData);
                } else {
                    alert('Harap berikan tanda tangan Anda');
                }
            });
            
            // Event untuk tombol Gambar Ulang
            document.getElementById('re-sign')?.addEventListener('click', function() {
                // Reset canvas
                console.log('Reset canvas');
                
                signaturePad.clear();
                
                // Tampilkan canvas container, sembunyikan preview
                document.getElementById('signature-canvas-container').classList.remove('d-none');
                document.getElementById('signature-preview-container').classList.add('d-none');
                
                // Reset signature di Livewire
                console.log("reset 1");
                
                @this.set('signature', null);
            });
            
            // Event ketika signature berhasil disimpan
            Livewire.on('signature-saved', () => {
                // Sembunyikan canvas, tampilkan preview
                document.getElementById('signature-canvas-container').classList.add('d-none');
                document.getElementById('signature-preview-container').classList.remove('d-none');
                
                // Update preview image
                const previewImage = document.getElementById('signature-preview-image');
                if (previewImage && @this.signature) {
                    previewImage.src = @this.signature;
                }
            });
            
            function showSuccessAlert(message) 
            {
                Swal.fire({
                    title: 'Pendaftaran Berhasil!',
                    html: message,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6',
                    customClass: {
                        popup: 'animated bounceIn'
                    }
                });
            }

            function initSelect2() {
                // SINGLE SELECT
                $('.select2-single').each(function () {
                    const select = $(this);
                    const prop = select.attr('id');

                    select.select2({
                        placeholder: "-- Pilih --",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('.card-body')
                    });

                    select.off('change').on('change', function (e) {
                        const value = $(this).val();
                        if (@this[prop] != value) {
                            console.log(`Update Livewire ${prop} to:`, value);
                            @this.set(prop, value);
                        }
                    });
                });
            }
            
            let signaturePad = null;
    
                function initSignaturePad() 
                {
                    const canvas = document.getElementById('signature-canvas');
                    if (!canvas) return null;
                    
                    // Hapus instance sebelumnya jika ada
                    if (signaturePad) {
                        signaturePad.off();
                        window.removeEventListener('resize', handleResize);
                        document.getElementById('clear-signature')?.removeEventListener('click', handleClear);
                    }
                    
                    // Fungsi untuk resize canvas
                    function handleResize() {
                        const ratio = Math.max(window.devicePixelRatio || 1, 1);
                        const container = canvas.parentElement;
                        
                        canvas.width = container.offsetWidth * ratio;
                        canvas.height = container.offsetHeight * ratio;
                        canvas.style.width = container.offsetWidth + 'px';
                        canvas.style.height = container.offsetHeight + 'px';
                        
                        const ctx = canvas.getContext('2d');
                        ctx.scale(ratio, ratio);
                        
                        if (signaturePad) {
                            signaturePad.clear();
                            // Jika ada signature yang disimpan, tampilkan kembali
                            if (@this.signature) {
                                signaturePad.fromDataURL(@this.signature);
                            }
                        }
                    }
                    
                    // Fungsi untuk clear signature
                    function handleClear() {
                        signaturePad.clear();
                         console.log("reset 2");
                        @this.call('saveSignature', null);
                    }
                    
                    // Inisialisasi ukuran pertama kali
                    handleResize();
                    
                    // Buat signature pad
                    signaturePad = new SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)',
                        penColor: 'rgb(0, 0, 0)',
                        minWidth: 1,
                        maxWidth: 3,
                        throttle: 16
                    });
                    
                    // Handle clear button
                    document.getElementById('clear-signature')?.addEventListener('click', handleClear);
                    
                    // Handle window resize
                    window.addEventListener('resize', handleResize);
                    
                    // Auto-save signature saat selesai menggambar
                    canvas.addEventListener('mouseup', saveSignature);
                    canvas.addEventListener('touchend', saveSignature);
                    
                    return signaturePad;
                }
                
                // Fungsi untuk menyimpan signature
                function saveSignature() 
                {
                    if (signaturePad && !signaturePad.isEmpty()) {
                        const signatureData = signaturePad.toDataURL();
                        @this.call('saveSignature', signatureData);
                    }
                }
                
                // Inisialisasi pertama kali
                signaturePad = initSignaturePad();

                initSelect2();
            Livewire.hook('message.processed', (message, component) => 
            {
                console.log("Livewire processed:", message);
                initSelect2();

                $('.select2-single').each(function () {
                    const id = $(this).attr('id');
                    if (@this[id] !== undefined) {
                        $(this).val(@this[id]).trigger('change');
                    }
                });

                if (component.get('step') === 4) 
                {
                     document.getElementById('save-signature')?.addEventListener('click', function() 
                     {
                        if (signaturePad && !signaturePad.isEmpty()) {
                            const signatureData = signaturePad.toDataURL();

                            console.log("save 1");
                            
                            @this.call('saveSignature', signatureData);
                        } else {
                            alert('Harap berikan tanda tangan Anda');
                        }
                    });

                    // ❗ Tambahkan ulang binding untuk tombol Re-sign
                    document.getElementById('re-sign')?.addEventListener('click', function () {
                        signaturePad.clear();
                        document.getElementById('signature-canvas-container').classList.remove('d-none');
                        document.getElementById('signature-preview-container').classList.add('d-none');
                         console.log("reset 3");
                        // @this.set('signature', null);
                        @this.call('saveSignature', null);
                    });

                    setTimeout(() => {
                        signaturePad = initSignaturePad();
                        if (component.get('signature') && signaturePad) {
                            signaturePad.fromDataURL(component.get('signature'));
                        }
                    }, 50);
            }
            });
        });
    </script>
@endpush