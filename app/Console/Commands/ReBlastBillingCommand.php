<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserCustomer;
use App\Jobs\ReBlastBillingReminderJob;
use Carbon\Carbon;
use App\Schemas\ParamSchema;

class ReBlastBillingCommand extends Command
{
    protected $signature = 'billing:re-blast
                            {--company_id= : UUID company yang akan di-re-blast (required)}
                            {--days=0 : Offset hari dari start_billing_date (0=H, 1=H+1, dst)}';

    protected $description = 'Re-blast WA billing berdasarkan start_billing_date, kecuali pelanggan AKTIF. Template WA dipilih berdasarkan end_billing_date tiap customer.';

    public function handle()
    {
        $companyId = $this->option('company_id');
        $days      = (int) $this->option('days');

        if (empty($companyId)) {
            $this->error('--company_id wajib diisi.');
            return 1;
        }

        // Filter start_billing_date = hari ini - $days
        // days=0 → start_billing_date = today (H)
        // days=1 → start_billing_date = H-1 (berarti hari ini adalah H+1 dari start)
        $targetDate = Carbon::today()->subDays($days);

        $this->info("Re-blast WA Billing");
        $this->info("Company ID         : {$companyId}");
        $this->info("start_billing_date : {$targetDate->toDateString()} (H+{$days})");
        $this->info("Template WA        : dihitung otomatis dari end_billing_date tiap customer");
        $this->newLine();

        $customers = UserCustomer::where('company_id', $companyId)
            ->whereDate('start_billing_date', $targetDate)
            ->whereHas('internetCustomer', function ($query) {
                // Kecuali yang AKTIF
                $query->where('status', '!=', ParamSchema::ACTIVE);
            })
            ->with(['internetCustomer.internetPackage'])
            ->get();

        if ($customers->isEmpty()) {
            $this->warn("Tidak ada customer ditemukan untuk start_billing_date={$targetDate->toDateString()} di company {$companyId}");
            return 0;
        }

        $this->info("Ditemukan {$customers->count()} customer(s)");
        $this->newLine();

        $progressBar  = $this->output->createProgressBar($customers->count());
        $progressBar->start();

        $successCount = 0;
        $delayStep    = 0;

        foreach ($customers as $customer) {
            try {
                $delayStep += 2;

                ReBlastBillingReminderJob::dispatch($customer)
                    ->delay(now()->addSeconds($delayStep));

                $successCount++;
                $progressBar->advance();

            } catch (\Throwable $th) {
                $code = $customer->internetCustomer->code ?? $customer->id;
                $this->error("\nGagal dispatch customer {$code}: {$th->getMessage()}");
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("=== SUMMARY ===");
        $this->info("Company ID       : {$companyId}");
        $this->info("Target Tanggal   : {$targetDate->toDateString()} (H+{$days} dari start_billing_date)");
        $this->info("Total Customer   : {$customers->count()}");
        $this->info("Jobs Dispatched  : {$successCount}");
        $this->info("Gagal            : " . ($customers->count() - $successCount));

        return 0;
    }
}
