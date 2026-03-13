<?php

namespace App\Jobs;

use App\Models\HotspotVoucherBatch;
use App\Models\HotspotVoucher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Generate voucher hotspot dalam satu batch lalu provision masing-masing.
 */
class GenerateVoucherBatchJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    public function __construct(public string $batchId) {}

    public function handle(): void
    {
        $batch = HotspotVoucherBatch::with('internetPackage')->findOrFail($this->batchId);

        $prefix = strtoupper($batch->prefix ?: 'VOC');
        $now    = now();

        $vouchers = [];
        for ($i = 0; $i < $batch->quantity; $i++) {
            $username = $prefix . '-' . strtoupper(Str::random(6));
            $password = strtoupper(Str::random(8));

            // Pastikan username unik
            while (HotspotVoucher::where('username', $username)->exists()) {
                $username = $prefix . '-' . strtoupper(Str::random(6));
            }

            $vouchers[] = [
                'id'                          => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'hotspot_voucher_batch_id'    => $batch->id,
                'hotspot_server_id'           => $batch->hotspot_server_id,
                'internet_package_id'         => $batch->internet_package_id,
                'username'                    => $username,
                'password'                    => $password,
                'status'                      => 'unused',
                'valid_from'                  => null,
                'expires_at'                  => null,
                'used_by_mac'                 => null,
                'meta'                        => null,
                'created_at'                  => $now,
                'updated_at'                  => $now,
            ];
        }

        // Bulk insert
        foreach (array_chunk($vouchers, 100) as $chunk) {
            HotspotVoucher::insert($chunk);
        }

        Log::info('[GenerateVoucherBatchJob] Generated ' . count($vouchers) . ' vouchers', ['batch_id' => $batch->id]);

        // Dispatch ProvisionVoucherJob per voucher (async)
        $ids = HotspotVoucher::where('hotspot_voucher_batch_id', $batch->id)->pluck('id');
        foreach ($ids as $id) {
            ProvisionVoucherJob::dispatch($id);
        }

        Log::info('[GenerateVoucherBatchJob] Dispatched ' . $ids->count() . ' provision jobs', ['batch_id' => $batch->id]);
    }
}
