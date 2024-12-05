
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            background-color: #dc3545;
            color: white !important;
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
    </style>
</head>
<body>
    <div class="container">
        <p>Yth, Bapak/Ibu,</p>
        <p>
            Kami ingin menginformasikan bahwa pengajuan KYE Anda dengan detail berikut telah disetujui:
        </p>

        <p>Detail Pengajuan:</p>
        <ul class="details">
            <li><strong>Nama Lengkap:</strong> {{ $data['full_name'] }}</li>
            <li><strong>Tempat Lahir:</strong> {{ $data['birth_place'] }}</li>
            <li><strong>Tanggal Lahir:</strong> {{ $data['birth_date'] }}</li>
            <li><strong>Alamat:</strong> {{ $data['address'] }}</li>
        </ul>

        <a href="{{ $data['url'] }}" class="button">Lihat KYE</a>

        <div class="footer">
            <p>Terima kasih atas perhatian dan kerjasamanya.</p>
        </div>

    </div>
</body>
</html>
