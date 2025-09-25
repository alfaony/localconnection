<?php

namespace App\Http\Livewire\Sale;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;

class SaleIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['refreshSales' => '$refresh'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteSale($saleId)
    {
        try {
            DB::transaction(function () use ($saleId) {
                $sale = Sale::findOrFail($saleId);
                $sale->delete();
            });

            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Sale deleted successfully!'
            ]);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error deleting sale: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $sales = Sale::byCompany(auth()->user()->company_id)->with(['user', 'items.productStore'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('transaction_code', 'like', '%' . $this->search . '%')
                      ->orWhere('transaction_number', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_email', 'like', '%' . $this->search . '%')
                      ->orWhere('payment_method', 'like', '%' . $this->search . '%')
                      ->orWhere('status', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function ($userQuery) {
                          $userQuery->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.sale.sale-index', compact('sales'))->extends('adminlte::page');
    }
}