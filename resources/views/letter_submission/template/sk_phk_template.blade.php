@extends('adminlte::page')
@php
    $fieldData = $letterSubmission->convert_field;
    $user = $letterSubmission->user;
@endphp

@section('content')
    <div class="container">
        <div class="card scrollable-div" id="printThis">
            <div class="card-body">
                <div class="text-center mb-4">
                    <h4 class="text-center"><strong>SURAT PEMUTUSAN HUBUNGAN KERJA</strong></h4>
                    <h6 class="text-center"><strong>hal: Surat Pemutusan Hubungan Kerja</strong></h6>
                    <p class="text-center">No. {{$letterSubmission->letter_number}}/HR/PHK/{{ \Carbon\Carbon::parse($letterSubmission->created_at)->format('d/m/Y') }}</p>
                </div>
                
                <p>Kepada Yth,<br>
                {{ $user->name ?? "" }}</p>
                
                <p>Ditempat</p>
        
                <p>Dengan Hormat,</p>
                <p class="text-justify">
                    Bersama surat ini, kami mohon maaf sebesar-besarnya karena tidak dapat melanjutkan kontrak Saudara dengan PT. Gema Teknologi Cahaya Gemilang. Hal itu dikarenakan kinerja Saudara selama masa kerja tidak memenuhi ekspektasi dalam melakukan pekerjaan. Namun sebagai apresiasi kinerja Saudara selama ini, akan diberikan gaji terakhir dan ditambah dengan pesangon 3x gaji yang akan diterima setelah melakukan proses serah terima pekerjaan dengan atasan. Kami juga akan memberikan paklaring dan surat rekomendasi kepada Saudara.
                </p>
        
                <p class="text-justify">
                    Demikianlah surat pemutusan hubungan kerja ini juga dibuat agar kedua pihak sepakat untuk membebaskan pihak lain dari segala bentuk tuntutan hukum di kemudian hari terkecuali tindakan pidana. Semoga Saudara dapat memaklumi dan mendapat pekerjaan pengganti yang sesuai, terima kasih.
                </p>        
                <div class="row">
                    <div class="col-4 mt-4">
                        <p>Jakarta, {{ $date ?? "" }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="ml-4 col-3">
                        @if(isset($fieldData['signature_image']))
                            <img src="{{ asset('logo/paraf.png') }}" class="img-fluid" alt="Signature" style="height:150px">
                        @endif
                    </div>
                    <div class="offset-3 col-4">
                        @if(isset($fieldData['signature_image'])    )
                            <img src="{{ Storage::url($fieldData['signature_image'] ?? '' ) }}" class="img-fluid"
                                alt="Signature" style="height:150px">
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-5 text-justify">
                        <p class="mb-0">{{ isset($company['director']) ? $company['director'] : "" }}</p>
                        <p class="mt-0">
                            {{ isset($company['name']) ? $company['name'] : "" }}
                        </p>
                    </div>
                    <div class="offset-2 col-5 text-justify">
                        <p class="mb-0">{{ $user->name ?? "" }}</p>
                        <p class="mt-0">
                            {{ $user->last_position_now ? $user->last_position_now->position->name: "" }}
                        </p>
                    </div>
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
    .scrollable-div 
    {
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
