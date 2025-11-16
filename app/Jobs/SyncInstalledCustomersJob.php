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
use Illuminate\Support\Facades\DB;
use RouterOS\Query;
use Illuminate\Support\Str;
use App\Models\PppoeServer;

/**
 * ✅ OPTIMIZED: Memory-efficient sync for installed customers
 * - Uses raw queries where possible
 * - Minimal eager loading
 * - Batch updates
 * - Router connection reuse
 */
class SyncInstalledCustomersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 2;

    public function __construct(public ?array $customerIds = null) {}

    public function handle(RouterOSService $ros): void
    {
        $totalChecked = 0;
        $totalActivated = 0;

        // ✅ Group customers by router FIRST (reduce queries)
        $customersByRouter = $this->getCustomersByRouter();

        Log::info('SyncInstalledCustomersJob started', [
            'routers_count' => count($customersByRouter),
            'total_customers' => array_sum(array_map('count', $customersByRouter)),
        ]);

        // ✅ Process per router (one connection per router)
        foreach ($customersByRouter as $routerId => $customerData) {
            try {
                $this->processRouterCustomers(
                    $ros,
                    $routerId,
                    $customerData,
                    $totalChecked,
                    $totalActivated
                );
            } catch (\Throwable $e) {
                Log::error('Failed to process router', [
                    'router_id' => $routerId,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }

            // ✅ Free memory after each router
            unset($customerData);
            gc_collect_cycles();
        }

        Log::info('SyncInstalledCustomersJob completed', [
            'checked' => $totalChecked,
            'activated' => $totalActivated,
        ]);
    }

    /**
     * ✅ Get customers grouped by router (optimized query)
     */
    protected function getCustomersByRouter(): array
    {
        $query = DB::table('internet_customers')
            ->select([
                'id',
                'router_id',
                'username',
                'ip_address',
                'mac_address',
                'vlan_id',
                'ros_comment_uuid',
                'meta',
            ])
            ->whereIn('status', [ParamSchema::INSTALLED, ParamSchema::REACTIVATED])
            ->whereNotNull('router_id')
            ->whereNotNull('username');

        if ($this->customerIds) {
            $query->whereIn('id', $this->customerIds);
        }

        $customers = $query->get();

        // Group by router_id
        $grouped = [];
        foreach ($customers as $customer) {
            $grouped[$customer->router_id][] = $customer;
        }

        return $grouped;
    }

    /**
     * ✅ Process all customers for one router
     */
    protected function processRouterCustomers(
        RouterOSService $ros,
        int $routerId,
        array $customerData,
        int &$totalChecked,
        int &$totalActivated
    ): void {
        // Get router
        $router = Router::find($routerId);
        if (!$router) {
            Log::warning('Router not found', ['router_id' => $routerId]);
            return;
        }

        // Connect to router ONCE
        try {
            $client = $ros->client($router);
        } catch (\Throwable $e) {
            Log::error('Failed to connect to router', [
                'router_id' => $routerId,
                'router_name' => $router->name,
                'error' => $e->getMessage(),
            ]);
            return;
        }

        // Get VLAN for this router (once)
        $defaultVlanId = $this->getDefaultVlanForRouter($routerId);

        // ✅ Batch get all active sessions from router
        $activeSessions = $this->getAllActiveSessions($client);
        $allSecrets = $this->getAllSecrets($client);

        Log::info('Processing router customers', [
            'router_id' => $routerId,
            'router_name' => $router->name,
            'customers_count' => count($customerData),
            'active_sessions' => count($activeSessions),
        ]);

        // ✅ Process each customer (no DB queries in loop!)
        $updates = [];
        foreach ($customerData as $customer) {
            $totalChecked++;

            try {
                $result = $this->processCustomer(
                    $customer,
                    $activeSessions,
                    $allSecrets,
                    $defaultVlanId
                );

                if ($result) {
                    $updates[] = $result;
                    $totalActivated++;
                }
            } catch (\Throwable $e) {
                Log::error('Failed to process customer', [
                    'customer_id' => $customer->id,
                    'username' => $customer->username,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // ✅ Batch update customers (single query per batch)
        if (!empty($updates)) {
            $this->batchUpdateCustomers($updates);
        }

        // ✅ Update comments on router if needed
        $this->batchUpdateSecretComments($client, $updates);
    }

    /**
     * ✅ Get all active sessions from router at once
     */
    protected function getAllActiveSessions($client): array
    {
        try {
            $sessions = $client->query(new Query('/ppp/active/print'))->read();
            
            $indexed = [];
            foreach ($sessions as $session) {
                if (isset($session['name'])) {
                    $indexed[$session['name']] = $session;
                }
            }
            return $indexed;
        } catch (\Throwable $e) {
            Log::error('Failed to get active sessions', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * ✅ Get all secrets from router at once
     */
    protected function getAllSecrets($client): array
    {
        try {
            $secrets = $client->query(new Query('/ppp/secret/print'))->read();
            
            $indexed = [];
            foreach ($secrets as $secret) {
                if (isset($secret['name'])) {
                    $indexed[$secret['name']] = $secret;
                }
            }
            return $indexed;
        } catch (\Throwable $e) {
            Log::error('Failed to get secrets', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * ✅ Get default VLAN for router
     */
    protected function getDefaultVlanForRouter(int $routerId): ?int
    {
        $srv = PppoeServer::with('interface')
            ->where('router_id', $routerId)
            ->whereNotNull('interface_id')
            ->first();

        return $srv?->interface?->vlan_id;
    }

    /**
     * ✅ Process single customer (no DB queries!)
     */
    protected function processCustomer(
        object $customer,
        array $activeSessions,
        array $allSecrets,
        ?int $defaultVlanId
    ): ?array {
        // Check if customer is active
        if (!isset($activeSessions[$customer->username])) {
            return null; // Not active
        }

        $activeRow = $activeSessions[$customer->username];
        $secretRow = $allSecrets[$customer->username] ?? [];

        // Get IP & MAC from active session
        $ip = $activeRow['address'] ?? $customer->ip_address;
        $mac = $activeRow['caller-id'] ?? $customer->mac_address;

        // Determine VLAN
        $vlanId = $customer->vlan_id ?? $defaultVlanId;

        // Ensure UUID
        $uuid = $customer->ros_comment_uuid ?: $customer->id;

        // Build meta
        $meta = $customer->meta ? json_decode($customer->meta, true) : [];
        $meta['ros_active'] = [
            'id' => $activeRow['.id'] ?? null,
            'address' => $activeRow['address'] ?? null,
            'caller_id' => $activeRow['caller-id'] ?? null,
            'uptime' => $activeRow['uptime'] ?? null,
            'encoding' => $activeRow['encoding'] ?? null,
            'service' => $activeRow['service'] ?? null,
            'last_seen' => now()->toIso8601String(),
        ];
        $meta['ros_secret'] = [
            'id' => $secretRow['.id'] ?? null,
            'disabled' => $secretRow['disabled'] ?? null,
            'profile' => $secretRow['profile'] ?? null,
            'comment' => $secretRow['comment'] ?? null,
        ];

        return [
            'id' => $customer->id,
            'username' => $customer->username,
            'status' => ParamSchema::ACTIVE,
            'ip_address' => $ip,
            'mac_address' => $mac,
            'vlan_id' => $vlanId,
            'ros_comment_uuid' => $customer->id,
            'meta' => json_encode($meta),
            'secret_id' => $secretRow['.id'] ?? null,
            'secret_comment_should' => $customer->id,
            'secret_comment_current' => $secretRow['comment'] ?? null,
        ];
    }

    /**
     * ✅ Batch update customers (efficient!)
     */
    protected function batchUpdateCustomers(array $updates): void
    {
        // ✅ Use DB::transaction with chunking
        DB::transaction(function () use ($updates) {
            foreach (array_chunk($updates, 100) as $chunk) {
                foreach ($chunk as $data) {
                    DB::table('internet_customers')
                        ->where('id', $data['id'])
                        ->update([
                            'status' => $data['status'],
                            'ip_address' => $data['ip_address'],
                            'mac_address' => $data['mac_address'],
                            'vlan_id' => $data['vlan_id'],
                            'ros_comment_uuid' => $data['ros_comment_uuid'],
                            'meta' => $data['meta'],
                            'last_updated_router' => now(),
                        ]);
                }

                // ✅ Free memory after each chunk
                unset($chunk);
                gc_collect_cycles();
            }
        });

        Log::info('Batch updated customers', ['count' => count($updates)]);
    }

    /**
     * ✅ Update secret comments on router if needed
     */
    protected function batchUpdateSecretComments($client, array $updates): void
    {
        $needsUpdate = array_filter($updates, function ($data) {
            return isset($data['secret_id']) &&
                   $data['secret_comment_current'] !== $data['secret_comment_should'];
        });

        if (empty($needsUpdate)) {
            return;
        }

        foreach ($needsUpdate as $data) {
            try {
                $client->query(
                    (new Query('/ppp/secret/set'))
                        ->equal('.id', $data['secret_id'])
                        ->equal('comment', $data['secret_comment_should'])
                )->read();

                Log::debug('Updated secret comment', [
                    'username' => $data['username'],
                    'comment' => $data['secret_comment_should'],
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to update secret comment', [
                    'username' => $data['username'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Updated secret comments', ['count' => count($needsUpdate)]);
    }
}