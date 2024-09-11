
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Pengajuan Surat Baru</title>
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
        .button {
            display: inline-block;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 16px;
        }
        .button:hover {
            background-color: #c82333;
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
        <h2>Subject: Notifikasi Pengajuan Surat Baru</h2>

        <p>Yth, Bapak/Ibu,</p>
        <p>Kami ingin memberitahukan bahwa ada pengajuan surat baru dari <span class="highlight">{{ $data['name'] }}</span> melalui sistem BOS.</p>

        <p>Detail Pengajuan:</p>
        <ul>
            <li><strong>Jenis Surat:</strong> {{ $data['letter_type'] }}</li>
            <li><strong>Tanggal Pengajuan:</strong> {{ $data['letter_date'] }}</li>
            <li><strong>Status:</strong> Disetujui Otomatis</li>
        </ul>

        <p>Surat tersebut sudah tersedia dan dapat diakses di sistem BOS untuk kebutuhan lebih lanjut.</p>

        <a href="#" class="button">Lihat Surat</a>

        <div class="footer">
            <p>Terima kasih atas perhatian dan kerjasamanya.</p>
        </div>
    </div>
</body>
</html>
