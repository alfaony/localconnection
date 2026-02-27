@extends('adminlte::page')

@section('title', 'Software Catalog')

@section('content_header')
    <div class="header-banner">
        <h1 class="header-title">Pilih Software Langganan Anda</h1>
        <p class="header-subtitle">Berbagai software premium dengan harga terjangkau</p>
    </div>
@stop

@section('content')
@include('components.alert')
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
                {{-- Logo --}}
                @if($software->logo)
                    <img src="{{ s3_asset(true, 10, $software->logo) }}" alt="{{ $software->nama }}" class="card-logo">
                @else
                    <div class="card-logo-placeholder">
                        <i class="fas fa-desktop"></i>
                    </div>
                @endif

                {{-- Info --}}
                <span class="card-type">{{ $software->tipe_paket }}</span>
                <div class="card-name">{{ $software->nama }}</div>

                {{-- Price --}}
                @if($software->activePackages->isNotEmpty())
                <div class="card-price">
                    Mulai <strong>Rp {{ number_format($software->activePackages->min('harga'), 0, ',', '.') }}</strong>
                </div>
                @endif

                <div class="card-spacer"></div>

                {{-- Availability --}}
                <span class="avail-badge {{ $software->has_available_slots ? 'available' : 'full' }}">
                    <i class="fas fa-{{ $software->has_available_slots ? 'check-circle' : 'times-circle' }}"></i>
                    {{ $software->has_available_slots ? 'Tersedia' : 'Penuh' }}
                </span>

                {{-- CTA --}}
                @canAccess('show','customer_software')
                <a href="{{ route('customer-software.show', $software->slug) }}" class="btn-detail {{ $software->has_available_slots ? 'primary' : 'secondary' }}">
                    Lihat Detail
                </a>
                @endcanAccess
            </div>
            @endforeach
        </div>
        @endif

        {{-- Pagination --}}
        @if($softwares->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $softwares->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
        @endif
    </div>
@stop

@section('css')
<style>
    :root {
        --primary:   #de342f;
        --primary-d: #b91c1c;
        --primary-l: #fee2e2;
        --success:   #10b981;
        --bg:        #f8faff;
        --card-bg:   #ffffff;
        --text:      #1e293b;
        --muted:     #64748b;
        --border:    #e2e8f0;
        --font-inter: 'Inter', sans-serif;
    }
    body { font-family: var(--font-inter); }
    .custom-container { max-width: 1100px; margin: 0 auto; padding-bottom: 40px; }

    /* ── Header ─── */
    .header-banner {
        background-color: #de342f;
        background-image: radial-gradient(circle at center, rgba(239, 68, 68, 0.8) 0%, rgba(219, 39, 41, 0) 80%);
        padding: 32px 20px; border-radius: 14px; text-align: center;
        color: #fff; margin-bottom: 24px;
    }
    .header-title { font-weight: 800; font-size: 1.6rem; margin-bottom: 4px; }
    .header-subtitle { color: rgba(255,255,255,0.85); font-size: 0.95rem; margin: 0; }

    /* ── Search ─── */
    .search-wrapper { max-width: 500px; margin: 0 auto; }
    .modern-search {
        display: flex; background: #fff; border: 1px solid var(--border);
        border-radius: 999px; padding: 4px 4px 4px 16px;
        align-items: center; transition: box-shadow 0.2s;
    }
    .modern-search:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(222,52,47,.1); }
    .search-icon { color: var(--muted); font-size: 14px; }
    .search-input { border: none; flex: 1; padding: 8px 12px; outline: none; font-size: 14px; background: transparent; }
    .btn-search {
        background: var(--primary); color: #fff; border: none;
        padding: 8px 20px; border-radius: 999px; font-weight: 600; font-size: 13px; cursor: pointer;
    }
    .btn-search:hover { background: var(--primary-d); }

    /* ── Software Grid (horizontal, minimalist) ─── */
    .software-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 16px;
    }
    .software-card {
        background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px;
        padding: 20px; display: flex; flex-direction: column; align-items: center;
        text-align: center; transition: transform .18s, box-shadow .18s;
        animation: fadeIn 0.3s ease forwards; opacity: 0;
    }
    @keyframes fadeIn { to { opacity: 1; } }
    .software-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.08); }
    .software-card.unavailable { opacity: 0.7; }

    .card-logo {
        width: 56px; height: 56px; object-fit: contain;
        border-radius: 12px; border: 1px solid var(--border);
        padding: 6px; background: #fff; margin-bottom: 12px;
    }
    .card-logo-placeholder {
        width: 56px; height: 56px; border-radius: 12px; background: var(--primary-l);
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; color: var(--primary); margin-bottom: 12px;
    }
    .card-type {
        font-size: 10px; font-weight: 600; letter-spacing: .3px;
        background: var(--primary-l); color: var(--primary);
        border-radius: 999px; padding: 3px 10px; margin-bottom: 8px;
    }
    .card-name { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
    .card-price { font-size: 12px; color: var(--muted); margin-bottom: 4px; }
    .card-price strong { color: var(--primary); font-weight: 700; }
    .card-spacer { flex: 1; }

    .avail-badge {
        font-size: 11px; font-weight: 600; border-radius: 999px;
        padding: 3px 12px; display: inline-flex; align-items: center; gap: 4px;
        margin-bottom: 12px;
    }
    .avail-badge.available { background: #d1fae5; color: #065f46; }
    .avail-badge.full { background: #f1f5f9; color: #475569; }

    .btn-detail {
        display: block; width: 100%; text-align: center;
        padding: 9px 16px; border-radius: 10px; font-size: 13px;
        font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all .15s;
    }
    .btn-detail.primary { background: var(--primary); color: #fff; }
    .btn-detail.primary:hover { background: var(--primary-d); color: #fff; }
    .btn-detail.secondary { background: #e2e8f0; color: var(--muted); }
    .btn-detail.secondary:hover { background: #cbd5e1; color: #475569; }

    /* Empty State */
    .empty-state {
        text-align: center; padding: 48px 20px; background: #fff;
        border: 2px dashed var(--border); border-radius: 14px; margin-top: 16px;
    }
    .empty-state i { font-size: 40px; color: #c7d2fe; margin-bottom: 12px; }
    .empty-state h3 { font-size: 1.1rem; color: var(--text); font-weight: 700; margin-bottom: 6px; }
    .empty-state p { color: var(--muted); margin: 0; font-size: 14px; }
</style>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@stop

@section('js')
@stop
