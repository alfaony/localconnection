@extends('adminlte::page')

@section('title', 'Assign XP Config')

@section('content_header')
<div class="d-flex align-items-center">
    <a href="{{ route('xp-config.index') }}" class="btn btn-sm mr-3" style="background:rgba(102,126,234,.15);color:#667eea;border:1px solid rgba(102,126,234,.3);">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="m-0 fw-bold" style="color:#e0e0ff;">🔗 Assign XP Config ke Company</h1>
        <small style="color:#a0a8d0;">Hubungkan konfigurasi XP ke setiap company</small>
    </div>
</div>
@stop

@section('content')
@include('components.alert')

@if(session('update'))
<div class="alert border-0 shadow-sm mb-3 d-flex align-items-center" style="background:rgba(56,239,125,.1);border-left:4px solid #38ef7d !important;border-radius:10px;">
    <i class="fas fa-check-circle mr-2" style="color:#38ef7d;"></i>
    <span style="color:#c8d0e0;">Assign berhasil diperbarui!</span>
</div>
@endif

<div class="xp-master-card">
    <div class="xp-master-header">
        <span><i class="fas fa-building mr-2"></i> Daftar Company</span>
        <small style="color:#a0a8d0;">{{ count($companies) }} company</small>
    </div>

    <div class="table-responsive">
        <table class="xp-table">
            <thead>
                <tr>
                    <th style="width:40%;padding-left:20px;">Company</th>
                    <th style="width:30%;">XP Config</th>
                    <th style="width:20%;">Status XP</th>
                    <th style="width:10%;text-align:center;">Ubah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($companies as $company)
                <tr>
                    <td style="padding-left:20px;">
                        <div class="d-flex align-items-center">
                            <div class="xp-avatar mr-3">{{ strtoupper(substr($company->name, 0, 1)) }}</div>
                            <div>
                                <div style="color:#e0e0ff;font-weight:600;font-size:.88rem;">{{ $company->name }}</div>
                                <small style="color:#8ab4c0;">{{ $company->slug ?? '-' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($company->xpConfig)
                            <span class="xp-badge" style="background:rgba(102,126,234,.15);color:#a0c4ff;border:1px solid rgba(102,126,234,.2);">
                                ⚡ {{ $company->xpConfig->name }}
                            </span>
                        @else
                            <span class="xp-badge" style="background:rgba(255,255,255,.05);color:#666;">Belum diset</span>
                        @endif
                    </td>
                    <td>
                        @if($company->xpConfig && $company->xpConfig->is_enabled)
                            <span style="color:#38ef7d;font-size:.83rem;"><i class="fas fa-check-circle mr-1"></i>Aktif</span>
                        @elseif($company->xpConfig)
                            <span style="color:#f5a623;font-size:.83rem;"><i class="fas fa-pause-circle mr-1"></i>Nonaktif</span>
                        @else
                            <span style="color:#555;font-size:.83rem;"><i class="fas fa-minus-circle mr-1"></i>Tidak ada</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <button class="btn btn-sm xp-btn-edit"
                                data-toggle="modal" data-target="#assignModal"
                                onclick="openAssign('{{ $company->id }}','{{ addslashes($company->name) }}','{{ $company->xp_config_id ?? '' }}')">
                            <i class="fas fa-exchange-alt mr-1"></i> Assign
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow" style="background:#16213e;border-radius:14px;overflow:hidden;">
            <div class="modal-header border-0" style="background:rgba(102,126,234,.2);">
                <h6 class="modal-title fw-bold" style="color:#e0e0ff;"><i class="fas fa-link mr-2"></i>Assign XP Config</h6>
                <button type="button" class="close" data-dismiss="modal" style="color:#a0a8d0;">&times;</button>
            </div>
            <form action="{{ route('xp-config.assign.update') }}" method="POST">
                @csrf
                <input type="hidden" name="company_id" id="modalCompanyId">
                <div class="modal-body p-4">
                    <p class="mb-3" style="color:#8ab4c0;font-size:.85rem;">Company: <strong style="color:#e0e0ff;" id="modalCompanyName"></strong></p>
                    <label class="xp-label">Pilih XP Config</label>
                    <select name="xp_config_id" id="modalConfigSelect" class="xp-input">
                        <option value="">-- Nonaktifkan XP --</option>
                        @foreach($configs as $config)
                        <option value="{{ $config->id }}">⚡ {{ $config->name }}</option>
                        @endforeach
                    </select>
                    <small style="color:#555;font-size:.75rem;margin-top:6px;display:block;">Kosong = XP dinonaktifkan untuk company ini.</small>
                </div>
                <div class="modal-footer border-0 pt-0 px-4">
                    <button type="button" class="btn btn-sm" data-dismiss="modal"
                            style="background:rgba(255,255,255,.07);color:#a0a8d0;border:1px solid rgba(255,255,255,.1);">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4">
                        <i class="fas fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
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
.xp-table { width: 100%; border-collapse: collapse; }
.xp-table thead tr { background: rgba(255,255,255,.03); border-bottom: 1px solid rgba(255,255,255,.07); }
.xp-table th { padding: 10px 12px; font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; color: #8ab4c0; font-weight: 600; }
.xp-table tbody tr { border-bottom: 1px solid rgba(255,255,255,.04); transition: background .15s; }
.xp-table tbody tr:hover { background: rgba(102,126,234,.06); }
.xp-table td { padding: 10px 12px; vertical-align: middle; }

.xp-avatar {
    width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff; font-weight: 700; font-size: .8rem;
    display: flex; align-items: center; justify-content: center;
}
.xp-badge {
    font-size: .72rem; padding: 3px 10px;
    border-radius: 20px; font-weight: 600; display: inline-block;
}
.xp-label {
    font-size: .75rem; font-weight: 600; color: #a0a8d0;
    text-transform: uppercase; letter-spacing: .04em;
    margin-bottom: 6px; display: block;
}
.xp-input {
    background: rgba(255,255,255,.05) !important;
    border: 1px solid rgba(255,255,255,.1) !important;
    border-radius: 8px !important;
    color: #e0e0ff !important;
    width: 100%; padding: 8px 12px;
}
.xp-input option { background: #16213e; color: #e0e0ff; }
.xp-btn-edit {
    background: rgba(102,126,234,.15);
    color: #667eea;
    border: 1px solid rgba(102,126,234,.3);
    padding: 4px 10px; font-size: .8rem;
}
.xp-btn-edit:hover { background: rgba(102,126,234,.3); color: #a0c4ff; }
</style>
@stop

@section('js')
<script>
function openAssign(id, name, configId) {
    document.getElementById('modalCompanyId').value = id;
    document.getElementById('modalCompanyName').textContent = name;
    document.getElementById('modalConfigSelect').value = configId;
}
</script>
@stop
