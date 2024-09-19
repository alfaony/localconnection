@extends('adminlte::page')
@php
    $fieldData = $letterSubmission->convert_field;
    $user = $letterSubmission->user;
@endphp

@section('content')
<div class="container">
    <div class="card scrollable-div" id="printThis">
        <div class="card-body">
            <div class="col-12 justify-content-center align-items-center">
                <h4 class="text-center"><strong>SURAT KETERANGAN KERJA</strong></h4>
                <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
            </div>

            <div class="col-12 justify-content-center align-items-center mt-4 mb-4">
                <p class="text-justify">Saya yang bertandatangan di bawah ini :</p>
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
                                Dengan ini menerangkan bahwa :
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
            
            <div class="col-12">
                <p class="text-justify"> 
                    Telah bekerja di perusahaan kami, {{ $company['name'] ?? "" }}, sejak tgl {{ $user->first_position ? \Carbon\Carbon::parse($user->first_position->start_date)->locale('id')->translatedFormat('d F Y') : "" }} 
                    s/d {{ isset($fieldData['end_date']) ? \Carbon\Carbon::parse($fieldData['end_date'])->locale('id')->translatedFormat('d F Y') : "saat ini" }} dengan posisi sebagai {{ isset($positionOld)? $positionOld->name : "" }}. Selama bekerja di perusahaan kami, yang bersangkutan telah bekerja dengan baik sesuai SOP perusahaan dan tidak pernah terlibat dalam tindakan yang dapat merugikan perusahaan.
                </p>
                <p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>
            </div>

            <div class="col-12">
                <div class="d-flex justify-content-start">
                    <p>Jakarta, {{ $date ?? "" }} </p>
                </div>
            </div>
            <div class="col-12">
                <p>Tertanda,</p>
                <div class="col-6 text-left">
                    @if($letterSubmission->is_approved == 1)
                    <img src="{{ asset('logo/paraf.png') }}" class="img-fluid" alt="Signature" style="height:150px">
                    <p>{{ $company['director'] ?? "" }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Download Button -->
<!-- Download Button -->
<div class="col-12 text-center mt-3">
    <!-- Download Button -->
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
            <button type="submit" class="btn btn-danger mx-2" name="action" value="decline">
                <i class="fa fa-times"></i> Decline
            </button>
        </div>
    </form>
    @endcanAccess
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
    let name = "Pengantar Kerja_"+"{{ $user->name }}";
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
