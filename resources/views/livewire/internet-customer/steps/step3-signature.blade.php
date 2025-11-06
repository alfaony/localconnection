<h3 class="section-title">Persetujuan dan Tanda Tangan</h3>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Ringkasan Pendaftaran</h5>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nama:</strong> {{ $name }}</p>
                <p><strong>Email:</strong> {{ $email }}</p>
                <p><strong>Alamat:</strong> {{ $address }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Paket Internet:</strong> {{ $selectedPackage->name ?? '-' }}</p>
                <p><strong>Harga:</strong> Rp {{ number_format($selectedPackage->price_nett ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Syarat dan Ketentuan</h5>
        <div style="max-height: 300px; overflow-y: auto; border: 1px solid #eee; padding: 15px;">
            @if($agreement)
                @if(view()->exists('partnership_agreement.pdf.perjanjian_berlangganan_internet'))
                    @include('partnership_agreement.pdf.perjanjian_berlangganan_internet', ['agreement' => $agreement])
                @else
                    <p>Syarat dan ketentuan tidak tersedia.</p>
                @endif
            @else
                <p>Syarat dan ketentuan tidak tersedia.</p>
            @endif
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title mb-3">Tanda Tangan</h5>
        
        <!-- Canvas Area -->
        <div id="signature-canvas-container" class="{{ $signature ? 'd-none' : '' }}">
            <div class="alert alert-info mb-3">
                <i class="fas fa-info-circle me-2"></i> Silakan gambar tanda tangan Anda pada area persegi di bawah
            </div>
            
            <div class="d-flex flex-column align-items-center">
                <div id="signature-pad-container" 
                    class="border rounded bg-light position-relative mx-auto"
                    style="width: 400px; height: 300px;">
                    <canvas id="signature-canvas" 
                            style="width: 100%; height: 100%; touch-action: none; background-color: white;"></canvas>
                    <div class="position-absolute bottom-0 start-0 w-100 p-2 bg-light border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-danger" id="clear-signature">
                                <i class="fas fa-eraser me-1"></i> Hapus
                            </button>
                            <small class="text-muted">Gambar di dalam area kotak</small>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="button" id="save-signature" class="btn btn-success px-4 py-2">
                        <i class="fas fa-save me-2"></i> Simpan Tanda Tangan
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Preview Area -->
        <div id="signature-preview-container" class="{{ $signature ? '' : 'd-none' }}">
            <div class="alert alert-success mb-3">
                <i class="fas fa-check-circle me-2"></i> Tanda tangan Anda telah disimpan
            </div>
            
            <div class="d-flex flex-column align-items-center">
                <div class="mb-3 text-center">
                    <h6>Preview Tanda Tangan:</h6>
                    <p class="text-muted small">Tanda tangan akan muncul di dokumen perjanjian</p>
                </div>
                
                <div class="border rounded bg-white p-2 shadow-sm" style="width: 300px; height: 150px;">
                    <img id="signature-preview-image" 
                        src="{{ $signature }}" 
                        class="img-fluid h-100 w-100" style="object-fit: contain;">
                </div>
                
                <div class="mt-4">
                    <button type="button" id="re-sign" class="btn btn-outline-secondary px-4 py-2">
                        <i class="fas fa-redo me-2"></i> Gambar Ulang
                    </button>
                </div>
            </div>
        </div>
        
        @error('signature') 
            <div class="alert alert-danger mt-3">
                <i class="fas fa-exclamation-circle me-2"></i> {{ $message }}
            </div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-between mt-4">
    <button wire:click="prevStep" class="btn btn-outline-secondary px-4">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </button>
    <button 
        wire:click="nextStep"
        wire:loading.attr="disabled"
        wire:target="nextStep"
        class="btn-primary-red px-4"
    >
        <span wire:loading.remove wire:target="nextStep">
            Selanjutnya <i class="fas fa-arrow-right ms-2"></i>
        </span>
        <span wire:loading wire:target="nextStep">
            Memproses...
            <span class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true"></span>
        </span>
    </button>
</div>