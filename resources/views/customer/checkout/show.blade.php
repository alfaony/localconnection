@extends('adminlte::page')

@section('title', 'Checkout - ' . $software->nama)

@section('content_header')
    <div class="mb-4">
        <a href="{{ route('customer-software.show', $software->slug) }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Layanan Software
        </a>
    </div>
@stop

@section('content')
<div class="custom-container">
    {{-- Top Radial Gradient Premium Banner --}}
    <div class="checkout-hero-banner mb-4 slide-up-1">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h2 class="mb-1 font-weight-bold"><i class="fas fa-shopping-cart mr-2"></i> Selesaikan Pembayaran Anda</h2>
                <p class="mb-0 text-white-50">Langkah terakhir untuk menikmati layanan premium {{ $software->nama }}.</p>
            </div>
            <div>
                <span class="custom-badge payment-safe-badge"><i class="fas fa-shield-alt mr-1"></i> Pembayaran Aman 100%</span>
            </div>
        </div>
    </div>

    <div class="modern-two-col">
        {{-- Left Column: Review Pembelian & Order Summary --}}
        <div class="left-col" style="flex: 1.5;">
            <div class="modern-card slide-up-2 border-primary-top">
                <h4 class="card-heading">Rincian Pembelian</h4>
                <div class="d-flex align-items-center mb-4 p-3 bg-light rounded" style="border: 1px solid var(--border);">
                    <div class="flex-shrink-0 mr-4">
                        @if($software->logo)
                        <img src="{{ s3_asset(true, 10, $software->logo) }}" 
                             alt="{{ $software->nama }}" 
                             class="detail-logo m-0" style="width: 80px; height: 80px;">
                        @else
                        <div class="detail-logo-placeholder m-0" style="width: 80px; height: 80px; font-size: 32px;">
                            <i class="fas fa-desktop"></i>
                        </div>
                        @endif
                    </div>
                    <div>
                        <h3 class="detail-title mb-1" style="font-size: 1.35rem;">{{ $software->nama }}</h3>
                        <div class="text-muted mb-2 font-weight-bold" style="font-size: 14px;">{{ $software->tipe_paket }}</div>
                        <span class="custom-badge status-primary d-inline-block">{{ $package->nama_paket }}</span>
                        <span class="custom-badge status-default d-inline-block">{{ $package->durasi_hari }} hari</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table modern-table border-top mt-3 mb-0">
                        <tr>
                            <th width="180" class="text-muted border-top-0 border-bottom">Software</th>
                            <td class="border-top-0 border-bottom text-right"><strong class="text-dark">{{ $software->nama }}</strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted border-bottom">Paket Lengkap</th>
                            <td class="border-bottom text-right"><strong class="text-dark">{{ $package->nama_paket }} ({{ $package->duration_in_months }} Bulan)</strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted border-bottom">Subtotal</th>
                            <td class="border-bottom text-right"><strong class="text-dark" style="font-size: 1.1rem;">Rp {{ number_format($ppnCalculation['subtotal'], 0, ',', '.') }}</strong></td>
                        </tr>
                        @if($ppnCalculation['ppn_rate'] > 0)
                        <tr>
                            <th class="text-muted border-bottom">PPN ({{ number_format($ppnCalculation['ppn_rate'], 0) }}%)</th>
                            <td class="border-bottom text-right"><strong class="text-dark">Rp {{ number_format($ppnCalculation['ppn_amount'], 0, ',', '.') }}</strong></td>
                        </tr>
                        @endif
                        <tr style="background: var(--primary-l); border-radius: 12px;">
                            <th class="align-middle border-bottom-0" style="color: var(--primary-d); font-size: 1.15rem; padding-left: 16px;">Total Pembayaran</th>
                            <td class="border-bottom-0 text-right" style="padding-right: 16px;">
                                <h4 class="mb-0 font-weight-bold" style="color: var(--primary); font-size: 1.5rem;">
                                    Rp {{ number_format($ppnCalculation['total'], 0, ',', '.') }}
                                </h4>
                            </td>
                        </tr>
                    </table>
                </div>
                
                {{-- Important Info --}}
                <div class="status-alert warning mt-4">
                    <i class="fas fa-info-circle icon" style="font-size: 20px; color: var(--primary); margin-top: 2px;"></i>
                    <div style="font-size: 13.5px;">
                        <ul class="mb-0 pl-3 text-dark">
                            <li>Akun bersifat sharing dengan user lain secara aman.</li>
                            <li>Masa aktif dimulai setelah pembayaran Anda berhasil terverifikasi.</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            @canAccess('process','customer_checkouts')
            {{-- Terms Agreement --}}
            <div class="modern-card mt-4 slide-up-3">
                <h4 class="card-heading"><i class="fas fa-file-contract mr-2 text-primary"></i> Syarat dan Ketentuan</h4>
                <p class="text-muted" style="font-size: 14px;">Silakan baca syarat dan ketentuan di bawah ini sampai paling bawah untuk dapat menyetujuinya.</p>
                <div class="tnc-container p-3 border rounded text-dark mb-4" id="tnc-box" style="height: 250px; overflow-y: scroll; font-size: 13.5px; transition: all 0.3s; background: #f8fafc;">
                    @php
                        $tncText = $settingCompany['software_sharing_term_and_condition'] ?? '';
                    @endphp
                    @if(!empty($tncText))
                        {!! $tncText !!}
                    @else
                        <h5>Syarat dan Ketentuan Layanan Software Sharing</h5>
                        <p>Selamat datang di layanan Software Sharing kami. Dengan melanjutkan proses pembelian ini, Anda menyatakan setuju untuk terikat oleh syarat dan ketentuan berikut:</p>
                        
                        <ol>
                            <li class="mb-2"><strong>Akses Akun:</strong> Akun yang diberikan bersifat sharing. Anda dilarang keras untuk mengubah kata sandi, email, atau pengaturan keamanan apapun pada akun yang dipinjamkan. Pelanggaran terhadap poin ini akan mengakibatkan pembatalan layanan tanpa pengembalian dana.</li>
                            <li class="mb-2"><strong>Penggunaan Wajar:</strong> Gunakan layanan ini sesuai dengan batas wajar dan tidak mengganggu kenyamanan pengguna lain yang berada dalam satu akun sharing yang sama.</li>
                            <li class="mb-2"><strong>Masa Berlaku:</strong> Layanan terhitung aktif sejak pembayaran dikonfirmasi dan kredensial dikirimkan ke email Anda. Layanan akan berakhir sesuai dengan durasi paket yang Anda pilih.</li>
                            <li class="mb-2"><strong>Dukungan Teknis:</strong> Jika terjadi masalah akses (seperti akun terkunci atau error limit device), silakan hubungi tim dukungan kami. Kami akan meresolusi kendala maksimal 2x24 jam kerja.</li>
                            <li class="mb-2"><strong>Kebijakan Pengembalian Dana:</strong> Semua transaksi yang telah berhasil dibayarkan tidak dapat dibatalkan atau diminta pengembalian dana (refund) dengan alasan apapun kecuali software benar-benar tidak dapat digunakan lagi dari penyedia layanan pusat.</li>
                            <li class="mb-2"><strong>Tanggung Jawab Data:</strong> Kami tidak bertanggung jawab atas hilangnya data pribadi atau proyek Anda di dalam aplikasi pihak ketiga. Sangat disarankan untuk selalu membackup data pekerjaan Anda ke penyimpanan lokal perangkat pribadi.</li>
                            <li class="mb-2"><strong>Perubahan Syarat & Ketentuan:</strong> Syarat dan ketentuan ini dapat berubah sewaktu-waktu tanpa pemberitahuan sebelumnya, sesuai dengan kebijakan provider pusat software terkait.</li>
                            <li class="mb-2"><strong>Keamanan:</strong> Mohon jaga kerahasiaan pin/password Anda. Dilarang menyebarkan akun sharing ini kepada pihak ketiga yang tidak terdaftar.</li>
                            <li class="mb-2"><strong>Penutupan Layanan:</strong> Kami berhak memutus akses secara sepihak apabila ditemukan indikasi kecurangan, penyalahgunaan eksploitatif, atau pelanggaran terhadap syarat dan ketentuan ini.</li>
                            <li class="mb-2"><strong>Perjanjian Mengikat:</strong> Dengan menyetujui, Anda telah memahami penuh seluruh konsekuensi teknis maupun non-teknis atas penggunaan layanan software sharing ini.</li>
                        </ol>
                        <p class="mb-0 text-center font-italic text-muted mt-4">--- Akhir dari Syarat dan Ketentuan ---</p>
                    @endif
                </div>

                <form action="{{ route('customer-checkout.process', [$software->slug, $package->id]) }}" method="POST" id="checkout-form">
                    @csrf
                    
                    {{-- Hidden payment fields that are mapped from the right column --}}
                    <input type="hidden" name="payment_gateway" id="hidden_payment_gateway" value="">
                    <input type="hidden" name="selected_bank" id="hidden_selected_bank" value="">
                    
                    <div class="form-group mb-0 p-3 rounded" style="background: var(--primary-l); border: 1px dashed var(--primary);">
                        <div class="custom-control custom-checkbox custom-control-lg">
                            <input type="checkbox" class="custom-control-input" id="agree_terms" name="agree_terms" required disabled>
                            <label class="custom-control-label font-weight-bold" style="color: var(--primary-d); padding-top: 2px; cursor: not-allowed;" for="agree_terms" id="agree_terms_label">
                                Saya setuju dengan seluruh Syarat & Ketentuan di atas.
                            </label>
                        </div>
                        <small class="text-danger mt-2 d-block font-weight-bold" id="tnc-alert" style="padding-left: 28px;">
                            <i class="fas fa-arrow-up"></i> Wajib scroll kotak S&K sampai ke bawah untuk membuka persetujuan.
                        </small>
                    </div>
            </div>
            @endcanAccess
        </div>

        {{-- Right Column: Payment Method Selection --}}
        <div class="right-col" style="flex: 1;">
            <div class="modern-card slide-up-2 position-sticky" style="top: 20px;">
                <h4 class="card-heading"><i class="fas fa-wallet mr-2 text-primary"></i> Metode Pembayaran</h4>
                
                @if(empty($paymentMethods))
                    <div class="status-alert warning">
                        <i class="fas fa-exclamation-triangle icon text-warning"></i>
                        <div>Tidak ada metode pembayaran yang tersedia. Silakan hubungi admin.</div>
                    </div>
                @else
                    <div class="payment-methods mb-4">
                        @foreach($paymentMethods as $key => $method)
                            <div class="modern-payment-item mb-3 p-3 border rounded {{ $loop->first ? 'active' : '' }}" data-method="{{ $key }}" style="cursor: pointer; transition: all 0.2s;">
                                <div class="custom-control custom-radio">
                                    <input type="radio" 
                                           id="v_payment_{{ $key }}" 
                                           name="v_payment_gateway" 
                                           value="{{ $key }}" 
                                           class="custom-control-input payment-method-checker" 
                                           {{ $loop->first ? 'checked' : '' }}
                                           required>
                                    <label class="custom-control-label w-100" for="v_payment_{{ $key }}" style="cursor: pointer;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="text-dark" style="font-size: 1.05rem;">{{ $method['name'] }}</strong><br>
                                                <small class="text-muted">{{ $method['description'] }}</small>
                                            </div>
                                            @if($key == 'manual')
                                                <i class="fas fa-university" style="font-size: 24px; color: var(--primary);"></i>
                                            @elseif($key == 'xendit')
                                                <i class="fas fa-wallet" style="font-size: 24px; color: var(--primary);"></i>
                                            @else
                                                <i class="fas fa-money-check-alt" style="font-size: 24px; color: var(--primary);"></i>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Manual Transfer Bank Details --}}
                            @canAccess('paymentPending','customer_checkouts')
                            @if($key === 'manual' && isset($method['banks']))
                                <div class="manual-transfer-details pl-2 mb-4" id="v_manual_details" style="{{ $loop->first ? '' : 'display:none;' }}">
                                    <h6 class="font-weight-bold mb-3" style="color: var(--primary-d);">Pilih Bank Tujuan:</h6>
                                    @foreach($method['banks'] as $index => $bank)
                                        <div class="custom-control custom-radio mb-3 p-3 bg-light border rounded manual-bank-item" style="transition: all 0.2s; cursor: pointer;">
                                            <input type="radio" 
                                                   id="v_bank_{{ $index }}" 
                                                   name="v_selected_bank" 
                                                   value="{{ $index }}" 
                                                   class="custom-control-input bank-checker"
                                                   {{ $index === 0 ? 'checked' : '' }}>
                                            <label class="custom-control-label w-100" for="v_bank_{{ $index }}" style="cursor: pointer;">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <strong class="text-dark">{{ $bank['bank_name'] }}</strong><br>
                                                        <small class="text-muted">A/N: {{ $bank['account_name'] }}</small>
                                                    </div>
                                                </div>
                                                <div class="mt-2 pt-2 border-top">
                                                    <strong class="text-primary" style="font-size: 16px; letter-spacing: 1px;">{{ $bank['account_number'] }}</strong>
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                    <div class="status-alert success mt-2 p-3" style="font-size: 13.5px;">
                                        <i class="fas fa-exclamation-circle icon"></i>
                                        <div>Setelah melakukan transfer, Anda akan diminta untuk meng-upload bukti pembayaran pada halaman selanjutnya.</div>
                                    </div>
                                </div>
                            @endif
                            @endcanAccess
                        @endforeach
                    </div>

                    @canAccess('process','customer_checkouts')
                    <div class="pt-3 mt-4">
                        <button type="button" class="btn-modern-submit" id="btn-submit-proxy">
                            <span class="btn-text"><i class="fas fa-lock mr-2"></i> Konfirmasi & Bayar</span>
                            <span class="spinner" style="display:none;"><i class="fas fa-spinner fa-spin mr-2"></i> Memproses...</span>
                        </button>
                    </div>
                    </form> {{-- Close form from left col --}}
                    @endcanAccess
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --primary:   #de342f;
        --primary-d: #b91c1c;
        --primary-l: #fee2e2;
        --success:   #10b981;
        --danger:    #ef4444;
        --warning:   #f59e0b;
        --info:      #3b82f6;
        --bg:        #fdfafa;
        --card-bg:   #ffffff;
        --text:      #1e293b;
        --muted:     #64748b;
        --border:    #e2e8f0;
        --radius:    16px;
        --shadow:    0 4px 24px rgba(222, 52, 47, .06);
        --font-inter: 'Inter', sans-serif;
    }

    body { font-family: var(--font-inter); background: var(--bg); }

    .custom-container { max-width: 1200px; margin: 0 auto; padding-bottom: 40px; }

    /* Custom Back button */
    .btn-back {
        display: inline-flex; align-items: center; gap: 8px;
        color: var(--muted); font-weight: 600; text-decoration: none;
        padding: 8px 18px; border-radius: 999px;
        background: #fff; border: 1px solid var(--border);
        transition: all 0.2s; font-size: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .btn-back:hover {
        background: var(--primary-l); color: var(--primary);
        border-color: var(--primary-l); transform: translateX(-2px);
    }

    /* Checkout Banner Header */
    .checkout-hero-banner {
        background-color: var(--primary);
        background-image: radial-gradient(circle at center, rgba(239, 68, 68, 0.9) 0%, rgba(219, 39, 41, 0) 90%);
        color: #fff;
        padding: 30px 40px;
        border-radius: var(--radius);
        box-shadow: 0 10px 30px rgba(222, 52, 47, 0.2);
    }
    .payment-safe-badge {
        background: rgba(255, 255, 255, 0.2);
        color: #fff; border: 1px solid rgba(255, 255, 255, 0.4);
        padding: 8px 16px; font-size: 13px; font-weight: 700;
        backdrop-filter: blur(10px);
    }

    .modern-two-col {
        display: flex; gap: 24px; align-items: flex-start;
    }
    .left-col { display: flex; flex-direction: column; gap: 24px; }
    .right-col { display: flex; flex-direction: column; gap: 24px; min-width: 0; }
    
    @media(max-width: 992px) {
        .modern-two-col { flex-direction: column; }
        .left-col, .right-col { flex: none; width: 100%; }
        .position-sticky { position: static !important; }
    }

    /* Cards */
    .modern-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 28px 32px;
        box-shadow: var(--shadow);
    }
    .modern-card.border-primary-top {
        border-top: 5px solid var(--primary);
    }
    .card-heading {
        font-size: 1.25rem; font-weight: 800; color: var(--text);
        margin-bottom: 24px; border-bottom: 1px solid var(--border);
        padding-bottom: 14px;
    }

    .detail-logo {
        object-fit: contain; border-radius: 16px; border: 1px solid var(--border);
        padding: 8px; background: #fff;
    }
    .detail-logo-placeholder {
        border-radius: 16px; background: var(--primary-l);
        display: flex; align-items: center; justify-content: center;
        color: var(--primary);
    }

    /* Badges */
    .custom-badge {
        display: inline-flex; align-items: center; padding: 6px 14px;
        border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .5px; margin-right: 6px; margin-top: 6px;
    }
    .status-primary { background: var(--primary-l); color: var(--primary-d); border: 1px solid var(--primary); }
    .status-default { background: #f1f5f9; color: var(--muted); border: 1px solid var(--border); }

    /* Tables */
    .modern-table { font-size: 14.5px; }
    .modern-table th { font-weight: 600; padding-top: 14px; padding-bottom: 14px; }
    .modern-table td { padding-top: 14px; padding-bottom: 14px; }

    /* Alerts */
    .status-alert {
        display: flex; gap: 14px; text-align: left; padding: 16px 20px;
        border-radius: 14px; font-size: 14px; align-items: flex-start;
    }
    .status-alert.warning { background: #fffbeb; color: #b45309; border: 1px solid #fcd34d; }
    .status-alert.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

    /* Payment Items */
    .modern-payment-item { background: #fff; }
    .modern-payment-item:hover { border-color: var(--primary-l) !important; background: #fafafa; }
    .modern-payment-item.active {
        border-color: var(--primary) !important;
        background-color: var(--primary-l) !important;
        box-shadow: 0 4px 12px rgba(222, 52, 47, 0.08);
    }
    .manual-bank-item.active {
        border-color: var(--primary) !important;
        background-color: #fff !important;
        box-shadow: 0 2px 10px rgba(222, 52, 47, 0.08);
    }
    
    /* Bigger checkmarks for terms */
    .custom-control-lg { padding-left: 2rem; }
    .custom-control-lg .custom-control-label::before,
    .custom-control-lg .custom-control-label::after {
        top: 0.15rem; left: -2rem; width: 1.5rem; height: 1.5rem; cursor: pointer;
    }
    .custom-control-lg .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--primary); border-color: var(--primary-d);
    }
    .custom-control-input:disabled ~ .custom-control-label::before {
        background-color: #e2e8f0; border-color: #cbd5e1;
    }

    /* Submit Button Red */
    .btn-modern-submit {
        background: var(--primary); color: #fff;
        width: 100%; border: none; border-radius: 12px; font-size: 16px;
        font-weight: 700; padding: 15px; cursor: pointer;
        transition: all 0.25s; box-shadow: 0 8px 24px rgba(222, 52, 47, 0.2);
    }
    .btn-modern-submit:hover {
        background: var(--primary-d); transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(222, 52, 47, 0.3);
    }
    .btn-modern-submit:disabled {
        background: var(--muted); transform: none; box-shadow: none; cursor: not-allowed; opacity: 0.8;
    }

    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .slide-up-1 { animation: fadeInUp 0.4s ease forwards; }
    .slide-up-2 { animation: fadeInUp 0.5s ease forwards; animation-delay: 0.1s; opacity: 0; }
    .slide-up-3 { animation: fadeInUp 0.5s ease forwards; animation-delay: 0.2s; opacity: 0; }
    
    /* Scrollbar for TNC box */
    #tnc-box::-webkit-scrollbar { width: 8px; }
    #tnc-box::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 8px; }
    #tnc-box::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
    #tnc-box::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // UI Interactions for TNC validation
    const tncBox = document.getElementById('tnc-box');
    const agreeTermsBox = document.getElementById('agree_terms');
    const agreeLabel = document.getElementById('agree_terms_label');
    const tncAlert = document.getElementById('tnc-alert');
    let scrolledToBottom = false;

    setTimeout(checkTncScroll, 300);

    tncBox.addEventListener('scroll', function() {
        checkTncScroll();
    });

    function checkTncScroll() {
        if (!scrolledToBottom) {
            let scrollPosition = tncBox.scrollTop + tncBox.clientHeight;
            let scrollHeight = tncBox.scrollHeight;
            
            if (scrollPosition >= scrollHeight - 20) {
                scrolledToBottom = true;
                agreeTermsBox.disabled = false;
                agreeLabel.style.cursor = 'pointer';
                
                $(tncAlert).fadeOut(200, function() {
                    let successMsg = $('<small class="text-success mt-2 d-block font-weight-bold" style="padding-left: 28px; animation: fadeInUp 0.3s;"><i class="fas fa-check-circle"></i> S&K berhasil dibaca. Silakan centang kotak persetujuan.</small>');
                    $(this).replaceWith(successMsg);
                });
                
                $(tncBox).css('border-color', 'var(--primary)');
                $(tncBox).css('border-width', '2px');
                $(tncBox).css('background', '#fff');
                
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3500,
                    timerProgressBar: true,
                });
                Toast.fire({
                    icon: 'success',
                    title: 'Syarat dan Ketentuan berhasil di-scroll ke bawah.'
                });
            }
        }
    }

    // Proxy form submit logic mapping
    $('.modern-payment-item').on('click', function(event) {
        if (!$(event.target).is(':radio')) {
            $(this).find('.payment-method-checker').prop('checked', true).trigger('change');
        }
    });

    $('.payment-method-checker').on('change', function() {
        $('.modern-payment-item').removeClass('active').css('border-color', 'var(--border)');
        $(this).closest('.modern-payment-item').addClass('active').css('border-color', 'var(--primary)');
        
        const selectedMethod = $(this).val();
        $('.manual-transfer-details').slideUp(200);
        
        if (selectedMethod === 'manual') {
            $('#v_manual_details').slideDown(200);
        }
        $('#hidden_payment_gateway').val(selectedMethod);
    });
    
    // Bank selection styling
    $('.bank-checker').on('change', function() {
        $('.manual-bank-item').removeClass('active').css('border-color', 'var(--border)');
        $(this).closest('.manual-bank-item').addClass('active').css('border-color', 'var(--primary)');
        $('#hidden_selected_bank').val($(this).val());
    });
    
    $('.manual-bank-item').on('click', function(event) {
        if (!$(event.target).is(':radio') && !$(event.target).is('label')) {
            $(this).find('.bank-checker').prop('checked', true).trigger('change');
        }
    });

    // Initialize hidden fields and UI styling on load
    $('#hidden_payment_gateway').val($('.payment-method-checker:checked').val());
    const initBank = $('.bank-checker:checked');
    $('#hidden_selected_bank').val(initBank.val());
    initBank.closest('.manual-bank-item').addClass('active').css('border-color', 'var(--primary)');

    // Submit form proxy
    $('#btn-submit-proxy').on('click', function(e) {
        e.preventDefault();
        
        if (!agreeTermsBox.checked) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Anda harus membaca sampai bawah dan mencentang persetujuan Syarat & Ketentuan.',
                confirmButtonColor: '#de342f',
                confirmButtonText: 'Scroll S&K Sekarang'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('tnc-box').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    $('#tnc-box').fadeTo(100, 0.3, function() { $(this).fadeTo(500, 1.0); });
                    if (!scrolledToBottom) {
                        $('#tnc-box').animate({ scrollTop: tncBox.scrollHeight }, 1200);
                    }
                }
            });
            return;
        }
        
        if ($('#hidden_payment_gateway').val() === 'manual' && !$('#hidden_selected_bank').val()) {
            Swal.fire({
                icon: 'error',
                title: 'Data Tidak Lengkap',
                text: 'Silakan pilih Bank Tujuan Transfer terlebih dahulu.',
                confirmButtonColor: '#de342f'
            });
            return;
        }
        
        $('#checkout-form').submit();
    });

    // Handle form submission UI update
    $('#checkout-form').on('submit', function() {
        const btn = $('#btn-submit-proxy');
        btn.prop('disabled', true);
        btn.find('.btn-text').hide();
        btn.find('.spinner').show();
    });
});
</script>
@stop
