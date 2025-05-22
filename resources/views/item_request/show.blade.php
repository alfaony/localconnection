@extends('adminlte::page')

@section('title', 'Detail Permintaan Barang')

@section('content')
@php
    $steps = [
        [
            'title' => 'Pengajuan Barang',
            'icon' => 'fas fa-file-signature',
            'status' => 'completed',
            'description' => 'Permintaan awal pengadaan barang',
            'date' => now()->subDays(3)->format('Y-m-d H:i'),
            'data' => [
                'requester' => 'John Doe',
                'department' => 'IT Department',
                'notes' => 'Permintaan diajukan melalui sistem'
            ]
        ],
        [
            'title' => 'Penunjukan PIC',
            'icon' => 'fas fa-user-tie',
            'status' => 'active',
            'description' => 'Penunjukan Penanggung Jawab Procurement',
            'date' => now()->subDays(2)->format('Y-m-d H:i'),
            'data' => [
                'assigned_pic' => 'Budi Santoso',
                'assignment_method' => 'Beban kerja terkecil',
                'current_workload' => '4 tugas aktif',
                'contact' => 'budi@santoso.com'
            ]
        ],
        [
            'title' => 'Pencarian Vendor',
            'icon' => 'fas fa-store',
            'status' => 'pending',
            'description' => 'Identifikasi dan negoisasi dengan vendor',
            'data' => [
                'vendors' => [
                    [
                        'name' => 'Toko Elektronik Maju',
                        'response' => 'positive',
                        'message' => 'Stok tersedia, harga Rp35.000.000',
                        'response_time' => '2 jam'
                    ],
                    [
                        'name' => 'Global Computer',
                        'response' => 'negative',
                        'message' => 'Stok kosong',
                        'response_time' => '4 jam'
                    ]
                ],
                'broadcast_status' => [
                    'sent' => true,
                    'total_vendors' => 8,
                    'responses' => 5
                ]
            ]
        ],
        [
            'title' => 'Konfirmasi Pembayaran',
            'icon' => 'fas fa-coins',
            'status' => 'pending',
            'description' => 'Verifikasi dan konfirmasi pembayaran',
            'data' => [
                'finance_contact' => 'finance@company.com',
                'payment_method' => 'Transfer Bank',
                'due_date' => now()->addDays(3)->format('Y-m-d')
            ]
        ],
        [
            'title' => 'Pengiriman Barang',
            'icon' => 'fas fa-truck',
            'status' => 'pending',
            'description' => 'Proses pengiriman ke gudang',
            'data' => [
                'shipping_methods' => ['JNE', 'SiCepat', 'DHL'],
                'tracking_info' => null
            ]
        ]
    ];
@endphp
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-white shadow-sm">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-info"><i class="fas fa-home"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('item-request.index') }}" class="text-info">Permintaan Barang</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $itemRequest->item_name }}</li>
        </ol>
    </nav>
    
    @include('components.alert')

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Item Details Card -->
            <div class="card card-primary card-outline">
                <div class="card-header bg-gradient-primary d-flex align-items-center">
                    <h3 class="card-title text-white flex-grow-1">
                        <i class="fas fa-box-open mr-2"></i>{{ $itemRequest->item_name }}
                    </h3>
                    <span class="badge badge-light">ID: #{{ $itemRequest->id }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card img-hover-zoom">
                                <img src="{{ Storage::url($itemRequest->picture) }}" class="card-img-top" 
                                    alt="Item Image" style="height: 200px; object-fit: cover;">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <dl class="row">
                                <dt class="col-sm-4 text-info"><i class="fas fa-tag mr-2"></i>Kategori</dt>
                                <dd class="col-sm-8">{{ $itemRequest->category->name ?? 'N/A' }}</dd>

                                <dt class="col-sm-4 text-info"><i class="fas fa-align-left mr-2"></i>Deskripsi</dt>
                                <dd class="col-sm-8">{!! $itemRequest->description !!}</dd>

                                <dt class="col-sm-4 text-info"><i class="fas fa-coins mr-2"></i>Estimasi Harga</dt>
                                <dd class="col-sm-8">Rp{{ number_format($itemRequest->estimated_price) }}</dd>

                                <dt class="col-sm-4 text-info"><i class="fas fa-cubes mr-2"></i>Kuantitas</dt>
                                <dd class="col-sm-8">{{ $itemRequest->qty }} Unit</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workflow Timeline Card -->
            <div class="card card-info card-outline mt-4">
                <div class="card-header bg-gradient-info">
                    <h3 class="card-title text-white"><i class="fas fa-project-diagram mr-2"></i>Alur Proses Pengadaan</h3>
                </div>
                <div class="card-body pt-4">
                    <div class="workflow-timeline">
                        @foreach($steps as $step)
                        <div class="workflow-step {{ $step['status'] }} animated fadeIn">
                            <div class="step-icon shadow-sm">
                                <i class="{{ $step['icon'] }}"></i>
                            </div>
                            <div class="step-content shadow-sm">
                                <div class="step-header border-bottom pb-2">
                                    <h5 class="mb-0">{{ $step['title'] }}</h5>
                                    <span class="badge badge-{{ 
                                        $step['status'] == 'completed' ? 'success' : 
                                        ($step['status'] == 'active' ? 'warning' : 'secondary') 
                                    }}">
                                        {{ ucfirst($step['status']) }}
                                    </span>
                                </div>
                                
                                <div class="step-body pt-3">
                                    @if($step['status'] == 'active')
                                    <div class="step-actions">
                                        <!-- Action Content -->
                                    </div>
                                    @endif
                                    
                                    <div class="step-details">
                                        <p class="mb-1">{{ $step['description'] }}</p>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Vendor List Card -->
            <div class="card card-success card-outline">
                <div class="card-header bg-gradient-success">
                    <h3 class="card-title text-white"><i class="fas fa-store-alt mr-2"></i>Daftar Vendor</h3>
                </div>
                <div class="card-body vendor-scroll" style="max-height: 400px; overflow-y: auto;">
                    <div class="vendor-card card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="vendor-avatar mr-3">
                                    <img src="https://via.placeholder.com/50" class="rounded-circle" alt="Vendor">
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Toko Elektronik Maju Jaya</h6>
                                    <div class="d-flex align-items-center">
                                        <small class="text-muted">Rating: 4.8/5</small>
                                        <div class="star-rating ml-2">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-info btn-block mt-2 btn-sm">
                                <i class="fas fa-handshake"></i> Pilih Vendor
                            </button>
                        </div>
                    </div>
                    <!-- Repeat Vendor Cards -->
                </div>
            </div>

            <!-- Live Chat Card -->
            <div class="card card-primary card-outline mt-4">
                <div class="card-header bg-gradient-primary">
                    <h3 class="card-title text-white"><i class="fas fa-comments mr-2"></i>Live Chat</h3>
                </div>
                <div class="card-body p-0">
                    <div class="chat-container" style="height: 300px; overflow-y: auto;">
                        <!-- Chat Messages -->
                    </div>
                    <div class="chat-input p-3 border-top">
                        <div class="input-group">
                            <textarea class="form-control" rows="1" placeholder="Ketik pesan..."></textarea>
                            <div class="input-group-append">
                                <button class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    /* Custom Styles */
    .img-hover-zoom {
        transition: transform .2s;
        border-radius: 8px;
        overflow: hidden;
    }

    .img-hover-zoom:hover {
        transform: scale(1.03);
    }

    .workflow-timeline {
        position: relative;
        padding-left: 60px;
    }

    .workflow-step {
        position: relative;
        padding: 25px 0;
        margin-left: 30px;
        border-left: 3px solid #dee2e6;
    }

    .workflow-step:last-child {
        border-left-style: dashed;
    }

    .step-icon {
        position: absolute;
        left: -48px;
        top: 25px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: all 0.3s ease;
    }

    .workflow-step.completed .step-icon {
        background: #28a745;
        color: white;
        border-color: #28a745;
    }

    .workflow-step.active .step-icon {
        background: #ffc107;
        color: white;
        animation: pulse 1.5s infinite;
    }

    .step-content {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-left: 35px;
        transition: transform 0.3s ease;
    }

    .workflow-step:hover .step-content {
        transform: translateX(10px);
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(255,193,7,0.4); }
        70% { box-shadow: 0 0 0 10px rgba(255,193,7,0); }
        100% { box-shadow: 0 0 0 0 rgba(255,193,7,0); }
    }

    .vendor-card {
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.1);
    }

    .vendor-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .star-rating {
        font-size: 0.9em;
    }

    .chat-container {
        background: #f8f9fa;
    }

    .chat-message {
        max-width: 80%;
        border-radius: 15px;
        padding: 10px 15px;
        margin: 10px;
        position: relative;
    }

    .received {
        background: white;
        border: 1px solid #dee2e6;
    }

    .sent {
        background: #007bff;
        color: white;
        margin-left: auto;
    }
</style>
@endsection

@section('js')
<script>
    // Smooth scroll for vendor list
    $('.vendor-scroll').smoothScroll({
        step: function() {
            this.stop();
        }
    });

    // Chat auto-scroll
    const chatContainer = $('.chat-container');
    chatContainer.scrollTop(chatContainer[0].scrollHeight);

    // Hover effects
    $('.vendor-card').hover(
        function() {
            $(this).addClass('shadow');
        },
        function() {
            $(this).removeClass('shadow');
        }
    );

    // Workflow step animations
    $('.workflow-step').hover(
        function() {
            $(this).find('.step-content').addClass('shadow');
        },
        function() {
            $(this).find('.step-content').removeClass('shadow');
        }
    );
</script>
@endsection