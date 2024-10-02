<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Persetujuan Pengajuan Surat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
            margin: 0;
        }
        .container {
            background-color: #ffffff;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-size: 20px;
            color: #333;
            font-weight: bold;
        }
        p {
            color: #555;
            font-size: 16px;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table td, table th {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .button {
            display: inline-block;
            background-color: #dc3545;
            color: white !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 16px;
        }
        .button:hover {
            background-color: #dc3545;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <p>Yth, Bapak/Ibu,</p>
        <p>Kami dengan senang hati menginformasikan bahwa pengajuan surat Anda dengan detail berikut telah disetujui:</p>

        <table>
            <tr>
                <th>Jenis Surat</th>
                <td>{{ $data['letter_type'] }}</td>
            </tr>
            <tr>
                <th>Tanggal Pengajuan</th>
                <td>{{ $data['letter_date'] }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>Disetujui</td>
            </tr>
        </table>

        <p>Anda dapat memeriksa dokumen tersebut melalui sistem, atau hubungi pihak manajemen jika memerlukan informasi lebih lanjut.</p>

        <a href="{{ $data['url'] }}" class="button">Lihat Surat</a>

        <div class="footer">
            <p>Terima kasih atas perhatian dan kerjasamanya.</p>
        </div>
    </div>
</body>
</html>
