<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\BrandProductStore;
use App\Models\Company;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class BrandProductStoreIndex extends Component
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
        return view('livewire.brand-product-store-index', [
            'productSupplierTypes' => BrandProductStore::byCompany(Auth::user()->company_id)->paginate(10)
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
            'name' => 'required|string|max:255|unique:brand_product_stores,name,NULL,id,company_id,' . Auth::user()->company_id . ',deleted_at,NULL'
        ]);

        BrandProductStore::create([
            'company_id' => Auth::user()->company_id,
            'name' => $this->name
        ]);

        session()->flash('message', 'Merk Toko Produk created successfully.');
        $this->resetInput();
    }

    public function edit($id)
    {
        $productSupplierType = BrandProductStore::findOrFail($id);
        
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
            $productSupplierType = BrandProductStore::find($this->productSupplierTypeId);
            $productSupplierType->update([
                'company_id' => $this->company_id,
                'name' => $this->name
            ]);

            session()->flash('message', 'Merk Toko Produk updated successfully.');
            $this->resetInput();
        }
    }

    public function delete($id)
    {
        BrandProductStore::find($id)->delete();
        session()->flash('message', 'Merk Toko Produk deleted successfully.');
    }

    public function cancel()
    {
        $this->resetInput();
    }
}
