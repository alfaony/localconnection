<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

use App\Services\DeviceService;

class DeviceController extends Controller
{

    protected $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }
    
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
        $search = $request->input('search', '');
        $page = $request->input('page', 1);

        $response = $this->deviceService->fetchDevices($page, $search);

        return response()->json($response, $response['status'] ?? 200);
    }
    
}
