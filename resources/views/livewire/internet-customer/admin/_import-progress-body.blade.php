{{--
    Partial: _import-progress-body.blade.php
    Variables: $importProgress (array|null), $isImporting (bool)
--}}
@if(!$importProgress)
    {{-- Job baru di-dispatch, record belum diupdate oleh worker --}}
    <div class="alert alert-secondary d-flex align-items-center">
        <div class="spinner-border spinner-border-sm mr-2"></div>
        <span>Import sedang antri di queue, mohon tunggu…</span>
    </div>
@else
@php
    $pct    = $importProgress['percentage'] ?? 0;
    $proc   = $importProgress['processed']  ?? 0;
    $total  = $importProgress['total']      ?? 0;
    $succ   = $importProgress['success']    ?? 0;
    $fail   = $importProgress['failed']     ?? 0;
    $status = $importProgress['status']     ?? 'processing';
    $errList= $importProgress['errors']     ?? [];

    $colorMap = [
        'queued'     => 'secondary',
        'processing' => 'info',
        'completed'  => 'success',
        'failed'     => 'danger',
    ];
    $color = $colorMap[$status] ?? 'info';
@endphp

{{-- Progress bar --}}
<div class="progress mb-1" style="height: 30px; border-radius: 6px; background:#e9ecef;">
    <div class="progress-bar progress-bar-striped {{ $isImporting ? 'progress-bar-animated' : '' }} bg-{{ $color }}"
         role="progressbar"
         style="width: {{ max($pct, 2) }}%; transition: width .5s ease;"
         aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
        <strong>{{ $pct }}%</strong>
    </div>
</div>
<div class="d-flex justify-content-between mb-3">
    <small class="text-muted">{{ $proc }} / {{ $total }} baris diproses</small>
    @if(isset($importProgress['updated_at']))
    <small class="text-muted">
        <i class="fas fa-clock mr-1"></i>
        {{ \Carbon\Carbon::parse($importProgress['updated_at'])->format('H:i:s') }}
    </small>
    @endif
</div>

{{-- Stats --}}
<div class="row text-center mb-3">
    {{-- Status --}}
    <div class="col-6 col-md-3 mb-2">
        <div class="card h-100">
            <div class="card-body py-2">
                <span class="badge badge-{{ $color }} px-2 py-1">
                    @if($status === 'queued')
                        <i class="fas fa-clock mr-1"></i>Antri
                    @elseif($status === 'processing')
                        <i class="fas fa-spinner fa-spin mr-1"></i>Diproses
                    @elseif($status === 'completed')
                        <i class="fas fa-check mr-1"></i>Selesai
                    @elseif($status === 'failed')
                        <i class="fas fa-times mr-1"></i>Gagal
                    @else
                        {{ strtoupper($status) }}
                    @endif
                </span>
                <div class="text-muted small mt-1">Status</div>
            </div>
        </div>
    </div>
    {{-- Total --}}
    <div class="col-6 col-md-3 mb-2">
        <div class="card h-100">
            <div class="card-body py-2">
                <h4 class="mb-0">{{ $total }}</h4>
                <div class="text-muted small">Total Baris</div>
            </div>
        </div>
    </div>
    {{-- Berhasil --}}
    <div class="col-6 col-md-3 mb-2">
        <div class="card border-success h-100">
            <div class="card-body py-2">
                <h4 class="mb-0 text-success">{{ $succ }}</h4>
                <div class="text-muted small">
                    <i class="fas fa-check-circle text-success mr-1"></i>Berhasil
                </div>
                @if($total > 0)
                    <small class="text-muted">({{ round($succ / $total * 100) }}%)</small>
                @endif
            </div>
        </div>
    </div>
    {{-- Gagal --}}
    <div class="col-6 col-md-3 mb-2">
        <div class="card border-danger h-100">
            <div class="card-body py-2">
                <h4 class="mb-0 text-danger">{{ $fail }}</h4>
                <div class="text-muted small">
                    <i class="fas fa-times-circle text-danger mr-1"></i>Gagal
                </div>
                @if($total > 0)
                    <small class="text-muted">({{ round($fail / $total * 100) }}%)</small>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Completed / Failed summary banner --}}
@if(!$isImporting)
    @if($status === 'completed' && $fail === 0)
    <div class="alert alert-success py-2 mb-3">
        <i class="fas fa-check-circle mr-1"></i>
        <strong>Semua {{ $succ }} baris berhasil diimport!</strong>
    </div>
    @elseif($status === 'completed' && $fail > 0)
    <div class="alert alert-warning py-2 mb-3">
        <i class="fas fa-exclamation-circle mr-1"></i>
        Import selesai: <strong class="text-success">{{ $succ }} berhasil</strong>,
        <strong class="text-danger">{{ $fail }} gagal</strong>.
        Lihat detail di bawah.
    </div>
    @elseif($status === 'failed')
    <div class="alert alert-danger py-2 mb-3">
        <i class="fas fa-times-circle mr-1"></i>
        <strong>Import gagal secara sistem.</strong> Periksa detail di bawah.
    </div>
    @endif
@endif

{{-- Error / Failed rows table --}}
@if(count($errList) > 0)
<div class="card border-danger mb-2">
    <div class="card-header bg-danger text-white py-2 d-flex justify-content-between align-items-center">
        <strong>
            <i class="fas fa-exclamation-triangle mr-1"></i>
            Detail Baris Gagal — {{ count($errList) }} baris
        </strong>
        @if($isImporting)
            <small class="text-white-50">(update otomatis setiap 1,5 detik)</small>
        @endif
    </div>
    <div class="card-body p-0">
        <div style="max-height: 280px; overflow-y: auto;">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light" style="position:sticky;top:0;z-index:1;">
                    <tr>
                        <th style="width:70px;">Baris</th>
                        <th>Penyebab Gagal</th>
                        <th style="width:200px;">Data (email/telepon)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($errList as $err)
                    @if(is_array($err))
                    <tr>
                        <td class="text-center">
                            <span class="badge badge-danger">{{ $err['row'] ?? '?' }}</span>
                        </td>
                        <td class="text-danger small">{{ $err['message'] ?? 'Unknown error' }}</td>
                        <td>
                            <small class="text-muted">{{ $err['data'] ?? '-' }}</small>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endif {{-- end @if(!$importProgress) --}}
