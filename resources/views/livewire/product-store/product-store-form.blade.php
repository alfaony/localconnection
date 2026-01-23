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
                    <!-- Basic Info -->
                    <div class="section-header mb-3">
                        <h5 class="text-primary"><i class="fas fa-info-circle mr-2"></i> Informasi Dasar</h5>
                        <hr>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Barcode 
                                    <small class="text-muted">(opsional)</small>
                                </label>
                                <div class="input-group">
                                    <input type="text" 
                                           wire:model.debounce.500ms="barcode" 
                                           id="barcodeInput"
                                           class="form-control" 
                                           placeholder="Kosongkan untuk auto-generate">
                                    <div class="input-group-append">
                                        <button type="button" 
                                                class="btn btn-outline-secondary" 
                                                wire:click="regenerateBarcode"
                                                title="Generate barcode baru">
                                            <i class="fas fa-sync-alt"></i> Generate Baru
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Validation Feedback -->
                                <div id="barcodeValidation" class="mt-1" style="min-height: 20px;">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        Barcode akan di-generate otomatis jika kosong
                                    </small>
                                </div>
                                
                                @error('barcode') 
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span> 
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Kode Barang</label>
                                <input type="text" wire:model="code" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control">
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Varian</label>
                                <input type="text" wire:model="variant" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Kategori <span class="text-danger">*</span></label>
                                <select wire:model="category_product_store_id" id="categoryProductStore" class="form-control">
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
                                <label class="font-weight-bold">Merk <span class="text-danger">*</span></label>
                                <select wire:model="brand_product_store_id" id="brandProductStore" class="form-control">
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
                                <label class="font-weight-bold">Harga Jual <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text">Rp</span></div>
                                    <input type="hidden" wire:model="selling_price" id="price_hidden">
                                    <input type="text" class="form-control" id="price_input" wire:ignore>
                                </div>
                                @error('selling_price') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Spesifikasi</label>
                        <textarea wire:model="specification" rows="3" class="form-control"></textarea>
                    </div>

                    <!-- Location -->
                    <div class="section-header mb-3 mt-4">
                        <h5 class="text-primary"><i class="fas fa-map-marked-alt mr-2"></i> Lokasi</h5>
                        <hr>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Warehouse</label>
                                <select wire:model="warehouse_id" id="warehouse_id" class="form-control">
                                    <option value="">Pilih</option>
                                    @foreach($warehouses as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Zone</label>
                                <select wire:model="zone_id" id="zone_id" class="form-control" {{ !$warehouse_id ? 'disabled' : '' }}>
                                    <option value="">Pilih</option>
                                    @foreach($zones as $z)
                                        <option value="{{ $z->id }}">{{ $z->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Rack</label>
                                <select wire:model="rack_id" id="rack_id" class="form-control" {{ !$zone_id ? 'disabled' : '' }}>
                                    <option value="">Pilih</option>
                                    @foreach($racks as $r)
                                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Dimensions -->
                    <div class="section-header mb-3 mt-4">
                        <h5 class="text-primary"><i class="fas fa-ruler-combined mr-2"></i> Dimensi</h5>
                        <hr>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Panjang (cm)</label>
                                <input type="number" step="0.01" wire:model="length" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Lebar (cm)</label>
                                <input type="number" step="0.01" wire:model="width" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Tinggi (cm)</label>
                                <input type="number" step="0.01" wire:model="height" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Berat (g)</label>
                                <input type="number" step="0.01" wire:model="weight" class="form-control" min="0">
                            </div>
                        </div>
                    </div>

                    <!-- Photos -->
                    <div class="section-header mb-3 mt-4">
                        <h5 class="text-primary"><i class="fas fa-images mr-2"></i> Foto Produk</h5>
                        <hr>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="font-weight-bold"><i class="fas fa-cloud-upload-alt mr-1"></i> Upload Foto</label>
                                
                                <input type="file" wire:model="photo" id="livewirePhotoInput" class="d-none" accept="image/*">
                                
                                <div class="custom-file">
                                    <input type="file" id="photoUploadMultiple" class="custom-file-input" accept="image/*" multiple {{ $isUploading ? 'disabled' : '' }}>
                                    <label class="custom-file-label" for="photoUploadMultiple">
                                        {{ $isUploading ? 'Sedang upload...' : 'Pilih foto (bisa lebih dari 1, max 5MB)' }}
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Pilih beberapa foto sekaligus. Upload otomatis satu per satu.
                                </small>
                                @error('photo') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            </div>

                            @if($isUploading)
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    <strong>Mengupload foto {{ $uploadedCount }} dari {{ $uploadingCount }}...</strong>
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         style="width: {{ $uploadingCount > 0 ? round(($uploadedCount / $uploadingCount) * 100) : 0 }}%">
                                        {{ $uploadingCount > 0 ? round(($uploadedCount / $uploadingCount) * 100) : 0 }}%
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if(!empty($allPhotos))
                            <div class="mb-3">
                                <h6 class="text-primary mb-2"><i class="fas fa-images mr-1"></i> Foto ({{ count($allPhotos) }})</h6>
                                <small class="text-muted d-block mb-2">
                                    {{ $isUploading ? '⏳ Upload sedang berlangsung...' : '✓ Drag untuk mengurutkan' }}
                                </small>

                                <div class="row sortable-photos {{ $isUploading ? 'uploading' : '' }}" id="photosContainer">
                                    @foreach($allPhotos as $i => $photo)
                                    <div class="col-lg-3 col-md-4 col-sm-6 mb-3 sortable-photo-item" data-photo-id="{{ $photo['id'] }}">
                                        <div class="card h-100 {{ $photo['is_saved'] ? 'border-success' : 'border-warning' }}">
                                            <div class="position-relative">
                                                <img src="{{ s3_asset(true,10,$photo['file_path']) }}" 
                                                     class="card-img-top" 
                                                     style="height: 200px; object-fit: cover; cursor: {{ $isUploading ? 'not-allowed' : 'move' }};" 
                                                     alt="Photo">
                                                
                                                @if($i === 0)
                                                <span class="badge badge-primary position-absolute" style="top: 8px; left: 8px;">★ Utama</span>
                                                @else
                                                <span class="badge badge-secondary position-absolute" style="top: 8px; left: 8px;">#{{ $i + 1 }}</span>
                                                @endif

                                                @if(!$photo['is_saved'])
                                                <span class="badge badge-warning position-absolute" style="top: 8px; right: 40px;">Baru</span>
                                                @endif

                                                <button type="button" wire:click="deletePhoto('{{ $photo['id'] }}')" 
                                                        class="btn btn-danger btn-sm position-absolute" 
                                                        style="top: 8px; right: 8px;" {{ $isUploading ? 'disabled' : '' }}>
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <div class="card-body p-2">
                                                <input type="text" wire:model.defer="photoCaptions.{{ $photo['id'] }}" 
                                                       class="form-control form-control-sm" 
                                                       placeholder="Caption" {{ $isUploading ? 'disabled' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @else
                            <div class="alert alert-light border text-center py-4">
                                <i class="fas fa-images fa-3x text-muted mb-2"></i>
                                <p class="mb-0">Belum ada foto</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary" wire:click="cancel" {{ $isUploading ? 'disabled' : '' }}>
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                        <div>
                            <button type="submit" class="btn btn-primary mr-2" wire:click="$set('createAgain', false)" {{ $isUploading ? 'disabled' : '' }}>
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                            <button type="submit" class="btn btn-success" wire:click="$set('createAgain', true)" {{ $isUploading ? 'disabled' : '' }}>
                                <i class="fas fa-plus-circle mr-1"></i> Simpan & Buat Lagi
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
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
<style>
.section-header h5 { font-weight: 600; }
.section-header hr { border-top: 2px solid #007bff; margin-top: 0; }
.bg-gradient-primary { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); }
.sortable-photos.uploading { opacity: 0.6; pointer-events: none; }
</style>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
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

let photoSortable = null;
function initPhotoSortable() {
    const c = document.getElementById('photosContainer');
    if (c && !photoSortable) {
        photoSortable = Sortable.create(c, {
            animation: 150,
            handle: '.sortable-photo-item',
            onEnd: () => {
                const ids = Array.from(c.querySelectorAll('.sortable-photo-item')).map(i => i.dataset.photoId);
                @this.call('updatePhotoOrder', ids);
            }
        });
    }
}

let priceMask = null;
function initPriceMask() {
    const inp = document.getElementById('price_input');
    const hid = document.getElementById('price_hidden');
    if (inp && hid && !priceMask) {
        priceMask = IMask(inp, {mask: Number, scale: 0, thousandsSeparator: '.', min: 0});
        if (hid.value) priceMask.value = hid.value;
        priceMask.on('accept', () => @this.set('selling_price', Math.max(0, priceMask.unmaskedValue)));
    }
}

let uploadQueue = [];
let isProcessing = false;

document.getElementById('photoUploadMultiple').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    if (!files.length) return;
    
    uploadQueue = files;
    isProcessing = true;
    
    @this.set('uploadingCount', files.length);
    @this.set('uploadedCount', 0);
    @this.set('isUploading', true);
    
    if (photoSortable) photoSortable.option('disabled', true);
    
    processNext();
});

function processNext() {
    if (uploadQueue.length === 0) {
        isProcessing = false;
        @this.set('isUploading', false);
        if (photoSortable) photoSortable.option('disabled', false);
        document.getElementById('photoUploadMultiple').value = '';
        return;
    }
    
    const file = uploadQueue.shift();
    const input = document.getElementById('livewirePhotoInput');
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    input.dispatchEvent(new Event('change', { bubbles: true }));
}

window.addEventListener('photo-uploaded', () => {
    const current = @this.get('uploadedCount');
    @this.set('uploadedCount', current + 1);
    setTimeout(processNext, 300);
});

document.addEventListener('livewire:load', function() {
    initSelect2('#categoryProductStore', 'category_product_store_id');
    initSelect2('#brandProductStore', 'brand_product_store_id');
    initSelect2('#warehouse_id', 'warehouse_id');
    initSelect2('#zone_id', 'zone_id');
    initSelect2('#rack_id', 'rack_id');
    initPriceMask();
    initPhotoSortable();
});

Livewire.hook('message.processed', () => {
    // Re-initialize Select2 after Livewire updates
    initAllSelect2();
    updateSelect2Values();
    
    // Handle photo sortable
    if (!isProcessing) {
        if (photoSortable) { photoSortable.destroy(); photoSortable = null; }
        setTimeout(initPhotoSortable, 100);
    }
});

// ============================================
// BARCODE VALIDATION
// ============================================
let barcodeCheckTimeout = null;

function updateBarcodeValidation(status, message) {
    const validationDiv = document.getElementById('barcodeValidation');
    if (!validationDiv) return;

    let icon, colorClass;
    switch(status) {
        case 'checking':
            icon = '<i class="fas fa-spinner fa-spin"></i>';
            colorClass = 'text-info';
            break;
        case 'available':
            icon = '<i class="fas fa-check-circle"></i>';
            colorClass = 'text-success';
            break;
        case 'taken':
            icon = '<i class="fas fa-times-circle"></i>';
            colorClass = 'text-danger';
            break;
        default:
            icon = '<i class="fas fa-info-circle"></i>';
            colorClass = 'text-muted';
    }

    validationDiv.innerHTML = `<small class="${colorClass}">${icon} ${message}</small>`;
}

async function checkBarcodeAvailability() {
    const barcode = @this.get('barcode');
    
    if (!barcode || barcode.trim() === '') {
        updateBarcodeValidation('info', 'Barcode akan di-generate otomatis jika kosong');
        return;
    }

    updateBarcodeValidation('checking', 'Memeriksa ketersediaan barcode...');

    try {
        const result = await @this.call('checkBarcodeAvailability');
        
        if (result.available) {
            updateBarcodeValidation('available', result.message);
        } else {
            updateBarcodeValidation('taken', result.message);
        }
    } catch (error) {
        console.error('Barcode check error:', error);
        updateBarcodeValidation('info', 'Gagal memeriksa barcode');
    }
}

// Listen to barcode changes with debounce
Livewire.on('barcode-updated', () => {
    clearTimeout(barcodeCheckTimeout);
    barcodeCheckTimeout = setTimeout(checkBarcodeAvailability, 500);
});

// Listen to barcode regeneration
window.addEventListener('barcode-regenerated', (event) => {
    updateBarcodeValidation('available', event.detail.message);
});

// Watch for barcode changes
document.addEventListener('livewire:load', function() {
    Livewire.hook('message.processed', (message, component) => {
        // Check barcode when it changes
        if (message.updateQueue && message.updateQueue.some(update => update.payload.name === 'barcode')) {
            clearTimeout(barcodeCheckTimeout);
            barcodeCheckTimeout = setTimeout(checkBarcodeAvailability, 500);
        }
    });
});
</script>
@endsection