<div>
    <div class="row">
        <div class="col-md-12 mt-2">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Daftar Penjualan</h4>
                <div class="d-flex gap-2">
                    <div class="input-group" style="width: 300px;">
                        <input type="text" wire:model.live="search" class="form-control" placeholder="Cari penjualan...">
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
                            <p class="text-muted">Tidak ada penjualan ditemukan</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Kode Transaksi</th>
                                        <th>Email Pelanggan</th>
                                        <th>Jumlah Total</th>
                                        <th>Jumlah Akhir</th>
                                        <th>Status</th>
                                        <th>Metode Pembayaran</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
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
                                            <td>{!! $sale->payment_method_html !!}</td>
                                            <td>{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @canAccess('show','sales')
                                                    <a href="{{ route('sales.show', $sale->id) }}" 
                                                       class="btn btn-sm btn-outline-primary mb-1 mr-1">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @endcanAccess

                                                    @canAccess('destroy','sales')
                                                    <button wire:click="confirmDelete('{{ $sale->id }}')" 
                                                            class="btn btn-sm btn-outline-danger mb-1">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    @endcanAccess
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
                                    <option value="5">5 per halaman</option>
                                    <option value="10">10 per halaman</option>
                                    <option value="25">25 per halaman</option>
                                    <option value="50">50 per halaman</option>
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
    </div>
</div>

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
<script>
    document.addEventListener('livewire:load', function() {
        window.addEventListener('confirm-delete', (event) => {
            const saleId = event.detail.saleId;
            
            Swal.fire({
                title: 'Hapus Penjualan?',
                text: "Anda tidak dapat mengembalikan data ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emit('deleteSale', saleId);
                }
            });
        });

        window.addEventListener('notify', (event) => {
            Swal.fire({
                icon: event.detail.type,
                title: event.detail.type === 'success' ? 'Berhasil!' : 'Error!',
                text: event.detail.message,
                timer: 3000,
                showConfirmButton: false
            });
        });
    });
</script>
@endpush