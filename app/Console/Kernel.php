<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\SettingCompany;
use App\Models\Company;
use App\Models\EmployeeChecking;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        // Tetapkan zona waktu Asia/Jakarta

        // Jadwalkan pekerjaan 'project:reccuring' setiap hari pada pukul 00:00
        $schedule->command('project:reccuring')->timezone('Asia/Jakarta')->dailyAt('00:00');
        $schedule->command('project:set-status-sent-time')->timezone('Asia/Jakarta')->dailyAt('00:00');
        $schedule->command('tasks:process-recurring')->timezone('Asia/Jakarta')->dailyAt('00:00');

        $company = Company::all();
        foreach ($company as $a) 
        {
            $settingCompany = SettingCompany::byCompany($a->id)->get()->pluck('field_value','field_title');
            $sentTime = $settingCompany['sent_time'] ?? NULL;

            if($sentTime != "")
            {
                $schedule->command('project:send-expiration-notifications')->timezone('Asia/Jakarta')->dailyAt($sentTime);
            }
        }

        $employeeCheckings = EmployeeChecking::where('is_active', true)
            ->whereDate('scheduled_time', Carbon::today()) // Filter today's check-ins
            ->get();

        foreach ($employeeCheckings as $checking) 
        {
            // Calculate the notification time (1 minute before scheduled time)
            $checkinNotificationTime = Carbon::parse($checking->scheduled_time);
            
            // Schedule the notification 1 minute before check-in time
            $schedule->command('checkin:notifyAndSentPopup')
                ->timezone('Asia/Jakarta')
                ->dailyAt($checkinNotificationTime->format('H:i'));

            // Calculate the deactivation time (2 minutes after scheduled time)
            $checkinDeactivateTime = Carbon::parse($checking->scheduled_timeout);

            // Schedule the deactivation 2 minutes after check-in time
            $schedule->command('checkin:deactivateAndRemove')
                ->timezone('Asia/Jakarta')
                ->dailyAt($checkinDeactivateTime->format('H:i'));
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
