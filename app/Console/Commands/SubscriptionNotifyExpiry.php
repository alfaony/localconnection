<?php

namespace App\Console\Commands;

use App\Jobs\SentInbox;
use App\Models\CustomerSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Schemas\RoleSchema;

class SubscriptionNotifyExpiry extends Command
{
    /**
     * php artisan subscription:notify-expiry
     * php artisan subscription:notify-expiry --days=7
     * (cron: run daily at 08:00)
     */
    protected $signature = 'subscription:notify-expiry
                            {--days=all : Specific day threshold (7, 3, 0) or "all"}';

    protected $description = 'Send inbox notifications to customers whose subscription is expiring in 7, 3 days, or on the expiry date';

    /** Thresholds we care about (in days) */
    protected array $thresholds = [7, 3, 0];

    public function handle()
    {
        $daysOpt = $this->option('days');

        // Decide which thresholds to run
        if ($daysOpt === 'all') {
            $targets = $this->thresholds;
        } elseif (in_array((int) $daysOpt, $this->thresholds, true)) {
            $targets = [(int) $daysOpt];
        } else {
            $this->error("--days must be one of: 7, 3, 0, or 'all'");
            return self::FAILURE;
        }

        foreach ($targets as $days) {
            $this->sendNotificationsFor($days);
        }

        $this->info('Subscription expiry notifications dispatched.');
        return self::SUCCESS;
    }

    protected function sendNotificationsFor(int $daysUntilExpiry): void
    {
        // Query subscriptions that expire exactly on this threshold day (± today/date window)
        if ($daysUntilExpiry === 0) {
            // Hari H = expired today
            $subscriptions = CustomerSubscription::active()
                ->whereDate('tanggal_expired', Carbon::today()->timezone('Asia/Jakarta'))
                ->with(['user', 'software', 'package'])
                ->get();
        } else {
            $targetDate = Carbon::today('Asia/Jakarta')->addDays($daysUntilExpiry);
            $subscriptions = CustomerSubscription::active()
                ->whereDate('tanggal_expired', $targetDate)
                ->with(['user', 'software', 'package'])
                ->get();
        }

        $this->line("→ {$daysUntilExpiry} hari: {$subscriptions->count()} subscription ditemukan.");

        foreach ($subscriptions as $subscription) {
            $user = $subscription->user;
            $system = User::whereHas('role', function ($query) {
                $query->whereIn('name', [RoleSchema::SYSTEM_BOS, RoleSchema::ROOT]);
            })->first();
            if (!$user) continue;

            $softwareName = $subscription->software->nama ?? 'Software';
            $packageName  = $subscription->package->nama_paket ?? 'Paket';
            $expiredDate  = $subscription->tanggal_expired
                ? Carbon::parse($subscription->tanggal_expired)->translatedFormat('d F Y')
                : '-';

            [$subject, $emoji] = $this->buildMessage($daysUntilExpiry);

            $message = "{$emoji} *Reminder Berlangganan* – {$softwareName} ({$packageName})\n"
                . "{$subject}. Masa aktif berakhir pada *{$expiredDate}*.\n"
                . "Silakan perpanjang langganan Anda agar layanan tidak terputus.";

            $url = route('customer-subscription.show', $subscription->id);

            // Dispatch inbox notification to the subscriber
            SentInbox::dispatch($system->id, $user->id, $message, $url);

            // Also notify PIC / admin of the software
            $pic = $subscription->software->pic ?? null;
            if ($pic && $pic->id !== $user->id) {
                $adminMsg = "📋 *Reminder untuk Admin* – Pelanggan *{$user->name}* memiliki {$subject} untuk {$softwareName} ({$packageName}). Expired: {$expiredDate}.";
                $adminUrl = route('subscription.show', $subscription->id);
                SentInbox::dispatch($pic->id, $pic->id, $adminMsg, $adminUrl);
            }

            Log::info('Subscription expiry notification sent', [
                'subscription_id' => $subscription->id,
                'user_id'         => $user->id,
                'days_until'      => $daysUntilExpiry,
                'expired_date'    => $expiredDate,
            ]);
        }
    }

    protected function buildMessage(int $days): array
    {
        return match ($days) {
            7  => ['langganan Anda akan berakhir dalam 7 hari', '🔔'],
            3  => ['langganan Anda akan berakhir dalam 3 hari', '⚠️'],
            0  => ['langganan Anda BERAKHIR HARI INI', '🚨'],
            default => ['langganan Anda akan segera berakhir', '📣'],
        };
    }
}
