<?php

namespace App\Console\Commands;

use App\Models\CustomerSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SubscriptionExpireOverdue extends Command
{
    /**
     * php artisan subscription:expire-overdue
     * Runs daily – marks subscriptions as expired if tanggal_expired has passed.
     */
    protected $signature = 'subscription:expire-overdue
                            {--dry-run : Preview without saving}';

    protected $description = 'Mark active subscriptions as expired when their tanggal_expired has passed';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $overdue = CustomerSubscription::active()
            ->whereNotNull('tanggal_expired')
            ->whereDate('tanggal_expired', '<', Carbon::today('Asia/Jakarta'))
            ->with(['user', 'software'])
            ->get();

        if ($overdue->isEmpty()) {
            $this->info('Tidak ada subscription yang perlu di-expire.');
            return self::SUCCESS;
        }

        $this->line("Ditemukan {$overdue->count()} subscription kadaluarsa.");

        foreach ($overdue as $sub) {
            $userName = $sub->user->name ?? 'Unknown';
            $soft     = $sub->software->nama ?? 'N/A';
            $expStr   = $sub->tanggal_expired ? Carbon::parse($sub->tanggal_expired)->format('d M Y') : '-';

            if (!$dryRun) {
                $sub->update(['status' => 'expired']);
                Log::info("subscription:expire-overdue → Expired [{$sub->order_number}] {$userName} | {$soft}, was due {$expStr}");
            }

            $prefix = $dryRun ? '[DRY-RUN] ' : '';
            $this->line("{$prefix}🔴 [{$sub->order_number}] {$userName} | {$soft} → expired (due: {$expStr})");
        }

        if ($dryRun) {
            $this->warn('DRY-RUN aktif – tidak ada data yang diubah.');
        } else {
            $this->info("Selesai! {$overdue->count()} subscription di-expire.");
        }

        return self::SUCCESS;
    }
}
