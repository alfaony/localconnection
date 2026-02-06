<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Http;
use Sentry\Laravel\Integration;

use Throwable;


class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {

            // dd($e);
            if (app()->bound('sentry') && config("sentry.environment") == 'production') {
                Integration::captureUnhandledException($e);
            }
            $data = [
                'content' => $e->getMessage(),
                'title' => 'Line: '.$e->getLine(),
                'desc' => 'Path: '.$e->getFile(),
                'status' => 'error'
            ];
            $this->discordLog($data);
        });
    }

    // discrod
    public function discordLog($data)
    {
        $status_colors = [
            'emergency' => '15548997',
            'alert' => '15418782',
            'critical' => '15548997',
            'error' => '15548997',
            'warning' => '16705372',
            'notice' => '5763719',
            'info' => '7506394',
            'debug' => '2895667',
        ];

        $status = '7506394';
        if ($data['status'] && array_key_exists($data['status'], $status_colors)) {
            $status = $status_colors[$data['status']];
        }

        $request = [
            'content' => $data['content'],
            'embeds' => [
                [
                    'title' => $data['title'],
                    'description' => $data['desc'],
                    'color' => $status,
                ]
            ]
        ];

        $response = Http::post(config('services.discord.webhook_url'), $request);

        return $response->successful();
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof PostTooLargeException) 
        {
            return redirect()->back()->with('store', true); 
        }

        return parent::render($request, $exception);
    }
}
