@extends('adminlte::page')

@section('title', 'Pembayaran Gagal')

@section('content_header')
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-danger">
                <div class="card-body text-center py-5">
                    <i class="fas fa-times-circle fa-5x text-danger mb-4"></i>
                    <h1 class="text-danger">Pembayaran Gagal</h1>
                    <p class="lead">Maaf, pembayaran Anda tidak dapat diproses</p>
                    
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

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Kemungkinan Penyebab:</strong>
                        <ul class="text-left mt-2 mb-0">
                            <li>Pembayaran dibatalkan</li>
                            <li>Waktu pembayaran habis</li>
                            <li>Kesalahan teknis</li>
                            <li>Saldo tidak mencukupi</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('customer.subscriptions.show', $subscription) }}" class="btn btn-primary btn-lg mr-2">
                            <i class="fas fa-redo"></i> Coba Lagi
                        </a>
                        <a href="{{ route('customer.subscriptions.index') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-list"></i> My Subscriptions
                        </a>
                    </div>
                    @else
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i> Pesanan tidak ditemukan. Silakan hubungi admin jika ada masalah.
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('customer.softwares.index') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart"></i> Coba Berlangganan Lagi
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Help Info --}}
            <div class="card">
                <div class="card-header bg-info">
                    <h5 class="mb-0"><i class="fas fa-question-circle"></i> Butuh Bantuan?</h5>
                </div>
                <div class="card-body">
                    <p>Jika Anda mengalami kesulitan atau memiliki pertanyaan, silakan:</p>
                    <ul>
                        <li>Pastikan informasi pembayaran Anda benar</li>
                        <li>Coba metode pembayaran lain</li>
                        <li>Hubungi customer support kami</li>
                        <li>Cek email untuk informasi lebih lanjut</li>
                    </ul>
                    
                    <div class="mt-3">
                        <a href="mailto:support@example.com" class="btn btn-info">
                            <i class="fas fa-envelope"></i> Hubungi Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
