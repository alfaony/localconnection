<?php 

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use App\Schemas\NoticeSchema;

use App\Helpers\ErrorLogHelper;

use App\Models\EmailLog;


class EmailHelper
{

    public static function sentEmail($fromEmail, $fromName, $toEmail, $toName, $subject, $view, $data, $smtpConfig, $companyId)
    {
        // Konfigurasi pengaturan SMTP menggunakan data dinamis
        Config::set('mail.mailers.smtp.host', $smtpConfig['host']);
        Config::set('mail.mailers.smtp.port', $smtpConfig['port']);
        Config::set('mail.mailers.smtp.username', $smtpConfig['username']);
        Config::set('mail.mailers.smtp.password', $smtpConfig['password']);
        Config::set('mail.mailers.smtp.encryption', $smtpConfig['encryption']);

        // sent email
        $data = $data;
        $data['company_name'] = $smtpConfig['name'];

        try {
            if($smtpConfig['host'] && $smtpConfig['port'] && $smtpConfig['username'])
            {
                Mail::send($view, ['data' => $data], function ($message) use ($fromEmail, $fromName, $toEmail, $toName, $subject, $data) {
                    $message->to($toEmail, $toName)
                            ->cc($data->user->email, $data->user->name) // Tambahkan alamat CC
                            ->subject($subject)
                            ->from($fromEmail, $fromName);
    
                    
                });
                // save to log
                self::emailLog($fromEmail, $fromName, $toEmail, $toName, $subject, $view, $data->toJson(), $smtpConfig->toJson(), $companyId, NoticeSchema::TRUE, NoticeSchema::SENT);

                return true;
            }else
            {
                if(!$smtpConfig['host'])
                {
                    $message ="Host SMTP tidak tersedia ". $fromName;
                }
                elseif(!$smtpConfig['port'])
                {
                    $message ="Port SMTP tidak tersedia". $fromName;
                }
                elseif(!$smtpConfig['username'])
                {
                    $message ="Username SMTP tidak tersedia". $fromName;
                }

                ErrorLogHelper::logMessage($message,'Class EmailHelper',$message,"info");

                self::emailLog($fromEmail, $fromName, $toEmail, $toName, $subject, $view, $data->toJson(), $smtpConfig->toJson(), $companyId, NoticeSchema::FALSE, NoticeSchema::FAILED);
                return false;
            }
            
        } catch (\Exception $e) {
            // dd($e);
            ErrorLogHelper::log($e);
            Log::error($e);
            self::emailLog($fromEmail, $fromName, $toEmail, $toName, $subject, $view, $data->toJson(), $smtpConfig->toJson(), $companyId, NoticeSchema::FALSE, NoticeSchema::FAILED);

            return false;
        }
    }

    protected static function emailLog($fromEmail, $fromName, $toEmail, $toName, $subject, $view, $data, $smtpConfig,$companyId,$response,$status)
    {
        try {
            //code...
            $emailLog = new EmailLog();
            $emailLog->company_id = $companyId;
            $emailLog->from_email = $fromEmail;
            $emailLog->from_name = $fromName;
            $emailLog->to_email = $toEmail;
            $emailLog->subject = $toName;
            $emailLog->body = $data;
            $emailLog->smtp = $smtpConfig;
            $emailLog->response = $response;
            $emailLog->status = $status;
            $emailLog->save();
        } catch (\Throwable $th) {
            //throw $th;
            ErrorLogHelper::log($th);
        }
    }
}
