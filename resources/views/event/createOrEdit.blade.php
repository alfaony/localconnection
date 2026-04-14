@extends('adminlte::page')

@section('title', isset($event) ? 'Edit Event' : 'Buat Event')

@section('content_header')
<div class="d-flex align-items-center gap-2">
    <a href="{{ route('event.index') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 class="m-0 fw-bold">{{ isset($event) ? '✏️ Edit Event' : '🎪 Buat Event Baru' }}</h1>
        <small style="color:#55596e;">Atur detail & jadwal event perusahaan</small>
    </div>
</div>
@stop

@section('content')
@include('components.alert')

<form action="{{ isset($event) ? route('event.update', $event->id) : route('event.store') }}"
      method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($event)) @method('PUT') @endif

    <div class="row g-4">
        {{-- Kolom Kiri: Info + Image --}}
        <div class="col-lg-7">
            {{-- Info --}}
            <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:16px;">
                <div style="height:3px;background:linear-gradient(90deg,#667eea,#f093fb);border-radius:16px 16px 0 0;"></div>
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color:#a5b4fc;"><i class="fas fa-info-circle me-2"></i>Informasi Event</h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#c8d0e0;">Nama Event <span style="color:#f5576c;">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $event->name ?? '') }}"
                               class="form-control gf @error('name') is-invalid @enderror"
                               placeholder="Contoh: Rapat Bulanan, Golden Expedition...">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#c8d0e0;">Deskripsi <span style="color:#606880;">(opsional)</span></label>
                        <input class="thriveEditor form-control" id="description_description" data-ids="description" name="description" placeholder="yang akan dicetak di perjanjian" value="{{ old('description', $event->description ?? '') }}"/>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Warna bar --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#c8d0e0;">Warna Bar Kalender</label>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            @php
                            $colorPresets = ['#667eea','#f093fb','#f5a623','#38ef7d','#f87171','#60a5fa','#fbbf24','#a78bfa','#34d399','#fb7185'];
                            $curColor = old('color', $event->color ?? '#667eea');
                            @endphp
                            @foreach($colorPresets as $c)
                            <label style="cursor:pointer;">
                                <input type="radio" name="color" value="{{ $c }}" class="d-none color-radio"
                                       {{ $curColor === $c ? 'checked' : '' }}>
                                <div class="color-swatch"
                                     style="width:26px;height:26px;border-radius:50%;background:{{ $c }};border:3px solid {{ $curColor === $c ? '#fff' : 'transparent' }};transition:border .15s;cursor:pointer;"></div>
                            </label>
                            @endforeach
                            <input type="color" id="custom-color-picker" value="{{ $curColor }}"
                                   style="width:28px;height:28px;border:none;background:none;cursor:pointer;padding:0;"
                                   title="Pilih warna custom">
                        </div>
                        @error('color')<div class="text-danger" style="font-size:.8rem;">{{ $message }}</div>@enderror
                    </div>

                    {{-- Jam --}}
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color:#c8d0e0;">Jam Mulai</label>
                            <input type="time" name="start_time"
                                   value="{{ old('start_time', isset($event) && $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('H:i') : '') }}"
                                   class="form-control gf @error('start_time') is-invalid @enderror">
                            @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color:#c8d0e0;">Jam Selesai</label>
                            <input type="time" name="end_time"
                                   value="{{ old('end_time', isset($event) && $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('H:i') : '') }}"
                                   class="form-control gf @error('end_time') is-invalid @enderror">
                            @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Image --}}
            <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:16px;">
                <div style="height:3px;background:linear-gradient(90deg,#f093fb,#f5576c);border-radius:16px 16px 0 0;"></div>
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-1" style="color:#f093fb;"><i class="fas fa-image me-2"></i>Image Event</h6>
                    <small style="color:#606880;" class="d-block mb-3">Ditampilkan di halaman detail, <b>bukan</b> di bar kalender.</small>

                    @if(isset($event) && $event->image)
                    <div class="mb-2">
                        <img src="{{ s3_asset(true, null, $event->image) }}" alt="current"
                             style="width:100%;max-height:160px;object-fit:cover;border-radius:10px;border:1px solid rgba(255,255,255,.1);">
                        <small style="color:#a0a8d0;" class="d-block mt-1">Gambar saat ini. Upload baru untuk mengganti.</small>
                    </div>
                    @endif
                    <div id="image-preview-wrap" style="display:none;margin-bottom:10px;">
                        <img id="image-preview" src="#" alt="preview"
                             style="width:100%;max-height:160px;object-fit:cover;border-radius:10px;border:1px solid rgba(255,255,255,.1);">
                    </div>
                    <input type="file" name="image" id="image-input" accept="image/*"
                           class="form-control gf @error('image') is-invalid @enderror">
                    <small style="color:#606880;">Max 5MB.</small>
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Status --}}
            <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:16px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', $event->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_active" style="color:#c8d0e0;">Event Aktif</label>
                    </div>
                    <small style="color:#606880;">Event nonaktif tidak muncul di kalender karyawan.</small>
                </div>
            </div>

            {{-- Challenges terkait --}}
            <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:16px;">
                <div style="height:3px;background:linear-gradient(90deg,#ffd700,#f5a623);border-radius:16px 16px 0 0;"></div>
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-1" style="color:#ffd700;"><i class="bi bi-trophy-fill me-2"></i>Challenge Terkait</h6>
                    <small style="color:#606880;" class="d-block mb-3">
                        Pilih challenge yang ingin dikaitkan dengan event ini. Challenge tidak wajib punya event.
                    </small>

                    <select name="challenges[]" id="challenges-select" class="form-select gf mb-3" multiple style="min-height:110px;">
                        @foreach($challenges as $ch)
                        <option value="{{ $ch->id }}"
                            {{ in_array($ch->id, old('challenges', $assignedChallengeIds ?? [])) ? 'selected' : '' }}>
                            {{ $ch->name }}
                            <span style="color:#606880;">({{ ucfirst($ch->status) }})</span>
                        </option>
                        @endforeach
                    </select>
                    @error('challenges')<div class="text-danger" style="font-size:.8rem;">{{ $message }}</div>@enderror

                    {{-- Sync toggle --}}
                    <div class="p-3" style="background:rgba(255,215,0,.06);border-radius:10px;border:1px solid rgba(255,215,0,.15);">
                        <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" name="sync_participants" id="sync_participants" value="1"
                                   {{ old('sync_participants', $event->sync_participants ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="sync_participants" style="color:#ffd700;">
                                <i class="fas fa-sync-alt me-1"></i>Sinkronisasi Peserta
                            </label>
                        </div>
                        <small style="color:#a0a8d0;font-size:.72rem;line-height:1.4;">
                            Jika aktif: setiap orang yang ditambahkan ke event akan otomatis diikutsertakan ke semua challenge terkait.
                            Sebaliknya, jika dikeluarkan dari event, mereka juga dikeluarkan dari challenge tersebut.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Jadwal + Peserta --}}
        <div class="col-lg-5">
            {{-- Jadwal --}}
            <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:16px;">
                <div style="height:3px;background:linear-gradient(90deg,#f5a623,#38ef7d);border-radius:16px 16px 0 0;"></div>
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color:#f5a623;"><i class="fas fa-calendar-alt me-2"></i>Jadwal Event</h6>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color:#c8d0e0;">Tanggal Mulai <span style="color:#f5576c;">*</span></label>
                            <input type="date" name="start_date"
                                   value="{{ old('start_date', isset($event) ? $event->start_date?->format('Y-m-d') : '') }}"
                                   class="form-control gf @error('start_date') is-invalid @enderror">
                            @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color:#c8d0e0;">Tanggal Selesai <span style="color:#f5576c;">*</span></label>
                            <input type="date" name="end_date"
                                   value="{{ old('end_date', isset($event) ? $event->end_date?->format('Y-m-d') : '') }}"
                                   class="form-control gf @error('end_date') is-invalid @enderror">
                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Routine toggle --}}
                    <div class="p-3 mb-3" style="background:rgba(255,255,255,.04);border-radius:10px;border:1px solid rgba(255,255,255,.08);">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_routine" id="is_routine" value="1"
                                   {{ old('is_routine', $event->is_routine ?? false) ? 'checked' : '' }}
                                   onchange="toggleRoutine(this.checked)">
                            <label class="form-check-label fw-semibold" for="is_routine" style="color:#c8d0e0;">
                                <i class="fas fa-sync-alt me-1" style="color:#667eea;"></i>Ulangi setiap minggu
                            </label>
                        </div>
                        <small style="color:#606880;font-size:.72rem;">
                            Sistem otomatis mengulang event ini tiap minggu dengan durasi yang sama.<br>
                            Contoh: event 3 hari → tiap Senin muncul 3 hari di kalender.
                        </small>

                        <div id="routine-end-wrap" style="{{ old('is_routine', $event->is_routine ?? false) ? '' : 'display:none;' }}margin-top:12px;">
                            <label class="form-label fw-semibold" style="color:#c8d0e0;font-size:.82rem;">Berakhir Pada <span style="color:#606880;">(opsional)</span></label>
                            <input type="date" name="routine_end_date"
                                   value="{{ old('routine_end_date', isset($event) ? $event->routine_end_date?->format('Y-m-d') : '') }}"
                                   class="form-control gf @error('routine_end_date') is-invalid @enderror">
                            <small style="color:#606880;font-size:.7rem;">Kosongkan jika tidak ada batas waktu.</small>
                            @error('routine_end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Preview bar --}}
                    <div style="background:rgba(255,255,255,.04);border-radius:10px;padding:12px;border:1px solid rgba(255,255,255,.08);">
                        <div style="color:#606880;font-size:.68rem;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;">Preview bar kalender</div>
                        <div id="bar-preview" style="border-left:3px solid #667eea;background:rgba(102,126,234,.15);border-radius:6px;padding:6px 12px;">
                            <span id="bar-preview-text" style="color:#667eea;font-size:.78rem;font-weight:700;">Nama Event</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Invite users (hanya saat create) --}}
            @if(!isset($event))
            <div class="card border-0 shadow-sm mb-4" style="background:linear-gradient(135deg,#1a1a2e,#16213e);border-radius:16px;">
                <div style="height:3px;background:linear-gradient(90deg,#38ef7d,#667eea);border-radius:16px 16px 0 0;"></div>
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3" style="color:#38ef7d;"><i class="fas fa-user-plus me-2"></i>Undang Peserta</h6>
                    <select name="users[]" id="users-select" class="form-select gf" multiple style="min-height:110px;">
                        @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                    <small style="color:#606880;">Bisa ditambah/dikurangi di halaman detail.</small>
                </div>
            </div>
            @endif

            <button type="submit" class="btn btn-primary w-100" style="border-radius:12px;padding:12px;font-weight:700;">
                <i class="fas fa-save me-2"></i>{{ isset($event) ? 'Simpan Perubahan' : 'Buat Event' }}
            </button>
        </div>
    </div>
</form>
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
.gf { background:#111827!important;border:1px solid rgba(255,255,255,.1)!important;color:#e0e0ff!important;border-radius:8px!important; }
.gf::placeholder { color:#55596e!important; }
.gf option { background:#111827; }
.color-swatch:hover { border-color:#fff!important; }

/* Select2 Dark Theme */
.select2-container .select2-selection--multiple { background-color:#111827!important;border:1px solid rgba(255,255,255,.1)!important;border-radius:8px!important;min-height:44px; }
.select2-container .select2-search--inline .select2-search__field { color:#e0e0ff!important; }
.select2-container--default .select2-selection--multiple .select2-selection__choice { background-color:rgba(102,126,234,.15)!important;border:1px solid rgba(102,126,234,.3)!important;color:#e0e0ff!important;border-radius:6px; }
.select2-container--default .select2-selection--multiple .select2-selection__choice__remove { color:#ff6b6b!important;border-right:none!important; }
.select2-dropdown { background-color:#16213e!important;border:1px solid rgba(255,255,255,.1)!important;color:#e0e0ff!important; }
.select2-container--default .select2-results__option--highlighted { background-color:rgba(102,126,234,.3)!important;color:#fff!important; }
.select2-search--dropdown .select2-search__field { background-color:#111827!important;border:1px solid rgba(255,255,255,.1)!important;color:#e0e0ff!important; }

/* Quill Dark Theme Overrides */
.ql-toolbar.ql-snow { background-color: #111827 !important; border: 1px solid rgba(255,255,255,.1) !important; border-radius: 8px 8px 0 0 !important; }
.ql-container.ql-snow { background-color: #16213e !important; border: 1px solid rgba(255,255,255,.1) !important; border-top: none !important; border-radius: 0 0 8px 8px !important; color: #e0e0ff !important; font-family: inherit; }
.ql-editor { color: #e0e0ff !important; min-height: 120px; }
.ql-editor.ql-blank::before { color: #55596e !important; font-style: normal; }
.ql-snow .ql-stroke { stroke: #a5b4fc !important; }
.ql-snow .ql-fill, .ql-snow .ql-stroke.ql-fill { fill: #a5b4fc !important; }
.ql-snow .ql-picker { color: #a5b4fc !important; }
.ql-snow .ql-picker-options { background-color: #111827 !important; border: 1px solid rgba(255,255,255,.1) !important; }
.ql-snow .ql-picker-item:hover, .ql-snow .ql-picker-label:hover { color: #fff !important; }
.ql-snow .ql-picker-item:hover .ql-stroke, .ql-snow .ql-picker-label:hover .ql-stroke { stroke: #fff !important; }
.ql-snow .ql-picker-item:hover .ql-fill, .ql-snow .ql-picker-label:hover .ql-fill { fill: #fff !important; }
button.ql-active .ql-stroke { stroke: #fff !important; }
button.ql-active .ql-fill { fill: #fff !important; }
</style>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
let _currentColor = '{{ old('color', $event->color ?? '#667eea') }}';

// ── Color swatches preset ─────────────────────────────────────────
document.querySelectorAll('.color-radio').forEach(r => {
    r.addEventListener('change', function () {
        _currentColor = this.value;
        refreshSwatchUI();
        refreshBarPreview();
    });
});

function refreshSwatchUI() {
    document.querySelectorAll('.color-swatch').forEach(el => {
        const radio = el.closest('label')?.querySelector('input');
        el.style.borderColor = radio?.checked ? '#fff' : 'transparent';
    });
}

// ── Custom color picker ───────────────────────────────────────────
document.getElementById('custom-color-picker')?.addEventListener('input', function () {
    _currentColor = this.value;
    // Deselect preset radios; inject hidden input
    document.querySelectorAll('.color-radio').forEach(r => { r.checked = false; r.name = '_color_preset'; });
    let hidden = document.getElementById('color-hidden');
    if (!hidden) {
        hidden = document.createElement('input');
        hidden.type = 'hidden'; hidden.name = 'color'; hidden.id = 'color-hidden';
        document.querySelector('form').appendChild(hidden);
    }
    hidden.value = this.value;
    document.querySelectorAll('.color-swatch').forEach(el => el.style.borderColor = 'transparent');
    refreshBarPreview();
});

// ── Bar preview ───────────────────────────────────────────────────
function refreshBarPreview() {
    const bar  = document.getElementById('bar-preview');
    const text = document.getElementById('bar-preview-text');
    const name = document.querySelector('[name="name"]')?.value || 'Nama Event';
    if (bar)  { bar.style.borderLeftColor = _currentColor; bar.style.background = _currentColor + '22'; }
    if (text) { text.style.color = _currentColor; text.textContent = name; }
}
document.querySelector('[name="name"]')?.addEventListener('input', refreshBarPreview);
refreshBarPreview();

// ── Routine toggle ────────────────────────────────────────────────
function toggleRoutine(checked) {
    document.getElementById('routine-end-wrap').style.display = checked ? 'block' : 'none';
}

// ── Image preview ─────────────────────────────────────────────────
document.getElementById('image-input')?.addEventListener('change', function () {
    if (this.files && this.files[0]) {
        document.getElementById('image-preview').src = URL.createObjectURL(this.files[0]);
        document.getElementById('image-preview-wrap').style.display = 'block';
    }
});

// ── Select2 ───────────────────────────────────────────────────────
$(document).ready(function() {
    $('#users-select').select2({ placeholder: 'Pilih karyawan...', allowClear: true });
    $('#challenges-select').select2({ placeholder: 'Pilih challenge...', allowClear: true });
});
</script>
@stop
