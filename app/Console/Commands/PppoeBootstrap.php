<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RouterOS\Client;
use RouterOS\Query;
use App\Models\Router;
use App\Models\PppoeServer;

class PppoeBootstrap extends Command
{
    protected $signature = 'pppoe:bootstrap {router_id}';
    protected $description = 'Aktifkan PPPoE server di MikroTik sesuai record pppoe_servers';

    public function handle()
    {
        $router = Router::findOrFail($this->argument('router_id'));
        /** @var PppoeServer $srv */
        $srv = PppoeServer::where('router_id',$router->id)->firstOrFail();

        $c = new Client([
            'host'=>$router->host,'user'=>$router->username,'pass'=>$router->password,
            'port'=>(int)$router->port,'ssl'=>(bool)$router->ssl,'timeout'=>5
        ]);

        // pastikan server ada (idempotent)
        $exist = $c->query((new Query('/interface/pppoe-server/server/print'))
                 ->where('service-name',$srv->service_name))->read();

        if (empty($exist)) {
            $c->query((new Query('/interface/pppoe-server/server/add'))
                ->equal('service-name',$srv->service_name)
                ->equal('interface',$srv->interface->name)
                ->equal('authentication','pap,chap,mschap1,mschap2')
                ->equal('one-session-per-host', $srv->only_one ? 'yes':'no')
            )->read();
            $this->info("PPPoE server {$srv->service_name} dibuat.");
        } else {
            $this->info("PPPoE server {$srv->service_name} sudah ada.");
        }

        // set pool (remote-address) jika ada
        if ($srv->addressPool) {
            $c->query((new Query('/interface/pppoe-server/server/set'))
                ->equal('numbers',$srv->service_name)
                ->equal('remote-address',$srv->addressPool->name)
            )->read();
            $this->info("Remote-address di-set ke pool {$srv->addressPool->name}.");
        }

        $this->info('Bootstrap PPPoE selesai.');
    }
}