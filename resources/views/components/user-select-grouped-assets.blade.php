{{-- Select2 Dark Theme + Grouped User Select Styles --}}
{{-- Include ONCE per page in @section('css') --}}
<style>
/* ── Select2 Base ─────────────────────────────────────────── */
.select2-container .select2-selection--multiple {
    background-color: #0f1623 !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    border-radius: 10px !important;
    min-height: 44px;
    padding: 4px 6px !important;
}
.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: rgba(102,126,234,.55) !important;
    box-shadow: 0 0 0 3px rgba(102,126,234,.15) !important;
}
.select2-container .select2-search--inline .select2-search__field {
    color: #c8d0e0 !important;
    font-size: .83rem;
    margin-top: 5px;
}
.select2-container .select2-search--inline .select2-search__field::placeholder {
    color: #55596e !important;
}

/* ── Choice Tags (selected items) ────────────────────────── */
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background: rgba(102,126,234,.2) !important;
    border: 1px solid rgba(102,126,234,.35) !important;
    border-radius: 6px !important;
    color: #c8d0e0 !important;
    font-size: .75rem !important;
    padding: 2px 8px 2px 6px !important;
    margin: 3px 3px 3px 0 !important;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    max-width: 180px;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__display {
    color: #c8d0e0 !important;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
    color: rgba(248,113,113,.7) !important;
    border-right: none !important;
    border-radius: 50% !important;
    width: 14px;
    height: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .8rem;
    margin-right: 2px;
    flex-shrink: 0;
    order: -1;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
    color: #f87171 !important;
    background: rgba(248,113,113,.15) !important;
}

/* ── Dropdown ─────────────────────────────────────────────── */
.select2-dropdown {
    background: #0f1623 !important;
    border: 1px solid rgba(102,126,234,.25) !important;
    border-radius: 10px !important;
    box-shadow: 0 8px 32px rgba(0,0,0,.5) !important;
    /* JANGAN overflow:hidden di sini — akan memotong scroll list */
    z-index: 9999 !important;
}
/* Rounded corners hanya di bagian search (atas) */
.select2-search--dropdown {
    border-radius: 10px 10px 0 0;
}
/* Rounded corners di bagian bawah list */
.select2-results {
    border-radius: 0 0 10px 10px;
    overflow: hidden;
}
.select2-search--dropdown {
    padding: 8px !important;
    border-bottom: 1px solid rgba(255,255,255,.06) !important;
}
.select2-search--dropdown .select2-search__field {
    background: rgba(255,255,255,.06) !important;
    border: 1px solid rgba(255,255,255,.1) !important;
    border-radius: 7px !important;
    color: #c8d0e0 !important;
    font-size: .82rem;
    padding: 6px 10px !important;
    outline: none;
}
.select2-search--dropdown .select2-search__field:focus {
    border-color: rgba(102,126,234,.5) !important;
}

/* ── Results list ─────────────────────────────────────────── */
/* .select2-results__options { max-height: 240px !important; } */

/* Reset default #ddd background dulu */
.select2-results__option,
.select2-results__option[aria-selected],
.select2-results__option[aria-selected="true"],
.select2-results__option[aria-disabled="false"] {
    background-color: transparent !important;
    background: transparent !important;
    color: #a0a8d0 !important;
    font-size: .82rem !important;
    padding: 7px 14px !important;
    transition: background .1s;
}

/* Hover / keyboard highlight */
.select2-container--default .select2-results__option--highlighted[aria-selected],
.select2-container--default .select2-results__option--highlighted {
    background-color: rgba(102,126,234,.25) !important;
    background: rgba(102,126,234,.25) !important;
    color: #e0e0ff !important;
}

/* Sudah terpilih (di dalam dropdown list) */
.select2-container--default .select2-results__option[aria-selected="true"],
.select2-container--default .select2-results__option--selected {
    background-color: rgba(102,126,234,.18) !important;
    background: rgba(102,126,234,.18) !important;
    color: #a5b4fc !important;
}

/* Sudah terpilih + hover sekaligus */
.select2-container--default .select2-results__option--highlighted[aria-selected="true"],
.select2-container--default .select2-results__option--selected.select2-results__option--highlighted {
    background-color: rgba(102,126,234,.35) !important;
    background: rgba(102,126,234,.35) !important;
    color: #e0e0ff !important;
}

/* Checkmark prefix untuk item sudah dipilih */
.select2-container--default .select2-results__option[aria-selected="true"]::before,
.select2-container--default .select2-results__option--selected::before {
    content: '✓ ';
    color: #667eea;
    font-weight: 700;
}

/* ── Group headers (optgroup) ─────────────────────────────── */
.select2-results__group {
    color: #667eea !important;
    font-size: .68rem !important;
    font-weight: 700 !important;
    text-transform: uppercase;
    letter-spacing: .6px;
    padding: 8px 12px 4px !important;
    background: rgba(102,126,234,.07) !important;
    border-top: 1px solid rgba(102,126,234,.12);
    cursor: default;
}
.select2-results__group:first-child { border-top: none; }

/* ── Division pills ───────────────────────────────────────── */
.division-pill:hover {
    background: rgba(102,126,234,.28) !important;
    color: #e0e0ff !important;
    border-color: rgba(102,126,234,.5) !important;
}
.division-pill.active {
    background: rgba(102,126,234,.32) !important;
    color: #fff !important;
    border-color: rgba(102,126,234,.6) !important;
}
.select-all-pill:hover  { background: rgba(56,239,125,.22) !important;  color: #fff !important; }
.clear-all-pill:hover   { background: rgba(248,113,113,.18) !important; color: #fff !important; }
</style>
