
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
                    SURAT KEPUTUSAN MANAJEMEN
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
                    <th>Jabatan / Fungsi / Keahlian</th>
                    <td>{{ $letterSubmission->user->last_position ? $letterSubmission->user->last_position->position->name : "" }}</td>
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
                    <p>
                        Perjanjian ini berlaku efektif sejak ditandatangani, dan peserta magang akan menjalankan peran sebagai {{ $user->last_position_now ? $user->last_position_now->position->name : "" }} selama masa magang. Peserta magang tunduk pada ketentuan yang berlaku dalam perusahaan serta aturan dan tanggung jawab yang telah ditetapkan.
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
            <div class="text-left mb-">
                <p><strong>Jakarta, {{ $date ?? "" }}</strong></p>
            </div>

            <!-- KTP and Signature Section -->
            <div class="row photo-ktp">
                <div class="col">
                    <img src="{{ asset('logo/paraf.png') }}" class="img-fluid" alt="Signature" style="height:150px">
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
        padding: 0rem !important;
        padding-top: 1rem !important;
        padding-left: 0.2rem !important;
    }

    .table th {
        padding: 0rem !important;
        padding-top: 1rem !important;
        padding-left: 0.2rem !important;

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