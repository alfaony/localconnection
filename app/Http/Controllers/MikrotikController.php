<?php

namespace App\Http\Controllers;

use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\PackageRouterProfile;
use App\Services\RadiusService;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * ✅ DUAL-MODE: RADIUS primary + Direct API fallback
 */
class MikrotikController extends Controller
{
    public function provision(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|String',
            'username'   => 'required|string',
            'password'   => 'required|string',
            'package_id' => 'required|integer',
            'comment'    => 'nullable|string'
        ]);

        $radius = app(RadiusService::class);
        $customer = InternetCustomer::where('username', $data['username'])->first();

        if ($customer) {
            $pkg = $customer->internetPackage;
            $map = PackageRouterProfile::where('router_id', $customer->router_id)
                  ->where('package_id', $pkg->id)->first();
            $groupName = $map->ros_profile ?? ('PKG_' . $pkg->id);

            try {
                // 🟢 RADIUS primary
                $radius->ensureGroup($pkg, $groupName);
                $radius->upsertUser($customer, $groupName);
            } catch (\Throwable $e) {
                // 🔴 Fallback Direct API
                Log::warning('[MikrotikCtrl] RADIUS provision failed, fallback', ['error' => $e->getMessage()]);
                $mt = new MikrotikService($data['company_id']);
                $sec = $mt->secretFind($data['username']);
                $payload = [
                    'password' => $data['password'],
                    'profile'  => $groupName,
                    'comment'  => $data['comment'] ?? '',
                    'disabled' => 'no'
                ];
                if ($sec) {
                    $mt->secretSet($sec['.id'], $payload);
                } else {
                    $mt->secretAdd(['name' => $data['username']] + $payload);
                }
                $mt->activeDisconnectByName($data['username']);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function cut(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|String',
            'username'   => 'required|string',
        ]);

        $radius = app(RadiusService::class);

        try {
            // 🟢 RADIUS primary
            $radius->suspendUser($data['username']);
        } catch (\Throwable $e) {
            // 🔴 Fallback Direct API
            Log::warning('[MikrotikCtrl] RADIUS cut failed, fallback', ['error' => $e->getMessage()]);
            $mt = new MikrotikService($data['company_id']);
            $sec = $mt->secretFind($data['username']);
            if ($sec) {
                $mt->secretSet($sec['.id'], ['disabled' => 'yes']);
                $mt->activeDisconnectByName($data['username']);
            }
        }

        return response()->json(['status' => 'cut']);
    }

    public function restore(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|String',
            'username'   => 'required|string',
            'package_id' => 'required|integer',
        ]);

        $radius = app(RadiusService::class);
        $customer = InternetCustomer::where('username', $data['username'])->first();

        if ($customer) {
            $pkg = InternetPackage::findOrFail($data['package_id']);
            $map = PackageRouterProfile::where('router_id', $customer->router_id)
                  ->where('package_id', $pkg->id)->first();
            $groupName = $map->ros_profile ?? ('PKG_' . $pkg->id);

            try {
                // 🟢 RADIUS primary
                $radius->ensureGroup($pkg, $groupName);
                $radius->reactivateUser($data['username'], $groupName);
            } catch (\Throwable $e) {
                // 🔴 Fallback Direct API
                Log::warning('[MikrotikCtrl] RADIUS restore failed, fallback', ['error' => $e->getMessage()]);
                $mt = new MikrotikService($data['company_id']);
                $sec = $mt->secretFind($data['username']);
                if ($sec) {
                    $mt->secretSet($sec['.id'], [
                        'profile'  => $groupName,
                        'disabled' => 'no'
                    ]);
                    $mt->activeDisconnectByName($data['username']);
                }
            }
        }

        return response()->json(['status' => 'restored']);
    }
}