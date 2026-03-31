@extends('adminlte::page')

@section('title', 'Master XP')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 fw-bold" style="color:#e0e0ff;">⚡ Master XP</h1>
        <small style="color:#a0a8d0;">Kelola konfigurasi & statistik Experience Points</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('employee-xp.leaderboard') }}" class="btn btn-sm btn-outline-warning mb-1">
            <i class="fas fa-trophy mr-1"></i> Leaderboard
        </a>
        <a href="{{ route('xp-config.assign') }}" class="btn btn-sm btn-outline-info mb-1 mr-1 ml-1">
            <i class="fas fa-link mr-1"></i> Assign
        </a>
        @canAccess('store','xp_configs')
        <a href="{{ route('xp-config.create') }}" class="btn btn-sm btn-primary mb-1">
            <i class="fas fa-plus-circle mr-1"></i> Config Baru
        </a>
        @endcanAccess
    </div>
</div>
@stop

@section('content')
@include('components.alert')

{{-- ── STATS ROW ─────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
        <div class="xp-stat-card" style="--accent:#667eea;">
            <div class="xp-stat-icon"><i class="fas fa-cog"></i></div>
            <div class="xp-stat-val">{{ $stats['total_configs'] }}</div>
            <div class="xp-stat-lbl">Total Config</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="xp-stat-card" style="--accent:#38ef7d;">
            <div class="xp-stat-icon" style="color:#38ef7d;"><i class="fas fa-check-circle"></i></div>
            <div class="xp-stat-val" style="color:#38ef7d;">{{ $stats['active_configs'] }}</div>
            <div class="xp-stat-lbl">Config Aktif</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="xp-stat-card" style="--accent:#4facfe;">
            <div class="xp-stat-icon" style="color:#4facfe;"><i class="fas fa-building"></i></div>
            <div class="xp-stat-val" style="color:#4facfe;">{{ $stats['companies_with_xp'] }}</div>
            <div class="xp-stat-lbl">Company Pakai XP</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="xp-stat-card" style="--accent:#f093fb;">
            <div class="xp-stat-icon" style="color:#f093fb;"><i class="fas fa-users"></i></div>
            <div class="xp-stat-val" style="color:#f093fb;">{{ $stats['users_with_xp'] }}</div>
            <div class="xp-stat-lbl">User Punya XP</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="xp-stat-card" style="--accent:#f5a623;">
            <div class="xp-stat-icon" style="color:#f5a623;"><i class="fas fa-bolt"></i></div>
            <div class="xp-stat-val" style="color:#f5a623;">{{ number_format($stats['total_xp_awarded']) }}</div>
            <div class="xp-stat-lbl">Total XP Diberikan</div>
        </div>
    </div>
    <div class="col-6 col-md-2">
        <div class="xp-stat-card" style="--accent:#f53844;">
            <div class="xp-stat-icon" style="color:#f53844;"><i class="fas fa-crown"></i></div>
            <div class="xp-stat-val" style="color:#f53844;">{{ number_format($stats['top_xp']) }}</div>
            <div class="xp-stat-lbl">XP Tertinggi</div>
        </div>
    </div>
</div>

{{-- ── CONFIG LIST ───────────────────────────────────── --}}
<div class="xp-master-card mb-4">
    <div class="xp-master-header">
        <span><i class="fas fa-database me-2"></i> Daftar XP Config</span>
        <small style="color:#a0a8d0;">{{ $configs->total() }} config tersedia</small>
    </div>

    @forelse($configs as $config)
    <div class="xp-config-row {{ $config->is_enabled ? '' : 'disabled-row' }}">
        {{-- Status strip --}}
        <div class="xp-config-strip" style="background: {{ $config->is_enabled ? 'linear-gradient(180deg,#38ef7d,#11998e)' : '#555' }};"></div>

        {{-- Info --}}
        <div class="flex-grow-1 px-3 py-2">
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <span class="fw-bold" style="color:#e0e0ff;font-size:.95rem;">{{ $config->name }}</span>
                <span class="xp-badge {{ $config->is_enabled ? 'badge-active' : 'badge-inactive' }}">
                    {{ $config->is_enabled ? '● Aktif' : '○ Nonaktif' }}
                </span>
                <span class="xp-badge" style="background:rgba(79,172,254,.15);color:#4facfe;">
                    <i class="fas fa-building mr-1" style="font-size:.65rem;"></i>{{ $config->companies_count }} company
                </span>
            </div>
            @if($config->description)
            <small style="color:#8ab4c0;">{{ $config->description }}</small>
            @endif
        </div>

        {{-- Models preview --}}
        <div class="xp-models-preview px-2">
            @foreach($config->models->take(5) as $m)
            <span class="xp-model-pill {{ $m->xp >= 0 ? 'pill-pos' : 'pill-neg' }}">
                {{ $m->label ?? $m->source_type }}
                <strong>{{ $m->xp > 0 ? '+' : '' }}{{ $m->xp }}⚡</strong>
            </span>
            @endforeach
            @if($config->models->count() > 5)
            <span class="xp-model-pill pill-more">+{{ $config->models->count() - 5 }} lagi</span>
            @endif
        </div>

        {{-- Actions --}}
        <div class="xp-config-actions px-3">
            @canAccess('edit','xp_configs')
            <a href="{{ route('xp-config.edit', $config) }}" class="btn btn-sm xp-btn-edit">
                <i class="fas fa-edit"></i>
            </a>
            @endcanAccess
            @canAccess('destroy','xp_configs')
            <form action="{{ route('xp-config.destroy', $config) }}" method="POST" class="d-inline delete-form">
                @csrf @method('DELETE')
                <button class="btn btn-sm xp-btn-del"><i class="fas fa-trash-alt"></i></button>
            </form>
            @endcanAccess
        </div>
    </div>
    @empty
    <div class="text-center py-5" style="color:#a0a8d0;">
        <i class="fas fa-bolt fa-3x mb-3 d-block" style="opacity:.3;"></i>
        <p class="mb-3">Belum ada XP Config. Buat yang pertama!</p>
        <a href="{{ route('xp-config.create') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fas fa-plus mr-1"></i> Buat Config
        </a>
    </div>
    @endforelse
</div>

@if($configs->hasPages())
<div class="d-flex justify-content-center">
    {{ $configs->links('vendor.pagination.bootstrap-4') }}
</div>
@endif

@stop

@section('css')
<style>
/* ── STAT CARDS ────────────────────────────────── */
.xp-stat-card {
    background: linear-gradient(145deg, #1a1a2e, #16213e);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 14px;
    padding: 16px 14px;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: transform .2s;
}
.xp-stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--accent, #667eea);
}
.xp-stat-card:hover { transform: translateY(-2px); }
.xp-stat-icon { font-size: 1.4rem; margin-bottom: 6px; color: #667eea; }
.xp-stat-val  { font-size: 1.5rem; font-weight: 800; color: #e0e0ff; line-height: 1; }
.xp-stat-lbl  { font-size: .7rem; color: #8ab4c0; margin-top: 4px; text-transform: uppercase; letter-spacing: .04em; }

/* ── MASTER CARD ───────────────────────────────── */
.xp-master-card {
    background: linear-gradient(145deg, #1a1a2e, #16213e);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 16px;
    overflow: hidden;
}
.xp-master-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid rgba(255,255,255,.07);
    font-weight: 700;
    color: #e0e0ff;
}

/* ── CONFIG ROW ────────────────────────────────── */
.xp-config-row {
    display: flex;
    align-items: center;
    border-bottom: 1px solid rgba(255,255,255,.05);
    transition: background .15s;
}
.xp-config-row:last-child { border-bottom: none; }
.xp-config-row:hover { background: rgba(102,126,234,.06); }
.xp-config-row.disabled-row { opacity: .6; }
.xp-config-strip { width: 4px; align-self: stretch; flex-shrink: 0; }

/* ── BADGES ────────────────────────────────────── */
.xp-badge {
    font-size: .7rem;
    padding: 2px 8px;
    border-radius: 20px;
    font-weight: 600;
}
.badge-active  { background: rgba(56,239,125,.15); color: #38ef7d; }
.badge-inactive{ background: rgba(150,150,150,.15); color: #adb5bd; }

/* ── MODEL PILLS ───────────────────────────────── */
.xp-models-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    max-width: 320px;
    padding: 8px 0;
}
.xp-model-pill {
    font-size: .68rem;
    padding: 2px 7px;
    border-radius: 12px;
    white-space: nowrap;
}
.pill-pos  { background: rgba(56,239,125,.1); color: #38ef7d; border: 1px solid rgba(56,239,125,.2); }
.pill-neg  { background: rgba(245,87,108,.1); color: #f5576c; border: 1px solid rgba(245,87,108,.2); }
.pill-more { background: rgba(255,255,255,.06); color: #a0a8d0; }

/* ── ACTION BUTTONS ────────────────────────────── */
.xp-config-actions { display: flex; gap: 6px; flex-shrink: 0; }
.xp-btn-edit {
    background: rgba(102,126,234,.15);
    color: #667eea;
    border: 1px solid rgba(102,126,234,.3);
    padding: 4px 10px;
}
.xp-btn-edit:hover { background: rgba(102,126,234,.3); color: #a0c4ff; }
.xp-btn-del {
    background: rgba(245,87,108,.1);
    color: #f5576c;
    border: 1px solid rgba(245,87,108,.2);
    padding: 4px 10px;
}
.xp-btn-del:hover { background: rgba(245,87,108,.25); color: #ff8fa3; }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
    @if(session('store'))
        Swal.fire({ icon:'success', title:'Berhasil!', text:'XP Config berhasil dibuat.', timer:2000, showConfirmButton:false });
    @endif
    @if(session('update'))
        Swal.fire({ icon:'success', title:'Diperbarui!', text:'XP Config berhasil diperbarui.', timer:2000, showConfirmButton:false });
    @endif
    @if(session('delete'))
        Swal.fire({ icon:'success', title:'Dihapus!', text:'XP Config berhasil dihapus.', timer:2000, showConfirmButton:false });
    @endif
    @if(session('error'))
        Swal.fire({ icon:'error', title:'Gagal!', text:'{{ session("error") }}' });
    @endif

    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();
            Swal.fire({
                title: 'Hapus Config ini?',
                text: 'Config yang masih dipakai company tidak bisa dihapus.',
                icon: 'warning',
                background: '#16213e',
                color: '#e0e0ff',
                showCancelButton: true,
                confirmButtonColor: '#f5576c',
                cancelButtonColor: '#667eea',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
            }).then(r => { if (r.isConfirmed) form.submit(); });
        });
    });
</script>
@stop
