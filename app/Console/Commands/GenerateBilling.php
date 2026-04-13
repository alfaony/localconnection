<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserCustomer;
use App\Jobs\GenerateBillingJob;
use App\Jobs\GenerateIsolirJob;
use Carbon\Carbon;
use App\Schemas\ParamSchema;

class GenerateBilling extends Command
{
    protected $signature = 'billing-or-isolir:generate {--type=all : Type of action: billing, isolir, or all}';

    protected $description = 'Generate billing atau isolir untuk pelanggan. Type: billing (generate invoice), isolir (suspend service), all (both)';

    public function handle()
    {
        $type = $this->option('type');
        $today = Carbon::today();

        // Validate type
        if (!in_array($type, ['billing', 'isolir', 'all'])) {
            $this->error("Invalid type. Must be 'billing', 'isolir', or 'all'");
            return 1;
        }

        $billingCount = 0;
        $isolirCount = 0;

        // Process BILLING (start_billing_date)
        if (in_array($type, ['billing', 'all'])) {
            $this->info("Processing BILLING for customers with start_billing_date <= {$today->toDateString()}...");

            $billingCustomers = UserCustomer::whereDate('start_billing_date', '<=', $today)
                ->whereHas('internetCustomer', function ($query) {
                    $query->whereIn('status', [
                        ParamSchema::ACTIVE,
                        ParamSchema::INSTALLED,
                    ]);
                })
                ->get();

            $delayStep = 0; // dalam detik
            foreach ($billingCustomers as $customer)
            {
                $delayStep += 3; // tambah 3 detik per customer untuk menghindari overload
                GenerateBillingJob::dispatch($customer)->delay(now()->addSeconds($delayStep));
                $billingCount++;
            }

            $this->info("✓ {$billingCount} billing job(s) dispatched");
        }

        // Process ISOLIR (end_billing_date)
        if (in_array($type, ['isolir', 'all'])) {
            $this->info("Processing ISOLIR for customers with end_billing_date = {$today->toDateString()}...");

            $isolirCustomers = UserCustomer::whereDate('end_billing_date','<=',$today)
                ->whereHas('internetCustomer', function ($query) {
                    $query->where('status', ParamSchema::WAITING_PAYMENT_SUBSCRIPTION);
                })
                ->get();
            $delayStep = 0;
            foreach ($isolirCustomers as $customer)
            {
                $delayStep += 2; // 2 detik delay per isolir
                GenerateIsolirJob::dispatch($customer)->delay(now()->addSeconds($delayStep));
                $isolirCount++;
            }

            $this->info("✓ {$isolirCount} isolir job(s) dispatched");
        }

        // Summary
        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Type: {$type}");
        $this->info("Billing Jobs: {$billingCount}");
        $this->info("Isolir Jobs: {$isolirCount}");
        $this->info("Total: " . ($billingCount + $isolirCount) . " jobs dispatched");

        return 0;
    }
}
