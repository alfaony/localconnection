@extends('adminlte::page')

@section('title', 'Master Badge')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 fw-bold" style="color:#e0e0ff;">🏅 Master Badge</h1>
        <small style="color:#a0a8d0;">Kelola koleksi gelar & lencana karyawan</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('badge.assign') }}" class="btn btn-sm btn-outline-warning mb-1 mr-1">
            <i class="fas fa-paper-plane mr-1"></i> Kirim Badge
        </a>
        @canAccess('create','badges')
        <a href="{{ route('badge.create') }}" class="btn btn-sm btn-primary mb-1">
            <i class="fas fa-plus-circle mr-1"></i> Badge Baru
        </a>
        @endcanAccess
    </div>
</div>
@stop

@section('content')
@include('components.alert')

<div class="row g-3">
    @forelse($badges as $badge)
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:16px;overflow:hidden;">
            <div style="height:3px;background:linear-gradient(90deg,#667eea,#f093fb,#f5a623);"></div>
            <div class="card-body text-center p-4">
                <div class="mb-3 d-flex justify-content-center">
                    @if($badge->image)
                    <div style="width:80px;height:80px;background:rgba(102,126,234,.15);border-radius:50%;padding:6px;border:2px solid rgba(102,126,234,.4);display:flex;align-items:center;justify-content:center;">
                        <img src="{{ s3_asset(true, null, $badge->image) }}" alt="{{ $badge->name }}"
                             style="width:60px;height:60px;object-fit:contain;">
                    </div>
                    @else
                    <div style="width:80px;height:80px;background:rgba(102,126,234,.15);border-radius:50%;border:2px solid rgba(102,126,234,.4);display:flex;align-items:center;justify-content:center;font-size:2rem;">
                        🏅
                    </div>
                    @endif
                </div>
                <h6 class="fw-bold mb-1" style="color:#e0e0ff;">{{ $badge->name }}</h6>
                @if($badge->description)
                <small style="color:#a0a8d0;">{{ $badge->description }}</small>
                @endif
                <div class="mt-3 d-flex justify-content-center gap-1" style="gap:.4rem;">
                    <span class="badge rounded-pill" style="background:rgba(240,147,251,.15);color:#f093fb;border:1px solid rgba(240,147,251,.3);font-size:.7rem;">
                        <i class="fas fa-users me-1"></i>{{ $badge->user_badges_count }} dikirim
                    </span>
                </div>
                <div class="mt-3 d-flex justify-content-center" style="gap:.5rem;">
                    @canAccess('edit','badges')
                    <a href="{{ route('badge.edit', $badge) }}" class="btn btn-sm btn-outline-info" style="border-radius:8px;font-size:.75rem;">
                        <i class="fas fa-edit"></i>
                    </a>
                    @endcanAccess
                    @canAccess('destroy','badges')
                    <form action="{{ route('badge.destroy', $badge) }}" method="POST" onsubmit="return confirm('Hapus badge ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius:8px;font-size:.75rem;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                    @endcanAccess
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="text-center py-5" style="color:#a0a8d0;">
            <div style="font-size:3rem;">🏅</div>
            <p class="mt-2">Belum ada badge. Buat badge pertama!</p>
            <a href="{{ route('badge.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">
                <i class="fas fa-plus-circle me-1"></i> Buat Badge
            </a>
        </div>
    </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $badges->links() }}
</div>
@stop
