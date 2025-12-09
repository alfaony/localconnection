<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InternetCustomer;


class UpdateInternetCustomrExisting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:internet-customr-existing';

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
        $count = 0;

        // Chunk to prevent memory leak
        InternetCustomer::where('status', 'process_installation')
            ->chunkById(200, function ($customers) use (&$count) {
                foreach ($customers as $cust) {
                    $cust->update([
                        'status' => 'customer_existing'
                    ]);
                    $count++;
                }
            });

        $this->info("Successfully updated {$count} customers to customer_existing.");

        return Command::SUCCESS;
    }
}
