<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InternetCustomer;
use App\Jobs\ProvisionCustomerJob;

class ProvisionAllActive extends Command
{
    protected $signature = 'provision:all-active {--router_id=} {--chunk=200}';
    protected $description = 'Dispatch ProvisionCustomerJob untuk semua pelanggan ACTIVE (opsional filter per router).';

    public function handle(): int
    {
        $routerId = $this->option('router_id');
        $chunk    = (int)$this->option('chunk');

        $q = InternetCustomer::where('status','active')
            ->where('access_type','pppoe')
            ->whereNotNull('username');

        if ($routerId) $q->where('router_id', (int)$routerId);

        $count = 0;
        $q->orderBy('created_at')->chunk($chunk, function ($rows) use (&$count) {
            foreach ($rows as $cust) {
                dispatch(new ProvisionCustomerJob($cust->id));
                $count++;
            }
        });

        $this->info("Dispatched {$count} jobs.");
        return self::SUCCESS;
    }
}
