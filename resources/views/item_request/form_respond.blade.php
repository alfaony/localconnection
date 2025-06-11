<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respon Permintaan Barang</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #e5383b;
            --light: #f8f9fa;
            --dark: #212529;
            --card-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e7f1 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }
        
        .container-custom {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .header-card {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .header-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1607082350899-7e105aa886ae?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80') center/cover;
            opacity: 0.15;
        }
        
        .header-content {
            position: relative;
            padding: 30px;
            text-align: center;
        }
        
        .header-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            backdrop-filter: blur(5px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .header-icon i {
            font-size: 2.5rem;
            color: white;
        }
        
        .card-custom {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
            margin-bottom: 25px;
            background-color: white;
        }
        
        .card-custom:hover {
            transform: translateY(-5px);
        }
        
        .card-header-custom {
            background: white;
            color: var(--primary);
            padding: 20px 25px;
            font-weight: 700;
            font-size: 1.25rem;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        
        .card-body-custom {
            padding: 25px;
        }
        
        .item-img-container {
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .item-img {
            width: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .item-img-container:hover .item-img {
            transform: scale(1.05);
        }
        
        .detail-item {
            display: flex;
            margin-bottom: 18px;
            padding-bottom: 18px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .detail-label {
            flex: 0 0 200px;
            font-weight: 600;
            color: #495057;
            display: flex;
            align-items: center;
        }
        
        .detail-label i {
            margin-right: 10px;
            color: var(--primary);
            width: 24px;
            text-align: center;
        }
        
        .detail-value {
            flex: 1;
            color: #212529;
            font-weight: 500;
        }
        
        .price-highlight {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
            background: rgba(67, 97, 238, 0.1);
            padding: 8px 15px;
            border-radius: 8px;
            display: inline-block;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 0.7rem;
            color: #495057;
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 8px;
            color: var(--primary);
        }
        
        .price-input-group {
            position: relative;
        }
        
        .price-input {
            padding-left: 45px;
            font-size: 1.1rem;
            height: 50px;
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .price-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
        }
        
        .price-input-group span {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-weight: 600;
            font-size: 1.1rem;
            z-index: 10;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            padding: 14px 30px;
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            width: 100%;
            display: block;
            margin: 30px auto 0;
            max-width: 300px;
            box-shadow: 0 5px 20px rgba(67, 97, 238, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4);
        }
        
        .btn-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }
        
        .btn-submit:hover::before {
            left: 100%;
        }
        
        .alert-custom {
            border-radius: 12px;
            border: none;
            box-shadow: var(--card-shadow);
            padding: 20px;
            display: flex;
            align-items: flex-start;
        }
        
        .alert-icon {
            font-size: 1.8rem;
            margin-right: 15px;
        }
        
        .status-badge {
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
        }
        
        .status-open {
            background: rgba(76, 201, 240, 0.2);
            color: var(--primary);
        }
        
        .status-closed {
            background: rgba(229, 56, 59, 0.2);
            color: var(--danger);
        }
        
        .status-responded {
            background: rgba(56, 193, 114, 0.2);
            color: #38c172;
        }
        
        .validation-feedback {
            display: flex;
            align-items: center;
            margin-top: 8px;
            font-weight: 500;
        }
        
        .validation-feedback i {
            margin-right: 7px;
        }
        
        .note-textarea {
            min-height: 120px;
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            padding: 15px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .note-textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.15);
            outline: none;
        }
        
        @media (max-width: 768px) {
            .item-img-container {
                height: 200px;
            }
            .detail-item {
                flex-direction: column;
            }
            
            .detail-label {
                margin-bottom: 8px;
            }
            
            .container-custom {
                padding: 0 10px;
            }
            
            .header-content {
                padding: 20px 15px;
            }
            
            .card-body-custom {
                padding: 20px;
            }
        }
        @media (max-width: 768px) 
        {
            .detail-label {
                flex: 0 0 auto;
                width: 100%;
                margin-bottom: 4px;
            }

            .detail-value {
                width: 100%;
                flex: 0 0 auto;
            }

            .detail-row {
                display: flex;
                flex-direction: column;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Card -->
         <div class="row">
            {{-- 
             <div class="header-card mb-1 mt-0">
                 <div class="header-content">
                     <div class="header-icon">
                         <i class="bi bi-cart-check"></i>
                     </div>
                     <h5 class="fw-bold">Respon Permintaan Barang</h5>
                 </div>
             </div>
             --}}
     
             <div class="alert-container">
                 @include('components.alert')
             </div>
             
             <!-- Status Indicator -->
             <div class="d-flex justify-content-center mb-2">
                 
                 @if($potentialVendor->responded && $potentialVendor->responded_at)
                 <span class="status-badge status-responded">
                     <i class="bi bi-check-circle me-2"></i>Sudah Menawarkan
                 </span>
                 @elseif(!$itemRequest->is_open)
                 <span class="status-badge status-closed">
                     <i class="bi bi-lock me-2"></i>Permintaan Ditutup
                 </span>
     
                 @elseif(!!$potentialVendor->responded && !!$potentialVendor->responded_at)
                 <span class="status-badge status-open">
                     <i class="bi bi-unlock me-2"></i> Permintaan Masih Terbuka
                 </span>
                 @endif
             </div>
             
             @if($itemRequest->is_open || $potentialVendor->responded )
             <!-- Item Image -->
             <div class="card">
                <div class="card-header-custom">
                     <i class="bi bi-cart-check me-2"></i>Respon Permintaan Barang
                 </div>
                 <div class="item-img-container">
                     <img src="{{ Storage::url($itemRequest->picture) }}" 
                          alt="{{ $itemRequest->name }}" class="item-img">
                 </div>
             </div>
             
             <!-- Item Details -->
             <div class="card-custom">
                 <div class="card-header-custom">
                     <i class="bi bi-box-seam me-2"></i>Detail Permintaan
                 </div>
                 <div class="card-body-custom">
                     <div class="detail-item">
                         <div class="detail-label">
                             <i class="bi bi-tag"></i>Nama Barang
                         </div>
                         <div class="detail-value">{{ $itemRequest->item_name }}</div>
                     </div>
                     
                     <div class="detail-item d-none">
                         <div class="detail-label">
                             <i class="bi bi-upc-scan"></i>Kode Permintaan
                         </div>
                         <div class="detail-value">{{ $potentialVendor->response_token  ?? "-" }}</div>
                     </div>
                     
                     <div class="detail-item d-none">
                         <div class="detail-label">
                             <i class="bi bi-calendar"></i>Tanggal Permintaan
                         </div>
                         <div class="detail-value">{{ $itemRequest->created_at->format('d F Y') }}</div>
                     </div>
                     
                     <div class="detail-item">
                         <div class="detail-label">
                             <i class="bi bi-boxes"></i>Jumlah
                         </div>
                         <div class="detail-value">{{ $itemRequest->qty }} unit</div>
                     </div>
                     
                     <div class="detail-item">
                         <div class="detail-label">
                             <i class="bi bi-currency-exchange"></i>Harga Maksimum
                         </div>
                         <div class="detail-value">
                             <span class="price-highlight">Rp {{ number_format($itemRequest->estimated_price,0,',','.') }}</span>
                         </div>
                     </div>
                     
                     <div class="detail-item mb-0 pb-0">
                         <div class="detail-label">
                             <i class="bi bi-card-text"></i>Deskripsi
                         </div>
                         <div class="detail-value">
                             {!! $itemRequest->description !!}
                         </div>
                     </div>
                     
                     @if($potentialVendor->price_offered)
                     <div class="detail-item mt-2">
                         <div class="detail-label">
                             <i class="bi bi-cash"></i>Harga Penawaran
                         </div>
                         <div class="detail-value">
                             <span class="price-highlight">Rp {{ number_format($potentialVendor->price_offered ?? 0,0,',','.') }}</span>
                         </div>
                     </div>
                     @endif
                     
                     <div class="detail-item mb-0 pb-0 d-none">
                         <div class="detail-label mb-2">
                             <i class="bi bi-check-circle"></i>Status
                         </div>
                         <div class="detail-value mb-2">
                             <span class="badge bg-{{ $potentialVendor->responded ? 'success' : 'warning' }} text-white">
                                 {{ $potentialVendor->responded ? 'Sudah Ditawarkan' : 'Belum Ditawarkan' }}
                             </span>
                         </div>
                     </div>
                     @if($potentialVendor->notes)
                     <div class="detail-item mb-0 pb-0">
                         <div class="detail-label">
                             <i class="bi bi-card-text"></i>Catatan
                         </div>
                         <div class="detail-value mb-2 mt-2">
                             {!! $potentialVendor->notes ?? '-' !!}
                         </div>
                     </div>
                     @endif
                 </div>
             </div>
             
             <!-- Response Form -->
              @if(!$potentialVendor->responded)
             <div class="card-custom">
                 <div class="card-header-custom">
                     <i class="bi bi-pencil-square me-2"></i>Form Penawaran
                 </div>
                 <div class="card-body-custom">
                     <div class="alert alert-primary alert-custom">
                         <div class="alert-icon">
                             <i class="bi bi-info-circle-fill"></i>
                         </div>
                         <div>
                             <p class="mb-0 fw-bold">
                                 Pastikan harga tidak melebihi harga maksimum. Penawaran tidak dapat diubah.
                             </p>
                         </div>
                     </div>
     
                     <form method="POST" action="{{ route('vendor.respond.submit', [$vendor->id, $vendor->response_token]) }}">
                         @csrf
                         <div class="mb-2">
                             <label class="form-label">
                                 <i class="bi bi-currency-dollar"></i>Harga Penawaran
                             </label>
                             <div class="price-input-group">
                                 <span>Rp</span>
                                 <input type="number" 
                                        id="price_offered"
                                        name="price_offered"
                                        class="form-control price-input" 
                                        placeholder="Masukkan harga penawaran"
                                        max="{{  $itemRequest->estimated_price }}"
                                        required>
                             </div>
                             
                             <div class="validation-feedback text-danger" id="priceFeedback">
                                 <i class="bi bi-exclamation-circle"></i> 
                                 <span>Harga tidak boleh melebihi  Rp. {{number_format($itemRequest->estimated_price,0,',','.')}}</span>
                             </div>
                             
                         </div>
                         
                         <div class="mb-2">
                             <label class="form-label">
                                 <i class="bi bi-card-text"></i>Catatan Tambahan
                                 <span class="text-muted ms-1">(Opsional)</span>
                             </label>
                             <textarea id="note" 
                                       name="note" 
                                       class="form-control note-textarea" 
                                       placeholder="Berikan catatan tambahan tentang penawaran Anda (misal: kondisi barang, garansi, dll)"
                                       rows="4"></textarea>
                         </div>
                         
                         <button type="submit" class="btn btn-submit text-white">
                             <i class="bi bi-send-check me-2"></i>Kirim Penawaran
                         </button>
                     </form>
                 </div>
             </div>
             @endif
             @endif
         </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() 
        {
            const priceInput = document.getElementById('price_offered');
            const priceFeedback = document.getElementById('priceFeedback');
            const priceProgress = document.getElementById('priceProgress');
            const maxPrice = parseInt(document.getElementById('price_offered').getAttribute('max')) || 0;
            
            // Format input harga secara real-time
            priceInput.addEventListener('input', function() {
                const enteredPrice = parseInt(this.value) || 0;
                
                // Validasi harga maksimum
                if (enteredPrice > maxPrice) {
                    this.value = maxPrice;
                    priceFeedback.innerHTML = '<i class="bi bi-x-circle"></i> Harga melebihi batas maksimum!';
                    priceFeedback.classList.remove('text-success');
                    priceFeedback.classList.add('text-danger');
                } else if (enteredPrice === 0) {
                    priceFeedback.innerHTML = '<i class="bi bi-exclamation-circle"></i> Masukkan harga penawaran';
                    priceFeedback.classList.remove('text-success');
                    priceFeedback.classList.add('text-danger');
                } else {
                    const discountPercent = Math.round((1 - enteredPrice / maxPrice) * 100);
                    const discountText = discountPercent > 0 ? 
                        ` (Diskon ${discountPercent}% dari harga maksimum)` : '';
                    
                    priceFeedback.innerHTML = `<i class="bi bi-check-circle"></i> Harga valid`;
                    priceFeedback.classList.remove('text-danger');
                    priceFeedback.classList.add('text-success');
                }
                
                // Update progress bar
                const progressPercent = Math.min(Math.round((enteredPrice / maxPrice) * 100), 100);
            });
            
            // Form submission validation
            document.getElementById('responseForm').addEventListener('submit', function(e) 
            {
                const enteredPrice = parseInt(priceInput.value) || 0;
                
                if (enteredPrice > maxPrice) {
                    e.preventDefault();
                    priceInput.focus();
                    
                    // Show error animation
                    priceInput.classList.add('is-invalid');
                    setTimeout(() => {
                        priceInput.classList.remove('is-invalid');
                    }, 2000);
                    
                    priceFeedback.innerHTML = '<i class="bi bi-x-circle"></i> Harga masih melebihi batas maksimum!';
                    priceFeedback.classList.remove('text-success');
                    priceFeedback.classList.add('text-danger');
                }
            });
            
            // Initialize progress bar
            priceInput.dispatchEvent(new Event('input'));
        });
    </script>
</body>
</html>