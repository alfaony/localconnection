@extends('adminlte::page')

@section('content')
<div class="containe mt-3">
    <div class="col-md-12">
        @if(Session::get('store'))
        <div class="alert alert-success mt-3">Berhasil Mengubah Pengaturan Perusahaan</div>
        @endif
        
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
    <div class="card">
        <div class="card-body">
            <h1>Setting Perusahaan</h1>
            <form method="post" action="{{ route('setting-company.store') }}" enctype="multipart/form-data">
                @csrf
                <div id="accordion">
                    <div class="card">
                        <div class="card-header" id="headingWABlas">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseWABlas" aria-expanded="false" aria-controls="collapseWABlas">
                                    Wablas Credential
                                </button>
                            </h2>
                        </div>
                        <div id="collapseWABlas" class="collapse" aria-labelledby="headingWABlas" data-parent="#accordion">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="server_wablas">Negara Server WA Blas (Texas)</label>
                                            <input type="text" name="server_wablas" class="form-control" value="{{ old('server_wablas', isset($data['server_wablas']) ? $data['server_wablas'] : '') }}">
                                            @error('server_wablas')
                                            <span class="text-danger text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="token_wablas">Token WA Blas</label>
                                            <input type="text" name="token_wablas" class="form-control" value="{{ old('token_wablas', isset($data['token_wablas']) ? $data['token_wablas'] : '') }}">
                                            @error('token_wablas')
                                            <span class="text-danger text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="webhook_key_wablas">Secret Key WA Blas</label>
                                            <input type="text" name="webhook_key_wablas" class="form-control" value="{{ old('webhook_key_wablas', isset($data['webhook_key_wablas']) ? $data['webhook_key_wablas'] : '') }}">
                                            @error('webhook_key_wablas')
                                            <span class="text-danger text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header" id="judulPayment">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapsePayment" aria-expanded="false" aria-controls="collapsePayment">
                                    Setting Payment Internet Customer
                                </button>
                            </h2>
                        </div>

                        <div id="collapsePayment" class="collapse" aria-labelledby="judulPayment" data-parent="#accordion">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="public_key">Public Key</label>
                                    <input type="text" name="public_key" class="form-control" value="{{ old('public_key', $data['public_key'] ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label for="secret_key">Secret Key</label>
                                    <input type="text" name="secret_key" class="form-control" value="{{ old('secret_key', $data['secret_key'] ?? '') }}">
                                </div>

                                <div class="form-group">
                                    <label for="webhook_token">Webhook Token</label>
                                    <input type="text" name="webhook_token" class="form-control" value="{{ old('webhook_token', $data['webhook_token'] ?? '') }}">
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="xendit_pay_with_ppn" name="xendit_pay_with_ppn" value="1" {{ old('xendit_pay_with_ppn', $data['xendit_pay_with_ppn'] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="xendit_pay_with_ppn">
                                            <strong>Gateway Auto-Calculate PPN</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Enabled:</strong> Kirim net price (price_nett), gateway akan tambahkan PPN<br>
                                        <strong>Disabled:</strong> Kirim gross price (price) yang sudah termasuk PPN
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header" id="judulMidtrans">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseMidtrans" aria-expanded="false" aria-controls="collapseMidtrans">
                                    Midtrans SNAP Payment (Internet Customer)
                                </button>
                            </h2>
                        </div>

                        <div id="collapseMidtrans" class="collapse" aria-labelledby="judulMidtrans" data-parent="#accordion">
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> <strong>Info:</strong> Konfigurasi Midtrans SNAP untuk pembayaran pelanggan internet. Dapatkan credentials dari <a href="https://dashboard.midtrans.com" target="_blank">Midtrans Dashboard</a>.
                                </div>

                                <div class="form-group">
                                    <label for="server_key_midtrans">Server Key Midtrans</label>
                                    <input type="text" name="server_key_midtrans" class="form-control" value="{{ old('server_key_midtrans', $data['server_key_midtrans'] ?? '') }}" placeholder="SB-Mid-server-... atau Mid-server-...">
                                    <small class="form-text text-muted">Server Key dari Midtrans (Sandbox atau Production)</small>
                                    @error('server_key_midtrans')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="client_key_midtrans">Client Key Midtrans</label>
                                    <input type="text" name="client_key_midtrans" class="form-control" value="{{ old('client_key_midtrans', $data['client_key_midtrans'] ?? '') }}" placeholder="SB-Mid-client-... atau Mid-client-...">
                                    <small class="form-text text-muted">Client Key dari Midtrans (Sandbox atau Production)</small>
                                    @error('client_key_midtrans')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="environment_midtrans">Environment</label>
                                    <select name="environment_midtrans" class="form-control">
                                        <option value="sandbox" {{ old('environment_midtrans', $data['environment_midtrans'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                                        <option value="production" {{ old('environment_midtrans', $data['environment_midtrans'] ?? 'sandbox') == 'production' ? 'selected' : '' }}>Production (Live)</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <strong>Sandbox:</strong> Untuk testing<br>
                                        <strong>Production:</strong> Untuk transaksi live
                                    </small>
                                    @error('environment_midtrans')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> <strong>Webhook URL:</strong> <code>{{ url('/midtrans/notification') }}</code><br>
                                    <small>Pastikan URL ini terdaftar di Midtrans Dashboard → Settings → Configuration → Notification URL</small>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="pay_with_ppn_midtrans" name="midtrans_pay_with_ppn" value="1" {{ old('midtrans_pay_with_ppn', $data['midtrans_pay_with_ppn'] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="pay_with_ppn_midtrans">
                                            <strong>Gateway Auto-Calculate PPN</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Enabled:</strong> Kirim net price (price_nett), Midtrans akan tambahkan PPN<br>
                                        <strong>Disabled:</strong> Kirim gross price (price) yang sudah termasuk PPN
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header" id="headingRekening">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseRekening" aria-expanded="false" aria-controls="collapseRekening">
                                    Rekening
                                </button>
                            </h2>
                        </div>

                        <div id="collapseRekening" class="collapse" aria-labelledby="headingRekening" data-parent="#accordion">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="rekening_number">Nomor Rekening</label>
                                    <input type="text" name="rekening_number" class="form-control" value="{{ old('rekening_number', isset($data['rekening_number']) ? $data['rekening_number'] : '') }}">
                                    @error('rekening_number')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
    
                                <div class="form-group">
                                    <label for="atas_nama">Nama Pemilik Rekening (opsional)</label>
                                    <input type="text" name="atas_nama" class="form-control" placeholder="opsional, kosongkan jika nama atas nama sama dengan nama perusahaan" value="{{ old('atas_nama', isset($data['atas_nama']) ? $data['atas_nama'] : '') }}">
                                    @error('atas_nama')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
    
                                <div class="form-group">
                                    <label for="nama_bank">Nama Bank</label>
                                    <input type="text" name="nama_bank" class="form-control" value="{{ old('nama_bank', isset($data['nama_bank']) ? $data['nama_bank'] : '') }}">
                                    @error('nama_bank')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="cabang_bank">Cabang Bank</label>
                                    <input type="text" name="cabang_bank" class="form-control" value="{{ old('cabang_bank', isset($data['cabang_bank']) ? $data['cabang_bank'] : '') }}">
                                    @error('cabang_bank')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Internet Invoice Branding Card -->
                    <div class="card">
                        <div class="card-header" id="headingInvoice">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseInvoice" aria-expanded="false" aria-controls="collapseInvoice">
                                    Internet Invoice Branding
                                </button>
                            </h2>
                        </div>
                        <div id="collapseInvoice" class="collapse" aria-labelledby="headingInvoice" data-parent="#accordion">
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> <strong>Info:</strong> Pengaturan ini khusus untuk invoice pelanggan internet. Kosongkan field untuk menggun akan setting default perusahaan.
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="manual_payment_status" name="manual_payment_status" value="1" {{ old('manual_payment_status', $data['manual_payment_status'] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="manual_payment_status">
                                            <strong>Manual Payment Status</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> 
                                        <strong>Enabled:</strong> Manual Payment Status<br>
                                    </small>
                                </div>
                                <!-- Logo/Icon -->
                                <div class="form-group">
                                    <label for="internet_icon">Logo/Icon Invoice</label>
                                    @if(isset($data['internet_icon']) && $data['internet_icon'])
                                        <div class="mb-2">
                                            <img src="{{ s3_asset(true, 10, $data['internet_icon']) }}" style="max-width: 200px; max-height: 100px;" class="img-thumbnail">
                                        </div>
                                    @endif
                                    <input type="file" name="internet_icon" class="form-control-file" accept="image/*">
                                    <small class="form-text text-muted">Logo akan tampil di header invoice</small>
                                    @error('internet_icon')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Company Name -->
                                <div class="form-group">
                                    <label for="internet_company_name">Nama Perusahaan untuk Invoice</label>
                                    <input type="text" name="internet_company_name" class="form-control" value="{{ old('internet_company_name', $data['internet_company_name'] ?? '') }}" placeholder="Kosongkan untuk menggunakan nama perusahaan default">
                                    <small class="form-text text-muted">Nama perusahaan yang tertera di invoice</small>
                                    @error('internet_company_name')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Address -->
                                <div class="form-group">
                                    <label for="internet_company_address">Alamat untuk Invoice</label>
                                    <textarea name="internet_company_address" class="form-control" rows="2" placeholder="Alamat lengkap perusahaan">{{ old('internet_company_address', $data['internet_company_address'] ?? '') }}</textarea>
                                    <small class="form-text text-muted">Alamat lengkap yang tertera di invoice</small>
                                    @error('internet_company_address')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div class="form-group">
                                    <label for="internet_phone">Telepon untuk Invoice</label>
                                    <input type="text" name="internet_phone" class="form-control" value="{{ old('internet_phone', $data['internet_phone'] ?? '') }}" placeholder="Nomor telepon">
                                    <small class="form-text text-muted">Nomor telepon yang tertera di invoice</small>
                                    @error('internet_phone')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Footer Message -->
                                <div class="form-group">
                                    <label for="internet_footer_message">Pesan Footer Invoice</label>
                                    <textarea name="internet_footer_message" class="form-control" rows="3" placeholder="Terima kasih atas kepercayaan Anda...">{{ old('internet_footer_message', $data['internet_footer_message'] ?? '') }}</textarea>
                                    <small class="form-text text-muted">Pesan terima kasih atau catatan yang tertera di footer invoice</small>
                                    @error('internet_footer_message')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                @php
                                $varHint = 'Variabel: <code>@{{nama}}</code> Nama, <code>@{{kode}}</code> Kode, <code>@{{paket}}</code> Paket, <code>@{{jatuh_tempo}}</code> Jatuh Tempo, <code>@{{tagihan}}</code> Nominal, <code>@{{url}}</code> Link Portal, <code>@{{tutorial}}</code> Tutorial Bayar. <strong>Kosongkan = tidak kirim WA.</strong>';
                                @endphp

                                <div class="form-group">
                                    <label for="internet_remainder_billing">Pesan WA Saat Generate Tagihan Baru</label>
                                    <input class="thriveEditor form-control" id="description_remainder_billing" data-ids="remainder_billing" name="internet_remainder_billing" rows="3" placeholder="Pesan WA dikirim ke customer saat tagihan baru dibuat. Kosongkan jika tidak perlu kirim." value="{{ old('internet_remainder_billing', $data['internet_remainder_billing'] ?? null) }}"/>
                                    <small class="form-text text-muted">
                                        Dikirim otomatis saat billing baru digenerate (tiap siklus bulanan). Tambahan variabel: <code>@{{faktur}}</code> Nomor Faktur.
                                        {!! $varHint !!}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="internet_remainder_billing_3">Pesan Reminder H-3 (3 Hari Sebelum Jatuh Tempo)</label>
                                    <input class="thriveEditor form-control" id="description_remainder_billing_3" data-ids="remainder_billing_3" name="internet_remainder_billing_3" rows="3" placeholder="Kosongkan jika tidak perlu kirim." value="{{ old('internet_remainder_billing_3', $data['internet_remainder_billing_3'] ?? null) }}"/>
                                    <small class="form-text text-muted">{!! $varHint !!}</small>
                                </div>

                                <div class="form-group">
                                    <label for="internet_remainder_billing_1">Pesan Reminder H-1 (1 Hari Sebelum Jatuh Tempo)</label>
                                    <input class="thriveEditor form-control" id="description_remainder_billing_1" data-ids="remainder_billing_1" name="internet_remainder_billing_1" rows="3" placeholder="Kosongkan jika tidak perlu kirim." value="{{ old('internet_remainder_billing_1', $data['internet_remainder_billing_1'] ?? null) }}"/>
                                    <small class="form-text text-muted">{!! $varHint !!}</small>
                                </div>

                                <div class="form-group">
                                    <label for="internet_remainder_billing_0">Pesan Reminder H-0 (Hari Terakhir Jatuh Tempo)</label>
                                    <input class="thriveEditor form-control" id="description_remainder_billing_0" data-ids="remainder_billing_0" name="internet_remainder_billing_0" rows="3" placeholder="Kosongkan jika tidak perlu kirim." value="{{ old('internet_remainder_billing_0', $data['internet_remainder_billing_0'] ?? null) }}"/>
                                    <small class="form-text text-muted">{!! $varHint !!}</small>
                                </div>

                                <div class="form-group">
                                    <label for="internet_remainder_billing_isolir">Pesan Reminder Saat Customer Diisolir</label>
                                    <input class="thriveEditor form-control" id="description_remainder_billing_isolir" data-ids="remainder_billing_isolir" name="internet_remainder_billing_isolir" rows="3" placeholder="Pesan WA dikirim ke customer saat berhasil diisolir. Kosongkan jika tidak perlu kirim." value="{{ old('internet_remainder_billing_isolir', $data['internet_remainder_billing_isolir'] ?? null) }}"/>
                                    <small class="form-text text-muted">{!! $varHint !!}</small>
                                    @error('internet_remainder_billing_isolir')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="internet_message_success">Pesan Sukses Pembayaran (setelah isolir lunas)</label>
                                    <input class="thriveEditor form-control" id="description_message_success" data-ids="message_success" name="internet_message_success" rows="3" placeholder="Pesan WA dikirim ke customer setelah pembayaran dikonfirmasi. Kosongkan jika tidak perlu kirim." value="{{ old('internet_message_success', $data['internet_message_success'] ?? null) }}"/>
                                    <small class="form-text text-muted">
                                        Variabel: <code>@{{nama}}</code> Nama, <code>@{{kode}}</code> Kode, <code>@{{paket}}</code> Paket, <code>@{{tagihan}}</code> Jumlah Bayar, <code>@{{url}}</code> Link Portal.
                                        <strong>Kosongkan = tidak kirim WA.</strong>
                                    </small>
                                    @error('internet_message_success')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>
                    
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>

        </div>
    </div>
</div>

@endsection
@section('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://unpkg.com/gijgo@1.9.14/js/gijgo.min.js" type="text/javascript"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('status_punihsment_task_doing');
        const pointInput = document.getElementById('point_punishment_task_doing');

        function syncPunishmentPoint() {
            if (toggle.checked) {
                toggle.value=1;
                pointInput.readOnly = false;
            } else {
                console.log("tidak aktif");
                

                toggle.value=0;
                pointInput.readOnly = true;
                pointInput.value = 0; // tetap terkirim ke server sebagai 0
            }
        }

        toggle.addEventListener('change', syncPunishmentPoint);
        syncPunishmentPoint(); // jalankan saat pertama load
    });
</script>
<script>
    $(document).ready(function () 
    {
        $('.select2').select2({
            width: '100%',
        });

        $('.timepicker').timepicker({
            showInputs: false,
            showMeridian: false
        })

    });

    $(document).ready(function () 
    {

        let currency_usd = document.getElementById("currency_usd").value;
        if (currency_usd) 
        {
            document.getElementById("currency_usd_show").value = currency_usd;
            formatRupiahFormat(document.getElementById("currency_usd_show"),"currency_usd"); // Format default value
        }

    });
    function formatRupiahFormat(input, inputNonFormat) 
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
            input.value = '0';
            numStr = 0;
        } else {
            // Menghapus angka 0 di depan jika input diawali dengan 0
            rupiah = rupiah.replace(/^0+/, '');
            input.value = 'Rp. '+rupiah;
        }

        // Update 'salary' input with non-formatted number
        document.getElementById(inputNonFormat).value = parseInt(numStr);
    }
    
</script>
<script>
function formatRupiahFormat(field, fieldHidden) {
    let number = parseInt(field.value.replace(/[^,\d]/g, '').toString());
    let cleanNumber = isNaN(number) ? 0 : number;
    let formatted = cleanNumber.toLocaleString('id-ID');

    document.getElementById(fieldHidden).value = cleanNumber;
    field.value = formatted;
}
</script>

@stop
@section('css')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<link href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css" rel="stylesheet" type="text/css" />
@stop
