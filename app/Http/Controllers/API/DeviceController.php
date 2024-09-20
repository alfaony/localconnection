<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DeviceController extends Controller
{
    public function index()
    {

        $url = config('services.device_iot_api.url');
        $token = config('services.device_iot_api.Authorization');

        try {
            $response = Http::withHeaders([
                'Authorization' =>  $token,
                'Accept'        => 'application/json',
            ])->get($url);

            if ($response->successful()) {
                
                $devices = $response->json('data');

                return view('device.index', ['devices' => $devices]);
            } else {
                // Handle errors
                return back()->withErrors('Failed to fetch devices from API.');
            }
        } catch (\Exception $e) {
            // Handle the error appropriately
            abort(500, 'API Request Failed: ' . $e->getMessage());
        }
    }
}
