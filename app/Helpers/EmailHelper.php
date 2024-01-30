<?php 

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailHelper
{

    public static function sentEmail($fromEmail, $fromName, $toEmail, $toName, $subject, $view, $data, $smtpConfig)
    {
        // Konfigurasi pengaturan SMTP menggunakan data dinamis
        Config::set('mail.mailers.smtp.host', $smtpConfig['host']);
        Config::set('mail.mailers.smtp.port', $smtpConfig['port']);
        Config::set('mail.mailers.smtp.username', $smtpConfig['username']);
        Config::set('mail.mailers.smtp.password', $smtpConfig['password']);
        Config::set('mail.mailers.smtp.encryption', $smtpConfig['encryption']);

        // sent email
        try {
            Mail::send($view, ['data' => $data], function ($message) use ($fromEmail, $fromName, $toEmail, $toName, $subject) {
                $message->to($toEmail, $toName)
                        ->subject($subject)
                        ->from($fromEmail, $fromName);

                
            });
            
            return true;
        } catch (\Exception $e) {
            // dd($e);
            Log::error($e);
            return false;
            // return ['status' => 'error', 'message' => 'Failed Sent Email ' . $e->getMessage()];
        }
    }
}
