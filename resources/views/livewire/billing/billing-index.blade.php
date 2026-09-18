<div>
    <div class="row">
        @include('components.alert')
    </div>

    @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('message') }}</div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">{{ session('warning') }}</div>
    @endif

    @if ($company->isBillingSuspended())
        <div class="alert alert-danger">
            <strong>Akses dashboard disuspend.</strong> Tagihan platform belum dibayar. Selesaikan pembayaran
            di bawah untuk mengaktifkan kembali akses dashboard.
        </div>
    @endif

    <div class="card card-primary card-outline mt-3">
        <div class="card-header">
            <h3 class="card-title">Tagihan Platform Keloola BOS</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Tarif: <strong>Rp{{ number_format($rate, 0, ',', '.') }}/customer aktif/bulan</strong>,
                dihitung dari rata-rata jumlah customer aktif harian selama sebulan.
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Jumlah Customer</th>
                            <th>Tarif</th>
                            <th>Total Tagihan</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->period }}</td>
                                <td>{{ $invoice->billed_customer_count }}</td>
                                <td>Rp{{ number_format($invoice->rate_per_customer, 0, ',', '.') }}</td>
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
                                    @if ($invoice->status !== 'paid')
                                        <button wire:click="pay({{ $invoice->id }})" wire:loading.attr="disabled" class="btn btn-sm btn-primary">
                                            Bayar Sekarang
                                        </button>
                                    @else
                                        <span class="text-muted">{{ optional($invoice->paid_at)->format('d M Y H:i') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada tagihan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $invoices->links() }}
        </div>
    </div>
</div>
