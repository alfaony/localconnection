@extends('adminlte::page')

@section('title', isset($xpConfig) ? 'Edit XP Config' : 'Buat XP Config')

@section('content_header')
    <div class="d-flex align-items-center">
        <a href="{{ route('xp-config.index') }}" class="btn btn-sm btn-outline-secondary mr-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="m-0 text-dark">
                {{ isset($xpConfig) ? '✏️ Edit XP Config' : '⚡ Buat XP Config Baru' }}
            </h1>
            <small class="text-muted">Konfigurasikan nilai XP untuk setiap jenis aksi</small>
        </div>
    </div>
@stop

@section('content')
@include('components.alert')

<form action="{{ isset($xpConfig) ? route('xp-config.update', $xpConfig) : route('xp-config.store') }}" method="POST" id="xpConfigForm">
    @csrf
    @if(isset($xpConfig)) @method('PUT') @endif

    <div class="row">
        {{-- Kiri: Info Config --}}
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-2">
                    <h5 class="font-weight-bold mb-0">Informasi Config</h5>
                </div>
                <div class="card-body pt-2">
                    <div class="form-group">
                        <label class="font-weight-semibold">Nama Config <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               placeholder="contoh: Standard, Premium..."
                               value="{{ old('name', $xpConfig->name ?? '') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Keterangan singkat tentang config ini...">{{ old('description', $xpConfig->description ?? '') }}</textarea>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" class="custom-control-input" id="isEnabled"
                                   name="is_enabled" value="1"
                                   {{ old('is_enabled', $xpConfig->is_enabled ?? true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="isEnabled">
                                <span class="font-weight-semibold">Config Aktif</span>
                                <br><small class="text-muted">XP akan diberikan ke company yang menggunakan config ini</small>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block mt-3">
                        <i class="fas fa-save mr-1"></i>
                        {{ isset($xpConfig) ? 'Simpan Perubahan' : 'Buat Config' }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Kanan: Model XP --}}
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="font-weight-bold mb-0">Nilai XP per Menu/Aksi</h5>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addModelRow">
                            <i class="fas fa-plus mr-1"></i> Tambah Baris
                        </button>
                    </div>
                    <small class="text-muted d-block pb-3 mt-1">Nilai XP bisa negatif untuk penalti. Default 100 jika model tidak terdaftar.</small>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-borderless" id="modelsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:35%">Tipe Model (Class Name)</th>
                                    <th style="width:30%">Label Tampilan</th>
                                    <th style="width:20%">Nilai XP</th>
                                    <th style="width:15%" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="modelsBody">
                                @php
                                    $existingModels = isset($xpConfig) ? $xpConfig->models->keyBy('source_type') : collect();
                                @endphp

                                @foreach($defaultModels as $sourceType => $label)
                                @php $existing = $existingModels->get($sourceType); @endphp
                                <tr class="model-row" data-default="true">
                                    <td>
                                        <input type="hidden" name="models[{{ $loop->index }}][source_type]" value="{{ $sourceType }}">
                                        <div class="form-control-plaintext font-weight-semibold" style="font-family: monospace; font-size: .85rem; color: #495057;">
                                            <i class="fas fa-code text-muted mr-1" style="font-size:.7rem;"></i>{{ $sourceType }}
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="models[{{ $loop->index }}][label]"
                                               class="form-control form-control-sm"
                                               value="{{ old("models.{$loop->index}.label", $existing->label ?? $label) }}">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="models[{{ $loop->index }}][xp]"
                                                   class="form-control xp-input"
                                                   value="{{ old("models.{$loop->index}.xp", $existing->xp ?? 100) }}"
                                                   required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">⚡</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-muted" title="Default model tidak bisa dihapus">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                    </td>
                                </tr>
                                @endforeach

                                {{-- Custom models yang sudah ada tapi bukan default --}}
                                @if(isset($xpConfig))
                                @foreach($xpConfig->models->whereNotIn('source_type', array_keys($defaultModels)) as $cm)
                                <tr class="model-row custom-row">
                                    <td>
                                        <input type="text" name="models[custom_{{ $loop->index }}][source_type]"
                                               class="form-control form-control-sm"
                                               placeholder="NamaModel" value="{{ $cm->source_type }}" required>
                                    </td>
                                    <td>
                                        <input type="text" name="models[custom_{{ $loop->index }}][label]"
                                               class="form-control form-control-sm"
                                               value="{{ $cm->label }}">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="models[custom_{{ $loop->index }}][xp]"
                                                   class="form-control xp-input" value="{{ $cm->xp }}" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">⚡</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row">
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
    </div>
</form>
@stop

@section('js')
<script>
    let customIndex = 1000; // Start dari 1000 agar tidak tabrakan dengan default

    document.getElementById('addModelRow').addEventListener('click', function () {
        customIndex++;
        const tbody = document.getElementById('modelsBody');
        const tr = document.createElement('tr');
        tr.className = 'model-row custom-row';
        tr.innerHTML = `
            <td>
                <input type="text" name="models[${customIndex}][source_type]"
                       class="form-control form-control-sm" placeholder="ContohModel" required>
            </td>
            <td>
                <input type="text" name="models[${customIndex}][label]"
                       class="form-control form-control-sm" placeholder="Label tampilan">
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" name="models[${customIndex}][xp]"
                           class="form-control xp-input" value="100" required>
                    <div class="input-group-append">
                        <span class="input-group-text">⚡</span>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        attachRemoveHandlers();
        updateXpColors();
    });

    function attachRemoveHandlers() {
        document.querySelectorAll('.remove-row').forEach(btn => {
            btn.onclick = function () {
                this.closest('tr').remove();
            };
        });
    }

    function updateXpColors() {
        document.querySelectorAll('.xp-input').forEach(input => {
            const val = parseInt(input.value) || 0;
            input.style.color = val < 0 ? '#dc3545' : val >= 0 ? '#28a745' : '';
            input.style.fontWeight = 'bold';
        });
    }

    document.getElementById('modelsBody').addEventListener('input', updateXpColors);
    attachRemoveHandlers();
    updateXpColors();
</script>
@stop
