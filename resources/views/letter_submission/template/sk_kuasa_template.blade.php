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
                <h4 class="text-center"><strong>SURAT KUASA</strong></h4>
                <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
            </div>

            <div class="col-12 justify-content-center align-items-center mt-4 mb-4">
                <p>Saya yang bertandatangan di bawah ini :</p>
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
                                Selanjutnya disebut PEMBERI KUASA               
                            </td>
                        </tr>
                        <tr>
                            <td class="mt-5" style="
                                    height: 1rem;
                                ">

                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                Dengan ini memberikan kuasa penuh kepada :
                            </td>
                        </tr>
                        <tr>
                            <td class="mt-5" style="
                                    height: 1rem;
                                ">

                            </td>
                        </tr>
                        <tr>
                            <td>
                                Nama</td>
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
                                Selanjutnya disebut PENERIMA KUASA
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-12">
                <p class="text-justify mb-0"> 
                    Penerima kuasa mewakili pemberi kuasa untuk :
                </p>
                {!! $fieldData['description'] ?? ''  !!}
            </div>
            <div class="col-12">
                <p class="text-justify"> 
                    Demikian Surat kuasa ini dibuat dengan sebenarnya, untuk dapat dipergunakan sebagaimana mestinya 
                </p>
            </div>

                <div class="row mt-4">
                    <div class="col-6 offset-6 text-center">
                        <p class="mb-0">Jakarta, {{ $dateCustom ?? '' }}</p>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-6 text-center">
                        Yang menerima Kuasa,
                    </div>
                    <div class="col-6 text-center pr-5">
                        Yang memberi Kuasa,
                    </div>
                </div>
                <div class="row">
                     <div class="col-6 text-center">
                        @if($letterSubmission->status !== 0)
                        <img src="{{ Storage::url($fieldData['signature_image'] ?? '' ) }}" class="img-fluid"
                            alt="Signature" style="height:150px">
                        @else
                        <div style="height: 150px;"></div> <!-- Empty space if no signature -->
                        @endif
                        <p>{{ $letterSubmission->user->name }}</p>
                    </div>
                    <div class="col-6 text-center">
                        @if($letterSubmission->is_approved == 1)
                        <img src="{{ asset('logo/paraf.png') }}" class="img-fluid" alt="Signature" style="height:150px">
                        @else
                        <div style="height: 150px;"></div> <!-- Empty space if no signature -->
                        @endif
                        <p>{{ $company['director'] ?? "" }}</p>
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
