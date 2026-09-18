<div>
    <div class="row">
        @include('components.alert')
    </div>

    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('message') }}</div>
    @endif

    <div class="card card-primary card-outline mt-3">
        <div class="card-header">
            <h3 class="card-title">Platform Billing — Semua Company</h3>
            <div class="card-tools">
                <span class="badge badge-danger">{{ $suspendedCount }} company disuspend</span>
                <button wire:click="generateNow" wire:loading.attr="disabled" wire:confirm="Generate invoice periode berjalan sekarang untuk semua company yang belum punya invoice?" class="btn btn-sm btn-outline-primary ml-2">
                    Generate Invoice Sekarang
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" wire:model.debounce.400ms="search" class="form-control" placeholder="Cari nama company...">
                </div>
                <div class="col-md-3">
                    <select wire:model="statusFilter" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="pending">Belum Bayar</option>
                        <option value="overdue">Menunggak</option>
                        <option value="paid">Lunas</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Periode</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th>Akses Dashboard</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->company->name ?? '-' }}</td>
                                <td>{{ $invoice->period }}</td>
                                <td>{{ $invoice->billed_customer_count }}</td>
                                <td>Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td>
                                <td>{{ optional($invoice->due_date)->format('d M Y') }}</td>
                                <td>
                                    @if ($invoice->status === 'paid')
                                        <span class="badge badge-success">Lunas</span>
                                    @elseif ($invoice->status === 'overdue')
                                        <span class="badge badge-danger">Menunggak</span>
                                    @else
                                        <span class="badge badge-warning">Belum Bayar</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($invoice->company && $invoice->company->isBillingSuspended())
                                        <span class="badge badge-danger">Disuspend</span>
                                    @else
                                        <span class="badge badge-success">Normal</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        @if ($invoice->status !== 'paid')
                                            <button wire:click="markPaid({{ $invoice->id }})" wire:confirm="Tandai invoice ini lunas secara manual?" class="btn btn-sm btn-success">
                                                Tandai Lunas
                                            </button>
                                        @endif

                                        @if ($invoice->company)
                                            @if ($invoice->company->isBillingSuspended())
                                                <button wire:click="unsuspendCompany('{{ $invoice->company->id }}')" class="btn btn-sm btn-outline-success">
                                                    Buka Suspend
                                                </button>
                                            @else
                                                <button wire:click="suspendCompany('{{ $invoice->company->id }}')" wire:confirm="Suspend akses dashboard company ini secara manual?" class="btn btn-sm btn-outline-danger">
                                                    Suspend
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Belum ada invoice.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $invoices->links() }}
        </div>
    </div>
</div>
