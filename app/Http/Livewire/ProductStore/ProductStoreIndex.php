<?php

namespace App\Http\Livewire\ProductStore;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use Illuminate\Support\Str;

use App\Helpers\Access;

use App\Models\ProductStore;
use App\Models\CategoryProductStore;
use App\Models\BrandProductStore;
use App\Models\Warehouse;
use App\Models\Zone;
use App\Models\Product;

use Livewire\WithFileUploads;
use App\Models\ImportProgress;
use App\Jobs\ImportProductStoreJob;

class ProductStoreIndex extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $categoryFilter = '';
    public $warehouseFilter = '';
    public $zoneFilter = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $selectedProductId;
    public $showFormModal = false;
    public $category_product_store_id;
    public $brand_product_store_id;

    // Import properties
    public $csvFile;
    public $isFileReady = false;
    public $uploadingFile = false;
    public $batchId = null;
    public $importProgress = null;
    public $isImporting = false;
    public $showImportSection = false;

    protected $paginationTheme = 'bootstrap';

    // Collections
    public $categories;
    public $warehouses;
    public $zones;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'warehouseFilter' => ['except' => ''],
        'zoneFilter' => ['except' => ''],
    ];

    protected $listeners = ['productSaved', 'closeForm','deleteProduct'];

    public function mount()
    {
        $this->categories = CategoryProductStore::byCompany(auth()->user()->company_id)->get();
        $this->warehouses = Warehouse::byCompany(auth()->user()->company_id)->get();
        $this->zones = collect();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingWarehouseFilter()
    {
        $this->zoneFilter = '';
        $this->resetPage();
    }

    public function updatingZoneFilter()
    {
        $this->resetPage();
    }

    public function updatedWarehouseFilter($value)
    {
        $this->zones = collect();
        
        if ($value) {
            $this->zones = Zone::where('warehouse_id', $value)
                ->select('id', 'name')
                ->get();
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->categoryFilter = '';
        $this->warehouseFilter = '';
        $this->zoneFilter = '';
        $this->zones = collect();
        $this->resetPage();
    }

    // ==================== IMPORT METHODS ====================
    
    public function toggleImportSection()
    {
        $this->showImportSection = !$this->showImportSection;
        
        if (!$this->showImportSection) {
            $this->resetImport();
        }
    }

    public function resetImport()
    {
        $this->reset(['csvFile', 'batchId', 'importProgress', 'isImporting']);
        $this->resetValidation();
    }

    // FIX: Tambahkan method untuk clear file setelah upload
    // public function updatedCsvFile()
    // {
    //     $this->resetValidation('csvFile');
    // }

public function updatedCsvFile()
{
    $this->uploadingFile = true;
    $this->isFileReady = false;
    $this->resetValidation('csvFile');
}

public function checkFileReady()
{
    try {
        if ($this->csvFile && $this->csvFile->exists()) {
            // Double check file can be read
            $testRead = $this->csvFile->get();
            
            if (!empty($testRead)) {
                $this->isFileReady = true;
                $this->uploadingFile = false;
                
                $this->dispatchBrowserEvent('file-ready', [
                    'filename' => $this->csvFile->getClientOriginalName(),
                    'size' => number_format($this->csvFile->getSize() / 1024, 2) . ' KB'
                ]);
                
                return true;
            }
        }
        
        return false;
    } catch (\Exception $e) {
        Log::error('File check error', ['error' => $e->getMessage()]);
        return false;
    }
}

// public function resetImport()
// {
//     $this->reset(['csvFile', 'batchId', 'importProgress', 'isImporting', 'isFileReady', 'uploadingFile']);
//     $this->resetValidation();
// }

public function import()
{
    // Final check
    if (!$this->isFileReady || !$this->csvFile) {
        $this->addError('csvFile', 'File belum siap. Silakan tunggu beberapa saat.');
        return;
    }

    // Validasi
    $this->validate([
        'csvFile' => 'required|file|max:10240',
    ], [
        'csvFile.required' => 'File CSV wajib diupload',
        'csvFile.max' => 'Ukuran file maksimal 10MB'
    ]);

    try {
        // Triple check file exists
        if (!$this->csvFile->exists()) {
            $this->addError('csvFile', 'File tidak ditemukan. Silakan upload ulang.');
            return;
        }

        // Get file content
        $fileContent = $this->csvFile->get();
        
        if (empty($fileContent)) {
            $this->addError('csvFile', 'File kosong atau corrupt.');
            return;
        }
        
        // Parse CSV
        $csv = Reader::createFromString($fileContent);
        $csv->setHeaderOffset(null);
        
        $csvData = iterator_to_array($csv->getRecords());
        
        if (count($csvData) <= 1) {
            $this->addError('csvFile', 'File CSV kosong atau hanya berisi header');
            return;
        }

        // Get accessible company IDs
        $accessibleCompanyIds = auth()->user()
            ->accessibleCompanies
            ->pluck('id')
            ->push(auth()->user()->company_id)
            ->unique()
            ->toArray();

        // Generate batch ID
        $this->batchId = Str::uuid()->toString();
        
        ImportProgress::create([
            'batch_id' => $this->batchId,
            'processed' => 0,
            'total' => count($csvData) - 1,
            'total_import' => 0,
            'errors' => [],
            'status' => 'queued'
        ]);

        // Dispatch job
        ImportProductStoreJob::dispatch(
            $csvData,
            auth()->id(),
            auth()->user()->company_id,
            $this->batchId,
            $accessibleCompanyIds
        );

        $this->isImporting = true;
        $this->isFileReady = false;
        $this->uploadingFile = false;
        $this->csvFile = null;
        
        $this->dispatchBrowserEvent('import-started', [
            'total_rows' => count($csvData) - 1
        ]);
        
        $this->dispatchBrowserEvent('start-progress-check');

    } catch (\Exception $e) {
        Log::error('Import error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        $this->addError('csvFile', 'Terjadi kesalahan: ' . $e->getMessage());
        $this->isFileReady = false;
        $this->uploadingFile = false;
    }
}
    public function checkProgress()
    {
        if (!$this->batchId) {
            return;
        }

        $progress = ImportProgress::where('batch_id', $this->batchId)->first();

        if ($progress) {
            // Pastikan errors adalah array
            $errors = $progress->errors;
            if (is_string($errors)) {
                $errors = json_decode($errors, true) ?? [];
            }
            if (!is_array($errors)) {
                $errors = [];
            }

            $this->importProgress = [
                'batch_id' => $progress->batch_id,
                'processed' => $progress->processed,
                'total' => $progress->total,
                'total_import' => $progress->total_import,
                'success' => $progress->success,
                'failed' => $progress->failed,
                'percentage' => $progress->percentage,
                'status' => $progress->status,
                'errors' => $errors,
                'updated_at' => $progress->updated_at->toDateTimeString()
            ];

            // Check if completed (semua data sudah diproses)
            if ($progress->processed >= $progress->total) {
                $this->isImporting = false;
                $this->dispatchBrowserEvent('import-completed', [
                    'progress' => $this->importProgress
                ]);
            }
        }
    }

    public function downloadTemplate()
    {
        return response()->streamDownload(function () {
            $file = fopen('php://output', 'w');
            
            $columns = [
                'name',
                'category',
                'brand',
                'variant',
                'specification',
                'length',
                'width',
                'height',
                'weight',
                'selling_price',
                'rack'
            ];
            
            fputcsv($file, $columns);
            
            // Sample data
            fputcsv($file, [
                'Laptop ASUS ROG',
                'Electronics',
                'ASUS',
                'ROG Strix G15',
                'Intel Core i7, 16GB RAM, 512GB SSD',
                '35',
                '25',
                '5',
                '2.5',
                '15000000',
                'A-01'
            ]);
            
            fputcsv($file, [
                'Mouse Logitech',
                'Accessories',
                'Logitech',
                'MX Master 3',
                'Wireless, Bluetooth, USB-C',
                '12',
                '8',
                '5',
                '0.15',
                '1500000',
                'B-12'
            ]);
            
            fclose($file);
        }, 'template_import_product_store.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function getStatusColor($status)
    {
        return match($status) {
            'queued' => 'secondary',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            default => 'secondary'
        };
    }

    // ==================== OTHER METHODS ====================

    public function render()
    {
        $products = ProductStore::with(['category', 'brand'])
            ->search($this->search)
            ->when($this->categoryFilter, function ($query) {
                $query->where('category_product_store_id', $this->categoryFilter);
            })
            ->when($this->warehouseFilter, function ($query) {
                $query->whereHas('rack.zone.warehouse', function ($q) {
                    $q->where('id', $this->warehouseFilter);
                });
            })
            ->when($this->zoneFilter, function ($query) {
                $query->whereHas('rack.zone', function ($q) {
                    $q->where('id', $this->zoneFilter);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        // permission
        $isShow = Access::can('show','product_stores');
        $isEdit = Access::can('edit','product_stores');
        $isDestroy = Access::can('destroy','product_stores');

        return view('livewire.product-store.product-store-index', compact('products','isShow','isEdit','isDestroy'))
            ->extends('adminlte::page');
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