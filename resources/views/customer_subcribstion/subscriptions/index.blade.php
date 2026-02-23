@extends('adminlte::page')

@section('title', 'My Subscriptions')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>My Subscriptions</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('customer-software.index') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Langganan Baru
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Langganan Saya</h3>
        </div>
        <div class="card-body">
            {{-- Filter --}}
            <form method="GET" class="mb-3">
                <div class="row">
                    <div class="col-md-3">
                        <select name="status" class="form-control">
                            <option value="">-- Semua Status --</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info btn-block">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('customer-subscription.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            {{-- Subscriptions Cards --}}
            <div class="row">
                @forelse($subscriptions as $subscription)
                <div class="col-md-6 mb-3">
                    <div class="card {{ $subscription->status == 'active' ? 'card-primary card-outline' : 'card-secondary' }}">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    @if($subscription->masterAccount->software->logo)
                                    <img src="{{ Storage::url($subscription->masterAccount->software->logo) }}" 
                                         alt="{{ $subscription->masterAccount->software->nama }}" 
                                         class="img-fluid"
                                         style="max-height: 80px;">
                                    @else
                                    <i class="fas fa-desktop fa-3x text-muted"></i>
                                    @endif
                                </div>
                                <div class="col-md-9">
                                    <h5>{{ $subscription->masterAccount->software->nama }}</h5>
                                    <p class="text-muted mb-2">{{ $subscription->package->nama_paket }}</p>
                                    
                                    <div class="mb-2">
                                        <span class="badge badge-{{ $subscription->status_badge }}">
                                            {{ ucfirst($subscription->status) }}
                                        </span>
                                        <span class="badge badge-{{ $subscription->payment_status == 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($subscription->payment_status) }}
                                        </span>
                                    </div>
                                    
                                    @if($subscription->tanggal_expired)
                                    <small class="text-muted">
                                        <i class="far fa-calendar"></i> 
                                        Expired: {{ carbon\carbon::parse($subscription->tanggal_expired)->format('d M Y') }}
                                        @if($subscription->isExpiringSoon(7) && $subscription->status == 'active')
                                            <span class="text-danger">
                                                ({{ $subscription->days_until_expiry }} hari lagi)
                                            </span>
                                        @endif
                                    </small>
                                    @endif
                                    
                                    <div class="mt-2">
                                        <a href="{{ route('customer-subscription.show', $subscription) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                        
                                        @if($subscription->status == 'expired' || $subscription->isExpiringSoon(7))
                                        <a href="{{ route('customer-subscription.renew', $subscription) }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-sync"></i> Perpanjang
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="callout callout-info text-center">
                        <h5><i class="fas fa-info-circle"></i> Belum ada langganan</h5>
                        <p>Anda belum memiliki langganan aktif. Mulai langganan sekarang!</p>
                        <a href="{{ route('customer-software.index') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Pilih Software
                        </a>
                    </div>
                </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($subscriptions->hasPages())
            <div class="mt-3">
                {{ $subscriptions->links() }}
            </div>
            @endif
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
