@extends('adminlte::page')

@section('title', 'Checkout - ' . $software->nama)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-12">
            <h1 class="text-center">Checkout</h1>
        </div>
    </div>
@stop

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary">
                    <h5 class="mb-0">Review Pembelian Anda</h5>
                </div>
                <div class="card-body">
                    {{-- Software Info --}}
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            @if($software->logo)
                            <img src="{{ Storage::url($software->logo) }}" alt="{{ $software->nama }}" class="img-fluid" style="max-height: 100px;">
                            @else
                            <i class="fas fa-desktop fa-4x text-muted"></i>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h3>{{ $software->nama }}</h3>
                            <p class="text-muted">{{ $software->tipe_paket }}</p>
                            <span class="badge badge-info">{{ $package->nama_paket }}</span>
                            <span class="badge badge-secondary">{{ $package->durasi_hari }} hari</span>
                        </div>
                    </div>

                    <hr>

                    {{-- Order Summary --}}
                    <h5 class="mb-3">Ringkasan Pesanan</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Software</th>
                            <td>{{ $software->nama }} - {{ $software->tipe_paket }}</td>
                        </tr>
                        <tr>
                            <th>Paket</th>
                            <td>{{ $package->nama_paket }}</td>
                        </tr>
                        <tr>
                            <th>Durasi</th>
                            <td>{{ $package->durasi_hari }} hari ({{ $package->duration_in_months }} bulan)</td>
                        </tr>
                        <tr class="table-active">
                            <th>Total Pembayaran</th>
                            <td>
                                <h4 class="text-success mb-0">
                                    Rp {{ number_format($package->harga, 0, ',', '.') }}
                                </h4>
                            </td>
                        </tr>
                    </table>

                    <hr>

                    {{-- Important Info --}}
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Informasi Penting:</h6>
                        <ul class="mb-0">
                            <li>Setelah pembayaran berhasil, kredensial akses akan dikirim ke email Anda: <strong>{{ Auth::user()->email }}</strong></li>
                            <li>Akun bersifat sharing dengan user lain</li>
                            <li>Invoice pembayaran akan dikirim via email</li>
                            <li>Masa aktif dimulai setelah pembayaran terverifikasi</li>
                        </ul>
                    </div>

                    {{-- Form --}}
                    <form action="{{ route('customer.checkout.process', [$software->slug, $package->id]) }}" method="POST" id="checkout-form">
                        @csrf
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="agree_terms" name="agree_terms" required>
                                <label class="custom-control-label" for="agree_terms">
                                    Saya setuju dengan <a href="#" target="_blank">syarat dan ketentuan</a> yang berlaku
                                </label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('customer.softwares.show', $software->slug) }}" class="btn btn-secondary btn-block btn-lg">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-success btn-block btn-lg" id="btn-checkout">
                                    <i class="fas fa-credit-card"></i> Lanjutkan ke Pembayaran
                                </button>
                            </div>
                        </div>
                    </form>
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
    $('#checkout-form').on('submit', function() {
        const btn = $('#btn-checkout');
        btn.prop('disabled', true)
           .html('<i class="fas fa-spinner fa-spin"></i> Memproses...');
    });
});
</script>
@stop
