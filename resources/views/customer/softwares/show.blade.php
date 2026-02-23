@extends('adminlte::page')

@section('title', $software->nama . ' - ' . $software->tipe_paket)

@section('content_header')
    <div class="mb-4">
        <a href="{{ route('customer-software.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Menu Utama
        </a>
    </div>
@stop

@section('content')
<div class="custom-container">

    <div class="modern-two-col">
        {{-- Left Column: Software Info --}}
        <div class="left-col">
            <div class="modern-card text-center slide-up-1">
                @if($software->logo)
                <img src="{{ s3_asset(true, 10, $software->logo) }}" alt="{{ $software->nama }}" class="detail-logo">
                @else
                <div class="detail-logo-placeholder">
                    <i class="fas fa-desktop"></i>
                </div>
                @endif
                
                <h2 class="detail-title">{{ $software->nama }}</h2>
                <div class="detail-type">{{ $software->tipe_paket }}</div>
                
                {{-- Slot Status --}}
                @if($hasAvailableSlots)
                <div class="status-alert success mt-4">
                    <i class="fas fa-check-circle icon"></i> 
                    <div>
                        <strong>Slot Tersedia</strong>
                        <div class="sub">Silakan pilih paket di samping.</div>
                    </div>
                </div>
                @else
                <div class="status-alert danger mt-4">
                    <i class="fas fa-times-circle icon"></i> 
                    <div>
                        <strong>Slot Penuh</strong>
                        <div class="sub">Mohon hubungi admin atau coba lagi nanti.</div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Company Info --}}
            <div class="modern-card slide-up-2">
                <h5 class="card-heading">Provider</h5>
                <div class="provider-info">
                    <i class="fas fa-building text-primary mr-2"></i>
                    <strong>{{ $software->company->name }}</strong>
                </div>
            </div>
            
            {{-- Info --}}
            <div class="modern-card info-card slide-up-3">
                <h5 class="card-heading text-info">
                    <i class="fas fa-info-circle"></i> Informasi Penting
                </h5>
                <ul class="info-list">
                    <li>Setelah pembayaran berhasil, kredensial akses akan dikirim ke email Anda.</li>
                    <li>Akun bersifat sharing dengan maksimal pengguna sesuai slot yang tersedia.</li>
                    <li>Pastikan email Anda aktif untuk menerima informasi akses.</li>
                    <li>Hubungi admin jika ada kendala.</li>
                </ul>
            </div>
        </div>

        {{-- Right Column: Packages & Desc --}}
        <div class="right-col">
            {{-- Description --}}
            @if($software->description)
            <div class="modern-card slide-up-1">
                <h5 class="card-heading">Tentang {{ $software->nama }}</h5>
                <p class="description-text">{{ $software->description }}</p>
            </div>
            @endif

            {{-- Packages --}}
            <div class="modern-card slide-up-2 border-primary-top">
                <h4 class="card-heading mb-4 text-primary">
                    <i class="fas fa-tags mr-2"></i> Pilih Paket Langganan
                </h4>
                
                <div class="package-grid">
                    @forelse($software->activePackages as $package)
                    <div class="package-card {{ !$hasAvailableSlots ? 'disabled' : '' }}">
                        <h4 class="pkg-name">{{ $package->nama_paket }}</h4>
                        <div class="pkg-price-wrap">
                            <span class="currency">Rp</span>
                            <span class="price-val">{{ number_format($package->harga, 0, ',', '.') }}</span>
                        </div>
                        <div class="pkg-duration">
                            <i class="fas fa-calendar-alt"></i> {{ $package->durasi_hari }} hari ({{ $package->duration_in_months }} bln)
                        </div>
                        
                        <div class="pkg-action">
                            @canAccess('show','customer_checkouts')
                                @if($hasAvailableSlots)
                                <a href="{{ route('customer-checkout.show', [$software->slug, $package->id]) }}" 
                                    class="btn-modern success">
                                    <i class="fas fa-shopping-cart"></i> Beli Sekarang
                                </a>
                                @else
                                <button class="btn-modern secondary disabled" disabled>
                                    <i class="fas fa-ban"></i> Slot Penuh
                                </button>
                                @endif
                            @endcanAccess
                        </div>
                    </div>
                    @empty
                    <div class="col-12" style="grid-column: 1 / -1;">
                        <div class="status-alert warning">
                            <i class="fas fa-exclamation-triangle icon"></i>
                            <div>Belum ada paket tersedia untuk software ini.</div>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary:   #de342f;
        --primary-d: #b91c1c;
        --primary-l: #fee2e2;
        --success:   #10b981;
        --danger:    #ef4444;
        --warning:   #f59e0b;
        --info:      #3b82f6;
        --bg:        #fdfafa;
        --card-bg:   #ffffff;
        --text:      #1e293b;
        --muted:     #64748b;
        --border:    #e2e8f0;
        --radius:    16px;
        --shadow:    0 4px 20px rgba(222, 52, 47, .08);
        --font-inter: 'Inter', sans-serif;
    }

    body { font-family: var(--font-inter); }

    .custom-container { max-width: 1100px; margin: 0 auto; padding-bottom: 40px; }

    .btn-back {
        display: inline-flex; align-items: center; gap: 8px;
        color: var(--muted); font-weight: 600; text-decoration: none;
        padding: 8px 16px; border-radius: 999px;
        background: #fff; border: 1px solid var(--border);
        transition: all 0.2s; font-size: 14px;
    }
    .btn-back:hover {
        background: var(--primary-l); color: var(--primary);
        border-color: var(--primary-l);
    }

    .modern-two-col {
        display: flex; gap: 24px; align-items: flex-start;
    }
    .left-col { flex: 0 0 340px; display: flex; flex-direction: column; gap: 24px; }
    .right-col { flex: 1; display: flex; flex-direction: column; gap: 24px; min-width: 0; }
    
    @media(max-width: 768px) {
        .modern-two-col { flex-direction: column; }
        .left-col { flex: none; width: 100%; }
    }

    /* Cards */
    .modern-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 28px 24px;
        box-shadow: var(--shadow);
        animation: fadeInUp 0.5s ease backwards;
    }
    .modern-card.border-primary-top {
        border-top: 4px solid var(--primary);
    }
    .modern-card.info-card {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .card-heading {
        font-size: 1.1rem; font-weight: 700; color: var(--text);
        margin-bottom: 16px; border-bottom: 1px solid var(--border);
        padding-bottom: 12px; display: flex; align-items: center; gap: 8px;
    }
    .modern-card.info-card .card-heading {
        border-color: #bbf7d0; margin-bottom: 12px;
    }

    /* Left Col Elements */
    .detail-logo {
        width: 120px; height: 120px; object-fit: contain;
        border-radius: 20px; border: 1px solid var(--border);
        padding: 12px; background: #fff; margin: 0 auto 20px; display: block;
    }
    .detail-logo-placeholder {
        width: 120px; height: 120px; border-radius: 20px; background: var(--primary-l);
        display: flex; align-items: center; justify-content: center;
        font-size: 48px; color: var(--primary); margin: 0 auto 20px;
    }
    .detail-title {
        font-size: 1.5rem; font-weight: 800; color: var(--text); margin-bottom: 8px;
    }
    .detail-type {
        display: inline-block; font-size: 12px; font-weight: 600;
        background: var(--primary-l); color: var(--primary);
        border-radius: 999px; padding: 4px 14px;
    }

    /* Alerts / Status */
    .status-alert {
        display: flex; gap: 12px; text-align: left; padding: 14px;
        border-radius: 12px; font-size: 14px; align-items: center;
    }
    .status-alert .icon { font-size: 24px; }
    .status-alert .sub { font-size: 12px; opacity: 0.8; margin-top: 2px; }
    
    .status-alert.success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
    .status-alert.danger { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
    .status-alert.warning { background: #fef3c7; color: #b45309; border: 1px solid #f59e0b; }

    .provider-info { font-size: 15px; color: var(--text); }
    .description-text { font-size: 14.5px; color: var(--muted); line-height: 1.6; }
    
    .info-list {
        margin: 0; padding-left: 20px; font-size: 13.5px; color: #166534; line-height: 1.6;
    }

    /* Packages Grid */
    .package-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;
    }
    .package-card {
        border: 2px solid var(--border);
        border-radius: 14px; padding: 20px;
        text-align: center; transition: all 0.2s;
        background: #fff;
        display: flex; flex-direction: column;
    }
    .package-card:not(.disabled):hover {
        border-color: var(--primary); box-shadow: 0 8px 20px rgba(222, 52, 47, .15);
        transform: translateY(-2px);
    }
    .package-card.disabled { background: #f8fafc; opacity: 0.7; }
    
    .pkg-name { font-size: 1.1rem; font-weight: 700; color: var(--text); margin-bottom: 12px; }
    .pkg-price-wrap { color: var(--success); margin-bottom: 8px; }
    .pkg-price-wrap .currency { font-size: 16px; font-weight: 600; vertical-align: top; }
    .pkg-price-wrap .price-val { font-size: 28px; font-weight: 800; line-height: 1; }
    .pkg-duration { font-size: 12px; color: var(--muted); margin-bottom: 20px; }
    .pkg-action { margin-top: auto; }

    /* Buttons */
    .btn-modern {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; padding: 12px; border-radius: 8px; font-size: 14px;
        font-weight: 700; text-decoration: none; cursor: pointer; border: none;
        transition: all .2s;
    }
    .btn-modern.success { background: var(--success); color: #fff; }
    .btn-modern.success:hover { background: #059669; color: #fff; box-shadow: 0 4px 12px rgba(16,185,129,.3); }
    .btn-modern.secondary { background: #e2e8f0; color: var(--muted); }

    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .slide-up-1 { animation-delay: 0.05s; }
    .slide-up-2 { animation-delay: 0.1s; }
    .slide-up-3 { animation-delay: 0.15s; }
</style>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@stop

@section('js')
@stop
