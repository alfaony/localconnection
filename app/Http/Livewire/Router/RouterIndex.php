<?php

namespace App\Http\Livewire\Router;

use App\Models\Router;
use Livewire\Component;
use Livewire\WithPagination;

class RouterIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

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