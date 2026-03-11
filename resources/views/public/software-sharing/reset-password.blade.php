<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru – {{ $company->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #de342f; --primary-d: #b91c1c; --primary-l: #fee2e2;
            --danger: #ef4444; --success: #10b981; --bg: #f8faff; --text: #1e293b;
            --muted: #64748b; --border: #e2e8f0;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg); min-height: 100vh; display: flex; }
        .page-wrap { display: flex; width: 100%; }
        .sidebar {
            width: 420px; flex-shrink: 0;
            background-color: #de342f;
            background-image: radial-gradient(circle at center, rgba(239,68,68,.8) 0%, rgba(219,39,41,0) 80%);
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
        .pass-toggle { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--muted); font-size: 14px; }
        .pass-toggle:hover { color: var(--primary); }

        /* Strength bar */
        .strength-bar { height: 4px; border-radius: 999px; background: var(--border); margin-top: 8px; overflow: hidden; }
        .strength-fill { height: 100%; border-radius: 999px; transition: width .3s, background .3s; width: 0; }

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

        @media(max-width: 768px) { .sidebar { display: none; } .form-side { padding: 32px 16px; align-items: flex-start; padding-top: 48px; } }
        @keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
    </style>
</head>
<body>
<div class="page-wrap">
    {{-- Sidebar --}}
    <aside class="sidebar">
        <h2>Buat<br>Password Baru</h2>
        <p>Pilih password yang kuat dan unik untuk menjaga keamanan akun Anda.</p>
        <ul class="feature-list">
            <li><i class="fas fa-check"></i> <span>Minimal 8 karakter</span></li>
            <li><i class="fas fa-check"></i> <span>Kombinasi huruf & angka</span></li>
            <li><i class="fas fa-check"></i> <span>Karakter spesial dianjurkan</span></li>
            <li><i class="fas fa-check"></i> <span>Jangan gunakan password lama</span></li>
        </ul>
    </aside>

    {{-- Form --}}
    <div class="form-side">
        <div class="form-box">
            <a href="{{ route('public.software-sharing.login', $companySlug) }}" class="brand-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Login
            </a>

            <h1>Password Baru</h1>
            <p class="sub">Masukkan password baru untuk akun <strong>{{ $email }}</strong></p>

            @if($errors->any())
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div>{{ $errors->first() }}</div>
            </div>
            @endif

            <form method="POST" action="{{ route('customer.password.reset', $companySlug) }}" id="resetForm">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" name="password" id="pwd"
                               class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Minimal 8 karakter" required>
                        <span class="pass-toggle" onclick="togglePwd('pwd','eye1')">
                            <i id="eye1" class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock field-icon"></i>
                        <input type="password" name="password_confirmation" id="pwd2"
                               class="form-control"
                               placeholder="Ulangi password baru" required>
                        <span class="pass-toggle" onclick="togglePwd('pwd2','eye2')">
                            <i id="eye2" class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span class="btn-text"><i class="fas fa-save"></i> Simpan Password Baru</span>
                    <span class="spinner"><i class="fas fa-spinner fa-spin"></i> Menyimpan...</span>
                </button>
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

// Password strength indicator
document.getElementById('pwd').addEventListener('input', function() {
    const val = this.value;
    const fill = document.getElementById('strengthFill');
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const colors = ['#ef4444','#f97316','#eab308','#10b981'];
    const widths = ['25%','50%','75%','100%'];
    fill.style.width  = score > 0 ? widths[score-1] : '0';
    fill.style.background = score > 0 ? colors[score-1] : 'transparent';
});

document.getElementById('resetForm').addEventListener('submit', function() {
    document.getElementById('submitBtn').classList.add('loading');
});
</script>
</body>
</html>
