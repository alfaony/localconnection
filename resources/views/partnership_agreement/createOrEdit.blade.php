
@extends('adminlte::page')
@section('title', isset($partnershipAgreement) ? 'Edit Dokumen' : 'Tambah Dokumen')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-contract"></i>
                    {{ isset($partnershipAgreement) ? 'Edit Dokumen' : 'Buat Dokumen Baru' }}
                </h3>
            </div>
    
            <div class="card-body">
                @include('components.alert')
                
                <form action="{{ isset($partnershipAgreement) ? route('partnership-agreement.update', $partnershipAgreement) : route('partnership-agreement.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($partnershipAgreement))
                        @method('PUT')
                    @endif
    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="nomor_perjanjian">Nomor Perjanjian</label>
                                <input class="form-control" type="text" placeholder="__/__/____" readonly value="{{ isset($partnershipAgreement) ? $partnershipAgreement->number_result : '' }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-primary">Tanggal Dokumen <span class="text-danger">*</span></label>
                                <input type="date" name="date_agreement" class="form-control" value="{{ isset($partnershipAgreement) ? $partnershipAgreement->date_agreement : date('Y-m-d') }}" required>
                                <small class="form-text text-muted">Tanggal dokumen</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-primary">Jenis Dokumen <span class="text-danger">*</span></label>
                                <select id="typeSelector" name="partnership_agreement_type_id" 
                                        class="form-control select2" style="width: 100%;" required>
                                    <option value="">-- Pilih Jenis Dokumen --</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" 
                                            {{ (isset($partnershipAgreement) && $partnershipAgreement->partnership_agreement_type_id == $type->id) ? 'selected' : '' }} 
                                            data-type="{{ $type->name_format }}">
                                            {{ ucfirst(str_replace('_', ' ', $type->name)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Pilih jenis dokumen yang akan dibuat</small>
                            </div>
                        </div>
                    </div>
    
                    <div id="dynamicFields" class="mt-3">
                        <!-- Dynamic fields akan muncul di sini -->
                    </div>
    
                    <div class="card-footer text-right">
                        @canAccess('store','partnership_agreements')
                        @canAccess('update','partnership_agreements')
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-save"></i> Simpan Dokumen
                        </button>
                        @endcanAccess
                        @endcanAccess
                        <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="{{ asset('js/thriveEditor.js') }}"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            placeholder: 'Pilih jenis dokumen',
            allowClear: true
        });

        const typeSelector = $('#typeSelector');
        const dynamicFields = $('#dynamicFields');
        const partnershipAgreementData = @json(isset($partnershipAgreement->fields) ? $partnershipAgreement->fields : '{}');
        const company = @json($company ?? '{}');
        const partnershipAgreement = JSON.parse(partnershipAgreementData);
        
        
        // Define the structure for each document type (e.g., for "perjanjian_berlangganan_internet")
        const fieldsByType = {
            perjanjian_berlangganan_internet : 
            `
            <div class="row">
                <!-- Identitas Section -->
                <div class="col-md-12">
                    <div class="card card-secondary mt-3">
                        <div class="card-header">Identitas</div>
                        <div class="card-body">
                            <!-- Nama -->
                            <div class="form-group">
                                <label for="nama">Nama <span class="text-danger">*</span></label>
                                <input type="text" id="nama" name="fields[nama]" class="form-control" value="${partnershipAgreement['nama'] || ''}" required>
                            </div>

                            <!-- KTP -->
                            <div class="form-group">
                                <label for="ktp">KTP <span class="text-danger">*</span></label>
                                <input type="text" id="ktp" name="fields[ktp]" class="form-control" value="${partnershipAgreement['ktp'] || ''}" required>
                            </div>

                            <!-- Alamat -->
                            <div class="form-group">
                                <label for="alamat">Alamat <span class="text-danger">*</span></label>
                                <input type="text" id="alamat" name="fields[alamat]" class="form-control" value="${partnershipAgreement['alamat'] || ''}" required>
                            </div>

                            <!-- Telepon -->
                            <div class="form-group">
                                <label for="telephon">Telepon <span class="text-danger">*</span></label>
                                <input type="text" id="telephon" name="fields[telephon]" class="form-control" value="${partnershipAgreement['telephon'] || ''}" required>
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="fields[email]" class="form-control" value="${partnershipAgreement['email'] || ''}" required>
                            </div>

                            <!-- Nama Bank -->
                            <div class="form-group">
                                <label for="nama_bank">Nama Bank <span class="text-danger">*</span></label>
                                <input type="text" id="nama_bank" name="fields[nama_bank]" class="form-control" value="${partnershipAgreement['nama_bank'] || ''}" required>
                            </div>

                            <!-- Holder Name -->
                            <div class="form-group">
                                <label for="holder_name">Holder Name <span class="text-danger">*</span></label>
                                <input type="text" id="holder_name" name="fields[holder_name]" class="form-control" value="${partnershipAgreement['holder_name'] || ''}" required>
                            </div>

                            <!-- Account Number -->
                            <div class="form-group">
                                <label for="account_number">Account Number <span class="text-danger">*</span></label>
                                <input type="text" id="account_number" name="fields[account_number]" class="form-control" value="${partnershipAgreement['account_number'] || ''}" required>
                            </div>

                            <!-- Branch Office -->
                            <div class="form-group">
                                <label for="branch_office">Branch Office <span class="text-danger">*</span></label>
                                <input type="text" id="branch_office" name="fields[branch_office]" class="form-control" value="${partnershipAgreement['branch_office'] || ''}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Langganan Section -->
                <div class="col-md-12">
                    <div class="card card-info mt-3">
                        <div class="card-header">Langganan</div>
                        <div class="card-body">
                            <!-- Alamat Pemasangan -->
                            <div class="form-group">
                                <label for="alamat_pemasangan">Alamat Pemasangan <span class="text-danger">*</span></label>
                                <input type="text" id="alamat_pemasangan" name="fields[alamat_pemasangan]" class="form-control" value="${partnershipAgreement['alamat_pemasangan'] || ''}" required>
                            </div>
                            <!-- Jangka Waktu / Time Period -->
                            <div class="form-group">
                                <label for="jangka_waktu">Jangka Waktu / Time Period <span class="text-danger">*</span></label>
                                <input type="text" id="jangka_waktu" name="fields[jangka_waktu]" class="form-control" value="${partnershipAgreement['jangka_waktu'] || ''}" required>
                            </div>

                            <!-- Nama Paket -->
                            <div class="form-group">
                                <label for="nama_paket">Nama Paket <span class="text-danger">*</span></label>
                                <input type="text" id="nama_paket" name="fields[nama_paket]" class="form-control" value="${partnershipAgreement['nama_paket'] || ''}" required>
                            </div>

                            <!-- Detail Paket -->
                            <div class="form-group">
                                <label for="detail_paket">Detail Paket <span class="text-danger">*</span></label>
                                <textarea id="detail_paket" name="fields[detail_paket]" class="form-control" required>${partnershipAgreement['detail_paket'] || ''}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            `,

            perjanjian_freelance : `
                <div class="card card-secondary mt-3">
                    <div class="card-header">Identitas Pihak Pertama</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_perusahaan_pertama">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" id="nama_perusahaan_pertama" name="fields[nama_perusahaan_pertama]" class="form-control" value="${partnershipAgreement['nama_perusahaan_pertama'] ||  company['name'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="alamat_perusahaan_pertama_id">Alamat Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" id="alamat_perusahaan_pertama" name="fields[alamat_perusahaan_pertama]" class="form-control" value="${partnershipAgreement['alamat_perusahaan_pertama'] || company['address'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="entitas_di_wakili_pihak_pertama">Entitas yang Diwakili <span class="text-danger">*</span></label>
                            <input type="text" id="entitas_di_wakili_pihak_pertama" name="fields[entitas_di_wakili_pihak_pertama]" class="form-control" value="${partnershipAgreement['entitas_di_wakili_pihak_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="nama_perwakilan_pertama">Nama Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="nama_perwakilan_pertama" name="fields[nama_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['nama_perwakilan_pertama'] || company['director'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jabatan_perwakilan_pertama">Jabatan Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="jabatan_perwakilan_pertama" name="fields[jabatan_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['jabatan_perwakilan_pertama'] || 'Director' || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="alamat_perwakilan_pertama">Alamat Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="alamat_perwakilan_pertama" name="fields[alamat_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['alamat_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="telephone_perwakilan_pertama">Telephone Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="telephone_perwakilan_pertama" name="fields[telephone_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="email_perwakilan_pertama">Email Perwakilan <span class="text-danger">*</span></label>
                            <input type="email" id="email_perwakilan_pertama" name="fields[email_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['email_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="up_perwakilan_pertama">Up Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="up_perwakilan_pertama" name="fields[up_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['up_perwakilan_pertama'] || ''}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-secondary mt-3">
                            <div class="card-header">Identitas Pihak Kedua (Individu) </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="nama_pihak_kedua_individu">Nama Individu</label>
                                    <input type="text" id="nama_pihak_kedua_individu" name="fields[nama_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['nama_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="jabatan_pihak_kedua_individu">Jabatan</label>
                                    <input type="text" id="jabatan_pihak_kedua_individu" name="fields[jabatan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['jabatan_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="nomor_identitas_pihak_kedua_individu">Nomor Identitas</label>
                                    <input type="text" id="nomor_identitas_pihak_kedua_individu" name="fields[nomor_identitas_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['nomor_identitas_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_domisili_pihak_kedua_individu">Alamat Domisili</label>
                                    <input type="text" id="alamat_domisili_pihak_kedua_individu" name="fields[alamat_domisili_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['alamat_domisili_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="entitas_di_wakili_individu">Entitas yang Diwakili / Bertanggung jawab untuk</label>
                                    <input type="text" id="entitas_di_wakili_individu" name="fields[entitas_di_wakili_individu]" class="form-control" value="${partnershipAgreement['entitas_di_wakili_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="telephone_perwakilan_pihak_kedua_individu">Telephone</label>
                                    <input type="text" id="telephone_perwakilan_pihak_kedua_individu" name="fields[telephone_perwakilan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="email_perwakilan_pihak_kedua_individu">Email</label>
                                    <input type="email" id="email_perwakilan_pihak_kedua_individu" name="fields[email_perwakilan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['email_perwakilan_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="up_perwakilan_pihak_kedua_individu">Up</label>
                                    <input type="text" id="up_perwakilan_pihak_kedua_individu" name="fields[up_perwakilan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['up_perwakilan_pihak_kedua_individu'] || ''}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-info">
                    <div class="card-header">Informasi Umum Perjanjian</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="masa_berlaku_id">Masa Berlaku Perjanjian (Bahasa) <span class="text-danger">*</span></label>
                            <input type="text" id="masa_berlaku_id" class="mt-2 form-control" data-ids="masa_berlaku_id" name="fields[masa_berlaku_id]" placeholder="1 Tahun" value="${partnershipAgreement['masa_berlaku_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="masa_berlaku_en">Masa Berlaku Perjanjian (English) <span class="text-danger">*</span></label>
                            <input type="text" id="masa_berlaku_en" class="mt-2 form-control" data-ids="masa_berlaku_en" name="fields[masa_berlaku_en]" placeholder="1 Year" value="${partnershipAgreement['masa_berlaku_en'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_mulai_perjanjian">Tanggal Mulai Perjanjian <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal_mulai_perjanjian" class="mt-2 form-control" data-ids="tanggal_mulai_perjanjian" name="fields[tanggal_mulai_perjanjian]" value="${partnershipAgreement['tanggal_mulai_perjanjian'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_berakhir_perjanjian">Tanggal Berakhir Perjanjian <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal_berakhir_perjanjian" class="mt-2 form-control" data-ids="tanggal_berakhir_perjanjian" name="fields[tanggal_berakhir_perjanjian]" value="${partnershipAgreement['tanggal_berakhir_perjanjian'] || ''}" required>
                        </div>

                        <div class="form-group">
                            <label for="keahlian_id">Keahlian <span class="text-danger">*</span></label>
                            <input type="text" id="keahlian_id" class="mt-2 form-control" data-ids="keahlian_id" name="fields[keahlian_id]" placeholder="Contoh: Dokter Gigi" value="${partnershipAgreement['keahlian_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="keahlian_en">Keahlian (English) <span class="text-danger">*</span></label>
                            <input type="text" id="keahlian_en" class="mt-2 form-control" data-ids="keahlian_en" name="fields[keahlian_en]" placeholder="Example: Dentist" value="${partnershipAgreement['keahlian_en'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="detail_kesepakatan_jasa_id">Detail Kesepakatan Jasa (Bahasa)<span class="text-danger">*</span></label>
                            <input type="text" id="description_detail_kesepakatan_jasa_id" class="thriveEditor mt-2 form-control" data-ids="detail_kesepakatan_jasa_id" name="fields[detail_kesepakatan_jasa_id]" placeholder="Contoh: Dokter Gigi" value="${partnershipAgreement['detail_kesepakatan_jasa_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="detail_kesepakatan_jasa_en">Detail Kesepakatan Jasa (English) <span class="text-danger">*</span></label>
                            <input type="text" id="description_detail_kesepakatan_jasa_en" class="thriveEditor mt-2 form-control" data-ids="detail_kesepakatan_jasa_en" name="fields[detail_kesepakatan_jasa_en]" placeholder="Example: Dentist" value="${partnershipAgreement['detail_kesepakatan_jasa_en'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="pembayaran_termin_pertama">Pembayaran Termin Pertama <span class="text-danger">*</span></label>
                            <input type="text" id="pembayaran_termin_pertama" class="mt-2 form-control thriveMoneyShow" data-hidden="pembayaran_termin_pertama" placeholder="Rp 500.000" required>
                            <input type="hidden" id="pembayaran_termin_pertama" class="thriveMoney" name="fields[pembayaran_termin_pertama]" value="${partnershipAgreement['pembayaran_termin_pertama'] || ''}" required>
                        <div class="form-group">
                            <label for="pesentase_pembayaran_termin_pertama">Persentase Pembayaran Termin Pertama <span class="text-danger">*</span></label>
                            <input type="number" id="pesentase_pembayaran_termin_pertama" name="fields[pesentase_pembayaran_termin_pertama]" class="mt-2 form-control" placeholder="50" value="${partnershipAgreement['pesentase_pembayaran_termin_pertama'] || ''}" required>                            
                        </div>
                        <div class="form-group">
                            <label for="pembayaran_termin_kedua">Pembayaran Termin Kedua <span class="text-danger">*</span></label>
                            <input type="text" id="pembayaran_termin_kedua" class="mt-2 form-control thriveMoneyShow" data-hidden="pembayaran_termin_kedua"  placeholder="Rp 5.000.000"  required>
                            <input type="hidden" id="pembayaran_termin_kedua" class="thriveMoney" name="fields[pembayaran_termin_kedua]" value="${partnershipAgreement['pembayaran_termin_kedua'] || ''}" required>
                        <div class="form-group">
                            <label for="pesentase_pembayaran_termin_kedua">Persentase Pembayaran Termin Kedua <span class="text-danger">*</span></label>
                            <input type="text" id="pesentase_pembayaran_termin_kedua" class="mt-2 form-control" data-ids="pesentase_pembayaran_termin_kedua" name="fields[pesentase_pembayaran_termin_kedua]" placeholder="50" value="${partnershipAgreement['pesentase_pembayaran_termin_kedua'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jangka_waktu_dilaksanakan_pembayaran">Jangka Waktu Dilaksanakan Pembayaran (Hari / Days )<span class="text-danger">*</span></label>
                            <input type="number" id="jangka_waktu_dilaksanakan_pembayaran" class="mt-2 form-control" data-ids="jangka_waktu_dilaksanakan_pembayaran" name="fields[jangka_waktu_dilaksanakan_pembayaran]" placeholder="Contoh: 1 Tahun" value="${partnershipAgreement['jangka_waktu_dilaksanakan_pembayaran'] || ''}" required>
                        </div>
                    </div>
                </div>

                <div class="card card-warning mt-3">
                    <div class="card-header">Informasi Rekening</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_pemilik_rekening">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                            <input type="text" id="nama_pemilik_rekening" name="fields[nama_pemilik_rekening]" class="form-control" value="${partnershipAgreement['nama_pemilik_rekening'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="nama_bank">Nama Bank <span class="text-danger">*</span></label>
                            <input type="text" id="nama_bank" name="fields[nama_bank]" class="form-control" value="${partnershipAgreement['nama_bank'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="kantor_cabang_bank">Kantor Cabang Bank <span class="text-danger">*</span></label>
                            <input type="text" id="kantor_cabang_bank" name="fields[kantor_cabang_bank]" class="form-control" value="${partnershipAgreement['kantor_cabang_bank'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="nomor_rekening">Nomor Rekening <span class="text-danger">*</span></label>
                            <input type="text" id="nomor_rekening" name="fields[nomor_rekening]" class="form-control" value="${partnershipAgreement['nomor_rekening'] || ''}" required>
                        </div>
                    </div>
                </div>
            `,
            kerjasama_kemitraan : `
                <div class="card card-secondary mt-3">
                    <div class="card-header">Identitas Pihak Pertama</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_perusahaan_pertama">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" id="nama_perusahaan_pertama" name="fields[nama_perusahaan_pertama]" class="form-control" value="${partnershipAgreement['nama_perusahaan_pertama'] ||  company['name'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="alamat_perusahaan_pertama_id">Alamat Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" id="alamat_perusahaan_pertama" name="fields[alamat_perusahaan_pertama]" class="form-control" value="${partnershipAgreement['alamat_perusahaan_pertama'] || company['address'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="entitas_di_wakili_pihak_pertama">Entitas yang Diwakili <span class="text-danger">*</span></label>
                            <input type="text" id="entitas_di_wakili_pihak_pertama" name="fields[entitas_di_wakili_pihak_pertama]" class="form-control" value="${partnershipAgreement['entitas_di_wakili_pihak_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="nama_perwakilan_pertama">Nama Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="nama_perwakilan_pertama" name="fields[nama_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['nama_perwakilan_pertama'] || company['director'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jabatan_perwakilan_pertama">Jabatan Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="jabatan_perwakilan_pertama" name="fields[jabatan_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['jabatan_perwakilan_pertama'] || 'Director' || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="alamat_perwakilan_pertama">Alamat Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="alamat_perwakilan_pertama" name="fields[alamat_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['alamat_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="telephone_perwakilan_pertama">Telephone Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="telephone_perwakilan_pertama" name="fields[telephone_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="email_perwakilan_pertama">Email Perwakilan <span class="text-danger">*</span></label>
                            <input type="email" id="email_perwakilan_pertama" name="fields[email_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['email_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="up_perwakilan_pertama">Up Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="up_perwakilan_pertama" name="fields[up_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['up_perwakilan_pertama'] || ''}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-secondary mt-3">
                            <div class="card-header">Identitas Pihak Kedua (Perusahaan) <span class="text-danger">*</span></div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="nama_perusahaan_pihak_kedua">Nama Perusahaan <span class="text-danger">*</span></label>
                                    <input type="text" id="nama_perusahaan_pihak_kedua" name="fields[nama_perusahaan_pihak_kedua]" class="form-control" value="${partnershipAgreement['nama_perusahaan_pihak_kedua'] || ''}" required>
                                </div>
                                <div class="form-group">
                                    <label for="alamat_perusahaan_pihak_kedua">Alamat Perusahaan <span class="text-danger">*</span></label>
                                    <input type="text" id="alamat_perusahaan_pihak_kedua" name="fields[alamat_perusahaan_pihak_kedua]" class="form-control" value="${partnershipAgreement['alamat_perusahaan_pihak_kedua'] || ''}" required>
                                </div>
                                <div class="form-group">
                                    <label for="entitas_di_wakili">Entitas yang Diwakili</label>
                                    <input type="text" id="entitas_di_wakili" name="fields[entitas_di_wakili]" class="form-control" value="${partnershipAgreement['entitas_di_wakili'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="nama_perwakilan_pihak_kedua">Nama Perwakilan <span class="text-danger">*</span></label>
                                    <input type="text" id="nama_perwakilan_pihak_kedua" name="fields[nama_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['nama_perwakilan_pihak_kedua'] || ''}" required>
                                </div>
                                <div class="form-group">
                                    <label for="jabatan_perwakilan_pihak_kedua">Jabatan Perwakilan <span class="text-danger">*</span></label>
                                    <input type="text" id="jabatan_perwakilan_pihak_kedua" name="fields[jabatan_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['jabatan_perwakilan_pihak_kedua'] || ''}" required>
                                </div>
                                <div class="form-group">
                                    <label for="alamat_perwakilan_pihak_kedua">Alamat Perwakilan <span class="text-danger">*</span></label>
                                    <input type="text" id="alamat_perwakilan_pihak_kedua" name="fields[alamat_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['alamat_perwakilan_pihak_kedua'] || ''}" required>
                                </div>
                                <div class="form-group">
                                    <label for="telephone_perwakilan_pihak_kedua">Telephone Perwakilan <span class="text-danger">*</span></label>
                                    <input type="text" id="telephone_perwakilan_pihak_kedua" name="fields[telephone_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pihak_kedua'] || ''}" required>
                                </div>
                                <div class="form-group">
                                    <label for="email_perwakilan_pihak_kedua">Email Perwakilan <span class="text-danger">*</span></label>
                                    <input type="email" id="email_perwakilan_pihak_kedua" name="fields[email_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['email_perwakilan_pihak_kedua'] || ''}" required>
                                </div>
                                <div class="form-group">
                                    <label for="up_perwakilan_pihak_kedua">Up Perwakilan <span class="text-danger">*</span></label>
                                    <input type="text" id="up_perwakilan_pihak_kedua" name="fields[up_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['up_perwakilan_pihak_kedua'] || ''}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-info">
                    <div class="card-header">Informasi Umum Perjanjian</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="lokasi_kerjasama">Lokasi Kerjasama <span class="text-danger">*</span></label>
                            <input type="text" id="lokasi_kerjasama" name="fields[lokasi_kerjasama]" class="form-control" value="${partnershipAgreement['lokasi_kerjasama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jumlah_kapasitas_pelanggan">Jumlah Kapasitas Pelanggan <span class="text-danger">*</span></label>
                            <input type="number" id="jumlah_kapasitas_pelanggan" name="fields[jumlah_kapasitas_pelanggan]" class="form-control" value="${partnershipAgreement['jumlah_kapasitas_pelanggan'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="masa_berlaku_id">Masa Berlaku Perjanjian (Bahasa) <span class="text-danger">*</span></label>
                            <input type="text" id="masa_berlaku_id" class="mt-2 form-control" data-ids="masa_berlaku_id" name="fields[masa_berlaku_id]" placeholder="1 Tahun" value="${partnershipAgreement['masa_berlaku_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="masa_berlaku_en">Masa Berlaku Perjanjian (English) <span class="text-danger">*</span></label>
                            <input type="text" id="masa_berlaku_en" class="mt-2 form-control" data-ids="masa_berlaku_en" name="fields[masa_berlaku_en]" placeholder="1 Year" value="${partnershipAgreement['masa_berlaku_en'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_mulai_perjanjian">Tanggal Mulai Perjanjian <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal_mulai_perjanjian" class="mt-2 form-control" data-ids="tanggal_mulai_perjanjian" name="fields[tanggal_mulai_perjanjian]" value="${partnershipAgreement['tanggal_mulai_perjanjian'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_berakhir_perjanjian">Tanggal Berakhir Perjanjian <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal_berakhir_perjanjian" class="mt-2 form-control" data-ids="tanggal_berakhir_perjanjian" name="fields[tanggal_berakhir_perjanjian]" value="${partnershipAgreement['tanggal_berakhir_perjanjian'] || ''}" required>
                        </div>

                        <div class="form-group">
                            <label for="penanggung_jawab_id">Penanggung Jawab Pihak Pertama Dalam Hal Ini (Bahasa) <span class="text-danger">*</span></label>
                            <input type="text" id="penanggung_jawab_id" name="fields[penanggung_jawab_id]" class="form-control" value="${partnershipAgreement['penanggung_jawab_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="penanggung_jawab_en">Penanggung Jawab Pihak Pertama Dalam Hal Ini (English) <span class="text-danger">*</span></label>
                            <input type="text" id="penanggung_jawab_en" name="fields[penanggung_jawab_en]" class="form-control" value="${partnershipAgreement['penanggung_jawab_en'] || ''}" required>
                        </div>
                    </div>
                </div>

                <div class="card card-success mt-3">
                    <div class="card-header">Informasi Pembayaran</div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="dana_pembiayaan">Dana Pembiayaan <span class="text-danger">*</span></label>
                                <input type="text" id="nominal_pembayaran_show" class="form-control thriveMoneyShow" data-hidden="nominal_pembayaran" placeholder="Masukkan nominal pembayaran" required >
                                <input type="hidden" id="nominal_pembayaran" name="fields[dana_pembiayaan]" class="thriveMoney" value="${partnershipAgreement['dana_pembiayaan'] || ''}" required>

                            </div>
                            <div class="form-group">
                                <label for="pembayaran_paling_lambat">Pembayaran Paling Lambat (hari) - setelah perjanjian <span class="text-danger">*</span></label>
                                <input type="number" id="pembayaran_paling_lambat" name="fields[pembayaran_paling_lambat]" class="form-control" value="${partnershipAgreement['pembayaran_paling_lambat'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="presentase_bagi_hasil_pihak_pertama">Presentase Bagi Hasil Pihak Pertama (%) <span class="text-danger">*</span></label>
                                <input type="number" id="presentase_bagi_hasil_pihak_pertama" name="fields[presentase_bagi_hasil_pihak_pertama]" class="form-control" value="${partnershipAgreement['presentase_bagi_hasil_pihak_pertama'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="presentase_bagi_hasil_pihak_kedua">Presentase Bagi Hasil Pihak Kedua (%) <span class="text-danger">*</span></label>
                                <input type="number" id="presentase_bagi_hasil_pihak_kedua" name="fields[presentase_bagi_hasil_pihak_kedua]" class="form-control" value="${partnershipAgreement['presentase_bagi_hasil_pihak_kedua'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="tangga_pembayaran_paling_lambat">Tanggal Pembayaran Paling Lambat <span class="text-danger">*</span></label>
                                <input type="number" id="tangga_pembayaran_paling_lambat" name="fields[tangga_pembayaran_paling_lambat]" class="form-control" value="${partnershipAgreement['tangga_pembayaran_paling_lambat'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="periode_pertama_bulan_dari">Periode Pertama - Bulan Dari <span class="text-danger">*</span></label>
                                <input type="text" id="periode_pertama_bulan_dari" name="fields[periode_pertama_bulan_dari]" class="form-control" value="${partnershipAgreement['periode_pertama_bulan_dari'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="periode_pertama_bulan_sampai">Periode Pertama - Bulan Sampai <span class="text-danger">*</span></label>
                                <input type="text" id="periode_pertama_bulan_sampai" name="fields[periode_pertama_bulan_sampai]" class="form-control" value="${partnershipAgreement['periode_pertama_bulan_sampai'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="presentase_pertama_dari_pelanggan">Presentase Pertama dari Pelanggan (%) <span class="text-danger">*</span></label>
                                <input type="number" id="presentase_pertama_dari_pelanggan" name="fields[presentase_pertama_dari_pelanggan]" class="form-control" value="${partnershipAgreement['presentase_pertama_dari_pelanggan'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="periode_kedua_bulan_dari">Periode Kedua - Bulan Dari <span class="text-danger">*</span></label>
                                <input type="text" id="periode_kedua_bulan_dari" name="fields[periode_kedua_bulan_dari]" class="form-control" value="${partnershipAgreement['periode_kedua_bulan_dari'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="periode_kedua_bulan_sampai">Periode Kedua - Bulan Sampai <span class="text-danger">*</span></label>
                                <input type="text" id="periode_kedua_bulan_sampai" name="fields[periode_kedua_bulan_sampai]" class="form-control" value="${partnershipAgreement['periode_kedua_bulan_sampai'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="presentase_kedua_dari_pelanggan">Presentase Kedua dari Pelanggan (%) <span class="text-danger">*</span></label>
                                <input type="number" id="presentase_kedua_dari_pelanggan" name="fields[presentase_kedua_dari_pelanggan]" class="form-control" value="${partnershipAgreement['presentase_kedua_dari_pelanggan'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="periode_ketiga_bulan_dari">Periode Ketiga - Bulan Dari <span class="text-danger">*</span></label>
                                <input type="text" id="periode_ketiga_bulan_dari" name="fields[periode_ketiga_bulan_dari]" class="form-control" value="${partnershipAgreement['periode_ketiga_bulan_dari'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="periode_ketiga_bulan_sampai">Periode Ketiga - Bulan Sampai <span class="text-danger">*</span></label>
                                <input type="text" id="periode_ketiga_bulan_sampai" name="fields[periode_ketiga_bulan_sampai]" class="form-control" value="${partnershipAgreement['periode_ketiga_bulan_sampai'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="presentase_ketiga_dari_pelanggan">Presentase Ketiga dari Pelanggan (%) <span class="text-danger">*</span></label>
                                <input type="number" id="presentase_ketiga_dari_pelanggan" name="fields[presentase_ketiga_dari_pelanggan]" class="form-control" value="${partnershipAgreement['presentase_ketiga_dari_pelanggan'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="periode_keempat_bulan_dari">Periode Keempat - Bulan Dari <span class="text-danger">*</span></label>
                                <input type="text" id="periode_keempat_bulan_dari" name="fields[periode_keempat_bulan_dari]" class="form-control" value="${partnershipAgreement['periode_keempat_bulan_dari'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="periode_keempat_bulan_sampai">Periode Keempat - Bulan Sampai <span class="text-danger">*</span></label>
                                <input type="text" id="periode_keempat_bulan_sampai" name="fields[periode_keempat_bulan_sampai]" class="form-control" value="${partnershipAgreement['periode_keempat_bulan_sampai'] || ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="presentase_keempat_dari_pelanggan">Presentase Keempat dari Pelanggan (%) <span class="text-danger">*</span></label>
                                <input type="number" id="presentase_keempat_dari_pelanggan" name="fields[presentase_keempat_dari_pelanggan]" class="form-control" value="${partnershipAgreement['presentase_keempat_dari_pelanggan'] || ''}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-warning mt-3">
                    <div class="card-header">Informasi Rekening</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_pemilik_rekening">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                            <input type="text" id="nama_pemilik_rekening" name="fields[nama_pemilik_rekening]" class="form-control" value="${partnershipAgreement['nama_pemilik_rekening'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="nama_bank">Nama Bank <span class="text-danger">*</span></label>
                            <input type="text" id="nama_bank" name="fields[nama_bank]" class="form-control" value="${partnershipAgreement['nama_bank'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="kantor_cabang_bank">Kantor Cabang Bank <span class="text-danger">*</span></label>
                            <input type="text" id="kantor_cabang_bank" name="fields[kantor_cabang_bank]" class="form-control" value="${partnershipAgreement['kantor_cabang_bank'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="nomor_rekening">Nomor Rekening <span class="text-danger">*</span></label>
                            <input type="text" id="nomor_rekening" name="fields[nomor_rekening]" class="form-control" value="${partnershipAgreement['nomor_rekening'] || ''}" required>
                        </div>
                    </div>
                </div>

                <div class="card card-secondary mt-3">
                    <div class="card-header">Lampiran</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="image_topologi">Topologi <span class="text-danger">*</span></label>
                            <input type="file" id="image_topologi" name="fields[image_topologi]" class="form-control" accept=".png, .jpg, .jpeg" required>
                        </div>
                        <div class="form-group">
                            <label for="image_bast">Bast <span class="text-danger">*</span></label>
                            <input type="file" id="image_bast" name="fields[image_bast]" class="form-control" accept=".png, .jpg, .jpeg" required>
                        </div>
                    </div>
                </div>
            `,

            konsinyasi_titip_jual : `
                <div class="card card-secondary mt-3">
                    <div class="card-header">Identitas Pihak Pertama</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_perusahaan_pertama">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" id="nama_perusahaan_pertama" name="fields[nama_perusahaan_pertama]" class="form-control" value="${partnershipAgreement['nama_perusahaan_pertama'] ||  company['name'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="alamat_perusahaan_pertama_id">Alamat Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" id="alamat_perusahaan_pertama" name="fields[alamat_perusahaan_pertama]" class="form-control" value="${partnershipAgreement['alamat_perusahaan_pertama'] || company['address'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="entitas_di_wakili_pihak_pertama">Entitas yang Diwakili <span class="text-danger">*</span></label>
                            <input type="text" id="entitas_di_wakili_pihak_pertama" name="fields[entitas_di_wakili_pihak_pertama]" class="form-control" value="${partnershipAgreement['entitas_di_wakili_pihak_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="nama_perwakilan_pertama">Nama Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="nama_perwakilan_pertama" name="fields[nama_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['nama_perwakilan_pertama'] || company['director'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jabatan_perwakilan_pertama">Jabatan Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="jabatan_perwakilan_pertama" name="fields[jabatan_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['jabatan_perwakilan_pertama'] || 'Director' || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="alamat_perwakilan_pertama">Alamat Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="alamat_perwakilan_pertama" name="fields[alamat_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['alamat_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="telephone_perwakilan_pertama">Telephone Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="telephone_perwakilan_pertama" name="fields[telephone_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="email_perwakilan_pertama">Email Perwakilan <span class="text-danger">*</span></label>
                            <input type="email" id="email_perwakilan_pertama" name="fields[email_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['email_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="up_perwakilan_pertama">Up Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="up_perwakilan_pertama" name="fields[up_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['up_perwakilan_pertama'] || ''}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-secondary mt-3">
                            <div class="card-header">Identitas Pihak Kedua (Perusahaan) </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="nama_perusahaan_pihak_kedua">Nama Perusahaan</label>
                                    <input type="text" id="nama_perusahaan_pihak_kedua" name="fields[nama_perusahaan_pihak_kedua]" class="form-control" value="${partnershipAgreement['nama_perusahaan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_perusahaan_pihak_kedua">Alamat Perusahaan</label>
                                    <input type="text" id="alamat_perusahaan_pihak_kedua" name="fields[alamat_perusahaan_pihak_kedua]" class="form-control" value="${partnershipAgreement['alamat_perusahaan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="entitas_di_wakili">Entitas yang Diwakili</label>
                                    <input type="text" id="entitas_di_wakili" name="fields[entitas_di_wakili]" class="form-control" value="${partnershipAgreement['entitas_di_wakili'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="nama_perwakilan_pihak_kedua">Nama Perwakilan</label>
                                    <input type="text" id="nama_perwakilan_pihak_kedua" name="fields[nama_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['nama_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="jabatan_perwakilan_pihak_kedua">Jabatan Perwakilan</label>
                                    <input type="text" id="jabatan_perwakilan_pihak_kedua" name="fields[jabatan_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['jabatan_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_perwakilan_pihak_kedua">Alamat Perwakilan</label>
                                    <input type="text" id="alamat_perwakilan_pihak_kedua" name="fields[alamat_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['alamat_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="telephone_perwakilan_pihak_kedua">Telephone Perwakilan</label>
                                    <input type="text" id="telephone_perwakilan_pihak_kedua" name="fields[telephone_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="email_perwakilan_pihak_kedua">Email Perwakilan</label>
                                    <input type="email" id="email_perwakilan_pihak_kedua" name="fields[email_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['email_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="up_perwakilan_pihak_kedua">Up Perwakilan</label>
                                    <input type="text" id="up_perwakilan_pihak_kedua" name="fields[up_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['up_perwakilan_pihak_kedua'] || ''}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-secondary mt-3">
                            <div class="card-header">Identitas Pihak Kedua (Individu) </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="nama_pihak_kedua_individu">Nama Individu</label>
                                    <input type="text" id="nama_pihak_kedua_individu" name="fields[nama_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['nama_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="jabatan_pihak_kedua_individu">Jabatan</label>
                                    <input type="text" id="jabatan_pihak_kedua_individu" name="fields[jabatan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['jabatan_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="nomor_identitas_pihak_kedua_individu">Nomor Identitas</label>
                                    <input type="text" id="nomor_identitas_pihak_kedua_individu" name="fields[nomor_identitas_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['nomor_identitas_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_domisili_pihak_kedua_individu">Alamat Domisili</label>
                                    <input type="text" id="alamat_domisili_pihak_kedua_individu" name="fields[alamat_domisili_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['alamat_domisili_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="entitas_di_wakili_individu">Entitas yang Diwakili / Bertanggung jawab untuk</label>
                                    <input type="text" id="entitas_di_wakili_individu" name="fields[entitas_di_wakili_individu]" class="form-control" value="${partnershipAgreement['entitas_di_wakili_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="telephone_perwakilan_pihak_kedua_individu">Telephone</label>
                                    <input type="text" id="telephone_perwakilan_pihak_kedua_individu" name="fields[telephone_perwakilan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="email_perwakilan_pihak_kedua_individu">Email</label>
                                    <input type="email" id="email_perwakilan_pihak_kedua_individu" name="fields[email_perwakilan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['email_perwakilan_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="up_perwakilan_pihak_kedua_individu">Up</label>
                                    <input type="text" id="up_perwakilan_pihak_kedua_individu" name="fields[up_perwakilan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['up_perwakilan_pihak_kedua_individu'] || ''}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-info">
                    <div class="card-header">Informasi Umum Perjanjian</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="masa_berlaku_id">Masa Berlaku Perjanjian (Bahasa) <span class="text-danger">*</span></label>
                            <input type="text" id="masa_berlaku_id" class="mt-2 form-control" data-ids="masa_berlaku_id" name="fields[masa_berlaku_id]" placeholder="1 Tahun" value="${partnershipAgreement['masa_berlaku_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="masa_berlaku_en">Masa Berlaku Perjanjian (English) <span class="text-danger">*</span></label>
                            <input type="text" id="masa_berlaku_en" class="mt-2 form-control" data-ids="masa_berlaku_en" name="fields[masa_berlaku_en]" placeholder="1 Year" value="${partnershipAgreement['masa_berlaku_en'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_mulai_perjanjian">Tanggal Mulai Perjanjian <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal_mulai_perjanjian" class="mt-2 form-control" data-ids="tanggal_mulai_perjanjian" name="fields[tanggal_mulai_perjanjian]" value="${partnershipAgreement['tanggal_mulai_perjanjian'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="tanggal_berakhir_perjanjian">Tanggal Berakhir Perjanjian <span class="text-danger">*</span></label>
                            <input type="date" id="tanggal_berakhir_perjanjian" class="mt-2 form-control" data-ids="tanggal_berakhir_perjanjian" name="fields[tanggal_berakhir_perjanjian]" value="${partnershipAgreement['tanggal_berakhir_perjanjian'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="presentase_konsinyasi">Presentase Konsinyasi <span class="text-danger">*</span></label>
                            <input type="text" id="presentase_konsinyasi" class="mt-2 form-control" name="fields[presentase_konsinyasi]" placeholder="10" value="{{ $partnershipAgreement['presentase_konsinyasi'] ?? '' }}" required>
                        </div>
                    </div>
                </div>
            `,
            table_ads_pemilik_restoran : `
                <div class="card card-secondary mt-3">
                    <div class="card-header">Identitas Pihak Pertama</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_perusahaan_pertama">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" id="nama_perusahaan_pertama" name="fields[nama_perusahaan_pertama]" class="form-control" value="${partnershipAgreement['nama_perusahaan_pertama'] ||  company['name'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="alamat_perusahaan_pertama_id">Alamat Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" id="alamat_perusahaan_pertama" name="fields[alamat_perusahaan_pertama]" class="form-control" value="${partnershipAgreement['alamat_perusahaan_pertama'] || company['address'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="entitas_di_wakili_pihak_pertama">Entitas yang Diwakili <span class="text-danger">*</span></label>
                            <input type="text" id="entitas_di_wakili_pihak_pertama" name="fields[entitas_di_wakili_pihak_pertama]" class="form-control" value="${partnershipAgreement['entitas_di_wakili_pihak_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="nama_perwakilan_pertama">Nama Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="nama_perwakilan_pertama" name="fields[nama_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['nama_perwakilan_pertama'] || company['director'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jabatan_perwakilan_pertama">Jabatan Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="jabatan_perwakilan_pertama" name="fields[jabatan_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['jabatan_perwakilan_pertama'] || 'Director' || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="alamat_perwakilan_pertama">Alamat Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="alamat_perwakilan_pertama" name="fields[alamat_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['alamat_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="telephone_perwakilan_pertama">Telephone Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="telephone_perwakilan_pertama" name="fields[telephone_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="email_perwakilan_pertama">Email Perwakilan <span class="text-danger">*</span></label>
                            <input type="email" id="email_perwakilan_pertama" name="fields[email_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['email_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="up_perwakilan_pertama">Up Perwakilan <span class="text-danger">*</span></label>
                            <input type="text" id="up_perwakilan_pertama" name="fields[up_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['up_perwakilan_pertama'] || ''}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-secondary mt-3">
                            <div class="card-header">Identitas Pihak Kedua (Perusahaan) </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="nama_perusahaan_pihak_kedua">Nama Perusahaan</label>
                                    <input type="text" id="nama_perusahaan_pihak_kedua" name="fields[nama_perusahaan_pihak_kedua]" class="form-control" value="${partnershipAgreement['nama_perusahaan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_perusahaan_pihak_kedua">Alamat Perusahaan</label>
                                    <input type="text" id="alamat_perusahaan_pihak_kedua" name="fields[alamat_perusahaan_pihak_kedua]" class="form-control" value="${partnershipAgreement['alamat_perusahaan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="entitas_di_wakili">Entitas yang Diwakili</label>
                                    <input type="text" id="entitas_di_wakili" name="fields[entitas_di_wakili]" class="form-control" value="${partnershipAgreement['entitas_di_wakili'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="nama_perwakilan_pihak_kedua">Nama Perwakilan</label>
                                    <input type="text" id="nama_perwakilan_pihak_kedua" name="fields[nama_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['nama_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="jabatan_perwakilan_pihak_kedua">Jabatan Perwakilan</label>
                                    <input type="text" id="jabatan_perwakilan_pihak_kedua" name="fields[jabatan_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['jabatan_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_perwakilan_pihak_kedua">Alamat Perwakilan</label>
                                    <input type="text" id="alamat_perwakilan_pihak_kedua" name="fields[alamat_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['alamat_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="telephone_perwakilan_pihak_kedua">Telephone Perwakilan</label>
                                    <input type="text" id="telephone_perwakilan_pihak_kedua" name="fields[telephone_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="email_perwakilan_pihak_kedua">Email Perwakilan</label>
                                    <input type="email" id="email_perwakilan_pihak_kedua" name="fields[email_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['email_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="up_perwakilan_pihak_kedua">Up Perwakilan</label>
                                    <input type="text" id="up_perwakilan_pihak_kedua" name="fields[up_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['up_perwakilan_pihak_kedua'] || ''}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-secondary mt-3">
                            <div class="card-header">Identitas Pihak Kedua (Individu) </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="nama_pihak_kedua_individu">Nama Individu</label>
                                    <input type="text" id="nama_pihak_kedua_individu" name="fields[nama_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['nama_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="jabatan_pihak_kedua_individu">Jabatan</label>
                                    <input type="text" id="jabatan_pihak_kedua_individu" name="fields[jabatan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['jabatan_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="nomor_identitas_pihak_kedua_individu">Nomor Identitas</label>
                                    <input type="text" id="nomor_identitas_pihak_kedua_individu" name="fields[nomor_identitas_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['nomor_identitas_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_domisili_pihak_kedua_individu">Alamat Domisili</label>
                                    <input type="text" id="alamat_domisili_pihak_kedua_individu" name="fields[alamat_domisili_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['alamat_domisili_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="entitas_di_wakili_individu">Entitas yang Diwakili / Bertanggung jawab untuk</label>
                                    <input type="text" id="entitas_di_wakili_individu" name="fields[entitas_di_wakili_individu]" class="form-control" value="${partnershipAgreement['entitas_di_wakili_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="telephone_perwakilan_pihak_kedua_individu">Telephone</label>
                                    <input type="text" id="telephone_perwakilan_pihak_kedua_individu" name="fields[telephone_perwakilan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="email_perwakilan_pihak_kedua_individu">Email</label>
                                    <input type="email" id="email_perwakilan_pihak_kedua_individu" name="fields[email_perwakilan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['email_perwakilan_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="up_perwakilan_pihak_kedua_individu">Up</label>
                                    <input type="text" id="up_perwakilan_pihak_kedua_individu" name="fields[up_perwakilan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['up_perwakilan_pihak_kedua_individu'] || ''}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-info">
                    <div class="card-header">Informasi Umum Perjanjian</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="masa_berlaku_id">Masa Berlaku Perjanjian (Bahasa) <span class="text-danger">*</span></label>
                            <input type="text" id="masa_berlaku_id" class="mt-2 form-control" data-ids="masa_berlaku_id" name="fields[masa_berlaku_id]" placeholder="1 Tahun" value="${partnershipAgreement['masa_berlaku_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="masa_berlaku_en">Masa Berlaku Perjanjian (English) <span class="text-danger">*</span></label>
                            <input type="text" id="masa_berlaku_en" class="mt-2 form-control" data-ids="masa_berlaku_en" name="fields[masa_berlaku_en]" placeholder="1 Year" value="${partnershipAgreement['masa_berlaku_en'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="description_tujuan_kerjasama_id">Tujuan Kerjasama (Bahasa) <span class="text-danger">*</span></label>
                            <input type="text" id="description_tujuan_kerjasama_id" class=" mt-2 form-control" data-ids="tujuan_kerjasama_id" name="fields[tujuan_kerjasama_id]" value="${partnershipAgreement['tujuan_kerjasama_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="description_tujuan_kerjasama_en">Tujuan Kerjasama (English) <span class="text-danger">*</span></label>
                            <input type="text" id="description_tujuan_kerjasama_en" class=" mt-2 form-control" data-ids="tujuan_kerjasama_en" name="fields[tujuan_kerjasama_en]" value="${partnershipAgreement['tujuan_kerjasama_en'] || ''}" required>
                        </div>
                    </div>
                </div>

                <div class="card card-success mt-3">
                    <div class="card-header">Informasi Pembayaran</div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="presentase_pendapatan_penayangan">Presentase Bagi Hasil Penayangan <span class="text-danger">*</span></label>
                                <input type="number" placeholder="10" id="presentase_pendapatan_penayangan" name="fields[presentase_pendapatan_penayangan]" class="form-control" value="${partnershipAgreement['presentase_pendapatan_penayangan'] || ''}"  required>
                            </div>
                            <div class="form-group">
                                <label for="pembayaran_bagi_hasil">Presentase Bagi Hasil Pembayaran Paling Lambat <span class="text-danger">*</span></label>
                                <input type="date" id="pembayaran_bagi_hasil" name="fields[pembayaran_bagi_hasil]" class="form-control" value="${partnershipAgreement['pembayaran_bagi_hasil'] || ''}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-warning mt-3">
                    <div class="card-header">Informasi Rekening</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_pemilik_rekening">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                            <input type="text" id="nama_pemilik_rekening" name="fields[nama_pemilik_rekening]" class="form-control" value="${partnershipAgreement['nama_pemilik_rekening'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="nama_bank">Nama Bank <span class="text-danger">*</span></label>
                            <input type="text" id="nama_bank" name="fields[nama_bank]" class="form-control" value="${partnershipAgreement['nama_bank'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="kantor_cabang_bank">Kantor Cabang Bank <span class="text-danger">*</span></label>
                            <input type="text" id="kantor_cabang_bank" name="fields[kantor_cabang_bank]" class="form-control" value="${partnershipAgreement['kantor_cabang_bank'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="nomor_rekening">Nomor Rekening <span class="text-danger">*</span></label>
                            <input type="text" id="nomor_rekening" name="fields[nomor_rekening]" class="form-control" value="${partnershipAgreement['nomor_rekening'] || ''}" required>
                        </div>
                    </div>
                </div>
            `,

            perjanjian_untuk_kol : `
                <div class="card card-secondary mt-3">
                    <div class="card-header">Identitas Pihak Pertama</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_perusahaan_pertama">Nama Perusahaan</label>
                            <input type="text" id="nama_perusahaan_pertama" name="fields[nama_perusahaan_pertama]" class="form-control" value="${partnershipAgreement['nama_perusahaan_pertama'] ||  company['name'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="alamat_perusahaan_pertama_id">Alamat Perusahaan</label>
                            <input type="text" id="alamat_perusahaan_pertama" name="fields[alamat_perusahaan_pertama]" class="form-control" value="${partnershipAgreement['alamat_perusahaan_pertama'] || company['address'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="entitas_di_wakili_pihak_pertama">Entitas yang Diwakili</label>
                            <input type="text" id="entitas_di_wakili_pihak_pertama" name="fields[entitas_di_wakili_pihak_pertama]" class="form-control" value="${partnershipAgreement['entitas_di_wakili_pihak_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="nama_perwakilan_pertama">Nama Perwakilan</label>
                            <input type="text" id="nama_perwakilan_pertama" name="fields[nama_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['nama_perwakilan_pertama'] || company['director'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jabatan_perwakilan_pertama">Jabatan Perwakilan</label>
                            <input type="text" id="jabatan_perwakilan_pertama" name="fields[jabatan_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['jabatan_perwakilan_pertama'] || 'Director' || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="alamat_perwakilan_pertama">Alamat Perwakilan</label>
                            <input type="text" id="alamat_perwakilan_pertama" name="fields[alamat_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['alamat_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="telephone_perwakilan_pertama">Telephone Perwakilan</label>
                            <input type="text" id="telephone_perwakilan_pertama" name="fields[telephone_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="email_perwakilan_pertama">Email Perwakilan</label>
                            <input type="email" id="email_perwakilan_pertama" name="fields[email_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['email_perwakilan_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="up_perwakilan_pertama">Up Perwakilan</label>
                            <input type="text" id="up_perwakilan_pertama" name="fields[up_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['up_perwakilan_pertama'] || ''}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-secondary mt-3">
                            <div class="card-header">Identitas Pihak Kedua (Perusahaan) </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="nama_perusahaan_pihak_kedua">Nama Perusahaan</label>
                                    <input type="text" id="nama_perusahaan_pihak_kedua" name="fields[nama_perusahaan_pihak_kedua]" class="form-control" value="${partnershipAgreement['nama_perusahaan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_perusahaan_pihak_kedua">Alamat Perusahaan</label>
                                    <input type="text" id="alamat_perusahaan_pihak_kedua" name="fields[alamat_perusahaan_pihak_kedua]" class="form-control" value="${partnershipAgreement['alamat_perusahaan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="entitas_di_wakili">Entitas yang Diwakili</label>
                                    <input type="text" id="entitas_di_wakili" name="fields[entitas_di_wakili]" class="form-control" value="${partnershipAgreement['entitas_di_wakili'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="nama_perwakilan_pihak_kedua">Nama Perwakilan</label>
                                    <input type="text" id="nama_perwakilan_pihak_kedua" name="fields[nama_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['nama_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="jabatan_perwakilan_pihak_kedua">Jabatan Perwakilan</label>
                                    <input type="text" id="jabatan_perwakilan_pihak_kedua" name="fields[jabatan_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['jabatan_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_perwakilan_pihak_kedua">Alamat Perwakilan</label>
                                    <input type="text" id="alamat_perwakilan_pihak_kedua" name="fields[alamat_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['alamat_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="telephone_perwakilan_pihak_kedua">Telephone Perwakilan</label>
                                    <input type="text" id="telephone_perwakilan_pihak_kedua" name="fields[telephone_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="email_perwakilan_pihak_kedua">Email Perwakilan</label>
                                    <input type="email" id="email_perwakilan_pihak_kedua" name="fields[email_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['email_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="up_perwakilan_pihak_kedua">Up Perwakilan</label>
                                    <input type="text" id="up_perwakilan_pihak_kedua" name="fields[up_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['up_perwakilan_pihak_kedua'] || ''}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-secondary mt-3">
                            <div class="card-header">Identitas Pihak Kedua (Individu) </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="nama_pihak_kedua_individu">Nama Individu</label>
                                    <input type="text" id="nama_pihak_kedua_individu" name="fields[nama_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['nama_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="jabatan_pihak_kedua_individu">Jabatan</label>
                                    <input type="text" id="jabatan_pihak_kedua_individu" name="fields[jabatan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['jabatan_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="nomor_identitas_pihak_kedua_individu">Nomor Identitas</label>
                                    <input type="text" id="nomor_identitas_pihak_kedua_individu" name="fields[nomor_identitas_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['nomor_identitas_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_domisili_pihak_kedua_individu">Alamat Domisili</label>
                                    <input type="text" id="alamat_domisili_pihak_kedua_individu" name="fields[alamat_domisili_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['alamat_domisili_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="entitas_di_wakili_individu">Entitas yang Diwakili / Bertanggung jawab untuk</label>
                                    <input type="text" id="entitas_di_wakili_individu" name="fields[entitas_di_wakili_individu]" class="form-control" value="${partnershipAgreement['entitas_di_wakili_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="telephone_perwakilan_pihak_kedua_individu">Telephone</label>
                                    <input type="text" id="telephone_perwakilan_pihak_kedua_individu" name="fields[telephone_perwakilan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="email_perwakilan_pihak_kedua_individu">Email</label>
                                    <input type="email" id="email_perwakilan_pihak_kedua_individu" name="fields[email_perwakilan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['email_perwakilan_pihak_kedua_individu'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="up_perwakilan_pihak_kedua_individu">Up</label>
                                    <input type="text" id="up_perwakilan_pihak_kedua_individu" name="fields[up_perwakilan_pihak_kedua_individu]" class="form-control" value="${partnershipAgreement['up_perwakilan_pihak_kedua_individu'] || ''}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-info">
                    <div class="card-header">Informasi Umum Perjanjian</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="masa_berlaku_id">Masa Berlaku Perjanjian (Bahasa) <span class="text-danger">*</span></label>
                            <input type="text" id="masa_berlaku_id" class="mt-2 form-control" data-ids="masa_berlaku_id" name="fields[masa_berlaku_id]" placeholder="1 Tahun" value="${partnershipAgreement['masa_berlaku_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="masa_berlaku_en">Masa Berlaku Perjanjian (English) <span class="text-danger">*</span></label>
                            <input type="text" id="masa_berlaku_en" class="mt-2 form-control" data-ids="masa_berlaku_en" name="fields[masa_berlaku_en]" placeholder="1 Year" value="${partnershipAgreement['masa_berlaku_en'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="nama_brand">Nama Brand <span class="text-danger">*</span></label>
                            <input type="text" id="nama_brand" class="mt-2 form-control" data-ids="nama_brand" name="fields[nama_brand]" value="${partnershipAgreement['nama_brand'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="detail_brand">Detail Brand <span class="text-danger">*</span></label>
                            <input type="text" id="detail_brand" class="mt-2 form-control" data-ids="detail_brand" name="fields[detail_brand]" value="${partnershipAgreement['detail_brand'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jangka_waktu_content_id">Jangka Waktu Upload Konten (Bahasa) <span class="text-danger">*</span></label>
                            <input type="text" id="jangka_waktu_content_id" class="mt-2 form-control" data-ids="jangka_waktu_content_id" name="fields[jangka_waktu_content_id]" value="${partnershipAgreement['jangka_waktu_content_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jangka_waktu_content_en">Jangka Waktu Upload Konten (English) <span class="text-danger">*</span></label>
                            <input type="text" id="jangka_waktu_content_en" class="mt-2 form-control" data-ids="jangka_waktu_content_en" name="fields[jangka_waktu_content_en]" value="${partnershipAgreement['jangka_waktu_content_en'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="description_tujuan_kerjasama_id">Tujuan Kerjasama (Bahasa) <span class="text-danger">*</span></label>
                            <input type="text" id="description_tujuan_kerjasama_id" class=" mt-2 form-control" data-ids="tujuan_kerjasama_id" name="fields[tujuan_kerjasama_id]" value="${partnershipAgreement['tujuan_kerjasama_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="description_tujuan_kerjasama_en">Tujuan Kerjasama (English) <span class="text-danger">*</span></label>
                            <input type="text" id="description_tujuan_kerjasama_en" class=" mt-2 form-control" data-ids="tujuan_kerjasama_en" name="fields[tujuan_kerjasama_en]" value="${partnershipAgreement['tujuan_kerjasama_en'] || ''}" required>
                        </div>
                    </div>
                </div>

                <div class="card card-success mt-3">
                    <div class="card-header">Informasi Pembayaran</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nominal_pembayaran">Nominal Pembayaran (Rp) <span class="text-danger">*</span></label>
                            <input type="text" id="nominal_pembayaran_show" class="form-control thriveMoneyShow" data-hidden="nominal_pembayaran" placeholder="Masukkan nominal pembayaran" required>
                            <input type="hidden" id="nominal_pembayaran" name="fields[nominal_pembayaran]" class="thriveMoney" value="${partnershipAgreement['nominal_pembayaran'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="terbilang_id">Terbilang (Bahasa) <span class="text-danger">*</span></label>
                            <input type="text" id="terbilang_id" name="fields[terbilang_id]" class="form-control" value="${partnershipAgreement['terbilang_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="terbilang_en">Terbilang (English) <span class="text-danger">*</span></label>
                            <input type="text" id="terbilang_en" name="fields[terbilang_en]" class="form-control" value="${partnershipAgreement['terbilang_en'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jangka_waktu_pembayaran_id">Jangka Waktu Pembayaran - Bahasa <span class="text-danger">*</span></label>
                            <input type="text" id="jangka_waktu_pembayaran_id" name="fields[jangka_waktu_pembayaran_id]" class="form-control" value="${partnershipAgreement['jangka_waktu_pembayaran_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jangka_waktu_pembayaran_en">Jangka Waktu Pembayaran - English <span class="text-danger">*</span></label>
                            <input type="text" id="jangka_waktu_pembayaran_en" name="fields[jangka_waktu_pembayaran_en]" class="form-control" value="${partnershipAgreement['jangka_waktu_pembayaran_en'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jangka_waktu_pembayaran_hari">Jangka Waktu Pembayaran (Hari) <span class="text-danger">*</span></label>
                            <input type="number" id="jangka_waktu_pembayaran_hari" name="fields[jangka_waktu_pembayaran_hari]" class="form-control" value="${partnershipAgreement['jangka_waktu_pembayaran_hari'] || ''}" required>
                        </div>
                    </div>
                </div>

                <div class="card card-warning mt-3">
                    <div class="card-header">Informasi Rekening</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_pemilik_rekening">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                            <input type="text" id="nama_pemilik_rekening" name="fields[nama_pemilik_rekening]" class="form-control" value="${partnershipAgreement['nama_pemilik_rekening'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="nama_bank">Nama Bank <span class="text-danger">*</span></label>
                            <input type="text" id="nama_bank" name="fields[nama_bank]" class="form-control" value="${partnershipAgreement['nama_bank'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="kantor_cabang_bank">Kantor Cabang Bank <span class="text-danger">*</span></label>
                            <input type="text" id="kantor_cabang_bank" name="fields[kantor_cabang_bank]" class="form-control" value="${partnershipAgreement['kantor_cabang_bank'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="nomor_rekening">Nomor Rekening <span class="text-danger">*</span></label>
                            <input type="text" id="nomor_rekening" name="fields[nomor_rekening]" class="form-control" value="${partnershipAgreement['nomor_rekening'] || ''}" required>
                        </div>
                    </div>
                </div>
            `,

            perjanjian_table_ads_pengiklan : `
                <div class="card card-secondary mt-3">
                    <div class="card-header">Identitas Pihak Pertama</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_perusahaan_pertama">Nama Perusahaan</label>
                            <input type="text" id="nama_perusahaan_pertama" name="fields[nama_perusahaan_pertama]" class="form-control" value="${partnershipAgreement['nama_perusahaan_pertama'] ||  company['name'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="alamat_perusahaan_pertama_id">Alamat Perusahaan</label>
                            <input type="text" id="alamat_perusahaan_pertama" name="fields[alamat_perusahaan_pertama]" class="form-control" value="${partnershipAgreement['alamat_perusahaan_pertama'] || company['address'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="nama_perwakilan_pertama">Nama Perwakilan</label>
                            <input type="text" id="nama_perwakilan_pertama" name="fields[nama_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['nama_perwakilan_pertama'] || company['director'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="entitas_di_wakili_pihak_pertama">Entitas yang Diwakili</label>
                            <input type="text" id="entitas_di_wakili_pihak_pertama" name="fields[entitas_di_wakili_pihak_pertama]" class="form-control" value="${partnershipAgreement['entitas_di_wakili_pihak_pertama'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jabatan_perwakilan_pertama">Jabatan Perwakilan</label>
                            <input type="text" id="jabatan_perwakilan_pertama" name="fields[jabatan_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['jabatan_perwakilan_pertama'] || 'Director' || ''}">
                        </div>
                        <div class="form-group">
                            <label for="alamat_perwakilan_pertama">Alamat Perwakilan</label>
                            <input type="text" id="alamat_perwakilan_pertama" name="fields[alamat_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['alamat_perwakilan_pertama'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="telephone_perwakilan_pertama">Telephone Perwakilan</label>
                            <input type="text" id="telephone_perwakilan_pertama" name="fields[telephone_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pertama'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="email_perwakilan_pertama">Email Perwakilan</label>
                            <input type="email" id="email_perwakilan_pertama" name="fields[email_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['email_perwakilan_pertama'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="up_perwakilan_pertama">Up Perwakilan</label>
                            <input type="text" id="up_perwakilan_pertama" name="fields[up_perwakilan_pertama]" class="form-control" value="${partnershipAgreement['up_perwakilan_pertama'] || ''}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-secondary mt-3">
                            <div class="card-header">Identitas Pihak Kedua (Perusahaan) </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="nama_perusahaan_pihak_kedua">Nama Perusahaan</label>
                                    <input type="text" id="nama_perusahaan_pihak_kedua" name="fields[nama_perusahaan_pihak_kedua]" class="form-control" value="${partnershipAgreement['nama_perusahaan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_perusahaan_pihak_kedua">Alamat Perusahaan</label>
                                    <input type="text" id="alamat_perusahaan_pihak_kedua" name="fields[alamat_perusahaan_pihak_kedua]" class="form-control" value="${partnershipAgreement['alamat_perusahaan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="bidang_perusahaan_pihak_kedua_id">Bidang Perusahaan (Bahasa)</label>
                                    <input type="text" id="bidang_perusahaan_pihak_kedua_id" name="fields[bidang_perusahaan_pihak_kedua_id]" class="form-control" value="${partnershipAgreement['bidang_perusahaan_pihak_kedua_id'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="bidang_perusahaan_pihak_kedua_en">Bidang Perusahaan</label>
                                    <input type="text" id="bidang_perusahaan_pihak_kedua_en" name="fields[bidang_perusahaan_pihak_kedua_en]" class="form-control" value="${partnershipAgreement['bidang_perusahaan_pihak_kedua_en'] || ''}">
                                </div>
                                Bahwa Pihak kedua adalah suatu perseroan terbatas yang bergerak dalam bidang
                                <div class="form-group">
                                    <label for="entitas_di_wakili">Entitas yang Diwakili</label>
                                    <input type="text" id="entitas_di_wakili" name="fields[entitas_di_wakili]" class="form-control" value="${partnershipAgreement['entitas_di_wakili'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="nama_perwakilan_pihak_kedua">Nama Perwakilan</label>
                                    <input type="text" id="nama_perwakilan_pihak_kedua" name="fields[nama_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['nama_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="jabatan_perwakilan_pihak_kedua">Jabatan Perwakilan</label>
                                    <input type="text" id="jabatan_perwakilan_pihak_kedua" name="fields[jabatan_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['jabatan_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="alamat_perwakilan_pihak_kedua">Alamat Perwakilan</label>
                                    <input type="text" id="alamat_perwakilan_pihak_kedua" name="fields[alamat_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['alamat_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="telephone_perwakilan_pihak_kedua">Telephone Perwakilan</label>
                                    <input type="text" id="telephone_perwakilan_pihak_kedua" name="fields[telephone_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['telephone_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="email_perwakilan_pihak_kedua">Email Perwakilan</label>
                                    <input type="email" id="email_perwakilan_pihak_kedua" name="fields[email_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['email_perwakilan_pihak_kedua'] || ''}">
                                </div>
                                <div class="form-group">
                                    <label for="up_perwakilan_pihak_kedua">Up Perwakilan</label>
                                    <input type="text" id="up_perwakilan_pihak_kedua" name="fields[up_perwakilan_pihak_kedua]" class="form-control" value="${partnershipAgreement['up_perwakilan_pihak_kedua'] || ''}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-info">
                    <div class="card-header">Informasi RUANG LINGKUP PERJANJIAN </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="description_ketentuan_kerjasama_id">Ketentuan Kerjasama (Bahasa)</label>
                            <input type="text" id="description_ketentuan_kerjasama_id" class="thriveEditor mt-2 form-control" data-ids="ketentuan_kerjasama_id" name="fields[ketentuan_kerjasama_id]" value="${partnershipAgreement['ketentuan_kerjasama_id'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="description_tujuan_kerjasama_en">Ketentuan Kerjasama (English)</label>
                            <input type="text" id="description_ketentuan_kerjasama_en" class="thriveEditor mt-2 form-control" data-ids="ketentuan_kerjasama_en" name="fields[ketentuan_kerjasama_en]" value="${partnershipAgreement['ketentuan_kerjasama_en'] || ''}">
                        </div>
                    </div>
                </div>
                <div class="card card-info">
                    <div class="card-header">Informasi Umum Perjanjian</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="masa_berlaku_id">Masa Berlaku Perjanjian (Bahasa) <span class="text-danger">*</span></label>
                            <input type="text" id="masa_berlaku_id" class="mt-2 form-control" data-ids="masa_berlaku_id" name="fields[masa_berlaku_id]" placeholder="1 Tahun" value="${partnershipAgreement['masa_berlaku_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="masa_berlaku_en">Masa Berlaku Perjanjian (English) <span class="text-danger">*</span></label>
                            <input type="text" id="masa_berlaku_en" class="mt-2 form-control" data-ids="masa_berlaku_en" name="fields[masa_berlaku_en]" placeholder="1 Year" value="${partnershipAgreement['masa_berlaku_en'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jangka_waktu_content_id">Jangka Waktu (Bahasa) <span class="text-danger">*</span></label>
                            <input type="text" id="jangka_waktu_content_id" class="mt-2 form-control" data-ids="jangka_waktu_content_id" name="fields[jangka_waktu_content_id]" value="${partnershipAgreement['jangka_waktu_content_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="jangka_waktu_content_en">Jangka Waktu (English) <span class="text-danger">*</span></label>
                            <input type="text" id="jangka_waktu_content_en" class="mt-2 form-control" data-ids="jangka_waktu_content_en" name="fields[jangka_waktu_content_en]" value="${partnershipAgreement['jangka_waktu_content_en'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="description_tujuan_kerjasama_id">Tujuan Kerjasama (Bahasa) <span class="text-danger">*</span></label>
                            <input type="text" id="description_tujuan_kerjasama_id" class=" mt-2 form-control" data-ids="tujuan_kerjasama_id" name="fields[tujuan_kerjasama_id]" value="${partnershipAgreement['tujuan_kerjasama_id'] || ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="description_tujuan_kerjasama_en">Tujuan Kerjasama (English) <span class="text-danger">*</span></label>
                            <input type="text" id="description_tujuan_kerjasama_en" class=" mt-2 form-control" data-ids="tujuan_kerjasama_en" name="fields[tujuan_kerjasama_en]" value="${partnershipAgreement['tujuan_kerjasama_en'] || ''}" required>
                        </div>
                    </div>
                </div>
                <div class="card card-success mt-3">
                    <div class="card-header">Informasi Pembayaran</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nominal_pembayaran">Nominal Pembayaran (Rp)</label>
                            <input type="text" id="nominal_pembayaran_show" class="form-control thriveMoneyShow" data-hidden="nominal_pembayaran" placeholder="Masukkan nominal pembayaran">
                            <input type="hidden" id="nominal_pembayaran" name="fields[nominal_pembayaran]" class="thriveMoney" value="${partnershipAgreement['nominal_pembayaran'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="terbilang_id">Terbilang (Bahasa)</label>
                            <input type="text" id="terbilang_id" name="fields[terbilang_id]" class="form-control" value="${partnershipAgreement['terbilang_id'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="terbilang_en">Terbilang (English)</label>
                            <input type="text" id="terbilang_en" name="fields[terbilang_en]" class="form-control" value="${partnershipAgreement['terbilang_en'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="jangka_waktu_pembayaran_id">Jangka Waktu Pembayaran - Bahasa</label>
                            <input type="text" id="jangka_waktu_pembayaran_id" name="fields[jangka_waktu_pembayaran_id]" class="form-control" value="${partnershipAgreement['jangka_waktu_pembayaran_id'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="jangka_waktu_pembayaran_en">Jangka Waktu Pembayaran - English</label>
                            <input type="text" id="jangka_waktu_pembayaran_en" name="fields[jangka_waktu_pembayaran_en]" class="form-control" value="${partnershipAgreement['jangka_waktu_pembayaran_en'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="jangka_waktu_pembayaran_hari">Jangka Waktu Pembayaran (Hari) <span class="text-danger">*</span></label>
                            <input type="number" id="jangka_waktu_pembayaran_hari" name="fields[jangka_waktu_pembayaran_hari]" class="form-control" value="${partnershipAgreement['jangka_waktu_pembayaran_hari'] || ''}" required>
                        </div>
                    </div>
                </div>

                <div class="card card-warning mt-3">
                    <div class="card-header">Informasi Rekening</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nama_pemilik_rekening">Nama Pemilik Rekening</label>
                            <input type="text" id="nama_pemilik_rekening" name="fields[nama_pemilik_rekening]" class="form-control" value="${partnershipAgreement['nama_pemilik_rekening'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="nama_bank">Nama Bank</label>
                            <input type="text" id="nama_bank" name="fields[nama_bank]" class="form-control" value="${partnershipAgreement['nama_bank'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="kantor_cabang_bank">Kantor Cabang Bank</label>
                            <input type="text" id="kantor_cabang_bank" name="fields[kantor_cabang_bank]" class="form-control" value="${partnershipAgreement['kantor_cabang_bank'] || ''}">
                        </div>
                        <div class="form-group">
                            <label for="nomor_rekening">Nomor Rekening</label>
                            <input type="text" id="nomor_rekening" name="fields[nomor_rekening]" class="form-control" value="${partnershipAgreement['nomor_rekening'] || ''}">
                        </div>
                    </div>
                </div>
            `,
            // Add more document types here if needed
        };

        function renderFields(type) 
        {
            dynamicFields.html(fieldsByType[type] || '');
            if(fieldsByType[type]) {
                dynamicFields.find('input, select').addClass('is-valid');
                setTimeout(() => dynamicFields.find('input, select').removeClass('is-valid'), 2000);
            }

            let inputs = document.querySelectorAll('.thriveEditor');
            let inputIds = [];
            inputs.forEach(input => 
            {
                if (input.id) {
                    let ids = input.getAttribute('data-ids');
                    generateThriveEditor(ids, input.value, "");
                }
            });

            let moneyInputs = document.querySelectorAll(".thriveMoney");

            moneyInputs.forEach(input => {
                let showInput = document.querySelector(`[data-hidden="${input.id}"]`);
                
                // Jika dalam mode edit dan ada nilai default di hidden input
                if (input.value) {
                    let formattedValue = new Intl.NumberFormat('id-ID').format(input.value);
                    showInput.value = 'Rp ' + formattedValue;
                    input.value = input.value; // Pastikan hidden input tetap menyimpan angka asli
                }

                // Tambahkan event listener untuk format angka saat mengetik
                showInput.addEventListener("input", function () {
                    formatRupiahFormat(showInput, input);
                });
            });
        }

        function generateCustom(documentId) 
        {
            const element = document.getElementById(documentId);
            if (element) 
            {
                const value = element.value ?? '';
                generate(documentId, value);
            }
        }

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
                input.value = '';
                inputNonFormat.value = 0;
            } else {
                rupiah = rupiah.replace(/^0+/, '');
                input.value = 'Rp ' + rupiah;
                inputNonFormat.value = numStr.replace(/^0+/, ''); // Simpan angka asli di hidden
            }
        }

        typeSelector.on('change', function() {
            dynamicFields.addClass('fade-in');
            const selectedType = $(this).find(':selected').data('type');
            renderFields(selectedType);
            setTimeout(() => dynamicFields.removeClass('fade-in'), 500);
        });

        // Initial load jika edit
        if(typeSelector.val()) {
            const initialType = typeSelector.find(':selected').data('type');
            renderFields(initialType);
        }
    });
</script>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    .fade-in {
        animation: fadeIn 0.5s;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .card-outline {
        border-top: 3px solid #007bff !important;
    }
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        padding: 5px 10px !important;
    }
    .select2-selection__choice
    {
        background-color: #007bff !important;
        border: 1px solid #007bff !important;
    }

    .select2-selection__choice__remove
    {
        color: #fe0700 !important;
        border: 1px solid #007bff !important;
    }
</style>
@endsection