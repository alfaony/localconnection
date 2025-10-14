@extends('adminlte::page')

@section('title', isset($laptop) ? 'Edit Laptop Bekas' : 'Tambah Laptop Bekas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">{{ isset($laptop) ? 'Edit Laptop Bekas' : 'Tambah Laptop Bekas' }}</h1>
        <a href="{{ route('used-laptop.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@stop

@section('content')
@include('components.alert')
<div class="card card-primary">
    <form action="{{ isset($laptop) ? route('used-laptop.update', $laptop->slug) : route('used-laptop.store') }}" method="POST" enctype="multipart/form-data" id="laptop-form">
        @csrf
        @if(isset($laptop))
            @method('PUT')
        @endif
        
        <div class="card-body">
            <!-- Section 1: Detail Laptop -->
            <div class="section-header mb-4">
                <h3 class="text-primary">
                    <i class="fas fa-laptop mr-2"></i> Detail Laptop
                </h3>
                <div class="border-bottom border-primary mt-2"></div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="laptop_type">Tipe Laptop <span class="text-danger">*</span></label>
                        <select class="form-control" id="laptop_type" name="is_sold" required>
                            @foreach($laptopType as $key => $value)
                                <option value="{{ $value }}" 
                                    {{ isset($laptop) && strval($laptop->is_sold) === strval($value) ? 'selected' : '' }}>
                                    {{ $key }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">Nama Laptop <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="{{ old('name', $laptop->name ?? '') }}"
                               placeholder="Contoh: MacBook Pro 2020" required>
                    </div>

                    <div class="form-group">
                        <label for="weight">Berat (Gram) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="weight" name="weight" 
                               value="{{ old('weight', $laptop->weight ?? '') }}"
                               placeholder="Contoh: 5" >
                    </div>
                    

                    <div class="form-group">
                        <label for="name">Merk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="brand" name="brand" 
                               value="{{ old('brand', $laptop->brand ?? '') }}"
                               placeholder="Apple" required>
                    </div>
                    @canAccess('checkSerialNumber', 'used_laptops')
                    <div class="form-group">
                        <label for="serial_number">Serial Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="serial_number" name="serial_number" 
                                value="{{ old('serial_number', $laptop->serial_number ?? '') }}"
                                placeholder="Masukkan Serial Number" required>
                            <div class="input-group-append">
                                <span class="input-group-text" id="serial-status">
                                    <i class="fas fa-circle text-muted"></i>
                                </span>
                            </div>
                        </div>
                        <small id="serial-feedback" class="form-text"></small>
                        <input type="hidden" id="laptop_id" value="{{ $laptop->id ?? '' }}">
                    </div>
                    @endcanAccess

                    <div class="form-group">
                        <label for="processor">Processor <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="processor" name="processor" 
                               value="{{ old('processor', $laptop->processor ?? '') }}"
                               placeholder="Contoh: Intel Core i7 10th Gen" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ram">RAM <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ram" name="ram" 
                               value="{{ old('ram', $laptop->ram ?? '') }}"
                               placeholder="Contoh: 16GB DDR4" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ssd">Memory <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ssd" name="ssd" 
                               value="{{ old('ssd', $laptop->ssd ?? '') }}"
                               placeholder="Contoh: 512GB NVMe SSD" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="gpu">GPU</label>
                        <input type="text" class="form-control" id="gpu" name="gpu" 
                               value="{{ old('gpu', $laptop->gpu ?? '') }}"
                               placeholder="Contoh: NVIDIA GeForce RTX 3080">
                    </div>
                    
                    <div class="form-group">
                        <label for="operating_system">Sistem Operasi</label>
                        <input type="text" class="form-control" id="operating_system" name="operating_system" 
                               value="{{ old('operating_system', $laptop->operating_system ?? '') }}"
                               placeholder="Contoh: Windows 11 Pro">
                    </div>
                    
                    <div class="form-group">
                        <label for="purchase_price">Harga Beli (Rp) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="purchase_price" name="purchase_price" 
                               value="{{ isset($laptop) ? number_format($laptop->purchase_price, 0, ',', '.') : (old('purchase_price') ? number_format(old('purchase_price'), 0, ',', '.') : '') }}"
                               data-raw-value="{{ old('purchase_price', $laptop->purchase_price ?? '') }}"
                               required onkeyup="formatCurrency(this)">
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Catatan</label>
                        <input class="thriveEditor form-control" id="description_notes" data-ids="notes" name="notes" rows="3" placeholder="yang akan dicetak di perjanjian" value="{{ old('notes', $laptop->notes ?? '') }}"/>

                    </div>
                </div>
            </div>
            
            @canAccess('getLocation','warehouses')
            <!-- Section 2: Foto Laptop -->
            <div class="section-header mb-4 mt-5">
                <h3 class="text-primary">
                    <i class="fas fa-map-marked mr-2"></i> Lokasi Barang
                </h3>
                <div class="border-bottom border-primary mt-2"></div>
            </div>

            <div class="row">
                <!-- Warehouse -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="warehouse_id">Warehouse</label>
                        <div class="input-group">
                            <select class="form-control" id="warehouse_id" name="warehouse_id" >
                                <option value="">Pilih Warehouse</option>
                            </select>
                            <div class="input-group-append">
                                <span class="input-group-text" id="warehouse-status">
                                    <i class="fas fa-circle text-muted"></i>
                                </span>
                            </div>
                        </div>
                        <small id="warehouse-helper" class="form-text text-muted"></small>
                    </div>
                </div>

                <!-- Zone -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="zone_id">Zone</label>
                        <div class="input-group">
                            <select class="form-control" id="zone_id" name="zone_id" disabled>
                                <option value="">Pilih Zone</option>
                            </select>
                            <div class="input-group-append">
                                <span class="input-group-text" id="zone-status">
                                    <i class="fas fa-circle text-muted"></i>
                                </span>
                            </div>
                        </div>
                        <small id="zone-helper" class="form-text text-muted"></small>
                    </div>
                </div>

                <!-- Rack -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="rack_id">Rack</label>
                        <div class="input-group">
                            <select class="form-control" id="rack_id" name="rack_id" disabled>
                                <option value="">Pilih Rack</option>
                            </select>
                            <div class="input-group-append">
                                <span class="input-group-text" id="rack-status">
                                    <i class="fas fa-circle text-muted"></i>
                                </span>
                            </div>
                        </div>
                        <small id="rack-helper" class="form-text text-muted"></small>
                    </div>
                </div>
            </div>
            @endcanAccess
            
            <!-- Section 2: Foto Laptop -->
            <div class="section-header mb-4 mt-5">
                <h3 class="text-primary">
                    <i class="fas fa-camera mr-2"></i> Foto Laptop
                </h3>
                <div class="border-bottom border-primary mt-2"></div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <!-- Upload Area -->
                    <div class="upload-area mb-4">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="photos" name="photos[]" multiple accept="image/*">
                            <label class="custom-file-label" for="photos">
                                <i class="fas fa-cloud-upload-alt mr-2"></i>Pilih beberapa foto
                            </label>
                        </div>
                        <small class="form-text text-muted mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Upload foto laptop dari berbagai sudut (maks. 5 foto, format: JPG, PNG, GIF)
                        </small>
                    </div>

                    @if(isset($laptop) && $laptop->media->count() > 0)
                    <div class="mb-4">
                        <label class="font-weight-bold mb-3">
                            <i class="fas fa-images mr-2 text-primary"></i>Foto Saat Ini (Drag untuk mengurutkan)
                        </label>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-hand-paper mr-2"></i>
                            <strong>Tip:</strong> Seret dan lepas foto untuk mengubah urutan tampilan
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        
                        <div class="photo-grid existing-photos" id="existing-photos-container">
                            @foreach($laptop->media->sortBy('order') as $media)
                            <div class="photo-item" data-media-id="{{ $media->id }}">
                                <div class="photo-wrapper">
                                    <div class="drag-handle">
                                        <i class="fas fa-grip-vertical"></i>
                                    </div>
                                    <img src="{{ Storage::url($media->file_path) }}" class="photo-thumbnail" alt="Laptop photo">
                                    <div class="photo-overlay">
                                        <div class="photo-actions">
                                            <button type="button" class="btn btn-sm btn-light btn-zoom" 
                                                    data-image="{{ Storage::url($media->file_path) }}"
                                                    title="Lihat Ukuran Penuh">
                                                <i class="fas fa-search-plus"></i>
                                            </button>
                                            @canAccess('mediaDestroy','used_items')
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-photo" 
                                                    data-media-id="{{ $media->id }}"
                                                    title="Hapus Foto">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endcanAccess
                                        </div>
                                    </div>
                                    <div class="photo-badge">
                                        <i class="fas fa-image mr-1"></i>#{{ $loop->iteration }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <input type="hidden" id="photo-order" name="photo_order">
                    </div>
                    @endif

                    {{-- ✅ PENTING: Hidden input untuk foto baru HARUS di luar kondisi --}}
                    <input type="hidden" id="new-photo-order" name="new_photo_order">

                    <!-- New Photos Preview -->
                    <div id="new-photos-preview" style="display: none;">
                        <label class="font-weight-bold mb-3">
                            <i class="fas fa-plus-circle mr-2 text-success"></i>Foto Baru (Drag untuk mengurutkan)
                        </label>
                        <div class="photo-grid" id="photo-preview"></div>
                    </div>
                </div>
            </div>
            <div class="row mt-3" id="photo-preview"></div>
            
            <!-- Section 3: Checklist Kondisi -->
            <div class="section-header mb-4 mt-5">
                <h3 class="text-primary">
                    <i class="fas fa-clipboard-check mr-2"></i> Checklist Kondisi
                </h3>
                <div class="border-bottom border-primary mt-2"></div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i> Periksa semua item di bawah ini untuk memastikan kondisi laptop
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
                                $existingCheck = isset($laptop) ? $laptop->checks->firstWhere('master_check_item_id', $item->id) : null;
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
                @if(isset($laptop) && $laptop->repairs->count() > 0)
                    @foreach($laptop->repairs as $index => $repair)
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
                                    @canAccess('mediaDestroy','used_items')
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
                                    Rp {{ isset($laptop) ? number_format($laptop->repairs->sum('cost'), 0, ',', '.') : '0' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        @canAccess('checkSerialNumber', 'used_laptops')
        <div class="card-footer">
           <button type="submit" id="submitBtn" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> {{ isset($laptop) ? 'Update Laptop' : 'Simpan Laptop' }}
            </button>
            <button type="reset" class="btn btn-outline-secondary">
                <i class="fas fa-undo mr-1"></i> Reset Form
            </button>
        </div>
        @endcanAccess
    </form>
</div>

<!-- Delete Photo Modal -->
@canAccess('mediaDestroy', 'used_items')
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
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

        /* ============================================ */
        /* ✅ SELECT2 CSS YANG BENAR */
        /* ============================================ */
        
        /* Container select2 */
        .select2-container {
            width: 100% !important;
        }
        
        /* Single selection box */
        .select2-container--default .select2-selection--single {
            height: 38px !important;
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
            background-color: #fff;
        }
        
        /* Text yang ditampilkan */
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
            padding-left: 12px !important;
            padding-right: 20px !important;
            color: #495057;
        }
        
        /* Arrow dropdown */
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            top: 1px !important;
            right: 1px !important;
        }
        
        /* Dropdown results */
        .select2-container--default .select2-results__option {
            padding: 6px 12px;
        }
        
        /* Hover state */
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #007bff !important;
            color: white;
        }
        
        /* Disabled state */
        .select2-container--default.select2-container--disabled .select2-selection--single {
            background-color: #e9ecef !important;
            cursor: not-allowed !important;
            border-color: #ced4da !important;
        }
        
        /* Focus state */
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #80bdff !important;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
        }
        
        /* Placeholder */
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #6c757d;
        }
        
        /* Dropdown */
        .select2-dropdown {
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
        }
        
        /* Search box dalam dropdown */
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            padding: 4px 8px;
        }
    </style>
    
    
    <style>
        /* ============================================ */
        /* PHOTO UPLOAD & GALLERY STYLES */
        /* ============================================ */
        
        .upload-area {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px dashed #007bff;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .upload-area:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            border-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.15);
        }
        
        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 10px;
        }
        
        @media (max-width: 768px) {
            .photo-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 15px;
            }
        }
        
        .photo-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: move;
        }
        
        .photo-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }
        
        .photo-item.sortable-ghost {
            opacity: 0.4;
            background: #e3f2fd;
        }
        
        .photo-item.sortable-drag {
            opacity: 0.8;
            transform: rotate(5deg);
        }
        
        .photo-wrapper {
            position: relative;
            padding-bottom: 100%; /* 1:1 Aspect Ratio */
            overflow: hidden;
        }
        
        .photo-thumbnail {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .photo-item:hover .photo-thumbnail {
            transform: scale(1.1);
        }
        
        .drag-handle {
            position: absolute;
            top: 8px;
            left: 8px;
            z-index: 3;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            cursor: grab;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .drag-handle:active {
            cursor: grabbing;
        }
        
        .drag-handle:hover {
            background: rgba(0, 123, 255, 0.9);
            transform: scale(1.1);
        }
        
        .photo-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 2;
        }
        
        .photo-item:hover .photo-overlay {
            opacity: 1;
        }
        
        .photo-actions {
            display: flex;
            gap: 10px;
        }
        
        .photo-actions .btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .photo-actions .btn:hover {
            transform: scale(1.2);
        }
        
        .photo-badge {
            position: absolute;
            bottom: 8px;
            right: 8px;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            z-index: 3;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        /* Image Modal Zoom */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .image-modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: zoomIn 0.3s ease;
        }
        
        @keyframes zoomIn {
            from { transform: translate(-50%, -50%) scale(0); }
            to { transform: translate(-50%, -50%) scale(1); }
        }
        
        .image-modal-close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .image-modal-close:hover {
            color: #ff4444;
            transform: scale(1.2);
        }
        
        /* Empty State */
        .empty-photos {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-photos i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .photo-badge.badge-new 
        {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
    </style>
@stop

@section('js')
    <!-- ✅ GUNAKAN URUTAN INI -->
    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Quill Editor -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script src="{{ asset('js/thriveEditor.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // ============================================
            // DRAG & DROP EXISTING PHOTOS
            // ============================================
            const existingPhotosContainer = document.getElementById('existing-photos-container');
            
            if (existingPhotosContainer) {
                const sortable = new Sortable(existingPhotosContainer, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onEnd: function(evt) {
                        updatePhotoOrder();
                        
                        // Update badge numbers
                        $('#existing-photos-container .photo-item').each(function(index) {
                            $(this).find('.photo-badge').html('<i class="fas fa-image mr-1"></i>#' + (index + 1));
                        });
                        
                        // Show toast notification
                        showToast('success', 'Urutan foto berhasil diubah');
                    }
                });
            }
            
            // Function to update photo order
            function updatePhotoOrder() {
                const order = [];
                $('#existing-photos-container .photo-item').each(function(index) {
                    order.push({
                        id: $(this).data('media-id'),
                        order: index
                    });
                });
                $('#photo-order').val(JSON.stringify(order));
                console.log('Photo order updated:', order);
            }
            
            // ============================================
            // NEW PHOTOS UPLOAD & PREVIEW
            // ============================================
            let newPhotosArray = [];

            $('#photos').on('change', function(e) {
                const files = Array.from(this.files);
                const previewContainer = $('#photo-preview');
                
                // Validate total photos
                const existingCount = $('#existing-photos-container .photo-item').length || 0;
                const totalCount = existingCount + files.length;
                
                if (totalCount > 5) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Terlalu Banyak Foto',
                        text: `Maksimal 5 foto. Anda sudah memiliki ${existingCount} foto. Hanya ${5 - existingCount} foto yang dapat ditambahkan.`,
                        confirmButtonColor: '#dc3545',
                    });
                    this.value = '';
                    return;
                }
                
                // Clear previous preview
                previewContainer.empty();
                newPhotosArray = [];
                
                // Show preview container
                $('#new-photos-preview').show();
                
                let loadedCount = 0;
                
                files.forEach((file, index) => {
                    if (!file.type.match('image.*')) {
                        loadedCount++;
                        return;
                    }
                    
                    // Store file with original index
                    newPhotosArray.push({
                        file: file,
                        originalIndex: index,
                        currentOrder: index
                    });
                    
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const photoItem = $(`
                            <div class="photo-item" data-index="${index}">
                                <div class="photo-wrapper">
                                    <div class="drag-handle">
                                        <i class="fas fa-grip-vertical"></i>
                                    </div>
                                    <img src="${event.target.result}" class="photo-thumbnail" alt="New photo">
                                    <div class="photo-overlay">
                                        <div class="photo-actions">
                                            <button type="button" class="btn btn-sm btn-light btn-zoom" 
                                                    data-image="${event.target.result}"
                                                    title="Lihat Ukuran Penuh">
                                                <i class="fas fa-search-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger btn-remove-new-photo" 
                                                    data-index="${index}"
                                                    title="Hapus Foto">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="photo-badge badge-new">
                                        <i class="fas fa-plus mr-1"></i>Baru #${index + 1}
                                    </div>
                                </div>
                            </div>
                        `);
                        previewContainer.append(photoItem);
                        
                        loadedCount++;
                        
                        // ✅ PENTING: Setelah semua foto selesai dimuat, update order
                        if (loadedCount === files.length) {
                            setTimeout(() => {
                                // Initialize Sortable
                                const newPhotosContainer = document.getElementById('photo-preview');
                                if (newPhotosContainer) {
                                    new Sortable(newPhotosContainer, {
                                        animation: 150,
                                        handle: '.drag-handle',
                                        ghostClass: 'sortable-ghost',
                                        dragClass: 'sortable-drag',
                                        onEnd: function(evt) {
                                            updateNewPhotosOrder();
                                            showToast('success', 'Urutan foto baru berhasil diubah');
                                        }
                                    });
                                }
                                
                                // ✅ KUNCI: Update order pertama kali setelah upload (sebelum drag & drop)
                                updateNewPhotosOrder();
                                console.log('Initial photo order set after upload');
                            }, 150);
                        }
                    };
                    reader.readAsDataURL(file);
                });
            });

            // ✅ UPDATE: Function untuk update urutan foto baru
            function updateNewPhotosOrder() {
                const newOrder = [];
                const orderMapping = [];
                
                $('#photo-preview .photo-item').each(function(displayIndex) {
                    const originalIndex = $(this).data('index');
                    
                    // Find file in array
                    const photoData = newPhotosArray.find(p => p.originalIndex === originalIndex);
                    if (photoData) {
                        photoData.currentOrder = displayIndex;
                        newOrder.push(photoData);
                        orderMapping.push(originalIndex); // Store original index in order
                    }
                    
                    // Update badge
                    $(this).find('.photo-badge').html('<i class="fas fa-plus mr-1"></i>Baru #' + (displayIndex + 1));
                });
                
                newPhotosArray = newOrder;
                
                // ✅ Save order mapping to hidden input
                $('#new-photo-order').val(JSON.stringify(orderMapping));
                
                console.log('New photos order updated:', orderMapping);
                console.log('Hidden input value:', $('#new-photo-order').val());
            }
            
            // Update file input with reordered files
            function updateFileInput() {
                const dataTransfer = new DataTransfer();
                newPhotosArray.forEach(file => {
                    dataTransfer.items.add(file);
                });
                document.getElementById('photos').files = dataTransfer.files;
            }
            
            // Remove new photo
            // Remove new photo
            $(document).on('click', '.btn-remove-new-photo', function() {
                const index = $(this).data('index');
                $(this).closest('.photo-item').fadeOut(300, function() {
                    $(this).remove();
                    
                    // Remove from array
                    newPhotosArray = newPhotosArray.filter(p => p.originalIndex !== index);
                    
                    // Hide container if empty
                    if (newPhotosArray.length === 0) {
                        $('#new-photos-preview').hide();
                        $('#photos').val(''); // Clear file input
                        $('#new-photo-order').val(''); // Clear order
                    } else {
                        // ✅ Update order after removal
                        updateNewPhotosOrder();
                    }
                });
            });
            // ============================================
            // DELETE EXISTING PHOTO
            // ============================================
            $(document).on('click', '.btn-delete-photo', function() {
                const mediaId = $(this).data('media-id');
                const photoItem = $(this).closest('.photo-item');
                
                Swal.fire({
                    title: 'Hapus Foto?',
                    text: "Foto yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash mr-1"></i>Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Send AJAX delete request
                        $.ajax({
                            url: `/used-laptop/mediaDestroy/${mediaId}`,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                photoItem.fadeOut(300, function() {
                                    $(this).remove();
                                    updatePhotoOrder();
                                    
                                    // Update badge numbers
                                    $('#existing-photos-container .photo-item').each(function(index) {
                                        $(this).find('.photo-badge').html('<i class="fas fa-image mr-1"></i>#' + (index + 1));
                                    });
                                });
                                
                                showToast('success', 'Foto berhasil dihapus');
                            },
                            error: function(xhr) {
                                showToast('error', 'Gagal menghapus foto');
                            }
                        });
                    }
                });
            });
            
            // ============================================
            // IMAGE ZOOM MODAL
            // ============================================
            $(document).on('click', '.btn-zoom', function() {
                const imageSrc = $(this).data('image');
                showImageModal(imageSrc);
            });
            
            function showImageModal(imageSrc) {
                // Create modal if not exists
                if ($('#imageModal').length === 0) {
                    $('body').append(`
                        <div id="imageModal" class="image-modal">
                            <span class="image-modal-close">&times;</span>
                            <img class="image-modal-content" id="modalImage">
                        </div>
                    `);
                }
                
                $('#modalImage').attr('src', imageSrc);
                $('#imageModal').fadeIn(300);
            }
            
            $(document).on('click', '#imageModal, .image-modal-close', function(e) {
                if (e.target.id === 'imageModal' || $(e.target).hasClass('image-modal-close')) {
                    $('#imageModal').fadeOut(300);
                }
            });
            
            // ============================================
            // TOAST NOTIFICATION
            // ============================================
            function showToast(type, message) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
                
                Toast.fire({
                    icon: type,
                    title: message
                });
            }
        });
    </script>

    <!-- ✅ Include Warehouse Location Selector -->
    @canAccess('getLocation','warehouses')
    <script src="{{ asset('js/warehouseLocation.js') }}"></script>
    <script>
        $(document).ready(function() {
            console.log('jQuery version:', $.fn.jquery);
            console.log('Select2 available:', typeof $.fn.select2 !== 'undefined');
            
            const laptopData = {
                isEditMode: @json(isset($laptop) && $laptop->rack),
                // ✅ JANGAN set currentRackId untuk exclude
                currentRackId: null,  // Set null agar tidak di-exclude
                initialWarehouseId: @json(isset($laptop) && $laptop->rack && $laptop->rack->zone ? $laptop->rack->zone->warehouse_id : null),
                initialZoneId: @json(isset($laptop) && $laptop->rack ? $laptop->rack->zone_id : null),
                initialRackId: @json(isset($laptop) && $laptop->rack_id ? $laptop->rack_id : null)
            };
            
            console.log('Laptop data:', laptopData);
            
            const locationSelector = new WarehouseLocationSelector({
                apiUrl: '{{ route("warehouses.get-location") }}',
                
                isEditMode: laptopData.isEditMode,
                currentRackId: null,  // Tidak exclude
                initialWarehouseId: laptopData.initialWarehouseId,
                initialZoneId: laptopData.initialZoneId,
                initialRackId: laptopData.initialRackId,
                
                useSelect2: true,  // ✅ DISABLE Select2 dulu
                showAlerts: true,
                debug: true
            });
            
            $('#laptop-form').on('submit', function(e) {
                // if (!locationSelector.validate()) {
                //     e.preventDefault();
                //     return false;
                // }
                
                // const values = locationSelector.getValues();
                // console.log('Selected location:', values);
                
                return true;
            });
        });
    </script>
    @endcanAccess

    @canAccess('checkSerialNumber', 'used_laptops')
    <script>
        // Serial Number Validation
        let serialCheckTimeout;
        let isSerialValid = {{ isset($laptop) ? 'true' : 'false' }}; // Default true saat edit
        let isCheckingSerial = false;
        const originalSerialNumber = "{{ $laptop->serial_number ?? '' }}";
        
        $('#serial_number').on('input', function() {
            clearTimeout(serialCheckTimeout);
            const serialNumber = $(this).val().trim();
            const laptopId = $('#laptop_id').val();
            
            // Reset state
            $('#serial-status i').removeClass().addClass('fas fa-circle text-muted');
            $('#serial-feedback').removeClass().html('');
            $('#serial_number').removeClass('is-valid is-invalid');
            
            if (serialNumber.length === 0) {
                isSerialValid = false;
                isCheckingSerial = false;
                return;
            }
            
            // Jika serial number sama dengan original (saat edit), langsung valid
            if (originalSerialNumber && serialNumber === originalSerialNumber) {
                isSerialValid = true;
                isCheckingSerial = false;
                $('#serial_number').addClass('is-valid');
                $('#serial-status i').removeClass().addClass('fas fa-check-circle text-success');
                $('#serial-feedback').removeClass().addClass('text-success').html(
                    '<i class="fas fa-info-circle mr-1"></i>Serial number tidak berubah'
                );
                return;
            }
            
            // Show loading
            isCheckingSerial = true;
            $('#serial-status i').removeClass().addClass('fas fa-spinner fa-spin text-primary');
            $('#serial-feedback').removeClass().addClass('text-muted').html('<i class="fas fa-clock mr-1"></i>Mengecek ketersediaan...');
            
            serialCheckTimeout = setTimeout(function() {
                $.ajax({
                    url: "{{ route('used-laptop.check-serial') }}",
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        serial_number: serialNumber,
                        laptop_id: laptopId
                    },
                    success: function(response) {
                        isCheckingSerial = false;
                        
                        if (response.exists) {
                            // Serial number sudah ada
                            isSerialValid = false;
                            $('#serial_number').addClass('is-invalid');
                            $('#serial-status i').removeClass().addClass('fas fa-times-circle text-danger');
                            $('#serial-feedback').removeClass().addClass('text-danger').html(
                                '<i class="fas fa-exclamation-triangle mr-1"></i>' +
                                '<strong>Serial number sudah terdaftar!</strong><br>' +
                                '<small>Laptop: ' + response.laptop.brand + ' - ' + response.laptop.name + 
                                ' <a href="/used-laptop/' + response.laptop.slug + '" target="_blank" class="text-primary">Lihat Detail <i class="fas fa-external-link-alt"></i></a></small>'
                            );
                            
                            // Show sweet alert
                            Swal.fire({
                                icon: 'warning',
                                title: 'Serial Number Sudah Terdaftar',
                                html: '<p class="mb-2">Serial number <strong>' + serialNumber + '</strong> sudah digunakan untuk:</p>' +
                                      '<div class="alert alert-warning">' +
                                      '<strong>' + response.laptop.brand + ' - ' + response.laptop.name + '</strong>' +
                                      '</div>',
                                showCancelButton: true,
                                confirmButtonText: '<i class="fas fa-eye mr-1"></i> Lihat Laptop',
                                cancelButtonText: 'Tutup',
                                confirmButtonColor: '#007bff',
                                cancelButtonColor: '#6c757d',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.open('/used-laptop/' + response.laptop.slug, '_blank');
                                }
                            });
                        } else {
                            // Serial number tersedia
                            isSerialValid = true;
                            $('#serial_number').removeClass('is-invalid').addClass('is-valid');
                            $('#serial-status i').removeClass().addClass('fas fa-check-circle text-success');
                            $('#serial-feedback').removeClass().addClass('text-success').html(
                                '<i class="fas fa-check mr-1"></i>Serial number tersedia'
                            );
                        }
                    },
                    error: function() {
                        isCheckingSerial = false;
                        isSerialValid = false;
                        $('#serial-status i').removeClass().addClass('fas fa-exclamation-circle text-warning');
                        $('#serial-feedback').removeClass().addClass('text-warning').html(
                            '<i class="fas fa-exclamation-triangle mr-1"></i>Gagal memeriksa serial number. Silakan coba lagi.'
                        );
                    }
                });
            }, 800); // Debounce 800ms
        });
        
        // Validate on form submit
        $('#laptop-form').on('submit', function(e) {
            const serialNumber = $('#serial_number').val().trim();
            
            // Cek jika masih dalam proses pengecekan
            if (isCheckingSerial) {
                e.preventDefault();
                Swal.fire({
                    icon: 'info',
                    title: 'Mohon Tunggu',
                    text: 'Sedang memvalidasi serial number...',
                    confirmButtonColor: '#007bff',
                });
                return false;
            }
            
            // Jika serial number sama dengan original (saat edit), skip validasi
            if (originalSerialNumber && serialNumber === originalSerialNumber) {
                convertCurrencyToNumber();
                disableSubmitButton();
                return true; // Allow form submission
            }
            
            // Validasi serial number
            if (!isSerialValid) {
                e.preventDefault();
                
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Serial number sudah terdaftar atau tidak valid. Silakan gunakan serial number yang berbeda.',
                    confirmButtonColor: '#dc3545',
                });
                
                // Scroll to serial number field
                $('html, body').animate({
                    scrollTop: $('#serial_number').offset().top - 100
                }, 500);
                
                $('#serial_number').focus();
                return false;
            }
            
            // Jika semua validasi lolos
            convertCurrencyToNumber();
            disableSubmitButton();
            return true; // Allow form submission
        });
        
        // Function to disable submit button
        function disableSubmitButton() {
            const btnSubmit = document.getElementById('submitBtn');
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `
                <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                Loading...
            `;
        }
        
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
        
        // Dynamic repair items
        let repairCounter = {{ isset($laptop) ? $laptop->repairs->count() : 1 }};
        
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
        
        // Inisialisasi total biaya perbaikan
        calculateTotalRepairCost();
    </script>
    @endcanAccess
    
    @canAccess('mediaDestroy', 'used_items')
    <script>
        // Setup delete photo modal
        $('#deletePhotoModal').on('show.bs.modal', function (event) {
            const button = $(event.relatedTarget);
            const mediaId = button.data('media-id');
            const deleteForm = document.getElementById('deletePhotoForm');

            console.log("hore");
            
            // Excekcutie
            let url = "{{ route('used-laptop.media.destroy', ':id') }}";
            url = url.replace(':id', mediaId);
            deleteForm.action = url;
        });
    </script>
    @endcanAccess
@stop