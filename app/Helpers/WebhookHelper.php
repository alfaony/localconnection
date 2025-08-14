<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WebhookLog;
use App\Jobs\DispatchWebhooksForAppJob;

class WebhookHelper
{
    public static function sendWebhook(string $companyId, string $appName, String $event, Array $payload): void
    {
        try {
            DispatchWebhooksForAppJob::dispatch(
                companyId: $companyId,
                appName:   $appName,
                payload:   $payload,
                event:     $event // atau 'created'
            );
        } catch (\Throwable $e) {
            dd($e);
            Log::error('Webhook error', [
                'exception' => $e,
            ]);
        }
    }
}
