@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>Detail Perlengkapan: {{ $reduction->equipment->name }}</h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label"><strong>Tanggal:</strong></label>
                <p>{{ \Carbon\Carbon::parse($reduction->date)->format('d M Y') }}</p>
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Perlengkapan:</strong></label>
                <p>{{ $reduction->equipment->name }}</p>
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Jenis Pengurangan:</strong></label>
                <p>{{ $reduction->reduction->name }}</p>
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Stok Digunakan:</strong></label>
                <p>{{ $reduction->stock }}</p>
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Laporan:</strong></label>
                <p>{!! $reduction->report !!}</p>
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Temuan:</strong></label>
                <p>{!! $reduction->found !!}</p>
            </div>
            <div class="mb-3">
                <label class="form-label"><strong>Tindakan:</strong></label>
                <p>{!! $reduction->doing !!}</p>
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('equipment-reduction.edit', $reduction->slug) }}" class="btn btn-primary"><i class="fa fa-edit"></i> Ubah</a>
                <a href="{{ route('equipment-reduction.index') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali Ke Daftar</a>
            </div>
        </div>
    </div>
</div>
@endsection
