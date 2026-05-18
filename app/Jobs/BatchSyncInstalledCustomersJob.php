<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use Throwable;

class BatchSyncInstalledCustomersJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 2;

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('batch-sync-installed'))
                ->expireAfter(600)
                ->dontRelease(),
        ];
    }

    public function handle(): void
    {
        Log::info('[BatchSync] Dispatching single SyncInstalledCustomersJob for all customers');

        // SyncInstalledCustomersJob dengan customerIds=null sudah menangani
        // semua customer installed, dikelompokkan per router (1 koneksi per router).
        // Tidak perlu chunk — cukup 1 job, bukan puluhan.
        dispatch(new SyncInstalledCustomersJob(null));
    }

    public function failed(Throwable $e): void
    {
        Log::error('[BatchSync] Failed to dispatch SyncInstalledCustomersJob', [
            'error' => $e->getMessage(),
        ]);
    }
}
