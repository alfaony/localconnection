@extends('adminlte::page')

@section('title', 'My Subscriptions')

@section('content_header')
    <div class="row mb-3 align-items-center">
        <div class="col-sm-6">
            <h1 class="header-title">My Subscriptions</h1>
        </div>
        <div class="col-sm-6 text-right">
            @canAccess('index','software_customers')
            <a href="{{ route('customer-software.index') }}" class="btn-modern primary d-inline-flex" style="width: auto; padding: 10px 20px;">
                <i class="fas fa-plus"></i> Langganan Baru
            </a>
            @endcanAccess
        </div>
    </div>
@stop

@section('content')
<div class="custom-container">
    <div class="modern-card p-4">
        {{-- Filter --}}
        <div class="search-wrapper mb-4 border-bottom pb-4">
            <form method="GET">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <select name="status" class="modern-select w-100">
                            <option value="">-- Semua Status --</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-6">
                        <button type="submit" class="btn-modern info w-100">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-3 col-6">
                        <a href="{{ route('customer-subscription.index') }}" class="btn-modern secondary w-100 text-center">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Subscriptions Grid --}}
        <div class="subscription-grid">
            @forelse($subscriptions as $subscription)
            <div class="sub-card {{ $subscription->status == 'active' ? 'active' : '' }}">
                <div class="sub-card-body">
                    <div class="sub-card-left">
                        @if($subscription->masterAccount->software->logo)
                        <img src="{{ s3_asset(true, 10, $subscription->masterAccount->software->logo) }}" 
                             alt="{{ $subscription->masterAccount->software->nama ?? ''}}" 
                             class="sub-logo">
                        @else
                        <div class="sub-logo-placeholder">
                            <i class="fas fa-desktop"></i>
                        </div>
                        @endif
                    </div>
                    <div class="sub-card-right">
                        <h5 class="sub-title">{{ $subscription->masterAccount->software->nama ?? ''}}</h5>
                        <p class="sub-package">{{ $subscription->package->nama_paket }}</p>
                        
                        <div class="sub-badges mb-2">
                            <span class="custom-badge status-{{ $subscription->status_badge }}">
                                {{ ucfirst($subscription->status) }}
                            </span>
                            <span class="custom-badge payment-{{ $subscription->payment_status == 'paid' ? 'success' : 'warning' }}">
                                {{ ucfirst($subscription->payment_status) }}
                            </span>
                        </div>
                        
                        @if($subscription->tanggal_expired)
                        <div class="sub-date">
                            <i class="far fa-calendar text-muted"></i> 
                            Expired: {{ carbon\carbon::parse($subscription->tanggal_expired)->format('d M Y') }}
                            @if($subscription->isExpiringSoon(7) && $subscription->status == 'active')
                                <span class="text-danger ml-1 font-weight-bold">
                                    ({{ $subscription->days_until_expiry }} hari lagi)
                                </span>
                            @endif
                        </div>
                        @endif
                        
                        <div class="sub-actions mt-3">
                            {{-- Main Detail Button --}}
                            @canAccess('show','customer_subscriptions')
                            <a href="{{ route('customer-subscription.show', $subscription) }}" class="btn-modern info py-2 px-3 action-btn">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            @endcanAccess

                            {{-- Payment Action Buttons based on state --}}
                            @php $latestPayment = $subscription->latestPayment; @endphp

                            @if($subscription->payment_status !== 'paid' && !in_array($subscription->status, ['expired']))
                                @if($latestPayment && $latestPayment->payment_gateway === 'manual' && in_array($latestPayment->status, ['pending','unpaid']))
                                    {{-- Manual: upload bukti transfer --}}
                                    @canAccess('paymentPending','customer_checkouts')
                                    <a href="{{ route('customer-checkout.payment.pending', $subscription->order_number) }}" 
                                       class="btn-modern warning py-2 px-3 action-btn" title="Selesaikan pembayaran">
                                        <i class="fas fa-upload"></i> Bayar
                                    </a>
                                    @endcanAccess

                                @elseif($latestPayment && in_array($latestPayment->payment_gateway, ['xendit','midtrans']) && in_array($subscription->payment_status, ['unpaid','pending']))
                                    {{-- Xendit/Midtrans pending: buat ulang sesi --}}
                                    @canAccess('paymentPending','customer_checkouts')
                                    <a href="{{ route('customer-checkout.retry-payment', ['subscription' => $subscription->id, 'gateway' => $latestPayment->payment_gateway]) }}"
                                       class="btn-modern warning py-2 px-3 action-btn"
                                       title="Buat ulang sesi pembayaran"
                                       onclick="return confirm('Buat ulang sesi pembayaran via {{ strtoupper($latestPayment->payment_gateway) }}?')">
                                        <i class="fas fa-credit-card"></i> Bayar
                                    </a>
                                    @endcanAccess

                                @elseif(!$latestPayment || in_array($latestPayment->status, ['failed', 'expired', 'cancelled']))
                                    {{-- Tidak ada payment atau gagal: arahkan ke katalog --}}
                                    @canAccess('index','customer_software')
                                    <a href="{{ route('customer-software.index') }}" 
                                       class="btn-modern warning py-2 px-3 action-btn" title="Pilih metode pembayaran">
                                        <i class="fas fa-credit-card"></i> Bayar Ulang
                                    </a>
                                    @endcanAccess
                                @endif
                            @else
                                @if($subscription->status == 'expired' || $subscription->isExpiringSoon(7))
                                <a href="{{ route('customer-subscription.renew', $subscription) }}" class="btn-modern success py-2 px-3 action-btn">
                                    <i class="fas fa-sync"></i> Perpanjang
                                </a>
                                @endif
                            @endif

                            {{-- Payment status quick info --}}
                            @if($latestPayment)
                                @if($latestPayment->status === 'paid' || $subscription->payment_status === 'paid')
                                    <span class="btn-modern success disabled py-2 px-3 action-btn">
                                        <i class="fas fa-check-circle"></i> Lunas
                                    </span>
                                @elseif($latestPayment->manual_transfer_proof && $latestPayment->status === 'pending')
                                    <span class="btn-modern secondary disabled py-2 px-3 action-btn" title="Bukti transfer sudah diupload, menunggu verifikasi">
                                        <i class="fas fa-hourglass-half"></i> Verifikasi
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12" style="grid-column: 1 / -1;">
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <h3>Belum ada langganan</h3>
                    <p>Anda belum memiliki langganan aktif. Mulai langganan sekarang!</p>
                    <a href="{{ route('customer-software.index') }}" class="btn-modern primary d-inline-flex mt-3" style="width: auto; padding: 12px 24px;">
                        <i class="fas fa-plus"></i> Pilih Software
                    </a>
                </div>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($subscriptions->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $subscriptions->withQueryString()->links('vendor.pagination.bootstrap-4') }}
        </div>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary:   #de342f;
        --primary-d: #b91c1c;
        --primary-l: #fee2e2;
        --success:   #10b981;
        --danger:    #ef4444;
        --warning:   #f59e0b;
        --info:      #3b82f6;
        --bg:        #fdfafa;
        --card-bg:   #ffffff;
        --text:      #1e293b;
        --muted:     #64748b;
        --border:    #e2e8f0;
        --radius:    16px;
        --shadow:    0 4px 20px rgba(222, 52, 47, .08);
        --font-inter: 'Inter', sans-serif;
    }

    body { font-family: var(--font-inter); background: var(--bg); }

    .custom-container { max-width: 1200px; margin: 0 auto; padding-bottom: 40px; }

    .header-title { font-weight: 800; font-size: 1.75rem; color: var(--text); margin: 0; }

    .modern-card {
        background: var(--card-bg); border: 1px solid var(--border);
        border-radius: var(--radius); box-shadow: var(--shadow);
    }

    /* Form Elements */
    .modern-select {
        border: 1px solid var(--border); border-radius: 8px;
        padding: 10px 14px; font-size: 14px; color: var(--text);
        background: #fff; outline: none; transition: border-color 0.2s;
    }
    .modern-select:focus { border-color: var(--primary); }

    /* Buttons */
    .btn-modern {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        border-radius: 8px; font-size: 14px; font-weight: 600;
        text-decoration: none; cursor: pointer; border: none; transition: all .2s;
    }
    .btn-modern.primary { background: var(--primary); color: #fff; }
    .btn-modern.primary:hover { background: var(--primary-d); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(222, 52, 47, .3); }
    
    .btn-modern.success { background: var(--success); color: #fff; }
    .btn-modern.success:hover { background: #059669; color: #fff; }
    
    .btn-modern.danger { background: var(--danger); color: #fff; }
    
    .btn-modern.warning { background: var(--warning); color: #fff; }
    .btn-modern.warning:hover { background: #d97706; color: #fff; }
    
    .btn-modern.info { background: #eff6ff; color: var(--info); border: 1px solid #bfdbfe; }
    .btn-modern.info:hover { background: #dbeafe; }
    
    .btn-modern.secondary { background: #f8fafc; color: var(--muted); border: 1px solid var(--border); }
    .btn-modern.secondary:hover { background: #f1f5f9; color: #475569; }
    
    .btn-modern.disabled { opacity: 0.8; cursor: not-allowed; pointer-events: none; }

    /* Subscription Grid */
    .subscription-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px;
    }
    @media (max-width: 768px) {
        .subscription-grid { grid-template-columns: 1fr; }
    }

    .sub-card {
        background: #fff; border: 1px solid var(--border); border-radius: 12px;
        transition: transform .2s, box-shadow .2s; border-left: 4px solid var(--border);
    }
    .sub-card.active { border-left-color: var(--primary); }
    .sub-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.06); }

    .sub-card-body { display: flex; padding: 20px; gap: 20px; align-items: flex-start; }
    
    .sub-card-left { flex-shrink: 0; }
    .sub-logo {
        width: 72px; height: 72px; object-fit: contain; border-radius: 12px;
        border: 1px solid var(--border); padding: 6px;
    }
    .sub-logo-placeholder {
        width: 72px; height: 72px; border-radius: 12px; background: var(--primary-l);
        display: flex; align-items: center; justify-content: center;
        font-size: 28px; color: var(--primary);
    }

    .sub-card-right { flex: 1; min-width: 0; }
    .sub-title { font-size: 1.15rem; font-weight: 700; color: var(--text); margin-bottom: 4px; }
    .sub-package { font-size: 13.5px; color: var(--muted); margin-bottom: 12px; }

    /* Badges */
    .custom-badge {
        display: inline-flex; align-items: center; padding: 4px 10px;
        border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .5px; margin-right: 6px;
    }
    .status-success { background: #d1fae5; color: #065f46; }
    .status-warning { background: #fef3c7; color: #b45309; }
    .status-danger { background: #fee2e2; color: #991b1b; }
    .status-default { background: #f1f5f9; color: #475569; }
    .payment-success { background: #d1fae5; color: #065f46; }
    .payment-warning { background: #fef3c7; color: #b45309; }
    .payment-danger  { background: #fee2e2; color: #991b1b; }

    .sub-date { font-size: 12.5px; color: var(--text); }
    
    .sub-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .action-btn { flex: 1; min-width: 100px; }

    /* Empty State */
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-state i { font-size: 48px; color: #fca5a5; margin-bottom: 16px; }
    .empty-state h3 { font-size: 1.25rem; color: var(--text); font-weight: 700; margin-bottom: 8px; }
    .empty-state p { color: var(--muted); margin: 0; }
</style>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@stop

@section('js')
@stop
