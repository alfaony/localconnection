@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="col-md-12">
        @if(Session::get('store'))
        <div class="alert alert-success mt-3">Surat Perjanjian Berhasil Ditambahkan</div>
        @endif
        @if(Session::get('update'))
        <div class="alert alert-success mt-3">Surat Perjanjian Berhasil Diperbarui</div>
        @endif
    </div>
    <div class="card scrollable" id="printThis">
        <div class="card-body" id="printItem">
            <div class="row">
                <div class="col-12">
                    <div class="text-center mb-4">
                        <p class="text-center mb-3"><strong>
                                SURAT PERJANJIAN SEWA MENYEWA
                            </strong></p>
                    </div>

                    <p class="mt-3 mb-0">Saya yang bertanda tangan di bawah ini :</p>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td style="width: 50px;">Nama</td>
                                    <td>: {{ $company['director'] }}</td>
                                </tr>
                                <tr>
                                    <td>Alamat</td>
                                    <td>: {{ $company['address'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p>Dalam hal ini bertindak atas nama pribadi sebagai pemilik yang selanjutnya disebut sebagai
                        <strong>PIHAK PERTAMA</strong>
                    </p>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td style="width: 50px;">Nama</td>
                                    <td>: {{ $agreementLetter->quote ? $agreementLetter->quote->customer->director : '' }}</td>
                                </tr>
                                <tr>
                                    <td>TTL</td>
                                    <td>: {{ isset($agreementLetter->custom_fields['custom_br_bp']) ? e($agreementLetter->custom_fields['custom_br_bp']) : '-' }}</td>
                                </tr>
                                <tr>
                                    <td>Alamat</td>
                                    <td>: {{ $agreementLetter->quote ? $agreementLetter->quote->customer->director : '' }}</td>
                                </tr>
                                <tr>
                                    <td>No. KTP</td>
                                    <td>: {{ isset($agreementLetter->custom_fields['custom_nik']) ? e($agreementLetter->custom_fields['custom_nik']) : '-' }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <p>Selanjutnya disebut sebagai PIHAK KEDUA.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-justify">
                        Dalam hal ini pihak dengan ini menerangkan bahwa <strong>PIHAK PERTAMA selaku pemilik sah telah
                            setuju untuk menyewakan kepada PIHAK KEDUA</strong> berupa sebuah kamar kosan yang terletak
                        di {{ $agreementLetter ? $agreementLetter->rent_address : '' }},
                        Senilai <strong>{{ number_format($agreementLetter->quote->total, 0, ',', '.') }},- ( {{ $agreementLetter->quote->total_terbilang }} )</strong> masa sewa {{ $agreementLetter->rent_count }}
                    </p>

                    <p class="text-center mb-2"><strong>PASAL 1</strong></p>
                    <ol>
                        <li><strong>PIHAK PERTAMA</strong> dengan ini menyewakan sebagian ruangan kepada Pihak Kedua
                            untuk digunakan sebagai tempat tinggal (kos) selama jangka waktu tertentu sesuai dengan
                            ketentuan dalam perjanjian ini.</li>
                        <li><strong>PIHAK KEDUA</strong> wajib membayar sewa sebesar<strong> {{ number_format($agreementLetter->quote->total, 0, ',', '.') }},-</strong>
                            setiap bulan paling lambat pada tanggal setiap bulannya.</li>
                    </ol>

                    <p class="text-center mb-2"><strong>PASAL 2</strong></p>
                    <ol>
                        <li>
                            <strong>PIHAK KEDUA</strong> wajib menjaga kebersihan kamar kos dan fasilitas umum.
                            Segala kerusakan yang disebabkan oleh <strong>PIHAK KEDUA</strong> harus segera dilaporkan kepada <strong>PIHAK
                            PERTAMA</strong> dan akan menjadi tanggung jawab <strong>PIHAK KEDUA</strong> untuk perbaikan atau penggantian.
                        </li>
                        <li>
                            Apabila terdapat sumber <strong>API</strong> dari kamar kos <strong>( PIHAK KEDUA )</strong> wajib mengganti karna kelalaian
                            <strong>PIHAK KEDUA</strong>
                        </li>
                        <li>
                            <strong>PIHAK KEDUA</strong> dilarang keras menggunakan, memiliki, atau menyimpan narkoba dalam lingkungan
                            kos. Pelanggaran terhadap kos ketentuan ini dapat mengakibatkan pemutusan kontrak sewa.
                        </li>
                        <li>
                        <strong>PIHAK KEDUA</strong> tidak diperbolehkan membawa tamu laki - laki untuk menginap di dalam kos.
                            Kehadiran tamu hanya diperbolehkan di area umum kos dan pada jam yang wajar.
                        </li>
                    </ol>

                    <p class="text-justify mb-2">
                        Pelanggaran terhadap peraturan - peraturan di atas dapat mengakibatkan tindakan disiplin yang
                        mencakup peringatan tertulis, denda, atau bahkan pemutusan kontrak sewa.
                    </p>

                    <p class="text-center mb-2"><strong>PASAL 4</strong></p>
                    <ol>
                        <li>
                            <Strong>Sanksi Pelanggaran</Strong></br>
                            Apabila <Strong>PIHAK KEDUA</Strong> melanggar ketentuan peraturan kos yang telah disebutkan di atas, <Strong>PIHAK
                            PERTAMA</Strong> berhak memberikan sanksi
                        </li>
                        <li>
                            <Strong>PIHAK KEDUA</Strong> wajib membayar denda yang akan ditentukan dari pihak kos sebagai akibat
                            pelanggaran yang dilakukan.
                        </li>
                    </ol>
                    <p class="text-justify">
                        Pemutusan kontrak sewa : <Strong>PIHAK PERTAMA</Strong> berhak untuk memutuskan kontrak sewa dengan <Strong>PIHAK KEDUA</Strong>
                        tanpa kewajiban pengembalian uang sewa yang telah di bayarkan.
                    </p>
                    <p class="text-justify">
                        Demikian perjanjian ini dibuat dengan sebenarnya tanpa ada unsur paksaan dari pihak manapun dan
                        telah dibaca serta dipahami oleh kedua belah pihak.
                    </p>

                    <div class="row mt-2 mb-1 text-center">
                        <div class="col-5">
                            <p class="mb-0">
                                <strong>PIHAK PERTAMA</strong>
                            </p>
                        </div>
                        <div class="offset-1 col-5 mb-1">
                            <p class="mb-0">
                                <strong>PIHAK KEDUA</strong>
                            </p>
                        </div>
                        <!-- Margin TTD -->
                        <div class="offset-2 col-11 mb-1 mt-1">
                            <img src="{{ asset('logo/paraf.png') }}" alt="Signature" style="with:auto; height:150px" class="left-aligned-image">
                        </div>

                        <div class="col-5">
                            <p class="mb-0">
                                {{ $company['director'] }}
                            </p>
                            <p>
                                <strong>PEMILIK KOS</strong>  
                            </p> 
                        </div>
                        <div class="offset-1 col-5 mb-5">
                            <p class="mb-0">
                                {{ $agreementLetter->quote ? $agreementLetter->quote->customer->director : '' }}
                            </p>
                            <p>
                                <strong>PENGHUNI KOS</strong>  
                            </p> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 text-center mt-3">
        <!-- Penambahan class text-center dan mt-3 -->
        <a href="{{ route('agreement-letter.edit',$agreementLetter->slug) }}" class="btn btn-primary"><i
                class="fa fa-edit"></i>Edit</a>
        <button type="button" id="downloadWorkOrder" class="btn btn-success"><i class="fa fa-file-pdf"></i>
            {{__('Download')}}</button>
    </div>
</div>
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    updateCustomerField();

    $('.select2').select2({
        width: '100%',
        placeholder: 'Pilih Quote'
    });

    $("#downloadWorkOrder").click(function(e) {
        e.preventDefault();
        prinsts();

    });

    $(".select2").on("change", updateCustomerField);
});


function updateCustomerField() {
    // Mendapatkan nilai dari atribut data-customer
    var customerName = $(".select2").find("option:selected").data("customer");

    // Menampilkan nilai tersebut di elemen dengan id "customer"
    $("#customer").val(customerName);
}

function prinsts() {
    let name = "{{ $nomorAgreementLetter }}" + "_surat_perjanjian";
    let printContents = document.getElementById("printThis").innerHTML;
    let originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;

    window.addEventListener("beforeprint", (event) => {
        document.title = name;
    });

    window.print();
    document.body.innerHTML = originalContents;
}
</script>
@stop
@section('css')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

<style>
@media print {
    #printItem {
        margin-left: 50px;
        margin-right: 50px;
        font-size: 10px;
    }

    @font-face {
        font-family: "Times";
        src: url("/fonts/times.ttf") format("truetype");
    }

    table,
    th,
    td {
        border: 1px solid black;
        border-collapse: collapse;
    }

    .centered-image {
        display: block;
        margin-left: auto;
        margin-right: auto;
        max-width: 100%;
        height: auto;
    }

    .page-break {
        page-break-before: always;
        /* atau page-break-after: always; */
    }
}

.centered-image {
    display: block;
    margin-left: auto;
    margin-right: auto;
    max-width: 100%;
    height: auto;
}

@font-face {
    font-family: "Times";
    src: url("/fonts/times.ttf") format("truetype");
}

#printItem {
    margin-left: 50px;
    margin-right: 50px;
    font-size: 12px;
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

table,
th,
td {
    border: 1px solid black;
    border-collapse: collapse;
}

.left-aligned-image {
    float: left;
}

.table td {
    padding: 0rem !important;
}

table,
th,
td {
    border: 0px solid black;
    border-collapse: collapse;
}
</style>
<style>
.table td {
    padding: 0rem !important;
}

body {
    line-height: 1.6;
}

.container {
    /* max-width: 600px; */
    margin: 0 auto;
}

.text-center {
    text-align: center;
}

.text-left {
    text-align: left;
}

.text-right {
    text-align: right;
}

table {
    margin-bottom: 20px;
}

.signature {
    margin-top: 50px;
}

.signature p {
    margin-bottom: 5px;
}

.scrollable-div {
    max-height: 600px;
    overflow-y: auto;
}

.text-justify {
    text-align: justify;
}

.card-body {
    padding-left: 10rem;
    padding-right: 10rem;
}
</style>
@stop