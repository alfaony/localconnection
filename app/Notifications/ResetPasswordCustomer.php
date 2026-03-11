<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordCustomer extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $token;
    protected string $companySlug;

    public function __construct(string $token, string $companySlug)
    {
        $this->token       = $token;
        $this->companySlug = $companySlug;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $resetUrl    = route('customer.password.reset.form', [
            'companySlug' => $this->companySlug,
            'token'       => $this->token,
        ]) . '?email=' . urlencode($notifiable->getEmailForPasswordReset());
        $loginUrl    = route('public.software-sharing.login', $this->companySlug);
        $companyName = $notifiable->company->name ?? config('app.name');
        $userName    = $notifiable->name;

        return (new MailMessage)
            ->subject('Reset Password Akun Anda — ' . $companyName)
            ->view('emails.customer.reset-password', [
                'resetUrl'    => $resetUrl,
                'loginUrl'    => $loginUrl,
                'companyName' => $companyName,
                'userName'    => $userName,
            ]);
    }
}
