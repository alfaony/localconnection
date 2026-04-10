@extends('adminlte::page')

@section('title', 'Master Challenge')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 fw-bold">⚔️ Master Challenge</h1>
        <small style="color:#55596e;">Kelola tantangan & kompetisi karyawan</small>
    </div>
    @canAccess('create','challenges')
    <a href="{{ route('challenge.create') }}" class="btn btn-sm btn-primary mb-1">
        <i class="fas fa-plus-circle mr-1"></i> Challenge Baru
    </a>
    @endcanAccess
</div>
@stop

@section('content')
@include('components.alert')

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:14px;overflow:hidden;">
            <div style="height:3px;background:linear-gradient(90deg,#667eea,#f093fb);"></div>
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:rgba(102,126,234,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-fire" style="color:#667eea;font-size:1.3rem;"></i>
                </div>
                <div>
                    <div style="color:#a0a8d0;font-size:.7rem;font-weight:700;letter-spacing:.05em;">TOTAL CHALLENGE</div>
                    <div style="color:#e0e0ff;font-size:1.5rem;font-weight:700;">{{ $challenges->total() }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:14px;overflow:hidden;">
            <div style="height:3px;background:linear-gradient(90deg,#38ef7d,#11998e);"></div>
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:rgba(56,239,125,.15);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-bolt" style="color:#38ef7d;font-size:1.3rem;"></i>
                </div>
                <div>
                    <div style="color:#a0a8d0;font-size:.7rem;font-weight:700;letter-spacing:.05em;">AKTIF SEKARANG</div>
                    <div style="color:#38ef7d;font-size:1.5rem;font-weight:700;">
                        {{ $challenges->getCollection()->filter(fn($c) => $c->isActive())->count() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:14px;overflow:hidden;">
            <div style="height:3px;background:linear-gradient(90deg,#f5a623,#f093fb);"></div>
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div style="width:48px;height:48px;background:rgba(245,166,35,.15);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-users" style="color:#f5a623;font-size:1.3rem;"></i>
                </div>
                <div>
                    <div style="color:#a0a8d0;font-size:.7rem;font-weight:700;letter-spacing:.05em;">TOTAL PESERTA</div>
                    <div style="color:#f5a623;font-size:1.5rem;font-weight:700;">
                        {{ $challenges->getCollection()->sum('challenge_users_count') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filter Section --}}
<div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:14px;overflow:hidden;">
    <div style="height:3px;background:rgba(255,255,255,0.1);"></div>
    <div class="card-body p-4">
        <form action="{{ route('challenge.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label style="color:#a0a8d0;font-size:0.8rem;margin-bottom:0.4rem;font-weight:600;"><i class="fas fa-search me-1"></i> Cari Challenge</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#a0a8d0;border-right:none;border-radius:8px 0 0 8px;"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" name="search" class="form-control" placeholder="Nama Challenge..." value="{{ request('search') }}" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#e0e0ff;border-left:none;border-radius:0 8px 8px 0;">
                    </div>
                </div>
                <div class="col-md-3">
                    <label style="color:#a0a8d0;font-size:0.8rem;margin-bottom:0.4rem;font-weight:600;"><i class="fas fa-filter me-1"></i> Status</label>
                    <select name="status" class="form-control" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#e0e0ff;border-radius:8px;">
                        <option value="" style="color:#222;">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }} style="color:#222;">Draft</option>
                        <option value="running" {{ request('status') == 'running' ? 'selected' : '' }} style="color:#222;">Running</option>
                        <option value="finish" {{ request('status') == 'finish' ? 'selected' : '' }} style="color:#222;">Finish</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label style="color:#a0a8d0;font-size:0.8rem;margin-bottom:0.4rem;font-weight:600;"><i class="far fa-calendar-alt me-1"></i> Tanggal Challenge</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#a0a8d0;border-right:none;border-radius:8px 0 0 8px;"><i class="far fa-calendar-alt"></i></span>
                        </div>
                        <input type="text" name="date_range" id="daterange" class="form-control" placeholder="Pilih Tanggal" value="{{ request('date_range') }}" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);color:#e0e0ff;border-left:none;border-radius:0 8px 8px 0;">
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1" style="border-radius:8px;font-weight:600;">
                        Tampilkan
                    </button>
                    @if(request()->hasAny(['search', 'status', 'date_range']))
                    <a href="{{ route('challenge.index') }}" class="btn btn-outline-secondary" style="border-radius:8px;" title="Reset Filter">
                        <i class="fas fa-undo"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
{{-- Challenge List --}}
<div class="row g-3">
    @forelse($challenges as $challenge)
    @php
        $color   = $challenge->moduleColor();
        $isActive = $challenge->isActive();
        $isExpired = $challenge->isExpired();
        $isFinished = $challenge->isFinished();
        $statusLabel = $isActive ? 'Aktif' : ($isFinished ? 'Selesai' : 'Belum Mulai');
        $statusClass = $isActive ? 'success' : ($isFinished ? 'primary' : 'warning');
    @endphp
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm challenge-card h-100"
             style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:16px;overflow:hidden;">
            <div style="height:4px;background:linear-gradient(90deg,{{ $color }},#667eea);"></div>
            <div class="card-body p-4">
                {{-- Header --}}
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:40px;height:40px;background:rgba(255,255,255,.07);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                            <i class="{{ $challenge->moduleIcon() }}" style="color:{{ $color }};font-size:1.1rem;"></i>
                        </div>
                        <div>
                            <div style="color:#e0e0ff;font-weight:700;font-size:.95rem;line-height:1.2;">{{ $challenge->name }}</div>
                            <div style="color:#a0a8d0;font-size:.7rem;">{{ $challenge->moduleLabel() }}</div>
                        </div>
                    </div>
                    <span class="badge bg-{{ $statusClass }} rounded-pill" style="font-size:.65rem;">{{ $statusLabel }}</span>
                </div>

                {{-- Dates --}}
                <div class="d-flex align-items-center gap-2 mb-3 mr-1" style="color:#a0a8d0;font-size:.75rem;">
                    <i class="bi bi-calendar-range me-1 mr-1 mb-1"></i>
                    {{ $challenge->start_date->format('d M Y') }} – {{ $challenge->end_date->format('d M Y') }}
                    @if($isActive)
                    <span class="ms-auto" style="color:#f5a623;font-weight:600;">{{ $challenge->daysRemaining() }}h lagi</span>
                    @endif
                </div>

                {{-- Target --}}
                <div class="d-flex align-items-center justify-content-between mb-3"
                     style="background:rgba(255,255,255,.05);border-radius:10px;padding:8px 12px; mr-1 mb-1">
                    <span style="color:#a0a8d0;font-size:.72rem;">TARGET</span>
                    <span style="color:#e0e0ff;font-weight:700;font-size:.88rem;">
                        {{ number_format($challenge->target_count) }}
                        {{ $challenge->module_type === 'score' ? 'XP' : 'kali' }}
                    </span>
                </div>

                {{-- Rewards --}}
                <div class="d-flex gap-2 mb-4">
                    @if($challenge->reward_point > 0)
                    <span class="px-2 py-1 rounded-pill" style="background:rgba(245,166,35,.15);color:#f5a623;border:1px solid rgba(245,166,35,.3);font-size:.7rem;font-weight:600;">
                        <i class="fas fa-coins me-1"></i>+{{ number_format($challenge->reward_point) }} Pts
                    </span>
                    @endif
                    @if($challenge->reward_xp > 0)
                    <span class="px-2 py-1 rounded-pill" style="background:rgba(240,147,251,.15);color:#f093fb;border:1px solid rgba(240,147,251,.3);font-size:.7rem;font-weight:600;">
                        <i class="fas fa-star me-1"></i>+{{ number_format($challenge->reward_xp) }} XP
                    </span>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="d-flex align-items-center justify-content-between">
                    <span style="color:#a0a8d0;font-size:.72rem;">
                        <i class="fas fa-users me-1"></i>{{ $challenge->challenge_users_count }} peserta
                    </span>
                    <div class="d-flex gap-2">
                        @canAccess('show','challenges')
                        <a href="{{ route('challenge.show', $challenge) }}"
                           class="btn btn-sm btn-outline-info mb-1 mr-1" style="border-radius:8px;font-size:.75rem;">
                            <i class="fas fa-eye me-1"></i> Detail
                        </a>
                        @endcanAccess
                        @canAccess('edit','challenges')
                        @if($challenge->isAbles())
                        <a href="{{ route('challenge.edit', $challenge) }}"
                           class="btn btn-sm btn-outline-warning mb-1 mr-1" style="border-radius:8px;font-size:.75rem;">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endif
                        @endcanAccess
                        @canAccess('destroy','challenges')
                        @if($challenge->isAbles())
                        <form action="{{ route('challenge.destroy', $challenge) }}" method="POST"
                              onsubmit="return confirm('Hapus challenge \'{{ $challenge->name }}\'?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger mb-1 mr-1" style="border-radius:8px;font-size:.75rem;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                        @endcanAccess
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5" style="color:#a0a8d0;">
            <div style="font-size:3.5rem;">⚔️</div>
            <p class="mt-2 mb-3">Belum ada challenge. Buat tantangan pertama!</p>
            @canAccess('create','challenges')
            <a href="{{ route('challenge.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">
                <i class="fas fa-plus-circle me-1"></i> Buat Challenge
            </a>
            @endcanAccess
        </div>
    </div>
    @endforelse
</div>

<div class="mt-4">{{ $challenges->links() }}</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style>
.challenge-card { transition: transform .25s, box-shadow .25s; }
.challenge-card:hover { transform: translateY(-5px); box-shadow: 0 14px 36px rgba(0,0,0,.25) !important; }
</style>
@stop

@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(document).ready(function() {
    $('#daterange').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear',
            format: 'YYYY-MM-DD'
        }
    });

    $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
    });

    $('#daterange').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
</script>
@stop
