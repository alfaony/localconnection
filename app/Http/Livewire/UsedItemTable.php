<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UsedItem;
use Illuminate\Support\Carbon;

class UsedItemTable extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
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

    public function render()
    {
        
        $items = UsedItem::query()
        ->byCompany(auth()->user()->company_id)
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('notes', 'like', '%'.$this->search.'%');
            });
        })
        ->when($this->statusFilter === 'sold', function ($query) {
            $query->where('is_sold', 1);
        })
        ->when($this->statusFilter === 'unsold', function ($query) {
            $query->where('is_sold', 0);
        })
         ->when(!$this->statusFilter, function ($query) {
            $query->orderBy('is_sold'); // unsold dulu
        })
        ->orderBy('created_at', 'desc') // terbaru di atas
        ->paginate($this->perPage);

        return view('livewire.used-item-table', [
            'items' => $items,
        ]);
    }
}
