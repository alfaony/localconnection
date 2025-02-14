@extends('adminlte::page')
@php
$fieldData = $letterSubmission->convert_field;
$user = $letterSubmission->user;
@endphp

@section('content')
<div class="container">
    <div class="card scrollable-div" id="printThis">
        <div class="card-body">
            <div class="text-center">
                <h2>SURAT KEPUTUSAN LEMBUR</h2>
                @if(isset($fieldData['no_surat']))
                <p class="mt-3"><strong>Nomor:</strong> {{ (isset($fieldData['no_surat'])) ? $fieldData['no_surat'] : '' }}</p>
                @endif
            </div>
            <div class="text-left mb-4">
                <p><strong>Tentang:</strong> Penetapan Lembur Karyawan</p>
                <p><strong>Direktur</strong> {{ $company['name'] ?? "" }}</p>
            </div>

            <div class="content">
                <p><strong>Menimbang:</strong></p>
                <ul>
                    <li>Bahwa dalam rangka memenuhi target kerja dan penyelesaian tugas yang mendesak, diperlukan kerja
                        lembur bagi beberapa karyawan;</li>
                    <li>Bahwa perlu ditetapkan keputusan mengenai lembur karyawan agar pelaksanaannya berjalan sesuai
                        dengan peraturan yang berlaku;</li>
                </ul>

                <p><strong>Mengingat:</strong></p>
                <ul>
                    <li>Undang-Undang Ketenagakerjaan No. 13 Tahun 2003 dan peraturan perundang-undangan terkait;</li>
                    <li>Peraturan Perusahaan tentang jam kerja dan lembur;</li>
                    <li>Kebijakan internal perusahaan terkait dengan kompensasi lembur.</li>
                </ul>

                <p><strong>MEMUTUSKAN</strong></p>
                <p><strong>Menetapkan:</strong></p>

                <p><strong>Pasal 1</strong> Menugaskan kerja lembur kepada karyawan yang namanya tercantum dalam
                    lampiran surat keputusan ini.</p>
                <p><strong>Pasal 2</strong> Pelaksanaan lembur akan dilakukan pada <br>
                    Tanggal {{ isset($fieldData['tanggal_lembur']) ? \Carbon\Carbon::parse($fieldData['tanggal_lembur'])->format('d/m/Y') : '' }} dari Pukul {{ isset($fieldData['jam_lembur_start']) ? $fieldData['jam_lembur_start'] : '' }} hingga Pukul {{ isset($fieldData['jam_lembur_end']) ? $fieldData['jam_lembur_end'] : '' }}, <br>
                    dengan durasi lembur maksimal <strong><u>8 Jam</u></strong> per hari.
                </p>
                <p><strong>Pasal 3</strong> Setiap karyawan yang melaksanakan lembur akan mendapatkan kompensasi sesuai
                    dengan peraturan perusahaan yang berlaku yaitu maksimal Rp 200.000 / hari.</p>
            </div>

            <div class="footer text-left mt-5">
                <p><strong>Jakarta, {{ $date ?? "" }}</strong></p>
                @if($letterSubmission->is_approved == 1)
                <img src="{{ asset('logo/paraf.png') }}" class="img-fluid" alt="Signature" style="height:150px">
                @else
                <div style="height: 150px;"></div> <!-- Empty space if no signature -->
                @endif
                <p class="mb-0 mt-2">{{ $company['director'] ?? "" }}</p>
                <p>Direktur</p>
            </div>
        </div>
    </div>

    <!-- Print Surat Kuasa -->
</div>

<!-- Download Button -->
<!-- Download Button -->
<div class="col-12 text-center mt-3">
    <!-- Download Button -->
    @if(isset($fieldData['file']))
    <a href="{{ Storage::url($fieldData['file']) }}" class="btn btn-primary mb-3" target="_blank"><i
            class="fa fa-download"></i> File Yang Dikuasakan</a>
    @endif
    <button type="button" id="downloadQuote" class="btn btn-success mb-3">
        <i class="fa fa-file-pdf"></i> {{ __('Download') }}
    </button>

    <!-- Approve/Decline Form -->
    @if(!isset($letterSubmission->is_approved))
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
                        <input type="text" name="notes[{{ $letterSubmission->id }}]" id="modal-reason"
                            class="form-control" required>
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
    let name = "Pengantar Kerja_" + "{{ $user->name }}";
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
.container {
    /* max-width: 700px; */
    margin: 0 auto;
    padding: 20px;
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

.signature {
    margin-top: 50px;
}

.signature p {
    margin-bottom: 5px;
}
</style>
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

.text-justify {
    text-align: justify;
}

.card-body {
    padding-left: 10rem;
    padding-right: 10rem;
}
</style>
@endsection