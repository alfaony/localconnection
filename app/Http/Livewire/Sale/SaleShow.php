<?php

namespace App\Http\Livewire\Sale;

use Livewire\Component;
use App\Models\Sale;

class SaleShow extends Component
{
    public $sale;
    public $saleId;

    protected $listeners = ['refreshSale' => '$refresh'];

    public function mount($id)
    {
        $this->saleId = $id;
        $this->loadSale();
    }

    public function loadSale()
    {
        $this->sale = Sale::with([
            'user', 
            'saleItems.productStore.product'
        ])->findOrFail($this->saleId);
    }

    public function deleteSale()
    {
        try {
            $this->sale->delete();
            
            session()->flash('message', 'Sale deleted successfully!');
            return redirect()->route('sales.index');
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error deleting sale: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.sale.sale-show')->extends('adminlte::page');
    }
}