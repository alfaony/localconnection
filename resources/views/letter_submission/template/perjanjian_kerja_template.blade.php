@extends('adminlte::page')
@php
    $fieldData = $letterSubmission->convert_field;
    $user = $letterSubmission->user;
@endphp
@section('content')
<div class="container">
    <div class="card scrollable-div" id="printThis">
        <div class="card-body">
            <!-- Header -->
            <div class="col-12 justify-content-center align-items-center">
                <h6 class="text-center"><strong><h3>PERJANJIAN KERJA</h3></strong></h6>
                <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
            </div>

            <div class="col-12 justify-content-center align-items-center mt-4 mb-4">
                <p class="text-justify">Pada Hari {{ $dateWithDay }} bertempat di Jakarta, telah ditanda tangani perjanjian kerja sama antara:</p>
            </div>

            <div class="col-12 mt-2">
                <!-- Table to display company and employee information -->
                <table class="table table-top table-borderless detail-table">
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
                            <td>: {{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td>No KTP</td>
                            <td>: {{ $user->id_card }}</td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>: {{ $user->address }}</td>
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
                        <p class="text-justify">
                            PARA PIHAK sepakat untuk menjalin hubungan kerja bersama, dimana PIHAK PERTAMA memberikan
                            pekerjaan tetap bulanan kepada PIHAK KEDUA.
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <h6><strong>PASAL 2. RUANG LINGKUP</strong></h6>
                        <p class="text-justify">
                            PIHAK PERTAMA menunjuk PIHAK KEDUA untuk melakukan pekerjaan sesuai kebutuhan PIHAK
                            PERTAMA dalam melakukan operasional dan fungsi perusahaan dalam memberikan memenuhi
                            kebutuhan pelanggan, pembeli dan pemegang saham perusahaan.
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <h6><strong>PASAL 3. HAK & KEWAJIBAN PARA PIHAK</strong></h6>
                        <ul class="text-justify custom-alphabet-list">
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
                        <p class="text-justify">
                            PARA PIHAK sepakat bahwa segala tindakan yang menyebabkan kerugian pada perusahaan, harus
                            dipertanggungjawabkan, walaupun hubungan kerja telah berakhir ataupun diakhiri secara
                            sepihak, dan menunjuk jalur hukum untuk diselesaikan di Pengadilan Negeri Jakarta Barat.
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <h6><strong>PASAL 5. JAM KERJA & CARA BEKERJA</strong></h6>
                        <ul class="text-justify custom-alphabet-list">
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
                        <p class="text-justify">
                            PARA PIHAK sepakat perjanjian kerja ini bersifat tetap, dan apabila terdapat hal-hal yang belum dijelaskan akan
                            ditentukan di peraturan perusahaan atau surat keputusan manajemen. Perjanjian ini berlaku hingga salah satu
                            pihak mengakhirinya.
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
                        @else
                        <div style="height: 150px;"></div> <!-- Empty space if no signature -->
                        @endif
                        <p class="sparator">_________________________</p>
                        <p>{{ $company['director'] ?? "" }}</p>
                    </div>
                    <div class="col-6 text-center">
                        <p><strong>PIHAK KEDUA</strong></p>
                        @if($letterSubmission->status !== 0)
                        <img src="{{ Storage::url($fieldData['signature_image'] ?? '' ) }}" class="img-fluid"
                            alt="Signature" style="height:150px">
                        @else
                        <div style="height: 150px;"></div> <!-- Empty space if no signature -->
                        @endif
                        <p class="sparator">_________________________</p>
                        <p>{{ $letterSubmission->user->name }}</p>
                    </div>
                </div>
            </div>

            <div class="page-break"></div>

            <div class="col-12 justify-content-center align-items-center header">
                <div class="header">
                    <h3>SURAT KEPUTUSAN MANAJEMEN</h3>
                </div>
                <div class="sub-header">
                    Perihal: {{ "Pengangkatan Pegawai" }}
                </div>
            </div>
            
            <!-- Information Table -->
            <table class="table table-bordered">
                <tr>
                    <th>Nama Lengkap</th>
                    <td>{{ $letterSubmission->user->name ?? "" }}</td>
                </tr>
                <tr>
                    <th>Jabatan / Fungsi / Keahlian</th>
                    <td>{{ isset($positionNew)? $positionNew->name : "" }}</td>
                </tr>
                <tr>
                    <th>Gaji Bulanan </th>
                    <td>{{'Rp. '.number_format($fieldData['salary'],0,',','.') ?? "" }}</td>
                </tr>
                <tr>
                    <th>Tanggal Perhitungan Gaji </th>
                    <td>{{ $fieldData['salary_date'] ?? "" }}</td>
                </tr>
                <tr>
                    <th>Jam Kerja</th>
                    <td>{{ $fieldData['working_hours'] ?? ''  }}</td>
                </tr>
                <tr>
                    <th>Penempatan</th>
                    <td>{{ $fieldData['work_location'] ?? ''  }}</td>
                </tr>
            </table>
            
            <div class="col-12 mt-3">
                <div class="row">
                    <p class="text-justify">
                        Pengangkatan ini berlaku efektif sejak di tanda-tangani, pegawai yang dipromosikan akan menduduki posisi Personal Assistant dalam perusahaan. Dan tunduk pada Undang - Undang Perusahaan Terbatas, Nomor 40 tahun 2007 yang mengikatkan kewenangan dan tanggung jawabnya.
                    </p>
                </div>
            </div>
            
            <table class="table table-bordered">
                <tr>
                    <td>
                        <p><strong>Fungsi Manajemen: {{ $user->last_position_now ? $user->last_position_now->position->name : "" }}</strong></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p><strong>Tanggung Jawab Pekerjaan:</strong></p>
                        {!! $fieldData['job_responsibilities'] ?? "" !!}
                    </td>
                </tr>
            </table>
            <!-- Footer -->
            <div class="text-left mt-4">
                <p><strong>Jakarta, {{ $date ?? "" }}</strong></p>
            </div>

            <!-- KTP and Signature Section -->
            <div class="row">
                <div class="col-6 text-center">
                    @if($letterSubmission->is_approved == 1)
                    <img src="{{ asset('logo/paraf.png') }}" class="img-fluid" alt="Signature" style="height:150px">
                    @else
                    <div style="height: 150px;"></div> <!-- Empty space if no signature -->
                    @endif
                    <p class="sparator">_________________________</p>
                    <p>{{ $company['director'] ?? "" }}</p>
                </div>
                <div class="col-6 text-center">
                    @if($letterSubmission->status !== 0)
                    <img src="{{ Storage::url($fieldData['signature_image'] ?? '' ) }}" class="img-fluid"
                        alt="Signature" style="height:150px">
                    @else
                    <div style="height: 150px;"></div> <!-- Empty space if no signature -->
                    @endif
                    <p class="sparator">_________________________</p>
                    <p>{{ $letterSubmission->user->name }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <p><strong>{{ $company['director'] ?? "" }}</strong></p>
                </div>
                <div class="col">
                    <p><strong>{{ $letterSubmission->user->name ?? "" }}</strong></p>
                </div>
            </div>

            <!-- No KTP and NPWP -->
            <div class="mt-3">
                <table class="table table-bordered">
                    <tr>
                        <th>
                            Foto KTP
                        </th>
                        <td>
                            <img src="{{ Storage::url($letterSubmission->user->id_card_image) }}" alt="Foto KTP" class="img-fluid" style="max-width: 150px;">
                        </td>
                        </td>
                    </tr>
                    <tr>
                        <th>No KTP</th>
                        <td>{{ $letterSubmission->user->id_card ?? '' }}</td>
                    </tr>
                    <tr>
                        <th>No NPWP</th>
                        <td>{{ $letterSubmission->user->npwp_number ?? " " }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Download Button -->
<div class="col-12 text-center mt-3">
    <!-- Download Button -->
    <button type="button" id="downloadQuote" class="btn btn-success mb-3">
        <i class="fa fa-file-pdf"></i> {{ __('Download') }}
    </button>

    <!-- Approve/Decline Form -->
    @if(!isset($letterSubmission->is_approved))
    @if(is_null($letterSubmission->status) || $letterSubmission->status == 1)
    @canAccess('approvement', 'letter_submissions')
    <form action="{{ route('letter-submission.approvement') }}" method="POST" id="bulk-action-form" class="d-inline">
        @csrf
        @method('PATCH')
        <input type="hidden" name="selected_ids[]" value="{{ $letterSubmission->id }}">

        <div class="d-flex justify-content-center">
            <!-- Approve Button -->
            <button type="submit" class="btn btn-success mx-2" name="action" value="approve">
                <i class="fa fa-check"></i> Approve
            </button>
            <!-- Decline Button -->
            <button type="submit" class="btn btn-danger mx-2" name="action" value="decline">
                <i class="fa fa-times"></i> Decline
            </button>
        </div>
    </form>
    @endcanAccess
    @endif
    @endif
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
.custom-alphabet-list {
    list-style: none; /* Remove default bullet points */
    counter-reset: alphabet-counter; /* Initialize the counter */
}

.custom-alphabet-list li {
    counter-increment: alphabet-counter; /* Increment the counter for each list item */
}

.custom-alphabet-list li::before {
    content: counter(alphabet-counter, lower-alpha) ") "; /* Add alphabetic counter followed by a closing parenthesis */
    font-weight: bold; /* Make the alphabet bold if needed */
}

.sparator 
{
    margin-bottom: 0px;
}
.table td {
    /* width: 50%; */
    padding-top: 0rem !important;
    
}
.table th {
    width: 50%;
    padding-top: 0rem !important;
}

.table-top td
{
    padding-left:0rem !important;
    /* max-width: 30% !important; */
}

.table-top th
{
    padding-left:0rem !important;
    /* max-width: 30%; */
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
.text-justify {
    text-align: justify;
}
.page-break {
    page-break-inside: always;
}
/* Page break styles */
@media print {
    .page-break {
        page-break-before: always; /* forces the next element to start on a new page */
    }
}
.card-body {
    padding-left: 10rem;
    padding-right: 10rem;
}
</style>
<style>
    .table-bordered td, .table-bordered th {
        border: 1px solid black !important;
    }
    .table td {
        /* padding: 0rem !important; */
        /* padding-top: 1rem !important; */
        /* padding-left: 0.2rem !important; */
    }

    .table th {
        /* padding: 0rem !important; */
        /* padding-top: 1rem !important; */
        /* padding-left: 0.2rem !important; */

    }

    .header, .sub-header {
        text-align: center;
    }

    .header {
        font-weight: bold;
    }

    .sub-header {
        font-weight: bold;
        margin-bottom: 20px;
    }

    .info-table th, .info-table td {
        padding: 10px;
    }


    th
    {
        font-weight: bold;
    }
    
    h6{
        font-weight: bold;
    }
</style>
@endsection