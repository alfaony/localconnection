<?php
namespace App\Http\Livewire\ProductStore;

use Livewire\Component;
use App\Models\ProductStore;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
use Milon\Barcode\Facades\DNS2DFacade as DNS2D;

class ProductStorePrint extends Component
{
    public $products;
    public $selectedProducts = [];
    public $barcodeType = 'QRCODE';
    public $width = 221;
    public $height = 95;
    public $paperSize = 'A4';
    public $barcodePreviews = [];
    public $copies = 1;

    protected $listeners = ['updatePreview', 'printBarcodes'];

    public function mount()
    {
        $this->products = ProductStore::with(['category', 'brand'])->get();
        $this->updatePreview();
    }

    public function updated()
    {
        $this->updatePreview();
    }

    public function updatePreview()
    {
        $this->barcodePreviews = [];

        $widthCode = (int) $this->width /80;
        $heightCode = (int) $this->height /40;
        $code = (int) $this->width /5;
        
        foreach ($this->selectedProducts as $productId) {
            $product = ProductStore::find($productId);
            if ($product) {
                if ($this->barcodeType === 'QRCODE') {
                    // kecil, biar gampang di-scale via CSS
                    $barcodeSvg = DNS2D::getBarcodeSVG($product->barcode, 'QRCODE', $widthCode, $heightCode);
                } else {
                    $barcodeType = $this->barcodeType === 'CODE128' ? 'C128' : 'C39';
                    $barcodeSvg = DNS1D::getBarcodeSVG($product->barcode, $barcodeType, $code - 5, $code);
                }

                // Inject properti preserveAspectRatio ke SVG supaya scalable
                $barcodeSvg = str_replace('<svg', '<svg preserveAspectRatio="xMidYMid meet"', $barcodeSvg);
                
                $this->barcodePreviews[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand ? $product->brand->name : '',
                    'variant' => $product->variant,
                    'barcode' => $product->barcode,
                    'price' => $product->selling_price,
                    'svg' => $barcodeSvg
                ];
            }
        }
    }

    public function printBarcodes()
    {
        $this->dispatchBrowserEvent('print-barcodes', ['paperSize' => $this->paperSize]);
    }

    public function render()
    {
        return view('livewire.product-store.product-store-print')
            ->extends('adminlte::page')
            ;
    }
}
