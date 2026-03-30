<?php

namespace App\Jobs;

use App\Models\EmployeeXpHistory;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class AwardXpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah retry jika job gagal.
     */
    public int $tries = 3;

    /**
     * Delay antar retry (dalam detik).
     */
    public int $backoff = 5;

    public function __construct(
        public readonly string  $userId,
        public readonly string  $companyId,
        public readonly int     $xp,
        public readonly string  $sourceType,
        public readonly ?string $sourceId,
        public readonly ?string $description,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // 1. Simpan ke history (immutable log)
            EmployeeXpHistory::create([
                'id'          => Uuid::uuid4()->toString(),
                'user_id'     => $this->userId,
                'company_id'  => $this->companyId,
                'xp'          => $this->xp,
                'source_type' => $this->sourceType,
                'source_id'   => $this->sourceId,
                'description' => $this->description,
            ]);

            // 2. Update total_xp user secara atomic
            // Gunakan increment/decrement agar aman dari race condition
            if ($this->xp >= 0) {
                User::where('id', $this->userId)->increment('total_xp', $this->xp);
            } else {
                // Pastikan total_xp tidak turun di bawah 0
                User::where('id', $this->userId)
                    ->where('total_xp', '>=', abs($this->xp))
                    ->decrement('total_xp', abs($this->xp));
            }
        } catch (\Throwable $th) {
            Log::error('[AwardXpJob] Gagal memberi XP', [
                'user_id'     => $this->userId,
                'xp'          => $this->xp,
                'source_type' => $this->sourceType,
                'error'       => $th->getMessage(),
            ]);

            throw $th; // Re-throw agar masuk retry queue
        }
    }
}
