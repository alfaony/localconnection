<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\Radius\RadAcct;
use App\Services\RadiusService;
use App\Schemas\ParamSchema;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Job untuk check status customer via RADIUS accounting
 * - Update last_updated_router jika masih aktif
 * - Set status REACTIVATED jika tidak aktif
 * - Dispatch provision job untuk reconnect
 */
class CheckActiveCustomersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 2;

    protected ?string $customerId;

    public function __construct(?string $customerId = null)
    {
        $this->customerId = $customerId;
    }

    public function handle(RadiusService $radius): void
    {
        Log::info('CheckActiveCustomersJob started', [
            'customer_id' => $this->customerId,
        ]);

        if ($this->customerId) {
            $customer = InternetCustomer::with('router')->find($this->customerId);
            if ($customer) {
                $this->checkCustomerStatus($radius, $customer);
            }
        } else {
            $this->checkAllActiveCustomers($radius);
        }

        Log::info('CheckActiveCustomersJob completed');
    }

    protected function checkAllActiveCustomers(RadiusService $radius): void
    {
        $customers = InternetCustomer::with('router')
            ->where('status', ParamSchema::ACTIVE)
            ->whereNotNull('router_id')
            ->whereNotNull('username')
            ->get();

        Log::info('Found active customers to check', ['count' => $customers->count()]);

        foreach ($customers as $customer) {
            try {
                $this->checkCustomerStatus($radius, $customer);
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

    protected function checkCustomerStatus(RadiusService $radius, InternetCustomer $customer): void
    {
        try {
            // Cek session aktif via radacct (pengganti /ppp/active/print)
            $isActive = $radius->isUserActive($customer->username);

            if ($isActive) {
                $this->handleActiveCustomer($customer);
            } else {
                $this->handleInactiveCustomer($customer);
            }
        } catch (\Exception $e) {
            $this->handleInactiveCustomer($customer);
            Log::error('Failed to check customer status', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ Handle active customer — update info dari radacct
     */
    protected function handleActiveCustomer(InternetCustomer $customer): void
    {
        try {
            // Ambil data session dari radacct
            $session = RadAcct::where('username', $customer->username)
                ->whereNull('acctstoptime')
                ->orderByDesc('acctstarttime')
                ->first();

            if (!$session) {
                return;
            }

            $customer->update([
                'ip_address'           => $session->framedipaddress ?: $customer->ip_address,
                'mac_address'          => $session->callingstationid ?: $customer->mac_address,
                'last_updated_router'  => now(),
            ]);

            Log::info('Customer active - Updated via RADIUS', [
                'customer'  => $customer->code,
                'username'  => $customer->username,
                'ip'        => $session->framedipaddress,
                'nas'       => $session->nasipaddress,
                'uptime'    => $session->acctsessiontime ? gmdate('H:i:s', $session->acctsessiontime) : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update active customer', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ❌ Handle inactive customer — trigger reconnection
     */
    protected function handleInactiveCustomer(InternetCustomer $customer): void
    {
        Log::warning('Customer is INACTIVE', [
            'customer'     => $customer->code,
            'username'     => $customer->username,
            'last_updated' => $customer->last_updated_router ? Carbon::parse($customer->last_updated_router)->diffForHumans() : null,
        ]);

        try {
            $customer->update([
                'status'      => ParamSchema::REACTIVATED,
                'ip_address'  => null,
                'mac_address' => null,
            ]);

            Log::info('Customer status updated to REACTIVATED', [
                'customer' => $customer->code
            ]);

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

    public function failed(\Throwable $exception): void
    {
        Log::error('CheckActiveCustomersJob failed', [
            'customer_id' => $this->customerId,
            'error' => $exception->getMessage(),
        ]);
    }
}