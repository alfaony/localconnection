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
                            @if($customer->status === 'pending')
                            {{-- ===== APPROVAL OVERLAY CARD ===== --}}
                            <div class="card shadow border-0" style="border-left: 4px solid #ffc107 !important;">
                                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user-circle mr-2"></i>Data Pribadi
                                        <span class="badge badge-dark ml-2" style="font-size:0.75rem;">Menunggu Persetujuan</span>
                                    </h5>
                                </div>
                                <div class="card-body" style="min-height: 220px; position: relative;">
                                    {{-- Data di-blur sebagai preview --}}
                                    <div style="filter: blur(5px); pointer-events: none; opacity: 0.35; user-select:none;">
                                        <table class="table table-sm table-bordered mb-0">
                                            <tbody>
                                                <tr><th width="30%">Nama Lengkap</th><td>{{ $customer->name }}</td></tr>
                                                <tr><th>Email</th><td>{{ $customer->userCustomer->email ?? '-' }}</td></tr>
                                                <tr><th>Nomor Telepon</th><td>{{ $customer->userCustomer->phone_number ?? '-' }}</td></tr>
                                                <tr><th>Alamat</th><td>{{ $customer->address }}</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    {{-- Tombol Approve / Tolak di tengah --}}
                                    <div class="d-flex flex-column align-items-center justify-content-center"
                                         style="position:absolute; inset:0; z-index:10;">
                                        <div class="text-center bg-white rounded shadow p-4" style="max-width:380px;">
                                            <i class="fas fa-user-check fa-3x text-warning mb-3"></i>
                                            <h5 class="font-weight-bold mb-1">Pelanggan Baru</h5>
                                            <p class="text-muted small mb-3">
                                                Tinjau data pelanggan lalu pilih aksi persetujuan.
                                            </p>
                                            @canAccess('edit', 'internet_customers')
                                            <div class="d-flex justify-content-center" style="gap:.75rem;">
                                                {{-- Setujui --}}
                                                <button type="button"
                                                        wire:loading.attr="disabled"
                                                        wire:target="approveCustomer,rejectCustomer"
                                                        onclick="confirmApproveCustomer()"
                                                        class="btn btn-success px-4 shadow-sm">
                                                    <span wire:loading.remove wire:target="approveCustomer">
                                                        <i class="fas fa-check-circle mr-1"></i>Setujui
                                                    </span>
                                                    <span wire:loading wire:target="approveCustomer">
                                                        <i class="fas fa-spinner fa-spin mr-1"></i>Memproses...
                                                    </span>
                                                </button>
                                                {{-- Tolak --}}
                                                <button type="button"
                                                        wire:loading.attr="disabled"
                                                        wire:target="approveCustomer,rejectCustomer"
                                                        onclick="confirmRejectCustomer()"
                                                        class="btn btn-danger px-4 shadow-sm">
                                                    <span wire:loading.remove wire:target="rejectCustomer">
                                                        <i class="fas fa-times-circle mr-1"></i>Tolak
                                                    </span>
                                                    <span wire:loading wire:target="rejectCustomer">
                                                        <i class="fas fa-spinner fa-spin mr-1"></i>Memproses...
                                                    </span>
                                                </button>
                                            </div>
                                            @endcanAccess
                                        </div>
                                    </div>
                                </div>
                            </div>{{-- /card --}}
                        </div>{{-- /col-md-12 --}}
                    </div>{{-- /row --}}
                            @else
                            {{-- ===== NORMAL DATA PRIBADI ===== --}}
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-user-circle mr-2"></i>Data Pribadi
                            </h4>
                            @canAccess('edit', 'internet_customers')
                            <button onclick="openEditPribadiModal()" class="btn btn-sm btn-warning mb-2">
                                <i class="fas fa-edit mr-1"></i>Edit Data Pribadi
                            </button>
                            @endcanAccess
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
                                            <th>Alamat Lengkap</th>
                                            <td>{{ $customer->address }}</td>
                                        </tr>
                                        @if($customer->customer_type === 'bisnis')
                                        <tr>
                                            <th>Nomor NPWP</th>
                                            <td>{{ $customer->npwp_number ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Foto NPWP</th>
                                            <td>
                                                <div>
                                                    @if($npwp_photo_url)
                                                        <button wire:click="viewNpwpPhoto" class="btn btn-sm btn-info mr-1">
                                                            <i class="fas fa-eye mr-1"></i>Lihat NPWP
                                                        </button>
                                                        <button wire:click="downloadNpwpPhoto" class="btn btn-sm btn-secondary mr-1">
                                                            <i class="fas fa-download mr-1"></i>Download
                                                        </button>
                                                    @else
                                                        <span class="text-muted mr-2">-</span>
                                                    @endif
                                                    @canAccess('edit', 'internet_customers')
                                                    <button wire:click="toggleNpwpUpload" class="btn btn-sm {{ $showNpwpUpload ? 'btn-danger' : 'btn-outline-warning' }}">
                                                        <i class="fas {{ $showNpwpUpload ? 'fa-times' : 'fa-upload' }} mr-1"></i>
                                                        {{ $showNpwpUpload ? 'Tutup' : ($npwp_photo_url ? 'Ganti Foto NPWP' : 'Upload Foto NPWP') }}
                                                    </button>
                                                    @endcanAccess
                                                </div>
                                            </td>
                                        </tr>
                                        @if($showNpwpUpload)
                                        <tr>
                                            <td colspan="2" class="p-0">
                                                <div class="bg-light border-left border-warning p-3" style="border-left-width:4px!important">
                                                    <label class="font-weight-bold text-warning mb-2">
                                                        <i class="fas fa-file-invoice mr-1"></i>
                                                        {{ $npwp_photo_url ? 'Ganti Foto NPWP' : 'Upload Foto NPWP' }}
                                                    </label>
                                                    <input type="file"
                                                        wire:model="npwp_photo_upload"
                                                        class="form-control form-control-sm"
                                                        accept="image/*,application/pdf"
                                                        @if($npwp_photo_pending_path) disabled @endif>
                                                    <div wire:loading wire:target="npwp_photo_upload" class="mt-2">
                                                        <small class="text-warning">
                                                            <i class="fas fa-spinner fa-spin mr-1"></i> Mengunggah ke server...
                                                        </small>
                                                    </div>
                                                    <div wire:loading.remove wire:target="npwp_photo_upload" class="mt-1">
                                                        @if($npwp_photo_pending_path)
                                                            <div class="d-flex align-items-center mt-2">
                                                                <small class="text-success mr-3">
                                                                    <i class="fas fa-check-circle mr-1"></i> Foto siap disimpan.
                                                                </small>
                                                                <button wire:click="saveNpwpPhoto"
                                                                    wire:loading.attr="disabled"
                                                                    wire:target="saveNpwpPhoto"
                                                                    class="btn btn-sm btn-success">
                                                                    <span wire:loading.remove wire:target="saveNpwpPhoto">
                                                                        <i class="fas fa-save mr-1"></i>Simpan Foto NPWP
                                                                    </span>
                                                                    <span wire:loading wire:target="saveNpwpPhoto">
                                                                        <i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        @else
                                                            <small class="text-muted">Format: JPG, PNG, PDF (maks. 2MB)</small>
                                                        @endif
                                                    </div>
                                                    @error('npwp_photo_upload') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                        @endif
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
                                            <th>Koordinat GPS</th>
                                            <td>
                                                @if($customer->latitude && $customer->longitude)
                                                    <span class="badge badge-info mr-1">
                                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                                        {{ $customer->latitude }}, {{ $customer->longitude }}
                                                    </span>
                                                    <button type="button" class="btn btn-xs btn-outline-info"
                                                            onclick="toggleAdminCustomerMap()">
                                                        <i class="fas fa-map mr-1"></i>Lihat Peta
                                                    </button>
                                                    <div id="admin-view-map-wrap" style="display:none; margin-top:.5rem;">
                                                        <div wire:ignore>
                                                            <div id="admin-view-map" style="height:220px; border-radius:6px; border:1px solid #dee2e6;"></div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">— belum diisi</span>
                                                @endif
                                            </td>
                                        </tr>
                                        {{-- 
                                        @if($customer->coupons->count() > 0)
                                        <tr>
                                            <td colspan="6">
                                                <div class="alert alert-success mb-0">
                                                    <strong><i class="fas fa-ticket-alt mr-2"></i>Kupon Tersedia: {{ $customer->coupons->count() }}</strong>
                                                    <div class="mt-2">
                                                        @foreach($customer->coupons as $coupon)
                                                            <span class="badge badge-lg badge-primary mr-1 mb-1">
                                                                {{ $coupon->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                        --}}
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
                            @endif
                            {{-- end pending/approve check --}}

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-wifi mr-2"></i>Paket Internet
                            </h4>

                            {{-- ✅ NEW: Button Edit Paket untuk customer aktif --}}
                                @canAccess('editPackage','internet_customers')
                                <button wire:click="openEditPackageModal" class="btn btn-sm btn-warning mb-2">
                                    <i class="fas fa-exchange-alt mr-1"></i>Ganti Paket Internet
                                </button>
                                @endcanAccess

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        <tr>
                                            <th width="25%">Nama Paket</th>
                                            <td>{{ $customer->internetPackage->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tipe Pelanggan</th>
                                            <td>
                                                @php $ct = $customer->customer_type ?? 'bisnis'; @endphp
                                                @if($ct === 'bisnis')
                                                    <span class="badge badge-primary"><i class="fas fa-building mr-1"></i> Bisnis</span>
                                                @else
                                                    <span class="badge badge-success"><i class="fas fa-home mr-1"></i> Rumah</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Deskripsi Paket</th>
                                            <td>{{ $customer->internetPackage->description ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Harga</th>
                                            <td>Rp {{ number_format($customer->internetPackage?->getPriceForRegion($customer->province_id, $customer->city_id, $customer->district_id, $customer->subdistrict_id)['price_nett'] ?? 0, 0, ',', '.') }}</td>
                                        </tr>
                                        @if($customer->promo)
                                        <tr>
                                            <th>Promo</th>
                                            <td>
                                                @if($customer->promo)
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge badge-info mr-2">{{ $customer->promo->name }}</span>
                                                        <span class="text-muted">{{ $customer->actionBy ? $customer->actionBy->name : ''}}</span>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ===== DATA INSTALASI ===== --}}
                    @php
                        $adminHasInstallation   = (bool) $customer->installation;
                        $adminIsProcessInstall  = $customer->status === \App\Schemas\ParamSchema::PROCESS_INSTALLATION;
                    @endphp
                    <div class="row mt-4">
                        <div class="col-md-12">

                            @if($adminHasInstallation)
                            {{-- ===== NORMAL INSTALASI DATA ===== --}}
                            <h4 class="text-primary mb-3">
                                <i class="fas fa-cogs mr-2"></i>Data Instalasi
                            </h4>
                            <div class="btn-group mb-2" role="group">
                                @canAccess('editInstalasi','internet_customers')
                                <button onclick="openEditInstalasiModal()" class="btn btn-sm btn-warning mr-2 mb-2">
                                    <i class="fas fa-edit mr-1"></i>Edit Data Instalasi
                                </button>
                                @endcanAccess
                                @canAccess('moveRouter','internet_customers')
                                <button wire:click="openMoveRouterModal" class="btn btn-sm btn-info mb-2">
                                    <i class="fas fa-exchange-alt mr-1"></i>Pindah Router
                                </button>
                                @endcanAccess
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <tbody>
                                        <tr>
                                            <th width="25%">Tanggal Instalasi</th>
                                            <td>{{ \Carbon\Carbon::parse($customer->installation->installed_at)->format('d F Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Router Saat Ini</th>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="badge bg-primary mb-1">
                                                        <i class="fas fa-server me-1"></i>{{ $customer->router->name ?? '-' }}
                                                    </span>
                                                    @if($customer->last_updated_router)
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>
                                                        Terakhir connect: {{ \Carbon\Carbon::parse($customer->last_updated_router)->locale('id')->translatedFormat('d F Y H:i') }}
                                                    </small>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>ODP</th>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-server me-1"></i>{{ $customer->odp ? $customer->odp->name : '-' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Serial Number Perangkat</th>
                                            <td>{{ $customer->installation->device_serial_number ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>IP Address</th>
                                            <td>{{ $customer->ip_address ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>MAC Address</th>
                                            <td>{{ $customer->mac_address ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Local Address</th>
                                            <td>{{ $customer->local_address ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Username</th>
                                            <td>{{ $customer->username ?: '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Catatan Instalasi</th>
                                            <td>{{ $customer->installation->notes ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Foto Instalasi</th>
                                            <td>
                                                @if(!empty($installationPhotos))
                                                    <button wire:click="viewInstallationPhotos" class="btn btn-sm btn-info">
                                                        <i class="fas fa-images mr-1"></i>Lihat Foto
                                                    </button>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            @else
                            {{-- ===== INSTALL BUTTON OVERLAY ===== --}}
                            <div class="card shadow border-0" style="border-left: 4px solid #17a2b8 !important;">
                                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="fas fa-tools mr-2"></i>Data Instalasi
                                        @if($adminIsProcessInstall)
                                            <span class="badge badge-warning ml-2" style="font-size:0.75rem;">Menunggu Instalasi</span>
                                        @else
                                            <span class="badge badge-secondary ml-2" style="font-size:0.75rem;">Belum Terinstal</span>
                                        @endif
                                    </h5>
                                </div>
                                <div class="card-body" style="min-height: 200px; position: relative;">
                                    {{-- Preview blur fields --}}
                                    <div style="filter: blur(5px); pointer-events: none; opacity: 0.3; user-select:none;">
                                        <table class="table table-sm table-bordered mb-0">
                                            <tbody>
                                                <tr><th width="30%">Username</th><td>—</td></tr>
                                                <tr><th>Password</th><td>—</td></tr>
                                                <tr><th>Local Address</th><td>—</td></tr>
                                                <tr><th>Serial Number</th><td>—</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    {{-- Install button centered --}}
                                    @if(!$customer->installation()->exists() && $customer->status == \App\Schemas\ParamSchema::PROCESS_INSTALLATION)
                                    <div class="d-flex flex-column align-items-center justify-content-center"
                                         style="position:absolute; inset:0; z-index:10;">
                                        <div class="text-center bg-white rounded shadow p-4" style="max-width:340px;">
                                            <i class="fas fa-tools fa-3x text-info mb-3"></i>
                                            <h5 class="font-weight-bold mb-1">
                                                @if($adminIsProcessInstall)
                                                    Siap Instalasi
                                                @else
                                                    Belum Ada Data Instalasi
                                                @endif
                                            </h5>
                                            <p class="text-muted small mb-3">
                                                @if($adminIsProcessInstall)
                                                    Pembayaran telah dikonfirmasi. Klik tombol di bawah untuk mengisi data instalasi pelanggan.
                                                @else
                                                    Isi data instalasi untuk mengaktifkan koneksi pelanggan ini.
                                                @endif
                                            </p>
                                            @canAccess('editInstalasi','internet_customers')
                                            <button onclick="openEditInstalasiModal()"
                                                    class="btn btn-info btn-lg px-4 shadow-sm">
                                                <i class="fas fa-network-wired mr-2"></i>Input Data Instalasi
                                            </button>
                                            @endcanAccess
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>
                    {{-- end instalasi section --}}

                    {{-- ✅ NEW: Modal Move Router - ADD THIS BEFORE @push('js') --}}
                    <div class="modal fade" id="moveRouterModal" tabindex="-1" wire:ignore.self>
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-exchange-alt"></i> Pindah Router
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Perhatian:</strong> Proses perpindahan router akan:
                                        <ul class="mb-0 mt-2">
                                            <li>Menghapus konfigurasi dari router lama</li>
                                            <li>Membuat konfigurasi baru di router tujuan</li>
                                            <li>Customer akan disconnect sementara selama proses</li>
                                        </ul>
                                    </div>

                                    <form wire:submit.prevent="submitMoveRouter">
                                        {{-- Current Router Info --}}
                                        <div class="mb-3">
                                            <label class="form-label">Router Saat Ini</label>
                                            <input type="text" class="form-control" 
                                                value="{{ $customer->router->name ?? '-' }}" 
                                                readonly>
                                        </div>

                                        {{-- New Router --}}
                                        <div class="mb-3">
                                            <label class="form-label">
                                                Router Tujuan <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control @error('new_router_id') is-invalid @enderror" 
                                                    wire:model="new_router_id">
                                                <option value="">Pilih Router</option>
                                                @foreach($availableRouters as $router)
                                                    <option value="{{ $router['id'] }}" @if(!$router['is_online']) disabled @endif>{{ $router['name'] }} {{ $router['is_online'] ? '(Online)' : '(Offline)' }}</option>
                                                @endforeach
                                            </select>
                                            @error('new_router_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- ✅ Address Pool Dropdown (Mandatory when router selected) --}}
                                        @if($new_router_id && count($availablePoolsForNewRouter) > 0)
                                        <div class="mb-3">
                                            <label class="form-label">
                                                Address Pool <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control @error('new_pool_id') is-invalid @enderror" 
                                                    wire:model.defer="new_pool_id">
                                                <option value="">-- Pilih Address Pool --</option>
                                                @foreach($availablePoolsForNewRouter as $pool)
                                                    <option value="{{ $pool['id'] }}">{{ $pool['label'] }}</option>
                                                @endforeach
                                            </select>
                                            @error('new_pool_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> Pilih address pool untuk router tujuan
                                            </small>
                                        </div>
                                        @elseif($new_router_id && count($availablePoolsForNewRouter) === 0)
                                        <div class="mb-3">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                Router ini tidak memiliki address pool. Silakan tambahkan address pool terlebih dahulu.
                                            </div>
                                        </div>
                                        @endif

                                        {{-- New Username (Optional) --}}
                                        <div class="mb-3">
                                            <label class="form-label">
                                                Username Baru 
                                                <small class="text-muted">(Kosongkan jika tidak berubah)</small>
                                            </label>
                                            <div class="input-group">
                                                <input type="text" 
                                                    class="form-control 
                                                            @error('new_username') is-invalid 
                                                            @elseif($newUsernameChecked && $newUsernameAvailable) is-valid 
                                                            @enderror" 
                                                    wire:model.debounce.500ms="new_username"
                                                    placeholder="{{ $customer->username }}">
                                                <span class="input-group-text">
                                                    <div wire:loading wire:target="new_username">
                                                        <i class="fas fa-spinner fa-spin"></i>
                                                    </div>
                                                    <div wire:loading.remove wire:target="new_username">
                                                        @if($newUsernameChecked)
                                                            @if($newUsernameAvailable)
                                                                <i class="fas fa-check-circle text-success"></i>
                                                            @else
                                                                <i class="fas fa-times-circle text-danger"></i>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </span>
                                            </div>
                                            
                                            @error('new_username')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            
                                            @if($newUsernameChecked && !$newUsernameAvailable)
                                                <div class="invalid-feedback d-block">
                                                    Username sudah digunakan oleh: 
                                                    <strong>{{ $newUsernameExistingCustomer['name'] ?? '' }}</strong>
                                                    <small>({{ $newUsernameExistingCustomer['code'] ?? '' }})</small>
                                                </div>
                                            @endif
                                            
                                            @if($newUsernameChecked && $newUsernameAvailable)
                                                <div class="valid-feedback d-block">
                                                    <i class="fas fa-check-circle"></i> Username tersedia
                                                </div>
                                            @endif
                                        </div>

                                        {{-- New Local Address (Optional) --}}
                                        <div class="mb-3">
                                            <label class="form-label">
                                                Local Address Baru 
                                                <small class="text-muted">(Kosongkan jika tidak berubah)</small>
                                            </label>
                                            <div class="input-group">
                                                <input type="text" 
                                                    class="form-control 
                                                            @error('new_local_address') is-invalid 
                                                            @elseif($newLocalAddressChecked && $newLocalAddressAvailable) is-valid 
                                                            @enderror" 
                                                    wire:model.debounce.500ms="new_local_address"
                                                    placeholder="{{ $customer->local_address ?? '10.10.10.100' }}">
                                                <span class="input-group-text">
                                                    <div wire:loading wire:target="new_local_address">
                                                        <i class="fas fa-spinner fa-spin"></i>
                                                    </div>
                                                    <div wire:loading.remove wire:target="new_local_address">
                                                        @if($newLocalAddressChecked)
                                                            @if($newLocalAddressAvailable)
                                                                <i class="fas fa-check-circle text-success"></i>
                                                            @else
                                                                <i class="fas fa-times-circle text-danger"></i>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </span>
                                            </div>
                                            
                                            @error('new_local_address')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            
                                            @if($newLocalAddressChecked && !$newLocalAddressAvailable)
                                                <div class="invalid-feedback d-block">
                                                    IP sudah digunakan oleh: 
                                                    <strong>{{ $newLocalAddressExistingCustomer['name'] ?? '' }}</strong>
                                                    <small>({{ $newLocalAddressExistingCustomer['code'] ?? '' }})</small>
                                                </div>
                                            @endif
                                            
                                            @if($newLocalAddressChecked && $newLocalAddressAvailable)
                                                <div class="valid-feedback d-block">
                                                    <i class="fas fa-check-circle"></i> IP address tersedia
                                                </div>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                                
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        <i class="fas fa-times"></i> Batal
                                    </button>
                                    <button type="button" 
                                            class="btn btn-info" 
                                            wire:click="submitMoveRouter"
                                            wire:loading.attr="disabled"
                                            @if(!$new_router_id || ($new_username && !$newUsernameAvailable) || ($new_local_address && !$newLocalAddressAvailable)) disabled @endif>
                                        <span wire:loading.remove wire:target="submitMoveRouter">
                                            <i class="fas fa-exchange-alt"></i> Proses Perpindahan
                                        </span>
                                        <span wire:loading wire:target="submitMoveRouter">
                                            <i class="fas fa-spinner fa-spin"></i> Processing...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $latestPurchase = $purchases->first();
                        $canCreateBilling = \App\Helpers\Access::can('as_finance', 'internet_customers')
                            && $customer->userCustomer
                            && (
                                !$latestPurchase
                                || $latestPurchase->payment_method == \App\Schemas\ParamSchema::EXPIRED
                                || $latestPurchase->isConfirmed()
                            );
                        $billingMonthLabel = $customer->userCustomer && $customer->userCustomer->start_billing_date
                            ? \Carbon\Carbon::parse($customer->userCustomer->start_billing_date)->format('F Y')
                            : \Carbon\Carbon::now()->format('F Y');
                    @endphp
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card shadow">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h4 class="text-primary mb-0">
                                        <i class="fas fa-credit-card mr-2"></i>Riwayat Pembayaran
                                    </h4>
                                    @if($canCreateBilling)
                                    <button wire:click="createManualBilling"
                                            wire:loading.attr="disabled"
                                            class="btn btn-sm btn-primary"
                                            onclick="return confirm('Buat tagihan untuk bulan {{ $billingMonthLabel }}?')">
                                        <span wire:loading.remove wire:target="createManualBilling">
                                            <i class="fas fa-plus mr-1"></i>Buat Tagihan
                                        </span>
                                        <span wire:loading wire:target="createManualBilling">
                                            <i class="fas fa-spinner fa-spin mr-1"></i>Memproses...
                                        </span>
                                    </button>
                                    @endif
                                </div>
                                <div class="card-body p-0">
                                    @if($purchases->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Periode</th>
                                                    <th>Metode</th>
                                                    <th>Status</th>
                                                    <th>Jumlah Bayar</th>
                                                    <th>Bukti Pembayaran</th>
                                                    <th>Invoice</th>
                                                    @canAccess('as_finance','internet_customers')
                                                    <th>Aksi</th>
                                                    @endcanAccess
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($purchases as $purchase)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($purchase->created_at)->format('F Y') }}</td>
                                                    <td>{!! $purchase->status_badge!!}</td>
                                                    <td>
                                                        @if($purchase->user_finance_id && $purchase->confirmation_finance_at)
                                                            <span class="badge badge-success">Lunas</span>
                                                        @elseif($purchase->payment_method != \App\Schemas\ParamSchema::EXPIRED)
                                                        <span class="badge badge-danger">Belum Lunas</span>
                                                        @else
                                                        <span class="badge badge-danger">Kadaluarsa</span>
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
                                                    <td>
                                                        <a href="{{ route('internet-customer.download-invoice', $purchase->id) }}"
                                                           class="btn btn-sm btn-primary"
                                                           target="_blank"
                                                           title="Lihat Invoice PDF">
                                                            <i class="fas fa-file-pdf mr-1"></i>Invoice
                                                        </a>
                                                    </td>
                                                    @canAccess('as_finance','internet_customers')
                                                    <td>
                                                        @if($purchase->payment_method == \App\Schemas\ParamSchema::EXPIRED)
                                                            {{-- Payment already expired --}}
                                                            <span class="badge badge-danger">
                                                                <i class="fas fa-times-circle mr-1"></i>Expired
                                                            </span>
                                                        @elseif($purchase->isConfirmed())
                                                            {{-- Payment confirmed --}}
                                                            <i class="fas fa-check-circle mr-1 text-success"></i>
                                                        @else
                                                            {{-- Payment not confirmed and not expired - show buttons --}}
                                                            <div class="btn-group-vertical btn-group-sm" role="group">
                                                                @if($customer->status == \App\Schemas\ParamSchema::WAITING_PAYMENT_CONFIRMATION && isset($purchase->payment_method) && $financeAccess)
                                                                <button class="btn btn-success mb-1" onclick="confirmPayment('{{ $purchase->id }}')">
                                                                    <i class="fas fa-check mr-1"></i>Konfirmasi
                                                                </button>
                                                                @endif

                                                                @if(!$purchase->payment_proof && $financeAccess)
                                                                <button class="btn btn-warning btn-sm mb-1" wire:click="showManualPaymentModal({{ $purchase->id }})">
                                                                    <i class="fas fa-upload mr-1"></i>Upload Bukti
                                                                </button>
                                                                @endif

                                                                @if($financeAccess)
                                                                <button class="btn btn-danger btn-sm" onclick="expirePayment('{{ $purchase->id }}')">
                                                                    <i class="fas fa-times-circle mr-1"></i>Tandai Expired
                                                                </button>
                                                                @endif
                                                            </div>
                                                        @endif
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
                                    @else
                                    <div class="text-center text-muted py-4">
                                        <i class="fas fa-receipt fa-2x mb-2"></i>
                                        <p class="mb-0">Belum ada riwayat pembayaran</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($wablasLogs->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card shadow border-0">
                                <div class="card-header bg-success text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fab fa-whatsapp mr-2"></i>Log Wablas
                                        <span class="badge badge-light text-success ml-2">{{ $wablasLogs->total() }}</span>
                                    </h4>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-sm mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="15%">Waktu</th>
                                                    <th width="10%">Tipe</th>
                                                    <th width="15%">No. HP</th>
                                                    <th>Pesan</th>
                                                    <th width="10%" class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($wablasLogs as $log)
                                                <tr>
                                                    <td class="text-nowrap">
                                                        <small>{{ \Carbon\Carbon::parse($log->created_at)->locale('id')->translatedFormat('d M Y H:i') }}</small>
                                                    </td>
                                                    <td>
                                                        @if($log->type === 'image')
                                                            <span class="badge badge-info"><i class="fas fa-image mr-1"></i>Image</span>
                                                        @elseif($log->type === 'document')
                                                            <span class="badge badge-secondary"><i class="fas fa-file mr-1"></i>Doc</span>
                                                        @elseif($log->type === 'audio')
                                                            <span class="badge badge-warning"><i class="fas fa-microphone mr-1"></i>Audio</span>
                                                        @elseif($log->type === 'video')
                                                            <span class="badge badge-primary"><i class="fas fa-video mr-1"></i>Video</span>
                                                        @else
                                                            <span class="badge badge-light border"><i class="fas fa-comment mr-1"></i>Text</span>
                                                        @endif
                                                    </td>
                                                    <td><small>{{ $log->phone }}</small></td>
                                                    <td>
                                                        <small class="text-muted" style="white-space: pre-wrap; word-break: break-word;">{{ \Illuminate\Support\Str::limit($log->message, 120) }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($log->status === 'success')
                                                            <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Terkirim</span>
                                                        @elseif($log->status === 'failed')
                                                            <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Gagal</span>
                                                        @else
                                                            <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Pending</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($wablasLogs->hasPages())
                                    <div class="card-footer bg-white py-2">
                                        {{ $wablasLogs->withQueryString()->links('vendor.pagination.bootstrap-4') }}
                                    </div>
                                    @endif
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
                                    <input type="text" class="form-control" id="phone_number" wire:model="phone_number" inputmode="numeric" pattern="[0-9]*" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    @error('phone_number') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                @if(!$customer->group_id)
                                    {{-- Belum punya group: tampilkan select group --}}
                                    <div class="form-group">
                                        <label>Assign Group</label>
                                        <select class="form-control" id="editGroupSelect">
                                            <option value="">— Pilih Group —</option>
                                        </select>
                                        <small class="text-muted">Grouping ID akan di-generate otomatis setelah disimpan.</small>
                                    </div>
                                @else
                                    {{-- Sudah punya group: tampilkan edit grouping_id --}}
                                    <div class="form-group">
                                        <label for="grouping_id">
                                            Grouping ID
                                            <small class="text-muted">— prefix: <strong>{{ $customer->group->grouping_prefix ?? '' }}</strong></small>
                                        </label>
                                        <input type="text"
                                               class="form-control @error('grouping_id') is-invalid @enderror"
                                               id="grouping_id"
                                               autocomplete="off"
                                               placeholder="{{ ($customer->group->grouping_prefix ?? '') . 'XXXX' }}">
                                        @error('grouping_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        @if(!$customer->grouping_id)
                                            <small class="text-warning"><i class="fas fa-clock mr-1"></i>Grouping ID sedang di-generate.</small>
                                        @endif
                                    </div>
                                @endif
                            </div>

                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_billing_date">Tanggal Pembayaran Selanjutnya</label>
                                    <input type="date" class="form-control" id="start_billing_date" wire:model="start_billing_date">
                                    @error('start_billing_date') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_billing_date">Batas Pembayaran Selanjutnya</label>
                                    <input type="date" class="form-control" id="end_billing_date" wire:model="end_billing_date">
                                    @error('end_billing_date') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Provinsi</label>
                                    <select class="form-control" id="province_id">
                                        <option value="">-- Pilih Provinsi --</option>
                                        @foreach($provinces as $province)
                                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('province_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kota/Kabupaten</label>
                                    <select class="form-control" id="city_id">
                                        <option value="">-- Pilih Kota/Kabupaten --</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('city_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kecamatan</label>
                                    <select class="form-control" id="district_id">
                                        <option value="">-- Pilih Kecamatan --</option>
                                        @foreach($districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('district_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kelurahan</label>
                                    <select class="form-control" id="subdistrict_id">
                                        <option value="">-- Pilih Kelurahan --</option>
                                        @foreach($subdistricts as $subdistrict)
                                            <option value="{{ $subdistrict->id }}">{{ $subdistrict->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('subdistrict_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address_edit">Alamat Lengkap</label>
                            <textarea class="form-control" id="address_edit" wire:model="address" rows="3" placeholder="Masukkan alamat lengkap"></textarea>
                            @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        {{-- Titik Koordinat --}}
                        <div class="form-group">
                            <label><i class="fas fa-map-marker-alt mr-1"></i>Titik Koordinat GPS</label>
                            @include('partials.location-map-picker', [
                                'mapId'    => 'admin-edit-map',
                                'height'   => '240px',
                                'btnClass' => 'btn btn-outline-primary btn-sm',
                            ])
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ktp_number_input">Nomor KTP / NIK</label>
                                    <input type="text" id="ktp_number_input" class="form-control" wire:model.defer="ktp_number" placeholder="16 digit NIK">
                                    @error('ktp_number') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @if($customer->customer_type === 'bisnis')
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="npwp_number_input">Nomor NPWP</label>
                                    <input type="text" id="npwp_number_input" class="form-control" wire:model.defer="npwp_number" placeholder="00.000.000.0-000.000">
                                    @error('npwp_number') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @endif
                        </div>
                        <small class="text-muted d-block mb-3">
                            <i class="fas fa-info-circle mr-1"></i>
                            Untuk mengganti foto KTP / NPWP, gunakan tombol <strong>Upload</strong> di tabel Data Pribadi.
                        </small>

                        <div class="form-group">
                            <label for="status_active">Status Aktif</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="status_active" wire:model="status_active" >
                                <label class="form-check-label" for="status_active">
                                    Aktif
                                </label>
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
                        <i class="fas fa-tools mr-2"></i>Edit Data Instalasi
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formEditInstalasi">
                        @include('components.alert')

                        {{-- Serial Number --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Serial Number Perangkat</label>
                            <input type="text" class="form-control @error('device_serial_number') is-invalid @enderror"
                                   id="instal_serial_number_input"
                                   value="{{ $device_serial_number }}"
                                   oninput="@this.set('device_serial_number', this.value)"
                                   placeholder="Contoh: SN-123456">
                            @error('device_serial_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- ODP --}}
                        <div class="form-group">
                            <label class="font-weight-bold">ODP (Optical Distribution Point) <span class="text-danger">*</span></label>
                            <select class="form-control @error('instal_odp_id') is-invalid @enderror"
                                    id="instal_odp_select"
                                    onchange="@this.set('instal_odp_id', this.value); onInstalasiOdpChange(this.value)">
                                <option value="">— Pilih ODP —</option>
                            </select>
                            @error('instal_odp_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Pilih ODP sesuai lokasi pemasangan pelanggan.</small>
                        </div>

                        {{-- Group (cascade dari ODP, hanya tampil jika belum punya group) --}}
                        @if(!$customer->group_id)
                        <div class="form-group">
                            <label class="font-weight-bold">Group</label>
                            <select class="form-control" id="instal_group_select"
                                    onchange="@this.set('instal_group_id', this.value); onInstalasiGroupChange(this.value)"
                                    disabled>
                                <option value="">— Pilih ODP dulu —</option>
                            </select>
                            <small class="text-muted">Group difilter otomatis dari ODP yang dipilih.</small>
                        </div>
                        @endif

                        {{-- Grouping ID --}}
                        <div class="form-group">
                            <label class="font-weight-bold">
                                Grouping ID
                                <small class="text-muted font-weight-normal">(bisa diubah jika perlu)</small>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                </div>
                                <input type="text" class="form-control font-weight-bold @error('instal_grouping_id') is-invalid @enderror"
                                       id="instal_grouping_id_input"
                                       value="{{ $instal_grouping_id }}"
                                       oninput="@this.set('instal_grouping_id', this.value)"
                                       placeholder="{{ ($customer->group->grouping_prefix ?? '') . 'XXXX' }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                            id="instal_pakai_btn"
                                            title="Gunakan Grouping ID sebagai Username dan Password">
                                        <i class="fas fa-arrow-right mr-1"></i>Pakai sbg User &amp; Pass
                                    </button>
                                </div>
                                @error('instal_grouping_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div id="instal_grouping_id_feedback" class="mt-1" style="display:none;font-size:.82em;"></div>
                            <small class="text-muted">Saran otomatis saat group dipilih — klik <strong>Pakai sbg User &amp; Pass</strong> untuk mengisi username &amp; password sekaligus.</small>
                        </div>

                        <hr>

                        {{-- Router --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Router</label>
                            <select class="form-control @error('instal_router_id') is-invalid @enderror"
                                    id="instal_router_select"
                                    onchange="@this.set('instal_router_id', this.value)">
                                <option value="">— Pilih Router —</option>
                            </select>
                            @error('instal_router_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Untuk pindah router ke yang berbeda, gunakan tombol <strong>Pindah Router</strong> setelah simpan.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Local Address</label>
                                    <input type="text" class="form-control @error('local_address') is-invalid @enderror"
                                           id="instal_local_address_input"
                                           value="{{ $local_address }}"
                                           oninput="@this.set('local_address', this.value)"
                                           placeholder="192.168.1.1">
                                    @error('local_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('username') is-invalid @enderror"
                                           id="instal_username_input"
                                           value="{{ $username }}"
                                           oninput="@this.set('username', this.value)"
                                           placeholder="username_pppoe">
                                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div id="instal_username_feedback" class="mt-1" style="display:none;font-size:.82em;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('pass_hash') is-invalid @enderror"
                                       id="instal_pass_hash"
                                       value="{{ $pass_hash }}"
                                       oninput="@this.set('pass_hash', this.value)">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button"
                                            onclick="toggleInstalPassword()">
                                        <i class="fas fa-eye" id="instal-pass-eye"></i>
                                    </button>
                                </div>
                            </div>
                            @error('pass_hash') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        {{-- Foto Instalasi — disembunyikan sementara --}}
                        {{-- <div class="form-group">...</div> --}}

                        {{-- Catatan Instalasi --}}
                        <div class="form-group">
                            <label class="font-weight-bold">Catatan Instalasi</label>
                            <textarea class="form-control" id="instal_notes_input" rows="3"
                                      oninput="@this.set('instal_notes', this.value)"
                                      placeholder="Catatan tambahan mengenai proses instalasi...">{{ $instal_notes }}</textarea>
                            @error('instal_notes') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- Hotspot Fields (hanya tampil jika access_type = hotspot) --}}
                        @if($customer->access_type === 'hotspot')
                        <hr>
                        <h6 class="text-primary"><i class="fas fa-wifi mr-1"></i>Konfigurasi Hotspot</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hotspot Server</label>
                                    <select wire:model="hotspot_server_id" class="form-control">
                                        <option value="">-- Tidak ada --</option>
                                        @foreach ($availableHotspotServers as $hs)
                                            <option value="{{ $hs['id'] }}" @selected($hotspot_server_id == $hs['id'])>{{ $hs['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('hotspot_server_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>IP Binding Type</label>
                                    <select wire:model="ip_binding_type" class="form-control">
                                        <option value="">-- Tidak ada --</option>
                                        <option value="direct">Direct (MikroTik)</option>
                                        <option value="radius">Radius (Framed-IP)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Binding Mode</label>
                                    <select wire:model="ip_binding_mode" class="form-control"
                                            @if($ip_binding_type !== 'direct') disabled @endif>
                                        <option value="">-- Tidak ada --</option>
                                        <option value="regular">Regular (login, IP fixed)</option>
                                        <option value="bypassed">Bypassed (tanpa login)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>IP Address <small class="text-muted">(untuk binding)</small></label>
                                    <input type="text" wire:model="ip_address" class="form-control" placeholder="192.168.1.100">
                                    @error('ip_address') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>MAC Address <small class="text-muted">(untuk binding)</small></label>
                                    <input type="text" wire:model="mac_address" class="form-control" placeholder="AA:BB:CC:DD:EE:FF">
                                    @error('mac_address') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="formEditInstalasiClose">Batal</button>
                    <button type="button" id="submitInstalasi" class="btn btn-primary"
                            wire:loading.attr="disabled" wire:target="saveInstalasi">
                        <span wire:loading.remove wire:target="saveInstalasi">
                            <i class="fas fa-save mr-1"></i>Simpan Perubahan
                        </span>
                        <span wire:loading wire:target="saveInstalasi">
                            <i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ✅ UPDATED: Modal Edit Paket Internet (Simplified) --}}
    <div class="modal fade" id="editPackageModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exchange-alt"></i> Ganti Paket Internet
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Info:</strong> Perubahan paket akan langsung diupdate ke router.
                    </div>

                    <form wire:submit.prevent="savePackageChange">
                        {{-- Current Package --}}
                        <div class="mb-3">
                            <label class="form-label">Paket Saat Ini</label>
                            <input type="text" class="form-control"
                                value="{{ $customer->internetPackage->name ?? '-' }} - Rp {{ number_format($customer->internetPackage?->getPriceForRegion($customer->province_id, $customer->city_id, $customer->district_id, $customer->subdistrict_id)['price_nett'] ?? 0, 0, ',', '.') }}"
                                readonly>
                        </div>

                        {{-- New Package --}}
                        <div class="mb-3" wire:ignore>
                            <label class="form-label">
                                Paket Baru <span class="text-danger">*</span>
                            </label>
                            <select id="new_package_id" class="form-control" style="width: 100%">
                                <option value="">Pilih Paket Baru</option>
                            </select>
                            @error('new_package_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="button" 
                            id="btnSavePackage"
                            class="btn btn-warning" 
                            >
                        <span id="btnSaveText">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </span>
                        <span id="btnSaveLoading" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Menyimpan...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Manual Payment (Admin Upload Bukti) -->
    <div class="modal fade" id="adminManualPaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-upload mr-2"></i>Upload Bukti Pembayaran Manual
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        Gunakan form ini untuk mengupload bukti pembayaran yang dilakukan pelanggan melalui platform lain (transfer manual, dll).
                    </div>

                    <div id="admin-payment-error" class="alert alert-danger" style="display:none;"></div>

                    <!-- Jumlah Bulan -->
                    <div class="form-group">
                        <label class="font-weight-bold">Jumlah Bulan <span class="text-danger">*</span></label>
                        <div class="input-group" style="max-width:250px">
                            <button class="btn btn-outline-secondary" type="button" onclick="adminDecreaseMonths()">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" id="admin-months-input" class="form-control text-center font-weight-bold"
                                   min="1" max="24" value="1">
                            <button class="btn btn-outline-secondary" type="button" onclick="adminIncreaseMonths()">
                                <i class="fas fa-plus"></i>
                            </button>
                            <div class="input-group-append">
                                <span class="input-group-text">Bulan</span>
                            </div>
                        </div>
                        <small class="text-muted">Minimal 1 bulan, maksimal 24 bulan</small>
                    </div>

                    <!-- Tanggal Transfer -->
                    <div class="form-group">
                        <label class="font-weight-bold">Tanggal Transfer <span class="text-danger">*</span></label>
                        <input type="date" id="admin-transfer-date" class="form-control"
                               max="{{ date('Y-m-d') }}">
                    </div>

                    <!-- Bank Pengirim -->
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Bank Pengirim</label>
                        <input type="text" id="admin-transfer-bank" class="form-control"
                               placeholder="Contoh: BCA, Mandiri, BNI">
                    </div>

                    <!-- Nama Pengirim -->
                    <div class="form-group">
                        <label class="font-weight-bold">Nama Pemilik Rekening Pengirim</label>
                        <input type="text" id="admin-transfer-account-name" class="form-control"
                               placeholder="Nama sesuai rekening">
                    </div>

                    <!-- Catatan -->
                    <div class="form-group">
                        <label class="font-weight-bold">Catatan (Opsional)</label>
                        <textarea id="admin-transfer-notes" class="form-control" rows="2"
                                  placeholder="Catatan tambahan (jika ada)"></textarea>
                    </div>

                    <!-- Upload Bukti -->
                    <div class="form-group">
                        <label class="font-weight-bold">Bukti Pembayaran <span class="text-danger">*</span></label>
                        <div id="admin-payment-drop-area"
                             class="border border-2 border-dashed rounded p-4 text-center"
                             style="cursor:pointer; border-color:#ddd;">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-2"></i>
                            <p class="mb-1 font-weight-bold">Klik untuk upload atau drag & drop</p>
                            <p class="text-muted small mb-0">PNG, JPG, GIF (Maksimal 2MB)</p>
                            <input id="admin_payment_proof_input" type="file" class="d-none" accept="image/*">
                        </div>
                        <div id="admin-payment-preview" class="mt-3"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-warning" id="adminSubmitPaymentBtn"
                            onclick="adminSubmitPayment()">
                        <i class="fas fa-paper-plane mr-1"></i>Simpan Bukti Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/location-map.js') }}"></script>
    <script>
        // Toggle peta view (read-only) di tabel data pribadi
        function toggleAdminCustomerMap() {
            var wrap = document.getElementById('admin-view-map-wrap');
            if (!wrap) return;
            var hidden = wrap.style.display === 'none';
            wrap.style.display = hidden ? 'block' : 'none';
            if (hidden) {
                var lat = {{ $customer->latitude ?? 'null' }};
                var lng = {{ $customer->longitude ?? 'null' }};
                if (!window._locMaps || !window._locMaps['admin-view-map']) {
                    locMapInit('admin-view-map', lat, lng, null);
                    // Read-only: disable click
                    if (window._locMaps['admin-view-map']) {
                        window._locMaps['admin-view-map'].map.off('click');
                    }
                } else {
                    window._locMaps['admin-view-map'].map.invalidateSize();
                }
            }
        }
        // Konfirmasi Setujui pelanggan pending
        function confirmApproveCustomer() {
            Swal.fire({
                title: 'Setujui Pendaftaran?',
                html: 'Pelanggan akan dipindah ke status <strong>Proses Instalasi</strong>.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check-circle mr-1"></i>Ya, Setujui',
                cancelButtonText: 'Batal',
            }).then(function(result) {
                if (result.isConfirmed) {
                    @this.call('approveCustomer');
                }
            });
        }

        // Konfirmasi Tolak pelanggan pending
        function confirmRejectCustomer() {
            Swal.fire({
                title: 'Tolak Pendaftaran?',
                html: 'Pendaftaran akan ditutup dan tidak dapat dibuka kembali.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-times-circle mr-1"></i>Ya, Tolak',
                cancelButtonText: 'Batal',
            }).then(function(result) {
                if (result.isConfirmed) {
                    @this.call('rejectCustomer');
                }
            });
        }

        // Fungsi untuk membuka modal edit data pribadi
        function openEditPribadiModal() {
            @this.call('openEditPribadiModal');
        }

        // Fungsi untuk membuka modal edit data instalasi
        function openEditInstalasiModal() {
            @this.call('openEditInstalasiModal');
        }

        // Toggle password visibility (generic)
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

        // Toggle password di modal edit instalasi
        function toggleInstalPassword() {
            const input = document.getElementById('instal_pass_hash');
            const icon  = document.getElementById('instal-pass-eye');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function downloadFileFromUrl(url, filename) {
            fetch(url)
                .then(response => response.blob())
                .then(blob => {
                    const blobUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = blobUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(blobUrl);
                    document.body.removeChild(a);
                })
                .catch(error => {
                    console.error('Download error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Gagal mengunduh file. Silakan coba lagi.',
                        confirmButtonText: 'OK'
                    });
                });
        }

        // Listen for download event from Livewire
        window.addEventListener('downloadFile', function(event) {
            Swal.fire({
                title: 'Mengunduh...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            downloadFileFromUrl(event.detail.url, event.detail.filename);
            
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'File berhasil diunduh',
                    showConfirmButton: false,
                    timer: 2000
                });
            }, 1000);
        });

        // ── Admin manual payment helpers ────────────────────────────────────
        window.adminUpdateMonths = function(val) {
            val = parseInt(val);
            if (val < 1) val = 1;
            if (val > 24) val = 24;
            document.getElementById('admin-months-input').value = val;
        };
        window.adminIncreaseMonths = function() {
            adminUpdateMonths(parseInt(document.getElementById('admin-months-input').value) + 1);
        };
        window.adminDecreaseMonths = function() {
            adminUpdateMonths(parseInt(document.getElementById('admin-months-input').value) - 1);
        };

        function adminResetModal() {
            document.getElementById('admin-months-input').value = 1;
            document.getElementById('admin-transfer-date').value = '';
            document.getElementById('admin-transfer-bank').value = '';
            document.getElementById('admin-transfer-account-name').value = '';
            document.getElementById('admin-transfer-notes').value = '';
            document.getElementById('admin_payment_proof_input').value = '';
            document.getElementById('admin-payment-preview').innerHTML = '';
            document.getElementById('admin-payment-error').style.display = 'none';
            document.getElementById('admin-payment-error').innerText = '';
        }

        function adminCloseModal() {
            const modalEl = document.getElementById('adminManualPaymentModal');

            if (!modalEl) {
                console.warn('Modal element not found: adminManualPaymentModal');
                return;
            }

            try {
                // Bootstrap 5
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modal.hide();

                    setTimeout(() => {
                        forceCleanupModal();
                    }, 300);

                    return;
                }

                // Bootstrap 4 fallback
                if (typeof $ !== 'undefined' && typeof $(modalEl).modal === 'function') {
                    $(modalEl).modal('hide');

                    setTimeout(() => {
                        forceCleanupModal();
                    }, 300);

                    return;
                }

                console.warn('Bootstrap modal handler not found');
            } catch (e) {
                console.error('Failed to close modal:', e);
                forceCleanupModal();
            }
        }

        function forceCleanupModal() {
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');

            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());

            const modalEl = document.getElementById('adminManualPaymentModal');
            if (modalEl) {
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
                modalEl.setAttribute('aria-hidden', 'true');
                modalEl.removeAttribute('aria-modal');
                modalEl.removeAttribute('role');
            }
        }

        function adminShowError(msg) {
            const el = document.getElementById('admin-payment-error');
            el.innerText = msg;
            el.style.display = 'block';
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        window.adminSubmitPayment = function() {
            const file         = document.getElementById('admin_payment_proof_input').files[0];
            const transferDate = document.getElementById('admin-transfer-date').value;
            const bank         = document.getElementById('admin-transfer-bank').value.trim();
            const accountName  = document.getElementById('admin-transfer-account-name').value.trim();
            const notes        = document.getElementById('admin-transfer-notes').value.trim();
            const months       = Math.max(1, Math.min(24, parseInt(document.getElementById('admin-months-input').value) || 1));

            // Reset error
            const errEl = document.getElementById('admin-payment-error');
            errEl.style.display = 'none';
            errEl.innerText = '';

            // Client-side validation
            if (!file) {
                adminShowError('Silakan pilih file bukti pembayaran.');
                return;
            }
            if (!transferDate) {
                adminShowError('Tanggal transfer wajib diisi.');
                return;
            }

            const btn = document.getElementById('adminSubmitPaymentBtn');

            function setBtnState(text, disabled) {
                btn.disabled = disabled;
                btn.innerHTML = text;
            }

            setBtnState('<i class="fas fa-spinner fa-spin mr-1"></i>Mengupload...', true);

            // Upload file, lalu kirim semua nilai sebagai parameter — tanpa @this.set()
            @this.upload(
                'admin_payment_proof',
                file,
                function() {
                    // Upload sukses: panggil backend dengan semua nilai sebagai argumen
                    setBtnState('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...', true);

                    @this.call('submitManualPayment', months, transferDate, bank || null, accountName || null, notes || null)
                        .then(function() {
                            // Tutup modal SEBELUM Livewire re-render (mount) agar instance Bootstrap masih ada
                            adminCloseModal();
                            forceCleanupModal();
                            setBtnState('<i class="fas fa-paper-plane mr-1"></i>Simpan Bukti Pembayaran', false);
                        })
                        .catch(function(err) {
                            setBtnState('<i class="fas fa-paper-plane mr-1"></i>Simpan Bukti Pembayaran', false);
                            adminShowError('Gagal menyimpan: ' + (err.message || 'Coba lagi.'));
                        });
                },
                function(err) {
                    setBtnState('<i class="fas fa-paper-plane mr-1"></i>Simpan Bukti Pembayaran', false);
                    adminShowError('Gagal mengupload file. Pastikan ukuran ≤ 2MB dan format gambar.');
                    console.error('Upload error:', err);
                },
                function(event) {
                    const pct = Math.round((event.detail && event.detail.progress) || 0);
                    setBtnState('<i class="fas fa-spinner fa-spin mr-1"></i>Uploading ' + pct + '%...', true);
                }
            );
        };

        document.addEventListener('livewire:load', function () {

            // ── State availability form instalasi ─────────────────────────────────
            var _instalasiUsernameOk = true;
            var _instalasiGroupingOk = true;

            function clearInstalasiErrors() {
                document.querySelectorAll('#editInstalasiModal .js-instal-error').forEach(function(el) { el.remove(); });
                document.querySelectorAll('#editInstalasiModal .is-invalid').forEach(function(el) {
                    el.classList.remove('is-invalid');
                });
            }
            function markInstalasiError(el, msg) {
                if (!el) return;
                el.classList.add('is-invalid');
                var parent = el.closest('.input-group') || el.parentElement;
                var fb = parent.querySelector('.js-instal-error');
                if (!fb) {
                    fb = document.createElement('div');
                    fb.className = 'invalid-feedback d-block js-instal-error';
                    parent.appendChild(fb);
                }
                fb.textContent = msg;
            }

            window.addEventListener('instalasiUsernameCheckComplete', function(e) {
                _instalasiUsernameOk = !!e.detail.available;
            });
            window.addEventListener('instalasiGroupingIdCheckComplete', function(e) {
                _instalasiGroupingOk = !!e.detail.available;
            });

            // Show / hide admin manual payment modal
            window.addEventListener('show-admin-manual-payment-modal', function() {
                adminResetModal();
                new bootstrap.Modal(document.getElementById('adminManualPaymentModal')).show();
            });

            window.addEventListener('hide-admin-manual-payment-modal', function() {
                adminCloseModal();
                adminResetModal();
            });

            // Drop area
            const adminDropArea = document.getElementById('admin-payment-drop-area');
            if (adminDropArea) {
                adminDropArea.addEventListener('click', function() {
                    document.getElementById('admin_payment_proof_input').click();
                });
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(ev => {
                    adminDropArea.addEventListener(ev, function(e) { e.preventDefault(); e.stopPropagation(); });
                });
                ['dragenter', 'dragover'].forEach(ev => {
                    adminDropArea.addEventListener(ev, function() { this.classList.add('border-primary', 'bg-light'); });
                });
                ['dragleave', 'drop'].forEach(ev => {
                    adminDropArea.addEventListener(ev, function() { this.classList.remove('border-primary', 'bg-light'); });
                });
                adminDropArea.addEventListener('drop', function(e) {
                    const input = document.getElementById('admin_payment_proof_input');
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }

            // File preview
            const adminProofInput = document.getElementById('admin_payment_proof_input');
            if (adminProofInput) {
                adminProofInput.addEventListener('change', function(e) {
                    const file    = e.target.files[0];
                    const preview = document.getElementById('admin-payment-preview');
                    if (!file) { preview.innerHTML = ''; return; }
                    if (!file.type.match('image.*')) {
                        preview.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-2"></i>File harus berupa gambar (JPG, PNG, GIF)</div>';
                        this.value = ''; return;
                    }
                    if (file.size > 2 * 1024 * 1024) {
                        preview.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Ukuran file terlalu besar! Maksimal 2MB.</div>';
                        this.value = ''; return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        preview.innerHTML = `<div class="card border-success"><div class="card-body text-center">
                            <img src="${ev.target.result}" class="img-fluid rounded" style="max-height:250px">
                            <div class="mt-2"><button type="button" class="btn btn-sm btn-danger"
                                onclick="document.getElementById('admin_payment_proof_input').value='';document.getElementById('admin-payment-preview').innerHTML=''">
                                <i class="fas fa-times mr-1"></i>Hapus</button></div>
                        </div></div>`;
                    };
                    reader.readAsDataURL(file);
                });
            }

            // Error dari backend manual payment (via dispatchBrowserEvent)
            window.addEventListener('admin-payment-error', function(event) {
                const btn = document.getElementById('adminSubmitPaymentBtn');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane mr-1"></i>Simpan Bukti Pembayaran';
                }
                adminShowError(event.detail.message || 'Terjadi kesalahan.');
            });

            document.getElementById('btnSavePackage').addEventListener('click', function() {
                const btn = this;
                const btnText = document.getElementById('btnSaveText');
                const btnLoading = document.getElementById('btnSaveLoading');
                
                const newPackageId = document.getElementById('new_package_id').value;

                @this.set('new_package_id', newPackageId);

                // Disable button
                btn.disabled = true;
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline';
                
                console.log('Calling savePackageChange on Livewire component...');
                
                // Call Livewire method
                @this.call('savePackageChange')
                    .then(() => {
                        console.log('savePackageChange completed');
                    })
                    .catch((error) => {
                        console.error('savePackageChange failed:', error);
                        
                        // Re-enable button on error
                        btn.disabled = false;
                        btnText.style.display = 'inline';
                        btnLoading.style.display = 'none';
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menyimpan perubahan: ' + error.message
                        });
                    });
            });

            window.addEventListener('show-edit-package-modal', (event) => {
                const packages = event.detail.packages || [];
                
                // Construct options HTML
                let optionsHtml = '<option value="">Pilih Paket Baru</option>';
                packages.forEach(pkg => {
                    optionsHtml += `<option value="${pkg.id}">${pkg.label}</option>`;
                });
                
                $('#new_package_id').html(optionsHtml);
                new bootstrap.Modal(document.getElementById('editPackageModal')).show();
                
                // Initialize Select2 after modal opens
                setTimeout(() => {
                    $('#new_package_id').select2({
                        placeholder: "Pilih Paket Baru",
                        width: '100%',
                        dropdownParent: $('#editPackageModal')
                    }).on('change', function (e) {
                        @this.set('new_package_id', $(this).val());
                    });
                }, 150);
            });

            window.addEventListener('hide-edit-package-modal', () => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editPackageModal'));
                if (modal) modal.hide();
                
                // Destroy logic if needed later
                // $('#new_package_id').select2('destroy');
            });

            // Event untuk menampilkan modal edit data pribadi
            window.addEventListener('open-move-router-modal', (e) => {
                new bootstrap.Modal(document.getElementById('moveRouterModal')).show();
            });
            // Close move router modal
            window.addEventListener('close-move-router-modal', () => {
                new bootstrap.Modal(document.getElementById('moveRouterModal')).hide();
            });

            // ✅ Refresh page after delay
            window.addEventListener('refresh-after-delay', (event) => {
                setTimeout(() => {
                    location.reload();
                }, event.detail.delay || 3000);
            });

            // ============================================================
            // Select2 Alamat — Fixed: populate options from event data
            // ============================================================
            var _addrSuppress = false;
            var _addrFields = ['province_id','city_id','district_id','subdistrict_id'];

            /**
             * Populate a <select> element with options from an array of {id, name}
             */
            function populateSelect(selectId, items, placeholder) {
                var el = document.querySelector('#editPribadiModal #' + selectId);
                if (!el) return;
                var html = '<option value="">' + (placeholder || '-- Pilih --') + '</option>';
                (items || []).forEach(function(item) {
                    html += '<option value="' + item.id + '">' + item.name + '</option>';
                });
                el.innerHTML = html;
            }

            /**
             * Init Select2 on address fields and set their values
             */
            function initAddrSelect2(vals) {
                _addrFields.forEach(function(id) {
                    var $el = $('#editPribadiModal #' + id);
                    if (!$el.length) return;
                    if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');

                    $el.select2({ placeholder:'-- Pilih --', allowClear:true, width:'100%',
                                dropdownParent:$('#editPribadiModal') });

                    $el.off('change.addr').on('change.addr', function() {
                        if (_addrSuppress) return;
                        @this.set(id, $(this).val() || null);
                    });

                    // Set value programmatically without triggering Livewire sync
                    var v = vals && vals[id] != null ? String(vals[id]) : null;
                    _addrSuppress = true;
                    $el.val(v).trigger('change'); // trigger('change') is required for Select2 to update UI
                    _addrSuppress = false;
                });
            }

            // ── Grouping ID duplicate check (show page) ──────────────────────
            function resetGroupingIdStateShow() {
                var input = document.getElementById('grouping_id');
                if (!input) return;
                input.classList.remove('is-valid', 'is-invalid');
                var err = input.parentElement?.querySelector('.grouping-id-error-msg');
                if (err) err.remove();
                window._showGroupingIdAvailable = true;
            }

            // Debounced check — fired when user stops typing in #grouping_id (edit pribadi)
            var _showGroupingTimer = null;
            document.addEventListener('input', function(e) {
                if (!e.target || e.target.id !== 'grouping_id') return;
                clearTimeout(_showGroupingTimer);
                var val = e.target.value.trim();
                if (!val || val.length < 2) { resetGroupingIdStateShow(); return; }
                _showGroupingTimer = setTimeout(function() {
                    @this.call('checkGroupingIdAvailabilityShow', val);
                }, 400);
            });

            window.addEventListener('groupingIdCheckComplete', function(event) {
                var data  = event.detail;
                var input = document.getElementById('grouping_id');
                if (!input) return; // tidak semua customer punya input ini

                var wrap = input.parentElement;
                var errorDiv = wrap?.querySelector('.grouping-id-error-msg');

                if (data.available) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                    if (errorDiv) errorDiv.remove();
                    window._showGroupingIdAvailable = true;
                } else {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                    if (!errorDiv) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback d-block grouping-id-error-msg';
                        wrap.appendChild(errorDiv);
                    }
                    errorDiv.innerHTML = 'Grouping ID sudah digunakan oleh: <strong>' + data.existing.code + ' - ' + data.existing.name + '</strong>';
                    window._showGroupingIdAvailable = false;
                }
            });

            window.addEventListener('showEditPribadiModal', function(e) {
                document.getElementById('name').value               = e.detail.name || '';
                document.getElementById('email').value              = e.detail.email || '';
                document.getElementById('phone_number').value       = e.detail.phone_number || '';
                document.getElementById('start_billing_date').value = e.detail.start_billing_date || '';
                document.getElementById('end_billing_date').value   = e.detail.end_billing_date || '';
                document.getElementById('status_active').checked    = !!e.detail.status_active;
                document.getElementById('address_edit').value       = e.detail.address || '';
                var ktpEl = document.getElementById('ktp_number_input');
                if (ktpEl) ktpEl.value = e.detail.ktp_number || '';
                var npwpEl = document.getElementById('npwp_number_input');
                if (npwpEl) npwpEl.value = e.detail.npwp_number || '';
                if(e.detail.npwp_number) {
                    document.getElementById('npwp_number_input').value  = e.detail.npwp_number || '';
                }

                // grouping_id input (may not exist if customer has no group yet)
                var gidEl = document.getElementById('grouping_id');
                if (gidEl) {
                    gidEl.value = e.detail.grouping_id || '';
                    resetGroupingIdStateShow();
                }

                // Group select (shown only when customer has no group_id)
                var groupSel = document.getElementById('editGroupSelect');
                if (groupSel && !e.detail.has_group) {
                    groupSel.innerHTML = '<option value="">— Pilih Group —</option>';
                    var groups = e.detail.groups_for_edit || [];
                    groups.forEach(function(g) {
                        var opt = document.createElement('option');
                        opt.value = g.id;
                        opt.textContent = g.description ? g.name + ' — ' + g.description : g.name;
                        groupSel.appendChild(opt);
                    });
                    groupSel.value = e.detail.edit_group_id || '';
                    groupSel.onchange = function() {
                        @this.set('edit_group_id', groupSel.value || null);
                    };
                }

                var startIn = document.getElementById('start_billing_date');
                var endIn   = document.getElementById('end_billing_date');
                startIn.onchange = function() {
                    if (!startIn.value) return;
                    var d = new Date(startIn.value); d.setDate(d.getDate() + 5);
                    endIn.value = d.toISOString().slice(0,10);
                };

                var pid = e.detail.province_id,
                    cid = e.detail.city_id,
                    did = e.detail.district_id,
                    sid = e.detail.subdistrict_id;

                // Populate city/district/subdistrict options DIRECTLY from event data
                // This bypasses Livewire DOM morphing race conditions
                populateSelect('city_id', e.detail.cities || [], '-- Pilih Kota/Kabupaten --');
                populateSelect('district_id', e.detail.districts || [], '-- Pilih Kecamatan --');
                populateSelect('subdistrict_id', e.detail.subdistricts || [], '-- Pilih Kelurahan --');

                new bootstrap.Modal(document.getElementById('editPribadiModal')).show();

                // Init Select2 + map after modal animation
                setTimeout(function() {
                    initAddrSelect2({province_id:pid, city_id:cid, district_id:did, subdistrict_id:sid});

                    // Init edit map (destroy old instance if modal was reopened)
                    locMapDestroy('admin-edit-map');
                    var eLat = e.detail.latitude  || null;
                    var eLng = e.detail.longitude || null;
                    locMapInit('admin-edit-map', eLat, eLng, function(rLat, rLng) {
                        @this.set('latitude',  rLat);
                        @this.set('longitude', rLng);
                    });
                }, 300);
            });

            // After user cascade change (province→city→district→subdistrict) → reinit Select2
            Livewire.hook('message.processed', function(message, component) {
                var modal = document.getElementById('editPribadiModal');
                if (!modal || !modal.classList.contains('show')) return;
                setTimeout(function() {
                    initAddrSelect2({
                        province_id:    @this.province_id,
                        city_id:        @this.city_id,
                        district_id:    @this.district_id,
                        subdistrict_id: @this.subdistrict_id,
                    });
                    var addr = document.getElementById('address_edit')?.value;
                    if (!addr) {
                        var _lwAddr = @this.get('address');
                        if (_lwAddr) document.getElementById('address_edit').value = _lwAddr;
                    }
                }, 100);
            });

            // Handle cascade update dari server (province/city/district changed)
            window.addEventListener('addressCascadeUpdate', function(e) {
                var modal = document.getElementById('editPribadiModal');
                if (!modal || !modal.classList.contains('show')) return;

                var d = e.detail;

                // Populate selects sesuai level yang berubah
                if (d.level === 'province') {
                    populateSelect('city_id',        d.cities        || [], '-- Pilih Kota/Kabupaten --');
                    populateSelect('district_id',    d.districts     || [], '-- Pilih Kecamatan --');
                    populateSelect('subdistrict_id', d.subdistricts  || [], '-- Pilih Kelurahan --');
                    // Reinit Select2 + reset nilai downstream
                    ['city_id','district_id','subdistrict_id'].forEach(function(id) {
                        var $el = $('#editPribadiModal #' + id);
                        if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
                        $el.select2({ placeholder:'-- Pilih --', allowClear:true, width:'100%',
                                    dropdownParent:$('#editPribadiModal') });
                        $el.off('change.addr').on('change.addr', function() {
                            if (_addrSuppress) return;
                            @this.set(id, $(this).val() || null);
                        });
                        _addrSuppress = true;
                        $el.val(null).trigger('change');
                        _addrSuppress = false;
                    });
                }

                if (d.level === 'city') {
                    populateSelect('district_id',    d.districts     || [], '-- Pilih Kecamatan --');
                    populateSelect('subdistrict_id', d.subdistricts  || [], '-- Pilih Kelurahan --');
                    ['district_id','subdistrict_id'].forEach(function(id) {
                        var $el = $('#editPribadiModal #' + id);
                        if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
                        $el.select2({ placeholder:'-- Pilih --', allowClear:true, width:'100%',
                                    dropdownParent:$('#editPribadiModal') });
                        $el.off('change.addr').on('change.addr', function() {
                            if (_addrSuppress) return;
                            @this.set(id, $(this).val() || null);
                        });
                        _addrSuppress = true;
                        $el.val(null).trigger('change');
                        _addrSuppress = false;
                    });
                }

                if (d.level === 'district') {
                    populateSelect('subdistrict_id', d.subdistricts || [], '-- Pilih Kelurahan --');
                    var $el = $('#editPribadiModal #subdistrict_id');
                    if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
                    $el.select2({ placeholder:'-- Pilih --', allowClear:true, width:'100%',
                                dropdownParent:$('#editPribadiModal') });
                    $el.off('change.addr').on('change.addr', function() {
                        if (_addrSuppress) return;
                        @this.set('subdistrict_id', $(this).val() || null);
                    });
                    _addrSuppress = true;
                    $el.val(null).trigger('change');
                    _addrSuppress = false;
                }
            });

            // Event untuk menyembunyikan modal edit data pribadi
            window.addEventListener('hideEditPribadiModal', function() {
                $("#editPribadiModalClick").click();
                var modal = new bootstrap.Modal(document.getElementById('editPribadiModal'));
                modal.hide();
            });

            // Event untuk menampilkan modal edit data instalasi
            window.addEventListener('showEditInstalasiModal', function(e) {
                var d = e.detail;

                // Populate ODP select
                var odpSel = document.getElementById('instal_odp_select');
                if (odpSel) {
                    odpSel.innerHTML = '<option value="">— Pilih ODP —</option>';
                    (d.odps || []).forEach(function(odp) {
                        var o = document.createElement('option');
                        o.value = odp.id;
                        o.textContent = odp.label;
                        if (odp.id == d.selected_odp) o.selected = true;
                        odpSel.appendChild(o);
                    });
                }

                // Populate Router select
                var rSel = document.getElementById('instal_router_select');
                if (rSel) {
                    rSel.innerHTML = '<option value="">— Pilih Router —</option>';
                    (d.routers || []).forEach(function(r) {
                        var o = document.createElement('option');
                        o.value = r.id;
                        o.textContent = r.name;
                        if (r.id == d.selected_router) o.selected = true;
                        rSel.appendChild(o);
                    });
                }

                // Populate Group select
                var gSel = document.getElementById('instal_group_select');
                if (gSel) {
                    gSel.innerHTML = '<option value="">— Pilih Group —</option>';
                    (d.groups || []).forEach(function(g) {
                        var o = document.createElement('option');
                        o.value = g.id;
                        o.textContent = g.name;
                        if (g.id == d.selected_group) o.selected = true;
                        gSel.appendChild(o);
                    });
                    gSel.disabled = (d.groups || []).length === 0;
                }

                // Isi text inputs dari payload (wire:model tidak reliabel di dalam Bootstrap modal)
                var setVal = function(id, val) {
                    var el = document.getElementById(id);
                    if (el) el.value = val || '';
                };
                setVal('instal_username_input', d.username);
                setVal('instal_pass_hash', d.pass_hash);
                setVal('instal_local_address_input', d.local_address);
                setVal('instal_grouping_id_input', d.grouping_id);
                setVal('instal_serial_number_input', d.serial_number);
                // textarea
                var notesEl = document.getElementById('instal_notes_input');
                if (notesEl) notesEl.value = d.notes || '';

                // Reset feedback lama, lalu cek ketersediaan username jika sudah ada
                resetInstalasiUsernameFeedback();
                resetInstalasiGroupingIdState();
                if (d.username && d.username.length >= 3) {
                    @this.call('checkInstalasiUsernameAvailability', d.username);
                }
                if (d.grouping_id && d.grouping_id.length >= 2) {
                    @this.call('checkInstalasiGroupingIdAvailability', d.grouping_id);
                }

                // Reset availability flags dan error saat modal buka
                _instalasiUsernameOk = true;
                _instalasiGroupingOk = true;
                clearInstalasiErrors();

                $('#editInstalasiModal').modal('show');
            });

            // ── ODP / Group / Grouping ID cascade ──────────────────────────────────

            // Harus pakai window.* agar bisa dipanggil dari attribute onchange HTML
            window.onInstalasiOdpChange = function(odpId) {
                var gSel = document.getElementById('instal_group_select');
                if (gSel) {
                    gSel.innerHTML = '<option value="">— Memuat group... —</option>';
                    gSel.disabled = true;
                }
                @this.set('instal_group_id', null);
                @this.set('instal_grouping_id', null);
                var inp = document.getElementById('instal_grouping_id_input');
                if (inp) inp.value = '';
                resetInstalasiGroupingIdState();
                if (odpId) {
                    @this.call('changeInstalasiOdp', odpId);
                }
            };

            // Setelah server memuat group baru (cascade ODP), perbarui select group
            window.addEventListener('instalasiGroupsLoaded', function(e) {
                var gSel = document.getElementById('instal_group_select');
                if (!gSel) return;
                var groups = e.detail.groups || [];
                gSel.innerHTML = '<option value="">' + (groups.length ? '— Pilih Group —' : '— Tidak ada group —') + '</option>';
                groups.forEach(function(g) {
                    var o = document.createElement('option');
                    o.value = g.id;
                    o.textContent = g.name;
                    gSel.appendChild(o);
                });
                gSel.disabled = groups.length === 0;
            });

            // Saat user pilih Group → auto-generate Grouping ID
            window.onInstalasiGroupChange = function(groupId) {
                resetInstalasiGroupingIdState();
                if (groupId) {
                    @this.call('previewInstalasiGroupingId', groupId);
                } else {
                    var inp = document.getElementById('instal_grouping_id_input');
                    if (inp) inp.value = '';
                    @this.set('instal_grouping_id', null);
                }
            };

            // Terima preview Grouping ID dari server → isi field + cek ketersediaan
            window.addEventListener('instalasi-grouping-id-preview', function(e) {
                var preview = e.detail.preview;
                var input = document.getElementById('instal_grouping_id_input');
                if (!input) return;
                if (preview) {
                    input.value = preview;
                    @this.set('instal_grouping_id', preview);
                    @this.call('checkInstalasiGroupingIdAvailability', preview);
                } else {
                    input.value = '';
                    @this.set('instal_grouping_id', null);
                    resetInstalasiGroupingIdState();
                }
            });

            // Tombol "Pakai sbg User & Pass"
            // Langsung set DOM dulu agar user langsung melihat nilai,
            // lalu panggil SATU method PHP yang set username+pass+cek available dalam satu round-trip
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#instal_pakai_btn')) return;
                var groupingId = (document.getElementById('instal_grouping_id_input')?.value || '').trim();
                if (!groupingId) return;

                // Set DOM langsung — user langsung lihat nilainya
                var usrInput  = document.getElementById('instal_username_input');
                var passInput = document.getElementById('instal_pass_hash');
                if (usrInput)  usrInput.value  = groupingId;
                if (passInput) passInput.value = groupingId;

                resetInstalasiUsernameFeedback();

                // Satu round-trip: set username+pass di server lalu cek availability
                @this.call('useGroupingIdAsCredentials', groupingId);
            });

            // ── Uniqueness check: Username ──────────────────────────────────────────

            function resetInstalasiUsernameFeedback() {
                var fb = document.getElementById('instal_username_feedback');
                var inp = document.getElementById('instal_username_input');
                if (fb) { fb.style.display = 'none'; fb.innerHTML = ''; }
                if (inp) { inp.classList.remove('is-valid','is-invalid'); }
            }

            window.addEventListener('instalasiUsernameCheckComplete', function(e) {
                var data = e.detail;
                var inp  = document.getElementById('instal_username_input');
                var fb   = document.getElementById('instal_username_feedback');
                if (!inp || !fb) return;
                if (data.available) {
                    inp.classList.remove('is-invalid'); inp.classList.add('is-valid');
                    fb.innerHTML = '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>Username tersedia</span>';
                    fb.style.display = 'block';
                } else {
                    inp.classList.remove('is-valid'); inp.classList.add('is-invalid');
                    fb.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle mr-1"></i>Sudah dipakai oleh: <strong>' + data.existing.code + ' - ' + data.existing.name + '</strong></span>';
                    fb.style.display = 'block';
                }
            });

            var _instalasiUsernameTimer = null;
            document.addEventListener('input', function(e) {
                if (!e.target || e.target.id !== 'instal_username_input') return;
                resetInstalasiUsernameFeedback();
                clearTimeout(_instalasiUsernameTimer);
                var val = e.target.value.trim();
                if (val.length < 3) return;
                _instalasiUsernameTimer = setTimeout(function() {
                    @this.call('checkInstalasiUsernameAvailability', val);
                }, 450);
            });

            // ── Uniqueness check: Grouping ID ───────────────────────────────────────

            function resetInstalasiGroupingIdState() {
                var fb  = document.getElementById('instal_grouping_id_feedback');
                var inp = document.getElementById('instal_grouping_id_input');
                if (fb)  { fb.style.display = 'none'; fb.innerHTML = ''; }
                if (inp) { inp.classList.remove('is-valid','is-invalid'); }
            }

            window.addEventListener('instalasiGroupingIdCheckComplete', function(e) {
                var data = e.detail;
                var inp  = document.getElementById('instal_grouping_id_input');
                var fb   = document.getElementById('instal_grouping_id_feedback');
                if (!inp || !fb) return;
                if (data.available) {
                    inp.classList.remove('is-invalid'); inp.classList.add('is-valid');
                    fb.innerHTML = '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>Grouping ID tersedia</span>';
                    fb.style.display = 'block';
                } else {
                    inp.classList.remove('is-valid'); inp.classList.add('is-invalid');
                    fb.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle mr-1"></i>Sudah dipakai oleh: <strong>' + data.existing.code + ' - ' + data.existing.name + '</strong></span>';
                    fb.style.display = 'block';
                }
            });

            var _instalasiGroupingTimer = null;
            document.addEventListener('input', function(e) {
                if (!e.target || e.target.id !== 'instal_grouping_id_input') return;
                resetInstalasiGroupingIdState();
                clearTimeout(_instalasiGroupingTimer);
                var val = e.target.value.trim();
                if (val.length < 2) return;
                _instalasiGroupingTimer = setTimeout(function() {
                    @this.call('checkInstalasiGroupingIdAvailability', val);
                }, 450);
            });

            // Event untuk menyembunyikan modal edit data instalasi
            window.addEventListener('hideEditInstalasiModal', function() {
                var closeBtn = document.getElementById('formEditInstalasiClose');
                if (closeBtn) {
                    closeBtn.click();
                } else {
                    $('#editInstalasiModal').modal('hide');
                }
            });

            // Event listener untuk submit data pribadi
            document.getElementById('submitPribadi').addEventListener('click', function() {
                // Tampilkan konfirmasi SweetAlert
                Swal.fire({
                    title: 'Konfirmasi Perubahan',
                    html: '<div class="text-danger font-weight-bold mb-2">' +
                          '<i class="fas fa-exclamation-triangle"></i> Data Ini Sangat Sensitif!' +
                          '</div>' +
                          '<p>Apakah Anda yakin ingin menyimpan perubahan data pribadi?</p>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Kumpulkan semua nilai dari DOM sekaligus sebelum ada request Livewire apapun.
                        // JANGAN tutup modal dulu — penutupan prematur men-trigger hidden.bs.modal
                        // yang mengirim @this.set() reset secara bersamaan dan meng-null-kan
                        // npwp_number & npwp_photo_upload (race condition Livewire v2).
                        // Modal akan ditutup otomatis oleh PHP via event hideEditPribadiModal.
                        var _name            = document.getElementById('name').value;
                        var _email           = document.getElementById('email').value;
                        var _phone           = document.getElementById('phone_number').value;
                        var _ktpNumber       = document.getElementById('ktp_number_input')?.value || null;
                        var _npwpNumber      = document.getElementById('npwp_number_input')?.value || null;
                        var _startBilling    = document.getElementById('start_billing_date').value;
                        var _endBilling      = document.getElementById('end_billing_date').value;
                        var _statusActive    = document.getElementById('status_active').checked;
                        var _groupingId      = document.getElementById('grouping_id')?.value || null;
                        var _address         = document.getElementById('address_edit').value;

                        if (_groupingId && window._showGroupingIdAvailable === false) {
                            Swal.fire({ icon: 'error', title: 'Grouping ID Duplikat', text: 'Grouping ID sudah digunakan pelanggan lain. Silakan ganti terlebih dahulu.' });
                            return;
                        }
                        var _provVal         = $('#editPribadiModal #province_id').val() || null;
                        var _cityVal         = $('#editPribadiModal #city_id').val() || null;
                        var _distVal         = $('#editPribadiModal #district_id').val() || null;
                        var _subdVal         = $('#editPribadiModal #subdistrict_id').val() || null;
                        
                        // Set semua nilai ke Livewire — satu batch berurutan, lalu save
                        @this.set('name',               _name);
                        @this.set('email',              _email);
                        @this.set('phone_number',       _phone);
                        @this.set('ktp_number',         _ktpNumber);
                        @this.set('npwp_number',        _npwpNumber);
                        @this.set('start_billing_date', _startBilling);
                        @this.set('end_billing_date',   _endBilling);
                        @this.set('status_active',      _statusActive);
                        @this.set('grouping_id',        _groupingId);
                        @this.set('address',            _address);

                        // Location fields sekaligus (tanpa cascade), lalu save
                        @this.call('initLocationFields', _provVal, _cityVal, _distVal, _subdVal).then(function() {
                            @this.call('savePribadi');
                        });
                    }
                });
            });

            // ── Submit dengan validasi JS terlebih dahulu ─────────────────────────
            document.getElementById('submitInstalasi').addEventListener('click', function() {
                clearInstalasiErrors();

                var errors = [];

                var odpVal      = document.getElementById('instal_odp_select')?.value || '';
                var routerVal   = document.getElementById('instal_router_select')?.value || '';
                var groupingVal = (document.getElementById('instal_grouping_id_input')?.value || '').trim();
                var usernameVal = (document.getElementById('instal_username_input')?.value || '').trim();
                var passVal     = (document.getElementById('instal_pass_hash')?.value || '').trim();
                var groupSel    = document.getElementById('instal_group_select');

                if (!odpVal) {
                    markInstalasiError(document.getElementById('instal_odp_select'), 'ODP wajib dipilih.');
                    errors.push('odp');
                }
                if (groupSel && !groupSel.disabled && !groupSel.value) {
                    markInstalasiError(groupSel, 'Group wajib dipilih.');
                    errors.push('group');
                }
                if (!groupingVal) {
                    markInstalasiError(document.getElementById('instal_grouping_id_input'), 'Grouping ID wajib diisi.');
                    errors.push('grouping');
                } else if (!_instalasiGroupingOk) {
                    markInstalasiError(document.getElementById('instal_grouping_id_input'), 'Grouping ID sudah dipakai, gunakan nilai lain.');
                    errors.push('grouping_taken');
                }
                if (!routerVal) {
                    markInstalasiError(document.getElementById('instal_router_select'), 'Router wajib dipilih.');
                    errors.push('router');
                }
                if (!usernameVal) {
                    markInstalasiError(document.getElementById('instal_username_input'), 'Username wajib diisi.');
                    errors.push('username');
                } else if (!_instalasiUsernameOk) {
                    markInstalasiError(document.getElementById('instal_username_input'), 'Username sudah dipakai, gunakan username lain.');
                    errors.push('username_taken');
                }
                if (passVal.length < 3) {
                    markInstalasiError(document.getElementById('instal_pass_hash'), passVal ? 'Password minimal 3 karakter.' : 'Password wajib diisi.');
                    errors.push('pass');
                }

                if (errors.length > 0) {
                    // Scroll ke error pertama
                    var firstErr = document.querySelector('#editInstalasiModal .is-invalid');
                    if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                @this.call('saveInstalasi');
            });

            // Reset form ketika modal ditutup
            $('#editPribadiModal, #editInstalasiModal').on('hidden.bs.modal', function () {
                @this.set('name', '{{ $customer->name }}');
                @this.set('email', '{{ $customer->userCustomer->email ?? '' }}');
                @this.set('phone_number', '{{ $customer->userCustomer->phone_number ?? '' }}');
                @this.set('start_billing_date', '{{ $customer->userCustomer->start_billing_date ?? '' }}');
                @this.set('end_billing_date', '{{ $customer->userCustomer->end_billing_date ?? '' }}');
                @this.set('province_id', '{{ $customer->province_id ?? '' }}' || null);
                @this.set('city_id', '{{ $customer->city_id ?? '' }}' || null);
                @this.set('district_id', '{{ $customer->district_id ?? '' }}' || null);
                @this.set('subdistrict_id', '{{ $customer->subdistrict_id ?? '' }}' || null);
                @this.set('address', '{{ $customer->address ?? '' }}');
                @this.set('local_address', '{{ $customer->local_address ?? '' }}');
                @this.set('username', '{{ $customer->username ?? '' }}');
                @this.set('pass_hash', '{{ $customer->pass_hash ?? '' }}');
                @this.set('device_serial_number', '{{ $customer->installation->device_serial_number ?? '' }}');
                @this.set('ip_address', '{{ $customer->ip_address ?? '' }}');
                @this.set('mac_address', '{{ $customer->mac_address ?? '' }}');
                

                // Reset error messages
                @this.call('clearErrors');
            });
        });
    </script>
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

        function expirePayment(purchaseId) 
        {
            Swal.fire({
                title: 'Tandai Pembayaran Expired?',
                text: "Pembayaran ini akan ditandai sebagai expired dan tidak dapat dibayar lagi.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, tandai expired',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Call Livewire method
                    @this.call('expirePayment', purchaseId);
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
                
                document.getElementById('modalTitle').innerText = event.detail.title;
                
                let content = `<img src="${event.detail.imageUrl}" class="img-fluid" alt="${event.detail.title}">`;
                
                // Add transfer details if available
                if (event.detail.transferDetails) {
                    const details = event.detail.transferDetails;
                    content += `
                        <div class="mt-3 text-left">
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    ${details.date ? `<tr><th width="40%">Tanggal Transfer</th><td>${details.date}</td></tr>` : ''}
                                    ${details.bank ? `<tr><th>Bank Pengirim</th><td>${details.bank}</td></tr>` : ''}
                                    ${details.account_name ? `<tr><th>Nama Pengirim</th><td>${details.account_name}</td></tr>` : ''}
                                    ${details.notes ? `<tr><th>Catatan</th><td>${details.notes}</td></tr>` : ''}
                                </tbody>
                            </table>
                        </div>
                    `;
                }
                
                document.getElementById('modalContent').innerHTML = content;
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
        
        window.addEventListener('show-notification', (event) => {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
            
            Toast.fire({
                icon: event.detail.type,
                title: event.detail.message
            });
        });
    </script>
    @endpush
    @push('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
    <style>
        .border-dashed { border-style: dashed !important; }
        #admin-payment-drop-area { transition: all 0.3s ease; }
        #admin-payment-drop-area:hover { border-color: #007bff !important; background-color: #f8f9fa !important; }
        #admin-payment-drop-area.border-primary { border-color: #007bff !important; background-color: rgba(0,123,255,.05) !important; }
    </style>
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