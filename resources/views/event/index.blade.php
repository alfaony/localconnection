@extends('adminlte::page')

@section('title', 'Master Event')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 fw-bold">🎪 Master Event</h1>
        <small style="color:#55596e;">Kelola event & agenda perusahaan</small>
    </div>
    @canAccess('create','events')
    <a href="{{ route('event.create') }}" class="btn btn-sm btn-primary mb-1">
        <i class="fas fa-plus-circle mr-1"></i> Event Baru
    </a>
    @endcanAccess
</div>
@stop

@section('content')
@include('components.alert')

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:14px;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('event.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control form-control-sm gf" placeholder="Cari nama event...">
            </div>
            <div class="col-md-3">
                <select name="is_routine" class="form-control form-select-sm gf">
                    <option value="">Semua Tipe</option>
                    <option value="0" @selected(request('is_routine') === '0')>Tanggal Tertentu</option>
                    <option value="1" @selected(request('is_routine') === '1')>Rutin (Repeat)</option>
                </select>
            </div>
            <div class="col-md-auto ml-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filter</button>
                <a href="{{ route('event.index') }}" class="btn btn-sm btn-secondary ms-1">Reset</a>
            </div>
        </form>
    </div>
</div>

@if($events->isEmpty())
<div class="text-center py-5" style="color:#55596e;">
    <i class="fas fa-calendar-times fa-3x mb-3 d-block" style="color:#2d3561;"></i>
    Belum ada event. <a href="{{ route('event.create') }}">Buat sekarang</a>
</div>
@else
<div class="row g-3 mb-3">
    @foreach($events as $event)
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden"
             style="border-radius:16px;background:#16213e;">
            {{-- Top accent menggunakan warna event --}}
            <div style="height:3px;background:linear-gradient(90deg,{{ $event->color }},{{ $event->color }}88);"></div>

            <div class="card-body p-3">
                {{-- Header --}}
                <div class="d-flex align-items-start gap-2 mb-2">
                    {{-- Color dot --}}
                    <div style="width:10px;height:10px;border-radius:50%;background:{{ $event->color }};flex-shrink:0;margin-top:5px;"></div>
                    <div class="flex-grow-1" style="min-width:0;">
                        <div class="fw-bold" style="color:#e0e0ff;font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $event->name }}
                        </div>
                        <div style="color:#a0a8d0;font-size:.68rem;">
                            @if($event->is_routine)
                                <i class="fas fa-sync-alt me-1"></i>Rutin
                                @if($event->routine_end_date)
                                    s/d {{ $event->routine_end_date->format('d M Y') }}
                                @endif
                            @else
                                <i class="fas fa-calendar-day me-1"></i>
                                {{ $event->start_date->format('d M Y') }}
                                @if($event->start_date->ne($event->end_date))
                                    – {{ $event->end_date->format('d M Y') }}
                                @endif
                            @endif
                        </div>
                    </div>
                    <span class="badge {{ $event->is_active ? 'bg-success' : 'bg-secondary' }}" style="font-size:.62rem;flex-shrink:0;">
                        {{ $event->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                {{-- Meta chips --}}
                <div class="d-flex gap-2 flex-wrap mb-3">
                    @if($event->start_time)
                    <span class="px-2 py-1 rounded-pill" style="background:rgba(102,126,234,.15);color:#a5b4fc;border:1px solid rgba(102,126,234,.3);font-size:.65rem;">
                        <i class="fas fa-clock me-1"></i>{{ $event->timeRange() }}
                    </span>
                    @endif
                    <span class="px-2 py-1 rounded-pill" style="background:rgba(255,255,255,.06);color:#a0a8d0;border:1px solid rgba(255,255,255,.1);font-size:.65rem;">
                        <i class="fas fa-users me-1"></i>{{ $event->event_users_count }} peserta
                    </span>
                    <span class="px-2 py-1 rounded-pill" style="background:rgba(255,255,255,.06);color:#a0a8d0;border:1px solid rgba(255,255,255,.1);font-size:.65rem;">
                        <i class="fas fa-layer-group me-1"></i>{{ $event->occurrences_count }} occurrence
                    </span>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2">
                    @canAccess('show','events')
                    <a href="{{ route('event.detail', $event->id) }}"
                       class="btn btn-sm flex-grow-1 mb-1 mr-1 "
                       style="background:rgba(102,126,234,.2);color:#a5b4fc;border:1px solid rgba(102,126,234,.3);font-size:.75rem;">
                        <i class="fas fa-eye me-1"></i>Detail
                    </a>
                    @endcanAccess
                    @canAccess('edit','events')
                    <a href="{{ route('event.edit', $event->id) }}"
                       class="btn btn-sm mb-1 mr-1 "
                       style="background:rgba(245,166,35,.15);color:#f5a623;border:1px solid rgba(245,166,35,.3);font-size:.75rem;">
                        <i class="fas fa-pen"></i>
                    </a>
                    @endcanAccess
                    @canAccess('destroy','events')
                    <form action="{{ route('event.destroy', $event->id) }}" method="POST"
                          onsubmit="return confirm('Hapus event ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm mb-1"
                                style="background:rgba(248,113,113,.15);color:#f87171;border:1px solid rgba(248,113,113,.3);font-size:.75rem;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endcanAccess
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
{{ $events->links() }}
@endif
@stop

@section('css')
<style>
.gf { background:#111827!important;border:1px solid rgba(255,255,255,.1)!important;color:#e0e0ff!important;border-radius:8px!important; }
.gf::placeholder { color:#55596e!important; }
.gf option { background:#111827; }
</style>
@stop
