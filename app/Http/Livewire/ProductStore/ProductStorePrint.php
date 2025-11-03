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
    
    // ✅ Copy per produk dengan array
    public $productCopies = [];
    
    // ✅ Custom paper size
    public $customPaperWidth = 210;  // mm (default A4 width)
    public $customPaperHeight = 297; // mm (default A4 height)
    public $useCustomSize = false;

    protected $listeners = ['updatePreview', 'printBarcodes'];

    public function mount()
    {
        $this->products = ProductStore::with(['category', 'brand'])->get();
        $this->updatePreview();
    }

    public function updated($propertyName)
    {
        // Update preview when custom size changes
        if (in_array($propertyName, ['customPaperWidth', 'customPaperHeight', 'useCustomSize', 'paperSize'])) {
            $this->updatePreview();
        }
    }

    public function updatePreview()
    {
        $this->barcodePreviews = [];

        $widthCode = (int) $this->width / 80;
        $heightCode = (int) $this->height / 40;
        $code = (int) $this->width / 5;
        
        foreach ($this->selectedProducts as $productId) {
            $product = ProductStore::find($productId);
            if ($product) {
                // ✅ Set default copy jika belum ada
                if (!isset($this->productCopies[$productId])) {
                    $this->productCopies[$productId] = 1;
                }

                if ($this->barcodeType === 'QRCODE') {
                    $barcodeSvg = DNS2D::getBarcodeSVG($product->barcode, 'QRCODE', $widthCode, $heightCode);
                } else {
                    $barcodeType = $this->barcodeType === 'CODE128' ? 'C128' : 'C39';
                    $barcodeSvg = DNS1D::getBarcodeSVG($product->barcode, $barcodeType, 1, $code);

                    // Hapus semua <text> bawaan
                    $barcodeSvg = preg_replace('/<text.*?<\/text>/', '', $barcodeSvg);

                    // Sisipkan text baru
                    $barcodeText = '<text x="' . ($this->barcodeType === 'CODE128' ? '61.5' : '120') . '" text-anchor="middle" y="44.2" id="code" fill="black" font-size="12px">' . $product->barcode . '</text>';
                    $barcodeSvg = str_replace('</svg>', $barcodeText . '</svg>', $barcodeSvg);
                }
                
                $this->barcodePreviews[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'brand' => $product->brand ? $product->brand->name : '',
                    'variant' => $product->variant,
                    'barcode' => $product->barcode,
                    'price' => $product->selling_price,
                    'svg' => $barcodeSvg,
                    // ✅ Tambahkan copies per produk
                    'copies' => $this->productCopies[$productId] ?? 1
                ];
            }
        }
    }

    // ✅ Method untuk update copy individual
    public function updateProductCopy($productId, $copies)
    {
        $this->productCopies[$productId] = max(1, min(100, (int)$copies));
        $this->updatePreview();
    }

    public function printBarcodes()
    {
        $paperConfig = [
            'paperSize' => $this->paperSize,
            'useCustomSize' => $this->useCustomSize,
            'customWidth' => $this->customPaperWidth,
            'customHeight' => $this->customPaperHeight
        ];
        
        $this->dispatchBrowserEvent('print-barcodes', $paperConfig);
    }

    public function render()
    {
        return view('livewire.product-store.product-store-print')
            ->extends('adminlte::page');
    }
}