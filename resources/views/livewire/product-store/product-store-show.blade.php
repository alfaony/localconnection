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
                            @canAccess('edit','product_stores')
                            <a href="{{ route('product-store.edit', $product->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Edit
                            </a>
                            @endcanAccess
                        </div>
                    </div>
                </div>
        
                <div class="card-body">
                    <!-- ============================================ -->
                    <!-- PRODUCT IMAGES GALLERY SECTION -->
                    <!-- ============================================ -->
                    @if($product->media && $product->media->count() > 0)
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-images mr-2"></i>Galeri Foto Produk
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Main Image Display -->
                                <div class="col-md-7">
                                    <div class="main-image-container">
                                        <div class="main-image-wrapper">
                                            <img id="mainImage" 
                                                 src="{{ $product->media->first()->file_url }}" 
                                                 alt="{{ $product->name }}"
                                                 class="img-fluid main-product-image">
                                            
                                            @if($product->media->first()->caption)
                                            <div class="image-caption">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                {{ $product->media->first()->caption }}
                                            </div>
                                            @endif

                                            <!-- Zoom Icon -->
                                            <button class="zoom-icon" onclick="openImageModal('{{ $product->media->first()->file_url }}')">
                                                <i class="fas fa-search-plus"></i>
                                            </button>

                                            <!-- Primary Badge -->
                                            <div class="primary-badge">
                                                <i class="fas fa-star mr-1"></i> Foto Utama
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Thumbnail Gallery -->
                                <div class="col-md-5">
                                    <div class="thumbnails-container">
                                        <h6 class="text-muted mb-3">
                                            <i class="fas fa-th mr-1"></i> Semua Foto ({{ $product->media->count() }})
                                        </h6>
                                        <div class="thumbnails-grid">
                                            @foreach($product->media as $index => $media)
                                            <div class="thumbnail-item {{ $index === 0 ? 'active' : '' }}" 
                                                 onclick="changeMainImage('{{ $media->file_url }}', '{{ $media->caption ?? '' }}', this)">
                                                <img src="{{ $media->file_url }}" 
                                                     alt="Foto {{ $index + 1 }}"
                                                     class="thumbnail-image">
                                                
                                                @if($index === 0)
                                                <div class="thumbnail-primary-badge">
                                                    <i class="fas fa-star"></i>
                                                </div>
                                                @endif

                                                <div class="thumbnail-order-badge">
                                                    #{{ $index + 1 }}
                                                </div>

                                                <!-- Zoom button for thumbnail -->
                                                <button class="thumbnail-zoom" onclick="event.stopPropagation(); openImageModal('{{ $media->file_url }}')">
                                                    <i class="fas fa-search-plus"></i>
                                                </button>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <!-- No Images State -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-images fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum Ada Foto Produk</h5>
                            <p class="text-muted mb-3">Upload foto produk untuk menampilkan galeri di sini</p>
                            @canAccess('edit','product_stores')
                            <a href="{{ route('product-store.edit', $product->id) }}" class="btn btn-primary">
                                <i class="fas "></i> Edit Produk
                            </a>
                            @endcanAccess
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <!-- Product Information -->
                        <div class="col-md-8">
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-light">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-info-circle mr-2"></i>Informasi Produk
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-group">
                                                <label class="font-weight-bold text-primary">
                                                    <i class="fas fa-tag mr-1"></i> Nama Produk
                                                </label>
                                                <p class="info-value">{{ $product->name }} {{ $product->code ? ' (' . $product->code . ')' : '' }} </p> 
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-group">
                                                <label class="font-weight-bold text-primary">
                                                    <i class="fas fa-barcode mr-1"></i> Nomor Barcode
                                                </label>
                                                <p class="info-value">{{ $product->barcode }}</p>
                                            </div>
                                        </div>
                                    </div>
        
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-group">
                                                <label class="font-weight-bold text-primary">
                                                    <i class="fas fa-th-large mr-1"></i> Kategori
                                                </label>
                                                <p class="info-value">
                                                    <span class="badge badge-info badge-pill px-3 py-2">{{ $product->category->name ?? '-' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-group">
                                                <label class="font-weight-bold text-primary">
                                                    <i class="fas fa-copyright mr-1"></i> Merk
                                                </label>
                                                <p class="info-value">
                                                    <span class="badge badge-secondary badge-pill px-3 py-2">{{ $product->brand->name ?? '-' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
        
                                    <div class="info-group">
                                        <label class="font-weight-bold text-primary">
                                            <i class="fas fa-layer-group mr-1"></i> Variant
                                        </label>
                                        <p class="info-value">{{ $product->variant ?? '-' }}</p>
                                    </div>
        
                                    <div class="info-group">
                                        <label class="font-weight-bold text-primary">
                                            <i class="fas fa-clipboard-list mr-1"></i> Spesifikasi
                                        </label>
                                        <div class="specification-box">
                                            {!! nl2br(e($product->specification)) ?? '<span class="text-muted">Tidak ada spesifikasi</span>' !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
        
                        <!-- Pricing & Details -->
                        <div class="col-md-4">
                            <div class="card mb-4 shadow-sm">
                                <div class="card-header bg-light">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-dollar-sign mr-2"></i>Harga & Detail
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="info-group">
                                        <label class="font-weight-bold text-primary">
                                            <i class="fas fa-money-bill-wave mr-1"></i> Harga Jual
                                        </label>
                                        <p class="price-display">
                                            Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                        </p>
                                    </div>
        
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-group">
                                                <label class="font-weight-bold text-primary">
                                                    <i class="fas fa-ruler-combined mr-1"></i> Dimensi
                                                </label>
                                                <p class="info-value">{{ $product->dimension ?? '-' }}</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-group">
                                                <label class="font-weight-bold text-primary">
                                                    <i class="fas fa-weight mr-1"></i> Berat
                                                </label>
                                                <p class="info-value">
                                                    {{ $product->weight ? number_format($product->weight, 2, ',', '.') . ' g' : '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
        
                                    <div class="info-group">
                                        <label class="font-weight-bold text-primary">
                                            <i class="fas fa-user mr-1"></i> Dibuat Oleh
                                        </label>
                                        <p class="info-value">{{ $product->creator->name ?? '-' }}</p>
                                    </div>
        
                                    <div class="info-group">
                                        <label class="font-weight-bold text-primary">
                                            <i class="fas fa-clock mr-1"></i> Terakhir Diperbarui
                                        </label>
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
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h4 class="card-title mb-0">
                                <i class="fas fa-barcode mr-2"></i>Kode Barcode Produk
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <!-- Barcode 1 -->
                                <div class="col-md-6 mb-4">
                                    <div class="barcode-card text-center p-3">
                                        <div class="d-flex justify-content-center flex-column">
                                            <h3 class="font-weight-bold mb-3">{{ $product->name }}</h3>
                                            <div class="barcode-container flex-fill p-2">
                                                {!! $barcode1Svg !!}
                                            </div>
                                            <div class="p-2">
                                                <p class="h3 text-success font-weight-bold">Rp. {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
        
                                <!-- QR Code -->
                                <div class="col-md-6 mb-4">
                                    <div class="barcode-card" style="border: 2px solid #000; padding: 20px;">
                                        <div class="row align-items-center">
                                            <div class="col-6 text-left">
                                                <h4 class="font-weight-bold mb-2" style="font-size: 1.4rem; color: #000;">{{ $product->name }}</h4>
                                                <p class="mb-1" style="font-size: 1.1rem; color: #333;">{{ $product->brand->name ?? '-' }}</p>
                                                <p class="mb-2" style="font-size: 1.1rem; color: #333;">Size {{ $product->variant ?? '-' }}</p>
                                                <p class="h4 mt-2 mb-0 text-success font-weight-bold">
                                                    IDR {{ number_format($product->selling_price, 0, ',', '.') }}
                                                </p>
                                            </div>
                                            
                                            <div class="col-6 text-center">
                                                <div class="d-inline-block">
                                                    {!! $qrCodeSvg !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- IMAGE MODAL FOR FULLSCREEN VIEW -->
    <!-- ============================================ -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body p-0 position-relative">
                    <button type="button" class="close-modal-btn" data-dismiss="modal">
                        <i class="fas fa-times"></i>
                    </button>
                    <img id="modalImage" src="" alt="Product Image" class="img-fluid w-100 rounded">
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

    // ============================================
    // IMAGE GALLERY FUNCTIONS
    // ============================================
    function changeMainImage(imageUrl, caption, thumbnailElement) {
        // Update main image
        const mainImage = document.getElementById('mainImage');
        mainImage.src = imageUrl;
        
        // Update caption
        const captionElement = mainImage.parentElement.querySelector('.image-caption');
        if (captionElement) {
            if (caption) {
                captionElement.innerHTML = '<i class="fas fa-info-circle mr-1"></i>' + caption;
                captionElement.style.display = 'block';
            } else {
                captionElement.style.display = 'none';
            }
        }
        
        // Update active thumbnail
        document.querySelectorAll('.thumbnail-item').forEach(item => {
            item.classList.remove('active');
        });
        thumbnailElement.classList.add('active');
        
        // Smooth fade effect
        mainImage.style.opacity = '0';
        setTimeout(() => {
            mainImage.style.opacity = '1';
        }, 100);
    }

    function openImageModal(imageUrl) {
        const modalImage = document.getElementById('modalImage');
        modalImage.src = imageUrl;
        $('#imageModal').modal('show');
    }
</script>
@endsection

@section('css')
<style>
    /* ============================================ */
    /* PRODUCT IMAGE GALLERY STYLES */
    /* ============================================ */
    .main-image-container {
        position: relative;
        background: #f8f9fa;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .main-image-wrapper {
        position: relative;
        padding-bottom: 75%; /* 4:3 aspect ratio */
        overflow: hidden;
    }

    .main-product-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: opacity 0.3s ease;
        background: white;
    }

    .image-caption {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.75);
        color: white;
        padding: 12px 16px;
        font-size: 0.9rem;
        backdrop-filter: blur(5px);
    }

    .zoom-icon {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(0, 0, 0, 0.6);
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 10;
    }

    .zoom-icon:hover {
        background: rgba(0, 0, 0, 0.8);
        transform: scale(1.1);
    }

    .primary-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.4);
    }

    /* Thumbnails */
    .thumbnails-container {
        height: 100%;
    }

    .thumbnails-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 12px;
        max-height: 500px;
        overflow-y: auto;
        padding-right: 5px;
    }

    /* Custom scrollbar */
    .thumbnails-grid::-webkit-scrollbar {
        width: 6px;
    }

    .thumbnails-grid::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .thumbnails-grid::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .thumbnails-grid::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .thumbnail-item {
        position: relative;
        padding-bottom: 100%;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
        border: 3px solid transparent;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .thumbnail-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .thumbnail-item.active {
        border-color: #667eea;
        box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.3);
    }

    .thumbnail-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .thumbnail-primary-badge {
        position: absolute;
        top: 5px;
        left: 5px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        z-index: 2;
    }

    .thumbnail-order-badge {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .thumbnail-zoom {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.7);
        color: white;
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 3;
    }

    .thumbnail-item:hover .thumbnail-zoom {
        display: flex;
    }

    .thumbnail-zoom:hover {
        background: rgba(0, 0, 0, 0.9);
        transform: translate(-50%, -50%) scale(1.1);
    }

    /* Modal Styles */
    .modal-content.bg-transparent {
        box-shadow: none;
    }

    .close-modal-btn {
        position: absolute;
        top: -40px;
        right: 0;
        background: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1.2rem;
        color: #333;
        z-index: 1060;
        transition: all 0.3s ease;
    }

    .close-modal-btn:hover {
        background: #f8f9fa;
        transform: rotate(90deg);
    }

    #modalImage {
        max-height: 85vh;
        object-fit: contain;
        background: white;
    }

    /* ============================================ */
    /* EXISTING STYLES */
    /* ============================================ */
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
        font-size: 1.8rem;
        font-weight: bold;
        color: #28a745;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 1rem;
        border-radius: 0.5rem;
        text-align: center;
        border: 2px solid #28a745;
    }
    
    .barcode-card {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        height: 100%;
    }

    .barcode-card:hover {
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        transform: translateY(-3px);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .thumbnails-grid {
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 8px;
        }

        .main-image-wrapper {
            padding-bottom: 100%; /* Square on mobile */
        }

        .price-display {
            font-size: 1.5rem;
        }
    }

    /* Print Styles */
    @media print {
        .card-header, .btn, .zoom-icon, .primary-badge, .thumbnail-zoom {
            display: none !important;
        }
        
        .thumbnails-container {
            display: none !important;
        }

        .col-md-7 {
            width: 100% !important;
        }
        
        .barcode-card {
            break-inside: avoid;
            box-shadow: none !important;
            border: 2px solid #000 !important;
            margin-bottom: 20px;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }

    /* Animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .thumbnail-item {
        animation: fadeIn 0.3s ease-out;
    }
</style>
@endsection