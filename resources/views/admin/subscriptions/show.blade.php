@extends('adminlte::page')

@section('title', 'Detail Subscription')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Detail Subscription</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('subscription.index') }}">Subscriptions</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        {{-- Subscription Info --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Subscription</h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $subscription->status_badge }} badge-lg">
                            {{ ucfirst($subscription->status) }}
                        </span>
                        <span class="badge badge-{{ $subscription->payment_status == 'paid' ? 'success' : 'warning' }} badge-lg">
                            {{ ucfirst($subscription->payment_status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Order Number</th>
                            <td><strong>{{ $subscription->order_number }}</strong></td>
                        </tr>
                        <tr>
                            <th>Customer</th>
                            <td>
                                {{ $subscription->user->name }}
                                <br><small class="text-muted">{{ $subscription->user->email }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Software</th>
                            <td>
                                <span class="badge badge-info">
                                    {{ $subscription->masterAccount->software->nama }} - {{ $subscription->masterAccount->software->tipe_paket }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Master Account</th>
                            <td>{{ $subscription->masterAccount->nama_akun }}</td>
                        </tr>
                        <tr>
                            <th>Package</th>
                            <td>
                                {{ $subscription->package->nama_paket }}
                                <small class="text-muted">({{ $subscription->package->durasi_hari }} hari)</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Harga Bayar</th>
                            <td><strong>Rp {{ number_format($subscription->harga_bayar, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <th>Tanggal Mulai</th>
                            <td>
                                @if($subscription->tanggal_mulai)
                                    {{ $subscription->tanggal_mulai->format('d M Y') }}
                                @else
                                    <small class="text-muted">Belum aktif</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Expired</th>
                            <td>
                                @if($subscription->tanggal_expired)
                                    {{ $subscription->tanggal_expired->format('d M Y') }}
                                    @if($subscription->isExpiringSoon(7) && $subscription->status == 'active')
                                        <br><small class="text-danger">
                                            <i class="fas fa-exclamation-triangle"></i> {{ $subscription->days_until_expiry }} hari lagi
                                        </small>
                                    @endif
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat</th>
                            <td>{{ $subscription->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Payment History --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Pembayaran</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>External ID</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Paid At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscription->payments as $payment)
                                <tr>
                                    <td>{{ $payment->xendit_external_id }}</td>
                                    <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $payment->status_badge }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($payment->paid_at)
                                            {{ $payment->paid_at->format('d M Y H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada pembayaran</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Aksi</h3>
                </div>
                <div class="card-body">
                    @if($subscription->status == 'active')
                        <a href="{{ route('subscription.edit-expiry', $subscription) }}" class="btn btn-warning btn-block">
                            <i class="fas fa-calendar"></i> Ubah Tanggal Expired
                        </a>
                        
                        <a href="{{ route('subscription.edit-master-account', $subscription) }}" class="btn btn-info btn-block">
                            <i class="fas fa-exchange-alt"></i> Ganti Master Account
                        </a>
                        
                        <form action="{{ route('subscription.suspend', $subscription) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Yakin ingin suspend subscription ini?')">
                                <i class="fas fa-ban"></i> Suspend Subscription
                            </button>
                        </form>
                    @elseif($subscription->status == 'suspended')
                        <form action="{{ route('subscription.activate', $subscription) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Yakin ingin activate subscription ini?')">
                                <i class="fas fa-check"></i> Activate Subscription
                            </button>
                        </form>
                    @endif
                    
                    <hr>
                    
                    <a href="{{ route('subscription.payments', $subscription) }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-money-bill"></i> Lihat Semua Payments
                    </a>
                    
                    <a href="{{ route('subscription.index') }}" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> Kembali ke List
                    </a>
                </div>
            </div>

            {{-- Credentials Info (if active & paid) --}}
            @if($subscription->isActivePaid())
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Informasi Akses</h3>
                </div>
                <div class="card-body">
                    @php $ma = $subscription->masterAccount; @endphp
                    
                    @if($ma->email_akun)
                    <div class="form-group">
                        <label>Email Akun:</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $ma->email_akun }}" readonly>
                    </div>
                    @endif
                    
                    @if($ma->password_akun)
                    <div class="form-group">
                        <label>Password:</label>
                        <div class="input-group input-group-sm">
                            <input type="password" id="password-field" class="form-control" value="{{ $ma->password_akun }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($ma->pin_code)
                    <div class="form-group">
                        <label>PIN Code:</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $ma->pin_code }}" readonly>
                    </div>
                    @endif
                    
                    @if($ma->link_invite)
                    <div class="form-group">
                        <label>Link Invite:</label>
                        <a href="{{ $ma->link_invite }}" target="_blank" class="btn btn-sm btn-info btn-block">
                            <i class="fas fa-external-link-alt"></i> Buka Link
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#toggle-password').on('click', function() {
        const passwordField = $('#password-field');
        const icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
});
</script>
@stop
