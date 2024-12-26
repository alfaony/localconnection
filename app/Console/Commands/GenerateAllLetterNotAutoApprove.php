<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LetterType;

class GenerateAllLetterNotAutoApprove extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:all-letter-not-auto-approve';

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
        \App\Models\LetterType::where('auto_approve', true)->each(function($letterType) {
            $letterType->auto_approve = false;
            $letterType->save();
        });

        $this->info('done');  
    }
}