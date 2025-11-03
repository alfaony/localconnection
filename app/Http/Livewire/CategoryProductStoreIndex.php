<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\CategoryProductStore;
use App\Models\Company;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class CategoryProductStoreIndex extends Component
{
    public $companies;
    public $productSupplierTypeId;
    public $company_id;
    public $name;
    public $isEdit = false;
    public $showModal = false;
    public $theme = 'bootstrap';
    protected $rules = [
        'name' => 'required|string|max:255'
    ];

    public function mount()
    {
        $this->companies = Company::all();
    }

    public function render()
    {
        return view('livewire.category-product-store-index', [
            'productSupplierTypes' => CategoryProductStore::byCompany(Auth::user()->company_id)->paginate(10)
        ])->extends('adminlte::page');
    }

    public function resetInput()
    {
        $this->company_id = '';
        $this->name = '';
        $this->productSupplierTypeId = '';
        $this->isEdit = false;
        $this->showModal = false;
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetInput();
        $this->showModal = true;
    }

    public function store()
    {        
        $this->validate([
            'name' => 'required|string|max:255|unique:category_product_stores,name,NULL,id,company_id,' . Auth::user()->company_id. ',deleted_at,NULL'
        ]);

        CategoryProductStore::create([
            'company_id' => Auth::user()->company_id,
            'name' => $this->name
        ]);

        session()->flash('message', 'Kategori Produk Toko created successfully.');
        $this->resetInput();
    }

    public function edit($id)
    {
        $productSupplierType = CategoryProductStore::findOrFail($id);
        
        $this->productSupplierTypeId = $id;
        $this->company_id = $productSupplierType->company_id;
        $this->name = $productSupplierType->name;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:supplier_types,name,' . $this->productSupplierTypeId . ',id,company_id,' . Auth::user()->company_id . ',deleted_at,NULL'
        ]);

        if ($this->productSupplierTypeId) {
            $productSupplierType = CategoryProductStore::find($this->productSupplierTypeId);
            $productSupplierType->update([
                'company_id' => $this->company_id,
                'name' => $this->name
            ]);

            session()->flash('message', 'Kategori Produk Toko updated successfully.');
            $this->resetInput();
        }
    }

    public function delete($id)
    {
        CategoryProductStore::find($id)->delete();
        session()->flash('message', 'Kategori Produk Toko deleted successfully.');
    }

    public function cancel()
    {
        $this->resetInput();
    }
}

