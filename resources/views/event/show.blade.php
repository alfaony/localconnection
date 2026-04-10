<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->name }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0d1117;
            color: #e0e0ff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Hero ─────────────────────────────────────────────── */
        .hero {
            position: relative;
            height: 55vh;
            min-height: 280px;
            background: #16213e;
            overflow: hidden;
            flex-shrink: 0;
        }

        .hero-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .hero-gradient {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(13,17,23,0)     0%,
                rgba(13,17,23,0.3)  40%,
                rgba(13,17,23,0.85) 75%,
                rgba(13,17,23,1)   100%
            );
        }

        /* Fallback placeholder when no image */
        .hero-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-placeholder-inner {
            width: 96px;
            height: 96px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.8rem;
            opacity: .35;
        }

        /* Color accent bar */
        .accent-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        /* Back button */
        .btn-back {
            position: absolute;
            top: 18px;
            left: 18px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(13,17,23,.7);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.12);
            color: #e0e0ff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: .9rem;
            transition: background .2s;
            z-index: 10;
        }
        .btn-back:hover { background: rgba(102,126,234,.4); color: #fff; }

        /* Detail link */
        .btn-detail {
            position: absolute;
            top: 18px;
            right: 18px;
            padding: 6px 14px;
            border-radius: 20px;
            background: rgba(13,17,23,.7);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.12);
            color: #a5b4fc;
            text-decoration: none;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .5px;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background .2s;
            z-index: 10;
        }
        .btn-detail:hover { background: rgba(102,126,234,.3); color: #fff; }

        /* ── Content ───────────────────────────────────────────── */
        .content {
            flex: 1;
            padding: 28px 24px 48px;
            max-width: 680px;
            width: 100%;
            margin: 0 auto;
        }

        .badges {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 600;
            letter-spacing: .3px;
        }

        .badge-active   { background: rgba(56,239,125,.15); color: #38ef7d; border: 1px solid rgba(56,239,125,.25); }
        .badge-inactive { background: rgba(156,163,175,.12); color: #9ca3af; border: 1px solid rgba(156,163,175,.2); }
        .badge-routine  { background: rgba(102,126,234,.15); color: #a5b4fc; border: 1px solid rgba(102,126,234,.25); }

        .event-name {
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -.3px;
            margin-bottom: 12px;
            color: #fff;
        }

        .meta-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: .8rem;
            color: #a0a8d0;
        }

        .meta-icon {
            width: 18px;
            text-align: center;
            flex-shrink: 0;
        }

        .divider {
            height: 1px;
            background: rgba(255,255,255,.06);
            margin: 22px 0;
        }

        .desc-label {
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #4b5563;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .description {
            font-size: .9rem;
            line-height: 1.75;
            color: #c8d0e0;
            white-space: pre-wrap;
        }

        /* Glow dot */
        .glow-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
            box-shadow: 0 0 8px currentColor;
        }
    </style>
</head>
<body>

{{-- ── Hero Section ──────────────────────────────────────────── --}}
<div class="hero">
    @if($event->image)
        <img class="hero-img" src="{{ s3_asset(true, null, $event->image) }}" alt="{{ $event->name }}">
    @else
        <div class="hero-placeholder">
            <div class="hero-placeholder-inner" style="background:{{ $event->color }}22;">
                🎪
            </div>
        </div>
    @endif

    <div class="hero-gradient"></div>

    <div class="accent-bar" style="background:linear-gradient(90deg,{{ $event->color }},{{ $event->color }}44);"></div>

    {{-- Back button --}}
    <a href="{{ url()->previous() }}" class="btn-back">&#8592;</a>

    {{-- Detail link (admin only) --}}
    @canAccess('show','events')
    <a href="{{ route('event.detail', $event->id) }}" class="btn-detail">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12"/></svg>
        Kelola Event
    </a>
    @endcanAccess
</div>

{{-- ── Content ───────────────────────────────────────────────── --}}
<div class="content">

    {{-- Badges --}}
    <div class="badges">
        <span class="badge {{ $event->is_active ? 'badge-active' : 'badge-inactive' }}">
            <span class="glow-dot" style="background:{{ $event->is_active ? '#38ef7d' : '#9ca3af' }};color:{{ $event->is_active ? '#38ef7d' : '#9ca3af' }};width:6px;height:6px;"></span>
            {{ $event->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
        @if($event->is_routine)
        <span class="badge badge-routine">
            &#x21bb; Rutin Mingguan
        </span>
        @endif
    </div>

    {{-- Name --}}
    <h1 class="event-name">{{ $event->name }}</h1>

    {{-- Meta info --}}
    <div class="meta-row">
        <span class="meta-icon" style="color:{{ $event->color }};">&#x1F4C5;</span>
        <span>
            {{ $event->start_date->format('d M Y') }}
            @if($event->start_date->ne($event->end_date))
                &ndash; {{ $event->end_date->format('d M Y') }}
            @endif
            <span style="opacity:.45;margin-left:4px;">({{ $event->durationDays() }} hari)</span>
        </span>
    </div>

    @if($event->start_time)
    <div class="meta-row">
        <span class="meta-icon" style="color:#f093fb;">&#x23F0;</span>
        <span>{{ $event->timeRange() }}</span>
    </div>
    @endif

    @if($event->is_routine && $event->routine_end_date)
    <div class="meta-row">
        <span class="meta-icon" style="color:#667eea;">&#x21BB;</span>
        <span>Repeat s/d {{ $event->routine_end_date->format('d M Y') }}</span>
    </div>
    @endif

    {{-- Description --}}
    @if($event->description)
    <div class="divider"></div>
    <div class="desc-label">Deskripsi</div>
    <p class="description">{{ $event->description }}</p>
    @endif

</div>

</body>
</html>
