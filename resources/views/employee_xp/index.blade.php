@extends('adminlte::page')

@section('title', 'Master Riwayat XP')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 fw-bold" style="color:#e0e0ff;">⚡ Master Riwayat XP</h1>
        <small style="color:#a0a8d0;">Kelola semua transaksi XP karyawan</small>
    </div>
    <div class="d-flex gap-2">
        @canAccess('leaderboard','employee_xps')
        <a href="{{ route('employee-xp.leaderboard') }}" class="btn btn-sm mb-1 mr-1" style="background:rgba(245,166,35,.15);color:#f5a623;border:1px solid rgba(245,166,35,.3);">
            <i class="fas fa-trophy mr-1"></i> Leaderboard
        </a>
        @endcanAccess
        @canAccess('store','employee_xps')
        <button class="btn btn-sm mb-1" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;" data-toggle="modal" data-target="#modalAward">
            <i class="fas fa-plus mr-1"></i> Award XP Manual
        </button>
        @endcanAccess
    </div>
</div>
@stop

@section('content')
@include('components.alert')

{{-- ── STAT CARDS ──────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="xi-stat" style="--accent:#38ef7d;">
            <div class="xi-stat-icon" style="color:#38ef7d;"><i class="fas fa-bolt"></i></div>
            <div class="xi-stat-val" style="color:#38ef7d;">+{{ number_format($stats['total_xp']) }}</div>
            <div class="xi-stat-lbl">Total XP Diberikan</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="xi-stat" style="--accent:#f5576c;">
            <div class="xi-stat-icon" style="color:#f5576c;"><i class="fas fa-minus-circle"></i></div>
            <div class="xi-stat-val" style="color:#f5576c;">{{ number_format($stats['total_penalty']) }}</div>
            <div class="xi-stat-lbl">Total XP Penalti</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="xi-stat" style="--accent:#667eea;">
            <div class="xi-stat-icon" style="color:#667eea;"><i class="fas fa-exchange-alt"></i></div>
            <div class="xi-stat-val">{{ number_format($stats['total_txn']) }}</div>
            <div class="xi-stat-lbl">Total Transaksi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="xi-stat" style="--accent:#f5a623;">
            <div class="xi-stat-icon" style="color:#f5a623;"><i class="fas fa-users"></i></div>
            <div class="xi-stat-val">{{ number_format($stats['users_with_xp']) }}</div>
            <div class="xi-stat-lbl">Karyawan Punya XP</div>
        </div>
    </div>
</div>

{{-- ── FILTER BAR ────────────────────────────────── --}}
<form method="GET" class="xi-filter-bar mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="xi-label">Karyawan</label>
            <select name="user_id" class="xi-select">
                <option value="">Semua Karyawan</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                    {{ $u->name }} ({{ number_format($u->total_xp) }} XP)
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="xi-label">Tipe</label>
            <select name="type" class="xi-select">
                <option value="">Semua</option>
                <option value="reward"  {{ request('type') === 'reward'  ? 'selected' : '' }}>Reward (+)</option>
                <option value="penalty" {{ request('type') === 'penalty' ? 'selected' : '' }}>Penalti (−)</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="xi-label">Sumber</label>
            <select name="source_type" class="xi-select">
                <option value="">Semua Sumber</option>
                @foreach($sourceTypes as $st)
                <option value="{{ $st }}" {{ request('source_type') === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-auto">
            <button type="submit" class="btn btn-sm xi-btn-filter">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            @if(request()->hasAny(['user_id','type','source_type']))
            <a href="{{ route('employee-xp.index') }}" class="btn btn-sm xi-btn-reset ml-1">Reset</a>
            @endif
        </div>
    </div>
</form>

{{-- ── TABLE ─────────────────────────────────────── --}}
<div class="xi-card">
    <div class="xi-card-header">
        <span><i class="fas fa-history mr-2"></i> Riwayat Transaksi</span>
        <small style="color:#a0a8d0;">{{ $histories->total() }} transaksi</small>
    </div>

    <div class="table-responsive">
        <table class="xi-table">
            <thead>
                <tr>
                    <th style="width:10%;text-align:center;">XP</th>
                    <th style="width:20%;">Karyawan</th>
                    <th style="width:16%;">Sumber</th>
                    <th style="width:36%;">Keterangan</th>
                    <th style="width:14%;">Tanggal</th>
                    <th style="width:4%;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $h)
                <tr>
                    <td style="text-align:center;">
                        <span class="xi-xp-badge {{ $h->xp > 0 ? 'xp-pos' : 'xp-neg' }}">
                            {{ $h->xp > 0 ? '+' : '' }}{{ $h->xp }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="xi-avatar mr-2">{{ strtoupper(substr($h->user->name ?? '?', 0, 1)) }}</div>
                            <span style="color:#c8d0e0;font-size:.85rem;">{{ $h->user->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="xi-source {{ $h->source_type === 'Manual' ? 'source-manual' : '' }}">
                            {{ $h->source_type }}
                        </span>
                    </td>
                    <td style="color:#8ab4c0;font-size:.83rem;">{{ $h->description ?? '—' }}</td>
                    <td>
                        <div style="color:#c8d0e0;font-size:.83rem;">{{ $h->created_at->format('d M Y') }}</div>
                        <small style="color:#555;">{{ $h->created_at->format('H:i') }}</small>
                    </td>
                    <td>
                        @if($h->source_type === 'Manual')
                        <form method="POST" action="{{ route('employee-xp.destroy', $h->id) }}"
                              onsubmit="return confirmDelete(this, '{{ addslashes($h->user->name ?? '') }}', {{ $h->xp }})">
                            @csrf @method('DELETE')
                            <button type="submit" class="xi-btn-del" title="Hapus">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="fas fa-bolt fa-3x mb-3 d-block" style="color:#333;"></i>
                        <p style="color:#555;">Tidak ada riwayat XP.</p>
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

@canAccess('store','employee_xps')
{{-- ── MODAL AWARD XP ────────────────────────────── --}}
<div class="modal fade" id="modalAward" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content xi-modal">
            <div class="xi-modal-header">
                <span>⚡ Award XP Manual</span>
                <button type="button" class="xi-modal-close" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" action="{{ route('employee-xp.store') }}">
                @csrf
                <div class="xi-modal-body">
                    <div class="xi-form-group">
                        <label class="xi-label">Karyawan <span style="color:#f5576c;">*</span></label>
                        <select name="user_id" class="xi-select" required>
                            <option value="">— Pilih Karyawan —</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ number_format($u->total_xp) }} XP)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="xi-form-group">
                        <label class="xi-label">
                            Jumlah XP <span style="color:#f5576c;">*</span>
                            <small style="color:#555;font-weight:400;">(gunakan angka negatif untuk penalti)</small>
                        </label>
                        <input type="number" name="xp" class="xi-input" id="xpInput"
                               placeholder="cth: 50 atau -20" min="-9999" max="9999" required />
                    </div>
                    <div class="xi-form-group">
                        <label class="xi-label">Keterangan <span style="color:#f5576c;">*</span></label>
                        <input type="text" name="description" class="xi-input"
                               placeholder="Alasan pemberian / pengurangan XP" maxlength="255" required />
                    </div>
                    <div id="xpPreview" class="xi-xp-preview" style="display:none;"></div>
                </div>
                <div class="xi-modal-footer">
                    <button type="button" class="xi-btn-cancel" data-dismiss="modal">Batal</button>
                    <button type="submit" class="xi-btn-save">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcanAccess
@stop

@section('css')
<style>
/* ── STAT CARDS ────────────────────────────────── */
.xi-stat {
    background: linear-gradient(145deg, #1a1a2e, #16213e);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 14px;
    padding: 18px 16px;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: transform .2s;
}
.xi-stat::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--accent, #667eea);
}
.xi-stat:hover { transform: translateY(-2px); }
.xi-stat-icon { font-size: 1.3rem; margin-bottom: 6px; color: #667eea; }
.xi-stat-val  { font-size: 1.5rem; font-weight: 800; color: #e0e0ff; line-height: 1; }
.xi-stat-lbl  { font-size: .7rem; color: #8ab4c0; margin-top: 5px; text-transform: uppercase; letter-spacing: .04em; }

/* ── FILTER BAR ────────────────────────────────── */
.xi-filter-bar { }
.xi-label { display: block; font-size: .73rem; color: #8ab4c0; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
.xi-select {
    width: 100%;
    background: #1a1a2e;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 8px;
    color: #c8d0e0;
    padding: 7px 10px;
    font-size: .85rem;
}
.xi-select option { background: #1a1a2e; }
.xi-btn-filter {
    background: rgba(102,126,234,.2);
    color: #2c4875;
    border: 1px solid rgba(102,126,234,.3);
    border-radius: 8px;
    font-size: .83rem;
}
.xi-btn-filter:hover { background: rgba(77, 103, 221, 0.35); color: #10102bff; }
.xi-btn-reset {
    background: rgba(255,255,255,.05);
    color: #8ab4c0;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 8px;
    font-size: .83rem;
}

/* ── CARD ──────────────────────────────────────── */
.xi-card {
    background: linear-gradient(145deg, #1a1a2e, #16213e);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 16px;
    overflow: hidden;
}
.xi-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid rgba(255,255,255,.07);
    font-weight: 700;
    color: #e0e0ff;
}

/* ── TABLE ─────────────────────────────────────── */
.xi-table { width: 100%; border-collapse: collapse; }
.xi-table thead tr { background: rgba(255,255,255,.03); border-bottom: 1px solid rgba(255,255,255,.07); }
.xi-table th { padding: 9px 12px; font-size: .73rem; text-transform: uppercase; letter-spacing: .05em; color: #8ab4c0; font-weight: 600; }
.xi-table tbody tr { border-bottom: 1px solid rgba(255,255,255,.04); transition: background .15s; }
.xi-table tbody tr:hover { background: rgba(102,126,234,.06); }
.xi-table td { padding: 9px 12px; vertical-align: middle; }

/* ── BADGES ────────────────────────────────────── */
.xi-xp-badge {
    font-size: .8rem; font-weight: 700;
    padding: 3px 10px; border-radius: 20px; display: inline-block;
}
.xp-pos { background: rgba(56,239,125,.12); color: #38ef7d; border: 1px solid rgba(56,239,125,.25); }
.xp-neg { background: rgba(245,87,108,.12); color: #f5576c; border: 1px solid rgba(245,87,108,.25); }

.xi-source {
    font-family: monospace;
    font-size: .78rem;
    padding: 2px 8px;
    background: rgba(102,126,234,.1);
    color: #a0c4ff;
    border: 1px solid rgba(102,126,234,.2);
    border-radius: 6px;
    display: inline-block;
}
.xi-source.source-manual {
    background: rgba(245,166,35,.1);
    color: #f5a623;
    border-color: rgba(245,166,35,.25);
}

.xi-avatar {
    width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff; font-weight: 700; font-size: .75rem;
    display: flex; align-items: center; justify-content: center;
}

.xi-btn-del {
    background: rgba(245,87,108,.1);
    color: #f5576c;
    border: 1px solid rgba(245,87,108,.2);
    border-radius: 6px;
    padding: 3px 7px;
    font-size: .78rem;
    cursor: pointer;
    transition: background .15s;
}
.xi-btn-del:hover { background: rgba(245,87,108,.25); }

/* ── MODAL ─────────────────────────────────────── */
.xi-modal {
    background: linear-gradient(145deg, #1a1a2e, #16213e);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 16px;
    color: #e0e0ff;
}
.xi-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid rgba(255,255,255,.08);
    font-weight: 700;
    font-size: 1rem;
}
.xi-modal-close {
    background: none; border: none; color: #8ab4c0; font-size: 1.2rem; cursor: pointer;
}
.xi-modal-body { padding: 20px; }
.xi-modal-footer {
    padding: 12px 20px;
    border-top: 1px solid rgba(255,255,255,.08);
    display: flex; justify-content: flex-end; gap: 10px;
}
.xi-form-group { margin-bottom: 16px; }
.xi-input {
    width: 100%;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 8px;
    color: #e0e0ff;
    padding: 8px 12px;
    font-size: .88rem;
}
.xi-input::placeholder { color: #444; }
.xi-input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,.15); }
.xi-btn-cancel {
    background: rgba(255,255,255,.06);
    color: #8ab4c0;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 8px;
    padding: 7px 16px;
    font-size: .88rem;
    cursor: pointer;
}
.xi-btn-save {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 7px 20px;
    font-size: .88rem;
    font-weight: 600;
    cursor: pointer;
}
.xi-xp-preview {
    background: rgba(255,255,255,.04);
    border-radius: 8px;
    padding: 10px 14px;
    font-size: .85rem;
    margin-top: -6px;
    border: 1px solid rgba(255,255,255,.08);
}

/* Pagination dark override */
.pagination .page-link { background: rgba(255,255,255,.05); border-color: rgba(255,255,255,.1); color: #a0a8d0; }
.pagination .page-item.active .page-link { background: #667eea; border-color: #667eea; color: #fff; }
.pagination .page-link:hover { background: rgba(102,126,234,.2); color: #e0e0ff; }
.pagination .page-item.disabled .page-link { background: rgba(255,255,255,.03); color: #555; }
</style>
@stop

@section('js')
<script>
// XP input preview
document.getElementById('xpInput').addEventListener('input', function () {
    var val = parseInt(this.value);
    var el  = document.getElementById('xpPreview');
    if (!isNaN(val) && val !== 0) {
        el.style.display = 'block';
        if (val > 0) {
            el.style.color   = '#38ef7d';
            el.innerHTML = '⚡ Akan menambahkan <strong>+' + val + ' XP</strong> ke karyawan';
        } else {
            el.style.color   = '#f5576c';
            el.innerHTML = '⚠️ Akan mengurangi <strong>' + val + ' XP</strong> dari karyawan';
        }
    } else {
        el.style.display = 'none';
    }
});

// Delete confirmation
function confirmDelete(form, name, xp) {
    var label = xp > 0 ? '+' + xp : '' + xp;
    return confirm('Hapus riwayat XP ' + label + ' milik ' + name + '?\nTotal XP karyawan akan dikembalikan.');
}
</script>
@stop
