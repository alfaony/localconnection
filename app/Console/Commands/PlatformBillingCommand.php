<?php

namespace App\Console\Commands;

use App\Services\PlatformBilling\PlatformInvoiceService;
use Illuminate\Console\Command;

class PlatformBillingCommand extends Command
{
    protected $signature = 'platform-billing:generate {--type=snapshot : snapshot|invoice|suspend}';

    protected $description = 'Jalankan proses billing platform (Keloola BOS -> Company): snapshot harian customer aktif, generate invoice bulanan, atau suspend company yang menunggak';

    public function handle(PlatformInvoiceService $service)
    {
        $type = $this->option('type');

        switch ($type) {
            case 'snapshot':
                $service->takeDailySnapshot();
                $this->info('Snapshot customer aktif harian selesai.');
                break;

            case 'invoice':
                $invoices = $service->generateInvoicesForPeriod();
                $this->info(count($invoices) . ' invoice platform berhasil dibuat.');
                break;

            case 'suspend':
                $service->markOverdueAndSuspend();
                $this->info('Pengecekan invoice overdue & suspend company selesai.');
                break;

            default:
                $this->error("Tipe tidak dikenal: {$type}. Gunakan snapshot|invoice|suspend.");
                return 1;
        }

        return 0;
    }
}
