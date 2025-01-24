<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateServiceType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:service-type';

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
        $data = ['Reguler', 'One Day','Ekonomi', 'Kargo'];
        foreach ($data as $key => $value) 
        {
            \App\Models\ServiceType::updateOrCreate([
                'name' => $value
            ]);
        }
        return Command::SUCCESS;
    }
}
