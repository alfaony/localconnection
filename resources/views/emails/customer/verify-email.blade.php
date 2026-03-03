<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email Anda</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f6f9;
            color: #1e293b;
            line-height: 1.6;
        }
        .wrapper {
            max-width: 580px;
            margin: 40px auto;
            padding: 0 16px 40px;
        }
        /* Header */
        .header {
            background: linear-gradient(135deg, #de342f 0%, #b91c1c 100%);
            border-radius: 16px 16px 0 0;
            padding: 36px 40px;
            text-align: center;
        }
        .header-logo {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
        }
        .header-logo span {
            opacity: 0.75;
            font-weight: 400;
        }
        /* Body */
        .body {
            background: #ffffff;
            padding: 40px;
            border-left: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
        }
        .greeting {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .body-text {
            font-size: 15px;
            color: #475569;
            margin-bottom: 16px;
        }
        /* Email verify box */
        .verify-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 24px;
            margin: 28px 0;
            text-align: center;
        }
        .verify-icon {
            font-size: 40px;
            margin-bottom: 12px;
        }
        .verify-box p {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 20px;
        }
        /* CTA Button */
        .btn-verify {
            display: inline-block;
            background: linear-gradient(135deg, #de342f 0%, #b91c1c 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-size: 16px;
            font-weight: 700;
            padding: 14px 36px;
            border-radius: 50px;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 14px rgba(222, 52, 47, 0.35);
        }
        /* Expiry note */
        .expiry-note {
            font-size: 13px;
            color: #94a3b8;
            text-align: center;
            margin-top: 12px;
        }
        /* Divider */
        .divider {
            border: none;
            border-top: 1px solid #e2e8f0;
            margin: 28px 0;
        }
        /* Fallback link */
        .fallback {
            font-size: 13px;
            color: #64748b;
        }
        .fallback a {
            color: #de342f;
            word-break: break-all;
        }
        /* Security note */
        .security-note {
            background: #f8fafc;
            border-radius: 10px;
            padding: 16px 20px;
            margin-top: 24px;
        }
        .security-note p {
            font-size: 13px;
            color: #64748b;
            margin: 0;
        }
        .security-note strong {
            color: #374151;
        }
        /* Footer */
        .footer {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 16px 16px;
            padding: 24px 40px;
            text-align: center;
        }
        .footer p {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 4px;
        }
        .footer a {
            color: #64748b;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="wrapper">

    <!-- Header -->
    <div class="header">
        <div class="header-logo">
            {{ $companyName }}
        </div>
    </div>

    <!-- Body -->
    <div class="body">
        <p class="greeting">Halo, {{ $userName }}! 👋</p>
        <p class="body-text">
            Terima kasih telah mendaftar di <strong>{{ $companyName }}</strong>.
            Satu langkah lagi — verifikasi alamat email Anda untuk mengaktifkan akun dan mulai menggunakan layanan kami.
        </p>

        <!-- Verify Box -->
        <div class="verify-box">
            <div class="verify-icon">✉️</div>
            <p>Klik tombol di bawah untuk memverifikasi email Anda. Link ini berlaku selama <strong>60 menit</strong>.</p>
            <a href="{{ $verifyUrl }}" class="btn-verify">
                ✓ &nbsp;Verifikasi Email Sekarang
            </a>
            <p class="expiry-note" style="margin-top:14px; margin-bottom:0;">
                Link kadaluarsa dalam 60 menit sejak email ini dikirim.
            </p>
        </div>

        <!-- Security -->
        <div class="security-note">
            <p>
                🔒 <strong>Tips keamanan:</strong>
                Jika Anda tidak mendaftar di {{ $companyName }}, abaikan email ini — akun tidak akan dibuat tanpa verifikasi.
            </p>
        </div>

        <hr class="divider">

        <!-- Fallback -->
        <p class="fallback">
            Tombol tidak berfungsi? Salin dan tempel link berikut ke browser Anda:<br>
            <a href="{{ $verifyUrl }}">{{ $verifyUrl }}</a>
        </p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Setelah verifikasi, Anda akan diarahkan ke halaman login.</p>
        <p>Atau langsung login di: <a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
        <p style="margin-top:12px;">
            &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
        </p>
    </div>

</div>
</body>
</html>
