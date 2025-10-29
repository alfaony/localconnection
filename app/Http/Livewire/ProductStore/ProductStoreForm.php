<?php

namespace App\Http\Livewire\ProductStore;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ProductStore;
use App\Models\ProductStoreMedia;
use App\Models\CategoryProductStore;
use App\Models\BrandProductStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Warehouse;
use App\Models\Zone;
use App\Models\Rack;

class ProductStoreForm extends Component
{
    use WithFileUploads;

    public $productId;
    public $barcode;
    public $code;
    public $category_product_store_id;
    public $brand_product_store_id;
    public $name;
    public $variant;
    public $specification;
    public $length;
    public $width;
    public $height;
    public $weight;
    public $selling_price;
    public $createAgain = false;

    // Location properties
    public $warehouse_id;
    public $zone_id;
    public $rack_id;

    // Media properties - CHANGED APPROACH
    public $photo;
    public $photos = []; // Contains ONLY database records (saved files)
    public $pendingPhotos = []; // New photos not yet in database
    public $photoCaptions = [];
    public $photosToDelete = [];

    public $categories;
    public $brands;
    public $warehouses;
    public $zones;
    public $racks;
    
    protected $listeners = [
        'editProduct', 
        'createProduct',
        'updatePhotoOrder'
    ];

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
        
        if($id) {
            $this->editProduct($id);
        }
    }

    public function render()
    {
        if(!$this->barcode) {
            $this->barcode = $this->generateBarcode();
        }

        // Merge saved and pending photos for display
        $allPhotos = array_merge($this->photos, $this->pendingPhotos);
        
        return view('livewire.product-store.product-store-form', [
            'allPhotos' => $allPhotos
        ])->extends('adminlte::page');
    }

    public function editProduct($id)
    {
        $product = ProductStore::with('media')->findOrFail($id);
        
        $this->productId = $product->id;
        $this->barcode = $product->barcode;
        $this->code = $product->code;
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

        // Load existing photos
        $this->photos = $product->media->map(function($media) {
            return [
                'id' => $media->id,
                'file_path' => $media->file_path,
                'caption' => $media->caption,
                'order' => $media->order,
                'url' => Storage::disk('s3')->url($media->file_path),
                'is_saved' => true
            ];
        })->toArray();

        foreach ($this->photos as $photo) {
            $this->photoCaptions[$photo['id']] = $photo['caption'] ?? '';
        }

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
        } else {
            $this->zones = collect();
            $this->racks = collect();
        }
    }

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

    /**
     * SOLUSI BARU: Upload langsung ke folder permanent
     * Tidak ada temp folder, tidak ada copy/move
     */
    public function updatedPhoto()
    {
        // $this->validate([
        //     'photo' => 'required|image|max:5120',
        // ]);

        try {
            // Generate filename langsung untuk permanent storage
            $fileName = time() . '_' . uniqid() . '.' . $this->photo->getClientOriginalExtension();
            
            // Upload LANGSUNG ke folder permanent (bukan temp)
            $filePath = $this->photo->storeAs('product-store-media', $fileName, 's3');
            
            // Get URL
            $url = Storage::disk('s3')->url($filePath);

            // Add to pending photos (belum masuk database)
            $tempId = 'pending_' . uniqid();
            $this->pendingPhotos[] = [
                'id' => $tempId,
                'file_path' => $filePath,
                'caption' => null,
                'order' => count($this->photos) + count($this->pendingPhotos),
                'url' => $url,
                'is_saved' => false,
                'original_name' => $this->photo->getClientOriginalName()
            ];

            $this->photoCaptions[$tempId] = '';
            $this->photo = null;

            \Log::info("Photo uploaded directly to permanent storage: {$filePath}");
            $this->dispatchBrowserEvent('photo-uploaded', ['message' => 'Foto berhasil ditambahkan']);
            
        } catch (\Exception $e) {
            $this->addError('photo', 'Gagal mengupload foto: ' . $e->getMessage());
            \Log::error('Photo upload error: ' . $e->getMessage());
        }
    }

    public function deletePhoto($photoId)
    {
        // Check if it's a saved photo
        $photoKey = collect($this->photos)->search(fn($p) => $p['id'] == $photoId);
        
        if ($photoKey !== false) {
            // Mark saved photo for deletion
            $this->photosToDelete[] = $photoId;
            array_splice($this->photos, $photoKey, 1);
            unset($this->photoCaptions[$photoId]);
            return;
        }

        // Check if it's a pending photo
        $pendingKey = collect($this->pendingPhotos)->search(fn($p) => $p['id'] == $photoId);
        
        if ($pendingKey !== false) {
            $photo = $this->pendingPhotos[$pendingKey];
            
            // Delete file from S3 immediately
            try {
                Storage::disk('s3')->delete($photo['file_path']);
                \Log::info("Pending photo deleted from S3: {$photo['file_path']}");
            } catch (\Exception $e) {
                \Log::error("Failed to delete pending photo: " . $e->getMessage());
            }
            
            array_splice($this->pendingPhotos, $pendingKey, 1);
            unset($this->photoCaptions[$photoId]);
        }

        // Reorder
        $this->reorderPhotos();
    }

    public function updatePhotoOrder($orderedIds)
    {
        // Separate saved and pending IDs
        $savedIds = [];
        $pendingIds = [];
        
        foreach ($orderedIds as $id) {
            if (strpos($id, 'pending_') === 0) {
                $pendingIds[] = $id;
            } else {
                $savedIds[] = $id;
            }
        }

        // Reorder saved photos
        $reorderedSaved = [];
        foreach ($savedIds as $index => $id) {
            $photo = collect($this->photos)->firstWhere('id', $id);
            if ($photo) {
                $photo['order'] = $index;
                $reorderedSaved[] = $photo;
            }
        }
        $this->photos = $reorderedSaved;

        // Reorder pending photos
        $reorderedPending = [];
        foreach ($pendingIds as $index => $id) {
            $photo = collect($this->pendingPhotos)->firstWhere('id', $id);
            if ($photo) {
                $photo['order'] = count($savedIds) + $index;
                $reorderedPending[] = $photo;
            }
        }
        $this->pendingPhotos = $reorderedPending;
    }

    private function reorderPhotos()
    {
        // Reorder all photos
        foreach ($this->photos as $index => &$photo) {
            $photo['order'] = $index;
        }
        
        $startOrder = count($this->photos);
        foreach ($this->pendingPhotos as $index => &$photo) {
            $photo['order'] = $startOrder + $index;
        }
    }

    public function createProduct()
    {
        $this->barcode = $this->generateBarcode();
    }

    public function save()
    {
        $validated = $this->validate([
            'code' => 'nullable|string|max:255',
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
            'selling_price' => 'required|integer',
            'photoCaptions.*' => 'nullable|string|max:255'
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
            $product = ProductStore::create($data);
        }

        $this->handlePhotoDeletion();
        $this->savePhotos($product);

        if ($this->createAgain) {
            $this->resetForm();
            $this->emit('productCreated');
            return redirect()->route('product-store.create')->with('store', 'Produk berhasil disimpan.');
        } else {
            $this->resetForm();
            $this->emit('closeForm');
            return redirect()->route('product-store.index')->with('message', 'Produk berhasil disimpan.');
        }
    }

    private function handlePhotoDeletion()
    {
        if (!empty($this->photosToDelete)) {
            $mediaToDelete = ProductStoreMedia::whereIn('id', $this->photosToDelete)->get();
            
            foreach ($mediaToDelete as $media) {
                try {
                    Storage::disk('s3')->delete($media->file_path);
                    \Log::info("Deleted from S3: {$media->file_path}");
                } catch (\Exception $e) {
                    \Log::error('Failed to delete file from S3: ' . $e->getMessage());
                }
                
                $media->delete();
            }
            
            $this->photosToDelete = [];
        }
    }

    /**
     * SAVE PHOTOS - Simple version
     * File sudah ada di S3, tinggal buat record database
     */
    private function savePhotos($product)
    {
        // Update existing photos
        foreach ($this->photos as $photo) {
            $media = ProductStoreMedia::find($photo['id']);
            if ($media) {
                $media->update([
                    'order' => $photo['order'],
                    'caption' => $this->photoCaptions[$photo['id']] ?? null
                ]);
            }
        }

        // Save pending photos (file sudah ada di S3, tinggal buat record)
        foreach ($this->pendingPhotos as $photo) {
            try {
                ProductStoreMedia::create([
                    'product_store_id' => $product->id,
                    'file_path' => $photo['file_path'], // File sudah ada di permanent location
                    'order' => $photo['order'],
                    'caption' => $this->photoCaptions[$photo['id']] ?? null
                ]);
                
                \Log::info("Photo record created for: {$photo['file_path']}");
            } catch (\Exception $e) {
                \Log::error('Failed to create photo record: ' . $e->getMessage());
            }
        }

        // Clear pending photos after save
        $this->pendingPhotos = [];
    }

    private function resetForm()
    {
        // Clean up pending photos that weren't saved
        foreach ($this->pendingPhotos as $photo) {
            try {
                Storage::disk('s3')->delete($photo['file_path']);
                \Log::info("Cleanup pending photo: {$photo['file_path']}");
            } catch (\Exception $e) {
                \Log::error('Failed to cleanup pending photo: ' . $e->getMessage());
            }
        }

        $this->reset([
            'warehouse_id',
            'zone_id',
            'rack_id',
            'productId', 
            'barcode', 
            'category_product_store_id', 
            'brand_product_store_id',
            'name', 
            'variant', 
            'specification', 
            'length', 
            'width', 
            'height',
            'weight', 
            'selling_price',
            'photo',
            'photos',
            'pendingPhotos',
            'photoCaptions',
            'photosToDelete'
        ]);

        $this->zones = collect();
        $this->racks = collect();
    }

    public function cancel()
    {
        // Clean up pending photos
        foreach ($this->pendingPhotos as $photo) {
            try {
                Storage::disk('s3')->delete($photo['file_path']);
            } catch (\Exception $e) {
                \Log::error('Failed to cleanup on cancel: ' . $e->getMessage());
            }
        }

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