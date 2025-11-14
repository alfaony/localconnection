<?php

namespace App\Console\Commands;

use App\Jobs\CheckActiveCustomersJob;
use Illuminate\Console\Command;

class CheckActiveCustomersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'customers:check-active 
                            {--customer= : Specific customer ID to check}
                            {--force : Force check immediately without queue}';

    /**
     * The console command description.
     */
    protected $description = 'Check active customers status on router and update last_updated_router';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $customerId = $this->option('customer');
        $force = $this->option('force');

        if ($customerId) {
            $this->info("Checking specific customer: {$customerId}");
        } else {
            $this->info("Checking all active customers");
        }

        if ($force) {
            $this->info("Running immediately (not queued)...");
            $job = new CheckActiveCustomersJob($customerId);
            $job->handle(app(\App\Services\RouterOSService::class));
            $this->info("Check completed!");
        } else {
            dispatch(new CheckActiveCustomersJob($customerId));
            $this->info("Job dispatched to queue!");
        }

        return 0;
    }
}