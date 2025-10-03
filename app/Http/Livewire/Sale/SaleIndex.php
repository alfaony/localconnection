<?php

namespace App\Http\Livewire\Sale;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Access;
class SaleIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['refreshSales' => '$refresh','deleteSale'];


    public function updatingSearch()
    {
        $this->resetPage();
    }


    /**
     * Dispatch a browser event to confirm deletion of a sale
     *
     * @param int $saleId The ID of the sale to be deleted
     */
    public function confirmDelete($saleId)
    {
        $this->dispatchBrowserEvent('confirm-delete', ['saleId' => $saleId]);
    }

    public function deleteSale($saleId)
    {
        if (!Access::can('destroy','sales')) 
        {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Anda tidak memiliki izin untuk menghapus penjualan'
            ]);
            return;
        }

        try {
            DB::transaction(function () use ($saleId) {
                $sale = Sale::byCompany(auth()->user()->company_id)->findOrFail($saleId);
                $sale->delete();
            });

            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Penjualan berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error menghapus penjualan: ' . $e->getMessage()
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