@extends('adminlte::page')

@section('title', 'Tambah Software')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Tambah Software</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('software.index') }}">Software</a></li>
                <li class="breadcrumb-item active">Tambah</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('software.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="nama">Nama Software <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('nama') is-invalid @enderror" 
                                   id="nama" 
                                   name="nama" 
                                   value="{{ old('nama') }}" 
                                   placeholder="Contoh: Netflix"
                                   required>
                            @error('nama')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tipe_paket">Tipe Paket <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('tipe_paket') is-invalid @enderror" 
                                   id="tipe_paket" 
                                   name="tipe_paket" 
                                   value="{{ old('tipe_paket') }}" 
                                   placeholder="Contoh: Premium, Family, Pro"
                                   required>
                            @error('tipe_paket')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">Tipe paket dari software (Premium, Family, dll)</small>
                        </div>

                        <div class="form-group">
                            <label for="description">Deskripsi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Deskripsi singkat tentang software ini...">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="logo">Logo Software</label>
                            <div class="custom-file">
                                <input type="file" 
                                       class="custom-file-input @error('logo') is-invalid @enderror" 
                                       id="logo" 
                                       name="logo"
                                       accept="image/*">
                                <label class="custom-file-label" for="logo">Pilih file...</label>
                                @error('logo')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Format: JPG, JPEG, PNG. Max: 2MB</small>
                            
                            <div id="preview-container" class="mt-3" style="display: none;">
                                <img id="preview-image" src="" alt="Preview" class="img-thumbnail" style="max-width: 100%;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
                <a href="{{ route('software.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Batal
                </a>
            </div>
        </form>
    </div>
@stop

@section('css')
@stop

@section('js')
<script>
$(document).ready(function() {
    // Custom file input label
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').addClass("selected").html(fileName);
        
        // Preview image
        if (this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-image').attr('src', e.target.result);
                $('#preview-container').show();
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>
@stop
