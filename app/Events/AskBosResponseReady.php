<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AskBosResponseReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int|string $userId,
        public readonly string     $analysis,
        public readonly int        $trustScore,
        public readonly ?int       $executionScore,  // nullable — tidak semua prompt generate ini
        public readonly ?string    $cacheKey,        // FIX #3 — frontend fetch dari key yang benar
        public readonly bool       $isError = false, // flag untuk frontend handle error state
    ) {}

    /**
     * Channel broadcasting — private per user.
     * Hanya user yang bersangkutan yang menerima event ini.
     */
    public function broadcastOn(): Channel
    {
        return new Channel("bos.user.{$this->userId}");
    }

    /**
     * Nama event yang diterima frontend (JavaScript).
     */
    public function broadcastAs(): string
    {
        return 'bos.response.ready';
    }

    /**
     * Data yang dikirim ke frontend via WebSocket.
     */
    public function broadcastWith(): array
    {
        return [
            'analysis'        => $this->analysis,
            'trust_score'     => $this->trustScore,
            'execution_score' => $this->executionScore,  // null jika tidak di-generate AI
            'cache_key'       => $this->cacheKey,
            'is_error'        => $this->isError,
            'timestamp'       => now()->toISOString(),
        ];
    }
}