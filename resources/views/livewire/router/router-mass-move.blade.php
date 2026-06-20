@section('title', 'Pindah Pelanggan Massal')

@section('content_header')
<div class="d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-0 font-weight-bold text-dark">
            <i class="fas fa-exchange-alt mr-2 text-primary"></i>Pindah Pelanggan Massal
        </h4>
        <small class="text-muted">Pindahkan pelanggan dari satu router ke router lain secara massal</small>
    </div>
    <a href="{{ route('router.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Router
    </a>
</div>
@stop

<div>

{{-- Flash --}}
@if($flashMessage)
<div class="alert alert-{{ $flashType === 'danger' ? 'danger' : 'success' }} alert-dismissible fade show mb-3">
    <i class="fas fa-{{ $flashType === 'danger' ? 'exclamation-circle' : 'check-circle' }} mr-2"></i>
    {{ $flashMessage }}
    <button type="button" class="close" onclick="this.parentElement.remove()"><span>&times;</span></button>
</div>
@endif

{{-- ── Step Indicator ── --}}
<div class="move-steps mb-4">
    @foreach([
        [1, 'fa-server',        'Pilih Pelanggan'],
        [2, 'fa-router',        'Router Tujuan'],
        [3, 'fa-check-double',  'Konfirmasi'],
        [4, 'fa-flag-checkered','Selesai'],
    ] as [$num, $icon, $label])
    <div class="move-step {{ $step >= $num ? ($step == $num ? 'active' : 'done') : '' }}">
        <div class="move-step-circle">
            @if($step > $num)
                <i class="fas fa-check"></i>
            @else
                <i class="fas fa-{{ $icon }}"></i>
            @endif
        </div>
        <div class="move-step-label">{{ $label }}</div>
    </div>
    @if($num < 4)
    <div class="move-step-line {{ $step > $num ? 'done' : '' }}"></div>
    @endif
    @endforeach
</div>

{{-- ═══════════════════════════════════════════
     STEP 1 — Pilih Router Asal & Pelanggan
══════════════════════════════════════════════ --}}
@if($step === 1)
<div class="row">

    {{-- Panel kiri: pilih router asal --}}
    <div class="col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="fas fa-server text-primary mr-1"></i> Router Asal</h5>
            </div>
            <div class="card-body">
                <label class="form-label-sm mb-1">Pilih Router <span class="text-danger">*</span></label>
                <select wire:model="sourceRouterId" class="form-control">
                    <option value="">— Pilih Router —</option>
                    @foreach($allRouters as $r)
                    <option value="{{ $r->id }}">
                        {{ $r->name }}
                        ({{ $r->internet_customers_count }} pelanggan)
                        @if($r->active_status === 'UP') ✅ @elseif($r->active_status === 'DOWN') ⚠️ @endif
                    </option>
                    @endforeach
                </select>

                @if($sourceRouter)
                <div class="mt-3 p-3 rounded" style="background:#f0f7ff;border:1px solid #bfdbfe">
                    <div style="font-size:.78rem;color:#1d4ed8;font-weight:600;margin-bottom:4px">
                        <i class="fas fa-info-circle mr-1"></i>Info Router Asal
                    </div>
                    <div style="font-size:.82rem;color:#475569">
                        <div><strong>Host:</strong> {{ $sourceRouter->host }}:{{ $sourceRouter->port }}</div>
                        <div><strong>POP:</strong> {{ $sourceRouter->pop->name ?? '–' }}</div>
                        <div><strong>Total Pelanggan:</strong> {{ $sourceRouter->internet_customers_count }}</div>
                        <div class="mt-1">
                            @if($sourceRouter->active_status === 'UP')
                                <span class="badge badge-success">Online</span>
                            @elseif($sourceRouter->active_status === 'DOWN')
                                <span class="badge badge-danger">Offline / Mati</span>
                            @else
                                <span class="badge badge-warning">{{ $sourceRouter->active_status }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Warning + toggle untuk router mati --}}
                @if($sourceRouter->active_status === 'DOWN')
                <div class="mt-2 p-3 rounded" style="background:#fff7ed;border:1px solid #fed7aa">
                    <div style="font-size:.78rem;color:#c2410c;font-weight:700;margin-bottom:4px">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Router Asal Tidak Aktif
                    </div>
                    <div style="font-size:.78rem;color:#7c2d12">
                        Router tidak dapat dihubungi. Mode <strong>Skip Operasi Router Lama</strong>
                        diaktifkan otomatis — proses disconnect & hapus secret di router asal akan dilewati.
                    </div>
                </div>
                @endif

                {{-- Toggle skip old router --}}
                <div class="mt-3 d-flex align-items-start gap-2"
                     style="background:#f8fafc;border-radius:8px;padding:10px 12px">
                    <div class="custom-control custom-switch mt-1">
                        <input type="checkbox" class="custom-control-input" id="skip-old-router"
                               wire:model="skipOldRouter">
                        <label class="custom-control-label" for="skip-old-router"></label>
                    </div>
                    <div>
                        <div style="font-size:.8rem;font-weight:700;color:#1e293b">
                            Skip Operasi Router Lama
                            @if($skipOldRouter)
                                <span class="badge badge-warning ml-1" style="font-size:.65rem">AKTIF</span>
                            @endif
                        </div>
                        <div style="font-size:.74rem;color:#64748b;line-height:1.4">
                            @if($skipOldRouter)
                                Proses <em>disconnect</em> & <em>hapus secret</em> di router asal akan
                                <strong>dilewati</strong>. Gunakan saat router asal <strong>mati/rusak/tidak bisa diakses</strong>.
                            @else
                                Aktifkan jika router asal sedang <strong>mati atau rusak</strong> agar tidak
                                menunggu timeout koneksi.
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Panel kanan: daftar pelanggan --}}
    <div class="col-lg-8 mb-3">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0"><i class="fas fa-users text-success mr-1"></i> Daftar Pelanggan</h5>
                @if($sourceRouterId)
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    {{-- Search --}}
                    <input wire:model.debounce.300ms="search" type="text"
                           class="form-control form-control-sm" style="width:180px"
                           placeholder="Cari nama / username...">
                    {{-- Status filter --}}
                    <select wire:model="statusFilter" class="form-control form-control-sm" style="width:130px">
                        <option value="all">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="reactivated">Connecting</option>
                        <option value="suspended">Suspend</option>
                        <option value="expired">Expired</option>
                    </select>
                    {{-- Grouping filter --}}
                    <select wire:model="groupingFilter" class="form-control form-control-sm" style="width:155px">
                        <option value="all">Semua Grouping</option>
                        <option value="none">— Tanpa Grouping</option>
                        @foreach($groupingOptions as $gid)
                        <option value="{{ $gid }}">{{ $gid }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            @if(!$sourceRouterId)
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-server fa-3x mb-3 d-block" style="opacity:.2"></i>
                Pilih router asal untuk melihat daftar pelanggan
            </div>

            @elseif($customers->isEmpty())
            <div class="card-body text-center text-muted py-4">
                <i class="fas fa-users-slash fa-2x d-block mb-2" style="opacity:.25"></i>
                Tidak ada pelanggan yang cocok dengan filter
            </div>

            @else
            <div class="table-responsive">
                <table class="table table-sm move-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="chk-all"
                                           wire:model="selectAll">
                                    <label class="custom-control-label" for="chk-all"></label>
                                </div>
                            </th>
                            <th>Pelanggan</th>
                            <th>Username</th>
                            <th>Grouping</th>
                            <th>Paket</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $cust)
                        <tr wire:key="cust-{{ $cust->id }}"
                            class="{{ in_array((string)$cust->id, $selectedCustomers) ? 'row-selected' : '' }}">
                            <td>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input"
                                           id="chk-{{ $cust->id }}"
                                           wire:model="selectedCustomers"
                                           value="{{ $cust->id }}">
                                    <label class="custom-control-label" for="chk-{{ $cust->id }}"></label>
                                </div>
                            </td>
                            <td>
                                <div style="font-size:.82rem;font-weight:600;color:#1e293b">{{ $cust->name }}</div>
                                <div style="font-size:.72rem;color:#94a3b8">{{ $cust->code }}</div>
                            </td>
                            <td style="font-size:.8rem;font-family:monospace;color:#475569">{{ $cust->username }}</td>
                            <td>
                                @if($cust->grouping_id)
                                    <span class="badge" style="background:#eff6ff;color:#1d4ed8;font-size:.7rem;padding:3px 8px;border-radius:20px">
                                        {{ $cust->grouping_id }}
                                    </span>
                                @else
                                    <span class="text-muted" style="font-size:.75rem">–</span>
                                @endif
                            </td>
                            <td style="font-size:.78rem">{{ $cust->internetPackage->name ?? '–' }}</td>
                            <td>
                                @php
                                    $sc = match($cust->status) {
                                        'active'      => ['success', 'Aktif'],
                                        'reactivated' => ['primary', 'Connecting'],
                                        'suspended'   => ['warning', 'Suspend'],
                                        'expired'     => ['secondary','Expired'],
                                        'disconnected'=> ['danger',  'Cabut'],
                                        default       => ['light',   $cust->status],
                                    };
                                @endphp
                                <span class="badge badge-{{ $sc[0] }}" style="font-size:.7rem">{{ $sc[1] }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Footer action --}}
            <div class="card-footer d-flex align-items-center justify-content-between"
                 style="background:#f8fafc">
                <div style="font-size:.8rem;color:#475569">
                    @if($selectedCount > 0)
                        <span class="badge badge-primary">{{ $selectedCount }}</span> pelanggan dipilih
                    @else
                        <span class="text-muted">Belum ada yang dipilih</span>
                    @endif
                </div>
                <button type="button" class="btn btn-primary btn-sm"
                        wire:click="goToStep2"
                        {{ $selectedCount === 0 ? 'disabled' : '' }}>
                    Lanjut: Pilih Router Tujuan
                    <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════
     STEP 2 — Pilih Router Tujuan
══════════════════════════════════════════════ --}}
@if($step === 2)
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-route text-primary mr-1"></i> Pilih Router Tujuan</h5>
            </div>
            <div class="card-body">

                {{-- Summary banner --}}
                <div class="move-summary-banner mb-4">
                    <div class="move-summary-side from">
                        <i class="fas fa-server"></i>
                        <div>
                            <div class="move-summary-label">Router Asal</div>
                            <div class="move-summary-val">{{ $sourceRouter->name ?? '–' }}</div>
                        </div>
                    </div>
                    <div class="move-summary-arrow">
                        <i class="fas fa-long-arrow-alt-right"></i>
                        <div style="font-size:.72rem;color:#64748b;margin-top:2px">{{ $selectedCount }} pelanggan</div>
                    </div>
                    <div class="move-summary-side to">
                        <i class="fas fa-network-wired"></i>
                        <div>
                            <div class="move-summary-label">Router Tujuan</div>
                            <div class="move-summary-val">{{ $targetRouter->name ?? 'Belum dipilih' }}</div>
                        </div>
                    </div>
                </div>

                <label class="form-label-sm">Router Tujuan <span class="text-danger">*</span></label>
                <select wire:model="targetRouterId" class="form-control mb-3">
                    <option value="">— Pilih Router Tujuan —</option>
                    @foreach($targetRouters as $r)
                    <option value="{{ $r->id }}">
                        {{ $r->name }} — {{ $r->host }}
                        ({{ $r->internet_customers_count }} pelanggan saat ini)
                        @if($r->active_status === 'UP') ✅ @elseif($r->active_status === 'DOWN') ⚠️ @endif
                    </option>
                    @endforeach
                </select>

                @if($targetRouter)
                <div class="p-3 rounded mb-3" style="background:#f0fdf4;border:1px solid #bbf7d0">
                    <div style="font-size:.78rem;color:#15803d;font-weight:600;margin-bottom:4px">
                        <i class="fas fa-check-circle mr-1"></i>Router Tujuan Dipilih
                    </div>
                    <div style="font-size:.82rem;color:#475569">
                        <strong>Host:</strong> {{ $targetRouter->host }}:{{ $targetRouter->port }} &nbsp;|&nbsp;
                        <strong>POP:</strong> {{ $targetRouter->pop->name ?? '–' }} &nbsp;|&nbsp;
                        <strong>Pelanggan saat ini:</strong> {{ $targetRouter->internet_customers_count }}
                    </div>
                </div>
                @endif

                @if($skipOldRouter)
                <div class="alert alert-warning" style="font-size:.8rem;border-left:4px solid #f59e0b">
                    <i class="fas fa-bolt mr-1"></i>
                    <strong>Mode Router Rusak/Mati</strong> — operasi ke router lama
                    (<em>disconnect</em> & <em>hapus secret</em>) akan <strong>dilewati</strong>.
                    Hanya setup di router baru yang dijalankan via RADIUS + Direct API.
                    Status sementara menjadi <em>Connecting</em>.
                </div>
                @else
                <div class="alert alert-warning" style="font-size:.8rem">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Proses pindah akan: <strong>disconnect</strong> pelanggan dari router lama,
                    <strong>hapus secret</strong> di router lama, dan <strong>setup ulang</strong>
                    di router baru via RADIUS + Direct API. Status sementara menjadi <em>Connecting</em>.
                </div>
                @endif
            </div>
            <div class="card-footer d-flex justify-content-between" style="background:#f8fafc">
                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="backToStep1">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </button>
                <button type="button" class="btn btn-primary btn-sm" wire:click="goToStep3"
                        {{ !$targetRouterId ? 'disabled' : '' }}>
                    Lanjut: Konfirmasi <i class="fas fa-arrow-right ml-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════
     STEP 3 — Konfirmasi
══════════════════════════════════════════════ --}}
@if($step === 3)
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header" style="background:#fff3cd;border-bottom:1px solid #fde68a">
                <h5 class="mb-0 text-warning">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Konfirmasi Perpindahan
                </h5>
            </div>
            <div class="card-body">

                <div class="move-summary-banner mb-4">
                    <div class="move-summary-side from">
                        <i class="fas fa-server"></i>
                        <div>
                            <div class="move-summary-label">Dari Router</div>
                            <div class="move-summary-val">{{ $sourceRouter->name ?? '–' }}</div>
                            <small class="text-muted">{{ $sourceRouter->host ?? '' }}</small>
                        </div>
                    </div>
                    <div class="move-summary-arrow">
                        <i class="fas fa-long-arrow-alt-right" style="color:#f59e0b;font-size:1.8rem"></i>
                        <div style="font-size:.72rem;font-weight:700;color:#b45309;margin-top:2px">
                            {{ $selectedCount }} pelanggan
                        </div>
                    </div>
                    <div class="move-summary-side to">
                        <i class="fas fa-network-wired" style="color:#16a34a"></i>
                        <div>
                            <div class="move-summary-label">Ke Router</div>
                            <div class="move-summary-val" style="color:#16a34a">{{ $targetRouter->name ?? '–' }}</div>
                            <small class="text-muted">{{ $targetRouter->host ?? '' }}</small>
                        </div>
                    </div>
                </div>

                @if($skipOldRouter)
                <div class="mb-3 p-3 rounded d-flex align-items-center gap-2"
                     style="background:#fff7ed;border:1px solid #fed7aa">
                    <i class="fas fa-bolt text-warning" style="font-size:1.1rem"></i>
                    <div style="font-size:.82rem;color:#92400e">
                        <strong>Mode Router Rusak/Mati Aktif</strong> —
                        operasi ke router lama akan <strong>dilewati sepenuhnya</strong>.
                        Hanya setup di router baru yang dieksekusi.
                    </div>
                </div>
                @endif

                <p style="font-size:.85rem;color:#475569">
                    Anda akan memindahkan <strong>{{ $selectedCount }} pelanggan</strong>
                    dari <strong>{{ $sourceRouter->name ?? '' }}</strong> ke
                    <strong>{{ $targetRouter->name ?? '' }}</strong>.
                    Proses berjalan di background (queue job). Pelanggan sementara berstatus
                    <span class="badge badge-primary" style="font-size:.75rem">Connecting</span>
                    sampai terhubung ke router baru.
                </p>

                <div class="alert alert-danger" style="font-size:.8rem">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    <strong>Tindakan ini tidak dapat dibatalkan.</strong>
                    Pastikan router tujuan aktif dan dapat diakses sebelum melanjutkan.
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between" style="background:#f8fafc">
                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="backToStep2">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </button>
                <button type="button" class="btn btn-danger"
                        wire:click="process"
                        wire:loading.attr="disabled"
                        wire:target="process">
                    <span wire:loading.remove wire:target="process">
                        <i class="fas fa-exchange-alt mr-1"></i> Proses Pindah {{ $selectedCount }} Pelanggan
                    </span>
                    <span wire:loading wire:target="process">
                        <i class="fas fa-circle-notch fa-spin mr-1"></i> Memproses…
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════
     STEP 4 — Hasil
══════════════════════════════════════════════ --}}
@if($step === 4)
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header" style="background:#f0fdf4;border-bottom:1px solid #bbf7d0">
                <h5 class="mb-0 text-success">
                    <i class="fas fa-flag-checkered mr-1"></i> Proses Selesai
                </h5>
            </div>
            <div class="card-body">

                <div class="text-center mb-4">
                    <div style="width:70px;height:70px;border-radius:50%;background:#f0fdf4;border:3px solid #22c55e;
                                margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:#16a34a">
                        <i class="fas fa-check"></i>
                    </div>
                    <h5 class="mb-1">{{ $dispatchedCount }} Job Berhasil Dikirim ke Antrian</h5>
                    <p style="font-size:.83rem;color:#64748b">
                        Proses perpindahan berjalan di background. Cek status pelanggan beberapa saat kemudian.
                    </p>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm move-table">
                        <thead>
                            <tr>
                                <th>Pelanggan</th>
                                <th>Kode</th>
                                <th>Status Job</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resultLog as $log)
                            <tr>
                                <td style="font-size:.82rem;font-weight:600">{{ $log['name'] }}</td>
                                <td style="font-size:.78rem;color:#94a3b8">{{ $log['code'] }}</td>
                                <td>
                                    @if($log['status'] === 'queued')
                                        <span class="badge badge-success">Antrian</span>
                                    @else
                                        <span class="badge badge-danger">Error</span>
                                    @endif
                                </td>
                                <td style="font-size:.78rem;color:#64748b">{{ $log['message'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
            <div class="card-footer d-flex justify-content-between" style="background:#f8fafc">
                <a href="{{ route('router.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-list mr-1"></i> Kembali ke Daftar Router
                </a>
                <button type="button" class="btn btn-primary btn-sm" wire:click="reset_form">
                    <i class="fas fa-redo mr-1"></i> Pindah Lagi
                </button>
            </div>
        </div>
    </div>
</div>
@endif

</div>

@section('css')
<style>
/* ── Step Indicator ── */
.move-steps {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 14px;
    padding: 18px 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
}
.move-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}
.move-step-circle {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #94a3b8;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem;
    transition: all .3s;
}
.move-step.active .move-step-circle {
    background: #2563eb; color: #fff;
    box-shadow: 0 0 0 4px rgba(37,99,235,.15);
}
.move-step.done .move-step-circle {
    background: #16a34a; color: #fff;
}
.move-step-label {
    font-size: .72rem; font-weight: 600; color: #94a3b8; text-align: center;
    white-space: nowrap;
}
.move-step.active .move-step-label { color: #2563eb; }
.move-step.done .move-step-label  { color: #16a34a; }
.move-step-line {
    flex: 1; height: 2px; background: #e2e8f0; margin: 0 8px;
    margin-bottom: 22px;
    transition: background .3s;
}
.move-step-line.done { background: #16a34a; }

/* ── Table ── */
.move-table thead th {
    background: #f8fafc;
    font-size: .7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .6px;
    color: #64748b; border-top: none;
    padding: 8px 12px !important;
}
.move-table tbody td { padding: 9px 12px !important; vertical-align: middle; }
.move-table tbody tr.row-selected { background: #eff6ff; }
.move-table tbody tr:hover { background: #f8fafc; }

/* ── Summary Banner ── */
.move-summary-banner {
    display: flex; align-items: center; justify-content: space-between;
    background: #f8fafc; border-radius: 12px; padding: 16px 20px;
    border: 1px solid #e2e8f0;
}
.move-summary-side {
    display: flex; align-items: center; gap: 10px;
}
.move-summary-side i {
    font-size: 1.4rem; color: #2563eb;
    width: 44px; height: 44px;
    background: #eff6ff; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
}
.move-summary-label { font-size: .7rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: .6px; }
.move-summary-val   { font-size: .95rem; font-weight: 700; color: #1e293b; }
.move-summary-arrow {
    text-align: center; color: #94a3b8; font-size: 1.4rem;
}
</style>
@stop
