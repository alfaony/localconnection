@extends('adminlte::page')

@section('title', 'Software Catalog')

@section('content_header')
    <div class="header-banner">
        <h1 class="header-title">Pilih Software Langganan Anda</h1>
        <p class="header-subtitle">Berbagai software premium dengan harga terjangkau</p>
    </div>
@stop

@section('content')
    <div class="custom-container">
        {{-- Search --}}
        <div class="search-wrapper mb-4">
            <form method="GET">
                <div class="modern-search">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" class="search-input" placeholder="Cari software..." value="{{ request('search') }}">
                    <button class="btn-search" type="submit">Cari</button>
                </div>
            </form>
        </div>

        {{-- Software Grid --}}
        @if($softwares->isEmpty())
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>Tidak ada software tersedia</h3>
            <p>Silakan coba kata kunci pencarian lain atau hubungi admin.</p>
        </div>
        @else
        <div class="software-grid">
            @foreach($softwares as $i => $software)
            <div class="software-card {{ !$software->has_available_slots ? 'unavailable' : '' }}" style="animation-delay: {{ $i * 0.05 }}s;">
                
                {{-- Ribbon --}}
                <div class="card-ribbon {{ $software->has_available_slots ? '' : 'full' }}">
                    {{ $software->has_available_slots ? 'Tersedia' : 'Penuh' }}
                </div>

                {{-- Logo --}}
                @if($software->logo)
                    <img src="{{ s3_asset(true, 10, $software->logo) }}" alt="{{ $software->nama }}" class="card-logo">
                @else
                    <div class="card-logo-placeholder">
                        <i class="fas fa-desktop"></i>
                    </div>
                @endif

                {{-- Type badge --}}
                <span class="card-type">{{ $software->tipe_paket }}</span>

                {{-- Name --}}
                <div class="card-name">{{ $software->nama }}</div>

                {{-- Description --}}
                <div class="card-desc">
                    {{ $software->description ? Str::limit($software->description, 100) : 'Software premium dengan sistem akun sharing yang aman dan terpercaya.' }}
                </div>

                {{-- Package Preview --}}
                @if($software->activePackages->isNotEmpty())
                <div class="package-preview">
                    <span class="muted">Mulai dari:</span>
                    <strong class="price">Rp {{ number_format($software->activePackages->min('harga'), 0, ',', '.') }}</strong>
                    <span class="muted">/ {{ $software->activePackages->where('harga', $software->activePackages->min('harga'))->first()->nama_paket }}</span>
                </div>
                @endif

                {{-- Availability Badge --}}
                <span class="avail-badge {{ $software->has_available_slots ? 'available' : 'full' }}">
                    <i class="fas fa-{{ $software->has_available_slots ? 'check-circle' : 'times-circle' }}"></i>
                    {{ $software->has_available_slots ? 'Slot Tersedia' : 'Slot Penuh' }}
                </span>

                @canAccess('show','customer_software')
                <a href="{{ route('customer-software.show', $software->slug) }}" class="btn-modern {{ $software->has_available_slots ? 'primary' : 'secondary' }}">
                    <i class="fas fa-info-circle"></i> Lihat Detail
                </a>
                @endcanAccess
            </div>
            @endforeach
        </div>
        @endif

        {{-- Pagination --}}
        @if($softwares->hasPages())
        <div class="d-flex justify-content-center mt-5">
            {{ $softwares->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
        @endif
    </div>
@stop

@section('css')
<style>
    /* CSS Variables matching the public template */
    :root {
        --primary:   #de342f;
        --primary-d: #b91c1c;
        --primary-l: #fee2e2;
        --success:   #10b981;
        --danger:    #ef4444;
        --bg:        #f8faff;
        --card-bg:   #ffffff;
        --text:      #1e293b;
        --muted:     #64748b;
        --border:    #e2e8f0;
        --radius:    16px;
        --shadow:    0 4px 24px rgba(99,102,241,.10);
        --shadow-hover: 0 12px 40px rgba(99,102,241,.18);
        --font-inter: 'Inter', sans-serif;
    }

    body {
        font-family: var(--font-inter);
    }

    .custom-container {
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 40px;
    }

    /* ── Header Banner ─────────────────────────────────── */
    .header-banner {
        background-color: #de342f;
        background-image: radial-gradient(circle at center, rgba(239, 68, 68, 0.8) 0%, rgba(219, 39, 41, 0) 80%);
        padding: 40px 20px;
        border-radius: var(--radius);
        text-align: center;
        color: #fff;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(79, 70, 229, 0.2);
    }
    .header-title {
        font-weight: 800;
        font-size: 2rem;
        margin-bottom: 8px;
    }
    .header-subtitle {
        color: rgba(255,255,255,0.85);
        font-size: 1.1rem;
        margin: 0;
    }

    /* ── Search Bar ────────────────────────────────────── */
    .search-wrapper {
        max-width: 600px;
        margin: 0 auto;
    }
    .modern-search {
        display: flex;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 999px;
        padding: 6px 6px 6px 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        transition: box-shadow 0.2s;
        align-items: center;
    }
    .modern-search:focus-within {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99,102,241,.12);
    }
    .search-icon {
        color: var(--muted);
        font-size: 16px;
    }
    .search-input {
        border: none;
        flex: 1;
        padding: 10px 14px;
        outline: none;
        font-size: 15px;
        color: var(--text);
        background: transparent;
    }
    .btn-search {
        background: var(--primary);
        color: #fff;
        border: none;
        padding: 10px 24px;
        border-radius: 999px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    .btn-search:hover {
        background: var(--primary-d);
    }

    /* ── Software Grid ─────────────────────────────────── */
    .software-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
    }
    .software-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 24px;
        display: flex;
        flex-direction: column;
        transition: transform .22s, box-shadow .22s;
        position: relative;
        overflow: hidden;
        animation: fadeInUp 0.5s ease forwards;
        opacity: 0;
        transform: translateY(20px);
    }
    @keyframes fadeInUp {
        to { opacity: 1; transform: translateY(0); }
    }
    .software-card:hover { 
        transform: translateY(-4px); 
        box-shadow: var(--shadow-hover); 
    }
    .software-card.unavailable { 
        opacity: 0.85; 
    }

    /* Ribbon */
    .card-ribbon {
        position: absolute; top: 16px; right: -28px;
        background: var(--success); color: #fff; font-size: 11px; font-weight: 700;
        padding: 4px 36px; transform: rotate(45deg); letter-spacing: .5px;
        z-index: 2;
    }
    .card-ribbon.full { background: var(--border); color: var(--muted); }

    /* Logo */
    .card-logo {
        width: 72px; height: 72px; object-fit: contain;
        border-radius: 14px; border: 1px solid var(--border);
        padding: 8px; background: #fff; margin-bottom: 20px;
    }
    .card-logo-placeholder {
        width: 72px; height: 72px; border-radius: 14px; background: var(--primary-l);
        display: flex; align-items: center; justify-content: center;
        font-size: 28px; color: var(--primary); margin-bottom: 20px;
    }

    /* Content */
    .card-type {
        align-self: flex-start;
        display: inline-block; font-size: 11px; font-weight: 600; letter-spacing: .5px;
        background: var(--primary-l); color: var(--primary);
        border-radius: 999px; padding: 4px 12px; margin-bottom: 12px;
    }
    .card-name { font-size: 1.25rem; font-weight: 700; margin-bottom: 8px; color: var(--text); }
    .card-desc { font-size: 13.5px; color: var(--muted); line-height: 1.5; margin-bottom: 20px; flex: 1; }

    /* Package Preview */
    .package-preview {
        background: #f8faff;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 20px;
        border: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .package-preview .muted { font-size: 12px; color: var(--muted); }
    .package-preview .price { font-size: 18px; color: var(--primary); font-weight: 800; margin: 4px 0; }

    /* Badges */
    .avail-badge {
        align-self: center;
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 600; border-radius: 999px;
        padding: 6px 16px; margin-bottom: 20px;
    }
    .avail-badge.available { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .avail-badge.full      { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

    /* Buttons */
    .btn-modern {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        width: 100%; padding: 12px; border-radius: 12px; font-size: 14px;
        font-weight: 700; text-decoration: none; cursor: pointer; border: none;
        transition: all .2s;
    }
    .btn-modern.primary { background: var(--primary); color: #fff; }
    .btn-modern.primary:hover { background: var(--primary-d); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 15px rgba(99,102,241,.3); }
    .btn-modern.secondary { background: #e2e8f0; color: var(--muted); }
    .btn-modern.secondary:hover { background: #cbd5e1; color: #475569; }

    /* Empty State */
    .empty-state {
        text-align: center; padding: 60px 20px;
        background: #fff; border: 2px dashed var(--border);
        border-radius: var(--radius);
        margin-top: 20px;
    }
    .empty-state i { font-size: 48px; color: #c7d2fe; margin-bottom: 16px; }
    .empty-state h3 { font-size: 1.25rem; color: var(--text); font-weight: 700; margin-bottom: 8px; }
    .empty-state p { color: var(--muted); margin: 0; }
</style>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@stop

@section('js')
@stop
