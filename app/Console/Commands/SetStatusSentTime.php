<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SettingCompany;

class SetStatusSentTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:set-status-sent-time';

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
        $settingCompany = SettingCompany::where('field_title','sent_time_status')->where('field_value','sent')->get();
        // // $this->info('sent'.count($settingCompany));
        // $company = Company::count();

        // if(count($settingCompany) == $company)
        // {
        foreach ($settingCompany as $a) 
        {
            $a->field_value = "waiting";
            $a->save();
        }
        // }

        return $this->info("Success Update");
    }
}
