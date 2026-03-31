@extends('adminlte::page')

@section('title', 'Leaderboard XP')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 fw-bold" style="color:#e0e0ff;">🏆 Leaderboard XP</h1>
        <small style="color:#a0a8d0;">Ranking karyawan berdasarkan total experience points</small>
    </div>

    @canAccess('myHistory','employee_xps')
    <a href="{{ route('employee-xp.my-history') }}" class="btn btn-sm" style="background:rgba(102,126,234,.15);color:#667eea;border:1px solid rgba(102,126,234,.3);">
        <i class="fas fa-history mr-1"></i> Riwayat Saya
    </a>
    @endcanAccess
</div>
@stop

@section('content')
@include('components.alert')

@php
    $topUsers   = $users->getCollection()->take(3);
    $currentPage = $users->currentPage();
    $maxXp      = $users->first()->total_xp ?? 1;
@endphp

{{-- ── PODIUM TOP 3 ───────────────────────────────── --}}
@if($currentPage === 1 && $topUsers->count() >= 3)
<div class="xl-podium-wrap mb-4">
    {{-- Rank 2 --}}
    <div class="xl-podium-slot" style="order:1;">
        @php $u2 = $topUsers->get(1); @endphp
        <div class="xl-podium-card rank-2">
            <div class="xl-podium-rank">🥈</div>
            <div class="xl-podium-avatar" style="background:linear-gradient(135deg,#9e9e9e,#616161);">
                {{ strtoupper(substr($u2->name, 0, 1)) }}
            </div>
            <div class="xl-podium-name">{{ $u2->name }}</div>
            <div class="xl-podium-xp">{{ number_format($u2->total_xp) }} XP</div>
            @php $lv2 = \App\Helpers\XpHelper::level($u2->total_xp); @endphp
            <div class="xl-podium-badge">{{ $lv2['badge'] }}</div>
            <div class="xl-podium-base" style="height:60px;background:linear-gradient(180deg,rgba(158,158,158,.3),rgba(97,97,97,.2));"></div>
        </div>
    </div>

    {{-- Rank 1 --}}
    <div class="xl-podium-slot" style="order:0;">
        @php $u1 = $topUsers->get(0); @endphp
        <div class="xl-podium-card rank-1">
            <div class="xl-podium-rank">🥇</div>
            <div class="xl-podium-avatar" style="background:linear-gradient(135deg,#f5a623,#e65100);width:72px;height:72px;font-size:1.5rem;">
                {{ strtoupper(substr($u1->name, 0, 1)) }}
            </div>
            <div class="xl-podium-name" style="font-size:1rem;">{{ $u1->name }}</div>
            <div class="xl-podium-xp" style="color:#f5a623;font-size:.9rem;">{{ number_format($u1->total_xp) }} XP</div>
            @php $lv1 = \App\Helpers\XpHelper::level($u1->total_xp); @endphp
            <div class="xl-podium-badge" style="font-size:1.5rem;">{{ $lv1['badge'] }}</div>
            <div class="xl-podium-base" style="height:90px;background:linear-gradient(180deg,rgba(245,166,35,.3),rgba(230,81,0,.15));"></div>
        </div>
    </div>

    {{-- Rank 3 --}}
    <div class="xl-podium-slot" style="order:2;">
        @php $u3 = $topUsers->get(2); @endphp
        <div class="xl-podium-card rank-3">
            <div class="xl-podium-rank">🥉</div>
            <div class="xl-podium-avatar" style="background:linear-gradient(135deg,#cd7f32,#8d5524);">
                {{ strtoupper(substr($u3->name, 0, 1)) }}
            </div>
            <div class="xl-podium-name">{{ $u3->name }}</div>
            <div class="xl-podium-xp">{{ number_format($u3->total_xp) }} XP</div>
            @php $lv3 = \App\Helpers\XpHelper::level($u3->total_xp); @endphp
            <div class="xl-podium-badge">{{ $lv3['badge'] }}</div>
            <div class="xl-podium-base" style="height:45px;background:linear-gradient(180deg,rgba(205,127,50,.3),rgba(141,85,36,.15));"></div>
        </div>
    </div>
</div>
@endif

{{-- ── MY RANK ─────────────────────────────────────── --}}
<div class="xl-myrank-card mb-4">
    <div class="d-flex align-items-center">
        <div class="xl-myrank-icon">⚡</div>
        <div class="flex-grow-1 px-3">
            <div style="color:#e0e0ff;font-weight:700;">Posisi Anda: <span style="color:#667eea;">#{{ $myRank }}</span></div>
            <div style="color:#8ab4c0;font-size:.85rem;">Total XP: <strong style="color:#f5a623;">{{ number_format(auth()->user()->total_xp ?? 0) }}</strong> poin</div>
        </div>
        @php $myLevel = \App\Helpers\XpHelper::level(auth()->user()->total_xp ?? 0); @endphp
        <div class="text-right">
            <span style="font-size:1.3rem;">{{ $myLevel['badge'] }}</span>
            <div style="color:{{ $myLevel['color'] }};font-size:.78rem;font-weight:600;">{{ $myLevel['label'] }}</div>
        </div>
    </div>
</div>

{{-- ── FULL TABLE ────────────────────────────────────── --}}
<div class="xl-master-card">
    <div class="xl-master-header">
        <span><i class="fas fa-list-ol mr-2"></i> Semua Ranking</span>
        <small style="color:#a0a8d0;">{{ $users->total() }} karyawan</small>
    </div>

    <div class="table-responsive">
        <table class="xl-table">
            <thead>
                <tr>
                    <th style="width:8%;text-align:center;">#</th>
                    <th style="width:42%;">Nama Karyawan</th>
                    <th style="width:32%;">Total XP</th>
                    <th style="width:18%;text-align:right;padding-right:20px;">Level</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $user)
                @php
                    $rank  = ($users->currentPage() - 1) * $users->perPage() + $index + 1;
                    $isMe  = $user->id === auth()->id();
                    $level = \App\Helpers\XpHelper::level($user->total_xp);
                    $pct   = min(100, ($user->total_xp / $maxXp) * 100);
                @endphp
                <tr class="{{ $isMe ? 'row-me' : '' }}">
                    <td style="text-align:center;">
                        @if($rank === 1)<span style="font-size:1.1rem;">🥇</span>
                        @elseif($rank === 2)<span style="font-size:1.1rem;">🥈</span>
                        @elseif($rank === 3)<span style="font-size:1.1rem;">🥉</span>
                        @else<span style="color:#555;font-weight:700;font-size:.88rem;">{{ $rank }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="xl-avatar mr-3">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                            <div>
                                <div style="color:#e0e0ff;font-weight:{{ $isMe ? '700' : '600' }};font-size:.88rem;">
                                    {{ $user->name }}
                                    @if($isMe)<span class="xl-me-badge">Anda</span>@endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="xl-bar-wrap">
                                <div class="xl-bar-fill" style="width:{{ $pct }}%;"></div>
                            </div>
                            <span style="color:#e0e0ff;font-weight:700;font-size:.88rem;min-width:55px;">{{ number_format($user->total_xp) }}</span>
                            <span style="color:#555;font-size:.75rem;">XP</span>
                        </div>
                    </td>
                    <td style="text-align:right;padding-right:20px;">
                        <span style="color:{{ $level['color'] }};font-size:.83rem;font-weight:600;">
                            {{ $level['badge'] }} {{ $level['label'] }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5">
                        <i class="fas fa-trophy fa-3x mb-3 d-block" style="color:#333;"></i>
                        <p style="color:#555;">Belum ada data XP.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="px-4 py-3" style="border-top:1px solid rgba(255,255,255,.06);">
        <div class="d-flex justify-content-center">
            {{ $users->links('vendor.pagination.bootstrap-4') }}
        </div>
    </div>
    @endif
</div>
@stop

@section('css')
<style>
/* ── PODIUM ────────────────────────────────────── */
.xl-podium-wrap {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    gap: 16px;
}
.xl-podium-slot { display: flex; }
.xl-podium-card {
    background: linear-gradient(145deg, #1a1a2e, #16213e);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 14px;
    overflow: hidden;
    text-align: center;
    width: 160px;
    transition: transform .2s;
}
.xl-podium-card:hover { transform: translateY(-4px); }
.xl-podium-card.rank-1 {
    width: 180px;
    border-color: rgba(245,166,35,.3);
    box-shadow: 0 0 24px rgba(245,166,35,.15);
}
.xl-podium-card.rank-2 { border-color: rgba(158,158,158,.2); }
.xl-podium-card.rank-3 { border-color: rgba(205,127,50,.2); }
.xl-podium-rank { font-size: 1.6rem; padding: 14px 0 4px; }
.xl-podium-avatar {
    width: 58px; height: 58px;
    border-radius: 50%;
    color: #fff; font-weight: 800; font-size: 1.2rem;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,.4);
}
.xl-podium-name { color: #e0e0ff; font-weight: 700; font-size: .88rem; padding: 0 10px; }
.xl-podium-xp   { color: #8ab4c0; font-size: .78rem; margin: 3px 0 8px; }
.xl-podium-badge { font-size: 1.2rem; margin-bottom: 10px; }
.xl-podium-base { width: 100%; }

/* ── MY RANK CARD ──────────────────────────────── */
.xl-myrank-card {
    background: linear-gradient(145deg, #1a1a2e, #16213e);
    border: 1px solid rgba(102,126,234,.2);
    border-left: 4px solid #667eea;
    border-radius: 12px;
    padding: 14px 18px;
}
.xl-myrank-icon { font-size: 1.8rem; }

/* ── MASTER CARD ───────────────────────────────── */
.xl-master-card {
    background: linear-gradient(145deg, #1a1a2e, #16213e);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 16px;
    overflow: hidden;
}
.xl-master-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid rgba(255,255,255,.07);
    font-weight: 700;
    color: #e0e0ff;
}

/* ── TABLE ─────────────────────────────────────── */
.xl-table { width: 100%; border-collapse: collapse; }
.xl-table thead tr { background: rgba(255,255,255,.03); border-bottom: 1px solid rgba(255,255,255,.07); }
.xl-table th { padding: 10px 12px; font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; color: #8ab4c0; font-weight: 600; }
.xl-table tbody tr { border-bottom: 1px solid rgba(255,255,255,.04); transition: background .15s; }
.xl-table tbody tr:hover { background: rgba(102,126,234,.06); }
.xl-table tbody tr.row-me { background: rgba(102,126,234,.1); }
.xl-table tbody tr.row-me:hover { background: rgba(102,126,234,.15); }
.xl-table td { padding: 10px 12px; vertical-align: middle; }

/* ── AVATAR ────────────────────────────────────── */
.xl-avatar {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff; font-weight: 700; font-size: .8rem;
    display: flex; align-items: center; justify-content: center;
}
.xl-me-badge {
    display: inline-block;
    font-size: .65rem;
    padding: 1px 6px;
    background: rgba(102,126,234,.3);
    color: #a0c4ff;
    border-radius: 10px;
    font-weight: 600;
    margin-left: 4px;
}

/* ── XP BAR ────────────────────────────────────── */
.xl-bar-wrap {
    width: 90px; height: 5px;
    background: rgba(255,255,255,.07);
    border-radius: 3px;
    overflow: hidden;
    flex-shrink: 0;
}
.xl-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    border-radius: 3px;
    transition: width .4s ease;
}

/* Pagination dark override */
.pagination .page-link { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); color: #a0a8d0; }
.pagination .page-item.active .page-link { background: #667eea; border-color: #667eea; color: #fff; }
.pagination .page-link:hover { background: rgba(102,126,234,.2); color: #e0e0ff; }
.pagination .page-item.disabled .page-link { background: rgba(255,255,255,.03); color: #555; }
</style>
@stop
