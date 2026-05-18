@extends('adminlte::page')

@section('title', $mode === 'edit' ? 'Edit Report Link' : 'Tambah Report Link')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">
            <i class="fas fa-{{ $mode === 'edit' ? 'edit' : 'plus-circle' }} mr-2"></i>
            {{ $mode === 'edit' ? 'Edit Report Link' : 'Tambah Report Link' }}
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('report-link.index') }}">Report Link</a></li>
                <li class="breadcrumb-item active">{{ $mode === 'edit' ? 'Edit' : 'Tambah' }}</li>
            </ol>
        </nav>
    </div>
@stop

@section('css')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
.img-row {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 12px;
}
.img-row .thumb-preview {
    width: 100%;
    height: 130px;
    object-fit: cover;
    border-radius: 6px;
    display: none;
}
.img-row .thumb-placeholder {
    width: 100%;
    height: 130px;
    background: #e9ecef;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
    font-size: 2rem;
}
.existing-deleted .img-row-inner { opacity: .3; pointer-events: none; }
</style>
@stop

@section('content')
@include('components.alert')

<form action="{{ $mode === 'edit' ? route('report-link.update', $reportLink->id) : route('report-link.store') }}"
      method="POST" enctype="multipart/form-data">
    @csrf
    @if($mode === 'edit') @method('PUT') @endif

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="m-0"><i class="fas fa-link mr-2"></i>Informasi Laporan</h5>
        </div>
        <div class="card-body">

            {{-- Info utama --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Laporan <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               placeholder="Masukkan nama laporan"
                               value="{{ old('name', $reportLink->name ?? '') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                               value="{{ old('date', isset($reportLink) ? $reportLink->date->format('Y-m-d') : now()->format('Y-m-d')) }}" required>
                        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="font-weight-bold">Link <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-link"></i></span>
                            </div>
                            <input type="url" name="link" class="form-control @error('link') is-invalid @enderror"
                                   placeholder="https://..."
                                   value="{{ old('link', $reportLink->link ?? '') }}" required>
                            @error('link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="font-weight-bold">Deskripsi</label>
                        <input class="thriveEditor form-control"
                               id="description_description"
                               data-ids="description"
                               name="description"
                               value="{{ old('description', $reportLink->description ?? '') }}"
                               placeholder="Tuliskan deskripsi laporan..."/>
                        @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <hr>

            {{-- ======= Gambar yang sudah ada (edit mode) ======= --}}
            @if($mode === 'edit' && $reportLink->images->count())
            <div class="mb-4">
                <label class="font-weight-bold d-block mb-2">
                    <i class="fas fa-photo-video mr-1"></i>Gambar Tersimpan
                </label>
                <div class="row">
                    @foreach($reportLink->images as $img)
                    <div class="col-12 col-md-6 col-lg-4 mb-3" id="existing-wrapper-{{ $img->id }}">
                        <div class="img-row">
                            <div class="img-row-inner">
                                <img src="{{ asset('storage/' . $img->path) }}"
                                     class="thumb-preview d-block" alt="">
                                <div class="d-flex justify-content-between align-items-center mt-2 mb-1">
                                    <small class="text-muted">Deskripsi</small>
                                    <button type="button" class="btn btn-danger btn-sm py-0 px-2"
                                            style="font-size:.72rem;"
                                            onclick="markDeleteExisting({{ $img->id }})">
                                        <i class="fas fa-trash-alt mr-1"></i>Hapus
                                    </button>
                                </div>
                                <input type="text"
                                       name="existing_descriptions[{{ $img->id }}]"
                                       class="form-control form-control-sm"
                                       placeholder="Deskripsi gambar (opsional)"
                                       value="{{ old('existing_descriptions.'.$img->id, $img->description ?? '') }}">
                            </div>
                        </div>
                        <input type="checkbox" name="delete_images[]" value="{{ $img->id }}"
                               id="del-{{ $img->id }}" class="d-none">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- ======= Upload Gambar Baru ======= --}}
            <div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="font-weight-bold mb-0">
                        <i class="fas fa-images mr-1"></i>
                        {{ $mode === 'edit' ? 'Tambah Gambar Baru' : 'Upload Gambar' }}
                        <small class="text-muted font-weight-normal">(maks 5MB/gambar)</small>
                    </label>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addImageRow()">
                        <i class="fas fa-plus mr-1"></i>Tambah Gambar
                    </button>
                </div>

                @error('images.*')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                <div id="newImagesContainer">
                    {{-- Satu baris default --}}
                </div>

                <p id="noImageHint" class="text-muted small mt-1">
                    <i class="fas fa-info-circle mr-1"></i>Klik "Tambah Gambar" untuk menambahkan gambar.
                </p>
            </div>

        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('report-link.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i>Kembali
            </a>
            <button type="submit" class="btn ml-auto btn-{{ $mode === 'edit' ? 'warning' : 'primary' }}">
                <i class="fas fa-{{ $mode === 'edit' ? 'save' : 'check' }} mr-1"></i>
                {{ $mode === 'edit' ? 'Update' : 'Simpan' }}
            </button>
        </div>
    </div>
</form>
@stop

@section('js')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
let imgRowCount = 0;

function addImageRow() {
    const idx = imgRowCount++;
    const container = document.getElementById('newImagesContainer');
    const hint = document.getElementById('noImageHint');

    const row = document.createElement('div');
    row.className = 'col-12 col-md-6 col-lg-4 mb-3 d-inline-block align-top';
    row.id = 'new-row-' + idx;
    row.style.width = '100%';

    row.innerHTML = `
        <div class="img-row">
            <div class="thumb-placeholder" id="placeholder-${idx}">
                <i class="fas fa-image"></i>
            </div>
            <img class="thumb-preview" id="preview-${idx}" alt="">

            <div class="mt-2">
                <input type="file" name="images[]" class="form-control form-control-sm mb-2"
                       accept="image/jpeg,image/png,image/jpg,image/webp"
                       onchange="previewImage(this, ${idx})">
                <input type="text" name="descriptions[]" class="form-control form-control-sm"
                       placeholder="Deskripsi gambar (opsional)">
            </div>
            <div class="text-right mt-2">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeImageRow(${idx})">
                    <i class="fas fa-times mr-1"></i>Hapus
                </button>
            </div>
        </div>`;

    // Wrap in a row if not already
    const wrapper = document.createElement('div');
    wrapper.className = 'row';
    wrapper.id = 'row-wrapper-' + idx;

    const col = document.createElement('div');
    col.className = 'col-12 col-md-6 col-lg-4 mb-3';
    col.id = 'new-col-' + idx;
    col.appendChild(row.querySelector('.img-row'));
    wrapper.appendChild(col);

    container.appendChild(wrapper);
    hint.style.display = 'none';
}

function previewImage(input, idx) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('preview-' + idx);
        const placeholder = document.getElementById('placeholder-' + idx);
        if (preview) { preview.src = e.target.result; preview.style.display = 'block'; }
        if (placeholder) placeholder.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}

function removeImageRow(idx) {
    const wrapper = document.getElementById('row-wrapper-' + idx);
    if (wrapper) wrapper.remove();

    const hint = document.getElementById('noImageHint');
    if (document.getElementById('newImagesContainer').children.length === 0) {
        hint.style.display = '';
    }
}

function markDeleteExisting(id) {
    const wrapper = document.getElementById('existing-wrapper-' + id);
    const checkbox = document.getElementById('del-' + id);
    checkbox.checked = true;
    wrapper.classList.add('existing-deleted');
}
</script>
@stop
