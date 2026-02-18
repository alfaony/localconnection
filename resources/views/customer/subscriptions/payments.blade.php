@extends('adminlte::page')

@section('title', 'Payment History')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Payment History</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                {{-- 
                    <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
                    --}}
                <li class="breadcrumb-item"><a href="{{ route('customer-software.subscription.index') }}">My Subscriptions</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customer-software.subscription.show', $subscription->id) }}">{{ $subscription->order_number }}</a></li>
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
                        <div class="col-md-3">
                            <strong>Order Number:</strong><br>
                            {{ $subscription->order_number }}
                        </div>
                        <div class="col-md-3">
                            <strong>Software:</strong><br>
                            {{ $subscription->software->nama_software ?? "-"}}
                        </div>
                        <div class="col-md-3">
                            <strong>Package:</strong><br>
                            {{ $subscription->package->nama_paket ?? "-"}}
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong><br>
                            {!! $subscription->status_badge !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-receipt"></i> Payment History
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('customer-software.subscription.show', $subscription->id) }}" 
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
                                        <th>Payment Date</th>
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
                                                <br>
                                                <small class="text-muted">
                                                    Created: {{ $payment->created_at->format('d M Y H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                @if($payment->paid_at)
                                                    {{ $payment->paid_at->format('d M Y H:i') }}
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
                                                @if($payment->status == 'pending' && $payment->xendit_invoice_url)
                                                    <a href="{{ $payment->xendit_invoice_url }}" 
                                                       target="_blank"
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-credit-card"></i> Pay Now
                                                    </a>
                                                @elseif($payment->status == 'paid')
                                                    <button type="button" 
                                                            class="btn btn-sm btn-success"
                                                            disabled>
                                                        <i class="fas fa-check"></i> Completed
                                                    </button>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No payment history yet.</p>
                        </div>
                    @endif
                </div>
                @if($payments->count() > 0)
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Total Payments:</strong> {{ $payments->count() }}
                            </div>
                            <div class="col-md-6 text-right">
                                <strong>Total Paid:</strong> 
                                Rp {{ number_format($payments->where('status', 'paid')->sum('amount'), 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Payment Summary -->
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Payment Summary
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-check"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Paid</span>
                                    <span class="info-box-number">
                                        {{ $payments->where('status', 'paid')->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-clock"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pending</span>
                                    <span class="info-box-number">
                                        {{ $payments->where('status', 'pending')->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-times"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Failed</span>
                                    <span class="info-box-number">
                                        {{ $payments->where('status', 'failed')->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary">
                                    <i class="fas fa-ban"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Expired</span>
                                    <span class="info-box-number">
                                        {{ $payments->where('status', 'expired')->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.info-box {
    min-height: 80px;
}
.info-box-number {
    font-size: 1.5rem;
}
</style>
@stop