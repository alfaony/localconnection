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
                                                    
                                                    @if($payment->status == 'pending')
                                                        <button type="button" 
                                                                class="btn btn-success manual-approve"
                                                                data-id="{{ $payment->id }}"
                                                                title="Manual Approve">
                                                            <i class="fas fa-check"></i>
                                                        </button>
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
@stop

@section('css')
<style>
.info-box-number {
    font-size: 1.3rem;
}
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Manual approve payment
    $('.manual-approve').on('click', function() {
        const paymentId = $(this).data('id');
        
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
                    url: `/admin/payments/${paymentId}/manual-approve`,
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