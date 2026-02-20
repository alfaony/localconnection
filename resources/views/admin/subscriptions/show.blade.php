@extends('adminlte::page')

@section('title', 'Detail Subscription')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>Detail Subscription</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('software-dashboard.index') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('subscription.index') }}">Subscriptions</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    @include('components.alert')
    <div class="row">
        {{-- Subscription Info --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Subscription</h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $subscription->status_badge }} badge-lg">
                            {{ ucfirst($subscription->status) }}
                        </span>
                        <span class="badge badge-{{ $subscription->payment_status == 'paid' ? 'success' : 'warning' }} badge-lg">
                            {{ ucfirst($subscription->payment_status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="200">Order Number</th>
                            <td><strong>{{ $subscription->order_number }}</strong></td>
                        </tr>
                        <tr>
                            <th>Customer</th>
                            <td>
                                {{ $subscription->user->name }}
                                <br><small class="text-muted">{{ $subscription->user->email }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Software</th>
                            <td>
                                <span class="badge badge-info">
                                    {{ $subscription->masterAccount->software->nama }} - {{ $subscription->masterAccount->software->tipe_paket }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Master Account</th>
                            <td>{{ $subscription->masterAccount->nama_akun }}</td>
                        </tr>
                        <tr>
                            <th>Package</th>
                            <td>
                                {{ $subscription->package->nama_paket }}
                                <small class="text-muted">({{ $subscription->package->durasi_hari }} hari)</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Harga Bayar</th>
                            <td><strong>Rp {{ number_format($subscription->harga_bayar, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <th>Tanggal Mulai</th>
                            <td>
                                @if($subscription->tanggal_mulai)
                                    {{ \Carbon\Carbon::parse($subscription->tanggal_mulai)->format('d M Y') }}
                                @else
                                    <small class="text-muted">Belum aktif</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Tanggal Expired</th>
                            <td>
                                @if($subscription->tanggal_expired)
                                    {{ carbon\carbon::parse($subscription->tanggal_expired)->format('d m y') }}
                                    @if($subscription->isExpiringSoon(7) && $subscription->status == 'active')
                                        <br><small class="text-danger">
                                            <i class="fas fa-exclamation-triangle"></i> {{ $subscription->days_until_expiry }} hari lagi
                                        </small>
                                    @endif
                                @else
                                    <small class="text-muted">-</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Dibuat</th>
                            <td>{{ $subscription->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Payment History --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Pembayaran</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>External ID</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Paid At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscription->payments as $payment)
                                <tr>
                                    <td>{{ $payment->xendit_external_id }}</td>
                                    <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $payment->status_badge }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($payment->paid_at)
                                            {{ $payment->paid_at->format('d M Y H:i') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada pembayaran</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Chat Section --}}
            @canAccess('index','customer_subscriptions')
            @canAccess('store','customer_subscriptions')
            <div class="card" id="chat-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-comments"></i> Chat Instalasi
                        @if(!$subscription->canChat())
                            <span class="badge badge-secondary ml-2">Read-only</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if(!$subscription->canChat())
                    <div class="alert alert-warning m-3 mb-0">
                        <i class="fas fa-lock"></i>
                        @if($subscription->payment_status != 'paid')
                            Belum ada pembayaran. Chat akan aktif setelah pembayaran berhasil.
                        @elseif($subscription->status == 'expired')
                            Subscription sudah <strong>expired</strong>. Chat hanya bisa dibaca.
                        @elseif($subscription->status == 'suspended')
                            Subscription sedang <strong>suspended</strong>. Chat hanya bisa dibaca.
                        @else
                            Chat tidak tersedia.
                        @endif
                    </div>
                    @endif

                    <div id="chat-messages" style="height: 350px; overflow-y: auto; padding: 15px; background: #f8f9fa;">
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-spinner fa-spin"></i> Memuat pesan...
                        </div>
                    </div>

                    @if($subscription->canChat())
                    <div class="card-footer p-2">
                        <form id="chat-form" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="text" id="chat-input" name="message" class="form-control"
                                       placeholder="Tulis pesan ke customer..." autocomplete="off">
                                <div class="input-group-append">
                                    <label class="btn btn-outline-secondary mb-0" title="Lampirkan file">
                                        <i class="fas fa-paperclip"></i>
                                        <input type="file" id="chat-file" name="attachment" style="display:none"
                                               accept=".jpg,.jpeg,.png,.pdf">
                                    </label>
                                    <button type="submit" class="btn btn-primary" id="chat-send">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="file-preview" class="mt-1" style="display:none;">
                                <small class="text-muted"><i class="fas fa-file"></i> <span id="file-name"></span>
                                    <a href="#" id="remove-file" class="text-danger ml-1"><i class="fas fa-times"></i></a>
                                </small>
                            </div>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            @endcanAccess
            @endcanAccess
            
        </div>

        {{-- Actions --}}
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Aksi</h3>
                </div>
                <div class="card-body">
                    @if($subscription->status == \App\Schemas\ParamSchema::ACTIVE)
                        @canAccess('editExpiry', 'subscriptions')
                        <a href="{{ route('subscription.edit-expiry', $subscription) }}" class="btn btn-warning btn-block">
                            <i class="fas fa-calendar"></i> Ubah Tanggal Expired
                        </a>
                        @endcanAccess
                        
                        @canAccess('editMasterAccount', 'subscriptions')
                        <a href="{{ route('subscription.edit-master-account', $subscription) }}" class="btn btn-info btn-block mb-2">
                            <i class="fas fa-exchange-alt"></i> Ganti Master Account
                        </a>
                        @endcanAccess
                        
                        @canAccess('suspend', 'subscriptions')
                        <form action="{{ route('subscription.suspend', $subscription) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Yakin ingin suspend subscription ini?')">
                                <i class="fas fa-ban"></i> Suspend Subscription
                            </button>
                        </form>
                        @endcanAccess
                    @elseif($subscription->status == \App\Schemas\ParamSchema::SUSPENDED)
                        @canAccess('activate', 'subscriptions')
                        <form action="{{ route('subscription.activate', $subscription) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Yakin ingin activate subscription ini?')">
                                <i class="fas fa-check"></i> Activate Subscription
                            </button>
                        </form>
                        @endcanAccess
                    @endif
                    
                    <hr>
                    
                    <a href="{{ route('subscription.payments', $subscription) }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-money-bill"></i> Lihat Semua Payments
                    </a>
                    
                    <a href="{{ route('subscription.index') }}" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> Kembali ke List
                    </a>
                </div>
            </div>

            {{-- Credentials Info (if active & paid) --}}
            @if($subscription->isActivePaid())
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">Informasi Akses</h3>
                </div>
                <div class="card-body">
                    @php $ma = $subscription->masterAccount; @endphp
                    
                    @if($ma->email_akun)
                    <div class="form-group">
                        <label>Email Akun:</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $ma->email_akun }}" readonly>
                    </div>
                    @endif
                    
                    @if($ma->password_akun)
                    <div class="form-group">
                        <label>Password:</label>
                        <div class="input-group input-group-sm">
                            <input type="password" id="password-field" class="form-control" value="{{ $ma->password_akun }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($ma->pin_code)
                    <div class="form-group">
                        <label>PIN Code:</label>
                        <input type="text" class="form-control form-control-sm" value="{{ $ma->pin_code }}" readonly>
                    </div>
                    @endif
                    
                    @if($ma->link_invite)
                    <div class="form-group">
                        <label>Link Invite:</label>
                        <a href="{{ $ma->link_invite }}" target="_blank" class="btn btn-sm btn-info btn-block">
                            <i class="fas fa-external-link-alt"></i> Buka Link
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
@stop

@section('css')
<style>
.chat-bubble { max-width: 75%; word-break: break-word; }
.chat-bubble .bubble { padding: 8px 12px; border-radius: 12px; font-size: 13px; }
.chat-bubble.me { margin-left: auto; text-align: right; }
.chat-bubble.me .bubble { background: #007bff; color: #fff; border-bottom-right-radius: 2px; }
.chat-bubble.other .bubble { background: #fff; border: 1px solid #dee2e6; border-bottom-left-radius: 2px; }
.chat-bubble .meta { font-size: 10px; color: #6c757d; margin-top: 2px; }
.chat-bubble.me .meta { text-align: right; }
.chat-attachment img { max-width: 200px; border-radius: 8px; margin-top: 4px; cursor: pointer; }
</style>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-smooth-scroll/2.2.0/jquery.smooth-scroll.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

<!-- 🎵 Notifikasi Suara -->
 
<audio id="notification-sound-update" src="/audio/notification-update-item-request.mp3" preload="auto"></audio>
<!-- Tambahkan ini di <head> atau sebelum penutup </body> -->
<script src="https://cdn.jsdelivr.net/npm/pusher-js@7.2.0/dist/web/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
<script>
    $(document).ready(function() {
        const chatUrl        = '{{ route("subscription.chat.index", $subscription) }}';
        const storeUrl       = '{{ route("subscription.chat.store", $subscription) }}';
        const subscriptionId = '{{ $subscription->id }}';
        const myId           = parseInt('{{ auth()->id() }}');

        // ── Helpers ────────────────────────────────────────────────────────────
        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
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
            return `<div class="d-flex mb-2"><div class="chat-bubble ${side}"><div class="meta">${escapeHtml(msg.sender_name)} · ${msg.created_at}</div>${content}</div></div>`;
        }

        function appendMessage(msg) {
            const $box = $('#chat-messages');
            $box.find('.text-center.text-muted').remove();
            $box.append(buildBubble(msg));
            $box.scrollTop($box[0].scrollHeight);
        }

        // ── Load messages ──────────────────────────────────────────────────────
        function loadMessages() {
            $.get(chatUrl, function(data) {
                const $box = $('#chat-messages');
                $box.empty();
                if (data.messages.length === 0) {
                    $box.html('<div class="text-center text-muted py-4"><i class="fas fa-comments fa-2x mb-2"></i><br>Belum ada pesan.</div>');
                    return;
                }
                data.messages.forEach(function(msg) { $box.append(buildBubble(msg)); });
                $box.scrollTop($box[0].scrollHeight);
            });
        }

        // ── Send message ───────────────────────────────────────────────────────
        $('#chat-form').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const $btn = $('#chat-send').prop('disabled', true);
            $.ajax({
                url: storeUrl, type: 'POST', data: formData, processData: false, contentType: false,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    if (res.success) {
                        $('#chat-input').val('');
                        $('#chat-file').val('');
                        $('#file-preview').hide();
                        appendMessage(res.message);
                    }
                },
                error: function(xhr) { toastr.error(xhr.responseJSON?.error || 'Gagal mengirim pesan.'); },
                complete: function() { $btn.prop('disabled', false); }
            });
        });

        // ── File preview ───────────────────────────────────────────────────────
        $('#chat-file').on('change', function() {
            if (this.files[0]) { $('#file-name').text(this.files[0].name); $('#file-preview').show(); }
        });
        $('#remove-file').on('click', function(e) {
            e.preventDefault(); $('#chat-file').val(''); $('#file-preview').hide();
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

        // ── Init ───────────────────────────────────────────────────────────────
        loadMessages();
    });
</script>

<script>
    const reverbHost = '{{ config('services.connection_reverb.host') }}';
    const reverbKey  = '{{ config('services.connection_reverb.key') }}';
    const reverbPort = '{{ config('services.connection_reverb.port') }}';
    const adminSubscriptionId = '{{ $subscription->id }}';
    const adminCurrentId = '{{ auth()->id() }}';
    const notifSound = document.getElementById('notification-sound-update');

    window.Pusher = Pusher;

    window.Echo = new Echo.default({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: reverbHost,
        wsPort: 8080,
        wssPort: reverbPort,
        forceTLS: true,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/authorize',
        disableStats: true,
    });

    window.Echo.private('subscription.chat.' + adminSubscriptionId)
        .listen('SubscriptionChatSent', function(e) {
            console.log('ADMIN received:', e.sender_id, adminCurrentId);

            if (e.sender_id !== adminCurrentId) {
                const $box = $('#chat-messages');
                $box.find('.text-center.text-muted').remove();

                let content = '';
                if (e.message) content += `<div class="bubble">${e.message.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>`;
                if (e.attachment_url) {
                    const isImg = /\.(jpg|jpeg|png)$/i.test(e.attachment_url);
                    content += isImg
                        ? `<div class="chat-attachment"><img src="${e.attachment_url}" onclick="window.open(this.src)"></div>`
                        : `<div class="chat-attachment mt-1"><a href="${e.attachment_url}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-download"></i> Download</a></div>`;
                }

                $box.append(`<div class="d-flex mb-2"><div class="chat-bubble other"><div class="meta">${e.sender_name} · ${e.created_at}</div>${content}</div></div>`);
                $box.scrollTop($box[0].scrollHeight);

                notifSound?.play();
            }
        });
</script>
@endcanAccess
@endcanAccess
        
@stop
