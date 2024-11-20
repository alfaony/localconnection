<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateAllTemplateBast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:template1-to-all-bast';

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
        $bast = \App\Models\Bast::all();
        foreach ($bast as $key => $value) {
            $value->template = 'template1';
            $value->save();
        }
        
        $this->info('Generate template 1 to all bast berhasil');
        return Command::SUCCESS;
    }
}
