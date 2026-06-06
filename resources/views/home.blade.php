@extends('adminlte::page')

@section('title', 'Dashboard Internet')

@section('content_header')
<div style="margin-top:-8px"></div>
@stop

@section('content')
{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- Page header --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="page-header-custom">
    <h2><i class="fas fa-wifi mr-2"></i>Dashboard Internet</h2>
    <p>Ringkasan data pelanggan, pendapatan, dan pendaftaran internet secara real-time.</p>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ROW 1 – Main Stat Cards --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="section-label">Status Pelanggan</div>
<div class="row mb-4" id="stat-cards-row">

    {{-- Total Semua --}}
    <div class="col-6 col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-users"></i></div>
            <div class="stat-body">
                <div class="stat-value" id="val-total-all">
                    <div class="skeleton short"></div>
                </div>
                <div class="stat-label">Total Semua Pelanggan</div>
                <div id="val-new-month" class="stat-sub neutral d-none">
                    <i class="fas fa-plus-circle"></i> <span></span> bulan ini
                </div>
            </div>
        </div>
    </div>

    {{-- Aktif --}}
    <div class="col-6 col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-user-check"></i></div>
            <div class="stat-body">
                <div class="stat-value" id="val-active">
                    <div class="skeleton short"></div>
                </div>
                <div class="stat-label">Pelanggan Aktif</div>
                <div id="pct-active" class="stat-sub neutral d-none">
                    <i class="fas fa-chart-pie"></i> <span></span>% dari total
                </div>
            </div>
        </div>
    </div>

    {{-- Connecting (Reactivated) --}}
    <div class="col-6 col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-sync-alt"></i></div>
            <div class="stat-body">
                <div class="stat-value" id="val-connecting">
                    <div class="skeleton short"></div>
                </div>
                <div class="stat-label">Connecting</div>
                <div class="stat-sub neutral" style="margin-top:5px">
                    <i class="fas fa-redo"></i> Proses reaktivasi
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue Bulan Ini --}}
    <div class="col-6 col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon teal"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-body">
                <div class="stat-value revenue" id="val-revenue">
                    <div class="skeleton short"></div>
                </div>
                <div class="stat-label">Revenue Bulan Ini</div>
                <div id="rev-growth-badge" class="stat-sub neutral d-none">
                    <i class="fas fa-arrow-up" id="rev-growth-icon"></i> <span id="rev-growth-val"></span>%
                    vs bulan lalu
                </div>
            </div>
        </div>
    </div>

    {{-- Mau Daftar --}}
    <div class="col-6 col-md-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-user-plus"></i></div>
            <div class="stat-body">
                <div class="stat-value" id="val-register">
                    <div class="skeleton short"></div>
                </div>
                <div class="stat-label">Dalam Proses Daftar</div>
                <div id="val-pending-note" class="stat-sub neutral d-none">
                    <i class="fas fa-clock"></i> <span></span> pending
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ROW 2 – Secondary Stats --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="section-label">Detail Status Isolir & Churn</div>
<div class="row mb-4" id="stat-cards-row2">

    <div class="col-6 col-lg-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-calendar-times"></i></div>
            <div class="stat-body">
                <div class="stat-value" id="val-expired">–</div>
                <div class="stat-label">Expired</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-pause-circle"></i></div>
            <div class="stat-body">
                <div class="stat-value" id="val-suspended">–</div>
                <div class="stat-label">Suspend / Isolir</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon gray"><i class="fas fa-plug"></i></div>
            <div class="stat-body">
                <div class="stat-value" id="val-disconnected">–</div>
                <div class="stat-label">Disconnected</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3 mb-3">
        <div class="stat-card">
            <div class="stat-icon pink"><i class="fas fa-ban"></i></div>
            <div class="stat-body">
                <div class="stat-value" id="val-cancelled">–</div>
                <div class="stat-label">Cancelled / Closed</div>
            </div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ROW 3 – Revenue Chart + Customer Status + Pipeline --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="row mb-4">

    {{-- Revenue Chart ─────────────────────────────────────── --}}
    <div class="col-lg-5 mb-3">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h5><i class="fas fa-chart-bar text-primary"></i> Revenue 6 Bulan Terakhir</h5>
                <button class="btn-refresh" id="btn-refresh" title="Refresh data">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="dash-card-body">
                <div id="rev-mini-card">
                    <div class="rev-mini">
                        <div class="rev-label">Revenue Bulan Ini</div>
                        <div class="rev-value" id="rev-mini-val">–</div>
                        <div class="rev-growth" id="rev-mini-growth">
                            <i class="fas fa-circle-notch fa-spin"></i> memuat…
                        </div>
                    </div>
                </div>
                <div class="chart-wrap">
                    <canvas id="revenueChart"></canvas>
                </div>
                <div class="last-updated text-right mt-2" id="last-updated-label">–</div>
            </div>
        </div>
    </div>

    {{-- Customer Status ────────────────────────────────────── --}}
    <div class="col-lg-4 mb-3">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h5><i class="fas fa-chart-pie text-success"></i> Distribusi Status</h5>
            </div>
            <div class="dash-card-body" id="status-distribution">
                {{-- filled by JS --}}
                @for ($i=0; $i<5; $i++)
                <div class="status-row">
                    <div class="s-label"><div class="skeleton" style="width:50%"></div><div class="skeleton" style="width:20%"></div></div>
                    <div class="progress"><div class="progress-bar bg-secondary" style="width:60%"></div></div>
                </div>
                @endfor
            </div>
        </div>
    </div>

    {{-- Registration Pipeline ─────────────────────────────── --}}
    <div class="col-lg-3 mb-3">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h5><i class="fas fa-stream text-warning"></i> Pipeline Daftar</h5>
            </div>
            <div class="dash-card-body" id="pipeline-list">
                <div class="skeleton mb-2" style="height:50px; border-radius:10px"></div>
                <div class="skeleton mb-2" style="height:50px; border-radius:10px"></div>
                <div class="skeleton mb-2" style="height:50px; border-radius:10px"></div>
                <div class="skeleton" style="height:50px; border-radius:10px"></div>
            </div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ROW 4 – Recent Registrations + Top Packages --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="row mb-4">

    {{-- Recent Registrations ──────────────────────────────── --}}
    <div class="col-lg-8 mb-3">
        <div class="dash-card">
            <div class="dash-card-header">
                <h5><i class="fas fa-user-clock text-info"></i> Pendaftaran Terbaru</h5>
                <a href="{{ route('internet-customer.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px; font-size:.75rem;">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div id="recent-reg-body">
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-circle-notch fa-spin fa-2x mb-2 text-primary"></i>
                    <div style="font-size:.8rem">Memuat data…</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Packages ──────────────────────────────────────── --}}
    <div class="col-lg-4 mb-3">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h5><i class="fas fa-trophy text-warning"></i> Top Paket</h5>
            </div>
            <div class="dash-card-body" id="top-packages-list">
                <div class="skeleton mb-2" style="height:44px; border-radius:10px"></div>
                <div class="skeleton mb-2" style="height:44px; border-radius:10px"></div>
                <div class="skeleton mb-2" style="height:44px; border-radius:10px"></div>
                <div class="skeleton" style="height:44px; border-radius:10px"></div>
            </div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ROW 5 – Asset Summary + ROI --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="section-label">Investasi Aset & ROI</div>
<div class="row mb-4">

    {{-- ROI Card ─────────────────────────────────────────────── --}}
    <div class="col-lg-4 mb-3">
        <div style="background:linear-gradient(135deg,#0f2544,#1e4976);border-radius:14px;padding:24px;color:#fff;height:100%">
            <div style="font-size:.72rem;opacity:.65;text-transform:uppercase;letter-spacing:1px;margin-bottom:10px">
                <i class="fas fa-chart-pie mr-1"></i>Estimasi Return on Investment
            </div>
            <div id="roi-months-val" style="font-size:2.4rem;font-weight:900;line-height:1">–</div>
            <div id="roi-years-val" style="font-size:.82rem;opacity:.7;margin-top:4px">Memuat…</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px">
                <div style="background:rgba(255,255,255,.08);border-radius:10px;padding:10px 12px">
                    <div style="font-size:.68rem;opacity:.6">Total Modal Aset</div>
                    <div id="roi-asset-val" style="font-size:.9rem;font-weight:700;margin-top:2px">–</div>
                </div>
                <div style="background:rgba(255,255,255,.08);border-radius:10px;padding:10px 12px">
                    <div style="font-size:.68rem;opacity:.6">MRR Bulan Lalu</div>
                    <div id="roi-mrr-val" style="font-size:.9rem;font-weight:700;margin-top:2px">–</div>
                </div>
            </div>

            <div style="margin-top:14px">
                <div style="display:flex;justify-content:space-between;font-size:.72rem;opacity:.65;margin-bottom:5px">
                    <span>Progress Recovery (per tahun)</span>
                    <span id="roi-pct-label">0%</span>
                </div>
                <div style="background:rgba(255,255,255,.15);border-radius:20px;height:6px">
                    <div id="roi-progress-bar" style="background:linear-gradient(90deg,#60a5fa,#34d399);height:6px;border-radius:20px;width:0%;transition:width .5s"></div>
                </div>
            </div>

            <div style="margin-top:16px">
                <a href="{{ route('internet-report.index') }}"
                   style="font-size:.75rem;color:rgba(255,255,255,.7);text-decoration:none">
                    <i class="fas fa-external-link-alt mr-1"></i>Lihat Laporan Lengkap
                </a>
            </div>
        </div>
    </div>

    {{-- Asset Stats ───────────────────────────────────────────── --}}
    <div class="col-lg-4 mb-3">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h5><i class="fas fa-hdd text-primary"></i> Ringkasan Aset</h5>
                <a href="{{ route('internet-asset.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.72rem">
                    Kelola <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="dash-card-body" id="asset-summary-box">
                <div class="skeleton mb-3" style="height:60px;border-radius:10px"></div>
                <div class="skeleton mb-3" style="height:60px;border-radius:10px"></div>
            </div>
        </div>
    </div>

    {{-- Quick Links ───────────────────────────────────────────── --}}
    <div class="col-lg-4 mb-3">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h5><i class="fas fa-rocket text-success"></i> Aksi Cepat</h5>
            </div>
            <div class="dash-card-body">
                <a href="{{ route('internet-customer.index') }}" class="quick-link-btn">
                    <i class="fas fa-users"></i> Data Pelanggan
                </a>
                @canAccess('index','internet_assets')
                <a href="{{ route('internet-asset.create') }}" class="quick-link-btn">
                    <i class="fas fa-plus-circle"></i> Tambah Aset
                </a>
                @endcanAccess
                <a href="{{ route('internet-report.index') }}" class="quick-link-btn">
                    <i class="fas fa-chart-bar"></i> Laporan & ROI
                </a>
                <a href="{{ route('internet-package.index') }}" class="quick-link-btn">
                    <i class="fas fa-box"></i> Paket Internet
                </a>
            </div>
        </div>
    </div>

</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
(function () {
    'use strict';

    const API_URL  = '{{ route("home.internet-report") }}';
    const CSRF     = document.querySelector('meta[name="csrf-token"]').content;

    let revenueChart = null;

    // ── Helpers ──────────────────────────────────────────────────────
    function fmtRupiah(val) {
        if (val >= 1_000_000_000) return 'Rp ' + (val / 1_000_000_000).toFixed(1) + 'M';
        if (val >= 1_000_000)     return 'Rp ' + (val / 1_000_000).toFixed(1)     + 'Jt';
        if (val >= 1_000)         return 'Rp ' + (val / 1_000).toFixed(0)          + 'rb';
        return 'Rp ' + val.toLocaleString('id-ID');
    }

    function fmtFull(val) {
        return 'Rp ' + parseFloat(val).toLocaleString('id-ID', { minimumFractionDigits: 0 });
    }

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function setHtml(id, html) {
        const el = document.getElementById(id);
        if (el) el.innerHTML = html;
    }

    function show(id) {
        const el = document.getElementById(id);
        if (el) el.classList.remove('d-none');
    }

    function statusLabel(s) {
        const map = {
            pending:                      'Pending',
            waiting_payment_subscription: 'Menunggu Pembayaran',
            waiting_payment_confirmation: 'Konfirmasi Pembayaran',
            process_installation:         'Proses Instalasi',
            installed:                    'Sudah Terpasang',
            active:                       'Aktif',
            reactivated:                  'Connecting',
            expired:                      'Expired',
            suspended:                    'Suspend',
            disconnected:                 'Disconnected',
            cancelled:                    'Cancelled',
        };
        return map[s] || s;
    }

    // ── Render functions ─────────────────────────────────────────────
    function renderStatusDistribution(customer, totalAll) {
        const items = [
            { key: 'active',      label: 'Aktif',         color: '#16a34a', val: customer.total_active },
            { key: 'connecting',  label: 'Connecting',    color: '#2563eb', val: customer.total_connecting || 0 },
            { key: 'expired',     label: 'Expired',       color: '#dc2626', val: customer.total_expired },
            { key: 'suspended',   label: 'Suspend',       color: '#ea7c00', val: customer.total_suspended },
            { key: 'disconnected',label: 'Disconnected',  color: '#6b7280', val: customer.total_disconnected },
            { key: 'cancelled',   label: 'Cancelled',     color: '#db2777', val: customer.total_cancelled + customer.total_closed },
        ];

        const total = totalAll || 1;
        let html = '';
        items.forEach(item => {
            const pct = Math.round((item.val / total) * 100);
            html += `
            <div class="status-row">
                <div class="s-label">
                    <span><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${item.color};margin-right:6px"></span>${item.label}</span>
                    <span>${item.val.toLocaleString('id-ID')} <small class="text-muted">(${pct}%)</small></span>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width:${pct}%;background:${item.color}"></div>
                </div>
            </div>`;
        });
        setHtml('status-distribution', html);
    }

    function renderPipeline(reg) {
        const steps = [
            { key: 'pending',                     label: 'Pendaftaran Baru',       sub: 'Belum bayar',              color: '#f59e0b', val: reg.pending },
            { key: 'waiting_payment_subscription', label: 'Menunggu Pembayaran',    sub: 'Tagihan dikirim',           color: '#3b82f6', val: reg.waiting_payment_subscription },
            { key: 'waiting_payment_confirmation', label: 'Konfirmasi Pembayaran',  sub: 'Bukti sudah diupload',      color: '#8b5cf6', val: reg.waiting_payment_confirmation },
            { key: 'process_installation',         label: 'Proses Instalasi',       sub: 'Teknisi sedang bekerja',    color: '#ec4899', val: reg.process_installation },
            { key: 'installed',                    label: 'Terpasang',              sub: 'Menunggu aktivasi',         color: '#06b6d4', val: reg.installed },
        ];

        let html = '';
        steps.forEach(s => {
            html += `
            <div class="pipeline-item">
                <div class="pipeline-dot" style="background:${s.color}"></div>
                <div class="pipeline-info">
                    <strong>${s.label}</strong>
                    <small>${s.sub}</small>
                </div>
                <div class="pipeline-count">${s.val}</div>
            </div>`;
        });

        const totalHtml = `
        <div class="pipeline-item" style="background:#f8fafc;border-radius:10px;margin-top:10px;padding:10px 12px;">
            <div class="pipeline-dot" style="background:#1e293b"></div>
            <div class="pipeline-info">
                <strong>Total Pipeline</strong>
                <small>Semua proses aktif</small>
            </div>
            <div class="pipeline-count" style="color:#2563eb">${reg.total}</div>
        </div>`;

        setHtml('pipeline-list', html + totalHtml);
    }

    function renderRecentRegistrations(rows) {
        if (!rows || rows.length === 0) {
            setHtml('recent-reg-body', '<div class="p-4 text-center text-muted" style="font-size:.8rem">Tidak ada data pendaftaran terbaru.</div>');
            return;
        }
        let html = `
        <div class="table-responsive">
            <table class="table dash-table mb-0">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Paket</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>`;
        rows.forEach(r => {
            const badgeClass = r.status in {
                pending:1, waiting_payment_subscription:1, waiting_payment_confirmation:1,
                process_installation:1, installed:1, active:1, expired:1, suspended:1
            } ? r.status : 'default';

            html += `
            <tr>
                <td><a href="/internet-customer/${r.id}" class="text-primary font-weight-bold">${r.code || '-'}</a></td>
                <td>${r.name}</td>
                <td class="text-muted">${r.package}</td>
                <td><span class="s-badge ${badgeClass}">${statusLabel(r.status)}</span></td>
                <td class="text-muted">${r.created}</td>
            </tr>`;
        });
        html += '</tbody></table></div>';
        setHtml('recent-reg-body', html);
    }

    function renderTopPackages(pkgs) {
        if (!pkgs || pkgs.length === 0) {
            setHtml('top-packages-list', '<div class="text-muted text-center" style="font-size:.8rem">Tidak ada data.</div>');
            return;
        }
        const rankClass = ['gold', 'silver', 'bronze'];
        let html = '';
        pkgs.forEach((p, i) => {
            html += `
            <div class="pkg-row">
                <div class="pkg-rank ${rankClass[i] || ''}">${i + 1}</div>
                <div class="pkg-info">
                    <strong>${p.name}</strong>
                    <small>${p.price > 0 ? fmtFull(p.price) + '/bln' : 'Harga belum diset'}</small>
                </div>
                <div class="pkg-total">${p.total} <span class="text-muted" style="font-size:.7rem">pelanggan</span></div>
            </div>`;
        });
        setHtml('top-packages-list', html);
    }

    function renderRevenueChart(monthly) {
        const labels = monthly.map(m => m.label);
        const values = monthly.map(m => m.value);

        const ctx = document.getElementById('revenueChart').getContext('2d');

        if (revenueChart) revenueChart.destroy();

        revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Revenue',
                    data: values,
                    backgroundColor: values.map((v, i) =>
                        i === values.length - 1
                            ? 'rgba(37,99,232,0.9)'
                            : 'rgba(37,99,232,0.25)'
                    ),
                    borderRadius: 7,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => fmtFull(ctx.raw)
                        }
                    }
                },
                scales: {
                    y: {
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: {
                            color: '#94a3b8',
                            font: { size: 10 },
                            callback: v => fmtRupiah(v)
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 10 } }
                    }
                }
            }
        });
    }

    // ── ROI & Asset renderers ─────────────────────────────────────────
    function renderROI(roi) {
        if (roi.roi_months !== null && roi.roi_months !== undefined) {
            setText('roi-months-val', roi.roi_months + ' bulan');
            setText('roi-years-val',  '≈ ' + roi.roi_years + ' tahun untuk balik modal');
        } else {
            setText('roi-months-val', '–');
            setText('roi-years-val',  'Belum ada data aset atau revenue');
        }
        setText('roi-asset-val', fmtRupiah(roi.total_asset));
        setText('roi-mrr-val',   fmtRupiah(roi.mrr));
        const pct = roi.recovered_pct || 0;
        setText('roi-pct-label', pct + '%');
        const bar = document.getElementById('roi-progress-bar');
        if (bar) bar.style.width = Math.min(pct, 100) + '%';
    }

    function renderAssetSummary(asset) {
        const items = [
            { icon:'fa-boxes',         color:'#2563eb', label:'Total Aset',     val: asset.total_count + ' unit' },
            { icon:'fa-coins',         color:'#0891b2', label:'Nilai Total',    val: fmtRupiah(asset.total_value) },
            { icon:'fa-check-circle',  color:'#16a34a', label:'Nilai Aktif',    val: fmtRupiah(asset.active_value) },
            { icon:'fa-tools',         color:'#dc2626', label:'Rusak',          val: asset.damaged_count + ' unit' },
        ];
        let html = '';
        items.forEach(it => {
            html += `
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center" style="gap:10px">
                    <div style="width:36px;height:36px;border-radius:10px;background:${it.color}18;color:${it.color};
                                display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0">
                        <i class="fas ${it.icon}"></i>
                    </div>
                    <span style="font-size:.8rem;color:#64748b">${it.label}</span>
                </div>
                <strong style="font-size:.85rem;color:#1e293b">${it.val}</strong>
            </div>`;
        });
        setHtml('asset-summary-box', html);
    }

    // ── Main fetch ────────────────────────────────────────────────────
    function loadReport() {
        const btn = document.getElementById('btn-refresh');
        if (btn) btn.classList.add('spinning');

        fetch(API_URL, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(json => {
            if (!json.success) return;
            const d = json.data;
            const c = d.customer;
            const reg = d.registration;
            const rev = d.revenue;

            // ── Main stat cards ──────────────────────────────────────
            setText('val-total-all',  c.total_all.toLocaleString('id-ID'));
            setText('val-active',     c.total_active.toLocaleString('id-ID'));
            setText('val-connecting', (c.total_connecting || 0).toLocaleString('id-ID'));
            setText('val-revenue',    fmtRupiah(rev.this_month));
            setText('val-register',   reg.total.toLocaleString('id-ID'));

            // Sub badges
            const newEl = document.getElementById('val-new-month');
            if (newEl) {
                newEl.querySelector('span').textContent = c.new_this_month;
                newEl.classList.remove('d-none');
            }

            if (c.total_all > 0) {
                const pctEl = document.getElementById('pct-active');
                if (pctEl) {
                    pctEl.querySelector('span').textContent = Math.round((c.total_active / c.total_all) * 100);
                    pctEl.classList.remove('d-none');
                }
            }

            const growthEl = document.getElementById('rev-growth-badge');
            if (growthEl) {
                const growthIcon = document.getElementById('rev-growth-icon');
                const growthVal  = document.getElementById('rev-growth-val');
                const isUp = rev.growth_pct >= 0;
                growthEl.className = 'stat-sub ' + (isUp ? 'up' : 'down');
                growthIcon.className = 'fas fa-arrow-' + (isUp ? 'up' : 'down');
                growthVal.textContent = Math.abs(rev.growth_pct);
                growthEl.classList.remove('d-none');
            }

            const pendingNoteEl = document.getElementById('val-pending-note');
            if (pendingNoteEl) {
                pendingNoteEl.querySelector('span').textContent = reg.pending;
                pendingNoteEl.classList.remove('d-none');
            }

            // ── Secondary cards ──────────────────────────────────────
            setText('val-expired',      c.total_expired.toLocaleString('id-ID'));
            setText('val-suspended',    c.total_suspended.toLocaleString('id-ID'));
            setText('val-disconnected', c.total_disconnected.toLocaleString('id-ID'));
            setText('val-cancelled',    (c.total_cancelled + c.total_closed).toLocaleString('id-ID'));

            // ── Revenue mini card ────────────────────────────────────
            setText('rev-mini-val', fmtFull(rev.this_month));
            const growth = rev.growth_pct;
            const isUp   = growth >= 0;
            setHtml('rev-mini-growth', `
                <i class="fas fa-arrow-${isUp ? 'up' : 'down'}"></i>
                ${Math.abs(growth)}% vs bulan lalu (${fmtRupiah(rev.last_month)})
            `);

            // ── Chart ─────────────────────────────────────────────────
            renderRevenueChart(rev.monthly_chart);

            // ── Other sections ────────────────────────────────────────
            renderStatusDistribution(c, c.total_all);
            renderPipeline(reg);
            renderRecentRegistrations(d.recent_registrations);
            renderTopPackages(d.top_packages);
            if (d.roi)   renderROI(d.roi);
            if (d.asset) renderAssetSummary(d.asset);

            // ── Timestamp ─────────────────────────────────────────────
            const now = new Date();
            setText('last-updated-label',
                'Diperbarui: ' + now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
            );
        })
        .catch(err => {
            console.error('internet-report error:', err);
            setHtml('recent-reg-body',
                '<div class="p-3 text-center text-danger" style="font-size:.8rem"><i class="fas fa-exclamation-triangle mr-1"></i>Gagal memuat data. Coba refresh.</div>'
            );
        })
        .finally(() => {
            if (btn) btn.classList.remove('spinning');
        });
    }

    // ── Init ──────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        loadReport();
        document.getElementById('btn-refresh').addEventListener('click', loadReport);
    });
})();
</script>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css" crossorigin="anonymous">
<style>
    /* ── Base overrides ─────────────────────────────────────── */
    body { background: #f0f2f8 !important; }
    .wrapper { background: #f0f2f8 !important; }
    .content-wrapper { background: #f0f2f8 !important; }

    /* ── Section header ─────────────────────────────────────── */
    .page-header-custom {
        background: linear-gradient(135deg, #1a3a5c 0%, #2563a8 100%);
        border-radius: 16px;
        padding: 24px 28px;
        margin-bottom: 24px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .page-header-custom::after {
        content: '';
        position: absolute;
        right: -40px; top: -40px;
        width: 180px; height: 180px;
        background: rgba(255,255,255,.07);
        border-radius: 50%;
    }
    .page-header-custom h2 { font-size: 1.5rem; font-weight: 700; margin: 0 0 4px; }
    .page-header-custom p  { margin: 0; opacity: .75; font-size: .875rem; }

    /* ── Section label ──────────────────────────────────────── */
    .section-label {
        font-size: .7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1.2px;
        color: #8898aa; margin-bottom: 10px; padding-left: 2px;
    }

    /* ── Stat Cards ─────────────────────────────────────────── */
    .stat-card {
        background: #fff;
        border-radius: 14px;
        padding: 20px 22px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 0;
        transition: transform .15s, box-shadow .15s;
        height: 100%;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.1); }
    .stat-icon {
        width: 54px; height: 54px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.4rem; flex-shrink: 0;
    }
    .stat-icon.green  { background: #e6f9f0; color: #16a34a; }
    .stat-icon.blue   { background: #e0f0ff; color: #2563eb; }
    .stat-icon.orange { background: #fff3e0; color: #ea7c00; }
    .stat-icon.red    { background: #fde8e8; color: #dc2626; }
    .stat-icon.purple { background: #f3e8ff; color: #7c3aed; }
    .stat-icon.teal   { background: #e0fafa; color: #0891b2; }
    .stat-icon.gray   { background: #f1f3f5; color: #6b7280; }
    .stat-icon.pink   { background: #fce7f3; color: #db2777; }

    .stat-body { flex: 1; min-width: 0; }
    .stat-value {
        font-size: 1.6rem; font-weight: 800; line-height: 1.1;
        color: #1e293b; white-space: nowrap;
    }
    .stat-value.revenue { font-size: 1.15rem; }
    .stat-label { font-size: .78rem; color: #64748b; margin-top: 2px; }
    .stat-sub {
        font-size: .72rem; margin-top: 5px;
        display: inline-flex; align-items: center; gap: 3px;
        padding: 2px 8px; border-radius: 20px; font-weight: 600;
    }
    .stat-sub.up   { background: #e6f9f0; color: #16a34a; }
    .stat-sub.down { background: #fde8e8; color: #dc2626; }
    .stat-sub.neutral { background: #f1f3f5; color: #64748b; }

    /* ── Skeleton loader ────────────────────────────────────── */
    .skeleton {
        background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.5s infinite;
        border-radius: 6px;
        height: 20px;
    }
    .skeleton.short { width: 60%; }
    @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

    /* ── Content cards ──────────────────────────────────────── */
    .dash-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        overflow: hidden;
    }
    .dash-card .dash-card-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between;
    }
    .dash-card .dash-card-header h5 {
        font-size: .9rem; font-weight: 700; color: #1e293b; margin: 0;
        display: flex; align-items: center; gap: 8px;
    }
    .dash-card .dash-card-header h5 i { font-size: .85rem; }
    .dash-card .dash-card-body { padding: 18px 20px; }

    /* ── Revenue chart ──────────────────────────────────────── */
    .chart-wrap { position: relative; height: 220px; }

    /* ── Status progress bars ───────────────────────────────── */
    .status-row { margin-bottom: 14px; }
    .status-row:last-child { margin-bottom: 0; }
    .status-row .s-label {
        display: flex; justify-content: space-between;
        font-size: .78rem; color: #475569; margin-bottom: 5px;
    }
    .status-row .s-label span:last-child { font-weight: 700; color: #1e293b; }
    .progress { border-radius: 20px; height: 8px !important; background: #f1f5f9; }
    .progress-bar { border-radius: 20px; }

    /* ── Registration pipeline ──────────────────────────────── */
    .pipeline-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid #f8fafc;
    }
    .pipeline-item:last-child { border-bottom: none; }
    .pipeline-dot {
        width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
    }
    .pipeline-info { flex: 1; }
    .pipeline-info strong { font-size: .82rem; color: #1e293b; display: block; }
    .pipeline-info small { font-size: .72rem; color: #94a3b8; }
    .pipeline-count {
        font-size: 1.1rem; font-weight: 800; color: #1e293b;
        min-width: 36px; text-align: right;
    }

    /* ── Table styling ──────────────────────────────────────── */
    .dash-table { font-size: .8rem; }
    .dash-table thead th {
        background: #f8fafc; font-size: .7rem;
        text-transform: uppercase; letter-spacing: .8px;
        color: #64748b; border-bottom: 1px solid #e2e8f0;
        padding: 10px 14px; font-weight: 600; border-top: none;
    }
    .dash-table tbody td {
        padding: 10px 14px; border-color: #f1f5f9;
        vertical-align: middle; color: #334155;
    }
    .dash-table tbody tr:hover td { background: #fafbfc; }

    /* ── Status badges ──────────────────────────────────────── */
    .s-badge {
        font-size: .68rem; padding: 3px 9px; border-radius: 20px; font-weight: 600;
    }
    .s-badge.pending             { background: #fef9ec; color: #b45309; }
    .s-badge.waiting_payment_subscription  { background: #e0f0ff; color: #1d4ed8; }
    .s-badge.waiting_payment_confirmation  { background: #f3e8ff; color: #6d28d9; }
    .s-badge.process_installation          { background: #fff0f6; color: #be185d; }
    .s-badge.installed           { background: #e0fafa; color: #0e7490; }
    .s-badge.active              { background: #ecfdf5; color: #065f46; }
    .s-badge.expired             { background: #fde8e8; color: #991b1b; }
    .s-badge.suspended           { background: #fff3e0; color: #92400e; }
    .s-badge.default             { background: #f1f5f9; color: #475569; }

    /* ── Top packages ──────────────────────────────────────── */
    .pkg-row {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 0; border-bottom: 1px solid #f8fafc;
    }
    .pkg-row:last-child { border-bottom: none; }
    .pkg-rank {
        width: 22px; height: 22px; border-radius: 6px;
        background: #f1f5f9; color: #64748b;
        font-size: .68rem; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .pkg-rank.gold   { background: #fef3c7; color: #92400e; }
    .pkg-rank.silver { background: #e2e8f0; color: #475569; }
    .pkg-rank.bronze { background: #fde8d0; color: #92400e; }
    .pkg-info { flex: 1; min-width: 0; }
    .pkg-info strong { font-size: .8rem; color: #1e293b; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pkg-info small  { font-size: .72rem; color: #94a3b8; }
    .pkg-total { font-size: .85rem; font-weight: 700; color: #1e293b; }

    /* ── Revenue mini cards ─────────────────────────────────── */
    .rev-mini {
        background: linear-gradient(135deg, #1a3a5c, #2563a8);
        border-radius: 12px; padding: 18px 20px; color: #fff; margin-bottom: 16px;
    }
    .rev-mini .rev-label { font-size: .75rem; opacity: .7; margin-bottom: 6px; }
    .rev-mini .rev-value { font-size: 1.3rem; font-weight: 800; }
    .rev-mini .rev-growth {
        font-size: .72rem; margin-top: 6px;
        display: inline-flex; align-items: center; gap: 4px;
        background: rgba(255,255,255,.15); padding: 2px 10px; border-radius: 20px;
    }

    /* ── Refresh button ─────────────────────────────────────── */
    .btn-refresh {
        background: #f1f5f9; border: none; color: #64748b;
        padding: 5px 10px; border-radius: 8px; font-size: .78rem;
        cursor: pointer; transition: background .15s;
    }
    .btn-refresh:hover { background: #e2e8f0; color: #1e293b; }
    .btn-refresh.spinning i { animation: spin .6s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ── Last updated label ─────────────────────────────────── */
    .last-updated { font-size: .7rem; color: #94a3b8; }

    /* ── Quick link buttons ─────────────────────────────────── */
    .quick-link-btn {
        display: flex; align-items: center; gap: 10px;
        padding: 11px 14px; border-radius: 10px;
        background: #f8fafc; color: #334155;
        text-decoration: none; font-size: .83rem; font-weight: 600;
        margin-bottom: 8px; transition: background .15s, color .15s;
        border: 1.5px solid #e8ecf4;
    }
    .quick-link-btn:hover { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; text-decoration: none; }
    .quick-link-btn i { font-size: .9rem; width: 20px; text-align: center; }

    /* ── Responsive tweaks ──────────────────────────────────── */
    @media (max-width: 575px) {
        .stat-value { font-size: 1.3rem; }
        .stat-value.revenue { font-size: 1rem; }
        .rev-mini .rev-value { font-size: 1.1rem; }
    }
</style>
@stop