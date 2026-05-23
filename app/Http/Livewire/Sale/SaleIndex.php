<?php

namespace App\Http\Livewire\Sale;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ProductStore;
use App\Models\CategoryProductStore;
use App\Models\BrandProductStore;
use App\Models\SettingCompany;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Access;

class SaleIndex extends Component
{
    use WithPagination;

    public $activeTab = 'active';

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

    // Filters for deleted tab
    public $filter_deleted_search = '';
    public $filter_deleted_start_date = '';
    public $filter_deleted_end_date = '';
    public $filter_deleted_user_id = '';

    public $temp_deleted_search = '';
    public $temp_deleted_start_date = '';
    public $temp_deleted_end_date = '';
    public $temp_deleted_user_id = '';

    // ── Ringkasan Produk tab ─────────────────────────────────────────────────
    public $ring_search         = '';
    public $ring_start_date     = '';
    public $ring_end_date       = '';
    public $ring_start_time     = '';
    public $ring_end_time       = '';
    public $ring_user_id        = '';
    public $ring_payment_method = '';
    public $ring_category_id    = '';
    public $ring_brand_id       = '';
    public $ring_sort           = 'desc'; // 'desc' = terbanyak, 'asc' = tersedikit
    public $ring_type           = 'sold'; // 'sold' = terjual, 'unsold' = tidak terjual

    public $temp_ring_search         = '';
    public $temp_ring_start_date     = '';
    public $temp_ring_end_date       = '';
    public $temp_ring_start_time     = '';
    public $temp_ring_end_time       = '';
    public $temp_ring_user_id        = '';
    public $temp_ring_payment_method = '';
    public $temp_ring_category_id    = '';
    public $temp_ring_brand_id       = '';

    public $ringkasanLoaded = true;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['refreshSales' => '$refresh', 'deleteSale', 'restoreSale'];

    public function mount()
    {
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
        $this->temp_user_id        = $this->filter_user_id;
        $this->temp_payment_method = $this->filter_payment_method;
    }

    public function switchTab($tab)
    {
        if ($tab === 'deleted' && !Access::can('index_withdeleted', 'sales')) {
            return;
        }
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // ─── Filter tab aktif ─────────────────────────────────────────────────────

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
        $this->temp_search         = '';
        $this->temp_start_date     = '';
        $this->temp_end_date       = '';
        $this->temp_start_time     = '';
        $this->temp_end_time       = '';
        $this->temp_user_id        = '';
        $this->temp_payment_method = '';

        $this->filter_search         = '';
        $this->filter_start_date     = '';
        $this->filter_end_date       = '';
        $this->filter_start_time     = '';
        $this->filter_end_time       = '';
        $this->filter_user_id        = '';
        $this->filter_payment_method = '';

        $this->resetPage();
        $this->dispatchBrowserEvent('filters-cleared');
    }

    // ─── Filter tab deleted ───────────────────────────────────────────────────

    public function applyDeletedFilters()
    {
        $this->filter_deleted_search     = $this->temp_deleted_search;
        $this->filter_deleted_start_date = $this->temp_deleted_start_date;
        $this->filter_deleted_end_date   = $this->temp_deleted_end_date;
        $this->filter_deleted_user_id    = $this->temp_deleted_user_id;

        $this->resetPage();
        $this->dispatchBrowserEvent('deleted-filters-applied');
    }

    public function applyDeletedFiltersFromInput($search, $startDate, $endDate, $userId)
    {
        $this->temp_deleted_search     = $search;
        $this->temp_deleted_start_date = $startDate;
        $this->temp_deleted_end_date   = $endDate;
        $this->temp_deleted_user_id    = $userId;

        $this->applyDeletedFilters();
    }

    public function clearDeletedFilters()
    {
        $this->temp_deleted_search     = '';
        $this->temp_deleted_start_date = '';
        $this->temp_deleted_end_date   = '';
        $this->temp_deleted_user_id    = '';

        $this->filter_deleted_search     = '';
        $this->filter_deleted_start_date = '';
        $this->filter_deleted_end_date   = '';
        $this->filter_deleted_user_id    = '';

        $this->resetPage();
        $this->dispatchBrowserEvent('deleted-filters-cleared');
    }

    // ─── Aksi ─────────────────────────────────────────────────────────────────

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

    public function confirmRestore($saleId)
    {
        $this->dispatchBrowserEvent('confirm-restore', ['saleId' => $saleId]);
    }

    public function restoreSale($saleId)
    {
        if (!Access::can('index_withdeleted', 'sales')) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Anda tidak memiliki izin untuk memulihkan penjualan'
            ]);
            return;
        }

        try {
            DB::transaction(function () use ($saleId) {
                Sale::byCompany(auth()->user()->company_id)
                    ->onlyTrashed()
                    ->findOrFail($saleId)
                    ->restore();
            });

            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Penjualan berhasil dipulihkan!'
            ]);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error memulihkan penjualan: ' . $e->getMessage()
            ]);
        }
    }

    // ── Ringkasan Produk methods ─────────────────────────────────────────────

    public function applyRingkasanFilters(
        $search = '', $startDate = '', $endDate = '', $startTime = '', $endTime = '',
        $userId = '', $paymentMethod = '', $categoryId = '', $brandId = ''
    ) {
        $this->temp_ring_search         = $search;
        $this->temp_ring_start_date     = $startDate;
        $this->temp_ring_end_date       = $endDate;
        $this->temp_ring_start_time     = $startTime;
        $this->temp_ring_end_time       = $endTime;
        $this->temp_ring_user_id        = $userId;
        $this->temp_ring_payment_method = $paymentMethod;
        $this->temp_ring_category_id    = $categoryId;
        $this->temp_ring_brand_id       = $brandId;

        $this->ring_search         = $search;
        $this->ring_start_date     = $startDate;
        $this->ring_end_date       = $endDate;
        $this->ring_start_time     = $startTime;
        $this->ring_end_time       = $endTime;
        $this->ring_user_id        = $userId;
        $this->ring_payment_method = $paymentMethod;
        $this->ring_category_id    = $categoryId;
        $this->ring_brand_id       = $brandId;
        $this->ringkasanLoaded     = true;
    }

    public function clearRingkasanFilters()
    {
        $this->ring_search = $this->temp_ring_search = '';
        $this->ring_start_date = $this->temp_ring_start_date = '';
        $this->ring_end_date = $this->temp_ring_end_date = '';
        $this->ring_start_time = $this->temp_ring_start_time = '';
        $this->ring_end_time = $this->temp_ring_end_time = '';
        $this->ring_user_id = $this->temp_ring_user_id = '';
        $this->ring_payment_method = $this->temp_ring_payment_method = '';
        $this->ring_category_id = $this->temp_ring_category_id = '';
        $this->ring_brand_id = $this->temp_ring_brand_id = '';
        $this->ring_type = 'sold';
        $this->ringkasanLoaded = true;
        $this->dispatchBrowserEvent('ringkasan-filters-cleared');
    }

    public function toggleRingkasanSort()
    {
        $this->ring_sort = $this->ring_sort === 'desc' ? 'asc' : 'desc';
    }

    public function setRingType($type)
    {
        $this->ring_type = $type;
        $this->ringkasanLoaded = true;
    }

    private function saleSubQuery($q, $companyId)
    {
        $companyIds = auth()->user()->accessibleCompanies->pluck('id')->push($companyId)->unique();

        $q->where('status', 'completed')
          ->whereNull('deleted_at')
          ->when($this->ring_start_date,     fn ($q) => $q->whereDate('created_at', '>=', $this->ring_start_date))
          ->when($this->ring_end_date,       fn ($q) => $q->whereDate('created_at', '<=', $this->ring_end_date))
          ->when($this->ring_start_time,     fn ($q) => $q->whereTime('created_at', '>=', $this->ring_start_time))
          ->when($this->ring_end_time,       fn ($q) => $q->whereTime('created_at', '<=', $this->ring_end_time))
          ->when($this->ring_payment_method, fn ($q) => $q->where('payment_method', $this->ring_payment_method))
          ->when($this->ring_user_id,        fn ($q) => $q->where('user_id', $this->ring_user_id))
          ->whereHas('user', fn ($q) => $q->whereIn('company_id', $companyIds));
    }

    private function productStoreSubQuery($q)
    {
        $q->when($this->ring_search, function ($q) {
              $s = $this->ring_search;
              $q->where(fn ($i) => $i
                  ->where('name', 'like', "%{$s}%")
                  ->orWhere('variant', 'like', "%{$s}%")
                  ->orWhere('barcode', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
              );
          })
          ->when($this->ring_category_id, fn ($q) => $q->where('category_product_store_id', $this->ring_category_id))
          ->when($this->ring_brand_id,    fn ($q) => $q->where('brand_product_store_id', $this->ring_brand_id));
    }

    private function computeProductRingkasan()
    {
        $companyId = auth()->user()->company_id;

        return SaleItem::selectRaw('product_store_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->whereHas('sale', fn ($q) => $this->saleSubQuery($q, $companyId))
            ->when($this->ring_search || $this->ring_category_id || $this->ring_brand_id, function ($q) {
                $q->whereHas('productStore', fn ($ps) => $this->productStoreSubQuery($ps));
            })
            ->with(['productStore.category', 'productStore.brand'])
            ->groupBy('product_store_id')
            ->orderBy('total_qty', $this->ring_sort)
            ->paginate($this->perPage, ['*'], 'ring_page');
    }

    private function computeRingkasanByCategory()
    {
        $companyId = auth()->user()->company_id;

        return SaleItem::selectRaw('
                category_product_stores.name as category_name,
                SUM(sale_items.quantity) as total_qty
            ')
            ->join('product_stores', 'sale_items.product_store_id', '=', 'product_stores.id')
            ->leftJoin('category_product_stores', 'product_stores.category_product_store_id', '=', 'category_product_stores.id')
            ->whereHas('sale', fn ($q) => $this->saleSubQuery($q, $companyId))
            ->when($this->ring_search || $this->ring_category_id || $this->ring_brand_id, function ($q) {
                $q->where(function ($inner) {
                    $inner->when($this->ring_search, function ($q) {
                        $s = $this->ring_search;
                        $q->where(fn ($i) => $i
                            ->where('product_stores.name', 'like', "%{$s}%")
                            ->orWhere('product_stores.variant', 'like', "%{$s}%")
                            ->orWhere('product_stores.barcode', 'like', "%{$s}%")
                            ->orWhere('product_stores.code', 'like', "%{$s}%")
                        );
                    })
                    ->when($this->ring_category_id, fn ($q) => $q->where('product_stores.category_product_store_id', $this->ring_category_id))
                    ->when($this->ring_brand_id,    fn ($q) => $q->where('product_stores.brand_product_store_id', $this->ring_brand_id));
                });
            })
            ->groupBy('category_product_stores.id', 'category_product_stores.name')
            ->orderByDesc('total_qty')
            ->get();
    }

    private function computeProductTidakTerjual()
    {
        $companyId = auth()->user()->company_id;

        $soldIds = SaleItem::whereHas('sale', fn ($q) => $this->saleSubQuery($q, $companyId))
            ->pluck('product_store_id')
            ->unique();

        return ProductStore::byCompany($companyId)
            ->whereNotIn('id', $soldIds)
            ->when($this->ring_search || $this->ring_category_id || $this->ring_brand_id,
                fn ($q) => $this->productStoreSubQuery($q))
            ->with(['category', 'brand'])
            ->orderBy('name')
            ->paginate($this->perPage, ['*'], 'ring_page');
    }

    public function render()
    {
        $users = \App\Models\User::byCompany(auth()->user()->company_id)->get();

        $settingCompany = SettingCompany::byCompany(auth()->user()->company_id)
            ->where('menu', 'store')
            ->get()
            ->pluck('field_value', 'field_title');

        // Query tab aktif
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

        $totalSubAmount         = (clone $baseQuery)->sum('total_amount');
        $totalTaxAmount         = (clone $baseQuery)->sum('tax_amount');
        $totalDeduction         = (clone $baseQuery)->sum('cash_deduction');
        $totalBeforeDeduction   = $totalSubAmount + $totalTaxAmount;
        $totalFinalAmount       = (clone $baseQuery)->sum('final_amount');

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

        // Query tab deleted (hanya load jika punya permission)
        $deletedSales   = null;
        $deletedTotal   = 0;
        $canSeeDeleted  = Access::can('index_withdeleted', 'sales');
        $canSeeProductSummary  = Access::can('index_product_summary', 'sales');

        if ($canSeeDeleted) {
            $deletedQuery = Sale::byCompany(auth()->user()->company_id)
                ->onlyTrashed()
                ->when($this->filter_deleted_search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('transaction_code', 'like', '%' . $this->filter_deleted_search . '%')
                          ->orWhere('transaction_number', 'like', '%' . $this->filter_deleted_search . '%')
                          ->orWhere('customer_email', 'like', '%' . $this->filter_deleted_search . '%')
                          ->orWhere('status', 'like', '%' . $this->filter_deleted_search . '%')
                          ->orWhereHas('user', function ($userQuery) {
                              $userQuery->where('name', 'like', '%' . $this->filter_deleted_search . '%');
                          });
                    });
                })
                ->when($this->filter_deleted_start_date, function ($query) {
                    $query->whereDate('deleted_at', '>=', $this->filter_deleted_start_date);
                })
                ->when($this->filter_deleted_end_date, function ($query) {
                    $query->whereDate('deleted_at', '<=', $this->filter_deleted_end_date);
                })
                ->when($this->filter_deleted_user_id, function ($query) {
                    $query->where('user_id', $this->filter_deleted_user_id);
                });

            $deletedTotal = (clone $deletedQuery)->count();
            $deletedSales = $deletedQuery->with(['user', 'items.productStore'])
                ->latest('deleted_at')
                ->paginate($this->perPage, ['*'], 'deleted_page');
        }

        $categories = CategoryProductStore::byCompany(auth()->user()->company_id)->orderBy('name')->get();
        $brands     = BrandProductStore::byCompany(auth()->user()->company_id)->orderBy('name')->get();

        $productRingkasan = $this->ringkasanLoaded
            ? ($this->ring_type === 'unsold' ? $this->computeProductTidakTerjual() : $this->computeProductRingkasan())
            : collect();

        $ringkasanByCategory = ($this->ringkasanLoaded && $this->ring_type === 'sold')
            ? $this->computeRingkasanByCategory()
            : collect();

        return view('livewire.sale.sale-index', compact(
            'sales', 'users', 'totalFinalAmount', 'totalSubAmount', 'totalTaxAmount',
            'totalDeduction', 'totalBeforeDeduction',
            'paymentBreakdown', 'settingCompany',
            'deletedSales', 'deletedTotal', 'canSeeDeleted',
            'categories', 'brands', 'productRingkasan', 'ringkasanByCategory','canSeeProductSummary'
        ))->extends('adminlte::page');
    }
}
