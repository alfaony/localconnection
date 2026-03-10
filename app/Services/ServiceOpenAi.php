<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\ApiLog;
use App\Models\User;
use App\Schemas\RoleSchema;

class ServiceOpenAi
{
    protected $apiUrl;
    protected $apiKey;
    protected  $model;

    public function __construct()
    {
        $this->apiUrl = env('KELOOLA_OPENAI_API_URL') ?? "https://keloola-integration-ai.test/api";
        $this->apiKey = env('KELOOLA_OPENAI_TOKEN');
        $this->model = env('KELOOLA_OPENAI_MODEL') ?? "gpt-3.5-turbo";
    }

    public function askOpenAi($prompt, $systemPrompt = null)
    {
        try {
            $payload = [
                'prompt' => $prompt,
                'model'  => $this->model
            ];

            if ($systemPrompt) {
                $payload['system_prompt'] = $systemPrompt;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(120)        
            ->retry(3, 5000)      
            ->post($this->apiUrl . "/chatgpt-text", [
                'prompt' => $prompt,
                'model'  => $this->model
            ]);
            $user = User::whereHas('role', function ($query) {
                $query->where('name', RoleSchema::ROOT);
            })->first();

            $this->logApiRequest('/chatgpt-text', 'POST', $prompt, $response, 200, $user);

            return $response->json()['response'] ?? 'Tidak ada jawaban.';
        } catch (\Exception $e) {
            return 'Terjadi kesalahan saat memproses permintaan ke OpenAI: ' . $e->getMessage();
        }
    }

    public function fineTuneOpenAi($filePath, $table, $user, $findFineTune)
    {

        try {
            // ** Periksa apakah file ada sebelum diunggah**
            if (!file_exists($filePath)) {
                return response()->json([
                    'error' => 'File not found',
                    'file_path' => $filePath
                ], 400);
            }
            $params = 
            [
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
            ];

            if($findFineTune)
            {
                $params[] = 
                [
                    'name'     => 'model',
                    'contents' => $findFineTune->fine_tune_model
                ];
            }

            // ** Kirim file JSONL ke OpenAI dengan multipart/form-data**
            $uploadResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->asMultipart()->post($this->apiUrl . "/chatgpt-fine-tune", $params);

            $fineTuneData = $uploadResponse->json();
            
            $this->logApiRequest('fine-tune', 'POST', ['filePath' => $filePath, 'table' => $table, 'model' => $model ?? NULL], $uploadResponse, 200, $user);

            return response()->json([
                'status' => 'Fine-tuning started successfully!',
                'response' => $fineTuneData ?? [],
            ]);
        } catch (\Throwable $th) {
            $this->logApiRequest('fine-tune', 'POST', ['filePath' => $filePath, 'table' => $table], $th->getMessage(), 500, $user);

            return response()->json([
                'error' => 'An unexpected error occurred.',
                'details' => $th->getMessage()
            ], 500);
        }
    }

    public function retriveFineTune($fineTuneId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->apiUrl."/chatgpt-retriveFineTune/". $fineTuneId);
            
            return response()->json([
                'status' => 'Fine Tunning Retrived successfully!',
                'response' => $response->json()['data']  ?? NULL 
            ]);

        } catch (\Exception $e) {
            $this->logApiRequest('fine-tune', 'POST', ['fineTuneId' => $fineTuneId], $th->getMessage(), 500, $user);

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