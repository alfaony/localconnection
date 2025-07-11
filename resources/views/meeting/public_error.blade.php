<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gagal Bergabung ke Meeting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --error-color: #e74c3c;
            --error-light: #fadbd8;
            --secondary-color: #3498db;
        }
        
        body {
            background: linear-gradient(135deg, #f9f9f9 0%, #f1f5f9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .error-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .error-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border-top: 5px solid var(--error-color);
            transform: translateY(0);
            transition: transform 0.3s ease;
        }
        
        .error-card:hover {
            transform: translateY(-5px);
        }
        
        .error-icon {
            width: 100px;
            height: 100px;
            background: var(--error-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            animation: pulse 2s infinite;
        }
        
        .error-icon i {
            font-size: 50px;
            color: var(--error-color);
        }
        
        .error-title {
            font-weight: 700;
            color: var(--error-color);
            margin-bottom: 15px;
            letter-spacing: -0.5px;
        }
        
        .error-message {
            font-size: 1.1rem;
            color: #555;
            line-height: 1.6;
            max-width: 80%;
            margin: 0 auto;
        }
        
        .btn-back {
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            margin-top: 25px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-back:hover {
            background: #2980b9;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        
        .btn-back:active {
            transform: translateY(1px);
        }
        
        .error-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            border-left: 4px solid var(--error-color);
        }
        
        .suggestion-box {
            background: #eaf7ff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 25px;
            border-left: 4px solid var(--secondary-color);
        }
        
        .suggestion-title {
            font-weight: 600;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .suggestion-list {
            text-align: left;
            padding-left: 20px;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @media (max-width: 768px) {
            .error-message {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="error-container">
            <div class="error-card p-4 p-md-5">
                <div class="text-center">
                    <div class="error-icon">
                        <i class="fas fa-times"></i>
                    </div>
                    
                    <h2 class="error-title">Gagal Bergabung ke Meeting</h2>
                    
                    <p class="error-message">
                        {{ request('message') ?? 'Terjadi kesalahan yang tidak diketahui saat mencoba bergabung ke meeting.' }}
                    </p>
                    
                    <div class="suggestion-box">
                        <h5 class="suggestion-title">Saran Pemecahan Masalah:</h5>
                        <ul class="suggestion-list">
                            <li>Pastikan email dan kode public yang Anda masukkan benar</li>
                            <li>Periksa koneksi internet Anda</li>
                            <li>Coba lagi beberapa saat kemudian</li>
                            <li>Hubungi penyelenggara meeting jika masalah berlanjut</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>