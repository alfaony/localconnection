<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\Radius\RadAcct;
use App\Schemas\ParamSchema;
use App\Services\RadiusService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * ✅ RADIUS VERSION: Sync installed customers via RADIUS accounting data
 * - No router connections needed
 * - Reads from radacct table for active sessions
 * - Batch updates customer status, IP, MAC
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

        // 1. Ambil semua customer INSTALLED/REACTIVATED
        $customers = $this->getCustomers();

        Log::info('SyncInstalledCustomersJob started (RADIUS)', [
            'total_customers' => count($customers),
        ]);

        // 2. Ambil semua active sessions dari radacct
        $activeSessions = $this->getActiveSessions();

        Log::info('Active sessions from RADIUS', [
            'count' => count($activeSessions),
        ]);

        // 3. Process setiap customer
        $updates = [];
        foreach ($customers as $customer) {
            $totalChecked++;

            $session = $activeSessions[$customer->username] ?? null;

            if ($session) {
                $totalActivated++;

                $meta = $customer->meta ? json_decode($customer->meta, true) : [];
                $meta['radius_session'] = [
                    'nas_ip'      => $session->nasipaddress,
                    'framed_ip'   => $session->framedipaddress,
                    'calling_id'  => $session->callingstationid,
                    'start_time'  => $session->acctstarttime,
                    'session_time' => $session->acctsessiontime,
                    'input_octets'  => $session->acctinputoctets,
                    'output_octets' => $session->acctoutputoctets,
                    'last_seen'   => now()->toIso8601String(),
                ];

                $updates[] = [
                    'id'          => $customer->id,
                    'status'      => ParamSchema::ACTIVE,
                    'ip_address'  => $session->framedipaddress ?: $customer->ip_address,
                    'mac_address' => $session->callingstationid ?: $customer->mac_address,
                    'meta'        => json_encode($meta),
                ];
            }
        }

        // 4. Batch update
        if (!empty($updates)) {
            $this->batchUpdateCustomers($updates);
        }

        Log::info('SyncInstalledCustomersJob completed (RADIUS)', [
            'checked'   => $totalChecked,
            'activated' => $totalActivated,
        ]);
    }

    /**
     * Get customers yang perlu di-sync
     */
    protected function getCustomers(): array
    {
        $query = DB::table('internet_customers')
            ->select(['id', 'router_id', 'username', 'ip_address', 'mac_address', 'meta'])
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

        Log::info('Batch updated customers via RADIUS', ['count' => count($updates)]);
    }
}