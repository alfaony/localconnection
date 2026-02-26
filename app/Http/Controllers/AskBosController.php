<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOpenAiQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Services\ServiceOpenAi;
use App\Models\User;

class AskBosController extends Controller
{
    protected $openAiService;
    public function index(ServiceOpenAi $openAiService)
    {
        $users = User::byCompany(Auth::user()->company_id)->get();
        return view('ask_bos.index', compact('users'));
    }

    public function ask(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'filters' => 'nullable|array'
        ]);

        $question = $validated['question'];
        $filters = $validated['filters'] ?? [];
        $userId = auth()->id();
        
        // Jalankan job ke queue
        $prompt = $this->prePrompt($request);
        // dd($prompt, $filters);

        ProcessOpenAiQuery::dispatch($prompt, $filters, $userId);

        return response()->json([
            'status' => 'processing',
            'message' => 'Pertanyaan sedang diproses, silakan tunggu beberapa detik.',
        ]);
    }

    public function makeDesition(Request $request)
    {
        $validated = $request->validate([
            'question'       => 'required|string',
            'filters'        => 'nullable|array',
            'nominal'        => 'nullable|numeric|min:0',
            'consult_vendor' => 'nullable|string|max:255',
        ]);

        $filters = $validated['filters'] ?? [];
        $userId  = auth()->id();

        $prompt = $this->prePromptDesition($request);
        ProcessOpenAiQuery::dispatch($prompt, $filters, $userId);

        return response()->json([
            'status'  => 'processing',
            'message' => 'Pertanyaan sedang diproses, silakan tunggu beberapa detik.',
        ]);
    }


    /**
     * Fallback: ambil hasil dari cache jika broadcast gagal.
     * Dipanggil manual via tombol "Reload" di halaman.
     */
    public function checkResponse(Request $request)
    {
        $userId   = auth()->id();
        $cacheKey = $request->input('cache_key');

        if (!$cacheKey) {
            $cacheKey = Cache::get("latest_ai_response_{$userId}");
        }

        if ($cacheKey) {
            $response = Cache::get($cacheKey, null);
            if ($response) {
                // Return payload utuh (bisa diambil ulang selama masih ada di cache 10 menit)
                return response()->json($response);
            }
        }

        return response()->json(['status' => 'waiting'], 202);
    }

    private function prePrompt($request)
    {
        $user = Auth::user();
        $background = strip_tags($user->background) ?? '';
        $experience = strip_tags($user->experience) ?? '';
        $skill      = strip_tags($user->skill) ?? '';
        $achievement = json_decode($user->achievement) ?? [];
        $failure     = json_decode($user->failure) ?? [];

        $prompt  = "Kamu adalah sistem B.O.S (Business Operating System) — AI advisor berbasis data nyata dari internet.\n";
        $prompt .= "Tugasmu adalah memberikan PENILAIAN (assessment) faktual, bukan daftar saran generik.\n";
        $prompt .= "Selalu dasarkan analisa pada data nyata, riset, dan fakta yang dapat diverifikasi.\n\n";

        $prompt .= "=== PERTANYAAN/TOPIK ===\n";
        $prompt .= "$request->question\n\n";

        $prompt .= "=== PROFIL PEMBERI SARAN ===\n";
        $prompt .= "Nama: $user->name\n";
        $prompt .= "Latar Belakang Pendidikan: $background\n";
        $prompt .= "Pengalaman Kerja: $experience\n";
        $prompt .= "Keterampilan: $skill\n";
        $prompt .= "Pencapaian: " . implode(', ', $achievement) . "\n";
        $prompt .= "Riwayat Kegagalan: " . implode(', ', $failure) . "\n\n";

        $prompt .= "=== YANG HARUS KAMU NILAI ===\n";
        $prompt .= "1. KREDIBILITAS SARAN: Apakah background, pengalaman, dan keterampilan orang ini memadai untuk memberikan saran tentang topik tersebut? Nilai secara jujur — apakah mereka kompeten di bidang ini berdasarkan profil di atas?\n";
        $prompt .= "2. VALIDASI FAKTUAL: Berdasarkan data dan riset terkini (2023-2025) dari internet, apakah saran/pernyataan mereka akurat? Sebutkan fakta konkret yang mendukung atau membantah.\n";
        $prompt .= "3. TRUST SCORE: Berikan nilai 0-100 — 100 berarti saran sangat valid dan orang ini sangat kompeten, 0 berarti tidak relevan sama sekali.\n\n";

        $prompt .= "INSTRUKSI OUTPUT — WAJIB DIIKUTI:\n";
        $prompt .= "Respond HANYA dengan JSON murni. JANGAN tambahkan teks apapun di luar JSON.\n";
        $prompt .= '{"Analysis": "<assessment faktual lengkap: nilai kredibilitas pemberi saran, validasi dengan data nyata, verdict akhir>", "trust_score": <angka 0-100>}' . "\n";
        $prompt .= "JANGAN output apapun selain JSON tersebut.\n";

        return $prompt;
    }

    private function prePromptDesition($request)
    {
        $user = Auth::user();
        $backgroundDecision = strip_tags($user->background) ?? '';
        $experienceDecision = strip_tags($user->experience) ?? '';
        $skillDecision      = strip_tags($user->skill) ?? '';
        $achievement = json_decode($user->achievement) ?? [];
        $failure     = json_decode($user->failure) ?? [];

        $prompt  = "Kamu adalah sistem B.O.S (Business Operating System) — AI advisor berbasis data nyata.\n";
        $prompt .= "Tugasmu: berikan PENILAIAN FAKTUAL (bukan daftar saran/tips generik) terhadap keputusan bisnis ini.\n";
        $prompt .= "Gunakan data nyata, riset terkini, dan fakta yang dapat diverifikasi sebagai dasar analisa.\n\n";

        $prompt .= "=== KEPUTUSAN YANG DIANALISA ===\n";
        $prompt .= "$request->question\n\n";

        $prompt .= "=== PROFIL YANG MEMUTUSKAN (Decision Maker) ===\n";
        $prompt .= "Nama: $user->name | Pendidikan: $backgroundDecision | Pengalaman: $experienceDecision | Skill: $skillDecision\n";
        $prompt .= "Pencapaian: " . implode(', ', $achievement) . " | Kegagalan: " . implode(', ', $failure) . "\n\n";

        if ($request->responsible) {
            $responsible = User::find($request->responsible);
            $prompt .= "=== PROFIL PELAKSANA (Responsible) ===\n";
            $prompt .= "Nama: $responsible->name\n";
            $prompt .= "Pendidikan: " . strip_tags($responsible->background ?? '') . "\n";
            $prompt .= "Pengalaman: " . strip_tags($responsible->experience ?? '') . "\n";
            $prompt .= "Skill: " . strip_tags($responsible->skill ?? '') . "\n";
            $rAch = json_decode($responsible->achievement) ?? [];
            $rFail = json_decode($responsible->failure) ?? [];
            $prompt .= "Pencapaian: " . implode(', ', $rAch) . " | Kegagalan: " . implode(', ', $rFail) . "\n\n";
        }

        if ($request->accountable) {
            $accountable = User::find($request->accountable);
            $prompt .= "=== PROFIL PENANGGUNG JAWAB (Accountable) ===\n";
            $prompt .= "Nama: $accountable->name\n";
            $prompt .= "Pendidikan: " . strip_tags($accountable->background ?? '') . "\n";
            $prompt .= "Pengalaman: " . strip_tags($accountable->experience ?? '') . "\n";
            $prompt .= "Skill: " . strip_tags($accountable->skill ?? '') . "\n";
            $aAch = json_decode($accountable->achievement) ?? [];
            $aFail = json_decode($accountable->failure) ?? [];
            $prompt .= "Pencapaian: " . implode(', ', $aAch) . " | Kegagalan: " . implode(', ', $aFail) . "\n\n";
        }

        if ($request->consult) {
            $consult = User::find($request->consult);
            $prompt .= "=== PROFIL KONSULTAN INTERNAL (Consult) ===\n";
            $prompt .= "Nama: $consult->name\n";
            $prompt .= "Pendidikan: " . strip_tags($consult->background ?? '') . "\n";
            $prompt .= "Pengalaman: " . strip_tags($consult->experience ?? '') . "\n";
            $prompt .= "Skill: " . strip_tags($consult->skill ?? '') . "\n";
            $cAch = json_decode($consult->achievement) ?? [];
            $cFail = json_decode($consult->failure) ?? [];
            $prompt .= "Pencapaian: " . implode(', ', $cAch) . " | Kegagalan: " . implode(', ', $cFail) . "\n\n";
        } elseif ($request->filled('consult_vendor')) {
            $vendorName    = $request->consult_vendor;
            $nominalFormat = 'Rp ' . number_format((float) ($request->nominal ?? 0), 0, ',', '.');
            $prompt .= "=== VENDOR/KONSULTAN EKSTERNAL ===\n";
            $prompt .= "Nama Vendor: $vendorName\n";
            $prompt .= "Nilai Kontrak: $nominalFormat\n\n";
        } else {
            $prompt .= "=== KONSULTAN: Tidak Ada ===\n\n";
        }

        if ($request->filled('nominal') && (float) $request->nominal > 0) {
            $nominalFormat = 'Rp ' . number_format((float) $request->nominal, 0, ',', '.');
            $prompt .= "=== NILAI KEPUTUSAN/TRANSAKSI ===\n";
            $prompt .= "Nominal: $nominalFormat\n\n";
        }

        $prompt .= "=== YANG HARUS KAMU NILAI (secara faktual, bukan tips) ===\n";
        $prompt .= "1. KESIAPAN TIM: Apakah latar belakang, pengalaman, dan keterampilan masing-masing orang (Decision Maker, Responsible, Accountable, Consult) BENAR-BENAR memadai untuk keputusan ini? Nilai secara jujur dan spesifik — jangan generik.\n";
        $prompt .= "2. KELAYAKAN KEPUTUSAN: Berdasarkan data dan riset nyata (fakta industri, statistik, precedent), apakah keputusan ini layak dilakukan? Sebutkan fakta konkret.\n";


        if ($request->filled('consult_vendor')) {
            $prompt .= "3. KREDIBILITAS VENDOR: Cari dan nilai vendor '$request->consult_vendor' — apakah dikenal di industri? Rekam jejak? Risiko kerjasama pada nilai $nominalFormat? Apakah wajar harga untuk scope ini?\n";
            $prompt .= "4. EXECUTION SCORE: Seberapa besar kemungkinan tim ini berhasil mengeksekusi keputusan ini bersama vendor tersebut? (0-100)\n";
        } else {
            $prompt .= "3. EXECUTION SCORE: Seberapa besar kemungkinan tim ini berhasil mengeksekusi keputusan ini? (0-100)\n";
        }
        $prompt .= "5. TRUST SCORE: Seberapa valid dan dapat dipercaya keputusan ini secara keseluruhan? (0-100)\n";
        $prompt .= "6. VERDICT: Berikan kesimpulan yang tegas — apakah keputusan ini layak dilanjutkan atau tidak, dan mengapa. Serta berikan Solusi untuk mengatasi kekurangan yang ada. Dan meningkatkan EXECUTION SCORE dan TRUST SCORE.\n\n";

        $prompt .= "INSTRUKSI OUTPUT — WAJIB DIIKUTI:\n";
        $prompt .= "Respond HANYA dengan JSON murni. JANGAN tambahkan teks apapun di luar JSON.\n";
        $prompt .= '{"Analysis": "<assessment faktual: kesiapan tim, kelayakan keputusan, kredibilitas vendor jika ada, verdict akhir — tulis seperti laporan analis profesional, bukan daftar tips>", "trust_score": <angka 0-100>, "execution_score": <angka 0-100>}' . "\n";
        $prompt .= "JANGAN output apapun selain JSON tersebut.\n";

        return $prompt;
    }
}
