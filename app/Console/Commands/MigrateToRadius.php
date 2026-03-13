<?php

namespace App\Console\Commands;

use App\Models\InternetCustomer;
use App\Models\HotspotVoucher;
use App\Models\Router;
use App\Models\PackageRouterProfile;
use App\Services\RadiusService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateToRadius extends Command
{
    protected $signature = 'radius:migrate
        {--dry-run : Tampilkan apa yang akan dilakukan tanpa write ke DB}
        {--customer= : Migrate satu customer PPPoE saja (by ID)}
        {--only-nas : Hanya migrasi NAS (router) saja}
        {--only-customers : Hanya migrasi customer PPPoE saja}
        {--only-hotspot : Hanya migrasi voucher hotspot saja}
        {--secret=radiussecret123 : Shared secret untuk NAS}';

    protected $description = 'Migrasi data router (NAS), customer PPPoE, dan voucher hotspot existing ke RADIUS database';

    public function handle(RadiusService $radius): int
    {
        $dryRun       = $this->option('dry-run');
        $onlyNas      = $this->option('only-nas');
        $onlyCustomers = $this->option('only-customers');
        $onlyHotspot  = $this->option('only-hotspot');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE — tidak ada perubahan yang ditulis');
        }

        $this->info('RADIUS_ENABLED = ' . (RadiusService::isEnabled() ? 'true ✅' : 'false ❌'));
        $this->newLine();

        $errors = 0;

        if (!$onlyCustomers && !$onlyHotspot) {
            $errors += $this->migrateNas($dryRun);
            $this->newLine();
        }

        if (!$onlyNas && !$onlyHotspot) {
            $errors += $this->migrateCustomers($radius, $dryRun);
            $this->newLine();
        }

        if (!$onlyNas && !$onlyCustomers) {
            $errors += $this->migrateVouchers($radius, $dryRun);
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function migrateNas(bool $dryRun): int
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📡 MIGRASI NAS (Router Mikrotik)');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $secret = $this->option('secret');
        $routers = Router::whereNotNull('host')->get();

        $this->info("Ditemukan {$routers->count()} router");
        $this->newLine();

        $success = 0;
        $errors = 0;
        $rows = [];

        foreach ($routers as $router) {
            try {
                if ($dryRun) {
                    $rows[] = [$router->name, $router->host, $secret, '🔍 dry-run'];
                } else {
                    DB::connection('radius')->table('nas')->updateOrInsert(
                        ['nasname' => $router->host],
                        [
                            'shortname'   => $router->name,
                            'type'        => 'other',
                            'secret'      => $secret,
                            'description' => "Router: {$router->name} (ID: {$router->id})",
                        ]
                    );
                    $rows[] = [$router->name, $router->host, $secret, '✅ synced'];
                }
                $success++;
            } catch (\Throwable $th) {
                $rows[] = [$router->name, $router->host, $secret, "❌ {$th->getMessage()}"];
                $errors++;
            }
        }

        $this->table(['Router', 'IP (NAS)', 'Secret', 'Status'], $rows);
        $this->info("✅ NAS Success: {$success}");
        if ($errors > 0) $this->error("❌ NAS Errors: {$errors}");

        return $errors;
    }

    protected function migrateCustomers(RadiusService $radius, bool $dryRun): int
    {
        $customerId = $this->option('customer');
        $baseQuery  = fn() => InternetCustomer::with(['internetPackage', 'router'])
            ->whereIn('status', ['installed', 'active', 'reactivated'])
            ->whereNotNull('username')
            ->whereNotNull('router_id');

        $errors = 0;

        // ── PPPoE / IPoE ──────────────────────────────────────────
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('👤 MIGRASI CUSTOMER PPPoE / IPoE');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $pppoeQuery = $baseQuery()->whereIn('access_type', ['pppoe', 'ipoe']);
        if ($customerId) $pppoeQuery->where('id', $customerId);
        $pppoeCustomers = $pppoeQuery->get();

        $this->info("Ditemukan {$pppoeCustomers->count()} customer PPPoE/IPoE");
        $this->newLine();

        $errors += $this->processMigrateCustomers($pppoeCustomers, $radius, $dryRun, 'pppoe');

        $this->newLine();

        // ── Hotspot ───────────────────────────────────────────────
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📶 MIGRASI CUSTOMER HOTSPOT');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $hotspotQuery = $baseQuery()->where('access_type', 'hotspot');
        if ($customerId) $hotspotQuery->where('id', $customerId);
        $hotspotCustomers = $hotspotQuery->get();

        $this->info("Ditemukan {$hotspotCustomers->count()} customer hotspot");
        $this->newLine();

        $errors += $this->processMigrateCustomers($hotspotCustomers, $radius, $dryRun, 'hotspot');

        return $errors;
    }

    protected function processMigrateCustomers($customers, RadiusService $radius, bool $dryRun, string $type): int
    {
        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        $success = 0;
        $skipped = 0;
        $errors  = 0;

        foreach ($customers as $cust) {
            try {
                $pkg = $cust->internetPackage;
                if (!$pkg) { $skipped++; $bar->advance(); continue; }

                $map = PackageRouterProfile::where('router_id', $cust->router_id)
                      ->where('package_id', $pkg->id)->first();
                $groupName = $map->ros_profile ?? ('PKG_' . $pkg->id);

                if ($dryRun) {
                    $this->newLine();
                    $this->line("  [{$cust->username}] type: {$type} → group: {$groupName}");
                } else {
                    $radius->ensureGroup($pkg, $groupName);
                    if ($type === 'hotspot') {
                        $radius->upsertHotspotCustomer($cust, $groupName);
                    } else {
                        $radius->upsertUser($cust, $groupName);
                    }
                }
                $success++;
            } catch (\Throwable $th) {
                $this->newLine();
                $this->error("❌ {$cust->username}: {$th->getMessage()}");
                $errors++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Success: {$success} | ⏭️ Skipped: {$skipped}");
        if ($errors > 0) $this->error("❌ Errors: {$errors}");
        if ($dryRun) { $this->newLine(); $this->warn('Dry run — jalankan tanpa --dry-run untuk apply.'); }

        return $errors;
    }

    protected function migrateVouchers(RadiusService $radius, bool $dryRun): int
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('🎫 MIGRASI VOUCHER HOTSPOT');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $vouchers = HotspotVoucher::with('internetPackage')
            ->whereIn('status', [HotspotVoucher::STATUS_UNUSED, HotspotVoucher::STATUS_ACTIVE])
            ->whereNotNull('username')
            // ->whereNotNull('internet_package_id')
            ->get();

        $this->info("Ditemukan {$vouchers->count()} voucher (unused + active)");
        $this->newLine();

        $bar = $this->output->createProgressBar($vouchers->count());
        $bar->start();

        $success = 0;
        $skipped = 0;
        $errors  = 0;

        foreach ($vouchers as $voucher) {
            try {
                if (!$voucher->internetPackage) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if ($dryRun) {
                    $this->newLine();
                    $this->line("  [{$voucher->username}] status: {$voucher->status}");
                } else {
                    $radius->upsertVoucherUser($voucher);
                }
                $success++;
            } catch (\Throwable $th) {
                $this->newLine();
                $this->error("❌ {$voucher->username}: {$th->getMessage()}");
                $errors++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Success: {$success} | ⏭️ Skipped: {$skipped}");
        if ($errors > 0) $this->error("❌ Errors: {$errors}");
        if ($dryRun) { $this->newLine(); $this->warn('Dry run — jalankan tanpa --dry-run untuk apply.'); }

        return $errors;
    }
}
