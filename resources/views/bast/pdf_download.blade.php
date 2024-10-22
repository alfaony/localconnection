<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAST Document</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            font-size: 24px;
            text-align: center;
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            text-align: left;
            background-color: #f4f4f4;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 50px;
        }
        .signature-section div {
            text-align: center;
            width: 40%;
        }
        .signature {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
        }
        .signature img {
            max-height: 150px;
            width: auto;
            display: block;
            margin: 0 auto;
        }
        .no-margin {
            margin: 0;
        }

        @media print {
            body, .container {
                margin: 0;
                padding: 0;
            }
            .container {
                border: none;
                margin: 0;
                width: 100%;
                padding: 0 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Berita Acara Serah Terima</h1>
        <p>No. {{ $bast->number ?? '' }}</p>

        <table>
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

        <p>Bersamaan dengan surat pernyataan ini, pekerjaan dengan nomor purchase order di atas dengan rincian pekerjaan:</p>
        <p><strong>{{ $bast->project ? $bast->project->title : '' }}</strong></p>
        <p>Telah diselesaikan dengan baik. Laporan bisa di unduh di link berikut ini:</p>
        <ul>
            @if($bast->project && $bast->project->reportProject->reportProjectDetail)
                @foreach($bast->project->reportProject->reportProjectDetail as $detail)
                    <li>{{ $detail->name }} - <a href="{{ $detail->url }}" style="color: blue;">{{ $detail->url }}</a></li>
                @endforeach
            @endif
        </ul>

        <div class="signature-section">
            <div>
                <p>TTD</p>
                <div class="signature">
                    <img src="{{ public_path('logo/paraf.png') }}" alt="Signature">
                </div>
                <p>{{ $company['director'] ?? '' }}</p>
            </div>
            <div>
                <p>Diterima,</p>
                <div class="signature" style="height: 150px;"></div>
                <p class="no-margin">{{ $bast->workOrder ? $bast->workOrder->quote->customer->{$bast->customer_signature} : '' }}</p>
                <p>{{ $bast->workOrder ? $bast->workOrder->quote->customer->name : '' }}</p>
            </div>
        </div>
    </div>
</body>
</html>
