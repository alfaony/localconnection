<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public string $sender_name;
    public string $message;
    public string $created_at;
    public string $item_request_id;
    public $sender_id;

    public function __construct(string $sender_name, string $message, string $created_at, string $item_request_id, $sender_id)
    {
        $this->sender_name = $sender_name;
        $this->message = $message;
        $this->created_at = $created_at;
        $this->item_request_id = $item_request_id;
        $this->sender_id = $sender_id;
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('chat.item-request.' . $this->item_request_id);
    }

    public function broadcastWith(): array
    {
        return [
            'sender_id' => $this->sender_id,
            'sender_name' => $this->sender_name,
            'message' => $this->message,
            'created_at' => $this->created_at,
        ];
    }
}