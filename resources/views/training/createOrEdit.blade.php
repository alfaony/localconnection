@extends('adminlte::page')

@section('content')
<div class="col-md-12">
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
<div class="container py-3">
    <div class="card shadow-sm">
        <div class="card-header">{{ isset($training) ? 'Ubah Sertifikat Pelatihan' : 'Tambah Sertifikat Pelatihan' }}</div>
        <div class="card-body">
            <!-- Perhatikan bagaimana URL form diubah -->
            <form action="{{ isset($training) ? route('training.update', $training->slug) : route('training.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($training))
                    @method('PUT') <!-- Method Spoofing untuk update karena HTML forms tidak support PUT/PATCH/DELETE -->
                @endif
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Pelatihan</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', isset($training) ? $training->name : '') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="skills_mastered" class="form-label">Kemampuan yang Dikuasai</label>
                    <select class="js-example-responsive form-control @error('skills_mastered') is-invalid @enderror" name="skills_mastered[]" multiple="multiple" style="width: 100%" id="skills_mastered">
                        @foreach($skills as $skill)
                            <option value="{{ $skill->name }}" {{ in_array($skill->id, old('skills_mastered', isset($training) ? $training->skills_mastered : [])) ? 'selected' : '' }}>
                                {{ $skill->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('skills_mastered')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="certification_date" class="form-label">Tanggal Sertifikasi</label>
                    <input type="date" class="form-control @error('certification_date') is-invalid @enderror" id="certification_date" name="certification_date" value="{{ old('certification_date', isset($training) ? $training->certification_date : '') }}" required>
                    @error('certification_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="certification_number" class="form-label">No. Sertifikasi</label>
                    <input type="text" class="form-control @error('certification_number') is-invalid @enderror" id="certification_number" name="certification_number" value="{{ old('certification_number', isset($training) ? $training->certification_number : '') }}" required>
                    @error('certification_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @if(@$training->point && @$training->user->approvement_user_id == Auth::user()->id)
                <div class="mb-3">
                    <label for="points" class="form-label">Poin</label>
                    <input type="number" class="form-control" id="points" name="point" value="{{ old('point', isset($training) ? $training->point : '') }}" >
                    @error('point')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @endif
                <div class="mb-3">
                    <label for="certification_file" class="form-label">Upload Sertifikasi</label>
                    <input type="file" class="form-control @error('certification_file') is-invalid @enderror" id="certification_file" name="certification_file" {{ !isset($training) ? 'required' : '' }} accept="application/pdf">

                    @error('certification_file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if(isset($training) && $training->certification_file)
                        <div class="mt-2">
                            <strong>Current File:</strong> {{ $training->certification_file }}
                        </div>
                    @endif
                </div>
                @if(@$training->point && @$training->user->approvement_user_id == Auth::user()->id)
                <button type="submit" class="btn btn-primary">{{ isset($training) ? 'Ubah' : 'Simpan' }}</button>
                @else
                    @if(Auth::user()->approvement_user_id)
                    <button type="submit" class="btn btn-primary">{{ isset($training) ? 'Ubah' : 'Simpan' }}</button>
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
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.js-example-responsive').select2({
        tags: true, // Allow users to create new tags
        tokenSeparators: [',', ' '], // Split tags by these delimiters
        placeholder: 'Select or create skills',
        width: 'resolve', // Use the style width
        allowClear: true // Allow clearing of value
    });
});
</script>
@stop
@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<style>
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

        .select2-container--default .select2-selection--multiple .select2-selection__rendered li {
            color: black;
        }
</style>
@stop

