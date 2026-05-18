<?php

namespace App\Jobs;

use App\Models\InternetCustomer;
use App\Models\InternetCustomerGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateGroupingIdJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $tries = 3;
    public $backoff = [5, 15, 30];

    protected string $customerId;

    public function __construct(string $customerId)
    {
        $this->customerId = $customerId;
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $customer = InternetCustomer::lockForUpdate()->find($this->customerId);

            if (!$customer || !$customer->group_id) {
                Log::warning('GenerateGroupingIdJob: customer not found or no group_id', [
                    'customer_id' => $this->customerId,
                ]);
                return;
            }

            if ($customer->grouping_id) {
                // Already has a grouping_id — skip
                return;
            }

            $group = InternetCustomerGroup::lockForUpdate()->find($customer->group_id);

            if (!$group) {
                Log::warning('GenerateGroupingIdJob: group not found', [
                    'group_id' => $customer->group_id,
                ]);
                return;
            }

            // If last_number is 0, scan existing grouping_ids to bootstrap the sequence
            $prefix = $group->grouping_prefix;

            if ($group->last_number == 0) {
                $maxSequence = (int) InternetCustomer::where('group_id', $group->id)
                    ->whereNotNull('grouping_id')
                    ->get('grouping_id')
                    ->pluck('grouping_id')
                    ->map(function ($gid) use ($prefix) {
                        $suffix = substr($gid, strlen($prefix));
                        if ($suffix === '') return 0;
                        return InternetCustomerGroup::parseSequence($suffix);
                    })
                    ->max() ?? 0;

                $group->last_number = $maxSequence;
            }

            $nextNumber  = $group->last_number + 1;
            $groupingId  = $prefix . InternetCustomerGroup::formatSequence($nextNumber);

            // Safety: skip if somehow already taken
            if (InternetCustomer::where('grouping_id', $groupingId)->exists()) {
                Log::error('GenerateGroupingIdJob: grouping_id collision', [
                    'grouping_id' => $groupingId,
                ]);
                return;
            }

            $group->update(['last_number' => $nextNumber]);
            $customer->update(['grouping_id' => $groupingId]);

            Log::info('GenerateGroupingIdJob: assigned', [
                'customer_id' => $customer->id,
                'grouping_id' => $groupingId,
            ]);
        });
    }
}
