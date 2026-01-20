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
    
    // Active filters (used for querying)
    public $filter_search = '';
    public $filter_start_date = '';
    public $filter_end_date = '';
    public $filter_start_time = '';
    public $filter_end_time = '';
    public $filter_user_id = '';
    
    // Temporary filters (user input, not yet applied)
    public $temp_search = '';
    public $temp_start_date = '';
    public $temp_end_date = '';
    public $temp_start_time = '';
    public $temp_end_time = '';
    public $temp_user_id = '';
    
    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['refreshSales' => '$refresh', 'deleteSale'];

    public function mount()
    {
        // Initialize temp values with filter values on mount
        $this->temp_search = $this->filter_search;
        $this->temp_start_date = $this->filter_start_date;
        $this->temp_end_date = $this->filter_end_date;
        $this->temp_start_time = $this->filter_start_time;
        $this->temp_end_time = $this->filter_end_time;
        $this->temp_user_id = $this->filter_user_id;
    }

    public function applyFilters()
    {
        // Copy temp filters to active filters
        $this->filter_search = $this->temp_search;
        $this->filter_start_date = $this->temp_start_date;
        $this->filter_end_date = $this->temp_end_date;
        $this->filter_start_time = $this->temp_start_time;
        $this->filter_end_time = $this->temp_end_time;
        $this->filter_user_id = $this->temp_user_id;
        
        $this->resetPage();
        
        // Emit event to notify JavaScript that filters have been applied
        $this->dispatchBrowserEvent('filters-applied');
    }

    public function clearFilters()
    {
        // Clear both temp and active filters
        $this->temp_search = '';
        $this->temp_start_date = '';
        $this->temp_end_date = '';
        $this->temp_start_time = '';
        $this->temp_end_time = '';
        $this->temp_user_id = '';
        
        $this->filter_search = '';
        $this->filter_start_date = '';
        $this->filter_end_date = '';
        $this->filter_start_time = '';
        $this->filter_end_time = '';
        $this->filter_user_id = '';
        
        $this->resetPage();
        
        // Emit event to notify JavaScript that filters have been cleared
        $this->dispatchBrowserEvent('filters-cleared');
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
        if (!Access::can('destroy', 'sales')) {
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
        $users = \App\Models\User::byCompany(auth()->user()->company_id)->get();
        
        $sales = Sale::byCompany(auth()->user()->company_id)
            ->with(['user', 'items.productStore'])
            ->when($this->filter_search, function ($query) {
                $query->where(function ($q) {
                    $q->where('transaction_code', 'like', '%' . $this->filter_search . '%')
                      ->orWhere('transaction_number', 'like', '%' . $this->filter_search . '%')
                      ->orWhere('customer_email', 'like', '%' . $this->filter_search . '%')
                      ->orWhere('payment_method', 'like', '%' . $this->filter_search . '%')
                      ->orWhere('status', 'like', '%' . $this->filter_search . '%')
                      ->orWhereHas('user', function ($userQuery) {
                          $userQuery->where('name', 'like', '%' . $this->filter_search . '%');
                      });
                });
            })
            ->when($this->filter_start_date, function ($query) {
                $query->whereDate('created_at', '>=', $this->filter_start_date);
            })
            ->when($this->filter_end_date, function ($query) {
                $query->whereDate('created_at', '<=', $this->filter_end_date);
            })
            ->when($this->filter_start_time, function ($query) {
                $query->whereTime('created_at', '>=', $this->filter_start_time);
            })
            ->when($this->filter_end_time, function ($query) {
                $query->whereTime('created_at', '<=', $this->filter_end_time);
            })
            ->when($this->filter_user_id, function ($query) {
                $query->where('user_id', $this->filter_user_id);
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.sale.sale-index', compact('sales', 'users'))
            ->extends('adminlte::page');
    }
}