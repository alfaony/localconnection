<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Illuminate\Console\Command;

class SubscriptionAutoReleaseSlots extends Command
{
    /**
     * php artisan subscription:auto-release-slots
     * Runs hourly – expires unpaid subscriptions whose slot reservation deadline has passed.
     */
    protected $signature = 'subscription:auto-release-slots
                            {--dry-run : Preview without saving}';

    protected $description = 'Auto-release slots for unpaid subscriptions past their reservation deadline';

    public function handle(SubscriptionService $service)
    {
        $dryRun = $this->option('dry-run');

        $results = $service->autoExpireUnpaidSubscriptions($dryRun);

        if (empty($results)) {
            $this->info('✅ Tidak ada subscription unpaid yang perlu di-expire.');
            return self::SUCCESS;
        }

        $this->line("Ditemukan " . count($results) . " subscription unpaid melewati deadline:");

        foreach ($results as $r) {
            $prefix = $dryRun ? '[DRY-RUN] ' : '';
            $this->line("{$prefix}🔴 [{$r['order_number']}] {$r['user']} | {$r['software']} → slot expired (deadline: {$r['reserved_until']})");
        }

        if ($dryRun) {
            $this->warn('DRY-RUN aktif – tidak ada data yang diubah.');
        } else {
            $this->info("Selesai! " . count($results) . " subscription di-expire dan slot dibebaskan.");
        }

        return self::SUCCESS;
    }
}
