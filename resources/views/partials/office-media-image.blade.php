<div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
    @forelse($imageMedias as $media)
        <div class="col">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="position-relative">
                        @canAccess('destroy','office_media')
                            <button class="btn btn-sm btn-danger position-absolute delete-media-btn"
                                    style="top: 5px; right: 5px; z-index: 10;"
                                    data-id="{{ $media->id }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        @endcanAccess
                    </div>
                    <img src="{{ Storage::url($media->file_path) }}"
                        class="img-fluid rounded w-100 office-media-thumb"
                        style="height: 200px; object-fit: cover; cursor: pointer;"
                        data-index="{{ $loop->index }}"
                        data-url="{{ Storage::url($media->file_path) }}"
                        alt="{{ $media->title }}">
                    <small class="text-center d-block text-truncate mt-2">{{ $media->title }}</small>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-image-fill text-muted" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">Tidak ada data</p>
                    @canAccess('store','office_media')
                        <button class="btn btn-outline-secondary btn-sm mt-2"
                                data-toggle="modal" data-target="#uploadMomentModal">
                            Upload Sekarang
                        </button>
                    @endcanAccess
                </div>
            </div>
        </div>
    @endforelse
</div>