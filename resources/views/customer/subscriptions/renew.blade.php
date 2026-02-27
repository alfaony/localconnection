@extends('adminlte::page')

@section('title', 'Perpanjang Langganan - ' . $subscription->software->nama)

@section('content_header')
    <div class="mb-4">
        <a href="{{ route('customer-subscription.show', $subscription->id) }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Detail Langganan
        </a>
    </div>
@stop

@section('content')
@include('components.alert')
<div class="custom-container">

    {{-- Hero Banner --}}
    <div class="renew-hero-banner mb-4 slide-up-1">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h2 class="mb-1 font-weight-bold"><i class="fas fa-sync-alt mr-2"></i> Perpanjang Langganan</h2>
                <p class="mb-0 text-white-50">Perpanjang akses Anda ke layanan <strong>{{ $subscription->software->nama }}</strong> dengan mudah dan cepat.</p>
            </div>
            <div>
                <span class="custom-badge payment-safe-badge"><i class="fas fa-shield-alt mr-1"></i> Pembayaran Aman 100%</span>
            </div>
        </div>
    </div>

    {{-- ⚠️ Pending Payment Warning Banner --}}
    @if(isset($pendingPayment) && $pendingPayment)
    <div class="pending-payment-banner mb-4 slide-up-1" id="pendingBanner">
        <div class="d-flex align-items-start gap-3">
            <div class="pending-icon-wrap" style="background:#fef3c7; color:#d97706; padding:12px 14px; border-radius:50%; display:inline-block;">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="mb-1 font-weight-bold" style="color:#92400e;">
                    Anda Masih Memiliki Pembayaran yang Belum Selesai
                </h5>
                <p class="mb-2 text-sm" style="color:#78350f; font-size:13.5px;">
                    Order <strong>#{{ $subscription->order_number }}</strong>
                    (via <strong>{{ strtoupper($pendingPayment->payment_gateway ?? 'manual') }}</strong>)
                    dibuat {{ $pendingPayment->created_at ? $pendingPayment->created_at->diffForHumans() : '' }}.
                    Semua slot sudah terkunci. Anda tidak dapat membuat pesanan baru sbelum pesanan lama diselesaikan atau dibatalkan.
                </p>
                @if($pendingPayment->expired_at && \Carbon\Carbon::parse($pendingPayment->expired_at)->isFuture())
                <p class="mb-2" style="color:#92400e; font-size:13px; font-weight:600;">
                    <i class="fas fa-clock"></i> Akan otomatis dibatalkan pada 
                    <strong>{{ \Carbon\Carbon::parse($pendingPayment->expired_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }} WIB</strong>
                    ({{ \Carbon\Carbon::parse($pendingPayment->expired_at)->diffForHumans(['parts' => 2, 'short' => true]) }})
                </p>
                @endif
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="{{ route('customer-subscription.resume-renewal-payment', $subscription->id) }}" class="btn btn-warning font-weight-bold shadow-sm" style="color: #663c00; padding:8px 16px; border-radius:8px; font-size:14px; text-decoration:none;">
                        <i class="fas fa-wallet mr-1"></i> Lanjutkan Pembayaran Sebelumnya
                    </a>
                    <form action="{{ route('customer-subscription.cancel-renewal-payment', $subscription->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger font-weight-bold bg-white shadow-sm" 
                                style="padding:8px 16px; border-radius:8px; font-size:14px;"
                                onclick="return confirm('Apakah Anda yakin ingin membatalkan pesanan sebelumnya? Jika dibatalkan, slot pesanan lama akan dilepas dan Anda dapat memilih paket/metode baru.')">
                            <i class="fas fa-times-circle mr-1"></i> Batalkan & Buat Order Baru
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <style>
            .modern-two-col { pointer-events: none; opacity: 0.6; filter: grayscale(30%); }
            .modern-two-col button { cursor: not-allowed !important; }
            .pending-payment-banner {
                background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
                border: 1px solid #fde68a;
                border-left: 5px solid #f59e0b;
                border-radius: 12px;
                padding: 24px;
                box-shadow: 0 4px 15px rgba(245, 158, 11, 0.1);
            }
        </style>
    </div>
    @endif

    {{-- Status Alert --}}
    @php
        $daysLeft = $subscription->days_until_expiry ?? 0;
        $expiredDate = $subscription->tanggal_expired ? \Carbon\Carbon::parse($subscription->tanggal_expired)->translatedFormat('d F Y') : '-';
    @endphp
    @if($daysLeft <= 0)
        <div class="status-alert danger mb-4 slide-up-1">
            <i class="fas fa-times-circle icon" style="font-size: 22px;"></i>
            <div><strong>Langganan Anda Telah Berakhir!</strong><br>Langganan sudah tidak aktif sejak <strong>{{ $expiredDate }}</strong>. Perpanjang sekarang untuk memulihkan akses Anda.</div>
        </div>
    @elseif($daysLeft <= 3)
        <div class="status-alert danger mb-4 slide-up-1">
            <i class="fas fa-exclamation-triangle icon" style="font-size: 22px;"></i>
            <div><strong>Langganan Akan Segera Berakhir!</strong><br>Tersisa <strong>{{ $daysLeft }} hari</strong> hingga masa aktif berakhir pada <strong>{{ $expiredDate }}</strong>. Segera perpanjang!</div>
        </div>
    @else
        <div class="status-alert warning mb-4 slide-up-1">
            <i class="fas fa-bell icon" style="font-size: 22px;"></i>
            <div><strong>Reminder Perpanjangan:</strong><br>Masa aktif berakhir pada <strong>{{ $expiredDate }}</strong> ({{ $daysLeft }} hari lagi). Perpanjang lebih awal untuk menghindari gangguan layanan.</div>
        </div>
    @endif

    <div class="modern-two-col">
        {{-- KIRI: Info Langganan & Metode Bayar --}}
        <div class="left-col">

            {{-- Info Langganan Aktif --}}
            <div class="modern-card slide-up-2 border-primary-top">
                <h4 class="card-heading"><i class="fas fa-info-circle mr-2 text-primary"></i> Langganan Saat Ini</h4>
                <div class="d-flex align-items-center p-3 bg-light rounded mb-3" style="border: 1px solid var(--border);">
                    <div class="flex-shrink-0 mr-3">
                        @if($subscription->software->logo)
                            <img src="{{ s3_asset(true, 10, $subscription->software->logo) }}" alt="{{ $subscription->software->nama }}" class="detail-logo m-0" style="width: 70px; height: 70px;">
                        @else
                            <div class="detail-logo-placeholder m-0" style="width: 70px; height: 70px; font-size: 28px;"><i class="fas fa-desktop"></i></div>
                        @endif
                    </div>
                    <div>
                        <h4 class="mb-1 font-weight-bold" style="font-size: 1.15rem;">{{ $subscription->software->nama }}</h4>
                        <span class="custom-badge status-primary">{{ $subscription->package->nama_paket }}</span>
                        <span class="custom-badge {{ $daysLeft > 3 ? 'status-success' : 'status-danger' }}">
                            {{ $daysLeft > 0 ? $daysLeft . ' hari lagi' : 'Kadaluarsa' }}
                        </span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table modern-table mb-0">
                        <tr>
                            <th class="text-muted border-top-0 border-bottom" width="160">No. Order</th>
                            <td class="border-top-0 border-bottom text-right text-dark font-weight-bold" style="font-size: 13px;">{{ $subscription->order_number }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted border-bottom">Mulai Aktif</th>
                            <td class="border-bottom text-right text-dark">{{ \Carbon\Carbon::parse($subscription->tanggal_mulai)->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted border-bottom-0">Berakhir</th>
                            <td class="border-bottom-0 text-right font-weight-bold" style="color: var(--{{ $daysLeft > 3 ? 'success' : 'danger' }});">{{ $expiredDate }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Keuntungan Perpanjangan --}}
            <div class="modern-card slide-up-3">
                <h4 class="card-heading"><i class="fas fa-star mr-2 text-warning"></i> Keuntungan Perpanjangan</h4>
                <ul class="list-unstyled mb-0">
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <span class="benefit-icon"><i class="fas fa-key"></i></span>
                        <div><strong>Kredensial Sama</strong><br><small class="text-muted">Tidak perlu login ulang, akses langsung berlanjut.</small></div>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <span class="benefit-icon"><i class="fas fa-calendar-plus"></i></span>
                        <div><strong>Masa Aktif Diperpanjang</strong><br><small class="text-muted">Dihitung dari tanggal berakhir saat ini.</small></div>
                    </li>
                    <li class="mb-3 d-flex align-items-start gap-2">
                        <span class="benefit-icon"><i class="fas fa-bolt"></i></span>
                        <div><strong>Aktivasi Instan</strong><br><small class="text-muted">Layanan aktif kembali segera setelah pembayaran dikonfirmasi.</small></div>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <span class="benefit-icon"><i class="fas fa-headset"></i></span>
                        <div><strong>Dukungan Penuh</strong><br><small class="text-muted">Tim kami siap membantu jika ada pertanyaan.</small></div>
                    </li>
                </ul>
            </div>

        </div>

        {{-- KANAN: Pilih Paket & Ringkasan --}}
        <div class="right-col">

            {{-- Pilih Paket --}}
            <div class="modern-card slide-up-2">
                <h4 class="card-heading"><i class="fas fa-box-open mr-2 text-primary"></i> Pilih Paket Perpanjangan</h4>

                @if(isset($hasFreeSlot) && !$hasFreeSlot)
                    <div class="status-alert danger mb-3">
                        <i class="fas fa-exclamation-circle icon" style="font-size: 22px;"></i>
                        <div><strong>Maaf, Slot Penuh!</strong><br>Saat ini tidak ada slot yang tersedia untuk perpanjangan layanan ini. Pilihan paket dinonaktifkan sementara. Silakan datang kembali nanti atau hubungi admin.</div>
                    </div>
                @endif

                @if($packages->count() > 0)
                    <div class="package-grid">
                        @foreach($packages as $package)
                        @php
                            $isCurrent = $subscription->package_id == $package->id;
                            $isDisabled = isset($hasFreeSlot) ? !$hasFreeSlot : false;
                        @endphp
                        <div class="package-card {{ $isCurrent ? 'current' : '' }} {{ $isDisabled ? 'disabled' : '' }}"
                             id="pkg-card-{{ $package->id }}"
                             @if(!$isDisabled) onclick="selectPackage('{{ $package->id }}', '{{ $package->nama_paket }}', {{ $package->harga }}, {{ $package->durasi_hari }}, '{{ $subscription->tanggal_expired ? \Carbon\Carbon::parse($subscription->tanggal_expired)->addDays($package->durasi_hari)->format('Y-m-d') : '' }}')" @endif>
                            @if($isCurrent)
                                <span class="pkg-label">Paket Saat Ini</span>
                            @endif
                            <div class="pkg-name">{{ $package->nama_paket }}</div>
                            <div class="pkg-price">Rp {{ number_format($package->harga, 0, ',', '.') }}</div>
                            <div class="pkg-duration"><i class="far fa-clock mr-1"></i>{{ $package->durasi_hari }} hari</div>
                            @if($subscription->tanggal_expired)
                            <div class="pkg-new-expiry">
                                <i class="fas fa-calendar-check mr-1"></i>
                                Aktif s/d {{ \Carbon\Carbon::parse($subscription->tanggal_expired)->addDays($package->durasi_hari)->translatedFormat('d M Y') }}
                            </div>
                            @endif
                            <button type="button" class="pkg-select-btn" id="btn-pkg-{{ $package->id }}"
                                data-id="{{ $package->id }}"
                                data-name="{{ $package->nama_paket }}"
                                data-price="{{ $package->harga }}"
                                data-days="{{ $package->durasi_hari }}"
                                {{ $isDisabled ? 'disabled' : '' }}>
                                <i class="fas fa-check mr-1"></i> Pilih Paket Ini
                            </button>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="status-alert danger">
                        <i class="fas fa-exclamation-circle icon"></i>
                        <div>Tidak ada paket perpanjangan yang tersedia. Silakan hubungi admin.</div>
                    </div>
                @endif
            </div>

            {{-- Ringkasan & Pembayaran --}}
            @canAccess('processRenewal','customer_subscriptions')
            <div class="modern-card slide-up-3" id="order-summary" style="display: none;">
                <h4 class="card-heading"><i class="fas fa-receipt mr-2 text-primary"></i> Ringkasan Pesanan</h4>

                <div class="table-responsive mb-4">
                    <table class="table modern-table mb-0">
                        <tr>
                            <th class="text-muted border-top-0 border-bottom" width="160">Software</th>
                            <td class="border-top-0 border-bottom text-right text-dark">{{ $subscription->software->nama }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted border-bottom">Paket Dipilih</th>
                            <td class="border-bottom text-right" id="summary-package">-</td>
                        </tr>
                        <tr>
                            <th class="text-muted border-bottom">Durasi</th>
                            <td class="border-bottom text-right" id="summary-duration">-</td>
                        </tr>
                        <tr>
                            <th class="text-muted border-bottom">Expired Saat Ini</th>
                            <td class="border-bottom text-right text-dark">{{ $expiredDate }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted border-bottom">Expired Baru</th>
                            <td class="border-bottom text-right font-weight-bold text-success" id="summary-new-expiry">-</td>
                        </tr>
                        <tr>
                            <th class="text-muted border-bottom">Subtotal</th>
                            <td class="border-bottom text-right text-dark" id="summary-subtotal">Rp 0</td>
                        </tr>
                        <tr id="summary-ppn-row" style="display: none;">
                            <th class="text-muted border-bottom" id="summary-ppn-label">PPN (0%)</th>
                            <td class="border-bottom text-right text-dark" id="summary-ppn-amount">Rp 0</td>
                        </tr>
                        <tr style="background: var(--primary-l);">
                            <th class="align-middle border-bottom-0" style="color: var(--primary-d); font-size: 1.05rem;">Total Pembayaran</th>
                            <td class="border-bottom-0 text-right"><h4 class="mb-0 font-weight-bold" style="color: var(--primary);" id="summary-total">Rp 0</h4></td>
                        </tr>
                    </table>
                </div>

                {{-- Metode Pembayaran --}}
                <h5 class="font-weight-bold mb-3" style="color: var(--text);"><i class="fas fa-wallet mr-2 text-primary"></i>Pilih Metode Pembayaran</h5>

                @if(empty($paymentMethods))
                    <div class="status-alert warning mb-3">
                        <i class="fas fa-exclamation-triangle icon"></i>
                        <div>Tidak ada metode pembayaran yang tersedia. Hubungi admin.</div>
                    </div>
                @else
                    <div class="payment-methods mb-4">
                        @foreach($paymentMethods as $key => $method)
                        <div class="modern-payment-item mb-2 p-3 border rounded {{ $loop->first ? 'active' : '' }}" data-method="{{ $key }}" style="cursor: pointer;">
                            <div class="custom-control custom-radio">
                                <input type="radio"
                                       id="rp_{{ $key }}"
                                       name="v_payment_gateway"
                                       value="{{ $key }}"
                                       class="custom-control-input payment-method-checker"
                                       {{ $loop->first ? 'checked' : '' }} required>
                                <label class="custom-control-label w-100" for="rp_{{ $key }}" style="cursor: pointer;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong class="text-dark">{{ $method['name'] }}</strong><br>
                                            <small class="text-muted">{{ $method['description'] }}</small>
                                        </div>
                                        @if($key === 'manual')
                                            <i class="fas fa-university" style="font-size: 22px; color: var(--primary);"></i>
                                        @elseif($key === 'xendit')
                                            <i class="fas fa-wallet" style="font-size: 22px; color: var(--primary);"></i>
                                        @else
                                            <i class="fas fa-money-check-alt" style="font-size: 22px; color: var(--primary);"></i>
                                        @endif
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Bank pilihan untuk manual --}}
                        @if($key === 'manual' && isset($method['banks']))
                        <div class="pl-2 mb-3" id="manual-bank-details" style="{{ $loop->first ? '' : 'display:none;' }}">
                            <h6 class="font-weight-bold mb-2" style="color: var(--primary-d);">Pilih Bank Tujuan:</h6>
                            @foreach($method['banks'] as $idx => $bank)
                            <div class="custom-control custom-radio mb-2 p-3 bg-light border rounded manual-bank-item" style="cursor: pointer;">
                                <input type="radio" id="rbank_{{ $idx }}" name="v_selected_bank" value="{{ $idx }}" class="custom-control-input bank-checker" {{ $idx === 0 ? 'checked' : '' }}>
                                <label class="custom-control-label w-100" for="rbank_{{ $idx }}" style="cursor: pointer;">
                                    <strong class="text-dark">{{ $bank['bank_name'] }}</strong><br>
                                    <small class="text-muted">A/N: {{ $bank['account_name'] }}</small>
                                    <div class="mt-1 pt-1 border-top">
                                        <strong class="text-primary" style="letter-spacing: 1px;">{{ $bank['account_number'] }}</strong>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        @endforeach
                    </div>

                    <form action="{{ route('customer-subscription.process-renewal', $subscription->id) }}" method="POST" id="renew-form">
                        @csrf
                        <input type="hidden" name="package_id"       id="input-package-id">
                        <input type="hidden" name="payment_gateway"   id="input-payment-gateway" value="{{ array_key_first($paymentMethods) }}">
                        <input type="hidden" name="selected_bank"     id="input-selected-bank">

                        <button type="button" class="btn-modern-submit" id="btn-submit-renew">
                            <span class="btn-text"><i class="fas fa-lock mr-2"></i> Konfirmasi & Bayar</span>
                            <span class="spinner" style="display:none;"><i class="fas fa-spinner fa-spin mr-2"></i> Memproses...</span>
                        </button>
                    </form>
                @endif
            </div>
            @endcanAccess

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
        --bg:        #fdfafa;
        --card-bg:   #ffffff;
        --text:      #1e293b;
        --muted:     #64748b;
        --border:    #e2e8f0;
        --radius:    16px;
        --shadow:    0 4px 24px rgba(222,52,47,.06);
    }
    body { font-family: 'Inter', sans-serif; background: var(--bg); }
    .custom-container { max-width: 1200px; margin: 0 auto; padding-bottom: 40px; }

    .btn-back {
        display: inline-flex; align-items: center; gap: 8px;
        color: var(--muted); font-weight: 600; text-decoration: none;
        padding: 8px 18px; border-radius: 999px;
        background: #fff; border: 1px solid var(--border);
        transition: all 0.2s; font-size: 14px;
    }
    .btn-back:hover { background: var(--primary-l); color: var(--primary); border-color: var(--primary-l); transform: translateX(-2px); }

    .renew-hero-banner {
        background-color: var(--primary);
        background-image: radial-gradient(circle at 80% 50%, rgba(185,28,28,.7) 0%, transparent 70%);
        color: #fff; padding: 28px 40px; border-radius: var(--radius);
        box-shadow: 0 10px 30px rgba(222,52,47,0.2);
    }
    .payment-safe-badge {
        background: rgba(255,255,255,0.2); color: #fff; border: 1px solid rgba(255,255,255,0.4);
        padding: 8px 16px; font-size: 13px; font-weight: 700; backdrop-filter: blur(10px);
    }

    .modern-two-col { display: flex; gap: 24px; align-items: flex-start; }
    .left-col  { flex: 0 0 340px; display: flex; flex-direction: column; gap: 24px; }
    .right-col { flex: 1; display: flex; flex-direction: column; gap: 24px; min-width: 0; }

    @media(max-width: 992px) {
        .modern-two-col { flex-direction: column; }
        .left-col, .right-col { flex: none; width: 100%; }
    }

    .modern-card {
        background: var(--card-bg); border: 1px solid var(--border);
        border-radius: var(--radius); padding: 26px 28px; box-shadow: var(--shadow);
    }
    .modern-card.border-primary-top { border-top: 5px solid var(--primary); }
    .card-heading {
        font-size: 1.1rem; font-weight: 800; color: var(--text);
        margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 12px;
    }

    .detail-logo { object-fit: contain; border-radius: 12px; border: 1px solid var(--border); padding: 6px; background: #fff; }
    .detail-logo-placeholder { border-radius: 12px; background: var(--primary-l); display: flex; align-items: center; justify-content: center; color: var(--primary); }

    .custom-badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; margin-right: 4px; margin-top: 4px; }
    .status-primary { background: var(--primary-l); color: var(--primary-d); border: 1px solid var(--primary); }
    .status-success { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
    .status-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }

    .status-alert { display: flex; gap: 14px; text-align: left; padding: 16px 20px; border-radius: 14px; font-size: 14px; align-items: flex-start; }
    .status-alert.warning { background: #fffbeb; color: #b45309; border: 1px solid #fcd34d; }
    .status-alert.danger  { background: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }

    .modern-table { font-size: 14px; }
    .modern-table th { font-weight: 600; padding-top: 12px; padding-bottom: 12px; }
    .modern-table td { padding-top: 12px; padding-bottom: 12px; }

    /* Benefits */
    .benefit-icon { width: 36px; height: 36px; min-width: 36px; border-radius: 10px; background: var(--primary-l); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 15px; margin-right: 12px; margin-top: 2px; }

    /* Package Grid */
    .package-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(195px, 1fr)); gap: 14px; }
    .package-card {
        border: 2px solid var(--border); border-radius: var(--radius); padding: 20px 16px;
        text-align: center; cursor: pointer; transition: all 0.2s; background: #fff; position: relative;
    }
    .package-card:hover { border-color: var(--primary); box-shadow: 0 4px 16px rgba(222,52,47,.1); transform: translateY(-2px); }
    .package-card.selected,
    .package-card.current { border-color: var(--primary); background: var(--primary-l); }
    .package-card.disabled { opacity: 0.6; cursor: not-allowed; border-color: var(--border); background: #f8f9fa; pointer-events: none; }
    .package-card.disabled:hover { transform: none; box-shadow: none; border-color: var(--border); }
    .package-card.disabled .pkg-select-btn { background: var(--muted); cursor: not-allowed; }
    .pkg-label { position: absolute; top: -1px; left: 50%; transform: translateX(-50%); background: var(--primary); color: #fff; font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 0 0 8px 8px; text-transform: uppercase; letter-spacing: .5px; white-space: nowrap; }
    .pkg-name  { font-weight: 800; font-size: 1rem; color: var(--text); margin-top: 10px; margin-bottom: 6px; }
    .pkg-price { font-size: 1.2rem; font-weight: 800; color: var(--primary); margin-bottom: 4px; }
    .pkg-duration { font-size: 12px; color: var(--muted); margin-bottom: 8px; }
    .pkg-new-expiry { font-size: 11.5px; color: #047857; background: #d1fae5; border-radius: 8px; padding: 5px 8px; margin-bottom: 12px; }
    .pkg-select-btn {
        width: 100%; border: none; background: var(--primary); color: #fff;
        border-radius: 8px; padding: 9px 12px; font-size: 13px; font-weight: 700;
        cursor: pointer; transition: all 0.2s;
    }
    .pkg-select-btn:hover { background: var(--primary-d); }
    .package-card.selected .pkg-select-btn { background: var(--primary-d); }

    /* Payment */
    .modern-payment-item { background: #fff; }
    .modern-payment-item:hover { border-color: var(--primary-l) !important; background: #fafafa; }
    .modern-payment-item.active { border-color: var(--primary) !important; background-color: var(--primary-l) !important; box-shadow: 0 4px 12px rgba(222,52,47,.08); }
    .manual-bank-item.active { border-color: var(--primary) !important; background-color: #fff !important; }

    .btn-modern-submit {
        background: var(--primary); color: #fff; width: 100%; border: none; border-radius: 12px;
        font-size: 16px; font-weight: 700; padding: 15px; cursor: pointer;
        transition: all 0.25s; box-shadow: 0 8px 24px rgba(222,52,47,0.2);
    }
    .btn-modern-submit:hover { background: var(--primary-d); transform: translateY(-2px); }
    .btn-modern-submit:disabled { background: var(--muted); transform: none; box-shadow: none; cursor: not-allowed; }

    @keyframes fadeInUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
    .slide-up-1 { animation: fadeInUp 0.4s ease forwards; }
    .slide-up-2 { animation: fadeInUp 0.5s ease forwards; animation-delay: 0.08s; opacity: 0; }
    .slide-up-3 { animation: fadeInUp 0.5s ease forwards; animation-delay: 0.16s; opacity: 0; }
    .gap-2 { gap: 8px; }

    .btn-back:hover { text-decoration: none; }
</style>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Constants (from backend)
const PPN_SETTINGS = @json($ppnSettings ?? ['rate' => 0]);

let selectedPkg = null;

function formatRupiah(amount) {
    return 'Rp ' + Math.round(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function selectPackage(pkgId, pkgName, pkgPrice, pkgDays, newExpiryRaw) {
    selectedPkg = { id: pkgId, name: pkgName, price: pkgPrice, days: pkgDays, newExpiry: newExpiryRaw };

    // Highlight package card
    document.querySelectorAll('.package-card').forEach(c => c.classList.remove('selected'));
    const card = document.getElementById('pkg-card-' + pkgId);
    if (card) card.classList.add('selected');

    // Update order summary
    updateSummary();

    // Show summary section
    const summary = document.getElementById('order-summary');
    if (summary.style.display === 'none') {
        summary.style.display = '';
        summary.style.opacity = '0';
        summary.style.transform = 'translateY(12px)';
        setTimeout(() => {
            summary.style.transition = 'all 0.35s';
            summary.style.opacity = '1';
            summary.style.transform = 'translateY(0)';
        }, 10);
    }

    // Scroll to summary
    setTimeout(() => {
        summary.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 150);

    // Set hidden input
    document.getElementById('input-package-id').value = pkgId;
}

function updateSummary(gateway) {
    if (!selectedPkg) return;

    const ppnRate   = parseFloat(PPN_SETTINGS.rate ?? 0);
    const ppnAmount = selectedPkg.price * (ppnRate / 100);
    // UI always shows total (harga + PPN) — gateway_amount handled by backend service
    const total     = selectedPkg.price + ppnAmount;

    document.getElementById('summary-package').innerHTML   = `<span class="custom-badge status-primary">${selectedPkg.name}</span>`;
    document.getElementById('summary-duration').textContent = `${selectedPkg.days} hari`;
    document.getElementById('summary-subtotal').textContent = formatRupiah(selectedPkg.price);
    document.getElementById('summary-total').textContent    = formatRupiah(total);

    // PPN row — always show if rate > 0
    const ppnRow = document.getElementById('summary-ppn-row');
    if (ppnRate > 0) {
        ppnRow.style.display = 'table-row';
        document.getElementById('summary-ppn-label').textContent  = `PPN (${ppnRate}%)`;
        document.getElementById('summary-ppn-amount').textContent = formatRupiah(ppnAmount);
    } else {
        ppnRow.style.display = 'none';
    }

    // New expiry date
    if (selectedPkg.newExpiry) {
        const d = new Date(selectedPkg.newExpiry);
        const opts = { year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('summary-new-expiry').textContent = d.toLocaleDateString('id-ID', opts);
    } else {
        document.getElementById('summary-new-expiry').textContent = '-';
    }
}


$(document).ready(function() {

    // Package card click
    $('.package-card').on('click', function() {
        const btn   = $(this).find('.pkg-select-btn');
        const id    = btn.data('id');           // UUID string — jangan parseInt!
        const name  = btn.data('name');
        const price = parseFloat(btn.data('price'));
        const days  = parseInt(btn.data('days'));
        const currentExpiry = "{{ $subscription->tanggal_expired ? \Carbon\Carbon::parse($subscription->tanggal_expired)->format('Y-m-d') : '' }}";
        // Calculate new expiry from JS
        const expDate = currentExpiry ? new Date(currentExpiry) : new Date();
        expDate.setDate(expDate.getDate() + days);
        const yyyy = expDate.getFullYear();
        const mm   = String(expDate.getMonth() + 1).padStart(2,'0');
        const dd   = String(expDate.getDate()).padStart(2,'0');
        selectPackage(id, name, price, days, `${yyyy}-${mm}-${dd}`);
    });

    // Payment method selection
    $('.modern-payment-item').on('click', function(e) {
        if (!$(e.target).is(':radio')) {
            $(this).find('.payment-method-checker').prop('checked', true).trigger('change');
        }
    });

    $('.payment-method-checker').on('change', function() {
        $('.modern-payment-item').removeClass('active').css('border-color', 'var(--border)');
        $(this).closest('.modern-payment-item').addClass('active').css('border-color', 'var(--primary)');

        const method = $(this).val();
        $('#input-payment-gateway').val(method);
        $('#manual-bank-details').toggle(method === 'manual');

        // Re-calculate summary with the new gateway's PPN rule
        updateSummary(method);
    });

    $('.bank-checker').on('change', function() {
        $('.manual-bank-item').removeClass('active').css('border-color', 'var(--border)');
        $(this).closest('.manual-bank-item').addClass('active').css('border-color', 'var(--primary)');
        $('#input-selected-bank').val($(this).val());
    });

    // Init defaults
    $('#input-payment-gateway').val($('.payment-method-checker:checked').val());
    // Init selected_bank from the first auto-checked bank radio
    var checkedBank = $('.bank-checker:checked');
    if (checkedBank.length) {
        $('#input-selected-bank').val(checkedBank.val());
    }

    // Submit
    $('#btn-submit-renew').on('click', function(e) {
        e.preventDefault();

        if (!selectedPkg) {
            Swal.fire({ icon: 'warning', title: 'Belum Ada Paket', text: 'Silakan pilih paket perpanjangan terlebih dahulu.', confirmButtonColor: '#de342f' });
            return;
        }
        if (!$('#input-payment-gateway').val()) {
            Swal.fire({ icon: 'error', title: 'Pilih Metode Bayar', text: 'Silakan pilih metode pembayaran.', confirmButtonColor: '#de342f' });
            return;
        }
        if ($('#input-payment-gateway').val() === 'manual' && !$('#input-selected-bank').val()) {
            Swal.fire({ icon: 'error', title: 'Pilih Bank', text: 'Silakan pilih bank tujuan transfer.', confirmButtonColor: '#de342f' });
            return;
        }

        $('#btn-submit-renew').prop('disabled', true).find('.btn-text').hide();
        $('#btn-submit-renew .spinner').show();
        $('#renew-form').submit();
    });

    $('#renew-form').on('submit', function() {
        $('#btn-submit-renew').prop('disabled', true);
    });
});
</script>
@stop