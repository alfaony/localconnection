<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Serah Terima - {{ $bast->number ?? '' }}</title>
    <!-- Tambahkan link CSS AdminLTE dan Bootstrap -->
    <link rel="stylesheet" href="{{ public_path('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ public_path('vendor/adminlte/plugins/bootstrap/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: 'Arial', sans-serif;
        }
        .card {
            /* border: 1px solid #ccc; */
            padding: 0px;
            margin: 10px;
        }
        .text-primary {
            color: #007bff !important;
        }
        .table-bordered {
            width: 100%;
            border-collapse: collapse;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 8px;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .mt-5 {
            margin-top: 3rem !important;
        }
        .noMargin {
            margin: 0;
        }
        header {
            /* position: fixed; */
            /* top: 0cm; */
            /* left: 0cm; */
            /* right: 0cm; */
            /* height: 6cm; */
            /* margin-right: 2cm; */
            /* margin-left: 1cm; */
        }
    </style>
</head>
<body>
    <header>
        <div class="row">
            <div class="col-md-12">
                <img src="{{ public_path('storage/' . $company['header']) }}" alt="Company Logo" class="img-fluid">
            </div>
        </div>
    </header>
    <div class="card" id="printThis">
        <div class="card-body" id="printItem">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h3>Berita Acara Serah Terima</h3>
                    <p>No. {{ $bast->number ?? '' }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <table class="table table-bordered">
                        <tr>
                            <th>Nomor</th>
                            <td>{{ $bast->number }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td>{{ $today ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>No. Purchase Order</th>
                            <td>{{ $bast->number_purchase ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Penanggung Jawab</th>
                            <td>{{ $bast->pic ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Perusahaan</th>
                            <td>{{ $bast->workOrder ? $bast->workOrder->quote->customer->name : '' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-12">
                    <p>Bersamaan dengan surat pernyataan ini, pekerjaan dengan nomor purchase order diatas dengan rincian pekerjaan:</p>
                    <p><strong>{{ $bast->project ? $bast->project->title : '' }}</strong></p>
                    <p><strong>{{ $bast->period ? "Period ".$bast->period : '' }}</strong></p>
                    <p>Telah diselesaikan dengan baik. Laporan bisa di unduh di link berikut ini</p>
                    <ul>
                        @if($bast->project && $bast->project->reportProject && $bast->project->reportProject->reportedDetails)
                            @foreach($bast->project->reportProject->reportedDetails as $a)
                                <li>
                                    {{ $a->name }} - <a href="{{ $a->url }}" class="text-primary">{{ $a->url }}</a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>
            </div>
            <div class="mt-5">
                <table style="width: 100%; margin-top: 50px; text-align: center;">
                    <tr>
                        <!-- Kolom Kiri -->
                        <td style="width: 50%; vertical-align: top;">
                            <p style="margin-bottom: 20px;">TTD</p>
                            <img src="{{ public_path('logo/paraf.png') }}" alt="Signature" style="width:auto; height:150px; margin-bottom: 10px;">
                            <p>{{ $company['director'] ?? '' }}</p>
                        </td>

                        <!-- Kolom Kanan -->
                        <td style="width: 50%; vertical-align: top;">
                            <p style="margin-bottom: 20px;">Diterima,</p>
                            <div style="margin: 90px 0;"></div>
                            @if($bast->customer_signature == \App\Schemas\ParamSchema::PENANGGUNGJAWAB)
                                <p class="noMargin">{{ $bast->pic ?? '' }}</p>
                            @else
                                <p class="noMargin">{{ $bast->workOrder ? $bast->workOrder->quote->customer->{$bast->customer_signature} : '' }}</p>
                            @endif
                            <p>{{ $bast->workOrder ? $bast->workOrder->quote->customer->name : '' }}</p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
