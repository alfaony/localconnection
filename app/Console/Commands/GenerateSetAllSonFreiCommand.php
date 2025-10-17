<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Schemas\ParamSchema;

class GenerateSetAllSonFreiCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set:all-son-frei';

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
        $setDay = 
        [
            "monday" => true,
            "tuesday" => true,
            "wednesday" => true,
            "thursday" => true,
            "friday" => true,
            "saturday" => false,
            "sunday" => false,
        ];

        $users = User::whereNull('wfo_working_days')->where('wfo_check_in',true)->get();
        foreach ($users as $user) 
        {
            $user->wfo_working_days = $setDay;
            $user->save();

            $this->info("Updated wfo_working_days for user: {$user->name} (ID: {$user->id})");
        }


    }
}
