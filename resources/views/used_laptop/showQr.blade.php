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
                        <h3 class="text-primary">{{ $laptop->name }}</h3>
                        <p class="text-muted">Serial Number: {{ $laptop->serial_number }}</p>           
                        <div>
                            <span class="badge {{ $laptop->is_sold ? 'badge-success' : 'badge-secondary' }} p-2">
                                {{ $laptop->is_sold ? 'Terjual' : 'Belum Terjual' }}
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

                            @auth
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
                            @endauth
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
                                    @if($check->status)
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
                     @Auth
                    @canAccess('maskAsSold','used_items')
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
                    @endcanAccess
                    @endauth
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
                    @auth
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
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- QR Code Generator -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

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