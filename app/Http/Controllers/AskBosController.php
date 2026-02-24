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
            'question' => 'required|string',
            'filters' => 'nullable|array'
        ]);

        $question = $validated['question'];
        $filters = $validated['filters'] ?? [];
        $userId = auth()->id();
        
        // Jalankan job ke queue
        $prompt = $this->prePromptDesition($request);
        ProcessOpenAiQuery::dispatch($prompt, $filters, $userId);

        return response()->json([
            'status' => 'processing',
            'message' => 'Pertanyaan sedang diproses, silakan tunggu beberapa detik.',
        ]);
    }


    /**
     * Fallback: ambil hasil dari cache jika broadcast gagal.
     * Dipanggil manual via tombol "Reload" di halaman.
     */
    public function checkResponse()
    {
        $userId   = auth()->id();
        $response = Cache::get("ai_response_{$userId}", null);

        if ($response) {
            Cache::forget("ai_response_{$userId}");
            return response()->json($response);
        }

        return response()->json(['status' => 'waiting'], 202);
    }

    private function prePrompt($request)
    {
        $user = Auth::user();
        $background = strip_tags($user->background) ?? '';
        $experience = strip_tags($user->experience) ?? '';
        $skill = strip_tags($user->skill) ?? '';

        $prompt  = "Profil Saya\n";
        $prompt .= "Nama: $user->name\n";
        $prompt .= "Pendidikan: $background\n";
        $prompt .= "Pengalaman Kerja: $experience\n";
        $prompt .= "Keterampilan: $skill\n";
        $achievement = json_decode($user->achievement) ?? [];
        $failure = json_decode($user->failure) ?? [];
        $prompt .= "Pencapaian: " . implode(', ', $achievement) . "\n";
        $prompt .= "Kegagalan: " . implode(', ', $failure) . "\n";

        $prompt .= "Saran: $user->name memberikan saran, $request->question.\n";

        $prompt .= "Analisa berdasarkan data di internet dan trends: \n";
        $prompt .= "Analisa berdasarkan latar belakang dan pengalaman dia: \n";
        $prompt .= "Analisa berdasarkan tingkat kepercayaan dan possibility benar: \n";
        $prompt .= "Berikan nilai score final: trust score 0 - 100. \n";
        $prompt .= "Analisa berdasarkan logika keputusan terbaik. terdapat minimal 15 pointers berupa pertimbangan, dan bagaimana cara naikin trust score jika nilai trust score di bawah 75.\n";
        
        $prompt .= "Tuliskan hasil analisa dalam format json seperti berikut: \n";
        $prompt .= "- Analysis: [Hasil analisis]\n";
        $prompt .= "- trust_score: [Nilai trust score dalam angka, 0-100]\n";

        return $prompt;
    }

    private function prePromptDesition($request)
    {
        $user = Auth::user();
        $backgroundDecision = strip_tags($user->background) ?? '';
        $experienceDecision = strip_tags($user->experience) ?? '';
        $skillDecision = strip_tags($user->skill) ?? '';

        $prompt = "$request->question \n";
        $prompt .= "Analisa pertanyaan diatas berdasarkan ini :\n";
        $prompt  .= "Profil Yang Memutuskan\n";
        $prompt .= "Nama: $user->name\n";
        $prompt .= "Pendidikan: $backgroundDecision\n";
        $prompt .= "Pengalaman Kerja: $experienceDecision\n";
        $prompt .= "Keterampilan: $skillDecision\n";
        $achievement = json_decode($user->achievement) ?? [];
        $failure = json_decode($user->failure) ?? [];
        $prompt .= "Pencapaian: " . implode(', ', $achievement) . "\n";
        $prompt .= "Kegagalan: " . implode(', ', $failure) . "\n";
        
        if($request->responsible)
        {
            $responsible = User::find($request->responsible);
            $backgroundResponsible = strip_tags($responsible->background) ?? '';
            $experienceResponsible = strip_tags($responsible->experience) ?? '';
            $skillResponsible = strip_tags($responsible->skill) ?? '';
    
            $prompt .= "Profil Yang Responsible\n";
            $prompt .= "Nama: $responsible->name\n";
            $prompt .= "Pendidikan: $backgroundResponsible\n";
            $prompt .= "Pengalaman Kerja: $experienceResponsible\n";
            $prompt .= "Keterampilan: $skillResponsible\n";
            $achievement = json_decode($responsible->achievement) ?? [];
            $failure = json_decode($responsible->failure) ?? [];
            $prompt .= "Pencapaian: " . implode(', ', $achievement) . "\n";
            $prompt .= "Kegagalan: " . implode(', ', $failure) . "\n";
            
        }

        $prompt .= "Buatkan analisa berdasarkan fakta, data yg ditemukan di internet juga. Analisa berdasarkan logika keputusan terbaik. Buatkan minimal 15 pointers berupa pertimbangan, dan bagaimana cara naikin trust score dan execution score ke 99 jika nilai trust score dan execution score di bawah 70.\n";
        $prompt .= "Jawablah dengan format : Trust Score 0 - 100 dengan mengamati kelengkapan informasi yang sudah dimiliki. Pekerjaan ini akan diserahkan kepada, sesuai dengan prinsip management RACI, analisa kesesuaian dan kemampuan pelaku dan hitung 0-100 execution score.\n";

        if($request->accountable)
        {
            $accountable = User::find($request->accountable);
            $backgroundAccountable = strip_tags($accountable->background) ?? '';
            $experienceAccountable = strip_tags($accountable->experience) ?? '';
            $skillAccountable = strip_tags($accountable->skill) ?? '';
    
            $prompt .= "Profil yang Accountable\n";
            $prompt .= "Accountable: $accountable->name\n";
            $prompt .= "Pendidikan: $backgroundAccountable\n";
            $prompt .= "Pengalaman Kerja: $experienceAccountable\n";
            $prompt .= "Keterampilan: $skillAccountable\n";
            $achievement = json_decode($accountable->achievement) ?? [];
            $failure = json_decode($accountable->failure) ?? [];
            $prompt .= "Pencapaian: " . implode(', ', $achievement) . "\n";
            $prompt .= "Kegagalan: " . implode(', ', $failure) . "\n";
            
        }

        if($request->consult)
        {
            $consult = User::find($request->consult);
            $backgroundConsult = strip_tags($consult->background) ?? '';
            $experienceConsult = strip_tags($consult->experience) ?? '';
            $skillConsult = strip_tags($consult->skill) ?? '';
    
            $prompt .= "Profil yang Consult\n";
            $prompt .= "Consult: $consult->name\n";
            $prompt .= "Pendidikan: $backgroundConsult\n";
            $prompt .= "Pengalaman Kerja: $experienceConsult\n";
            $prompt .= "Keterampilan: $skillConsult\n";
            $achievement = json_decode($consult->achievement) ?? [];
            $failure = json_decode($consult->failure) ?? [];
            $prompt .= "Pencapaian: " . implode(', ', $achievement) . "\n";
            $prompt .= "Kegagalan: " . implode(', ', $failure) . "\n";
            
        }else
        {
            $prompt .= "Consult : Tidak Ada\n";
        }
        $prompt .= "Tuliskan hasil analisa dalam format json seperti berikut: \n";
        $prompt .= "- Analysis: [Hasil analisis]\n";
        $prompt .= "- trust_score: [Nilai trust score dalam angka, 0-100]\n";
        $prompt .= "- execution_score: [Nilai  Execution Score dalam angka, 0-100]\n";

        return $prompt;
    }
}
