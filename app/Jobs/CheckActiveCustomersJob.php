<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\Router;
use App\Services\RouterOSService;
use App\Schemas\ParamSchema;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Job untuk check status customer di router
 * - Update last_updated_router jika masih aktif
 * - Set status REACTIVATED jika tidak aktif
 * - Dispatch provision job untuk reconnect
 */
class CheckActiveCustomersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 2;

    protected ?string $customerId;

    /**
     * Create a new job instance.
     * 
     * @param int|null $customerId - Check specific customer, or all if null
     */
    public function __construct(?string $customerId = null)
    {
        $this->customerId = $customerId;
        // $this->onQueue('customer-monitoring');
    }

    /**
     * Execute the job.
     */
    public function handle(RouterOSService $ros): void
    {
        Log::info('CheckActiveCustomersJob started', [
            'customer_id' => $this->customerId,
        ]);

        if ($this->customerId) {
            // Check specific customer
            $customer = InternetCustomer::with(['router', 'userCustomer'])->find($this->customerId);
            
            if ($customer) {
                $this->checkCustomerStatus($ros, $customer);
            }
        } else {
            // Check all active customers
            $this->checkAllActiveCustomers($ros);
        }

        Log::info('CheckActiveCustomersJob completed');
    }

    /**
     * Check all active customers
     */
    protected function checkAllActiveCustomers(RouterOSService $ros): void
    {
        $customers = InternetCustomer::with(['router', 'userCustomer'])
            ->where('status', ParamSchema::ACTIVE)
            ->whereNotNull('router_id')
            ->whereNotNull('username')
            ->get();

        Log::info('Found active customers to check', ['count' => $customers->count()]);

        foreach ($customers as $customer) {
            try {
                $this->checkCustomerStatus($ros, $customer);
            } catch (\Exception $e) {
                Log::error('Failed to check customer', [
                    'customer_id' => $customer->id,
                    'customer_code' => $customer->code,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
    }

    /**
     * Check customer status on router
     */
    protected function checkCustomerStatus(RouterOSService $ros, InternetCustomer $customer): void
    {
        try {
            $router = $customer->router;

            if (!$router) {
                Log::warning('Customer has no router', ['customer_id' => $customer->id]);
                return;
            }

            // Connect to router
            $client = $ros->client($router);

            // Check if customer is active
            $isActive = $ros->isUserActive($client, $customer->username);

            if ($isActive) {
                // ✅ Customer is active - Update last_updated_router
                $this->handleActiveCustomer($client, $customer);
            } else {
                // ❌ Customer is NOT active - Handle reconnection
                $this->handleInactiveCustomer($customer);
            }

        } catch (\Exception $e) {
            $this->handleInactiveCustomer($customer);

            Log::error('Failed to check customer status', [
                'customer_id' => $customer->id,
                'customer_code' => $customer->code,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ Handle active customer - Update info
     */
    protected function handleActiveCustomer($client, InternetCustomer $customer): void
    {
        try {
            // Get active session info
            $activeSession = $client->query(
                (new \RouterOS\Query('/ppp/active/print'))
                    ->where('name', $customer->username)
            )->read();

            if (empty($activeSession)) {
                return;
            }
            
            $session = $activeSession[0];
            // dd($session);

            // Update customer info
            $customer->update([
                'ip_address' => $session['address'] ?? $customer->ip_address,
                'mac_address' => $session['caller-id'] ?? $customer->mac_address,
                'last_updated_router' => now(),
            ]);

            Log::info('Customer active - Updated', [
                'customer' => $customer->code,
                'username' => $customer->username,
                'ip' => $session['address'] ?? null,
                'uptime' => $session['uptime'] ?? null,
            ]);

            // Check billing payment status after updating active info
            $this->checkBillingPaymentStatus($customer);

        } catch (\Exception $e) {
            Log::error('Failed to update active customer', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if customer is within billing window and has unpaid subscription
     */
    protected function checkBillingPaymentStatus(InternetCustomer $customer): void
    {
        $userCustomer = $customer->userCustomer;

        if (!$userCustomer || !$userCustomer->start_billing_date) {
            return;
        }

        $today = Carbon::today();
        $startDay = Carbon::parse($userCustomer->start_billing_date)->day;

        // start_billing_date > today → belum waktunya tagihan, customer tetap ACTIVE
        if ($today->day < $startDay) {
            return;
        }

        // Check if there's a confirmed/paid purchase covering today's date
        $hasPaidPurchase = $customer->purchases()
            ->where('period_start', '<=', $today)
            ->where('period_end', '>=', $today)
            ->whereNotNull('payment_method')
            ->where(function ($q) {
                $q->where(function ($q) {
                    // Manual transfer confirmed by finance
                    $q->whereNotNull('user_finance_id')
                      ->whereNotNull('confirmation_finance_at');
                })->orWhereNotNull('xendit_paid_at')
                  ->orWhereNotNull('midtrans_paid_at');
            })
            ->exists();

        if (!$hasPaidPurchase) {
            $customer->update(['status' => ParamSchema::WAITING_PAYMENT_SUBSCRIPTION]);

            Log::warning('Customer set to WAITING_PAYMENT_SUBSCRIPTION - unpaid billing', [
                'customer'          => $customer->code,
                'start_billing_day' => $startDay,
                'today'             => $today->format('Y-m-d'),
            ]);
        }
    }

    /**
     * ❌ Handle inactive customer - Trigger reconnection
     */
    protected function handleInactiveCustomer(InternetCustomer $customer): void
    {
        Log::warning('Customer is INACTIVE', [
            'customer' => $customer->code,
            'username' => $customer->username
        ]);

        try {
            // Update status and clear connection info
            $customer->update([
                'status' => ParamSchema::DISCONNECTED,
                'ip_address' => null,
                'mac_address' => null,
            ]);

            Log::info('Customer status updated to REACTIVATED', [
                'customer' => $customer->code
            ]);

            // Dispatch provision job to reconnect
            dispatch(new ProvisionCustomerJob($customer->id));

            Log::info('ProvisionCustomerJob dispatched for reconnection', [
                'customer' => $customer->code
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to handle inactive customer', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('CheckActiveCustomersJob failed', [
            'customer_id' => $this->customerId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}