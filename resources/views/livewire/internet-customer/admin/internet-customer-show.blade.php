    @section('content_header')
    <nav aria-label="breadcrumb" >
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('internet-customer.index') }}">Internet Customers</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detail Pelanggan Internet</li>
        </ol>
    </nav>
    @endsection

    <div class="row mb-4">
        <div class="col-md-12">
            @include('components.alert')
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-user mr-2"></i>Detail Pelanggan Internet
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-id-card"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Kode Pelanggan</span>
                                    <span class="info-box-number">{{ $customer->code }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-signal"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Status</span>
                                    <span class="info-box-number">
                                        {!! $customer->status_badge !!}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-user-circle mr-2"></i>Data Pribadi
                            </h4>
                            <button onclick="openEditPribadiModal()" class="btn btn-sm btn-warning mb-2">
                                <i class="fas fa-edit mr-1"></i>Edit Data Pribadi
                            </button>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        <tr>
                                            <th width="25%">Nama Lengkap</th>
                                            <td>{{ $customer->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ $customer->userCustomer->email ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Nomor Telepon</th>
                                            <td>{{ $customer->userCustomer->phone_number ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Nomor KTP</th>
                                            <td>
                                                {{ $customer->ktp_number }}
                                                @if($ktpPhotoUrl)
                                                    <button wire:click="viewKtpPhoto" class="btn btn-sm btn-info ml-2">
                                                        <i class="fas fa-eye mr-1"></i>Lihat KTP
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Alamat Lengkap</th>
                                            <td>{{ $customer->address }}</td>
                                        </tr>
                                        <tr>
                                            <th>Lokasi</th>
                                            <td>
                                                {{ $customer->subdistrict->name ?? '-' }}, 
                                                {{ $customer->district->name ?? '-' }}, 
                                                {{ $customer->city->name ?? '-' }}, 
                                                {{ $customer->province->name ?? '-' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Pembayaran Selanjutnya</th>
                                            <td>
                                                @if($customer->userCustomer->start_billing_date)
                                                    <span class="badge badge-success">{{ \Carbon\Carbon::parse($customer->userCustomer->start_billing_date)->format('d M Y') }}</span>
                                                @else
                                                    <span class="badge badge-secondary">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Batas Pembayaran Selanjutnya</th>
                                            <td>
                                                @if($customer->userCustomer->end_billing_date)
                                                    <span class="badge badge-warning">{{ \Carbon\Carbon::parse($customer->userCustomer->end_billing_date)->format('d M Y') }}</span>
                                                @else
                                                    <span class="badge badge-secondary">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-wifi mr-2"></i>Paket Internet
                            </h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        <tr>
                                            <th width="25%">Nama Paket</th>
                                            <td>{{ $customer->internetPackage->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Deskripsi Paket</th>
                                            <td>{{ $customer->internetPackage->description ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Harga</th>
                                            <td>Rp {{ number_format($customer->internetPackage->price_nett, 0, ',', '.') }}</td>
                                        </tr>
                                        @if($customer->promo)
                                        <tr>
                                            <th>Promo</th>
                                            <td>
                                                @if($customer->promo)
                                                    <span class="badge badge-info">{{ $customer->promo->name }}</span>
                                                @else
                                                    <span>-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if($customer->installation)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-cogs mr-2"></i>Data Instalasi
                            </h4>
                            <button onclick="openEditInstalasiModal()" class="btn btn-sm btn-warning mb-2">
                                <i class="fas fa-edit mr-1"></i>Edit Data Instalasi
                            </button>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        <tr>
                                            <th width="25%">Tanggal Instalasi</th>
                                            <td>{{ \Carbon\Carbon::parse($customer->installation->installed_at)->format('d F Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Serial Number Perangkat</th>
                                            <td>{{ $customer->installation->device_serial_number }}</td>
                                        </tr>
                                        <tr>
                                            <th>IP Address</th>
                                            <td>{{ $customer->ip_address }}</td>
                                        </tr>
                                        <tr>
                                            <th>MAC Address</th>
                                            <td>{{ $customer->mac_address }}</td>
                                        </tr>
                                        <tr>
                                            <th>Local Address</th>
                                            <td>{{ $customer->local_address }}</td>
                                        </tr>
                                        <tr>
                                            <th>Username</th>
                                            <td>{{ $customer->username }}</td>
                                        </tr>
                                        <!-- <tr>
                                            <th>Password</th>
                                            <td>
                                                @if($customer->pass_hash)
                                                    <button wire:click="showInstallationPassword" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye mr-1"></i>Lihat Password
                                                    </button>
                                                    <div class="mt-2" style="display: none;" wire:id="installation-password-{{ $customer->id }}">
                                                        {{ $customer->pass_hash }}
                                                    </div>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr> -->
                                        <tr>
                                            <th>Catatan Instalasi</th>
                                            <td>{{ $customer->installation->notes ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Foto Instalasi</th>
                                            <td>
                                                @if(!empty($installationPhotos))
                                                    <button wire:click="viewInstallationPhotos" class="btn btn-sm btn-info">
                                                        <i class="fas fa-images mr-1"></i>Lihat Foto Instalasi
                                                    </button>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($purchases->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card shadow">
                                <div class="card-header bg-light">
                                    <h4 class="text-primary mb-3">
                                        <i class="fas fa-credit-card mr-2"></i>Riwayat Pembayaran
                                    </h4>
                                </div>
                                <div classa="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead> {{-- Tambahkan header untuk tabel --}}
                                                <tr>
                                                    <th>Periode</th>
                                                    <th>Metode</th>
                                                    <th>Status</th>
                                                    <th>Jumlah Bayar</th>
                                                    <th>Bukti Pembayaran</th>
                                                    @canAccess('as_finance','internet_customers')
                                                    <th>Konfirmasi Pembayaran</th>
                                                    @endcanAccess
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($purchases as $purchase)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($purchase->created_at)->format('F Y') }}</td>
                                                    <td>{{ ucfirst($purchase->payment_method ?? '-') }}</td>
                                                    <td>
                                                        @if($purchase->user_finance_id && $purchase->confirmation_finance_at)
                                                            <span class="badge badge-success">Lunas</span>
                                                        @else
                                                            <span class="badge badge-danger">Belum Lunas</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        Rp {{ number_format($purchase->amount_paid, 0, ',', '.') }}
                                                    </td>
                                                    <td>
                                                        @if($purchase->payment_proof)
                                                            <button wire:click="viewPaymentProof('{{ $purchase->id }}')" class="btn btn-sm btn-info">
                                                                <i class="fas fa-eye mr-1"></i>Lihat
                                                            </button>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    @canAccess('as_finance','internet_customers')
                                                    <td>
                                                        @switch($customer->status)
                                                            @case(\App\Schemas\ParamSchema::WAITING_PAYMENT_CONFIRMATION)
                                                                @if($purchase->user_finance_id && $purchase->confirmation_finance_at)
                                                                    <i class="fas fa-check-circle mr-1 text-success"></i>                   
                                                                @else
                                                                <button class="btn btn-sm btn-success mt-1" onclick="confirmPayment('{{ $purchase->id }}')">
                                                                    Konfirmasi
                                                                </button>
                                                                @endif
                                                                @break
                                                            @case(\App\Schemas\ParamSchema::WAITING_PAYMENT_SUBSCRIPTION)
                                                                @break
                                                            @default
                                                                <i class="fas fa-check-circle mr-1 text-success"></i>
                                                                @break
                                                        @endswitch
                                                    </td>
                                                    @endcanAccess
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="card-footer">
                                        <div class="float-right">
                                            {{ $purchases->links('pagination::bootstrap-4') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($agreementFields)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-file-contract mr-2"></i>Perjanjian Kerjasama
                            </h4>
                            <div class="card border-primary">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">Detail Perjanjian</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $agreement = $customer->partnershipAgreement;
                                    @endphp
                                    @if(view()->exists('partnership_agreement.pdf.' . $agreement->type->name_format))
                                    <div class="card scrollable" id="printThis">
                                        @include('partnership_agreement.pdf.' . $agreement->type->name_format, ['agreement' => $agreement])
                                    </div>
                                    <div class="d-flex justify-content-center mt-3">
                                        <button type="button" id="downloadWorkOrder" class="btn btn-info mb-2 mr-2"><i class="fa fa-file-pdf"></i> Download</button>
                                    </div>
                                    @else
                                    <div class="d-flex justify-content-center">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <h5><i class="fa fa-exclamation-circle"></i> Tidak Ada Template Yang Tersedia</h5>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="card-footer text-muted">
                                    <small>Dibuat pada: {{ \Carbon\Carbon::parse($customer->partnershipAgreement->created_at)->locale('id')->translatedFormat('d F Y') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Single Modal for all image previews -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle"></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" id="modalContent">
                    <!-- Image will be inserted here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Gallery Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="galleryModalTitle"></h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="carouselGallery" class="carousel slide" data-ride="carousel">
                        <div class="carousel-inner" id="carouselInner">
                            <!-- Slides will be inserted here -->
                        </div>
                        <a class="carousel-control-prev" href="#carouselGallery" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#carouselGallery" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <!-- Modal Edit Data Pribadi -->
    <div class="modal fade" id="editPribadiModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit mr-2"></i>Edit Data Pribadi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formEditPribadi">
                        <div class="row">
                            @include('components.alert')
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="name" wire:model="name" required>
                                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" wire:model="email">
                                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone_number">Nomor Telepon</label>
                                    <input type="text" class="form-control" id="phone_number" wire:model="phone_number">
                                    @error('phone_number') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_billing_date">Tanggal Pembayaran Selanjutnya</label>
                                    <input type="date" class="form-control" id="start_billing_date" wire:model="start_billing_date">
                                    @error('start_billing_date') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_billing_date">Batas Pembayaran Selanjutnya</label>
                                    <input type="date" class="form-control" id="end_billing_date" wire:model="end_billing_date">
                                    @error('end_billing_date') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="editPribadiModalClick">Batal</button>
                    <button type="button" id="submitPribadi" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Data Instalasi -->
    <div class="modal fade" id="editInstalasiModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-network-wired mr-2"></i>Edit Data Instalasi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formEditInstalasi">
                        <div class="row">
                            @include('components.alert')
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="local_address">Local Address</label>
                                    <input type="text" class="form-control" id="local_address" wire:model="local_address">
                                    @error('local_address') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" wire:model="username">
                                    @error('username') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pass_hash">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="pass_hash" wire:model="pass_hash">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('pass_hash')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @error('pass_hash') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="device_serial_number">Serial Number Perangkat</label>
                                    <input type="text" class="form-control" id="device_serial_number" wire:model="device_serial_number">
                                    @error('device_serial_number') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="formEditInstalasiClose">Batal</button>
                    <button type="button" id="submitInstalasi" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </div>
    </div>
    @push('js')
    <script>
        // Fungsi untuk membuka modal edit data pribadi
        function openEditPribadiModal() {
            @this.call('openEditPribadiModal');
        }

        // Fungsi untuk membuka modal edit data instalasi
        function openEditInstalasiModal() {
            @this.call('openEditInstalasiModal');
        }

        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.parentNode.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.addEventListener('livewire:load', function () {
            // Event untuk menampilkan modal edit data pribadi
            window.addEventListener('showEditPribadiModal', function(e) 
            {
                // Isi nilai-nilai form dari data Livewire
                document.getElementById('name').value = e.detail.name || '';
                document.getElementById('email').value = e.detail.email || '';
                document.getElementById('phone_number').value = e.detail.phone_number || '';
                document.getElementById('start_billing_date').value = e.detail.start_billing_date || '';
                document.getElementById('end_billing_date').value = e.detail.end_billing_date || '';
                
                // Tampilkan modal
                new bootstrap.Modal(document.getElementById('editPribadiModal')).show();
            });

            // Event untuk menyembunyikan modal edit data pribadi
            window.addEventListener('hideEditPribadiModal', function() {
                $("#editPribadiModalClick").click();
                var modal = new bootstrap.Modal(document.getElementById('editPribadiModal'));
                modal.hide();
            });

            // Event untuk menampilkan modal edit data instalasi
            window.addEventListener('showEditInstalasiModal', function(e) {
                // Isi nilai-nilai form dari data Livewire
                $("#formEditInstalasiClose").click();
                
                document.getElementById('local_address').value = e.detail.local_address || '';
                document.getElementById('username').value = e.detail.username || '';
                document.getElementById('pass_hash').value = e.detail.pass_hash || '';
                document.getElementById('device_serial_number').value = e.detail.device_serial_number || '';
                
                // Tampilkan modal
                new bootstrap.Modal(document.getElementById('editInstalasiModal')).show();
            });

            // Event untuk menyembunyikan modal edit data instalasi
            window.addEventListener('hideEditInstalasiModal', function() {
                new bootstrap.Modal(document.getElementById('editInstalasiModal')).hide();
            });

            // Event listener untuk submit data pribadi
            document.getElementById('submitPribadi').addEventListener('click', function() {
                // Kumpulkan data dari form
                $("#editPribadiModalClick").click();

                const name = document.getElementById('name').value;
                const email = document.getElementById('email').value;
                const phone_number = document.getElementById('phone_number').value;
                const start_billing_date = document.getElementById('start_billing_date').value;
                const end_billing_date = document.getElementById('end_billing_date').value;
                
                // Set nilai ke Livewire
                @this.set('name', name);
                @this.set('email', email);
                @this.set('phone_number', phone_number);
                @this.set('start_billing_date', start_billing_date);
                @this.set('end_billing_date', end_billing_date);
                
                // Panggil method save di Livewire
                @this.call('savePribadi');
            });

            // Event listener untuk submit data instalasi
            document.getElementById('submitInstalasi').addEventListener('click', function() {
                // Kumpulkan data dari form
                $("#formEditInstalasiClose").click();

                const local_address = document.getElementById('local_address').value;
                const username = document.getElementById('username').value;
                const pass_hash = document.getElementById('pass_hash').value;
                const device_serial_number = document.getElementById('device_serial_number').value;
                
                // Set nilai ke Livewire
                @this.set('local_address', local_address);
                @this.set('username', username);
                @this.set('pass_hash', pass_hash);
                @this.set('device_serial_number', device_serial_number);
                
                // Panggil method save di Livewire
                @this.call('saveInstalasi');
            });

            // Reset form ketika modal ditutup
            $('#editPribadiModal, #editInstalasiModal').on('hidden.bs.modal', function () {
                @this.set('name', '{{ $customer->name }}');
                @this.set('email', '{{ $customer->userCustomer->email ?? '' }}');
                @this.set('phone_number', '{{ $customer->userCustomer->phone_number ?? '' }}');
                @this.set('start_billing_date', '{{ $customer->userCustomer->start_billing_date ?? '' }}');
                @this.set('end_billing_date', '{{ $customer->userCustomer->end_billing_date ?? '' }}');
                @this.set('local_address', '{{ $customer->local_address ?? '' }}');
                @this.set('username', '{{ $customer->username ?? '' }}');
                @this.set('pass_hash', '{{ $customer->pass_hash ?? '' }}');
                @this.set('device_serial_number', '{{ $customer->installation->device_serial_number ?? '' }}');
                
                // Reset error messages
                @this.resetErrorBag();
                @this.resetValidation();
            });
        });
    </script>
    @if($customer->partnershipAgreement)
    <script>
        function prinsts() 
        {
            let name = "{{$customer->partnershipAgreement->number_result}}" + " {{ $customer->partnershipAgreement->type->name}}";
            let printContents = document.getElementById("printThis").innerHTML;
            let originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.addEventListener("beforeprint", (event) => {
                document.title = name;
            });

            window.print();
            document.body.innerHTML = originalContents;
        }

        $("#downloadWorkOrder").click(function(e) {
            e.preventDefault();
            prinsts();
        });
    </script>
    @endif
    <script>
        function confirmPayment(customerId) 
        {
            Swal.fire({
                title: 'Konfirmasi Pembayaran?',
                text: "Pastikan bukti pembayaran sudah valid.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, konfirmasi',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Panggil ke Livewire
                    @this.call('confirmPayment', customerId);
                }
            });
        }

        window.addEventListener('showSuccessAlert', function(event) {
            Swal.fire({
                icon: 'success',
                title: event.detail.title,
                text: event.detail.message,
                showConfirmButton: false,
                timerProgressBar: true,
                timer: 3000,
            });
        });

        window.addEventListener('showErrorAlert', function(event) {
            Swal.fire({
                icon: 'success',
                title: event.detail.title,
                text: event.detail.message,
                showConfirmButton: false,
                timerProgressBar: true,
                timer: 3000,
            });
        });

        document.addEventListener('livewire:load', function () {  
            window.addEventListener('showImageModal', function(event) {
                const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
                
                // Set modal title and content
                document.getElementById('modalTitle').innerText = event.detail.title;
                document.getElementById('modalContent').innerHTML = `
                    <img src="${event.detail.imageUrl}" class="img-fluid" alt="${event.detail.title}">
                `;
                
                // Show modal
                modal.show();
            });

            // Gallery modal handler
            window.addEventListener('showGalleryModal', function(event) {
                const modal = new bootstrap.Modal(document.getElementById('galleryModal'));
                const carouselInner = document.getElementById('carouselInner');
                
                // Set title
                document.getElementById('galleryModalTitle').innerText = event.detail.title;
                
                // Clear previous items
                carouselInner.innerHTML = '';
                
                // Add new items
                event.detail.images.forEach((image, index) => {
                    const item = document.createElement('div');
                    item.className = `carousel-item ${index === 0 ? 'active' : ''}`;
                    item.innerHTML = `
                        <div class="text-center">
                            <img src="${image}" class="d-block w-100" style="max-height: 70vh; object-fit: contain;">
                            <div class="mt-2">Foto ${index + 1}/${event.detail.images.length}</div>
                        </div>
                    `;
                    carouselInner.appendChild(item);
                });
                
                // Initialize carousel
                const carousel = new bootstrap.Carousel(document.getElementById('carouselGallery'));
                modal.show();
            });
        });
    </script>
    @endpush
    @push('css')
    <style>
        .img-signature
        {
            background-color: transparent !important; 
            border: 0px solid #dee2e6 !important;
            box-shadow: 0px 0px 0px 0px rgba(0,0,0,0.0) !important;       
            max-height: 100px !important; 
        }
        .signature-container {
            width: fit-content;
        }
        .signature-canvas {
            /* width: 100%; */
            /* height: 200px; */
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background-color: white;
            touch-action: none;
        }
        .custom-file-label::after {
            content: "Browse";
        }
        #ktpPreviewImg {
            max-height: 200px;
        }
    </style>
    <style>
    .small-text 
        {
            text-align: justify;
            font-size: 0.79rem;
        }

        .text-ads a, 
        .text-ads li, 
        .text-ads p, 
        .text-ads div, 
        .text-ads span, 
        .text-ads h1, 
        .text-ads h2, 
        .text-ads h3, 
        .text-ads h4, 
        .text-ads h5, 
        .text-ads h6 
        {
            font-size: 0.92rem;
        }
        .small-header
        {
            font-size: 1rem;
            font-weight: bold;
        }
        @media print {
            #printItem {
                margin-left: 50px;
                margin-right: 50px;
            }
        }

        body {
            font-family: Arial;
            /* font-size : 12px; */
            /* padding: 20px; */
            /* background-color: #f4f4f4; */
        }

        .container {
            /* background-color: #fff; */
            padding: 10px;
            border-radius: 5px;
        }

        .select2-selection__rendered {
            line-height: 31px !important;
        }

        .select2-container .select2-selection--single {
            height: 35px !important;
        }

        .select2-selection__arrow {
            height: 34px !important;
        }

        hr {
            border: 1px solid black;
            border-radius: 5px;
        }

        .select2-selection__rendered {
            line-height: 31px !important;
        }

        .select2-container .select2-selection--single {
            height: 35px !important;
        }

        .select2-selection__arrow {
            height: 34px !important;
        }

        /* li */
        .margin {
            margin-bottom: 15px;
        }

        .noMargin {
            margin-bottom: 0px;
        }

        .scrollable {
            width: 100%;
            height: 650px;
            overflow: auto;
            border: 1px solid #ccc;
        }
    </style>
    <style>
        .info-box {
            border-radius: 5px;
            margin-bottom: 15px;
            box-shadow: 0 0 1px rgba(0,0,0,0.1);
        }
        .info-box-content {
            padding: 10px 15px;
        }
        .info-box-text {
            font-size: 14px;
            color: #6c757d;
        }
        .info-box-number {
            font-size: 20px;
            font-weight: 600;
        }
        .card-header {
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        .modal.show {
            background: rgba(0,0,0,0.5);
            display: block;
            overflow: auto;
        }

        /* Add to your styles */
        .carousel-item {
            transition: transform 0.6s ease-in-out;
        }

        .carousel-control-prev, .carousel-control-next {
            width: 5%;
            background: rgba(0,0,0,0.3);
        }

        .carousel-control-prev-icon, .carousel-control-next-icon {
            width: 2rem;
            height: 2rem;
        }
    </style>
    @endpush