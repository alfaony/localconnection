<?php

namespace App\Http\Livewire\Router;

use App\Jobs\RouterRefreshJob;
use App\Models\InternetCustomer;
use App\Models\Router;
use App\Schemas\ParamSchema;
use Livewire\Component;
use Livewire\WithPagination;

class RouterIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function refreshStatus(int $id): void
    {
        $router = Router::findOrFail($id);

        // Hitung pelanggan pending (DB saja, tidak ada koneksi ke router)
        $pendingCount = InternetCustomer::where('router_id', $id)
            ->whereIn('status', [
                ParamSchema::INSTALLED,
                ParamSchema::REACTIVATED,
                ParamSchema::DISCONNECTED,
            ])
            ->whereNotNull('username')
            ->count();

        // afterResponse() → job jalan SETELAH response dikirim ke browser
        // sehingga button loading selesai segera, meski QUEUE_CONNECTION=sync
        dispatch(new RouterRefreshJob($id))->afterResponse();

        $message = $pendingCount > 0
            ? "Refresh router {$router->name} dijadwalkan. {$pendingCount} pelanggan (installed/reactivated/disconnected) akan di-cek."
            : "Refresh router {$router->name} dijadwalkan. Tidak ada pelanggan pending.";

        $this->dispatchBrowserEvent('toast', [
            'type'    => 'info',
            'message' => $message,
        ]);
    }

    public function delete($id)
    {
        $router = Router::withCount('internetCustomers')->findOrFail($id);

        if ($router->internet_customers_count > 0) {
            session()->flash('error', "Router \"{$router->name}\" tidak dapat dihapus karena masih memiliki {$router->internet_customers_count} pelanggan.");
            return;
        }

        $router->delete();
        session()->flash('message', 'Router berhasil dihapus.');
    }

    public function render()
    {
        $mikrotiks = Router::where('company_id', auth()->user()->company_id)
            ->withCount('internetCustomers')
            ->paginate(10);

        return view('livewire.router.router-index', compact('mikrotiks'))
            ->extends('adminlte::page');
    }
}