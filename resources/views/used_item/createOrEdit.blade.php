@extends('adminlte::page')

@section('title', isset($usedItem) ? 'Edit Barang Bekas' : 'Tambah Barang Bekas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">{{ isset($usedItem) ? 'Edit Barang Bekas' : 'Tambah Barang Bekas' }}</h1>
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
                        <div class="col-md-2 mb-3 position-relative">
                            <img src="{{ Storage::url($media->file_path) }}" class="img-thumbnail photo-preview">
                            <button type="button" class="btn btn-danger btn-sm position-absolute" 
                                    style="top: -10px; right: -10px; border-radius: 50%; padding: 2px 8px;"
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
            
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th width="40%">Item Pemeriksaan</th>
                            <th width="20%">Kondisi</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checkItems as $item)
                            @php
                                $existingCheck = isset($usedItem) ? $usedItem->checks->firstWhere('master_check_item_id', $item->id) : null;
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-primary mr-3">
                                            <i class="fas fa-check text-white"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $item->name }}</strong>
                                            <div class="text-muted small">{{ $item->description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <select class="form-control" name="check_items[{{ $item->id }}][condition]" >
                                        <option value="" {{ is_null($existingCheck) && !old("check_items.{$item->id}.condition") ? 'selected' : '' }}> Pilih </option>
                                        <option value="good" {{ ($existingCheck->status ?? old("check_items.{$item->id}.condition")) == 'good' ? 'selected' : '' }}>Baik</option>
                                        <option value="damaged" {{ ($existingCheck->status ?? old("check_items.{$item->id}.condition")) == 'damaged' ? 'selected' : '' }}>Rusak</option>
                                    </select>
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
            <button type="submit" class="btn btn-primary" id="submit-btn"
                onclick="this.disabled = true; this.form.submit(); this.innerHTML = '<i class=\'fas fa-spinner fa-spin mr-1\'></i> Menyimpan...';">
                <i class="fas fa-save mr-1"></i> {{ isset($laptop) ? 'Update Laptop' : 'Simpan Laptop' }}
            </button>
            <button type="reset" class="btn btn-outline-secondary">
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
        document.getElementById('photos').addEventListener('change', function(e) {
            const previewContainer = document.getElementById('photo-preview');
            previewContainer.innerHTML = '';
            
            if (this.files.length > 5) {
                alert('Maksimal 5 foto yang dapat diupload');
                this.value = '';
                return;
            }
            
            for (const file of this.files) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const div = document.createElement('div');
                    div.className = 'col-md-2 mb-3';
                    div.innerHTML = `
                        <div class="position-relative">
                            <img src="${event.target.result}" class="img-thumbnail photo-preview">
                            <button type="button" class="btn btn-danger btn-sm position-absolute" 
                                    style="top: -10px; right: -10px; border-radius: 50%; padding: 2px 8px;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    previewContainer.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
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
            if (e.target && e.target.closest('.btn-danger') && e.target.closest('.position-absolute')) {
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