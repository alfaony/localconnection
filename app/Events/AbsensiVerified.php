<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AbsensiVerified implements ShouldBroadcast
{
    use SerializesModels;

    public $barcode;

    /**
     * Create a new event instance.
     *
     * @param $barcode
     */
    public function __construct($barcode)
    {
        $this->barcode = $barcode;
    }

    /**
     * The channel the event should broadcast on.
     *
     * @return string
     */
    public function broadcastOn()
    {
        // Channel yang digunakan untuk broadcasting
        return new Channel('office.barcode.' . $this->barcode->company_id);
    }

    /**
     * The data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        // Data yang akan dikirim saat broadcasting
        return [
            'barcode_id' => $this->barcode->id,
            'message' => 'QR code berhasil diverifikasi! Mengarah ke form foto dan lokasi.',
        ];
    }
}
