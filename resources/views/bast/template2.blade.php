<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Serah Terima - {{ $bast->number ?? '' }}</title>
    <link rel="stylesheet" href="{{ public_path('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ public_path('vendor/adminlte/plugins/bootstrap/css/bootstrap.min.css') }}">
    <style>
        /* General font size */
        body, html {
            font-family: DejaVu Sans, sans-serif !important; /* ✅ font default dompdf */
            font-size: 12px;
        }
        body, html {
            font-size: 12px; /* Smaller default font size */
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
        }

        header, footer {
            width: 100%;
            position: fixed;
            left: 0;
            right: 0;
            height: auto;
        }
        
        footer {
            bottom: 0;
            margin-top: 0px;
        }

        .header-image, .footer-image {
            width: 100%;
            display: block;
        }

        .content {
            padding-bottom: 10px;
            padding-left: 20px;
            padding-right: 20px;
        }

        .table-bordered {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px; /* Smaller font specifically for tables */
            page-break-inside: avoid;
        }

        .table-bordered th, .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 6px;
        }

        .mt-5, .mt-3 {
            margin-top: 1.5rem !important;
        }

        .noMargin {
            margin: 0;
        }
    </style>
    <style>
        body {
            margin-top: 5cm;
            margin-bottom: 2cm;
        }
        header {
            position: fixed;
            top: 0cm;
            height: 6cm;
        }
    </style>
</head>
<body>
    @if(!empty($company['header']) && !empty($company['footer']))
    <header>
        <img src="{{ public_path('storage/' . $company['header']) }}" alt="Company Logo" class="header-image" style="margin-bottom: 150px">
    </header>
    <footer>
        <img src="{{ public_path('storage/' . $company['footer']) }}" alt="Company Footer" class="footer-image">
    </footer>
    @endif
    
    <div class="content">
        <div class="card" id="printThis">
            <div class="card-body" id="printItem">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h3 style="margin-bottom: 10px;">Berita Acara Serah Terima</h3>
                        <p>No. {{ $bast->number ?? '' }}</p>
                    </div>
                </div>
                <div class="row mr-4">
                    <div class="col-md-12">
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
                        <p>Bersamaan dengan surat pernyataan ini, pekerjaan dengan nomor purchase order diatas dengan rincian pekerjaan <strong>{{ $bast->project ? $bast->project->title : '' }}</strong> telah diselesaikan dengan baik dan pekerjaan tersebut telah kami serah terimakan kepada {{ $bast->workOrder ? $bast->workOrder->quote->customer->name : '' }}</p>
                        <p>Laporan bisa di unduh di link berikut ini</p>
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
                <div class="mt-3">
                    @if(!$bast->signature)
                    <table style="width: 100%; text-align: center;">
                        <tr>
                            <td style="width: 50%; vertical-align: top;">
                                <p style="margin-bottom: 10px;">TTD</p>
                                <img src="{{ public_path('logo/paraf.png') }}" alt="Signature"
                                    style="width:auto; height:100px; margin-bottom: 10px;">
                                <p>{{ $company['director'] ?? '' }}</p>
                            </td>

                            <td style="width: 50%; vertical-align: top;">
                                <p style="margin-bottom: 10px;">Diterima,</p>
                                <div style="margin: 60px 0;"></div>
                                @if($bast->customer_signature == \App\Schemas\ParamSchema::PENANGGUNGJAWAB)
                                <p class="noMargin">{{ $bast->pic ?? '' }}</p>
                                @else
                                <p class="noMargin">
                                    {{ $bast->workOrder ? $bast->workOrder->quote->customer->{$bast->customer_signature} : '' }}
                                </p>
                                @endif
                                <p>{{ $bast->workOrder ? $bast->workOrder->quote->customer->name : '' }}</p>
                            </td>
                        </tr>
                    </table>
                    @else
                    @if($bast->signature)
                    <table style="width: 100%; float: left; text-align: center; margin-bottom: 20px;">
                        <tr>
                            <td style="text-align: center; width: 25%;">
                                {{ $company['name'] ?? '' }}
                            </td>
                            <td style="text-align: center;" colspan="{{ count(json_decode($bast->signature_data, true)) }}">
                                {{ $bast->workOrder ? $bast->workOrder->quote->customer->name : '' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: center; width: 25%;">
                                <img src="{{ public_path('logo/paraf.png') }}" alt="Signature"
                                style="width:auto; height:100px; margin-bottom: 10px;">
                            </td>
                        <tr>
                            <td style="text-align: center; width: 20%;"><strong><u>{{ $company['director'] ?? '' }}</u></strong></td>
                            @foreach (json_decode($bast->signature_data, true) as $index => $signature)
                            <th style="text-decoration: underline;">{{ $signature['pic_name'] }}</th>
                            @endforeach
                        </tr>
                        <tr>
                            <td style="text-align: center; width: 25%;">Directur</td>
                            @foreach (json_decode($bast->signature_data, true) as $index => $signature)
                            <td>{{ $signature['section_name'] }}</td>
                            @endforeach
                        </tr>
                    </table>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>