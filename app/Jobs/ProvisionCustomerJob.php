<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\Router;
use App\Models\PackageRouterProfile;
use App\Services\RadiusService;
use App\Services\RouterOSService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use App\Schemas\ParamSchema;

/**
 * ✅ DUAL-MODE: RADIUS primary + Direct API fallback
 *
 * - Auth (password/rate-limit) → via RADIUS DB
 * - PPP Profile setup → tetap Direct API ke Mikrotik
 * - Fallback ke Direct API jika RADIUS gagal
 */
class ProvisionCustomerJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    public function __construct(public string $internetCustomerId) {}

    public function handle(RadiusService $radius): void
    {
        $cust = InternetCustomer::with(['internetPackage', 'router'])->findOrFail($this->internetCustomerId);

        try {
            /** @var Router $router */
            $router = $cust->router;
            /** @var InternetPackage $pkg */
            $pkg = $cust->internetPackage;

            $map = PackageRouterProfile::where('router_id', $router->id)
                  ->where('package_id', $pkg->id)->first();

            $groupName = $map->ros_profile ?? ('PKG_' . $pkg->id);

            // ==========================================
            // DIRECT API: PPP Profile setup (selalu jalan)
            // ==========================================
            $this->ensureProfileOnRouter($router, $pkg, $groupName);

            // ==========================================
            // Auth berdasarkan status
            // ==========================================
            if ($cust->status == ParamSchema::INSTALLED) {
                $this->handleInstall($radius, $cust, $pkg, $groupName, $router);
            } elseif ($cust->status == ParamSchema::SUSPENDED) {
                $this->handleSuspend($radius, $cust, $router);
            } elseif ($cust->status == ParamSchema::REACTIVATED) {
                $this->handleReactivate($radius, $cust, $pkg, $groupName, $router);
            }

        } catch (\Throwable $th) {
            Log::error('[ProvisionJob] Error: ' . $th->getMessage(), [
                'customer_id' => $this->internetCustomerId,
            ]);
        }
    }

    /**
     * DIRECT API: Pastikan PPP profile ada di router
     */
    protected function ensureProfileOnRouter(Router $router, InternetPackage $pkg, string $profileName): void
    {
        try {
            $ros = app(RouterOSService::class);
            $client = $ros->client($router);
            $ros->ensurePppProfile($client, $pkg, $profileName, null, $router->id);

            Log::info('[ProvisionJob] PPP Profile ensured via Direct API', [
                'router'  => $router->name,
                'profile' => $profileName,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[ProvisionJob] Direct API profile setup failed (non-fatal)', [
                'router' => $router->name,
                'error'  => $e->getMessage(),
            ]);
            // Non-fatal — profile mungkin sudah ada
        }
    }

    /**
     * INSTALLED: RADIUS primary → Direct API fallback
     */
    protected function handleInstall(RadiusService $radius, InternetCustomer $cust, InternetPackage $pkg, string $groupName, Router $router): void
    {
        try {
            // 🟢 RADIUS: set password + group
            $radius->ensureGroup($pkg, $groupName);
            $radius->upsertUser($cust, $groupName);

            Log::info('[ProvisionJob] INSTALLED via RADIUS ✅', [
                'customer' => $cust->username, 'group' => $groupName,
            ]);
        } catch (\Throwable $e) {
            // 🔴 FALLBACK: Direct API
            Log::warning('[ProvisionJob] RADIUS failed, falling back to Direct API', [
                'customer' => $cust->username, 'error' => $e->getMessage(),
            ]);
            $this->fallbackDirectApi($cust, $groupName, $router);
        }
    }

    /**
     * SUSPENDED: RADIUS primary → Direct API fallback
     */
    protected function handleSuspend(RadiusService $radius, InternetCustomer $cust, Router $router): void
    {
        try {
            // 🟢 RADIUS: pindahkan ke group ISOLIR (walled garden)
            $radius->suspendUser($cust->username);

            // 🔌 Disconnect session aktif agar reconnect dengan ISOLIR attributes
            try {
                $ros = app(RouterOSService::class);
                $client = $ros->client($router);
                $ros->disconnectIfActive($client, $cust->username);
                Log::info('[ProvisionJob] Session disconnected for ISOLIR reconnect');
            } catch (\Throwable $dcErr) {
                Log::warning('[ProvisionJob] Disconnect failed (user will apply on next reconnect)', [
                    'error' => $dcErr->getMessage(),
                ]);
            }

            Log::info('[ProvisionJob] SUSPENDED via RADIUS → ISOLIR ✅', [
                'customer' => $cust->username,
            ]);
        } catch (\Throwable $e) {
            // 🔴 FALLBACK: Direct API — disable secret
            Log::warning('[ProvisionJob] RADIUS suspend failed, falling back to Direct API', [
                'error' => $e->getMessage(),
            ]);
            try {
                $ros = app(RouterOSService::class);
                $client = $ros->client($router);
                $ros->disableSecret($client, $cust->username);
                $ros->disconnectIfActive($client, $cust->username);
            } catch (\Throwable $e2) {
                Log::error('[ProvisionJob] Direct API fallback also failed', [
                    'error' => $e2->getMessage(),
                ]);
            }
        }
    }

    /**
     * REACTIVATED: RADIUS primary → Direct API fallback
     */
    protected function handleReactivate(RadiusService $radius, InternetCustomer $cust, InternetPackage $pkg, string $groupName, Router $router): void
    {
        try {
            // 🟢 RADIUS: unblock + set group kembali ke paket asal
            $radius->ensureGroup($pkg, $groupName);
            $radius->reactivateUser($cust->username, $groupName);

            // 🔌 Disconnect session ISOLIR agar reconnect dengan paket asal
            try {
                $ros = app(RouterOSService::class);
                $client = $ros->client($router);
                $ros->disconnectIfActive($client, $cust->username);
                Log::info('[ProvisionJob] Session disconnected for reactivation reconnect');
            } catch (\Throwable $dcErr) {
                Log::warning('[ProvisionJob] Disconnect failed on reactivate (user will apply on next reconnect)', [
                    'error' => $dcErr->getMessage(),
                ]);
            }

            Log::info('[ProvisionJob] REACTIVATED via RADIUS ✅', [
                'customer' => $cust->username, 'group' => $groupName,
            ]);
        } catch (\Throwable $e) {
            // 🔴 FALLBACK: Direct API
            Log::warning('[ProvisionJob] RADIUS reactivate failed, falling back to Direct API', [
                'error' => $e->getMessage(),
            ]);
            $this->fallbackDirectApi($cust, $groupName, $router);
        }
    }

    /**
     * 🔴 Fallback ke Direct API (RouterOSService)
     */
    protected function fallbackDirectApi(InternetCustomer $cust, string $profileName, Router $router): void
    {
        try {
            $ros = app(RouterOSService::class);
            $client = $ros->client($router);
            $ros->disconnectIfActive($client, $cust->username);
            $ros->upsertPppSecret($client, $cust, $profileName, $cust->local_address);

            Log::info('[ProvisionJob] Fallback Direct API success', [
                'customer' => $cust->username,
            ]);
        } catch (\Throwable $e) {
            Log::error('[ProvisionJob] Direct API fallback also failed', [
                'customer' => $cust->username,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}