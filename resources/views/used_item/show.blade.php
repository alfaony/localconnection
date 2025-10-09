@extends('adminlte::page')

@section('title', 'Detail Barang')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">
            <i class="fas fa-box mr-2"></i> Detail Barang
        </h1>
        <div>
            <a href="{{ route('used-item.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            @if(!$usedItem->is_sold)
            @canAccess('update','used_laptops')
            <a href="{{ route('used-item.edit', $usedItem->slug) }}" class="btn btn-primary ml-2">
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
                        <h3 class="text-primary mb-2">{{ $usedItem->name }}</h3>
                        @if($usedItem->brand)
                            <p class="text-muted mb-0">
                                <i class="fas fa-tag mr-1"></i> {{ $usedItem->brand }}
                            </p>
                        @endif
                    </div>
                    <div>
                        <span class="badge badge-{{ $usedItem->is_sold ? 'success' : 'warning' }} badge-pill px-3 py-2" style="font-size: 1rem;">
                            <i class="fas fa-{{ $usedItem->is_sold ? 'check-circle' : 'clock' }} mr-1"></i>
                            {{ $usedItem->sale_status }}
                        </span>
                    </div>
                </div>

                <!-- Serial Number -->
                <div class="alert alert-light border mb-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-barcode fa-2x text-primary mr-3"></i>
                        <div>
                            <small class="text-muted d-block">Serial Number</small>
                            <code class="h5 mb-0 bg-white px-2 py-1 rounded">{{ $usedItem->serial_number }}</code>
                        </div>
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
                @if($usedItem->rack)
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
                                        {{ $usedItem->rack->zone->warehouse->name }}
                                    </h5>
                                    @if($usedItem->rack->zone->warehouse->address)
                                        <small class="d-block opacity-75 mt-1">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            {{ Str::limit($usedItem->rack->zone->warehouse->address, 30) }}
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
                                        {{ $usedItem->rack->zone->name }}
                                    </h5>
                                    @if($usedItem->rack->zone->code)
                                        <small class="d-block opacity-75 mt-1">
                                            <i class="fas fa-code mr-1"></i> {{ $usedItem->rack->zone->code }}
                                        </small>
                                    @endif
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
                                        {{ $usedItem->rack->name }}
                                    </h5>
                                    {{--
                                    <small class="d-block opacity-75 mt-1">
                                        <i class="fas fa-code mr-1"></i> {{ $usedItem->rack->code }}
                                        @if($usedItem->rack->capacity)
                                            <span class="ml-2">
                                                <i class="fas fa-box mr-1"></i> Kapasitas: {{ $usedItem->rack->capacity }}
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
                                {{ $usedItem->rack->zone->warehouse->name }}
                            </span>
                            <i class="fas fa-chevron-right text-muted mr-2 mb-2"></i>
                            <span class="badge badge-info px-3 py-2 mr-2 mb-2">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                {{ $usedItem->rack->zone->name }}
                            </span>
                            <i class="fas fa-chevron-right text-muted mr-2 mb-2"></i>
                            <span class="badge badge-secondary px-3 py-2 mb-2">
                                <i class="fas fa-th mr-1"></i>
                                {{ $usedItem->rack->name }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                            <div>
                                <h6 class="mb-1">Lokasi Belum Ditentukan</h6>
                                <p class="mb-0 small">Barang ini belum memiliki lokasi penyimpanan. Silakan edit untuk menambahkan lokasi.</p>
                            </div>
                        </div>
                    </div>
                @endif
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
                    <div class="col-md-6 mb-3">
                        <div class="price-card border-danger">
                            <div class="price-icon bg-danger">
                                <i class="fas fa-shopping-cart text-white"></i>
                            </div>
                            <div class="price-content">
                                <small class="text-muted d-block mb-1">Harga Beli</small>
                                <h4 class="mb-0 font-weight-bold text-danger">
                                    Rp {{ number_format($usedItem->purchase_price, 0, ',', '.') }}
                                </h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="price-card border-success">
                            <div class="price-icon bg-success">
                                <i class="fas fa-tag text-white"></i>
                            </div>
                            <div class="price-content">
                                <small class="text-muted d-block mb-1">Harga Jual Disarankan</small>
                                <h4 class="mb-0 font-weight-bold text-success">
                                    Rp {{ number_format($usedItem->suggested_selling_price, 0, ',', '.') }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catatan -->
        @if($usedItem->notes)
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-sticky-note text-warning mr-2"></i> Catatan
                </h5>
            </div>
            <div class="card-body">
                <div class="notes-content">
                    {!! $usedItem->notes !!}
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
                            @forelse($usedItem->checks->where('status', '!=', null) as $check)
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
                                <td>
                                    {{ $check->notes ?? '-' }}
                                </td>
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
                @if($usedItem->repairs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="70%">Deskripsi Kerusakan</th>
                                <th class="text-right">Biaya Perbaikan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usedItem->repairs as $repair)
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
                                        Rp {{ number_format($usedItem->repairs->sum('cost'), 0, ',', '.') }}
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
        @if(!$usedItem->is_sold)
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-gradient-success">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-money-bill-wave mr-2"></i> Input Penjualan Barang
                </h5>
            </div>
            <div class="card-body">
                @canAccess('maskAsSold','used_items')
                <form action="{{ route('used-item.mark-as-sold', $usedItem->slug) }}" method="POST" id="sale-form">
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
                                    Rekomendasi: Rp {{ number_format($usedItem->suggested_selling_price, 0, ',', '.') }}
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
        <!-- Foto Barang -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-camera text-primary mr-2"></i> Foto Barang
                </h5>
            </div>
            <div class="card-body">
                @if($usedItem->media->count() > 0)
                    <div id="carouselPhotos" class="carousel slide mb-3" data-ride="carousel">
                        <ol class="carousel-indicators">
                            @foreach($usedItem->media as $index => $media)
                                <li data-target="#carouselPhotos" data-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}"></li>
                            @endforeach
                        </ol>
                        <div class="carousel-inner">
                            @foreach($usedItem->media as $index => $media)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <a href="{{ Storage::url($media->file_path) }}" target="_blank">
                                    <img src="{{ Storage::url($media->file_path) }}" class="d-block w-100 carousel-img" alt="Foto {{ $index + 1 }}">
                                </a>
                            </div>
                            @endforeach
                        </div>
                        <a class="carousel-control-prev" href="#carouselPhotos" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#carouselPhotos" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    </div>
                    
                    <!-- Thumbnails -->
                    <div class="row">
                        @foreach($usedItem->media as $index => $media)
                        <div class="col-4 mb-2">
                            <a href="{{ Storage::url($media->file_path) }}" target="_blank">
                                <img src="{{ Storage::url($media->file_path) }}" class="img-thumbnail thumbnail-img">
                            </a>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-image fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Tidak ada foto tersedia</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- QR Code -->
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-qrcode text-info mr-2"></i> QR Code Barang
                </h5>
            </div>
            <div class="card-body text-center">
                <div id="qrcode" class="mb-3"></div>
                <p class="text-muted small mb-3">
                    Scan untuk melihat detail barang di perangkat mobile
                </p>
                <a href="{{ Storage::url($usedItem->qr_code_path) }}" download class="btn btn-outline-primary btn-block">
                    <i class="fas fa-download mr-1"></i> Download QR Code
                </a>
            </div>
        </div>

        <!-- Info Penjualan -->
        @if($usedItem->is_sold)
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
                            Rp {{ number_format($usedItem->sold_price, 0, ',', '.') }}
                        </h3>
                        @php
                            $actualProfit = $usedItem->sold_price - $usedItem->purchase_price;
                            $actualProfitPercent = $usedItem->purchase_price > 0 ? ($actualProfit / $usedItem->purchase_price) * 100 : 0;
                        @endphp
                        <small class="text-success">
                            <i class="fas fa-arrow-up mr-1"></i>
                            Profit Aktual: +{{ number_format($actualProfitPercent, 1) }}%
                        </small>
                    </div>
                </div>
                
                <div class="sale-info-item">
                    <div class="sale-icon bg-info">
                        <i class="fas fa-calendar-alt text-white fa-2x"></i>
                    </div>
                    <div class="sale-content">
                        <small class="text-muted d-block mb-1">Tanggal Terjual</small>
                        <h4 class="font-weight-bold mb-0">
                            {{ $usedItem->sold_at->format('d F Y') }}
                        </h4>
                        <small class="text-muted">
                            {{ $usedItem->sold_at->diffForHumans() }}
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
                    <strong>{{ $usedItem->user->name }}</strong>
                </div>
                <div class="metadata-item">
                    <i class="fas fa-calendar-plus text-muted mr-2"></i>
                    <span class="text-muted">Dibuat pada:</span>
                    <strong>{{ $usedItem->created_at->format('d F Y, H:i') }}</strong>
                </div>
                @if($usedItem->updated_at != $usedItem->created_at)
                <div class="metadata-item">
                    <i class="fas fa-calendar-check text-muted mr-2"></i>
                    <span class="text-muted">Terakhir diupdate:</span>
                    <strong>{{ $usedItem->updated_at->format('d F Y, H:i') }}</strong>
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
        .price-card {
            margin-bottom: 15px;
        }

        .carousel-img {
            height: 200px;
        }
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
    document.getElementById('sale-form')?.addEventListener('submit', function(e) {
        const soldPrice = document.getElementById('sold_price');
        if (soldPrice && soldPrice.dataset.rawValue) {
            soldPrice.value = soldPrice.dataset.rawValue;
        }
        return true;
    });

    // Generate QR Code
    document.addEventListener('DOMContentLoaded', function() {
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ route('used-item.show-qr', $usedItem->slug) }}",
            width: 200,
            height: 200,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    });
</script>
@stop