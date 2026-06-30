<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Models\Radius\RadAcct;
use App\Schemas\ParamSchema;
use App\Services\RadiusService;
use App\Services\RouterOSService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RouterOS\Query;

/**
 * Check semua customer ACTIVE setiap jam.
 *
 * Strategi bulk:
 *  1. Ambil SEMUA active session dari tiap router sekaligus (1 query per router)
 *  2. Simpan hasilnya di cache (TTL 55 menit) — hindari hammer Mikrotik
 *  3. Bandingkan daftar customer ACTIVE kita vs cache
 *  4. Tidak ditemukan → set DISCONNECTED (CheckDisconnectedCustomers yang handle selanjutnya)
 */
class CheckActiveCustomersCommand extends Command
{
    protected $signature   = 'customers:check-active {--fresh : Paksa fetch ulang dari Mikrotik, abaikan cache}';
    protected $description = 'Check active customers every hour using bulk router session fetch + cache';

    // Cache TTL: 55 menit (aman untuk jadwal hourly)
    private const CACHE_TTL_MINUTES = 55;
    private const CACHE_KEY_PREFIX  = 'router_active_sessions:';

    public function handle(RouterOSService $ros): int
    {
        $forceFresh = (bool) $this->option('fresh');

        // 1. Ambil semua customer ACTIVE dari DB
        $customers = DB::table('internet_customers')
            ->select(['id', 'router_id', 'username', 'ip_address', 'mac_address', 'status', 'meta', 'access_type'])
            ->where('status', ParamSchema::ACTIVE)
            ->whereNotNull('router_id')
            ->whereNotNull('username')
            ->get()
            ->all();

        if (empty($customers)) {
            $this->info('No active customers to check.');
            return 0;
        }

        $total = count($customers);
        $this->info("Checking {$total} active customers...");
        Log::info('[CheckActive] Started', ['total' => $total, 'fresh' => $forceFresh]);

        // 2. Kumpulkan semua router unik yang dipakai customer ACTIVE ini
        $routerIds = array_unique(array_column($customers, 'router_id'));

        // 3. Fetch & cache session aktif per router (bulk, bukan per customer)
        $allActiveSessions = []; // username → true (dari semua router)

        foreach ($routerIds as $routerId) {
            $sessions = $this->fetchRouterSessions($ros, $routerId, $forceFresh);
            foreach ($sessions as $username => $_) {
                $allActiveSessions[$username] = true;
            }
        }

        // 4. Jika RADIUS enabled, tambahkan sumber RADIUS radacct
        if (RadiusService::isEnabled()) {
            try {
                $cacheKey = 'radius_active_sessions';
                $radiusSessions = $forceFresh
                    ? null
                    : Cache::get($cacheKey);

                if ($radiusSessions === null) {
                    $radiusSessions = RadAcct::whereNull('acctstoptime')
                        ->pluck('username')
                        ->flip()
                        ->all();
                    Cache::put($cacheKey, $radiusSessions, now()->addMinutes(self::CACHE_TTL_MINUTES));
                }

                foreach (array_keys($radiusSessions) as $u) {
                    $allActiveSessions[$u] = true;
                }

                Log::info('[CheckActive] RADIUS sessions loaded', ['count' => count($radiusSessions)]);
            } catch (\Throwable $e) {
                Log::warning('[CheckActive] RADIUS fetch failed (continuing with Mikrotik only)', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 5. Bandingkan customer list vs cache — pisahkan aktif vs tidak ditemukan
        $toDisconnect = [];
        $stillActiveIds = [];
        $checkedAt = now();

        foreach ($customers as $customer) {
            if (isset($allActiveSessions[$customer->username])) {
                $stillActiveIds[] = $customer->id;
            } else {
                $meta = $customer->meta ? json_decode($customer->meta, true) : [];
                $meta['disconnected_at'] = $checkedAt->toIso8601String();

                $toDisconnect[] = [
                    'id'   => $customer->id,
                    'meta' => json_encode($meta),
                ];
            }
        }

        // 6. Update last_updated_router untuk yang masih aktif (tandai sudah diverifikasi)
        if (!empty($stillActiveIds)) {
            DB::table('internet_customers')
                ->whereIn('id', $stillActiveIds)
                ->update(['last_updated_router' => $checkedAt]);
        }

        // 7. Batch update ke DISCONNECTED
        if (!empty($toDisconnect)) {
            DB::transaction(function () use ($toDisconnect, $checkedAt) {
                foreach (array_chunk($toDisconnect, 100) as $chunk) {
                    foreach ($chunk as $data) {
                        DB::table('internet_customers')
                            ->where('id', $data['id'])
                            ->update([
                                'status'              => ParamSchema::DISCONNECTED,
                                'meta'                => $data['meta'],
                                'last_updated_router' => $checkedAt,
                            ]);
                    }
                }
            });
        }

        $disconnected = count($toDisconnect);
        $stillActive  = count($stillActiveIds);

        $this->info("Still active: {$stillActive} customers.");
        if ($disconnected > 0) {
            $this->warn("Disconnected: {$disconnected} customers.");
        }

        Log::info('[CheckActive] Completed', [
            'total'        => $total,
            'still_active' => $stillActive,
            'disconnected' => $disconnected,
        ]);

        return 0;
    }

    /**
     * Ambil semua username aktif dari satu router, simpan di cache.
     * Return: ['username' => true, ...]
     */
    private function fetchRouterSessions(RouterOSService $ros, int $routerId, bool $forceFresh): array
    {
        $cacheKey = self::CACHE_KEY_PREFIX . $routerId;

        if (!$forceFresh) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                Log::info('[CheckActive] Cache hit', ['router_id' => $routerId, 'count' => count($cached)]);
                return $cached;
            }
        }

        try {
            $router = Router::find($routerId);
            if (!$router || !$router->host) return [];

            $client   = $ros->client($router);
            $sessions = [];

            // Fetch semua PPPoE active sekaligus
            $pppoeAll = $client->query(new Query('/ppp/active/print'))->read();
            foreach ($pppoeAll as $a) {
                if (!empty($a['name'])) {
                    $sessions[$a['name']] = true;
                }
            }

            // Fetch semua Hotspot active sekaligus
            $hotspotAll = $client->query(new Query('/ip/hotspot/active/print'))->read();
            foreach ($hotspotAll as $a) {
                if (!empty($a['user'])) {
                    $sessions[$a['user']] = true;
                }
            }

            Cache::put($cacheKey, $sessions, now()->addMinutes(self::CACHE_TTL_MINUTES));

            Log::info('[CheckActive] Router sessions fetched & cached', [
                'router_id'  => $routerId,
                'router'     => $router->name,
                'pppoe'      => count($pppoeAll),
                'hotspot'    => count($hotspotAll),
                'unique'     => count($sessions),
            ]);

            return $sessions;
        } catch (\Throwable $e) {
            Log::warning('[CheckActive] Router fetch failed', [
                'router_id' => $routerId,
                'error'     => $e->getMessage(),
            ]);
            // Kembalikan cache lama jika ada (stale-on-error)
            return Cache::get($cacheKey, []);
        }
    }
}
