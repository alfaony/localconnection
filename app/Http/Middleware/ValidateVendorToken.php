<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PotentialVendor;

class ValidateVendorToken
{
    public function handle(Request $request, Closure $next)
    {
        $vendor = PotentialVendor::where('id', $request->route('id'))
            ->where('response_token', $request->route('token'))
            ->first();

        if (!$vendor) 
        {
            abort(403, 'Token tidak valid atau tidak ditemukan.');
        }

        $request->merge(['vendor' => $vendor]);

        return $next($request);
    }
}

