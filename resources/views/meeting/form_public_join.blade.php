<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bergabung ke Meeting - {{ $meeting->meeting_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(120deg, #4b6cb7 0%, #182848 100%);
            color: white;
            padding: 25px 30px;
            border-bottom: none;
        }
        
        .meeting-icon {
            background: rgba(255, 255, 255, 0.2);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .form-control {
            border: 1px solid #e1e5eb;
            border-radius: 8px;
            padding: 14px 16px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #4b6cb7;
            box-shadow: 0 0 0 3px rgba(75, 108, 183, 0.15);
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #e1e5eb;
            border-radius: 8px 0 0 8px;
        }
        
        .btn-primary {
            background: linear-gradient(120deg, #4b6cb7 0%, #182848 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(75, 108, 183, 0.3);
        }
        
        .info-box {
            background-color: #f0f5ff;
            border-left: 4px solid #4b6cb7;
            border-radius: 8px;
            padding: 16px;
        }
        
        .meeting-name {
            font-weight: 700;
            color:rgb(220, 224, 232);
            background: linear-gradient(120deg, #f6f6f6 0%, #182848 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        @media (max-width: 768px) {
            .card {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header text-center">
                        <div class="meeting-icon mx-auto">
                            <i class="fas fa-video fa-2x"></i>
                        </div>
                        <h2 class="mb-3">Bergabung ke Meeting</h2>
                        <h3 class="meeting-name">{{ $meeting->meeting_name }}</h3>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <div id="alert-area" class="mb-4"></div>
                        
                        <div class="info-box mb-4">
                            <p class="mb-0"><i class="fas fa-info-circle me-2"></i>Silakan masukkan email dan kode public yang Anda terima untuk bergabung ke meeting ini.</p>
                        </div>
                        
                        <form id="publicJoinForm" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <label class="form-label">Alamat Email (Yang Digunakan Untuk Meeting)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" id="emailInput" class="form-control" placeholder="contoh@email.com" required>
                                </div>
                                <div class="form-text">Pastikan email yang Anda masukkan valid</div>
                                <div id="emailError" class="invalid-feedback d-block"></div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Kode Public</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="text" name="public_code" class="form-control" placeholder="Masukkan kode akses" required>
                                </div>
                                <div class="form-text">Kode 6-8 digit yang Anda terima</div>
                            </div>
                            
                            <div class="d-grid mt-5">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Gabung Sekarang
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="card-footer bg-transparent text-center py-3">
                        <p class="mb-0 text-muted">
                            <i class="fas fa-lock me-1"></i>Data Anda akan dilindungi dan tidak dibagikan ke pihak lain
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JS (harus bundle) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Sweet Alert 2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize Bootstrap Toast
        const successToast = new bootstrap.Toast(document.getElementById('successToast'), {
            autohide: true
        });
        
        // Email validation function
        function validateEmail(email) {
            const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        }
        
        // Handle form submission
        document.getElementById('publicJoinForm').addEventListener('submit', function (e) {
            e.preventDefault();
            
            const form = this;
            const formData = new FormData(form);
            const emailInput = document.getElementById('emailInput');
            const emailError = document.getElementById('emailError');
            const email = emailInput.value.trim();
            const submitButton = form.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            
            // Reset previous errors
            emailError.textContent = '';
            emailInput.classList.remove('is-invalid');
            
            // Validate email
            if (!validateEmail(email)) {
                emailInput.classList.add('is-invalid');
                emailError.textContent = 'Format email tidak valid';
                return;
            }
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            
            
            setTimeout(() => {
                // This would be your actual fetch implementation:
                fetch("{{ route('meeting.public.join.submit', [$meeting->slug, $meeting->public_token]) }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': formData.get('_token'),
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Show success toast
                        Swal.fire({
                            title: 'Berhasil',
                            text: data.message,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        }).then(() => {
                            // Redirect to meeting link if provided
                            if (data.redirect) 
                            {
                                window.location.href = data.redirect;
                            }
                        });
                        
                    } else {
                        // Show error message
                        document.getElementById('alert-area').innerHTML = `
                            <div class="alert alert-danger">${data.message}</div>
                        `;
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalButtonText;
                    }
                })
                .catch(err => {
                    console.log(err);
                    
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                    document.getElementById('alert-area').innerHTML = `
                        <div class="alert alert-danger">Terjadi kesalahan. Silakan coba lagi.</div>
                    `;
                });
    
                
                // Reset button after showing toast
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                // Simulate redirect after toast
                
            }, 1000);

                

        });
    </script>
</body>
</html>