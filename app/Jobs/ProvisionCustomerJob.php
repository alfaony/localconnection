<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\InternetPackage;
use App\Models\Router;
use App\Models\PackageRouterProfile;
use App\Models\HotspotServer;
use App\Services\RadiusService;
use App\Services\RouterOSService;
use App\Jobs\SyncInstalledCustomersJob;
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
        $cust = InternetCustomer::with(['internetPackage', 'router', 'hotspotServer'])->findOrFail($this->internetCustomerId);

        try {
            /** @var Router $router */
            $router = $cust->router;
            /** @var InternetPackage $pkg */
            $pkg = $cust->internetPackage;

            $map = PackageRouterProfile::where('router_id', $router->id)
                  ->where('package_id', $pkg->id)->first();

            $groupName = $map?->ros_profile ?? ('PKG_' . $pkg->id);

            // ==========================================
            // Route berdasarkan access_type
            // ==========================================
            if ($cust->access_type === 'hotspot') {
                $this->handleHotspot($radius, $cust, $pkg, $groupName, $router);
            } else {
                // Default: PPPoE flow (tidak berubah)
                $this->handlePppoe($radius, $cust, $pkg, $groupName, $router);
                    // upsert secret + enable/disable by status
            }
            
            // ✅ Trigger sync check after 45 seconds to update status to ACTIVE
            // setelah disconnectIfActive, router butuh waktu reconnect
            if (in_array($cust->status, [ParamSchema::REACTIVATED, ParamSchema::INSTALLED])) {
                dispatch(new SyncInstalledCustomersJob([$cust->id]))->delay(now()->addMinutes(1));
            }

        } catch (\Throwable $th) {
            Log::error('[ProvisionJob] Error: ' . $th->getMessage(), [
                'customer_id' => $this->internetCustomerId,
            ]);
        }
    }

    /**
     * PPPoE flow (existing logic, tidak berubah)
     */
    protected function handlePppoe(RadiusService $radius, InternetCustomer $cust, InternetPackage $pkg, string $groupName, Router $router): void
    {
        // DIRECT API: PPP Profile setup (selalu jalan)
        $this->ensureProfileOnRouter($router, $pkg, $groupName);

        if (RadiusService::isEnabled()) {
            if ($cust->status == ParamSchema::INSTALLED) {
                $this->handleInstall($radius, $cust, $pkg, $groupName, $router);
            } elseif ($cust->status == ParamSchema::SUSPENDED) {
                $this->handleSuspend($radius, $cust, $router);
            } elseif ($cust->status == ParamSchema::REACTIVATED) {
                $this->handleReactivate($radius, $cust, $pkg, $groupName, $router);
            }
        } else {
            Log::info('[ProvisionJob] RADIUS disabled → Direct API mode', [
                'customer' => $cust->username, 'status' => $cust->status,
            ]);
            $this->handleDirectApiOnly($cust, $groupName, $router);
        }
    }

    /**
     * Hotspot flow (RADIUS primary + Direct fallback).
     *
     * ip_binding_mode='bypassed' → user tidak perlu auth. MikroTik mengizinkan
     * akses berdasarkan MAC/IP saja. Tidak butuh RADIUS atau hotspot user.
     */
    protected function handleHotspot(RadiusService $radius, InternetCustomer $cust, InternetPackage $pkg, string $groupName, Router $router): void
    {
        // Bypassed: hanya kelola ip-binding, skip semua auth
        if ($cust->ip_binding_type === 'direct' && $cust->ip_binding_mode === 'bypassed') {
            $this->handleBypassedHotspot($cust, $router);
            return;
        }

        // Normal hotspot: RADIUS primary + Direct fallback
        $this->ensureHotspotProfileOnRouter($router, $pkg, $groupName);

        if (RadiusService::isEnabled()) {
            if ($cust->status == ParamSchema::INSTALLED) {
                $this->handleHotspotInstall($radius, $cust, $pkg, $groupName, $router);
            } elseif ($cust->status == ParamSchema::SUSPENDED) {
                $this->handleHotspotSuspend($radius, $cust, $router);
            } elseif ($cust->status == ParamSchema::REACTIVATED) {
                $this->handleHotspotReactivate($radius, $cust, $pkg, $groupName, $router);
            }
        } else {
            Log::info('[ProvisionJob] RADIUS disabled → Hotspot Direct API mode', [
                'customer' => $cust->username, 'status' => $cust->status,
            ]);
            $this->handleHotspotDirectApiOnly($cust, $groupName, $router);
        }
    }

    /**
     * Bypassed hotspot: hanya kelola ip-binding entry di MikroTik.
     * Tidak ada autentikasi, tidak ada RADIUS, tidak ada hotspot user.
     *
     *  INSTALLED/REACTIVATED → add ip-binding (bypassed)
     *  SUSPENDED             → remove ip-binding (putus akses)
     */
    protected function handleBypassedHotspot(InternetCustomer $cust, Router $router): void
    {
        try {
            $ros    = app(RouterOSService::class);
            $client = $ros->client($router);

            if ($cust->status == ParamSchema::SUSPENDED) {
                $ros->removeHotspotIpBinding($client, $cust->ip_address ?: null, $cust->mac_address ?: null);
                Log::info('[ProvisionJob] BYPASSED SUSPENDED — ip-binding removed ✅', ['customer' => $cust->username]);
            } else {
                $this->handleIpBinding($cust, $router);
                Log::info('[ProvisionJob] BYPASSED ACTIVE — ip-binding set ✅', ['customer' => $cust->username]);
            }
        } catch (\Throwable $e) {
            Log::error('[ProvisionJob] Bypassed ip-binding failed', [
                'customer' => $cust->username,
                'error'    => $e->getMessage(),
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

            // Pool, local_address: PackageRouterProfile → fallback PPPoE Server router
            $mapping = PackageRouterProfile::where('router_id', $router->id)
                ->where('package_id', $pkg->id)
                ->with('addressPool')
                ->first();

            $poolNameOverride = $mapping?->addressPool?->name;
            // local_address di mapping prioritas utama, fallback ke gateway AddressPool
            $gatewayOverride  = $mapping?->local_address ?: $mapping?->addressPool?->gateway;

            $ros->ensurePppProfile($client, $pkg, $profileName, null, $router->id, $poolNameOverride, $gatewayOverride);

            Log::info('[ProvisionJob] PPP Profile ensured via Direct API', [
                'router'        => $router->name,
                'profile'       => $profileName,
                'pool'          => $poolNameOverride ?? 'fallback PPPoE Server',
                'local_address' => $gatewayOverride  ?? 'fallback PPPoE Server',
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
            // 🔴 FALLBACK: Direct API — ganti profile ke ISOLIR
            Log::warning('[ProvisionJob] RADIUS suspend failed, falling back to Direct API', [
                'error' => $e->getMessage(),
            ]);
            try {
                $ros = app(RouterOSService::class);
                $client = $ros->client($router);
                $ros->upsertPppSecret($client, $cust, 'SUSPENDED');
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
     * 🔴 DIRECT API ONLY: Ketika RADIUS_ENABLED=false
     * Handle semua status langsung via Mikrotik API
     */
    protected function handleDirectApiOnly(InternetCustomer $cust, string $profileName, Router $router): void
    {
        try {
            $ros = app(RouterOSService::class);
            $client = $ros->client($router);

            if ($cust->status == ParamSchema::SUSPENDED) {
                // Suspend: ganti profile ke ISOLIR + disconnect agar reconnect dengan profile baru
                $ros->ensureSuspendedPppProfile($client, "SUSPENDED");
                $ros->upsertPppSecret($client, $cust, 'SUSPENDED');
                $ros->disconnectIfActive($client, $cust->username);
                Log::info('[ProvisionJob] SUSPENDED via Direct API ✅', ['customer' => $cust->username]);
            } else {
                // Installed/Reactivated: upsert secret + disconnect old session
                $ros->upsertPppSecret($client, $cust, $profileName, $cust->local_address);
                $ros->disconnectIfActive($client, $cust->username);
                Log::info('[ProvisionJob] ' . strtoupper($cust->status) . ' via Direct API ✅', [
                    'customer' => $cust->username, 'profile' => $profileName,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[ProvisionJob] Direct API failed', [
                'customer' => $cust->username,
                'status'   => $cust->status,
                'error'    => $e->getMessage(),
            ]);
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

    // ============================================================
    // HOTSPOT METHODS
    // ============================================================

    /**
     * Pastikan hotspot user profile ada di router (selalu Direct API)
     */
    protected function ensureHotspotProfileOnRouter(Router $router, InternetPackage $pkg, string $profileName): void
    {
        try {
            $ros    = app(RouterOSService::class);
            $client = $ros->client($router);
            $down   = (int)($pkg->rate_down_mbps ?? $pkg->bandwidth ?? 0);
            $up     = (int)($pkg->rate_up_mbps ?? max(1, (int)ceil(($pkg->bandwidth ?? 1) * 0.2)));
            $rate   = "{$down}M/{$up}M";

            $ros->ensureHotspotUserProfile(
                $client,
                $profileName,
                $rate,
                $pkg->session_timeout_seconds ?: null,
                $pkg->idle_timeout_seconds ?: null
            );

            Log::info('[ProvisionJob] Hotspot profile ensured', ['router' => $router->name, 'profile' => $profileName]);
        } catch (\Throwable $e) {
            Log::warning('[ProvisionJob] Hotspot profile setup failed (non-fatal)', [
                'router' => $router->name, 'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * HOTSPOT INSTALLED: RADIUS primary + Direct fallback
     */
    protected function handleHotspotInstall(RadiusService $radius, InternetCustomer $cust, InternetPackage $pkg, string $groupName, Router $router): void
    {
        try {
            // ensureHotspotGroup: set rate-limit + Session-Timeout + Idle-Timeout dari paket
            $radius->ensureHotspotGroup($pkg, $groupName);
            // upsertHotspotCustomer: set password, group, Framed-IP-Address (jika radius binding)
            $radius->upsertHotspotCustomer($cust, $groupName);

            Log::info('[ProvisionJob] HOTSPOT INSTALLED via RADIUS ✅', ['customer' => $cust->username]);
        } catch (\Throwable $e) {
            Log::warning('[ProvisionJob] RADIUS hotspot install failed, fallback to Direct API', ['error' => $e->getMessage()]);
            $this->fallbackHotspotDirectApi($cust, $groupName, $router, 'install');
        }

        // Handle Direct ip-binding (Framed-IP-Address untuk radius binding sudah di-handle upsertHotspotCustomer)
        $this->handleIpBinding($cust, $router);
    }

    /**
     * HOTSPOT SUSPENDED: RADIUS primary + Direct fallback
     */
    protected function handleHotspotSuspend(RadiusService $radius, InternetCustomer $cust, Router $router): void
    {
        try {
            $ros    = app(RouterOSService::class);
            $client = $ros->client($router);

            // ⚠️ ip_binding_mode='bypassed': user bypass autentikasi via MAC/IP whitelist.
            // Auth-Type:Reject TIDAK ada efeknya untuk bypassed user — mereka tidak perlu login!
            // Wajib hapus ip-binding entry dari MikroTik agar benar-benar terputus.
            if ($cust->ip_binding_type === 'direct' && $cust->ip_binding_mode === 'bypassed') {
                try {
                    $ros->removeHotspotIpBinding($client, $cust->ip_address ?: null, $cust->mac_address ?: null);
                    Log::info('[ProvisionJob] Bypassed ip-binding removed on suspend', ['customer' => $cust->username]);
                } catch (\Throwable $bindErr) {
                    Log::warning('[ProvisionJob] Bypassed ip-binding removal failed', ['error' => $bindErr->getMessage()]);
                }
            }

            // Hapus Framed-IP-Address untuk radius binding (user tidak boleh dapat IP tetap saat suspend)
            if ($cust->ip_binding_type === 'radius') {
                $radius->removeFramedIp($cust->username);
            }

            // RADIUS: Auth-Type = Reject → user tidak bisa auth (efektif untuk mode 'regular')
            $radius->suspendHotspotCustomer($cust->username);

            // Kick active session
            try {
                $ros->disconnectHotspotUser($client, $cust->username);
                Log::info('[ProvisionJob] Hotspot session kicked');
            } catch (\Throwable $dcErr) {
                Log::warning('[ProvisionJob] Hotspot disconnect failed (akan reject saat reconnect)', [
                    'error' => $dcErr->getMessage(),
                ]);
            }

            Log::info('[ProvisionJob] HOTSPOT SUSPENDED ✅', ['customer' => $cust->username]);
        } catch (\Throwable $e) {
            Log::warning('[ProvisionJob] RADIUS hotspot suspend failed, fallback', ['error' => $e->getMessage()]);
            $this->fallbackHotspotDirectApi($cust, '', $router, 'suspend');
        }
    }

    /**
     * HOTSPOT REACTIVATED: RADIUS primary + Direct fallback
     */
    protected function handleHotspotReactivate(RadiusService $radius, InternetCustomer $cust, InternetPackage $pkg, string $groupName, Router $router): void
    {
        try {
            $radius->ensureHotspotGroup($pkg, $groupName);
            // Hapus Auth-Type:Reject, restore group
            $radius->reactivateUser($cust->username, $groupName);
            // Restore Framed-IP-Address jika radius binding (hilang saat suspend)
            $radius->upsertHotspotCustomer($cust, $groupName);

            try {
                $ros    = app(RouterOSService::class);
                $client = $ros->client($router);
                $ros->disconnectHotspotUser($client, $cust->username);
            } catch (\Throwable $dcErr) {
                Log::warning('[ProvisionJob] Hotspot disconnect failed on reactivate', ['error' => $dcErr->getMessage()]);
            }

            Log::info('[ProvisionJob] HOTSPOT REACTIVATED via RADIUS ✅', ['customer' => $cust->username]);
        } catch (\Throwable $e) {
            Log::warning('[ProvisionJob] RADIUS hotspot reactivate failed, fallback', ['error' => $e->getMessage()]);
            $this->fallbackHotspotDirectApi($cust, $groupName, $router, 'install');
        }

        // Re-add direct ip-binding jika ada (termasuk bypassed yang dihapus saat suspend)
        $this->handleIpBinding($cust, $router);
    }

    /**
     * HOTSPOT DIRECT API ONLY (RADIUS disabled)
     */
    protected function handleHotspotDirectApiOnly(InternetCustomer $cust, string $profileName, Router $router): void
    {
        $action = $cust->status == ParamSchema::SUSPENDED ? 'suspend' : 'install';
        $this->fallbackHotspotDirectApi($cust, $profileName, $router, $action);
        $this->handleIpBinding($cust, $router);
    }

    /**
     * Fallback ke Direct API untuk hotspot
     */
    protected function fallbackHotspotDirectApi(InternetCustomer $cust, string $profileName, Router $router, string $action): void
    {
        try {
            $ros    = app(RouterOSService::class);
            $client = $ros->client($router);

            if ($action === 'suspend') {
                $ros->removeHotspotUser($client, $cust->username);
                $ros->disconnectHotspotUser($client, $cust->username);
            } else {
                $ros->disconnectHotspotUser($client, $cust->username);
                $ros->upsertHotspotUser($client, $cust, $profileName);
            }

            Log::info('[ProvisionJob] Hotspot Direct API success', ['customer' => $cust->username, 'action' => $action]);
        } catch (\Throwable $e) {
            Log::error('[ProvisionJob] Hotspot Direct API failed', ['customer' => $cust->username, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Handle IP Binding untuk hotspot customer.
     * Dipanggil setelah auth install/reactivate.
     */
    protected function handleIpBinding(InternetCustomer $cust, Router $router): void
    {
        if (!$cust->ip_binding_type) return;
        if ($cust->ip_binding_type !== 'direct') return; // radius binding ditangani via Framed-IP-Address

        $ip  = $cust->ip_address  ?: null;
        $mac = $cust->mac_address ?: null;

        if (!$ip && !$mac) 
        {
            Log::warning('[ProvisionJob] IP Binding skipped — ip_address and mac_address both empty', [
                'customer' => $cust->username,
            ]);
            return;
        }

        try 
        {
            $ros        = app(RouterOSService::class);
            $client     = $ros->client($router);
            $serverName = $cust->hotspotServer?->name ?? '';
            $mode       = $cust->ip_binding_mode ?? 'regular';

            Log::info('[ProvisionJob] Creating IP Binding', [
                'customer' => $cust->username,
                'server'   => $serverName ?: '(all)',
                'mode'     => $mode,
                'ip'       => $ip,
                'mac'      => $mac,
            ]);

            $ros->addHotspotIpBinding($client, $serverName, $ip, $mac, $mode);

            Log::info('[ProvisionJob] IP Binding set ✅', ['customer' => $cust->username]);
        } catch (\Throwable $e) 
        {
            Log::error('[ProvisionJob] IP Binding FAILED', [
                'customer' => $cust->username,
                'error'    => $e->getMessage(),
                'ip'       => $ip,
                'mac'      => $mac,
            ]);
            throw $th;
        }
    }
}