@extends('adminlte::page')

@section('title', 'Detail Subscription')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-12">
            <a href="{{ route('customer.subscription.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali ke My Subscriptions
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        {{-- Subscription Info --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Informasi Langganan</h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $subscription->status == 'active' ? 'success' : 'danger' }} badge-lg">
                            {{ ucfirst($subscription->status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 text-center">
                            @if($subscription->masterAccount->software->logo)
                            <img src="{{ Storage::url($subscription->masterAccount->software->logo) }}" 
                                 alt="{{ $subscription->masterAccount->software->nama }}" 
                                 class="img-fluid"
                                 style="max-height: 120px;">
                            @else
                            <i class="fas fa-desktop fa-4x text-muted"></i>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h3>{{ $subscription->masterAccount->software->nama }}</h3>
                            <p class="text-muted">{{ $subscription->masterAccount->software->tipe_paket }}</p>
                            <span class="badge badge-info">{{ $subscription->package->nama_paket }}</span>
                        </div>
                    </div>

                    <hr>

                    <table class="table">
                        <tr>
                            <th width="200">Order Number</th>
                            <td><strong>{{ $subscription->order_number }}</strong></td>
                        </tr>
                        <tr>
                            <th>Package</th>
                            <td>{{ $subscription->package->nama_paket }} ({{ $subscription->package->durasi_hari }} hari)</td>
                        </tr>
                        <tr>
                            <th>Harga</th>
                            <td><strong>Rp {{ number_format($subscription->harga_bayar, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <th>Tanggal Mulai</th>
                            <td>
                                @if($subscription->tanggal_mulai)
                                    {{ $subscription->tanggal_mulai->format('d M Y') }}
                                @else
                                    <small class="text-muted">Menunggu pembayaran</small>
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
                            <th>Status Pembayaran</th>
                            <td>
                                <span class="badge badge-{{ $subscription->payment_status == 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($subscription->payment_status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Payment History --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Riwayat Pembayaran</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Dibayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscription->payments as $payment)
                                <tr>
                                    <td>{{ $payment->created_at->format('d M Y H:i') }}</td>
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

        {{-- Credentials Access --}}
        <div class="col-md-4">
            @if($showCredentials)
            {{-- Show credentials only if active and paid --}}
            <div class="card card-success">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key"></i> Informasi Akses
                    </h5>
                </div>
                <div class="card-body">
                    @php $ma = $subscription->masterAccount; @endphp
                    
                    @if($ma->email_akun)
                    <div class="form-group">
                        <label>Email Akun:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $ma->email_akun }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary copy-btn" data-copy="{{ $ma->email_akun }}" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($ma->password_akun)
                    <div class="form-group">
                        <label>Password:</label>
                        <div class="input-group">
                            <input type="password" id="password-field" class="form-control" value="{{ $ma->password_akun }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="toggle-password" title="Show/Hide">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-secondary copy-btn" data-copy="{{ $ma->password_akun }}" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($ma->pin_code)
                    <div class="form-group">
                        <label>PIN Code:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $ma->pin_code }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary copy-btn" data-copy="{{ $ma->pin_code }}" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($ma->link_invite)
                    <div class="form-group">
                        <label>Link Invite:</label>
                        <a href="{{ $ma->link_invite }}" target="_blank" class="btn btn-info btn-block">
                            <i class="fas fa-external-link-alt"></i> Buka Link
                        </a>
                    </div>
                    @endif
                    
                    @if($ma->attachment)
                    <div class="form-group">
                        <label>File Attachment:</label>
                        <a href="{{ Storage::url($ma->attachment) }}" target="_blank" class="btn btn-secondary btn-block">
                            <i class="fas fa-file-download"></i> Download File
                        </a>
                    </div>
                    @endif
                    
                    @if($ma->instruksi_akses)
                    <hr>
                    <div class="form-group">
                        <label>Instruksi Akses:</label>
                        <div class="border p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                            {!! $ma->instruksi_akses !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @else
            {{-- Subscription not active or not paid --}}
            <div class="card card-warning">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lock"></i> Akses Terkunci
                    </h5>
                </div>
                <div class="card-body text-center">
                    @if($subscription->payment_status == 'unpaid')
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h6>Menunggu Pembayaran</h6>
                    <p class="text-muted">Silakan selesaikan pembayaran untuk mengakses kredensial</p>
                    @else
                    <i class="fas fa-ban fa-3x text-danger mb-3"></i>
                    <h6>Subscription Tidak Aktif</h6>
                    <p class="text-muted">Perpanjang langganan Anda untuk mengakses kembali</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aksi</h5>
                </div>
                <div class="card-body">
                    @if($subscription->status == 'expired' || $subscription->isExpiringSoon(7))
                    <a href="{{ route('customer.subscription.renew', $subscription) }}" class="btn btn-success btn-block">
                        <i class="fas fa-sync"></i> Perpanjang Langganan
                    </a>
                    @endif
                    
                    <a href="{{ route('customer.subscription.payments', $subscription) }}" class="btn btn-info btn-block">
                        <i class="fas fa-money-bill"></i> Riwayat Pembayaran
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
<script>
$(document).ready(function() {
    // Toggle password visibility
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

    // Copy to clipboard
    $('.copy-btn').on('click', function() {
        const text = $(this).data('copy');
        const btn = $(this);
        
        navigator.clipboard.writeText(text).then(function() {
            const originalHtml = btn.html();
            btn.html('<i class="fas fa-check"></i>');
            
            toastr.success('Berhasil disalin!');
            
            setTimeout(function() {
                btn.html(originalHtml);
            }, 2000);
        }).catch(function() {
            toastr.error('Gagal menyalin');
        });
    });
});
</script>
@stop
