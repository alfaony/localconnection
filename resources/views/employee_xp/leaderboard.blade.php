@extends('adminlte::page')

@section('title', 'Leaderboard XP')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark">🏆 Leaderboard XP</h1>
            <small class="text-muted">Ranking karyawan berdasarkan total experience points</small>
        </div>
        <a href="{{ route('employee-xp.my-history') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-history mr-1"></i> Riwayat Saya
        </a>
    </div>
@stop

@section('content')

{{-- Podium Top 3 --}}
@php
    $topUsers = $users->getCollection()->take(3);
    $currentPage = $users->currentPage();
@endphp

@if($currentPage === 1 && $topUsers->count() >= 3)
<div class="row mb-4 justify-content-center align-items-end">
    {{-- Rank 2 --}}
    <div class="col-md-3 text-center">
        @php $u2 = $topUsers->get(1); @endphp
        <div class="card shadow border-0 mb-2" style="border-radius: 16px; background: linear-gradient(135deg, #e0e0e0, #bdbdbd);">
            <div class="card-body py-4">
                <div class="rounded-circle mx-auto text-white font-weight-bold d-flex align-items-center justify-content-center shadow"
                     style="width:64px;height:64px;background:linear-gradient(135deg,#9e9e9e,#757575);font-size:1.4rem;">
                    {{ strtoupper(substr($u2->name, 0, 1)) }}
                </div>
                <div class="mt-2 font-weight-bold text-dark" style="font-size:.9rem;">{{ $u2->name }}</div>
                <div class="text-muted" style="font-size:.75rem;">{{ number_format($u2->total_xp) }} XP</div>
                <div class="mt-2" style="font-size: 1.8rem;">🥈</div>
            </div>
        </div>
    </div>

    {{-- Rank 1 --}}
    <div class="col-md-3 text-center">
        @php $u1 = $topUsers->get(0); @endphp
        <div class="card shadow border-0 mb-2" style="border-radius: 16px; background: linear-gradient(135deg, #FFD700, #FFC200); transform: scale(1.05);">
            <div class="card-body py-4">
                <div class="rounded-circle mx-auto text-white font-weight-bold d-flex align-items-center justify-content-center shadow"
                     style="width:72px;height:72px;background:linear-gradient(135deg,#f57c00,#e65100);font-size:1.6rem;">
                    {{ strtoupper(substr($u1->name, 0, 1)) }}
                </div>
                <div class="mt-2 font-weight-bold text-dark" style="font-size:1rem;">{{ $u1->name }}</div>
                <div class="text-dark" style="font-size:.8rem; font-weight:600;">{{ number_format($u1->total_xp) }} XP</div>
                <div class="mt-2" style="font-size: 2rem;">🥇</div>
            </div>
        </div>
    </div>

    {{-- Rank 3 --}}
    <div class="col-md-3 text-center">
        @php $u3 = $topUsers->get(2); @endphp
        <div class="card shadow border-0 mb-2" style="border-radius: 16px; background: linear-gradient(135deg, #FFCC80, #FFA726);">
            <div class="card-body py-4">
                <div class="rounded-circle mx-auto text-white font-weight-bold d-flex align-items-center justify-content-center shadow"
                     style="width:64px;height:64px;background:linear-gradient(135deg,#795548,#5d4037);font-size:1.4rem;">
                    {{ strtoupper(substr($u3->name, 0, 1)) }}
                </div>
                <div class="mt-2 font-weight-bold text-dark" style="font-size:.9rem;">{{ $u3->name }}</div>
                <div class="text-dark" style="font-size:.75rem;">{{ number_format($u3->total_xp) }} XP</div>
                <div class="mt-2" style="font-size: 1.8rem;">🥉</div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Ranking Anda --}}
<div class="alert shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea22, #764ba222); border-left: 4px solid #667eea; border-radius: 8px;">
    <div class="d-flex align-items-center">
        <span style="font-size: 1.8rem; margin-right: 12px;">⚡</span>
        <div>
            <div class="font-weight-bold text-dark">Posisi Anda: #{{ $myRank }}</div>
            <div class="text-muted" style="font-size: .85rem;">Total XP Anda: <strong>{{ number_format(auth()->user()->total_xp ?? 0) }}</strong> poin</div>
        </div>
    </div>
</div>

{{-- Tabel Lengkap --}}
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0 font-weight-bold">Semua Ranking</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-4 text-center" style="width: 8%">#</th>
                        <th style="width: 45%">Nama Karyawan</th>
                        <th style="width: 25%">Total XP</th>
                        <th style="width: 22%" class="text-right pr-4">Level</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                    @php
                        $rank = ($users->currentPage() - 1) * $users->perPage() + $index + 1;
                        $isMe = $user->id === auth()->id();
                        $level = match(true) {
                            $user->total_xp >= 5000  => ['Diamond', 'text-info',    '💎'],
                            $user->total_xp >= 2000  => ['Platinum', 'text-primary', '🔮'],
                            $user->total_xp >= 1000  => ['Gold',     'text-warning', '🌟'],
                            $user->total_xp >= 500   => ['Silver',   'text-secondary','⭐'],
                            default                  => ['Bronze',   'text-muted',   '🔶'],
                        };
                    @endphp
                    <tr class="{{ $isMe ? 'table-primary' : '' }}">
                        <td class="pl-4 text-center">
                            @if($rank === 1) 🥇
                            @elseif($rank === 2) 🥈
                            @elseif($rank === 3) 🥉
                            @else
                                <span class="text-muted font-weight-bold">{{ $rank }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle text-white font-weight-bold d-flex align-items-center justify-content-center mr-3"
                                     style="width:34px;height:34px;background:linear-gradient(135deg,#667eea,#764ba2);flex-shrink:0;font-size:.8rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-weight-{{ $isMe ? 'bold' : 'semibold' }} text-dark">
                                        {{ $user->name }}
                                        @if($isMe)<span class="badge badge-primary ml-1 px-2" style="font-size:.7rem;">Anda</span>@endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="mr-2" style="width: 100px; height: 6px; background: #e9ecef; border-radius: 3px; overflow: hidden;">
                                    @php $pct = min(100, ($user->total_xp / max(1, $users->first()->total_xp)) * 100); @endphp
                                    <div style="width: {{ $pct }}%; height: 100%; background: linear-gradient(90deg, #667eea, #764ba2); border-radius: 3px;"></div>
                                </div>
                                <span class="font-weight-bold text-dark">{{ number_format($user->total_xp) }}</span>
                                <span class="text-muted ml-1" style="font-size:.75rem;">XP</span>
                            </div>
                        </td>
                        <td class="text-right pr-4">
                            <span class="{{ $level[1] }} font-weight-semibold" style="font-size:.85rem;">
                                {{ $level[2] }} {{ $level[0] }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="fas fa-trophy fa-2x mb-2 d-block"></i>
                            Belum ada data XP.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="card-footer bg-light py-2">
            <div class="d-flex justify-content-center">
                {{ $users->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    .table-primary td { background-color: #e8eaff !important; }
</style>
@stop
