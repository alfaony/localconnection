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

    public $warehouse_id;
    public $zone_id;
    public $rack_id;

    public $photo;
    public $photos = [];
    public $pendingPhotos = [];
    public $photoCaptions = [];
    public $photosToDelete = [];
    
    // Upload queue tracking
    public $uploadingCount = 0;
    public $uploadedCount = 0;
    public $isUploading = false;

    public $categories;
    public $brands;
    public $warehouses;
    public $zones;
    public $racks;
    
    protected $listeners = ['editProduct', 'createProduct', 'updatePhotoOrder'];

    public function mount($id = null)
    {
        $this->categories = CategoryProductStore::byCompany(Auth::user()->company_id)->get();
        $this->brands = BrandProductStore::byCompany(Auth::user()->company_id)->get();
        $this->warehouses = Warehouse::byCompany(Auth::user()->company_id)->get();
        $this->zones = collect();
        $this->racks = collect();
        
        if($id) {
            $this->editProduct($id);
        } else {
            // Auto-generate barcode only for new products
            $this->barcode = $this->generateBarcode();
        }
    }

    public function render()
    {
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
            $this->zones = Zone::where('warehouse_id', $this->warehouse_id)->select('id', 'name')->orderBy('name')->get();
            $this->racks = Rack::where('zone_id', $this->zone_id)->select('id', 'name')->orderBy('name')->get();
        }
    }

    public function updatedWarehouseId($value)
    {
        $this->zone_id = null;
        $this->rack_id = null;
        $this->zones = collect();
        $this->racks = collect();
        if ($value) {
            $this->zones = Zone::where('warehouse_id', $value)->select('id', 'name')->orderBy('name')->get();
        }
    }

    public function updatedZoneId($value)
    {
        $this->rack_id = null;
        $this->racks = collect();
        if ($value && $this->warehouse_id) {
            $this->racks = Rack::where('zone_id', $value)->select('id', 'name')->orderBy('name')->get();
        }
    }

    public function updatedPhoto()
    {
        // Validate first
        // $this->validate([
        //     'photo' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        // ], [
        //     'photo.required' => 'Foto harus dipilih',
        //     'photo.image' => 'File harus berupa gambar',
        //     'photo.mimes' => 'Format foto harus: jpeg, jpg, png, gif, atau webp',
        //     'photo.max' => 'Ukuran foto maksimal 5MB'
        // ]);

        // PENTING: Deklarasi variabel di awal
        $maxRetries = 5;
        $retryDelay = 1; // seconds
        $uploaded = false;
        $filePath = null;
        
        // Generate filename SEKALI di awal, jangan di-reset
        $fileName = time() . '_' . uniqid() . '.' . $this->photo->getClientOriginalExtension();

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                \Log::info("Photo upload attempt {$attempt}/{$maxRetries} - File: {$fileName}");

                // Upload to S3 with timeout
                $filePath = $this->photo->storeAs(
                    'product-store-media', 
                    $fileName, 
                    's3',
                    'public'
                );

                // Verify upload was successful by checking if file exists
                if (!Storage::disk('s3')->exists($filePath)) {
                    throw new \Exception('File uploaded but not found in S3');
                }

                // Verify file size matches
                $s3Size = Storage::disk('s3')->size($filePath);
                $localSize = $this->photo->getSize();
                
                if (abs($s3Size - $localSize) > 100) { // Allow 100 bytes difference
                    throw new \Exception("File size mismatch. Local: {$localSize}, S3: {$s3Size}");
                }

                $uploaded = true;
                \Log::info("Photo uploaded successfully: {$filePath}");
                break;

            } catch (\Exception $e) {
                \Log::warning("Photo upload attempt {$attempt} failed: " . $e->getMessage());
                
                // Clean up failed upload if file exists
                if ($filePath && Storage::disk('s3')->exists($filePath)) {
                    try {
                        Storage::disk('s3')->delete($filePath);
                        \Log::info("Cleaned up failed upload: {$filePath}");
                    } catch (\Exception $cleanupError) {
                        \Log::error("Failed to cleanup: " . $cleanupError->getMessage());
                    }
                }

                // If this is not the last attempt, wait before retry
                if ($attempt < $maxRetries) {
                    sleep($retryDelay);
                    $retryDelay *= 2; // Exponential backoff
                } else {
                    // All retries failed
                    $this->addError('photo', 'Gagal mengupload foto setelah ' . $maxRetries . ' percobaan. Silakan coba lagi.');
                    \Log::error('Photo upload failed after ' . $maxRetries . ' attempts: ' . $e->getMessage());
                    return;
                }
            }
        }

        // If upload successful, add to pending photos
        if ($uploaded && $filePath) {
            try {
                // Get URL with error handling
                $url = Storage::disk('s3')->url($filePath);

                // Add to pending photos
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
                
                // Clear the file input
                $this->photo = null;

                // Dispatch success event
                $this->dispatchBrowserEvent('photo-uploaded', [
                    'message' => 'Foto berhasil ditambahkan',
                    'type' => 'success'
                ]);

                // Reset validation errors
                $this->resetErrorBag('photo');
                
            } catch (\Exception $e) {
                \Log::error('Failed to process uploaded photo: ' . $e->getMessage());
                $this->addError('photo', 'Foto terupload tetapi gagal diproses. Silakan coba lagi.');
                
                // Cleanup
                if ($filePath) {
                    try {
                        Storage::disk('s3')->delete($filePath);
                    } catch (\Exception $cleanupError) {
                        \Log::error('Cleanup error: ' . $cleanupError->getMessage());
                    }
                }
            }
        }
    }

    public function deletePhoto($photoId)
    {
        $photoKey = collect($this->photos)->search(fn($p) => $p['id'] == $photoId);
        if ($photoKey !== false) {
            $this->photosToDelete[] = $photoId;
            array_splice($this->photos, $photoKey, 1);
            unset($this->photoCaptions[$photoId]);
            return;
        }

        $pendingKey = collect($this->pendingPhotos)->search(fn($p) => $p['id'] == $photoId);
        if ($pendingKey !== false) {
            $photo = $this->pendingPhotos[$pendingKey];
            try {
                Storage::disk('s3')->delete($photo['file_path']);
            } catch (\Exception $e) {
                \Log::error("Delete failed: " . $e->getMessage());
            }
            array_splice($this->pendingPhotos, $pendingKey, 1);
            unset($this->photoCaptions[$photoId]);
        }

        $this->reorderPhotos();
    }

    public function updatePhotoOrder($orderedIds)
    {
        $savedIds = [];
        $pendingIds = [];
        
        foreach ($orderedIds as $id) {
            if (strpos($id, 'pending_') === 0) {
                $pendingIds[] = $id;
            } else {
                $savedIds[] = $id;
            }
        }

        $reorderedSaved = [];
        foreach ($savedIds as $index => $id) {
            $photo = collect($this->photos)->firstWhere('id', $id);
            if ($photo) {
                $photo['order'] = $index;
                $reorderedSaved[] = $photo;
            }
        }
        $this->photos = $reorderedSaved;

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
        foreach ($this->photos as $index => &$photo) {
            $photo['order'] = $index;
        }
        $startOrder = count($this->photos);
        foreach ($this->pendingPhotos as $index => &$photo) {
            $photo['order'] = $startOrder + $index;
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'code' => 'nullable|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'zone_id' => 'nullable|exists:zones,id',
            'rack_id' => 'nullable|exists:racks,id',
            'barcode' => 'nullable|string|max:255|unique:product_stores,barcode,' . ($this->productId ?? 'NULL') . ',id,deleted_at,NULL',
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
        $data['dimension'] = ($data['length'] ?? 0) . ' x ' . ($data['width'] ?? 0) . ' x ' . ($data['height'] ?? 0);
        
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
            return redirect()->route('product-store.create')->with('success', 'Produk berhasil disimpan.');
        } else {
            $this->resetForm();
            return redirect()->route('product-store.index')->with('success', 'Produk berhasil disimpan.');
        }
    }

    private function handlePhotoDeletion()
    {
        if (!empty($this->photosToDelete)) {
            $mediaToDelete = ProductStoreMedia::whereIn('id', $this->photosToDelete)->get();
            foreach ($mediaToDelete as $media) {
                try {
                    Storage::disk('s3')->delete($media->file_path);
                } catch (\Exception $e) {
                    \Log::error('Delete failed: ' . $e->getMessage());
                }
                $media->delete();
            }
            $this->photosToDelete = [];
        }
    }

    private function savePhotos($product)
    {
        foreach ($this->photos as $photo) {
            $media = ProductStoreMedia::find($photo['id']);
            if ($media) {
                $media->update([
                    'order' => $photo['order'],
                    'caption' => $this->photoCaptions[$photo['id']] ?? null
                ]);
            }
        }

        foreach ($this->pendingPhotos as $photo) {
            ProductStoreMedia::create([
                'product_store_id' => $product->id,
                'file_path' => $photo['file_path'],
                'order' => $photo['order'],
                'caption' => $this->photoCaptions[$photo['id']] ?? null
            ]);
        }
        $this->pendingPhotos = [];
    }

    private function resetForm()
    {
        foreach ($this->pendingPhotos as $photo) {
            try {
                Storage::disk('s3')->delete($photo['file_path']);
            } catch (\Exception $e) {
                \Log::error('Cleanup error: ' . $e->getMessage());
            }
        }

        $this->reset([
            'warehouse_id', 'zone_id', 'rack_id', 'productId', 'barcode',
            'category_product_store_id', 'brand_product_store_id',
            'name', 'variant', 'specification', 'length', 'width', 'height',
            'weight', 'selling_price', 'photo', 'photos', 'pendingPhotos',
            'photoCaptions', 'photosToDelete', 'uploadingCount', 'uploadedCount', 'isUploading'
        ]);
        $this->zones = collect();
        $this->racks = collect();
    }

    public function cancel()
    {
        foreach ($this->pendingPhotos as $photo) {
            try {
                Storage::disk('s3')->delete($photo['file_path']);
            } catch (\Exception $e) {}
        }
        $this->resetForm();
    }

    public function createProduct()
    {
        $this->barcode = $this->generateBarcode();
    }

    public function regenerateBarcode()
    {
        $this->barcode = $this->generateBarcode();
        $this->dispatchBrowserEvent('barcode-regenerated', [
            'barcode' => $this->barcode,
            'message' => 'Barcode baru berhasil di-generate'
        ]);
    }

    public function checkBarcodeAvailability()
    {
        if (!$this->barcode) {
            return [
                'available' => true,
                'message' => 'Barcode akan di-generate otomatis jika kosong'
            ];
        }

        // Check if barcode exists, excluding current product if editing
        $query = ProductStore::withTrashed()->where('barcode', $this->barcode);
        
        if ($this->productId) {
            $query->where('id', '!=', $this->productId);
        }

        $exists = $query->exists();

        return [
            'available' => !$exists,
            'message' => $exists ? 'Barcode sudah digunakan' : 'Barcode tersedia'
        ];
    }

    protected function generateBarcode()
    {
        do {
            $barcode = now()->format('Y') . str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT);
        } while (ProductStore::withTrashed()->where('barcode', $barcode)->exists());
        return $barcode;
    }
}