@extends('adminlte::page')

@section('title', 'Detail Pengajuan Cuti')

@section('content_header')
    <h1 class="m-0 text-dark">📝 Detail Pengajuan Cuti</h1>
@stop

@section('content')
    <div class="card border-0 shadow">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Nama Pegawai</dt>
                <dd class="col-sm-9">{{ $dayoff->user->name }}</dd>

                <dt class="col-sm-3">Jenis Cuti</dt>
                <dd class="col-sm-9">{{ $dayoff->type->name }}</dd>

                <dt class="col-sm-3">Tanggal Cuti</dt>
                <dd class="col-sm-9">
                    {{ \Carbon\Carbon::parse($dayoff->date_start)->format('d M Y') }}
                    sampai
                    {{ \Carbon\Carbon::parse($dayoff->date_end)->format('d M Y') }}
                    ({{ $dayoff->durationInDays() }} hari)
                </dd>

                <dt class="col-sm-3">Alasan</dt>
                <dd class="col-sm-9">{{ $dayoff->reason ?? '-' }}</dd>

                @if($dayoff->file)
                    <dt class="col-sm-3">Lampiran</dt>
                    <dd class="col-sm-9">
                        <a href="{{ s3_asset(true,10,$dayoff->file) }}" target="_blank">
                            Lihat File
                        </a>
                    </dd>
                @endif

                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">{!! $dayoff->statusBadge !!}</dd>

                <dt class="col-sm-3">Approval HR</dt>
                <dd class="col-sm-9">
                    @if($dayoff->approvalHR)
                        {{ $dayoff->approvalHR->name }} <br>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($dayoff->approved_hr_at)->format('d M Y H:i') }}</small>
                    @else
                        <span class="text-muted">Belum disetujui</span>
                    @endif
                </dd>

                <dt class="col-sm-3">Approval Finance</dt>
                <dd class="col-sm-9">
                    @if($dayoff->approvalFinance)
                        {{ $dayoff->approvalFinance->name }} <br>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($dayoff->approved_finance_at)->format('d M Y H:i') }}</small>
                    @else
                        <span class="text-muted">Belum disetujui</span>
                    @endif
                </dd>

                @if($dayoff->rejected_at)
                    <dt class="col-sm-3">Ditolak Pada</dt>
                    <dd class="col-sm-9">
                        {{ \Carbon\Carbon::parse($dayoff->rejected_at)->format('d M Y H:i') }}
                    </dd>
                    <dt class="col-sm-3">Alasan Ditolak</dt>
                    <dd class="col-sm-9">{{ $dayoff->rejected_reason ?? '-' }}</dd>
                @endif
            </dl>
        </div>

        <div class="card-footer bg-light d-flex justify-content-end">
            <a href="{{ route('dayoff.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
@stop