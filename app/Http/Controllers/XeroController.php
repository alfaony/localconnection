<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\XeroBos;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class XeroController extends Controller
{
    protected $xeroService;

    public function __construct()
    {
        $this->xeroService = new XeroBos();
    }

    public function connect()
    {
        return $this->xeroService->connect();
    }

    public function disconnect()
    {
        $this->xeroService->disconnect();
        return redirect()->route('home')->with('success', 'Disconnected from Xero.');
    }

    public function callback(Request $request)
    {
        $this->xeroService->handleCallback($request);
        return redirect()->route('xero.invoices')->with('success', 'Connected to Xero.');
    }
}
