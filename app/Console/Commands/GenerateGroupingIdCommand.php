<?php

namespace App\Console\Commands;

use App\Models\InternetCustomer;
use App\Models\InternetCustomerGroup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateGroupingIdCommand extends Command
{
    /**
     * php artisan internet-customer:generate-grouping-id
     *   [--group=]   Filter ke nama/ID group tertentu (opsional)
     *   [--dry-run]  Preview tanpa menyimpan
     */
    protected $signature = 'internet-customer:generate-grouping-id
                            {--group=   : Filter ke nama atau ID group tertentu (opsional)}
                            {--dry-run  : Preview tanpa menyimpan ke database}';

    protected $description = 'Auto-assign grouping_id (misal PUNTUK0001) untuk pelanggan yang punya group_id tapi belum punya grouping_id';

    public function handle(): int
    {
        $isDry     = $this->option('dry-run');
        $groupOpt  = $this->option('group');

        $this->info('=== Generate Grouping ID ===');
        if ($isDry) {
            $this->warn('[DRY RUN] Tidak ada yang akan disimpan.');
        }

        // ── Cari semua pelanggan: group_id terisi, grouping_id kosong ─────────
        $query = InternetCustomer::query()
            ->whereNotNull('group_id')
            ->whereNull('grouping_id');

        // Filter group tertentu jika --group diberikan
        if ($groupOpt) {
            $group = InternetCustomerGroup::where('id', $groupOpt)
                ->orWhere('name', 'like', "%{$groupOpt}%")
                ->first();

            if (!$group) {
                $this->error("Group '{$groupOpt}' tidak ditemukan.");
                return self::FAILURE;
            }

            $query->where('group_id', $group->id);
            $this->line("Filter group: {$group->name}");
        }

        $customers = $query->get(['id', 'code', 'name', 'group_id', 'grouping_id']);

        if ($customers->isEmpty()) {
            $this->info('Tidak ada pelanggan yang perlu di-assign grouping_id.');
            return self::SUCCESS;
        }

        $this->line("Ditemukan {$customers->count()} pelanggan tanpa grouping_id.");

        // ── Kelompokkan per group ─────────────────────────────────────────────
        $grouped = $customers->groupBy('group_id');
        $totalAssigned = 0;
        $totalError    = 0;

        foreach ($grouped as $groupId => $groupCustomers) {
            $group = InternetCustomerGroup::find($groupId);
            if (!$group) {
                $this->warn("Group ID {$groupId} tidak ditemukan, dilewati.");
                continue;
            }

            $prefix = $group->grouping_prefix;

            $this->newLine();
            $this->line("<fg=cyan>[Group: {$group->name}]</> prefix=<fg=yellow>{$prefix}</> | {$groupCustomers->count()} pelanggan");

            // Cari nomor terakhir yang sudah dipakai di group ini
            $lastNumber = (int) $group->last_number;
            if ($lastNumber === 0) {
                $lastNumber = (int) InternetCustomer::where('group_id', $group->id)
                    ->whereNotNull('grouping_id')
                    ->get('grouping_id')
                    ->pluck('grouping_id')
                    ->map(fn($gid) => InternetCustomerGroup::parseSequence(substr($gid, strlen($prefix))))
                    ->max();
            }

            $counter = $lastNumber;

            foreach ($groupCustomers as $customer) {
                $counter++;
                $groupingId = $prefix . InternetCustomerGroup::formatSequence($counter);

                // Pastikan tidak collision
                while (InternetCustomer::where('grouping_id', $groupingId)->where('id', '!=', $customer->id)->exists()) {
                    $this->warn("  Collision: {$groupingId} sudah dipakai, lanjut ke nomor berikutnya.");
                    $counter++;
                    $groupingId = $prefix . InternetCustomerGroup::formatSequence($counter);
                }

                $this->line("  #{$customer->code} {$customer->name} → <fg=green>{$groupingId}</>");

                if (!$isDry) {
                    try {
                        DB::transaction(function () use ($customer, $groupingId, $group, $counter) {
                            $customer->update(['grouping_id' => $groupingId]);
                            if ($counter > $group->last_number) {
                                $group->update(['last_number' => $counter]);
                            }
                        });
                        $totalAssigned++;
                    } catch (\Throwable $e) {
                        $this->error("  [ERROR] #{$customer->code}: " . $e->getMessage());
                        Log::error('GenerateGroupingIdCommand error', [
                            'customer_id' => $customer->id,
                            'error'       => $e->getMessage(),
                        ]);
                        $totalError++;
                        $counter--;
                    }
                } else {
                    $totalAssigned++;
                }
            }
        }

        $this->newLine();
        $this->table(['Status', 'Jumlah'], [
            ['Berhasil di-assign', $totalAssigned],
            ['Error',              $totalError],
        ]);

        if ($isDry) {
            $this->warn('[DRY RUN] Jalankan tanpa --dry-run untuk menyimpan.');
        } else {
            $this->info('Selesai.');
        }

        return self::SUCCESS;
    }
}
