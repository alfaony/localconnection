<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password – {{ $company->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #de342f; --primary-d: #b91c1c; --primary-l: #fee2e2;
            --danger: #ef4444; --bg: #f8faff; --text: #1e293b;
            --muted: #64748b; --border: #e2e8f0; --radius: 16px;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); min-height: 100vh; display: flex; }
        .page-wrap { display: flex; width: 100%; }
        .sidebar {
            width: 420px; flex-shrink: 0;
            background-color: #de342f;
            background-image: radial-gradient(circle at center, rgba(239, 68, 68, 0.8) 0%, rgba(219, 39, 41, 0) 80%);
            display: flex; flex-direction: column; justify-content: center;
            padding: 60px 48px; color: #fff;
        }
        .sidebar h2 { font-size: 2rem; font-weight: 800; line-height: 1.2; margin-bottom: 14px; }
        .sidebar p  { font-size: 1rem; color: rgba(255,255,255,.75); line-height: 1.6; margin-bottom: 40px; }
        .feature-list { list-style: none; display: flex; flex-direction: column; gap: 14px; }
        .feature-list li { display: flex; align-items: center; gap: 12px; font-size: 14px; }
        .feature-list i { width: 32px; height: 32px; border-radius: 8px; background: rgba(255,255,255,.15); display: flex; align-items: center; justify-content: center; font-size: 14px; }

        .form-side { flex: 1; display: flex; align-items: center; justify-content: center; padding: 48px 24px; }
        .form-box { width: 100%; max-width: 420px; animation: fadeInUp .4s ease; }
        .brand-back { display: flex; align-items: center; gap: 8px; color: var(--muted); font-size: 14px; text-decoration: none; margin-bottom: 32px; }
        .brand-back:hover { color: var(--primary); }
        .form-box h1 { font-size: 1.75rem; font-weight: 800; margin-bottom: 6px; }
        .form-box .sub { color: var(--muted); font-size: 14px; margin-bottom: 32px; line-height: 1.5; }
        .form-box .sub a { color: var(--primary); font-weight: 600; text-decoration: none; }

        .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 13.5px; display: flex; align-items: center; gap: 10px; }
        .alert-danger  { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

        .form-group { margin-bottom: 20px; }
        .form-label { font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 6px; display: block; }
        .input-wrap { position: relative; }
        .input-wrap i.field-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 14px; pointer-events: none; }
        .form-control {
            width: 100%; padding: 12px 14px 12px 40px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 14.5px; font-family: inherit; color: var(--text);
            transition: border-color .2s, box-shadow .2s; background: #fff; outline: none;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(222,52,47,.12); }
        .form-control.is-invalid { border-color: var(--danger); }
        .invalid-feedback { font-size: 12px; color: var(--danger); margin-top: 5px; }

        .btn-submit {
            width: 100%; padding: 14px; background: var(--primary); color: #fff;
            border: none; border-radius: 12px; font-size: 16px; font-weight: 700;
            cursor: pointer; font-family: inherit; transition: all .2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover { background: var(--primary-d); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(222,52,47,.35); }
        .btn-submit .spinner { display: none; }
        .btn-submit.loading .spinner { display: inline-block; }
        .btn-submit.loading .btn-text { display: none; }

        .back-to-login {
            margin-top: 24px; text-align: center; padding: 20px;
            background: var(--primary-l); border-radius: 12px;
        }
        .back-to-login p { font-size: 14px; color: var(--muted); margin-bottom: 8px; }
        .btn-login-link {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--primary); color: #fff; font-weight: 700;
            padding: 10px 24px; border-radius: 8px; text-decoration: none; font-size: 14px;
            transition: background .2s;
        }
        .btn-login-link:hover { background: var(--primary-d); }

        @media(max-width: 768px) { .sidebar { display: none; } .form-side { padding: 32px 16px; align-items: flex-start; padding-top: 48px; } }
        @keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    </style>
</head>
<body>
<div class="page-wrap">

    {{-- Sidebar --}}
    <aside class="sidebar">
        <h2>Lupa<br>Password?</h2>
        <p>Tenang, kami akan kirim link reset ke email Anda. Prosesnya mudah dan aman.</p>
        <ul class="feature-list">
            <li><i class="fas fa-envelope"></i> <span>Cek inbox atau folder spam</span></li>
            <li><i class="fas fa-clock"></i> <span>Link berlaku 60 menit</span></li>
            <li><i class="fas fa-shield-alt"></i> <span>Link hanya sekali pakai</span></li>
            <li><i class="fas fa-lock"></i> <span>Password lama tetap aman</span></li>
        </ul>
    </aside>

    {{-- Form --}}
    <div class="form-side">
        <div class="form-box">
            <a href="{{ route('public.software-sharing.login', $companySlug) }}" class="brand-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Login
            </a>

            <h1>Reset Password</h1>
            <p class="sub">
                Masukkan email yang terdaftar. Kami akan mengirimkan link untuk membuat password baru.
            </p>

            @if(session('error'))
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
            @endif
            @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif

            @if(!session('success'))
            <form method="POST" action="{{ route('customer.password.email', $companySlug) }}" id="forgotForm">
                @csrf

                <div class="form-group">
                    <label class="form-label">Alamat Email</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope field-icon"></i>
                        <input type="email" name="email"
                               class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               placeholder="email@example.com"
                               value="{{ old('email') }}" required autofocus>
                    </div>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span class="btn-text"><i class="fas fa-paper-plane"></i> Kirim Link Reset</span>
                    <span class="spinner"><i class="fas fa-spinner fa-spin"></i> Mengirim...</span>
                </button>
            </form>
            @endif

            <div class="back-to-login">
                <p>Sudah ingat password?</p>
                <a href="{{ route('public.software-sharing.login', $companySlug) }}" class="btn-login-link">
                    <i class="fas fa-sign-in-alt"></i> Masuk Sekarang
                </a>
            </div>
        </div>
    </div>
</div>

<script>
const form = document.getElementById('forgotForm');
if (form) {
    form.addEventListener('submit', function() {
        document.getElementById('submitBtn').classList.add('loading');
    });
}
</script>
</body>
</html>
