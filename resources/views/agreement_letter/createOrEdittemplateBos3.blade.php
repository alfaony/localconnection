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
                                    <input type="date" name="date" class="form-control" id="date"
                                        value="{{ old('date', date('Y-m-d')) ?? @$agreementLetter->date }}" required>
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
                        <label for="template_agreement_show" class="col-sm-3 col-form-label">Pilih Template
                            Perjanjian:</label>
                        <div class="col-sm-5">
                            <select class="form-control select2" name="template_agreement_id"
                                id="template_agreement_id">
                                @foreach($selectTemplate as $template)
                                <option value="{{ $template->id }}" data-template="{{ $template->template_agreement }}" {{ 
                                    (@$agreementLetter->template_agreement_id ? 
                                    @$agreementLetter->template_agreement_id == $template->id : 
                                    $template->is_default) ? 'selected' : '' }}>
                                    {{ $template->template_agreement_show }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="quote" class="col-sm-3 col-form-label">Pilih No. Quote:</label>
                        <div class="col-sm-5">
                            <input type="hidden" name="quote_id"
                                value="{{ old('quote') ?? @$agreementLetter->quote_id }}">
                            <select class="form-control select2" name="quote" id="quote">

                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="customer" class="col-sm-3 col-form-label">Customer:</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="customer" value="" placeholder="Pilih Quote"
                                readonly>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="customer" class="col-sm-3 col-form-label">Alamat Customer:</label>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" id="customer_address" value=""
                                placeholder="Pilih Quote" readonly>
                        </div>
                    </div>

                    <div id="customFields" style="display: none;">
                        <div class="form-group row">
                            <label for="custom_br_bp" class="col-sm-3 col-form-label">Tempat, Tanggal Lahir:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_br_bp" id="custom_br_bp"
                                    placeholder="Masukkan Tempat, Tanggal Lahir"
                                    value="{{ isset($agreementLetter->custom_fields['custom_br_bp']) ? e($agreementLetter->custom_fields['custom_br_bp']) : '-' }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="custom_nik" class="col-sm-3 col-form-label">NIK:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_nik" id="custom_nik"
                                    placeholder="Masukkan NIK"
                                    value="{{ isset($agreementLetter->custom_fields['custom_nik']) ? e($agreementLetter->custom_fields['custom_nik']) : '-' }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mt-5">
                        <label for="customer" class="col-sm-3 col-form-label">Alamat Kantor Disewa:</label>
                        <div class="col-sm-5">
                            <textarea type="text" class="form-control" id="description_rent_address"
                                data-ids="rent_address" name="rent_address" id="rent_address"
                                placeholder="Alamat Kantor Disewa">{{ old('rent_address') ?? @$agreementLetter->rent_address }}</textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="customer" class="col-sm-3 col-form-label">Durasi Sewa:</label>
                        <div class="col-sm-5 ">
                            <input type="date" class="form-control" name="rent_start_duration"
                                value="{{ old('rent_start_duration') ?? @$agreementLetter->rent_start_duration }}"
                                placeholder="Pilih Quote">
                            <label for=""> - </label>
                            <input type="date" class="form-control" name="rent_end_duration"
                                value="{{ old('rent_end_duration') ?? @$agreementLetter->rent_end_duration }}"
                                placeholder="Pilih Quote">
                        </div>
                    </div>
                    <!-- CUSTOM FIELDS (MUNCUL JIKA TEMPLATE = "templateBos3_2") -->
                    <div id="customFieldsContainer" style="display: none;">
                        <h3 class="mt-4">OBJEK SEWA (Unit Self Storage)</h3>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Unit:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_unit"
                                    value="{{ @$agreementLetter->custom_fields['custom_unit'] }}">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Tipe:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_type"
                                    value="{{ @$agreementLetter->custom_fields['custom_type'] }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Dimensi (PxLxT):</label>
                            <div class="col-sm-2"><input type="text" class="form-control" name="custom_length"
                                    value="{{ @$agreementLetter->custom_fields['custom_length'] }}"></div>
                            <div class="col-sm-2"><input type="text" class="form-control" name="custom_width"
                                    value="{{ @$agreementLetter->custom_fields['custom_width'] }}"></div>
                            <div class="col-sm-2"><input type="text" class="form-control" name="custom_height"
                                    value="{{ @$agreementLetter->custom_fields['custom_height'] }}"></div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Nomor Unit:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_unit_number"
                                    value="{{ @$agreementLetter->custom_fields['custom_unit_number'] }}">
                            </div>
                        </div>

                        <h3 class="mt-4">PIHAK PERTAMA (Penyedia Gudangplus)</h3>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Nama Perusahaan:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_provider_company"
                                    value="{{ @$agreementLetter->custom_fields['custom_provider_company'] ?? $company['name']}} ">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">No Telepon:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_provider_phone"
                                    value="{{ @$agreementLetter->custom_fields['custom_provider_phone'] }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Email:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_provider_email"
                                    value="{{ @$agreementLetter->custom_fields['custom_provider_email'] }}">
                            </div>
                        </div>

                        <h3 class="mt-4">PIHAK KEDUA (Penyewa Gudangplus)</h3>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Nama Perusahaan:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_tenant_company"
                                    value="{{ @$agreementLetter->custom_fields['custom_tenant_company'] }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">No Telp Perusahaan:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_tenant_phone"
                                    value="{{ @$agreementLetter->custom_fields['custom_tenant_phone'] }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">NPWP Perusahaan:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_tenant_npwp"
                                    value="{{ @$agreementLetter->custom_fields['custom_tenant_npwp'] }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Alamat Perusahaan:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_tenant_address"
                                    value="{{ @$agreementLetter->custom_fields['custom_tenant_address'] }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Nama PIC:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_pic_name"
                                    value="{{ @$agreementLetter->custom_fields['custom_pic_name'] }}">
                            </div>
                        </div>


                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Jabatan PIC:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_pic_position"
                                    value="{{ @$agreementLetter->custom_fields['custom_pic_position'] }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">No Telepon PIC:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_pic_phone"
                                    value="{{ @$agreementLetter->custom_fields['custom_pic_phone'] }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">NIK PIC:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_pic_nik"
                                    value="{{ @$agreementLetter->custom_fields['custom_pic_nik'] }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Email PIC:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="custom_pic_email"
                                    value="{{ @$agreementLetter->custom_fields['custom_pic_email'] }}">
                            </div>
                        </div>


                        <div class="form-group row mt-5">
                            <label for="customer" class="col-sm-3 col-form-label">Nama Penjamin:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="commission_name"
                                    value="{{ old('commission_name') ?? @$agreementLetter->commission_name }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="customer" class="col-sm-3 col-form-label">Nomor Penjamin:</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" id="" name="commission_phone"
                                    placeholder="0856...."
                                    value="{{ old('commission_phone') ?? @$agreementLetter->commission_phone }}">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="customer" class="col-sm-3 col-form-label">Alamat Penjamin:</label>
                            <div class="col-sm-5">
                                <textarea type="text" class="form-control" id="description_rent_address"
                                    data-ids="rent_address" name="commission_address" id="rent_address"
                                    placeholder="Alamat Penjamin">{{ old('commission_address') ?? @$agreementLetter->commission_address }}</textarea>
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
$(document).ready(function() {
    function toggleCustomFields() {
        var selectedTemplate = $('#template_agreement_id option:selected').data('template');

        if (selectedTemplate === "templateBos3_1") {
            $('#customFields').show();
            $('#custom_br_bp, #custom_nik').prop('required', true);
        } else {
            $('#customFields').hide();
            $('#custom_br_bp, #custom_nik').prop('required', false);
        }
    }

    function toggleCustomFieldsTemplate3() {
        let selectedTemplate = $("#template_agreement_id option:selected").attr("data-template");
        if (selectedTemplate === "templateBos3_2") {
            $("#customFieldsContainer").show();
        } else {
            $("#customFieldsContainer").hide();
        }
    }

    // Panggil fungsi saat halaman dimuat untuk memeriksa apakah field harus ditampilkan
    toggleCustomFields();
    toggleCustomFieldsTemplate3();

    // Panggil fungsi saat user mengganti pilihan template
    $('#template_agreement_id').change(function() {
        toggleCustomFields();
        toggleCustomFieldsTemplate3();
    });
});
$(document).ready(function() {

    $('#quote').select2({
        placeholder: 'Pilih Nomor Quote Baru',
        ajax: {
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
                        return {
                            id: quote.id,
                            text: quote.number_result,
                            data: {
                                customer: quote.customer ? quote.customer.name : '',
                                customer_address: quote.customer ? quote.customer.address :
                                    ''
                            }
                        };
                    })
                };
            }
        }
    });


    $('#quote').on('select2:select', function(e) {
        // Ambil data-customer dari opsi yang dipilih
        // console.log(e);
        var customerName = e.params.data.data.customer;
        // Ambil data-customer_address dari opsi yang dipilih
        var customerAddress = e.params.data.data.customer_address;

        // Menampilkan nilai tersebut di elemen dengan id "customer_address"
        $("#customer_address").val(customerAddress);

        // Menampilkan nilai tersebut di elemen dengan id "customer"
        $("#customer").val(customerName);
    });

    var selectedValueQuote = "{{ @$agreementLetter->quote_id }}";
    if (selectedValueQuote) {
        title = "{{ @$agreementLetter->quote->number_result }}";
        customerName = "{{ @$agreementLetter->quote->customer->name }}";
        customerAddress = "{{ @$agreementLetter->quote->customer->address }}";
        // Create an option element with the selected value
        var newOption = new Option(title, selectedValueQuote, true, true);

        // Append the option to the select2 element and trigger change
        $('#quote').append(newOption).trigger('change');
        $("#customer").val(customerName);
        $("#customer_address").val(customerAddress);
    }

    // updateCustomerField();

    // $('.select2').select2({
    //     width: '100%',
    //     placeholder: 'Pilih Quote'
    // });

    // $(".select2").on("change", updateCustomerField);


});
</script>
@stop
@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<style>
body {
    font-family: Arial, sans-serif;
    /* padding: 20px; */
    background-color: #f4f4f4;
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

.ql-container {
    min-height: 150px;
    height: auto;
}
</style>
@stop