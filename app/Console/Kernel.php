<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\SettingCompany;
use App\Models\Company;
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

        $company = Company::all();
        foreach ($company as $a) 
        {
            $settingCompany = SettingCompany::byCompany($a->id)->get()->pluck('field_value','field_title');
            $sentTime = $settingCompany['sent_time'];

            if($sentTime != "")
            {
                $schedule->command('project:send-expiration-notifications')->timezone('Asia/Jakarta')->dailyAt($sentTime);
            }
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
