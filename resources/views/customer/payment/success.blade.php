@extends('adminlte::page')

@section('title', 'Pembayaran Berhasil')

@section('content_header')
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-success">
                <div class="card-body text-center py-5">
                    <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                    <h1 class="text-success">Pembayaran Berhasil!</h1>
                    <p class="lead">Terima kasih atas pembayaran Anda</p>
                    
                    @if($subscription)
                    <div class="mt-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">Detail Pesanan</h5>
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <th width="200" class="text-right">Order Number:</th>
                                        <td class="text-left"><strong>{{ $subscription->order_number }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th class="text-right">Software:</th>
                                        <td class="text-left">{{ $subscription->masterAccount->software->nama }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-right">Package:</th>
                                        <td class="text-left">{{ $subscription->package->nama_paket }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-right">Total:</th>
                                        <td class="text-left"><strong>Rp {{ number_format($subscription->harga_bayar, 0, ',', '.') }}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if($subscription->payment_status == 'paid')
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-envelope"></i> Untuk credential bisa dilihat tatacara di laman, atau chat admin kami melalui room chat subscription
                    </div>
                    @else
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-hourglass-half"></i> Pembayaran sedang diproses. Silakan tunggu konfirmasi via email.
                    </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('customer-subscription.show', $subscription) }}" class="btn btn-success btn mr-2">
                            <i class="fas fa-eye"></i> Lihat Detail Subscription
                        </a>
                        <a href="{{ route('customer-subscription.index') }}" class="btn btn-outline-success btn">
                            <i class="fas fa-list"></i> My Subscriptions
                        </a>
                    </div>
                    @else
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i> Silakan cek email Anda untuk detail lebih lanjut.
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('customer-subscription.index') }}" class="btn btn-success btn">
                            <i class="fas fa-list"></i> My Subscriptions
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Additional Info --}}
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-question-circle"></i> Langkah Selanjutnya:</h5>
                    <ol>
                        <li>Silahkan Chat untuk menanyakan credential</li>
                        <li>Simpan informasi akses dengan aman</li>
                        <li>Login menggunakan kredensial yang diberikan</li>
                        <li>Hubungi admin jika ada kendala</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@stop


@section('js')
<script>
$(document).ready(function() {
    @if($subscription && $subscription->payment_status != 'paid')
    // Auto check payment status every 10 seconds
    let checkCount = 0;
    const maxChecks = 12; // Check for 2 minutes (12 * 10 seconds)
    
    const checkInterval = setInterval(function() {
        checkCount++;
        
        $.ajax({
            url: '{{ route("subscription-payment.check-status", $subscription->order_number ?? "none") }}',
            method: 'GET',
            success: function(response) {
                if (response.is_paid) {
                    clearInterval(checkInterval);
                    toastr.success('Pembayaran berhasil diverifikasi!');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            }
        });
        
        if (checkCount >= maxChecks) {
            clearInterval(checkInterval);
        }
    }, 10000); // Check every 10 seconds
    @endif
});
</script>
@stop
