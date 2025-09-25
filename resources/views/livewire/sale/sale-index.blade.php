<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Sales List</h4>
        <div class="d-flex gap-2">
            <div class="input-group" style="width: 300px;">
                <input type="text" wire:model.live="search" class="form-control" placeholder="Search sales...">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($sales->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No sales found</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Transaction Code</th>
                                <th>Customer Email</th>
                                <th>Total Amount</th>
                                <th>Final Amount</th>
                                <th>Status</th>
                                <th>Payment Method</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sales as $sale)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $sale->transaction_code ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $sale->transaction_number ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $sale->customer_email ?? 'Guest' }}</td>
                                    <td>Rp {{ number_format($sale->total_amount, 2) }}</td>
                                    <td>Rp {{ number_format($sale->final_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $sale->status === 'completed' ? 'success' : ($sale->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($sale->status) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($sale->payment_method) }}</td>
                                    <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('sales.show', $sale->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button wire:click="deleteSale('{{ $sale->id }}')" 
                                                    wire:confirm="Are you sure you want to delete this sale?"
                                                    class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                            <option value="5">5 per page</option>
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                        </select>
                    </div>
                    
                    <div>
                        {{ $sales->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>