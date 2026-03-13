<?php

namespace App\Http\Livewire\Hotspot;

use App\Models\HotspotServer;
use Livewire\Component;
use Livewire\WithPagination;

class HotspotServerIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public function delete(string $id): void
    {
        HotspotServer::findOrFail($id)->delete();
        session()->flash('message', 'Hotspot Server berhasil dihapus.');
    }

    public function render()
    {
        $servers = HotspotServer::with(['router', 'addressPool'])
            ->whereHas('router', fn($q) => $q->where('company_id', auth()->user()->company_id))
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->paginate(15);

        return view('livewire.hotspot-server.index', compact('servers'))
            ->extends('adminlte::page');
    }
}
