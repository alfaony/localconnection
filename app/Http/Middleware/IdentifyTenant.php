<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;

/**
 * Resolve tenant (Company) dari hostname request, di-jalanin di SETIAP
 * request (didaftarkan sebagai middleware global di Kernel.php). Bind
 * hasil resolve ke container, bisa diakses lewat helper tenant() di mana
 * saja (controller, Livewire, Blade).
 *
 * Kalau host-nya subdomain platform ({slug}.internetrt.com) atau custom
 * domain yang sudah diverifikasi -> tenant ke-resolve.
 * Kalau host-nya domain utama platform (internetrt.com / domain aplikasi
 * biasa) -> tenant null, request diproses normal seperti biasa (path-based).
 */
class IdentifyTenant
{
    public function handle(Request $request, Closure $next)
    {
        $baseDomain = config('app.tenant_base_domain', 'internetrt.com');
        $host = $request->getHost();

        $tenant = Company::resolveByHost($host, $baseDomain);

        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
