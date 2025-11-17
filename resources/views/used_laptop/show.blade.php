@extends('adminlte::page')

@section('title', 'Detail Laptop Bekas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">
            <i class="fas fa-laptop mr-2"></i> Detail Laptop Bekas
        </h1>
        <div>
            <a href="{{ route('used-laptop.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            @if(!$laptop->is_sold)
            @canAccess('update','used_items')
            <a href="{{ route('used-laptop.edit', $laptop->slug) }}" class="btn btn-primary ml-2">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            @endcanAccess
            @endif
        </div>
    </div>
@stop

@section('content')
@include('components.alert')

<div class="row">
    <!-- Kolom Kiri: Detail Utama -->
    <div class="col-lg-8">
        <!-- Header Card -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h3 class="text-primary mb-2">{{ $laptop->name }}</h3>
                        <p class="text-muted mb-1">
                            <i class="fas fa-tag mr-1"></i> 
                            <strong>Brand:</strong> {{ $laptop->brand }}
                        </p>
                        <p class="text-muted mb-0">
                            <i class="fas fa-barcode mr-1"></i> 
                            <strong>Serial Number:</strong> 
                            <code class="bg-light px-2 py-1 rounded">{{ $laptop->serial_number }}</code>
                        </p>
                    </div>
                    <div>
                        <span class="badge badge-{{ $laptop->is_sold == 1 ? 'success' : ($laptop->is_sold == 0 ? 'warning' : 'info') }} badge-pill px-3 py-2" style="font-size: 1rem;">
                            <i class="fas fa-{{ $laptop->is_sold == 1 ? 'check-circle' : ($laptop->is_sold == 0 ? 'clock' : 'warehouse') }} mr-1"></i>
                            {{ $laptop->sale_status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- LOKASI PENYIMPANAN CARD -->
        <!-- ============================================ -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-gradient-primary">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-map-marked-alt mr-2"></i> Lokasi Penyimpanan
                </h5>
            </div>
            <div class="card-body">
                @if($laptop->rack)
                    <div class="row">
                        <!-- Warehouse -->
                        <div class="col-md-4 mb-3">
                            <div class="location-card bg-primary text-white">
                                <div class="location-icon">
                                    <i class="fas fa-warehouse fa-2x"></i>
                                </div>
                                <div class="location-content">
                                    <small class="d-block opacity-75 mb-1">Warehouse</small>
                                    <h5 class="mb-0 font-weight-bold">
                                        {{ $laptop->rack->zone->warehouse->name }}
                                    </h5>
                                    @if($laptop->rack->zone->warehouse->address)
                                        <small class="d-block opacity-75 mt-1">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            {{ Str::limit($laptop->rack->zone->warehouse->address, 30) }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Zone -->
                        <div class="col-md-4 mb-3">
                            <div class="location-card bg-info text-white">
                                <div class="location-icon">
                                    <i class="fas fa-map-marker-alt fa-2x"></i>
                                </div>
                                <div class="location-content">
                                    <small class="d-block opacity-75 mb-1">Zone</small>
                                    <h5 class="mb-0 font-weight-bold">
                                        {{ $laptop->rack->zone->name }}
                                    </h5>
                                    {{--
                                    @if($laptop->rack->zone->code)
                                        <small class="d-block opacity-75 mt-1">
                                            <i class="fas fa-code mr-1"></i> {{ $laptop->rack->zone->code }}
                                        </small>
                                    @endif
                                    --}}
                                </div>
                            </div>
                        </div>

                        <!-- Rack -->
                        <div class="col-md-4 mb-3">
                            <div class="location-card bg-secondary text-white">
                                <div class="location-icon">
                                    <i class="fas fa-th fa-2x"></i>
                                </div>
                                <div class="location-content">
                                    <small class="d-block opacity-75 mb-1">Rack</small>
                                    <h5 class="mb-0 font-weight-bold">
                                        {{ $laptop->rack->name }}
                                    </h5>
                                    {{--
                                    <small class="d-block opacity-75 mt-1">
                                        <i class="fas fa-code mr-1"></i> {{ $laptop->rack->code }}
                                        @if($laptop->rack->capacity)
                                            <span class="ml-2">
                                                <i class="fas fa-box mr-1"></i> Kapasitas: {{ $laptop->rack->capacity }}
                                            </span>
                                        @endif
                                    </small>
                                    --}}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Path Visual -->
                    <div class="alert alert-light border mb-0">
                        <div class="d-flex align-items-center justify-content-center flex-wrap">
                            <span class="badge badge-primary px-3 py-2 mr-2 mb-2">
                                <i class="fas fa-warehouse mr-1"></i>
                                {{ $laptop->rack->zone->warehouse->name }}
                            </span>
                            <i class="fas fa-chevron-right text-muted mr-2 mb-2"></i>
                            <span class="badge badge-info px-3 py-2 mr-2 mb-2">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                {{ $laptop->rack->zone->name }}
                            </span>
                            <i class="fas fa-chevron-right text-muted mr-2 mb-2"></i>
                            <span class="badge badge-secondary px-3 py-2 mb-2">
                                <i class="fas fa-th mr-1"></i>
                                {{ $laptop->rack->name }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                            <div>
                                <h6 class="mb-1">Lokasi Belum Ditentukan</h6>
                                <p class="mb-0 small">Laptop ini belum memiliki lokasi penyimpanan. Silakan edit untuk menambahkan lokasi.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Spesifikasi Laptop -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-gradient-info">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-desktop mr-2"></i> Spesifikasi Laptop
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="spec-item">
                            <div class="spec-icon bg-primary">
                                <i class="fas fa-microchip text-white"></i>
                            </div>
                            <div class="spec-content">
                                <small class="text-muted d-block">Processor</small>
                                <strong>{{ $laptop->processor }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="spec-item">
                            <div class="spec-icon bg-success">
                                <i class="fas fa-memory text-white"></i>
                            </div>
                            <div class="spec-content">
                                <small class="text-muted d-block">RAM</small>
                                <strong>{{ $laptop->ram }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="spec-item">
                            <div class="spec-icon bg-info">
                                <i class="fas fa-hdd text-white"></i>
                            </div>
                            <div class="spec-content">
                                <small class="text-muted d-block">Storage (SSD)</small>
                                <strong>{{ $laptop->ssd }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="spec-item">
                            <div class="spec-icon bg-warning">
                                <i class="fas fa-gamepad text-white"></i>
                            </div>
                            <div class="spec-content">
                                <small class="text-muted d-block">GPU</small>
                                <strong>{{ $laptop->gpu ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="spec-item">
                            <div class="spec-icon bg-secondary">
                                <i class="fas fa-window-restore text-white"></i>
                            </div>
                            <div class="spec-content">
                                <small class="text-muted d-block">Sistem Operasi</small>
                                <strong>{{ $laptop->operating_system ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="spec-item">
                            <div class="spec-icon bg-dark">
                                <i class="fas fa-weight text-white"></i>
                            </div>
                            <div class="spec-content">
                                <small class="text-muted d-block">Berat</small>
                                <strong>{{ $laptop->weight ?? '-' }} gram</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Harga -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-gradient-success">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-money-bill-wave mr-2"></i> Informasi Harga
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="price-card border-danger">
                            <div class="price-icon bg-danger">
                                <i class="fas fa-shopping-cart text-white"></i>
                            </div>
                            <div class="price-content">
                                <small class="text-muted d-block mb-1">Harga Beli</small>
                                <h5 class="mb-0 font-weight-bold text-danger">
                                    Rp {{ number_format($laptop->purchase_price, 0, ',', '.') }}
                                </h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="price-card border-success">
                            <div class="price-icon bg-success">
                                <i class="fas fa-map-marker-alt text-white"></i>
                            </div>
                            <div class="price-content">
                                <small class="text-muted d-block mb-1">Harga Jakarta</small>
                                <h5 class="mb-0 font-weight-bold text-success">
                                    Rp {{ number_format($laptop->jakarta_price, 0, ',', '.') }}
                                </h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="price-card border-warning">
                            <div class="price-icon bg-warning">
                                <i class="fas fa-map-marker-alt text-white"></i>
                            </div>
                            <div class="price-content">
                                <small class="text-muted d-block mb-1">Harga Jambi</small>
                                <h5 class="mb-0 font-weight-bold text-warning">
                                    Rp {{ number_format($laptop->jambi_price, 0, ',', '.') }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Raw Price -->
                <div class="alert alert-light border">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-calculator text-info mr-2"></i>
                            <strong>Harga Jual Disarankan (RAW)</strong>
                            <small class="text-muted d-block">Perhitungan: (Harga Beli + Perbaikan) + 30%</small>
                        </div>
                        <h5 class="mb-0 text-info font-weight-bold">
                            Rp {{ number_format($laptop->suggested_selling_price, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catatan -->
        @if($laptop->notes)
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-sticky-note text-warning mr-2"></i> Catatan
                </h5>
            </div>
            <div class="card-body">
                <div class="notes-content">
                    {!! $laptop->notes !!}
                </div>
            </div>
        </div>
        @endif

        <!-- Kondisi -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-gradient-info">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-clipboard-check mr-2"></i> Checklist Kondisi
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="60%">Item Pemeriksaan</th>
                                <th class="text-center">Kondisi</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($laptop->checks->where('status', '!=', null) as $check)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="check-icon bg-{{ $check->status == 'good' ? 'success' : 'danger' }} mr-3">
                                            <i class="fas fa-{{ $check->status == 'good' ? 'check' : 'times' }} text-white"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $check->item->name }}</strong>
                                            <div class="text-muted small">{{ $check->item->description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $check->status == 'good' ? 'success' : 'danger' }} px-3 py-2">
                                        <i class="fas fa-{{ $check->status == 'good' ? 'check-circle' : 'times-circle' }} mr-1"></i>
                                        {{ $check->status == 'good' ? 'Baik' : 'Rusak' }}
                                    </span>
                                </td>
                                <td>{{ $check->notes ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">
                                    <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                                    <p class="text-muted mb-0">Tidak ada item yang diperiksa</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kerusakan dan Perbaikan -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-gradient-warning">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-tools mr-2"></i> Kerusakan dan Perbaikan
                </h5>
            </div>
            <div class="card-body">
                @if($laptop->repairs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="70%">Deskripsi Kerusakan</th>
                                <th class="text-right">Biaya Perbaikan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($laptop->repairs as $repair)
                            <tr>
                                <td>
                                    <i class="fas fa-wrench text-warning mr-2"></i>
                                    {{ $repair->repair_item }}
                                </td>
                                <td class="text-right">
                                    <span class="font-weight-bold text-danger">
                                        Rp {{ number_format($repair->cost, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-light">
                            <tr>
                                <td class="text-right font-weight-bold">Total Biaya Perbaikan:</td>
                                <td class="text-right">
                                    <span class="h5 font-weight-bold text-danger mb-0">
                                        Rp {{ number_format($laptop->repairs->sum('cost'), 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle mr-2"></i> Tidak ada kerusakan yang dicatat
                </div>
                @endif
            </div>
        </div>

        <!-- Form Input Harga Jual -->
        @if(isset($laptop->is_sold) && !$laptop->is_sold)
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-gradient-success">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-money-bill-wave mr-2"></i> Input Penjualan Laptop
                </h5>
            </div>
            <div class="card-body">
                @canAccess('maskAsSold','used_items')
                <form action="{{ route('used-laptop.mark-as-sold', $laptop->slug) }}" method="POST" id="sale-form">
                    @csrf
                    @method('PATCH')
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sold_price">
                                    <i class="fas fa-money-bill-wave text-success mr-1"></i>
                                    Harga Jual (Rp) <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg" id="sold_price" name="sold_price" 
                                    required onkeyup="formatCurrency(this)"
                                    placeholder="Masukkan harga jual">
                                <small class="form-text text-muted">
                                    Rekomendasi Jakarta: Rp {{ number_format($laptop->jakarta_price, 0, ',', '.') }}
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sold_at">
                                    <i class="fas fa-calendar-alt text-success mr-1"></i>
                                    Tanggal Penjualan <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control form-control-lg" id="sold_at" name="sold_at" 
                                    value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}"
                                    required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="fas fa-check-circle mr-2"></i> Tandai Sebagai Terjual
                        </button>
                    </div>
                </form>
                @endcanAccess
            </div>
        </div>
        @endif
    </div>

    <!-- Kolom Kanan: Foto, QR Code, dan Info Penjualan -->
    <div class="col-lg-4">
        <!-- Foto Laptop -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-camera text-primary mr-2"></i> Foto Laptop
                </h5>
            </div>
            <div class="card-body">
            @if($laptop->media->count() > 0)
                <!-- Main Carousel -->
                <div class="photo-carousel-container mb-4">
                    <div id="carouselPhotos" class="carousel slide" data-ride="false" data-interval="false">
                        <!-- Carousel Indicators (Custom) -->
                        <div class="carousel-indicators-custom">
                            @foreach($laptop->media->sortBy('order') as $index => $media)
                                <span class="indicator-dot {{ $loop->first ? 'active' : '' }}" 
                                    data-target="#carouselPhotos" 
                                    data-slide-to="{{ $loop->index }}">
                                </span>
                            @endforeach
                        </div>

                        <!-- Carousel Inner -->
                        <div class="carousel-inner rounded-lg shadow-lg">
                            @foreach($laptop->media->sortBy('order') as $media)
                            <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                <div class="carousel-image-wrapper">
                                    <img src="{{ s3_asset(true,10,$media->file_path) }}" 
                                        class="d-block w-100 carousel-main-img" 
                                        alt="Foto {{ $loop->iteration }}"
                                        data-index="{{ $loop->index }}"
                                        data-media-id="{{ $media->id }}"
                                        data-order="{{ $media->order }}">
                                    
                                    <!-- Photo Badge -->
                                    <div class="photo-number-badge">
                                        <i class="fas fa-camera mr-1"></i>
                                        {{ $loop->iteration }} / {{ $laptop->media->count() }}
                                    </div>
                                    
                                    <!-- Zoom Button -->
                                    <button class="btn-zoom-photo" data-image="{{ s3_asset(true,10,$media->file_path) }}">
                                        <i class="fas fa-search-plus"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Carousel Controls -->
                        <a class="carousel-control-prev" href="#carouselPhotos" role="button" data-slide="prev">
                            <div class="control-icon">
                                <i class="fas fa-chevron-left"></i>
                            </div>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#carouselPhotos" role="button" data-slide="next">
                            <div class="control-icon">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                            <span class="sr-only">Next</span>
                        </a>
                    </div>
                </div>
                
                <!-- Thumbnails Navigation -->
                <div class="thumbnails-container">
                    <label class="font-weight-bold mb-3">
                        <i class="fas fa-images mr-2 text-primary"></i>Galeri Foto
                    </label>
                    <div class="thumbnails-grid">
                        @foreach($laptop->media->sortBy('order') as $media)
                        <div class="thumbnail-item {{ $loop->first ? 'active' : '' }}" 
                            data-target="#carouselPhotos" 
                            data-slide-to="{{ $loop->index }}"
                            data-media-id="{{ $media->id }}">
                            <div class="thumbnail-wrapper">
                                <img src="{{ s3_asset(true,10,$media->file_path) }}" 
                                    class="thumbnail-img" 
                                    alt="Thumbnail {{ $loop->iteration }}">
                                <div class="thumbnail-overlay">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div class="thumbnail-badge">
                                    {{ $loop->iteration }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Lightbox Modal -->
                <div id="photoLightbox" class="photo-lightbox">
                    <span class="lightbox-close">&times;</span>
                    <div class="lightbox-content">
                        <img id="lightboxImage" src="" alt="Full size photo">
                        <div class="lightbox-caption">
                            <span id="lightboxCounter"></span>
                        </div>
                    </div>
                    <button class="lightbox-nav lightbox-prev">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="lightbox-nav lightbox-next">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>

            @else
                <!-- Empty State -->
                <div class="empty-photos-state">
                    <div class="empty-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <h5 class="text-muted mt-3">Tidak Ada Foto</h5>
                    <p class="text-muted mb-0">Belum ada foto yang diupload untuk laptop ini</p>
                </div>
            @endif
            </div>
        </div>

        <!-- QR Code -->
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-qrcode text-info mr-2"></i> QR Code Laptop
                </h5>
            </div>
            <div class="card-body text-center">
                <div id="qrcode" class="mb-3"></div>
                <p class="text-muted small mb-3">
                    Scan untuk melihat detail laptop di perangkat mobile
                </p>
                <!-- <a href="{{ s3_asset(true,10,$laptop->qr_code_path) }}" download class="btn btn-outline-primary btn-block">
                    <i class="fas fa-download mr-1"></i> Download QR Code
                </a> -->
                <a href="javascript:void(0);" class="btn btn-outline-primary btn-block btn-download-qrcode">
                    <i class="fas fa-download mr-1"></i> Download QR Code
                </a>
            </div>
        </div>

        <!-- Info Penjualan -->
        @if($laptop->is_sold == 1)
        <div class="card shadow-sm mt-4 border-success">
            <div class="card-header bg-gradient-success">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-check-circle mr-2"></i> Info Penjualan
                </h5>
            </div>
            <div class="card-body">
                <div class="sale-info-item mb-4">
                    <div class="sale-icon bg-success">
                        <i class="fas fa-money-bill-wave text-white fa-2x"></i>
                    </div>
                    <div class="sale-content">
                        <small class="text-muted d-block mb-1">Harga Terjual</small>
                        <h3 class="text-success font-weight-bold mb-0">
                            Rp {{ number_format($laptop->sold_price, 0, ',', '.') }}
                        </h3>
                    </div>
                </div>
                
                <div class="sale-info-item">
                    <div class="sale-icon bg-info">
                        <i class="fas fa-calendar-alt text-white fa-2x"></i>
                    </div>
                    <div class="sale-content">
                        <small class="text-muted d-block mb-1">Tanggal Terjual</small>
                        <h4 class="font-weight-bold mb-0">
                            {{ $laptop->sold_at->format('d F Y') }}
                        </h4>
                        <small class="text-muted">
                            {{ $laptop->sold_at->diffForHumans() }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Metadata -->
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle text-secondary mr-2"></i> Informasi Sistem
                </h5>
            </div>
            <div class="card-body">
                <div class="metadata-item">
                    <i class="fas fa-user text-muted mr-2"></i>
                    <span class="text-muted">Dibuat oleh:</span>
                    <strong>{{ $laptop->user->name ?? '-' }}</strong>
                </div>
                <div class="metadata-item">
                    <i class="fas fa-calendar-plus text-muted mr-2"></i>
                    <span class="text-muted">Dibuat pada:</span>
                    <strong>{{ $laptop->created_at->format('d F Y, H:i') }}</strong>
                </div>
                @if($laptop->updated_at != $laptop->created_at)
                <div class="metadata-item">
                    <i class="fas fa-calendar-check text-muted mr-2"></i>
                    <span class="text-muted">Terakhir diupdate:</span>
                    <strong>{{ $laptop->updated_at->format('d F Y, H:i') }}</strong>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.css" />
<style>
    /* Location Cards */
    .location-card {
        border-radius: 10px;
        padding: 20px;
        height: 100%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .location-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }

    .location-icon {
        margin-bottom: 15px;
        opacity: 0.9;
    }

    .location-content h5 {
        font-size: 1.1rem;
    }

    .opacity-75 {
        opacity: 0.75;
    }

    /* Spec Items */
    .spec-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 10px;
        background: #f8f9fa;
        height: 100%;
        transition: transform 0.2s;
    }

    .spec-item:hover {
        transform: translateX(5px);
        background: #e9ecef;
    }

    .spec-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        flex-shrink: 0;
    }

    /* Price Cards */
    .price-card {
        display: flex;
        align-items: center;
        padding: 20px;
        border-radius: 10px;
        border: 2px solid;
        background: white;
        height: 100%;
        transition: transform 0.2s;
    }

    .price-card:hover {
        transform: scale(1.02);
    }

    .price-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        flex-shrink: 0;
    }

    .price-icon i {
        font-size: 1.5rem;
    }

    /* Check Icon */
    .check-icon {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Sale Info */
    .sale-info-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-radius: 10px;
        background: #f8f9fa;
    }

    .sale-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        flex-shrink: 0;
    }

    /* Carousel */
    .carousel-img {
        height: 300px;
        object-fit: cover;
        border-radius: 8px;
    }

    .thumbnail-img {
        height: 80px;
        object-fit: cover;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .thumbnail-img:hover {
        transform: scale(1.05);
    }

    /* QR Code */
    #qrcode {
        display: flex;
        justify-content: center;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    #qrcode canvas {
        border: 1px solid #dee2e6;
        padding: 10px;
        background: white;
        border-radius: 8px;
    }

    /* Metadata */
    .metadata-item {
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .metadata-item:last-child {
        border-bottom: none;
    }

    /* Notes Content */
    .notes-content {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #ffc107;
    }

    /* Gradient Headers */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
    }

    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .location-card,
        .price-card,
        .spec-item {
            margin-bottom: 15px;
        }

        .carousel-img {
            height: 200px;
        }
    }
</style>
<style>
    /* ============================================ */
    /* PHOTO CAROUSEL STYLES */
    /* ============================================ */
    
    .photo-carousel-container {
        position: relative;
        max-width: 100%;
        margin: 0 auto;
    }
    
    .carousel-inner {
        border-radius: 12px;
        overflow: hidden;
    }
    
    .carousel-image-wrapper {
        position: relative;
        background: #000;
        height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .carousel-main-img {
        max-height: 500px;
        width: auto !important;
        object-fit: contain;
        cursor: pointer;
    }
    
    @media (max-width: 768px) {
        .carousel-image-wrapper {
            height: 300px;
        }
        .carousel-main-img {
            max-height: 300px;
        }
    }
    
    /* Photo Number Badge */
    .photo-number-badge {
        position: absolute;
        top: 20px;
        left: 20px;
        background: linear-gradient(135deg, rgba(0, 123, 255, 0.95) 0%, rgba(0, 86, 179, 0.95) 100%);
        color: white;
        padding: 8px 16px;
        border-radius: 25px;
        font-weight: bold;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        z-index: 10;
    }
    
    /* Zoom Button */
    .btn-zoom-photo {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.95);
        border: none;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        z-index: 10;
    }
    
    .btn-zoom-photo:hover {
        background: #007bff;
        color: white;
        transform: scale(1.1);
    }
    
    .btn-zoom-photo i {
        font-size: 18px;
    }
    
    /* Custom Carousel Indicators */
    .carousel-indicators-custom {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 8px;
        z-index: 10;
    }
    
    .indicator-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .indicator-dot.active {
        width: 30px;
        border-radius: 5px;
        background: white;
    }
    
    .indicator-dot:hover {
        background: rgba(255, 255, 255, 0.8);
    }
    
    /* Carousel Controls */
    .carousel-control-prev,
    .carousel-control-next {
        width: 60px;
        opacity: 1;
    }
    
    .control-icon {
        background: rgba(255, 255, 255, 0.9);
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    
    .control-icon i {
        color: #333;
        font-size: 20px;
    }
    
    .carousel-control-prev:hover .control-icon,
    .carousel-control-next:hover .control-icon {
        background: #007bff;
        transform: scale(1.1);
    }
    
    .carousel-control-prev:hover .control-icon i,
    .carousel-control-next:hover .control-icon i {
        color: white;
    }
    
    /* ============================================ */
    /* THUMBNAILS STYLES */
    /* ============================================ */
    
    .thumbnails-container {
        margin-top: 30px;
    }
    
    .thumbnails-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 15px;
        padding: 10px 0;
    }
    
    @media (max-width: 576px) {
        .thumbnails-grid {
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
        }
    }
    
    .thumbnail-item {
        cursor: pointer;
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 3px solid transparent;
    }
    
    .thumbnail-item.active {
        border-color: #007bff;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }
    
    .thumbnail-wrapper {
        position: relative;
        padding-bottom: 100%; /* 1:1 Aspect Ratio */
        overflow: hidden;
        background: #f8f9fa;
    }
    
    .thumbnail-img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .thumbnail-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 123, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .thumbnail-overlay i {
        color: white;
        font-size: 24px;
    }
    
    .thumbnail-item:hover .thumbnail-overlay {
        opacity: 1;
    }
    
    .thumbnail-item:hover .thumbnail-img {
        transform: scale(1.1);
    }
    
    .thumbnail-badge {
        position: absolute;
        top: 8px;
        right: 8px;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        z-index: 2;
    }
    
    /* ============================================ */
    /* LIGHTBOX MODAL STYLES */
    /* ============================================ */
    
    .photo-lightbox {
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
    
    .lightbox-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        max-width: 90%;
        max-height: 90%;
        text-align: center;
    }
    
    #lightboxImage {
        max-width: 100%;
        max-height: 85vh;
        object-fit: contain;
        animation: zoomIn 0.3s ease;
    }
    
    @keyframes zoomIn {
        from { transform: scale(0.8); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    
    .lightbox-close {
        position: absolute;
        top: 25px;
        right: 40px;
        color: white;
        font-size: 45px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 10001;
    }
    
    .lightbox-close:hover {
        color: #ff4444;
        transform: scale(1.2) rotate(90deg);
    }
    
    .lightbox-caption {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        font-size: 14px;
    }
    
    .lightbox-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.2);
        border: none;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        color: white;
        font-size: 24px;
        cursor: pointer;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    
    .lightbox-nav:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-50%) scale(1.1);
    }
    
    .lightbox-prev {
        left: 30px;
    }
    
    .lightbox-next {
        right: 30px;
    }
    
    /* ============================================ */
    /* EMPTY STATE STYLES */
    /* ============================================ */
    
    .empty-photos-state {
        text-align: center;
        padding: 80px 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        border: 2px dashed #dee2e6;
    }
    
    .empty-icon {
        display: inline-block;
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .empty-icon i {
        font-size: 60px;
        color: #adb5bd;
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // Format angka ke format mata uang Indonesia
    function formatCurrency(input) {
        let value = input.value.replace(/[^\d]/g, '');
        input.dataset.rawValue = value;
        
        if (value.length > 0) {
            value = parseInt(value, 10).toLocaleString('id-ID');
        }
        
        input.value = value;
    }

    // Konversi format mata uang ke angka murni sebelum submit
    const saleForm = document.getElementById('sale-form');
    if (saleForm) {
        saleForm.addEventListener('submit', function(e) {
            const soldPrice = document.getElementById('sold_price');
            if (soldPrice && soldPrice.dataset.rawValue) {
                soldPrice.value = soldPrice.dataset.rawValue;
            }
            return true;
        });
    }

    // Generate QR Code
    document.addEventListener('DOMContentLoaded', function() {
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ route('used-laptop.show-qr', $laptop->slug) }}",
            width: 200,
            height: 200,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    });

    document.querySelector('.btn-download-qrcode').addEventListener('click', function () {
        const qrCanvas = document.querySelector('#qrcode canvas');
        if (!qrCanvas) {
            alert('QR Code belum tersedia.');
            return;
        }

        const imageData = qrCanvas.toDataURL("image/png");
        const downloadLink = document.createElement("a");
        downloadLink.href = imageData;
        downloadLink.download = "qr-code-{{ $laptop->slug }}.png";
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    });
</script>
@stop