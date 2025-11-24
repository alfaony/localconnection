<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Jobs\RouterHealthCheckJob;
use App\Jobs\SyncRouterInventoryJob;
use App\Services\Mikrotik\RouterMonitoringService;
use Illuminate\Console\Command;

/**
 * ✅ NEW: Command untuk manual health check semua routers
 * Usage: php artisan routers:health-check {--force}
 */
class RoutersHealthCheckCommand extends Command
{
    protected $signature = 'routers:health-check 
                            {--force : Force check all routers regardless of last check time}
                            {--router= : Check specific router ID only}';

    protected $description = 'Run health check on all routers or specific router';

    public function handle(RouterMonitoringService $monitoring): int
    {
        $force = $this->option('force');
        $routerId = $this->option('router');

        if ($routerId) {
            return $this->checkSingleRouter((int)$routerId, $monitoring);
        }

        $this->info('Starting router health checks...');

        $query = Router::whereNotNull('host');
        
        if (!$force) {
            $this->info('Checking only routers that need refresh...');
        }

        $count = 0;
        $total = $query->count();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunk(50, function ($routers) use ($force, &$count, $bar) {
            foreach ($routers as $router) {
                if ($force || $router->needsHealthCheck()) {
                    dispatch(new RouterHealthCheckJob($router->id))
                        ->onQueue('health-checks');
                    $count++;
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        $this->info("✓ Dispatched {$count} health check jobs out of {$total} routers");

        return self::SUCCESS;
    }

    protected function checkSingleRouter(int $routerId, RouterMonitoringService $monitoring): int
    {
        $this->info("Checking router ID: {$routerId}");

        try {
            $result = $monitoring->forceHealthCheck($routerId);
            
            if ($result['success']) {
                $this->info("✓ {$result['message']}");
                return self::SUCCESS;
            } else {
                $this->error("✗ {$result['message']}");
                return self::FAILURE;
            }

        } catch (\Throwable $e) {
            $this->error("✗ Error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}

/**
 * ✅ NEW: Command untuk sync router inventory
 * Usage: php artisan routers:sync {--router=} {--full}
 */
class RoutersSyncCommand extends Command
{
    protected $signature = 'routers:sync 
                            {--router= : Sync specific router ID only}
                            {--full : Full sync including profiles, secrets, and sessions}
                            {--online-only : Only sync online routers}';

    protected $description = 'Sync router inventory data';

    public function handle(): int
    {
        $routerId = $this->option('router');
        $full = $this->option('full');
        $onlineOnly = $this->option('online-only');

        if ($routerId) {
            return $this->syncSingleRouter((int)$routerId, $full);
        }

        $this->info('Starting router inventory sync...');

        $query = Router::query();

        if ($onlineOnly) {
            $query->online();
            $this->info('Syncing online routers only...');
        }

        $count = 0;
        $total = $query->count();

        if ($total === 0) {
            $this->warn('No routers to sync');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->chunk(20, function ($routers) use ($full, &$count, $bar) {
            foreach ($routers as $router) {
                dispatch((new SyncRouterInventoryJob(
                    routerId: $router->id,
                    withProfiles: $full,
                    withSecrets: $full,
                    withSessions: $full,
                    withPppoe: true,
                ))->onQueue('mikrotik'));

                $count++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        $this->info("✓ Dispatched {$count} sync jobs");

        return self::SUCCESS;
    }

    protected function syncSingleRouter(int $routerId, bool $full): int
    {
        $router = Router::find($routerId);

        if (!$router) {
            $this->error("Router ID {$routerId} not found");
            return self::FAILURE;
        }

        $this->info("Syncing router: {$router->name} (ID: {$routerId})");

        dispatch((new SyncRouterInventoryJob(
            routerId: $routerId,
            withProfiles: $full,
            withSecrets: $full,
            withSessions: $full,
            withPppoe: true,
        ))->onQueue('mikrotik'));

        $this->info('✓ Sync job dispatched');

        return self::SUCCESS;
    }
}

/**
 * ✅ NEW: Command untuk monitoring report
 * Usage: php artisan routers:report
 */
class RoutersReportCommand extends Command
{
    protected $signature = 'routers:report 
                            {--json : Output as JSON}';

    protected $description = 'Generate router health report';

    public function handle(RouterMonitoringService $monitoring): int
    {
        $report = $monitoring->generateHealthReport();

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT));
            return self::SUCCESS;
        }

        // Display formatted report
        $this->info('═══════════════════════════════════════');
        $this->info('        ROUTER HEALTH REPORT          ');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        // Stats
        $stats = $report['stats'];
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Routers', $stats['total']],
                ['Online (UP)', "<info>{$stats['up']}</info>"],
                ['Offline (DOWN)', $stats['down'] > 0 ? "<error>{$stats['down']}</error>" : $stats['down']],
                ['Error', $stats['error'] > 0 ? "<error>{$stats['error']}</error>" : $stats['error']],
                ['Unknown', $stats['unknown']],
                ['Health %', "<info>{$stats['health_percentage']}%</info>"],
            ]
        );

        $this->newLine();

        // Problematic routers
        if (!empty($report['problematic_routers'])) {
            $this->error('⚠ Problematic Routers:');
            $this->table(
                ['ID', 'Name', 'Status', 'Last Check', 'Company'],
                collect($report['problematic_routers'])->map(fn($r) => [
                    $r['id'],
                    $r['name'],
                    $r['status'],
                    $r['last_check'] ?? 'Never',
                    $r['company'] ?? '-',
                ])
            );
            $this->newLine();
        }

        // Stale routers
        if (!empty($report['stale_routers'])) {
            $this->warn('⏰ Stale Routers (not checked recently):');
            $this->table(
                ['ID', 'Name', 'Last Check', 'Company'],
                collect($report['stale_routers'])->map(fn($r) => [
                    $r['id'],
                    $r['name'],
                    $r['last_check'],
                    $r['company'] ?? '-',
                ])
            );
            $this->newLine();
        }

        // Recommendations
        $this->info('📋 Recommendations:');
        foreach ($report['recommendations'] as $rec) {
            $icon = match($rec['severity']) {
                'high' => '🔴',
                'medium' => '🟡',
                'low' => '🔵',
                default => 'ℹ️',
            };
            
            $this->line("{$icon} [{$rec['severity']}] {$rec['message']}");
            $this->line("   → {$rec['action']}");
        }

        return self::SUCCESS;
    }
}