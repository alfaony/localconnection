<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Laptop Bekas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 4 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <style>
        .info-item {
            padding: 10px 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
            border-left: 3px solid #007bff;
            margin-bottom: 15px;
        }

        .icon-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background-color: #007bff;
            color: white;
            margin-right: 15px;
        }

        #qrcode canvas {
            margin: 0 auto;
            padding: 10px;
            background: white;
            border: 1px solid #ccc;
        }

        .img-thumbnail {
            height: 150px;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid mt-4">
        @auth
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="{{ route('home') }}">Laptop Bekas</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('used-laptop.index') }}">Daftar Laptop</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('used-laptop.create') }}">Tambah Laptop</a>
                    </li>
                </ul>
            </div>
        </nav>
        @endauth
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <!-- Kolom Kiri: Detail Utama -->
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="text-primary">{{ $usedItem->name }}</h3>
                            <div>
                                <span class="badge {{ $usedItem->is_sold ? 'badge-success' : 'badge-secondary' }} p-2">
                                    {{ $usedItem->is_sold ? 'Terjual' : 'Belum Terjual' }}
                                </span>
                            </div>
                        </div>

                        @if($usedItem->notes)
                        <div class="mt-4">
                            <h5 class="text-primary">
                                <i class="fas fa-sticky-note mr-2"></i> Catatan
                            </h5>
                            <div class="alert alert-light border">
                                <p>{!! $usedItem->notes !!}</p>
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
                                        @foreach($usedItem->checks as $check)
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
                         @auth
                        <div class="mt-5">
                            <h5 class="text-primary">
                                <i class="fas fa-tools mr-2"></i> Kerusakan dan Perbaikan
                            </h5>
                            @if($usedItem->repairs->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th width="70%">Deskripsi Kerusakan</th>
                                            <th>Biaya Perbaikan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($usedItem->repairs as $repair)
                                        <tr>
                                            <td>{{ $repair->repair_item }}</td>
                                            <td class="text-danger">Rp {{ number_format($repair->cost) }}</td>
                                        </tr>
                                        @endforeach
                                        <tr class="table-warning">
                                            <td class="text-right font-weight-bold">Total Biaya Perbaikan:</td>
                                            <td class="font-weight-bold text-danger">Rp {{ number_format($usedItem->repairs->sum('cost')) }}</td>
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
                                        Rp {{ number_format($usedItem->suggested_selling_price) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endauth
                    </div>

                    <!-- Kolom Kanan: Foto dan QR Code -->
                    <div class="col-md-4">
                        <!-- Foto Laptop -->
                        <div class="mb-5">
                            <h5 class="text-primary">
                                <i class="fas fa-camera mr-2"></i> Foto Laptop
                            </h5>
                            @if($usedItem->media->count() > 0)
                                <div class="row">
                                    @foreach($usedItem->media as $media)
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
                                    <a href="{{ Storage::url($usedItem->qr_code_path) }}" download class="btn btn-sm btn-outline-primary mt-2">
                                        <i class="fas fa-download mr-1"></i> Download QR Code
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Info Penjualan -->
                        @auth
                        @if($usedItem->is_sold)
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
                                                Rp {{ number_format($usedItem->sold_price) }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-alt fa-2x text-success mr-3"></i>
                                        <div>
                                            <div class="font-weight-bold">Tanggal Terjual</div>
                                            <div class="h4">
                                                {{ $usedItem->sold_at->format('d F Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endauth
                    </div>
                </div>
            </div>
            <!-- Form Input Harga Jual (hanya jika belum terjual) -->
             @auth
            @if(!$usedItem->is_sold)
            <div class="card mt-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-money-bill-wave mr-2"></i> Input Penjualan Laptop
                    </h5>
                </div>
                <div class="card-body">
                    @canAccess('maskAsSold','used_laptops')
                    <form action="{{ route('used-laptop.mark-as-sold', $usedItem->slug) }}" method="POST" id="sale-form">
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
                                    Rp {{ number_format($usedItem->suggested_selling_price) }}
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
            @endauth
        </div>
    </div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- QR Code Generator -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

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
    document.addEventListener("DOMContentLoaded", function() {
        new QRCode(document.getElementById("qrcode"), {
            text: window.location.href, // atau bisa diganti dengan route detail laptop
            width: 180,
            height: 180,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    });
</script>
</body>
</html>