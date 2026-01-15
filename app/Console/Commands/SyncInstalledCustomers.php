<?php

namespace App\Console\Commands;

use App\Jobs\SyncInstalledCustomersJob;
use Illuminate\Console\Command;

class SyncInstalledCustomers extends Command
{
    protected $signature = 'customers:sync-installed {--id=* : Batasi ke ID customer tertentu}';
    protected $description = 'Cek pelanggan berstatus INSTALLED dan aktifkan jika koneksi PPPoE sudah up.';

    public function handle(): int
    {
        $ids = $this->option('id') ?: null;

        SyncInstalledCustomersJob::dispatch($ids);

        $this->info('SyncInstalledCustomersJob dispatched' . ($ids ? (' for IDs: '.implode(',', $ids)) : ' (all).'));
        return self::SUCCESS;
    }
}