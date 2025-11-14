<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\JobsProvisioning;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ✅ Simplified Job untuk memproses perpindahan router
 * Tanpa migration/logging - langsung proses
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
     * Execute the job.
     */
    public function handle()
    {
        try {
            DB::beginTransaction();

            $customer = InternetCustomer::findOrFail($this->customerId);

            Log::info('Starting router move', [
                'customer_id' => $this->customerId,
                'old_router' => $this->oldRouterId,
                'new_router' => $this->newRouterId,
            ]);

            // ========================================
            // STEP 1: Deprovision from Old Router
            // ========================================
            $this->deprovisionOldRouter($customer);

            // ========================================
            // STEP 2: Update Customer Data
            // ========================================
            $this->updateCustomer($customer);

            // ========================================
            // STEP 3: Provision to New Router
            // ========================================
            $this->provisionNewRouter($customer);

            DB::commit();

            Log::info('Router move completed', [
                'customer_id' => $this->customerId,
            ]);

        } catch (\Exception $e) {
            // dd()
            DB::rollBack();

            Log::error('Router move failed', [
                'customer_id' => $this->customerId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Deprovision from old router
     */
    protected function deprovisionOldRouter($customer)
    {
        Log::info('Deprovisioning from old router', [
            'customer_id' => $customer->id,
            'router_id' => $this->oldRouterId,
        ]);

        // JobsProvisioning::create([
        //     'type' => JobsProvisioning::TYPE_DEPROVISION,
        //     'internet_customer_id' => $customer->id,
        //     'router_id' => $this->oldRouterId,
        //     'status' => JobsProvisioning::STATUS_QUEUED,
        //     'payload' => [
        //         'reason' => 'Router Move',
        //         'old_username' => $customer->username,
        //     ],
        // ]);

        dispatch(new ProvisionCustomerJob($customer->id));
        sleep(3); // Wait for deprovision
    }

    /**
     * Update customer with new router
     */
    protected function updateCustomer($customer)
    {
        Log::info('Updating customer', [
            'customer_id' => $customer->id,
            'new_router' => $this->newRouterId,
        ]);

        $customer->update([
            'router_id' => $this->newRouterId,
            'username' => $this->newUsername ?: $customer->username,
            'local_address' => $this->newLocalAddress ?: $customer->local_address,
            'override_pool_id' => $this->newPoolId,
            'status' => \App\Schemas\ParamSchema::REACTIVATED,
        ]);
    }

    /**
     * Provision to new router
     */
    protected function provisionNewRouter($customer)
    {
        Log::info('Provisioning to new router', [
            'customer_id' => $customer->id,
            'router_id' => $this->newRouterId,
        ]);

        // JobsProvisioning::create([
        //     'type' => JobsProvisioning::TYPE_PROVISION,
        //     'internet_customer_id' => $customer->id,
        //     'router_id' => $this->newRouterId,
        //     'status' => JobsProvisioning::STATUS_QUEUED,
        //     'payload' => [
        //         'reason' => 'Router Move',
        //         'new_username' => $this->newUsername,
        //     ],
        // ]);

        dispatch(new ProvisionCustomerJob($customer->id));
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Router move job failed permanently', [
            'customer_id' => $this->customerId,
            'error' => $exception->getMessage(),
        ]);
    }
}