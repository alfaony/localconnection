<?php

namespace App\Console\Commands;

use Carbon\Carbon;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

use App\Helpers\EmailHelper;
use App\Mail\ProjectNotification;

use App\Schemas\NoticeSchema;

use App\Models\SettingCompany;
use App\Models\Project;
use App\Models\User;
use App\Models\Company;

class SendProjectExpirationNotifications extends Command
{
    protected $signature = 'project:send-expiration-notifications';
    protected $description = 'Kirim notifikasi proyek yang akan segera berakhir';

    public function handle()
    {
        // load Company
        $company = Company::all();
        foreach ($company as $a) 
        {
            $user = $a->user()->first() ? $a->user()->first() : '';
            $settingCompany = SettingCompany::byCompany($a->id)
            ->get();
            $profile = $settingCompany->pluck('field_value','field_title');
            $time = Carbon::now()->tz('Asia/Jakarta')->format('H:i');

            $sentTime = $profile['sent_time'];
            $sentTimeStatus = $profile['sent_time_status'];

            if(isset($user) && ($sentTime == $time) && ($sentTimeStatus != "sent"))
            // if ($user && ($sentTimeStatus != "sent"))  
            {
                $this->info('========= DO EXCUTE ============');

                // update status sent time
                $sentTime = SettingCompany::byCompany($a->id)
                                ->where('field_title','sent_time_status')
                                ->first();
                $sentTime->field_value = "sent";
                $sentTime->save();


                $currentDate = Carbon::today();
        
                // Add 1 week to the current date
                $oneWeekLater = $currentDate->copy()->addWeek();
                
                // Add 2 week to the current date
                $twoMonthsLater = $currentDate->copy()->addWeek(2);
        
                // Add 1 month to the current date
                $oneMonthLater = $currentDate->copy()->addMonth();
        
        
                // today
                $projectsToday = Project::byCompany($user->company_id)->where('alert_expired',NoticeSchema::ACTIVED)->whereDate('end_date', $currentDate)->get();
        
                // 1 week
                $projectsOneWeek = Project::byCompany($user->company_id)->where('alert_one_week',NoticeSchema::ACTIVED)->whereDate('end_date', $oneWeekLater)->get();
        
                // 2 week
                $projectsTwoMonth = Project::byCompany($user->company_id)->where('alert_two_week',NoticeSchema::ACTIVED)->whereDate('end_date', $twoMonthsLater)->get();
        
                // 1 month
                $projectsOneMonth = Project::byCompany($user->company_id)->where('alert_one_month',NoticeSchema::ACTIVED)->whereDate('end_date', $oneMonthLater)->get();

                // today
                foreach ($projectsToday as $expired) 
                {
                    $this->sendNotification($expired,NoticeSchema::EXPIRED);
                    $this->info('DONE Sent');
                }
        
                foreach ($projectsOneWeek as $expired) 
                {
                    $this->sendNotification($expired,NoticeSchema::ONEWEEK);
                    $this->info('DONE Sent');

                }
        
                foreach ($projectsTwoMonth as $expired) 
                {
                    $this->sendNotification($expired,NoticeSchema::TWOWEEK);
                    $this->info('DONE Sent');

                }
        
                foreach ($projectsOneMonth as $expired) 
                {
                    $this->sendNotification($expired,NoticeSchema::ONEMONTH);
                    $this->info('DONE Sent');
                }

                $this->info('Next Excute.');
            }
            else
            {
                $this->info('No Excute.');
                $this->info($sentTime);
            }

        }
        // $this->updateSentTime();
    }

    protected function sendNotification($project, $timeNotify)
    {
        
        $email = $project->workOrder->quote->customer->email;
        $pic = $project->workOrder->quote->customer->pic;
        $companyName = $project->workOrder->quote->customer->name;
        $user = User::find($project->user_id);
        
        $smtpConfig = SettingCompany::byCompany($user->company_id)->get()->pluck('field_value','field_title');
        $sentTIme = $smtpConfig['sent_time'] ?? now();
        $fromEmail = $smtpConfig['username'] ?? '';
        $fromName = $smtpConfig['name'] ?? '';
        switch ($timeNotify) 
        {
            case NoticeSchema::EXPIRED:
                $subject = 'Project Telah Berakhir';
                $tamplate = 'email.notif_'.NoticeSchema::EXPIRED;
                break;
            case NoticeSchema::ONEWEEK:
                $subject = 'Project Akan Berakhir 1 Minggu Kemudiah';
                $tamplate = 'email.notif_'.NoticeSchema::ONEWEEK;

                break;

            case NoticeSchema::TWOWEEK:
                $subject = 'Project Akan Berakhir 2 Minggu Kemudiah';
                $tamplate = 'email.notif_'.NoticeSchema::TWOWEEK;
                break;

            case NoticeSchema::ONEMONTH:
                $subject = 'Project Akan Berakhir 1 Bulan Kemudiah';
                $tamplate = 'email.notif_'.NoticeSchema::ONEMONTH;
                break;
        }

        
        $sentEmail = EmailHelper::sentEmail(
            $fromEmail,
            $fromName,
            $email,
            $companyName,
            $subject,
            $tamplate,
            $project,
            $smtpConfig
        );
    }

    protected function updateSentTime()
    {
        $settingCompany = SettingCompany::where('field_title','sent_time_status')->where('field_value','sent')->get();
        // $this->info('sent'.count($settingCompany));
        $company = Company::count();

        if(count($settingCompany) == $company)
        {
            foreach ($settingCompany as $a) 
            {
                $a->field_value = "waiting";
                $a->save();
            }
        }
    }
}
