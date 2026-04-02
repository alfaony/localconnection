@extends('adminlte::page')

@section('title', 'Master Badge')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 fw-bold" >🏅 Master Badge</h1>
        <small style="color:#55596e;">Kelola koleksi gelar & lencana karyawan</small>
    </div>
    <div class="d-flex gap-2">
        @canAccess('assignIndex','badges')
        @canAccess('assignStore','badges')
        <a href="{{ route('badge.assign') }}" class="btn btn-sm btn-outline-warning mb-1 mr-1">
            <i class="fas fa-paper-plane mr-1"></i> Kirim Badge
        </a>
        @endcanAccess
        @endcanAccess
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

<div class="row g-4">
    @forelse($badges as $badge)
    <div class="col-xl-3 col-md-4 col-sm-6 col-12">
        <div class="badge-master-card card border-0 shadow-sm h-100" style="background:linear-gradient(145deg,#1a1a2e,#16213e);border-radius:20px;overflow:hidden;">
            <div style="height:4px;background:linear-gradient(90deg,#667eea,#f093fb,#f5a623);"></div>

            {{-- Icon Area --}}
            <div class="d-flex justify-content-center pt-4 pb-2">
                <div class="badge-img-wrap">
                    @if($badge->image)
                    <img src="{{ s3_asset(true, null, $badge->image) }}"
                         alt="{{ $badge->name }}"
                         style="width:110px;height:110px;object-fit:contain;filter:drop-shadow(0 6px 18px rgba(240,147,251,.45));">
                    @else
                    <div class="d-flex align-items-center justify-content-center"
                         style="width:110px;height:110px;font-size:3.8rem;">🏅</div>
                    @endif
                </div>
            </div>

            <div class="card-body text-center px-4 pb-4 pt-2">
                <h5 class="fw-bold mb-1" style="color:#e0e0ff;">{{ $badge->name }}</h5>

                @if($badge->description)
                <p class="mb-3" style="color:#a0a8d0;font-size:.8rem;line-height:1.4;">{{ $badge->description }}</p>
                @else
                <div class="mb-3"></div>
                @endif

                <div class="d-flex justify-content-center mb-3">
                    <span class="px-3 py-1 rounded-pill" style="background:rgba(240,147,251,.12);color:#f093fb;border:1px solid rgba(240,147,251,.3);font-size:.75rem;font-weight:600;">
                        <i class="fas fa-users me-1"></i>{{ $badge->user_badges_count }} penerima
                    </span>
                </div>

                <div class="d-flex justify-content-center" style="gap:.6rem;">
                    @canAccess('edit','badges')
                    <a href="{{ route('badge.edit', $badge) }}"
                       class="btn btn-sm btn-outline-info"
                       style="border-radius:10px;padding:.35rem .75rem;">
                        <i class="fas fa-edit me-1"></i> Edit
                    </a>
                    @endcanAccess
                    @canAccess('destroy','badges')
                    <form action="{{ route('badge.destroy', $badge) }}" method="POST"
                          onsubmit="return confirm('Hapus badge \'{{ $badge->name }}\'?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                style="border-radius:10px;padding:.35rem .75rem;">
                            <i class="fas fa-trash me-1"></i> Hapus
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
            <div style="font-size:4rem;line-height:1;margin-bottom:.75rem;">🏅</div>
            <p class="mb-3">Belum ada badge. Buat badge pertama!</p>
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

@section('css')
<style>
.badge-master-card {
    transition: transform .25s ease, box-shadow .25s ease;
}
.badge-master-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 40px rgba(102,126,234,.25) !important;
}
.badge-img-wrap {
    transition: transform .3s ease;
}
.badge-master-card:hover .badge-img-wrap {
    transform: scale(1.08);
}
</style>
@stop
