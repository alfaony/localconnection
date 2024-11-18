<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Services\XeroBos;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Exception;

class EnsureXeroConnected
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        $companyId = Auth::user()->company_id;
        if (!Session::has('company_id')) {
            Session::put('company_id', $companyId);
        }


        try {
            $xeroService = new XeroBos($companyId);
            if (!$xeroService->isConnected()) {
                return $xeroService->connect();
            }
        } catch (Exception $e) {
            Log::error('Xero connection error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to connect to Xero: ' . $e->getMessage());
        }

        return $next($request);
    }

}