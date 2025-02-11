<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FineTuneTable;

class GenerateFineTuneTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:fine-tune-table';

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
        $fineTuneTables = [
            'quotes',
            'work_orders'
        ];
    
        foreach ($fineTuneTables as $data) 
        {
            FineTuneTable::firstOrCreate(
                ['name' => $data],  // Kolom yang digunakan untuk pengecekan duplikat
                ['name' => $data]  // Data yang akan ditambahkan jika belum ada
            );
        }

        
        $this->info('Fine-tune table generated successfully!');
        return Command::SUCCESS;
    }
}
