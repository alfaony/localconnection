<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\ApiLog;

class DeviceService
{
    /**
     * Fetch devices from the external API.
     */
    public function fetchDevices($page = 1, $search = '')
    {
        $url = config('services.device_iot_api.url');
        $status = config('services.device_iot_api.url_device_device');
        $token = config('services.device_iot_api.Authorization');
        $companyId = Auth::user()->company_id; // Assuming Auth::user() is available

        $queryParams = [
            'company_id' => $companyId,
            'page'       => $page,
            'search'     => $search,
        ];

        try {
            // Make the API request with pagination and search filters
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept'        => 'application/json',
            ])->get($url, $queryParams);

            // Log the successful request
            ApiLog::create([
                'user_id' => Auth::id(),
                'endpoint' => $url,
                'method' => 'GET',
                'request_payload' => json_encode($queryParams),
                'response_payload' => json_encode($response->json()),
                'status_code' => $response->status(),
            ]);

            if ($response->successful()) {
                return [
                    'success'    => true,
                    'devices'    => $response->json('data'),
                    'pagination' => $response->json('pagination'),
                ];
            } else {    
                return [
                    'success' => false,
                    'message' => 'Failed to fetch devices from API.',
                    'status'  => $response->status()
                ];
            }
            
        } catch (\Exception $e) {
            // Log the exception details
            ApiLog::create([
                'user_id' => Auth::user()->id,
                'endpoint' => $url,
                'method' => 'GET',
                'request_payload' => json_encode($queryParams),
                'error_message' => $e->getMessage(),
            ]);

            // Optionally rethrow the exception if you want to handle it elsewhere
            throw $e;
        }
    }

    public function listDeviceOpen($companyId, $user)
    {
        $urlStatus = config('services.device_iot_api.url_device_device');
        $token = config('services.device_iot_api.Authorization');

        $queryParams = [
            'company_id' => $companyId,
        ];

        try {
            // Make the API request with pagination and search filters
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept'        => 'application/json',
            ])->get($urlStatus, $queryParams);

            // Log the successful request
            ApiLog::create([
                'user_id' => $user->id,
                'endpoint' => $urlStatus,
                'method' => 'GET',
                'request_payload' => json_encode($queryParams),
                'response_payload' => json_encode($response->json()),
                'status_code' => $response->status(),
            ]);

            if ($response->successful()) {
                return [
                    'success'    => true,
                    'devices'    => $response->json('data'),
                ];
            } else {    
                return [
                    'success' => false,
                    'message' => 'Failed to fetch devices from API.',
                    'status'  => $response->status()
                ];
            }
            
        } catch (\Exception $e) {
            // Log the exception details
            ApiLog::create([
                'user_id' => $user->id,
                'endpoint' => $urlStatus,
                'method' => 'GET',
                'request_payload' => json_encode($queryParams),
                'error_message' => $e->getMessage(),
            ]);

            // Optionally rethrow the exception if you want to handle it elsewhere
            throw $e;
        }
    }

}
