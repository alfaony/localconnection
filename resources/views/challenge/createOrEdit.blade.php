@extends('adminlte::page')

@section('title', isset($challenge) ? 'Edit Challenge' : 'Buat Challenge')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 fw-bold">{{ isset($challenge) ? '✏️ Edit Challenge' : '⚔️ Challenge Baru' }}</h1>
        <small style="color:#55596e;">{{ isset($challenge) ? 'Perbarui detail tantangan' : 'Buat tantangan baru untuk karyawan' }}</small>
    </div>
    <a href="{{ route('challenge.index') }}" class="btn btn-sm btn-outline-secondary mb-1">
        <i class="fas fa-arrow-left mr-1"></i> Kembali
    </a>
</div>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-11">
        @include('components.alert')
        <div class="card border-0 shadow-lg" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:18px;overflow:hidden;">
            <div style="height:4px;background:linear-gradient(90deg,#667eea,#f093fb,#f5a623);"></div>
            <div class="card-body p-4">

                <form action="{{ isset($challenge) ? route('challenge.update',$challenge) : route('challenge.store') }}"
                      method="POST">
                    @csrf
                    @if(isset($challenge)) @method('PUT') @endif

                    {{-- Name --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#c8d0e0;">Nama Challenge</label>
                        <input type="text" name="name" class="form-control gf @error('name') is-invalid @enderror"
                               value="{{ old('name', $challenge->name ?? '') }}"
                               placeholder="cth: Sprint Task Bulan April..." {{ (isset($challenge) && in_array($challenge->status, ['running', 'finish'])) ? 'readonly' : '' }}>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#c8d0e0;">Deskripsi <span style="color:#606880;">(opsional)</span></label>
                        <textarea name="description" rows="2" class="form-control gf @error('description') is-invalid @enderror"
                                  placeholder="Jelaskan tujuan challenge..." {{ (isset($challenge) && in_array($challenge->status, ['running', 'finish'])) ? 'readonly' : '' }}>{{ old('description', $challenge->description ?? '') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Module Type --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color:#c8d0e0;">Target Module <span style="color:#f5576c;">*</span></label>
                        <div class="row g-2" id="module-picker">
                            @foreach($moduleOptions as $value => $label)
                            @php
                                $selected = old('module_type', $challenge->module_type ?? '') === $value;
                            @endphp
                            <div class="col-6 col-md-3">
                                <label style="cursor:pointer;width:100%;">
                                    <input type="radio" name="module_type" value="{{ $value }}"
                                           class="d-none module-radio" {{ $selected ? 'checked' : '' }}>
                                    <div class="module-card text-center p-3 rounded-3"
                                         style="background:rgba(255,255,255,.04);border:2px solid rgba(255,255,255,.08);transition:all .2s;">
                                        <i class="{{ config('challenge.icons.'.$value) }}" style="color:{{ config('challenge.colors.'.$value) }};font-size:1.4rem;"></i>
                                        <div style="color:#a0a8d0;font-size:.65rem;margin-top:6px;line-height:1.3;">{{ $label }}</div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('module_type')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    {{-- Dates --}}
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color:#c8d0e0;">Tanggal Mulai <span style="color:#f5576c;">*</span></label>
                            <input type="date" name="start_date" class="form-control gf @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date', isset($challenge) ? $challenge->start_date->format('Y-m-d') : '') }}" {{ (isset($challenge) && in_array($challenge->status, ['running', 'finish'])) ? 'readonly' : '' }}>
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color:#c8d0e0;">Tanggal Selesai <span style="color:#f5576c;">*</span></label>
                            <input type="date" name="end_date" class="form-control gf @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date', isset($challenge) ? $challenge->end_date->format('Y-m-d') : '') }}" {{ (isset($challenge) && in_array($challenge->status, ['running', 'finish'])) ? 'readonly' : '' }}>
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Target --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#c8d0e0;">Target <span id="target-unit" style="color:#f5a623;">(jumlah)</span> <span style="color:#f5576c;">*</span></label>
                        <input type="number" name="target_count" min="1" class="form-control gf @error('target_count') is-invalid @enderror"
                               value="{{ old('target_count', $challenge->target_count ?? '') }}"
                               placeholder="cth: 20" {{ (isset($challenge) && in_array($challenge->status, ['running', 'finish'])) ? 'readonly' : '' }}>
                        @error('target_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Status --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#c8d0e0;">Status</label>
                        <select name="status" class="form-control gf @error('status') is-invalid @enderror">
                            <option value="draft" {{ old('status', $challenge->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="running" {{ old('status', $challenge->status ?? '') == 'running' ? 'selected' : '' }}>Running</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Event terkait --}}
                    @if($events->isNotEmpty())
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color:#c8d0e0;">
                            <i class="fas fa-calendar-alt me-1" style="color:#667eea;"></i>
                            Gabungkan ke Event <span style="color:#606880;">(opsional)</span>
                        </label>
                        <select name="events[]" id="events-select" class="form-control gf" multiple style="min-height:90px;">
                            @foreach($events as $ev)
                            @php
                                $isSelected = in_array($ev->id, old('events', $assignedEventIds ?? []));
                            @endphp
                            <option value="{{ $ev->id }}"
                                    data-sync="{{ $ev->sync_participants ? '1' : '0' }}"
                                    {{ $isSelected ? 'selected' : '' }}>
                                {{ $ev->name }}
                                ({{ $ev->start_date->format('d M') }} – {{ $ev->end_date->format('d M Y') }})
                                {{ $ev->sync_participants ? '⚡ Sync' : '' }}
                            </option>
                            @endforeach
                        </select>
                        @error('events')<small class="text-danger">{{ $message }}</small>@enderror

                        {{-- Info sync banner: muncul saat ada event ber-sync dipilih --}}
                        <div id="sync-info-banner" class="mt-2 p-2" style="display:none;background:rgba(56,239,125,.07);border-radius:8px;border:1px solid rgba(56,239,125,.2);">
                            <small style="color:#a0a8d0;font-size:.72rem;line-height:1.5;">
                                <i class="fas fa-sync-alt me-1" style="color:#38ef7d;"></i>
                                Event bertanda <strong style="color:#38ef7d;">⚡ Sync</strong> yang kamu pilih memiliki fitur <em>Sinkronisasi Peserta</em> aktif —
                                semua peserta event tersebut akan <strong style="color:#e0e0ff;">otomatis ditambahkan</strong> ke challenge ini saat disimpan.
                            </small>
                        </div>
                    </div>
                    @endif

                    {{-- Rewards --}}
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color:#c8d0e0;">Reward Point</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:rgba(245,166,35,.15);border-color:rgba(255,255,255,.15);color:#f5a623;">
                                    <i class="fas fa-coins"></i>
                                </span>
                                <input type="number" name="reward_point" min="0" class="form-control gf @error('reward_point') is-invalid @enderror"
                                       value="{{ old('reward_point', $challenge->reward_point ?? 0) }}" {{ (isset($challenge) && in_array($challenge->status, ['running', 'finish'])) ? 'readonly' : '' }}>
                            </div>
                            @error('reward_point')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color:#c8d0e0;">Reward XP</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background:rgba(240,147,251,.15);border-color:rgba(255,255,255,.15);color:#f093fb;">
                                    <i class="fas fa-star"></i>
                                </span>
                                <input type="number" name="reward_xp" min="0" class="form-control gf @error('reward_xp') is-invalid @enderror"
                                       value="{{ old('reward_xp', $challenge->reward_xp ?? 0) }}" {{ (isset($challenge) && in_array($challenge->status, ['running', 'finish'])) ? 'readonly' : '' }}>
                            </div>
                            @error('reward_xp')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>

                    <button type="submit" class="btn w-100 rounded-pill fw-bold py-2"
                            style="background:linear-gradient(90deg,#667eea,#f093fb);border:none;color:#fff;">
                        <i class="fas fa-save me-2"></i>{{ isset($challenge) ? 'Simpan Perubahan' : 'Buat Challenge' }}
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
.gf { background:rgba(255,255,255,.07)!important;border:1px solid rgba(255,255,255,.15)!important;color:#e0e0ff!important;border-radius:10px!important; }
.gf::placeholder { color:#606880; }
.gf:focus { background:rgba(255,255,255,.1)!important;border-color:rgba(102,126,234,.6)!important;box-shadow:0 0 0 .2rem rgba(102,126,234,.25)!important; }
.input-group-text { border-radius:10px 0 0 10px !important; }
.module-radio:checked + .module-card { border-color:#f093fb!important;background:rgba(240,147,251,.12)!important;box-shadow:0 0 14px rgba(240,147,251,.3); }
.module-card:hover { border-color:rgba(102,126,234,.5)!important;background:rgba(102,126,234,.08)!important; }
/* Select2 dark */
.select2-container .select2-selection--multiple { background-color:rgba(255,255,255,.07)!important;border:1px solid rgba(255,255,255,.15)!important;border-radius:10px!important;min-height:44px; }
.select2-container .select2-search--inline .select2-search__field { color:#e0e0ff!important; }
.select2-container--default .select2-selection--multiple .select2-selection__choice { background-color:rgba(102,126,234,.2)!important;border:1px solid rgba(102,126,234,.35)!important;color:#e0e0ff!important;border-radius:6px; }
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove { color:#ff6b6b!important;border-right:none!important; }
.select2-dropdown { background-color:#16213e!important;border:1px solid rgba(255,255,255,.1)!important;color:#e0e0ff!important; }
.select2-container--default .select2-results__option--highlighted { background-color:rgba(102,126,234,.3)!important;color:#fff!important; }
.select2-search--dropdown .select2-search__field { background-color:#111827!important;border:1px solid rgba(255,255,255,.1)!important;color:#e0e0ff!important; }
.select2-results__option[data-sync="1"] { border-left: 3px solid #38ef7d; }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function () {
    var $eventsSelect = $('#events-select');
    if ($eventsSelect.length) {
        $eventsSelect.select2({
            placeholder: 'Pilih event...',
            allowClear: true,
            templateResult: function (data) {
                if (!data.id) return data.text;
                var sync = $(data.element).data('sync');
                var $el = $('<span></span>').text(data.text);
                if (sync == '1') {
                    $el.css('border-left', '3px solid #38ef7d').css('padding-left', '8px');
                }
                return $el;
            },
        });

        // Tampilkan banner sync jika ada event ber-sync dipilih
        function checkSyncBanner() {
            var selected = $eventsSelect.val() || [];
            var hasSyncEvent = selected.some(function (id) {
                return $eventsSelect.find('option[value="' + id + '"]').data('sync') == '1';
            });
            $('#sync-info-banner').toggle(hasSyncEvent);
        }

        $eventsSelect.on('change', checkSyncBanner);
        checkSyncBanner(); // run on load (edit mode)
    }
});

document.querySelectorAll('.module-radio').forEach(function(radio) {
    radio.addEventListener('change', function() {
        const unit = document.getElementById('target-unit');
        unit.textContent = this.value === 'score' ? '(POINT)' : '(jumlah)';
    });
    // Set on load if already selected
    if (radio.checked) {
        const unit = document.getElementById('target-unit');
        unit.textContent = radio.value === 'score' ? '(POINT)' : '(jumlah)';
    }
});

// Swal warning saat simpan jika status = running
$('form').on('submit', function (e) {
    if ($('select[name="status"]').val() === 'running') {
        e.preventDefault();
        var $form = $(this);
        Swal.fire({
            title: 'Perhatian!',
            html: 'Challenge <b>tidak bisa diubah</b> ketika status sudah <b>Running</b>.<br>Lanjutkan?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#667eea',
            cancelButtonColor: '#6c757d',
        }).then(function (result) {
            if (result.value) {
                $form.off('submit').submit();
            }
        });
    }
});
</script>
@stop
