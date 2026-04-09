@extends('adminlte::page')

@section('title', isset($xpConfig) ? 'Edit XP Config' : 'Buat XP Config')

@section('content_header')
<div class="d-flex align-items-center">
    <a href="{{ route('xp-config.index') }}" class="btn btn-sm mr-3" style="background:rgba(102,126,234,.15);color:#667eea;border:1px solid rgba(102,126,234,.3);">
        <i class="fas fa-arrow-left"></i>
    </a>
    <div>
        <h1 class="m-0 fw-bold" style="color:#e0e0ff;">
            {{ isset($xpConfig) ? '✏️ Edit XP Config' : '⚡ Buat XP Config Baru' }}
        </h1>
        <small style="color:#a0a8d0;">Konfigurasikan nilai XP untuk setiap jenis aksi</small>
    </div>
</div>
@stop

@section('content')
@include('components.alert')

<form action="{{ isset($xpConfig) ? route('xp-config.update', $xpConfig) : route('xp-config.store') }}" method="POST" id="xpConfigForm">
    @csrf
    @if(isset($xpConfig)) @method('PUT') @endif

    <div class="row g-3">
        {{-- Kiri: Info --}}
        <div class="col-md-4">
            <div class="xp-form-card mb-3">
                <div class="xp-form-card-header">
                    <i class="fas fa-info-circle mr-2" style="color:#667eea;"></i> Informasi Config
                </div>
                <div class="p-4">
                    <div class="form-group mb-3">
                        <label class="xp-label">Nama Config <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="xp-input @error('name') is-invalid @enderror"
                               placeholder="contoh: Standard, Premium..."
                               value="{{ old('name', $xpConfig->name ?? '') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="xp-label">Deskripsi</label>
                        <textarea name="description" class="xp-input" rows="3"
                                  placeholder="Keterangan singkat...">{{ old('description', $xpConfig->description ?? '') }}</textarea>
                    </div>

                    <div class="xp-toggle-wrap mb-4">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" class="custom-control-input" id="isEnabled"
                                   name="is_enabled" value="1"
                                   {{ old('is_enabled', $xpConfig->is_enabled ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="isEnabled" style="color:#c8d0e0;">
                                Config Aktif
                                <br><small style="color:#8ab4c0;">XP diberikan ke company yang menggunakan config ini</small>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block rounded-pill">
                        <i class="fas fa-save mr-1"></i>
                        {{ isset($xpConfig) ? 'Simpan Perubahan' : 'Buat Config' }}
                    </button>
                </div>
            </div>

            {{-- Tips --}}
            <div class="xp-form-card">
                <div class="xp-form-card-header"><i class="fas fa-lightbulb mr-2" style="color:#f5a623;"></i> Tips</div>
                <div class="p-3">
                    <ul class="mb-0 pl-3" style="color:#8ab4c0;font-size:.8rem;line-height:1.8;">
                        <li><code style="color:#f093fb;">ALL</code> = XP default jika model tidak terdaftar</li>
                        <li>Nilai XP bisa <span style="color:#f5576c;">negatif</span> untuk penalti</li>
                        <li>Label hanya untuk tampilan UI</li>
                        <li>Default model tidak bisa dihapus</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Kanan: Model XP Table --}}
        <div class="col-md-8">
            <div class="xp-form-card">
                <div class="xp-form-card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-table mr-2" style="color:#4facfe;"></i> Nilai XP per Aksi</span>
                    <button type="button" class="btn btn-sm rounded-pill" id="addModelRow"
                            style="background:rgba(79,172,254,.15);color:#4facfe;border:1px solid rgba(79,172,254,.3);">
                        <i class="fas fa-plus mr-1"></i> Tambah Baris
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="xp-table" id="modelsTable">
                        <thead>
                            <tr>
                                <th style="width:35%">Model / Aksi</th>
                                <th style="width:30%">Label Tampilan</th>
                                <th style="width:20%">Nilai XP</th>
                                <th style="width:15%;text-align:center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="modelsBody">
                            @php
                                $existingModels = isset($xpConfig) ? $xpConfig->models->keyBy('source_type') : collect();
                            @endphp
                            @foreach($defaultModels as $sourceType => $label)
                            @php $existing = $existingModels->get($sourceType); @endphp
                            <tr class="model-row">
                                <td>
                                    <input type="hidden" name="models[{{ $loop->index }}][source_type]" value="{{ $sourceType }}">
                                    <span class="xp-source-type"><i class="fas fa-code mr-1" style="font-size:.65rem;opacity:.5;"></i>{{ $sourceType }}</span>
                                </td>
                                <td>
                                    <input type="text" name="models[{{ $loop->index }}][label]"
                                           class="xp-input-sm"
                                           value="{{ old("models.{$loop->index}.label", $existing->label ?? $label) }}">
                                </td>
                                <td>
                                    <div class="xp-input-group">
                                        <input type="number" name="models[{{ $loop->index }}][xp]"
                                               class="xp-input-sm xp-input" style="width:80px;"
                                               value="{{ old("models.{$loop->index}.xp", $existing->xp ?? 100) }}" required>
                                        <span style="color:#f5a623;font-size:.8rem;">⚡</span>
                                    </div>
                                </td>
                                <td style="text-align:center;">
                                    <span title="Default — tidak bisa dihapus" style="color:#555;font-size:.8rem;">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                </td>
                            </tr>
                            @endforeach

                            @if(isset($xpConfig))
                            @foreach($xpConfig->models->whereNotIn('source_type', array_keys($defaultModels)) as $cm)
                            <tr class="model-row custom-row">
                                <td>
                                    <input type="text" name="models[custom_{{ $loop->index }}][source_type]"
                                           class="xp-input-sm" placeholder="NamaModel"
                                           value="{{ $cm->source_type }}" required>
                                </td>
                                <td>
                                    <input type="text" name="models[custom_{{ $loop->index }}][label]"
                                           class="xp-input-sm" value="{{ $cm->label }}">
                                </td>
                                <td>
                                    <div class="xp-input-group">
                                        <input type="number" name="models[custom_{{ $loop->index }}][xp]"
                                               class="xp-input-sm xp-input" style="width:80px;" value="{{ $cm->xp }}" required>
                                        <span style="color:#f5a623;font-size:.8rem;">⚡</span>
                                    </div>
                                </td>
                                <td style="text-align:center;">
                                    <button type="button" class="btn btn-sm remove-row xp-btn-del">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@section('css')
<style>
.xp-form-card {
    background: linear-gradient(145deg, #1a1a2e, #16213e);
    border: 1px solid rgba(255,255,255,.06);
    border-radius: 14px;
    overflow: hidden;
}
.xp-form-card-header {
    padding: 12px 16px;
    border-bottom: 1px solid rgba(255,255,255,.06);
    font-weight: 700;
    color: #e0e0ff;
    font-size: .9rem;
}
.xp-label {
    font-size: .8rem;
    font-weight: 600;
    color: #a0a8d0;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 6px;
    display: block;
}
.xp-input {
    background: rgba(255,255,255,.05) !important;
    border: 1px solid rgba(255,255,255,.1) !important;
    border-radius: 8px !important;
    color: #e0e0ff !important;
    width: 100%;
    padding: 8px 12px;
}
.xp-input:focus {
    background: rgba(102,126,234,.1) !important;
    border-color: rgba(102,126,234,.5) !important;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102,126,234,.15) !important;
    color: #e0e0ff !important;
}
.xp-input::placeholder { color: #555 !important; }
.xp-input-sm {
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 6px;
    color: #c8d0e0;
    padding: 4px 8px;
    font-size: .82rem;
    width: 100%;
}
.xp-input-sm:focus {
    background: rgba(102,126,234,.1);
    border-color: rgba(102,126,234,.4);
    outline: none;
    color: #e0e0ff;
}
.xp-input-group { display: flex; align-items: center; gap: 6px; }
.xp-toggle-wrap {
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.07);
    border-radius: 10px;
    padding: 12px;
}

/* Table */
.xp-table { width: 100%; border-collapse: collapse; }
.xp-table thead tr {
    background: rgba(255,255,255,.03);
    border-bottom: 1px solid rgba(255,255,255,.07);
}
.xp-table th {
    padding: 10px 12px;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #8ab4c0;
    font-weight: 600;
}
.xp-table tbody tr {
    border-bottom: 1px solid rgba(255,255,255,.04);
    transition: background .15s;
}
.xp-table tbody tr:hover { background: rgba(102,126,234,.05); }
.xp-table td { padding: 8px 12px; vertical-align: middle; }
.xp-source-type {
    font-family: monospace;
    font-size: .82rem;
    color: #c8d0e0;
}
.xp-btn-del {
    background: rgba(245,87,108,.1);
    color: #f5576c;
    border: 1px solid rgba(245,87,108,.2);
    padding: 3px 8px;
    font-size: .75rem;
}
.xp-btn-del:hover { background: rgba(245,87,108,.25); color: #ff8fa3; }
</style>
@stop

@section('js')
<script>
let customIndex = 1000;

document.getElementById('addModelRow').addEventListener('click', function () {
    customIndex++;
    const tbody = document.getElementById('modelsBody');
    const tr = document.createElement('tr');
    tr.className = 'model-row custom-row';
    tr.innerHTML = `
        <td><input type="text" name="models[${customIndex}][source_type]" class="xp-input-sm" placeholder="ContohModel" required></td>
        <td><input type="text" name="models[${customIndex}][label]" class="xp-input-sm" placeholder="Label tampilan"></td>
        <td>
            <div class="xp-input-group">
                <input type="number" name="models[${customIndex}][xp]" class="xp-input-sm xp-input" style="width:80px;" value="100" required>
                <span style="color:#f5a623;font-size:.8rem;">⚡</span>
            </div>
        </td>
        <td style="text-align:center;">
            <button type="button" class="btn btn-sm remove-row xp-btn-del"><i class="fas fa-times"></i></button>
        </td>
    `;
    tbody.appendChild(tr);
    attachRemove();
    colorXp();
});

function attachRemove() {
    document.querySelectorAll('.remove-row').forEach(btn => {
        btn.onclick = function () { this.closest('tr').remove(); };
    });
}

function colorXp() {
    document.querySelectorAll('.xp-input').forEach(input => {
        const v = parseInt(input.value) || 0;
        input.style.color = v < 0 ? '#f5576c' : '#38ef7d';
        input.style.fontWeight = '700';
    });
}

document.getElementById('modelsBody').addEventListener('input', colorXp);
attachRemove();
colorXp();
</script>
@stop
