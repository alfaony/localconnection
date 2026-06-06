@section('title', 'Master Asset')

@section('content_header')
<div class="d-flex align-items-center justify-content-between">
    <div>
        <h4 class="mb-0 font-weight-bold text-dark">
            <i class="fas fa-hdd mr-2 text-primary"></i>Master Asset
        </h4>
        <small class="text-muted">Kelola inventaris perangkat dan infrastruktur</small>
    </div>
    <a href="{{ route('internet-asset.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Tambah Asset
    </a>
</div>
@stop

{{-- ═══════════════════════════════════════════════════════
     SINGLE ROOT ELEMENT — required by Livewire v2
     ═══════════════════════════════════════════════════════ --}}
<div>

    {{-- Flash message (Livewire reactive) --}}
    @if($flashMessage)
    <div class="alert alert-{{ $flashType }} alert-dismissible fade show" role="alert">
        <i class="fas fa-{{ $flashType === 'success' ? 'check-circle' : ($flashType === 'warning' ? 'exclamation-triangle' : 'times-circle') }} mr-1"></i>
        {{ $flashMessage }}
        <button type="button" class="close" wire:click="$set('flashMessage','')"><span>&times;</span></button>
    </div>
    @endif

    {{-- ── Summary Cards ── --}}
    <div class="row mb-4">
        <div class="col-6 col-lg-3 mb-3">
            <div class="asset-stat-card" style="border-left:4px solid #2563eb">
                <div class="d-flex align-items-center" style="gap:12px">
                    <div class="asset-stat-icon" style="background:#e0f0ff;color:#2563eb"><i class="fas fa-boxes"></i></div>
                    <div>
                        <div class="asset-stat-val">{{ number_format($stats->total) }}</div>
                        <div class="asset-stat-label">Total Asset</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="asset-stat-card" style="border-left:4px solid #16a34a">
                <div class="d-flex align-items-center" style="gap:12px">
                    <div class="asset-stat-icon" style="background:#e6f9f0;color:#16a34a"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="asset-stat-val">{{ number_format($stats->total_active) }}</div>
                        <div class="asset-stat-label">Asset Aktif</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="asset-stat-card" style="border-left:4px solid #dc2626">
                <div class="d-flex align-items-center" style="gap:12px">
                    <div class="asset-stat-icon" style="background:#fde8e8;color:#dc2626"><i class="fas fa-tools"></i></div>
                    <div>
                        <div class="asset-stat-val">{{ number_format($stats->total_damaged + $stats->total_maintenance) }}</div>
                        <div class="asset-stat-label">Rusak / Maintenance</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 mb-3">
            <div class="asset-stat-card" style="border-left:4px solid #0891b2">
                <div class="d-flex align-items-center" style="gap:12px">
                    <div class="asset-stat-icon" style="background:#e0fafa;color:#0891b2"><i class="fas fa-coins"></i></div>
                    <div>
                        <div class="asset-stat-val" style="font-size:1rem">
                            Rp {{ number_format($stats->total_value, 0, ',', '.') }}
                        </div>
                        <div class="asset-stat-label">Total Nilai Investasi</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-5 mb-2">
                    <input wire:model.debounce.400ms="search" type="text" class="form-control"
                           placeholder="Cari nama, brand, model, serial...">
                </div>
                <div class="col-md-3 mb-2">
                    <select wire:model="statusFilter" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="damaged">Rusak</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="sold">Dijual</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select wire:model="categoryFilter" class="form-control">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 mb-2">
                    <button wire:click="$set('search','');$set('statusFilter','');$set('categoryFilter','')"
                            class="btn btn-outline-secondary w-100" title="Reset">
                        <i class="fas fa-redo"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Table ── --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table dash-table mb-0">
                <thead>
                    <tr>
                        <th wire:click="sortBy('name')" style="cursor:pointer">
                            Nama Asset
                            @if($sortField === 'name') <i class="fas fa-sort-{{ $sortDir === 'asc' ? 'up' : 'down' }} ml-1"></i> @endif
                        </th>
                        <th>Kategori</th>
                        <th>Brand / Model</th>
                        <th wire:click="sortBy('quantity')" style="cursor:pointer">
                            Qty @if($sortField === 'quantity') <i class="fas fa-sort-{{ $sortDir === 'asc' ? 'up' : 'down' }} ml-1"></i> @endif
                        </th>
                        <th wire:click="sortBy('unit_price')" style="cursor:pointer">
                            Harga Satuan @if($sortField === 'unit_price') <i class="fas fa-sort-{{ $sortDir === 'asc' ? 'up' : 'down' }} ml-1"></i> @endif
                        </th>
                        <th>Total Harga</th>
                        <th wire:click="sortBy('purchase_date')" style="cursor:pointer">
                            Tgl Beli @if($sortField === 'purchase_date') <i class="fas fa-sort-{{ $sortDir === 'asc' ? 'up' : 'down' }} ml-1"></i> @endif
                        </th>
                        <th>Status</th>
                        <th>Tgl Rusak</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($assets as $asset)
                    <tr wire:key="asset-row-{{ $asset->id }}">
                        <td>
                            <strong>{{ $asset->name }}</strong>
                            @if($asset->serial_number)
                            <br><small class="text-muted">SN: {{ $asset->serial_number }}</small>
                            @endif
                        </td>
                        <td><span class="category-chip">{{ $asset->category_label }}</span></td>
                        <td>
                            {{ $asset->brand ?? '–' }}
                            @if($asset->model) <br><small class="text-muted">{{ $asset->model }}</small> @endif
                        </td>
                        <td class="text-center">{{ $asset->quantity }}</td>
                        <td>Rp {{ number_format($asset->unit_price, 0, ',', '.') }}</td>
                        <td class="font-weight-bold">Rp {{ number_format($asset->total_price, 0, ',', '.') }}</td>
                        <td>{{ $asset->purchase_date->format('d M Y') }}</td>
                        <td>{!! $asset->status_badge !!}</td>
                        <td>
                            @if($asset->damaged_at)
                                <span class="text-danger">{{ $asset->damaged_at->format('d M Y') }}</span>
                            @else
                                <span class="text-muted">–</span>
                            @endif
                        </td>
                        <td class="text-center" style="white-space:nowrap">
                            <a href="{{ route('internet-asset.edit', $asset->id) }}"
                               class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button"
                                    wire:click="toggleStatus('{{ $asset->id }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="toggleStatus('{{ $asset->id }}')"
                                    class="btn btn-sm {{ $asset->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                    title="{{ $asset->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                <i class="fas fa-{{ $asset->status === 'active' ? 'times' : 'check' }}"></i>
                            </button>
                            <button type="button"
                                    wire:click="confirmDelete('{{ $asset->id }}')"
                                    class="btn btn-sm btn-outline-danger" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                            Belum ada asset. <a href="{{ route('internet-asset.create') }}">Tambah sekarang</a>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $assets->links() }}</div>
    </div>

    {{-- ── Delete Confirmation Overlay (Livewire native) ── --}}
    @if($showDeleteModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;display:flex;align-items:center;justify-content:center"
         wire:key="delete-overlay">
        <div style="background:#fff;border-radius:16px;padding:28px 28px 24px;max-width:340px;width:90%;box-shadow:0 12px 40px rgba(0,0,0,.18);text-align:center">
            <div style="width:56px;height:56px;background:#fde8e8;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:1.4rem;color:#dc2626">
                <i class="fas fa-trash"></i>
            </div>
            <h5 class="font-weight-bold mb-2" style="color:#1e293b">Hapus Asset?</h5>
            <p class="text-muted mb-4" style="font-size:.85rem">Data akan dipindahkan ke trash dan bisa dipulihkan.</p>
            <div class="d-flex justify-content-center" style="gap:10px">
                <button type="button" wire:click="cancelDelete" class="btn btn-outline-secondary">
                    Batal
                </button>
                <button type="button"
                        wire:click="destroy"
                        wire:loading.attr="disabled"
                        wire:target="destroy"
                        class="btn btn-danger">
                    <span wire:loading.remove wire:target="destroy">
                        <i class="fas fa-trash mr-1"></i> Ya, Hapus
                    </span>
                    <span wire:loading wire:target="destroy">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Menghapus...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>{{-- end single root --}}

@section('css')
<style>
.asset-stat-card {
    background:#fff; border-radius:12px;
    padding:16px 18px;
    box-shadow:0 2px 10px rgba(0,0,0,.06);
    height:100%;
}
.asset-stat-icon {
    width:48px;height:48px;border-radius:12px;
    display:flex;align-items:center;justify-content:center;
    font-size:1.2rem;flex-shrink:0;
}
.asset-stat-val   { font-size:1.4rem;font-weight:800;color:#1e293b; }
.asset-stat-label { font-size:.75rem;color:#64748b;margin-top:2px; }
.category-chip {
    background:#f1f5f9;color:#475569;
    padding:2px 8px;border-radius:6px;
    font-size:.72rem;font-weight:600;
}
</style>
@stop
