<?php

namespace App\Http\Livewire\Hotspot;

use App\Models\HotspotServer;
use App\Models\Router;
use App\Models\RouterInterface;
use App\Models\AddressPool;
use Livewire\Component;

class HotspotServerForm extends Component
{
    public ?string $serverId = null;
    public string  $router_id = '';
    public ?string $interface_id = null;
    public string  $name = '';
    public ?string $address_pool_id = null;
    public ?string $profile_name = null;
    public ?string $dns_name = null;

    protected function rules(): array
    {
        return [
            'router_id'       => 'required|exists:routers,id',
            'name'            => 'required|string|max:64',
            'interface_id'    => 'nullable|exists:router_interfaces,id',
            'address_pool_id' => 'nullable|exists:address_pools,id',
            'profile_name'    => 'nullable|string|max:64',
            'dns_name'        => 'nullable|string|max:128',
        ];
    }

    public function mount(?string $id = null): void
    {
        if ($id) {
            $server = HotspotServer::findOrFail($id);
            $this->serverId      = $server->id;
            $this->router_id     = $server->router_id;
            $this->interface_id  = $server->interface_id;
            $this->name          = $server->name;
            $this->address_pool_id = $server->address_pool_id;
            $this->profile_name  = $server->profile_name;
            $this->dns_name      = $server->dns_name;
        }
    }

    public function updatedRouterId(): void
    {
        $this->interface_id    = null;
        $this->address_pool_id = null;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'router_id'       => $this->router_id,
            'interface_id'    => $this->interface_id ?: null,
            'name'            => $this->name,
            'address_pool_id' => $this->address_pool_id ?: null,
            'profile_name'    => $this->profile_name ?: null,
            'dns_name'        => $this->dns_name ?: null,
        ];

        if ($this->serverId) {
            HotspotServer::findOrFail($this->serverId)->update($data);
            session()->flash('message', 'Hotspot Server berhasil diperbarui.');
        } else {
            HotspotServer::create($data);
            session()->flash('message', 'Hotspot Server berhasil ditambahkan.');
        }

        redirect()->route('hotspot-server.index');
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $routers    = Router::where('company_id', $companyId)->get();
        $interfaces = $this->router_id
            ? RouterInterface::where('router_id', $this->router_id)->get()
            : collect();
        $pools      = $this->router_id
            ? AddressPool::where('router_id', $this->router_id)->get()
            : collect();

        return view('livewire.hotspot-server.form', compact('routers', 'interfaces', 'pools'))
            ->extends('adminlte::page');
    }
}
