@extends('adminlte::page')

@section('title', 'Laporan Internet')

@section('content_header')
<div style="margin-top:-8px"></div>
@stop

@section('content')

{{-- ── Page Header ── --}}
<div class="rpt-header">
    <h3><i class="fas fa-chart-line mr-2"></i>Laporan Internet</h3>
    <p>Analisis pendapatan, pelanggan, aset, dan Return on Investment (ROI)</p>
</div>

{{-- ── Date Range ── --}}
<div class="date-range-bar">
    <label>Dari:</label>
    <input type="date" id="rpt-from" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
    <label>Sampai:</label>
    <input type="date" id="rpt-to"   class="form-control" value="{{ now()->endOfMonth()->format('Y-m-d') }}">

    {{-- Grouping filter --}}
    <label class="ml-2">Grouping:</label>
    <select id="rpt-grouping" class="form-control" style="max-width:200px">
        <option value="all">Semua Grouping</option>
        <option value="none">— Tanpa Grouping</option>
    </select>

    <button class="btn-load" onclick="loadReport()">
        <i class="fas fa-search mr-1"></i> Tampilkan
    </button>
    <div class="ml-auto d-flex gap-2">
        <button class="btn btn-sm btn-outline-secondary" onclick="setRange('this_month')">Bulan Ini</button>
        <button class="btn btn-sm btn-outline-secondary" onclick="setRange('last_month')">Bulan Lalu</button>
        <button class="btn btn-sm btn-outline-secondary" onclick="setRange('this_year')">Tahun Ini</button>
    </div>
</div>

{{-- ── Section: ROI + KPI Income ── --}}
<div class="section-head">Ringkasan Utama</div>
<div class="row mb-4">

    {{-- ROI Card --}}
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="roi-card">
            <div style="font-size:.72rem;opacity:.65;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px">
                <i class="fas fa-chart-pie mr-1"></i>Estimasi ROI
            </div>
            <div class="roi-val" id="roi-months">–</div>
            <div class="roi-lbl" id="roi-label">Memuat…</div>
            <div style="margin-top:14px;background:rgba(255,255,255,.08);border-radius:8px;padding:10px 12px">
                <div style="font-size:.7rem;opacity:.65">Modal Aset Aktif</div>
                <div style="font-size:.92rem;font-weight:700" id="roi-asset-val">–</div>
                <div style="font-size:.7rem;opacity:.65;margin-top:6px">MRR Bulan Lalu</div>
                <div style="font-size:.92rem;font-weight:700" id="roi-mrr-val">–</div>
            </div>
        </div>
    </div>

    {{-- Total Pendapatan --}}
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="kpi-card" style="border-left-color:#16a34a">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px">
                <i class="fas fa-money-bill-wave mr-1 text-success"></i>Total Pendapatan
            </div>
            <div class="kpi-val" id="kpi-income">
                <div class="skeleton" style="width:70%"></div>
            </div>
            <div class="kpi-lbl" id="kpi-income-period">–</div>
            <div id="kpi-income-tx" class="kpi-sub info d-none">
                <i class="fas fa-receipt"></i><span></span> transaksi
            </div>
        </div>
    </div>

    {{-- ARPU --}}
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="kpi-card" style="border-left-color:#2563eb">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px">
                <i class="fas fa-user-tag mr-1 text-primary"></i>ARPU
            </div>
            <div class="kpi-val md" id="kpi-arpu">
                <div class="skeleton" style="width:60%"></div>
            </div>
            <div class="kpi-lbl">Avg Revenue Per Active Customer</div>
            <div id="kpi-active-count" class="kpi-sub info d-none">
                <i class="fas fa-users"></i><span></span> pelanggan aktif
            </div>
        </div>
    </div>

    {{-- Customer Baru --}}
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="kpi-card" style="border-left-color:#7c3aed">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px">
                <i class="fas fa-user-plus mr-1 text-purple"></i>Pelanggan Baru
            </div>
            <div class="kpi-val" id="kpi-new-cust">
                <div class="skeleton" style="width:40%"></div>
            </div>
            <div class="kpi-lbl">Dalam periode yang dipilih</div>
            <div id="kpi-churn" class="kpi-sub warn d-none">
                <i class="fas fa-user-times"></i><span></span> churn
            </div>
        </div>
    </div>
</div>

{{-- ── Section: Pengeluaran ── --}}
<div class="section-head">Pengeluaran Langganan</div>
<div class="row mb-4">

    {{-- Total Pengeluaran --}}
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="kpi-card" style="border-left-color:#dc2626">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px">
                <i class="fas fa-arrow-circle-down mr-1 text-danger"></i>Total Pengeluaran
            </div>
            <div class="kpi-val" id="kpi-expense-total">
                <div class="skeleton" style="width:70%"></div>
            </div>
            <div class="kpi-lbl" id="kpi-expense-period">Dalam periode dipilih</div>
            <div id="kpi-expense-monthly" class="kpi-sub warn d-none">
                <i class="fas fa-calendar-alt"></i><span></span>/bulan
            </div>
        </div>
    </div>

    {{-- Net Income --}}
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="kpi-card" style="border-left-color:#0891b2">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px">
                <i class="fas fa-balance-scale mr-1 text-info"></i>Net Income
            </div>
            <div class="kpi-val md" id="kpi-net-income">
                <div class="skeleton" style="width:60%"></div>
            </div>
            <div class="kpi-lbl">Pendapatan – Pengeluaran</div>
        </div>
    </div>

    {{-- Breakdown DataCenter --}}
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="fas fa-server text-danger"></i> Data Center</h5>
            </div>
            <div class="card-body" id="expense-datacenter-list">
                <div class="skeleton mb-2"></div>
                <div class="skeleton mb-2"></div>
            </div>
        </div>
    </div>

    {{-- Breakdown POP --}}
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="fas fa-network-wired text-warning"></i> POP</h5>
            </div>
            <div class="card-body" id="expense-pop-list">
                <div class="skeleton mb-2"></div>
                <div class="skeleton mb-2"></div>
            </div>
        </div>
    </div>
</div>

{{-- ── Section: Charts ── --}}
<div class="section-head">Grafik Pendapatan & Pelanggan</div>
<div class="row mb-4">

    {{-- Monthly Income Chart --}}
    <div class="col-lg-8 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar text-primary"></i> Pendapatan per Bulan</h5>
            </div>
            <div class="card-body">
                <div class="chart-box"><canvas id="incomeChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Income by Package --}}
    <div class="col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="fas fa-layer-group text-success"></i> Top Paket</h5>
            </div>
            <div class="card-body" id="pkg-list">
                <div class="skeleton mb-2" style="height:40px;border-radius:8px"></div>
                <div class="skeleton mb-2" style="height:40px;border-radius:8px"></div>
                <div class="skeleton" style="height:40px;border-radius:8px"></div>
            </div>
        </div>
    </div>
</div>

{{-- ── Section: MRR Trend + Asset ── --}}
<div class="section-head">Tren MRR & Analisis Aset</div>
<div class="row mb-4">

    {{-- MRR Trend --}}
    <div class="col-lg-7 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="fas fa-chart-line text-info"></i> Tren MRR 12 Bulan Terakhir</h5>
            </div>
            <div class="card-body">
                <div class="chart-box"><canvas id="mrrChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Asset by Category --}}
    <div class="col-lg-5 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <h5><i class="fas fa-hdd text-warning"></i> Aset per Kategori</h5>
            </div>
            <div class="card-body">
                <div class="chart-box" style="height:200px"><canvas id="assetChart"></canvas></div>
                <div id="asset-summary-list" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

{{-- ── Section: Payment Method Table ── --}}
<div class="section-head">Detail Metode Pembayaran</div>
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-credit-card text-primary"></i> Pembayaran per Metode</h5>
            </div>
            <div id="method-table-wrap">
                <div class="p-4 text-center text-muted" style="font-size:.8rem">
                    <i class="fas fa-circle-notch fa-spin fa-2x d-block mb-2 text-primary"></i>Memuat…
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-users text-success"></i> Ringkasan Pelanggan</h5>
            </div>
            <div class="card-body" id="customer-summary">
                <div class="skeleton mb-2"></div>
                <div class="skeleton mb-2"></div>
                <div class="skeleton mb-2"></div>
            </div>
        </div>
    </div>
</div>
{{-- ── Section: Grouping ID ── --}}
<div class="section-head" id="section-grouping-head">Pelanggan per Grouping ID</div>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5><i class="fas fa-layer-group text-primary"></i> Breakdown per Grouping ID</h5>
                <small class="text-muted">Total pelanggan & pendapatan dalam periode terpilih</small>
            </div>
            <div id="grouping-table-wrap">
                <div class="p-4 text-center text-muted" style="font-size:.8rem">
                    <i class="fas fa-circle-notch fa-spin fa-2x d-block mb-2 text-primary"></i>Memuat…
                </div>
            </div>
        </div>
    </div>
</div>
{{-- ── Section: Payment Bulan Ini ── --}}
<div class="section-head" id="section-payment-head">Status Payment Bulan Ini</div>

{{-- Summary banner --}}
<div class="row mb-3" id="payment-summary-row">
    <div class="col-md-6 mb-3">
        <div class="kpi-card" style="border-left-color:#16a34a">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px">
                <i class="fas fa-check-circle mr-1 text-success"></i>Sudah Payment
                <span id="payment-month-label" class="text-muted font-weight-normal ml-1"></span>
            </div>
            <div class="kpi-val" id="paid-count"><div class="skeleton" style="width:40%"></div></div>
            <div class="kpi-lbl" id="paid-total">pelanggan unik &nbsp;•&nbsp; total: –</div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="kpi-card" style="border-left-color:#dc2626">
            <div style="font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px">
                <i class="fas fa-times-circle mr-1 text-danger"></i>Belum Payment
                <span class="text-muted font-weight-normal ml-1">(aktif &amp; connecting)</span>
            </div>
            <div class="kpi-val" id="unpaid-count"><div class="skeleton" style="width:40%"></div></div>
            <div class="kpi-lbl">pelanggan aktif belum ada pembayaran bulan ini</div>
        </div>
    </div>
</div>

<div class="row mb-4">
    {{-- Sudah Payment --}}
    <div class="col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="fas fa-check-circle text-success mr-1"></i>
                    Sudah Payment <span class="badge badge-success ml-1" id="paid-badge">0</span>
                </h5>
                <input type="text" id="search-paid" class="form-control form-control-sm"
                       style="width:160px" placeholder="Cari...">
            </div>
            <div id="paid-table-wrap" style="max-height:420px;overflow-y:auto">
                <div class="p-4 text-center text-muted"><i class="fas fa-circle-notch fa-spin fa-2x d-block mb-2 text-success"></i>Memuat…</div>
            </div>
        </div>
    </div>

    {{-- Belum Payment --}}
    <div class="col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="fas fa-times-circle text-danger mr-1"></i>
                    Belum Payment <span class="badge badge-danger ml-1" id="unpaid-badge">0</span>
                </h5>
                <input type="text" id="search-unpaid" class="form-control form-control-sm"
                       style="width:160px" placeholder="Cari...">
            </div>
            <div id="unpaid-table-wrap" style="max-height:420px;overflow-y:auto">
                <div class="p-4 text-center text-muted"><i class="fas fa-circle-notch fa-spin fa-2x d-block mb-2 text-danger"></i>Memuat…</div>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
(function(){
'use strict';

const API          = '{{ route("internet-report.data") }}';
const API_GROUPINGS = '{{ route("internet-report.groupings") }}';
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

let incomeChartInst = null, mrrChartInst = null, assetChartInst = null;

const COLOR_PALETTE = ['#2563eb','#16a34a','#f59e0b','#7c3aed','#0891b2','#dc2626','#ea7c00','#db2777','#6b7280','#0d9488'];

// ── Helpers ──────────────────────────────────────────────────────
function fmtRp(v) {
    if (v >= 1e9) return 'Rp ' + (v/1e9).toFixed(1) + 'M';
    if (v >= 1e6) return 'Rp ' + (v/1e6).toFixed(1) + 'Jt';
    if (v >= 1e3) return 'Rp ' + (v/1e3).toFixed(0) + 'rb';
    return 'Rp ' + v.toLocaleString('id-ID');
}
function fmtFull(v) { return 'Rp ' + parseFloat(v).toLocaleString('id-ID', {minimumFractionDigits:0}); }
function setText(id,v){ const e=document.getElementById(id); if(e) e.textContent=v; }
function setHtml(id,h){ const e=document.getElementById(id); if(e) e.innerHTML=h; }
function show(id)    { const e=document.getElementById(id); if(e) e.classList.remove('d-none'); }

function methodLabel(m) {
    const map={manual_transfer:'Transfer Manual',xendit:'Xendit',midtrans:'Midtrans',transfer:'Transfer Manual'};
    return map[m]||m||'Lainnya';
}

// ── Date helpers ─────────────────────────────────────────────────
function setRange(key) {
    const now  = new Date();
    const y    = now.getFullYear();
    const m    = now.getMonth();
    let from, to;
    if (key === 'this_month') {
        from = new Date(y, m, 1);
        to   = new Date(y, m+1, 0);
    } else if (key === 'last_month') {
        from = new Date(y, m-1, 1);
        to   = new Date(y, m, 0);
    } else if (key === 'this_year') {
        from = new Date(y, 0, 1);
        to   = new Date(y, 11, 31);
    }
    document.getElementById('rpt-from').value = from.toISOString().slice(0,10);
    document.getElementById('rpt-to').value   = to.toISOString().slice(0,10);
    loadReport();
}
window.setRange = setRange;

// ── Charts ───────────────────────────────────────────────────────
function makeBar(canvasId, labels, values, color) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
        type: 'bar',
        data: { labels, datasets: [{ label:'Pendapatan', data:values,
            backgroundColor: color || values.map((_,i)=>COLOR_PALETTE[i%COLOR_PALETTE.length]),
            borderRadius:6, borderSkipped:false }] },
        options: { responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{display:false},
                tooltip:{ callbacks:{ label: c=>fmtFull(c.raw) } } },
            scales:{
                y:{ grid:{color:'#f1f5f9'}, ticks:{color:'#94a3b8',font:{size:10},callback:v=>fmtRp(v)} },
                x:{ grid:{display:false}, ticks:{color:'#94a3b8',font:{size:10}} }
            }
        }
    });
}

function makeLine(canvasId, labels, values) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
        type:'line',
        data:{ labels, datasets:[{ label:'MRR', data:values,
            borderColor:'#2563eb', backgroundColor:'rgba(37,99,235,.1)',
            fill:true, tension:.3, pointRadius:3, pointBackgroundColor:'#2563eb' }] },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{display:false},
                tooltip:{ callbacks:{ label:c=>fmtFull(c.raw) } } },
            scales:{
                y:{ grid:{color:'#f1f5f9'}, ticks:{color:'#94a3b8',font:{size:10},callback:v=>fmtRp(v)} },
                x:{ grid:{display:false}, ticks:{color:'#94a3b8',font:{size:10}} }
            }
        }
    });
}

function makeDoughnut(canvasId, labels, values) {
    const ctx = document.getElementById(canvasId).getContext('2d');
    return new Chart(ctx, {
        type:'doughnut',
        data:{ labels, datasets:[{ data:values,
            backgroundColor:COLOR_PALETTE.slice(0,labels.length),
            borderWidth:2, borderColor:'#fff', hoverOffset:6 }] },
        options:{ responsive:true, maintainAspectRatio:false,
            plugins:{ legend:{ position:'right', labels:{font:{size:10},padding:10,color:'#475569'} },
                tooltip:{ callbacks:{ label:c=>`${c.label}: ${fmtFull(c.raw)}` } } }
        }
    });
}

// ── Render sections ───────────────────────────────────────────────
function renderROI(roi) {
    if (roi.roi_months !== null) {
        setText('roi-months', roi.roi_months + ' bulan');
        setText('roi-label', '≈ ' + roi.roi_years + ' tahun untuk balik modal');
    } else {
        setText('roi-months', '–');
        setText('roi-label', 'Belum ada data revenue atau aset');
    }
    setText('roi-asset-val', fmtFull(roi.total_asset));
    setText('roi-mrr-val',   fmtFull(roi.monthly_recurring));
}

function renderPackageList(pkgs) {
    if (!pkgs || !pkgs.length) {
        setHtml('pkg-list', '<div class="text-center text-muted p-3" style="font-size:.8rem">Tidak ada data</div>');
        return;
    }
    const max = Math.max(...pkgs.map(p=>p.total));
    let h = '';
    pkgs.forEach((p, i)=>{
        const pct = max > 0 ? Math.round((p.total/max)*100) : 0;
        h += `<div class="mb-3">
            <div class="d-flex justify-content-between mb-1" style="font-size:.78rem">
                <span><strong>#${i+1}</strong> ${p.package_name}</span>
                <span class="font-weight-bold">${fmtRp(p.total)}</span>
            </div>
            <div class="progress" style="height:6px">
                <div class="progress-bar" style="width:${pct}%;background:${COLOR_PALETTE[i%10]}"></div>
            </div>
            <small class="text-muted">${p.count} transaksi</small>
        </div>`;
    });
    setHtml('pkg-list', `<div class="card-body">${h}</div>`);
}

function renderMethodTable(methods) {
    if (!methods || !methods.length) {
        setHtml('method-table-wrap', '<div class="p-3 text-muted text-center" style="font-size:.8rem">Tidak ada data</div>');
        return;
    }
    let total = methods.reduce((s,m)=>s+(+m.total),0);
    let rows = methods.map(m=>`
        <tr>
            <td>${methodLabel(m.payment_method)}</td>
            <td class="text-right font-weight-bold">${fmtFull(m.total)}</td>
            <td class="text-right">${m.count} tx</td>
            <td class="text-right"><small>${total>0?Math.round(m.total/total*100):0}%</small></td>
        </tr>`).join('');
    setHtml('method-table-wrap', `
        <div class="table-responsive">
        <table class="table rpt-table mb-0">
            <thead><tr><th>Metode</th><th class="text-right">Total</th><th class="text-right">Tx</th><th class="text-right">%</th></tr></thead>
            <tbody>${rows}</tbody>
            <tfoot><tr style="background:#f8fafc;font-weight:700">
                <td>Total</td><td class="text-right">${fmtFull(total)}</td>
                <td class="text-right">${methods.reduce((s,m)=>s+m.count,0)} tx</td><td></td>
            </tr></tfoot>
        </table></div>`);
}

function renderCustomerSummary(c) {
    const items = [
        {icon:'fa-user-plus',  color:'#7c3aed', label:'Pelanggan Baru',  val:c.new},
        {icon:'fa-user-check', color:'#16a34a', label:'Aktif',            val:c.total_active_now},
        {icon:'fa-sync-alt',   color:'#2563eb', label:'Connecting',       val:c.total_connecting_now || 0},
        {icon:'fa-user-slash', color:'#dc2626', label:'Churn / Cabut',    val:c.churned},
        {icon:'fa-dollar-sign',color:'#0891b2', label:'MRR Bulan Lalu',   val:fmtRp(c.monthly_recurring)},
    ];
    let h = '';
    items.forEach(it=>{
        h += `<div class="d-flex align-items-center justify-content-between py-2" style="border-bottom:1px solid #f8fafc">
            <div class="d-flex align-items-center gap-2">
                <div style="width:32px;height:32px;border-radius:8px;background:${it.color}15;color:${it.color};display:flex;align-items:center;justify-content:center;font-size:.85rem">
                    <i class="fas ${it.icon}"></i>
                </div>
                <span style="font-size:.82rem;color:#475569">${it.label}</span>
            </div>
            <strong style="font-size:.9rem;color:#1e293b">${it.val}</strong>
        </div>`;
    });
    setHtml('customer-summary', h);
}

function renderAssetSummary(asset) {
    let h = '';
    asset.by_category.forEach((c,i)=>{
        h += `<div class="d-flex align-items-center justify-content-between py-1">
            <span style="font-size:.78rem;color:#475569">
                <span style="display:inline-block;width:8px;height:8px;border-radius:2px;background:${COLOR_PALETTE[i%10]};margin-right:4px"></span>
                ${c.label}
            </span>
            <div class="text-right">
                <div style="font-size:.8rem;font-weight:700;color:#1e293b">${fmtRp(c.value)}</div>
                <div style="font-size:.7rem;color:#94a3b8">${c.count} unit</div>
            </div>
        </div>`;
    });
    setHtml('asset-summary-list', h || '<div class="text-muted text-center" style="font-size:.8rem">Belum ada data aset</div>');
}

function renderGroupingTable(rows) {
    if (!rows || !rows.length) {
        setHtml('grouping-table-wrap', '<div class="p-3 text-muted text-center" style="font-size:.8rem">Tidak ada data grouping</div>');
        return;
    }
    const totalRevenue = rows.reduce((s, r) => s + r.revenue, 0);
    let tbody = '';
    rows.forEach((r, i) => {
        const isNoGroup = !r.grouping_id;
        const pct = totalRevenue > 0 ? Math.round(r.revenue / totalRevenue * 100) : 0;
        tbody += `<tr${isNoGroup ? ' style="opacity:.65"' : ''}>
            <td>
                ${isNoGroup
                    ? `<span class="text-muted" style="font-style:italic"><i class="fas fa-minus-circle mr-1" style="color:#94a3b8"></i>Tanpa Grouping</span>`
                    : `<span class="badge" style="background:#eff6ff;color:#1d4ed8;font-size:.78rem;padding:4px 10px;border-radius:20px;font-weight:600">
                            <i class="fas fa-tag mr-1"></i>${r.label}
                        </span>`
                }
            </td>
            <td class="text-center"><strong>${r.total.toLocaleString('id-ID')}</strong></td>
            <td class="text-center">
                <span style="color:#16a34a;font-weight:600">${r.active.toLocaleString('id-ID')}</span>
            </td>
            <td class="text-center">
                <span style="color:#2563eb;font-weight:600">${r.connecting.toLocaleString('id-ID')}</span>
            </td>
            <td class="text-right">
                <div style="font-size:.85rem;font-weight:700;color:#1e293b">${fmtFull(r.revenue)}</div>
                <div class="progress mt-1" style="height:4px">
                    <div class="progress-bar" style="width:${pct}%;background:#2563eb"></div>
                </div>
                <div style="font-size:.7rem;color:#94a3b8">${pct}% dari total • ${r.tx_count} tx</div>
            </td>
        </tr>`;
    });

    const grandTotal = rows.reduce((s, r) => ({
        total: s.total + r.total, active: s.active + r.active,
        connecting: s.connecting + r.connecting, revenue: s.revenue + r.revenue,
        tx_count: s.tx_count + r.tx_count
    }), {total:0, active:0, connecting:0, revenue:0, tx_count:0});

    setHtml('grouping-table-wrap', `
        <div class="table-responsive">
        <table class="table rpt-table mb-0">
            <thead>
                <tr>
                    <th style="width:30%">Grouping ID</th>
                    <th class="text-center">Total Pelanggan</th>
                    <th class="text-center"><i class="fas fa-check-circle text-success mr-1"></i>Aktif</th>
                    <th class="text-center"><i class="fas fa-sync-alt text-primary mr-1"></i>Connecting</th>
                    <th class="text-right">Pendapatan (Periode)</th>
                </tr>
            </thead>
            <tbody>${tbody}</tbody>
            <tfoot>
                <tr style="background:#f8fafc;font-weight:700">
                    <td>Total</td>
                    <td class="text-center">${grandTotal.total.toLocaleString('id-ID')}</td>
                    <td class="text-center" style="color:#16a34a">${grandTotal.active.toLocaleString('id-ID')}</td>
                    <td class="text-center" style="color:#2563eb">${grandTotal.connecting.toLocaleString('id-ID')}</td>
                    <td class="text-right">${fmtFull(grandTotal.revenue)} <small class="text-muted font-weight-normal">${grandTotal.tx_count} tx</small></td>
                </tr>
            </tfoot>
        </table></div>`);
}

function renderExpenseList(items, nameKey, costKey, containerId) {
    if (!items || !items.length) {
        setHtml(containerId, '<div class="text-muted text-center" style="font-size:.8rem">Belum ada data</div>');
        return;
    }
    let h = '';
    items.forEach(item => {
        h += `<div class="d-flex align-items-center justify-content-between py-2" style="border-bottom:1px solid #f8fafc">
            <span style="font-size:.8rem;color:#475569">${item[nameKey]}</span>
            <div class="text-right">
                <div style="font-size:.8rem;font-weight:700;color:#dc2626">${fmtRp(item[costKey])}<small class="text-muted font-weight-normal">/bln</small></div>
            </div>
        </div>`;
    });
    setHtml(containerId, h);
}

// ── Payment tables ────────────────────────────────────────────────
let paidData = [], unpaidData = [];

function methodLabel(m) {
    const map={manual_transfer:'Transfer Manual',xendit:'Xendit',midtrans:'Midtrans',transfer:'Transfer Manual'};
    return map[m]||m||'Lainnya';
}

function groupBadge(g) {
    return g
        ? `<span style="background:#eff6ff;color:#1d4ed8;font-size:.68rem;padding:2px 7px;border-radius:20px;font-weight:600">${g}</span>`
        : `<span style="color:#94a3b8;font-size:.72rem;font-style:italic">–</span>`;
}

function renderPaidTable(rows) {
    if (!rows.length) {
        setHtml('paid-table-wrap','<div class="p-3 text-center text-muted" style="font-size:.8rem">Tidak ada pembayaran bulan ini</div>');
        return;
    }
    const body = rows.map(r => `<tr>
        <td>
            <div style="font-size:.8rem;font-weight:600;color:#1e293b">${r.name}</div>
            <div style="font-size:.7rem;color:#94a3b8">${r.code}</div>
        </td>
        <td style="font-size:.75rem">${r.package}</td>
        <td>${groupBadge(r.grouping_id)}</td>
        <td style="font-size:.8rem;font-weight:700;color:#16a34a">${fmtFull(r.amount)}</td>
        <td style="font-size:.72rem;color:#64748b">${r.paid_at ?? '–'}</td>
    </tr>`).join('');
    setHtml('paid-table-wrap',`<table class="table table-sm rpt-table mb-0">
        <thead><tr><th>Pelanggan</th><th>Paket</th><th>Grouping</th><th>Jumlah</th><th>Tgl Bayar</th></tr></thead>
        <tbody>${body}</tbody>
    </table>`);
}

function renderUnpaidTable(rows) {
    if (!rows.length) {
        setHtml('unpaid-table-wrap','<div class="p-3 text-center text-muted" style="font-size:.8rem">Semua pelanggan aktif sudah membayar 🎉</div>');
        return;
    }
    const body = rows.map(r => {
        const st = r.status === 'reactivated'
            ? `<span class="badge badge-primary" style="font-size:.68rem">Connecting</span>`
            : `<span class="badge badge-success" style="font-size:.68rem">Aktif</span>`;
        return `<tr>
            <td>
                <div style="font-size:.8rem;font-weight:600;color:#1e293b">${r.name}</div>
                <div style="font-size:.7rem;color:#94a3b8">${r.code}</div>
            </td>
            <td style="font-size:.75rem;font-family:monospace;color:#475569">${r.username}</td>
            <td style="font-size:.75rem">${r.package}</td>
            <td>${groupBadge(r.grouping_id)}</td>
            <td>${st}</td>
        </tr>`;
    }).join('');
    setHtml('unpaid-table-wrap',`<table class="table table-sm rpt-table mb-0">
        <thead><tr><th>Pelanggan</th><th>Username</th><th>Paket</th><th>Grouping</th><th>Status</th></tr></thead>
        <tbody>${body}</tbody>
    </table>`);
}

// Client-side search for payment tables
function initPaymentSearch() {
    document.getElementById('search-paid').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        const filtered = q ? paidData.filter(r =>
            r.name.toLowerCase().includes(q) ||
            r.code.toLowerCase().includes(q) ||
            (r.username||'').toLowerCase().includes(q) ||
            (r.grouping_id||'').toLowerCase().includes(q)
        ) : paidData;
        renderPaidTable(filtered);
    });
    document.getElementById('search-unpaid').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        const filtered = q ? unpaidData.filter(r =>
            r.name.toLowerCase().includes(q) ||
            r.code.toLowerCase().includes(q) ||
            (r.username||'').toLowerCase().includes(q) ||
            (r.grouping_id||'').toLowerCase().includes(q)
        ) : unpaidData;
        renderUnpaidTable(filtered);
    });
}

// ── Load grouping options ─────────────────────────────────────────
function loadGroupings() {
    fetch(API_GROUPINGS, { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(json => {
            if (!json.success) return;
            const sel = document.getElementById('rpt-grouping');
            json.groupings.forEach(g => {
                const opt = document.createElement('option');
                opt.value = g; opt.textContent = g;
                sel.appendChild(opt);
            });
        });
}

// ── Main load ─────────────────────────────────────────────────────
function loadReport() {
    const from      = document.getElementById('rpt-from').value;
    const to        = document.getElementById('rpt-to').value;
    const grouping  = document.getElementById('rpt-grouping').value;
    if (!from || !to) return;

    // Reset skeleton
    ['kpi-income','kpi-arpu','kpi-new-cust'].forEach(id=>{
        setHtml(id,'<div class="skeleton" style="width:60%"></div>');
    });

    fetch(`${API}?from=${from}&to=${to}&grouping_id=${grouping}`, {
        headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
    })
    .then(r=>r.json())
    .then(json=>{
        if (!json.success) return;
        const d = json.data;
        const {income, customer, asset, roi, expense, payment} = d;

        // KPIs
        setText('kpi-income', fmtFull(income.total));
        setText('kpi-income-period', 'Periode: ' + d.period.label);
        const txEl = document.getElementById('kpi-income-tx');
        if (txEl) { txEl.querySelector('span').textContent = income.total_tx; txEl.classList.remove('d-none'); }

        setText('kpi-arpu', fmtFull(income.arpu));
        const activeEl = document.getElementById('kpi-active-count');
        if (activeEl) {
            activeEl.querySelector('span').textContent =
                customer.total_active_now + ' aktif' +
                (customer.total_connecting_now > 0 ? ' + ' + customer.total_connecting_now + ' connecting' : '');
            activeEl.classList.remove('d-none');
        }

        setText('kpi-new-cust', customer.new.toLocaleString('id-ID'));
        const churnEl = document.getElementById('kpi-churn');
        if (churnEl) { churnEl.querySelector('span').textContent = customer.churned; churnEl.classList.remove('d-none'); }

        // ROI
        renderROI(roi);

        // Income bar chart
        if (incomeChartInst) incomeChartInst.destroy();
        incomeChartInst = makeBar('incomeChart',
            income.monthly.map(m=>m.month),
            income.monthly.map(m=>m.value),
            '#2563eb'
        );

        // MRR line chart
        if (mrrChartInst) mrrChartInst.destroy();
        mrrChartInst = makeLine('mrrChart',
            customer.mrr_history.map(m=>m.label),
            customer.mrr_history.map(m=>m.value)
        );

        // Asset doughnut
        if (asset.by_category.length) {
            if (assetChartInst) assetChartInst.destroy();
            assetChartInst = makeDoughnut('assetChart',
                asset.by_category.map(c=>c.label),
                asset.by_category.map(c=>c.value)
            );
        }

        // Tables & lists
        renderPackageList(income.by_package);
        renderMethodTable(income.by_method);
        renderCustomerSummary({...customer});
        renderAssetSummary(asset);

        // Pengeluaran
        setText('kpi-expense-total', fmtFull(expense.total_period));
        setText('kpi-expense-period', `${expense.period_months} bulan × ${fmtRp(expense.total_monthly)}/bln`);
        const expMonthlyEl = document.getElementById('kpi-expense-monthly');
        if (expMonthlyEl) { expMonthlyEl.querySelector('span').textContent = fmtRp(expense.total_monthly); expMonthlyEl.classList.remove('d-none'); }

        const netEl = document.getElementById('kpi-net-income');
        if (netEl) {
            netEl.textContent = fmtFull(expense.net_income);
            netEl.style.color = expense.net_income >= 0 ? '#16a34a' : '#dc2626';
        }

        renderExpenseList(expense.data_centers, 'name', 'cost_per_month', 'expense-datacenter-list');
        renderExpenseList(expense.pops,         'name', 'monthly_cost',   'expense-pop-list');

        // Grouping ID breakdown
        renderGroupingTable(customer.by_grouping || []);

        // Payment bulan ini
        if (payment) {
            const lbl = document.getElementById('payment-month-label');
            if (lbl) lbl.textContent = payment.month_label;

            setText('paid-count',  payment.paid_count.toLocaleString('id-ID') + ' pelanggan');
            setText('paid-total',  'Total: ' + fmtFull(payment.paid_total));
            setText('unpaid-count', payment.unpaid_count.toLocaleString('id-ID') + ' pelanggan');
            setText('paid-badge',   payment.paid_count);
            setText('unpaid-badge', payment.unpaid_count);

            paidData   = payment.paid_list   || [];
            unpaidData = payment.unpaid_list || [];
            renderPaidTable(paidData);
            renderUnpaidTable(unpaidData);

            // Reset search inputs
            const sp = document.getElementById('search-paid');
            const su = document.getElementById('search-unpaid');
            if (sp) sp.value = '';
            if (su) su.value = '';
        }
    })
    .catch(err=>console.error('Report error:', err));
}
window.loadReport = loadReport;

// ── Init ──────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadGroupings();
    loadReport();
    initPaymentSearch();
});
})();
</script>
@stop

@section('css')
    <style>
    /* ── Report page ── */
    .rpt-header {
        background: linear-gradient(135deg,#0f2544,#2563a8);
        border-radius:14px; padding:24px 28px; color:#fff;
        margin-bottom:24px; position:relative; overflow:hidden;
    }
    .rpt-header::after {
        content:''; position:absolute; right:-30px;top:-30px;
        width:140px;height:140px;background:rgba(255,255,255,.07);border-radius:50%;
    }
    .rpt-header h3  { font-weight:800; font-size:1.3rem; margin:0 0 4px; }
    .rpt-header p   { opacity:.75; font-size:.85rem; margin:0; }

    /* Date picker row */
    .date-range-bar {
        background:#fff; border-radius:12px; padding:14px 18px;
        box-shadow:0 2px 10px rgba(0,0,0,.06); margin-bottom:20px;
        display:flex; align-items:center; gap:12px; flex-wrap:wrap;
    }
    .date-range-bar label { font-size:.78rem; font-weight:600; color:#64748b; margin:0; }
    .date-range-bar .form-control { border-radius:8px; font-size:.82rem; max-width:160px; }
    .btn-load { background:#2563eb;color:#fff;border:none;border-radius:8px;padding:7px 18px;font-size:.82rem;font-weight:600;cursor:pointer; }
    .btn-load:hover { background:#1d4ed8; }

    /* KPI Cards */
    .kpi-card {
        background:#fff; border-radius:14px;
        box-shadow:0 2px 10px rgba(0,0,0,.06);
        padding:20px 22px;
        border-left:4px solid transparent;
        height:100%;
    }
    .kpi-val { font-size:1.5rem; font-weight:800; color:#1e293b; line-height:1.1; }
    .kpi-val.md { font-size:1.15rem; }
    .kpi-lbl { font-size:.75rem; color:#64748b; margin-top:3px; }
    .kpi-sub { font-size:.72rem; margin-top:6px; display:inline-flex; align-items:center; gap:3px;
            padding:2px 8px; border-radius:20px; font-weight:600; }
    .kpi-sub.good { background:#ecfdf5; color:#065f46; }
    .kpi-sub.warn { background:#fef3c7; color:#92400e; }
    .kpi-sub.info { background:#eff6ff; color:#1d4ed8; }

    /* ROI card */
    .roi-card {
        background:linear-gradient(135deg,#0f2544,#1e4976);
        border-radius:14px; padding:24px; color:#fff;
    }
    .roi-val { font-size:2.5rem; font-weight:900; line-height:1; }
    .roi-lbl { font-size:.8rem; opacity:.7; margin-top:4px; }

    /* Section heading */
    .section-head {
        font-size:.7rem; font-weight:700; text-transform:uppercase;
        letter-spacing:1.2px; color:#8898aa; margin-bottom:10px; padding-left:2px;
    }

    /* Chart */
    .chart-box { position:relative; height:240px; }

    /* Table inside report */
    .rpt-table { font-size:.8rem; }
    .rpt-table th {
        background:#f8fafc; font-size:.68rem; text-transform:uppercase;
        letter-spacing:.8px; color:#64748b; border-top:none !important;
        border-bottom:1px solid #e2e8f0 !important; padding:8px 12px !important;
        font-weight:600 !important;
    }
    .rpt-table td { padding:9px 12px !important; border-color:#f1f5f9 !important; vertical-align:middle !important; }

    /* Skeleton */
    .skeleton { background:linear-gradient(90deg,#e2e8f0 25%,#f1f5f9 50%,#e2e8f0 75%);
        background-size:200% 100%; animation:shimmer 1.5s infinite; border-radius:6px; height:18px; }
    @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

    /* Responsive */
    @media(max-width:575px) { .date-range-bar { flex-direction:column; align-items:flex-start; } }
    </style>
@stop
