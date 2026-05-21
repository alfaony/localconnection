<?php

namespace App\Http\Livewire\ProductStore;

use Livewire\Component;
use App\Models\ProductStore;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
use Milon\Barcode\Facades\DNS2DFacade as DNS2D;

class ProductStoreShow extends Component
{
    public $product;
    public $barcode1Svg;
    public $barcode2Svg;
    public $qrCodeSvg;

    public $page;
    public $search;
    public $categoryFilter;
    public $warehouseFilter;
    public $zoneFilter;


    public function mount($id)
    {
        $this->product = ProductStore::with(['category', 'brand', 'creator', 'modifier', 'company'])
            ->findOrFail($id);

        $this->page = request()->query('page', 1);
        $this->search = request()->query('search', '');
        $this->categoryFilter = request()->query('categoryFilter', '');
        $this->warehouseFilter = request()->query('warehouseFilter', '');
        $this->zoneFilter = request()->query('zoneFilter', '');
            
        // Generate barcode 1 (Code 128)
        $this->barcode1Svg = DNS1D::getBarcodeSVG($this->product->barcode, 'C128', 2, 60);
        
        // Generate barcode 2 (Code 39)
        $this->barcode2Svg = DNS1D::getBarcodeSVG($this->product->barcode, 'C39', 2, 60);
        
        // Generate QR Code
        $this->qrCodeSvg = DNS2D::getBarcodeSVG($this->product->barcode, 'QRCODE', 6, 6);
    }

     public function printBarcodes()
    {
        $this->dispatchBrowserEvent('print-barcodes');
    }

    public function render()
    {
        return view('livewire.product-store.product-store-show')
            ->extends('adminlte::page');
    }
}