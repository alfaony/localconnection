<div class="row">
    <div class="col-md-12 mt-2">
        <div class="card promo-card">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <div class="feature-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div>
                        <h3 class="card-title mb-0">
                            {{ isset($promo) ? 'Edit Promo: ' . $promo->name : 'Form Tambah Promo' }}
                        </h3>
                        <p class="mb-0">Kelola promo untuk pelanggan Anda</p>
                    </div>
                </div>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="{{ isset($promo) ? 'update' : 'save' }}">
                    <!-- Nama Promo -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-tag me-2"></i> Nama Promo
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-pencil-alt"></i>
                            </span>
                            <input type="text" wire:model="name" class="form-control" placeholder="Masukkan nama promo">
                        </div>
                        @error('name')
                            <div class="error-message text-danger">
                                <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Jenis Promo -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-list me-2"></i> Jenis Promo
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-gift"></i>
                            </span>
                            <select wire:model="type" class="form-control">
                                <option value="" selected disabled>Pilih Jenis Promo</option>
                                @foreach (config('custom.promo_type') as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('type')
                            <div class="error-message text-danger">
                                <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Nilai -->
                    <div class="mb-4">
                        <label class="form-label">
                            @if($type == 'percentage')
                                <i class="fas fa-percentage"></i> Besaran Persen
                            @elseif($type == 'free_months')
                                <i class="fas fa-calendar-check"></i> Jumlah Gratis Bulan
                            @else
                                <i class="fas fa-money-bill"></i> Besaran Potongan Harga
                            @endif
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                @if($type == 'percentage')
                                    <i class="fas fa-percentage"></i>
                                @elseif($type == 'free_months')
                                    <i class="fas fa-calendar-check"></i>
                                @else
                                    <i class="fas fa-money-bill"></i>
                                @endif
                            </span>
                            <input type="number" wire:model="value" class="form-control" placeholder="Masukkan nilai promo">
                        </div>
                        @if($type == 'percentage')
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i> Masukkan nilai persentase (contoh: 10 untuk 10%)
                            </div>
                        @endif
                        @error('value')
                            <div class="error-message text-danger">
                                <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Package Internet (Multi-select) -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-wifi me-2"></i> Paket Internet
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-network-wired"></i>
                            </span>
                            <select wire:model="packageInternets" class="form-control select2" multiple="multiple">
                                @foreach($allPackages as $package)
                                    <option value="{{ $package->id }}">
                                        {{ $package->name }} - Rp{{ number_format($package->price_nett, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i> Pilih paket internet yang akan mendapatkan promo
                        </div>
                    </div>

                    <!-- Conditional Section for Free Months -->
                    @if($type == 'free_months')
                        <div class="mb-4 conditional-section">
                            <label class="form-label">
                                <i class="fas fa-calendar-check me-2"></i> Batas Waktu Pendaftaran
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="far fa-calendar-alt"></i>
                                </span>
                                <input type="date" wire:model="register_date" class="form-control">
                            </div>
                            <div class="form-text mt-2">
                                <i class="fas fa-info-circle me-1"></i> Promo ini berlaku sebelum batas pendaftaran di tanggal tersebut, jika lebih dari tanggal pendaftaran maka promo terhitung pada bulan berikutnya
                            </div>
                        </div>
                    @endif

                    <!-- Periode -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt me-2"></i> Periode
                        </label>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label small">Mulai Tanggal</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-play"></i>
                                    </span>
                                    <input type="date" wire:model="start_date" min="{{ $start_date ? $start_date : \Carbon\Carbon::now()->addDays()->format('Y-m-d') }}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Sampai Tanggal</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-stop"></i>
                                    </span>
                                    <input type="date" wire:model="end_date" min="{{ $end_date ? $end_date : \Carbon\Carbon::now()->addDays()->format('Y-m-d') }}" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kuota -->
                     {{-- 
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-ticket-alt me-2"></i> Kuota (opsional)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-hashtag"></i>
                            </span>
                            <input type="number" wire:model="quota" class="form-control" placeholder="Masukkan jumlah kuota">
                        </div>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i> Kosongkan jika tidak ada batasan kuota
                        </div>
                    </div>
                    --}}

                    <!-- Status Aktif -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input type="checkbox" wire:model="is_active" class="form-check-input" id="activeCheck" role="switch">
                            <label class="form-check-label fw-bold" for="activeCheck">
                                <i class="fas fa-power-off me-2"></i> Aktifkan Promo
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center mt-5 pt-3 border-top">
                        <a href="{{ route('promo.index') }}" class="btn btn-md btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Kembali
                        </a>
                        <button class="btn btn-md btn-primary">
                            <i class="fas fa-save me-2"></i> Simpan Promo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@section('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        // Inisialisasi Select2
        document.addEventListener('livewire:load', function () {
            $('.select2').select2({
                placeholder: "Pilih paket internet",
                allowClear: true
            });
            
            // Update Livewire ketika ada perubahan di select2
            $('.select2').on('change', function (e) {
                let data = $(this).val();
                @this.set('packageInternets', data);
            });
            
            // Update select2 ketika Livewire di-render
            Livewire.hook('message.processed', (message, component) => {
                $('.select2').select2({
                    placeholder: "Pilih paket internet",
                    allowClear: true
                });
            });
        });
        
        // Tangani perubahan jenis promo untuk ikon nilai
        document.addEventListener('livewire:update', function () {
            const type = @this.type;
            let icon = document.querySelector('.input-group-text i');
            
            if (type === 'percentage') {
                icon.className = 'fas fa-percentage';
            } else {
                icon.className = 'fas fa-money-bill';
            }
        });
    </script>
@stop

@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>        
        .promo-card {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: none;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(120deg, var(--primary), #2a48a3);
            color: white;
            font-weight: 700;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.25rem 1.5rem;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .input-group-text {
            background-color: #eef1f7;
            border: 1px solid #d1d3e2;
            color: var(--primary);
        }
        
        
        .error-message {
            font-size: 0.85rem;
            padding-top: 0.25rem;
        }
        
        .btn-primary {
            background: linear-gradient(120deg, var(--primary), #2a48a3);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(78, 115, 223, 0.4);
        }
        
        .btn-secondary {
            background-color: var(--secondary);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        
        .conditional-section {
            background-color: #f0f8ff;
            border-radius: 8px;
            padding: 1.25rem;
            border-left: 4px solid var(--info);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .form-check-input:checked {
            background-color: var(--success);
            border-color: var(--success);
        }
        
        .form-text {
            font-size: 0.85rem;
            color: #6c757d;
        }

          .select2-container--default .select2-selection--single 
        {
            height: 38px !important;
            padding: 5px 10px !important;
        }
        .select2-selection__choice
        {
            background-color: #007bff !important;
            border: 1px solid #007bff !important;
        }

        .select2-selection__choice__remove
        {
            color: #fe0700 !important;
            border: 1px solid #007bff !important;
        }
        
    </style>
@stop