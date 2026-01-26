@extends('adminlte::page')

@section('title', 'Customers - ' . $masterAccount->nama_akun)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Customers</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.master-accounts.index') }}">Master Accounts</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.master-accounts.show', $masterAccount->id) }}">{{ $masterAccount->nama_akun }}</a></li>
                <li class="breadcrumb-item active">Customers</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i> Customers using {{ $masterAccount->nama_akun }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.master-accounts.show', $masterAccount->id) }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($subscriptions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Order Number</th>
                                        <th>Customer</th>
                                        <th>Package</th>
                                        <th>Start Date</th>
                                        <th>Expired Date</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Payment</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subscriptions as $subscription)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.subscriptions.show', $subscription->id) }}">
                                                    {{ $subscription->order_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <strong>{{ $subscription->user->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $subscription->user->email }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $subscription->package->nama_paket }}
                                                </span>
                                                <br>
                                                <small class="text-muted">{{ $subscription->package->durasi_hari }} hari</small>
                                            </td>
                                            <td>{{ $subscription->tanggal_mulai->format('d M Y') }}</td>
                                            <td>
                                                {{ $subscription->tanggal_expired->format('d M Y') }}
                                                @if($subscription->isExpiringSoon(7))
                                                    <br><span class="badge badge-warning">Expiring Soon</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{!! $subscription->status_badge !!}</td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $subscription->payment_status == 'paid' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($subscription->payment_status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.subscriptions.show', $subscription->id) }}" 
                                                   class="btn btn-sm btn-info"
                                                   title="View Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $subscriptions->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-4x text-muted mb-3"></i>
                            <p class="text-muted">No customers using this master account yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop