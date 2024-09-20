<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    /**
     * Display the initial page.
     */
    public function index()
    {
        return view('device.index');
    }

    /**
     * Fetch device data from the API and return it for the frontend.
     */
    public function dataJson(Request $request)
    {
        $url = config('services.device_iot_api.url');
        $token = config('services.device_iot_api.Authorization');
    
        // Retrieve 'company_id', 'search', and pagination parameters from the request
        $search = $request->input('search', ''); // Default to empty if not provided
        $page = $request->input('page', 1);      // Default to page 1
        
        try {
            // Make the API request with pagination and search filters
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept'        => 'application/json',
            ])->get($url, [
                'company_id' => Auth::user()->company_id,
                'page'       => $page,
                'search'     => $search,
            ]);

    
            if ($response->successful()) {
                // Assuming 'data' holds the devices and 'pagination' holds pagination info
                $devices = $response->json('data');
                $pagination = $response->json('pagination');
    
                return response()->json([
                    'success'    => true,
                    'devices'    => $devices,
                    'pagination' => $pagination,
                    'search'     => $search,
                    'page'       => $page,
                ]);
            } else {
                // Handle API errors
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to fetch devices from API.'], 
                    $response->status() // Return API response status
                );
            }
        } catch (\Exception $e) {
            // Catch and return API call errors
            return response()->json([
                'success' => false, 
                'message' => 'API Request Failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
}
