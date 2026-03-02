<?php

namespace App\Services\Mikrotik;

use App\Models\Router;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RouterDownNotification;

/**
 * ✅ NEW: Service untuk monitoring router health dan alerting
 */
class RouterMonitoringService
{
    /**
     * Get system-wide router health statistics
     */
    public function getHealthStats(): array
    {
        return Cache::remember('router_health_stats', now()->addMinutes(1), function () {
            $total = Router::count();
            $up = Router::where('active_status', Router::STATUS_UP)->count();
            $down = Router::where('active_status', Router::STATUS_DOWN)->count();
            $error = Router::where('active_status', Router::STATUS_ERROR)->count();
            $unknown = Router::where('active_status', Router::STATUS_UNKNOWN)->count();

            return [
                'total' => $total,
                'up' => $up,
                'down' => $down,
                'error' => $error,
                'unknown' => $unknown,
                'health_percentage' => $total > 0 ? round(($up / $total) * 100, 2) : 0,
                'last_updated' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Get routers yang bermasalah (down/error)
     */
    public function getProblematicRouters(): \Illuminate\Database\Eloquent\Collection
    {
        return Router::whereIn('active_status', [
                Router::STATUS_DOWN,
                Router::STATUS_ERROR,
            ])
            ->with(['company', 'pop'])
            ->orderBy('last_check_at', 'desc')
            ->get();
    }

    /**
     * Get routers yang belum di-check dalam X menit
     */
    public function getStaleRouters(int $minutes = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Router::where(function ($q) use ($minutes) {
                $q->whereNull('last_check_at')
                  ->orWhere('last_check_at', '<', now()->subMinutes($minutes));
            })
            ->with(['company', 'pop'])
            ->get();
    }

    /**
     * Check dan alert jika ada router down
     */
    public function checkAndAlertDownRouters(): void
    {
        $downRouters = $this->getProblematicRouters();

        if ($downRouters->isEmpty()) {
            return;
        }

        // Group by company untuk efficient notification
        $grouped = $downRouters->groupBy('company_id');

        foreach ($grouped as $companyId => $routers) {
            $this->sendDownRouterAlert($companyId, $routers);
        }
    }

    /**
     * Send alert untuk routers yang down
     */
    protected function sendDownRouterAlert(int $companyId, $routers): void
    {
        // Cache key untuk prevent spam alerts
        $cacheKey = "router_down_alert_{$companyId}";

        // Only send alert once per hour
        if (Cache::has($cacheKey)) {
            return;
        }

        try {
            // Get admin users untuk company
            $admins = \App\Models\User::where('company_id', $companyId)
                ->whereHas('role', fn($q) => $q->whereIn('name', ['admin', 'super_admin']))
                ->get();

            if ($admins->isEmpty()) {
                Log::warning('[RouterMonitoring] No admins found for company', [
                    'company_id' => $companyId,
                ]);
                return;
            }

            // Send notification
            Notification::send($admins, new RouterDownNotification($routers));

            // Cache untuk 1 hour
            Cache::put($cacheKey, true, now()->addHour());

            Log::info('[RouterMonitoring] Down router alert sent', [
                'company_id' => $companyId,
                'routers_count' => $routers->count(),
                'admins_count' => $admins->count(),
            ]);

        } catch (\Throwable $e) {
            Log::error('[RouterMonitoring] Failed to send alert', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate health report untuk dashboard
     */
    public function generateHealthReport(): array
    {
        $stats = $this->getHealthStats();
        $problematic = $this->getProblematicRouters();
        $stale = $this->getStaleRouters();

        return [
            'stats' => $stats,
            'problematic_routers' => $problematic->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'status' => $r->active_status,
                'last_check' => $r->last_check_at?->diffForHumans(),
                'last_error' => $r->last_error,
                'company' => $r->company?->name,
                'pop' => $r->pop?->name,
            ]),
            'stale_routers' => $stale->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'last_check' => $r->last_check_at?->diffForHumans() ?? 'Never',
                'company' => $r->company?->name,
            ]),
            'recommendations' => $this->generateRecommendations($stats, $problematic, $stale),
        ];
    }

    /**
     * Generate recommendations berdasarkan health metrics
     */
    protected function generateRecommendations(array $stats, $problematic, $stale): array
    {
        $recommendations = [];

        if ($stats['health_percentage'] < 90) {
            $recommendations[] = [
                'severity' => 'high',
                'message' => "System health is below 90% ({$stats['health_percentage']}%). Immediate action required.",
                'action' => 'Check problematic routers and investigate connectivity issues.',
            ];
        }

        if ($problematic->count() > 0) {
            $recommendations[] = [
                'severity' => 'medium',
                'message' => "{$problematic->count()} router(s) are currently down or have errors.",
                'action' => 'Review router logs and check network connectivity.',
            ];
        }

        if ($stale->count() > 0) {
            $recommendations[] = [
                'severity' => 'low',
                'message' => "{$stale->count()} router(s) have not been checked recently.",
                'action' => 'Verify health check jobs are running properly.',
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'severity' => 'info',
                'message' => 'All routers are healthy!',
                'action' => 'Continue monitoring.',
            ];
        }

        return $recommendations;
    }

    /**
     * Force health check untuk specific router
     */
    public function forceHealthCheck(int $routerId): array
    {
        $router = Router::findOrFail($routerId);

        try {
            $mikrotik = new MikrotikService($routerId);
            $response = $mikrotik->ping();

            $status = $response->getStatusCode() === 200 
                ? Router::STATUS_UP 
                : Router::STATUS_DOWN;

            $router->updateHealthStatus($status);

            return [
                'success' => true,
                'status' => $status,
                'message' => "Router health check completed: {$status}",
            ];

        } catch (\Throwable $e) {
            $router->updateHealthStatus(Router::STATUS_ERROR, $e->getMessage());

            return [
                'success' => false,
                'status' => Router::STATUS_ERROR,
                'message' => "Health check failed: {$e->getMessage()}",
            ];
        }
    }
}