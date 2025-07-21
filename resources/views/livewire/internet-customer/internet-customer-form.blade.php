<div class="py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-body p-5">
                    <!-- Progress Bar -->
                    <div class="mb-5">
                        <div class="d-flex justify-content-between mb-3">
                            @foreach(['Alamat', 'Data Pribadi', 'Pembayaran', 'Konfirmasi'] as $index => $title)
                                <div class="text-center {{ $step > $index + 1 ? 'text-success' : ($step == $index + 1 ? 'fw-bold text-primary' : 'text-muted') }}">
                                    <small>Step {{ $index + 1 }}</small><br>
                                    {{ $title }}
                                </div>
                            @endforeach
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ ($step - 1) * 33.33 }}%" aria-valuenow="{{ ($step - 1) * 33.33 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Step Content -->
                    <div>
                        @if($step === 1)
                            <!-- STEP 1: ALAMAT & PAKET -->
                            <h2 class="mb-4">Data Alamat & Paket</h2>
                            
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
                             <div class="mt-2">
                            @if ($isAvailableArea & $province_id && $city_id && $district_id && $subdistrict_id)
                                @if ($isAvailableArea)
                                    <span class="badge bg-success">Layanan tersedia</span>
                                @else
                                    <span class="badge bg-danger">Layanan belum tersedia di area ini</span>
                                @endif
                            @endif
                        </div>
                            <div class="mt-4">
                                <label class="form-label">Paket Internet</label>
                                <select wire:model="internet_package_id" id="internet_package_id" class="form-select select2-single">
                                    <option value="">Pilih Paket Internet</option>
                                    @foreach($internetPackages as $package)
                                        <option {{ $isAvailableArea ? '' : 'disabled'}} value="{{ $package->id }}">{{ $package->name }} - Rp {{ number_format($package->nett, 0, ',', '.') }}</option>
                                    @endforeach
                                </select>
                                @error('internet_package_id') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button wire:click="nextStep" class="btn btn-primary px-4">
                                    Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        @endif

                        @if($step === 2)
                            <!-- STEP 2: DATA PRIBADI -->
                            <h2 class="mb-4">Data Pribadi</h2>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" wire:model="name" class="form-control">
                                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" wire:model="email" class="form-control">
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Password</label>
                                    <input type="password" wire:model="password" class="form-control">
                                    @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Konfirmasi Password</label>
                                    <input type="password" wire:model="password_confirmation" class="form-control">
                                </div>
                                
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
                                    <input type="file" wire:model="ktp_photo" class="form-control">
                                    @error('ktp_photo') <small class="text-danger">{{ $message }}</small> @enderror
                                    @if($ktp_photo)
                                        <small class="text-muted">File terpilih: {{ $ktp_photo->getClientOriginalName() }}</small>
                                    @endif
                                </div>
                                
                                <div class="col-12 mt-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" wire:model="terms" id="termsCheck">
                                        <label class="form-check-label" for="termsCheck">
                                            Saya menyetujui 
                                            <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#termsModal">
                                                Syarat dan Ketentuan
                                            </a>
                                        </label>
                                    </div>
                                    @error('terms') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button wire:click="prevStep" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Kembali
                                </button>
                                <button wire:click="nextStep" class="btn btn-primary px-4">
                                    Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        @endif

                        @if($step === 3)
                            <!-- STEP 3: PEMBAYARAN -->
                            <h2 class="mb-4">Pembayaran</h2>
                            
                            <div class="card bg-light mb-4">
                                <div class="card-body">
                                    <h3 class="h5 card-title mb-3">Detail Tagihan</h3>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Paket Internet:</span>
                                        <span class="fw-semibold">{{ $selectedPackage->name }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Harga:</span>
                                        <span class="fw-semibold">Rp {{ number_format($selectedPackage->price, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-bold mt-3 pt-3 border-top">
                                        <span>Total:</span>
                                        <span class="text-primary">Rp {{ number_format($selectedPackage->price, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label mb-3">Metode Pembayaran</label>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="form-check card p-3 h-100 {{ $payment_method === 'transfer' ? 'border-primary' : '' }}">
                                            <input class="form-check-input" type="radio" wire:model="payment_method" value="transfer" id="transfer">
                                            <label class="form-check-label d-block" for="transfer">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-university fs-4 me-2"></i>
                                                    <span>Transfer Bank</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    {{-- 
                                    <div class="col-md-4">
                                        <div class="form-check card p-3 h-100 {{ $payment_method === 'qris' ? 'border-primary' : '' }}">
                                            <input class="form-check-input" type="radio" wire:model="payment_method" value="qris" id="qris">
                                            <label class="form-check-label d-block" for="qris">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-qrcode fs-4 me-2"></i>
                                                    <span>QRIS</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check card p-3 h-100 {{ $payment_method === 'e-wallet' ? 'border-primary' : '' }}">
                                            <input class="form-check-input" type="radio" wire:model="payment_method" value="e-wallet" id="e-wallet">
                                            <label class="form-check-label d-block" for="e-wallet">
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-wallet fs-4 me-2"></i>
                                                    <span>E-Wallet</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    --}}
                                </div>
                                @error('payment_method') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            
                            @if($payment_method === 'transfer')
                                <div class="card bg-warning bg-opacity-10 mb-4">
                                    <div class="card-body">
                                        <h4 class="h6 card-title">Instruksi Transfer:</h4>
                                        <p>Silakan transfer ke rekening berikut:</p>
                                        <div class="mt-2">
                                            <p><strong>Bank:</strong> BCA</p>
                                            <p><strong>Nomor Rekening:</strong> 1234567890</p>
                                            <p><strong>Atas Nama:</strong> PT. Internet Cepat</p>
                                            <p class="fw-bold mt-2">Jumlah: Rp {{ number_format($selectedPackage->price, 0, ',', '.') }}</p>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <label class="form-label">Upload Bukti Transfer</label>
                                            <input type="file" wire:model="payment_proof" class="form-control">
                                            @error('payment_proof') <small class="text-danger">{{ $message }}</small> @enderror
                                            @if($payment_proof)
                                                <small class="text-muted">File terpilih: {{ $payment_proof->getClientOriginalName() }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="d-flex justify-content-between mt-4">
                                <button wire:click="prevStep" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-arrow-left me-2"></i> Kembali
                                </button>
                                <button wire:click="nextStep" class="btn btn-success px-4">
                                    Konfirmasi Pembayaran <i class="fas fa-check ms-2"></i>
                                </button>
                            </div>
                        @endif

                        @if($step === 4)
                            <!-- STEP 4: KONFIRMASI -->
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <div class="d-inline-flex bg-success bg-opacity-10 p-3 rounded-circle">
                                        <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                                    </div>
                                </div>
                                <h2 class="mb-3">Pendaftaran Berhasil!</h2>
                                <p class="lead text-muted mb-4">Terima kasih telah mendaftar sebagai pelanggan kami.</p>
                                
                                <div class="card border-0 shadow-sm mb-4 mx-auto" style="max-width: 500px;">
                                    <div class="card-body">
                                        <h3 class="h5 card-title mb-3">Detail Pendaftaran</h3>
                                        <div class="text-start">
                                            <p><strong>Nama:</strong> {{ $name }}</p>
                                            <p><strong>Paket:</strong> {{ $selectedPackage->name }}</p>
                                            <p><strong>Total Pembayaran:</strong> Rp {{ number_format($selectedPackage->price, 0, ',', '.') }}</p>
                                            <p><strong>Nomor Pelanggan:</strong> {{ rand(100000, 999999) }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="/" class="btn btn-primary px-4">
                                        <i class="fas fa-home me-2"></i> Kembali ke Beranda
                                    </a>
                                    <a href="#" class="btn btn-outline-primary px-4">
                                        <i class="fas fa-download me-2"></i> Unduh Invoice
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
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Syarat dan Ketentuan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="terms-content" style="max-height: 60vh; overflow-y: auto;">
                    <h4>1. Ketentuan Umum</h4>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam in dui mauris. Vivamus hendrerit arcu sed erat molestie vehicula. Sed auctor neque eu tellus rhoncus ut eleifend nibh porttitor.</p>
                    
                    <h4 class="mt-4">2. Hak dan Kewajiban</h4>
                    <p>Ut in nulla enim. Phasellus molestie magna non est bibendum non venenatis nisl tempor. Suspendisse dictum feugiat nisl ut dapibus.</p>
                    
                    <h4 class="mt-4">3. Pembayaran</h4>
                    <p>Sed auctor neque eu tellus rhoncus ut eleifend nibh porttitor. Ut in nulla enim. Phasellus molestie magna non est bibendum non venenatis nisl tempor.</p>
                    
                    <h4 class="mt-4">4. Pembatalan</h4>
                    <p>Vivamus hendrerit arcu sed erat molestie vehicula. Sed auctor neque eu tellus rhoncus ut eleifend nibh porttitor.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Saya Mengerti</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
         .select2-container--default .select2-selection--single,
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            border-radius: 4px;
            min-height: 38px;
            padding: 6px 12px;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            border-color: #006fe6;
            color: white;
            padding: 0 8px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: rgba(255,255,255,0.7);
            margin-right: 5px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: white;
        }
        
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
    </style>
    <style>
        .form-check.card {
            cursor: pointer;
            transition: all 0.2s;
        }
        .form-check.card:hover {
            border-color: #0d6efd;
        }
        .form-check-input:checked + .form-check-label {
            color: #0d6efd;
        }
        .terms-content h4 {
            font-weight: 600;
            color: #333;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('livewire:load', function () {
            console.log('livewire loaded');
            
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

            initSelect2();

            Livewire.hook('message.processed', (message, component) => {
                console.log("Livewire processed:", message);
                initSelect2();

                $('.select2-single').each(function () {
                    const id = $(this).attr('id');
                    if (@this[id] !== undefined) {
                        $(this).val(@this[id]).trigger('change');
                    }
                });
            });
        });
    </script>
@endpush