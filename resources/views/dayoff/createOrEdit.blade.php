@extends('adminlte::page')

@section('title', $mode === 'edit' ? 'Edit Cuti' : 'Ajukan Cuti')

@section('content_header')
    <h1>{{ $mode === 'edit' ? 'Edit Pengajuan Cuti' : 'Ajukan Cuti' }}</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form 
        action="{{ $mode === 'edit' ? route('dayoff.update', $cuti->id) : route('dayoff.store') }}" 
        method="POST" 
        enctype="multipart/form-data">
        @csrf
        @if($mode === 'edit') @method('PATCH') @endif

        <div class="mb-3">
            <label>Jenis Cuti</label>
            <select name="dayoff_type_code" id="dayoff_type_code" class="form-control" required {{ $mode === 'edit' ? 'disabled' : '' }}>
                <option value="">-- Pilih Jenis --</option>
                @foreach($types as $type)
                    <option value="{{ $type->code }}" 
                        {{ old('dayoff_type_code', $cuti->type->code ?? '') === $type->code ? 'selected' : '' }}>
                        {{ $type->name }}
                    </option>
                @endforeach
            </select>
            <small id="kuota-info" class="form-text text-muted"></small>
        </div>

        <div class="mb-3">
            <label>Tanggal Mulai</label>
            <input type="date" name="date_start" class="form-control" 
                value="{{ old('date_start', optional($cuti)->date_start) }}" required {{ $mode === 'edit' ? 'disabled' : '' }}>
        </div>

        <div class="mb-3">
            <label>Tanggal Selesai</label>
            <input type="date" name="date_end" class="form-control" 
                value="{{ old('date_end', optional($cuti)->date_end) }}" required {{ $mode === 'edit' ? 'disabled' : '' }}>
        </div>

        <div class="mb-3">
            <label>Alasan</label>
            <textarea name="reason" class="form-control">{{ old('reason', optional($cuti)->reason) }}</textarea>
        </div>

        <div class="mb-3">
            <label>Upload Bukti (khusus sakit)</label>
            <input type="file" name="file" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">
            {{ $mode === 'edit' ? 'Update' : 'Ajukan Cuti' }}
        </button>
    </form>
@stop

@section('js')
<script>
    $(document).ready(function () {
        if ("{{ $mode }}" === "create") {
            $('#dayoff_type_code').change(function () {
                let type = $(this).val();
                if (!type) return $('#kuota-info').text('');
                $.get(`{{ route('dayoff.checkQuota') }}?type=${type}`, function (data) {
                    $('#kuota-info').text(`Sisa kuota: ${data.quota}`);
                });
            }).trigger('change');
        }
    });
</script>
@stop