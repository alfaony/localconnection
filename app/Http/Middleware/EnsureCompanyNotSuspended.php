<?php

namespace App\Http\Middleware;

use App\Schemas\RoleSchema;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Suspend penuh akses dashboard ISP-admin kalau company-nya menunggak tagihan
 * platform (Rp1000/customer/bulan). TIDAK berlaku untuk halaman publik
 * end-customer (di luar group route ini), supaya customer si ISP tidak kena
 * imbas kalau ISP-nya sendiri yang belum bayar ke platform.
 */
class EnsureCompanyNotSuspended
{
    protected array $exemptRouteNames = [
        'billing.index',
        'platform-billing.index',
        'logout',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, $this->exemptRouteNames, true)) {
            return $next($request);
        }

        if ($user->role && in_array($user->role->name, [RoleSchema::ROOT, RoleSchema::SUPER_ADMIN], true)) {
            return $next($request);
        }

        $company = $user->company;

        if ($company && $company->isBillingSuspended()) {
            return redirect()->route('billing.index')
                ->with('warning', 'Akses dashboard disuspend karena tagihan platform belum dibayar. Silakan selesaikan pembayaran untuk mengaktifkan kembali akses.');
        }

        return $next($request);
    }
}
