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
                <h4 class="text-center mb-0"><strong>SURAT PERINGATAN</strong></h4>
                @if($letterSubmission->number_result)
                <strong>
                    <p class="text-center">No. {{$letterSubmission->letter_number}}/HR/SP/{{ \Carbon\Carbon::parse($letterSubmission->created_at)->format('d/m/Y') }}</p>
                </strong>
                @endif
            </div>

            <div class="col-12 mt-4 mb-3">
                <p>Kepada Yth,<br>
                Sdr/i. {{ $user->name }}<br>
                Di tempat</p>
            </div>

            <div class="col-12 mb-3">
                <p>Dengan hormat,</p>
            </div>

            <div class="col-12">
                <p class="text-justify">
                    Surat peringatan ini kami terbitkan karena Saudara/i {{ $user->name }} telah melakukan kelalaian dalam menjalankan tanggung jawab sebagai bagian dari {!! isset($fieldData['part_of']) ? $fieldData['part_of'] : "" !!} di <strong>{{ $company['name'] ?? "" }}</strong>.
                </p>
            </div>

            <div class="col-12 mb-3">
                <p>Adapun kelalaian yang dimaksud antara lain:</p>
                <div style="padding-left: 20px;">
                    {!! isset($fieldData['job_mistake']) ? $fieldData['job_mistake'] : "" !!}
                </div>
            </div>

            <div class="col-12">
                <p class="text-justify">
                    Hal-hal tersebut berdampak pada hasil kerja tim secara keseluruhan dan tidak sejalan dengan standar kerja yang telah disepakati bersama di perusahaan.
                </p>
            </div>

            <div class="col-12">
                <p class="text-justify">
                    Oleh karena itu, perusahaan memberikan <strong>
                    {{ isset($fieldData['type_sp']) ? $fieldData['type_sp'] : "" }}
                    </strong> kepada Saudara/i sebagai bentuk pembinaan agar dapat meningkatkan kedisiplinan dan kualitas kerja ke depannya.
                </p>
            </div>

            <div class="col-12">
                <p class="text-justify">
                    Demikian surat peringatan ini kami buat agar dapat dijadikan perhatian serius oleh yang bersangkutan. Apabila dalam waktu ke depan tidak ada perbaikan, maka perusahaan berhak mengambil tindakan lanjutan sesuai dengan ketentuan yang berlaku.
                </p>
            </div>

            <div class="col-12 mt-4">
                <div class="d-flex justify-content-start">
                    <p>Jakarta, {{ $date ?? "" }}</p>
                </div>
            </div>

            <div class="col-12 ">
                @if($letterSubmission->is_approved == 1)
                <img src="{{ asset('logo/paraf.png') }}" class="img-fluid mb-2" alt="Signature" style="height:150px">
                @else
                <div style="height:100px;"></div>
                @endif
                <p class="mb-0"><strong>{{ $company['director'] ?? "" }}</strong></p>
                <p>CEO {{ $company['name'] ?? "" }}</p>
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
<script>
    $(document).ready(function() {
        $("#downloadQuote").click(function(e) {
            e.preventDefault();
            printDocument();
        });
    });

    function printDocument() {
        let name = "Surat_Peringatan_" + "{{ $user->name }}";
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