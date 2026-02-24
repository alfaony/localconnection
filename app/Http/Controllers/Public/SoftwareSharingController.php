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

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class SoftwareSharingController extends Controller
{
    /**
     * Redirect ke company pertama yang tersedia
     * URL: /software-sharing
     */
    public function redirectToFirst()
    {
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

            // Kirim email verifikasi (tidak auto-login)
            $user->sendEmailVerificationNotification();

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
        $company = Company::where('slug', $companySlug)->firstOrFail();
        return view('public.software-sharing.login', compact('company', 'companySlug'));
    }

    /**
     * Proses login untuk customer software
     */
    public function login(Request $request, string $companySlug)
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();

        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (auth()->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Cek verifikasi email sebelum mengizinkan masuk
            if (!auth()->user()->hasVerifiedEmail()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->back()
                    ->withInput($request->only('email'))
                    ->withErrors(['email' => 'Email Anda belum diverifikasi. Silakan cek inbox dan klik link verifikasi yang kami kirimkan.']);
            }

            return redirect()
                ->intended(route('customer-software.index'))
                ->with('success', 'Selamat datang kembali, ' . auth()->user()->name . '!');
        }

        return redirect()
            ->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Email atau password tidak cocok.']);
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
            $user->sendEmailVerificationNotification();
            return redirect()->back()->with('success', 'Link verifikasi ulang telah dikirim ke email Anda. Silakan periksa inbox atau folder spam.');
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Email tidak ditemukan, sudah diverifikasi, atau tidak terdaftar di perusahaan ini.']);
    }
}
