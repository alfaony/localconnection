<style>
/* ════════════════════════════════════════════════════════════════════
   PUBLIC REGISTRATION FORM — SOFT MODERN DESIGN
   ════════════════════════════════════════════════════════════════════ */

/* ── Page wrapper ─────────────────────────────────────────────── */
body, .main-content, .wrapper, .content-wrapper {
    background: linear-gradient(135deg, #f0f4ff 0%, #faf5ff 50%, #f0fdf4 100%) !important;
    min-height: 100vh;
}

/* Touch optimasi */
* { -webkit-tap-highlight-color: rgba(0,0,0,0); -webkit-touch-callout: none; }

/* ── Card ─────────────────────────────────────────────────────── */
.registration-card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 8px 40px rgba(0,0,0,.08);
    padding: 36px 40px 40px;
    margin: 0 auto;
    max-width: 900px;
}
@media (max-width: 575px) {
    .registration-card { padding: 20px 16px; border-radius: 16px; }
}

/* ── Progress steps ───────────────────────────────────────────── */
.progress-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 28px;
    padding-bottom: 0;
    border-bottom: none;
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    gap: 4px;
}
.step-item {
    flex: 1 1 auto;
    text-align: center;
    color: #cbd5e1;
    font-size: 13px;
    min-width: 72px;
    white-space: nowrap;
    position: relative;
}
.step-item::before {
    content: '';
    position: absolute;
    top: 12px;
    left: 50%;
    width: 100%;
    height: 2px;
    background: #e2e8f0;
    z-index: 0;
}
.step-item:last-child::before { display: none; }
.step-item.completed::before { background: #2563eb; }
.step-item.active::before    { background: linear-gradient(90deg, #2563eb 50%, #e2e8f0 50%); }

.step-number {
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 26px; height: 26px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #94a3b8;
    font-weight: 700;
    margin: 0 auto 6px;
    position: relative;
    z-index: 1;
    transition: background .2s, color .2s;
}
.step-item.active    .step-number { background: #2563eb; color: #fff; box-shadow: 0 0 0 4px rgba(37,99,235,.15); }
.step-item.completed .step-number { background: #16a34a; color: #fff; }
.step-item.completed .step-number::after {
    content: '✓';
    position: absolute;
    font-size: 11px;
}
.step-item.completed .step-number span { display: none; }
.step-title {
    font-size: 11px;
    color: #94a3b8;
}
.step-item.active    .step-title { color: #2563eb; font-weight: 700; }
.step-item.completed .step-title { color: #16a34a; }

/* ── Progress bar ─────────────────────────────────────────────── */
.progress { height: 6px !important; border-radius: 20px !important; background: #f1f5f9 !important; margin-bottom: 28px; }
.progress-bar { background: linear-gradient(90deg, #2563eb, #7c3aed) !important; border-radius: 20px !important; }

/* ── Section title ────────────────────────────────────────────── */
.section-title {
    font-size: 1.1rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ── Form elements ────────────────────────────────────────────── */
.form-label {
    font-size: .8rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 5px;
    display: block;
}

.form-control, .form-select {
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 10px !important;
    padding: 9px 13px !important;
    font-size: .85rem !important;
    color: #1e293b !important;
    background: #fafbfd !important;
    transition: border-color .15s, box-shadow .15s !important;
}
.form-control:focus, .form-select:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1) !important;
    background: #fff !important;
}
.form-control::placeholder { color: #b0bac8 !important; }
.form-control.is-invalid { border-color: #dc2626 !important; }

/* ── Radio / checkbox groups ──────────────────────────────────── */
.form-check-input:checked {
    background-color: #2563eb !important;
    border-color: #2563eb !important;
}

/* ── Package cards ────────────────────────────────────────────── */
.package-card {
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s, transform .1s;
}
.package-card:hover { border-color: #93c5fd; box-shadow: 0 4px 14px rgba(37,99,235,.1); }
.package-card.selected {
    border-color: #2563eb;
    background: #eff6ff;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.package-card .pkg-name  { font-weight: 700; font-size: .9rem; color: #1e293b; }
.package-card .pkg-speed { font-size: .75rem; color: #64748b; }
.package-card .pkg-price { font-size: 1rem; font-weight: 800; color: #2563eb; margin-top: 4px; }

/* ── Buttons ──────────────────────────────────────────────────── */
.btn-primary-red {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 10px 28px;
    font-size: .88rem;
    font-weight: 700;
    cursor: pointer;
    transition: opacity .15s, transform .1s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-primary-red:hover:not(:disabled) { opacity: .9; transform: translateY(-1px); }
.btn-primary-red:disabled { opacity: .55; cursor: not-allowed; }
.btn-outline-secondary {
    border-radius: 10px !important;
    font-size: .86rem !important;
    font-weight: 600 !important;
}

/* ── KTP input mode tabs ──────────────────────────────────────── */
.ktp-mode-tabs {
    display: flex; gap: 8px; margin-bottom: 16px;
}
.ktp-mode-tab {
    flex: 1; padding: 8px 12px; border-radius: 10px;
    border: 1.5px solid #e2e8f0; cursor: pointer;
    text-align: center; font-size: .8rem; font-weight: 600;
    color: #64748b; background: #f8fafc;
    transition: all .15s;
}
.ktp-mode-tab.active {
    border-color: #2563eb; background: #eff6ff; color: #2563eb;
}

/* ── Info/alert boxes ─────────────────────────────────────────── */
.info-box-soft {
    background: #eff6ff;
    border-left: 4px solid #2563eb;
    border-radius: 10px;
    padding: 12px 16px;
    font-size: .82rem;
    color: #1e40af;
    margin-bottom: 16px;
}

/* ── Payment summary card ─────────────────────────────────────── */
.payment-summary {
    background: linear-gradient(135deg, #f8fafc, #f0f4ff);
    border: 1.5px solid #e0e7ff;
    border-radius: 14px;
    padding: 18px 20px;
    margin-bottom: 16px;
}
.payment-summary .ps-row {
    display: flex; justify-content: space-between;
    font-size: .83rem; padding: 5px 0;
    border-bottom: 1px solid rgba(0,0,0,.05);
}
.payment-summary .ps-row:last-child { border-bottom: none; }
.payment-summary .ps-label { color: #64748b; }
.payment-summary .ps-val   { font-weight: 700; color: #1e293b; }
.payment-summary .ps-total .ps-label { color: #1e293b; font-weight: 700; font-size: .9rem; }
.payment-summary .ps-total .ps-val   { color: #2563eb; font-size: 1rem; }

/* ── Coverage badge ───────────────────────────────────────────── */
.coverage-ok   { background: #ecfdf5; color: #065f46; border-radius: 8px; padding: 8px 12px; font-size: .82rem; font-weight: 600; }
.coverage-fail { background: #fde8e8; color: #991b1b; border-radius: 8px; padding: 8px 12px; font-size: .82rem; font-weight: 600; }

/* ── Signature pad ────────────────────────────────────────────── */
canvas#signature-canvas {
    border: 1.5px solid #e2e8f0 !important;
    border-radius: 12px !important;
}

/* ── File upload ──────────────────────────────────────────────── */
input[type="file"].form-control {
    padding: 6px 10px !important;
}
</style>
