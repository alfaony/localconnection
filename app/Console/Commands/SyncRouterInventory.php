<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Router;
use App\Services\RouterOSService;
use RouterOS\Query;
use App\Models\RouterInterface;
use App\Models\AddressPool;
use Illuminate\Support\Str;

class SyncRouterInventory extends Command
{
    protected $signature = 'sync:router {router_id}';
    protected $description = 'Sync interfaces & IP pools from MikroTik';

    public function handle(RouterOSService $svc)
    {
        $router = Router::findOrFail($this->argument('router_id'));
        $c = $svc->client($router);


        // Interfaces
        $ifs = $c->query(new Query('/interface/print'))->read();
        foreach ($ifs as $row) {
            $name = $row['name'] ?? null;
            if (!$name) continue;

            RouterInterface::updateOrCreate(
                ['router_id' => $router->id, 'name' => $name],
                ['role' => 'access', 'meta' => $row]
            );
        }

        // VLANs (optional)
        $vlans = $c->query(new Query('/interface/vlan/print'))->read();
        foreach ($vlans as $v) {
            $name = $v['name'] ?? null;
            if (!$name) continue;

            RouterInterface::updateOrCreate(
                ['router_id'=>$router->id, 'name'=>$name],
                ['role'=>'access', 'vlan_id'=>(int)($v['vlan-id'] ?? 0), 'meta'=>$v]
            );
        }

        // IP Pools
        $pools = $c->query(new Query('/ip/pool/print'))->read();
        foreach ($pools as $p) {
            $id = AddressPool::firstOrCreate(
                ['name' => $p['name']],
                ['id' => (string) Str::uuid(), 'cidr' => $p['ranges'] ?? '', 'meta' => $p]
            )->id;
        }

        $this->info('Sync done for router '.$router->name);
    }
}