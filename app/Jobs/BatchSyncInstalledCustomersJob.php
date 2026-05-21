<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Schemas\ParamSchema;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * ✅ NEW: Batch job untuk sync banyak customers sekaligus
 * Lebih efisien daripada schedule individual jobs
 */
class BatchSyncInstalledCustomersJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    public int $timeout = 300; // 5 minutes
    public int $tries = 3;

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
        // Get customers yang perlu di-sync
        $customers = InternetCustomer::query()
            ->with('router')
            ->whereIn('status', [
                ParamSchema::INSTALLED,
                ParamSchema::REACTIVATED
            ])
            ->whereNotNull('router_id')
            ->whereNotNull('username')
            ->get();

        if ($customers->isEmpty()) {
            Log::info('[BatchSync] No customers to sync');
            return;
        }

        // Group by router untuk efficient processing
        $grouped = $customers->groupBy('router_id');

        Log::info('[BatchSync] Starting batch sync', [
            'total_customers' => $customers->count(),
            'routers_count' => $grouped->count(),
        ]);

        foreach ($grouped as $routerId => $routerCustomers) {
            // Dispatch individual sync job per customer
            // Tapi batch per router untuk sequencing yang lebih baik
            $customerIds = $routerCustomers->pluck('id')->toArray();
            
            // Batch size 10 customers per job
            $chunks = array_chunk($customerIds, 10);
            
            foreach ($chunks as $chunk) {
                dispatch(new SyncInstalledCustomersJob($chunk))
                    // ->onQueue('mikrotik')
                    ->delay(now()->addSeconds(2)); // Stagger untuk prevent overload
            }
        }

        Log::info('[BatchSync] Batch sync completed', [
            'jobs_dispatched' => count($customers),
        ]);
    }

    public function failed(Throwable $e): void
    {
        Log::error('[BatchSync] Batch sync failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}