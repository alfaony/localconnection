<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UsedItem;
use App\Models\User;

use Illuminate\Support\Carbon;

class UsedItemTable extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap'; // ⬅️ INI YANG PENTING

    public $search = '';
    public $statusFilter = '';
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

        return view('livewire.used-item-table', [
            'items' => $items,
            'users' => $users, // Kirim data user ke view
        ]);
    }
}
