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
    protected $signature = 'billing-or-isolir:generate';

    protected $description = 'Generate billing untuk pelanggan yang mulai tagihan hari ini';

    public function handle()
    {
        $today = Carbon::today();

        $customers = UserCustomer::whereDate('start_billing_date', $today)->orWhereDate('end_billing_date', $today)->get();

        $delayStep = 0; // dalam detik
        foreach ($customers as $customer) 
        {
            $delayStep += 3; // tambah 3 detik per customer

            // Kirim ke job untuk diproses per pelanggan
            if (Carbon::parse($customer->start_billing_date) == $today && in_array($customer->internetCustomer->status, [
                ParamSchema::ACTIVE,
                ParamSchema::INSTALLED,
            ]))
            {
                // GenerateBillingJob::dispatch($customer);
                  GenerateBillingJob::dispatch($customer)->delay(now()->addSeconds($delayStep));
            }

            if (Carbon::parse($customer->end_billing_date) == $today && in_array($customer->internetCustomer->status, [
                // ParamSchema::ACTIVE,
                // ParamSchema::INSTALLED,
                ParamSchema::WAITING_PAYMENT_CONFIRMATION
            ]))
            {
                GenerateIsolirJob::dispatch($customer);
            }
        }


        $this->info("Penagihan Dan Isolir " . $customers->count() . " pelanggan telah dijadwalkan.");
    }
}
