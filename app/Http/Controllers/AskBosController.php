<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOpenAiQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\ServiceOpenAi;

class AskBosController extends Controller
{
    protected $openAiService;
    public function index(ServiceOpenAi $openAiService)
    {
        // $answer = $openAiService->askOpenAi("");
        // dd($answer);
        return view('ask_bos.index');
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
        ProcessOpenAiQuery::dispatch($question, $filters, $userId);

        return response()->json([
            'status' => 'processing',
            'message' => 'Pertanyaan sedang diproses, silakan tunggu beberapa detik.',
        ]);
    }

    public function checkResponse()
    {
        $userId = auth()->id();
        $response = Cache::get("ai_response_{$userId}", null);

        if ($response) {
            Cache::forget("ai_response_{$userId}"); // Hapus setelah ditampilkan
            return response()->json($response);
        }

        return response()->json(['status' => 'waiting'], 202);
    }
}
