@extends('adminlte::page')

@section('content')
<div class="container p-3">
    <div class="card shadow-sm">
        <div class="card-header">{{ isset($ipRight) ? 'Edit Hak Cipta' : 'Mendaftarkan Hak Cipta' }}</div>
        <div class="card-body">
            <form action="{{ isset($ipRight) ? route('ip-right.update', $ipRight->slug) : route('ip-right.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($ipRight))
                    @method('PUT')
                @endif
                <div class="form-group">
                    <label for="name">Nama Ciptaan</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', isset($ipRight) ? $ipRight->name : '') }}" required>
                    @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="patent_date">Tanggal Terbit Paten</label>
                    <input type="date" class="form-control @error('patent_date') is-invalid @enderror" name="patent_date" value="{{ old('patent_date', isset($ipRight) ? $ipRight->patent_date : '') }}" required>
                    @error('patent_date')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="patent_number">No. Patent / Hak Cipta</label>
                    <input type="text" class="form-control @error('patent_number') is-invalid @enderror" name="patent_number" value="{{ old('patent_number', isset($ipRight) ? $ipRight->patent_number : '') }}" required>
                    @error('patent_number')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                @if(@$ipRight->point && @$ipRight->user->approvement_user_id == Auth::user()->id)
                <div class="mb-3">
                    <label for="points" class="form-label">Poin</label>
                    <input type="number" class="form-control" id="points" name="point" value="{{ old('point', isset($ipRight) ? $ipRight->point : '') }}" >
                    @error('point')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @endif
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <input class="thriveEditor form-control" id="description_description" data-ids="description" name="description" value="{{ old('description', isset($ipRight) ? $ipRight->description : '') }}"/>
                    @error('description')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="file_path">Upload Hak Cipta</label>
                    <input type="file" class="form-control @error('file_path') is-invalid @enderror" name="file_path" accept="application/pdf" {{ isset($ipRight) ? '' : 'required' }}>
                    @error('file_path')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    @if(isset($ipRight) && $ipRight->file_path)
                        <div class="mt-2">
                            <strong>Current File:</strong> {{ $ipRight->file_path }}
                        </div>
                    @endif
                </div>
                @if(@$ipRight->point && @$ipRight->user->approvement_user_id == Auth::user()->id)
                <button type="submit" class="btn btn-primary">{{ isset($ipRight) ? 'Ubah' : 'Simpan' }}</button>
                @else
                    @if(Auth::user()->approvement_user_id)
                    <button type="submit" class="btn btn-primary">{{ isset($ipRight) ? 'Ubah' : 'Simpan' }}</button>
                    @else
                    <div class="mt-5">
                        <span class="alert alert-warning" role="alert">
                            Silahkan hubungi admin atau atasan Anda untuk memberikan approval.
                        </span>
                    </div>
                    @endif
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>

@stop
@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<style>
   body
   {
        font-family: Arial, sans-serif;
        /* padding: 20px; */
        background-color: #f4f4f4;
    }
    .select2-selection__rendered
    {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single
    {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }
    .ql-container
    {
        min-height: 150px;
        height: auto;
    }
</style>
@stop
