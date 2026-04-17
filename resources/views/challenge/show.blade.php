@extends('adminlte::page')

@section('title', $challenge->name)

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="m-0 fw-bold">⚔️ {{ $challenge->name }}</h1>
        <small style="color:#55596e;">{{ $challenge->moduleLabel() }} &middot; {{ $challenge->start_date->format('d M Y') }} – {{ $challenge->end_date->format('d M Y') }}</small>
    </div>
    <div class="d-flex gap-2">
        @canAccess('edit','challenges')
        @if($challenge->isAbles())
        <a href="{{ route('challenge.edit', $challenge) }}" class="btn btn-sm btn-outline-warning mb-1 mr-1">
            <i class="fas fa-edit mr-1"></i> Edit
        </a>
        @endif
        @endcanAccess
        <a href="{{ route('challenge.index') }}" class="btn btn-sm btn-outline-secondary mb-1">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
</div>
@stop

@section('content')
@include('components.alert')

@php $color = $challenge->moduleColor(); @endphp

<div class="row g-4">

    {{-- ── LEFT: Info + Invite ── --}}
    <div class="col-lg-4">

        {{-- Challenge Info Card --}}
        <div class="card border-0 shadow-sm mb-3" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:16px;overflow:hidden;">
            <div style="height:4px;background:linear-gradient(90deg,{{ $color }},#667eea);"></div>
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <div style="width:64px;height:64px;background:rgba(255,255,255,.07);border-radius:18px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                        <i class="{{ $challenge->moduleIcon() }}" style="color:{{ $color }};font-size:1.8rem;"></i>
                    </div>
                    @php
                        $dbStatus = ucfirst($challenge->status);
                        $statusClass = $challenge->status === 'running' ? 'success' : ($challenge->status === 'finish' ? 'primary' : 'warning');
                        $isActive  = $challenge->isActive();
                    @endphp
                    <span class="badge bg-{{ $statusClass }} rounded-pill px-3 py-1">{{ $dbStatus }}</span>
                    @if($challenge->status === 'running')
                    <div class="mt-1" style="color:#f5a623;font-size:.8rem;font-weight:600;">
                        <i class="bi bi-clock me-1"></i>{{ $challenge->daysRemaining() }} hari lagi
                    </div>
                    @endif
                </div>

                <div class="mb-2" style="background:rgba(255,255,255,.05);border-radius:10px;padding:10px 14px;">
                    <div class="d-flex justify-content-between mb-2">
                        <small style="color:#a0a8d0;">Tipe Modul</small>
                        <span style="color:#c8d0e0;font-weight:600;font-size:.85rem;">{{ $challenge->moduleLabel() }}</span>
                    </div>
                    @if($challenge->events->isNotEmpty())
                    <div class="d-flex justify-content-between mb-2">
                        <small style="color:#a0a8d0;">Event</small>
                        <span style="color:#c8d0e0;font-weight:600;font-size:.85rem;">{{ $challenge->events->pluck('name')->implode(', ') }}</span>
                    </div>
                    @endif
                    <div class="d-flex justify-content-between">
                        <small style="color:#a0a8d0;">Target</small>
                        <small class="fw-bold" style="color:#e0e0ff;">
                            {{ number_format($challenge->target_count) }} {{ $challenge->module_type === 'score' ? 'XP' : 'kali' }}
                        </small>
                    </div>
                </div>

                @if($challenge->description)
                <p style="color:#a0a8d0;font-size:.8rem;margin-top:10px;">{{ $challenge->description }}</p>
                @endif

                <div class="d-flex gap-2 mt-3 flex-wrap gap-2">
                    @if($challenge->reward_point > 0)
                    <span class="px-3 py-1 rounded-pill mb-1 mr-2" style="background:rgba(245,166,35,.15);color:#f5a623;border:1px solid rgba(245,166,35,.3);font-size:.75rem;font-weight:600;">
                        <i class="fas fa-coins me-1"></i>+{{ number_format($challenge->reward_point) }} Pts
                    </span>
                    @endif
                    @if($challenge->reward_xp > 0)
                    <span class="px-3 py-1 rounded-pill mb-1 mr-1" style="background:rgba(240,147,251,.15);color:#f093fb;border:1px solid rgba(240,147,251,.3);font-size:.75rem;font-weight:600;">
                        <i class="fas fa-star me-1"></i>+{{ number_format($challenge->reward_xp) }} XP
                    </span>
                    @endif
                </div>

                <div class="mt-3" style="color:#a0a8d0;font-size:.72rem;">
                    Dibuat oleh: <span style="color:#c8d0e0;">{{ $challenge->createdBy->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Invite Card --}}
        @canAccess('invite','challenges')
        @php $hasInvitable = $groupedInvitable->isNotEmpty() || $invitableNoDivision->isNotEmpty(); @endphp
        @if($hasInvitable && !$challenge->isFinished())
        <div class="card border-0 shadow-sm" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:16px;overflow:hidden;">
            <div style="height:3px;background:linear-gradient(90deg,#38ef7d,#4facfe);"></div>
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#c8d0e0;"><i class="fas fa-user-plus me-2" style="color:#38ef7d;"></i> Invite Peserta</h6>
                <form action="{{ route('challenge.invite', $challenge) }}" method="POST">
                    @csrf
                    @include('components.user-select-grouped', [
                        'selectName'      => 'user_ids[]',
                        'selectId'        => 'invite-challenge-select',
                        'groupedUsers'    => $groupedInvitable,
                        'usersNoDivision' => $invitableNoDivision,
                        'selectedIds'     => [],
                    ])
                    @error('user_ids')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                    <button type="submit" class="btn w-100 rounded-pill fw-bold py-2 mt-3"
                            style="background:linear-gradient(90deg,#38ef7d,#11998e);border:none;color:#1a1a2e;font-size:.85rem;">
                        <i class="fas fa-paper-plane me-1"></i> Kirim Invite
                    </button>
                </form>
            </div>
        </div>
        @endif
        @endcanAccess

    </div>

    {{-- ── RIGHT: Leaderboard ── --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:16px;overflow:hidden;">
            <div style="height:3px;background:linear-gradient(90deg,#f5a623,#f093fb,#667eea);"></div>
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4" style="color:#c8d0e0;">
                    <i class="fas fa-trophy me-2" style="color:#f5a623;"></i>
                    Leaderboard Peserta
                    <span class="ms-2 badge rounded-pill" style="background:rgba(255,255,255,.1);color:#a0a8d0;font-size:.65rem;font-weight:600;">{{ count($participants) }} orang</span>
                </h6>

                @if($participants->isEmpty())
                <div class="text-center py-5" style="color:#606880;">
                    <div style="font-size:2.5rem;">👥</div>
                    <small>Belum ada peserta. Invite dulu!</small>
                </div>
                @else
                <div class="d-flex flex-column gap-3">
                    @foreach($participants as $i => $p)
                    @php
                        $rankIcon = match($i) {
                            0 => '🥇', 1 => '🥈', 2 => '🥉',
                            default => '<span style="color:#a0a8d0;font-size:.8rem;min-width:24px;text-align:center;display:inline-block;">'.($i+1).'</span>'
                        };
                        $barColor = $p['percent'] >= 100 ? '#38ef7d' : ($p['percent'] >= 50 ? $color : '#4facfe');
                    @endphp
                    <div class="participant-row" style="background:rgba(255,255,255,.04);border-radius:12px;padding:12px 16px;">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <span style="font-size:1.2rem;min-width:28px;">{!! $rankIcon !!}</span>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span style="color:#e0e0ff;font-weight:600;font-size:.88rem;">{{ $p['user']->name ?? '-' }}</span>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="mb-1 mr-1" style="color:#a0a8d0;font-size:.75rem;">
                                            {{ number_format($p['current']) }} / {{ number_format($challenge->target_count) }}
                                        </span>
                                        <span class="fw-bold mb-1 mr-1" style="color:{{ $p['percent'] >= 100 ? '#38ef7d' : '#f5a623' }};font-size:.8rem;">{{ $p['percent'] }}%</span>
                                        @if(!is_null($p['cu']->finished_at) || $p['percent'] >= 100)
                                        <span class="badge" style="background:rgba(56,239,125,.2);color:#38ef7d;font-size:.62rem;border:1px solid rgba(56,239,125,.3);">
                                            <i class="fas fa-check me-1"></i>Berhasil ✓
                                        </span>
                                        @elseif($challenge->isExpired())
                                        <span class="badge" style="background:rgba(245,87,108,.2);color:#f5576c;font-size:.62rem;border:1px solid rgba(245,87,108,.3);">
                                            <i class="fas fa-times me-1"></i>Gagal
                                        </span>
                                        @else
                                        <span class="badge" style="background:rgba(255,255,255,.1);color:#a0a8d0;font-size:.62rem;border:1px solid rgba(255,255,255,.2);">
                                            <i class="fas fa-hourglass-half me-1"></i>Proses
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Progress Bar --}}
                        <div style="height:6px;background:rgba(255,255,255,.08);border-radius:4px;overflow:hidden;margin-left:40px;">
                            <div style="height:100%;width:{{ $p['percent'] }}%;background:linear-gradient(90deg,{{ $barColor }},{{ $color }});border-radius:4px;transition:width .8s ease;"></div>
                        </div>
                        {{-- Remove --}}
                        @canAccess('removeUser','challenges')
                        @if(!$p['cu']->reward_given)
                        <div class="text-end mt-1">
                            <form action="{{ route('challenge.removeUser', [$challenge, $p['user']->id]) }}" method="POST"
                                  onsubmit="return confirm('Keluarkan {{ $p['user']->name }} dari challenge?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm mt-1 btn-danger" style="font-size:.65rem;padding:0 4px;">
                                    <i class="fas fa-times me-1"></i> Keluarkan
                                </button>
                            </form>
                        </div>
                        @endif
                        @endcanAccess
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
@include('components.user-select-grouped-assets')
<style>
.gf { background:rgba(255,255,255,.07)!important;border:1px solid rgba(255,255,255,.15)!important;color:#e0e0ff!important;border-radius:10px!important; }
.gf:focus { border-color:rgba(102,126,234,.6)!important; }
.participant-row { transition: background .2s; }
.participant-row:hover { background:rgba(255,255,255,.07) !important; }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@include('components.user-select-grouped-js', ['userSelectIds' => ['invite-challenge-select']])
@stop
