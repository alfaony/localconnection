<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Software;
use App\Models\User;
use App\Models\Role;
use App\Schemas\RoleSchema;
use App\Models\SoftwareSharing;
use App\Models\SettingCompany;
use App\Notifications\VerifyEmailCustomer;
use App\Notifications\ResetPasswordCustomer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class SoftwareSharingController extends Controller
{
    /**
     * Redirect ke company pertama yang tersedia
     * URL: /software-sharing
     */
    /**
     * Cek apakah user sudah login sebagai Customer Software
     */
    private function isLoggedInCustomer(): bool
    {
        return auth()->check()
            && auth()->user()->role?->name === RoleSchema::CUSTOMER_SOFTWARE;
    }

    public function redirectToFirst()
    {
        // Kalau sudah login sebagai Customer Software → langsung ke dashboard
        if ($this->isLoggedInCustomer()) {
            return redirect()->route('customer-software.index');
        }

        // Guest → cari company pertama yang punya software aktif
        $company = Company::whereHas('softwares', function ($q) {
            $q->where('status', 'active');
        })->first();

        if (!$company) {
            abort(404, 'Tidak ada perusahaan yang tersedia.');
        }

        return redirect()->route('public.software-sharing.index', $company->slug);
    }

    /**
     * Halaman publik: daftar semua software milik company
     * URL: /customer-software/registration/{companySlug}
     */
    public function index(string $companySlug)
    {
        // Sudah login → ke dashboard
        if ($this->isLoggedInCustomer()) {
            return redirect()->route('customer-software.index');
        }

        $company = Company::where('slug', $companySlug)->firstOrFail();
        $settingCompany = SettingCompany::byCompany($company->id)->where('menu','software_sharing_setting')->get()->pluck('field_value', 'field_title')->toArray();

        $softwares = Software::where('company_id', $company->id)
            ->where('status', 'active')
            ->with(['activePackages', 'availableMasterAccounts'])
            ->get()
            ->map(function ($s) {
                $s->has_available_slots = $s->availableMasterAccounts->isNotEmpty();
                $s->min_price           = $s->activePackages->min('harga');
                $s->cheapest_package    = $s->activePackages
                    ->where('harga', $s->min_price)
                    ->first();
                return $s;
            });

        return view('public.software-sharing.index', compact('company', 'softwares', 'settingCompany'));
    }

    /**
     * Tampilkan form registrasi untuk login/daftar, lalu redirect ke catalog
     * URL: /customer-software/registration/{companySlug}/register
     */
    public function showRegister(string $companySlug)
    {
        // Sudah login → ke dashboard
        if ($this->isLoggedInCustomer()) {
            return redirect()->route('customer-software.index');
        }

        $company = Company::where('slug', $companySlug)->firstOrFail();
        return view('public.software-sharing.register', compact('company', 'companySlug'));
    }

    /**
     * Proses registrasi user baru
     */
    public function register(Request $request, string $companySlug)
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();
        $role = Role::where('name', RoleSchema::CUSTOMER_SOFTWARE)->firstOrFail();
        
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar. Silakan login.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
        ]);
        
        DB::beginTransaction();
        try {
            $user = User::create([
                'name'       => $request->name,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'password'   => Hash::make($request->password),
                'company_id' => $company->id,
                'role_id'    => $role->id,
            ]);
            
            DB::commit();

            // Kirim custom email verifikasi dengan UI rapi + redirect ke customer login
            $user->notify(new VerifyEmailCustomer($companySlug));

            return redirect()
                ->route('public.software-sharing.login', $companySlug)
                ->with('verify_email', true)
                ->with('success', 'Akun berhasil dibuat! Silakan cek email Anda dan klik link verifikasi sebelum login.');

        } catch (\Throwable $th) {
            // dd($th);

            DB::rollBack();
            Log::error('Software sharing registration error: ' . $th->getMessage());

            return redirect()
                ->back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Registrasi gagal. Silakan coba lagi.');
        }
    }

    /**
     * Tampilkan form login dan redirect ke catalog setelah login
     * URL: /customer-software/registration/{companySlug}/login
     */
    public function showLogin(string $companySlug)
    {
        // Sudah login → ke dashboard
        if ($this->isLoggedInCustomer()) {
            return redirect()->route('customer-software.index');
        }

        $company = Company::where('slug', $companySlug)->firstOrFail();
        return view('public.software-sharing.login', compact('company', 'companySlug'));
    }

    /**
     * Proses login untuk customer software — mendukung email ATAU username
     */
    public function login(Request $request, string $companySlug)
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();

        $request->validate([
            'login_field' => ['required', 'string'],
            'password'    => ['required'],
        ]);

        $loginField = $request->login_field;
        $password   = $request->password;
        $remember   = $request->boolean('remember');

        // Coba login via email terlebih dahulu
        $loggedIn = false;
        if (filter_var($loginField, FILTER_VALIDATE_EMAIL)) {
            $loggedIn = auth()->attempt(['email' => $loginField, 'password' => $password], $remember);
        }

        // Jika gagal atau bukan format email, coba via username (scoped ke company)
        if (!$loggedIn) {
            $user = User::where('company_id', $company->id)
                        ->where('username', $loginField)
                        ->whereHas('role', fn($q) => $q->where('name', RoleSchema::CUSTOMER_SOFTWARE))
                        ->first();

            if ($user && Hash::check($password, $user->password)) {
                auth()->login($user, $remember);
                $loggedIn = true;
            }
        }

        if ($loggedIn) {
            $request->session()->regenerate();

            // Cek verifikasi email — user tanpa email (login via username) dianggap terverifikasi
            if (!auth()->user()->hasVerifiedEmail()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->back()
                    ->withInput($request->only('login_field'))
                    ->withErrors(['login_field' => 'Email Anda belum diverifikasi. Silakan cek inbox dan klik link verifikasi yang kami kirimkan.']);
            }

            return redirect()
                ->intended(route('customer-software.index'))
                ->with('success', 'Selamat datang kembali, ' . auth()->user()->name . '!');
        }

        return redirect()
            ->back()
            ->withInput($request->only('login_field'))
            ->withErrors(['login_field' => 'Email/username atau password tidak cocok.']);
    }

    /**
     * Kirim ulang email verifikasi
     */
    public function resendVerification(Request $request, string $companySlug)
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();

        $request->validate([
            'email' => ['required', 'email']
        ]);

        $user = User::where('email', $request->email)
                    ->where('company_id', $company->id)
                    ->first();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->notify(new VerifyEmailCustomer($companySlug));
            return redirect()->back()->with('success', 'Link verifikasi ulang telah dikirim ke email Anda. Silakan periksa inbox atau folder spam.');
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Email tidak ditemukan, sudah diverifikasi, atau tidak terdaftar di perusahaan ini.']);
    }

    /**
     * Verifikasi email customer dan redirect ke halaman login company
     */
    public function verifyEmail(Request $request, string $id, string $hash)
    {
        $user = User::findOrFail($id);

        // Validasi hash
        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Link verifikasi tidak valid.');
        }

        // Ambil company_slug dari query param
        $companySlug = $request->query('company_slug');

        // Jika belum diverifikasi, tandai sekarang
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        // Redirect ke customer login page
        $loginRoute = $companySlug
            ? route('public.software-sharing.login', $companySlug)
            : route('login');

        return redirect($loginRoute)
            ->with('success', '✅ Email Anda berhasil diverifikasi! Silakan login untuk mulai menggunakan layanan.');
    }

    // ============================================================
    // FORGOT PASSWORD
    // ============================================================

    /**
     * Form request reset password
     */
    public function showForgotPassword(string $companySlug)
    {
        if ($this->isLoggedInCustomer()) {
            return redirect()->route('customer-software.index');
        }
        $company = Company::where('slug', $companySlug)->firstOrFail();
        return view('public.software-sharing.forgot-password', compact('company', 'companySlug'));
    }

    /**
     * Kirim link reset password via email
     */
    public function sendResetLink(Request $request, string $companySlug)
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();

        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
        ]);

        // Cari user berdasarkan email DAN company
        $user = User::where('email', $request->email)
                    ->where('company_id', $company->id)
                    ->first();

        // Selalu tampilkan pesan sukses (mencegah user enumeration)
        if ($user) {
            // Generate token manual via Password broker
            $token = app('auth.password.broker')->createToken($user);
            $user->notify(new ResetPasswordCustomer($token, $companySlug));
        }

        return redirect()->back()
            ->with('success', '📩 Jika email terdaftar, link reset password telah dikirim. Silakan cek inbox Anda.');
    }

    /**
     * Tampilkan form reset password baru
     */
    public function showResetPassword(Request $request, string $companySlug, string $token)
    {
        if ($this->isLoggedInCustomer()) {
            return redirect()->route('customer-software.index');
        }
        $company = Company::where('slug', $companySlug)->firstOrFail();
        $email   = $request->query('email', '');

        return view('public.software-sharing.reset-password', compact('company', 'companySlug', 'token', 'email'));
    }

    /**
     * Proses reset password baru
     */
    public function resetPassword(Request $request, string $companySlug)
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();

        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min'       => 'Password minimal 8 karakter.',
        ]);

        // Verifikasi token via Password broker
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('public.software-sharing.login', $companySlug)
                ->with('success', '🔒 Password berhasil direset! Silakan login dengan password baru Anda.');
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
