<div>
    <div class="row mt-3">
        @include('components.alert')
        <div class="col-md-12">

            {{-- ══════════════════════════════════════════════ --}}
            {{-- HEADER + TOGGLE BUTTON                        --}}
            {{-- ══════════════════════════════════════════════ --}}
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-gradient-primary py-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <h3 class="card-title text-white mb-0">
                            <i class="fas fa-boxes mr-2"></i> Manajemen Stok Produk
                        </h3>
                        <button wire:click="toggleForm"
                                class="btn btn-sm {{ $showForm ? 'btn-light' : 'btn-info' }}">
                            <i class="fas {{ $showForm ? 'fa-chevron-up' : 'fa-plus-circle' }} mr-1"></i>
                            {{ $showForm ? 'Tutup Form Input' : 'Input Stok' }}
                        </button>
                    </div>
                </div>

                {{-- ── FORM INPUT STOK (hide/show) ─────────── --}}
                @if($showForm)
                <div class="card-body border-bottom bg-light">

                    {{-- Search Produk --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="font-weight-bold mb-1">
                                    <i class="fas fa-barcode mr-1"></i> Scan / Ketik Barcode atau Nama
                                </label>
                                <div class="input-group">
                                    <input
                                        type="text"
                                        wire:model.debounce.400ms="searchQuery"
                                        class="form-control"
                                        placeholder="Barcode, kode, atau nama produk..."
                                        autofocus
                                        autocomplete="off"
                                    />
                                    @if($searchQuery)
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button"
                                            wire:click="clearSearch">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Loading --}}
                            <div wire:loading wire:target="searchQuery" class="text-muted small mb-1">
                                <i class="fas fa-spinner fa-spin"></i> Mencari...
                            </div>

                            {{-- Tidak ditemukan --}}
                            @if($searchQuery && strlen($searchQuery) >= 2 && !$foundProduct && empty($multipleProducts))
                            <div wire:loading.remove wire:target="searchQuery">
                                <div class="alert alert-warning py-2 mb-0">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Produk "<strong>{{ $searchQuery }}</strong>" tidak ditemukan.
                                </div>
                            </div>
                            @endif

                            {{-- Multiple products found --}}
                            @if(!empty($multipleProducts))
                            <div wire:loading.remove wire:target="searchQuery">
                                <div class="alert alert-info py-2 mb-0">
                                    <i class="fas fa-layer-group mr-1"></i>
                                    Ditemukan <strong>{{ count($multipleProducts) }}</strong> produk — pilih di bawah.
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Info produk ditemukan --}}
                        @if($foundProduct)
                        <div class="col-md-6" wire:loading.remove wire:target="searchQuery">
                            <div class="card border-primary mb-0 h-100">
                                <div class="card-body py-2 px-3 d-flex align-items-center">
                                    @if($foundProduct->primaryMedia)
                                    <img src="{{ $foundProduct->primaryMedia->file_url }}"
                                         class="rounded mr-3"
                                         style="width:48px;height:48px;object-fit:cover;" alt="">
                                    @else
                                    <div class="rounded bg-light d-flex align-items-center justify-content-center mr-3"
                                         style="width:48px;height:48px;font-size:1.3rem;color:#aaa;">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    @endif
                                    <div class="flex-grow-1 min-width-0">
                                        <div class="font-weight-bold text-truncate">{{ $foundProduct->name }}</div>
                                        <small class="text-muted">
                                            <i class="fas fa-barcode mr-1"></i>{{ $foundProduct->barcode }}
                                        </small>
                                    </div>
                                    <div class="text-right ml-3">
                                        <div class="h4 font-weight-bold text-primary mb-0">
                                            {{ $inventory ? $inventory->quantity : 0 }}
                                        </div>
                                        <small class="text-muted">{{ $inventory ? $inventory->unit : 'pcs' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Pilih produk (multiple results) --}}
                    @if(!empty($multipleProducts))
                    <div wire:loading.remove wire:target="searchQuery" class="mt-3">
                        <div class="list-group">
                            @foreach($multipleProducts as $p)
                            <button type="button"
                                wire:click="selectProduct('{{ $p['id'] }}')"
                                class="list-group-item list-group-item-action d-flex align-items-center py-2 px-3">
                                {{-- Gambar --}}
                                @if($p['image'])
                                <img src="{{ $p['image'] }}" class="rounded mr-3 flex-shrink-0"
                                     style="width:40px;height:40px;object-fit:cover;" alt="">
                                @else
                                <div class="rounded bg-light d-flex align-items-center justify-content-center mr-3 flex-shrink-0"
                                     style="width:40px;height:40px;font-size:1.1rem;color:#bbb;">
                                    <i class="fas fa-box"></i>
                                </div>
                                @endif
                                {{-- Nama & barcode --}}
                                <div class="flex-grow-1 min-width-0">
                                    <div class="font-weight-bold text-truncate" style="font-size:0.9rem;">
                                        {{ $p['name'] }}
                                    </div>
                                    <small class="text-muted">
                                        <i class="fas fa-barcode mr-1"></i>{{ $p['barcode'] ?? '-' }}
                                        @if($p['code'])
                                            &nbsp;·&nbsp;<i class="fas fa-hashtag mr-1"></i>{{ $p['code'] }}
                                        @endif
                                        &nbsp;·&nbsp;{{ $p['category'] }}
                                    </small>
                                </div>
                                {{-- Stok --}}
                                <div class="text-right ml-3 flex-shrink-0">
                                    <span class="font-weight-bold text-primary" style="font-size:1rem;">
                                        {{ $p['stock'] }}
                                    </span>
                                    <small class="text-muted d-block">{{ $p['unit'] }}</small>
                                </div>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Form aksi (muncul hanya jika ada produk) --}}
                    @if($foundProduct)
                    <div wire:loading.remove wire:target="searchQuery">
                        <hr class="my-3">
                        <div class="row align-items-end">

                            {{-- Pilih Aksi --}}
                            <div class="col-md-3">
                                <label class="font-weight-bold mb-1">Tipe</label>
                                <div class="btn-group w-100" role="group">
                                    <button type="button"
                                        wire:click="selectAction('in')"
                                        class="btn btn-sm {{ $actionType === 'in' ? 'btn-success' : 'btn-outline-success' }}">
                                        <i class="fas fa-arrow-up"></i> Masuk
                                    </button>
                                    <button type="button"
                                        wire:click="selectAction('out')"
                                        class="btn btn-sm {{ $actionType === 'out' ? 'btn-danger' : 'btn-outline-danger' }}">
                                        <i class="fas fa-arrow-down"></i> Keluar
                                    </button>
                                    <button type="button"
                                        wire:click="selectAction('adjustment')"
                                        class="btn btn-sm {{ $actionType === 'adjustment' ? 'btn-warning' : 'btn-outline-warning' }}">
                                        <i class="fas fa-sliders-h"></i> Set
                                    </button>
                                </div>
                                @if($actionType === 'adjustment')
                                <small class="text-warning d-block mt-1">
                                    <i class="fas fa-info-circle"></i> Set stok ke angka ini
                                </small>
                                @endif
                            </div>

                            {{-- Qty --}}
                            <div class="col-md-2">
                                <label class="font-weight-bold mb-1">
                                    @if($actionType === 'in') Jumlah Masuk
                                    @elseif($actionType === 'out') Jumlah Keluar
                                    @else Stok Baru
                                    @endif
                                </label>
                                <input
                                    type="number"
                                    wire:model="qty"
                                    class="form-control text-center font-weight-bold @error('qty') is-invalid @enderror"
                                    placeholder="0"
                                    min="1"
                                    wire:keydown.enter="saveStock"
                                />
                                @error('qty')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Catatan --}}
                            <div class="col-md-5">
                                <label class="font-weight-bold mb-1">
                                    Catatan <small class="text-muted font-weight-normal">(opsional)</small>
                                </label>
                                <input
                                    type="text"
                                    wire:model="notes"
                                    class="form-control"
                                    placeholder="Contoh: restock, rusak, opname..."
                                    wire:keydown.enter="saveStock"
                                />
                            </div>

                            {{-- Simpan --}}
                            <div class="col-md-2">
                                <button
                                    wire:click="saveStock"
                                    wire:loading.attr="disabled"
                                    wire:target="saveStock"
                                    class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-1"
                                       wire:loading.remove wire:target="saveStock"></i>
                                    <i class="fas fa-spinner fa-spin mr-1"
                                       wire:loading wire:target="saveStock"></i>
                                    <span wire:loading.remove wire:target="saveStock">Simpan</span>
                                    <span wire:loading wire:target="saveStock">...</span>
                                </button>
                            </div>

                        </div>
                    </div>
                    @endif

                </div>
                @endif
            </div>

            {{-- ══════════════════════════════════════════════ --}}
            {{-- RIWAYAT MOVEMENT                              --}}
            {{-- ══════════════════════════════════════════════ --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white py-2">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <h5 class="mb-0">
                                <i class="fas fa-history mr-1 text-secondary"></i> Riwayat Pergerakan Stok
                            </h5>
                        </div>
                        {{-- Search --}}
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input
                                    type="text"
                                    wire:model.debounce.400ms="searchMovement"
                                    class="form-control"
                                    placeholder="Cari nama / barcode produk..."
                                />
                                @if($searchMovement)
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" wire:click="$set('searchMovement','')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>
                        {{-- Filter tipe --}}
                        <div class="col-md-4 text-right">
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button"
                                    wire:click="$set('filterType', '')"
                                    class="btn {{ $filterType === '' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                                    Semua
                                </button>
                                <button type="button"
                                    wire:click="$set('filterType', 'in')"
                                    class="btn {{ $filterType === 'in' ? 'btn-success' : 'btn-outline-success' }}">
                                    <i class="fas fa-arrow-up"></i> Masuk
                                </button>
                                <button type="button"
                                    wire:click="$set('filterType', 'out')"
                                    class="btn {{ $filterType === 'out' ? 'btn-danger' : 'btn-outline-danger' }}">
                                    <i class="fas fa-arrow-down"></i> Keluar
                                </button>
                                <button type="button"
                                    wire:click="$set('filterType', 'adjustment')"
                                    class="btn {{ $filterType === 'adjustment' ? 'btn-warning' : 'btn-outline-warning' }}">
                                    <i class="fas fa-sliders-h"></i> Set
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center" style="width:80px;">Tipe</th>
                                    <th class="text-center" style="width:60px;">Qty</th>
                                    <th class="text-center" style="width:120px;">Sebelum → Sesudah</th>
                                    <th>Catatan</th>
                                    <th style="width:100px;">Oleh</th>
                                    <th style="width:100px;">Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movements as $movement)
                                <tr>
                                    <td>
                                        <div class="font-weight-bold" style="font-size:0.85rem;">
                                            {{ $movement->inventory->productStore->name ?? '-' }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $movement->inventory->productStore->barcode ?? '' }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        @if($movement->type === 'in')
                                            <span class="badge badge-success">
                                                <i class="fas fa-arrow-up mr-1"></i>Masuk
                                            </span>
                                        @elseif($movement->type === 'out')
                                            <span class="badge badge-danger">
                                                <i class="fas fa-arrow-down mr-1"></i>Keluar
                                            </span>
                                        @else
                                            <span class="badge badge-warning">
                                                <i class="fas fa-sliders-h mr-1"></i>Set
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold">
                                        {{ $movement->quantity }}
                                    </td>
                                    <td class="text-center" style="font-size:0.8rem;">
                                        <span class="text-muted">{{ $movement->quantity_before }}</span>
                                        <i class="fas fa-arrow-right text-secondary mx-1" style="font-size:0.65rem;"></i>
                                        <span class="font-weight-bold">{{ $movement->quantity_after }}</span>
                                    </td>
                                    <td style="font-size:0.82rem;">
                                        {{ $movement->notes ?? '-' }}
                                        @if($movement->source === 'sync')
                                            <span class="badge badge-info ml-1">sync</span>
                                        @endif
                                    </td>
                                    <td style="font-size:0.8rem;">
                                        {{ $movement->creator->name ?? '-' }}
                                    </td>
                                    <td style="font-size:0.78rem;" class="text-muted">
                                        {{ $movement->created_at->format('d/m H:i') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        @if($searchMovement)
                                            Tidak ada riwayat untuk "<strong>{{ $searchMovement }}</strong>"
                                        @else
                                            Belum ada riwayat pergerakan stok.
                                        @endif
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($movements->hasPages())
                <div class="card-footer py-2">
                    {{ $movements->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</div>


@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    window.addEventListener('stock-saved', event => {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: event.detail.message,
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
        });
        setTimeout(() => {
            document.querySelector('input[wire\\:model\\.debounce\\.400ms="searchQuery"]')?.focus();
        }, 200);
    });
</script>
@endpush
