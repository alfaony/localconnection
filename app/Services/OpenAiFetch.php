<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\ApiLog; // Import Model ApiLog

class OpenAiFetch
{
    public function fetch($url, $headers = [], $params = [], $isMultipart = false)
    {
        try {
            // **1️⃣ Pilih format request (JSON atau multipart)**
            if ($isMultipart) {
                // 🚀 **Kirim sebagai multipart/form-data**
                $response = Http::withHeaders($headers)
                    ->asMultipart()
                    ->post($url, $params);
            } else {
                // 🚀 **Kirim sebagai JSON**
                $response = Http::withHeaders($headers)
                    ->post($url, $params);
            }
    
            // **2️⃣ Ambil status & response body**
            $statusCode = $response->status();
            $responsePayload = $response->json();
    
            // **3️⃣ Simpan ke API Log**
            $this->logApiRequest($url, 'POST', $params, $responsePayload, $statusCode);
    
            // **4️⃣ Cek jika ada error dalam response**
            if ($statusCode >= 400) {
                return response()->json([
                    'error' => 'HTTP request failed',
                    'status' => $statusCode,
                    'details' => $responsePayload
                ], $statusCode);
            }
    
            return $responsePayload;
        } catch (\Exception $e) {
            // **5️⃣ Simpan error ke API Log**
            $this->logApiRequest($url, 'POST', $params, ['error' => $e->getMessage()], 500);
    
            return response()->json([
                'error' => 'An error occurred while making HTTP request.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    
    protected function logApiRequest($endpoint, $method, $requestPayload, $responsePayload, $statusCode)
    {
        ApiLog::create([
            'user_id' => Auth::id(),
            'endpoint' => $endpoint,
            'method' => $method,
            'request_payload' => json_encode($requestPayload),
            'response_payload' => json_encode($responsePayload),
            'status_code' => $statusCode,
        ]);
    }
}