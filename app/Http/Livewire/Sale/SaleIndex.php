<?php

namespace App\Http\Livewire\Sale;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SettingCompany;
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
    public $filter_payment_method = '';

    // Temporary filters (user input, not yet applied)
    public $temp_search = '';
    public $temp_start_date = '';
    public $temp_end_date = '';
    public $temp_start_time = '';
    public $temp_end_time = '';
    public $temp_user_id = '';
    public $temp_payment_method = '';
    
    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['refreshSales' => '$refresh', 'deleteSale'];

    public function mount()
    {
        // Default filter ke user yang sedang login
        if (empty($this->filter_user_id)) {
            $this->filter_user_id = auth()->id();
            $this->temp_user_id   = auth()->id();
        }

        // Default filter tanggal hari ini
        if (empty($this->filter_start_date)) {
            $today = now()->toDateString();
            $this->filter_start_date = $today;
            $this->filter_end_date   = $today;
            $this->temp_start_date   = $today;
            $this->temp_end_date     = $today;
        }

        $this->temp_search         = $this->filter_search;
        $this->temp_start_date     = $this->filter_start_date;
        $this->temp_end_date       = $this->filter_end_date;
        $this->temp_start_time     = $this->filter_start_time;
        $this->temp_end_time       = $this->filter_end_time;
        $this->temp_payment_method = $this->filter_payment_method;
    }

    public function applyFilters()
    {
        $this->filter_search         = $this->temp_search;
        $this->filter_start_date     = $this->temp_start_date;
        $this->filter_end_date       = $this->temp_end_date;
        $this->filter_start_time     = $this->temp_start_time;
        $this->filter_end_time       = $this->temp_end_time;
        $this->filter_user_id        = $this->temp_user_id;
        $this->filter_payment_method = $this->temp_payment_method;

        $this->resetPage();
        $this->dispatchBrowserEvent('filters-applied');
    }

    public function applyFiltersFromInput(
        $search, $startDate, $endDate, $startTime, $endTime, $userId, $paymentMethod
    ) {
        $this->temp_search         = $search;
        $this->temp_start_date     = $startDate;
        $this->temp_end_date       = $endDate;
        $this->temp_start_time     = $startTime;
        $this->temp_end_time       = $endTime;
        $this->temp_user_id        = $userId;
        $this->temp_payment_method = $paymentMethod;

        $this->applyFilters();
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
        $this->temp_payment_method = '';

        $this->filter_search = '';
        $this->filter_start_date = '';
        $this->filter_end_date = '';
        $this->filter_start_time = '';
        $this->filter_end_time = '';
        $this->filter_user_id = '';
        $this->filter_payment_method = '';
        
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

        $settingCompany = SettingCompany::byCompany(auth()->user()->company_id)
            ->where('menu', 'store')
            ->get()
            ->pluck('field_value', 'field_title');

        $baseQuery = Sale::byCompany(auth()->user()->company_id)
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
            ->when($this->filter_payment_method, function ($query) {
                $query->where('payment_method', $this->filter_payment_method);
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
            });

        $totalFinalAmount = (clone $baseQuery)->sum('final_amount');

        $paymentBreakdown = null;
        if (!$this->filter_payment_method) {
            $breakdown = (clone $baseQuery)
                ->selectRaw('payment_method, SUM(final_amount) as total')
                ->groupBy('payment_method')
                ->pluck('total', 'payment_method');

            $paymentBreakdown = [
                'cash'         => $breakdown['cash'] ?? 0,
                'qris'         => $breakdown['qris'] ?? 0,
                'debit_credit' => $breakdown['debit_credit'] ?? 0,
            ];
        }

        $sales = $baseQuery->with(['user', 'items.productStore'])
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.sale.sale-index', compact('sales', 'users', 'totalFinalAmount', 'paymentBreakdown', 'settingCompany'))
            ->extends('adminlte::page');
    }
}