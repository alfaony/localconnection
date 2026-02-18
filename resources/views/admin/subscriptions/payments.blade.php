@extends('adminlte::page')

@section('title', 'Payment History')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Payment History</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('subscription.index') }}">Subscriptions</a></li>
                <li class="breadcrumb-item"><a href="{{ route('subscription.show', $subscription->id) }}">{{ $subscription->order_number }}</a></li>
                <li class="breadcrumb-item active">Payments</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <!-- Subscription Info -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Subscription Information
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <strong>Order Number:</strong><br>
                            {{ $subscription->order_number }}
                        </div>
                        <div class="col-md-2">
                            <strong>Customer:</strong><br>
                            {{ $subscription->user->name }}
                        </div>
                        <div class="col-md-2">
                            <strong>Software:</strong><br>
                            {{ $subscription->software->nama ?? "" }}
                        </div>
                        <div class="col-md-2">
                            <strong>Package:</strong><br>
                            {{ $subscription->package->nama_paket }}
                        </div>
                        <div class="col-md-2">
                            <strong>Status:</strong><br>
                            {!! $subscription->status_badge !!}
                        </div>
                        <div class="col-md-2">
                            <strong>Payment:</strong><br>
                            <span class="badge badge-{{ $subscription->payment_status == 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($subscription->payment_status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Statistics -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-success">
                    <i class="fas fa-check"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Paid Payments</span>
                    <span class="info-box-number">{{ $payments->where('status', 'paid')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning">
                    <i class="fas fa-clock"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending Payments</span>
                    <span class="info-box-number">{{ $payments->where('status', 'pending')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-danger">
                    <i class="fas fa-times"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Failed Payments</span>
                    <span class="info-box-number">{{ $payments->where('status', 'failed')->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-info">
                    <i class="fas fa-money-bill-wave"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Paid</span>
                    <span class="info-box-number">
                        Rp {{ number_format($payments->where('status', 'paid')->sum('amount') / 1000, 0) }}k
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i> All Payments
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('subscription.show', $subscription->id) }}" 
                           class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Invoice Number</th>
                                        <th>Created At</th>
                                        <th>Paid At</th>
                                        <th>Method</th>
                                        <th>Channel</th>
                                        <th class="text-right">Amount</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td>
                                                <strong>{{ $payment->invoice_number }}</strong>
                                                @if($payment->xendit_invoice_id)
                                                    <br>
                                                    <small class="text-muted" title="Xendit ID">
                                                        {{ Str::limit($payment->xendit_invoice_id, 20) }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $payment->created_at->format('d M Y') }}
                                                <br>
                                                <small class="text-muted">{{ $payment->created_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                @if($payment->paid_at)
                                                    {{ $payment->paid_at->format('d M Y') }}
                                                    <br>
                                                    <small class="text-muted">{{ $payment->paid_at->format('H:i') }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payment->payment_method)
                                                    <span class="badge badge-secondary">
                                                        {{ strtoupper($payment->payment_method) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($payment->payment_channel)
                                                    {{ ucwords(str_replace('_', ' ', $payment->payment_channel)) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                                            </td>
                                            <td class="text-center">
                                                @if($payment->status == 'paid')
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Paid
                                                    </span>
                                                @elseif($payment->status == 'pending')
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Pending
                                                    </span>
                                                @elseif($payment->status == 'failed')
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times"></i> Failed
                                                    </span>
                                                @elseif($payment->status == 'expired')
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-ban"></i> Expired
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    @if($payment->xendit_invoice_url)
                                                        <a href="{{ $payment->xendit_invoice_url }}" 
                                                           target="_blank"
                                                           class="btn btn-info"
                                                           title="View Invoice">
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </a>
                                                    @endif

                                                    @if($payment->manual_transfer_proof)
                                                        <button type="button" 
                                                                class="btn btn-primary btn-lihat-bukti"
                                                                title="Lihat Bukti Transfer"
                                                                data-foto="{{ s3_asset(10,true,$payment->manual_transfer_proof) }}"
                                                                data-nama="{{ $payment->manual_transfer_sender_name ?? '-' }}"
                                                                data-bank-pengirim="{{ $payment->manual_transfer_sender_bank ?? '-' }}"
                                                                data-nama-rekening="{{ $payment->manual_transfer_account_name ?? '-' }}"
                                                                data-no-rekening="{{ $payment->manual_transfer_account_number ?? '-' }}"
                                                                data-bank-tujuan="{{ $payment->manual_transfer_bank ?? '-' }}">
                                                            <i class="fas fa-file-image"></i>
                                                        </button>
                                                    @endif
                                                    
                                                    @if($payment->status == 'pending')
                                                        @canAccess('manual-approve','subscriptions')
                                                        <button type="button" 
                                                                class="btn btn-success manual-approve"
                                                                data-id="{{ $payment->id }}"
                                                                title="Manual Approve">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        @endcanAccess
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-right">Total Paid Amount:</th>
                                        <th class="text-right">
                                            <strong>Rp {{ number_format($payments->where('status', 'paid')->sum('amount'), 0, ',', '.') }}</strong>
                                        </th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No payment history yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Timeline -->
    @if($payments->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Payment Timeline
                    </h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($payments->sortByDesc('created_at') as $payment)
                            <div>
                                <i class="fas fa-{{ $payment->status == 'paid' ? 'check bg-success' : ($payment->status == 'pending' ? 'clock bg-warning' : 'times bg-danger') }}"></i>
                                <div class="timeline-item">
                                    <span class="time">
                                        <i class="fas fa-clock"></i> {{ $payment->created_at->diffForHumans() }}
                                    </span>
                                    <h3 class="timeline-header">
                                        <strong>{{ $payment->invoice_number }}</strong> - 
                                        <span class="badge badge-{{ $payment->status == 'paid' ? 'success' : ($payment->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ strtoupper($payment->status) }}
                                        </span>
                                    </h3>
                                    <div class="timeline-body">
                                        <strong>Amount:</strong> Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                        @if($payment->payment_method)
                                            <br><strong>Method:</strong> {{ strtoupper($payment->payment_method) }}
                                        @endif
                                        @if($payment->payment_channel)
                                            <br><strong>Channel:</strong> {{ ucwords(str_replace('_', ' ', $payment->payment_channel)) }}
                                        @endif
                                        @if($payment->paid_at)
                                            <br><strong>Paid At:</strong> {{ $payment->paid_at->format('d M Y H:i') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal Bukti Transfer -->
    <div class="modal fade" id="modalBuktiTransfer" tabindex="-1" role="dialog" aria-labelledby="modalBuktiTransferLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="modalBuktiTransferLabel">
                        <i class="fas fa-file-image"></i> Bukti Transfer
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Foto Bukti -->
                        <div class="col-md-7 text-center">
                            <p class="font-weight-bold text-muted mb-2">Foto Bukti Transfer</p>
                            <a id="linkFotoBukti" href="#" target="_blank">
                                <img id="fotoBuktiTransfer" src="" alt="Bukti Transfer"
                                     class="img-fluid rounded border"
                                     style="max-height: 450px; object-fit: contain; cursor: zoom-in;">
                            </a>
                            <br>
                            <small class="text-muted">Klik foto untuk membuka di tab baru</small>
                        </div>
                        <!-- Info Transfer -->
                        <div class="col-md-5">
                            <p class="font-weight-bold text-muted mb-3">Informasi Transfer</p>
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    <tr class="table-light">
                                        <th colspan="2" class="text-center">Pengirim</th>
                                    </tr>
                                    <tr>
                                        <th width="45%">Nama</th>
                                        <td id="modalNamaPengirim">-</td>
                                    </tr>
                                    <tr>
                                        <th>Bank</th>
                                        <td id="modalBankPengirim">-</td>
                                    </tr>
                                    <tr class="table-light">
                                        <th colspan="2" class="text-center">Rekening Tujuan</th>
                                    </tr>
                                    <tr>
                                        <th>Bank</th>
                                        <td id="modalBankTujuan">-</td>
                                    </tr>
                                    <tr>
                                        <th>Nama</th>
                                        <td id="modalNamaRekening">-</td>
                                    </tr>
                                    <tr>
                                        <th>No. Rekening</th>
                                        <td>
                                            <strong id="modalNoRekening">-</strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                    <a id="btnDownloadBukti" href="#" target="_blank" class="btn btn-primary">
                        <i class="fas fa-download"></i> Buka / Download
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.info-box-number {
    font-size: 1.3rem;
}
</style>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Lihat Bukti Transfer - Modal
    $('.btn-lihat-bukti').on('click', function() {
        const foto        = $(this).data('foto');
        const nama        = $(this).data('nama');
        const bankPengirim= $(this).data('bank-pengirim');
        const namaRek     = $(this).data('nama-rekening');
        const noRek       = $(this).data('no-rekening');
        const bankTujuan  = $(this).data('bank-tujuan');

        $('#fotoBuktiTransfer').attr('src', foto);
        $('#linkFotoBukti').attr('href', foto);
        $('#btnDownloadBukti').attr('href', foto);
        $('#modalNamaPengirim').text(nama);
        $('#modalBankPengirim').text(bankPengirim);
        $('#modalNamaRekening').text(namaRek);
        $('#modalNoRekening').text(noRek);
        $('#modalBankTujuan').text(bankTujuan);
        
        // $('#modalBuktiTransfer').modal('show');
        // Tampilkan modal
        const modalElement = document.getElementById('modalBuktiTransfer');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            console.error('Modal #modalBuktiTransfer not found in DOM');
        }
    });

    // Manual approve payment
    $('.manual-approve').on('click', function() {
        const paymentId = $(this).data('id');
        const url = `{{ route('subscription.payments.manual-approve', ':id') }}`.replace(':id', paymentId);
        
        Swal.fire({
            title: 'Manual Approve Payment?',
            text: "This will mark the payment as paid and activate the subscription.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Approved!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    },
                    error: function(xhr) {
                        let message = 'Failed to approve payment';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            title: 'Error!',
                            text: message,
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });
});
</script>
@stop