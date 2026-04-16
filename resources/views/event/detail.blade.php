@extends('adminlte::page')

@section('title', $event->name)

@section('content_header')
<div class="d-flex align-items-center gap-2">
    <a href="{{ route('event.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="m-0 fw-bold">🎪 {{ $event->name }}</h1>
        <small style="color:#55596e;">Detail, Peserta & Riwayat Event</small>
    </div>
    <div class="ms-auto d-flex gap-2 ml-auto">
        @canAccess('edit','events')
        <a href="{{ route('event.edit', $event->id) }}" class="btn btn-sm btn-warning">
            <i class="fas fa-pen me-1"></i>Edit
        </a>
        @endcanAccess
    </div>
</div>
@stop

@section('content')
@include('components.alert')

<div class="row g-4">
    {{-- Kiri: Info + Invite + View History --}}
    <div class="col-lg-5">
        {{-- Card Info --}}
        <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="border-radius:16px;background:#16213e;">
            @if($event->image)
            <div style="height:180px;background:url('{{ s3_asset(true,null,$event->image) }}') center/cover no-repeat;position:relative;">
                <div style="position:absolute;inset:0;background:linear-gradient(to bottom,transparent 40%,#16213e 100%);"></div>
            </div>
            @endif
            <div style="height:3px;background:linear-gradient(90deg,{{ $event->color }},{{ $event->color }}88);"></div>
            <div class="p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge {{ $event->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $event->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    @if($event->is_routine)
                    <span class="badge" style="background:rgba(102,126,234,.2);color:#a5b4fc;border:1px solid rgba(102,126,234,.3);">
                        <i class="fas fa-sync-alt me-1"></i>Rutin
                    </span>
                    @endif
                </div>

                <h5 class="fw-bold mb-1" style="color:#e0e0ff;">{{ $event->name }}</h5>

                @if($event->description)
                <div class="html-content mb-3" style="color:#a0a8d0;font-size:.85rem;line-height:1.6;">{!! $event->description !!}</div>
                @endif

                <div class="d-flex flex-column gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-calendar-alt" style="color:{{ $event->color }};width:16px;"></i>
                        <span style="color:#c8d0e0;font-size:.82rem;">
                            {{ $event->start_date->format('d M Y') }}
                            @if($event->start_date->ne($event->end_date))
                                – {{ $event->end_date->format('d M Y') }}
                            @endif
                            <span style="color:#606880;">({{ $event->durationDays() }} hari)</span>
                        </span>
                    </div>
                    @if($event->start_time)
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-clock" style="color:#f093fb;width:16px;"></i>
                        <span style="color:#c8d0e0;font-size:.82rem;">{{ $event->timeRange() }}</span>
                    </div>
                    @endif
                    @if($event->is_routine)
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-sync-alt" style="color:#667eea;width:16px;"></i>
                        <span style="color:#c8d0e0;font-size:.82rem;">
                            Repeat setiap minggu
                            @if($event->routine_end_date)
                                s/d {{ $event->routine_end_date->format('d M Y') }}
                            @else
                                (tanpa batas)
                            @endif
                        </span>
                    </div>
                    @endif
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-user-tie" style="color:#f5a623;width:16px;"></i>
                        <span style="color:#c8d0e0;font-size:.82rem;">Dibuat oleh {{ $event->creator?->name ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Occurrences --}}
        @if($occurrences->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:16px;">
            <div style="height:3px;background:linear-gradient(90deg,#667eea,#38ef7d);border-radius:16px 16px 0 0;"></div>
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#a5b4fc;">
                    <i class="fas fa-layer-group me-2"></i>Jadwal Occurrence
                    <span class="badge ms-1" style="background:rgba(102,126,234,.2);color:#a5b4fc;font-size:.65rem;">{{ $occurrences->count() }}</span>
                </h6>
                <div style="max-height:220px;overflow-y:auto;">
                    @foreach($occurrences as $occ)
                    <div class="d-flex align-items-center gap-2 mb-2 pb-2" style="border-bottom:1px solid rgba(255,255,255,.05);">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $event->color }};flex-shrink:0;"></div>
                        <span style="color:#c8d0e0;font-size:.78rem;">
                            {{ $occ->start_date->format('d M Y') }}
                            @if($occ->start_date->ne($occ->end_date))
                                – {{ $occ->end_date->format('d M Y') }}
                            @endif
                        </span>
                        @if($occ->start_date->lte(now()) && $occ->end_date->gte(now()))
                        <span class="badge ms-auto" style="background:rgba(56,239,125,.2);color:#38ef7d;font-size:.6rem;">Sekarang</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Invite Form --}}
        @canAccess('invite','events')
        <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:16px;">
            <div style="height:3px;background:linear-gradient(90deg,#38ef7d,#667eea);border-radius:16px 16px 0 0;"></div>
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#38ef7d;"><i class="fas fa-user-plus me-2"></i>Undang Peserta</h6>
                <form action="{{ route('event.invite', $event->id) }}" method="POST">
                    @csrf
                    <select name="users[]" id="invite-select" class="form-select gf mb-2" multiple style="min-height:100px;">
                        @foreach($invitableUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm w-100"
                            style="background:rgba(56,239,125,.15);color:#38ef7d;border:1px solid rgba(56,239,125,.3);border-radius:8px;">
                        <i class="fas fa-paper-plane me-1"></i>Undang
                    </button>
                </form>
            </div>
        </div>
        @endcanAccess

        {{-- View History --}}
        <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:16px;">
            <div style="height:3px;background:linear-gradient(90deg,#f5a623,#f093fb);border-radius:16px 16px 0 0;"></div>
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#f5a623;">
                    <i class="fas fa-eye me-2"></i>Riwayat Yang Melihat
                    <span class="badge ms-1" style="background:rgba(245,166,35,.2);color:#f5a623;font-size:.65rem;">{{ $viewHistory->count() }}</span>
                </h6>
                @if($viewHistory->isEmpty())
                <div class="text-center py-3" style="color:#55596e;font-size:.8rem;">Belum ada yang melihat event ini.</div>
                @else
                <div style="max-height:260px;overflow-y:auto;">
                    @foreach($viewHistory as $view)
                    <div class="d-flex align-items-center gap-2 mb-2 pb-2" style="border-bottom:1px solid rgba(255,255,255,.05);">
                        <div style="width:30px;height:30px;border-radius:50%;background:rgba(245,166,35,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="fas fa-user" style="color:#f5a623;font-size:.65rem;"></i>
                        </div>
                        <div class="flex-grow-1" style="min-width:0;">
                            <div style="color:#e0e0ff;font-size:.78rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                {{ $view->user?->name ?? '-' }}
                            </div>
                            <div style="color:#a0a8d0;font-size:.65rem;">
                                {{ $view->occurrence ? $view->occurrence->start_date->format('d M Y') . ' (occurrence)' : '' }}
                                · {{ $view->created_at->format('H:i') }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Kanan: Daftar Peserta --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:16px;">
            <div style="height:3px;background:linear-gradient(90deg,{{ $event->color }},{{ $event->color }}66);border-radius:16px 16px 0 0;"></div>
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#a5b4fc;">
                    <i class="fas fa-users me-2"></i>Daftar Peserta
                    <span class="badge ms-1" style="background:rgba(102,126,234,.2);color:#a5b4fc;font-size:.65rem;">{{ $event->eventUsers->count() }} orang</span>
                </h6>

                @if($event->eventUsers->isEmpty())
                <div class="text-center py-4" style="color:#55596e;font-size:.85rem;">
                    <i class="fas fa-user-slash d-block fa-2x mb-2" style="color:#2d3561;"></i>
                    Belum ada peserta.
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-sm mb-0" style="color:#c8d0e0;">
                        <thead>
                            <tr style="border-bottom:1px solid rgba(255,255,255,.1);font-size:.72rem;color:#a0a8d0;text-transform:uppercase;letter-spacing:.5px;">
                                <th style="background:transparent;border:none;">#</th>
                                <th style="background:transparent;border:none;">Nama</th>
                                <th style="background:transparent;border:none;">Diundang Oleh</th>
                                <th style="background:transparent;border:none;">Tanggal</th>
                                <th style="background:transparent;border:none;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($event->eventUsers as $i => $eu)
                            <tr style="border-bottom:1px solid rgba(255,255,255,.05);">
                                <td style="background:transparent;border:none;color:#606880;font-size:.75rem;">{{ $i + 1 }}</td>
                                <td style="background:transparent;border:none;">
                                    <div style="font-weight:600;font-size:.82rem;color:#e0e0ff;">{{ $eu->user?->name ?? '-' }}</div>
                                    <div style="color:#a0a8d0;font-size:.68rem;">{{ $eu->user?->role?->name ?? '' }}</div>
                                </td>
                                <td style="background:transparent;border:none;font-size:.75rem;color:#a0a8d0;">
                                    {{ $eu->invitedBy?->name ?? '-' }}
                                </td>
                                <td style="background:transparent;border:none;font-size:.72rem;color:#606880;">
                                    {{ $eu->created_at->format('d M Y') }}
                                </td>
                                <td style="background:transparent;border:none;">
                                    @canAccess('removeUser','events')
                                    <form action="{{ route('event.removeUser', [$event->id, $eu->user_id]) }}" method="POST"
                                          onsubmit="return confirm('Hapus peserta ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm px-2 py-0"
                                                style="background:rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.2);border-radius:6px;font-size:.7rem;">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                    @endcanAccess
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.gf { background:#111827!important;border:1px solid rgba(255,255,255,.1)!important;color:#e0e0ff!important;border-radius:8px!important; }
.gf option { background:#111827; }
.select2-container .select2-selection--multiple { background-color:#111827!important;border:1px solid rgba(255,255,255,.1)!important;border-radius:8px!important;min-height:44px; }
.select2-container .select2-search--inline .select2-search__field { color:#e0e0ff!important; }
.select2-container--default .select2-selection--multiple .select2-selection__choice { background-color:rgba(102,126,234,.15)!important;border:1px solid rgba(102,126,234,.3)!important;color:#e0e0ff!important;border-radius:6px; }
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove { color:#ff6b6b!important;border-right:none!important; }
.select2-dropdown { background-color:#16213e!important;border:1px solid rgba(255,255,255,.1)!important;color:#e0e0ff!important; }
.select2-container--default .select2-results__option--highlighted { background-color:rgba(102,126,234,.3)!important;color:#fff!important; }
.select2-search--dropdown .select2-search__field { background-color:#111827!important;border:1px solid rgba(255,255,255,.1)!important;color:#e0e0ff!important; }

/* Quill HTML Content Styles */
.html-content p { margin-bottom: 12px; }
.html-content ul, .html-content ol { margin-left: 20px; margin-bottom: 12px; padding-left: 10px; }
.html-content li { margin-bottom: 6px; }
.html-content h1, .html-content h2, .html-content h3 { color: #e0e0ff; margin-top: 18px; margin-bottom: 10px; font-weight: 700; }
.html-content a { color: #a5b4fc; text-decoration: none; }
.html-content a:hover { text-decoration: underline; }
.html-content strong, .html-content b { color: #e0e0ff; font-weight: 700; }
.html-content blockquote {
    border-left: 3px solid rgba(255,255,255,0.2);
    padding-left: 14px;
    margin: 12px 0;
    color: #a0a8d0;
    font-style: italic;
    background: rgba(255,255,255,.03);
    border-radius: 0 4px 4px 0;
    padding-top: 8px;
    padding-bottom: 8px;
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#invite-select').select2({ placeholder: 'Pilih karyawan...', allowClear: true });
});
</script>
@stop
