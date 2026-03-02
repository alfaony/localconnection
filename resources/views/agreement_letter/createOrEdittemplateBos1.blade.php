@extends('adminlte::page')

@section('content')
<div class="container mt-3">
    <div class="card">
        <div class="card-body">
            @include('components.alert')
            @if(@$agreementLetter)
            <form method="post" action="{{ route('agreement-letter.update',$agreementLetter) }}">
                @method('put')
            @else
            <form method="post" action="{{ route('agreement-letter.store') }}">
            @endif
                @csrf
                <div class="form-group row">
                    <div class="col-md-6">
                        <h2>Surat Perjanjian</h2>
                        <div class="mt-5">No Surat Perjanjain: {{ $nomorAgreementLetter ?? '' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <label for="date" class="col-sm-8 col-form-label text-right">Tanggal:</label>
                            <div class="col-sm-4">
                                <input type="date" name="date" class="form-control" id="date" value="{{ old('date') ?? @$agreementLetter->date ?? \Carbon\Carbon::now()->format('Y-m-d')}}" required>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <label class="col-sm-8 col-form-label text-right">Finance:</label>
                            <div class="col-sm-4">
                                <span class="form-control-plaintext">{{ $userCreate ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
        
                <div class="form-group row">
                    <label for="template_agreement_show" class="col-sm-2 col-form-label">Pilih Template
                        Perjanjian:</label>
                    <div class="col-sm-5">
                        <select class="form-control select2" name="template_agreement_id"
                            id="template_agreement_id">
                            @foreach($selectTemplate as $template)
                            <option value="{{ $template->id }}" 
                                data-template="{{ $template->template_agreement }}" 
                                {{ (@$agreementLetter->template_agreement_id ? 
                                @$agreementLetter->template_agreement_id == $template->id : 
                                $template->is_default) ? 'selected' : '' }}>
                                {{ $template->template_agreement_show }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="quote" class="col-sm-2 col-form-label">Pilih No. Quote:</label>
                    <div class="col-sm-5">
                        <input type="hidden" name="quote_id" value="{{ old('quote') ?? @$agreementLetter->quote_id }}">
                        <select class="form-control select2" name="quote" id="quote" required>
                           
                        </select>
                    </div>
                </div>
        
                <div class="form-group row">
                    <label for="customer" class="col-sm-2 col-form-label">Customer:</label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" id="customer" value="" placeholder="Pilih Quote" readonly>
                    </div>
                </div>
                <!-- Form tambahan untuk template templateBos1_1 -->
                <div class="templateBos1_1-field" style="display:none;">
                    <hr class="my-4">
                    <h4>Detail Perjanjian</h4>

                    <!-- Data Pihak Pertama -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Data Pihak Pertama</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama Perusahaan:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_first_party_company_name" value="{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : (isset($company['name']) ? e($company['name']) : '')  }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Alamat:</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="custom_first_party_address" rows="2">{{ isset($agreementLetter->custom_fields['custom_first_party_address']) ? e($agreementLetter->custom_fields['custom_first_party_address']) : (isset($company['address']) ? e($company['address']) : '')  }}</textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Diwakili Oleh:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_first_party_represented_by" value="{{ isset($agreementLetter->custom_fields['custom_first_party_represented_by']) ? e($agreementLetter->custom_fields['custom_first_party_represented_by']) : (isset($company['director']) ? e($company['director']) : '') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Jabatan:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_first_party_position" value="{{ isset($agreementLetter->custom_fields['custom_first_party_position']) ? e($agreementLetter->custom_fields['custom_first_party_position']) : 'Director' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Bidang Usaha:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_first_party_business_field" value="{{ isset($agreementLetter->custom_fields['custom_first_party_business_field']) ? e($agreementLetter->custom_fields['custom_first_party_business_field']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Pihak Kedua -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Data Pihak Kedua</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tipe:</label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="custom_second_party_type" id="pihak_kedua_tipe">
                                        <option value="company" {{ (isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'company') ? 'selected' : '' }}>Perusahaan</option>
                                        <option value="individual" {{ (isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'individual') ? 'selected' : '' }}>Perorangan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_second_party_name" value="{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Alamat:</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="custom_second_party_address" rows="2">{{ isset($agreementLetter->custom_fields['custom_second_party_address']) ? e($agreementLetter->custom_fields['custom_second_party_address']) : '' }}</textarea>
                                </div>
                            </div>
                            <div class="form-group row perusahaan-field">
                                <label class="col-sm-3 col-form-label">Diwakili Oleh:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_second_party_represented_by" value="{{ isset($agreementLetter->custom_fields['custom_second_party_represented_by']) ? e($agreementLetter->custom_fields['custom_second_party_represented_by']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row perusahaan-field">
                                <label class="col-sm-3 col-form-label">Jabatan:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_second_party_position" value="{{ isset($agreementLetter->custom_fields['custom_second_party_position']) ? e($agreementLetter->custom_fields['custom_second_party_position']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row perorangan-field" style="display:none;">
                                <label class="col-sm-3 col-form-label">Nomor Identitas:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_second_party_identity_number" value="{{ isset($agreementLetter->custom_fields['custom_second_party_identity_number']) ? e($agreementLetter->custom_fields['custom_second_party_identity_number']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Bidang Usaha:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_second_party_business_field" value="{{ isset($agreementLetter->custom_fields['custom_second_party_business_field']) ? e($agreementLetter->custom_fields['custom_second_party_business_field']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Sewa -->
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Detail Sewa dan Jadwal</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Hari Live:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_schedule_days" placeholder="Senin - Jumat" value="{{ isset($agreementLetter->custom_fields['custom_schedule_days']) ? e($agreementLetter->custom_fields['custom_schedule_days']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Jam Mulai:</label>
                                <div class="col-sm-9">
                                    <input type="time" class="form-control" name="custom_schedule_start_time" value="{{ isset($agreementLetter->custom_fields['custom_schedule_start_time']) ? e($agreementLetter->custom_fields['custom_schedule_start_time']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Jam Selesai:</label>
                                <div class="col-sm-9">
                                    <input type="time" class="form-control" name="custom_schedule_end_time" value="{{ isset($agreementLetter->custom_fields['custom_schedule_end_time']) ? e($agreementLetter->custom_fields['custom_schedule_end_time']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Biaya Sewa (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_rental_fee_amount" value="{{ isset($agreementLetter->custom_fields['custom_rental_fee_amount']) ? e($agreementLetter->custom_fields['custom_rental_fee_amount']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Biaya Sewa (Terbilang):</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_rental_fee_words" placeholder="tiga juta Rupiah" value="{{ isset($agreementLetter->custom_fields['custom_rental_fee_words']) ? e($agreementLetter->custom_fields['custom_rental_fee_words']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Persentase Komisi (%):</label>
                                <div class="col-sm-9">
                                    <input type="number" step="0.01" class="form-control" name="custom_commission_percentage" value="{{ isset($agreementLetter->custom_fields['custom_commission_percentage']) ? e($agreementLetter->custom_fields['custom_commission_percentage']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Biaya Per Produk (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_fee_per_product" value="{{ isset($agreementLetter->custom_fields['custom_fee_per_product']) ? e($agreementLetter->custom_fields['custom_fee_per_product']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tanggal Pembayaran Komisi:</label>
                                <div class="col-sm-9">
                                    <input type="number" min="1" max="31" class="form-control" name="custom_commission_payment_date" placeholder="1-31" value="{{ isset($agreementLetter->custom_fields['custom_commission_payment_date']) ? e($agreementLetter->custom_fields['custom_commission_payment_date']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Periode Perjanjian -->
                    <div class="card mb-3">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0">Periode Perjanjian</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tanggal Mulai:</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" name="custom_agreement_start_date" value="{{ isset($agreementLetter->custom_fields['custom_agreement_start_date']) ? e($agreementLetter->custom_fields['custom_agreement_start_date']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tanggal Selesai:</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" name="custom_agreement_end_date" value="{{ isset($agreementLetter->custom_fields['custom_agreement_end_date']) ? e($agreementLetter->custom_fields['custom_agreement_end_date']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Rekening -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Data Rekening Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama Pemilik Rekening:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_account_holder_name" value="{{ isset($agreementLetter->custom_fields['custom_account_holder_name']) ? e($agreementLetter->custom_fields['custom_account_holder_name']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama Bank:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_bank_name" value="{{ isset($agreementLetter->custom_fields['custom_bank_name']) ? e($agreementLetter->custom_fields['custom_bank_name']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Kantor Cabang:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_branch_office" value="{{ isset($agreementLetter->custom_fields['custom_branch_office']) ? e($agreementLetter->custom_fields['custom_branch_office']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nomor Rekening:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_account_number" value="{{ isset($agreementLetter->custom_fields['custom_account_number']) ? e($agreementLetter->custom_fields['custom_account_number']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Biaya Tambahan -->
                    <div class="card mb-3">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Biaya Tambahan (Opsional)</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Biaya Handling per Produk (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_handling_fee_per_product" value="{{ isset($agreementLetter->custom_fields['custom_handling_fee_per_product']) ? e($agreementLetter->custom_fields['custom_handling_fee_per_product']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Biaya Gudang (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_warehouse_fee" value="{{ isset($agreementLetter->custom_fields['custom_warehouse_fee']) ? e($agreementLetter->custom_fields['custom_warehouse_fee']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Ukuran Gudang (m³):</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_warehouse_size" value="{{ isset($agreementLetter->custom_fields['custom_warehouse_size']) ? e($agreementLetter->custom_fields['custom_warehouse_size']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Biaya Overtime per Jam (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_overtime_fee_per_hour" value="{{ isset($agreementLetter->custom_fields['custom_overtime_fee_per_hour']) ? e($agreementLetter->custom_fields['custom_overtime_fee_per_hour']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Denda Keterlambatan (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_late_payment_penalty" value="{{ isset($agreementLetter->custom_fields['custom_late_payment_penalty']) ? e($agreementLetter->custom_fields['custom_late_payment_penalty']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Persentase Denda (%):</label>
                                <div class="col-sm-9">
                                    <input type="number" step="0.01" class="form-control" name="custom_penalty_percentage" value="{{ isset($agreementLetter->custom_fields['custom_penalty_percentage']) ? e($agreementLetter->custom_fields['custom_penalty_percentage']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Korespondensi -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Data Korespondensi</h5>
                        </div>
                        <div class="card-body">
                            <h6>Pihak Pertama</h6>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Telepon:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_first_party_phone" value="{{ isset($agreementLetter->custom_fields['custom_first_party_phone']) ? e($agreementLetter->custom_fields['custom_first_party_phone']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Email:</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" name="custom_first_party_email" value="{{ isset($agreementLetter->custom_fields['custom_first_party_email']) ? e($agreementLetter->custom_fields['custom_first_party_email']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Up (Attention):</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_first_party_attention" value="{{ isset($agreementLetter->custom_fields['custom_first_party_attention']) ? e($agreementLetter->custom_fields['custom_first_party_attention']) : '' }}">
                                </div>
                            </div>

                            <hr>

                            <h6>Pihak Kedua</h6>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Telepon:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_second_party_phone" value="{{ isset($agreementLetter->custom_fields['custom_second_party_phone']) ? e($agreementLetter->custom_fields['custom_second_party_phone']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Email:</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" name="custom_second_party_email" value="{{ isset($agreementLetter->custom_fields['custom_second_party_email']) ? e($agreementLetter->custom_fields['custom_second_party_email']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Up (Attention):</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_second_party_attention" value="{{ isset($agreementLetter->custom_fields['custom_second_party_attention']) ? e($agreementLetter->custom_fields['custom_second_party_attention']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal Perjanjian -->
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Tanggal Penandatanganan</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tanggal Perjanjian:</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" name="custom_agreement_signing_date" value="{{ isset($agreementLetter->custom_fields['custom_agreement_signing_date']) ? e($agreementLetter->custom_fields['custom_agreement_signing_date']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="templateBos1_2-field" style="display:none;">
                    <hr class="my-4">
                    <h4>Detail Perjanjian Sewa Laptop</h4>

                    <!-- Data Pihak Pertama -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Data Pihak Pertama</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama Perusahaan:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_first_party_company_name" value="{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_company_name']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Alamat:</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="custom_laptop_first_party_address" rows="2">{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_address']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_address']) : '' }}</textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Diwakili Oleh:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_first_party_represented_by" value="{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_represented_by']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_represented_by']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Jabatan:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_first_party_position" value="{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_position']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_position']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Bidang Usaha:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_first_party_business_field" value="{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_business_field']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_business_field']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Pihak Kedua -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Data Pihak Kedua</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tipe:</label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="custom_laptop_second_party_type" id="laptop_pihak_kedua_tipe">
                                        <option value="company" {{ (isset($agreementLetter->custom_fields['custom_laptop_second_party_type']) && $agreementLetter->custom_fields['custom_laptop_second_party_type'] == 'company') ? 'selected' : '' }}>Perusahaan</option>
                                        <option value="individual" {{ (isset($agreementLetter->custom_fields['custom_laptop_second_party_type']) && $agreementLetter->custom_fields['custom_laptop_second_party_type'] == 'individual') ? 'selected' : '' }}>Perorangan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_second_party_name" value="{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_name']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_name']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Alamat:</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="custom_laptop_second_party_address" rows="2">{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_address']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_address']) : '' }}</textarea>
                                </div>
                            </div>
                            <div class="form-group row laptop-perusahaan-field">
                                <label class="col-sm-3 col-form-label">Diwakili Oleh:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_second_party_represented_by" value="{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_represented_by']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_represented_by']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row laptop-perusahaan-field">
                                <label class="col-sm-3 col-form-label">Jabatan:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_second_party_position" value="{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_position']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_position']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row laptop-perorangan-field" style="display:none;">
                                <label class="col-sm-3 col-form-label">Nomor Identitas:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_second_party_identity_number" value="{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_identity_number']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_identity_number']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Bidang Usaha:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_second_party_business_field" value="{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_business_field']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_business_field']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Spesifikasi Laptop -->
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Spesifikasi Unit Laptop</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Spesifikasi Singkat:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_specification" placeholder="e.g., Intel Core i5, 8GB RAM, 256GB SSD" value="{{ isset($agreementLetter->custom_fields['custom_laptop_specification']) ? e($agreementLetter->custom_fields['custom_laptop_specification']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Seri Laptop:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_series" placeholder="e.g., ThinkPad X1 Carbon" value="{{ isset($agreementLetter->custom_fields['custom_laptop_series']) ? e($agreementLetter->custom_fields['custom_laptop_series']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tahun Keluaran:</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_laptop_year" placeholder="e.g., 2023" value="{{ isset($agreementLetter->custom_fields['custom_laptop_year']) ? e($agreementLetter->custom_fields['custom_laptop_year']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Jumlah Unit:</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_laptop_quantity" value="{{ isset($agreementLetter->custom_fields['custom_laptop_quantity']) ? e($agreementLetter->custom_fields['custom_laptop_quantity']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Aksesoris Lengkap:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_accessories" placeholder="e.g., charger, tas, mouse" value="{{ isset($agreementLetter->custom_fields['custom_laptop_accessories']) ? e($agreementLetter->custom_fields['custom_laptop_accessories']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Unit Cadangan (>20 unit):</label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="custom_laptop_backup_unit">
                                        <option value="yes" {{ (isset($agreementLetter->custom_fields['custom_laptop_backup_unit']) && $agreementLetter->custom_fields['custom_laptop_backup_unit'] == 'yes') ? 'selected' : '' }}>Ya, Sediakan 2 Unit Cadangan</option>
                                        <option value="no" {{ (isset($agreementLetter->custom_fields['custom_laptop_backup_unit']) && $agreementLetter->custom_fields['custom_laptop_backup_unit'] == 'no') ? 'selected' : '' }}>Tidak</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Periode Perjanjian -->
                    <div class="card mb-3">
                        <div class="card-header bg-warning">
                            <h5 class="mb-0">Periode Perjanjian</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tanggal Mulai:</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" name="custom_laptop_agreement_start_date" value="{{ isset($agreementLetter->custom_fields['custom_laptop_agreement_start_date']) ? e($agreementLetter->custom_fields['custom_laptop_agreement_start_date']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tanggal Selesai:</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" name="custom_laptop_agreement_end_date" value="{{ isset($agreementLetter->custom_fields['custom_laptop_agreement_end_date']) ? e($agreementLetter->custom_fields['custom_laptop_agreement_end_date']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Biaya dan Pembayaran -->
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">Biaya dan Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Biaya Sewa Total (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_laptop_rental_fee_amount" value="{{ isset($agreementLetter->custom_fields['custom_laptop_rental_fee_amount']) ? e($agreementLetter->custom_fields['custom_laptop_rental_fee_amount']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Biaya Sewa (Terbilang):</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_rental_fee_words" placeholder="dua juta lima ratus ribu Rupiah" value="{{ isset($agreementLetter->custom_fields['custom_laptop_rental_fee_words']) ? e($agreementLetter->custom_fields['custom_laptop_rental_fee_words']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Deposit per Unit (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_laptop_deposit_per_unit" placeholder="Default: 150.000" value="{{ isset($agreementLetter->custom_fields['custom_laptop_deposit_per_unit']) ? e($agreementLetter->custom_fields['custom_laptop_deposit_per_unit']) : '150000' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Biaya Instalasi Software (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_laptop_software_installation_fee" value="{{ isset($agreementLetter->custom_fields['custom_laptop_software_installation_fee']) ? e($agreementLetter->custom_fields['custom_laptop_software_installation_fee']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Biaya Pengiriman (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_laptop_delivery_fee" value="{{ isset($agreementLetter->custom_fields['custom_laptop_delivery_fee']) ? e($agreementLetter->custom_fields['custom_laptop_delivery_fee']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Rekening -->
                    <div class="card mb-3">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">Data Rekening Pembayaran</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama Pemilik Rekening:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_account_holder_name" value="{{ isset($agreementLetter->custom_fields['custom_laptop_account_holder_name']) ? e($agreementLetter->custom_fields['custom_laptop_account_holder_name']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama Bank:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_bank_name" value="{{ isset($agreementLetter->custom_fields['custom_laptop_bank_name']) ? e($agreementLetter->custom_fields['custom_laptop_bank_name']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Kantor Cabang:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_branch_office" value="{{ isset($agreementLetter->custom_fields['custom_laptop_branch_office']) ? e($agreementLetter->custom_fields['custom_laptop_branch_office']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nomor Rekening:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_account_number" value="{{ isset($agreementLetter->custom_fields['custom_laptop_account_number']) ? e($agreementLetter->custom_fields['custom_laptop_account_number']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Denda dan Penalti -->
                    <div class="card mb-3">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Denda dan Penalti</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Denda Keterlambatan Pembayaran (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_laptop_late_payment_penalty" value="{{ isset($agreementLetter->custom_fields['custom_laptop_late_payment_penalty']) ? e($agreementLetter->custom_fields['custom_laptop_late_payment_penalty']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Persentase Denda Keterlambatan Pengembalian (%):</label>
                                <div class="col-sm-9">
                                    <input type="number" step="0.01" class="form-control" name="custom_laptop_late_return_penalty_percentage" value="{{ isset($agreementLetter->custom_fields['custom_laptop_late_return_penalty_percentage']) ? e($agreementLetter->custom_fields['custom_laptop_late_return_penalty_percentage']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Korespondensi -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Data Korespondensi</h5>
                        </div>
                        <div class="card-body">
                            <h6>Pihak Pertama</h6>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Telepon:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_first_party_phone" value="{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_phone']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_phone']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Email:</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" name="custom_laptop_first_party_email" value="{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_email']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_email']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Up (Attention):</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_first_party_attention" value="{{ isset($agreementLetter->custom_fields['custom_laptop_first_party_attention']) ? e($agreementLetter->custom_fields['custom_laptop_first_party_attention']) : '' }}">
                                </div>
                            </div>

                            <hr>

                            <h6>Pihak Kedua</h6>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Telepon:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_second_party_phone" value="{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_phone']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_phone']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Email:</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" name="custom_laptop_second_party_email" value="{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_email']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_email']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Up (Attention):</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_laptop_second_party_attention" value="{{ isset($agreementLetter->custom_fields['custom_laptop_second_party_attention']) ? e($agreementLetter->custom_fields['custom_laptop_second_party_attention']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal Perjanjian -->
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Tanggal Penandatanganan</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tanggal Perjanjian:</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" name="custom_laptop_agreement_signing_date" value="{{ isset($agreementLetter->custom_fields['custom_laptop_agreement_signing_date']) ? e($agreementLetter->custom_fields['custom_laptop_agreement_signing_date']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Form tambahan untuk template templateBos1_3 -->
                <div class="templateBos1_3-field" style="display:none;">
                    <hr class="my-4">
                    <h4>Detail Perjanjian Kerjasama N8N</h4>

                    <!-- Data Pihak Pertama -->
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Data Pihak Pertama</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama Perusahaan:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_first_party_company_name" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_first_party_company_name']) ? e($agreementLetter->custom_fields['custom_first_party_company_name']) : (isset($company['name']) ? e($company['name']) : '') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Alamat Perusahaan:</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="custom_first_party_address" rows="2">{{ isset($agreementLetter->custom_fields['custom_first_party_address']) ? e($agreementLetter->custom_fields['custom_first_party_address']) : (isset($company['address']) ? e($company['address']) : '') }}</textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Diwakili Oleh:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_first_party_represented_by" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_first_party_represented_by']) ? e($agreementLetter->custom_fields['custom_first_party_represented_by']) : (isset($company['director']) ? e($company['director']) : '') }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Jabatan:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_first_party_position" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_first_party_position']) ? e($agreementLetter->custom_fields['custom_first_party_position']) : 'Director' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Telepon:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_first_party_telephone" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_first_party_telephone']) ? e($agreementLetter->custom_fields['custom_first_party_telephone']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Email:</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" name="custom_first_party_email" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_first_party_email']) ? e($agreementLetter->custom_fields['custom_first_party_email']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Up (Person in Charge):</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_first_party_up" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_first_party_up']) ? e($agreementLetter->custom_fields['custom_first_party_up']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Pihak Kedua -->
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Data Pihak Kedua</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tipe Pihak Kedua:</label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="custom_second_party_type" id="n8n_pihak_kedua_tipe">
                                        <option value="company" {{ (isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'company') ? 'selected' : '' }}>Perusahaan</option>
                                        <option value="individual" {{ (isset($agreementLetter->custom_fields['custom_second_party_type']) && $agreementLetter->custom_fields['custom_second_party_type'] == 'individual') ? 'selected' : '' }}>Perorangan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_second_party_name" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_second_party_name']) ? e($agreementLetter->custom_fields['custom_second_party_name']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Alamat:</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="custom_second_party_address" rows="2">{{ isset($agreementLetter->custom_fields['custom_second_party_address']) ? e($agreementLetter->custom_fields['custom_second_party_address']) : '' }}</textarea>
                                </div>
                            </div>
                            <div class="form-group row n8n-perusahaan-field">
                                <label class="col-sm-3 col-form-label">Diwakili Oleh:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_second_party_represented_by" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_second_party_represented_by']) ? e($agreementLetter->custom_fields['custom_second_party_represented_by']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row n8n-perusahaan-field">
                                <label class="col-sm-3 col-form-label">Jabatan:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_second_party_position" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_second_party_position']) ? e($agreementLetter->custom_fields['custom_second_party_position']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Telepon:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_second_party_telephone" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_second_party_telephone']) ? e($agreementLetter->custom_fields['custom_second_party_telephone']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Email:</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" name="custom_second_party_email" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_second_party_email']) ? e($agreementLetter->custom_fields['custom_second_party_email']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Up (Person in Charge):</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_second_party_up" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_second_party_up']) ? e($agreementLetter->custom_fields['custom_second_party_up']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Kerjasama -->
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Detail Kerjasama</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Jangka Waktu (Bulan):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_cooperation_duration_months" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_cooperation_duration_months']) ? e($agreementLetter->custom_fields['custom_cooperation_duration_months']) : '3' }}" min="1">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tanggal Mulai:</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" name="custom_cooperation_start_date" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_cooperation_start_date']) ? e($agreementLetter->custom_fields['custom_cooperation_start_date']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tanggal Berakhir:</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" name="custom_cooperation_end_date" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_cooperation_end_date']) ? e($agreementLetter->custom_fields['custom_cooperation_end_date']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Persentase Komisi (%):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_commission_percentage" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_commission_percentage']) ? e($agreementLetter->custom_fields['custom_commission_percentage']) : '20' }}" min="0" max="100" step="0.1">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Durasi Pembayaran (Hari Kerja):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="custom_payment_duration_days" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_payment_duration_days']) ? e($agreementLetter->custom_fields['custom_payment_duration_days']) : '7' }}" min="1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Partner -->
                    <div class="card mb-3">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Informasi Partner (Lampiran)</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama Partner:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_partner_name" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_partner_name']) ? e($agreementLetter->custom_fields['custom_partner_name']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Email Partner:</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" name="custom_partner_email" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_partner_email']) ? e($agreementLetter->custom_fields['custom_partner_email']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nomor Telepon Partner:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_partner_phone" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_partner_phone']) ? e($agreementLetter->custom_fields['custom_partner_phone']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Kode Referral:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_referral_code" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_referral_code']) ? e($agreementLetter->custom_fields['custom_referral_code']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Bank / E-Wallet:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_bank_ewallet_name" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_bank_ewallet_name']) ? e($agreementLetter->custom_fields['custom_bank_ewallet_name']) : '' }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nomor Rekening:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="custom_account_number" 
                                        value="{{ isset($agreementLetter->custom_fields['custom_account_number']) ? e($agreementLetter->custom_fields['custom_account_number']) : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Partner List (LAMPIRAN 4) -->
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Lampiran 4 - Daftar Partner & Periode Kerja Sama</h5>
                            <button type="button" class="btn btn-sm btn-light" id="addPartnerRow">
                                <i class="fa fa-plus"></i> Tambah Partner
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="partnerListTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="25%">Nama Partner</th>
                                            <th width="25%">Lokasi</th>
                                            <th width="25%">Periode</th>
                                            <th width="15%">Status</th>
                                            <th width="5%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="partnerListBody">
                                        @php
                                            $partnerList = isset($agreementLetter->custom_fields['custom_partner_list']) 
                                                ? json_decode($agreementLetter->custom_fields['custom_partner_list'], true) 
                                                : [];
                                            
                                            // Jika kosong, tambahkan 3 baris default
                                            if(empty($partnerList)) {
                                                $partnerList = [
                                                    ['name' => '', 'location' => '', 'period' => '', 'status' => 'Aktif'],
                                                ];
                                            }
                                        @endphp
                                        
                                        @foreach($partnerList as $index => $partner)
                                        <tr class="partner-row">
                                            <td class="text-center row-number">{{ $index + 1 }}</td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" 
                                                    name="custom_partner_list[{{ $index }}][name]" 
                                                    value="{{ $partner['name'] ?? '' }}" 
                                                    placeholder="Nama Partner">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" 
                                                    name="custom_partner_list[{{ $index }}][location]" 
                                                    value="{{ $partner['location'] ?? '' }}" 
                                                    placeholder="Lokasi">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" 
                                                    name="custom_partner_list[{{ $index }}][period]" 
                                                    value="{{ $partner['period'] ?? '' }}" 
                                                    placeholder="Contoh: Jan 2024 - Des 2024">
                                            </td>
                                            <td>
                                                <select class="form-control form-control-sm" name="custom_partner_list[{{ $index }}][status]">
                                                    <option value="Aktif" {{ (isset($partner['status']) && $partner['status'] == 'Aktif') ? 'selected' : '' }}>Aktif</option>
                                                    <option value="Tidak Aktif" {{ (isset($partner['status']) && $partner['status'] == 'Tidak Aktif') ? 'selected' : '' }}>Tidak Aktif</option>
                                                    <option value="Selesai" {{ (isset($partner['status']) && $partner['status'] == 'Selesai') ? 'selected' : '' }}>Selesai</option>
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-partner-row" title="Hapus">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> Klik tombol "Tambah Partner" untuk menambah baris baru. 
                                Minimal 1 baris harus diisi.
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="row template-specific-fields">

                    <!-- English -->
                    <div class="col-md-5 mt-3">
                        <div class="form-group">
                            <label for="pembayaran">Payment Term Clause </label>            
                            <input class="thriveEditor form-control" id="description_payment_term_english" data-ids="payment_term_english" name="payment_term_english" rows="3" placeholder="yang akan dicetak di perjanjian" value="{{ old('payment_term_english') ?? @$agreementLetter->payment_term_english }}"/>
                        </div>
                
                        <div class="form-group">
                            <label for="periode">Agreement Period Clause</label>
                            <input class="thriveEditor form-control" id="description_period_term_english" data-ids="period_term_english" name="period_term_english" rows="3" placeholder="yang akan dicetak di perjanjian" value="{{ old('period_term_english') ?? @$agreementLetter->period_term_english }}"/>
                        </div>
                
                        <div class="form-group">
                            <label for="tambahan">Other Additional Clause</label>
                            <input class="thriveEditor form-control" id="description_other_term_english" data-ids="other_term_english" rows="3" name="other_term_english" placeholder="yang akan dicetak di perjanjian" value="{{ old('other_term_english') ?? @$agreementLetter->other_term_english }}" />
                        </div>
                    </div>

                    <!-- Indonesia -->
                    <div class="offset-1 col-md-5 mt-3">
                        <div class="form-group">
                            <label for="pembayaran">Klausul Termin Pembayaran</label>
                            <input class="thriveEditor form-control" id="description_payment_term" data-ids="payment_term"  id="payment_term" name="payment_term" rows="3" placeholder="yang akan dicetak di perjanjian" value="{{ old('payment_term') ?? @$agreementLetter->payment_term }}"/>
                        </div>
                
                        <div class="form-group">
                            <label for="periode">Klausul Periode Perjanjian</label>
                            <input class="thriveEditor form-control" id="description_period_term" data-ids="period_term" id="period_term" name="period_term" rows="3" placeholder="yang akan dicetak di perjanjian" value="{{ old('period_term') ?? @$agreementLetter->period_term }}"/>
                        </div>
                
                        <div class="form-group">
                            <label for="tambahan">Klausul Tambahan Lain</label>
                            <input class="thriveEditor form-control" id="description_other_term" data-ids="other_term"  id="other_term" rows="3" name="other_term" placeholder="yang akan dicetak di perjanjian" value="{{ old('other_term') ?? @$agreementLetter->other_term }}" />
                        </div>
                    </div>
    
                </div>

        
                <div class="form-group text-right">
                    @if(@$agreementLetter)
                    <button type="submit" class="btn btn-primary">Ubah</button>
                    @else
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src="https://cdn.quilljs.com/1.0.0/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>
<script>
    $(document).ready(function () 
    {
        $('#quote').select2({
            placeholder: 'Pilih Nomor Quote Baru',
            ajax: 
            {
                url: "{{ route('quote.select2') }}",
                dataType: 'json',
                data: function(params) {
                    return {
                        number_result: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(quote) {
                            console.log(quote.customer.name);
                            return {
                                id: quote.id,
                                text: quote.number_result,
                                data: 
                                {
                                    customer: quote.customer ? quote.customer.name : ''
                                }
                            };
                        })
                    };
                }
            }
        });

        
        $('#quote').on('select2:select', function(e) {
            var customerName = e.params.data.data.customer;
            $("#customer").val(customerName);
        });

        var selectedValueQuote = "{{ @$agreementLetter->quote_id }}";
        if(selectedValueQuote)
        {
            title = "{{ @$agreementLetter->quote->number_result }}";
            customerName = "{{ @$agreementLetter->quote->customer->name }}";
            var newOption = new Option(title, selectedValueQuote, true, true);
    
            $('#quote').append(newOption).trigger('change');
            $("#customer").val(customerName);
        }

        // Handle template selection change
        $('#template_agreement_id').on('change', function() {
            toggleTemplateFields();
        });

        // Handle pihak kedua tipe change
        $('#pihak_kedua_tipe').on('change', function() {
            togglePihakKeduaFields();
        });

        // Handle biaya tipe change
        $('#biaya_tipe').on('change', function() {
            toggleBiayaFields();
        });

        // Handle skema pembayaran change
        $('#skema_pembayaran').on('change', function() {
            toggleSkemaPembayaran();
        });
        $('#laptop_pihak_kedua_tipe').on('change', function() {
            toggleLaptopPihakKeduaFields();
        });
        // Event listener untuk perubahan tipe pihak kedua
        $('#n8n_pihak_kedua_tipe').on('change', function() {
            toggleN8nPihakKeduaFields();
        })

        // Initial toggle on page load
        toggleTemplateFields();
        togglePihakKeduaFields();
        toggleBiayaFields();
        toggleSkemaPembayaran();
          // Initial toggle on page load
        toggleLaptopPihakKeduaFields();
        toggleN8nPihakKeduaFields();

        function toggleLaptopPihakKeduaFields() 
        {
            var tipe = $('#laptop_pihak_kedua_tipe').val();
            
            if (tipe === 'company') {
                $('.laptop-perusahaan-field').show();
                $('.laptop-perorangan-field').hide();
            } else {
                $('.laptop-perusahaan-field').hide();
                $('.laptop-perorangan-field').show();
            }
        }

        function toggleTemplateFields() {
            var selectedTemplate = $('#template_agreement_id option:selected').data('template');

            $('.templateBos1_1-field, .templateBos1_2-field, .templateBos1_3-field, .template-specific-fields').hide();

            if (selectedTemplate === 'templateBos1_1') {
                $('.templateBos1_1-field').show();
            } else if (selectedTemplate === 'templateBos1_2') {
                $('.templateBos1_2-field').show();
            } else if (selectedTemplate === 'templateBos1_3') {
                $('.templateBos1_3-field').show();
            } else {
                $('.template-specific-fields').show();
            }
        }

        function togglePihakKeduaFields() {
            var tipe = $('#pihak_kedua_tipe').val();
            
            if (tipe === 'company') {
                $('.perusahaan-field').show();
                $('.perorangan-field').hide();
            } else {
                $('.perusahaan-field').hide();
                $('.perorangan-field').show();
            }
        }

        function toggleN8nPihakKeduaFields() {
            var tipe = $('#n8n_pihak_kedua_tipe').val();
            
            if (tipe === 'company') {
                $('.n8n-perusahaan-field').show();
            } else {
                $('.n8n-perusahaan-field').hide();
            }
        }

        function toggleBiayaFields() {
            // Can be used for additional logic if needed
        }

        function toggleSkemaPembayaran() {
            // Can be used for additional logic if needed
        }
    });
</script>
<script>
    $(document).ready(function() {
        let rowIndex = {{ count($partnerList) }};

        // Fungsi untuk update nomor urut
        function updateRowNumbers() {
            $('#partnerListBody tr').each(function(index) {
                $(this).find('.row-number').text(index + 1);
                
                // Update name attributes
                $(this).find('input, select').each(function() {
                    let name = $(this).attr('name');
                    if(name) {
                        let newName = name.replace(/\[\d+\]/, '[' + index + ']');
                        $(this).attr('name', newName);
                    }
                });
            });
        }

        // Tambah baris baru
        $('#addPartnerRow').click(function() {
            let newRow = `
                <tr class="partner-row">
                    <td class="text-center row-number">${rowIndex + 1}</td>
                    <td>
                        <input type="text" class="form-control form-control-sm" 
                            name="custom_partner_list[${rowIndex}][name]" 
                            placeholder="Nama Partner">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" 
                            name="custom_partner_list[${rowIndex}][location]" 
                            placeholder="Lokasi">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm" 
                            name="custom_partner_list[${rowIndex}][period]" 
                            placeholder="Contoh: Jan 2024 - Des 2024">
                    </td>
                    <td>
                        <select class="form-control form-control-sm" name="custom_partner_list[${rowIndex}][status]">
                            <option value="Aktif" selected>Aktif</option>
                            <option value="Tidak Aktif">Tidak Aktif</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger remove-partner-row" title="Hapus">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            $('#partnerListBody').append(newRow);
            rowIndex++;
            updateRowNumbers();
        });

        // Hapus baris
        $(document).on('click', '.remove-partner-row', function() {
            let rowCount = $('#partnerListBody tr').length;
            
            if(rowCount > 1) {
                $(this).closest('tr').remove();
                updateRowNumbers();
                rowIndex--;
            } else {
                alert('Minimal harus ada 1 baris partner!');
            }
        });

        // Toggle show/hide untuk field perusahaan pada pihak kedua
        function toggleSecondPartyFields() {
            let type = $('#n8n_pihak_kedua_tipe').val();
            if(type === 'individual') {
                $('.n8n-perusahaan-field').hide();
                $('.n8n-perusahaan-field input').prop('required', false);
            } else {
                $('.n8n-perusahaan-field').show();
            }
        }

        // Jalankan saat page load
        toggleSecondPartyFields();

        // Jalankan saat dropdown berubah
        $('#n8n_pihak_kedua_tipe').change(function() {
            toggleSecondPartyFields();
        });
    });
</script>
@stop

@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<style>
   body 
   {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }
    .select2-selection__rendered 
    {
        line-height: 31px !important;
    }
    .select2-container .select2-selection--single 
    {
        height: 35px !important;
    }
    .select2-selection__arrow {
        height: 34px !important;
    }
    .ql-container 
    {
        min-height: 150px;
        height: auto;
    }
    .card {
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .card-header h5 {
        margin: 0;
    }
</style>
<style>
    #partnerListTable input.form-control-sm,
    #partnerListTable select.form-control-sm {
        font-size: 0.875rem;
    }
    
    .partner-row:hover {
        background-color: #f8f9fa;
    }
    
    #partnerListTable thead th {
        background-color: #e9ecef;
        font-weight: 600;
    }
</style>
@stop