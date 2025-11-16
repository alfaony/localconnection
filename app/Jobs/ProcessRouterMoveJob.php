<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\Router;
use App\Models\PackageRouterProfile;

use App\Services\RouterOSService;
use App\Services\PoolResolver;
use App\Schemas\ParamSchema;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Jobs\ProvisionCustomerJob;

/**
 * ✅ Job untuk memproses perpindahan router
 * Langsung komunikasi dengan MikroTik (tidak dispatch job lagi)
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

    /**
     * Create a new job instance.
     */
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
        // $this->onQueue('router-provisioning');
    }

    /**
     * Execute the job - Handle directly with RouterOSService
     */
    public function handle(RouterOSService $ros)
    {
        DB::beginTransaction();
        
        $customer = InternetCustomer::lockForUpdate()
            ->findOrFail($this->customerId);
        
        // 1. Delete old router
        $this->deleteFromOldRouter($ros, $customer);
        
        // 2. Update DB
        $customer->update([
            'router_id' => $this->newRouterId,
            'username' => $this->newUsername ?? $customer->username,
            'local_address' => $this->newLocalAddress,
            'override_pool_id' => $this->newPoolId,
            'ip_address' => null,
            'mac_address' => null,
            'status' => ParamSchema::REACTIVATED,
        ]);
        
        // 3. Create new router
        $this->createOnNewRouter($ros, $customer);
        
        DB::commit();

        ProvisionCustomerJob::dispatch($customer->id);
    }

    /**
     * ✅ STEP 1: Delete secret from old router via MikroTik API
     */
    protected function deleteFromOldRouter(RouterOSService $ros, InternetCustomer $customer)
    {
        $oldRouter = Router::find($this->oldRouterId);

        if (!$oldRouter) {
            Log::warning('Old router not found', ['router_id' => $this->oldRouterId]);
            return;
        }

        try {
            Log::info('Connecting to old router', [
                'router' => $oldRouter->name,
                'username' => $customer->username,
            ]);

            $client = $ros->client($oldRouter);

            // Check if secret exists
            $secret = $client->query(
                (new \RouterOS\Query('/ppp/secret/print'))
                    ->where('name', $customer->username)
            )->read();

            if (!empty($secret)) {
                Log::info('Secret found on old router, deleting...', [
                    'secret_id' => $secret[0]['.id'],
                ]);

                // Disconnect if active
                $ros->disconnectIfActive($client, $customer->username);

                // Delete secret
                $client->query(
                    (new \RouterOS\Query('/ppp/secret/remove'))
                        ->equal('.id', $secret[0]['.id'])
                )->read();

                Log::info('Secret deleted from old router successfully');
            } else {
                Log::info('Secret not found on old router (already removed)');
            }

        } catch (\Exception $e) {
            // Log but continue - old router might be offline
            Log::error('Failed to delete from old router (continuing anyway)', [
                'router' => $oldRouter->name,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * ✅ STEP 2: Update customer data in database
     */
    protected function updateCustomerData(InternetCustomer $customer)
    {
        Log::info('Updating customer data', [
            'customer' => $customer->code,
            'new_router_id' => $this->newRouterId,
            'new_username' => $this->newUsername,
            'new_local_address' => $this->newLocalAddress,
        ]);

        // Refresh customer to get new router relation
        // $customer->load('router');
    }

    /**
     * ✅ STEP 3: Create secret on new router via MikroTik API
     */
    protected function createOnNewRouter(RouterOSService $ros, InternetCustomer $customer)
    {
        $newRouter = Router::find($this->newRouterId);

        if (!$newRouter) {
            throw new \Exception('New router not found');
        }

        Log::info('Connecting to new router', [
            'router' => $newRouter->name,
            'username' => $customer->username,
        ]);

        try {
            $client = $ros->client($newRouter);
            $pkg = $customer->internetPackage;

            // Get profile mapping
            $map = PackageRouterProfile::where('router_id', $newRouter->id)
                  ->where('package_id', $pkg->id)
                  ->first();

            $profile = $map->ros_profile ?? ('PKG_'.$pkg->id);
            $fup = $profile.'_FUP';

            // Get pool info
            $pool = PoolResolver::forCustomer($customer);
            $poolName = $pool?->name;
            $gateway = $pool?->gateway;

            Log::info('Profile configuration', [
                'profile' => $profile,
                'pool' => $poolName,
                'gateway' => $gateway,
            ]);

            // ========================================
            // ✅ CRITICAL: Create profile FIRST
            // ========================================
            Log::info('Ensuring profile exists on new router...');
            
            $ros->ensurePppProfile(
                $client,
                $pkg,
                $profile,
                $fup,
                $newRouter->id,
                $poolName,
                $gateway
            );

            Log::info('Profile ensured successfully', ['profile' => $profile]);

            // ========================================
            // ✅ THEN: Create secret with that profile
            // ========================================
            Log::info('Creating secret on new router', [
                'username' => $customer->username,
                'local_address' => $customer->local_address,
                'profile' => $profile,
            ]);

            $ros->upsertPppSecret(
                $client, 
                $customer, 
                $profile, 
                $customer->local_address
            );

            Log::info('Secret created successfully with profile', [
                'username' => $customer->username,
                'profile' => $profile,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create on new router', [
                'router' => $newRouter->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \Exception('Failed to provision on new router: ' . $e->getMessage());
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Router move job failed permanently', [
            'customer_id' => $this->customerId,
            'old_router_id' => $this->oldRouterId,
            'new_router_id' => $this->newRouterId,
            'error' => $exception->getMessage(),
        ]);
    }
}