<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InternetCustomer;

class AssignCustomerCodes extends Command
{
    protected $signature = 'customers:assign-codes {limit?}';
    protected $description = 'Assign sequential code_cust and code based on province with optional limit';

    public function handle()
    {
        $limit = $this->argument('limit');
        $this->info("Running code assignment" . ($limit ? " with limit = {$limit}" : " for ALL customers"));

        $counter = 0;

        // Use chunkById to avoid ORDER BY & OFFSET scanning
        $query = InternetCustomer::with('province');

        if ($limit) {
            $query->limit($limit);
        }

        $query->chunkById(200, function ($customers) use (&$counter) {

            foreach ($customers as $cust) {

                $counter++;

                // Province prefix
                $prefix = $cust->province?->initial ?? 'XXX';

                // Assign sequential codes
                if(!$cust->code) 
                {
                    $cust->code_cust = $counter;
                }else
                {
                    $counter = $cust->code_cust;
                }

                $cust->code = $prefix . $counter;

                $cust->save();
            }
        });

        $this->info("DONE. Total processed: {$counter}");
    }
}