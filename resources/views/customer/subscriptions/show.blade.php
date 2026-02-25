@extends('adminlte::page')

@section('title', 'Detail Subscription')

@section('content_header')
    <div class="mb-4">
        <a href="{{ route('customer-subscription.index') }}" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke My Subscriptions
        </a>
    </div>
@stop

@section('content')
<div class="custom-container">
    <div class="modern-two-col">
        {{-- Left Column: Sub Info, Payment History, Chat --}}
        <div class="left-col" style="flex: 2;">
            {{-- Subscription Info --}}
            <div class="modern-card slide-up-1">
                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                    <h4 class="card-heading border-0 pb-0 mb-0">Informasi Langganan</h4>
                    <span class="custom-badge status-{{ $subscription->status == 'active' ? 'success' : 'danger' }} px-3 py-2" style="font-size: 13px;">
                        {{ ucfirst($subscription->status) }}
                    </span>
                </div>
                
                <div class="d-flex align-items-center mb-4">
                    <div class="flex-shrink-0 mr-4">
                        @if($subscription->masterAccount->software->logo)
                        <img src="{{ s3_asset(true, 10, $subscription->masterAccount->software->logo) }}" 
                             alt="{{ $subscription->masterAccount->software->nama }}" 
                             class="detail-logo m-0" style="width: 100px; height: 100px;">
                        @else
                        <div class="detail-logo-placeholder m-0" style="width: 100px; height: 100px; font-size: 36px;">
                            <i class="fas fa-desktop"></i>
                        </div>
                        @endif
                    </div>
                    <div>
                        <h2 class="detail-title mb-1">{{ $subscription->masterAccount->software->nama }}</h2>
                        <div class="text-muted mb-2 font-weight-bold">{{ $subscription->masterAccount->software->tipe_paket }}</div>
                        <span class="custom-badge status-info d-inline-block">{{ $subscription->package->nama_paket }}</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table modern-table border-top mt-3">
                        <tr>
                            <th width="180" class="text-muted">Order Number</th>
                            <td><strong class="text-dark">{{ $subscription->order_number }}</strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Package</th>
                            <td><strong class="text-dark">{{ $subscription->package->nama_paket }}</strong> <span class="text-muted ml-1">({{ $subscription->package->durasi_hari }} hari)</span></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Harga</th>
                            <td><strong class="text-success" style="font-size: 1.1rem;">Rp {{ number_format($subscription->harga_bayar, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <th class="text-muted">Tanggal Mulai</th>
                            <td>
                                @if($subscription->tanggal_mulai)
                                    <strong class="text-dark">{{ \Carbon\Carbon::parse($subscription->tanggal_mulai)->format('d M Y') }}</strong>
                                @else
                                    <small class="text-muted font-italic">Menunggu pembayaran</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Tanggal Expired</th>
                            <td>
                                @if($subscription->tanggal_expired)
                                    <strong class="text-dark">{{ carbon\carbon::parse($subscription->tanggal_expired)->format('d M Y') }}</strong>
                                    @if($subscription->isExpiringSoon(7) && $subscription->status == 'active')
                                        <br><span class="text-danger mt-1 d-inline-block font-weight-bold" style="font-size: 13px;">
                                            <i class="fas fa-exclamation-triangle"></i> {{ $subscription->days_until_expiry }} hari lagi
                                        </span>
                                    @endif
                                @else
                                    <small class="text-muted font-italic">-</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted align-middle border-bottom-0">Status Pembayaran</th>
                            <td class="border-bottom-0">
                                <span class="custom-badge payment-{{ $subscription->payment_status == 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($subscription->payment_status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Payment History --}}
            <div class="modern-card slide-up-2">
                <h4 class="card-heading mb-3 pt-2">Riwayat Pembayaran</h4>
                <div class="table-responsive">
                    <table class="table modern-table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-top-0 border-bottom-0 text-muted">Tanggal</th>
                                <th class="border-top-0 border-bottom-0 text-muted">Jumlah</th>
                                <th class="border-top-0 border-bottom-0 text-muted">Status</th>
                                <th class="border-top-0 border-bottom-0 text-muted">Dibayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subscription->payments as $payment)
                            <tr>
                                <td class="align-middle border-top text-dark">{{ $payment->created_at->format('d M Y H:i') }}</td>
                                <td class="align-middle border-top text-dark font-weight-bold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                <td class="align-middle border-top">
                                    <span class="custom-badge status-{{ strtolower($payment->status_badge) }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td class="align-middle border-top text-muted">
                                    @if($payment->paid_at)
                                        {{ $payment->paid_at->format('d M Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4 font-italic border-top">Belum ada pembayaran</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Chat Section --}}
            @if($subscription->payment_status == 'paid')
            @canAccess('index','customer_subscriptions')
            @canAccess('store','customer_subscriptions')

            <div class="modern-card p-0 overflow-hidden slide-up-3" id="chat-card">
                <div class="p-4 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <h4 class="card-heading m-0 border-0 p-0 text-dark">
                        <i class="fas fa-comments text-primary mr-2"></i> Chat Instalasi
                    </h4>
                    @if(!$subscription->canChat())
                        <span class="custom-badge status-default"><i class="fas fa-lock mr-1"></i> Read-only</span>
                    @endif
                </div>
                
                <div class="card-body p-0">
                    {{-- Status info jika tidak bisa chat --}}
                    @if(!$subscription->canChat())
                    <div class="status-alert warning m-3">
                        <i class="fas fa-lock icon text-warning"></i>
                        <div>
                        @if($subscription->status == 'expired')
                            Subscription sudah <strong>expired</strong>. Chat hanya bisa dibaca.
                        @elseif($subscription->status == 'suspended')
                            Subscription sedang <strong>suspended</strong>. Chat hanya bisa dibaca.
                        @else
                            Chat tidak tersedia.
                        @endif
                        </div>
                    </div>
                    @endif

                    {{-- Chat Messages --}}
                    <div id="chat-messages" style="height: 400px; overflow-y: auto; padding: 20px; background: #f8faff;">
                        <div class="text-center text-muted py-4" id="chat-loading">
                            <i class="fas fa-spinner fa-spin"></i> Memuat pesan...
                        </div>
                    </div>

                    {{-- Chat Input --}}
                    @if($subscription->canChat())
                    <div class="p-3 border-top bg-white">
                        <form id="chat-form" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="text" id="chat-input" name="message" class="form-control chat-input-field"
                                       placeholder="Tulis pesan..." autocomplete="off">
                                <div class="input-group-append">
                                    <label class="btn btn-outline-secondary mb-0 chat-btn d-flex align-items-center justify-content-center" title="Lampirkan file">
                                        <i class="fas fa-paperclip"></i>
                                        <input type="file" id="chat-file" name="attachment" style="display:none"
                                               accept=".jpg,.jpeg,.png,.pdf">
                                    </label>
                                    <button type="submit" class="btn btn-primary chat-btn d-flex align-items-center justify-content-center px-4" id="chat-send">
                                        <i class="fas fa-paper-plane mr-2"></i> Kirim
                                    </button>
                                </div>
                            </div>
                            <div id="file-preview" class="mt-2 pl-2" style="display:none;">
                                <small class="text-muted"><i class="fas fa-file text-primary"></i> <span id="file-name" class="font-weight-bold"></span>
                                    <a href="#" id="remove-file" class="text-danger ml-2 hover-opacity"><i class="fas fa-times"></i></a>
                                </small>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            
            @endcanAccess
            @endcanAccess
            @endif
        </div>

        {{-- Right Column: Credentials & Actions --}}
        <div class="right-col" style="flex: 1;">
            {{-- Credentials Access --}}
            @if($showCredentials)
            {{-- Show credentials only if active and paid --}}
            <div class="modern-card p-0 slide-up-1 overflow-hidden" style="border-top: 4px solid var(--success);">
                <div class="p-4 border-bottom bg-light">
                    <h5 class="card-title m-0 font-weight-bold text-dark d-flex align-items-center">
                        <i class="fas fa-key text-success mr-2"></i> Informasi Akses
                    </h5>
                </div>
                <div class="p-4">
                    @php $ma = $subscription->masterAccount; @endphp
                    
                    @if($ma->email_akun)
                    <div class="form-group mb-3">
                        <label class="text-muted font-weight-bold" style="font-size: 13px;">Email Akun:</label>
                        <div class="input-group">
                            <input type="text" class="form-control text-dark font-weight-bold bg-white" value="{{ $ma->email_akun }}" readonly style="border-radius: 8px 0 0 8px;">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary copy-btn" data-copy="{{ $ma->email_akun }}" title="Copy" style="border-radius: 0 8px 8px 0;">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($ma->password_akun)
                    <div class="form-group mb-3">
                        <label class="text-muted font-weight-bold" style="font-size: 13px;">Password:</label>
                        <div class="input-group">
                            <input type="password" id="password-field" class="form-control text-dark font-weight-bold bg-white" value="{{ $ma->password_akun }}" readonly style="border-radius: 8px 0 0 8px;">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="toggle-password" title="Show/Hide">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-secondary copy-btn" data-copy="{{ $ma->password_akun }}" title="Copy" style="border-radius: 0 8px 8px 0;">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($ma->pin_code)
                    <div class="form-group mb-3">
                        <label class="text-muted font-weight-bold" style="font-size: 13px;">PIN Code:</label>
                        <div class="input-group">
                            <input type="text" class="form-control text-dark font-weight-bold bg-white" value="{{ $ma->pin_code }}" readonly style="border-radius: 8px 0 0 8px;">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary copy-btn" data-copy="{{ $ma->pin_code }}" title="Copy" style="border-radius: 0 8px 8px 0;">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($ma->link_invite)
                    <div class="form-group mb-3 mt-4">
                        <a href="{{ $ma->link_invite }}" target="_blank" class="btn-modern info py-2 w-100">
                            <i class="fas fa-external-link-alt"></i> Buka Link Invite
                        </a>
                    </div>
                    @endif
                    
                    @if($ma->attachment)
                    <div class="form-group mb-3">
                        <a href="{{ s3_asset(true,10,$ma->attachment) }}" target="_blank" class="btn-modern secondary py-2 w-100">
                            <i class="fas fa-file-download mr-1"></i> Download Attachment
                        </a>
                    </div>
                    @endif
                    
                    @if($ma->instruksi_akses)
                    <div class="mt-4 pt-3 border-top">
                        <label class="text-muted font-weight-bold mb-2" style="font-size: 13px;">Instruksi Akses:</label>
                        <div class="p-3 bg-light rounded shadow-sm text-dark instruction-box" style="font-size: 14px; max-height: 250px; overflow-y: auto;">
                            {!! $ma->instruksi_akses !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @else
            {{-- Subscription not active or not paid --}}
            <div class="modern-card slide-up-1 text-center py-5 border-0" style="background: #fff8f1; border-top: 4px solid var(--warning) !important;">
                @if($subscription->payment_status == 'unpaid' && in_array($subscription->status, ['active', 'pending']))
                <div class="mb-4 text-warning" style="font-size: 48px;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="font-weight-bold text-dark mb-2">Menunggu Pembayaran</h5>
                <p class="text-muted" style="font-size: 14px;">Silakan selesaikan pembayaran untuk mengakses kredensial</p>

                @if($subscription->slot_deadline)
                <div class="mt-3 mx-4 p-3 rounded" style="background: {{ $subscription->isSlotExpired() ? '#fee2e2' : '#fef3c7' }}; border: 1px solid {{ $subscription->isSlotExpired() ? '#f87171' : '#fbbf24' }};">
                    @if($subscription->isSlotExpired())
                        <div class="text-danger font-weight-bold" style="font-size: 13px;">
                            <i class="fas fa-times-circle"></i> Reservasi slot sudah habis
                        </div>
                        <small class="text-muted">Slot akan segera dibebaskan oleh sistem</small>
                    @else
                        <div class="text-warning font-weight-bold" style="font-size: 13px;">
                            <i class="fas fa-clock"></i> Sisa waktu reservasi slot:
                        </div>
                        <div class="font-weight-bold text-dark mt-1" style="font-size: 16px;" id="slot-countdown">
                            {{ $subscription->slot_remaining }}
                        </div>
                        <small class="text-muted d-block mt-1">
                            Deadline: {{ $subscription->slot_deadline->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
                        </small>
                        <small class="text-danger d-block mt-1">
                            <i class="fas fa-info-circle"></i> Jika pembayaran tidak selesai, slot akan otomatis dibebaskan.
                        </small>
                    @endif
                </div>
                @endif

                @else
                <div class="mb-4 text-danger" style="font-size: 48px;">
                    <i class="fas fa-ban"></i>
                </div>
                <h5 class="font-weight-bold text-dark mb-2">Subscription Tidak Aktif</h5>
                <p class="text-muted" style="font-size: 14px;">Perpanjang langganan Anda untuk mengakses kembali kredensial</p>
                @endif
            </div>
            @endif

            {{-- Actions --}}
            <div class="modern-card p-0 mt-4 slide-up-2">
                <div class="p-3 border-bottom bg-light">
                    <h5 class="m-0 font-weight-bold text-dark d-flex align-items-center" style="font-size: 15px;">
                        <i class="fas fa-cog text-muted mr-2"></i> Aksi Lainnya
                    </h5>
                </div>
                <div class="p-4 d-flex flex-column gap-3">
                    @if($subscription->status == 'expired' || $subscription->isExpiringSoon(7))
                    <a href="{{ route('customer-subscription.renew', $subscription) }}" class="btn-modern success py-2">
                        <i class="fas fa-sync"></i> Perpanjang Langganan
                    </a>
                    @endif
                    
                    @canAccess('payments','customer_subscriptions')
                    <a href="{{ route('customer-subscription.payments', $subscription) }}" class="btn-modern info py-2 mt-2">
                        <i class="fas fa-money-bill"></i> Riwayat Pembayaran Lengkap
                    </a>
                    @endcanAccess
                </div>
            </div>
        </div>
    </div>
</div>
@stop


@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-smooth-scroll/2.2.0/jquery.smooth-scroll.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

<!-- 🎵 Notifikasi Suara -->
 
<audio id="notification-sound-update" src="/audio/notification-chat.mp3" preload="auto"></audio>
<!-- Tambahkan ini di <head> atau sebelum penutup </body> -->
<script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>

@canAccess('index','customer_subscriptions')
@canAccess('store','customer_subscriptions')
<script>
    $(document).ready(function() {
        const subscriptionId = '{{ $subscription->id }}';
        const chatUrl        = '{{ route("customer-subscription.chat.index", $subscription) }}';
        const storeUrl       = '{{ route("customer-subscription.chat.store", $subscription) }}';
        const myId           = parseInt('{{ auth()->id() }}');
        let canChat          = false;

        // ── Load messages (initial load) ───────────────────────────────────────
        function loadMessages() {
            $.get(chatUrl, function(data) {
                canChat = data.can_chat;
                const $box = $('#chat-messages');
                $box.empty();

                if (data.messages.length === 0) {
                    $box.html('<div class="text-center text-muted py-4"><i class="fas fa-comments fa-2x mb-2"></i><br>Belum ada pesan. Mulai percakapan!</div>');
                    return;
                }

                data.messages.forEach(function(msg) {
                    $box.append(buildBubble(msg));
                });
                $box.scrollTop($box[0].scrollHeight);
            });
        }

        function buildBubble(msg) {
            const isMe = (parseInt(msg.sender_id) === myId) || msg.is_me === true;
            const side = isMe ? 'me' : 'other';
            let content = '';
            if (msg.message) content += `<div class="bubble">${escapeHtml(msg.message)}</div>`;
            if (msg.attachment_url) {
                const isImg = /\.(jpg|jpeg|png)$/i.test(msg.attachment_url);
                content += isImg
                    ? `<div class="chat-attachment"><img src="${msg.attachment_url}" onclick="window.open(this.src)"></div>`
                    : `<div class="chat-attachment mt-1"><a href="${msg.attachment_url}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-download"></i> Download</a></div>`;
            }
            return `<div class="d-flex mb-2">
                <div class="chat-bubble ${side}">
                    <div class="meta">${escapeHtml(msg.sender_name)} · ${msg.created_at}</div>
                    ${content}
                </div>
            </div>`;
        }

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        function appendMessage(msg) {
            const $box = $('#chat-messages');
            // Hapus placeholder "belum ada pesan" jika ada
            $box.find('.text-center.text-muted').remove();
            $box.append(buildBubble(msg));
            $box.scrollTop($box[0].scrollHeight);
        }


        // ── Send message ───────────────────────────────────────────────────────
        $('#chat-form').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const $btn = $('#chat-send').prop('disabled', true);

            $.ajax({
                url: storeUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    if (res.success) {
                        $('#chat-input').val('');
                        $('#chat-file').val('');
                        $('#file-preview').hide();
                        // appendMessage(res.message);
                        loadMessages();
                    }
                },
                error: function(xhr) {
                    const err = xhr.responseJSON?.error || 'Gagal mengirim pesan.';
                    toastr.error(err);
                },
                complete: function() { $btn.prop('disabled', false); }
            });
        });

        // ── File preview ───────────────────────────────────────────────────────
        $('#chat-file').on('change', function() {
            if (this.files[0]) {
                $('#file-name').text(this.files[0].name);
                $('#file-preview').show();
            }
        });
        $('#remove-file').on('click', function(e) {
            e.preventDefault();
            $('#chat-file').val('');
            $('#file-preview').hide();
        });

        // ── Toggle password ────────────────────────────────────────────────────
        $('#toggle-password').on('click', function() {
            const passwordField = $('#password-field');
            const icon = $(this).find('i');
            if (passwordField.attr('type') === 'password') {
                passwordField.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // ── Copy to clipboard ──────────────────────────────────────────────────
        $('.copy-btn').on('click', function() {
            const text = $(this).data('copy');
            const btn = $(this);
            navigator.clipboard.writeText(text).then(function() {
                const originalHtml = btn.html();
                btn.html('<i class="fas fa-check"></i>');
                toastr.success('Berhasil disalin!');
                setTimeout(function() { btn.html(originalHtml); }, 2000);
            }).catch(function() { toastr.error('Gagal menyalin'); });
        });

        // ── Broadcast: Dengarkan pesan baru via Echo (Reverb) ─────────────────
        @if($subscription->payment_status == 'paid')
        loadMessages();
        @endif
    });
</script>

@if($subscription->payment_status == 'paid')
<script>
    const _subId      = '{{ $subscription->id }}';
    const _myId       = '{{ auth()->id() }}';
    const _reverbHost = '{{ config('services.connection_reverb.host') }}';
    const _reverbKey  = '{{ config('services.connection_reverb.key') }}';
    const _reverbPort = '{{ config('services.connection_reverb.port') }}';
    const notifSound = document.getElementById('notification-sound-update');

    console.log('Customer Echo ready for subscription:', _subId);
    
    window.Pusher = Pusher;

    window.Echo = new Echo.default({
        broadcaster: 'reverb',
        key: _reverbKey,
        wsHost: _reverbHost,
        wsPort: 8080,
        wssPort: _reverbPort,
        forceTLS: true,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/authorize',
        disableStats: true,
    });

    window.Echo.private('subscription.chat.' + _subId)
        .listen('SubscriptionChatSent', function(e) {
            
            // Hanya append jika bukan pesan sendiri (pengirim sudah dapat via AJAX response)
            if (e.sender_id !== _myId) {
                const $box = $('#chat-messages');
                $box.find('.text-center.text-muted').remove();

                const isImg = e.attachment_url && /\.(jpg|jpeg|png)$/i.test(e.attachment_url);
                let content = '';
                if (e.message) {
                    const safeMsg = e.message.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                    content += `<div class="bubble">${safeMsg}</div>`;
                }
                if (e.attachment_url) {
                    content += isImg
                        ? `<div class="chat-attachment"><img src="${e.attachment_url}" onclick="window.open(this.src)"></div>`
                        : `<div class="chat-attachment mt-1"><a href="${e.attachment_url}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-download"></i> Download</a></div>`;
                }

                $box.append(`<div class="d-flex mb-2">
                    <div class="chat-bubble other">
                        <div class="meta">${e.sender_name} · ${e.created_at}</div>
                        ${content}
                    </div>
                </div>`);
                $box.scrollTop($box[0].scrollHeight);

                notifSound?.play();
            }
        });
</script>
@endif
@endcanAccess
@endcanAccess

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
        --shadow:    0 4px 20px rgba(99,102,241,.08);
        --font-inter: 'Inter', sans-serif;
    }

    body { font-family: var(--font-inter); background: var(--bg); }

    .custom-container { max-width: 1200px; margin: 0 auto; padding-bottom: 40px; }

    .btn-back {
        display: inline-flex; align-items: center; gap: 8px;
        color: var(--muted); font-weight: 600; text-decoration: none;
        padding: 8px 16px; border-radius: 999px;
        background: #fff; border: 1px solid var(--border);
        transition: all 0.2s; font-size: 14px;
    }
    .btn-back:hover {
        background: var(--primary-l); color: var(--primary);
        border-color: var(--primary-l);
    }

    .modern-two-col {
        display: flex; gap: 24px; align-items: flex-start;
    }
    .left-col { flex: 0 0 340px; display: flex; flex-direction: column; gap: 24px; }
    .right-col { flex: 1; display: flex; flex-direction: column; gap: 24px; min-width: 0; }
    
    @media(max-width: 992px) {
        .modern-two-col { flex-direction: column; }
        .left-col, .right-col { flex: none; width: 100%; }
    }

    /* Cards */
    .modern-card {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 28px 24px;
        box-shadow: var(--shadow);
        animation: fadeInUp 0.5s ease backwards;
    }
    .card-heading {
        font-size: 1.1rem; font-weight: 700; color: var(--text);
        margin-bottom: 16px; border-bottom: 1px solid var(--border);
        padding-bottom: 12px;
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
    .detail-title {
        font-size: 1.5rem; font-weight: 800; color: var(--text);
    }

    /* Badges */
    .custom-badge {
        display: inline-flex; align-items: center; padding: 4px 10px;
        border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .5px; margin-right: 6px;
    }
    .status-success { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
    .status-warning { background: #fef3c7; color: #b45309; border: 1px solid #fbbf24; }
    .status-danger { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
    .status-info { background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
    .status-default { background: #f1f5f9; color: #475569; }
    .payment-success { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
    .payment-warning { background: #fef3c7; color: #b45309; border: 1px solid #fbbf24; }

    /* Tables */
    .modern-table { font-size: 14.5px; }
    .modern-table th { font-weight: 600; padding-top: 14px; padding-bottom: 14px; }
    .modern-table td { padding-top: 14px; padding-bottom: 14px; }

    /* Forms & Inputs */
    /* .form-control:not(.chat-input-field) {
        border: 1px solid var(--border);
        padding: 10px 14px;
        height: auto;
        box-shadow: none !important;
    } */
    .form-control:focus { border-color: var(--primary); }
    .chat-input-field {
        border: 1px solid var(--border); height: 46px; border-radius: 8px 0 0 8px;
        padding-left: 16px; box-shadow: 0 2px 6px rgba(0,0,0,0.02) inset;
    }
    .chat-input-field:focus { border-color: var(--primary); box-shadow: none; outline: none; }
    .chat-btn { border-radius: 0; height: 46px; border-color: var(--border); }
    #chat-send { border-radius: 0 8px 8px 0; border-color: var(--primary); }
    .hover-opacity:hover { opacity: 0.8; }

    /* Alerts */
    .status-alert {
        display: flex; gap: 12px; text-align: left; padding: 14px 18px;
        border-radius: 12px; font-size: 14px; align-items: flex-start;
    }
    .status-alert.warning { background: #fffbeb; color: #92400e; border: 1px solid #fcd34d; }

    /* Instructions Box */
    .instruction-box ul { padding-left: 20px; margin-bottom: 0; }
    .instruction-box li { margin-bottom: 8px; }

    /* Buttons */
    .btn-modern {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        border-radius: 8px; font-size: 14px; font-weight: 600;
        text-decoration: none; cursor: pointer; border: none; transition: all .2s;
    }
    .btn-modern.success { background: var(--success); color: #fff; }
    .btn-modern.success:hover { background: #059669; color: #fff; box-shadow: 0 4px 12px rgba(16,185,129,.3); }
    .btn-modern.info { background: #eff6ff; color: var(--info); border: 1px solid #bfdbfe; }
    .btn-modern.info:hover { background: #dbeafe; }
    .btn-modern.secondary { background: #f8fafc; color: var(--muted); border: 1px solid var(--border); }
    .btn-modern.secondary:hover { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }

    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .slide-up-1 { animation-delay: 0.05s; }
    .slide-up-2 { animation-delay: 0.1s; }
    .slide-up-3 { animation-delay: 0.15s; }

    /* Chat Styling enhancements */
    .chat-bubble { max-width: 80%; word-break: break-word; }
    .chat-bubble .bubble { 
        padding: 10px 14px; border-radius: 16px; font-size: 14px; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        line-height: 1.5;
    }
    .chat-bubble.me { margin-left: auto; text-align: right; }
    .chat-bubble.me .bubble { 
        background: var(--primary); color: #fff; 
        border-bottom-right-radius: 4px; border: none;
    }
    .chat-bubble.other .bubble { 
        background: #fff; border: 1px solid var(--border); 
        border-bottom-left-radius: 4px; color: var(--text);
    }
    .chat-bubble .meta { font-size: 11px; color: var(--muted); margin-bottom: 4px; font-weight: 500; }
    .chat-attachment img { 
        max-width: 250px; border-radius: 12px; margin-top: 6px; cursor: pointer; 
        border: 1px solid var(--border); padding: 4px; background: #fff;
    }
    
    /* Scrollbar for chat */
    #chat-messages::-webkit-scrollbar { width: 6px; }
    #chat-messages::-webkit-scrollbar-track { background: transparent; }
    #chat-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    #chat-messages::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@stop
