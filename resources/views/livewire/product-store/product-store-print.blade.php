@php
    $paper = $paperSize ?? 'A4';
@endphp
<div>
    <div class="card">
        <div class="card-header bg-gradient-primary">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="card-title text-white">
                        <i class="fas fa-barcode mr-2"></i> Print Barcodes
                    </h3>
                </div>
                <div class="col-md-6 text-right">
                    <a href="{{ route('product-store.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Products
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <!-- Settings Panel -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-cog mr-2"></i>Print Settings
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="font-weight-bold">Select Products</label>
                                <div wire:ignore>
                                    <select multiple class="form-control" id="productSelect">
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} - {{ $product->variant }} - {{ $product->barcode }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Barcode Type</label>
                                <select class="form-control" wire:model="barcodeType" wire:change="updatePreview">
                                    <option value="QRCODE">QR Code</option>
                                    <option value="CODE128">Code 128</option>
                                    <option value="CODE39">Code 39</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Jumlah Copy</label>
                                <input type="number" class="form-control" wire:model="copies" min="1" max="100">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Width (px)</label>
                                        <input type="number" class="form-control" wire:model="width" wire:change="updatePreview" min="50" max="500">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Height (px)</label>
                                        <input type="number" class="form-control" wire:model="height" wire:change="updatePreview" min="50" max="500">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Paper Size</label>
                                <select class="form-control" wire:model="paperSize">
                                    <option value="A4">A4</option>
                                    <option value="A5">A5</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Panel -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-eye mr-2"></i>Preview
                                <small class="text-muted ml-2">(Paper: {{ $paperSize }})</small>
                            </h4>
                            <button wire:click="printBarcodes" class="btn btn-primary btn-sm">
                                <i class="fas fa-print mr-1"></i> Print
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row mr-3 mb-3" id="barcode-preview">
                                @foreach($barcodePreviews as $barcode)
                                    @for($i = 0; $i < $copies; $i++)
                                        <div class="barcode-card">
                                            @if($barcodeType === 'QRCODE')
                                                <div class="row align-items-center">
                                                    <div class="col-6">
                                                        <h5 class="label-title mb-1">{{ $barcode['name'] }}</h5>
                                                        <p class="label-text mb-1">{{ $barcode['brand'] ?? '' }}</p>
                                                        <p class="label-text mb-1">{{ $barcode['variant'] ?? '' }}</p>
                                                        <p class="label-text text-bold">Rp. {{ number_format($barcode['price'], 0, ',', '.') }}</p>
                                                    </div>
                                                    <div class="col-6 text-center">
                                                        <div class="d-inline-block">
                                                            {!! $barcode['svg'] !!}
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($barcodeType === 'CODE128' || $barcodeType === 'CODE39')
                                                <div class="barcode-label text-center">
                                                    <h5 class="label-title mb-1 font-weight-bold">{{ $barcode['name'] }}</h5>
                                                    <div class="barcode-container">
                                                        {!! $barcode['svg'] !!}
                                                    </div>
                                                    <p class="barcode-price text-left mb-0 ml-3">
                                                        Rp. {{ number_format($barcode['price'], 0, ',', '.') }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    @endfor
                                @endforeach

                                @if(count($barcodePreviews) === 0)
                                <div class="col-12 text-center py-5">
                                    <i class="fas fa-barcode fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Select products to generate barcode preview</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css">
    <style>
        .barcode-card {
            background-color: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,.08);
            padding: 8px;                /* padding konsisten */
            display: flex;
            flex-direction: column;
            overflow: hidden;            /* cegah konten keluar kotak */
        }

        /* A5 Specific Layout */
        .barcode-preview-container[data-paper-size="A5"] {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 8px;
        }

        /* Grid 2 kolom: info (kiri) dan barcode (kanan) */
        .label-inner {
            display: grid;
            grid-template-columns: 1.4fr 1fr; /* rasio kiri:kanan */
            gap: 8px;
            flex: 1;                            /* isi tinggi kontainer */
            min-height: 0;                      /* biar child bisa shrink */
        }

        /* Area teks kiri */
        .info {
            overflow: hidden;
        }
        .label-title {
            font-weight: 700;
            margin: 0 0 4px 0;
            font-size: 12px;
            line-height: 1.1;
        }
        .label-text {
            margin: 0;
            font-size: 11px;
            line-height: 1.15;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis; /* potong jika kepanjangan */
        }

        /* Area barcode kanan: auto-fit penuh tinggi/lebarnya */
      .barcode-box {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center; 
            justify-content: center;
        }

        .barcode-svg {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .barcode-svg svg {
            width: 100% !important;
            height: 100% !important;
            max-width: 100% !important;
            max-height: 100% !important;
            object-fit: contain; /* ini kunci: selalu menyesuaikan */
        }

        .barcode-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            height: 100%;
            padding: 4px;
        }

        .barcode-container {
            width: 100%;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .barcode-container svg {
            max-width: 100%;
            height: auto;
        }

        .barcode-code {
            font-size: 12px;
            margin-top: 2px;
        }

        .barcode-price {
            font-size: 14px;
            font-weight: bold;
            align-self: flex-start;
        }

        /* Kode di bawah label */
        .code-text {
            margin-top: 4px;
            font-size: 10px;
            text-align: center;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .barcode-preview-container[data-paper-size="A4"] .barcode-item {
                font-size: 13px;
                width: 25%;
                float: left;
                padding: 5px;
                box-sizing: border-box;
                height: 120px;
            }

            /* A5 Print Layout - More compact */
            .barcode-preview-container[data-paper-size="A5"] .barcode-item {
                font-size: 13px;
                width: 31%; /* sedikit lebih kecil dari 33.33% */
                padding: 2px;
                box-sizing: border-box;
            }

        .barcode-card {
            /* margin: 0 !important; */
            box-sizing: border-box;
            overflow: hidden;
        }

        /* PRINT */
        @media print {
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
        }

        body * {
            visibility: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        #barcode-preview, #barcode-preview * {
            visibility: visible !important;
        }

        #barcode-preview {
            margin: 0 !important;
            padding: 0 !important;
            display: flex !important;
            flex-wrap: wrap !important;
            justify-content: flex-start !important;
            align-items: flex-start !important;
            width: 100% !important;

            /* kunci: biar nempel atas kiri */
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
        }

        .barcode-card {
            padding: 10px !important;
            display: inline-block !important;
            border: 1px solid #000 !important;
            box-shadow: none !important;
            margin: 2px !important;
            page-break-inside: avoid !important;
            box-sizing: border-box;
        }

        /* Layout A4: 4 kolom */
        #barcode-preview[data-paper-size="A4"] .barcode-card {
            width: 50mm !important;
            height: auto !important;
        }

        /* A5 Layout: 2 kolom biar proporsional */
        #barcode-preview[data-paper-size="A5"] .barcode-card {
            width: 70mm !important;
            height: auto !important;
        }
    }

    @page {
        size: A5;
        margin: 0 !important;   /* ✅ hilangkan margin kosong */
    }

        /* PAGE SETTINGS */
        @page {
            size: A4;
            margin: 0.5cm;
        }

        body[data-paper="A5"] @page {
            size: A5;
            margin: 0.3cm;
        }

        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 6px;
            height: auto;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
            padding: 3px 10px;
        }
        .select2-selection__rendered {
            line-height: 31px !important;
        }
        .select2-container .select2-selection--single {
            height: 38px !important;
        }
        .select2-selection__arrow {
            height: 36px !important;
        }
    </style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
document.addEventListener('livewire:load', function () {
    // Select2
    $('#productSelect').select2({
        placeholder: 'Select products',
        allowClear: true
    }).on('change', function () {
        @this.set('selectedProducts', $(this).val());
        @this.updatePreview();
    });

    // Re-init Select2 setelah DOM Livewire update
    Livewire.hook('message.processed', () => {
        $('#productSelect').select2({ placeholder: 'Select products', allowClear: true });
    });

    // ✅ Tangkap event dari dispatchBrowserEvent (window-level)
    window.addEventListener('print-barcodes', function (event) 
    {
                const paperSize = @this.paperSize || 'A4';
                
                // Update data attribute for CSS
                const previewContainer = document.getElementById('barcode-preview');
                if (previewContainer) {
                    previewContainer.setAttribute('data-paper-size', paperSize);
                }

                // Dynamic @page styling
                let style = document.getElementById('dynamic-print-style');
                if (!style) {
                    style = document.createElement('style');
                    style.id = 'dynamic-print-style';
                    document.head.appendChild(style);
                }
                
                // Set page size based on selection
                if (paperSize === 'A5') {
                    console.log("A5");
                    
                    style.textContent = `
                        @page { 
                            size: A5; 
                            margin: 0.3cm;
                        }
                        body { 
                            margin: 0.3cm !important;
                        }
                    `;
                }
                 else {
                    style.textContent = `
                        @page { 
                            size: A4; 
                            margin: 0.5cm;
                        }
                        body { 
                            margin: 0.5cm !important; 
                        }
                    `;
                }

                // Wait for styles to apply then print
                setTimeout(() => {
                    window.print();
                }, 100);
            });
    });
</script>
@endsection