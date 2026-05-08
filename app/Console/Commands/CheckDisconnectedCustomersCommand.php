<?php

namespace App\Console\Commands;

use App\Jobs\CheckDisconnectedCustomersJob;
use Illuminate\Console\Command;

class CheckDisconnectedCustomersCommand extends Command
{
    protected $signature = 'customers:check-disconnected
                            {--customer= : Specific customer ID to check}
                            {--force : Run immediately without queue}';

    protected $description = 'Check disconnected customers on router — if active, set status to ACTIVE';

    public function handle()
    {
        $customerId = $this->option('customer');
        $force      = $this->option('force');

        if ($customerId) {
            $this->info("Checking disconnected customer: {$customerId}");
        } else {
            $this->info("Checking all disconnected customers...");
        }

        if ($force) {
            $job = new CheckDisconnectedCustomersJob($customerId);
            $job->handle(app(\App\Services\RouterOSService::class));
            $this->info("Check completed!");
        } else {
            dispatch(new CheckDisconnectedCustomersJob($customerId));
            $this->info("Job dispatched to queue!");
        }

        return 0;
    }
}
