<?php

namespace App\Http\Livewire\ProductStore;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProductStore;

class ProductStoreIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $selectedProductId;
    public $showFormModal = false;

    protected $listeners = ['productSaved', 'closeForm','deleteProduct'];

    public function render()
    {
        $products = ProductStore::with(['category', 'brand'])
            ->search($this->search)
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.product-store.product-store-index', compact('products'))
            ->extends('adminlte::page')
            ->section('content');
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

    public function updatingSearch()
    {
        $this->resetPage();
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
