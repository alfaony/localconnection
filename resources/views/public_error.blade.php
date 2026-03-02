<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $code ?? 'Error' }} - {{ $title ?? 'Terjadi Kesalahan' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts & Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f2f6fc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .error-wrapper {
            background: white;
            border-radius: 16px;
            padding: 40px 30px;
            max-width: 550px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        .error-icon {
            background: #ffe6e6;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 20px;
        }
        .error-icon i {
            font-size: 36px;
            color: #e74c3c;
        }
        .error-code {
            font-size: 48px;
            font-weight: 700;
            color: #e74c3c;
            margin-bottom: 10px;
        }
        .error-title {
            font-size: 22px;
            font-weight: 600;
            color: #333;
        }
        .error-message {
            font-size: 16px;
            color: #555;
            margin: 15px 0 20px;
        }
        .btn-back {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-back:hover {
            background-color: #2c80b4;
        }
        .footer-note {
            margin-top: 25px;
            font-size: 13px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="error-wrapper">
        <div class="error-icon">
            <i class="{{ $icon ?? 'fas fa-exclamation-triangle' }}"></i>
        </div>
        <div class="error-code">{{ $code ?? 'Error' }}</div>
        <div class="error-title">{{ $title ?? 'Terjadi Kesalahan' }}</div>
        <p class="error-message">
            {{ $message ?? 'Terjadi kesalahan yang tidak diketahui. Silakan coba beberapa saat lagi.' }}
        </p>
        <a href="{{ url()->previous() }}" class="btn-back">Kembali</a>

        <div class="footer-note">
            &copy; {{ date('Y') }} {{ config('app.name', 'Aplikasi Anda') }}
        </div>
    </div>
</body>
</html>