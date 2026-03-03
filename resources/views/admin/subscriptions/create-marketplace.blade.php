@extends('adminlte::page')

@section('content')
@include('components.alert')
@canAccess('checkUserEmail','subscriptions')
@canAccess('storeMarketplace','subscriptions')
<div class="container-fluid py-3">
    <!-- Header with better spacing -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="page-title fw-semibold">
                <i class="fas fa-plus-circle me-2 text-primary"></i>Create Marketplace Subscription
            </h4>
            <p class="text-muted">Fill in the details to create a new subscription</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('subscription.store-marketplace') }}" method="POST">
        @csrf
        
        <!-- User Information Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 text-primary fw-semibold">
                    <i class="fas fa-user-circle me-2"></i>User Information
                </h5>
            </div>
            <div class="card-body">
                <!-- Jalur Pendaftaran (Radio) -->
                <div class="bg-light rounded p-3 mb-4">
                    <label class="form-label fw-semibold mb-2">Jalur Pendaftaran</label>
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check mr-5 mb-2">
                            <input class="form-check-input" type="radio" name="user_type" id="userTypeNew" value="new" {{ old('user_type', 'new') == 'new' ? 'checked' : '' }}>
                            <label class="form-check-label fw-medium" for="userTypeNew">
                                <i class="fas fa-user-plus me-1 text-success"></i>Buat User Baru
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="user_type" id="userTypeExisting" value="existing" {{ old('user_type') == 'existing' ? 'checked' : '' }}>
                            <label class="form-check-label fw-medium" for="userTypeExisting">
                                <i class="fas fa-user-check me-1 text-info"></i>Pilih User Lama
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Email and Name row -->
                <div class="row g-4 align-items-start">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-envelope me-1 text-secondary"></i> Email Address <span class="text-danger">*</span>
                        </label>
                        <div class="position-relative">
                            <input type="email" name="user_email" id="user_email" class="form-control form-control-lg" value="{{ old('user_email') }}" required placeholder="contoh@email.com">
                            <div class="spinner-border spinner-border-sm text-primary position-absolute" id="emailLoader" role="status" style="right: 15px; top: 50%; transform: translateY(-50%); display: none;"></div>
                        </div>
                        <div class="invalid-feedback" id="emailErrorMsg"></div>
                        <div class="valid-feedback" id="emailSuccessMsg"></div>
                        <small class="text-muted mt-1 d-block" id="emailStatusText">
                            <i class="far fa-lightbulb me-1"></i>Masukkan email user yang akan didaftarkan.
                        </small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user-tag me-1 text-secondary"></i> Full Name <span class="text-danger" id="nameRequiredStar">*</span>
                        </label>
                        <input type="text" name="user_name" id="user_name" class="form-control form-control-lg" value="{{ old('user_name') }}" required placeholder="Nama lengkap">
                    </div>
                </div>

                <!-- Password & Phone row (hidden for existing user) -->
                <div class="row g-4 mt-2" id="passwordPhoneRow">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-lock me-1 text-secondary"></i> Password <span class="text-danger">*</span>
                        </label>
                        <input type="password" name="user_password" id="user_password" class="form-control form-control-lg" required minlength="8" placeholder="Minimal 8 karakter">
                        <small class="text-muted">Minimal 8 karakter untuk keamanan</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-phone-alt me-1 text-secondary"></i> Phone Number <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <input type="text" name="user_phone" id="user_phone" class="form-control form-control-lg" value="{{ old('user_phone') }}" placeholder="Contoh: 08123456789">
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Detail Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 text-primary fw-semibold">
                    <i class="fas fa-box-open me-2"></i>Subscription Detail
                </h5>
            </div>
            <div class="card-body">
                <!-- Software & Package -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-cube me-1 text-secondary"></i>Software <span class="text-danger">*</span>
                        </label>
                        <select name="software_id" id="software_id" class="form-select form-select-lg select2" required>
                            <option value="">Pilih Software</option>
                            @foreach($softwares as $sw)
                                @php
                                    $hasSlots = $sw->availableMasterAccounts->isNotEmpty();
                                @endphp
                                <option value="{{ $sw->id }}" 
                                        data-packages="{{ json_encode($sw->packages) }}" 
                                        {{ old('software_id') == $sw->id ? 'selected' : '' }}
                                        @if(!$hasSlots) disabled="disabled" @endif>
                                    {{ $sw->nama }} {{ !$hasSlots ? '(Quota Penuh)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-boxes me-1 text-secondary"></i>Package <span class="text-danger">*</span>
                        </label>
                        <select name="package_id" id="package_id" class="form-select form-select-lg select2" required>
                            <option value="">Pilih Package</option>
                        </select>
                    </div>
                </div>

                <!-- Date and Order Reference row with background highlight -->
                <div class="row g-4 p-4 bg-light rounded-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-success">
                            <i class="fas fa-calendar-alt me-1"></i>Tanggal Mulai <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="tanggal_mulai" class="form-control form-control-lg" value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-danger">
                            <i class="fas fa-calendar-times me-1"></i>Tanggal Expired <span class="text-danger">*</span>
                        </label>
                        <input type="date" name="tanggal_expired" class="form-control form-control-lg" value="{{ old('tanggal_expired', date('Y-m-d', strtotime('+1 month'))) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-hashtag me-1 text-secondary"></i>Order Reference <span class="text-danger fw-normal">*</span>
                        </label>
                        <input type="text" name="order_reference" class="form-control form-control-lg" value="{{ old('order_reference') }}" placeholder="INV/2026/XI/12345" required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-3 mb-5">
            <a href="{{ route('subscription.index') }}" class="btn btn-outline-secondary btn-md px-4 shadow-sm mb-1 mr-1">
                <i class="fas fa-arrow-left me-2"></i>Batal
            </a>
            <button type="submit" class="btn btn-primary btn-md px-5 shadow-sm mb-1" id="btnSubmit">
                <i class="fas fa-check-circle me-2"></i>Create Subscription
            </button>
        </div>
    </form>
</div>

<!-- Add a little extra custom spacing if needed -->
<style>
    .form-control-lg, .form-select-lg {
        font-size: 1rem;
        padding: 0.75rem 1rem;
    }
    .card-header {
        background-color: #fafafa;
    }
    .bg-light {
        background-color: #f8f9fa !important;
    }
    .rounded-3 {
        border-radius: 0.5rem;
    }
    /* Better focus styles */
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }
    /* Validation feedback spacing */
    .invalid-feedback, .valid-feedback {
        margin-top: 0.25rem;
        font-size: 0.875rem;
    }
    /* Make sure select2 matches form-control-lg height */
    .select2-container--bootstrap-5 .select2-selection--single {
        height: calc(3rem + 2px) !important;
        padding: 0.75rem 1rem !important;
        font-size: 1rem !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 1.5 !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: calc(3rem - 2px) !important;
    }
</style>
@endsection

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        const softwareSelect = $('#software_id');
        const packageSelect = $('#package_id');
        const oldPackageId = "{{ old('package_id') }}";

        function updatePackages() {
            const selectedOption = softwareSelect.find('option:selected');
            packageSelect.empty().append('<option value="">Select Package</option>');
            
            if (selectedOption.val()) {
                const packagesData = selectedOption.attr('data-packages');
                const packages = JSON.parse(packagesData || '[]');
                
                packages.forEach(pkg => {
                    const selected = (oldPackageId == pkg.id) ? 'selected' : '';
                    packageSelect.append(`<option value="${pkg.id}" ${selected}>${pkg.nama_paket} (Rp ${new Intl.NumberFormat('id-ID').format(pkg.harga)})</option>`);
                });
            }
            packageSelect.trigger('change.select2');
        }

        softwareSelect.on('change', updatePackages);
        
        // Trigger on load for old data
        if (softwareSelect.val()) {
            updatePackages();
        }
        let emailCheckTimeout;

        function resetEmailValidation() {
            $('#user_email').removeClass('is-invalid is-valid');
            $('#emailErrorMsg, #emailSuccessMsg').hide();
        }

        function updateUserTypeView() {
            resetEmailValidation();
            const isNew = $('#userTypeNew').is(':checked');
            if (isNew) {
                $('#passwordPhoneRow').slideDown();
                $('#user_password').prop('required', true);
                $('#user_name').prop('readonly', false).val('');
                $('#user_phone').prop('readonly', false).val('');
                $('#emailStatusText').html('<i class="far fa-lightbulb me-1"></i>Email harus belum terdaftar.').show();
            } else {
                $('#passwordPhoneRow').slideUp();
                $('#user_password').prop('required', false);
                $('#user_name').prop('readonly', true).val('');
                $('#user_phone').prop('readonly', true).val('');
                $('#emailStatusText').html('<i class="far fa-lightbulb me-1"></i>Ketik perlahan untuk mencari user yang sudah ada.').show();
            }
            // Trigger check if there is an email typed already
            if ($('#user_email').val()) {
                checkEmailExistence($('#user_email').val());
            }
        }

        $('input[name="user_type"]').on('change', updateUserTypeView);
        
        $('#user_email').on('input', function() {
            clearTimeout(emailCheckTimeout);
            const email = $(this).val();

            resetEmailValidation();
            $('#user_name').val('');
            $('#user_phone').val('');
            
            if (!email) {
                const isNew = $('#userTypeNew').is(':checked');
                $('#emailStatusText').show();
                if(!isNew){
                    $('#user_name').prop('readonly', true);
                    $('#user_phone').prop('readonly', true);
                }
                return;
            }

            // Basic frontend email hint before hitting server
            if(email.length < 5 || countOccurrences(email, '@') !== 1 || !email.includes('.')) {
                 return; 
            }

            $('#emailLoader').show();
            $('#emailStatusText').hide();

            emailCheckTimeout = setTimeout(function() {
                checkEmailExistence(email);
            }, 500); // 500ms debounce
        });

        function checkEmailExistence(email) {
            $('#emailLoader').show();
            $.ajax({
                url: "{{ route('subscription.check-user-email') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    email: email
                },
                success: function(response) {
                    const isNew = $('#userTypeNew').is(':checked');
                    $('#emailLoader').hide();

                    if (isNew) {
                         // CREATE NEW USER MODE
                        if (response.exists) {
                            $('#user_email').addClass('is-invalid');
                            $('#emailErrorMsg').text('Email ini sudah terdaftar. Silakan gunakan Jalur "Pilih User Lama".').show();
                            $('#btnSubmit').prop('disabled', true);
                        } else {
                            $('#user_email').addClass('is-valid');
                            $('#emailSuccessMsg').text('Email tersedia untuk didaftarkan.').show();
                            $('#btnSubmit').prop('disabled', false);
                        }
                    } else {
                         // EXISTING USER MODE
                        if (response.exists) {
                            $('#user_email').addClass('is-valid');
                            $('#emailSuccessMsg').text('User ditemukan!').show();
                            $('#user_name').val(response.name);
                            $('#user_phone').val(response.phone);
                            $('#btnSubmit').prop('disabled', false);
                        } else {
                            $('#user_email').addClass('is-invalid');
                            $('#emailErrorMsg').text('User tidak ditemukan. Pastikan email terdaftar di sistem.').show();
                            $('#btnSubmit').prop('disabled', true);
                        }
                    }
                },
                error: function() {
                    $('#emailLoader').hide();
                    $('#user_email').addClass('is-invalid');
                    $('#emailErrorMsg').text('Terjadi kesalahan saat memeriksa email.').show();
                }
            });
        }

        // Helper string func for basic email check
        function countOccurrences(str, char) {
            return str.split(char).length - 1;
        }

        updateUserTypeView(); // trigger initial state
    });
</script>
@endpush
@endcanAccess
@endcanAccess