@extends('adminlte::page')

@section('title', 'Detail Pemasok Produk')

@section('content_header')
    <h1>Detail Pemasok Produk</h1>
@stop

@section('content')
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('product-supplier.index') }}">Product Suppliers</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $supplier->store_name }}</li>
        </ol>
    </nav>

    <div class="card shadow-lg border-0 rounded-3">
        <div class="card-header bg-primary text-white py-3 rounded-top">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">
                    <i class="fas fa-store-alt me-2"></i>{{ $supplier->store_name }}
                </h3>
                <div class="d-flex gap-2">
                    @canAccess('edit','product_suppliers')
                    <a href="{{ route('product-supplier.edit', $supplier->id) }}" 
                       class="btn btn-light btn-sm rounded-3 mr-2"
                       data-bs-toggle="tooltip" 
                       title="Edit Supplier">
                        <i class="fas fa-edit text-primary"></i>
                    </a>
                    @endcanAccess
                    
                    @canAccess('destroy','product_suppliers')
                    <form action="{{ route('product-supplier.destroy', $supplier->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" 
                                class="btn btn-light btn-sm rounded-3"
                                onclick="return confirm('Hapus supplier ini?')"
                                data-bs-toggle="tooltip" 
                                title="Hapus Supplier">
                            <i class="fas fa-trash-alt text-danger"></i>
                        </button>
                    </form>
                    @endcanAccess
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <!-- Main Information -->
                <div class="col-md-8">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="bg-light p-4 rounded-3">
                                <h5 class="text-primary mb-4">
                                    <i class="fas fa-user-tie me-2"></i>Informasi Pemilik
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-4 fw-normal text-muted">Nama Pemilik</dt>
                                            <dd class="col-sm-8 fw-medium">{{ $supplier->owner_name }}</dd>
                                            
                                            <dt class="col-sm-4 fw-normal text-muted">No. Telepon</dt>
                                            <dd class="col-sm-8">
                                                <a href="tel:{{ $supplier->phone_number }}" class="text-dark">
                                                    {{ $supplier->phone_number }}
                                                </a>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-4 fw-normal text-muted">Lokasi</dt>
                                            <dd class="col-sm-8">
                                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                                {{ $supplier->location }}
                                            </dd>
                                            
                                            <dt class="col-sm-4 fw-normal text-muted">Dibuat Pada</dt>
                                            <dd class="col-sm-8">
                                                {{ $supplier->created_at->format('d M Y H:i') }}
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Supplier Categories -->
                        <div class="col-12">
                            <div class="bg-light p-4 rounded-3">
                                <h5 class="text-primary mb-4">
                                    <i class="fas fa-tags me-2"></i>Kategori Supplier
                                </h5>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($supplier->supplierCategories as $category)
                                        <span class="badge bg-white text-dark border py-2 px-3">
                                            <i class="fas fa-tag me-2 text-muted"></i>
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="col-12">
                            <div class="bg-light p-4 rounded-3">
                                <h5 class="text-primary mb-4">
                                    <i class="fas fa-info-circle me-2"></i>Informasi Tambahan
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-2">Informasi Penjualan</h6>
                                        <p class="mb-0">
                                            {!! $supplier->sales_information ? nl2br(e($supplier->sales_information)) : '-' !!}
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-2">Catatan Tambahan</h6>
                                        <p class="mb-0">
                                            {!! $supplier->additional_information ? nl2br(e($supplier->additional_information)) : '-' !!}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photo Section -->
                <div class="col-md-4">
                    <div class="sticky-top" style="top: 20px;">
                        <div class="bg-light p-4 rounded-3 mb-4">
                            <h5 class="text-primary mb-4">
                                <i class="fas fa-camera me-2"></i>Dokumentasi
                            </h5>
                            
                            <!-- Store Photo -->
                            <div class="mb-4">
                                <h6 class="text-muted small mb-2">Foto Toko</h6>
                                @if($supplier->store_photo)
                                    <a href="{{ Storage::url($supplier->store_photo) }}" data-lightbox="store-photo">
                                        <img src="{{ Storage::url($supplier->store_photo) }}" 
                                             class="img-fluid rounded-3 shadow-sm"
                                             alt="Store Photo">
                                    </a>
                                @else
                                    <div class="bg-white rounded-3 p-4 text-center border">
                                        <i class="fas fa-store fa-2x text-muted mb-3"></i>
                                        <p class="small text-muted mb-0">Tidak ada foto toko</p>
                                    </div>
                                @endif
                            </div>

                            <!-- KTP Photo -->
                            <div>
                                <h6 class="text-muted small mb-2">Foto KTP</h6>
                                @if($supplier->ktp_photo)
                                    <a href="{{ Storage::url($supplier->ktp_photo) }}" data-lightbox="ktp-photo">
                                        <img src="{{ Storage::url($supplier->ktp_photo) }}" 
                                             class="img-fluid rounded-3 shadow-sm"
                                             alt="KTP Photo">
                                    </a>
                                @else
                                    <div class="bg-white rounded-3 p-4 text-center border">
                                        <i class="fas fa-id-card fa-2x text-muted mb-3"></i>
                                        <p class="small text-muted mb-0">Tidak ada foto KTP</p>
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
@endsection
@section('js')
<!-- Lightbox JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
@endsection

@section('css')
<!-- Lightbox CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
<style>
    .breadcrumb {
        background-color: #f8f9fa;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
    }
    
    .badge {
        transition: all 0.2s;
    }
    
    .img-hover-zoom {
        transition: transform .2s;
    }
    
    .img-hover-zoom:hover {
        transform: scale(1.02);
        cursor: zoom-in;
    }
</style>
@endsection