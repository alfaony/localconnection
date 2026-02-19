<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionChatSent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public string $sender_name;
    public string $message;
    public ?string $attachment_url;
    public string $created_at;
    public string $subscription_id;
    public $sender_id;
    public bool $is_me;
    public int $chat_id;

    public function __construct(
        int    $chat_id,
        string $sender_name,
        string $message,
        ?string $attachment_url,
        string $created_at,
        string $subscription_id,
        $sender_id
    ) {
        $this->chat_id        = $chat_id;
        $this->sender_name    = $sender_name;
        $this->message        = $message;
        $this->attachment_url = $attachment_url;
        $this->created_at     = $created_at;
        $this->subscription_id = $subscription_id;
        $this->sender_id      = $sender_id;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('subscription.chat.' . $this->subscription_id);
    }

    public function broadcastWith(): array
    {
        return [
            'id'             => $this->chat_id,
            'sender_id'      => $this->sender_id,
            'sender_name'    => $this->sender_name,
            'message'        => $this->message,
            'attachment_url' => $this->attachment_url,
            'created_at'     => $this->created_at,
        ];
    }
}
