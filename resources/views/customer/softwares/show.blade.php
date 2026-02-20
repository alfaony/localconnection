@extends('adminlte::page')

@section('title', $software->nama . ' - ' . $software->tipe_paket)

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-12">
            <a href="{{ route('customer-software.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali ke Catalog
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    @if($software->logo)
                    <img src="{{ s3_asset(true,10,$software->logo) }}" alt="{{ $software->nama }}" class="img-fluid mb-3" style="max-height: 200px;">
                    @else
                    <div class="bg-light mb-3" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-desktop fa-4x text-muted"></i>
                    </div>
                    @endif
                    
                    <h3>{{ $software->nama }}</h3>
                    <p class="text-muted">{{ $software->tipe_paket }}</p>
                    
                    {{-- Slot Status --}}
                    @if($hasAvailableSlots)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Slot Tersedia
                    </div>
                    @else
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i> Slot Penuh
                        <br><small>Mohon hubungi admin atau coba lagi nanti</small>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Company Info --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Provider</h5>
                </div>
                <div class="card-body">
                    <p><strong>{{ $software->company->name }}</strong></p>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            {{-- Description --}}
            @if($software->description)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Tentang {{ $software->nama }}</h5>
                </div>
                <div class="card-body">
                    <p>{{ $software->description }}</p>
                </div>
            </div>
            @endif

            {{-- Packages --}}
            <div class="card">
                <div class="card-header bg-primary">
                    <h5 class="card-title mb-0">Pilih Paket Langganan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($software->activePackages as $package)
                        <div class="col-md-6 mb-3">
                            <div class="card {{ !$hasAvailableSlots ? 'bg-light' : '' }}">
                                <div class="card-body text-center">
                                    <h4 class="text-primary">{{ $package->nama_paket }}</h4>
                                    <hr>
                                    <div class="mb-3">
                                        <h2 class="text-success">
                                            Rp {{ number_format($package->harga, 0, ',', '.') }}
                                        </h2>
                                        <small class="text-muted">{{ $package->durasi_hari }} hari ({{ $package->duration_in_months }} bulan)</small>
                                    </div>
                                    
                                    @canAccess('show','customer_checkouts')
                                    @if($hasAvailableSlots)
                                    <a href="{{ route('customer-checkout.show', [$software->slug, $package->id]) }}" 
                                       class="btn btn-success btn-block btn-lg">
                                        <i class="fas fa-shopping-cart"></i> Beli Sekarang
                                    </a>
                                    @else
                                    <button class="btn btn-secondary btn-block btn-lg" disabled>
                                        <i class="fas fa-ban"></i> Slot Penuh
                                    </button>
                                    @endif
                                    @endcanAccess
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Belum ada paket tersedia untuk software ini.
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Info --}}
            <div class="card card-info">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Informasi Penting
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Setelah pembayaran berhasil, kredensial akses akan dikirim ke email Anda</li>
                        <li>Akun bersifat sharing dengan maksimal pengguna sesuai slot yang tersedia</li>
                        <li>Pastikan email Anda aktif untuk menerima informasi akses</li>
                        <li>Hubungi admin jika ada kendala</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
@stop
