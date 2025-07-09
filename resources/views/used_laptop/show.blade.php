@extends('adminlte::page')

@section('title', 'Detail Laptop Bekas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Detail Laptop Bekas</h1>
        <div>
            <a href="{{ route('used-laptop.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            @if(!$laptop->is_sold)
            @canAccess('update','used_laptops')
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
<div class="card">
    <div class="card-body">
        <div class="row">
            <!-- Kolom Kiri: Detail Utama -->
            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="text-primary">{{ $laptop->name }}</h3>
                    <div>
                        <span class="badge {{ $laptop->is_sold ? 'badge-success' : 'badge-secondary' }} p-2">
                            {{ $laptop->sale_status }}
                        </span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-primary mr-3">
                                    <i class="fas fa-microchip text-white"></i>
                                </div>
                                <div>
                                    <label class="text-muted">Processor</label>
                                    <p class="font-weight-bold">{{ $laptop->processor }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="info-item mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-primary mr-3">
                                    <i class="fas fa-memory text-white"></i>
                                </div>
                                <div>
                                    <label class="text-muted">RAM</label>
                                    <p class="font-weight-bold">{{ $laptop->ram }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="info-item mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-primary mr-3">
                                    <i class="fas fa-hdd text-white"></i>
                                </div>
                                <div>
                                    <label class="text-muted">SSD</label>
                                    <p class="font-weight-bold">{{ $laptop->ssd }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-item mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-primary mr-3">
                                    <i class="fas fa-gamepad text-white"></i>
                                </div>
                                <div>
                                    <label class="text-muted">GPU</label>
                                    <p class="font-weight-bold">{{ $laptop->gpu ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="info-item mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-primary mr-3">
                                    <i class="fas fa-window-restore text-white"></i>
                                </div>
                                <div>
                                    <label class="text-muted">Sistem Operasi</label>
                                    <p class="font-weight-bold">{{ $laptop->operating_system ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-item mb-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle bg-primary mr-3">
                                    <i class="fas fa-money-bill-wave text-white"></i>
                                </div>
                                <div>
                                    <label class="text-muted">Harga Beli</label>
                                    <p class="font-weight-bold">Rp {{ number_format($laptop->purchase_price) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($laptop->notes)
                <div class="mt-4">
                    <h5 class="text-primary">
                        <i class="fas fa-sticky-note mr-2"></i> Catatan
                    </h5>
                    <div class="alert alert-light border">
                        <p>{!! $laptop->notes !!}</p>
                    </div>
                </div>
                @endif

                <!-- Kondisi -->
                <div class="mt-5">
                    <h5 class="text-primary">
                        <i class="fas fa-clipboard-check mr-2"></i> Kondisi
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th width="60%">Item Pemeriksaan</th>
                                    <th>Kondisi</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($laptop->checks as $check)
                                @if($check->status && $check->notes)
                                <tr>
                                    <td>
                                        <strong>{{ $check->item->name }}</strong>
                                        <div class="text-muted small">{{ $check->item->description }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $check->status == 'good' ? 'badge-success' : 'badge-danger' }}">
                                            {{ $check->status == 'good' ? 'Baik' : 'Rusak' }}
                                        </span>
                                    </td>
                                    <td>{{ $check->notes ?? '-' }}</td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Kerusakan dan Perbaikan -->
                <div class="mt-5">
                    <h5 class="text-primary">
                        <i class="fas fa-tools mr-2"></i> Kerusakan dan Perbaikan
                    </h5>
                    @if($laptop->repairs->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th width="70%">Deskripsi Kerusakan</th>
                                    <th>Biaya Perbaikan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($laptop->repairs as $repair)
                                <tr>
                                    <td>{{ $repair->repair_item }}</td>
                                    <td class="text-danger">Rp {{ number_format($repair->cost) }}</td>
                                </tr>
                                @endforeach
                                <tr class="table-warning">
                                    <td class="text-right font-weight-bold">Total Biaya Perbaikan:</td>
                                    <td class="font-weight-bold text-danger">Rp {{ number_format($laptop->repairs->sum('cost')) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i> Tidak ada kerusakan yang dicatat
                    </div>
                    @endif
                </div>

                <!-- Harga Jual Disarankan -->
                <div class="mt-5">
                    <div class="alert bg-light border">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="text-primary mb-1">Harga Jual Disarankan</h5>
                            </div>
                            <div class="h5 text-success font-weight-bold">
                                Rp {{ number_format($laptop->suggested_selling_price) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Foto dan QR Code -->
            <div class="col-md-4">
                <!-- Foto Laptop -->
                <div class="mb-5">
                    <h5 class="text-primary">
                        <i class="fas fa-camera mr-2"></i> Foto Laptop
                    </h5>
                    @if($laptop->media->count() > 0)
                        <div class="row">
                            @foreach($laptop->media as $media)
                            <div class="col-md-6 mb-3">
                                <a href="{{ Storage::url($media->file_path) }}" target="_blank">
                                    <img src="{{ Storage::url($media->file_path) }}" class="img-fluid img-thumbnail">
                                </a>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i> Tidak ada foto tersedia
                        </div>
                    @endif
                </div>

                <!-- QR Code -->
                <div class="mt-5">
                    <h5 class="text-primary">
                        <i class="fas fa-qrcode mr-2"></i> QR Code Laptop
                    </h5>
                    <div class="card border">
                        <div class="card-body text-center">
                            <div id="qrcode" class="mb-3"></div>
                            <p class="text-muted small mb-0">
                                Scan untuk melihat detail laptop di perangkat mobile
                            </p>
                            <a href="{{ Storage::url($laptop->qr_code_path) }}" download class="btn btn-sm btn-outline-primary mt-2">
                                <i class="fas fa-download mr-1"></i> Download QR Code
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Info Penjualan -->
                @if($laptop->is_sold)
                <div class="mt-5">
                    <h5 class="text-primary">
                        <i class="fas fa-check-circle mr-2"></i> Info Penjualan
                    </h5>
                    <div class="card border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-money-bill-wave fa-2x text-success mr-3"></i>
                                <div>
                                    <div class="font-weight-bold">Harga Terjual</div>
                                    <div class="h5 text-success font-weight-bold">
                                        Rp {{ number_format($laptop->sold_price) }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-alt fa-2x text-success mr-3"></i>
                                <div>
                                    <div class="font-weight-bold">Tanggal Terjual</div>
                                    <div class="h4">
                                        {{ $laptop->sold_at->format('d F Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    <!-- Form Input Harga Jual (hanya jika belum terjual) -->
    @if(!$laptop->is_sold)
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-money-bill-wave mr-2"></i> Input Penjualan Laptop
            </h5>
        </div>
        <div class="card-body">
            @canAccess('maskAsSold','used_laptops')
            <form action="{{ route('used-laptop.mark-as-sold', $laptop->slug) }}" method="POST" id="sale-form">
                @csrf
                @method('PATCH')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sold_price">Harga Jual (Rp) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sold_price" name="sold_price" 
                                required onkeyup="formatCurrency(this)"
                                placeholder="Masukkan harga jual">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sold_at">Tanggal Penjualan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="sold_at" name="sold_at" 
                                value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}"
                                required>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div>
                        <h5 class="text-success">Rekomendasi Harga Jual</h5>
                        <div class="h3 text-success font-weight-bold">
                            Rp {{ number_format($laptop->suggested_selling_price) }}
                        </div>
                        <small class="text-muted">(Harga beli + perbaikan) + 30%</small>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle mr-1"></i> Tandai Sebagai Terjual
                    </button>
                </div>
            </form>
            @endcanAccess
        </div>
    </div>
    @endif
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.css" />
<style>
    .info-item {
        padding: 10px 15px;
        border-radius: 8px;
        background-color: #f8f9fa;
        border-left: 3px solid #007bff;
    }
    
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    #qrcode canvas {
        margin: 0 auto;
        border: 1px solid #eee;
        padding: 10px;
        background: white;
    }
    
    .img-thumbnail {
        height: 150px;
        object-fit: cover;
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // Format angka ke format mata uang Indonesia
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
    document.getElementById('sale-form').addEventListener('submit', function(e) {
        const soldPrice = document.getElementById('sold_price');
        if (soldPrice && soldPrice.dataset.rawValue) {
            soldPrice.value = soldPrice.dataset.rawValue;
        }
        return true;
    });
</script>
<script>
    // Generate QR Code
    document.addEventListener('DOMContentLoaded', function() {
        // URL untuk QR code (detail laptop)
        const url = "{{ route('used-laptop.show', $laptop->id) }}";
        
        // Buat QR code
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ route('used-laptop.show-qr', $laptop->slug) }}",
            width: 200,
            height: 200,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    });

    // Fungsi download QR Code
    function downloadQRCode() {
        const link = document.createElement('a');
        
        link.download = 'qr-code-laptop-' + "{{ basename(Storage::url($laptop->qr_code_path)) }}";
        link.href = "{{ Storage::url($laptop->qr_code_path) }}";
        link.click();
    }
</script>
@stop