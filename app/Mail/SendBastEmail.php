<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class SendBastEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;
    public $filePath;
    public $smtpConfig;

    /**
     * Create a new message instance.
     *
     * @param array $emailData
     * @param string $filePath
     * @return void
     */
    public function __construct($emailData, $filePath, $smtpConfig)
    {
        $this->emailData = $emailData;
        $this->filePath = $filePath;
        $this->smtpConfig = $smtpConfig;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Config::set('mail.mailers.smtp.host', $this->smtpConfig['host']);
        Config::set('mail.mailers.smtp.port', $this->smtpConfig['port']);
        Config::set('mail.mailers.smtp.username', $this->smtpConfig['username']);
        Config::set('mail.mailers.smtp.password', $this->smtpConfig['password']);
        Config::set('mail.mailers.smtp.encryption', $this->smtpConfig['encryption']);

        return $this->subject($this->emailData['subject'])
        ->view('email.bast_email')
        ->with([
            'content' => $this->emailData['content'],
            ])
            ->attach($this->filePath, [
                'as' => 'BAST_Merged_File.pdf',
                'mime' => 'application/pdf',
            ])
            // ->from("no-reply@gmail.com", "test")
            ;
    }
}

