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
                                <img src="{{ $itemRequest->picture ? Storage::url($itemRequest->picture) : asset('logo/logo-thrive.png') }}" class="card-img-top"
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
                                <dd class="col-sm-8" id="status_badge">{!! $itemRequest->status_badge !!}</dd>

                                <dt class="col-sm-4 text-info"><i class="fas fa-door-open mr-2"></i>Open Status</dt>
                                <dd class="col-sm-8" id="status_open">
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
             @canAccess('show','chat_messages')
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
                        @canAccess('store','chat_messages')
                        <form id="chat-form" enctype="multipart/form-data">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="chat-message" placeholder="Ketik pesan..." name="message">
                                <div class="input-group-append">
                                    <label class="btn btn-outline-secondary mb-0" for="chat-file">
                                        <i class="fas fa-paperclip"></i>
                                    </label>
                                    <input type="file" id="chat-file" name="file" accept=".jpg,.jpeg,.png,.pdf" style="display: none;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted d-block">Hanya file gambar atau PDF, maks. 2MB</small>
                        </form>
                        <div id="file-preview" class="text-muted small mt-1" style="display: none;"></div>
                        @endcanAccess
                    </div>
                </div>
            </div>
             @endcanAccess
        </div>
    </div>
</div>

@canAccess('update','item_purchases')
<!-- Modal Pilih Vendor -->
<div class="modal fade" id="selectVendorModal" tabindex="-1" role="dialog" aria-labelledby="selectVendorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="vendor-billing-form" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item_request_id" value="{{ $itemRequest->id }}" required>
        <input type="hidden" name="product_supplier_id" id="modal_vendor_id">

        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title">
              <i class="fas fa-file-invoice-dollar mr-2"></i>
              Konfirmasi Vendor & Penagihan
            </h5>
            <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <!-- ▷ Area Konfirmasi Inline (hidden awalnya) ◁ -->
          <div id="vendor-confirm-section" class="alert alert-warning m-3 d-none">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Yakin ingin mengirim ke Finance?</strong><br>
                Pastikan harga, bon, dan data lainnya sudah benar.
              </div>
              <div>
                <button type="button" id="confirm-send-btn" class="btn btn-sm btn-danger mr-2 mb-2">
                  <i class="fas fa-paper-plane"></i> Kirim
                </button>
                <button type="button" id="cancel-send-btn" class="btn btn-sm btn-secondary">
                  Batal
                </button>
              </div>
            </div>
          </div>
          <!-- ▷ /Area Konfirmasi Inline ◁ -->

          <div class="modal-body">
            <div class="row" id="additional_vendor_fields"></div>

              <div id="modal_vendor_info" class="mb-3 text-muted small"></div>

              <div class="form-group">
                  <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" id="is_finished" name="is_finished">
                      <label class="custom-control-label" for="is_finished">
                        Apakah proses pembelian ini sudah selesai?
                      </label>
                  </div>
              </div>

              <div class="form-group">
                  <label for="purchase_date">Tenggat Tanggal Pembayaran <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" name="payment_term_date" value="{{ date('Y-m-d') }}" required>
              </div>

              <div class="form-group">
                  <label for="amount">Total Pembelian (Rp) <span class="text-danger">*</span></label>
                  <input
                      type="text"
                      class="form-control @error('estimated_price') is-invalid @enderror"
                      id="estimated_price_show"
                      placeholder="30.000.000"
                      oninput="formatRupiahFormat(this,'estimated_price')"
                      required
                  />
                  <input type="hidden" id="estimated_price" name="actual_price" required>
              </div>

              <div class="form-group">
                  <label for="payment_method">Metode Pembayaran <span class="text-danger">*</span></label>
                  <select class="form-control" name="payment_method" required>
                      <option value="">- Pilih -</option>
                      <option value="TRANSFER">Transfer</option>
                      <option value="CASH">Cash</option>
                  </select>
              </div>

              <div class="form-group">
                  <label for="rekening_number">Nomor Rekening <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="rekening_number" required>
              </div>

              <div class="form-group">
                  <label for="bon_image">Upload Foto Bon <span class="text-danger">*</span></label>
                  <input
                      type="file"
                      class="form-control-file"
                      name="bon_photo"
                      accept="image/*"
                      capture="environment"
                      required
                  >
              </div>

              <div class="form-group">
                  <label for="note">Catatan</label>
                  <textarea class="form-control" name="note" rows="3"></textarea>
              </div>
          </div>

          <div class="modal-footer">
            <!-- Tombol Normal yang bakal memicu tampilan konfirmasi -->
            <button type="submit" id="vendor-submit-btn" class="btn btn-success">
                <i class="fas fa-paper-plane"></i> Simpan & Proses
            </button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          </div>
        </div>
    </form>
  </div>
</div>
@endcanAccess

@canAccess('payment','item_purchases')
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
                    <button type="button" id="btnUploadTransferModalClose" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div id="upload-confirm-section" class="alert alert-warning mx-3 mt-3 d-none">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Yakin ingin mengunggah bukti transfer?</strong><br>
                            Pastikan file yang Anda unggah sudah benar.
                        </div>
                        <div>
                            <button type="button" id="confirm-upload-btn" class="btn btn-sm btn-danger mr-2 mb-2">
                                <i class="fas fa-paper-plane"></i> Unggah
                            </button>
                            <button type="button" id="cancel-upload-btn" class="btn btn-sm btn-secondary">
                                <i class="fas fa-times"></i> Batal
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Bukti Transfer (JPG, PNG, PDF)</label>
                        <input type="file" name="proof_image" class="form-control-file" accept="image/*" required>
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
@endcanAccess
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

@canAccess('complete','item_purchases')
<script>
    function confirmCompleteRequest(itemRequestId) {
        if (!confirm('Apakah Anda yakin ingin menyelesaikan permintaan ini?')) return;

        document.getElementById('btn-complete-request').style.display = 'none';

        let url = "{{ route('item-purchase.complete', ':id') }}";
        url = url.replace(':id', itemRequestId);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ is_open: false })
        })
        .then(res => {
            if (!res.ok) throw new Error('Gagal menyelesaikan permintaan');
            return res.json();
        })
        .then(data => {
            // Tampilkan toast sukses
            const toast = $(`
                <div class="toast align-items-center position-fixed top-0 end-0 m-3 show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-success text-white">
                        <strong class="me-auto"><i class="fas fa-check-circle me-2"></i> Berhasil</strong>
                        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body bg-white text-dark">
                        Data telah dikirim ke Sprinter.
                    </div>
                </div>
            `).appendTo('body');

            toast.toast({ delay: 2000 }).toast('show');
            // loadWorkflow();

            // Reload setelah delay (jika perlu)
            setTimeout(() => {
                location.reload();
            }, 2000);
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan saat menyelesaikan permintaan.');
        });
    }
</script>
@endcanAccess

@canAccess('delivery','item_requests')
<script>
    $(document).on('submit', '#form-upload-delivery', function (e) {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);
        const id = document.getElementById('item_purchase_id').value; // pastikan ID ada

        formData.append('_method', 'PUT');

        let url = "{{ route('item-request.delivery', ':id') }}";
        url = url.replace(':id', id);

        $.ajax({
            url: url,
            method: 'POST', // tetap POST karena spoof PUT
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                // Tampilkan toast sukses
                $('<div class="toast align-items-center position-fixed top-0 end-0 m-3 show" role="alert" aria-live="assertive" aria-atomic="true">' +
                    '<div class="toast-header bg-success text-white">' +
                    '<strong class="me-auto"><i class="fas fa-check-circle me-2"></i>Berhasil</strong>' +
                    '<button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                    '</div>' +
                    '<div class="toast-body bg-white text-dark">Data telah dikirim ke Sprinter.</div>' +
                '</div>')
                .appendTo('body')
                .toast({ delay: 2000 })
                .toast('show');

                // Callback
                loadWorkflow();
            },
            error: function (err) {
                console.error(err);
                // Toast untuk error
                $('<div class="toast align-items-center position-fixed top-0 end-0 m-3 show" role="alert" aria-live="assertive" aria-atomic="true">' +
                    '<div class="toast-header bg-danger text-white">' +
                    '<strong class="me-auto"><i class="fas fa-times-circle me-2"></i>Gagal</strong>' +
                    '<button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                    '</div>' +
                    '<div class="toast-body bg-white text-dark">Terjadi kesalahan saat mengirim data.</div>' +
                '</div>')
                .appendTo('body')
                .toast({ delay: 3000 })
                .toast('show');
            }
        });
    });
</script>
@endcanAccess

@canAccess('payment','item_purchases')
<script>
     $(document).on('click', '.btn-upload-transfer', function () {
        const itemPurchaseId = $(this).data('id');
        $('#item_purchase_id_input').val(itemPurchaseId);
    });

    $(document).ready(function () 
    {
        // Langkah 1: Saat klik submit → tampilkan konfirmasi
        $('#form-upload-payment').on('submit', function(e) 
        {
            e.preventDefault();
            $('#upload-confirm-section').removeClass('d-none');
            $(this).find('button[type="submit"]').addClass('d-none');
        });

        // Langkah 2: Batal konfirmasi → sembunyikan kembali konfirmasi & tampilkan submit
        $('#cancel-upload-btn').on('click', function () {
            $('#upload-confirm-section').addClass('d-none');
            $('#form-upload-payment').find('button[type="submit"]').removeClass('d-none');
        });

        // Langkah 3: Konfirmasi kirim
        $('#confirm-upload-btn').on('click', function () {
            const form = document.getElementById('form-upload-payment');
            const formData = new FormData(form);
            formData.append('_method', 'PUT');

            const itemPurchaseId = $('#item_purchase_id_input').val();

            // Disable tombol
            $('#form-upload-payment :input').prop('disabled', true);
            $('#btnUploadTransferModalClose').prop('disabled', false);

            $('#confirm-upload-btn').html(`
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Mengunggah...
            `).prop('disabled', true);

            let url = "{{ route('item-purchase.payment', ':id') }}";
            url = url.replace(':id', itemPurchaseId);

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    // Tampilkan notifikasi kecil di dalam modal
                   $('<div class="toast align-items-center position-fixed top-0 end-0 m-3 show" role="alert" aria-live="assertive" aria-atomic="true">' +
                        '<div class="toast-header bg-success text-white">' +
                        '<strong class="me-auto"><i class="fas fa-check-circle me-2"></i>Berhasil</strong>' +
                        '<button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                        '</div>' +
                        '<div class="toast-body bg-white text-dark">Bukti Transfer Telah berhasil diunggah.</div>' +
                    '</div>')
                    .appendTo('body')
                    .toast({ delay: 2000 })
                    .toast('show');
                    setTimeout(() => {

                        // Reset form
                        $('#form-upload-payment')[0].reset();

                        // Pulihkan tombol submit
                        $('#form-upload-payment button[type="submit"]').removeClass('d-none');
                        $('#upload-confirm-section').addClass('d-none');

                        // Pulihkan tombol konfirmasi
                        $('#confirm-upload-btn')
                            .html('<i class="fas fa-paper-plane"></i> Unggah')
                            .prop('disabled', false);

                        // Aktifkan kembali semua input
                        $('#form-upload-payment :input').prop('disabled', false);

                        $('#uploadTransferModal').modal('hide');
                    
                        $('#btnUploadTransferModalClose').click();
                        // Reset form agar tidak menyisakan file lama

                        // Optional: reload data
                        if (typeof loadWorkflow === 'function') {
                            loadWorkflow();
                        }
                    }, 1000);
                },
                error: function (err) {
                    console.error(err);
                    $('#upload-confirm-section').addClass('d-none');
                    $('#form-upload-payment').find('button[type="submit"]').removeClass('d-none');
                    $('#form-upload-payment :input').prop('disabled', false);
                    $('#confirm-upload-btn').html('<i class="fas fa-check"></i> Ya, Unggah').prop('disabled', false);

                    // Tambahkan pesan error inline
                    if ($('#upload-error-alert').length === 0) {
                        $('.modal-body').prepend(`
                            <div id="upload-error-alert" class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> Upload gagal. Periksa file Anda.
                            </div>
                        `);
                    }
                }
            });
        });

        $('#uploadTransferModal').on('hidden.bs.modal', function () 
        {
            // Reset form
            $('#form-upload-payment')[0].reset();

            // Kembalikan semua input ke keadaan aktif
            $('#form-upload-payment :input').prop('disabled', false);

            // Kembalikan tombol konfirmasi
            $('#confirm-upload-btn')
                .html('<i class="fas fa-paper-plane"></i> Unggah')
                .prop('disabled', false);

            // Tampilkan kembali tombol submit utama
            $('#form-upload-payment button[type="submit"]').removeClass('d-none');

            // Sembunyikan konfirmasi upload jika masih muncul
            $('#upload-confirm-section').addClass('d-none');

            // Hapus semua alert (success dan error) jika ada
            $('#upload-error-alert').remove();
            $('#uploadTransferModal .alert-success').remove();

            // Kosongkan hidden input ID (opsional)
            $('#item_purchase_id_input').val('');
        });
    });

</script>
@endcanAccess

@canAccess('store','item_purchases')
<script>
    $(document).ready(function () {
        // 1. Tahan submit pertama kali
        $('#vendor-billing-form').on('submit', function (e) 
        {
            e.preventDefault();
            $('#vendor-submit-btn').addClass('d-none');
            $('#vendor-confirm-section').removeClass('d-none');
        });

        // 2. Batal kirim
        $('#cancel-send-btn').on('click', function () {
            $('#vendor-confirm-section').addClass('d-none');
            $('#vendor-submit-btn').removeClass('d-none');
        });

        // 3. Konfirmasi “Ya, Kirim” → AJAX
        $('#confirm-send-btn').on('click', function () {
            // Step 1: Disable tombol kirim dulu biar user nggak klik spam
            $('#confirm-send-btn').html(`
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Mengirim...
            `).prop('disabled', true);

            // Step 2: Ambil FormData SEBELUM form di-disable
            const form = document.getElementById('vendor-billing-form');
            const formData = new FormData(form);
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            formData.append('_token', csrfToken);

            // Step 3: Baru disable seluruh input
            $('#vendor-billing-form :input').prop('disabled', true);

            // Step 4: Kirim AJAX
            $.ajax({
                url: '{{ route("item-purchase.store") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                   $('<div class="toast align-items-center position-fixed top-0 end-0 m-3 show" role="alert" aria-live="assertive" aria-atomic="true">' +
                    '<div class="toast-header bg-success text-white">' +
                    '<strong class="me-auto"><i class="fas fa-check-circle me-2"></i>Berhasil</strong>' +
                    '<button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                    '</div>' +
                    '<div class="toast-body bg-white text-dark">Data telah dikirim ke Finance.</div>' +
                    '</div>')
                    .appendTo('body')
                    .toast({ delay: 2000 })
                    .toast('show');

                    $('#vendor-billing-form')[0].reset(); // reset nilai
                    $('#modal_vendor_info').empty(); // bersihkan info vendor

                    // 3. Pulihkan UI
                    $('#vendor-billing-form :input').prop('disabled', false);
                    $('#confirm-send-btn')
                        .html('<i class="fas fa-paper-plane"></i> Kirim')
                        .prop('disabled', false);
                    $('#vendor-submit-btn').removeClass('d-none');
                    $('#vendor-confirm-section').addClass('d-none');

                    setTimeout(function () {
                        $('#selectVendorModal').modal('hide');
                    }, 500);

                    if (typeof loadWorkflow === 'function') {
                        loadWorkflow();
                    }
                },
                error: function (xhr) {
                    $('#vendor-billing-form :input').prop('disabled', false);
                    $('#vendor-confirm-section').addClass('d-none');
                    $('#vendor-submit-btn').removeClass('d-none');
                    $('#confirm-send-btn').html('<i class="fas fa-paper-plane"></i> Ya, Kirim').prop('disabled', false);

                    const msg = xhr.responseJSON?.message ?? 'Terjadi kesalahan saat mengirim data.';
                    if ($('#error-alert-inline').length === 0) {
                        $('#vendor-confirm-section').before(`
                            <div id="error-alert-inline" class="alert alert-danger m-3">
                                <i class="fas fa-exclamation-circle"></i> ${msg}
                            </div>
                        `);
                    } else {
                        $('#error-alert-inline').html(`<i class="fas fa-exclamation-circle"></i> ${msg}`);
                    }
                }
            });
        });
    });
 
    document.addEventListener('DOMContentLoaded', function () {
        $(document).on('click', '.btn-select-vendor', function () 
        {
            const vendorId = $(this).data('vendor-id');
            const vendorName = $(this).data('vendor-name');
            const vendorPhone = $(this).data('vendor-phone');
            const vendorLocation = $(this).data('vendor-location');
            const vendorPriceOffered = $(this).data('vendor-price-offered');

            // Set nilai jika harga ditawarkan tersedia
            $('#modal_vendor_id').val(vendorId);

            if (vendorPriceOffered) {
                document.getElementById("estimated_price").value = vendorPriceOffered;
                document.getElementById("estimated_price_show").value = vendorPriceOffered;
                formatRupiahFormat(document.getElementById("estimated_price_show"), "estimated_price");
            }

            
            // Update vendor info
            if (vendorId) 
            {
                $('#modal_vendor_info').html(`
                    <i class="fas fa-user-tie mr-1"></i><strong> ${vendorName}</strong> 
                    <i class="fas fa-map-marker-alt ml-3 mr-1"></i>${vendorLocation}
                    <i class="fas fa-phone-alt ml-3 mr-1"></i>${vendorPhone}
                `);
                $('#additional_vendor_fields').html(''); // Kosongkan form tambahan jika sebelumnya terisi
            } else {
                // Jika vendorId tidak ditemukan, tambahkan field input manual
                $('#modal_vendor_info').html(`<strong class="text-danger">Vendor baru - silakan isi detailnya</strong>`);
                $('#additional_vendor_fields').html(`
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label for="owner_name" class="form-label fw-bold">Nama Pemilik <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="owner_name" class="form-control rounded-end"
                                    placeholder="Nama lengkap pemilik" required>
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <label for="phone_number" class="form-label fw-bold">Nomor Telepon <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" name="phone_number" class="form-control rounded-end"
                                    placeholder="Format: 628" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-4">
                            <label for="store_name" class="form-label fw-bold">Nama Toko <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-store"></i></span>
                                <input type="text" name="store_name" class="form-control rounded-end"
                                    placeholder="Nama toko supplier" required>
                            </div>
                        </div>
                        <div class="form-group mb-4">
                            <label for="location" class="form-label fw-bold">Lokasi <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" name="location" class="form-control rounded-end"
                                    placeholder="Alamat lengkap toko" required>
                            </div>
                        </div>
                    </div>
                `);
            }

            // Tampilkan modal
            $('#selectVendorModal').modal('show');
        });
    });

</script>
@endcanAccess

@canAccess('workflow','item_requests')
<script>
    async function loadWorkflow() {
        const wrapper = document.getElementById('workflow-wrapper');
        const statusBadge = document.getElementById('status_badge');
        const statusOpen = document.getElementById('status_open');

        wrapper.innerHTML = `
            <div class="text-center p-4" id="workflow-loading">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p>Memuat alur proses...</p>
            </div>`;

        try {
            const response = await fetch(`{{ route('item-request.workflow', $itemRequest->id) }}`);
            const result = await response.json();

            if (result.success) {
                statusBadge.innerHTML = result.status_badge;
                statusOpen.innerHTML = result.status_open;
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
@endcanAccess

@canAccess('store','chat_messages')
@canAccess('show','chat_messages')
<script>
    document.getElementById('chat-file').addEventListener('change', function () {
        const preview = document.getElementById('file-preview');
        const file = this.files[0];

        if (file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
            const maxSize = 2 * 1024 * 1024; // 2MB

            if (!allowedTypes.includes(file.type)) {
                alert('Format file tidak diizinkan. Hanya gambar atau PDF.');
                this.value = '';
                preview.style.display = 'none';
                return;
            }

            if (file.size > maxSize) {
                alert('Ukuran file melebihi batas 2MB.');
                this.value = '';
                preview.style.display = 'none';
                return;
            }

            preview.textContent = `📎 ${file.name}`;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
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
            // console.log(response);
            
            const data = await response.json();

            const messages = data.message;

            let html = '';
            Object.entries(messages).forEach(([key, msg]) => {

                const isMine = msg.sender.id === "{{ auth()->id() }}"; // Ganti sesuai auth user ID
                const alignment = isMine ? 'text-right' : 'text-left';
                const bubbleClass = isMine ? 'bg-primary text-white ml-auto' : 'bg-light text-dark';
                const senderName = isMine ? 'Saya' : msg.sender.name;

                let fileHtml = '';

                if (msg.attachment) {
                    const ext = msg.attachment.split('.').pop().toLowerCase();
                    const url = `/storage//${msg.attachment}`; // Ganti sesuai path file kamu

                    if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                        fileHtml = `
                            <div class="mt-2">
                                <img src="${url}" alt="attachment" class="img-fluid rounded" style="max-height: 150px;">
                                <div class="mt-1">
                                    <a href="${url}" download class="btn btn-sm btn-light border">
                                        <i class="fas fa-download mr-1"></i> Download Gambar
                                    </a>
                                </div>
                            </div>`;
                    } else if (ext === 'pdf') {
                        fileHtml = `
                            <div class="mt-2">
                                <a href="${url}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-file-pdf mr-1"></i> Lihat PDF
                                </a>
                                <a href="${url}" download class="btn btn-sm btn-outline-dark ml-2">
                                    <i class="fas fa-download mr-1"></i> Download PDF
                                </a>
                            </div>`;
                    } else {
                        fileHtml = `
                            <div class="mt-2">
                                <a href="${url}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-file mr-1"></i> Buka File
                                </a>
                                <a href="${url}" download class="btn btn-sm btn-outline-dark ml-2">
                                    <i class="fas fa-download mr-1"></i> Download
                                </a>
                            </div>`;
                    }
                }

                html += `
                    <div class="d-flex flex-column ${alignment} mb-3">
                        <div class="small text-muted mb-1">${senderName} - ${new Date(msg.created_at).toLocaleTimeString()}</div>
                        <div class="p-2 rounded ${bubbleClass}" style="max-width: 70%;">
                            ${msg.message || ''}
                            ${fileHtml}
                        </div>
                    </div>`;
            });



            chatContainer.innerHTML = html;
            scrollToBottom();

            if (!data.status) 
            {
                chatForm.innerHTML = '<div class="text-center">Chat telah berakhir.</div>';
                return;
            }
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

    chatForm.addEventListener('submit', async function (e) 
    {
        e.preventDefault();

        if (isSendingMessage) return;

        const message = chatInput.value.trim();
        const fileInput = document.getElementById('chat-file');
        const file = fileInput.files[0];

        if (!message && !file) return; // jangan kirim kosong

        isSendingMessage = true;

        const formData = new FormData();
        formData.append('item_request_id', itemRequestId);
        if (message) formData.append('message', message);
        if (file) formData.append('file', file);

        chatInput.value = '';
            fileInput.value = '';
            document.getElementById('file-preview').style.display = 'none';

            const timestamp = new Date().toLocaleTimeString();
            const senderName = 'Saya';
            const messageText = message;
            const previewURL = file ? URL.createObjectURL(file) : null;

            let fileHtml = '';
            if (file) {
                const ext = file.name.split('.').pop().toLowerCase();

                if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                    fileHtml = `
                        <div class="mt-2">
                            <img src="${previewURL}" alt="attachment" class="img-fluid rounded" style="max-height: 150px;">
                        </div>`;
                } else if (ext === 'pdf') {
                    fileHtml = `
                        <div class="mt-2">
                            <span class="badge badge-danger"><i class="fas fa-file-pdf"></i> PDF Dilampirkan</span>
                        </div>`;
                } else {
                    fileHtml = `
                        <div class="mt-2">
                            <span class="badge badge-secondary"><i class="fas fa-file"></i> File Dilampirkan</span>
                        </div>`;
                }
            }

            const html = `
                <div class="d-flex flex-column text-right mb-3">
                    <div class="small text-muted mb-1">${senderName} - ${timestamp}</div>
                    <div class="p-2 rounded bg-primary text-white ml-auto" style="max-width: 70%;">
                        ${messageText || ''}
                        ${fileHtml}
                    </div>
                </div>`;

            chatContainer.insertAdjacentHTML('beforeend', html);
            scrollToBottom();
        try {
            await fetch('{{ route("chat-message.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            });
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
</script>

<script>
    host = '{{ config('services.connection_reverb.host')}}';
    key = '{{ config('services.connection_reverb.key')}}';
    port = '{{ config('services.connection_reverb.port')}}';  
    currentClientId = "{{ Auth::user()->id }}";
    notifSound = document.getElementById('notification-sound-update');

    window.Pusher = Pusher;

    window.Echo = new Echo.default({
        broadcaster: 'reverb',
        key: key,
        wsHost: host,
        wsPort: 8080,
        wssPort: port,
        forceTLS: true,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/authorize',
        disableStats: true,
    });

    window.Echo.private('chat.item-request.' + itemRequestId)
        .listen('ChatMessageSent', function (e) {
            console.log(e);
            
            if(e.sender_id != currentClientId)
            {
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
            }
            
            loadChat();
            loadWorkflow();
        });
</script>
@endcanAccess
@endcanAccess

<script>
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
    #chat-container .d-flex 
    {
        word-break: break-word;
    }
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