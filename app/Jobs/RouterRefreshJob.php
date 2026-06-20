<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\Router;
use App\Schemas\ParamSchema;
use App\Services\RouterOSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Refresh status router + re-provision pelanggan yang pending.
 *
 * Triggered manual dari tombol Refresh di halaman Router Index.
 *
 * Flow:
 * 1. Ping router → update active_status (UP / DOWN / ERROR)
 * 2. Jika UP: dispatch ProvisionCustomerJob untuk setiap pelanggan
 *    dengan status installed, reactivated, atau disconnected di router ini.
 */
class RouterRefreshJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;
    public int $tries   = 1;

    public function __construct(public int $routerId) {}

    public function handle(RouterOSService $svc): void
    {
        $router = Router::findOrFail($this->routerId);

        // ── 1. Cek koneksi router ────────────────────────────────────────────
        try {
            $client = $svc->client($router);
            $online = $svc->quickPing($client);
        } catch (Throwable $e) {
            $router->updateHealthStatus(Router::STATUS_ERROR, $e->getMessage());
            Log::warning('[RouterRefresh] Connection error', [
                'router_id' => $router->id,
                'error'     => $e->getMessage(),
            ]);
            return;
        }

        if (!$online) {
            $router->updateHealthStatus(Router::STATUS_DOWN, 'Ping tidak berhasil');
            Log::info('[RouterRefresh] Router masih DOWN', ['router_id' => $router->id]);
            return;
        }

        $router->updateHealthStatus(Router::STATUS_UP);
        Log::info('[RouterRefresh] Router UP — mulai cek pelanggan pending', ['router_id' => $router->id]);

        // ── 2. Ambil pelanggan installed + reactivated + disconnected ────────
        $customers = InternetCustomer::where('router_id', $router->id)
            ->whereIn('status', [
                ParamSchema::INSTALLED,
                ParamSchema::REACTIVATED,
                ParamSchema::DISCONNECTED,
            ])
            ->whereNotNull('username')
            ->get();

        if ($customers->isEmpty()) {
            Log::info('[RouterRefresh] Tidak ada pelanggan pending', ['router_id' => $router->id]);
            return;
        }

        // ── 3. Dispatch ProvisionCustomerJob per pelanggan ──────────────────
        foreach ($customers as $cust) {
            dispatch(new ProvisionCustomerJob($cust->id));

            Log::info('[RouterRefresh] Dispatch ProvisionCustomerJob', [
                'router_id' => $router->id,
                'customer'  => $cust->username,
                'status'    => $cust->status,
            ]);
        }

        Log::info('[RouterRefresh] Selesai', [
            'router_id'  => $router->id,
            'dispatched' => $customers->count(),
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('[RouterRefresh] Job gagal', [
            'router_id' => $this->routerId,
            'error'     => $e->getMessage(),
        ]);
    }
}
