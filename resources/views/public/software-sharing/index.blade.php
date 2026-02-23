<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Langganan software premium dengan harga terjangkau – {{ $company->name }}">
    <title>Software Sharing – {{ $company->name }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:   #6366f1;
            --primary-d: #4f46e5;
            --primary-l: #e0e7ff;
            --accent:    #f59e0b;
            --success:   #10b981;
            --danger:    #ef4444;
            --bg:        #f8faff;
            --card-bg:   #ffffff;
            --text:      #1e293b;
            --muted:     #64748b;
            --border:    #e2e8f0;
            --radius:    16px;
            --shadow:    0 4px 24px rgba(99,102,241,.10);
            --shadow-lg: 0 12px 40px rgba(99,102,241,.18);
        }

        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        /* ── HERO ────────────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 60%, #a855f7 100%);
            color: #fff;
            padding: 72px 24px 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
        }
        .hero-content { position: relative; z-index: 1; max-width: 720px; margin: 0 auto; }
        .company-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,.15); backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.25);
            border-radius: 999px; padding: 6px 18px; font-size: 13px; font-weight: 500;
            margin-bottom: 24px; color: rgba(255,255,255,.9);
        }
        .hero h1 { font-size: clamp(2rem, 5vw, 3rem); font-weight: 800; line-height: 1.15; margin-bottom: 16px; }
        .hero p   { font-size: 1.125rem; color: rgba(255,255,255,.8); margin-bottom: 36px; }
        .hero-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn-hero-primary {
            display: inline-flex; align-items: center; gap: 8px;
            background: #fff; color: var(--primary-d); font-weight: 700;
            padding: 14px 32px; border-radius: 999px; text-decoration: none;
            font-size: 1rem; transition: transform .2s, box-shadow .2s;
            box-shadow: 0 4px 18px rgba(0,0,0,.2);
        }
        .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,0,0,.25); }
        .btn-hero-ghost {
            display: inline-flex; align-items: center; gap: 8px;
            border: 2px solid rgba(255,255,255,.6); color: #fff; font-weight: 600;
            padding: 12px 28px; border-radius: 999px; text-decoration: none;
            font-size: 1rem; transition: all .2s;
        }
        .btn-hero-ghost:hover { background: rgba(255,255,255,.15); color: #fff; }

        /* ── STATS BAR ───────────────────────────────────────── */
        .stats-bar {
            background: #fff; border-bottom: 1px solid var(--border);
            padding: 20px 24px;
        }
        .stats-inner {
            max-width: 1100px; margin: 0 auto;
            display: flex; gap: 32px; justify-content: center; flex-wrap: wrap;
        }
        .stat-item { display: flex; align-items: center; gap: 10px; }
        .stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .stat-icon.indigo { background: var(--primary-l); color: var(--primary); }
        .stat-icon.green  { background: #d1fae5; color: var(--success); }
        .stat-icon.amber  { background: #fef3c7; color: var(--accent); }
        .stat-label { font-size: 12px; color: var(--muted); }
        .stat-value { font-size: 18px; font-weight: 700; color: var(--text); line-height: 1; }

        /* ── MAIN LAYOUT ─────────────────────────────────────── */
        .main-wrap { max-width: 1100px; margin: 0 auto; padding: 48px 24px 80px; }

        /* ── SECTION HEADER ──────────────────────────────────── */
        .section-header { margin-bottom: 32px; }
        .section-header h2 { font-size: 1.5rem; font-weight: 700; }
        .section-header p  { color: var(--muted); margin-top: 4px; }

        /* ── SOFTWARE GRID ───────────────────────────────────── */
        .software-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }

        .software-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px 24px;
            display: flex; flex-direction: column;
            transition: transform .22s, box-shadow .22s;
            position: relative; overflow: hidden;
        }
        .software-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        .software-card.unavailable { opacity: .8; }

        .card-ribbon {
            position: absolute; top: 16px; right: -28px;
            background: var(--success); color: #fff; font-size: 11px; font-weight: 700;
            padding: 4px 36px; transform: rotate(45deg); letter-spacing: .5px;
        }
        .card-ribbon.full { background: var(--danger); }

        .card-logo {
            width: 72px; height: 72px; object-fit: contain;
            border-radius: 14px; border: 1px solid var(--border);
            padding: 8px; background: #fff; margin-bottom: 16px;
        }
        .card-logo-placeholder {
            width: 72px; height: 72px; border-radius: 14px; background: var(--primary-l);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; color: var(--primary); margin-bottom: 16px;
        }

        .card-type {
            display: inline-block; font-size: 11px; font-weight: 600; letter-spacing: .5px;
            background: var(--primary-l); color: var(--primary);
            border-radius: 999px; padding: 3px 12px; margin-bottom: 8px;
        }

        .card-name { font-size: 1.2rem; font-weight: 700; margin-bottom: 6px; }
        .card-desc { font-size: 13.5px; color: var(--muted); line-height: 1.5; margin-bottom: 18px; flex: 1; }

        /* packages */
        .packages-list { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
        .package-item {
            display: flex; justify-content: space-between; align-items: center;
            background: #f8faff; border: 1px solid var(--border);
            border-radius: 10px; padding: 10px 14px;
        }
        .package-name { font-size: 13px; font-weight: 600; }
        .package-duration { font-size: 11px; color: var(--muted); }
        .package-price { font-size: 14px; font-weight: 700; color: var(--primary); }

        /* availability badge */
        .avail-badge {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 12px; font-weight: 600; border-radius: 999px;
            padding: 5px 14px; margin-bottom: 18px;
        }
        .avail-badge.available { background: #d1fae5; color: #065f46; }
        .avail-badge.full      { background: #fee2e2; color: #991b1b; }

        /* CTA button */
        .btn-register {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; padding: 13px; border-radius: 12px; font-size: 15px;
            font-weight: 700; text-decoration: none; cursor: pointer; border: none;
            transition: all .2s; letter-spacing: .3px;
        }
        .btn-register.primary { background: var(--primary); color: #fff; }
        .btn-register.primary:hover { background: var(--primary-d); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.35); }
        .btn-register.disabled-btn { background: #e2e8f0; color: var(--muted); cursor: not-allowed; }

        /* ── EMPTY STATE ─────────────────────────────────────── */
        .empty-state {
            text-align: center; padding: 80px 24px;
            background: #fff; border: 2px dashed var(--border);
            border-radius: var(--radius);
        }
        .empty-state i { font-size: 52px; color: #c7d2fe; margin-bottom: 16px; }
        .empty-state h3 { font-size: 1.2rem; color: var(--muted); font-weight: 600; }

        /* ── HOW IT WORKS ────────────────────────────────────── */
        .how-section { margin-top: 64px; }
        .how-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px; margin-top: 32px;
        }
        .how-card {
            background: #fff; border: 1px solid var(--border);
            border-radius: var(--radius); padding: 28px 20px; text-align: center;
        }
        .how-num {
            width: 44px; height: 44px; border-radius: 50%; background: var(--primary-l);
            color: var(--primary); font-size: 18px; font-weight: 800;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 14px;
        }
        .how-card h4 { font-size: 15px; font-weight: 700; margin-bottom: 6px; }
        .how-card p  { font-size: 13px; color: var(--muted); line-height: 1.5; }

        /* ── STICKY NAV ──────────────────────────────────────── */
        .top-nav {
            position: sticky; top: 0; z-index: 100;
            background: rgba(255,255,255,.9); backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 12px 24px;
        }
        .nav-brand { font-size: 1rem; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 8px; }
        .nav-actions { display: flex; gap: 10px; align-items: center; }
        .nav-btn {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 14px; font-weight: 600; padding: 8px 18px; border-radius: 8px;
            text-decoration: none; transition: all .2s;
        }
        .nav-btn.outline { border: 1.5px solid var(--primary); color: var(--primary); }
        .nav-btn.outline:hover { background: var(--primary-l); }
        .nav-btn.filled  { background: var(--primary); color: #fff; }
        .nav-btn.filled:hover  { background: var(--primary-d); }

        /* ── FOOTER ─────────────────────────────────────────── */
        .footer { background: #1e293b; color: #94a3b8; text-align: center; padding: 24px; font-size: 13px; }
        .footer a { color: #6366f1; text-decoration: none; }

        /* ── ANIMATIONS ──────────────────────────────────────── */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        .animate { opacity: 0; animation: fadeInUp .5s ease forwards; }
        .delay-1 { animation-delay: .1s; } .delay-2 { animation-delay: .2s; }
        .delay-3 { animation-delay: .3s; } .delay-4 { animation-delay: .4s; }

        @media(max-width: 640px) {
            .hero { padding: 52px 16px 60px; }
            .hero h1 { font-size: 1.75rem; }
            .stats-inner { gap: 16px; }
            .top-nav { padding: 10px 16px; }
        }
    </style>
</head>
<body>

{{-- ── Sticky Nav ── --}}
<nav class="top-nav">
    <div class="nav-brand">
        <i class="fas fa-cube"></i>
        {{ $company->name }}
    </div>
    <div class="nav-actions">
        <a href="{{ route('public.software-sharing.login', $company->slug) }}" class="nav-btn outline">
            <i class="fas fa-sign-in-alt"></i> Masuk
        </a>
        <a href="{{ route('public.software-sharing.register', $company->slug) }}" class="nav-btn filled">
            <i class="fas fa-user-plus"></i> Daftar
        </a>
    </div>
</nav>

{{-- ── Hero ── --}}
<section class="hero">
    <div class="hero-content">
        <div class="company-badge animate delay-1">
            <i class="fas fa-building"></i> {{ $company->name }} Software Sharing
        </div>
        <h1 class="animate delay-2">Software Premium,<br>Harga Terjangkau</h1>
        <p class="animate delay-3">Akses software-software pilihan dengan sistem akun sharing. Hemat biaya, kualitas tetap premium.</p>
        <div class="hero-actions animate delay-4">
            <a href="{{ route('public.software-sharing.register', $company->slug) }}" class="btn-hero-primary">
                <i class="fas fa-rocket"></i> Daftar Sekarang – Gratis!
            </a>
            <a href="#software-list" class="btn-hero-ghost">
                <i class="fas fa-th-large"></i> Lihat Software
            </a>
        </div>
    </div>
</section>

{{-- ── Stats Bar ── --}}
@php
    $totalSoftware   = $softwares->count();
    $availableSoftware = $softwares->where('has_available_slots', true)->count();
    $totalPackages   = $softwares->sum(fn($s) => $s->activePackages->count());
@endphp
<div class="stats-bar">
    <div class="stats-inner">
        <div class="stat-item">
            <div class="stat-icon indigo"><i class="fas fa-box-open"></i></div>
            <div>
                <div class="stat-value">{{ $totalSoftware }}</div>
                <div class="stat-label">Total Software</div>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="stat-value">{{ $availableSoftware }}</div>
                <div class="stat-label">Slot Tersedia</div>
            </div>
        </div>
        <div class="stat-item">
            <div class="stat-icon amber"><i class="fas fa-tags"></i></div>
            <div>
                <div class="stat-value">{{ $totalPackages }}</div>
                <div class="stat-label">Pilihan Paket</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Main Content ── --}}
<div class="main-wrap">

    {{-- Section Header --}}
    <div class="section-header" id="software-list">
        <h2><i class="fas fa-th-large" style="color:var(--primary)"></i> Daftar Software Tersedia</h2>
        <p>Pilih software yang Anda butuhkan, lalu daftar akun untuk mulai berlangganan.</p>
    </div>

    {{-- Software Grid --}}
    @if($softwares->isEmpty())
    <div class="empty-state">
        <i class="fas fa-box-open"></i>
        <h3>Belum ada software tersedia saat ini.</h3>
        <p style="color:var(--muted); margin-top:8px">Silakan cek kembali nanti atau hubungi admin.</p>
    </div>
    @else
    <div class="software-grid">
        @foreach($softwares as $i => $software)
        <div class="software-card {{ !$software->has_available_slots ? 'unavailable' : '' }}" style="animation: fadeInUp .45s ease {{ $i * .06 }}s both">

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
            @if($software->description)
                <div class="card-desc">{{ Str::limit($software->description, 120) }}</div>
            @else
                <div class="card-desc">Software premium dengan sistem akun sharing yang aman dan terpercaya.</div>
            @endif

            {{-- Packages --}}
            @if($software->activePackages->isNotEmpty())
            <div class="packages-list">
                @foreach($software->activePackages->take(3) as $package)
                <div class="package-item">
                    <div>
                        <div class="package-name">{{ $package->nama_paket }}</div>
                        <div class="package-duration"><i class="fas fa-calendar-alt"></i> {{ $package->durasi_hari }} hari</div>
                    </div>
                    <div class="package-price">Rp {{ number_format($package->harga, 0, ',', '.') }}</div>
                </div>
                @endforeach
                @if($software->activePackages->count() > 3)
                <div style="text-align:center;font-size:12px;color:var(--muted)">
                    +{{ $software->activePackages->count() - 3 }} paket lainnya setelah daftar
                </div>
                @endif
            </div>
            @endif

            {{-- Availability --}}
            <span class="avail-badge {{ $software->has_available_slots ? 'available' : 'full' }}">
                <i class="fas fa-{{ $software->has_available_slots ? 'check-circle' : 'times-circle' }}"></i>
                {{ $software->has_available_slots ? 'Slot Tersedia' : 'Slot Penuh' }}
            </span>

            {{-- CTA --}}
            @if($software->has_available_slots)
                <a href="{{ route('public.software-sharing.register', $company->slug) }}?software={{ $software->id }}"
                   class="btn-register primary">
                    <i class="fas fa-user-plus"></i> Daftar untuk Berlangganan
                </a>
            @else
                <button class="btn-register disabled-btn" disabled>
                    <i class="fas fa-ban"></i> Slot Sedang Penuh
                </button>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── How it Works ── --}}
    <div class="how-section">
        <div class="section-header">
            <h2><i class="fas fa-info-circle" style="color:var(--primary)"></i> Cara Berlangganan</h2>
            <p>Langkah mudah untuk mulai menggunakan software pilihan Anda.</p>
        </div>
        <div class="how-grid">
            <div class="how-card">
                <div class="how-num">1</div>
                <h4>Daftar Akun</h4>
                <p>Buat akun gratis dengan email & password Anda.</p>
            </div>
            <div class="how-card">
                <div class="how-num">2</div>
                <h4>Pilih Software</h4>
                <p>Pilih software dan paket yang sesuai kebutuhan Anda.</p>
            </div>
            <div class="how-card">
                <div class="how-num">3</div>
                <h4>Lakukan Pembayaran</h4>
                <p>Bayar dengan transfer bank atau gateway pembayaran online.</p>
            </div>
            <div class="how-card">
                <div class="how-num">4</div>
                <h4>Akses Software</h4>
                <p>Dapatkan kredensial dan langsung gunakan software Anda.</p>
            </div>
        </div>
    </div>

</div>

{{-- ── Footer ── --}}
<footer class="footer">
    <p>&copy; {{ date('Y') }} <a href="#">{{ $company->name }}</a>. Platform Software Sharing terpercaya.</p>
</footer>

<script>
// Smooth scroll untuk link anchor
document.querySelectorAll('a[href^="#"]').forEach(el => {
    el.addEventListener('click', e => {
        e.preventDefault();
        const target = document.querySelector(el.getAttribute('href'));
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
</script>
</body>
</html>
