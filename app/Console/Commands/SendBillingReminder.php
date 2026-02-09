<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserCustomer;
use App\Jobs\SendBillingReminderJob;
use Carbon\Carbon;
use App\Schemas\ParamSchema;

class SendBillingReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:send-reminder {--days=1 : Number of days before due date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim reminder WhatsApp kepada pelanggan N hari sebelum jatuh tempo pembayaran';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $daysBeforeDue = (int) $this->option('days');
        $reminderDate = Carbon::today()->addDays($daysBeforeDue);

        $this->info("Checking customers with end_billing_date = {$reminderDate->toDateString()} (H-{$daysBeforeDue})...");
        $this->newLine();

        // Query customers yang akan jatuh tempo N hari dari sekarang
        // DAN belum bayar (status WAITING_PAYMENT)
        $customers = UserCustomer::whereDate('end_billing_date', $reminderDate)
            ->whereHas('internetCustomer', function ($query) {
                $query->whereIn('status', [
                    ParamSchema::WAITING_PAYMENT_SUBSCRIPTION,
                ])
                // ->where('is_paid', false)
                ;
            })
            ->with(['internetCustomer.internetPackage', 'internetCustomer.purchases'])
            ->get();
        
        if ($customers->isEmpty()) {
            $this->warn("Tidak ada customer yang perlu diingatkan untuk tanggal {$reminderDate->toDateString()}");
            return 0;
        }

        $this->info("Found {$customers->count()} customer(s) to remind");
        $this->newLine();

        $successCount = 0;
        $delayStep = 0;

        $progressBar = $this->output->createProgressBar($customers->count());
        $progressBar->start();

        foreach ($customers as $customer) {
            try {
                $delayStep += 2; // 2 detik delay per customer untuk prevent rate limit

                // Dispatch job dengan delay
                SendBillingReminderJob::dispatch($customer, $daysBeforeDue)->delay(now()->addSeconds($delayStep));

                $successCount++;
                $progressBar->advance();

            } catch (\Throwable $th) {
                $this->error("\nFailed to dispatch reminder for customer {$customer->internetCustomer->code}: {$th->getMessage()}");
            }
        }

        $progressBar->finish();
        // $this->newLine(2);

        // Summary
        // $this->info("=== SUMMARY ===");
        // $this->info("Reminder Date: {$reminderDate->toDateString()} (H-{$daysBeforeDue})");
        // $this->info("Total Customers: {$customers->count()}");
        // $this->info("Jobs Dispatched: {$successCount}");
        // $this->info("Failed: " . ($customers->count() - $successCount));
        // $this->newLine();

        // // Show customer details if < 20 customers
        // if ($customers->count() <= 20) {
        //     $this->info("=== CUSTOMER LIST ===");
        //     $tableData = [];
        //     foreach ($customers as $customer) {
        //         $tableData[] = [
        //             $customer->internetCustomer->code,
        //             $customer->name,
        //             $customer->phone_number,
        //             $customer->end_billing_date,
        //             'Rp ' . number_format($customer->internetCustomer->internetPackage->price_nett, 0, ',', '.'),
        //         ];
        //     }
        //     $this->table(
        //         ['Kode', 'Nama', 'Telepon', 'Jatuh Tempo', 'Tagihan'],
        //         $tableData
        //     );
        // }

        $this->info("✓ Billing reminder jobs dispatched successfully");

        return 0;
    }
}
