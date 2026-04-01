@extends('adminlte::page')

@section('title', isset($badge) ? 'Edit Badge' : 'Buat Badge')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 fw-bold" style="color:#e0e0ff;">{{ isset($badge) ? '✏️ Edit Badge' : '🆕 Badge Baru' }}</h1>
        <small style="color:#a0a8d0;">{{ isset($badge) ? 'Perbarui data badge' : 'Buat lencana baru untuk karyawan' }}</small>
    </div>
    <a href="{{ route('badge.index') }}" class="btn btn-sm btn-outline-secondary mb-1">
        <i class="fas fa-arrow-left mr-1"></i> Kembali
    </a>
</div>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-lg" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:16px;overflow:hidden;">
            <div style="height:4px;background:linear-gradient(90deg,#667eea,#f093fb,#f5a623);"></div>
            <div class="card-body p-4">

                {{-- Preview icon --}}
                <div class="text-center mb-4">
                    <div id="badge-preview" style="width:100px;height:100px;background:rgba(102,126,234,.15);border-radius:50%;padding:8px;border:2px solid rgba(102,126,234,.4);display:inline-flex;align-items:center;justify-content:center;margin:auto;">
                        @if(isset($badge) && $badge->image)
                        <img id="preview-img" src="{{ s3_asset(false, null, $badge->image) }}" style="width:76px;height:76px;object-fit:contain;" alt="preview">
                        @else
                        <span id="preview-placeholder" style="font-size:2.5rem;">🏅</span>
                        <img id="preview-img" src="" style="width:76px;height:76px;object-fit:contain;display:none;" alt="preview">
                        @endif
                    </div>
                </div>

                <form action="{{ isset($badge) ? route('badge.update', $badge) : route('badge.store') }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($badge)) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#c8d0e0;">Nama Badge</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $badge->name ?? '') }}"
                               placeholder="cth: Rajin Lembur, Pejuang Deadline..."
                               style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);color:#e0e0ff;border-radius:10px;">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color:#c8d0e0;">Deskripsi <span style="color:#a0a8d0;">(opsional)</span></label>
                        <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
                               value="{{ old('description', $badge->description ?? '') }}"
                               placeholder="Keterangan singkat..."
                               style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);color:#e0e0ff;border-radius:10px;">
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="color:#c8d0e0;">Icon Badge <span style="color:#a0a8d0;">(JPG/PNG/SVG/WebP, maks 2MB)</span></label>
                        <input type="file" name="image" id="image-input"
                               class="form-control @error('image') is-invalid @enderror"
                               accept=".jpg,.jpeg,.png,.svg,.webp"
                               style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.15);color:#e0e0ff;border-radius:10px;">
                        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold" style="background:linear-gradient(90deg,#667eea,#f093fb);border:none;padding:.7rem;">
                        <i class="fas fa-save me-2"></i>{{ isset($badge) ? 'Simpan Perubahan' : 'Buat Badge' }}
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
document.getElementById('image-input').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        const img = document.getElementById('preview-img');
        const ph  = document.getElementById('preview-placeholder');
        img.src = ev.target.result;
        img.style.display = 'block';
        if (ph) ph.style.display = 'none';
    };
    reader.readAsDataURL(file);
});
</script>
@stop

@section('css')
<style>
.form-control::placeholder { color: #606880; }
.form-control:focus { background: rgba(255,255,255,.1) !important; border-color: rgba(102,126,234,.6) !important; color: #e0e0ff !important; box-shadow: 0 0 0 .2rem rgba(102,126,234,.25); }
</style>
@stop
