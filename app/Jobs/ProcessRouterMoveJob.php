<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\Router;
use App\Models\PackageRouterProfile;

use App\Services\RadiusService;
use App\Services\RouterOSService;
use App\Schemas\ParamSchema;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ✅ DUAL-MODE: RADIUS primary + Direct API fallback
 *
 * - Disconnect dari router lama → Direct API
 * - Auth update → RADIUS primary → Direct API fallback
 * - PPP Profile di router baru → Direct API
 */
class ProcessRouterMoveJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 3;
    public $backoff = 60;

    protected $customerId;
    protected $oldRouterId;
    protected $newRouterId;
    protected $newUsername;
    protected $newLocalAddress;
    protected $newPoolId;

    public function __construct(
        $customerId,
        $oldRouterId,
        $newRouterId,
        $newUsername = null,
        $newLocalAddress = null,
        $newPoolId = null
    ) {
        $this->customerId = $customerId;
        $this->oldRouterId = $oldRouterId;
        $this->newRouterId = $newRouterId;
        $this->newUsername = $newUsername;
        $this->newLocalAddress = $newLocalAddress;
        $this->newPoolId = $newPoolId;
    }

    public function handle(RadiusService $radius)
    {
        DB::beginTransaction();

        $customer = InternetCustomer::lockForUpdate()->findOrFail($this->customerId);

        Log::info('[RouterMove] Starting move (dual-mode)', [
            'customer'   => $customer->username,
            'old_router' => $this->oldRouterId,
            'new_router' => $this->newRouterId,
        ]);

        // 1. DIRECT API: Disconnect dari router lama
        $this->disconnectFromOldRouter($customer);

        // 2. DIRECT API: Hapus secret dari router lama
        $this->removeSecretFromOldRouter($customer);

        // 3. Update customer data di DB
        $customer->update([
            'router_id'        => $this->newRouterId,
            'username'         => $this->newUsername ?? $customer->username,
            'local_address'    => $this->newLocalAddress,
            'override_pool_id' => $this->newPoolId,
            'ip_address'       => null,
            'mac_address'      => null,
            'status'           => ParamSchema::REACTIVATED,
        ]);

        $customer = $customer->fresh();

        // 4. Setup di router baru + RADIUS
        $pkg = $customer->internetPackage;
        $map = PackageRouterProfile::where('router_id', $this->newRouterId)
              ->where('package_id', $pkg->id)->first();
        $groupName = $map->ros_profile ?? ('PKG_' . $pkg->id);

        // DIRECT API: Ensure PPP profile di router baru
        $this->ensureProfileOnNewRouter($customer, $pkg, $groupName);

        // RADIUS: Update auth → fallback Direct API
        try {
            $radius->ensureGroup($pkg, $groupName);
            $radius->upsertUser($customer, $groupName);
            Log::info('[RouterMove] RADIUS auth updated ✅', ['customer' => $customer->username]);
        } catch (\Throwable $e) {
            Log::warning('[RouterMove] RADIUS failed, fallback to Direct API', ['error' => $e->getMessage()]);
            $this->fallbackCreateOnNewRouter($customer, $groupName);
        }

        DB::commit();

        Log::info('[RouterMove] Completed (dual-mode)', [
            'customer' => $customer->username,
            'group'    => $groupName,
        ]);
    }

    protected function disconnectFromOldRouter(InternetCustomer $customer): void
    {
        try {
            $oldRouter = Router::find($this->oldRouterId);
            if (!$oldRouter) return;

            $ros = app(RouterOSService::class);
            $client = $ros->client($oldRouter);
            $ros->disconnectIfActive($client, $customer->username);

            Log::info('[RouterMove] Disconnected from old router ✅');
        } catch (\Throwable $e) {
            Log::warning('[RouterMove] Failed to disconnect from old router (non-fatal)', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function removeSecretFromOldRouter(InternetCustomer $customer): void
    {
        try {
            $oldRouter = Router::find($this->oldRouterId);
            if (!$oldRouter) return;

            $ros = app(RouterOSService::class);
            $client = $ros->client($oldRouter);

            // Hapus secret berdasarkan comment (customer ID)
            $q = (new \RouterOS\Query('/ppp/secret/print'))->where('comment', $customer->id);
            $rows = $client->query($q)->read();

            foreach ($rows as $row) {
                $client->query(
                    (new \RouterOS\Query('/ppp/secret/remove'))->equal('.id', $row['.id'])
                )->read();
            }

            Log::info('[RouterMove] Secret removed from old router ✅');
        } catch (\Throwable $e) {
            Log::warning('[RouterMove] Failed to remove secret from old router (non-fatal)', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function ensureProfileOnNewRouter(InternetCustomer $customer, $pkg, string $groupName): void
    {
        try {
            $newRouter = Router::find($this->newRouterId);
            if (!$newRouter) return;

            $ros = app(RouterOSService::class);
            $client = $ros->client($newRouter);
            $ros->ensurePppProfile($client, $pkg, $groupName, null, $newRouter->id);

            Log::info('[RouterMove] PPP profile ensured on new router ✅');
        } catch (\Throwable $e) {
            Log::warning('[RouterMove] Failed to ensure profile on new router (non-fatal)', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function fallbackCreateOnNewRouter(InternetCustomer $customer, string $groupName): void
    {
        try {
            $newRouter = Router::find($this->newRouterId);
            if (!$newRouter) return;

            $ros = app(RouterOSService::class);
            $client = $ros->client($newRouter);
            $ros->upsertPppSecret($client, $customer, $groupName, $customer->local_address);

            Log::info('[RouterMove] Fallback Direct API on new router ✅');
        } catch (\Throwable $e) {
            Log::error('[RouterMove] Direct API fallback also failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('[RouterMove] Failed permanently', [
            'customer_id'   => $this->customerId,
            'error'         => $exception->getMessage(),
        ]);
    }
}