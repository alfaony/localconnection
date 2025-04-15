<div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
    @forelse($youtubeMedias as $media)
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="position-relative">
                    @canAccess('destroy','office_medias')
                    <button class="btn btn-sm btn-danger position-absolute delete-media-btn" style="top: 5px; right: 5px; z-index: 10;"
                            data-id="{{ $media->id }}">
                        <i class="bi bi-trash"></i>
                    </button>
                    @endcanAccess
                </div>
                <div class="embed-responsive embed-responsive-16by9 mb-3 p-2">
                    <iframe class="embed-responsive-item" src="{{ $media->youtube_url }}" frameborder="0" allowfullscreen></iframe>
                </div>
                <p class="card-text text-center text-truncate">{{ $media->title }}</p>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-5">
                <i class="bi bi-image-fill text-muted" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2">Tidak ada data</p>
                @canAccess('store','office_medias')
                <button class="btn btn-outline-secondary btn-sm mt-2" data-toggle="modal" data-target="#youtubeEmbedModal">Embed URL Sekarang</button>
                @endcanAccess
            </div>
        </div>
    </div>
    @endforelse
</div>

