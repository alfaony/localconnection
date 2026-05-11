<div>
    @if($sale)
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4>Detail Penjualan</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Penjualan</a></li>
                        <li class="breadcrumb-item active">{{ $sale->transaction_code ?? $sale->id }}</li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Barang</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Variant</th>
                                        <th>Jumlah</th>
                                        <th>Harga Satuan</th>
                                        <th>Diskon</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->items as $item)
                                        <tr>
                                            <td>
                                                @if($item->productStore)
                                                    <div class="fw-bold">{{ $item->productStore->name }}</div>
                                                    @if($item->productStore->specification || $item->productStore->code)
                                                        <small class="text-muted">
                                                            {{ $item->productStore->code }} {{ $item->productStore->specification }}
                                                        </small>
                                                    @endif
                                                @else
                                                    <div class="text-muted">Produk tidak ditemukan</div>
                                                @endif
                                            </td>
                                            <td class="align-middle">{{ $item->productStore->variant ?? '-' }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>
                                                @php
                                                    $hasDiscount = $item->discount_percent > 0
                                                        || ($item->discount_type === 'flat' && $item->discount_amount > 0);
                                                @endphp
                                                @if($hasDiscount && $item->original_price)
                                                    <del class="text-muted small">Rp {{ number_format($item->original_price, 0, ',', '.') }}</del><br>
                                                    <span class="fw-bold text-success">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                                @else
                                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->discount_type === 'flat' && $item->discount_amount > 0)
                                                    <span class="badge bg-danger">-Rp {{ number_format($item->discount_amount, 0, ',', '.') }}</span>
                                                @elseif($item->discount_percent > 0)
                                                    <span class="badge bg-danger">{{ number_format($item->discount_percent, 0) }}%</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Penjualan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kode Transaksi:</label>
                            <p>{{ $sale->transaction_code ?? 'N/A' }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Nomor Transaksi:</label>
                            <p>{{ $sale->transaction_number ?? 'N/A' }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status:</label>
                            <span class="badge bg-{{ $sale->status === 'completed' ? 'success' : ($sale->status === 'pending' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($sale->status) }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email Pelanggan:</label>
                            <p>{{ $sale->customer_email ?? 'Guest' }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Metode Pembayaran:</label>
                            <p>{!! $sale->payment_details_html !!}</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ringkasan Jumlah</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Jumlah:</span>
                            <strong>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</strong>
                        </div>

                        @if($sale->tax_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Pajak ({{ $sale->tax_value }}%):</span>
                                <strong>Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</strong>
                            </div>
                        @endif

                        @if($sale->discount_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Diskon:</span>
                                <strong>-Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</strong>
                            </div>
                        @endif

                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Jumlah Akhir:</span>
                            <strong class="fs-5 text-primary">Rp {{ number_format($sale->final_amount, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Timestamps</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-label">Dibuat Oleh:</label>
                        <p>{{ $sale->user->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Dibuat Pada:</label>
                        <p>{{ $sale->created_at->format('M d, Y H:i:s') }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Diperbarui Pada:</label>
                        <p>{{ $sale->updated_at->format('M d, Y H:i:s') }}</p>
                    </div>
                    @if($sale->deleted_at)
                    <div class="col-md-4">
                        <label class="form-label">Dihapus Pada:</label>
                        <p class="text-danger">{{ $sale->deleted_at->format('M d, Y H:i:s') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuatkan detail penjualan...</p>
        </div>
    @endif
</div>
