<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f6f9; color: #1e293b; line-height: 1.6;
        }
        .wrapper { max-width: 580px; margin: 40px auto; padding: 0 16px 40px; }
        .header {
            background: linear-gradient(135deg, #de342f 0%, #b91c1c 100%);
            border-radius: 16px 16px 0 0; padding: 36px 40px; text-align: center;
        }
        .header-logo { font-size: 28px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px; }
        .body { background: #ffffff; padding: 40px; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; }
        .greeting { font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 12px; }
        .body-text { font-size: 15px; color: #475569; margin-bottom: 16px; }
        .reset-box {
            background: #fff8f8; border: 1px solid #fecaca; border-radius: 12px;
            padding: 24px; margin: 28px 0; text-align: center;
        }
        .reset-icon { font-size: 40px; margin-bottom: 12px; }
        .reset-box p { font-size: 14px; color: #6b7280; margin-bottom: 20px; }
        .btn-reset {
            display: inline-block;
            background: linear-gradient(135deg, #de342f 0%, #b91c1c 100%);
            color: #ffffff !important; text-decoration: none;
            font-size: 16px; font-weight: 700; padding: 14px 36px;
            border-radius: 50px; letter-spacing: 0.3px;
            box-shadow: 0 4px 14px rgba(222, 52, 47, 0.35);
        }
        .expiry-note { font-size: 13px; color: #94a3b8; text-align: center; margin-top: 12px; }
        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 28px 0; }
        .fallback { font-size: 13px; color: #64748b; }
        .fallback a { color: #de342f; word-break: break-all; }
        .security-note { background: #f8fafc; border-radius: 10px; padding: 16px 20px; margin-top: 24px; }
        .security-note p { font-size: 13px; color: #64748b; margin: 0; }
        .security-note strong { color: #374151; }
        .footer {
            background: #f1f5f9; border: 1px solid #e2e8f0; border-top: none;
            border-radius: 0 0 16px 16px; padding: 24px 40px; text-align: center;
        }
        .footer p { font-size: 12px; color: #94a3b8; margin-bottom: 4px; }
        .footer a { color: #64748b; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="header-logo">{{ $companyName }}</div>
    </div>
    <div class="body">
        <p class="greeting">Halo, {{ $userName }}! 🔐</p>
        <p class="body-text">
            Kami menerima permintaan untuk mereset password akun Anda di <strong>{{ $companyName }}</strong>.
            Klik tombol di bawah untuk membuat password baru.
        </p>
        <div class="reset-box">
            <div class="reset-icon">🔑</div>
            <p>Link reset password ini berlaku selama <strong>60 menit</strong>. Setelah itu Anda perlu meminta ulang.</p>
            <a href="{{ $resetUrl }}" class="btn-reset">🔒 &nbsp;Reset Password Sekarang</a>
            <p class="expiry-note" style="margin-top:14px; margin-bottom:0;">
                Link kadaluarsa dalam 60 menit sejak email ini dikirim.
            </p>
        </div>
        <div class="security-note">
            <p>
                🛡️ <strong>Keamanan akun:</strong>
                Jika Anda tidak meminta reset password, abaikan email ini — password Anda tidak akan berubah.
            </p>
        </div>
        <hr class="divider">
        <p class="fallback">
            Tombol tidak berfungsi? Salin dan tempel link berikut ke browser Anda:<br>
            <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
        </p>
    </div>
    <div class="footer">
        <p>Setelah reset, Anda akan dapat login kembali di:</p>
        <p><a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
        <p style="margin-top:12px;">&copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.</p>
    </div>
</div>
</body>
</html>
