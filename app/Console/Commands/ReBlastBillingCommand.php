<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserCustomer;
use App\Jobs\SendBillingReminderJob;
use Carbon\Carbon;
use App\Schemas\ParamSchema;

class ReBlastBillingCommand extends Command
{
    protected $signature = 'billing:re-blast
                            {--company_id= : UUID company yang akan di-re-blast (required)}
                            {--days=0 : Jumlah hari setelah start_billing_date (0=H, 1=H+1, dst)}';

    protected $description = 'Re-blast WA billing reminder berdasarkan start_billing_date, kecuali pelanggan AKTIF';

    public function handle()
    {
        $companyId = $this->option('company_id');
        $days      = (int) $this->option('days');

        if (empty($companyId)) {
            $this->error('--company_id wajib diisi.');
            return 1;
        }

        // Target: start_billing_date = hari ini - $days
        // days=0 → start_billing_date = today (H)
        // days=1 → start_billing_date = today - 1 (H-1), artinya H+1 dari start
        $targetDate = Carbon::today()->subDays($days);

        $this->info("Re-blast WA untuk company_id: {$companyId}");
        $this->info("Target start_billing_date: {$targetDate->toDateString()} (H+{$days})");
        $this->info("Template daysBeforeDue: {$days}");
        $this->newLine();

        $customers = UserCustomer::where('company_id', $companyId)
            ->whereDate('start_billing_date', $targetDate)
            ->whereHas('internetCustomer', function ($query) {
                // Kecuali yang AKTIF
                $query->where('status', '!=', ParamSchema::ACTIVE)
                    ->whereNotNull('status');
            })
            ->with(['internetCustomer.internetPackage', 'internetCustomer.purchases'])
            ->get();

        if ($customers->isEmpty()) {
            $this->warn("Tidak ada customer ditemukan untuk tanggal {$targetDate->toDateString()} di company {$companyId}");
            return 0;
        }

        $this->info("Ditemukan {$customers->count()} customer(s) untuk di-re-blast");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($customers->count());
        $progressBar->start();

        $successCount = 0;
        $delayStep    = 0;

        foreach ($customers as $customer) {
            try {
                $delayStep += 2;

                SendBillingReminderJob::dispatch($customer, $days)
                    ->delay(now()->addSeconds($delayStep));

                $successCount++;
                $progressBar->advance();
            } catch (\Throwable $th) {
                $code = $customer->internetCustomer->code ?? $customer->id;
                $this->error("\nGagal dispatch untuk customer {$code}: {$th->getMessage()}");
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
