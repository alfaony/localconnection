<?php

namespace App\Http\Livewire\ProductStore;

use Livewire\Component;
use App\Models\ProductStore;
use App\Models\CategoryProductStore;
use App\Models\BrandProductStore;

class ProductStoreForm extends Component
{
    public $productId;
    public $barcode;
    public $category_product_store_id;
    public $brand_product_store_id;
    public $name;
    public $variant;
    public $specification;
    public $length;
    public $width;
    public $height;
    public $weight;
    public $selling_price_view;
    public $selling_price;

    public function updatedSellingPriceView($value)
    {
        $this->selling_price = preg_replace('/\D/', '', $value);
    }

    public $categories;
    public $brands;

    protected $listeners = ['editProduct', 'createProduct'];

    public function mount()
    {
        $this->categories = CategoryProductStore::all();
        $this->brands = BrandProductStore::all();
    }

    public function render()
    {
        return view('livewire.product-store.product-store-form')
            ->extends('adminlte::page')
            ->section('content');
    }

    public function editProduct($id)
    {
        $product = ProductStore::findOrFail($id);
        
        $this->productId = $product->id;
        $this->barcode = $product->barcode;
        $this->category_product_store_id = $product->category_product_store_id;
        $this->brand_product_store_id = $product->brand_product_store_id;
        $this->name = $product->name;
        $this->variant = $product->variant;
        $this->specification = $product->specification;
        $this->length = $product->length;
        $this->width = $product->width;
        $this->height = $product->height;
        $this->weight = $product->weight;
        $this->selling_price = $product->selling_price;
    }

    public function createProduct()
    {
        $this->resetForm();
        $this->barcode = (string) \Illuminate\Support\Str::uuid();
    }

    public function save()
    {
        $validated = $this->validate([
            'barcode' => 'nullable|string|max:255',
            'category_product_store_id' => 'required|exists:category_product_stores,id',
            'brand_product_store_id' => 'required|exists:brand_product_stores,id',
            'name' => 'required|string|max:255',
            'variant' => 'nullable|string|max:255',
            'specification' => 'nullable|string',
            'length' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'weight' => 'nullable|numeric',
            'selling_price' => 'required|integer'
        ]);

        $data = $validated;
        $data['dimension'] = $data['length'] . ' x ' . $data['width'] . ' x ' . $data['height'];
        
        if ($this->productId) {
            $product = ProductStore::find($this->productId);
            $product->update($data);
        } else {
            $data['user_create_id'] = auth()->id();
            $data['company_id'] = auth()->user()->company_id;
            ProductStore::create($data);
        }

        $this->emit('productSaved');
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'productId', 'barcode', 'category_product_store_id', 'brand_product_store_id',
            'name', 'variant', 'specification', 'length', 'width', 'height',
            'weight', 'selling_price'
        ]);
    }

    public function cancel()
    {
        $this->emit('closeForm');
        $this->resetForm();
    }
}
