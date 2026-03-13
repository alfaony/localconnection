<?php

namespace App\Jobs;

use App\Models\HotspotVoucher;
use App\Services\RadiusService;
use App\Services\RouterOSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Provision satu voucher hotspot ke RADIUS atau MikroTik local.
 * DUAL MODE: RADIUS primary + Direct API fallback (sama pola PPPoE).
 */
class ProvisionVoucherJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    public function __construct(public string $voucherId) {}

    public function handle(RadiusService $radius): void
    {
        $voucher = HotspotVoucher::with(['internetPackage', 'hotspotServer.router'])->findOrFail($this->voucherId);

        try {
            if (RadiusService::isEnabled()) {
                $this->provisionToRadius($radius, $voucher);
            } else {
                $this->provisionToMikrotik($voucher);
            }
        } catch (\Throwable $th) {
            Log::error('[ProvisionVoucherJob] Error: ' . $th->getMessage(), [
                'voucher_id' => $this->voucherId,
                'username'   => $voucher->username,
            ]);
        }
    }

    /**
     * 🟢 RADIUS MODE: tulis ke FreeRADIUS
     */
    protected function provisionToRadius(RadiusService $radius, HotspotVoucher $voucher): void
    {
        try {
            $radius->upsertVoucherUser($voucher);

            Log::info('[ProvisionVoucherJob] RADIUS provisioned ✅', ['username' => $voucher->username]);
        } catch (\Throwable $e) {
            Log::warning('[ProvisionVoucherJob] RADIUS failed, fallback to Direct API', [
                'username' => $voucher->username, 'error' => $e->getMessage(),
            ]);
            $this->provisionToMikrotik($voucher);
        }
    }

    /**
     * 🔴 DIRECT API MODE: tulis ke MikroTik local hotspot user
     */
    protected function provisionToMikrotik(HotspotVoucher $voucher): void
    {
        $router = $voucher->hotspotServer?->router;
        if (!$router) {
            Log::warning('[ProvisionVoucherJob] No router found for hotspot server', ['voucher_id' => $voucher->id]);
            return;
        }

        try {
            $ros    = app(RouterOSService::class);
            $client = $ros->client($router);
            $ros->upsertVoucherOnMikrotik($client, $voucher);

            Log::info('[ProvisionVoucherJob] MikroTik provisioned ✅', ['username' => $voucher->username]);
        } catch (\Throwable $e) {
            Log::error('[ProvisionVoucherJob] MikroTik Direct API failed', [
                'username' => $voucher->username, 'error' => $e->getMessage(),
            ]);
        }
    }
}
