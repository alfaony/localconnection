<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ServiceOpenAi
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = env('KELOOLA_OPENAI_API_URL');
        $this->apiKey = env('KELOOLA_OPENAI_TOKEN');
    }

    public function askOpenAi($prompt)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, 
            [
                'prompt' => $prompt,
            ]);

            return $response->json()['response'] ?? 'Tidak ada jawaban.';
        } catch (\Exception $e) {
            return 'Terjadi kesalahan saat memproses permintaan ke OpenAI: ' . $e->getMessage();
        }
    }
}