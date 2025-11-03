<?php

namespace App\Http\Livewire;

use Illuminate\Support\Carbon;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\UsedLaptop;
use App\Models\Warehouse;
use App\Models\Zone;

use App\Helpers\Access;

class UsedLaptopTable extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap'; // ⬅️ INI YANG PENTING

    public $search = '';
    public $statusFilter = '';
    public $warehouseFilter = '';
    public $zoneFilter = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;
    
    // Form fields
    public $laptopId;
    public $name;
    public $processor;
    public $ram;
    public $ssd;
    public $gpu;
    public $operating_system;
    public $purchase_price;
    public $notes;
    public $is_sold = false;
    public $sold_price;
    public $sold_at;

    
    // Modal states
    public $showFormModal = false;
    public $showDeleteModal = false;
    public $showDetailModal = false;
    
    // Current laptop for detail view
    public $currentLaptop;
    
    // Validation rules
    protected $rules = [
        'name' => 'required|min:3',
        'processor' => 'required',
        'ram' => 'required',
        'ssd' => 'required',
        'purchase_price' => 'required|numeric|min:0',
        'is_sold' => 'boolean',
        'sold_price' => 'nullable|required_if:is_sold,true|numeric|min:0',
    ];

     protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'warehouseFilter' => ['except' => ''],
        'zoneFilter' => ['except' => ''],
    ];

    public function updatingWarehouseFilter()
    {
        $this->zoneFilter = ''; // Reset zone when warehouse changes
        $this->resetPage();
    }

    public function updatingZoneFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showFormModal = true;

        $this->emit('showFormModal'); // ✅ untuk v2
    }

    public function openEdit($slug)
    {
        return redirect()->route('used-laptop.edit', $slug);
    }

    public function openDetailModal($id)
    {
        $this->currentLaptop = UsedLaptop::with(['repairs', 'checks', 'media'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function openDeleteModal($id)
    {
        $this->laptopId = $id;
        $this->showDeleteModal = true;
    }

    public function saveLaptop()
    {
        $this->validate();
        
        $data = [
            'name' => $this->name,
            'processor' => $this->processor,
            'ram' => $this->ram,
            'ssd' => $this->ssd,
            'gpu' => $this->gpu,
            'operating_system' => $this->operating_system,
            'purchase_price' => $this->purchase_price,
            'notes' => $this->notes,
            'is_sold' => $this->is_sold,
            'sold_price' => $this->is_sold ? $this->sold_price : null,
            'sold_at' => $this->is_sold ? $this->sold_at : null,
            'user_id' => auth()->id(),
            'company_id' => auth()->user()->company_id,
        ];
        
        if ($this->laptopId) {
            UsedLaptop::find($this->laptopId)->update($data);
            session()->flash('success', 'Laptop updated successfully!');
        } else {
            UsedLaptop::create($data);
            session()->flash('success', 'Laptop created successfully!');
        }
        
        $this->showFormModal = false;
        $this->resetForm();
    }

    public function deleteLaptop()
    {
        UsedLaptop::find($this->laptopId)->delete();
        $this->showDeleteModal = false;
        session()->flash('success', 'Laptop deleted successfully!');
    }

    public function resetForm()
    {
        $this->reset([
            'laptopId', 
            'name', 
            'processor', 
            'ram', 
            'ssd', 
            'gpu', 
            'operating_system',
            'purchase_price',
            'notes',
            'is_sold',
            'sold_price',
            'sold_at'
        ]);
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->warehouseFilter = '';
        $this->zoneFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        
        $laptops = UsedLaptop::query()
        ->byCompany(auth()->user()->company_id)
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('processor', 'like', '%'.$this->search.'%')
                ->orWhere('ram', 'like', '%'.$this->search.'%')
                ->orWhere('ssd', 'like', '%'.$this->search.'%')
                ->orWhere('serial_number', 'like', '%'.$this->search.'%')
                ->orWhere('notes', 'like', '%'.$this->search.'%');
            });
        })
        ->when($this->statusFilter === 'sold', function ($query) {
            $query->where('is_sold', 1);
        })
        ->when($this->statusFilter === 'unsold', function ($query) {
            $query->where('is_sold', 0);
        })
        ->when($this->statusFilter === 'inventory', function ($query) {
            $query->where('is_sold', null);
        })
         ->when(!$this->statusFilter, function ($query) {
            $query->orderBy('is_sold'); // unsold dulu
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
        ->orderBy('created_at', 'desc') // terbaru di atas
        ->paginate($this->perPage);

        $warehouses = Warehouse::byCompany(auth()->user()->company_id)->select('id', 'name')->get();
        
        $zones = Zone::byCompany(auth()->user()->company_id)->when($this->warehouseFilter, function ($query) {
            $query->where('warehouse_id', $this->warehouseFilter);
        })
        ->select('id', 'name')
        ->get();

        $isShow = Access::can('show','used_laptops');
        $isEdit = Access::can('edit','used_laptops');
        $isDestroy = Access::can('destroy','used_laptops');

        return view('livewire.used-laptop-table', [
            'laptops' => $laptops,
            'warehouses' => $warehouses,
            'zones' => $zones,
            'isShow' => $isShow,
            'isEdit' => $isEdit,
            'isDestroy' => $isDestroy
        ]);
    }
}
