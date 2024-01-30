<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project "Custom"</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        h1, h2 {
            color: #333;
        }

        p {
            margin-bottom: 20px;
        }

        .expired {
            background-color: #ff9999;
        }

        .one-week-before {
            background-color: #ffcc66;
        }

        .two-weeks-before {
            background-color: #ffff66;
        }

        .one-month-before {
            background-color: #ccffcc;
        }
    </style>
</head>
<body>
<!-- One Week Before Template -->
<div class="container one-week-before">
    <h1>Project {{ $data->title ?? '' }} - 1 Minggu Sebelum Expired</h1>
    <p>Nama Perusahaan: {{ $data->workOrder->quote->customer->name ?? '' }} </p>
    <p>Nama Project: {{ $data->title ?? '' }}</p>
    <p>Tanggal Berakhir: {{ $data->end_date ?? '' }}</p>
</div>

</body>
</html>
