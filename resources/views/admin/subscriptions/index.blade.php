@extends('adminlte::page')

@section('title', 'Subscriptions')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Subscriptions</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item active">Subscriptions</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Subscription</h3>
        </div>
        <div class="card-body">
            {{-- Filter --}}
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-2">
                        <input type="text" name="search" class="form-control" placeholder="Cari order/customer..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-control">
                            <option value="">-- Status --</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="payment_status" class="form-control">
                            <option value="">-- Payment --</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="software_id" class="form-control">
                            <option value="">-- Software --</option>
                            @foreach($softwares as $software)
                            <option value="{{ $software->id }}" {{ request('software_id') == $software->id ? 'selected' : '' }}>
                                {{ $software->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info btn-block">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('subscription.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th>Order Number</th>
                            <th>Customer</th>
                            <th>Software</th>
                            <th>Package</th>
                            <th>Harga</th>
                            <th>Tanggal Expired</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $subscription)
                        <tr>
                            <td>
                                <strong>{{ $subscription->order_number }}</strong>
                                <br><small class="text-muted">{{ $subscription->created_at->format('d M Y') }}</small>
                            </td>
                            <td>
                                {{ $subscription->user->name }}
                                <br><small class="text-muted">{{ $subscription->user->email }}</small>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $subscription->masterAccount->software->nama }}
                                </span>
                            </td>
                            <td>{{ $subscription->package->nama_paket }}</td>
                            <td>Rp {{ number_format($subscription->harga_bayar, 0, ',', '.') }}</td>
                            <td>
                                @if($subscription->tanggal_expired)
                                    {{ carbon\carbon::parse($subscription->tanggal_expired)->format('d M Y') }}
                                    <br>
                                    @if($subscription->isExpiringSoon(7) && $subscription->status == 'active')
                                        <small class="text-danger">
                                            <i class="fas fa-exclamation-triangle"></i> {{ $subscription->days_until_expiry }} hari lagi
                                        </small>
                                    @endif
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $subscription->status_badge }}">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $subscription->payment_status == 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($subscription->payment_status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    @canAccess('show','subscriptions')
                                    <a href="{{ route('subscription.show', $subscription) }}" class="btn btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcanAccess
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Belum ada data subscription</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $subscriptions->withQueryString()->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
