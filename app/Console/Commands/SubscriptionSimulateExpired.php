<?php

namespace App\Console\Commands;

use App\Models\CustomerSubscription;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SubscriptionSimulateExpired extends Command
{
    /**
     * Examples:
     *   php artisan subscription:simulate-expired                    (preview all active)
     *   php artisan subscription:simulate-expired --id=<uuid> --days=0
     *   php artisan subscription:simulate-expired --id=<uuid> --days=3
     *   php artisan subscription:simulate-expired --all --days=7     (seluruh active)
     *   php artisan subscription:simulate-expired --id=<uuid> --mark-expired
     */
    protected $signature = 'subscription:simulate-expired
                            {--id=          : UUID of a specific subscription}
                            {--all          : Apply to ALL active subscriptions (USE WITH CAUTION)}
                            {--days=7       : Set tanggal_expired N days from now (0 = today)}
                            {--mark-expired : Directly set status=expired (skip date simulation)}
                            {--dry-run      : Preview without saving}';

    protected $description = 'Simulate subscription expiry for testing notifications';

    public function handle()
    {
        $id          = $this->option('id');
        $all         = $this->option('all');
        $days        = (int) $this->option('days');
        $markExpired = $this->option('mark-expired');
        $dryRun      = $this->option('dry-run');

        // -- Fetch candidates
        if ($id) {
            $subscriptions = CustomerSubscription::where('id', $id)
                ->with(['user', 'software', 'package'])
                ->get();
        } elseif ($all) {
            $subscriptions = CustomerSubscription::active()
                ->with(['user', 'software', 'package'])
                ->get();
        } else {
            // No filter → interactive list
            $this->showActiveSubscriptions();
            return self::SUCCESS;
        }

        if ($subscriptions->isEmpty()) {
            $this->warn('Tidak ada subscription ditemukan dengan kriteria tersebut.');
            return self::SUCCESS;
        }

        // -- Confirm destructive ops
        if (!$dryRun && ($all || $markExpired)) {
            if (!$this->confirm("Anda akan mengubah {$subscriptions->count()} subscription. Lanjutkan?")) {
                $this->info('Dibatalkan.');
                return self::SUCCESS;
            }
        }

        foreach ($subscriptions as $sub) {
            $userName    = $sub->user->name ?? 'Unknown';
            $softName    = $sub->software->nama ?? 'N/A';
            $packageName = $sub->package->nama_paket ?? 'N/A';

            if ($markExpired) {
                // Langsung set status expired
                $action = "Status → expired";
                if (!$dryRun) {
                    $sub->update([
                        'tanggal_expired'  => Carbon::today('Asia/Jakarta'),
                    ]);
                    // Use service to properly expire (release slots & expire payments)
                    app(\App\Services\SubscriptionService::class)->expireSubscription($sub);
                }
            } else {
                $newDate = Carbon::today('Asia/Jakarta')->addDays($days);
                $action  = "tanggal_expired → {$newDate->format('d M Y')} (H-{$days})";
                if (!$dryRun) {
                    $sub->update([
                        'tanggal_expired' => $newDate,
                    ]);
                }
            }

            $prefix = $dryRun ? '[DRY-RUN] ' : '';
            $this->line("{$prefix}✅ [{$sub->order_number}] {$userName} | {$softName} - {$packageName} → {$action}");
        }

        if ($dryRun) {
            $this->warn('DRY-RUN: Tidak ada perubahan disimpan. Hapus --dry-run untuk menerapkan.');
        } else {
            $this->info("Selesai! {$subscriptions->count()} subscription diperbarui.");
            $this->line('');
            $this->line('Jalankan notifikasi uji coba:');
            $this->line('  php artisan subscription:notify-expiry --days=' . ($markExpired ? 0 : $days));
        }

        return self::SUCCESS;
    }

    protected function showActiveSubscriptions(): void
    {
        $subs = CustomerSubscription::active()
            ->with(['user', 'software', 'package'])
            ->orderBy('tanggal_expired','desc')
            ->limit(20)
            ->get();

        if ($subs->isEmpty()) {
            $this->warn('Tidak ada subscription aktif.');
            return;
        }

        $this->table(
            ['ID (singkat)', 'Customer', 'Software', 'Paket', 'Expired', 'Sisa'],
            $subs->map(fn($s) => [
                $s->id,
                $s->user->name ?? '-',
                $s->software->nama ?? '-',
                $s->package->nama_paket ?? '-',
                $s->tanggal_expired ? Carbon::parse($s->tanggal_expired)->format('d M Y') : '-',
                $s->days_until_expiry !== null ? $s->days_until_expiry . ' hari' : '-',
            ])
        );

        $this->line('');
        $this->line('Gunakan salah satu opsi:');
        $this->line('  --id=<uuid> --days=7   → Set expired 7 hari dari sekarang');
        $this->line('  --id=<uuid> --days=3   → Set expired 3 hari dari sekarang');
        $this->line('  --id=<uuid> --days=0   → Set expired HARI INI');
        $this->line('  --id=<uuid> --mark-expired → Langsung set status=expired');
        $this->line('  --all --days=7         → Apply ke semua subscription aktif');
        $this->line('  --dry-run              → Preview tanpa menyimpan');
    }
}
