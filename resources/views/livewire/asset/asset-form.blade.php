@section('title', $assetId ? 'Edit Asset' : 'Tambah Asset')

@section('content_header')

<div class="d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-0 font-weight-bold text-dark">
            <i class="fas fa-{{ $assetId ? 'edit' : 'plus-circle' }} mr-2 text-primary"></i>
            {{ $assetId ? 'Edit Asset' : 'Tambah Asset Baru' }}
        </h4>
        <small class="text-muted">{{ $assetId ? 'Perbarui data asset' : 'Tambahkan perangkat/infrastruktur baru' }}</small>
    </div>
    <a href="{{ route('internet-asset.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Kembali
    </a>
</div>
@stop


<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-hdd text-primary"></i> Informasi Asset</h5>
            </div>
            <div class="card-body">

                {{-- ── Row 1: Nama & Kategori ── --}}
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label-sm">Nama Asset <span class="text-danger">*</span></label>
                        <input wire:model.lazy="name" type="text" class="form-control @error('name') is-invalid @enderror"
                               placeholder="Contoh: Router Mikrotik Lantai 1">
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label-sm">Kategori <span class="text-danger">*</span></label>
                        <select wire:model="category" class="form-control @error('category') is-invalid @enderror">
                            @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                {{-- ── Row 2: Brand, Model, Serial ── --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label-sm">Brand / Merek</label>
                        <input wire:model.lazy="brand" type="text" class="form-control"
                               placeholder="Mikrotik, Cisco, Huawei...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label-sm">Model</label>
                        <input wire:model.lazy="model" type="text" class="form-control"
                               placeholder="RB750Gr3, CRS326...">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label-sm">Serial Number</label>
                        <input wire:model.lazy="serial_number" type="text" class="form-control"
                               placeholder="SN/IMEI/No. Seri">
                    </div>
                </div>

                <hr style="border-color:#f1f5f9;margin:8px 0 16px">

                {{-- ── Row 3: Qty, Harga, Total ── --}}
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label class="form-label-sm">Jumlah <span class="text-danger">*</span></label>
                        <input wire:model.lazy="quantity" type="number" min="1"
                               class="form-control @error('quantity') is-invalid @enderror">
                        @error('quantity') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label-sm">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                        <input wire:model.lazy="unit_price" type="number" min="0" step="1000"
                               class="form-control @error('unit_price') is-invalid @enderror"
                               placeholder="0">
                        @error('unit_price') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label-sm">Total Harga</label>
                        <div class="form-control-plaintext font-weight-bold text-primary" style="font-size:1rem;padding-top:7px">
                            Rp {{ number_format((float)($unit_price ?: 0) * $quantity, 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label-sm">Garansi (bulan)</label>
                        <input wire:model.lazy="warranty_months" type="number" min="0" max="120"
                               class="form-control" placeholder="0 = tidak ada">
                    </div>
                </div>

                {{-- ── Row 4: Vendor, Tanggal Beli ── --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label-sm">Vendor / Toko</label>
                        <input wire:model.lazy="vendor" type="text" class="form-control"
                               placeholder="Nama toko atau pemasok">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label-sm">Tanggal Beli <span class="text-danger">*</span></label>
                        <input wire:model.lazy="purchase_date" type="date"
                               class="form-control @error('purchase_date') is-invalid @enderror"
                               max="{{ now()->format('Y-m-d') }}">
                        @error('purchase_date') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <hr style="border-color:#f1f5f9;margin:8px 0 16px">

                {{-- ── Row 5: Status & Tanggal Rusak ── --}}
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label-sm">Status <span class="text-danger">*</span></label>
                        <select wire:model="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="active">✅ Aktif</option>
                            <option value="maintenance">🔧 Maintenance</option>
                            <option value="damaged">❌ Rusak</option>
                            <option value="sold">📦 Dijual</option>
                        </select>
                        @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    @if($status === 'damaged')
                    <div class="col-md-4 mb-3">
                        <label class="form-label-sm">Tanggal Rusak</label>
                        <input wire:model.lazy="damaged_at" type="date" class="form-control">
                        <small class="text-muted">Diisi otomatis saat dinonaktifkan</small>
                    </div>
                    @endif

                    @if($status === 'sold')
                    <div class="col-md-4 mb-3">
                        <label class="form-label-sm">Tanggal Dijual</label>
                        <input wire:model.lazy="sold_at" type="date" class="form-control">
                    </div>
                    @endif
                </div>

                {{-- ── Catatan ── --}}
                <div class="mb-3">
                    <label class="form-label-sm">Catatan</label>
                    <textarea wire:model.lazy="notes" rows="3" class="form-control"
                              placeholder="Keterangan tambahan tentang asset..."></textarea>
                    @error('notes') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                {{-- ── Submit ── --}}
                <div class="d-flex justify-content-end gap-2 mt-2">
                    <a href="{{ route('internet-asset.index') }}" class="btn btn-outline-secondary">
                        Batal
                    </a>
                    <button wire:click="save" wire:loading.attr="disabled" class="btn btn-primary">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-save mr-1"></i> {{ $assetId ? 'Simpan Perubahan' : 'Simpan Asset' }}
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

@section('css')
<style>
.form-label-sm {
    font-size: .8rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 5px;
    display: block;
}
.gap-2 { gap: 8px !important; }
</style>
@stop
