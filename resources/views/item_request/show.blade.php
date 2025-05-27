@extends('adminlte::page')

@section('title', 'Detail Permintaan Barang')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-white shadow-sm">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-info"><i class="fas fa-home"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('item-request.index') }}" class="text-info">Permintaan Barang</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $itemRequest->item_name }}</li>
        </ol>
    </nav>
    
    @include('components.alert')

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-12">
            <!-- Item Details Card -->
            <div class="card card-primary card-outline">
                <div class="card-header bg-gradient-primary d-flex align-items-center">
                    <h3 class="card-title text-white flex-grow-1">
                        <i class="fas fa-box-open mr-2"></i>{{ $itemRequest->item_name }}
                    </h3>
                    <span class="badge badge-light">ID: #{{ $itemRequest->id }}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <div class="card img-hover-zoom">
                                <img src="{{ Storage::url($itemRequest->picture) }}" class="card-img-top" 
                                    alt="Item Image" style="height: 200px; object-fit: cover;">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <dl class="row">
                                <dt class="col-sm-4 text-info"><i class="fas fa-tag mr-2"></i>Kategori</dt>
                                <dd class="col-sm-8">{{ $itemRequest->category->name ?? 'N/A' }}</dd>

                                <dt class="col-sm-4 text-info"><i class="fas fa-align-left mr-2"></i>Deskripsi</dt>
                                <dd class="col-sm-8">{!! $itemRequest->description !!}</dd>

                                <dt class="col-sm-4 text-info"><i class="fas fa-coins mr-2"></i>Estimasi Harga</dt>
                                <dd class="col-sm-8">Rp{{ number_format($itemRequest->estimated_price) }}</dd>

                                <dt class="col-sm-4 text-info"><i class="fas fa-cubes mr-2"></i>Kuantitas</dt>
                                <dd class="col-sm-8">{{ $itemRequest->qty }} Unit</dd>
                                <dt class="col-sm-4 text-info"><i class="fas fa-info-circle mr-2"></i>Status</dt>
                                <dd class="col-sm-8">{!! $itemRequest->status_badge !!}</dd>

                                <dt class="col-sm-4 text-info"><i class="fas fa-door-open mr-2"></i>Open Status</dt>
                                <dd class="col-sm-8">
                                    <span class="badge {{ $itemRequest->is_open ? 'badge-success' : 'badge-danger' }} rounded-pill">
                                        {{ $itemRequest->is_open ? 'Open' : 'Closed' }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        
    </div>
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-8">
            <!-- Workflow Timeline Card -->
            <div class="card card-info card-outline ">
                <div class="card-header bg-gradient-info">
                    <h3 class="card-title text-white"><i class="fas fa-project-diagram mr-2"></i>Alur Proses Pengadaan</h3>
                </div>
                <div class="card-body pt-4">
                    <div class="workflow-timeline" id="workflow-wrapper">
                        
                    </div>
                </div>
            </div>
        </div>        
        <div class="col-lg-4">
            <!-- Vendor List Card -->
            {{-- 
            <div class="card card-success card-outline">
                <div class="card-header bg-gradient-success">
                    <h3 class="card-title text-white"><i class="fas fa-store-alt mr-2"></i>Daftar Vendor</h3>
                </div>
                <div class="card-body vendor-scroll" style="max-height: 400px; overflow-y: auto;">
                    <div class="vendor-card card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="vendor-avatar mr-3">
                                    <img src="https://via.placeholder.com/50" class="rounded-circle" alt="Vendor">
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">Toko Elektronik Maju Jaya</h6>
                                    <div class="d-flex align-items-center">
                                        <small class="text-muted">Rating: 4.8/5</small>
                                        <div class="star-rating ml-2">
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star text-warning"></i>
                                            <i class="fas fa-star-half-alt text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-info btn-block mt-2 btn-sm">
                                <i class="fas fa-handshake"></i> Pilih Vendor
                            </button>
                        </div>
                    </div>
                    <!-- Repeat Vendor Cards -->
                </div>
            </div>
            --}}
    
            <!-- Live Chat Card -->
            <div class="card card-primary card-outline" id="chat-wrapper">
                <div class="card-header bg-gradient-primary">
                    <h3 class="card-title text-white"><i class="fas fa-comments mr-2"></i>Live Chat</h3>
                </div>
                <div class="card-body p-0">
                    <div class="chat-container p-3" id="chat-container" style="height: 300px; overflow-y: auto;">
                        <div class="text-center" id="chat-loading">
                            <i class="fas fa-spinner fa-spin"></i> Loading chat...
                        </div>
                    </div>
                    <div class="chat-input p-3 border-top">
                        <form id="chat-form">
                            <div class="input-group">
                                <input type="text" class="form-control" id="chat-message" placeholder="Ketik pesan..." required>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal Pilih Vendor -->
<div class="modal fade" id="selectVendorModal" tabindex="-1" role="dialog" aria-labelledby="selectVendorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="vendor-billing-form"  method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item_request_id" value="{{ $itemRequest->id }}">
        <input type="hidden" name="product_supplier_id" id="modal_vendor_id">

        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title"><i class="fas fa-file-invoice-dollar mr-2"></i>Konfirmasi Vendor & Penagihan</h5>
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              <div id="modal_vendor_info" class="mb-3 text-muted small"></div>
              <div class="form-group">

              </div>
              <div class="form-group">
                  <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" id="is_finished" name="is_finished">
                      <label class="custom-control-label" for="is_finished">Apakah proses pembelian ini sudah selesai?</label>
                    </div>
            </div>
              <div class="form-group">
                  <label for="purchase_date">Tenggat Tanggal Pembayaran</label>
                  <input type="date" class="form-control" name="payment_term_date" required>
              </div>

              <div class="form-group">
                  <label for="amount">Total Pembelian (Rp)</label>
                  <input type="text" class="form-control @error('estimated_price') is-invalid @enderror" id="estimated_price_show" placeholder="30.000.000" oninput="formatRupiahFormat(this,'estimated_price')" required/>
                  <input type="hidden" id="estimated_price" name="actual_price">
              </div>
              <div class="form-group">
                  <label for="payment_method">Metode Pembayaran</label>
                  <select class="form-control" name="payment_method" required>
                      <option value="">- Pilih -</option>
                      <option value="TRANSFER">Transfer</option>
                      <option value="CASH">Cash</option>
                  </select>
              </div>

              <div class="form-group">
                  <label for="rekening_number">Nomor Rekening</label>
                  <input type="text" class="form-control" name="rekening_number">
              </div>
                <div class="form-group">
                    <label for="bon_image">Upload Foto Bon</label>
                    <input type="file" class="form-control-file" name="bon_photo" accept="image/*" capture="environment" required>
                </div>
              <div class="form-group">
                  <label for="note">Catatan</label>
                  <textarea class="form-control" name="note" rows="3"></textarea>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-paper-plane"></i> Simpan & Proses
            </button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          </div>
        </div>
    </form>
  </div>
</div>

<!-- Modal Upload Transfer -->
<div class="modal fade" id="uploadTransferModal" tabindex="-1" role="dialog" aria-labelledby="uploadTransferModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="form-upload-payment" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" id="item_purchase_id_input">

            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="uploadTransferModalLabel"><i class="fas fa-file-invoice-dollar mr-2"></i>Upload Bukti Transfer</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Bukti Transfer (JPG, PNG, PDF)</label>
                        <input type="file" name="proof_image" class="form-control-file" accept="image/*,application/pdf" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload mr-2"></i>Upload
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/js/all.min.js"></script>
<script src="https://cdn.socket.io/4.5.0/socket.io.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-smooth-scroll/2.2.0/jquery.smooth-scroll.min.js"></script>
<!-- 🎵 Notifikasi Suara -->
 
<audio id="notification-sound" src="/audio/notification-sound.mp3" preload="auto"></audio>

<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.3.0/socket.io.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.3/dist/echo.iife.js"></script>
<script>
    const SOCKET_IO_HOST = @json(config('services.setting.app_socet_url'));

    const socket = io(SOCKET_IO_HOST, {
        transports: ['websocket'],
        secure: true
    });

    if (typeof window !== 'undefined' ) 
    {
        const socket = io(SOCKET_IO_HOST, {
            transports: ["polling"],
            secure: false,
        });
        
        socket.on("connect", () => {
            console.log("[Socket.IO] Connected with ID:", socket.id);
        });

        socket.on("connect_error", (error) => {
            console.error("[Socket.IO] Connection Error:", error);
        });

        socket.on("disconnect", (reason) => {
            console.warn("[Socket.IO] Disconnected:", reason);
        });

         echo = new Echo({
            broadcaster: "socket.io",
            host: SOCKET_IO_HOST,
            secure: false,
            client:io,
            transports: ["polling"],
            withCredentials: true // ⬅️ WAJIB agar cookie dikirim
        });

         echo.connector.socket.on('connect', () => 
         {
            console.log('✅ Echo connected successfully!');
        });

          echo.connector.socket.on('connect_error', (err) => 
          {
            console.error('❌ Echo connection failed:', err);
        });

        const chatContainer = document.getElementById('chat-container');
        const notifSound = document.getElementById('notification-sound');
        const itemRequestId = '{{ $itemRequest->id }}';
        
        echo.channel('chat.item-request.' + itemRequestId)
            .listen('ChatMessageSent', (e) => {
                console.log("📩 Real-time message:", e);

                const html = `
                    <div class="mb-2">
                        <strong>${e.sender_name}:</strong> ${e.message}
                        <div class="text-muted" style="font-size: 12px;">
                            ${new Date(e.created_at).toLocaleTimeString()}
                        </div>
                    </div>`;
                chatContainer.innerHTML += html;
                chatContainer.scrollTop = chatContainer.scrollHeight;

                notifSound?.play();
            });
    }    
</script>

<script>
    $(document).on('submit', '#form-upload-delivery', function (e) {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);
        const id = document.getElementById('item_purchase_id').value; // pastikan ID ada

        formData.append('_method', 'PUT');

        $.ajax({
            url: `/item-request/delivery/${id}`,
            method: 'POST', // tetap POST karena spoof PUT
            data: formData,
            processData: false, // WAJIB
            contentType: false, // WAJIB
            beforeSend: () => {
                Swal.fire({ title: 'Mengirim...', didOpen: () => Swal.showLoading() });
            },
            success: function (res) {
                Swal.fire('Berhasil', 'Data pengiriman berhasil disimpan', 'success').then(() => {
                    loadWorkflow();
                });
            },
            error: function (err) {
                console.error(err);
                Swal.fire('Gagal', 'Gagal mengirim data', 'error');
            }
        });
    });
</script>
<script>
     $(document).on('click', '.btn-upload-transfer', function () {
        const itemPurchaseId = $(this).data('id');
        $('#item_purchase_id_input').val(itemPurchaseId);
    });

    $('#form-upload-payment').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('_method', 'PUT');
        const itemPurchaseId = $('#item_purchase_id_input').val();
        

        $.ajax({
            url: `/item-purchase/payment/${itemPurchaseId}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: () => {
                Swal.fire({ title: 'Mengupload...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            },
            success: (res) => 
            {
                let timerInterval;
                Swal.fire({
                    title: 'Berhasil',
                    html: 'Bukti transfer berhasil diunggah!<br>Menutup dalam <b></b> detik.',
                    timer: 3000,
                    timerProgressBar: true,
                    willOpen: () => {
                        Swal.showLoading();
                        timerInterval = setInterval(() => {
                            const content = Swal.getHtmlContainer();
                            if (content) {
                                const b = content.querySelector('b');
                                if (b) {
                                    b.textContent = Math.ceil(Swal.getTimerLeft() / 1000);
                                }
                            }
                        }, 100);
                    },
                    onClose: () => {
                        clearInterval(timerInterval);
                    }
                }).then(() => {
                    loadWorkflow();
                });
            },
            error: (err) => {
                console.error(err);
                Swal.fire('Gagal', 'Upload gagal. Periksa input Anda.', 'error');
            }
        });
    });
</script>

<script>
    $(document).ready(function () 
    {
        $('#vendor-billing-form').on('submit', async function (e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);

            // Konfirmasi sebelum submit
            // const confirm = await Swal.fire({
            //     title: 'Kirim ke Finance?',
            //     html: 'Pastikan harga dan bon sudah benar. Data ini akan dikirim ke tim Finance untuk proses pembayaran.',
            //     icon: 'warning',
            //     showCancelButton: true,
            //     confirmButtonText: 'Ya, kirim sekarang!',
            //     cancelButtonText: 'Batal'
            // });

            // if (!confirm.isConfirmed) return;

            $.ajax({
                url: '{{ route("item-purchase.store") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: () => {
                    Swal.showLoading();
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data telah dikirim ke Finance.',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    $('#selectVendorModal').modal('hide');
                    // Optional: reload workflow section
                    loadWorkflow();
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan saat mengirim data.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: msg
                    });
                }
            });

        });
    });
    
    document.addEventListener('DOMContentLoaded', function () {
        $(document).on('click', '.btn-select-vendor', function () {
            const vendorId = $(this).data('vendor-id');
            const vendorName = $(this).data('vendor-name');
            const vendorPhone = $(this).data('vendor-phone');
            const vendorLocation = $(this).data('vendor-location');

            $('#modal_vendor_id').val(vendorId);
            $('#modal_vendor_info').html(`
                <i class="fas fa-user-tie mr-1"></i><strong> ${vendorName}</strong> 
                <i class="fas fa-map-marker-alt ml-3 mr-1"></i>${vendorLocation}
                <i class="fas fa-phone-alt ml-3 mr-1"></i>${vendorPhone}
            `);

            $('#selectVendorModal').modal('show');
        });
    });
</script>
<script>
    async function loadWorkflow() {
        const wrapper = document.getElementById('workflow-wrapper');
        wrapper.innerHTML = `
            <div class="text-center p-4" id="workflow-loading">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p>Memuat alur proses...</p>
            </div>`;

        try {
            const response = await fetch(`{{ route('item-request.workflow', $itemRequest->id) }}`);
            const result = await response.json();

            if (result.success) {
                wrapper.innerHTML = result.html;
            } else {
                wrapper.innerHTML = `<div class="alert alert-warning">Gagal memuat workflow: ${result.message}</div>`;
            }
        } catch (error) {
            // console.log(error);
            
            wrapper.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan koneksi.</div>`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadWorkflow();
    });
</script>
<script>
    const itemRequestId = '{{ $itemRequest->id }}';
    const chatContainer = document.getElementById('chat-container');
    const chatForm = document.getElementById('chat-form');
    const chatInput = document.getElementById('chat-message');

    let isLoadingChat = false;
    let isSendingMessage = false;

    async function loadChat() {
        if (isLoadingChat) return;
        isLoadingChat = true;
        $('#chat-loading').show();

        const urlChat = "{{ route('chat-message.show', ':id') }}".replace(':id', itemRequestId);

        try {
            const response = await fetch(urlChat);
            const messages = await response.json();

            let html = '';
            Object.entries(messages).forEach(([key, msg]) => {
                html += `
                    <div class="mb-2">
                        <strong>${msg.sender.name}:</strong> ${msg.message}
                        <div class="text-muted" style="font-size: 12px;">${new Date(msg.created_at).toLocaleTimeString()}</div>
                    </div>`;
            });

            chatContainer.innerHTML = html;
            scrollToBottom();
        } catch (err) {
            console.error('Gagal memuat chat:', err);
        } finally {
            $('#chat-loading').hide();
            isLoadingChat = false;
        }
    }

    function scrollToBottom() {
        if (chatContainer && chatContainer.scrollHeight) {
            chatContainer.scrollTo({
                top: chatContainer.scrollHeight,
                behavior: 'smooth'
            });
        }
    }

    chatForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        if (isSendingMessage) return;

        const message = chatInput.value.trim();
        if (!message) return;

        isSendingMessage = true;

        try {
            await fetch('{{ route("chat-message.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    item_request_id: itemRequestId,
                    message: message
                })
            });

            chatInput.value = '';
            await loadChat();
        } catch (err) {
            console.error('Gagal mengirim pesan:', err);
        } finally {
            isSendingMessage = false;
        }
    });

    // Load awal
    loadChat();
</script>
<script>
    // Smooth scroll for vendor list
    $('.vendor-scroll').smoothScroll({
        step: function() {
            this.stop();
        }
    });

    // Hover effects
    $('.vendor-card').hover(
        function() {
            $(this).addClass('shadow');
        },
        function() {
            $(this).removeClass('shadow');
        }
    );

    // Workflow step animations
    $('.workflow-step').hover(
        function() {
            $(this).find('.step-content').addClass('shadow');
        },
        function() {
            $(this).find('.step-content').removeClass('shadow');
        }
    );

    function formatRupiahFormat(input = null, inputNonFormat = null) 
    {
        let numStr = input.value.toString().replace(/[^,\d]/g, '');
        let split = numStr.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;

        if (numStr === "" || parseInt(numStr) === 0) {
            input.value = '';
            numStr = 0;
        } else {
            // Menghapus angka 0 di depan jika input diawali dengan 0
            rupiah = rupiah.replace(/^0+/, '');
            input.value ='Rp. '+rupiah;
        }

        // Update 'salary' input with non-formatted number
        document.getElementById(inputNonFormat).value = parseInt(numStr);
    }
</script>
@endsection
@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
<style>
.workflow-step {
    position: relative;
    padding: 20px 0;
    margin-left: 60px;
    border-left: 2px solid #eee;
}

.step-icon {
    position: absolute;
    left: -45px;
    top: 20px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.detail-card {
    background: #f8f9fa;
    transition: transform 0.2s;
}

.detail-card:hover {
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.response-item {
    margin: 10px 0;
    border-radius: 8px!important;
}

.vendor-responses .alert-success {
    border-left: 4px solid #28a745;
}

.vendor-responses .alert-danger {
    border-left: 4px solid #dc3545;
}

.workflow-step.completed {
    opacity: 0.8;
}

.workflow-step.active {
    border-left-color: #ffc107;
}

.workflow-step.active .step-icon {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(255,193,7,0.4); }
    70% { box-shadow: 0 0 0 12px rgba(255,193,7,0); }
    100% { box-shadow: 0 0 0 0 rgba(255,193,7,0); }
}
</style>
<style>
.upload-form {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #eee;
}

.shipping-info .alert {
    border-left: 4px solid #28a745;
}

.custom-file-label::after {
    content: "Browse";
}

.delivery-status .alert-success {
    border-left: 4px solid #28a745;
    padding-left: 1rem;
}

.delivery-status .alert-info {
    border-left: 4px solid #17a2b8;
}
</style>
@endsection