@section('title', $settingCompany['name'])
<div class="mt-5 mb-5 gradient">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body p-5">
                    <!-- Progress Bar -->
                    <div class="mb-5">
                        @include('components.alert')
                        <div class="progress-steps">
                            @foreach(['Alamat', 'Data Pribadi', 'Persetujuan', 'Pembayaran', 'Konfirmasi'] as $index => $title)
                                <div class="step-item {{ $step === $index + 1 ? 'active' : ($step > $index + 1 ? 'completed' : '') }}">
                                    <span class="step-number">Step {{ $index + 1 }}</span>
                                    <span class="step-title">{{ $title }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" role="progressbar" style="width: {{ ($step - 1) * 25 }}%" aria-valuenow="{{ ($step - 1) * 25 }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Step Content -->
                    <div>
                        @if($step === 1)
                            <!-- STEP 1: ALAMAT & PAKET -->
                            @include('livewire.internet-customer.steps.step1-address')
                        @endif

                        @if($step === 2)
                            <!-- STEP 2: DATA PRIBADI -->
                            @include('livewire.internet-customer.steps.step2-personal')
                        @endif

                        @if($step === 3)
                            <!-- STEP 3: TANDA TANGAN & PERSETUJUAN (DIPINDAH) -->
                            @include('livewire.internet-customer.steps.step3-signature')
                        @endif

                        @if($step === 4)
                            <!-- STEP 4: PEMBAYARAN (DIPINDAH) -->
                            @include('livewire.internet-customer.steps.step4-payment')
                        @endif

                        @if($step === 5)
                            <!-- STEP 5: KONFIRMASI -->
                            @include('livewire.internet-customer.steps.step5-confirmation')
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        // FIX 2: Script untuk Signature dan File Upload yang lebih robust
        document.addEventListener('livewire:load', function () {
            let signaturePad = null;
            let signatureCanvas = null;

            // Track upload state
            let uploadInProgress = false;
            let uploadQueue = [];

            // Override upload handlers
            window.addEventListener('livewire-upload-start', (event) => {
                console.log('Upload started:', event.detail);
                uploadInProgress = true;
                uploadQueue.push(event.detail.id);
            });

            window.addEventListener('livewire-upload-finish', (event) => {
                console.log('Upload finished:', event.detail);
                uploadQueue = uploadQueue.filter(id => id !== event.detail.id);
                
                if (uploadQueue.length === 0) {
                    uploadInProgress = false;
                    console.log('All uploads completed');
                }
            });

            window.addEventListener('livewire-upload-error', (event) => {
                console.error('Upload error:', event.detail);
                uploadQueue = uploadQueue.filter(id => id !== event.detail.id);
                uploadInProgress = false;
                
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Gagal',
                    text: 'Terjadi kesalahan saat mengupload file. Silakan coba lagi.'
                });
            });

            // Intercept next step clicks to ensure upload is complete
            document.addEventListener('click', function(e) {
                const nextButton = e.target.closest('[wire\\:click="nextStep"]');
                
                if (nextButton && uploadInProgress) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    Swal.fire({
                        icon: 'warning',
                        title: 'Upload Sedang Berlangsung',
                        text: 'Mohon tunggu hingga upload file selesai...',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    return false;
                }
            }, true);

            // FIX 2: Initialize Signature Pad dengan error handling
            function initSignaturePad() {
                const canvas = document.getElementById('signature-canvas');
                if (!canvas) {
                    console.log('Signature canvas not found');
                    return null;
                }
                
                signatureCanvas = canvas;
                
                // Cleanup existing instance
                if (signaturePad) {
                    signaturePad.off();
                    window.removeEventListener('resize', handleResize);
                }
                
                function handleResize() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    const container = canvas.parentElement;
                    
                    canvas.width = container.offsetWidth * ratio;
                    canvas.height = container.offsetHeight * ratio;
                    canvas.style.width = container.offsetWidth + 'px';
                    canvas.style.height = container.offsetHeight + 'px';
                    
                    const ctx = canvas.getContext('2d');
                    ctx.scale(ratio, ratio);
                    
                    if (signaturePad) {
                        signaturePad.clear();
                    }
                }
                
                handleResize();
                
                signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(0, 0, 0)',
                    minWidth: 1,
                    maxWidth: 3,
                    throttle: 16
                });
                
                window.addEventListener('resize', handleResize);
                
                console.log('Signature pad initialized');
                return signaturePad;
            }
            
            // FIX 2: Event handler untuk tombol Save Signature
            function attachSaveSignatureHandler() {
                const saveBtn = document.getElementById('save-signature');
                if (!saveBtn) return;
                
                // Remove existing listeners
                const newSaveBtn = saveBtn.cloneNode(true);
                saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);
                
                newSaveBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Save signature clicked');
                    
                    if (signaturePad && !signaturePad.isEmpty()) {
                        const signatureData = signaturePad.toDataURL();
                        console.log('Saving signature data');
                        @this.call('saveSignature', signatureData);
                    } else {
                        alert('Harap berikan tanda tangan Anda terlebih dahulu');
                    }
                });
                
                console.log('Save signature handler attached');
            }
            
            // FIX 2: Event handler untuk tombol Clear Signature  
            function attachClearSignatureHandler() {
                const clearBtn = document.getElementById('clear-signature');
                if (!clearBtn) return;
                
                // Remove existing listeners
                const newClearBtn = clearBtn.cloneNode(true);
                clearBtn.parentNode.replaceChild(newClearBtn, clearBtn);
                
                newClearBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Clear signature clicked');
                    
                    if (signaturePad) {
                        signaturePad.clear();
                        @this.call('clearSignature');
                    }
                });
                
                console.log('Clear signature handler attached');
            }
            
            // FIX 2: Event handler untuk tombol Re-sign
            function attachReSignHandler() {
                const resignBtn = document.getElementById('re-sign');
                if (!resignBtn) return;
                
                // Remove existing listeners
                const newResignBtn = resignBtn.cloneNode(true);
                resignBtn.parentNode.replaceChild(newResignBtn, resignBtn);
                
                newResignBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Re-sign clicked');
                    
                    if (signaturePad) {
                        signaturePad.clear();
                    }
                    
                    document.getElementById('signature-canvas-container')?.classList.remove('d-none');
                    document.getElementById('signature-preview-container')?.classList.add('d-none');
                    
                    @this.call('clearSignature');
                });
                
                console.log('Re-sign handler attached');
            }
            
            // FIX 2: Listen untuk event signature-saved dari Livewire
            Livewire.on('signature-saved', (data) => {
                console.log('Signature saved event received', data);
                
                const canvasContainer = document.getElementById('signature-canvas-container');
                const previewContainer = document.getElementById('signature-preview-container');
                const previewImage = document.getElementById('signature-preview-image');
                
                if (canvasContainer && previewContainer) {
                    canvasContainer.classList.add('d-none');
                    previewContainer.classList.remove('d-none');
                    
                    if (previewImage && @this.signature) {
                        previewImage.src = @this.signature;
                    }
                }
            });
            
            // FIX 2: Listen untuk event signature-cleared
            Livewire.on('signature-cleared', () => {
                console.log('Signature cleared event received');
                
                const canvasContainer = document.getElementById('signature-canvas-container');
                const previewContainer = document.getElementById('signature-preview-container');
                
                if (canvasContainer && previewContainer) {
                    canvasContainer.classList.remove('d-none');
                    previewContainer.classList.add('d-none');
                }
                
                if (signaturePad) {
                    signaturePad.clear();
                }
            });
            
            // Redirect to Xendit
            window.addEventListener('redirect-to-xendit', function(event) {
                setTimeout(() => {
                    window.location.href = event.detail.url;
                }, 1000);
            });

            // Redirect to Midtrans
            window.addEventListener('redirect-to-midtrans', function(event) {
                setTimeout(() => {
                    if (event.detail.redirect_url) {
                        window.location.href = event.detail.redirect_url;
                    }
                }, 1000);
            });

            // Initialize Select2
            function initSelect2() {
                $('.select2-single').each(function () {
                    const select = $(this);
                    const prop = select.attr('id');

                    select.select2({
                        placeholder: "-- Pilih --",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('.card-body')
                    });

                    select.off('change').on('change', function (e) {
                        const value = $(this).val();
                        if (@this[prop] != value) {
                            @this.set(prop, value);
                        }
                    });
                });
            }

            // Initialize on load
            initSelect2();
            
            // FIX 2: Initialize signature pad jika step 3
            if (@this.step === 3) {
                setTimeout(() => {
                    signaturePad = initSignaturePad();
                    attachSaveSignatureHandler();
                    attachClearSignatureHandler();
                    attachReSignHandler();
                    
                    // Restore signature if exists
                    if (@this.signature && signaturePad) {
                        try {
                            signaturePad.fromDataURL(@this.signature);
                            document.getElementById('signature-canvas-container')?.classList.add('d-none');
                            document.getElementById('signature-preview-container')?.classList.remove('d-none');
                            
                            const previewImage = document.getElementById('signature-preview-image');
                            if (previewImage) {
                                previewImage.src = @this.signature;
                            }
                        } catch (error) {
                            console.error('Error restoring signature:', error);
                        }
                    }
                }, 100);
            }

            // Re-initialize after Livewire updates
            Livewire.hook('message.processed', (message, component) => {
                // Reinit Select2
                initSelect2();

                $('.select2-single').each(function () {
                    const id = $(this).attr('id');
                    if (@this[id] !== undefined) {
                        $(this).val(@this[id]).trigger('change');
                    }
                });

                // FIX 2: Reinit signature pad pada step 3
                if (component.get('step') === 3) {
                    setTimeout(() => {
                        signaturePad = initSignaturePad();
                        attachSaveSignatureHandler();
                        attachClearSignatureHandler();
                        attachReSignHandler();
                        
                        // Restore signature if exists
                        if (component.get('signature') && signaturePad) {
                            try {
                                signaturePad.fromDataURL(component.get('signature'));
                                document.getElementById('signature-canvas-container')?.classList.add('d-none');
                                document.getElementById('signature-preview-container')?.classList.remove('d-none');
                                
                                const previewImage = document.getElementById('signature-preview-image');
                                if (previewImage) {
                                    previewImage.src = component.get('signature');
                                }
                            } catch (error) {
                                console.error('Error restoring signature:', error);
                            }
                        }
                    }, 100);
                }
            });
            
            // FIX 1: Handle file upload dengan loading indicator
            window.addEventListener('livewire-upload-start', () => {
                console.log('File upload started');
                // Bisa tambahkan loading spinner jika perlu
            });
            
            window.addEventListener('livewire-upload-finish', () => {
                console.log('File upload finished');
                // Remove loading spinner
            });
            
            window.addEventListener('livewire-upload-error', () => {
                console.error('File upload error');
                alert('Terjadi kesalahan saat mengupload file. Silakan coba lagi.');
            });
        });
    </script>
@endpush

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @include('livewire.internet-customer.steps.styles')
@endpush