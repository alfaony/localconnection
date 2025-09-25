<div>
    @if($sale)
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4>Sale Details</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
                        <li class="breadcrumb-item active">{{ $sale->transaction_code ?? $sale->id }}</li>
                    </ol>
                </nav>
            </div>
            <div class="btn-group">
                <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <button wire:click="deleteSale" 
                        wire:confirm="Are you sure you want to delete this sale?"
                        class="btn btn-outline-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->items as $item)
                                        <tr>
                                            <td>
                                                @if($item->productStore->product)
                                                    <div class="fw-bold">{{ $item->productStore->product->name }}</div>
                                                @else
                                                    <div class="text-muted">Product not found</div>
                                                @endif
                                            </td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>Rp {{ number_format($item->unit_price, 2) }}</td>
                                            <td>Rp {{ number_format($item->subtotal, 2) }}</td>
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
                        <h5 class="card-title mb-0">Sale Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Transaction Code:</label>
                            <p>{{ $sale->transaction_code ?? 'N/A' }}</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Transaction Number:</label>
                            <p>{{ $sale->transaction_number ?? 'N/A' }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Status:</label>
                            <span class="badge bg-{{ $sale->status === 'completed' ? 'success' : ($sale->status === 'pending' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($sale->status) }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Customer Email:</label>
                            <p>{{ $sale->customer_email ?? 'Guest' }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Payment Method:</label>
                            <p>{{ ucfirst($sale->payment_method) }}</p>
                        </div>

                        @if($sale->payment_details)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Payment Details:</label>
                                <pre class="bg-light p-2 rounded">{{ json_encode($sale->payment_details, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Amount Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Amount:</span>
                            <strong>Rp {{ number_format($sale->total_amount, 2) }}</strong>
                        </div>
                        
                        @if($sale->tax_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax ({{ $sale->tax_value }}%):</span>
                                <strong>Rp {{ number_format($sale->tax_amount, 2) }}</strong>
                            </div>
                        @endif

                        @if($sale->discount_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Discount:</span>
                                <strong>-Rp {{ number_format($sale->discount_amount, 2) }}</strong>
                            </div>
                        @endif

                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Final Amount:</span>
                            <strong class="fs-5 text-primary">Rp {{ number_format($sale->final_amount, 2) }}</strong>
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
                        <label class="form-label">Created At:</label>
                        <p>{{ $sale->created_at->format('M d, Y H:i:s') }}</p>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Updated At:</label>
                        <p>{{ $sale->updated_at->format('M d, Y H:i:s') }}</p>
                    </div>
                    @if($sale->deleted_at)
                    <div class="col-md-4">
                        <label class="form-label">Deleted At:</label>
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
            <p class="mt-2">Loading sale details...</p>
        </div>
    @endif
</div>