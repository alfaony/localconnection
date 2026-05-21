<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Services\RouterOSService;
use App\Schemas\ParamSchema;
use App\Jobs\BatchSyncInstalledCustomersJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckDisconnectedCustomersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 2;

    protected ?string $customerId;

    public function __construct(?string $customerId = null)
    {
        $this->customerId = $customerId;
    }

    public function handle(RouterOSService $ros): void
    {
        Log::info('CheckDisconnectedCustomersJob started', [
            'customer_id' => $this->customerId,
        ]);

        if ($this->customerId) {
            $customer = InternetCustomer::with('router')->find($this->customerId);
            if ($customer) {
                $this->checkCustomer($ros, $customer);
            }
        } else {
            $this->checkAll($ros);
        }

        Log::info('CheckDisconnectedCustomersJob completed');
    }

    protected function checkAll(RouterOSService $ros): void
    {
        $customers = InternetCustomer::with('router')
            ->whereIn('status', [
                ParamSchema::DISCONNECTED,
                ParamSchema::INSTALLED,
                ParamSchema::REACTIVATED,
            ])
            ->whereNotNull('router_id')
            ->whereNotNull('username')
            ->get();

        if ($customers->isEmpty()) {
            Log::info('CheckDisconnectedCustomersJob: no customers to check, skipping');
            return;
        }

        $grouped = $customers->groupBy('status');

        $disconnected = $grouped->get(ParamSchema::DISCONNECTED, collect());
        $needsSync    = collect()
            ->merge($grouped->get(ParamSchema::INSTALLED, collect()))
            ->merge($grouped->get(ParamSchema::REACTIVATED, collect()));

        Log::info('CheckDisconnectedCustomersJob found customers', [
            'disconnected' => $disconnected->count(),
            'installed'    => $grouped->get(ParamSchema::INSTALLED, collect())->count(),
            'reactivated'  => $grouped->get(ParamSchema::REACTIVATED, collect())->count(),
        ]);

        foreach ($disconnected as $customer) {
            try {
                $this->checkCustomer($ros, $customer);
            } catch (\Exception $e) {
                Log::error('Failed to check disconnected customer', [
                    'customer_id'   => $customer->id,
                    'customer_code' => $customer->code,
                    'error'         => $e->getMessage(),
                ]);
            }
        }

        if ($needsSync->isNotEmpty()) {
            dispatch(new BatchSyncInstalledCustomersJob());
            Log::info('Dispatched BatchSyncInstalledCustomersJob', [
                'count' => $needsSync->count(),
            ]);
        }
    }

    protected function checkCustomer(RouterOSService $ros, InternetCustomer $customer): void
    {
        $router = $customer->router;

        if (!$router) {
            Log::warning('Disconnected customer has no router', ['customer_id' => $customer->id]);
            return;
        }

        try {
            $client = $ros->client($router);
            $isActive = $ros->isUserActive($client, $customer->username);

            if ($isActive) {
                $activeSession = $client->query(
                    (new \RouterOS\Query('/ppp/active/print'))
                        ->where('name', $customer->username)
                )->read();

                $session = $activeSession[0] ?? [];

                $customer->update([
                    'status'             => ParamSchema::ACTIVE,
                    'ip_address'         => $session['address'] ?? null,
                    'mac_address'        => $session['caller-id'] ?? null,
                    'last_updated_router' => now(),
                ]);

                Log::info('Disconnected customer back ACTIVE', [
                    'customer' => $customer->code,
                    'username' => $customer->username,
                    'ip'       => $session['address'] ?? null,
                ]);
            } else {
                Log::info('Disconnected customer still offline', [
                    'customer' => $customer->code,
                    'username' => $customer->username,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to check disconnected customer on router', [
                'customer_id' => $customer->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('CheckDisconnectedCustomersJob failed', [
            'customer_id' => $this->customerId,
            'error'       => $exception->getMessage(),
        ]);
    }
}
