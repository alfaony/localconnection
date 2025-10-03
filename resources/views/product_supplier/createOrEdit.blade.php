@extends('adminlte::page')

@section('title', isset($productSupplier) ? 'Edit Supplier' : 'Create Supplier')

@section('content')
@include('components.alert')

<div class="card shadow-lg mt-5">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">{{ isset($productSupplier) ? 'Edit Supplier' : 'Tambah Supplier Baru' }}</h5>
    </div>
    <div class="card-body">
        <form
            action="{{ isset($productSupplier) ? route('product-supplier.update', $productSupplier->id) : route('product-supplier.store') }}"
            method="POST"
            enctype="multipart/form-data"
            >
            @csrf
            @isset($productSupplier)
            @method('PUT')
            @endisset

            <div class="row">
                <!-- Kolom Kiri -->
                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label for="owner_name" class="form-label fw-bold">Nama Pemilik <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="owner_name" class="form-control rounded-end"
                                value="{{ old('owner_name', $productSupplier->owner_name ?? '') }}"
                                placeholder="Nama lengkap pemilik" required>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="store_name" class="form-label fw-bold">Nama Toko <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-store"></i></span>
                            <input type="text" name="store_name" class="form-control rounded-end"
                                value="{{ old('store_name', $productSupplier->store_name ?? '') }}"
                                placeholder="Nama toko supplier" required>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="phone_number" class="form-label fw-bold">Nomor Telepon <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" name="phone_number" class="form-control rounded-end"
                                value="{{ old('phone_number', $productSupplier->phone_number ?? '') }}"
                                placeholder="Format: +62" required>
                        </div>
                    </div>
                    <div class="form-group mb-4">
                        <label for="location" class="form-label fw-bold">Lokasi <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" name="location" class="form-control rounded-end"
                                value="{{ old('location', $productSupplier->location ?? '') }}"
                                placeholder="Alamat lengkap toko" required>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label for="supplier_categories" class="form-label fw-bold">Kategori Supplier <span
                                class="text-danger">*</span></label>
                        <select name="supplier_categories[]" id="supplierCategories" class="form-control select2"
                            multiple="multiple" data-placeholder="Pilih kategori" style="width: 100%;" >
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" @if(isset($productSupplier) && $productSupplier->
                                supplierCategories->contains($category->id)) selected @endif>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted mt-1">
                            <i class="fas fa-info-circle"></i> Tekan tombol spasi untuk menambahkan kategori baru
                        </small>
                    </div>
                    <div class="form-group mb-4">
                        <label for="type" class="form-label fw-bold">Tipe Supplier <span
                                class="text-danger">*</span></label>
                        <select name="supplier_type_id" id="type" class="form-control select2" data-placeholder="Pilih tipe"
                            style="width: 100%;" required>
                            <option value="">-- Pilih Tipe --</option>
                            @foreach($types as $type)
                            <option value="{{ $type->id }}" @if(isset($productSupplier) && $productSupplier->supplier_type_id == $type->id) selected @endif>
                                {{ $type->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Foto KTP</label>
                        <div class="file-upload-container">
                            <input type="file" name="ktp_photo" id="ktpPhoto" class="form-control" accept="image/*">
                            <div class="image-preview mt-2" id="ktpPreview"></div>
                            <small class="form-text text-muted">Format: JPG/PNG (maks. 2MB)</small>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label fw-bold">Foto Toko</label>
                        <div class="file-upload-container">
                            <input type="file" name="store_photo" id="storePhoto" class="form-control" accept="image/*">
                            <div class="image-preview mt-2" id="storePreview"></div>
                            <small class="form-text text-muted">Format: JPG/PNG (maks. 2MB)</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Textarea Section -->
            <div class="row mt-2">
                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label for="sales_information" class="form-label fw-bold">Informasi Penjualan</label>
                        <textarea name="sales_information" class="form-control" rows="3"
                            placeholder="Contoh: Minimal order, syarat pembayaran, dll">{{ old('sales_information', $productSupplier->sales_information ?? '') }}</textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-4">
                        <label for="additional_information" class="form-label fw-bold">Informasi Tambahan</label>
                        <textarea name="additional_information" class="form-control" rows="3"
                            placeholder="Catatan khusus tentang supplier">{{ old('additional_information', $productSupplier->additional_information ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('product-supplier.index') }}" class="btn btn-outline-secondary rounded-pill px-4 mr-2">
                    <i class="fas fa-arrow-left me-2"></i> Kembali
                </a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">
                    <i class="fas fa-save me-2"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
   document.addEventListener('DOMContentLoaded', function() {
    const compressImage = async (file, maxWidth = 800, quality = 0.7) => {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;

                    if (width > maxWidth) {
                        height = Math.round((height *= maxWidth / width));
                        width = maxWidth;
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    canvas.toBlob((blob) => {
                        resolve(new File([blob], file.name, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        }));
                    }, 'image/jpeg', quality);
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    };

    const handleImageUpload = async (input, previewId) => {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const preview = document.getElementById(previewId);
            
            // Kompresi gambar
            const compressedFile = await compressImage(file);
            
            // Preview gambar
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.innerHTML = `
                    <div class="position-relative d-inline-block">
                        <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px;">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0" 
                            onclick="this.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            };
            reader.readAsDataURL(compressedFile);
            
            // Replace file dengan yang sudah dikompresi
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(compressedFile);
            input.files = dataTransfer.files;
        }
    };

    // Event listeners untuk kedua input file
    document.getElementById('ktpPhoto').addEventListener('change', function() {
        handleImageUpload(this, 'ktpPreview');
    });
    
    document.getElementById('storePhoto').addEventListener('change', function() {
        handleImageUpload(this, 'storePreview');
    });
}); 
</script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        tags: true, // Allow new values
        placeholder: "Select or add categories",
        allowClear: true
    });

    $('#addCategoryForm').on('submit', function(e) {
        e.preventDefault();
        let categoryName = $('#new_category_name').val();
        if (categoryName.trim() === '') return;

        $.ajax({
            url: "{{ route('supplier-category.store') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: categoryName
            },
            success: function(response) {
                if (response.success) {
                    let newOption = new Option(response.category.name, response.category.id,
                        true, true);
                    $('#supplierCategories').append(newOption).trigger('change');
                    $('#new_category_name').val('');
                    $('#addCategoryModal').modal('hide');
                } else {
                    alert("Error adding category.");
                }
            },
            error: function() {
                alert("Error adding category.");
            }
        });
    });
});
</script>
@stop
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
.card {
    border: none;
    border-radius: 15px;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    padding: 1.5rem;
}

.form-control {
    border-radius: 8px;
    transition: all 0.3s ease;
}

.form-control:focus {
    box-shadow: 0 0 8px rgba(13, 110, 253, 0.25);
    border-color: #86b7fe;
}

.select2-selection {
    border-radius: 8px !important;
    min-height: 38px !important;
    padding: 4px !important;
}

.rounded-pill {
    transition: all 0.3s ease;
}
</style>
<style>
.thriveEditor {
    height: 100px;
}

.select2-selection__choice {
    background-color: #007bff !important;
    border: 1px solid #007bff !important;
}

.select2-selection__choice__remove {
    color: #fe0700 !important;
    border: 1px solid #007bff !important;
}
</style>
<style>
.file-upload-container {
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    padding: 1rem;
    transition: all 0.3s ease;
}

.file-upload-container:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}

.image-preview img {
    border-radius: 8px;
    transition: transform 0.3s ease;
}

.image-preview img:hover {
    transform: scale(1.05);
}

.btn-remove-image {
    right: -10px;
    top: -10px;
    padding: 0.35rem 0.5rem;
}
</style>
@endsection