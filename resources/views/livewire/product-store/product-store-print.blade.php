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
                                    <select multiple class="form-control select2" id="productSelect">
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} - {{ $product->barcode }}</option>
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
                                    <div class="barcode-card" style="width: {{ (int)$width }}px; height: {{ (int)$height }}px;">
                                        <div class="label-inner">
                                            <div class="info">
                                                <h5 class="label-title">BERGY SHOP</h5>
                                                <p class="label-text">{{ $barcode['name'] }}</p>
                                                <p class="label-text">IDR {{ number_format($barcode['price'], 0, ',', '.') }}</p>
                                            </div>
                                            <div class="barcode-box">
                                                <div class="barcode-svg">
                                                    {!! $barcode['svg'] !!}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="code-text">{{ $barcode['barcode'] }}</div>
                                    </div>
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

        #barcode-preview {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start; /* mulai dari atas */
            justify-content: flex-start; /* mulai dari kiri */
            gap: 4px; /* jarak antar label (atur sesuai kebutuhan) */
            margin: 0 !important;
            padding: 0 !important;
        }

        .barcode-card {
            margin: 0 !important;
        }

        /* PRINT */
        @media print {
            body * { visibility: hidden; }
            #barcode-preview, #barcode-preview * { visibility: visible; }
            #barcode-preview {
                display: block !important; /* jangan flex, pakai block */
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .barcode-card {
                display: inline-block !important;
                border: 2px solid #000 !important; 
                box-shadow: none !important; 
                vertical-align: top;
                margin: 0 !important;
                page-break-inside: avoid;
            }
            /* @page akan disisipkan dinamis via JS sesuai pilihan (A4/A5) */
        }
        /* Optional scaling khusus A5: aktifkan dengan data attribute di <body> dari JS */
        @media print {
            body[data-paper="A5"] .barcode-card {
                transform: scale(0.88);
                transform-origin: top left;
            }
        }

        @media print {
            #barcode-preview {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-start !important;
                align-items: flex-start !important;
                margin: 0 !important;
                padding: 0 !important;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
            }

            .barcode-card {
                margin: 0 !important;
                page-break-inside: avoid;
            }
        }

        @page {
            size: {{ $paper }};
            margin: 0.5cm;
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
    window.addEventListener('print-barcodes', function (event) {
        const paper = (event.detail && event.detail.paperSize) ? event.detail.paperSize : 'A4';

        // Tandai data-paper di body (untuk scaling opsional A5)
        document.body.setAttribute('data-paper', paper);

        // Sisipkan / replace style @page dinamis
        let styleEl = document.getElementById('dynamic-print-style');
        if (!styleEl) {
            styleEl = document.createElement('style');
            styleEl.id = 'dynamic-print-style';
            document.head.appendChild(styleEl);
        }
        styleEl.textContent = `
            @page { size: ${paper}; margin: 0.5cm; }
        `;

        // Pastikan style sudah ter-apply dulu, baru print
        setTimeout(() => window.print(), 50);
    });
});
</script>
@endsection