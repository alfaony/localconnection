<div>
    <div class="row">
        @include('components.alert')
        <div class="col-md-12 mt-3">
            <div class="card">
                <div class="card-header bg-gradient-primary">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3 class="card-title text-white">
                                <i class="fas fa-eye mr-2"></i> Detail Produk
                            </h3>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="{{ route('product-store.index') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Produk
                            </a>
                            <button wire:click="editProduct('{{ $product->id }}')" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit mr-1"></i> Edit Produk
                            </button>
                        </div>
                    </div>
                </div>
        
                <div class="card-body">
                    <div class="row">
                        <!-- Product Information -->
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-info-circle mr-2"></i>Informasi Produk
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-group">
                                                <label class="font-weight-bold text-primary">Nama Produk</label>
                                                <p class="info-value">{{ $product->name }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-group">
                                                <label class="font-weight-bold text-primary">Nomor Barcode</label>
                                                <p class="info-value">{{ $product->barcode }}</p>
                                            </div>
                                        </div>
                                    </div>
        
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-group">
                                                <label class="font-weight-bold text-primary">Kategori</label>
                                                <p class="info-value">
                                                    <span class="badge badge-info badge-pill px-3 py-2">{{ $product->category->name ?? '-' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-group">
                                                <label class="font-weight-bold text-primary">Merk</label>
                                                <p class="info-value">
                                                    <span class="badge badge-secondary badge-pill px-3 py-2">{{ $product->brand->name ?? '-' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
        
                                    <div class="info-group">
                                        <label class="font-weight-bold text-primary">Variant</label>
                                        <p class="info-value">{{ $product->variant ?? '-' }}</p>
                                    </div>
        
                                    <div class="info-group">
                                        <label class="font-weight-bold text-primary">Spesifikasi</label>
                                        <div class="specification-box">
                                            {!! nl2br(e($product->specification)) ?? '-' !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
        
                        <!-- Pricing & Details -->
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-tag mr-2"></i>Harga & Detail
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="info-group">
                                        <label class="font-weight-bold text-primary">Harga Jual</label>
                                        <p class="price-display">
                                            Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                        </p>
                                    </div>
        
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-group">
                                                <label class="font-weight-bold text-primary">Dimensi</label>
                                                <p class="info-value">{{ $product->dimension ?? '-' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-group">
                                                <label class="font-weight-bold text-primary">Berat</label>
                                                <p class="info-value">
                                                    {{ $product->weight ? number_format($product->weight, 2, ',', '.') . ' g' : '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
        
                                    <div class="info-group">
                                        <label class="font-weight-bold text-primary">Dibuat OleH</label>
                                        <p class="info-value">{{ $product->creator->name ?? '-' }}</p>
                                    </div>
        
                                    <div class="info-group">
                                        <label class="font-weight-bold text-primary">Terakhir Diperbarui</label>
                                        <p class="info-value">
                                            {{ $product->updated_at->format('d M Y H:i') }}
                                            @if($product->modifier)
                                                <br><small>oleh {{ $product->modifier->name }}</small>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
        
                    <!-- Barcode Section -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-barcode mr-2"></i>Kode Barcode Produk
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <!-- Barcode 1 -->
                                <div class="col-md-4 mb-4">
                                    <div class="barcode-card text-center p-2 pl-4">
                                        <div class="d-flex justify-content-center flex-column">
                                            <h3 class="font-weight-bold mb-0 p-2">{{ $product->name }}</h3>
                                            <div class="barcode-container flex-fill p-2">
                                                {!! $barcode1Svg !!}
                                            </div>
                                            <div class="p-2 ml-4">
                                                <p class="text-left h3">Rp. {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
        
                                <!-- QR Code -->
                                <div class="col-md-4 mb-4">
                                    <div class="barcode-card" style="border: 2px solid #000; padding: 20px;">
                                        <div class="row align-items-center">
                                            <!-- Informasi Produk -->
                                            <div class="col-6">
                                                <h4 class="font-weight-bold mb-1" style="font-size: 1.4rem; color: #000;">{{ $product->name  }}</h4>
                                                <p class="mb-1" style="font-size: 1.1rem; color: #333;">{{ $product->brand->name ?? '-' }}</p>
                                                <p class="mb-1" style="font-size: 1.1rem; color: #333;">Size {{ $product->variant ?? '-' }}</p>
                                                <p class="h4 mt-2 mb-0" style="font-weight: bold; color: #000;">
                                                    IDR {{ number_format($product->selling_price, 0, ',', '.') }}
                                                </p>
                                            </div>
                                            
                                            <!-- QR Code -->
                                            <div class="col-6 text-center">
                                                <div class="d-inline-block">
                                                    {!! $qrCodeSvg !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{--
                            <div class="text-center mt-4">
                                <button wire:click="printBarcodes" class="btn btn-primary btn-lg">
                                    <i class="fas fa-print mr-2"></i> Cetak Kode Barcode
                                </button>
                            </div>
                            --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@section('js')
<script>
    document.addEventListener('livewire:load', function() {
        Livewire.on('print-barcodes', () => {
            window.print();
        });
    });
</script>
@endsection

@section('css')
    <style>
        .info-group {
            margin-bottom: 1.5rem;
        }
        
        .info-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .info-value {
            min-height: 2.2rem;
            padding: 0.5rem;
            background-color: #f8f9fa;
            border-radius: 0.35rem;
            margin-bottom: 0;
            font-size: 1rem;
        }
        
        .specification-box {
            background-color: #f8f9fa;
            border-radius: 0.35rem;
            padding: 1rem;
            min-height: 100px;
            line-height: 1.6;
        }
        
        .price-display {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
            background-color: #f8f9fa;
            padding: 0.75rem;
            border-radius: 0.35rem;
            text-align: center;
        }
        
        .barcode-card {
            border: 1px solid #e3e6f0;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .barcode-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .barcode-header {
            background: linear-gradient(180deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 0.75rem;
        }
        
        .qr-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
            background: white;
            min-height: 150px;
        }
        
        .barcode-footer {
            padding: 0.75rem;
            background-color: #f8f9fa;
            border-top: 1px solid #e3e6f0;
        }
        
        .barcode-number {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            margin-bottom: 0;
            color: #5a5c69;
        }
        
        @media (max-width: 768px) {
            .barcode-card {
                margin-bottom: 1.5rem;
            }
        }
        
        @media print {
            .card-header, .btn, .col-md-4.text-center:last-child {
                display: none !important;
            }
            
            .barcode-card {
                break-inside: avoid;
                box-shadow: none !important;
                border: 1px solid #000 !important;
                margin-bottom: 20px;
            }
            
            .barcode-card:hover {
                transform: none !important;
            }
            
            .col-md-4 {
                width: 50% !important;
                float: left;
            }
            
            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
    <style>
        .barcode-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .barcode-card:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        @media print {
            .barcode-card {
                border: 2px solid #000 !important;
                box-shadow: none !important;
            }
        }
    </style>
@endsection
