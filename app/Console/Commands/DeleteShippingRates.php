<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteShippingRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipping_rates:delete {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all records in the shipping_rates table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // DB::table('postal_codes')->truncate();
        //     $this->info('Deleted all records from postal_codes.');

        // Confirm deletion unless --force option is used
        if (!$this->option('force') && !$this->confirm('Are you sure you want to delete all records from the shipping_rates table?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Perform the deletion
        try {
            DB::table('shipping_rates')->truncate(); // Truncate table to reset auto-increment ID
            $this->info('All records in the shipping_rates table have been successfully deleted.');
        } catch (\Exception $e) {
            $this->error('An error occurred while deleting records: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}