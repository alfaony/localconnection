<?php

namespace App\Http\Livewire\Asset;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\InternetAsset;
use Illuminate\Support\Facades\Auth;

class AssetIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';
    public $categoryFilter = '';
    public $sortField = 'purchase_date';
    public $sortDir = 'desc';

    // Delete confirm
    public $deleteId = null;

    protected $queryString = ['search', 'statusFilter', 'categoryFilter'];

    public function updatingSearch()    { $this->resetPage(); }
    public function updatingStatusFilter()   { $this->resetPage(); }
    public function updatingCategoryFilter() { $this->resetPage(); }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir = 'asc';
        }
    }

    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->dispatchBrowserEvent('show-delete-modal');
    }

    public function destroy()
    {
        $asset = InternetAsset::byCompany(Auth::user()->company_id)->findOrFail($this->deleteId);
        $asset->delete();
        $this->deleteId = null;
        $this->dispatchBrowserEvent('hide-delete-modal');
        session()->flash('success', 'Asset berhasil dihapus.');
    }

    public function toggleStatus($id)
    {
        $asset = InternetAsset::byCompany(Auth::user()->company_id)->findOrFail($id);

        if ($asset->status === 'active') {
            $asset->update(['status' => 'damaged', 'damaged_at' => now()->toDateString()]);
            session()->flash('success', 'Asset dinonaktifkan — tanggal rusak dicatat.');
        } else {
            $asset->update(['status' => 'active', 'damaged_at' => null]);
            session()->flash('success', 'Asset diaktifkan kembali.');
        }
    }

    public function render()
    {
        $query = InternetAsset::byCompany(Auth::user()->company_id)
            ->when($this->search, fn($q) =>
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('brand', 'like', "%{$this->search}%")
                      ->orWhere('model', 'like', "%{$this->search}%")
                      ->orWhere('serial_number', 'like', "%{$this->search}%")
                      ->orWhere('vendor', 'like', "%{$this->search}%");
                })
            )
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter, fn($q) => $q->where('category', $this->categoryFilter))
            ->orderBy($this->sortField, $this->sortDir);

        $assets = $query->paginate(15);

        // Summary stats
        $stats = InternetAsset::byCompany(Auth::user()->company_id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(unit_price * quantity) as total_value,
                SUM(CASE WHEN status='active' THEN unit_price * quantity ELSE 0 END) as active_value,
                COUNT(CASE WHEN status='active' THEN 1 END) as total_active,
                COUNT(CASE WHEN status='damaged' THEN 1 END) as total_damaged,
                COUNT(CASE WHEN status='maintenance' THEN 1 END) as total_maintenance
            ")
            ->first();

        return view('livewire.asset.asset-index', [
            'assets'     => $assets,
            'stats'      => $stats,
            'categories' => InternetAsset::categoryOptions(),
        ])->extends('adminlte::page');
    }
}
