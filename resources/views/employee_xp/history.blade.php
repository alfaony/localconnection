@extends('adminlte::page')

@section('title', 'Riwayat XP Saya')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0 text-dark">⚡ Riwayat XP {{ isset($user) ? ' — ' . $user->name : 'Saya' }}</h1>
            <small class="text-muted">Seluruh riwayat poin pengalaman Anda</small>
        </div>
        <a href="{{ route('employee-xp.leaderboard') }}" class="btn btn-outline-warning btn-sm">
            <i class="fas fa-trophy mr-1"></i> Leaderboard
        </a>
    </div>
@stop

@section('content')

{{-- Total XP Card --}}
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border:none;">
            <div class="card-body d-flex align-items-center py-4">
                <div class="mr-4">
                    <div style="font-size: 2.5rem;">⚡</div>
                </div>
                <div>
                    <div class="text-uppercase text-white-50 mb-1" style="font-size:.75rem; letter-spacing:1px;">Total XP</div>
                    <div class="font-weight-bold" style="font-size: 2rem; line-height:1;">
                        {{ number_format($totalXp) }}
                    </div>
                    <div class="text-white-75 mt-1" style="font-size:.8rem;">Experience Points</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm" style="border:none;">
            <div class="card-body d-flex align-items-center py-4">
                <div class="mr-4 text-success" style="font-size: 2rem;">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase mb-1" style="font-size:.75rem; letter-spacing:1px;">XP Diterima</div>
                    <div class="font-weight-bold text-success" style="font-size: 1.8rem; line-height:1;">
                        +{{ number_format($histories->where('xp', '>', 0)->sum('xp')) }}
                    </div>
                    <div class="text-muted" style="font-size:.8rem;">{{ $histories->where('xp', '>', 0)->count() }} transaksi</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm" style="border:none;">
            <div class="card-body d-flex align-items-center py-4">
                <div class="mr-4 text-danger" style="font-size: 2rem;">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div>
                    <div class="text-muted text-uppercase mb-1" style="font-size:.75rem; letter-spacing:1px;">XP Dikurangi</div>
                    <div class="font-weight-bold text-danger" style="font-size: 1.8rem; line-height:1;">
                        {{ number_format($histories->where('xp', '<', 0)->sum('xp')) }}
                    </div>
                    <div class="text-muted" style="font-size:.8rem;">{{ $histories->where('xp', '<', 0)->count() }} penalti</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Riwayat List --}}
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0 font-weight-bold">Riwayat Transaksi XP</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="pl-4" style="width: 8%">XP</th>
                        <th style="width: 25%">Sumber</th>
                        <th style="width: 40%">Keterangan</th>
                        <th style="width: 20%">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($histories as $h)
                    <tr>
                        <td class="pl-4">
                            <span class="badge badge-{{ $h->xp > 0 ? 'success' : 'danger' }} badge-pill px-3 py-1"
                                  style="font-size: .85rem;">
                                {{ $h->xp > 0 ? '+' : '' }}{{ $h->xp }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="badge badge-light text-dark mr-2 px-2 py-1" style="font-size:.75rem; font-family: monospace;">
                                    {{ $h->source_type }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="text-dark">{{ $h->description ?? '-' }}</span>
                        </td>
                        <td>
                            <div class="text-dark">{{ $h->created_at->format('d M Y') }}</div>
                            <small class="text-muted">{{ $h->created_at->format('H:i') }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="fas fa-bolt fa-2x mb-2 d-block"></i>
                            Belum ada riwayat XP.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($histories->hasPages())
        <div class="card-footer bg-light py-2">
            <div class="d-flex justify-content-center">
                {{ $histories->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>
</div>
@stop
