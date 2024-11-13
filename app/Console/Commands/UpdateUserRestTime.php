<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateUserRestTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:set-rest-time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set custom rest times for all users, with a default rest time on Friday';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Define the rest times with Friday default and null for other days
        $defaultRestTimes = [
            'monday' => ['start' => null, 'end' => null],
            'tuesday' => ['start' => null, 'end' => null],
            'wednesday' => ['start' => null, 'end' => null],
            'thursday' => ['start' => null, 'end' => null],
            'friday' => ['start' => '11:30', 'end' => '13:30'],
            'saturday' => ['start' => null, 'end' => null],
            'sunday' => ['start' => null, 'end' => null],
        ];

        // Get all users
        $users = User::where('is_checkin',true)->whereNull('custom_rest_times')->get();

        DB::beginTransaction();
        try {
            foreach ($users as $user) 
            {
                // Update each user’s custom_rest_times to include the default values
                $user->custom_rest_times = $defaultRestTimes;
                $user->save();
    
                $this->info("Updated custom rest times for user: {$user->name} (ID: {$user->id})");
            }
    
            $this->info('Custom rest times have been set for all users with Friday as the default.');
            DB::commit();
            return Command::SUCCESS;
        } catch (\Throwable $th) {
            //throw $th;
            // dd($th);
            DB::rollBack();
            $this->error('An error occurred while updating custom rest times.');
        }
    }
}
