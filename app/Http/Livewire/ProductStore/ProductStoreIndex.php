<?php

namespace App\Http\Livewire\ProductStore;

use Livewire\Component;
use Livewire\WithPagination;

use App\Helpers\Access;

use App\Models\ProductStore;
use App\Models\CategoryProductStore;
use App\Models\BrandProductStore;
use App\Models\Warehouse;
use App\Models\Zone;
use App\Models\Product;

class ProductStoreIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $warehouseFilter = '';
    public $zoneFilter = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $selectedProductId;
    public $showFormModal = false;
    public $category_product_store_id;
    public $brand_product_store_id;
    protected $paginationTheme = 'bootstrap';

      // Collections
    public $categories;
    public $warehouses;
    public $zones;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'warehouseFilter' => ['except' => ''],
        'zoneFilter' => ['except' => ''],
    ];

    protected $listeners = ['productSaved', 'closeForm','deleteProduct'];


    public function mount()
    {
        $this->categories = CategoryProductStore::byCompany(auth()->user()->company_id)->get();
        $this->warehouses = Warehouse::byCompany(auth()->user()->company_id)->get();
        $this->zones = collect();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingWarehouseFilter()
    {
        $this->zoneFilter = '';
        $this->resetPage();
    }

    public function updatingZoneFilter()
    {
        $this->resetPage();
    }

    public function updatedWarehouseFilter($value)
    {
        $this->zones = collect();
        
        if ($value) {
            $this->zones = Zone::where('warehouse_id', $value)
                ->select('id', 'name')
                ->get();
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->warehouseFilter = '';
        $this->zoneFilter = '';
        $this->zones = collect();
        $this->resetPage();
    }

    public function render()
    {
        $products = ProductStore::with(['category', 'brand'])
            ->search($this->search)
             ->when($this->categoryFilter, function ($query) {
                $query->where('category_product_store_id', $this->categoryFilter);
            })
            ->when($this->warehouseFilter, function ($query) {
                $query->whereHas('rack.zone.warehouse', function ($q) {
                    $q->where('id', $this->warehouseFilter);
                });
            })
            ->when($this->zoneFilter, function ($query) {
                $query->whereHas('rack.zone', function ($q) {
                    $q->where('id', $this->zoneFilter);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

            // permission
            $isShow = Access::can('show','product_stores');
            $isEdit = Access::can('edit','product_stores');
            $isDestroy = Access::can('destroy','product_stores');

        return view('livewire.product-store.product-store-index', compact('products','isShow','isEdit','isDestroy'))
            ->extends('adminlte::page')
            // ->section('content')
            ;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function editProduct($id)
    {
        $this->selectedProductId = $id;
        $this->showFormModal = true;
        $this->emit('editProduct', $id);
    }

    public function createProduct()
    {
        $this->selectedProductId = null;
        $this->showFormModal = true;
        $this->emit('createProduct');
    }

    public function confirmDelete($id)
    {
        $this->dispatchBrowserEvent('swal:confirm', [
            'type' => 'warning',
            'title' => 'Are you sure?',
            'text' => 'You won\'t be able to revert this!',
            'id' => $id
        ]);
    }

    public function deleteProduct($id)
    {
        ProductStore::find($id)->delete();
        session()->flash('message', 'Product deleted successfully.');
    }

    public function productSaved()
    {
        $this->showFormModal = false;
        session()->flash('message', 'Product saved successfully.');
    }

    public function closeForm()
    {
        $this->showFormModal = false;
    }
}
