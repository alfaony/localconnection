
<div class="row justify-content-center align-items-center py-5">
    <div class="col-12 col-md-8 col-lg-6 col-xl-5">
        
        <!-- Main Card -->
        <div class="card shadow-lg border-0 rounded-lg overflow-hidden">
            
            <!-- Header with Gradient -->
            <div class="card-header bg-gradient-primary text-white text-center py-5">
                <div class="mb-3">
                    <i class="fas fa-user-check fa-4x opacity-75"></i>
                </div>
                <h2 class="mb-2 font-weight-bold">Portal Pelanggan</h2>
                <p class="mb-0 opacity-90">Masukkan kode pelanggan Anda untuk melanjutkan</p>
            </div>

            <!-- Body -->
            <div class="card-body p-4 p-md-5">
                
                <!-- Error Alert -->
                @if($error_message)
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle fa-2x mr-3"></i>
                        <div>
                            <strong>Oops!</strong>
                            <p class="mb-0">{{ $error_message }}</p>
                        </div>
                    </div>
                    <button type="button" class="close" wire:click="clearInput" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                @endif

                <!-- Form -->
                <form wire:submit.prevent="checkCustomer">
                    
                    <!-- Input Group -->
                    <div class="form-group mb-4">
                        <label for="customer_code" class="form-label font-weight-bold text-dark mb-3">
                            <i class="fas fa-barcode mr-2 text-primary"></i>Kode Pelanggan
                        </label>
                        
                        <div class="input-group input-group-lg">
                            <input 
                                type="text" 
                                class="form-control form-control-lg border-left-0 @error('customer_code') is-invalid @enderror" 
                                id="customer_code"
                                wire:model.defer="customer_code"
                                placeholder="Contoh: CUST-001"
                                autocomplete="off"
                                autofocus
                                {{ $is_loading ? 'disabled' : '' }}>
                            
                            @if($customer_code)
                            <div class="input-group-append">
                                <button 
                                    class="btn btn-outline-secondary" 
                                    type="button"
                                    wire:click="clearInput"
                                    {{ $is_loading ? 'disabled' : '' }}>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            @endif
                        </div>
                        
                        @error('customer_code')
                        <div class="invalid-feedback d-block mt-2">
                            <i class="fas fa-info-circle mr-1"></i>{{ $message }}
                        </div>
                        @enderror

                        <!-- Helper Text -->
                        <small class="form-text text-muted mt-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Kode pelanggan dapat ditemukan pada dokumen perjanjian atau invoice Anda
                        </small>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="btn btn-primary btn-block shadow-sm mb-3"
                        {{ $is_loading ? 'disabled' : '' }}>
                        @if($is_loading)
                            <span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                            <span>Memverifikasi...</span>
                        @else
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            <span>Akses Portal</span>
                        @endif
                    </button>

                    <!-- Additional Info -->
                    <div class="text-center mt-4 pt-3 border-top">
                        <p class="text-muted mb-2">
                            <i class="fas fa-question-circle mr-1"></i>
                            Tidak menemukan kode pelanggan Anda?
                        </p>
                        <a href="https://ticket.thrive.co.id" target="_blank" class="btn btn-outline-secondary btn-sm" data-toggle="modal" data-target="#helpModal">
                            <i class="fas fa-headset mr-2"></i>Hubungi Admin
                        </a>
                    </div>

                </form>

            </div>

            <!-- Footer -->
            <div class="card-footer bg-light text-center py-3">
                <small class="text-muted">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Portal aman dan terenkripsi
                </small>
            </div>

        </div>

        <!-- Features Info -->
        <div class="row mt-4">
            <div class="col-md-4 mb-3">
                <div class="text-center">
                    <div class="feature-icon bg-primary text-white rounded-circle mx-auto mb-2">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <small class="text-muted">Akses Cepat</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="text-center">
                    <div class="feature-icon bg-success text-white rounded-circle mx-auto mb-2">
                        <i class="fas fa-lock"></i>
                    </div>
                    <small class="text-muted">Aman & Privat</small>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="text-center">
                    <div class="feature-icon bg-info text-white rounded-circle mx-auto mb-2">
                        <i class="fas fa-clock"></i>
                    </div>
                    <small class="text-muted">24/7 Tersedia</small>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="helpModalLabel">
                    <i class="fas fa-question-circle mr-2"></i>Bantuan
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h6 class="font-weight-bold mb-3">Cara Menemukan Kode Pelanggan:</h6>
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <i class="fas fa-file-contract text-primary mr-2"></i>
                        <strong>Dokumen Perjanjian:</strong> Cek bagian atas dokumen perjanjian Anda
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-receipt text-success mr-2"></i>
                        <strong>Invoice/Tagihan:</strong> Kode pelanggan tertera di setiap invoice
                    </li>
                    <li class="mb-3">
                        <i class="fas fa-envelope text-info mr-2"></i>
                        <strong>Email Aktivasi:</strong> Cek email aktivasi saat pertama kali mendaftar
                    </li>
                </ul>

                <hr>

                <h6 class="font-weight-bold mb-3">Hubungi Admin:</h6>
                <div class="contact-info">
                    <div class="d-flex align-items-center mb-3">
                        <div class="contact-icon bg-success text-white rounded-circle mr-3">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">WhatsApp</small>
                            <a href="https://wa.me/6281234567890" target="_blank" class="font-weight-bold">
                                +62 812-3456-7890
                            </a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="contact-icon bg-primary text-white rounded-circle mr-3">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Email</small>
                            <a href="mailto:admin@company.com" class="font-weight-bold">
                                admin@company.com
                            </a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="contact-icon bg-info text-white rounded-circle mr-3">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Telepon</small>
                            <a href="tel:+6281234567890" class="font-weight-bold">
                                (024) 123-4567
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
@include('livewire.internet-customer.steps.styles')
<style>
    /* Card Styling */
    .card {
        border-radius: 20px !important;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.2) !important;
    }

    /* Gradient Header */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #f4f4f5ff 0%, #DB2328 100%) !important;
    }

    /* Input Styling */
    #customer_code {
        height: 60px;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    #customer_code:focus {
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        border-color: #667eea;
    }

    .input-group-text {
        border-radius: 10px 0 0 10px;
        background-color: #f8f9fa;
    }

    #customer_code {
        width: 100%;
        max-width: 100%;
        border-radius: 0 10px 10px 0;
    }

    /* Button Styling */
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 10px;
        /* padding: 15px; */
        /* font-size: 1.1rem; */
        /* font-weight: 600; */
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        background: #ce6d6dff;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    }

    .btn-primary:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    /* Feature Icons */
    .feature-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    /* Contact Icons in Modal */
    .contact-icon {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    /* Alert Styling */
    .alert {
        border-radius: 10px;
        border-left: 4px solid;
    }

    .alert-danger {
        border-left-color: #dc3545;
        background-color: #fff5f5;
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 15px;
        border: none;
    }

    .modal-header {
        border-radius: 15px 15px 0 0;
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeIn 0.6s ease-out;
    }

    /* Loading Spinner */
    .spinner-border-sm {
        width: 1.2rem;
        height: 1.2rem;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .card-header h2 {
            font-size: 1.5rem;
        }

        .form-control-lg {
            height: 50px;
            font-size: 1rem !important;
        }

        .btn-primary {
            font-size: 1rem;
            padding: 12px;
        }

        .feature-icon {
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
        }
    }

    /* Card Footer */
    .card-footer {
        border-radius: 0 0 20px 20px;
        background-color: #f8f9fa;
    }

    /* Hover Effects */
    .btn-outline-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Focus visible for accessibility */
    .btn:focus,
    .form-control:focus {
        outline: none;
    }

    .btn-primary {
        background: #DB2328;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('livewire:load', function() {
    // Auto-focus on input when page loads
    document.getElementById('customer_code')?.focus();

    // Convert input to uppercase as user types
    const codeInput = document.getElementById('customer_code');
    if (codeInput) {
        codeInput.addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    }

    // Handle enter key press
    document.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && document.getElementById('customer_code') === document.activeElement) {
            e.preventDefault();
            @this.call('checkCustomer');
        }
    });
});

// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
    const alert = document.querySelector('.alert');
    if (alert) {
        $(alert).fadeOut('slow');
    }
}, 5000);
</script>
@endpush