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
                        <div class="card-header" id="headingOne">
                        <h5 class="mb-0">
                            <button type="button" class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                Profile
                            </button>
                        </h5>
                        </div>

                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                            <div class="card-body">

                                <div class="form-group">
                                    <label for="name">Nama Perusahaan</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', isset($data['name']) ? $data['name'] : '') }}">
                                    @error('name')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="alamat">Alamat</label>
                                    <input type="text" name="address" class="form-control" value="{{ old('address', isset($data['address']) ? $data['address'] : '') }}">
                                    @error('address')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="no_npwp">No. NPWP</label>
                                    <input type="text" name="npwp_number" class="form-control" value="{{ old('npwp_number', isset($data['npwp_number']) ? $data['npwp_number'] : '') }}">
                                    @error('npwp_number')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="direktur">Direktur</label>
                                    <input type="text" name="director" class="form-control" value="{{ old('director', isset($data['director']) ? $data['director'] : '') }}">
                                    @error('director')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="mata_uang_dasar">Mata Uang Dasar</label>
                                    <input type="text" name="currency" class="form-control" value="{{ old('currency', isset($data['currency']) ? $data['currency']  : '') }}">
                                    @error('currency')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="nilai_tukar_1_usd">Nilai Tukar 1 USD</label>
                                    <input type="text" class="form-control"  id="currency_usd_show" oninput="formatRupiahFormat(this,'currency_usd')" />
                                    <input type="hidden" name="currency_usd" id="currency_usd" class="form-control" value="{{ old('currency_usd', isset($data['currency_usd']) ? $data['currency_usd'] : '') }}">
                                    @error('currency_usd')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="clock_in">Waktu Masuk Standar</label>
                                    <input type="time" name="clock_in" id="clock_in" class="form-control" value="{{ old('clock_in', $data['clock_in'] ?? '08:00') }}">
                                    @error('clock_in')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="closed_time">Waktu Tutup Sprinter</label>
                                    <input type="time" name="closed_time" id="closed_time" class="form-control" value="{{ old('closed_time', $data['closed_time'] ?? '14:00') }}">
                                    @error('closed_time')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="reward_point_conversion">Konversi Poin Hadiah (per 1 POIN)</label>
                                    <input type="text" class="form-control" id="reward_point_conversion_show" oninput="formatRupiahFormat(this,'reward_point_conversion')" value="{{ old('reward_point_conversion', $data['reward_point_conversion'] ?? '500') }}">
                                    <input type="hidden" name="reward_point_conversion" id="reward_point_conversion" class="form-control" value="{{ old('reward_point_conversion', $data['reward_point_conversion'] ?? '500') }}">
                                    @error('reward_point_conversion')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="late_point">Poin Terlambat</label>
                                    <input type="number" name="late_point" id="late_point" class="form-control" value="{{ old('late_point', $data['late_point'] ?? '-10') }}">
                                    @error('late_point')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="on_time_point">Poin Tepat Waktu</label>
                                    <input type="number" name="on_time_poin" id="on_time_poin" class="form-control" value="{{ old('on_time_poin', $data['on_time_poin'] ?? '0') }}">
                                    @error('on_time_point')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>


                                <div class="form-group">
                                    <label for="mata_uang_dasar">Template Perjanjian</label>
                                    <select name="template_perjanjian" class="form-control">
                                        @foreach($agreementTemplate as $index => $value)
                                        <option value="{{ $index }}" {{ $index == @$data['template_perjanjian'] ? 'selected' : '' }} >{{ $index }}</option>
                                        @endforeach
                                    </select>
                                    @error('template_perjanjian')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="affiliate_company">Affiliate Company</label>
                                    <input type="text" name="affiliate_company" class="form-control" value="{{ old('affiliate_company', isset($data['affiliate_company']) ? $data['affiliate_company'] : '') }}">
                                    @error('affiliate_company')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="file_nib">Upload File NIB</label>
                                    @if($data['nib_file']) 
                                        <div class="mb-2">
                                            <a href="{{ s3_asset(true,10,$data['nib_file']) }}"  class="btn btn-sm btn-primary"  download><i class="fa fa-file-pdf"></i> Download</a>
                                        </div>
                                    @endif
                                    <input type="file" name="nib_file" class="form-control-file" accept=".pdf" >
                                    @error('nib_file')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="file_akta">Upload File Akta</label>
                                    @if($data['acta_file']) 
                                        <div class="mb-2">
                                            <a href="{{ s3_asset(true,10,$data['acta_file']) }}"  class="btn btn-sm btn-primary" download><i class="fa fa-file-pdf"></i> Download</a>
                                        </div>
                                    @endif
                                    <input type="file" name="acta_file" class="form-control-file" accept=".pdf" >
                                    @error('acta_file')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="file_npwp">Upload File NPWP</label>
                                    @if($data['npwp_file']) 
                                        <div class="mb-2">
                                            <a href="{{ s3_asset(true,10,$data['npwp_file']) }}"  class="btn btn-sm btn-primary" download><i class="fa fa-file-pdf"></i> Download</a>
                                        </div>
                                    @endif
                                    <input type="file" name="npwp_file" class="form-control-file" accept=".pdf" >
                                    @error('npwp_file')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                        <h5 class="mb-0">
                            <button type="button" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                SMTP Email
                            </button>
                        </h5>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="name">Host</label>
                                    <input type="text" name="host" class="form-control" value="{{ old('host', isset($data['host']) ? $data['host'] : '') }}">
                                    @error('host')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
    
                                <div class="form-group">
                                    <label for="name">Port</label>
                                    <input type="text" name="port" class="form-control" value="{{ old('port', isset($data['port']) ? $data['port'] : '') }}">
                                    @error('port')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
    
                                <div class="form-group">
                                    <label for="name">Username</label>
                                    <input type="text" name="username" class="form-control" value="{{ old('username', isset($data['username']) ? $data['username'] : '') }}">
                                    @error('username')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
    
                                <div class="form-group">
                                    <label for="name">Password</label>
                                    <input type="text" name="password" class="form-control" value="{{ old('password', isset($data['password']) ? $data['password'] : '') }}">
                                    @error('password')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
    
                                <div class="form-group">
                                    <label for="name">Encryption</label>
                                    <input type="text" name="encryption" class="form-control" value="{{ old('encryption', isset($data['encryption']) ? $data['encryption'] : '') }}">
                                    @error('encryption')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="name">Sent Time</label>
                                    <input type="text" name="sent_time" class="form-control timepicker" value="{{ old('sent_time', isset($data['sent_time']) ? $data['sent_time'] : '') }}">
                                    @error('sent_time')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingThree">
                            <h5 class="mb-0">
                                <button type="button" class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Header dan Footer Kop Surat
                                </button>
                            </h5>
                        </div>
                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="header">Upload Header</label>
                                    @if(isset($data['header']) && file_exists(public_path('storage/' . $data['header']))) 
                                        <div class="mb-2">
                                            <a href="{{ s3_asset(true,10,$data['header']) }}"  class="btn btn-sm btn-primary" download><i class="fa fa-download"></i> Header</a>
                                        </div>
                                    @endif
                                    <input type="file" name="header" class="form-control-file" accept="image/*">
                                    @error('header')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="footer">Upload Footer</label>
                                    @if(isset($data['footer']) && file_exists(public_path('storage/' . $data['footer']))) 
                                        <div class="mb-2">
                                            <a href="{{ s3_asset(true,10,$data['footer']) }}"  class="btn btn-sm btn-primary" download><i class="fa fa-download"></i> Footer</a>
                                        </div>
                                    @endif
                                    <input type="file" name="footer" class="form-control-file" accept="image/*">
                                    @error('footer')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingXero">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseXero" aria-expanded="true" aria-controls="collapseXero">
                                    Xero Credential
                                </button>
                            </h2>
                        </div>

                        <div id="collapseXero" class="collapse" aria-labelledby="headingXero" data-parent="#accordion">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="client_id">Client ID</label>
                                    <input type="text" name="client_id" class="form-control" value="{{ old('client_id', isset($data['client_id']) ? $data['client_id'] : '') }}">
                                    @error('client_id')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
    
                                <div class="form-group">
                                    <label for="client_secret">Client Secret</label>
                                    <input type="text" name="client_secret" class="form-control" value="{{ old('client_secret', isset($data['client_secret']) ? $data['client_secret'] : '') }}">
                                    @error('client_secret')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
    
                                <div class="form-group">
                                    <label for="webhook_key">Webhook Key</label>
                                    <input type="text" name="webhook_key" class="form-control" value="{{ old('webhook_key', isset($data['webhook_key']) ? $data['webhook_key'] : '') }}">
                                    @error('webhook_key')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
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
                    
                    <div class="card">
                        <div class="card-header" id="headingTaskDoing">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseTaskDoing" aria-expanded="false" aria-controls="collapseTaskDoing">
                                    Pengaturan Sanksi Task DOING
                                </button>
                            </h2>
                        </div>
                        <div id="collapseTaskDoing" class="collapse" aria-labelledby="headingTaskDoing" data-parent="#accordion">
                            <div class="card-body">

                                <div class="form-group">
                                            <label for="status_punihsment_task_doing">Aktifkan Sanksi Otomatis Task DOING</label><br>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input" id="status_punihsment_task_doing" name="status_punihsment_task_doing"
                                                {{ old('status_punihsment_task_doing', $data['status_punihsment_task_doing'] ?? 0) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="status_punihsment_task_doing">Aktifkan</label>
                                            </div>
                                            @error('status_punihsment_task_doing')
                                                <span class="text-danger text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="point_punishment_task_doing">Poin Sanksi Task DOING</label>
                                            <input type="number" name="point_punishment_task_doing" id="point_punishment_task_doing" class="form-control"
                                                value="{{ old('point_punishment_task_doing', $data['point_punishment_task_doing'] ?? '') }}">
                                            @error('point_punishment_task_doing')
                                                <span class="text-danger text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>

                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" id="headingPunishment">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapsePunishment" aria-expanded="false" aria-controls="collapsePunishment">
                                        Pengaturan Sanksi Task TODO & Weekly Report
                                    </button>
                                </h2>
                            </div>

                            <div id="collapsePunishment" class="collapse" aria-labelledby="headingPunishment" data-parent="#accordion">
                                <div class="card-body">

                                    <div class="form-group">
                                        <label for="point_punishment_task_todo">Poin Sanksi Task TODO</label>
                                        <input type="number" name="point_punishment_task_todo" class="form-control" value="{{ old('point_punishment_task_todo', $data['point_punishment_task_todo'] ?? '') }}">
                                        @error('point_punishment_task_todo')
                                            <span class="text-danger text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="point_punishment_weekly_report">Poin Sanksi Weekly Report</label>
                                        <input type="number" name="point_punishment_weekly_report" class="form-control" value="{{ old('point_punishment_weekly_report', $data['point_punishment_weekly_report'] ?? '') }}">
                                        @error('point_punishment_weekly_report')
                                            <span class="text-danger text-sm">{{ $message }}</span>
                                        @enderror
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

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
                        <div class="card-header" id="headingNewCard">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseNewCard" aria-expanded="false" aria-controls="collapseNewCard">
                                    Google Credential
                                </button>
                            </h2>
                        </div>
                        <div id="collapseNewCard" class="collapse" aria-labelledby="headingNewCard" data-parent="#accordion">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="google_client_id">Google Client ID</label>
                                    <input type="text" name="google_client_id" class="form-control" value="{{ old('google_client_id', isset($data['google_client_id']) ? $data['google_client_id'] : '') }}">
                                    @error('google_client_id')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror

                                    <label for="google_client_secret">Google Client Secret</label>
                                    <input type="text" name="google_client_secret" class="form-control" value="{{ old('google_client_secret', isset($data['google_client_secret']) ? $data['google_client_secret'] : '') }}">
                                    @error('google_client_secret')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header" id="headingWFO">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseWFO" aria-expanded="false" aria-controls="collapseWFO">
                                    Punishment WFO & WFH RULES
                                </button>
                            </h2>
                        </div>
                        <div id="collapseWFO" class="collapse" aria-labelledby="headingWFO" data-parent="#accordion">
                            <div class="card-body">
                                <div class="card">
                                    <div class="card-header">
                                        WFH Rules
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="punishment_point_wfh">Poin Sanksi WFH</label>
                                            <input type="number" name="punishment_point_wfh" class="form-control" value="{{ old('punishment_point_wfh', $data['punishment_point_wfh'] ?? 10) }}">
                                            @error('punishment_point_wfh')
                                            <span class="text-danger text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="range_start_date">Rentang Tanggal Mulai</label>
                                            <input type="number" name="range_start_date" class="form-control" value="{{ old('range_start_date', $data['range_start_date'] ?? 21) }}">
                                            @error('range_start_date')
                                            <span class="text-danger text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
        
                                        <div class="form-group">
                                            <label for="range_end_date">Rentang Tanggal Akhir</label>
                                            <input type="number" name="range_end_date" class="form-control" value="{{ old('range_end_date', $data['range_end_date'] ?? 20) }}">
                                            @error('range_end_date')
                                            <span class="text-danger text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
        
                                        <div class="form-group">
                                            <label for="presence_checkin">Presensi Check-in</label>
                                            <input type="number" name="presence_checkin" class="form-control" value="{{ old('presence_checkin', $data['presence_checkin'] ?? 70) }}">
                                            @error('presence_checkin')
                                            <span class="text-danger text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
        
                                        <div class="form-group">
                                            <label for="overdue_task">Tugas Keterlambatan</label>
                                            <input type="number" name="overdue_task" class="form-control" value="{{ old('overdue_task', $data['overdue_task'] ?? 50) }}">
                                            @error('overdue_task')
                                            <span class="text-danger text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                         WFO Rules
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="punishment_point_wfo">Poin Sanksi WFO</label>
                                            <input type="number" name="punishment_point_wfo" class="form-control" value="{{ old('punishment_point_wfo', $data['punishment_point_wfo'] ?? 0) }}">
                                            @error('punishment_point_wfo')
                                            <span class="text-danger text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="entry_time">Waktu Masuk</label>
                                            <input type="time" name="entry_time" class="form-control timepicker" value="{{ old('entry_time', $data['entry_time'] ?? '08:00') }}">
                                            @error('entry_time')
                                            <span class="text-danger text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
        
                                        <div class="form-group">
                                            <label for="tolerance">Basis Toleransi (menit)</label>
                                            <input type="number" name="tolerance" class="form-control" value="{{ old('tolerance', $data['tolerance'] ?? 20) }}">
                                            @error('tolerance')
                                            <span class="text-danger text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
        
                                        <div class="form-group">
                                            <label for="checkin_onday">Standar Check-in Setiap Hari</label>
                                            <input type="number" name="checkin_onday" class="form-control" value="{{ old('checkin_onday', $data['checkin_onday'] ?? 4) }}">
                                            @error('checkin_onday')
                                            <span class="text-danger text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header" id="judulStore">
                            <h2 class="mb-0">
                                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseStore" aria-expanded="false" aria-controls="collapseStore">
                                    Setting Toko
                                </button>
                            </h2>
                        </div>

                        <div id="collapseStore" class="collapse" aria-labelledby="judulStore" data-parent="#accordion">
                            <div class="card-body">
                                @error('default_tax')
                                <div class="alert alert-danger mt-3">
                                    <i class="fas fa-exclamation-circle me-2"></i> {{ $message }}
                                </div>
                                @enderror

                                <div class="form-group">
                                    <label for="default_tax">Default Pajak</label>
                                    <input type="number" min="0" name="default_tax" class="form-control" value="{{ old('default_tax', $data['default_tax'] ?? null) }}">
                                </div>

                                @error('header_store_image')
                                <div class="alert alert-danger mt-3">
                                    <i class="fas fa-exclamation-circle me-2"></i> {{ $message }}
                                </div>
                                @enderror

                                <div class="form-group">
                                    <label for="header_store_image">Header Store Image</label>
                                    @if($data['header_store_image']) 
                                        <div class="mb-2">
                                            <a href="{{ s3_asset(true,10,$data['header_store_image']) }}"  class="btn btn-sm btn-primary"  download><i class="fa fa-file-pdf"></i> Download</a>
                                        </div>
                                    @endif
                                    <input type="file" name="header_store_image" class="form-control" accept="image/*">
                                </div>

                                @error('footer_store_message')
                                <div class="alert alert-danger mt-3">
                                    <i class="fas fa-exclamation-circle me-2"></i> {{ $message }}
                                </div>
                                @enderror

                                <div class="form-group">
                                    <label for="footer_store_message">Footer Store Message</label>
                                    <input class="thriveEditor form-control" id="description_payment_term_english" data-ids="payment_term_english" name="footer_store_message" rows="3" placeholder="yang akan dicetak di perjanjian" value="{{ old('footer_store_message', $data['footer_store_message'] ?? null) }}"/>
                                </div>

                                @error('store_name')
                                <div class="alert alert-danger mt-3">
                                    <i class="fas fa-exclamation-circle me-2"></i> {{ $message }}
                                </div>
                                @enderror

                                <div class="form-group">
                                    <label for="store_name">Nama Store</label>
                                    <input type="text" name="store_name" class="form-control" value="{{ old('store_name', $data['store_name'] ?? null) }}">
                                </div>

                                @error('store_address')
                                <div class="alert alert-danger mt-3">
                                    <i class="fas fa-exclamation-circle me-2"></i> {{ $message }}
                                </div>
                                @enderror

                                <div class="form-group">
                                    <label for="store_address">Alamat Store</label>
                                    <input type="text" name="store_address" class="form-control" value="{{ old('store_address', $data['store_address'] ?? null) }}">
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
