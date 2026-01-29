<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Log;

class ItemRequestNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $firebase = (new Factory)
            ->withServiceAccount(
                storage_path(config('services.firebase.service_account'))
            )
            ->withProjectId(
                config('services.firebase.project_id')
            );

        $this->messaging = $firebase->createMessaging();
    }

    /**
     * @param array $fcmTokens
     * @param array $payload
     *  [
     *    'event' => 'item_request_created|item_request_paid|item_request_delivered',
     *    'request_id' => int,
     *    'item_name' => string,
     *    'sprinter_id' => int|null
     *  ]
     */
    public function send(array $fcmTokens, array $payload)
    {
        try {
            $fcmTokens = array_filter($fcmTokens, fn ($t) => is_string($t) && !empty($t));

            if (empty($fcmTokens)) {
                Log::warning('ItemRequestNotification: No valid FCM tokens');
                return;
            }

            [$title, $body] = $this->resolveMessage($payload);

            $message = CloudMessage::new()
                ->withNotification([
                    'title' => $title,
                    'body'  => $body,
                ])
                ->withData([
                    'type'        => $payload['event'],
                    'request_id'  => (string) ($payload['request_id'] ?? ''),
                    'item_name'   => (string) ($payload['item_name'] ?? ''),
                    'sprinter_id' => (string) ($payload['sprinter_id'] ?? ''),
                ])
                ->withAndroidConfig([
                    'priority' => 'high',
                ]);

            $this->messaging->sendMulticast($message, $fcmTokens);

        } catch (FirebaseException $e) {
            Log::error('ItemRequestNotification Firebase error', [
                'message' => $e->getMessage()
            ]);
        }
    }

    protected function resolveMessage(array $payload): array
    {
        $item = $payload['item_name'] ?? '';

        return match ($payload['event'] ?? '') {
            'item_request_created' => [
                'Permintaan Barang Baru',
                'Ada permintaan barang "' . $item . '" yang perlu ditindaklanjuti',
            ],

            'item_request_paid' => [
                'Pembayaran Item',
                'Pembayaran untuk item "' . $item . '" telah ditindaklanjuti',
            ],

            default => [
                'Notifikasi Item',
                'Ada pembaruan pada item "' . $item . '"',
            ],
        };
    }
}
