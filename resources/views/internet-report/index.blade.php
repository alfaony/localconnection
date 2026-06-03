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

{{-- ── Section: Charts ── --}}
<div class="section-head">Grafik Pendapatan & Pelanggan</div>
<div class="row mb-4">

    {{-- Monthly Income Chart --}}
    <div class="col-lg-8 mb-3">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h5><i class="fas fa-chart-bar text-primary"></i> Pendapatan per Bulan</h5>
            </div>
            <div class="dash-card-body">
                <div class="chart-box"><canvas id="incomeChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Income by Package --}}
    <div class="col-lg-4 mb-3">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h5><i class="fas fa-layer-group text-success"></i> Top Paket</h5>
            </div>
            <div class="dash-card-body" id="pkg-list">
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
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h5><i class="fas fa-chart-line text-info"></i> Tren MRR 12 Bulan Terakhir</h5>
            </div>
            <div class="dash-card-body">
                <div class="chart-box"><canvas id="mrrChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Asset by Category --}}
    <div class="col-lg-5 mb-3">
        <div class="dash-card h-100">
            <div class="dash-card-header">
                <h5><i class="fas fa-hdd text-warning"></i> Aset per Kategori</h5>
            </div>
            <div class="dash-card-body">
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
        <div class="dash-card">
            <div class="dash-card-header">
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
        <div class="dash-card">
            <div class="dash-card-header">
                <h5><i class="fas fa-users text-success"></i> Ringkasan Pelanggan</h5>
            </div>
            <div class="dash-card-body" id="customer-summary">
                <div class="skeleton mb-2"></div>
                <div class="skeleton mb-2"></div>
                <div class="skeleton mb-2"></div>
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

const API  = '{{ route("internet-report.data") }}';
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
    setHtml('pkg-list', `<div class="dash-card-body">${h}</div>`);
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
        {icon:'fa-user-plus',  color:'#7c3aed', label:'Pelanggan Baru', val:c.new},
        {icon:'fa-user-check', color:'#16a34a', label:'Aktif Sekarang',  val:c.total_active_now},
        {icon:'fa-user-slash', color:'#dc2626', label:'Churn / Cabut',   val:c.churned},
        {icon:'fa-dollar-sign',color:'#0891b2', label:'MRR Bulan Lalu',  val:fmtRp(c.monthly_recurring)},
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

// ── Main load ─────────────────────────────────────────────────────
function loadReport() {
    const from = document.getElementById('rpt-from').value;
    const to   = document.getElementById('rpt-to').value;
    if (!from || !to) return;

    // Reset skeleton
    ['kpi-income','kpi-arpu','kpi-new-cust'].forEach(id=>{
        setHtml(id,'<div class="skeleton" style="width:60%"></div>');
    });

    fetch(`${API}?from=${from}&to=${to}`, {
        headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
    })
    .then(r=>r.json())
    .then(json=>{
        if (!json.success) return;
        const d = json.data;
        const {income, customer, asset, roi} = d;

        // KPIs
        setText('kpi-income', fmtFull(income.total));
        setText('kpi-income-period', 'Periode: ' + d.period.label);
        const txEl = document.getElementById('kpi-income-tx');
        if (txEl) { txEl.querySelector('span').textContent = income.total_tx; txEl.classList.remove('d-none'); }

        setText('kpi-arpu', fmtFull(income.arpu));
        const activeEl = document.getElementById('kpi-active-count');
        if (activeEl) { activeEl.querySelector('span').textContent = customer.total_active_now; activeEl.classList.remove('d-none'); }

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
    })
    .catch(err=>console.error('Report error:', err));
}
window.loadReport = loadReport;

// ── Init ──────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => loadReport());
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
