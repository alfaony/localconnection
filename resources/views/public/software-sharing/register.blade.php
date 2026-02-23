<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun – {{ $company->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #6366f1; --primary-d: #4f46e5; --primary-l: #e0e7ff;
            --success: #10b981; --danger: #ef4444;
            --bg: #f8faff; --card: #fff; --text: #1e293b; --muted: #64748b;
            --border: #e2e8f0; --radius: 16px;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); min-height: 100vh; display: flex; flex-direction: column; }

        /* ── Split Layout ── */
        .page-wrap { display: flex; min-height: 100vh; }
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

        /* ── Form Side ── */
        .form-side { flex: 1; display: flex; align-items: center; justify-content: center; padding: 48px 24px; }
        .form-box { width: 100%; max-width: 460px; }
        .brand-back { display: flex; align-items: center; gap: 8px; color: var(--muted); font-size: 14px; text-decoration: none; margin-bottom: 32px; }
        .brand-back:hover { color: var(--primary); }
        .form-box h1 { font-size: 1.75rem; font-weight: 800; margin-bottom: 6px; }
        .form-box .sub { color: var(--muted); font-size: 14px; margin-bottom: 32px; }
        .form-box .sub a { color: var(--primary); font-weight: 600; text-decoration: none; }
        .form-box .sub a:hover { text-decoration: underline; }

        /* Alert */
        .alert {
            padding: 12px 16px; border-radius: 10px; margin-bottom: 20px;
            font-size: 13.5px; display: flex; align-items: flex-start; gap: 10px;
        }
        .alert-danger  { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert ul { margin: 4px 0 0 16px; }

        /* Form group */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { margin-bottom: 20px; }
        .form-label { font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 6px; display: block; }
        .input-wrap { position: relative; }
        .input-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 14px; pointer-events: none; }
        .form-control {
            width: 100%; padding: 11px 14px 11px 40px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-size: 14.5px; font-family: inherit; color: var(--text);
            transition: border-color .2s, box-shadow .2s; background: #fff; outline: none;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
        .form-control.is-invalid { border-color: var(--danger); }
        .invalid-feedback { font-size: 12px; color: var(--danger); margin-top: 5px; }

        /* Password toggle */
        .pass-toggle { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--muted); font-size: 14px; }
        .pass-toggle:hover { color: var(--primary); }

        /* Submit */
        .btn-submit {
            width: 100%; padding: 14px; background: var(--primary); color: #fff;
            border: none; border-radius: 12px; font-size: 16px; font-weight: 700;
            cursor: pointer; font-family: inherit; letter-spacing: .3px;
            transition: background .2s, transform .15s, box-shadow .2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover { background: var(--primary-d); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,.35); }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit .spinner { display: none; }
        .btn-submit.loading .spinner { display: inline-block; }
        .btn-submit.loading .btn-text { display: none; }

        .divider { text-align: center; position: relative; margin: 20px 0; color: var(--muted); font-size: 13px; }
        .divider::before { content:''; position: absolute; left: 0; top: 50%; width: 100%; height: 1px; background: var(--border); }
        .divider span { position: relative; background: var(--bg); padding: 0 12px; }

        .terms { font-size: 12px; color: var(--muted); text-align: center; margin-top: 16px; line-height: 1.5; }
        .terms a { color: var(--primary); }

        @media(max-width: 768px) {
            .sidebar { display: none; }
            .form-side { padding: 32px 16px; align-items: flex-start; padding-top: 48px; }
        }
        @keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        .form-box { animation: fadeInUp .4s ease; }
    </style>
</head>
<body>
<div class="page-wrap">

    {{-- Sidebar --}}
    <aside class="sidebar">
        <h2>Bergabung dengan<br>{{ $company->name }}</h2>
        <p>Akses ratusan software premium dengan harga berbagi yang sangat hemat.</p>
        <ul class="feature-list">
            <li><i class="fas fa-shield-alt"></i> <span>Akun aman & terverifikasi</span></li>
            <li><i class="fas fa-bolt"></i> <span>Aktivasi cepat setelah pembayaran</span></li>
            <li><i class="fas fa-headset"></i> <span>Support tim siap membantu 24/7</span></li>
            <li><i class="fas fa-tags"></i> <span>Harga sharing jauh lebih hemat</span></li>
        </ul>
    </aside>

    {{-- Form --}}
    <div class="form-side">
        <div class="form-box">

            <a href="{{ route('public.software-sharing.index', $companySlug) }}" class="brand-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Katalog
            </a>

            <h1>Buat Akun Baru</h1>
            <p class="sub">Sudah punya akun? <a href="{{ route('public.software-sharing.login', $companySlug) }}">Masuk di sini</a></p>

            {{-- Flash --}}
            @if(session('error'))
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <span>{{ session('error') }}</span></div>
            @endif
            @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Periksa kembali data Anda:</strong>
                    <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('public.software-sharing.register.post', $companySlug) }}" id="regForm">
                @csrf

                {{-- Nama --}}
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span style="color:#ef4444">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-user"></i>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               placeholder="Nama lengkap" value="{{ old('name') }}" required>
                    </div>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Email & Phone side by side --}}
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email <span style="color:#ef4444">*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                   placeholder="email@example.com" value="{{ old('email') }}" required>
                        </div>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. WhatsApp</label>
                        <div class="input-wrap">
                            <i class="fas fa-phone"></i>
                            <input type="text" name="phone" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                   placeholder="08xx..." value="{{ old('phone') }}">
                        </div>
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                {{-- Password --}}
                <div class="form-group">
                    <label class="form-label">Password <span style="color:#ef4444">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="pwd" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Minimal 8 karakter" required>
                        <span class="pass-toggle" onclick="togglePwd('pwd','eyePwd')">
                            <i id="eyePwd" class="fas fa-eye"></i>
                        </span>
                    </div>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Konfirmasi --}}
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password <span style="color:#ef4444">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password_confirmation" id="pwd2" class="form-control"
                               placeholder="Ulangi password" required>
                        <span class="pass-toggle" onclick="togglePwd('pwd2','eyePwd2')">
                            <i id="eyePwd2" class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span class="btn-text"><i class="fas fa-user-plus"></i> Buat Akun Sekarang</span>
                    <span class="spinner"><i class="fas fa-spinner fa-spin"></i> Memproses...</span>
                </button>

                <p class="terms">
                    Dengan mendaftar, Anda menyetujui <a href="#">Syarat & Ketentuan</a>
                    dan <a href="#">Kebijakan Privasi</a> kami.
                </p>
            </form>
        </div>
    </div>
</div>

<script>
function togglePwd(id, iconId) {
    const inp = document.getElementById(id);
    const ico = document.getElementById(iconId);
    if (inp.type === 'password') { inp.type = 'text'; ico.className = 'fas fa-eye-slash'; }
    else { inp.type = 'password'; ico.className = 'fas fa-eye'; }
}
document.getElementById('regForm').addEventListener('submit', function() {
    document.getElementById('submitBtn').classList.add('loading');
});
</script>
</body>
</html>
