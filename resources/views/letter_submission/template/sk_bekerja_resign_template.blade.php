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
                    <h4><strong>SURAT PENGUNDURAN DIRI</strong></h4>
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
                        <img src="{{ Storage::url($fieldData['signature_image'] ?? '' ) }}" class="img-fluid"
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
        <button type="button" id="downloadQuote" class="btn btn-success"><i class="fa fa-file-pdf"></i>
            {{__('Download')}}</button>
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
