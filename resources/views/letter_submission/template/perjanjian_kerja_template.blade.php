@extends('adminlte::page')
@php
$fieldData = $letterSubmission->convert_field;
@endphp
@section('content')
<div class="container">
    <div class="card scrollable-div" id="printThis">
        <div class="card-body">
            <!-- Header -->
            <div class="col-12 justify-content-center align-items-center">
                <h6 class="text-center"><strong>PERJANJIAN KERJA</strong></h6>
                <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
            </div>

            <div class="col-12 justify-content-center align-items-center mt-4 mb-4">
                <p>Pada Hari {{ $date }} bertempat di Jakarta, telah ditanda tangani perjanjian kerja sama antara:</p>
            </div>

            <div class="col-12 mt-2">
                <!-- Table to display company and employee information -->
                <table class="table table-borderless detail-table">
                    <tbody>
                        <tr>
                            <td>Nama</td>
                            <td>: {{ $company['name'] ?? "" }}</td>
                        </tr>
                        <tr>
                            <td>Penanggung Jawab</td>
                            <td>: {{ $company['director'] ?? "" }}</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>: {{ $company['address'] ?? "" }}</td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <p>Bertindak atas perusahaan yang mempekerjakan, selanjutnya disebut PIHAK PERTAMA.</p>
                            </td>
                        </tr>
                        <tr>
                            <td>Nama</td>
                            <td>: {{ Auth::user()->name }}</td>
                        </tr>
                        <tr>
                            <td>No KTP</td>
                            <td>: {{ Auth::user()->id_card }}</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>: {{ Auth::user()->address }}</td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <p>Bertindak atas nama pribadi, sebagai pekerja / staff yang dipekerjakan, selanjutnya
                                    disebut sebagai PIHAK KEDUA.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>


            <!-- Sections with details -->
            <div class="col-12">
                <div class="row">
                    <div class="col">
                        <h6><strong>PASAL 1. MAKSUD DAN TUJUAN</strong></h6>
                        @if($letterSubmission->user->last_position_now)
                        <p>
                            PARA PIHAK sepakat untuk menjalin hubungan kerja bersama, dimana PIHAK PERTAMA memberikan
                            pekerjaan tetap bulanan kepada PIHAK KEDUA.
                        </p>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <h6><strong>PASAL 2. RUANG LINGKUP</strong></h6>
                        <p>
                            PIHAK PERTAMA menunjuk PIHAK KEDUA untuk melakukan pekerjaan sesuai kebutuhan PIHAK
                            PERTAMA dalam melakukan operasional dan fungsi perusahaan dalam memberikan memenuhi
                            kebutuhan pelanggan, pembeli dan pemegang saham perusahaan.
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <h6><strong>PASAL 3. HAK & KEWAJIBAN PARA PIHAK</strong></h6>
                        <ul>
                            <li>PIHAK KEDUA berhak menerima gaji dari PIHAK PERTAMA sebagaimana yang telah disepakati
                                bersama.</li>
                            <li>PIHAK KEDUA berhak mendapatkan 12 hari cuti dalam setahun.</li>
                            <li>PIHAK KEDUA wajib mentaati seluruh peraturan yang ditetapkan PIHAK PERTAMA.</li>
                            <li>PIHAK KEDUA wajib menjalankan tugas dan pekerjaan dengan bertanggung jawab, dan
                                memberikan hasil yang maksimal.</li>
                            <li>PIHAK KEDUA wajib merahasiakan seluruh informasi perusahaan yang diterima maupun
                                diketahui, yang dapat merugikan PIHAK PERTAMA apabila diketahui pihak lain.</li>
                            <li>PIHAK PERTAMA berhak menetapkan peraturan perusahaan, strategi perusahaan, tugas dan
                                tanggung jawab yang diberikan kepada PIHAK KEDUA.</li>
                            <li>PIHAK PERTAMA berhak mendapatkan perlindungan dan jaminan dari tindakan kecurangan,
                                pencurian, persaingan tidak sehat, dan tindakan melawan hukum yang terjadi akibat
                                tindakan PIHAK KEDUA.</li>
                            <li>PIHAK PERTAMA wajib memberikan peringatan minimal 2 kali, dalam kurun waktu 1 bulan
                                sebelum melakukan pemutusan hubungan kerja sepihak terhadap PIHAK KEDUA.</li>
                            <li>PIHAK KEDUA wajib memberikan keterangan pengunduran diri minimal 2 bulan sebelum
                                mengundurkan diri, dan wajib melakukan serah terima, dan pelatihan kepada pekerja
                                pengganti yang ada.</li>
                            <li>PIHAK PERTAMA berhak melakukan pemutusan hubungan kerja sepihak apabila ditemukan
                                tindakan melawan hukum yang berlaku di UU Republik Indonesia kepada PIHAK KEDUA, tanpa
                                peringatan.</li>
                            <li>PIHAK PERTAMA mendapatkan jaminan dari PIHAK KEDUA untuk tidak membocorkan informasi
                                sensitif yang dapat mengganggu aktivitas perusahaan seperti: gaji yang diterima, bonus
                                yang diterima, insentif dan hal fasilitas lain.</li>
                        </ul>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <h6><strong>PASAL 4. TANGGUNG JAWAB HUKUM</strong></h6>
                        <p>
                            PARA PIHAK sepakat bahwa segala tindakan yang menyebabkan kerugian pada perusahaan, harus
                            dipertanggungjawabkan, walaupun hubungan kerja telah berakhir ataupun diakhiri secara
                            sepihak, dan menunjuk jalur hukum untuk diselesaikan di Pengadilan Negeri Jakarta Barat.
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <h6><strong>PASAL 5. JAM KERJA & CARA BEKERJA</strong></h6>
                        <ul>
                            <li>Pekerjaan diselesaikan dan dikerjakan baik di tempat kerja yang ditentukan sesuai
                                kebutuhan perusahaan dengan penjadwalan kerja sesuai kebutuhan dan kondisi. Disepakati
                                penanggalan merah adalah libur.</li>
                            <li>PIHAK KEDUA wajib bekerja di tempat yang ditentukan sesuai kebutuhan perusahaan.</li>
                            <li>PIHAK KEDUA wajib melakukan absensi secara digital menggunakan aplikasi, dan aktif dalam
                                komunikasi untuk menjaga fungsi dan tanggung jawab pekerjaan berjalan dengan baik.</li>
                            <li>PIHAK PERTAMA dan PIHAK KEDUA sepakat untuk melakukan kalkulasi biaya operasional yang
                                timbul akibat pekerjaan, seperti transportasi, parkir, dan lainnya dalam surat keputusan
                                yang terpisah sesuai fungsi pekerjaan masing-masing.</li>
                            <li>Jumlah jam bekerja adalah 5 hari kerja, dari pukul 08.00 – 17.00 wajib dipenuhi.</li>
                            <li>Pelaporan atas pekerjaan wajib dilakukan dalam bentuk yang dapat ditelusuri,
                                terdokumentasi, dan dipertanggungjawabkan oleh PIHAK KEDUA.</li>
                            <li>Keamanan data, peralatan, dokumen, dan informasi yang dimiliki, dibawa pulang,
                                digunakan, dan diakses PIHAK KEDUA wajib dijaga sebaik-baiknya, dan bertanggung jawab
                                penuh apabila terjadi kelalaian dalam memastikan keamanan hal disebutkan diatas.</li>
                        </ul>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <h6><strong>PASAL 6. JANGKA WAKTU</strong></h6>
                        <p>
                            PARA PIHAK sepakat perjanjian kerja ini bersifat tetap, dan apabila terdapat hal-hal yang
                            belum dijelaskan akan ditentukan di peraturan perusahaan atau surat keputusan manajemen.
                            Perjanjian ini berlaku hingga salah satu pihak mengakhirinya.
                        </p>
                    </div>
                </div>

                <!-- Signature and date -->
                <div class="row mt-4">
                    <div class="col-6 text-center">
                        <p>Jakarta, {{ $date ?? "" }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 text-center">
                        <p><strong>PIHAK PERTAMA</strong></p>
                        @if($letterSubmission->is_approved == 1)
                        <img src="{{ asset('logo/paraf.png') }}" class="img-fluid" alt="Signature" style="height:150px">
                        <p>_________________________</p>
                        <p>{{ $company['director'] ?? "" }}</p>
                        @endif
                    </div>
                    <div class="col-6 text-center">
                        <p><strong>PIHAK KEDUA</strong></p>
                        @if($letterSubmission->is_approved == 1)
                        <img src="{{ Storage::url($fieldData['signature_image'] ?? '' ) }}" class="img-fluid"
                            alt="Signature" style="height:150px">
                        <p>_________________________</p>
                        <p>{{ $letterSubmission->user->name }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Download Button -->
<div class="col-12 text-center mt-3">
    <button type="button" id="downloadQuote" class="btn btn-success"><i class="fa fa-file-pdf"></i>
        {{__('Download')}}</button>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $("#downloadQuote").click(function(e) {
        e.preventDefault();
        printDocument();
    });
});

function printDocument() {
    let name = "Perjanjian Kerja";
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
@endsection

@section('css')
<style>
.table td {
    padding: 0rem !important;
}

@media print {
    table {
        page-break-inside: auto;
    }

    tr {
        page-break-inside: auto;
        page-break-after: auto;
    }

    .strongText {
        font-weight: bold;
        color: #000000;
    }
}

.signature p {
    margin: 0;
}

.strongText {
    font-weight: bold;
    color: #000000;
}

.scrollable-div {
    max-height: 600px;
    overflow-y: auto;
}
</style>
@endsection