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
        $schedule->command('media:cleanup-temporary')->timezone('Asia/Jakarta')->dailyAt('00:00');
        $schedule->command('dayoff:reset-quota')->timezone('Asia/Jakarta')->yearlyOn(1, 1, '0:00');
        // $schedule->command('email:send-device-list')->dailyAt('13:00');

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

        // Run Scheduler
        $schedule->command('schedule:employee-checkin')->dailyAt('07:00');

        $employeeCheckings = EmployeeChecking::where('is_active', true)
            ->where('is_dayoff', false)
            ->whereDate('scheduled_time', Carbon::today()) // Filter today's check-ins
            ->whereBetween('scheduled_time', [
                Carbon::now('Asia/Jakarta')->format('Y-m-d H:i'),
                Carbon::now('Asia/Jakarta')->addMinute(2)->format('Y-m-d H:i')
            ])
            ->get();
        

        foreach ($employeeCheckings as $checking) 
        {
            // Calculate the notification time (1 minute before scheduled time)
            $checkinNotificationTime = Carbon::parse($checking->scheduled_time);
            // $checkinDeactivateTime = Carbon::parse($checking->scheduled_timeout);
        
            $schedule->command("checkin:active --id={$checking->id}")->timezone('Asia/Jakarta')->dailyAt($checkinNotificationTime->format('H:i'));
            // $schedule->command("checkin:deactivate --id={$checking->id}")->timezone('Asia/Jakarta')->dailyAt($checkinDeactivateTime->format('H:i'));
        }

        $employeeCheckingDeactives = EmployeeChecking::where('is_active', true)
            ->where('is_dayoff', false)
            ->whereDate('scheduled_timeout', Carbon::today()) // Filter today's check-ins
            ->whereBetween('scheduled_timeout', [
                Carbon::now('Asia/Jakarta')->format('Y-m-d H:i'),
                Carbon::now('Asia/Jakarta')->addMinute(2)->format('Y-m-d H:i')
            ])
            ->get();

        foreach ($employeeCheckingDeactives as $checking) 
        {
            // Calculate the notification time (1 minute before scheduled time)
            $checkinDeactivateTime = Carbon::parse($checking->scheduled_timeout);
            
            $schedule->command("checkin:deactivate --id={$checking->id}")->timezone('Asia/Jakarta')->dailyAt($checkinDeactivateTime->format('H:i'));
        }

        // foreach ($employeeCheckings as $checking) 
        // {
        //     // Calculate the notification time (1 minute before scheduled time)
        //     $checkinNotificationTime = Carbon::parse($checking->scheduled_time);
            
        //     // Schedule the notification 1 minute before check-in time
        //     $schedule->command('checkin:notifyAndSentPopup')
        //         ->timezone('Asia/Jakarta')
        //         ->dailyAt($checkinNotificationTime->format('H:i'));

        //     // Calculate the deactivation time (2 minutes after scheduled time)
        //     $checkinDeactivateTime = Carbon::parse($checking->scheduled_timeout);

        //     // Schedule the deactivation 2 minutes after check-in time
        //     $schedule->command('checkin:deactivateAndRemove')
        //         ->timezone('Asia/Jakarta')
        //         ->dailyAt($checkinDeactivateTime->format('H:i'));
        // }
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
