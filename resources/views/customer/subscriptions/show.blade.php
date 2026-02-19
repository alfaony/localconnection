@extends('adminlte::page')

@section('title', 'Detail Subscription')

@section('content_header')
    <div class="row mb-2">
        <div class="col-sm-12">
            <a href="{{ route('customer-software.subscription.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali ke My Subscriptions
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        {{-- Subscription Info --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Informasi Langganan</h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $subscription->status == 'active' ? 'success' : 'danger' }} badge-lg">
                            {{ ucfirst($subscription->status) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 text-center">
                            @if($subscription->masterAccount->software->logo)
                            <img src="{{ s3_asset(true,10,$subscription->masterAccount->software->logo) }}" 
                                 alt="{{ $subscription->masterAccount->software->nama }}" 
                                 class="img-fluid"
                                 style="max-height: 120px;">
                            @else
                            <i class="fas fa-desktop fa-4x text-muted"></i>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <h3>{{ $subscription->masterAccount->software->nama }}</h3>
                            <p class="text-muted">{{ $subscription->masterAccount->software->tipe_paket }}</p>
                            <span class="badge badge-info">{{ $subscription->package->nama_paket }}</span>
                        </div>
                    </div>

                    <hr>

                    <table class="table">
                        <tr>
                            <th width="200">Order Number</th>
                            <td><strong>{{ $subscription->order_number }}</strong></td>
                        </tr>
                        <tr>
                            <th>Package</th>
                            <td>{{ $subscription->package->nama_paket }} ({{ $subscription->package->durasi_hari }} hari)</td>
                        </tr>
                        <tr>
                            <th>Harga</th>
                            <td><strong>Rp {{ number_format($subscription->harga_bayar, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <th>Tanggal Mulai</th>
                            <td>
                                @if($subscription->tanggal_mulai)
                                    {{ \Carbon\Carbon::parse($subscription->tanggal_mulai)->format('d M Y') }}
                                @else
                                    <small class="text-muted">Menunggu pembayaran</small>
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
                            <th>Status Pembayaran</th>
                            <td>
                                <span class="badge badge-{{ $subscription->payment_status == 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($subscription->payment_status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Payment History --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Riwayat Pembayaran</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Dibayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subscription->payments as $payment)
                                <tr>
                                    <td>{{ $payment->created_at->format('d M Y H:i') }}</td>
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
            @if($subscription->payment_status == 'paid')
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
                    {{-- Status info jika tidak bisa chat --}}
                    @if(!$subscription->canChat())
                    <div class="alert alert-warning m-3 mb-0">
                        <i class="fas fa-lock"></i>
                        @if($subscription->status == 'expired')
                            Subscription sudah <strong>expired</strong>. Chat hanya bisa dibaca.
                        @elseif($subscription->status == 'suspended')
                            Subscription sedang <strong>suspended</strong>. Chat hanya bisa dibaca.
                        @else
                            Chat tidak tersedia.
                        @endif
                    </div>
                    @endif

                    {{-- Chat Messages --}}
                    <div id="chat-messages" style="height: 350px; overflow-y: auto; padding: 15px; background: #f8f9fa;">
                        <div class="text-center text-muted py-4" id="chat-loading">
                            <i class="fas fa-spinner fa-spin"></i> Memuat pesan...
                        </div>
                    </div>

                    {{-- Chat Input --}}
                    @if($subscription->canChat())
                    <div class="card-footer p-2">
                        <form id="chat-form" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="text" id="chat-input" name="message" class="form-control"
                                       placeholder="Tulis pesan..." autocomplete="off">
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
            @endif
        </div>

        {{-- Credentials Access --}}
        <div class="col-md-4">
            @if($showCredentials)
            {{-- Show credentials only if active and paid --}}
            <div class="card card-success">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-key"></i> Informasi Akses
                    </h5>
                </div>
                <div class="card-body">
                    @php $ma = $subscription->masterAccount; @endphp
                    
                    @if($ma->email_akun)
                    <div class="form-group">
                        <label>Email Akun:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $ma->email_akun }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary copy-btn" data-copy="{{ $ma->email_akun }}" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($ma->password_akun)
                    <div class="form-group">
                        <label>Password:</label>
                        <div class="input-group">
                            <input type="password" id="password-field" class="form-control" value="{{ $ma->password_akun }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="toggle-password" title="Show/Hide">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline-secondary copy-btn" data-copy="{{ $ma->password_akun }}" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($ma->pin_code)
                    <div class="form-group">
                        <label>PIN Code:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $ma->pin_code }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary copy-btn" data-copy="{{ $ma->pin_code }}" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($ma->link_invite)
                    <div class="form-group">
                        <label>Link Invite:</label>
                        <a href="{{ $ma->link_invite }}" target="_blank" class="btn btn-info btn-block">
                            <i class="fas fa-external-link-alt"></i> Buka Link
                        </a>
                    </div>
                    @endif
                    
                    @if($ma->attachment)
                    <div class="form-group">
                        <label>File Attachment:</label>
                        <a href="{{ Storage::url($ma->attachment) }}" target="_blank" class="btn btn-secondary btn-block">
                            <i class="fas fa-file-download"></i> Download File
                        </a>
                    </div>
                    @endif
                    
                    @if($ma->instruksi_akses)
                    <hr>
                    <div class="form-group">
                        <label>Instruksi Akses:</label>
                        <div class="border p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                            {!! $ma->instruksi_akses !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @else
            {{-- Subscription not active or not paid --}}
            <div class="card card-warning">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lock"></i> Akses Terkunci
                    </h5>
                </div>
                <div class="card-body text-center">
                    @if($subscription->payment_status == 'unpaid')
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h6>Menunggu Pembayaran</h6>
                    <p class="text-muted">Silakan selesaikan pembayaran untuk mengakses kredensial</p>
                    @else
                    <i class="fas fa-ban fa-3x text-danger mb-3"></i>
                    <h6>Subscription Tidak Aktif</h6>
                    <p class="text-muted">Perpanjang langganan Anda untuk mengakses kembali</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Aksi</h5>
                </div>
                <div class="card-body">
                    @if($subscription->status == 'expired' || $subscription->isExpiringSoon(7))
                    <a href="{{ route('customer-software.subscription.renew', $subscription) }}" class="btn btn-success btn-block">
                        <i class="fas fa-sync"></i> Perpanjang Langganan
                    </a>
                    @endif
                    
                    <a href="{{ route('customer-software.subscription.payments', $subscription) }}" class="btn btn-info btn-block">
                        <i class="fas fa-money-bill"></i> Riwayat Pembayaran
                    </a>
                </div>
            </div>
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
<script>
$(document).ready(function() {
    const subscriptionId = '{{ $subscription->id }}';
    const chatUrl        = '{{ route("customer-software.subscription.chat.index", $subscription) }}';
    const storeUrl       = '{{ route("customer-software.subscription.chat.store", $subscription) }}';
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
                    appendMessage(res.message);
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

{{-- Reverb Echo: Setup sesuai pola item_request/show.blade.php --}}
@if($subscription->payment_status == 'paid')
<script>
    const _subId      = '{{ $subscription->id }}';
    const _myId       = parseInt('{{ auth()->id() }}');
    const _reverbHost = '{{ config('services.connection_reverb.host') }}';
    const _reverbKey  = '{{ config('services.connection_reverb.key') }}';
    const _reverbPort = '{{ config('services.connection_reverb.port') }}';

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
            if (parseInt(e.sender_id) !== _myId) {
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
            }
        });
</script>
@endif
@stop
