<?php

namespace App\Jobs;

use App\Models\Router;
use App\Services\MikrotikService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Dedicated job untuk health check router
 * Dipisah dari sync job agar lebih lightweight
 */
class RouterHealthCheckJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30; // Longer timeout to prevent worker killed (Mikrotik service has 10s timeout)
    public int $tries = 2;

    public function __construct(public int $routerId) {}

    public function middleware(): array
    {
        return [
            // Lock untuk prevent duplicate check
            (new WithoutOverlapping("health-check-{$this->routerId}"))
                ->expireAfter(30)
                ->dontRelease(), // Jangan release job jika lock gagal
        ];
    }

    public function handle(): void
    {
        $router = Router::find($this->routerId);
        
        if (!$router) {
            Log::warning('[HealthCheck] Router not found', ['router_id' => $this->routerId]);
            return;
        }

        try {
            $mikrotik = new MikrotikService($this->routerId);
            $response = $mikrotik->ping();  

            if ($response->getStatusCode() === 200) {
                $router->updateHealthStatus(Router::STATUS_UP);
            } else {
                $router->updateHealthStatus(Router::STATUS_DOWN);
            }

        } catch (Throwable $e) {
            // dd("here");
            $router->updateHealthStatus(
                Router::STATUS_ERROR,
                substr($e->getMessage(), 0, 255) // Limit error message length
            );

            Log::warning('[HealthCheck] Router check failed', [
                'router_id' => $this->routerId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(Throwable $e): void
    {
        $router = Router::find($this->routerId);
        
        if ($router) {
            $router->updateHealthStatus(Router::STATUS_ERROR, 'Health check job failed');
        }

        Log::error('[HealthCheck] Job failed', [
            'router_id' => $this->routerId,
            'error' => $e->getMessage(),
        ]);
    }
}