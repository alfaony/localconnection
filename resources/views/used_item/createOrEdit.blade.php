@extends('adminlte::page')

@section('title', isset($usedItem) ? 'Edit Barang' : 'Tambah Barang')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">{{ isset($usedItem) ? 'Edit Barang' : 'Tambah Barang' }}</h1>
        <a href="{{ route('used-item.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
@include('components.alert')
<div class="card card-primary">
    <form action="{{ isset($usedItem) ? route('used-item.update', $usedItem->slug) : route('used-item.store') }}" method="POST" enctype="multipart/form-data" id="item-form">
        @csrf
        @if(isset($usedItem))
            @method('PUT')
        @endif
        
        <div class="card-body">
            <!-- Section 1: Detail Barang -->
            <div class="section-header mb-4">
                <h3 class="text-primary">
                    <i class="fas fa-box mr-2"></i> Detail Barang
                </h3>
                <div class="border-bottom border-primary mt-2"></div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="{{ old('name', $usedItem->name ?? '') }}"
                               placeholder="Contoh: MacBook Pro 2020" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="serial_number">Serial Number </label>
                        <input type="text" class="form-control" id="serial_number" name="serial_number" 
                            value="{{ old('serial_number', $usedItem->serial_number ?? '') }}"
                            placeholder="Masukkan Serial Number" required>
                    </div>
                    <div class="form-group">
                        <label for="purchase_price">Harga Beli (Rp) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="purchase_price" name="purchase_price" 
                               value="{{ isset($usedItem) ? number_format($usedItem->purchase_price, 0, ',', '.') : (old('purchase_price') ? number_format(old('purchase_price'), 0, ',', '.') : '') }}"
                               data-raw-value="{{ old('purchase_price', $usedItem->purchase_price ?? '') }}"
                               required onkeyup="formatCurrency(this)">
                    </div>
                </div>
                <div class="col-md-6">
                    
                    <div class="form-group">
                        <label for="notes">Catatan</label>
                        <input class="thriveEditor form-control" id="description_notes" data-ids="notes" name="notes" rows="3" placeholder="yang akan dicetak di perjanjian" value="{{ old('notes', $usedItem->notes ?? '') }}"/>
                    </div>
                </div>
            </div>
            
            <!-- Section 2: Foto Barang -->
            <div class="section-header mb-4 mt-5">
                <h3 class="text-primary">
                    <i class="fas fa-camera mr-2"></i> Foto Barang
                </h3>
                <div class="border-bottom border-primary mt-2"></div>
            </div>
            
            <div class="form-group">
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="photos" name="photos[]" multiple>
                    <label class="custom-file-label" for="photos">Pilih beberapa foto</label>
                </div>
                <small class="form-text text-muted">Upload foto barang dari berbagai sudut (maks. 5 foto)</small>
            </div>
            
            <!-- Existing photos in edit mode -->
            @if(isset($usedItem) && $usedItem->media->count() > 0)
                <div class="mb-3">
                    <label>Foto Saat Ini:</label>
                    <div class="row">
                        @foreach($usedItem->media as $media)
                        <div class="col-md-2 mb-3 photo-wrapper position-relative">
                            <img src="{{ Storage::url($media->file_path) }}" class="img-thumbnail photo-preview w-100" style="object-fit: cover;">
                            <button type="button" class="btn btn-danger btn-sm" 
                                    style="position: absolute; top: 4px; right: 4px; border-radius: 50%; padding: 2px 6px;"
                                    data-toggle="modal" data-target="#deletePhotoModal" 
                                    data-media-id="{{ $media->id }}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <div class="row mt-3" id="photo-preview"></div>
            
            <!-- Section 3: Checklist Kondisi -->
            <div class="section-header mb-4 mt-5">
                <h3 class="text-primary">
                    <i class="fas fa-clipboard-check mr-2"></i> Checklist Kondisi
                </h3>
                <div class="border-bottom border-primary mt-2"></div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i> Periksa semua item di bawah ini untuk memastikan kondisi Barang
            </div>

            {{-- Filter Kategori --}}
            @php
                // Jika edit, ambil ID dari relasi belongsToMany
                $selectedCategories = collect($checkItems)
                                    ->map(fn($item) => $item->item_category_id)
                                    ->filter() // skip null
                                    ->unique()
                                    ->values()
                                    ->toArray();
                @endphp
            
            <div class="form-group">
                <label for="category_ids">Pilih Kategori Item</label>
                <select class="form-control select2" id="categoryFilter" name="category_ids[]" multiple>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ in_array($category->id, $selectedCategories) ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th width="40%">Item Pemeriksaan</th>
                            <th width="20%">Kondisi</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody id="checkItemsBody">
                        @foreach($checkItems as $item)
                            @php
                                $existingCheck = isset($usedItem) ? $usedItem->checks->firstWhere('master_check_item_id', $item->id) : null;
                            @endphp
                            <tr data-category="{{ $item->item_category_id ?? '' }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-primary mr-3">
                                            <i class="fas fa-check text-white"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $item->name }}</strong>
                                            <div class="text-muted small">{{ $item->description }}</div>
                                            @if($item->category)
                                                <span class="badge badge-secondary mt-1">
                                                    <i class="fas fa-tag mr-1"></i> {{ $item->category->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <input type="hidden" name="check_items[{{ $item->id }}][condition]" value="">
                                   <div class="form-check form-check-inline">
                                        <input class="form-check-input exclusive-checkbox" type="checkbox"
                                            name="check_items[{{ $item->id }}][condition]"
                                            value="good"
                                            data-group="{{ $item->id }}"
                                            {{ ($existingCheck->status ?? old("check_items.{$item->id}.condition")) == 'good' ? 'checked' : '' }}>
                                        <label class="form-check-label">Baik</label>
                                    </div>

                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input exclusive-checkbox" type="checkbox"
                                            name="check_items[{{ $item->id }}][condition]"
                                            value="damaged"
                                            data-group="{{ $item->id }}"
                                            {{ ($existingCheck->status ?? old("check_items.{$item->id}.condition")) == 'damaged' ? 'checked' : '' }}>
                                        <label class="form-check-label">Rusak</label>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="check_items[{{ $item->id }}][notes]" value="{{ $existingCheck->notes ?? old("check_items.{$item->id}.notes") }}" placeholder="Catatan kondisi...">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Section 4: Kerusakan dan Perbaikan -->
            <div class="section-header mb-4 mt-5">
                <h3 class="text-primary">
                    <i class="fas fa-tools mr-2"></i> Kerusakan dan Perbaikan
                </h3>
                <div class="border-bottom border-primary mt-2"></div>
            </div>
            
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i> 
                Tambahkan kerusakan jika ada dan biaya perbaikannya
            </div>
            
            <div id="repairs-container">
                @if(isset($usedItem) && $usedItem->repairs->count() > 0)
                    @foreach($usedItem->repairs as $index => $repair)
                        <div class="repair-item card mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label>Deskripsi Kerusakan</label>
                                            <input type="text" class="form-control" 
                                                   name="repairs[{{ $index }}][description]" 
                                                   value="{{ old("repairs.{$index}.description", $repair->repair_item) }}"
                                                   placeholder="Deskripsi kerusakan..." required >
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Biaya Perbaikan (Rp)</label>
                                            <input type="text" class="form-control repair-cost" 
                                                   name="repairs[{{ $index }}][cost]"
                                                   value="{{ number_format($repair->cost, 0, ',', '.') }}"
                                                   data-raw-value="{{ $repair->cost }}"
                                                   placeholder="0"
                                                   onkeyup="formatCurrency(this); calculateTotalRepairCost()" required>
                                        </div>
                                    </div>
                                    @canAccess('mediaDestroy','used_laptops')
                                    <div class="col-md-1 d-flex align-items-center justify-content-center mt-3">
                                        <button type="button" class="btn btn-danger btn-block btn-remove-repair btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    @endcanAccess
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <!-- Default empty repair item -->
                    <div class="repair-item card mb-3">
                    </div>
                @endif
            </div>
            
            <button type="button" id="add-repair" class="btn btn-outline-primary">
                <i class="fas fa-plus mr-1"></i> Tambah Kerusakan
            </button>
            
            <!-- Total Kerusakan -->
            <div class="row mt-4">
                <div class="col-md-4 offset-md-8">
                    <div class="card bg-light">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Total Biaya Perbaikan:</strong>
                                <span id="total-repair-cost" class="font-weight-bold text-danger">
                                    Rp {{ isset($usedItem) ? number_format($usedItem->repairs->sum('cost'), 0, ',', '.') : '0' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-footer">
            <button type="submit" id="submitBtn" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> {{ isset($laptop) ? 'Update Barang' : 'Simpan Barang' }}
            </button>
            <button type="reset" id="resetForm" class="btn btn-outline-secondary">
                <i class="fas fa-undo mr-1"></i> Reset Form
            </button>
        </div>
    </form>
</div>

<!-- Delete Photo Modal -->
@canAccess('mediaDestroy', 'used_laptops')
<div class="modal fade" id="deletePhotoModal" tabindex="-1" role="dialog" aria-labelledby="deletePhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white" id="deletePhotoModalLabel">Hapus Foto</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus foto ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form method="POST" id="deletePhotoForm">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcanAccess
@stop

@section('css')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .section-header {
            position: relative;
        }
        
        .icon-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .photo-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 2px dashed #ddd;
            border-radius: 8px;
            margin: 5px;
            padding: 5px;
        }
        
        .repair-item {
            border-left: 4px solid #007bff;
        }
        
        .custom-file-label::after {
            content: "Pilih";
        }
        
        .total-card {
            background-color: #f8f9fa;
            border-left: 4px solid #dc3545;
        }

        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 5px;
        }

        .select2-selection__choice {
            background-color: #007bff !important;
            border: 1px solid #007bff !important;
            color: white;
            padding: 0 5px;
            margin-top: 5px;
        }

        .select2-selection__choice__remove {
            color: #fe0700 !important;
            margin-right: 5px;
            cursor: pointer;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            display: inline-block;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            background-color: #e3342f;
            color: white;
        }

        .photo-wrapper {
            aspect-ratio: 1/1; /* agar square */
            overflow: hidden;
        }

        .photo-wrapper img {
            height: 100%;
            object-fit: cover;
        }

    </style>
@stop

@section('js')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
    <script src="{{ asset('js/thriveEditor.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
    $(document).on('change', '.exclusive-checkbox', function () {
        const group = $(this).data('group');
        if ($(this).is(':checked')) {
            $(`.exclusive-checkbox[data-group="${group}"]`).not(this).prop('checked', false);
        }
    });

    $(document).ready(function () {
        const $categoryFilter = $('#categoryFilter');

        // Init select2
        $categoryFilter.select2({
            placeholder: 'Pilih kategori...',
            allowClear: true,
            width: '100%'
        });
        

        function filterItems() 
        {
            const selectedCategories = $categoryFilter.val(); // array of string
            $('tbody tr').each(function () {
                const $row = $(this);
                const categoryId = $row.data('category') || '';

                // Jika tidak ada kategori yang dipilih, tampilkan semua
                if (!selectedCategories || selectedCategories.length === 0) {
                    $row.show();
                    return;
                }

                // Jika item tidak punya kategori dan ada kategori yang dipilih, sembunyikan + clear
                if (!categoryId) {
                    clearRowInputs($row);
                    $row.hide();
                    return;
                }

                // Tampilkan jika kategori item ada di yang dipilih
                if (selectedCategories.includes(categoryId.toString())) {
                    $row.show();
                } else {
                    clearRowInputs($row);
                    $row.hide();
                }
            });
        }

       function clearRowInputs($row) 
       {
            // Kosongkan nilai input teks, hidden, select, dan textarea
            $row.find('input[type="text"], input[type="hidden"], select, textarea').val('');

            // Uncheck checkbox dan radio
            $row.find('input[type="checkbox"], input[type="radio"]').prop('checked', false);
        }

        $categoryFilter.on('change', filterItems);

        // Auto-trigger on load
        filterItems();
    });
</script>
    @canAccess('mediaDestroy', 'used_laptops')
    <script>
        let deleteUrl = ''; // Simpan URL target
        /// Format angka ke format mata uang Indonesia
        function formatCurrency(input) {
            // Hapus karakter selain angka
            let value = input.value.replace(/[^\d]/g, '');
            
            // Simpan nilai asli tanpa format
            input.dataset.rawValue = value;
            
            // Format angka dengan pemisah ribuan
            if (value.length > 0) {
                value = parseInt(value, 10).toLocaleString('id-ID');
            }
            
            // Set nilai input
            input.value = value;
        }

        // Konversi format mata uang ke angka murni sebelum submit
        function convertCurrencyToNumber() {
            // Konversi harga beli
            const purchasePrice = document.getElementById('purchase_price');
            if (purchasePrice) {
                purchasePrice.value = purchasePrice.dataset.rawValue || '0';
            }
            
            // Konversi biaya perbaikan
            const repairCosts = document.querySelectorAll('.repair-cost');
            repairCosts.forEach(input => {
                if (input.dataset.rawValue) {
                    input.value = input.dataset.rawValue;
                }
            });
        }

        // Hitung total biaya perbaikan
        function calculateTotalRepairCost() {
            let total = 0;
            const repairCosts = document.querySelectorAll('.repair-cost');
            
            repairCosts.forEach(input => {
                const rawValue = input.dataset.rawValue || '0';
                total += parseInt(rawValue, 10);
            });
            
            document.getElementById('total-repair-cost').textContent = 
                'Rp ' + total.toLocaleString('id-ID');
        }

        // Preview uploaded photos
        let selectedFiles = [];
        
        // Fungsi untuk menampilkan preview foto
        function showPhotoPreviews(files) {
            const previewContainer = document.getElementById('photo-preview');
            previewContainer.innerHTML = '';
            
            Array.from(files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'col-md-2 mb-3 position-relative new-photo';
                    div.dataset.index = index;
                    div.innerHTML = `
                         <div class="photo-wrapper position-relative">
                            <img src="${e.target.result}" class="img-thumbnail photo-preview w-100" style="object-fit: cover;">
                            <button type="button" class="btn btn-danger btn-sm remove-new-photo" 
                                    style="position: absolute; top: 4px; right: 4px; border-radius: 50%; padding: 2px 6px;"
                                    data-index="${index}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }
        
        // Event ketika memilih file
        document.getElementById('photos').addEventListener('change', function(e) {
            if (this.files && this.files.length > 0) {
                // Tambahkan file baru ke array selectedFiles
                Array.from(this.files).forEach(file => {
                    selectedFiles.push(file);
                });
                
                // Batasi maksimal 5 file
                if (selectedFiles.length > 5) {
                    selectedFiles = selectedFiles.slice(0, 5);
                    alert('Maksimal 5 foto yang dapat diupload');
                }
                
                // Update preview
                showPhotoPreviews(selectedFiles);
                
                // Update label
                this.nextElementSibling.textContent = `${selectedFiles.length} file dipilih`;
                
            }
        });
        
        // Event untuk menghapus foto baru
        document.addEventListener('click', function(e) {
            // Hapus foto baru
            if (e.target.classList.contains('remove-new-photo') || e.target.closest('.remove-new-photo')) {
                const index = e.target.closest('.remove-new-photo').dataset.index;
                selectedFiles.splice(index, 1);
                showPhotoPreviews(selectedFiles);

                
                // Update label
                const fileInput = document.getElementById('photos');
                fileInput.nextElementSibling.textContent = selectedFiles.length > 0 ? 
                    `${selectedFiles.length} file dipilih` : 'Pilih beberapa foto (maks. 5)';
            }
            
            // Hapus foto yang sudah disimpan (saat edit)
            if (e.target.classList.contains('delete-saved-photo') || e.target.closest('.delete-saved-photo')) {
                const photoElement = e.target.closest('.saved-photo');
                photoElement.remove();
            }
        });
        
        // Fungsi untuk update input file temporary
        function updateTempInput() {
            const tempInput = document.getElementById('photos-temp');
            const dataTransfer = new DataTransfer();
            
            selectedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            
            tempInput.files = dataTransfer.files;
        }
        
        // Reset form
        document.getElementById('resetForm').addEventListener('click', function() {
            selectedFiles = [];
            document.getElementById('photo-preview').innerHTML = '';
            document.getElementById('photos').nextElementSibling.textContent = 'Pilih beberapa foto (maks. 5)';
        });
        
        // Dynamic repair items
        let repairCounter = {{ isset($usedItem) ? $usedItem->repairs->count() : 1 }};
        
        document.getElementById('add-repair').addEventListener('click', function() {
            const container = document.getElementById('repairs-container');
            const item = document.createElement('div');
            item.className = 'repair-item card mb-3';
            item.innerHTML = `
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label>Deskripsi Kerusakan</label>
                                <input type="text" class="form-control" 
                                    name="repairs[${repairCounter}][description]" 
                                    placeholder="Deskripsi kerusakan...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Biaya Perbaikan (Rp)</label>
                                <input type="text" class="form-control repair-cost" 
                                    name="repairs[${repairCounter}][cost]"
                                    placeholder="0"
                                    onkeyup="formatCurrency(this); calculateTotalRepairCost()">
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-center justify-content-center mt-3">
                            <button type="button" class="btn btn-danger btn-block btn-remove-repair btn-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            container.appendChild(item);
            repairCounter++;
            
            // Enable all remove buttons
            document.querySelectorAll('.btn-remove-repair').forEach(btn => {
                btn.disabled = false;
            });
        });
        
        // Remove repair item
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('btn-remove-repair')) {
                const repairItem = e.target.closest('.repair-item');
                if (repairItem) {
                    repairItem.remove();
                    calculateTotalRepairCost();
                }
            }
        });
        
        // Remove photo preview
        document.addEventListener('click', function(e) {
            if (e.target && e.target.closest('.btn-danger') && e.target.closest('.position-absolute') && e.target.closest('.remove-new-photo')) {
                const photoPreview = e.target.closest('.col-md-2');
                if (photoPreview) {
                    photoPreview.remove();
                    
                    // Update file input
                    const input = document.getElementById('photos');
                    const dataTransfer = new DataTransfer();
                    
                    Array.from(input.files).forEach((file, index) => {
                        if (index !== Array.from(photoPreview.parentNode.children).indexOf(photoPreview)) {
                            dataTransfer.items.add(file);
                        }
                    });
                    
                    input.files = dataTransfer.files;
                }
            }
        });
        
        // Konversi format mata uang sebelum submit form
        document.getElementById('item-form').addEventListener('submit', function(e) {
            convertCurrencyToNumber();
            console.log("submit");
            const input = document.getElementById('photos');
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            input.files = dataTransfer.files;
            
            
            const btnSubmit = document.getElementById('submitBtn');
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `
                <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                Loading...
            `;
            return true;
        });
        
        // Inisialisasi total biaya perbaikan
        calculateTotalRepairCost();
        
        // Setup delete photo modal
        $('#deletePhotoModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const mediaId = button.data('media-id');
            const deleteForm = document.getElementById('deletePhotoForm');
            
            // Excekcutie
            let url = "{{ route('used-item.media.destroy', ':id') }}";
            url = url.replace(':id', mediaId);
            deleteForm.action = url;
        });
    </script>
    @endcanAccess
@stop