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

                            <!-- ✅ Paper Size Settings -->
                            <div class="form-group">
                                <label class="font-weight-bold">Paper Size</label>
                                <select class="form-control" wire:model="paperSize">
                                    <option value="A4">A4 (210 x 297 mm)</option>
                                    <option value="A5">A5 (148 x 210 mm)</option>
                                    <option value="CUSTOM">Custom Size</option>
                                </select>
                            </div>

                            <!-- ✅ Custom Paper Size -->
                            @if($paperSize === 'CUSTOM')
                            <div class="card border-info mb-3">
                                <div class="card-header bg-info text-white">
                                    <small><i class="fas fa-ruler-combined mr-1"></i> Custom Paper Size</small>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Width (mm)</label>
                                                <input type="number" class="form-control" wire:model="customPaperWidth" min="50" max="500">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Height (mm)</label>
                                                <input type="number" class="form-control" wire:model="customPaperHeight" min="50" max="500">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Preview Panel -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-eye mr-2"></i>Preview
                                <small class="text-muted ml-2">
                                    (Paper: {{ $paperSize === 'CUSTOM' ? $customPaperWidth.'x'.$customPaperHeight.'mm' : $paperSize }})
                                </small>
                            </h4>
                            <button wire:click="printBarcodes" class="btn btn-primary btn-sm" @if(count($barcodePreviews) === 0) disabled @endif>
                                <i class="fas fa-print mr-1"></i> Print
                            </button>
                        </div>
                        <div class="card-body">
                            {{-- ✅ Individual Copy Control --}}
                            @if(count($barcodePreviews) > 0)
                            <div class="mt-4">
                                <h5 class="font-weight-bold mb-3">
                                    <i class="fas fa-copy mr-2"></i>Copies per Product
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="bg-light">
                                            <tr>
                                                <th width="50%">Product</th>
                                                <th width="25%">Barcode</th>
                                                <th width="25%" class="text-center">Copies</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($barcodePreviews as $barcode)
                                            <tr>
                                                <td>
                                                    <strong>{{ $barcode['name'] }}</strong><br>
                                                    <small class="text-muted">{{ $barcode['variant'] }}</small>
                                                </td>
                                                <td><code>{{ $barcode['barcode'] }}</code></td>
                                                <td>
                                                    <input 
                                                        type="number" 
                                                        class="form-control form-control-sm copy-input" 
                                                        data-product-id="{{ $barcode['id'] }}"
                                                        value="{{ $barcode['copies'] }}"
                                                        min="1" 
                                                        max="100"
                                                    >
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                            <div class="barcode-preview-wrapper" id="barcode-preview" data-paper-size="{{ $paperSize }}">
                                @foreach($barcodePreviews as $barcode)
                                    {{-- ✅ Loop sesuai jumlah copies per produk --}}
                                    @for($i = 0; $i < $barcode['copies']; $i++)
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
                                                    <div class="barcode-container mb-1">
                                                        {!! $barcode['svg'] !!}
                                                    </div>
                                                    <p class="barcode-price text-center pr-5 mb-0">
                                                        Rp. {{ number_format($barcode['price'], 0, ',', '.') }}
                                                    </p>
                                                </div>
                                            @endif
                                            
                                            {{-- ✅ Badge counter --}}
                                            @if($barcode['copies'] > 1)
                                            <div class="copy-badge">{{ $i + 1 }}/{{ $barcode['copies'] }}</div>
                                            @endif
                                        </div>
                                    @endfor
                                @endforeach

                                @if(count($barcodePreviews) === 0)
                                <div class="empty-state">
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
        /* Preview Layout */
        .barcode-preview-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .empty-state {
            width: 100%;
            text-align: center;
            padding: 50px 0;
        }

        /* Barcode Card */
        .barcode-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,.08);
            padding: 10px;
            position: relative;
            box-sizing: border-box;
            width: calc(22% - 0px);
        }

        /* Badge */
        .copy-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(0, 123, 255, 0.9);
            color: white;
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: bold;
            z-index: 10;
        }

        /* QR Code Layout */
        .qr-layout {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .qr-info {
            flex: 1;
            min-width: 0;
        }

        .qr-code {
            flex-shrink: 0;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-code svg {
            width: 100% !important;
            height: 100% !important;
        }

        /* Barcode Layout */
        .barcode-layout {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .barcode-image {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
        }

        .barcode-image svg {
            max-width: 100%;
            height: auto;
        }

        /* Text Styles */
        .label-title {
            font-weight: 700;
            margin: 0 0 4px 0;
            font-size: 12px;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .label-text {
            margin: 0 0 2px 0;
            font-size: 10px;
            line-height: 1.2;
            color: #666;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .label-price {
            font-weight: bold;
            font-size: 13px;
            margin: 4px 0 0 0;
            color: #000;
        }

        /* ===== PRINT STYLES ===== */
        @media print {
            @page {
                margin: 0;
            }

            html {
                zoom: 100%;
                transform: scale(1);
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 100%;
                height: 100%;
            }

            body * {
                visibility: hidden;
            }

            #barcode-preview,
            #barcode-preview * {
                visibility: visible;
            }

             .barcode-card, #barcode-preview * {
                box-sizing: border-box !important;
            }
            
            #barcode-preview {
                 position: fixed !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                height: 100% !important;
                margin: 0 !important;
                padding: 3mm !important;
                display: flex !important;
                flex-wrap: wrap !important;
                align-content: flex-start !important;
                /* gap: 2mm !important; */
                box-sizing: border-box !important;
            }

            .barcode-card {
                padding: 3mm !important;
                margin: 0 !important;
                border: 0.5pt solid #000 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                box-sizing: border-box !important;
                background: white !important;
                overflow: visible !important;
                display: flex !important;
                flex-direction: column !important;
            }

            .copy-badge {
                display: none !important;
            }

            /* ✅ A4: 4 kolom dengan ukuran lebih besar */
            #barcode-preview[data-paper-size="A4"] .barcode-card {
                width: 50mm !important; /* (210 - (3*3mm padding))/4 kira2 */
                min-height: 35mm !important;
                box-sizing: border-box !important;
            }

            /* ✅ A5: 2 kolom dengan ukuran lebih besar */
            #barcode-preview[data-paper-size="A5"] .barcode-card {
                width: 50mm !important; /* (210 - (3*3mm padding))/4 kira2 */
                min-height: 35mm !important;
                box-sizing: border-box !important;
            }

            /* QR Layout for print */
            .qr-layout {
                display: flex !important;
                gap: 3mm !important;
                height: 100% !important;
                align-items: stretch !important;
            }

            .qr-info {
                flex: 1 !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
            }

            .qr-code {
                flex: 0 0 28mm !important;
                width: 28mm !important;
                height: 28mm !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .qr-code svg {
                width: 100% !important;
                height: 100% !important;
                max-width: 100% !important;
                max-height: 100% !important;
            }

            /* Barcode layout for print */
            .barcode-layout {
                display: flex !important;
                flex-direction: column !important;
                height: 100% !important;
                justify-content: space-between !important;
            }

            .barcode-image {
                flex: 1 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 2mm 0 !important;
            }

            .barcode-image svg {
                max-width: 100% !important;
                max-height: 100% !important;
                width: auto !important;
                height: auto !important;
            }

            /* ✅ Font sizes lebih besar untuk print */
            .label-title {
                font-size: 11pt !important;
                font-weight: 700 !important;
                margin-bottom: 1mm !important;
                line-height: 1.2 !important;
                color: #000 !important;
            }

            .label-text {
                font-size: 9pt !important;
                margin-bottom: 0.5mm !important;
                color: #333 !important;
                line-height: 1.2 !important;
            }

            .label-price {
                font-size: 12pt !important;
                font-weight: 700 !important;
                margin-top: 1mm !important;
                color: #000 !important;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
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
    // Select2 initialization
    $('#productSelect').select2({
        placeholder: 'Select products',
        allowClear: true
    }).on('change', function () {
        @this.set('selectedProducts', $(this).val());
        @this.updatePreview();
    });

    Livewire.hook('message.processed', () => {
        $('#productSelect').select2({ placeholder: 'Select products', allowClear: true });
        attachCopyInputListeners();
    });

    // ✅ Auto-update copies saat input berubah
    function attachCopyInputListeners() {
        document.querySelectorAll('.copy-input').forEach(input => {
            input.removeEventListener('change', handleCopyChange);
            input.addEventListener('change', handleCopyChange);
        });
    }

    function handleCopyChange(e) {
        const productId = e.target.dataset.productId;
        const copies = parseInt(e.target.value) || 1;
        
        // Update Livewire
        @this.updateProductCopy(productId, copies);
    }

    // Initial attachment
    attachCopyInputListeners();

    // ✅ Print handler
    window.addEventListener('print-barcodes', function (event) {
        const config = event.detail;
        const paperSize = config.paperSize || 'A4';
        const customWidth = parseFloat(config.customWidth) || 210;
        const customHeight = parseFloat(config.customHeight) || 297;
        
        const previewContainer = document.getElementById('barcode-preview');
        if (!previewContainer) return;
        
        previewContainer.setAttribute('data-paper-size', paperSize);

        let style = document.getElementById('dynamic-print-style');
        if (!style) {
            style = document.createElement('style');
            style.id = 'dynamic-print-style';
            document.head.appendChild(style);
        }
        
        // Calculate columns for custom size
        let columns = 2;
        if (paperSize === 'CUSTOM') {
            if (customWidth >= 200) columns = 4;
            else if (customWidth >= 140) columns = 3;
            else if (customWidth >= 100) columns = 2;
            else columns = 1;
        }

        if (paperSize === 'CUSTOM') {
            const gapTotal = (columns - 1) * 2;
            style.textContent = `
                @page { 
                    size: ${customWidth}mm ${customHeight}mm portrait; 
                    margin: 0;
                }
                
                @media print {
                    #barcode-preview[data-paper-size="CUSTOM"] .barcode-card {
                        width: calc((100% - ${gapTotal}mm) / ${columns}) !important;
                        min-height: 35mm !important;
                    }
                }
            `;
        } else if (paperSize === 'A5') {
            style.textContent = `
                @page { 
                    size: 148mm 210mm portrait; 
                    margin: 0;
                }
            `;
        } else {
            style.textContent = `
                @page { 
                    size: 210mm 297mm portrait; 
                    margin: 0;
                }
            `;
        }

        setTimeout(() => {
            window.print();
        }, 250);
    });
});
</script>
@endsection