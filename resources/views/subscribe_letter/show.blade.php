@extends('adminlte::page')

@section('title', 'Detail Surat Langganan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Detail Surat Langganan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('subscribe-letter.index') }}">Surat Langganan</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $letter->name }}</li>
            </ol>
        </nav>
    </div>
@stop
@section('content')
@include('components.alert')
<div class="card">
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Nama Surat</dt>
            <dd class="col-sm-9">{{ $letter->name }}</dd>

            <dt class="col-sm-3">Berlaku Dari</dt>
            <dd class="col-sm-9">{{ $letter->valid_from }}</dd>

            <dt class="col-sm-3">Berlaku Sampai</dt>
            <dd class="col-sm-9">
                <span class="badge bg-{{ $letter->getColorStatusFor('valid_until') }}">
                    {{ $letter->valid_until }}
                </span>
            </dd>

            <dt class="col-sm-3">Penanggung Jawab</dt>
            <dd class="col-sm-9">{{ $letter->responsibleUser->name ?? '-' }}</dd>

            <dt class="col-sm-3">Dokumen</dt>
            <dd class="col-sm-9">
                @if($letter->document_path)
                    <a href="{{ asset('storage/' . $letter->document_path) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                        📄 Lihat Dokumen
                    </a>
                @else
                    <span class="text-muted">Tidak ada dokumen</span>
                @endif
            </dd>
        </dl>

        <button class="btn btn-warning mb-3" data-bs-toggle="modal" data-bs-target="#modalPerpanjang">
            Perpanjang Masa Berlaku
        </button>

        @include('subscribe_letter.modal_perpanjang')
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">Riwayat Perubahan</div>
    <div class="card-body">
        <ul class="list-group">
            @forelse($letter->activities->sortByDesc('created_at') as $log)
                <li class="list-group-item">
                    <strong>{{ $log->created_at->format('d M Y H:i') }}</strong> –
                    {{ $log->description }} oleh {{ $log->causer?->name ?? 'Sistem' }}

                    @if($log->properties['attributes'] ?? false)
                        <ul class="mt-2">
                            @foreach($log->properties['attributes'] as $key => $val)
                                <li>{{ $key }}:
                                    <span class="text-danger">{{ $log->properties['old'][$key] ?? '–' }}</span> →
                                    <span class="text-success">{{ $val }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @empty
                <li class="list-group-item">Belum ada perubahan tercatat.</li>
            @endforelse
        </ul>
    </div>
</div>
@stop
@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@stop