<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewBarcodeGenerated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $companyId;
    public $barcode;
    public function __construct($barcode, $companyId)
    {
        $this->companyId = $companyId;
        $this->barcode = $barcode;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('office.barcode.' . $this->companyId);
    }

    public function broadcastWith(): array
    {
        return 
        [
            'barcode' => $this->barcode,
        ];
    }
}
