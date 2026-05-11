<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmployeeChecking;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Events\EmployeeCheckinDeactivated;

class DeactivateCheckin extends Command
{
    protected $signature = 'checkin:deactivateAndRemove';
    protected $description = 'Deactivate check-ins and broadcast deactivation via WebSocket after the check-in timeout period';

    public function handle()
    {
        // Fetch all active check-ins that are scheduled for today and are not completed
        $employeeCheckings = EmployeeChecking::where('is_active', true)
            ->whereDate('scheduled_time', Carbon::today()) // Filter for today's scheduled check-ins
            ->whereTime('scheduled_timeout', Carbon::now()->tz('Asia/Jakarta')->format('H:i'))
            ->get();

        foreach ($employeeCheckings as $checkin) 
        {
            // Get the current time and schedule timeout
            $timeoutTime = $checkin->scheduled_timeout ? Carbon::parse($checkin->scheduled_timeout)->format('H:i') : NULL;
            $currentTime = Carbon::now()->tz('Asia/Jakarta')->format('H:i');

            // If the current time is greater than the timeout time, deactivate the check-in
            if (isset($checkin->scheduled_timeout) && isset($timeoutTime) && $currentTime == $timeoutTime) {
                $checkin->is_active = false;
                $checkin->save();

                try {
                    broadcast(new EmployeeCheckinDeactivated($checkin->user_id, $checkin->id));
                } catch (\Throwable $e) {
                    Log::error('DeactivateCheckin broadcast gagal: ' . $e->getMessage());
                }

                $this->info("Check-in deactivated via WebSocket untuk user: {$checkin->user_id}");
            }
        }

        $this->info('Check-in deactivations processed successfully.');
    }
}
