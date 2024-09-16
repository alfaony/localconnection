<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Perubahan Pengajuan Surat</title>
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
        ul {
            list-style-type: none;
            padding: 0;
        }
        ul li {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .highlight {
            font-weight: bold;
            color: #007BFF;
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
            background-color: #28a745;
            color: white !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 16px;
        }
        .button:hover {
            background-color: #218838;
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
        <p>
            Kami ingin menginformasikan bahwa pengajuan surat dari <strong>{{ $data['name'] }}</strong> telah mengalami perubahan oleh admin.
            Mohon untuk memeriksa kembali pengajuan tersebut dan melakukan tanda tangan ulang.
        </p>

        <p>Detail Pengajuan:</p>
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
                <td>Memerlukan Tanda Tangan Ulang</td>
            </tr>
        </table>

        <p>
            Silakan klik tautan di bawah ini untuk meninjau dan menandatangani ulang dokumen tersebut.
        </p>

        <a href="{{ $data['url'] }}" class="button">Lihat dan Tanda Tangan Ulang</a>

        <div class="footer">
            <p>Terima kasih atas perhatian dan kerjasamanya.</p>
        </div>
    </div>
</body>
</html>
