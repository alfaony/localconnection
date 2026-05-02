<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Services\RouterOSService;
use App\Schemas\ParamSchema;
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
            $this->checkAllDisconnected($ros);
        }

        Log::info('CheckDisconnectedCustomersJob completed');
    }

    protected function checkAllDisconnected(RouterOSService $ros): void
    {
        $customers = InternetCustomer::with('router')
            ->where('status', ParamSchema::DISCONNECTED)
            ->whereNotNull('router_id')
            ->whereNotNull('username')
            ->get();

        Log::info('Found disconnected customers to check', ['count' => $customers->count()]);

        foreach ($customers as $customer) {
            try {
                $this->checkCustomer($ros, $customer);
            } catch (\Exception $e) {
                Log::error('Failed to check disconnected customer', [
                    'customer_id' => $customer->id,
                    'customer_code' => $customer->code,
                    'error' => $e->getMessage(),
                ]);
            }
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
