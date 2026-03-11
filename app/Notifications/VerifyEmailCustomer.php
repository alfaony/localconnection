<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class VerifyEmailCustomer extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Company slug — untuk redirect ke login page yang tepat setelah verifikasi.
     */
    protected string $companySlug;

    public function __construct(string $companySlug)
    {
        $this->companySlug = $companySlug;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Buat URL verifikasi yang setelah diklik mengarah ke customer login.
     * Kita embed companySlug sebagai query param agar controller bisa redirect.
     */
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'customer.email.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id'           => $notifiable->getKey(),
                'hash'         => sha1($notifiable->getEmailForVerification()),
                'company_slug' => $this->companySlug,
            ]
        );
    }

    public function toMail($notifiable): MailMessage
    {
        try {
            $verifyUrl   = $this->verificationUrl($notifiable);
            $loginUrl    = route('public.software-sharing.login', $this->companySlug);
            $companyName = $notifiable->company->name ?? config('app.name');
            $userName    = $notifiable->name;
    
            return (new MailMessage)
                ->subject('Verifikasi Email Anda — ' . $companyName)
                ->view('emails.customer.verify-email', [
                    'verifyUrl'   => $verifyUrl,
                    'loginUrl'    => $loginUrl,
                    'companyName' => $companyName,
                    'userName'    => $userName,
                ]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th);
        }
    }
}
