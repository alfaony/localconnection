<div class="text-center py-5">
    <div class="mb-4">
        <div class="d-inline-flex bg-success bg-opacity-10 p-3 rounded-circle">
            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
        </div>
    </div>
    <h2 class="mb-3">Pendaftaran Berhasil!</h2>
    <p class="lead text-muted mb-4">
        @if($payment_method === 'xendit')
            Terima kasih! Anda akan segera diarahkan ke halaman pembayaran Xendit.
        @else
            Terima kasih telah mendaftar sebagai pelanggan kami. Tim kami akan segera menghubungi Anda.
        @endif
    </p>
    
    <div class="card border-0 shadow-sm mb-4 mx-auto" style="max-width: 500px;">
        <div class="card-body">
            <h3 class="h5 card-title mb-3">Detail Pendaftaran</h3>
            <div class="text-start">
                <p><strong>Nama:</strong> {{ $name }}</p>
                <p><strong>Paket:</strong> {{ $selectedPackage->name ?? '-' }}</p>
                <p><strong>Periode:</strong> {{ $payment_months }} bulan</p>
                @if(!$hasFreeMonthsPromo)
                    <p><strong>Total Pembayaran:</strong> Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
                @else
                    <p><strong>Total Pembayaran:</strong> <span class="text-success">GRATIS (Promo)</span></p>
                @endif
                <p><strong>Nomor Pelanggan:</strong> {{ $code }}</p>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="{{ route('internet-customer.customer.show', $code) }}" 
           class="btn-primary-red">
            Lihat Detail Pelanggan
        </a>
    </div>

    @if($payment_method === 'xendit')
        <div class="mt-3">
            <small class="text-muted">
                <i class="fas fa-spinner fa-spin mr-1"></i>
                Mengarahkan ke halaman pembayaran...
            </small>
        </div>
    @endif
</div>