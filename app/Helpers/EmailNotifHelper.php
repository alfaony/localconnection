<?php 

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use App\Schemas\NoticeSchema;
use App\Helpers\ErrorLogHelper;
use App\Models\EmailLog;

class EmailNotifHelper
{

    public static function sentEmail($fromEmail, $fromName, $toEmails, $toNames, $subject, $view, $data, $smtpConfig, $companyId, $ccEmails = [], $ccNames = [], $attachments = [])
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
                Mail::send($view, ['data' => $data], function ($message) use ($fromEmail, $fromName, $toEmails, $toNames, $subject, $data, $ccEmails, $ccNames, $attachments) {
                    foreach ($toEmails as $key => $toEmail) {
                        $toName = $toNames[$key] ?? null;
                        $message->to($toEmail, $toName);
                    }
                    
                    if(count($ccEmails) > 0)
                    {
                        foreach ($ccEmails as $key => $ccEmail) {
                            $ccName = $ccNames[$key] ?? null;
                            $message->cc($ccEmail, $ccName);
                        }
                    }

                    $message->subject($subject)->from($fromEmail, $fromName);

                    // Attach files if provided and not empty
                    if (!empty($attachments)) 
                    {
                        foreach ($attachments as $filePath => $fileName) {
                            if (file_exists($filePath)) {
                                $message->attach($filePath, [
                                    'as' => $fileName,
                                    'mime' => mime_content_type($filePath),
                                ]);
                            }
                        }
                    }
                });

                // save to log
                self::emailLog($fromEmail, $fromName, json_encode($toEmails), json_encode($toNames), $subject, $view, json_encode($data), json_encode($smtpConfig), $companyId, NoticeSchema::TRUE, NoticeSchema::SENT);

                return true;
            } else {
                $message = self::getSmtpErrorMessage($smtpConfig, $fromName);

                ErrorLogHelper::logMessage($message, 'Class EmailHelper', $message, "info");

                // self::emailLog($fromEmail, $fromName, json_encode($toEmails), json_encode($toNames), $subject, $view, json_encode($data), json_encode($smtpConfig), $companyId, NoticeSchema::FALSE, NoticeSchema::FAILED);
                return false;
            }
        } catch (\Exception $e) {
            // dd($e);

            // Log
            ErrorLogHelper::log($e);
            Log::error($e);
            self::emailLog($fromEmail, $fromName, json_encode($toEmails), json_encode($toNames), $subject, $view, json_encode($data), json_encode($smtpConfig), $companyId, NoticeSchema::FALSE, NoticeSchema::FAILED);

            return false;
        }
    }

    protected static function emailLog($fromEmail, $fromName, $toEmails, $toNames, $subject, $view, $data, $smtpConfig, $companyId, $response, $status)
    {
        try {
            $emailLog = new EmailLog();
            $emailLog->company_id = $companyId;
            $emailLog->from_email = $fromEmail;
            $emailLog->from_name = $fromName;
            $emailLog->to_email = $toEmails;
            $emailLog->subject = $subject;
            $emailLog->body = $data;
            $emailLog->smtp = $smtpConfig;
            $emailLog->response = $response;
            $emailLog->status = $status;
            $emailLog->save();
        } catch (\Throwable $th) {
            ErrorLogHelper::log($th);
        }
    }

    private static function getSmtpErrorMessage($smtpConfig, $fromName)
    {
        if(!$smtpConfig['host']) {
            return "Host SMTP tidak tersedia " . $fromName;
        } elseif(!$smtpConfig['port']) {
            return "Port SMTP tidak tersedia " . $fromName;
        } elseif(!$smtpConfig['username']) {
            return "Username SMTP tidak tersedia " . $fromName;
        }
        return "SMTP configuration is missing";
    }
}