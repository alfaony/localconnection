<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ApiLog;

class ServiceOpenAi
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = env('KELOOLA_OPENAI_API_URL') ?? "https://keloola-integration-ai.test/api";
        $this->apiKey = env('KELOOLA_OPENAI_TOKEN');
    }

    public function askOpenAi($prompt)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl."/chatgpt-text", 
            [
                'prompt' => $prompt,
            ]);


            return $response->json()['response'] ?? 'Tidak ada jawaban.';
        } catch (\Exception $e) {
            return 'Terjadi kesalahan saat memproses permintaan ke OpenAI: ' . $e->getMessage();
        }
    }

    public function fineTuneOpenAi($filePath, $table, $user)
    {

        try {
            // ** Periksa apakah file ada sebelum diunggah**
            if (!file_exists($filePath)) {
                return response()->json([
                    'error' => 'File not found',
                    'file_path' => $filePath
                ], 400);
            }
            // ** Kirim file JSONL ke OpenAI dengan multipart/form-data**
            $uploadResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->asMultipart()->post($this->apiUrl . "/chatgpt-fine-tune", [
                [
                    'name'     => 'file',
                    'contents' => fopen($filePath, 'r'),
                    'filename' => basename($filePath)
                ],
                [
                    'name'     => 'purpose',
                    'contents' => 'fine-tune'
                ],
                [
                    'name'     => 'table',
                    'contents' => $table
                ]
            ]);

            $fineTuneData = $uploadResponse->json();

            $this->logApiRequest('fine-tune', 'POST', ['filePath' => $filePath, 'table' => $table], $uploadResponse, 200, $user);

            return response()->json([
                'status' => 'Fine-tuning started successfully!',
                'fine_tune_id' => $fineTuneData['fine_tune_id'] ?? [],
            ]);
        } catch (\Throwable $th) {
            $this->logApiRequest('fine-tune', 'POST', ['filePath' => $filePath, 'table' => $table], $th->getMessage(), 500, $user);

            return response()->json([
                'error' => 'An unexpected error occurred.',
                'details' => $th->getMessage()
            ], 500);
        }
    }

    protected function logApiRequest($endpoint, $method, $requestPayload, $responsePayload, $statusCode, $user)
    {
        ApiLog::create([
            'user_id' => $user->id,
            'endpoint' => $endpoint,
            'method' => $method,
            'request_payload' => json_encode($requestPayload),
            'response_payload' => json_encode($responsePayload),
            'status_code' => $statusCode,
        ]);
    }
}