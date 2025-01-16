<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class IpRestriction
{
    public function handle(Request $request, Closure $next)
    {
        // 1️Ambil user yang sedang login
        $user = Auth::user();
        
        //  Cek apakah user mengaktifkan IP restriction
        if ($user->use_ip_restriction) {

            //  Ambil daftar IP yang diizinkan dari database
            $allowedIps = $user->ip_addresses ?? [];

            //  Ambil IP saat ini dari API ipify
            try {
                $response = Http::get('https://api64.ipify.org?format=json');
                if ($response->failed()) {
                    abort(403, 'Gagal mendapatkan IP publik');
                }
                $currentIp = $response->json('ip');
            } catch (\Exception $e) {
                abort(403, 'Gagal mendapatkan IP publik');
            }

            //  Cek apakah IP saat ini ada di daftar yang diizinkan
            if (!in_array($currentIp, $allowedIps)) {
                return response()->view('ip_restricted',['currentIp' => $currentIp]);
            }
        }

        return $next($request);
    }
}