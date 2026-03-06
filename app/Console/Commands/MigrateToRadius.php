<?php

namespace App\Console\Commands;

use App\Models\InternetCustomer;
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
        {--customer= : Migrate satu customer saja (by ID)}
        {--only-nas : Hanya migrasi NAS (router) saja}
        {--only-customers : Hanya migrasi customer saja}
        {--secret=radiussecret123 : Shared secret untuk NAS}';

    protected $description = 'Migrasi data router (NAS) dan customer existing ke RADIUS database';

    public function handle(RadiusService $radius): int
    {
        $dryRun = $this->option('dry-run');
        $onlyNas = $this->option('only-nas');
        $onlyCustomers = $this->option('only-customers');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE — tidak ada perubahan yang ditulis');
        }

        $errors = 0;

        // ========================================
        // STEP 1: Migrasi NAS (Router Mikrotik)
        // ========================================
        if (!$onlyCustomers) {
            $errors += $this->migrateNas($dryRun);
            $this->newLine();
        }

        // ========================================
        // STEP 2: Migrasi Customer
        // ========================================
        if (!$onlyNas) {
            $errors += $this->migrateCustomers($radius, $dryRun);
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Migrasi router Mikrotik ke tabel NAS
     */
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

        $headers = ['Router', 'IP (NAS)', 'Secret', 'Status'];
        $rows = [];

        foreach ($routers as $router) {
            try {
                $nasName = $router->host;
                $shortName = $router->name;

                if ($dryRun) {
                    $rows[] = [$shortName, $nasName, $secret, '🔍 dry-run'];
                } else {
                    // Upsert ke tabel nas (di database radius)
                    DB::connection('radius')->table('nas')->updateOrInsert(
                        ['nasname' => $nasName],
                        [
                            'shortname'   => $shortName,
                            'type'        => 'other',
                            'secret'      => $secret,
                            'description' => "Router: {$shortName} (ID: {$router->id})",
                        ]
                    );
                    $rows[] = [$shortName, $nasName, $secret, '✅ synced'];
                }

                $success++;
            } catch (\Throwable $th) {
                $rows[] = [$router->name, $router->host, $secret, "❌ {$th->getMessage()}"];
                $errors++;
            }
        }

        $this->table($headers, $rows);
        $this->info("✅ NAS Success: {$success}");
        if ($errors > 0) {
            $this->error("❌ NAS Errors: {$errors}");
        }

        return $errors;
    }

    /**
     * Migrasi customer ke tabel radcheck, radusergroup, radgroupreply
     */
    protected function migrateCustomers(RadiusService $radius, bool $dryRun): int
    {
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('👤 MIGRASI CUSTOMER');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        $customerId = $this->option('customer');

        $query = InternetCustomer::with(['internetPackage', 'router'])
            ->whereIn('status', ['installed', 'active', 'reactivated'])
            ->whereNotNull('username')
            ->whereNotNull('router_id');

        if ($customerId) {
            $query->where('id', $customerId);
        }

        $customers = $query->get();

        $this->info("Ditemukan {$customers->count()} customer");
        $this->newLine();

        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        $success = 0;
        $skipped = 0;
        $errors  = 0;

        foreach ($customers as $cust) {
            try {
                $pkg = $cust->internetPackage;

                if (!$pkg) {
                    $this->newLine();
                    $this->warn("⏭️  Skip {$cust->username} — paket tidak ditemukan");
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $map = PackageRouterProfile::where('router_id', $cust->router_id)
                      ->where('package_id', $pkg->id)->first();
                $groupName = $map->ros_profile ?? ('PKG_' . $pkg->id);

                if ($dryRun) {
                    $this->newLine();
                    $this->line("  [{$cust->username}] → group: {$groupName}, pass: ***");
                } else {
                    $radius->ensureGroup($pkg, $groupName);
                    $radius->upsertUser($cust, $groupName);
                }

                $success++;
            } catch (\Throwable $th) {
                $this->newLine();
                $this->error("❌ Error {$cust->username}: {$th->getMessage()}");
                Log::error('[radius:migrate] Error', [
                    'customer' => $cust->username,
                    'error'    => $th->getMessage(),
                ]);
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Customer Success: {$success}");
        $this->info("⏭️  Customer Skipped: {$skipped}");
        if ($errors > 0) {
            $this->error("❌ Customer Errors: {$errors}");
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('Ini hanya dry run. Jalankan tanpa --dry-run untuk apply.');
        }

        return $errors;
    }
}
