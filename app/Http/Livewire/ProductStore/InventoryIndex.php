<?php

namespace App\Http\Livewire\ProductStore;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProductStore;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Helpers\Access;

class InventoryIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // ── Toggle form
    public $showForm      = false;

    // ── Search & found product
    public $searchQuery   = '';
    public $foundProduct  = null;
    public $inventory     = null;

    // ── Form input stok
    public $actionType    = 'in';   // in | out | adjustment
    public $qty           = '';
    public $notes         = '';

    // ── Filter & search riwayat
    public $filterType      = '';   // '' | in | out | adjustment
    public $searchMovement  = '';
    public $perPage         = 15;

    protected $queryString = [
        'filterType'     => ['except' => ''],
        'searchMovement' => ['except' => ''],
    ];

    protected $listeners = ['clearSearch'];

    public function updatedSearchQuery()
    {
        $this->resetPage();
        $this->foundProduct = null;
        $this->inventory    = null;
        $this->resetForm();

        if (strlen($this->searchQuery) < 2) {
            return;
        }

        $product = ProductStore::with('inventory')
            ->byCompany(auth()->user()->company_id)
            ->where(function ($q) {
                $q->where('barcode', $this->searchQuery)
                  ->orWhere('code', $this->searchQuery)
                  ->orWhere('name', 'like', '%' . $this->searchQuery . '%');
            })
            ->first();

        if ($product) {
            $this->foundProduct = $product;
            $this->inventory    = $product->inventory;
        }
    }

    public function selectAction(string $type)
    {
        $this->actionType = $type;
        $this->qty        = '';
    }

    public function saveStock()
    {
        $this->validate([
            'qty'   => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ], [
            'qty.required' => 'Jumlah wajib diisi.',
            'qty.integer'  => 'Jumlah harus angka.',
            'qty.min'      => 'Jumlah minimal 1.',
        ]);

        if (!$this->foundProduct) {
            return;
        }

        // Buat inventory jika belum ada
        if (!$this->inventory) {
            $inv = Inventory::create([
                'product_store_id' => $this->foundProduct->id,
                'quantity'         => 0,
                'unit'             => 'pcs',
                'company_id'       => auth()->user()->company_id,
                'user_create_id'   => auth()->id(),
            ]);
            $this->inventory = $inv;
        } else {
            $this->inventory = Inventory::find($this->inventory->id);
        }

        $qty = (int) $this->qty;

        if ($this->actionType === 'out' && $qty > $this->inventory->quantity) {
            $this->addError('qty', 'Stok tidak cukup. Stok saat ini: ' . $this->inventory->quantity . ' pcs.');
            return;
        }

        match ($this->actionType) {
            'in'         => $this->inventory->addStock($qty, $this->notes),
            'out'        => $this->inventory->deductStock($qty, $this->notes),
            'adjustment' => $this->inventory->adjustStock($qty, $this->notes),
        };

        // Refresh product & inventory
        $this->foundProduct = $this->foundProduct->fresh(['inventory']);
        $this->inventory    = $this->foundProduct->inventory;

        $this->qty   = '';
        $this->notes = '';

        $this->dispatchBrowserEvent('stock-saved', [
            'message' => 'Stok berhasil diperbarui!',
        ]);
    }

    public function toggleForm()
    {
        $this->showForm = !$this->showForm;
        if (!$this->showForm) {
            $this->clearSearch();
        }
    }

    public function updatedSearchMovement()
    {
        $this->resetPage();
    }

    public function clearSearch()
    {
        $this->searchQuery  = '';
        $this->foundProduct = null;
        $this->inventory    = null;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->actionType = 'in';
        $this->qty        = '';
        $this->notes      = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        $movements = InventoryMovement::with(['inventory.productStore', 'creator'])
            ->whereHas('inventory', function ($q) {
                $q->whereHas('productStore', function ($q2) {
                    $q2->byCompany(auth()->user()->company_id);
                });
            })
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->searchMovement, function ($q) {
                $q->whereHas('inventory.productStore', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->searchMovement . '%')
                       ->orWhere('barcode', 'like', '%' . $this->searchMovement . '%')
                       ->orWhere('code', 'like', '%' . $this->searchMovement . '%');
                });
            })
            ->latest()
            ->paginate($this->perPage);

        $isEdit = Access::can('edit', 'product_stores');

        return view('livewire.product-store.inventory-index', compact('movements', 'isEdit'))
            ->extends('adminlte::page');
    }
}
