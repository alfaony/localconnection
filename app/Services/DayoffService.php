<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DayoffService
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.cuti_api.base_url');
        $this->token = config('services.cuti_api.token');
    }

    /**
     * Get Cuti List from the BOS API.
     *
     * @return mixed
     */
    public function getCutiListBOS()
    {
        try {
            $response = Http::withToken($this->token)->get("{$this->baseUrl}/getCutiListBOS");

            // Check if the request was successful
            if ($response->successful()) 
            {
                return [
                    'error' => false,
                    'data' => $response->json(), // Return the response as JSON
                ];
            }

            // Handle response errors
            return [
                'error' => true,
                'message' => $response->body() ?? 'Error fetching Cuti List from BOS API.',
            ];

        } catch (\Exception $e) {
            // Handle exception errors
            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }
}
