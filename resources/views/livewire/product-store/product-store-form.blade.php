<div class="row">
    <div class="col-md-12 mt-3">
        @include('components.alert')
        <div class="card shadow-sm">
            <div class="card-header bg-gradient-primary">
                <h3 class="card-title text-white mb-0">
                    <i class="fas fa-box mr-2"></i> Form Produk Toko
                </h3>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <!-- Section 1: Informasi Dasar -->
                    <div class="section-header mb-3">
                        <h5 class="text-primary">
                            <i class="fas fa-info-circle mr-2"></i> Informasi Dasar
                        </h5>
                        <hr>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="barcode" class="font-weight-bold">
                                    <i class="fas fa-barcode mr-1"></i> Barcode
                                </label>
                                <div class="input-group">
                                    <input type="text" wire:model="barcode" id="barcode" class="form-control bg-light" readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="fas fa-barcode"></i>
                                        </span>
                                    </div>
                                </div>
                                @error('barcode') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="barcode" class="font-weight-bold">
                                    <i class="fas fa-barcode mr-1"></i> Kode Barang
                                </label>
                                <div class="input-group">
                                    <input type="text" wire:model="code" id="code" class="form-control bg-light">
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            Kode Barang
                                        </span>
                                    </div>
                                </div>
                                @error('barcode') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name" class="font-weight-bold">
                                    <i class="fas fa-tag mr-1"></i> Nama Produk <span class="text-danger">*</span>
                                </label>
                                <input type="text" wire:model="name" id="name" class="form-control" placeholder="Masukan nama produk">
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="variant" class="font-weight-bold">
                                    <i class="fas fa-layer-group mr-1"></i> Varian
                                </label>
                                <input type="text" wire:model="variant" id="variant" class="form-control" placeholder="Masukan varian produk">
                                @error('variant') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="categoryProductStore" class="font-weight-bold">
                                    <i class="fas fa-th-large mr-1"></i> Kategori <span class="text-danger">*</span>
                                </label>
                                <select wire:model="category_product_store_id" id="categoryProductStore" class="form-control select2-category" data-placeholder="Pilih Kategori">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_product_store_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="brandProductStore" class="font-weight-bold">
                                    <i class="fas fa-copyright mr-1"></i> Merk <span class="text-danger">*</span>
                                </label>
                                <select wire:model="brand_product_store_id" id="brandProductStore" class="form-control select2-brand" data-placeholder="Pilih Merk">
                                    <option value="">Pilih Merk</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                @error('brand_product_store_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="selling_price" class="font-weight-bold">
                                    <i class="fas fa-money-bill-wave mr-1"></i> Harga Jual <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="hidden" wire:model="selling_price" id="price_hidden">
                                    <input type="text" class="form-control" id="price_input" wire:ignore placeholder="0">
                                </div>
                                @error('selling_price') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="specification" class="font-weight-bold">
                            <i class="fas fa-clipboard-list mr-1"></i> Spesifikasi
                        </label>
                        <textarea wire:model="specification" id="specification" rows="3" class="form-control" placeholder="Masukan spesifikasi produk"></textarea>
                        @error('specification') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <!-- ============================================ -->
                    <!-- SECTION 2: LOKASI PENYIMPANAN WITH SELECT2 -->
                    <!-- ============================================ -->
                    <div class="section-header mb-3 mt-4">
                        <h5 class="text-primary">
                            <i class="fas fa-map-marked-alt mr-2"></i> Lokasi Penyimpanan
                        </h5>
                        <hr>
                    </div>

                    <div class="row">
                        <!-- Warehouse -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="warehouse_id" class="font-weight-bold">
                                    <i class="fas fa-warehouse mr-1"></i> Warehouse <span class="text-danger">*</span>
                                </label>
                                <div wire:loading.class="loading-overlay" wire:target="warehouse_id">
                                    <select wire:model="warehouse_id" id="warehouse_id" class="form-control select2-warehouse" data-placeholder="Pilih Warehouse">
                                        <option value="">Pilih Warehouse</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="form-text text-muted">
                                    @if($warehouse_id && $zones->count() > 0)
                                        <i class="fas fa-check-circle text-success"></i> {{ $zones->count() }} zone tersedia
                                    @else
                                        <i class="fas fa-info-circle"></i> Pilih warehouse terlebih dahulu
                                    @endif
                                </small>
                                @error('warehouse_id') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Zone -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="zone_id" class="font-weight-bold">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Zone <span class="text-danger">*</span>
                                </label>
                                <div wire:loading.class="loading-overlay" wire:target="zone_id">
                                    <select wire:model="zone_id" id="zone_id" class="form-control select2-zone" data-placeholder="Pilih Zone" {{ !$warehouse_id ? 'disabled' : '' }}>
                                        <option value="">Pilih Zone</option>
                                        @foreach($zones as $zone)
                                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="form-text text-muted">
                                    @if($zone_id && $racks->count() > 0)
                                        <i class="fas fa-check-circle text-success"></i> {{ $racks->count() }} rack tersedia
                                    @elseif($warehouse_id && $zones->count() == 0)
                                        <i class="fas fa-exclamation-triangle text-warning"></i> Tidak ada zone
                                    @else
                                        <i class="fas fa-info-circle"></i> Pilih warehouse terlebih dahulu
                                    @endif
                                </small>
                                @error('zone_id') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Rack -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="rack_id" class="font-weight-bold">
                                    <i class="fas fa-th mr-1"></i> Rack <span class="text-danger">*</span>
                                </label>
                                <div wire:loading.class="loading-overlay" wire:target="rack_id">
                                    <select wire:model="rack_id" id="rack_id" class="form-control select2-rack" data-placeholder="Pilih Rack" {{ !$zone_id ? 'disabled' : '' }}>
                                        <option value="">Pilih Rack</option>
                                        @foreach($racks as $rack)
                                            <option value="{{ $rack->id }}">
                                                {{ $rack->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <small class="form-text text-muted">
                                    @if($rack_id)
                                        <i class="fas fa-check-circle text-success"></i> Rack dipilih
                                    @elseif($zone_id && $racks->count() == 0)
                                        <i class="fas fa-exclamation-triangle text-warning"></i> Tidak ada rack
                                    @else
                                        <i class="fas fa-info-circle"></i> Pilih zone terlebih dahulu
                                    @endif
                                </small>
                                @error('rack_id') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Location Path Preview -->
                    @if($warehouse_id || $zone_id || $rack_id)
                    <div class="alert alert-light border">
                        <div class="d-flex align-items-center justify-content-center flex-wrap">
                            @if($warehouse_id)
                                @php
                                    $selectedWarehouse = $warehouses->firstWhere('id', $warehouse_id);
                                @endphp
                                <span class="badge badge-primary px-3 py-2 mr-2 mb-2">
                                    <i class="fas fa-warehouse mr-1"></i>
                                    {{ $selectedWarehouse->name ?? 'Warehouse' }}
                                </span>
                            @endif
                            
                            @if($warehouse_id && $zone_id)
                                <i class="fas fa-chevron-right text-muted mr-2 mb-2"></i>
                                @php
                                    $selectedZone = $zones->firstWhere('id', $zone_id);
                                @endphp
                                <span class="badge badge-info px-3 py-2 mr-2 mb-2">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    {{ $selectedZone->name ?? 'Zone' }}
                                </span>
                            @endif
                            
                            @if($zone_id && $rack_id)
                                <i class="fas fa-chevron-right text-muted mr-2 mb-2"></i>
                                @php
                                    $selectedRack = $racks->firstWhere('id', $rack_id);
                                @endphp
                                <span class="badge badge-secondary px-3 py-2 mb-2">
                                    <i class="fas fa-th mr-1"></i>
                                    {{ $selectedRack->name ?? 'Rack' }}
                                    @if($selectedRack && $selectedRack->code)
                                        ({{ $selectedRack->code }})
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Section 3: Dimensi dan Berat -->
                    <div class="section-header mb-3 mt-4">
                        <h5 class="text-primary">
                            <i class="fas fa-ruler-combined mr-2"></i> Dimensi dan Berat
                        </h5>
                        <hr>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="length" class="font-weight-bold">
                                    <i class="fas fa-arrows-alt-h mr-1"></i> Panjang
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" wire:model="length" id="length" class="form-control" placeholder="0.00" min="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                                @error('length') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="width" class="font-weight-bold">
                                    <i class="fas fa-arrows-alt-v mr-1"></i> Lebar
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" wire:model="width" id="width" class="form-control" placeholder="0.00" min="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                                @error('width') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="height" class="font-weight-bold">
                                    <i class="fas fa-arrows-alt mr-1"></i> Tinggi
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" wire:model="height" id="height" class="form-control" placeholder="0.00" min="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text">cm</span>
                                    </div>
                                </div>
                                @error('height') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="weight" class="font-weight-bold">
                                    <i class="fas fa-weight mr-1"></i> Berat
                                </label>
                                <div class="input-group">
                                    <input type="number" step="0.01" wire:model="weight" id="weight" class="form-control" placeholder="0.00" min="0">
                                    <div class="input-group-append">
                                        <span class="input-group-text">g</span>
                                    </div>
                                </div>
                                @error('weight') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Media / Foto Produk -->
                    <div class="section-header mb-3 mt-4">
                        <h5 class="text-primary">
                            <i class="fas fa-images mr-2"></i> Foto Produk
                        </h5>
                        <hr>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <!-- Upload Area -->
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    <i class="fas fa-cloud-upload-alt mr-1"></i> Upload Foto
                                </label>
                                <div class="custom-file">
                                    <input type="file" 
                                        wire:model="photo" 
                                        class="custom-file-input" 
                                        id="photoUpload" 
                                        accept="image/*">
                                    <label class="custom-file-label" for="photoUpload">
                                        Pilih foto (max 5MB)
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Foto akan langsung ditambahkan setelah dipilih. Anda bisa upload foto lagi untuk menambah lebih banyak.
                                </small>
                                @error('photo') 
                                    <span class="text-danger small d-block">{{ $message }}</span> 
                                @enderror
                            </div>

                            <!-- Loading Indicator -->
                            <div wire:loading wire:target="photo" class="alert alert-info">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Mengupload foto...
                            </div>

                            <!-- All Photos (Sortable) -->
                            @if(!empty($allPhotos))
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <h6 class="font-weight-bold text-primary mb-0">
                                            <i class="fas fa-images mr-1"></i> Foto Produk ({{ count($allPhotos) }})
                                        </h6>
                                        <small class="text-muted">
                                            <i class="fas fa-arrows-alt mr-1"></i> Drag untuk mengurutkan, foto pertama akan jadi foto utama
                                        </small>
                                    </div>
                                </div>

                                <div class="row sortable-photos" id="photosContainer">
                                    @foreach($allPhotos as $index => $photo)
                                    <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-3 sortable-photo-item" data-photo-id="{{ $photo['id'] }}">
                                        <div class="card shadow-sm h-100 {{ $photo['is_saved'] ? 'border-success' : 'border-warning' }}">
                                            <div class="position-relative">
                                                <img src="{{ s3_asset(true,10,$photo['file_path']) }}" 
                                                    class="card-img-top" 
                                                    style="height: 200px; object-fit: cover; cursor: move;" 
                                                    alt="Photo {{ $index + 1 }}">
                                                
                                                <!-- Status Badge -->
                                                @if($index === 0)
                                                    <div class="badge badge-primary position-absolute" style="top: 8px; left: 8px;">
                                                        <i class="fas fa-star mr-1"></i> Utama
                                                    </div>
                                                @else
                                                    <div class="badge badge-secondary position-absolute" style="top: 8px; left: 8px;">
                                                        #{{ $index + 1 }}
                                                    </div>
                                                @endif

                                                @if(!$photo['is_saved'])
                                                    <div class="badge badge-warning position-absolute" style="top: 8px; right: 40px;">
                                                        <i class="fas fa-clock mr-1"></i> Baru
                                                    </div>
                                                @endif

                                                <!-- Delete Button -->
                                                <button type="button" 
                                                        wire:click="deletePhoto('{{ $photo['id'] }}')" 
                                                        class="btn btn-danger btn-sm position-absolute" 
                                                        style="top: 8px; right: 8px;" 
                                                        title="Hapus"
                                                        wire:loading.attr="disabled"
                                                        wire:target="deletePhoto">
                                                    <i class="fas fa-times"></i>
                                                </button>

                                                <!-- Drag Handle -->
                                                <div class="badge badge-dark position-absolute" style="bottom: 8px; right: 8px;">
                                                    <i class="fas fa-grip-vertical"></i>
                                                </div>
                                            </div>
                                            <div class="card-body p-2">
                                                <!-- Caption Input -->
                                                <div class="form-group mb-1">
                                                    <input type="text" 
                                                        wire:model.defer="photoCaptions.{{ $photo['id'] }}" 
                                                        class="form-control form-control-sm" 
                                                        placeholder="Caption foto (opsional)"
                                                        maxlength="255">
                                                </div>
                                                @if(isset($photo['original_name']))
                                                <small class="text-muted d-block text-truncate" title="{{ $photo['original_name'] }}">
                                                    <i class="fas fa-file-image mr-1"></i>{{ $photo['original_name'] }}
                                                </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Empty State -->
                            @if(empty($allPhotos))
                            <div class="alert alert-light border text-center py-5">
                                <i class="fas fa-images fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum ada foto produk</h5>
                                <p class="mb-0 text-muted">Upload foto untuk menampilkan produk Anda dengan lebih menarik</p>
                            </div>
                            @endif
                        </div>
                    </div>

                                        <!-- Footer Buttons -->
                                        <div class="card-footer bg-light mt-4 px-0">
                                            <div class="d-flex justify-content-between">
                                                <button type="button" class="btn btn-secondary" wire:click="cancel">
                                                    <i class="fas fa-times mr-1"></i> Batal
                                                </button>
                                                <div>
                                                    <button type="submit" class="btn btn-primary mr-2" wire:click="$set('createAgain', false)">
                                                        <i class="fas fa-save mr-1"></i> Simpan
                                                    </button>
                                                    <button type="submit" class="btn btn-success" wire:click="$set('createAgain', true)">
                                                        <i class="fas fa-plus-circle mr-1"></i> Simpan & Buat Lagi
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" rel="stylesheet" />


<style>
    /* Section Headers */
    .section-header h5 {
        font-weight: 600;
        margin-bottom: 10px;
    }

    .section-header hr {
        margin-top: 0;
        border-top: 2px solid #007bff;
    }

    /* Card Styling */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    /* ============================================ */
    /* SELECT2 CUSTOM STYLING */
    /* ============================================ */
    
    /* Container */
    .select2-container {
        width: 100% !important;
    }

    /* Single Selection Box */
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        padding: 0.375rem 0.75rem;
    }

    /* Text Displayed */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px !important;
        padding-left: 0 !important;
        color: #495057;
    }

    /* Arrow Dropdown */
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        top: 1px !important;
        right: 1px !important;
    }

    /* Placeholder */
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d;
    }

    /* Dropdown Results */
    .select2-container--default .select2-results__option {
        padding: 8px 12px;
    }

    /* Hover State */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #007bff !important;
        color: white;
    }

    /* Selected Option */
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #e9ecef;
    }

    /* Disabled State */
    .select2-container--default.select2-container--disabled .select2-selection--single {
        background-color: #e9ecef !important;
        cursor: not-allowed !important;
        border-color: #ced4da !important;
    }

    /* Focus State */
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #80bdff !important;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
    }

    /* Dropdown */
    .select2-dropdown {
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.175);
    }

    /* Search Box in Dropdown */
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        padding: 6px 12px;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        outline: none;
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }

    /* Clear Button */
    .select2-container--default .select2-selection--single .select2-selection__clear {
        margin-right: 10px;
        font-size: 1.2em;
    }

    /* Loading Overlay */
    .loading-overlay {
        position: relative;
        opacity: 0.6;
        pointer-events: none;
    }

    .loading-overlay::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 20px;
        height: 20px;
        border: 2px solid #007bff;
        border-top-color: transparent;
        border-radius: 50%;
        animation: spin 0.6s linear infinite;
        z-index: 1000;
    }

    @keyframes spin {
        to { transform: translate(-50%, -50%) rotate(360deg); }
    }

    /* Input Groups */
    .input-group-text {
        background-color: #f8f9fa;
    }

    /* Badge Styling */
    .badge {
        font-size: 0.875rem;
        font-weight: 500;
    }

    /* Form Text Helper */
    .form-text.text-muted {
        font-size: 0.875rem;
    }
</style>
@endsection

@section('js')
<!-- jQuery (Required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>

<!-- IMask for Price -->
<script src="https://cdn.jsdelivr.net/npm/imask"></script>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>


<script>
    const SELECT2_EVENT_NS = '.select2Livewire';

    // ============================================
    // SELECT2 HELPERS
    // ============================================
    function destroySelect2($elements) {
        $elements.each(function() {
            const $el = $(this);
            $el.off(SELECT2_EVENT_NS);

            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
        });
    }

    function initSelect2(selector, livewireProperty, options = {}) {
        const $elements = $(selector);
        if (!$elements.length) {
            return;
        }

        destroySelect2($elements);

        $elements.each(function() {
            const $el = $(this);

            $el.select2({
                allowClear: true,
                width: '100%',
                theme: 'default',
                placeholder: $el.data('placeholder') || $el.attr('placeholder') || 'Pilih opsi',
                ...options
            });

            $el.on('change' + SELECT2_EVENT_NS, function() {
                @this.set(livewireProperty, $(this).val());
            });
        });
    }

    function setSelect2Value(selector, value) {
        const $element = $(selector);
        if (!$element.length) {
            return;
        }

        const normalized = value ?? '';
        const current = $element.val();

        if (normalized === '') {
            if (current && current !== '') {
                $element.val(null).trigger('change.select2');
            }
            return;
        }

        if (current !== normalized.toString()) {
            $element.val(normalized.toString()).trigger('change.select2');
        }
    }

    function syncDependentSelectState() {
        const warehouseValue = @this.get('warehouse_id');
        const zoneValue = @this.get('zone_id');

        const $zoneSelect = $('#zone_id');
        const $rackSelect = $('#rack_id');

        const hasWarehouse = Boolean(warehouseValue);
        const hasZone = Boolean(zoneValue);

        $zoneSelect.prop('disabled', !hasWarehouse);
        if (!hasWarehouse) {
            $zoneSelect.val(null).trigger('change.select2');
        }

        $rackSelect.prop('disabled', !hasZone);
        if (!hasZone) {
            $rackSelect.val(null).trigger('change.select2');
        }
    }

    function initAllSelect2() {
        initSelect2('#categoryProductStore', 'category_product_store_id');
        initSelect2('#brandProductStore', 'brand_product_store_id');
        initSelect2('#warehouse_id', 'warehouse_id');
        initSelect2('#zone_id', 'zone_id');
        initSelect2('#rack_id', 'rack_id');
    }

    function updateSelect2Values() {
        setSelect2Value('#categoryProductStore', @this.get('category_product_store_id'));
        setSelect2Value('#brandProductStore', @this.get('brand_product_store_id'));
        setSelect2Value('#warehouse_id', @this.get('warehouse_id'));
        setSelect2Value('#zone_id', @this.get('zone_id'));
        setSelect2Value('#rack_id', @this.get('rack_id'));

        syncDependentSelectState();
    }

    function resetAllSelect2() {
        ['#categoryProductStore', '#brandProductStore', '#warehouse_id', '#zone_id', '#rack_id'].forEach(selector => {
            const $el = $(selector);
            if ($el.length) {
                $el.val(null).trigger('change.select2');
            }
        });

        $('#zone_id').prop('disabled', true);
        $('#rack_id').prop('disabled', true);
    }

    // ============================================
    // SORTABLE PHOTOS
    // ============================================
    let photoSortable = null;

    function initPhotoSortable() {
        const container = document.getElementById('photosContainer');
        
        if (container && !photoSortable) {
            photoSortable = Sortable.create(container, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                handle: '.sortable-photo-item',
                onEnd: function (evt) {
                    const orderedIds = Array.from(container.querySelectorAll('.sortable-photo-item'))
                        .map(item => item.dataset.photoId);
                    
                    @this.call('updatePhotoOrder', orderedIds);
                }
            });
        }
    }

    function destroyPhotoSortable() {
        if (photoSortable) {
            photoSortable.destroy();
            photoSortable = null;
        }
    }

    // ============================================
    // PRICE MASK (IMask)
    // ============================================
    let priceMask = null;

    function initPriceMask() {
        const priceInput = document.getElementById('price_input');
        const priceHidden = document.getElementById('price_hidden');

        if (priceInput && priceHidden && !priceMask) {
            priceMask = IMask(priceInput, {
                mask: Number,
                scale: 0,
                thousandsSeparator: '.',
                padFractionalZeros: false,
                normalizeZeros: true,
                radix: ',',
                mapToRadix: ['.'],
                min: 0
            });

            if (priceHidden.value) {
                priceMask.value = priceHidden.value;
            }

            priceMask.on('accept', () => {
                const unmaskedValue = Math.max(0, priceMask.unmaskedValue);
                @this.set('selling_price', unmaskedValue);
            });

            priceInput.addEventListener('blur', () => {
                if (priceMask.unmaskedValue < 0) {
                    priceMask.value = 0;
                    @this.set('selling_price', 0);
                }
            });
        }
    }

     window.addEventListener('photo-uploaded', event => {
        // Show success message
        if (event.detail.message) {
            // You can use your notification system here
            console.log(event.detail.message);
        }
    });

    // ============================================
    // DOCUMENT READY & LIVEWIRE HOOKS
    // ============================================
    document.addEventListener('livewire:load', function() {
        initAllSelect2();
        initPriceMask();
        updateSelect2Values();
        initPhotoSortable();
    });

    Livewire.hook('message.processed', () => {
        initAllSelect2();
        updateSelect2Values();

        const priceHidden = document.getElementById('price_hidden');
        if (priceHidden && priceMask && priceHidden.value && priceMask.value !== priceHidden.value) {
            priceMask.value = priceHidden.value;
        }

        // Reinitialize sortable
        destroyPhotoSortable();
        setTimeout(() => {
            initPhotoSortable();
        }, 100);
    });

    // ============================================
    // LIVEWIRE EVENTS
    // ============================================
    Livewire.on('productCreated', () => {
        resetAllSelect2();

        if (priceMask) {
            priceMask.value = '';
        }

        destroyPhotoSortable();
    });

    Livewire.on('productUpdated', () => {
        updateSelect2Values();
        destroyPhotoSortable();
        setTimeout(() => {
            initPhotoSortable();
        }, 100);
    });
</script>
@endsection
