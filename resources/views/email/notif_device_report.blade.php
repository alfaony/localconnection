<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .content {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="content">
        Halo {{ $data['name'] }},<br><br>
        Kami ingin mengingatkan Anda bahwa ada lampu/pintu yang belum dimatikan/ditutup di lokasi berikut:
    </div>

    <table>
        <tr>
            <th>Nama Device</th>
            <th>Type</th>
            <th>Location</th>
        </tr>
        @if(count($data['devices']) != 0)
        @foreach($data['devices'] as $device)
        <tr>
            <td>
                {{ $device['name'] }}
            </td>
            <td>
                {{ $device['type'] }}
            </td>
            <td>
                {{ $device['location'] }} - {{ $device['location_type'] }}
            </td>
        </tr>
        @endforeach
        @endif
    </table>

    <div class="footer">
        Mohon pastikan untuk mematikan lampu atau menutup pintu untuk penghematan energi dan menjaga keamanan.<br><br>
        Terima kasih atas perhatian dan kerjasamanya.
    </div>
</div>

</body>
</html>