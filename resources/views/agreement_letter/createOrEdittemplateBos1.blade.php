@extends('adminlte::page')

@section('content')
<div class="container mt-3">
    <div class="card">
        <div class="card-body">
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
                                <input type="date" name="date" class="form-control" id="date" value="{{ old('date') ?? @$agreementLetter->date }}" required>
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
                        <select class="form-control select2" name="quote" id="quote">
                           
                        </select>
                    </div>
                </div>
        
                <div class="form-group row">
                    <label for="customer" class="col-sm-2 col-form-label">Customer:</label>
                    <div class="col-sm-5">
                        <input type="text" class="form-control" id="customer" value="" placeholder="Pilih Quote" readonly>
                    </div>
                </div>

                <!-- Form tambahan untuk template templateBos1_1 dan templateBos1_2 -->
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
                                    <input type="text" class="form-control" name="pihak_pertama_nama" value="{{ old('pihak_pertama_nama') ?? @$agreementLetter->pihak_pertama_nama }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Alamat:</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="pihak_pertama_alamat" rows="2">{{ old('pihak_pertama_alamat') ?? @$agreementLetter->pihak_pertama_alamat }}</textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Diwakili Oleh:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="pihak_pertama_wakil" value="{{ old('pihak_pertama_wakil') ?? @$agreementLetter->pihak_pertama_wakil }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Jabatan:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="pihak_pertama_jabatan" value="{{ old('pihak_pertama_jabatan') ?? @$agreementLetter->pihak_pertama_jabatan }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Bidang Usaha:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="pihak_pertama_bidang" value="{{ old('pihak_pertama_bidang') ?? @$agreementLetter->pihak_pertama_bidang }}">
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
                                    <select class="form-control" name="pihak_kedua_tipe" id="pihak_kedua_tipe">
                                        <option value="perusahaan" {{ (old('pihak_kedua_tipe') ?? @$agreementLetter->pihak_kedua_tipe) == 'perusahaan' ? 'selected' : '' }}>Perusahaan</option>
                                        <option value="perorangan" {{ (old('pihak_kedua_tipe') ?? @$agreementLetter->pihak_kedua_tipe) == 'perorangan' ? 'selected' : '' }}>Perorangan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="pihak_kedua_nama" value="{{ old('pihak_kedua_nama') ?? @$agreementLetter->pihak_kedua_nama }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Alamat:</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control" name="pihak_kedua_alamat" rows="2">{{ old('pihak_kedua_alamat') ?? @$agreementLetter->pihak_kedua_alamat }}</textarea>
                                </div>
                            </div>
                            <div class="form-group row perusahaan-field">
                                <label class="col-sm-3 col-form-label">Diwakili Oleh:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="pihak_kedua_wakil" value="{{ old('pihak_kedua_wakil') ?? @$agreementLetter->pihak_kedua_wakil }}">
                                </div>
                            </div>
                            <div class="form-group row perusahaan-field">
                                <label class="col-sm-3 col-form-label">Jabatan:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="pihak_kedua_jabatan" value="{{ old('pihak_kedua_jabatan') ?? @$agreementLetter->pihak_kedua_jabatan }}">
                                </div>
                            </div>
                            <div class="form-group row perorangan-field" style="display:none;">
                                <label class="col-sm-3 col-form-label">Nomor Identitas:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="pihak_kedua_nomor_identitas" value="{{ old('pihak_kedua_nomor_identitas') ?? @$agreementLetter->pihak_kedua_nomor_identitas }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Bidang Usaha:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="pihak_kedua_bidang" value="{{ old('pihak_kedua_bidang') ?? @$agreementLetter->pihak_kedua_bidang }}">
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
                                    <input type="text" class="form-control" name="jadwal_hari" placeholder="Senin - Jumat" value="{{ old('jadwal_hari') ?? @$agreementLetter->jadwal_hari }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Jam Mulai:</label>
                                <div class="col-sm-9">
                                    <input type="time" class="form-control" name="jadwal_jam_mulai" value="{{ old('jadwal_jam_mulai') ?? @$agreementLetter->jadwal_jam_mulai }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Jam Selesai:</label>
                                <div class="col-sm-9">
                                    <input type="time" class="form-control" name="jadwal_jam_selesai" value="{{ old('jadwal_jam_selesai') ?? @$agreementLetter->jadwal_jam_selesai }}">
                                </div>
                            </div>
                            <div class="form-group row templateBos1_2-field" style="display:none;">
                                <label class="col-sm-3 col-form-label">Tipe Biaya:</label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="biaya_tipe" id="biaya_tipe">
                                        <option value="per_jam" {{ (old('biaya_tipe') ?? @$agreementLetter->biaya_tipe) == 'per_jam' ? 'selected' : '' }}>Per Jam</option>
                                        <option value="per_bulan" {{ (old('biaya_tipe') ?? @$agreementLetter->biaya_tipe) == 'per_bulan' ? 'selected' : '' }}>Per Bulan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Biaya Sewa (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="biaya_sewa" value="{{ old('biaya_sewa') ?? @$agreementLetter->biaya_sewa }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Biaya Sewa (Terbilang):</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="biaya_sewa_terbilang" placeholder="tiga juta Rupiah" value="{{ old('biaya_sewa_terbilang') ?? @$agreementLetter->biaya_sewa_terbilang }}">
                                </div>
                            </div>
                            <div class="form-group row templateBos1_2-field" style="display:none;">
                                <label class="col-sm-3 col-form-label">Skema Pembayaran:</label>
                                <div class="col-sm-9">
                                    <select class="form-control" name="skema_pembayaran" id="skema_pembayaran">
                                        <option value="dp_pelunasan" {{ (old('skema_pembayaran') ?? @$agreementLetter->skema_pembayaran) == 'dp_pelunasan' ? 'selected' : '' }}>DP 50% + Pelunasan 50%</option>
                                        <option value="penuh" {{ (old('skema_pembayaran') ?? @$agreementLetter->skema_pembayaran) == 'penuh' ? 'selected' : '' }}>Pembayaran Penuh</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row templateBos1_1-field" style="display:none;">
                                <label class="col-sm-3 col-form-label">Persentase Komisi (%):</label>
                                <div class="col-sm-9">
                                    <input type="number" step="0.01" class="form-control" name="persentase_komisi" value="{{ old('persentase_komisi') ?? @$agreementLetter->persentase_komisi }}">
                                </div>
                            </div>
                            <div class="form-group row templateBos1_1-field" style="display:none;">
                                <label class="col-sm-3 col-form-label">Biaya Per Produk (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="biaya_per_produk" value="{{ old('biaya_per_produk') ?? @$agreementLetter->biaya_per_produk }}">
                                </div>
                            </div>
                            <div class="form-group row templateBos1_1-field" style="display:none;">
                                <label class="col-sm-3 col-form-label">Tanggal Pembayaran Komisi:</label>
                                <div class="col-sm-9">
                                    <input type="number" min="1" max="31" class="form-control" name="tanggal_bayar_komisi" placeholder="1-31" value="{{ old('tanggal_bayar_komisi') ?? @$agreementLetter->tanggal_bayar_komisi }}">
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
                                    <input type="date" class="form-control" name="periode_mulai" value="{{ old('periode_mulai') ?? @$agreementLetter->periode_mulai }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Tanggal Selesai:</label>
                                <div class="col-sm-9">
                                    <input type="date" class="form-control" name="periode_selesai" value="{{ old('periode_selesai') ?? @$agreementLetter->periode_selesai }}">
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
                                    <input type="text" class="form-control" name="rekening_nama" value="{{ old('rekening_nama') ?? @$agreementLetter->rekening_nama }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nama Bank:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="rekening_bank" value="{{ old('rekening_bank') ?? @$agreementLetter->rekening_bank }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Kantor Cabang:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="rekening_cabang" value="{{ old('rekening_cabang') ?? @$agreementLetter->rekening_cabang }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Nomor Rekening:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="rekening_nomor" value="{{ old('rekening_nomor') ?? @$agreementLetter->rekening_nomor }}">
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
                                <label class="col-sm-3 col-form-label">Biaya Handling (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="biaya_handling" value="{{ old('biaya_handling') ?? @$agreementLetter->biaya_handling }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Biaya Gudang (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="biaya_gudang" value="{{ old('biaya_gudang') ?? @$agreementLetter->biaya_gudang }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Ukuran Gudang (m3):</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="ukuran_gudang" value="{{ old('ukuran_gudang') ?? @$agreementLetter->ukuran_gudang }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Biaya Overtime (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="biaya_overtime" value="{{ old('biaya_overtime') ?? @$agreementLetter->biaya_overtime }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Denda Keterlambatan (Rp):</label>
                                <div class="col-sm-9">
                                    <input type="number" class="form-control" name="denda_keterlambatan" value="{{ old('denda_keterlambatan') ?? @$agreementLetter->denda_keterlambatan }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Persentase Denda (%):</label>
                                <div class="col-sm-9">
                                    <input type="number" step="0.01" class="form-control" name="persentase_denda" value="{{ old('persentase_denda') ?? @$agreementLetter->persentase_denda }}">
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
                                    <input type="text" class="form-control" name="korespondensi_p1_telepon" value="{{ old('korespondensi_p1_telepon') ?? @$agreementLetter->korespondensi_p1_telepon }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Email:</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" name="korespondensi_p1_email" value="{{ old('korespondensi_p1_email') ?? @$agreementLetter->korespondensi_p1_email }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Up:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="korespondensi_p1_up" value="{{ old('korespondensi_p1_up') ?? @$agreementLetter->korespondensi_p1_up }}">
                                </div>
                            </div>

                            <hr>

                            <h6>Pihak Kedua</h6>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Telepon:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="korespondensi_p2_telepon" value="{{ old('korespondensi_p2_telepon') ?? @$agreementLetter->korespondensi_p2_telepon }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Email:</label>
                                <div class="col-sm-9">
                                    <input type="email" class="form-control" name="korespondensi_p2_email" value="{{ old('korespondensi_p2_email') ?? @$agreementLetter->korespondensi_p2_email }}">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Up:</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="korespondensi_p2_up" value="{{ old('korespondensi_p2_up') ?? @$agreementLetter->korespondensi_p2_up }}">
                                </div>
                            </div>
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

        // Initial toggle on page load
        toggleTemplateFields();
        togglePihakKeduaFields();
        toggleBiayaFields();
        toggleSkemaPembayaran();

        function toggleTemplateFields() {
            var selectedTemplate = $('#template_agreement_id option:selected').data('template');
            
            if (selectedTemplate === 'templateBos1_1' || selectedTemplate === 'templateBos1_2') {
                $('.template-specific-fields').hide();
                
                // Show/hide fields based on template type
                if (selectedTemplate === 'templateBos1_1') {
                    $('.templateBos1_1-field').show();
                    $('.templateBos1_2-field').hide();
                } else if (selectedTemplate === 'templateBos1_2') {
                    $('.templateBos1_1-field').hide();
                    $('.templateBos1_2-field').show();
                }
            } else 
            {
                $('.templateBos1_1-field').hide();
                $('.templateBos1_2-field').hide();
                $('.template-specific-fields').show();
            }
        }

        function togglePihakKeduaFields() {
            var tipe = $('#pihak_kedua_tipe').val();
            
            if (tipe === 'perusahaan') {
                $('.perusahaan-field').show();
                $('.perorangan-field').hide();
            } else {
                $('.perusahaan-field').hide();
                $('.perorangan-field').show();
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
@stop