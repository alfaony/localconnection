<?php 

namespace App\Helpers;

use Illuminate\Support\Facades\Http;


class ErrorLogHelper
{
    public static function log($e)
    {
        $data = [
            'content' => $e->getMessage(),
            'title' => 'Line: '.$e->getLine(),
            'desc' => 'Path: '.$e->getFile(),
            'status' => 'error'
        ];

        self::discordLog($data);
    }

    public static function logMessage($message, $line = null, $description = null , $status = null)
    {
        $data = [
            'content' => $message,
            'title' => 'Line: '.$line,
            'desc' => 'Path: '.$description,
            'status' =>  $status ?? 'error'
        ];

        self::discordLog($data);
    }

    private static function discordLog($data)
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
}