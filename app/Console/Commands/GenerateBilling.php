<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserCustomer;
use App\Jobs\GenerateBillingJob;
use App\Jobs\GenerateIsolirJob;
use App\Jobs\SendBillingReminderJob;
use Carbon\Carbon;
use App\Schemas\ParamSchema;

class GenerateBilling extends Command
{
    protected $signature = 'billing-or-isolir:generate {--type=all : Type of action: billing, isolir, remainder, or all}';

    protected $description = 'Generate billing atau isolir untuk pelanggan. Type: billing (generate invoice), isolir (suspend service), remainder (kirim WA reminder < 3 hari jatuh tempo), all (both)';

    public function handle()
    {
        $type = $this->option('type');
        $today = Carbon::today();

        // Validate type
        if (!in_array($type, ['billing', 'isolir', 'remainder', 'all'])) {
            $this->error("Invalid type. Must be 'billing', 'isolir', 'remainder', or 'all'");
            return 1;
        }

        $billingCount   = 0;
        $isolirCount    = 0;
        $reminderCount  = 0;

        // Process BILLING (start_billing_date)
        if (in_array($type, ['billing', 'all'])) {
            $this->info("Processing BILLING for customers with start_billing_date <= {$today->toDateString()}...");

            $billingCustomers = UserCustomer::whereDate('start_billing_date', '<=', $today)
                ->whereHas('internetCustomer', function ($query) {
                    $query->whereIn('status', [
                        ParamSchema::ACTIVE,
                        ParamSchema::INSTALLED,
                        ParamSchema::REACTIVATED,
                        ParamSchema::DISCONNECTED,
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
                    $query->whereIn('status', [
                        ParamSchema::ACTIVE,
                        ParamSchema::WAITING_PAYMENT_SUBSCRIPTION,
                        ParamSchema::REACTIVATED,
                        ParamSchema::DISCONNECTED,
                    ]);
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

        // Process REMAINDER (end_billing_date antara hari ini s/d 2 hari ke depan)
        if (in_array($type, ['remainder'])) {
            $endWindow = $today->copy()->addDays(3);

            $this->info("Processing REMAINDER for WaitingPaymentSubs with end_billing_date between {$today->toDateString()} and {$endWindow->toDateString()}...");

            $reminderCustomers = UserCustomer::whereBetween('end_billing_date', [
                    $today->toDateString(),
                    $endWindow->toDateString(),
                ])
                ->whereHas('internetCustomer', function ($query) {
                    $query->whereIn('status', [
                        ParamSchema::ACTIVE,
                        ParamSchema::WAITING_PAYMENT_SUBSCRIPTION,
                        ParamSchema::REACTIVATED,
                        ParamSchema::DISCONNECTED,
                    ]);
                })
                ->with(['internetCustomer.internetPackage', 'internetCustomer.purchases'])
                ->get();

            $delayStep = 0;
            foreach ($reminderCustomers as $customer) {
                $dueDate       = Carbon::parse($customer->end_billing_date)->startOfDay();
                $daysBeforeDue = (int) $today->diffInDays($dueDate, false);
                $daysBeforeDue = max(0, $daysBeforeDue);

                $delayStep += 2;
                SendBillingReminderJob::dispatch($customer, $daysBeforeDue)->delay(now()->addSeconds($delayStep));
                $reminderCount++;
            }

            $this->info("✓ {$reminderCount} reminder job(s) dispatched");
        }

        // Summary
        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Type: {$type}");
        $this->info("Billing Jobs: {$billingCount}");
        $this->info("Isolir Jobs: {$isolirCount}");
        $this->info("Reminder Jobs: {$reminderCount}");
        $this->info("Total: " . ($billingCount + $isolirCount + $reminderCount) . " jobs dispatched");

        return 0;
    }
}
