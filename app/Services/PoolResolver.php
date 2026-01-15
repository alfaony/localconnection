<?php

// app/Services/PoolResolver.php
namespace App\Services;

use App\Models\{InternetCustomer, Router, AddressPool, PppoeServer};

class PoolResolver
{
    public static function forCustomer(InternetCustomer $cust): ?AddressPool
    {
        // 1) per-customer override
        if ($cust->override_pool_id) return AddressPool::find($cust->override_pool_id);

        // 2) PPPoE server by router (+opsional by VLAN)
        $srv = PppoeServer::with('addressPool','interface')
            ->where('router_id', $cust->router_id)
            ->when($cust->vlan_id, fn($q) => $q->whereHas('interface', fn($i)=>$i->where('vlan_id',$cust->vlan_id)))
            ->first();
        if ($srv?->addressPool) return $srv->addressPool;

        // 3) router default
        $router = Router::with('defaultPool')->find($cust->router_id);
        if ($router?->defaultPool) return $router->defaultPool;

        // 4) hint dari paket (opsional, by name)
        $hint = $cust->internetPackage?->meta['pool_hint'] ?? null;
        if ($hint) return AddressPool::where('name',$hint)->first();

        return null;
    }

    public static function forRouter(Router $router): ?AddressPool
    {
        if ($router->defaultPool) return $router->defaultPool;
        $srv = PppoeServer::with('addressPool')->where('router_id',$router->id)->first();
        return $srv?->addressPool;
    }
}