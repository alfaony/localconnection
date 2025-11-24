<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RouterOS\Query;

use App\Models\Router;
use App\Services\RouterOSService;
use App\Models\RouterInterface;
use App\Models\AddressPool;
use App\Models\InternetPackage;
use App\Models\PackageRouterProfile;
use App\Models\InternetCustomer;
use App\Jobs\SyncRouterInventoryJob;

class SyncRouterInventory extends Command
{
    protected $signature = 'sync:router {router_id}
                            {--profiles : Scan PPP profiles & auto-map ke packages}
                            {--secrets  : Rekonsiliasi PPP secrets ke internet_customers (meta saja)}
                            {--sessions : Tarik /ppp active untuk update ip/mac pelanggan}
                            {--pppoe    : Import daftar PPPoE server dari router}
                            {--ensureProfiles : Pastikan semua profiles ada di router}
                            '
                            ;

    protected $description = 'Sync interfaces, VLANs, IP pools, dan (opsional) profiles, secrets, sessions dari MikroTik ke DB';

    public function handle()
    {
        SyncRouterInventoryJob::dispatch($this->argument('router_id'), $this->option('profiles'), $this->option('secrets'), $this->option('sessions'), $this->option('pppoe'), $this->option('ensureProfiles'));
        $this->info('🎉 Sync Run for router');
    }
}