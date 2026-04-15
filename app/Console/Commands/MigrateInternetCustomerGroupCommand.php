<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\InternetCustomer;
use App\Models\InternetCustomerGroup;
use App\Schemas\ParamSchema;
use App\Models\Company;

class MigrateInternetCustomerGroupCommand extends Command
{
    /**
     * php artisan internet-customer:migrate-group
     *   [--company=]   Batasi ke company_id tertentu (opsional)
     *   [--dry-run]    Tampilkan rencana tanpa menyimpan
     */
    protected $signature = 'internet-customer:migrate-group
                            {--company= : Batasi ke company_id tertentu}
                            {--dry-run  : Tampilkan rencana tanpa menyimpan ke database}';

    protected $description = 'Migrasi grouping_id (string) ke group_id (FK) pada pelanggan aktif & waiting payment subscription';

    public function handle(): int
    {
        $isDry     = $this->option('dry-run');
        $companyId = $this->option('company');

        $this->info('=== Migrate InternetCustomer grouping_id → group_id ===');
        $isDry && $this->warn('[DRY RUN] Tidak ada perubahan yang akan disimpan.');

        // Ambil customer yang harus dimigrasikan
        $query = InternetCustomer::query()
            ->whereIn('status', [ParamSchema::ACTIVE, ParamSchema::WAITING_PAYMENT_SUBSCRIPTION])
            ->whereNull('group_id')                     // belum dimigrasikan
            ->with(['installation:id,internet_customer_id,device_serial_number']);

        $companyIds = Company::where('slug',$companyId)->first();

        if ($companyIds) {
            $query->where('company_id', $companyIds->id);
            $this->line("Filter company_id = {$companyIds->id}");
        }

        $customers = $query->get();

        $this->line("Ditemukan {$customers->count()} pelanggan yang perlu dimigrasikan.");

        if ($customers->isEmpty()) {
            $this->info('Tidak ada data yang perlu dimigrasikan.');
            return self::SUCCESS;
        }

        $stats = ['matched' => 0, 'not_found' => 0];

        $bar = $this->output->createProgressBar($customers->count());
        $bar->start();

        foreach ($customers as $customer) {
            $group = $this->resolveGroup($customer);

            if ($group) {
                $stats['matched']++;
                $this->newLine();
                $this->line(
                    "  [MATCH] #{$customer->code} → Group \"{$group->name}\" (id: {$group->id})"
                    . " | via: {$this->lastMatchReason}"
                );
                if (!$isDry) {
                    $customer->update(['group_id' => $group->id]);
                }
            } else {
                $stats['not_found']++;
                $this->newLine();
                $this->warn(
                    "  [NO MATCH] #{$customer->code} — grouping_id=\"{$customer->grouping_id}\""
                    . " username=\"{$customer->username}\""
                    . " serial=\"{$customer->installation?->device_serial_number}\""
                );
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Selesai.');
        $this->table(
            ['Status', 'Jumlah'],
            [
                ['Matched (group_id diisi)', $stats['matched']],
                ['Tidak ditemukan (group_id = null)', $stats['not_found']],
            ]
        );

        if ($isDry) {
            $this->warn('[DRY RUN] Tidak ada yang disimpan. Jalankan tanpa --dry-run untuk apply.');
        }

        return self::SUCCESS;
    }

    // ── Resolusi group ────────────────────────────────────────────────────────

    private string $lastMatchReason = '';

    private function resolveGroup(InternetCustomer $customer): ?InternetCustomerGroup
    {
        // Kandidat pencarian (prioritas: grouping_id → serial number → username)
        $candidates = array_filter([
            'grouping_id'   => $customer->grouping_id,
            'serial_number' => $customer->installation?->device_serial_number,
            'username'      => $customer->username,
        ]);

        foreach ($candidates as $reason => $value) {
            if (!$value) {
                continue;
            }

            $searchValue = $value;

            // Khusus grouping_id, normalisasi ke kode grup
            if ($reason === 'grouping_id') {
                $searchValue = $this->extractGroupCode($value) ?? $value;
            }

            $groups = InternetCustomerGroup::where('company_id', $customer->company_id)
                ->where('name', 'like', '%' . $searchValue . '%')
                ->get();

            if ($groups->count() > 1) {
                $this->line("  [NO MATCH] #{$customer->code} — {$reason}=\"{$value}\" => search=\"{$searchValue}\"");
                return null;
            }

            if ($groups->count() === 1) {
                $this->lastMatchReason = "{$reason}=\"{$value}\" => matched=\"{$searchValue}\"";
                return $groups->first();
            }
        }

        $this->lastMatchReason = '';
        return null;
    }

    private function extractGroupCode(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = strtoupper(trim($value));

        // Ambil pola huruf di depan + 2 digit setelahnya
        // contoh:
        // HN1100159   -> HN11
        // KDT1105899  -> KDT11
        if (preg_match('/^([A-Z]+)(\d{2})/', $value, $matches)) {
            return $matches[1] . $matches[2];
        }

        return null;
    }
}
