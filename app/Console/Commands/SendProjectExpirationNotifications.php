<?php

namespace App\Console\Commands;

use Carbon\Carbon;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use App\Helpers\EmailHelper;
use App\Helpers\ErrorLogHelper;

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

            if(($sentTime == $time) && ($sentTimeStatus != "sent"))
            // if ($user)  
            {
                try {
                    //code...
                    $this->info('========= DO EXCUTE ============');
        
    
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
                        $this->sendNotification($expired,NoticeSchema::EXPIRED,$a->id);
                        $this->info('DONE Sent');
                    }
            
                    foreach ($projectsOneWeek as $expired) 
                    {
                        $this->sendNotification($expired,NoticeSchema::ONEWEEK,$a->id);
                        $this->info('DONE Sent');
    
                    }
            
                    foreach ($projectsTwoMonth as $expired) 
                    {
                        $this->sendNotification($expired,NoticeSchema::TWOWEEK,$a->id);
                        $this->info('DONE Sent');
    
                    }
            
                    foreach ($projectsOneMonth as $expired) 
                    {
                        $this->sendNotification($expired,NoticeSchema::ONEMONTH,$a->id);
                        $this->info('DONE Sent');
                    }
                    
                        // update status sent time
                        $sentTime = SettingCompany::byCompany($a->id)
                            ->where('field_title','sent_time_status')
                            ->first();
                        $sentTime->field_value = "sent";
                        $sentTime->save();

                    $this->info('Next Excute.');
                } catch (\Throwable $th) {
                    //throw $th;
                    // dd($th);
                    ErrorLogHelper::log($th);
                    Log::error($th->getMessage());
                }
            }
            else
            {
                $this->info('No Excute.');
                $this->info($sentTime);
            }

        }
        // $this->updateSentTime();
    }

    protected function sendNotification($project, $timeNotify, $companyId)
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
                $subject = 'Your project '.$project->title.' Has Expired';
                $tamplate = 'email.notif_'.NoticeSchema::EXPIRED;
                break;
            case NoticeSchema::ONEWEEK:
                $subject = 'Your project '.$project->title.' Expires in 1 week - Exclusive Renewal Offer Inside!';
                $tamplate = 'email.notif_'.NoticeSchema::ONEWEEK;

                break;

            case NoticeSchema::TWOWEEK:
                $subject = 'Your project '.$project->title.' is About to Expire - Renew Now!';
                $tamplate = 'email.notif_'.NoticeSchema::TWOWEEK;
                break;

            case NoticeSchema::ONEMONTH:
                $subject = 'Your project '.$project->title.' Is Expires Soon - Renew Now!';
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
            $smtpConfig,
            $companyId,
        );
    }
}
