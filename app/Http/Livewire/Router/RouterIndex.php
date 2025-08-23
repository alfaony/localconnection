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
        Router::find($id)->delete();
        session()->flash('message', 'Router deleted successfully.');
    }

    public function render()
    {
        $mikrotiks = Router::where('company_id', auth()->user()->company_id)
            ->paginate(10);
            
        return view('livewire.router.router-index', compact('mikrotiks'))
            ->extends('adminlte::page');
    }
}