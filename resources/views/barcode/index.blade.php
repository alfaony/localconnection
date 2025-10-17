@extends('adminlte::page')

@section('title', 'Scan Absensi - Office Attendance')

@section('content')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">Scan Absensi</li>
    </ol>
</nav>

<!-- WFO Rules Information Card -->
@if(auth()->user()->wfoRules)
<div class="row mt-4 justify-content-center">
    <div class="col-md-8">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Informasi Aturan WFO Anda
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="info-box bg-gradient-primary">
                            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Jam Masuk</span>
                                <span class="info-box-number">{{ auth()->user()->wfoRules->entry_time_checkin->format('H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="info-box bg-gradient-info">
                            <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Minimal Check-in</span>
                                <span class="info-box-number">{{ auth()->user()->wfoRules->times_checkin_in_day }}x / Hari</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 col-sm-6 mb-3">
                        <div class="info-box bg-gradient-success">
                            <span class="info-box-icon"><i class="fas fa-star"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Poin</span>
                                <span class="info-box-number">{{ auth()->user()->wfoRules->point_checkin_in_day }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row mt-2 justify-content-center">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-qrcode mr-2"></i>
                    Scan untuk Absensi
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body text-center">
                <div class="instruction-box text-left">
                    <h5><i class="fas fa-info-circle mr-2"></i>Petunjuk Penggunaan:</h5>
                    <ol class="pl-3 mb-0">
                        <li>Pastikan Anda sudah login di akun BOS</li>
                        <li>Buka aplikasi kamera atau pemindai QR code di ponsel Anda</li>
                        <li>Arahkan kamera ke QR code di bawah ini</li>
                        <li>Tunggu hingga notifikasi absensi berhasil muncul</li>
                        <li>Setelah notifikasi muncul, Anda akan diminta untuk melakukan foto lokasi, selesai</li>
                    </ol>
                    @canAccess('generate','barcodes')
                    <button id="generate-barcode-btn" class="btn btn-primary mt-4">
                        <i class="fas fa-sync-alt mr-1"></i> Generate
                    </button>
                    @endcanAccess
                </div>
                {{ $barcode->code }}

                <div class="qr-container pulse mb-4">
                    {!! QrCode::size(250)->generate(route('office-attendance.scan', ['code' => $barcode->code])) !!}
                </div>

                <div id="barcode-status" class="status-indicator bg-light">
                    <i class="fas fa-sync-alt fa-spin mr-2"></i>
                    <span>Menunggu scan untuk absensi...</span>
                </div>

                <div class="mt-4">
                    <p class="text-muted mb-0">
                        <small>
                            <i class="fas fa-clock mr-1"></i> 
                            QR code diperbarui setiap menit untuk keamanan
                        </small>
                    </p>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-6">
                        <small class="text-muted">
                            <i class="fas fa-laptop mr-1"></i> 
                            Dibuat pada: {{ $barcode->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                    <div class="col-sm-6 text-right">
                        <small class="text-muted">
                            <i class="fas fa-refresh mr-1"></i> 
                            Akan diperbarui otomatis
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <audio id="notification-message-entry" src="/audio/notification-message-email.mp3" preload="auto"></audio>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
    <script>
        const companyId = @json(auth()->user()->id);

        host = '{{ config('services.connection_reverb.host')}}';
        key = '{{ config('services.connection_reverb.key')}}';
        port = '{{ config('services.connection_reverb.port')}}';  
        notifSoundEntry = document.getElementById('notification-message-entry');

        window.Pusher = Pusher;

        window.Echo = new Echo.default({
            broadcaster: 'reverb',
            key: key,
            wsHost: host,
            wsPort: 8080,
            wssPort: port,
            forceTLS: true,
            enabledTransports: ['ws', 'wss'],
            authEndpoint: '/broadcasting/authorize',
            disableStats: true,
        });

        window.Echo.private(`office.barcode.${companyId}`)
            .listen('NewBarcodeGenerated', (e) => {
                const statusElement = document.getElementById('barcode-status');
                statusElement.innerHTML = '<i class="fas fa-check-circle mr-2 text-success"></i><span class="text-success">QR code berhasil diperbarui! Memuat ulang...</span>';
                statusElement.classList.remove('bg-light');
                statusElement.classList.add('bg-success-light');

                notifSoundEntry?.play();
                
                setTimeout(() => {
                    location.reload();
                }, 1500);
            });

        $('#generate-barcode-btn').click(function() {
            $.ajax({
                url: "{{ route('barcode.generate') }}",
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function () {
                    $('#generate-barcode-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menghasilkan...');
                },
                success: function(response) {
                    console.log('Generate request sent');
                },
                complete: function() 
                {
                    $('#generate-barcode-btn').prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Generate');
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
                        icon: 'success',
                        title: 'Generate request sent'
                    });
                },
                error: function(xhr) {
                    alert('Gagal generate barcode. Silakan coba lagi.');
                }
            });
        });
            
        setInterval(() => {
            const qrContainer = document.querySelector('.qr-container');
            qrContainer.classList.toggle('pulse');
        }, 5000);
    </script>
@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .qr-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin: 0 auto;
            max-width: 400px;
        }
        .status-indicator {
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
            font-weight: 500;
        }
        .instruction-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); }
            100% { transform: scale(1); }
        }
        .info-box {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: .25rem;
            display: flex;
            margin-bottom: 1rem;
            min-height: 80px;
            padding: .5rem;
            position: relative;
        }
        .info-box .info-box-icon {
            border-radius: .25rem;
            align-items: center;
            display: flex;
            font-size: 1.875rem;
            justify-content: center;
            text-align: center;
            width: 70px;
        }
        .info-box .info-box-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.8;
            flex: 1;
            padding: 0 10px;
        }
        .info-box .info-box-number {
            display: block;
            font-weight: 700;
            font-size: 1.2rem;
        }
        .info-box .info-box-text,
        .info-box .progress-description {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .bg-gradient-primary {
            background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%) !important;
            color: #fff;
        }
        .bg-gradient-warning {
            background: linear-gradient(87deg, #fb6340 0, #fbb140 100%) !important;
            color: #fff;
        }
        .bg-gradient-info {
            background: linear-gradient(87deg, #11cdef 0, #1171ef 100%) !important;
            color: #fff;
        }
        .bg-gradient-success {
            background: linear-gradient(87deg, #2dce89 0, #2dcecc 100%) !important;
            color: #fff;
        }
    </style>
@endsection