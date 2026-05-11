<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmployeeChecking;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\CheckinLog;
use App\Events\EmployeeCheckinDeactivated;

class DeactivateCheckinV2 extends Command
{
    protected $signature = 'checkin:deactivate {--id= : The ID of the employee checking}';
    protected $description = 'Deactivate check-ins and broadcast deactivation via WebSocket after the check-in timeout period';

    public function handle()
    {
        // Fetch all active check-ins that are scheduled for today and are not completed
        $id = $this->option('id');
        $checkin = EmployeeChecking::find($id);    

        if($checkin)
        {
            // Get the current time and schedule timeout
            $timeoutTime = $checkin->scheduled_timeout ? Carbon::parse($checkin->scheduled_timeout)->format('H:i') : NULL;
            $currentTime = Carbon::now()->tz('Asia/Jakarta')->format('H:i');
            $timeoutTimeWithSpare2Mnit = $checkin->scheduled_timeout ? Carbon::parse($checkin->scheduled_timeout)->addMinutes(2)->format('H:i') : NULL;

    
            // If the current time is greater than the timeout time, deactivate the check-in
            $existingLog = CheckinLog::where('employee_checkin_id', $checkin->id)->first();
            if ($existingLog) {
                $existingLog->update(
                    [
                        'excecuted_out_at' => Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'),
                ]);
            }

            if (isset($checkin->scheduled_timeout) && isset($timeoutTime) && $currentTime <= $timeoutTimeWithSpare2Mnit) {
                $checkin->is_active = false;
                $checkin->save();

                $this->broadcastDeactivation($checkin);

                $this->info("Check-in deactivated via WebSocket untuk user: {$checkin->user_id}");
            }

            $this->info('Check-in deactivations processed successfully.');
        }
    }

    private function broadcastDeactivation($checkin)
    {
        try {
            broadcast(new EmployeeCheckinDeactivated($checkin->user_id, $checkin->id));
        } catch (\Throwable $e) {
            Log::error('DeactivateCheckinV2 broadcast gagal: ' . $e->getMessage());
            $this->error("Gagal broadcast deactivate untuk user {$checkin->user_id}: {$e->getMessage()}");
        }
    }
}
