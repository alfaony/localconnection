
@extends('adminlte::page')
@php
    $fieldData = $letterSubmission->convert_field;
    $user = $letterSubmission->user;
@endphp
@section('content')
<div class="container">
    <div class="card scrollable-div" id="printThis">
        <div class="card-body">
            <div class="col-12 justify-content-center align-items-center header">
                <div class="header">
                    <h4 class="text-center"><strong>SURAT KETERANGAN KERJA</strong></h4>
                    <h6 class="text-center"><strong>{{ $company['name'] ?? "" }}</strong></h6>
                </div>
                <div class="sub-header">
                    Perihal: {{ $fieldData['perihal'] ?? "" }}
                </div>
            </div>
            
            <!-- Information Table -->
            <table class="table table-bordered">
                <tr>
                    <th>Nama Lengkap</th>
                    <td>{{ $letterSubmission->user->name ?? "" }}</td>
                </tr>
                <tr>
                    <th>Jabatan Sebelumnya</th>
                    <td>
                        {{ $positionOld->name ?? "" }} 
                    </td>
                </tr>
                <tr>
                    <th>Jabatan Terbaru</th>
                    <td>
                    {{ $positionNew->name ?? "" }} 
                    </td>
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
                        Surat Keputusan ini berlaku efektif sejak ditanda-tangani, pegawai yang dipromosikan akan menduduki posisi baru {{ $positionNew->name }} dalam perusahaan. Dan tunduk pada Undang-Undang Perusahaan Terbatas, Nomor 40 tahun 2007 yang mengikatkan kewenangan dan tanggung jawabnya.
                    </p>
                </div>
            </div>
            
            <table class="table table-bordered">
                <tr>
                    <td>
                        <p><strong>Fungsi Manajemen: {{ $positionNew->name ?? "" }}</strong></p>
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
                    <img src="{{ Storage::url($fieldData['signature_image'] ?? '' ) }}" class="img-fluid"
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
                        <td>
                            Foto KTP
                        </td>
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
    @canAccess('approvement', 'letter_submissions')
    <form action="{{ route('letter-submission.approvement') }}" method="POST" id="bulk-action-form" class="d-inline">
        @csrf
        @method('PATCH')
        <input type="hidden" name="selected_ids[]" value="{{ $letterSubmission->id }}">
        <div class="form-group">
            <label for="reason">Alasan Penolakan:</label>
            <input type="text" name="notes[{{ $letterSubmission->id }}]" class="form-control" value="{{ $letterSubmission->reason ?? '' }}">
        </div>

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
    .table-bordered td, .table-bordered th {
        border: 1px solid black !important;
    }
    .table td {
        /* padding: 0rem !important; */
        /* padding-top: 1rem !important; */
        /* padding-left: 0.2rem !important; */
        width: 50%;
    }

    .table th {
        /* padding: 0rem !important; */
        /* padding-top: 1rem !important; */
        /* padding-left: 0.2rem !important; */
        width: 50%;

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