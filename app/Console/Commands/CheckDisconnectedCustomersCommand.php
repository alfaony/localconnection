<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Schemas\ParamSchema;
use App\Services\RouterOSService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RouterOS\Query;

/**
 * Check customer DISCONNECTED apakah sudah reconnect ke router.
 * Hanya check selama 6 jam sejak status berubah ke DISCONNECTED.
 * Jika ditemukan aktif → set kembali ke ACTIVE.
 * Dijadwalkan setiap 2 menit.
 */
class CheckDisconnectedCustomersCommand extends Command
{
    protected $signature = 'customers:check-disconnected';
    protected $description = 'Check disconnected customers (max 6 hours); set active if reconnected';

    // Maksimum 6 jam sejak disconnected_at dicek
    private const MAX_CHECK_HOURS = 6;

    public function handle(RouterOSService $ros): int
    {
        // Ambil customer DISCONNECTED yang disconnected_at dalam 6 jam terakhir
        // disconnected_at disimpan di kolom meta (JSON), fallback ke last_updated_router
        $cutoff = now()->subHours(self::MAX_CHECK_HOURS);

        $customers = DB::table('internet_customers')
            ->select(['id', 'router_id', 'username', 'ip_address', 'mac_address', 'status', 'meta', 'access_type'])
            ->where('status', ParamSchema::DISCONNECTED)
            ->whereNotNull('router_id')
            ->whereNotNull('username')
            ->where('last_updated_router', '>=', $cutoff)
            ->get()
            ->all();

        if (empty($customers)) {
            $this->info('No disconnected customers within 6-hour window.');
            return 0;
        }

        $this->info('Checking ' . count($customers) . ' disconnected customers...');
        Log::info('[CheckDisconnected] Started', ['total' => count($customers)]);

        $toActivate = [];

        // Group by router untuk efisiensi (1 koneksi per router)
        $grouped = collect($customers)->groupBy('router_id');

        foreach ($grouped as $routerId => $routerCustomers) {
            try {
                $router = Router::find($routerId);
                if (!$router || !$router->host) continue;

                $client = $ros->client($router);

                $pppoeCustomers   = $routerCustomers->filter(fn($c) => ($c->access_type ?? 'pppoe') !== 'hotspot');
                $hotspotCustomers = $routerCustomers->filter(fn($c) => ($c->access_type ?? '') === 'hotspot');

                // --- PPPoE ---
                if ($pppoeCustomers->isNotEmpty()) {
                    $allActive = $client->query(new Query('/ppp/active/print'))->read();
                    $activeByName = [];
                    foreach ($allActive as $a) {
                        $activeByName[$a['name']] = $a;
                    }

                    foreach ($pppoeCustomers as $customer) {
                        $active = $activeByName[$customer->username] ?? null;
                        if ($active) {
                            $meta = $customer->meta ? json_decode($customer->meta, true) : [];
                            unset($meta['disconnected_at']);
                            $meta['radius_session'] = [
                                'source'    => 'mikrotik_api',
                                'address'   => $active['address'] ?? null,
                                'caller_id' => $active['caller-id'] ?? null,
                                'uptime'    => $active['uptime'] ?? null,
                                'last_seen' => now()->toIso8601String(),
                            ];
                            $toActivate[] = [
                                'id'          => $customer->id,
                                'ip_address'  => $active['address'] ?? $customer->ip_address,
                                'mac_address' => $active['caller-id'] ?? $customer->mac_address,
                                'meta'        => json_encode($meta),
                            ];
                        }
                    }
                }

                // --- Hotspot ---
                if ($hotspotCustomers->isNotEmpty()) {
                    $allHs = $client->query(new Query('/ip/hotspot/active/print'))->read();
                    $hsActiveByUser = [];
                    foreach ($allHs as $a) {
                        $hsActiveByUser[$a['user']] = $a;
                    }

                    foreach ($hotspotCustomers as $customer) {
                        $active = $hsActiveByUser[$customer->username] ?? null;
                        if ($active) {
                            $meta = $customer->meta ? json_decode($customer->meta, true) : [];
                            unset($meta['disconnected_at']);
                            $meta['radius_session'] = [
                                'source'    => 'mikrotik_api_hotspot',
                                'address'   => $active['address'] ?? null,
                                'mac'       => $active['mac-address'] ?? null,
                                'uptime'    => $active['uptime'] ?? null,
                                'last_seen' => now()->toIso8601String(),
                            ];
                            $toActivate[] = [
                                'id'          => $customer->id,
                                'ip_address'  => $active['address'] ?? $customer->ip_address,
                                'mac_address' => $active['mac-address'] ?? $customer->mac_address,
                                'meta'        => json_encode($meta),
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('[CheckDisconnected] Mikrotik check failed', [
                    'router_id' => $routerId,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        // Batch update ke ACTIVE
        if (!empty($toActivate)) {
            DB::transaction(function () use ($toActivate) {
                foreach (array_chunk($toActivate, 100) as $chunk) {
                    foreach ($chunk as $data) {
                        DB::table('internet_customers')
                            ->where('id', $data['id'])
                            ->update([
                                'status'              => ParamSchema::ACTIVE,
                                'ip_address'          => $data['ip_address'],
                                'mac_address'         => $data['mac_address'],
                                'meta'                => $data['meta'],
                                'last_updated_router' => now(),
                            ]);
                    }
                }
            });

            $this->info('Reactivated: ' . count($toActivate) . ' customers.');
            Log::info('[CheckDisconnected] Customers reactivated', ['count' => count($toActivate)]);
        } else {
            $this->info('No reconnected customers found.');
        }

        Log::info('[CheckDisconnected] Completed', [
            'checked'     => count($customers),
            'reactivated' => count($toActivate),
        ]);

        return 0;
    }
}
