<?php

namespace App\Http\Livewire\Router;

use Livewire\Component;
use App\Models\{Router, RouterInterface, AddressPool, PppoeServer};
use App\Jobs\SyncRouterInventoryJob;

class RouterInventory extends Component
{
    public int $routerId;

    public function mount(int $routerId) { $this->routerId = $routerId; }

    public function render()
    {
        $router = Router::findOrFail($this->routerId);
        return view('livewire.router.router-inventory', [
            'router'     => $router,
            'interfaces' => RouterInterface::where('router_id',$router->id)->orderBy('name')->get(),
            'pools'      => AddressPool::orderBy('name')->get(), // pool bersifat global di skema kamu
            'pppoes'     => PppoeServer::where('router_id',$router->id)->orderBy('service_name')->get(),
        ])->extends('adminlte::page');
    }

    public function resyncLight()
    {
        dispatch(new SyncRouterInventoryJob($this->routerId, withProfiles:false, withSecrets:false, withSessions:false, withPppoe:true));
        $this->dispatchBrowserEvent('toast', ['type'=>'info','message'=>'Resync dispatched (interfaces, pools, pppoe)']);
    }

    public function resyncProfiles()
    {
        dispatch(new SyncRouterInventoryJob($this->routerId, withProfiles:true, ensureProfiles:true));
        $this->dispatchBrowserEvent('toast', ['type'=>'info','message'=>'Profile scan dispatched']);
    }

    public function resyncSecretsSessions()
    {
        dispatch(new SyncRouterInventoryJob($this->routerId, withProfiles:false, withSecrets:true, withSessions:true));
        $this->dispatchBrowserEvent('toast', ['type'=>'info','message'=>'Secrets & sessions sync dispatched']);
    }
}