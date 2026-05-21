@extends('adminlte::page')

@section('title', $reportLink->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">
            <i class="fas fa-link mr-2"></i>{{ $reportLink->name }}
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 bg-transparent p-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('report-link.index') }}">Report Link</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<style>
/* ── Swiper wrapper ── */
.swiper {
    width: 100%;
    border-radius: 10px;
    overflow: hidden;
    background: #111;
}
.swiper-slide {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-end;
    position: relative;
}
.swiper-slide img {
    width: 100%;
    max-height: 480px;
    object-fit: contain;
    display: block;
    background: #111;
}
.slide-caption {
    width: 100%;
    background: linear-gradient(transparent, rgba(0,0,0,.75));
    color: #fff;
    padding: 14px 16px 12px;
    font-size: .88rem;
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
}
.slide-caption.empty { display: none; }

/* Pagination dots */
.swiper-pagination-bullet { background: #fff; opacity: .6; }
.swiper-pagination-bullet-active { opacity: 1; }

/* Nav buttons */
.swiper-button-next, .swiper-button-prev { color: #fff; }

/* ── Info card ── */
.info-card dt { font-weight: 600; color: #495057; }
.info-card dd { color: #212529; word-break: break-all; }

/* counter badge */
.slide-counter {
    position: absolute;
    top: 12px;
    right: 14px;
    background: rgba(0,0,0,.55);
    color: #fff;
    font-size: .75rem;
    padding: 3px 10px;
    border-radius: 20px;
    z-index: 10;
}

/* Thumbnail strip */
.thumb-strip {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 8px 0;
}
.thumb-strip img {
    width: 72px;
    height: 56px;
    object-fit: cover;
    border-radius: 5px;
    cursor: pointer;
    border: 2px solid transparent;
    flex-shrink: 0;
    transition: border-color .2s;
}
.thumb-strip img.active { border-color: #007bff; }
</style>
@stop

@section('content')
@include('components.alert')

<div class="row">
    {{-- ── Kiri: Carousel ── --}}
    <div class="col-lg-8 mb-4">
        @if($reportLink->images->count())
        <div class="card shadow-sm">
            <div class="card-body p-2">

                {{-- Counter --}}
                <div class="position-relative">
                    <div class="swiper mySwiper">
                        <div class="swiper-wrapper">
                            @foreach($reportLink->images as $i => $img)
                            <div class="swiper-slide">
                                <img src="{{ asset('storage/' . $img->path) }}"
                                     alt="{{ $img->description ?? $reportLink->name }}">
                                @if($img->description)
                                <div class="slide-caption">
                                    <i class="fas fa-comment-alt mr-1"></i>{{ $img->description }}
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        <div class="swiper-pagination"></div>
                        @if($reportLink->images->count() > 1)
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                        @endif
                    </div>

                    @if($reportLink->images->count() > 1)
                    <div class="slide-counter" id="slideCounter">
                        1 / {{ $reportLink->images->count() }}
                    </div>
                    @endif
                </div>

                {{-- Thumbnail strip --}}
                @if($reportLink->images->count() > 1)
                <div class="thumb-strip px-2 mt-2" id="thumbStrip">
                    @foreach($reportLink->images as $i => $img)
                    <img src="{{ asset('storage/' . $img->path) }}"
                         class="{{ $i === 0 ? 'active' : '' }}"
                         data-index="{{ $i }}"
                         alt=""
                         onclick="goToSlide({{ $i }})">
                    @endforeach
                </div>
                @endif

            </div>
        </div>
        @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="fas fa-image fa-3x mb-3"></i>
                <p>Belum ada gambar pada laporan ini.</p>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Kanan: Info ── --}}
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h6 class="m-0"><i class="fas fa-info-circle mr-2"></i>Informasi Laporan</h6>
            </div>
            <div class="card-body info-card">
                <dl class="row mb-0">
                    <dt class="col-5">Nama</dt>
                    <dd class="col-7">{{ $reportLink->name }}</dd>

                    <dt class="col-5">Tanggal</dt>
                    <dd class="col-7">{{ $reportLink->date->format('d M Y') }}</dd>

                    <dt class="col-5">Dibuat oleh</dt>
                    <dd class="col-7">{{ $reportLink->user->name ?? '-' }}</dd>

                    <dt class="col-5">Gambar</dt>
                    <dd class="col-7">{{ $reportLink->images->count() }} foto</dd>

                    <dt class="col-12 mb-1">Link</dt>
                    <dd class="col-12">
                        <a href="{{ $reportLink->link }}" target="_blank"
                           class="btn btn-outline-primary btn-sm btn-block">
                            <i class="fas fa-external-link-alt mr-1"></i>
                            Buka Link
                        </a>
                        <small class="text-muted d-block mt-1" style="font-size:.72rem;word-break:break-all;">
                            {{ $reportLink->link }}
                        </small>
                    </dd>

                    @if($reportLink->description)
                    <dt class="col-12 mt-2 mb-1">Deskripsi</dt>
                    <dd class="col-12">
                        <div class="description-body p-2 rounded" style="background:#f8f9fa;font-size:.88rem;">
                            {!! $reportLink->description !!}
                        </div>
                    </dd>
                    @endif
                </dl>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('report-link.index') }}" class="btn btn-secondary btn-sm text-white">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali
                </a>
                <div class="ml-auto">
                    @canAccess('update', 'report_links')
                    <a href="{{ route('report-link.edit', $reportLink->id) }}"
                       class="btn btn-outline-warning btn-sm">
                        <i class="fas fa-edit mr-1"></i>Edit
                    </a>
                    @endcanAccess
                    @canAccess('destroy', 'report_links')
                    <form action="{{ route('report-link.destroy', $reportLink->id) }}" method="POST"
                          class="d-inline" onsubmit="return confirm('Hapus laporan ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                    @endcanAccess
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
const swiper = new Swiper('.mySwiper', {
    loop: {{ $reportLink->images->count() > 1 ? 'true' : 'false' }},
    pagination: {
        el: '.swiper-pagination',
        clickable: true,
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
    keyboard: { enabled: true },
    on: {
        slideChange: function () {
            const real = this.realIndex;
            const total = {{ $reportLink->images->count() }};
            const counter = document.getElementById('slideCounter');
            if (counter) counter.textContent = (real + 1) + ' / ' + total;

            // Update thumbnail active state
            document.querySelectorAll('#thumbStrip img').forEach((img, i) => {
                img.classList.toggle('active', i === real);
            });
        }
    }
});

function goToSlide(index) {
    swiper.slideToLoop(index);
}
</script>
@stop
