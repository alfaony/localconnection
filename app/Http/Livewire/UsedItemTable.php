<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UsedItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Zone;

use Illuminate\Support\Carbon;
use App\Helpers\Access;

class UsedItemTable extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap'; // ⬅️ INI YANG PENTING

    public $search = '';
    public $statusFilter = '';
    public $warehouseFilter = '';
    public $zoneFilter = '';
    public $userFilter = ''; // Tambahkan filter user
    public $startDate = ''; // Tambahkan filter tanggal mulai
    public $endDate = ''; // Tambahkan filter tanggal akhir
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    
    
    // Modal states
    public $showFormModal = false;
    public $showDeleteModal = false;
    public $showDetailModal = false;
    
    // Current laptop for detail view
    public $currentLaptop;

     protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'warehouseFilter' => ['except' => ''],
        'zoneFilter' => ['except' => ''],
        'userFilter' => ['except' => ''],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingWarehouseFilter()
    {
        $this->zoneFilter = ''; // Reset zone when warehouse changes
        $this->resetPage();
    }

    public function updatingZoneFilter()
    {
        $this->resetPage();
    }

    public function updatingUserFilter()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
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

    public function resetDateFilter()
    {
        $this->startDate = '';
        $this->endDate = '';
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->warehouseFilter = '';
        $this->zoneFilter = '';
        $this->userFilter = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->resetPage();
    }
    
    public function updatedStartDate($value)
    {
        if($this->endDate < $value || !$this->endDate)
        {
            $this->endDate = $value;   
        }
    }

     public function render()
    {
        $items = UsedItem::query()
            ->byCompany(auth()->user()->company_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('serial_number', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter === 'sold', function ($query) {
                $query->where('is_sold', 1);
            })
            ->when($this->statusFilter === 'unsold', function ($query) {
                $query->where('is_sold', 0);
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
            ->when($this->userFilter, function ($query) { // Filter user
                $query->where('user_id', $this->userFilter);
            })
            ->when($this->startDate || $this->endDate, function ($query) { // Filter tanggal
                if ($this->startDate) {
                    $query->where('created_at', '>=', Carbon::parse($this->startDate)->startOfDay());
                }
                if ($this->endDate) {
                    $query->where('created_at', '<=', Carbon::parse($this->endDate)->endOfDay());
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Ambil user untuk dropdown filter
        $users = User::whereHas('usedItems', function ($query) {
                $query->byCompany(auth()->user()->company_id);
            })
            ->orderBy('name')
            ->get();

        $warehouses = Warehouse::byCompany(auth()->user()->company_id)->select('id', 'name')->get();
        
        $zones = Zone::byCompany(auth()->user()->company_id)->when($this->warehouseFilter, function ($query) {
            $query->where('warehouse_id', $this->warehouseFilter);
        })
        ->select('id', 'name')
        ->get();

        $isShow = Access::can('show','used_items');
        $isEdit = Access::can('edit','used_items');
        $isDestroy = Access::can('destroy','used_items');

        return view('livewire.used-item-table', [
            'items' => $items,
            'users' => $users, // Kirim data user ke view
            'warehouses' => $warehouses,
            'zones' => $zones,
            'isShow' => $isShow,
            'isEdit' => $isEdit,
            'isDestroy' => $isDestroy
        ]);
    }
}
