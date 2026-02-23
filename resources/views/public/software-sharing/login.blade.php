<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk – {{ $company->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #6366f1; --primary-d: #4f46e5; --primary-l: #e0e7ff;
            --danger: #ef4444; --bg: #f8faff; --text: #1e293b;
            --muted: #64748b; --border: #e2e8f0; --radius: 16px;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); min-height: 100vh; display: flex; }
        .page-wrap { display: flex; width: 100%; }
        .sidebar {
            width: 420px; flex-shrink: 0;
            background: linear-gradient(145deg, #4f46e5 0%, #7c3aed 60%, #a855f7 100%);
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
        .form-box .sub { color: var(--muted); font-size: 14px; margin-bottom: 32px; }
        .form-box .sub a { color: var(--primary); font-weight: 600; text-decoration: none; }
        .form-box .sub a:hover { text-decoration: underline; }

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
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
        .form-control.is-invalid { border-color: var(--danger); }
        .invalid-feedback { font-size: 12px; color: var(--danger); margin-top: 5px; }
        .pass-toggle { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--muted); font-size: 14px; }
        .pass-toggle:hover { color: var(--primary); }

        .remember-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .check-label { display: flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; }
        .check-label input { accent-color: var(--primary); width: 15px; height: 15px; }

        .btn-submit {
            width: 100%; padding: 14px; background: var(--primary); color: #fff;
            border: none; border-radius: 12px; font-size: 16px; font-weight: 700;
            cursor: pointer; font-family: inherit; transition: all .2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover { background: var(--primary-d); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.35); }
        .btn-submit .spinner { display: none; }
        .btn-submit.loading .spinner { display: inline-block; }
        .btn-submit.loading .btn-text { display: none; }

        .register-cta {
            margin-top: 24px; text-align: center; padding: 20px;
            background: var(--primary-l); border-radius: 12px;
        }
        .register-cta p { font-size: 14px; color: var(--muted); margin-bottom: 8px; }
        .btn-register-link {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--primary); color: #fff; font-weight: 700;
            padding: 10px 24px; border-radius: 8px; text-decoration: none; font-size: 14px;
            transition: background .2s;
        }
        .btn-register-link:hover { background: var(--primary-d); }

        @media(max-width: 768px) { .sidebar { display: none; } .form-side { padding: 32px 16px; align-items: flex-start; padding-top: 48px; } }
        @keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    </style>
</head>
<body>
<div class="page-wrap">
    {{-- Sidebar --}}
    <aside class="sidebar">
        <h2>Selamat Datang<br>Kembali!</h2>
        <p>Masuk ke akun Anda dan lanjutkan akses ke semua software pilihan.</p>
        <ul class="feature-list">
            <li><i class="fas fa-history"></i> <span>Lihat riwayat langganan Anda</span></li>
            <li><i class="fas fa-key"></i> <span>Kelola akses software aktif</span></li>
            <li><i class="fas fa-bell"></i> <span>Notifikasi perpanjangan otomatis</span></li>
            <li><i class="fas fa-headset"></i> <span>Chat langsung dengan support</span></li>
        </ul>
    </aside>

    {{-- Form --}}
    <div class="form-side">
        <div class="form-box">
            <a href="{{ route('public.software-sharing.index', $companySlug) }}" class="brand-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Katalog
            </a>

            <h1>Masuk ke Akun</h1>
            <p class="sub">Belum punya akun? <a href="{{ route('public.software-sharing.register', $companySlug) }}">Daftar gratis</a></p>

            {{-- Alerts --}}
            @if(session('verify_email'))
            <div class="alert" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;">
                <i class="fas fa-envelope-open-text"></i>
                <div>
                    <strong>Cek email Anda!</strong><br>
                    Kami telah mengirim link verifikasi ke email Anda. Klik link tersebut terlebih dahulu sebelum login.
                </div>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
            @endif
            @if(session('success'))
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('public.software-sharing.login.post', $companySlug) }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label class="form-label">Alamat Email</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope field-icon"></i>
                        <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               placeholder="email@example.com" value="{{ old('email') }}" required autofocus>
                    </div>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" name="password" id="pwd" class="form-control" placeholder="Password Anda" required>
                        <span class="pass-toggle" onclick="togglePwd()"><i id="eyeIcon" class="fas fa-eye"></i></span>
                    </div>
                </div>

                <div class="remember-row">
                    <label class="check-label">
                        <input type="checkbox" name="remember"> Ingat saya
                    </label>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span class="btn-text"><i class="fas fa-sign-in-alt"></i> Masuk Sekarang</span>
                    <span class="spinner"><i class="fas fa-spinner fa-spin"></i> Memproses...</span>
                </button>
            </form>

            <div class="register-cta">
                <p>Belum punya akun?</p>
                <a href="{{ route('public.software-sharing.register', $companySlug) }}" class="btn-register-link">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang – Gratis!
                </a>
            </div>

            {{-- Form Kirim Ulang Verifikasi --}}
            <div style="margin-top: 24px; text-align: center; font-size: 14px;">
                Belum menerima email verifikasi? 
                <a href="#" onclick="document.getElementById('resendForm').style.display='block'; return false;" style="color: var(--primary); font-weight: 600; text-decoration: none;">Kirim Ulang</a>
            </div>
            
            <form id="resendForm" method="POST" action="{{ route('public.software-sharing.resend-verification', $companySlug) }}" style="display: none; margin-top: 15px; padding: 15px; background: #fff; border: 1px dashed var(--border); border-radius: 12px; animation: fadeInUp .3s ease;">
                @csrf
                <p style="font-size: 13px; margin-bottom: 12px; color: var(--muted); text-align: center;">Masukkan email Anda untuk menerima ulang link verifikasi.</p>
                <div class="form-group" style="margin-bottom: 12px;">
                    <div class="input-wrap">
                        <i class="fas fa-envelope field-icon"></i>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com" value="{{ old('email') }}" required>
                    </div>
                </div>
                <button type="submit" class="btn-submit" style="padding: 10px; font-size: 14px; border-radius: 8px;">Kirim Link Verifikasi</button>
            </form>
        </div>
    </div>
</div>
<script>
function togglePwd() {
    const inp = document.getElementById('pwd');
    const ico = document.getElementById('eyeIcon');
    if (inp.type === 'password') { inp.type = 'text'; ico.className = 'fas fa-eye-slash'; }
    else { inp.type = 'password'; ico.className = 'fas fa-eye'; }
}
document.getElementById('loginForm').addEventListener('submit', function() {
    document.getElementById('submitBtn').classList.add('loading');
});
</script>
</body>
</html>
