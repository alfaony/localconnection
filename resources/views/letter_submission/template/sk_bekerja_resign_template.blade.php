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
                    <h4 class="text-center"><strong>SURAT PENGUNDURAN DIRI</strong></h4>
                    <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
                </div>
                
                <p>Kepada Yth,<br>
                HRD/Director<br>
                {{ $company['name'] }}</p>
        
                <p>Saya yang bertanda tangan di bawah ini :</p>
        
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td style="width: 150px;"><strong>Nama</strong></td>
                                <td>: {{ $user->name ?? "" }}</td>
                            </tr>
                            <tr>
                                <td><strong>NIK</strong></td>
                                <td>: {{ $user->id_card ?? "" }}</td>
                            </tr>
                            <tr>
                                <td><strong>Jabatan</strong></td>
                                <td>: {{ $user->last_position_now ? $user->last_position_now->position->name : "" }}</td>
                            </tr>
                            <tr>
                                <td><strong>Perusahaan</strong></td>
                                <td>: {{ $company['address'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
        
                <p class="text-justify">Menyatakan dengan sesungguhnya bahwa mulai tanggal {{ isset($fieldData['end_date']) ? \Carbon\Carbon::parse($fieldData['end_date'])->locale('id')->translatedFormat('d F Y') : ""}}  saya mengajukan permohonan untuk mengundurkan diri sebagai karyawan {{ $company['name'] ?? "" }}</p>
        
                <p class="text-justify">Ucapan terima kasih yang sebesar-besarnya saya sampaikan atas kesempatan yang diberikan untuk bekerja di {{ $company['name'] ?? "" }}</p>
        
                <p class="text-justify">Melalui surat ini saya memohon maaf kepada segenap manajemen dan karyawan {{ $company['name'] ?? "" }} jika terdapat kesalahan yang saya perbuat selama bekerja. Besar harapan saya {{ $company['name'] ?? "" }} akan terus berkembang dan maju.</p>
        
                <div class="col-4 offset-8 mt-4">
                    <p>Jakarta, {{ $date ?? "" }}</p>
                    <p>Hormat Saya,</p>
                    @if(isset($fieldData['signature_image'])    )
                        <img src="{{ s3_asset(true,10,$fieldData['signature_image'] ?? '' ) }}" class="img-fluid"
                            alt="Signature" style="height:150px">
                    @endif
                    <p><strong>{{ $user->name ?? "" }}</strong></p>
                    <p>NIK: {{ $user->id_card ?? "" }}</p>
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
