<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerSubscription;

class SubscriptionSimulatePaymentExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:simulate-payment-expired {--id= : The UUID of the subscription to forcefully expire its pending payment}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate the expiration of a pending payment and automatically release its slots without waiting 24 hours (For Testing)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $id = $this->option('id');

        if (!$id) {
            $this->error('Please provide a subscription ID using --id=...');
            
            // List pending subscriptions
            $subs = CustomerSubscription::whereHas('payments', function ($q) {
                $q->whereIn('status', ['pending', 'unpaid']);
            })->with('user', 'software')->get();

            if ($subs->isEmpty()) {
                $this->info("Currently there are NO pending payments to simulate.");
                return self::SUCCESS;
            }

            $this->info("Available Pending Payments:");
            $this->table(
                ['ID', 'User', 'Software', 'Order Number'],
                $subs->map(function ($s) {
                    return [
                        $s->id,
                        $s->user->name ?? '-',
                        $s->software->nama ?? '-',
                        $s->order_number,
                    ];
                })
            );
            return self::FAILURE;
        }

        $this->info("Simulating expiration for subscription: {$id}");
        
        $this->call('subscription:auto-release-slots', [
            '--id' => $id,
            '--force' => true
        ]);

        return self::SUCCESS;
    }
}
