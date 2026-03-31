@extends('adminlte::page')

@section('title', 'Riwayat XP')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 fw-bold" style="color:#e0e0ff;">⚡ Riwayat XP{{ isset($user) ? ' — ' . $user->name : ' Saya' }}</h1>
        <small style="color:#a0a8d0;">Seluruh riwayat poin pengalaman Anda</small>
    </div>
    <a href="{{ route('employee-xp.leaderboard') }}" class="btn btn-sm" style="background:rgba(245,166,35,.15);color:#f5a623;border:1px solid rgba(245,166,35,.3);">
        <i class="fas fa-trophy mr-1"></i> Leaderboard
    </a>
</div>
@stop

@section('content')
@include('components.alert')

{{-- ── STAT CARDS ──────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="xh-stat-card" style="--accent:#667eea;">
            <div class="xh-stat-icon"><i class="fas fa-bolt"></i></div>
            <div class="xh-stat-val">{{ number_format($totalXp) }}</div>
            <div class="xh-stat-lbl">Total XP</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="xh-stat-card" style="--accent:#38ef7d;">
            <div class="xh-stat-icon" style="color:#38ef7d;"><i class="fas fa-arrow-up"></i></div>
            <div class="xh-stat-val" style="color:#38ef7d;">+{{ number_format($histories->where('xp', '>', 0)->sum('xp')) }}</div>
            <div class="xh-stat-lbl">XP Diterima ({{ $histories->where('xp', '>', 0)->count() }} transaksi)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="xh-stat-card" style="--accent:#f5576c;">
            <div class="xh-stat-icon" style="color:#f5576c;"><i class="fas fa-arrow-down"></i></div>
            <div class="xh-stat-val" style="color:#f5576c;">{{ number_format($histories->where('xp', '<', 0)->sum('xp')) }}</div>
            <div class="xh-stat-lbl">XP Dikurangi ({{ $histories->where('xp', '<', 0)->count() }} penalti)</div>
        </div>
    </div>
</div>

{{-- ── HISTORY LIST ─────────────────────────────────── --}}
<div class="xh-master-card">
    <div class="xh-master-header">
        <span><i class="fas fa-history mr-2"></i> Riwayat Transaksi XP</span>
        <small style="color:#a0a8d0;">{{ $histories->total() }} transaksi</small>
    </div>

    <div class="table-responsive">
        <table class="xh-table">
            <thead>
                <tr>
                    <th style="width:10%;padding-left:20px;">XP</th>
                    <th style="width:22%;">Sumber</th>
                    <th style="width:45%;">Keterangan</th>
                    <th style="width:23%;">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $h)
                <tr>
                    <td style="padding-left:20px;">
                        <span class="xh-xp-badge {{ $h->xp > 0 ? 'xp-pos' : 'xp-neg' }}">
                            {{ $h->xp > 0 ? '+' : '' }}{{ $h->xp }}⚡
                        </span>
                    </td>
                    <td>
                        <span class="xh-source-badge">{{ $h->source_type }}</span>
                    </td>
                    <td style="color:#c8d0e0;font-size:.85rem;">{{ $h->description ?? '-' }}</td>
                    <td>
                        <div style="color:#c8d0e0;font-size:.85rem;">{{ $h->created_at->format('d M Y') }}</div>
                        <small style="color:#555;">{{ $h->created_at->format('H:i') }}</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <i class="fas fa-bolt fa-3x mb-3 d-block" style="color:#333;"></i>
                        <p style="color:#555;">Belum ada riwayat XP.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($histories->hasPages())
    <div class="px-4 py-3" style="border-top:1px solid rgba(255,255,255,.06);">
        <div class="d-flex justify-content-center">
            {{ $histories->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
    @endif
</div>
@stop

@section('css')
<style>
/* ── STAT CARDS ────────────────────────────────── */
.xh-stat-card {
    background: linear-gradient(145deg, #1a1a2e, #16213e);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 14px;
    padding: 20px 18px;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: transform .2s;
}
.xh-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--accent, #667eea);
}
.xh-stat-card:hover { transform: translateY(-2px); }
.xh-stat-icon { font-size: 1.5rem; margin-bottom: 8px; color: #667eea; }
.xh-stat-val  { font-size: 1.7rem; font-weight: 800; color: #e0e0ff; line-height: 1; }
.xh-stat-lbl  { font-size: .72rem; color: #8ab4c0; margin-top: 6px; text-transform: uppercase; letter-spacing: .04em; }

/* ── MASTER CARD ───────────────────────────────── */
.xh-master-card {
    background: linear-gradient(145deg, #1a1a2e, #16213e);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 16px;
    overflow: hidden;
}
.xh-master-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid rgba(255,255,255,.07);
    font-weight: 700;
    color: #e0e0ff;
}

/* ── TABLE ─────────────────────────────────────── */
.xh-table { width: 100%; border-collapse: collapse; }
.xh-table thead tr { background: rgba(255,255,255,.03); border-bottom: 1px solid rgba(255,255,255,.07); }
.xh-table th { padding: 10px 12px; font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; color: #8ab4c0; font-weight: 600; }
.xh-table tbody tr { border-bottom: 1px solid rgba(255,255,255,.04); transition: background .15s; }
.xh-table tbody tr:hover { background: rgba(102,126,234,.06); }
.xh-table td { padding: 10px 12px; vertical-align: middle; }

/* ── BADGES ────────────────────────────────────── */
.xh-xp-badge {
    font-size: .78rem; font-weight: 700;
    padding: 3px 10px; border-radius: 20px; display: inline-block;
}
.xp-pos { background: rgba(56,239,125,.12); color: #38ef7d; border: 1px solid rgba(56,239,125,.25); }
.xp-neg { background: rgba(245,87,108,.12); color: #f5576c; border: 1px solid rgba(245,87,108,.25); }

.xh-source-badge {
    font-family: monospace;
    font-size: .78rem;
    padding: 2px 8px;
    background: rgba(102,126,234,.1);
    color: #a0c4ff;
    border: 1px solid rgba(102,126,234,.2);
    border-radius: 6px;
    display: inline-block;
}

/* Pagination dark override */
.pagination .page-link { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); color: #a0a8d0; }
.pagination .page-item.active .page-link { background: #667eea; border-color: #667eea; color: #fff; }
.pagination .page-link:hover { background: rgba(102,126,234,.2); color: #e0e0ff; }
.pagination .page-item.disabled .page-link { background: rgba(255,255,255,.03); color: #555; }
</style>
@stop
