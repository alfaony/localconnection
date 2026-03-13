<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\Router;
use App\Models\Radius\RadAcct;
use App\Schemas\ParamSchema;
use App\Services\RadiusService;
use App\Services\RouterOSService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use RouterOS\Query;

/**
 * HYBRID VERSION: Sync installed customers via RADIUS accounting + Mikrotik API fallback.
 * 
 * Flow:
 * 1. Cek radacct (RADIUS accounting) untuk active sessions
 * 2. Customer yang tidak ditemukan di radacct → fallback cek langsung ke Mikrotik API
 * 3. Update status ke ACTIVE jika ditemukan aktif di salah satu sumber
 */
class SyncInstalledCustomersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 2;

    public function __construct(public ?array $customerIds = null) {}

    public function handle(RadiusService $radius): void
    {
        $totalChecked = 0;
        $totalActivated = 0;
        $radiusHits = 0;
        $mikrotikHits = 0;

        // 1. Ambil semua customer INSTALLED/REACTIVATED
        $customers = $this->getCustomers();

        Log::info('[SyncJob] Started (HYBRID mode)', [
            'total_customers' => count($customers),
        ]);

        if (empty($customers)) return;

        // 2. Ambil active sessions dari radacct
        $activeSessions = $this->getActiveSessions();

        Log::info('[SyncJob] RADIUS radacct active sessions', [
            'count' => count($activeSessions),
        ]);

        // 3. Process: RADIUS first, collect remaining for Mikrotik fallback
        $updates = [];
        $remaining = []; // Customers not found in radacct → fallback to Mikrotik

        foreach ($customers as $customer) {
            $totalChecked++;

            $session = $activeSessions[$customer->username] ?? null;

            if ($session) {
                // ✅ Ditemukan di RADIUS radacct
                $radiusHits++;
                $totalActivated++;

                $updates[] = $this->buildUpdateFromRadius($customer, $session);
            } else {
                // ❌ Tidak ada di radacct → simpan untuk fallback Mikrotik
                $remaining[] = $customer;
            }
        }

        // 4. Fallback: Cek Mikrotik API untuk customer yang tidak ada di radacct
        if (!empty($remaining)) {
            Log::info('[SyncJob] Fallback to Mikrotik API', [
                'remaining_customers' => count($remaining),
            ]);

            $mikrotikUpdates = $this->checkViaMikrotikApi($remaining);
            $mikrotikHits = count($mikrotikUpdates);
            $totalActivated += $mikrotikHits;

            $updates = array_merge($updates, $mikrotikUpdates);
        }

        // 5. Batch update
        if (!empty($updates)) {
            $this->batchUpdateCustomers($updates);
        }

        Log::info('[SyncJob] Completed (HYBRID)', [
            'checked'       => $totalChecked,
            'activated'     => $totalActivated,
            'via_radius'    => $radiusHits,
            'via_mikrotik'  => $mikrotikHits,
        ]);
    }

    /**
     * Build update array dari RADIUS radacct session
     */
    protected function buildUpdateFromRadius(object $customer, object $session): array
    {
        $meta = $customer->meta ? json_decode($customer->meta, true) : [];
        $meta['radius_session'] = [
            'source'       => 'radacct',
            'nas_ip'       => $session->nasipaddress,
            'framed_ip'    => $session->framedipaddress,
            'calling_id'   => $session->callingstationid,
            'start_time'   => $session->acctstarttime,
            'session_time' => $session->acctsessiontime,
            'input_octets'  => $session->acctinputoctets,
            'output_octets' => $session->acctoutputoctets,
            'last_seen'    => now()->toIso8601String(),
        ];

        return [
            'id'          => $customer->id,
            'status'      => ParamSchema::ACTIVE,
            'ip_address'  => $session->framedipaddress ?: $customer->ip_address,
            'mac_address' => $session->callingstationid ?: $customer->mac_address,
            'meta'        => json_encode($meta),
        ];
    }

    /**
     * Fallback: Cek active connections langsung ke Mikrotik API
     * Group by router untuk efisiensi (1 koneksi per router)
     */
    protected function checkViaMikrotikApi(array $customers): array
    {
        $updates = [];
        $ros = app(RouterOSService::class);

        // Group by router_id
        $grouped = collect($customers)->groupBy('router_id');

        foreach ($grouped as $routerId => $routerCustomers) {
            try {
                $router = Router::find($routerId);
                if (!$router || !$router->host) continue;

                $client = $ros->client($router);

                // Split customers by access_type
                $pppoeCustomers   = $routerCustomers->filter(fn($c) => ($c->access_type ?? 'pppoe') !== 'hotspot');
                $hotspotCustomers = $routerCustomers->filter(fn($c) => ($c->access_type ?? '') === 'hotspot');
                // --- PPPoE: /ppp/active/print ---
                if (!empty($pppoeCustomers)) {
                    $allActive = $client->query(new Query('/ppp/active/print'))->read();
                    $activeByName = [];
                    foreach ($allActive as $a) {
                        $activeByName[$a['name']] = $a;
                    }

                    Log::info("[SyncJob] Mikrotik PPPoE active connections", [
                        'router'       => $router->name,
                        'router_id'    => $routerId,
                        'active_count' => count($allActive),
                    ]);

                    foreach ($pppoeCustomers as $customer) {
                        $active = $activeByName[$customer->username] ?? null;
                        if ($active) {
                            $meta = $customer->meta ? json_decode($customer->meta, true) : [];
                            $meta['radius_session'] = [
                                'source'    => 'mikrotik_api',
                                'caller_id' => $active['caller-id'] ?? null,
                                'address'   => $active['address'] ?? null,
                                'uptime'    => $active['uptime'] ?? null,
                                'service'   => $active['service'] ?? null,
                                'last_seen' => now()->toIso8601String(),
                            ];
                            $updates[] = [
                                'id'          => $customer->id,
                                'status'      => ParamSchema::ACTIVE,
                                'ip_address'  => $active['address'] ?? $customer->ip_address,
                                'mac_address' => $active['caller-id'] ?? $customer->mac_address,
                                'meta'        => json_encode($meta),
                            ];
                        }
                    }
                }

                // --- Hotspot: /ip/hotspot/active/print ---
                if (!empty($hotspotCustomers)) {
                    $allHsActive = $client->query(new Query('/ip/hotspot/active/print'))->read();
                    $hsActiveByUser = [];
                    foreach ($allHsActive as $a) {
                        $hsActiveByUser[$a['user']] = $a;
                    }

                    Log::info("[SyncJob] Mikrotik Hotspot active connections", [
                        'router'       => $router->name,
                        'router_id'    => $routerId,
                        'active_count' => count($allHsActive),
                    ]);

                    foreach ($hotspotCustomers as $customer) {
                        $active = $hsActiveByUser[$customer->username] ?? null;
                        if ($active) {
                            $meta = $customer->meta ? json_decode($customer->meta, true) : [];
                            $meta['radius_session'] = [
                                'source'    => 'mikrotik_api_hotspot',
                                'address'   => $active['address'] ?? null,
                                'mac'       => $active['mac-address'] ?? null,
                                'uptime'    => $active['uptime'] ?? null,
                                'last_seen' => now()->toIso8601String(),
                            ];
                            $updates[] = [
                                'id'          => $customer->id,
                                'status'      => ParamSchema::ACTIVE,
                                'ip_address'  => $active['address'] ?? $customer->ip_address,
                                'mac_address' => $active['mac-address'] ?? $customer->mac_address,
                                'meta'        => json_encode($meta),
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("[SyncJob] Mikrotik API fallback failed for router {$routerId}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $updates;
    }

    /**
     * Get customers yang perlu di-sync
     */
    protected function getCustomers(): array
    {
        $query = DB::table('internet_customers')
            ->select(['id', 'router_id', 'username', 'ip_address', 'mac_address', 'meta', 'access_type'])
            ->whereIn('status', [ParamSchema::INSTALLED, ParamSchema::REACTIVATED])
            ->whereNotNull('router_id')
            ->whereNotNull('username');

        if ($this->customerIds) {
            $query->whereIn('id', $this->customerIds);
        }

        return $query->get()->all();
    }

    /**
     * Get semua active sessions dari radacct, indexed by username
     */
    protected function getActiveSessions(): array
    {
        $sessions = RadAcct::whereNull('acctstoptime')
            ->orderByDesc('acctstarttime')
            ->get();

        $indexed = [];
        foreach ($sessions as $session) {
            // Hanya ambil session terbaru per username
            if (!isset($indexed[$session->username])) {
                $indexed[$session->username] = $session;
            }
        }

        return $indexed;
    }

    /**
     * Batch update customers (efficient!)
     */
    protected function batchUpdateCustomers(array $updates): void
    {
        DB::transaction(function () use ($updates) {
            foreach (array_chunk($updates, 100) as $chunk) {
                foreach ($chunk as $data) {
                    DB::table('internet_customers')
                        ->where('id', $data['id'])
                        ->update([
                            'status'               => $data['status'],
                            'ip_address'            => $data['ip_address'],
                            'mac_address'           => $data['mac_address'],
                            'meta'                  => $data['meta'],
                            'last_updated_router'   => now(),
                        ]);
                }
            }
        });

        Log::info('[SyncJob] Batch updated customers', ['count' => count($updates)]);
    }
}