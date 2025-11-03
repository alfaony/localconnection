<?php

namespace App\Http\Livewire\ProductStore;

use Livewire\Component;
use App\Models\ProductStore;
use App\Models\CategoryProductStore;
use App\Models\BrandProductStore;
use Illuminate\Support\Facades\Auth;
use App\Models\Warehouse;
use App\Models\Zone;
use App\Models\Rack;


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
    public $createAgain = false;

    // Location properties
    public $warehouse_id;
    public $zone_id;
    public $rack_id;

    public $categories;
    public $brands;
    public $warehouses;
    public $zones;
    public $racks;
    
    protected $listeners = ['editProduct', 'createProduct'];

    public function updatedSellingPriceView($value)
    {
        $this->selling_price = preg_replace('/\D/', '', $value);
    }
    

    public function mount($id = null)
    {
        $this->categories = CategoryProductStore::byCompany(Auth::user()->company_id)->get();
        $this->brands = BrandProductStore::byCompany(Auth::user()->company_id)->get();
        $this->warehouses = Warehouse::byCompany(Auth::user()->company_id)->get();
        $this->zones = collect();
        $this->racks = collect();
        if($id)
        {
            $this->editProduct($id);
        }
    }

    public function render()
    {
        if(!$this->barcode)
        {
            $this->barcode = $this->generateBarcode();
        }

        return view('livewire.product-store.product-store-form')
            ->extends('adminlte::page')
            // ->section('content')
            ;
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

        if ($product->rack) {
            $this->warehouse_id = $product->rack->zone->warehouse_id;
            $this->zone_id = $product->rack->zone_id;
            $this->rack_id = $product->rack_id;

            $this->zones = Zone::where('warehouse_id', $this->warehouse_id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
            $this->racks = Rack::where('zone_id', $this->zone_id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        }else 
        {
            $this->zones = collect();
            $this->racks = collect();
        }
    }

     // ============================================
    // WAREHOUSE CHANGED - Load Zones
    // ============================================
    public function updatedWarehouseId($value)
    {
        $this->zone_id = null;
        $this->rack_id = null;
        $this->zones = collect();
        $this->racks = collect();

        if ($value) {
            $this->zones = Zone::where('warehouse_id', $value)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        }
    }

    // ============================================
    // ZONE CHANGED - Load Racks
    // ============================================
    public function updatedZoneId($value)
    {
        $this->rack_id = null;
        $this->racks = collect();

        if ($value && $this->warehouse_id) {
            $this->racks = Rack::where('zone_id', $value)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        }
    }

    public function createProduct()
    {
        // $this->resetForm();
        $this->barcode = $this->generateBarcode();
    }

    public function save()
    {
        $validated = $this->validate([
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'zone_id' => 'nullable|exists:zones,id',
            'rack_id' => 'nullable|exists:racks,id',
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
            $data['user_modified_id'] = auth()->id();
            $product->update($data);
        } else {
            $data['user_create_id'] = auth()->id();
            $data['company_id'] = auth()->user()->company_id;
            ProductStore::create($data);
        }

        if ($this->createAgain) {

            $this->resetForm();
            $this->emit('closeForm');

            return redirect()->route('product-store.create')->with('store', 'Produk berhasil disimpan.');
        } else 
        {            
            $this->resetForm();
            $this->emit('closeForm');

            return redirect()->route('product-store.index')->with('message', 'Produk berhasil disimpan.');
        }
    }

    private function resetForm()
    {
        $this->reset([
            'warehouse_id',
            'zone_id',
            'rack_id',
            'productId', 'barcode', 'category_product_store_id', 'brand_product_store_id',
            'name', 'variant', 'specification', 'length', 'width', 'height',
            'weight', 'selling_price'
        ]);

        $this->zones = collect();
        $this->racks = collect();
    }

    public function cancel()
    {
        $this->emit('closeForm');
        $this->resetForm();
    }

    protected static function generateBarcode()
    {
        do {
            $barcode = now()->format('Y') . str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
        } while (ProductStore::withTrashed()->where('barcode', $barcode)->exists());

        return $barcode;
    }
}
