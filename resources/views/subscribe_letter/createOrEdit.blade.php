@extends('adminlte::page')

@section('title', isset($letter) ? 'Edit Surat' : 'Tambah Surat')
@section('content_header')
    <h1>{{ isset($letter) ? 'Edit Surat Langganan' : 'Tambah Surat Langganan' }}</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ isset($letter) 
                ? route('subscribe-letter.update', $letter->id)
                : route('subscribe-letter.store') }}"
            method="POST"
            enctype="multipart/form-data">

            @csrf
            @if(isset($letter))
                @method('PUT')
            @endif

            {{-- Tampilkan error --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-3">
                <label>Nama Surat <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control"
                    value="{{ old('name', $letter->name ?? '') }}" required>
            </div>

            <div class="mb-3">
                <label>Berlaku Dari <span class="text-danger">*</span></label>
                <input type="date" name="valid_from" class="form-control"
                    value="{{ old('valid_from', isset($letter) ? $letter->valid_from: '') }}" required>
            </div>

            <div class="mb-3">
                <label>Sampai <span class="text-danger">*</span></label>
                <input type="date" name="valid_until" class="form-control"
                    value="{{ old('valid_until', isset($letter) ? $letter->valid_until : '') }}" required>
            </div>

            <div class="mb-3">
                <label>Penanggung Jawab <span class="text-danger">*</span></label>
                <select name="pic_user_id" class="form-control" required>
                    <option value="">-- Pilih PIC --</option>
                    @foreach($users as $id => $name)
                        <option value="{{ $id }}"
                            {{ old('pic_user_id', $letter->pic_user_id ?? '') == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label>Upload Dokumen (PDF/JPG/PNG)    @if(!isset($letter))<span class="text-danger">*</span> @endif</label>
                <input type="file" name="document_path" accept=".pdf,.jpg,.jpeg,.png" class="form-control"
                @if(!isset($letter)) required @endif
                >
                @if(isset($letter) && $letter->document_path)
                    <p class="mt-2">
                        <a href="{{ s3_asset(true,10, $letter->document_path) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                            📄 Lihat Dokumen Saat Ini
                        </a>
                    </p>
                @endif
            </div>

            <button type="submit" class="btn btn-success">
                {{ isset($letter) ? 'Update' : 'Simpan' }}
            </button>
            <a href="{{ route('subscribe-letter.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@stop