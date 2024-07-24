<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Anggaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #007bff;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .content {
            padding: 20px;
        }
        .content p {
            margin: 10px 0;
        }
        .content .title {
            font-weight: bold;
        }
        .footer {
            text-align: center;
            padding: 10px;
            font-size: 12px;
            color: #777777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $data['title'] }}</h1>
        </div>
        <div class="content">
            <p class="title">Yth. Tim Keuangan,</p>
            <p>Berikut ini adalah pengajuan anggaran yang perlu ditinjau:</p>
            <p><span class="title">Nama:</span> {{ $data['name'] }}</p>
            <p><span class="title">Anggaran:</span> Rp {{ number_format($data['budget'], 0, ',', '.') }}</p>
            <p><span class="title">Deskripsi:</span> {!! $data['description'] !!}</p>
            <p><span class="title">Divisi:</span> {{ $data['division'] }}</p>
            <p><span class="title">Diajukan oleh:</span> {{ $data['user_create'] }}</p>
            <p>Mohon tinjau dan proses pengajuan ini sesegera mungkin.</p>
            <p>Terima kasih.</p>
            <p>Hormat kami,<br>{{ $data['user_create'] }}</p>
        </div>
        <div class="footer">
            <p>Ini adalah email otomatis, mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>