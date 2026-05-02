<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RouterOS\Client;
use RouterOS\Query;
use App\Models\Router;
use App\Models\InternetCustomer;
use App\Schemas\ParamSchema;
use Illuminate\Foundation\Bus\Dispatchable;


class SyncActiveSessionsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    public function __construct(public int $routerId) {}

    public function handle(): void
    {
        $router = Router::findOrFail($this->routerId);
        $c = new Client([
            'host'=>$router->host,'user'=>$router->username,'pass'=>$router->password,
            'port'=>(int)$router->port,'ssl'=>(bool)$router->ssl,'timeout'=>5
        ]);

        // Ambil semua sesi PPP aktif
        $actives = $c->query(new Query('/ppp/active/print'))->read(); // name, address, caller-id
        foreach ($actives as $row) {
            $user = $row['name'] ?? null;
            if (!$user) continue;

            $cust = InternetCustomer::where('router_id',$router->id)
                     ->where('username',$user)->first();
            if (!$cust) continue;
            
            if($cust->isActiveConneciton())
            {
                $cust->update([
                    'ip_address' => $row['address']    ?? $cust->ip_address,
                    'mac_address'=> $row['caller-id']  ?? $cust->mac_address,
                ]);
            }else
            {
                $cust->update([
                    'status'     => ParamSchema::DISCONNECTED,
                    'ip_address' => null,
                    'mac_address'=> null,
                ]);
            }
        }
    }
}