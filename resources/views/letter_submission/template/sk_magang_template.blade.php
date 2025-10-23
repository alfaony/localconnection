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
                <h4 class="text-center"><strong>PERJANJIAN KERJA MAGANG</strong></h4>
                <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
            </div>

            <div class="col-12 justify-content-center align-items-center mt-4 mb-4">
                <p class="text-justify">Pada Hari {{ $startDate ? $startDate->translatedFormat('l, d F Y') : "" }} bertempat di Jakarta, telah ditanda tangani perjanjian kerja magang antara:</p>
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
                                <p class="text-justify">Bertindak atas perusahaan yang mempekerjakan, selanjutnya disebut PIHAK PERTAMA.</p>
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
                                <p class="text-justify">Bertindak atas nama pribadi, sebagai pekerja / staff yang dipekerjakan, selanjutnya disebut sebagai PIHAK KEDUA.</p>
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
                            PARA PIHAK sepakat untuk menjalin hubungan kerja magang, dimana PIHAK PERTAMA memberikan
                            kesempatan magang kepada PIHAK KEDUA sebagai
                            {{ isset($positionNew) ? $positionNew->name : "" }}.
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <h6><strong>PASAL 2. RUANG LINGKUP</strong></h6>
                        <p class="text-justify">
                            PIHAK PERTAMA menunjuk PIHAK KEDUA untuk melakukan pekerjaan magang sesuai kebutuhan PIHAK
                            PERTAMA dalam melakukan operasional dan fungsi perusahaan terkait desain antarmuka pengguna
                            dan pengalaman pengguna.
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <h6><strong>PASAL 3. HAK & KEWAJIBAN PARA PIHAK</strong></h6>
                        <ul class="text-justify custom-alphabet-list">
                            <li>PIHAK KEDUA berhak menerima kompensasi atas tunjangan magang sesuai kesepakatan.</li>
                            <li>PIHAK KEDUA berhak mendapatkan bimbingan dan pelatihan dari PIHAK PERTAMA.</li>
                            <li>PIHAK KEDUA wajib mentaati seluruh peraturan yang ditetapkan PIHAK PERTAMA.</li>
                            <li>PIHAK KEDUA wajib menjalankan tugas dan pekerjaan dengan bertanggung jawab, dan memberikan hasil
                                yang maksimal.</li>
                            <li>PIHAK KEDUA wajib merahasiakan seluruh informasi perusahaan yang diterima maupun diketahui, yang
                                dapat merugikan PIHAK PERTAMA apabila diketahui pihak lain.</li>
                            <li>PIHAK PERTAMA berhak menetapkan peraturan perusahaan, strategi perusahaan, tugas dan
                                tanggung jawab yang diberikan kepada PIHAK KEDUA.</li>
                            <li>PIHAK PERTAMA berhak mendapatkan perlindungan dan jaminan dari tindakan kecurangan,
                                pencurian, persaingan tidak sehat, dan tindakan melawan hukum yang terjadi akibat
                                tindakan PIHAK KEDUA.</li>
                            <li>PIHAK PERTAMA wajib memberikan peringatan sebelum melakukan pemutusan hubungan magang
                                sepihak terhadap PIHAK KEDUA.</li>
                            <li>PIHAK KEDUA wajib memberikan keterangan pengunduran diri minimal 1bulan sebelum
                                mengundurkan diri dari program magang.</li>
                        </ul>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <h6><strong>PASAL 4. TANGGUNG JAWAB HUKUM</strong></h6>
                        <p class="text-justify">
                            PARA PIHAK sepakat bahwa segala tindakan yang menyebabkan kerugian pada perusahaan harus
                            dipertanggungjawabkan walaupun hubungan magang telah berakhir ataupun diakhiri secara
                            sepihak dan menunjuk jalur hukum untuk diselesaikan di pengadilan negeri Jakarta Barat.
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <h6><strong>PASAL 5. JAM KERJA & CARA BEKERJA</strong></h6>
                        <ul class="text-justify custom-alphabet-list">
                            <li>Pekerjaan diselesaikan dan dikerjakan baik di tempat kerja yang ditentukan sesuai kebutuhan perusahaan dengan penjadwalan kerja sesuai kebutuhan dan kondisi. Disepakati penanggalan merah adalah libur.</li>
                            <li>PIHAK KEDUA wajib bekerja di tempat yang ditentukan sesuai kebutuhan perusahaan.</li>
                            <li>PIHAK KEDUA wajib melakukan absensi secara digital menggunakan aplikasi, dan aktif dalam komunikasi untuk menjaga fungsi dan tanggung jawab pekerjaan berjalan dengan baik.</li>
                            <li>PIHAK PERTAMA dan PIHAK KEDUA sepakat untuk melakukan kalkulasi biaya operasional yang timbul akibat pekerjaan, seperti transportasi, parkir, dan lainnya dalam surat keputusan yang terpisah sesuai fungsi pekerjaan masing-masing.</li>
                            <li>Jumlah jam bekerja adalah 5 hari kerja, dari pukul 08.00 – 17.00 wajib dipenuhi.</li>
                            <li>Pelaporan atas pekerjaan magang wajib dilakukan dalam bentuk yang dapat ditelusuri, terdokumentasi, dan dipertanggungjawabkan oleh PIHAK KEDUA.</li>
                            <li>Keamanan data, peralatan, dokumen dan informasi yang dimiliki, dibawa pulang, digunakan, dan diakses oleh PIHAK KEDUA wajib dijaga sebaik-baiknya, dan bertanggung jawab penuh apabila terjadi kelalaian dalam memastikan keamanan hal disebutkan di atas.</li>
                        </ul>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <h6><strong>PASAL 6. JANGKA WAKTU</strong></h6>
                        <p class="text-justify">
                            PARA PIHAK sepakat perjanjian kerja magang ini berlaku selama 3 bulan dan dapat diperpanjang sesuai kesepakatan kedua belah pihak. Perjanjian ini berlaku hingga salah satu pihak mengakhirinya.
                        </p>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-6 text-center">
                        <p>Jakarta, {{ $startDate ? $startDate->translatedFormat('d F Y') : "" }}</p>
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
                        <img src="{{ s3_asset(true,10,$fieldData['signature_image'] ?? '' ) }}" class="img-fluid" alt="Signature" style="height:150px">
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
                    <h4 class="text-center"><strong>SURAT KEPUTUSAN MANAJEMEN</strong></h4>
                    <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
                </div>
                <div class="sub-header">
                    Perihal: {{ "Penerimaan Magang" }}
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
                    <th>Kompensasi Magang</th>
                    <td>{{ 'Rp. '.number_format($fieldData['salary'],0,',','.') ?? "" }}</td>
                </tr>
                <tr>
                    <th>Tanggal Pembayaran Kompensasi Magang</th>
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
                        Perjanjian ini berlaku efektif sejak ditandatangani, dan peserta magang akan menjalankan peran sebagai {{ isset($positionNew)? $positionNew->name : "" }} selama masa magang. Peserta magang tunduk pada ketentuan yang berlaku dalam perusahaan serta aturan dan tanggung jawab yang telah ditetapkan.
                    </p>
                </div>
            </div>
            
            <table class="table table-bordered">
                <tr>
                    <td>
                        <p><strong>Fungsi Manajemen: {{ isset($positionNew)? $positionNew->name : "" }}</strong></p>
                    </td>
                </tr>
                <tr>
                    <td>
                        <p><strong>Tanggung Jawab Pekerjaan:</strong></p>
                        {!! $fieldData['description_task'] ?? "" !!}
                    </td>
                </tr>
            </table>
            <!-- Footer -->
            <div class="text-left mt-4">
                <p><strong>Jakarta, {{ $startDate ? $startDate->translatedFormat('d F Y') : "" }}</strong></p>
            </div>
    
            <!-- KTP and Signature Section -->
            <div class="d-flex justify-content-start">
                <div class="coltext-center">
                    @if($letterSubmission->is_approved == 1)
                    <img src="{{ asset('logo/paraf.png') }}" class="img-fluid" alt="Signature" style="height:150px">
                    @else
                    <div style="height: 150px;"></div> <!-- Empty space if no signature -->
                    @endif
                    <p><strong>{{ $company['director'] ?? "" }}</strong></p>
                </div>
                <div class="col text-center">
                    @if($letterSubmission->status !== 0)
                    <img src="{{ s3_asset(true,10,$fieldData['signature_image'] ?? '' ) }}" class="img-fluid"
                        alt="Signature" style="height:150px">
                    @else
                    <div style="height: 150px;"></div> <!-- Empty space if no signature -->
                    @endif
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
                            <img src="{{ s3_asset(true,10,$letterSubmission->user->id_card_image) }}" alt="Foto KTP" class="img-fluid" style="max-width: 150px;">
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
            <button type="button" class="btn btn-danger mx-2" data-bs-toggle="modal" data-bs-target="#declineModal">
                <i class="fa fa-times"></i> Decline
            </button>
        </div>
    </form>
    @endcanAccess
    @endif
    @endif
</div>
<!-- Modal for inputting the reason for rejection -->
<div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="declineModalLabel">Alasan Penolakan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">Tutup</button>
      </div>
      <div class="modal-body">
        <form id="declineForm" action="{{ route('letter-submission.approvement') }}" method="POST">
          @csrf
          @method('PATCH')
          <input type="hidden" name="action" value="decline">
          <input type="hidden" name="selected_ids[]" value="{{ $letterSubmission->id }}">

          <div class="form-group">
            <label for="modal-reason">Alasan Penolakan:</label>
            <input type="text" name="notes[{{ $letterSubmission->id }}]" id="modal-reason" class="form-control" required>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            <button type="submit" class="btn btn-danger">Decline</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>
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
.table th {
    width: 50%;
}

.table-top td
{
    padding-left:0rem !important;
    max-width: 30%;
}

.table-top th
{
    padding-left:0rem !important;
    max-width: 30%;
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
    .page-break 
    {
        page-break-before: always; /* forces the next element to start on a new page */
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
</style>
<style>
    .table-bordered td, .table-bordered th {
        border: 1px solid black !important;
    }
    .table td {
        /* padding: 0rem !important; */
        padding-top: 0rem !important;
        /* padding-left: 0.2rem !important; */
    }

    .table th {
        /* padding: 0rem !important; */
        padding-top: 0rem !important;
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
    .card-body {
        padding-left: 10rem;
        padding-right: 10rem;
    }
</style>
@endsection