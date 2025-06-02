@foreach($steps as $index => $step)
    <div class="workflow-step {{ $step['status'] }} animated fadeIn">
        <div class="step-icon shadow-sm bg-{{ $step['status'] == 'completed' ? 'success' : ($step['status'] == 'active' ? 'warning' : 'secondary') }}">
            <i class="{{ $step['icon'] }} text-white"></i>
        </div>
        <div class="step-content shadow-sm ml-3">
            <div class="step-header border-bottom pb-2 d-flex align-items-center p-2">
                <div class="flex-grow-1">
                    <h5 class="mb-0 text-primary">
                        <i class="{{ $step['icon'] }} mr-2"></i>{{ $index }}
                    </h5>
                    @if(isset($step['date']))
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> 
                        {{ \Carbon\Carbon::parse($step['date'])->diffForHumans() }}
                    </small>
                    @endif
                </div>
                <span class="badge badge-{{ 
                    $step['status'] == 'completed' ? 'success' : 
                    ($step['status'] == 'active' ? 'warning' : 'secondary') 
                }} ml-2">
                    {{ ucfirst($step['status']) }}
                </span>
            </div>
            
            <div class="step-body pt-3 p-2 rounded">
                @if($step['status'] == 'active')
                <div class="step-actions mb-3">
                    @switch($index)
                        @case('Penunjukan PIC')
                            <button class="btn btn-sm btn-warning">
                                <i class="fas fa-user-check"></i> Konfirmasi PIC
                            </button>
                        @break

                        @case('Pencarian Vendor')
                            <div class="alert alert-info py-2">
                                <i class="fas fa-bullhorn"></i> 
                                Terkirim ke {{ $step['data']['broadcast_status']['total_vendors'] }} vendor
                                <span class="badge badge-success ml-2">
                                    {{ $step['data']['broadcast_status']['responses'] }} respons
                                </span>
                            </div>
                        @break
                    @endswitch
                </div>
                @endif
                
                <div class="step-details">
                    <div class="mb-2 text-secondary">{{ $step['description'] }}</div>
                    
                    @isset($step['data'])
                    <div class="detail-card p-3 border rounded">
                        @switch($index)
                            @case('Pengajuan Barang')
                                <div class="detail-item">
                                    <i class="fas fa-user-tag mr-2"></i>
                                    <strong>Pengaju:</strong> {{ $step['data']['requester'] }}
                                </div>
                                <div class="detail-item">
                                    <div class="ql-editor" style="white-space:unset; padding:0px 0px;">
                                        {!! $step['data']['notes'] !!}
                                    </div>
                                </div>
                            @break

                            @case('Penunjukan PIC')
                                <div class="media align-items-center">
                                    <div class="media-body">
                                        <h6 class="mt-0 mb-1">{{ $step['data']['assigned_pic'] }}</h6>
                                    </div>
                                </div>
                            @break

                            @case('Pencarian Toko')
                                <div class="vendor-responses">
                                    @foreach($step['data']['vendors'] as $vendor)
                                    <div class="response-item alert {{ $vendor['response'] == 'positive' ? 'alert-success' : ($vendor['response'] == 'negative' ? 'alert-danger' : 'alert-light') }}">
                                        <div class="d-flex justify-content-between align-items-center">
                                             <div class="d-flex align-items-center">
                                                <div class="vendor-avatar mr-3">
                                                    @if($itemRequest->is_open)
                                                    <button class="btn btn-sm btn-success btn-select-vendor"
                                                        data-vendor-id="{{ $vendor['id'] }}"
                                                        data-vendor-name="{{ $vendor['name'] }}"
                                                        data-vendor-location="{{ $vendor['location'] }}"
                                                        data-vendor-phone="{{ $vendor['phone_number'] }}">
                                                        Pilih <i class="fas fa-check"></i>
                                                    </button>
                                                    @else
                                                    <span class="badge badge-danger">
                                                        Closed
                                                    </span>

                                                    @endif
                                                </div>
                                                <div class="flex-grow-1">
                                                    <i class="fas fa-user-tie mr-2"></i>
                                                    <strong>{{ $vendor['name'] }}</strong>
                                                    <div class="small">
                                                        <i class="fas fa-map-marker-alt mr-2"></i> {{ $vendor['location'] }}
                                                        <i class="fas fa-phone-alt mr-2 ml-3"></i> {{ $vendor['phone_number'] }}
                                                    </div>
                                                    <div class="small">{{ $vendor['message'] }}</div>
                                                    @if(isset($vendor['response']))
                                                    <span class="badge badge-pill {{ $vendor['response'] == 'positive' ? 'badge-success' : 'badge-danger' }}">
                                                        {{ $vendor['response'] == 'positive' ? 'Ya' : 'Tidak' }}
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <small class="text-muted">
                                                    {{ $vendor['response_time'] }}
                                                </small>
                                                @if($vendor['response'] == 'positive')
                                                <button class="btn btn-sm btn-success ml-2">
                                                    Pilih <i class="fas fa-check"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @break

                            @case('Konfirmasi Pembayaran')
                                @if($itemRequest->purchase)
                                @foreach($itemRequest->purchase as $purchase)
                                <div class="p-3 border rounded mt-2">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="payment-info">
                                                <h6 class="mb-1">{{ $purchase->productSupplier->store_name }}</h6>
                                                <div class="mb-2">
                                                    <i class="fas fa-phone-alt mr-2"></i>
                                                    No. Telepon: {{ $purchase->productSupplier->phone_number }}
                                                </div>
                                                <div class="mb-2">
                                                    <i class="fas fa-map-marker-alt mr-2"></i>
                                                    Alamat Toko: {{ $purchase->productSupplier->location }}
                                                </div>
                                                <div class="mb-2">
                                                    <i class="fas fa-university mr-2"></i>
                                                    Metode Pembayaran: {{ $purchase->payment_method }}
                                                </div>
                                                <div class="mb-2">
                                                    <i class="fas fa-clock mr-2"></i>
                                                    Tenggat Pembayaran: {{ $purchase->payment_term_date }}
                                                </div>
                                                <div class="mb-2">
                                                    <i class="fas fa-money-bill-alt mr-2"></i>
                                                    Harga: Rp {{ number_format($purchase->actual_price, 0, ',', '.') }}
                                                </div>
                                                <div class="mb-2">
                                                    <i class="fas fa-receipt mr-2"></i>
                                                    No. Rekening: {{ $purchase->rekening_number}}
                                                </div>
                                                @if(!$purchase->payment)
                                                <button class="btn btn-sm btn-success btn-upload-transfer"
                                                    data-toggle="modal"
                                                    data-target="#uploadTransferModal"
                                                    data-id="{{ $purchase->id }}">
                                                    <i class="fas fa-upload mr-2"></i>Upload Bukti Transfer
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                        @if($purchase->payment)
                                        <div class="col-md-6">
                                            <img src="{{ Storage::url($purchase->payment->proof_image) }}" class="img-thumbnail" max-width="70%" alt="Bukti Transfer">
                                            <a href="{{ Storage::url($purchase->payment->proof_image) }}" download class="btn btn-sm btn-outline-primary ml-2">
                                                <i class="fas fa-download"></i> Unduh
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                                @endif
                            @break

                            @case('Upload Resi Pengiriman')
                                <div class="shipping-info">
                                    @if($step['data']['tracking_info'])
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle"></i>
                                            Resi sudah diupload: 
                                            <strong>{{ $step['data']['tracking_info'] }}</strong>
                                            <a href="#" class="btn btn-sm btn-outline-primary ml-2">
                                                <i class="fas fa-eye"></i> Lihat Resi
                                            </a>
                                        </div>
                                    @else
                                        @if(!$itemRequest->delivery)
                                        @if($itemRequest->status == 'WAITING_DELIVERY_CONFIRMATION')
                                        <form id="form-upload-delivery" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" id="item_purchase_id" name="item_purchase_id" value="{{ $itemRequest->id }}">

                                            <div class="form-group">
                                                <label>Pilih Ekspedisi</label>
                                                <input type="text" class="form-control" name="shipping_method" placeholder="Input nama ekspedisi" required>
                                            </div>

                                            <div class="form-group">
                                                <label>No. Resi</label>
                                                <input type="text" name="resi_number" class="form-control" required>
                                            </div>

                                            <div class="form-group">
                                                <label>Foto Air Way Bill (PDF/JPG/PNG)</label>
                                                <input type="file" name="airwillbill_photo" class="form-control-file" accept="image/*,application/pdf" required>
                                            </div>


                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-paper-plane"></i> Submit
                                            </button>
                                        </form>
                                        @endif
                                        @else
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <div class="mb-2">
                                                    <i class="fas fa-user mr-2"></i>
                                                    <strong>Sprinter: </strong>
                                                    {{ $itemRequest->delivery->sprinter->name }}
                                                </div>
                                                <div class="mb-2">
                                                    <i class="fas fa-barcode mr-2"></i>
                                                    <strong>Resi Number: </strong>
                                                    {{ $itemRequest->delivery->resi_number }}
                                                </div>
                                                <div class="mb-2">
                                                    <i class="fas fa-truck mr-2"></i>
                                                    <strong>Shipping Method: </strong>
                                                    {{ $itemRequest->delivery->shipping_method }}
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="mb-2">
                                                    <i class="fas fa-file-pdf mr-2"></i>
                                                    <strong>Air Way Bill Photo: </strong>
                                                    <a href="{{ Storage::url($itemRequest->delivery->airwillbill_photo) }}" target="_blank">
                                                        Lihat
                                                    </a>
                                                    <img src="{{ Storage::url($itemRequest->delivery->airwillbill_photo) }}" class="img-thumbnail mt-2" max-width="50%" alt="Air Way Bill Photo">
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    @endif
                                </div>
                            @break

                            @case('Pengiriman Barang')
                                <div class="delivery-status">
                                    @if($itemRequest->delivery && $itemRequest->delivery->delivery_photo)
                                        <div class="row alert">
                                            <div class="col-md-6">
                                                <i class="fas fa-check mr-2"></i>
                                                Barang telah dikirim via {{ $itemRequest->delivery->shipping_method }}
                                                <div class="mt-2">
                                                    <i class="fas fa-barcode"></i> No. Resi: 
                                                    <strong>{{ $itemRequest->delivery->resi_number  }}</strong>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                @if($itemRequest->delivery->delivery_photo)
                                                <a href="{{ Storage::url($itemRequest->delivery->delivery_photo) }}" 
                                                class="btn btn-sm btn-success"
                                                target="_blank">
                                                    <i class="fas fa-file-pdf"></i> Bukti Pengiriman
                                                </a>
                                                <img src="{{ Storage::url($itemRequest->delivery->delivery_photo) }}" class="img-thumbnail mt-2" max-width="50%" alt="Air Way Bill Photo">
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <form id="form-upload-delivery" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" id="item_purchase_id" name="item_purchase_id" value="{{ $itemRequest->id }}">

                                                <div class="form-group">
                                                    <label>Foto Bukti Pengiriman (PDF/JPG/PNG)</label>
                                                    <input type="file" name="delivery_photo" class="form-control-file" environment="capture" accept="image/*,application/pdf" required>
                                                </div>

                                                <button class="btn btn-primary" type="submit">
                                                    <i class="fas fa-paper-plane"></i> Submit
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @break
                        @endswitch
                    </div>
                    @endisset
                </div>
            </div>
        </div>
    </div>
@endforeach