<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UsedLaptop;
use Illuminate\Support\Carbon;

class UsedLaptopTable extends Component
{
    use WithPagination;

    public $search = '';
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
        'sold_at' => 'nullable|required_if:is_sold,true|date',
    ];

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

    public function render()
    {
        $laptops = UsedLaptop::query()
            ->byCompany(auth()->user()->company_id)
            ->where('name', 'like', '%'.$this->search.'%')
            ->orWhere('processor', 'like', '%'.$this->search.'%')
            ->orWhere('ram', 'like', '%'.$this->search.'%')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.used-laptop-table', [
            'laptops' => $laptops,
        ]);
    }
}
