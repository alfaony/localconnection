<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 600px;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: left;
        }
        .header {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
        .content-head {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        .content {
            font-size: 16px;
            line-height: 1.7;
            color: #555;
            margin-bottom: 20px;
        }
        .details {
            padding: 0;
            list-style: none;
        }
        .details li {
            margin-bottom: 10px;
            font-size: 15px;
            color: #444;
        }
        .details li strong {
            font-weight: 600;
            color: #333;
        }
        .details li::before {
            content: "• ";
            color: #ff5722;
            margin-right: 5px;
        }
        .button {
            display: inline-block;
            background-color: #ff5722;
            color: white !important;
            padding: 12px 25px;
            margin-top: 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }
        .button:hover {
            background-color: #e64a19;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            Kepada Yth. Pihak Manajemen,
        </div>
        <div class="content">
            Kami informasikan bahwa terdapat laporan proyek baru yang telah diunggah ke sistem dan memerlukan persetujuan.
        </div>
        <div class="content-head">
            Detail Laporan Proyek
        </div>
        <ul class="details">
            <li><strong>No. SPK/Nama Proyek:</strong> {{ $data['work_order'] }}/{{ $data['project'] }}</li>
            <li><strong>Tanggal Unggah:</strong> {{ $data['created_at'] }}</li>
            <li><strong>Penanggung Jawab:</strong> {{ $data['user_create'] }}</li>
        </ul>
        <div class="content">
            Laporan ini memerlukan persetujuan dari bapak/ibu. Mohon untuk dapat melakukan tinjauan dan memberikan tanggapan. Untuk melihat detail suratnya dan memberikan persetujuan, silahkan klik pada tautan di bawah ini.
        </div>
        <a href="#" class="button">Tinjau Laporan</a>
        <div class="content" style="margin-top: 25px;">
            Terima kasih atas perhatian dan kerjasamanya.
        </div>
    </div>
</body>
</html>