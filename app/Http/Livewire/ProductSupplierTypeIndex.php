<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SupplierType;
use App\Models\ProductSupplier;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class ProductSupplierTypeIndex extends Component
{
    // Form fields
    public $productSupplierTypeId;
    public $company_id;
    public $name;
    public $isEdit      = false;
    public $showModal   = false;

    // Show panel
    public $showingTypeId   = null;
    public $showingTypeName = '';

    // Delete state
    public $deleteTypeId      = null;
    public $deleteHasSuppliers = false;   // true → tampilkan assign modal
    public $assignToTypeId    = null;     // pilihan assign target
    public $showDeleteModal   = false;

    protected $rules = [
        'name' => 'required|string|max:255',
    ];

    public function render()
    {
        $productSupplierTypes = SupplierType::byCompany(Auth::user()->company_id)->paginate(10);

        $showingSuppliers = [];
        if ($this->showingTypeId) {
            $showingSuppliers = ProductSupplier::byCompany(Auth::user()->company_id)
                ->where('supplier_type_id', $this->showingTypeId)
                ->get();
        }

        // Daftar tipe lain untuk opsi assign (saat delete)
        $otherTypes = $this->deleteTypeId
            ? SupplierType::byCompany(Auth::user()->company_id)
                ->where('id', '!=', $this->deleteTypeId)
                ->get()
            : collect();

        return view('livewire.product-supplier-type-index', compact(
            'productSupplierTypes',
            'showingSuppliers',
            'otherTypes'
        ))->extends('adminlte::page');
    }

    // ── SHOW ──────────────────────────────────────────────────────────────────

    public function showType($id)
    {
        $type = SupplierType::findOrFail($id);
        $this->showingTypeId   = $id;
        $this->showingTypeName = $type->name;
    }

    public function closeShow()
    {
        $this->showingTypeId   = null;
        $this->showingTypeName = '';
    }

    // ── CREATE / EDIT MODAL ───────────────────────────────────────────────────

    public function resetInput()
    {
        $this->productSupplierTypeId = '';
        $this->company_id            = '';
        $this->name                  = '';
        $this->isEdit                = false;
        $this->showModal             = false;
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
            'name' => 'required|string|max:255|unique:supplier_types,name,NULL,id,company_id,' . Auth::user()->company_id,
        ]);

        SupplierType::create([
            'company_id' => Auth::user()->company_id,
            'name'       => $this->name,
        ]);

        session()->flash('message', 'Jenis supplier berhasil ditambahkan.');
        $this->resetInput();
    }

    public function edit($id)
    {
        $type = SupplierType::findOrFail($id);

        $this->productSupplierTypeId = $id;
        $this->company_id            = $type->company_id;
        $this->name                  = $type->name;
        $this->isEdit                = true;
        $this->showModal             = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:supplier_types,name,' . $this->productSupplierTypeId . ',id,company_id,' . Auth::user()->company_id . ',deleted_at,NULL',
        ]);

        SupplierType::find($this->productSupplierTypeId)->update([
            'company_id' => $this->company_id,
            'name'       => $this->name,
        ]);

        session()->flash('message', 'Jenis supplier berhasil diperbarui.');
        $this->resetInput();
    }

    public function cancel()
    {
        $this->resetInput();
    }

    // ── DELETE ────────────────────────────────────────────────────────────────

    public function confirmDelete($id)
    {
        $this->deleteTypeId      = $id;
        $this->assignToTypeId    = null;
        $this->deleteHasSuppliers = ProductSupplier::where('supplier_type_id', $id)->exists();
        $this->showDeleteModal   = true;
    }

    public function cancelDelete()
    {
        $this->deleteTypeId      = null;
        $this->deleteHasSuppliers = false;
        $this->assignToTypeId    = null;
        $this->showDeleteModal   = false;
    }

    public function deleteDirectly()
    {
        if (!$this->deleteTypeId) return;

        SupplierType::find($this->deleteTypeId)?->delete();

        // Jika sedang menampilkan type yang dihapus, tutup panel
        if ($this->showingTypeId == $this->deleteTypeId) {
            $this->closeShow();
        }

        session()->flash('message', 'Jenis supplier berhasil dihapus.');
        $this->cancelDelete();
    }

    public function deleteWithAssign()
    {
        $this->validate([
            'assignToTypeId' => 'required|exists:supplier_types,id',
        ], [
            'assignToTypeId.required' => 'Pilih jenis supplier tujuan terlebih dahulu.',
            'assignToTypeId.exists'   => 'Jenis supplier tujuan tidak valid.',
        ]);

        // Pindahkan semua supplier ke tipe tujuan
        ProductSupplier::where('supplier_type_id', $this->deleteTypeId)
            ->update(['supplier_type_id' => $this->assignToTypeId]);

        // Hapus tipe
        SupplierType::find($this->deleteTypeId)?->delete();

        if ($this->showingTypeId == $this->deleteTypeId) {
            $this->closeShow();
        }

        session()->flash('message', 'Supplier telah dipindahkan dan jenis supplier dihapus.');
        $this->cancelDelete();
    }
}
