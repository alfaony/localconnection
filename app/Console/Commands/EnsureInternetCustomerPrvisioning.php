<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InternetCustomer;
use App\Jobs\ProvisionCustomerJob;

class EnsureInternetCustomerPrvisioning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intenret:ensure-prvisioning {--status= : Specific customer status to check}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $status = $this->option('status');
        $customers = InternetCustomer::where('status', $status)->get();

        if($customers->isEmpty()) {
            $this->info('No customers found with status ' . $status);
            return Command::SUCCESS;
        }

        foreach ($customers as $customer) {
            ProvisionCustomerJob::dispatch($customer->id);
        }

        $this->info('Dispatched ' . $customers->count() . ' jobs for status ' . $status);
        return Command::SUCCESS;
    }
}
