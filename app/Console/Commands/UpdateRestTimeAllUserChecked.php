<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateRestTimeAllUserChecked extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-rest-time';

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
        // Get all users
        $users = User::where('is_checkin',true)->get();

        DB::beginTransaction();
        try {
            foreach ($users as $user) 
            {
                // Update each user’s custom_rest_times to include the default values
                $user->rest_time = '11:45';
                $user->end_rest_time = '13:15';
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

