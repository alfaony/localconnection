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
    public bool $showDeleteModal = false;

    // Flash message
    public string $flashMessage = '';
    public string $flashType    = 'success';

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
        $this->deleteId      = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->deleteId        = null;
        $this->showDeleteModal = false;
    }

    public function destroy()
    {
        if (!$this->deleteId) return;

        try {
            $asset = InternetAsset::findOrFail($this->deleteId);
            $asset->delete();
            $this->flash('Asset berhasil dihapus.', 'success');
        } catch (\Throwable $e) {
            $this->flash('Gagal menghapus asset: ' . $e->getMessage(), 'danger');
        }

        $this->deleteId        = null;
        $this->showDeleteModal = false;
    }

    public function toggleStatus($id)
    {
        try {
            $asset = InternetAsset::findOrFail($id);

            if ($asset->status === 'active') {
                $asset->update(['status' => 'damaged', 'damaged_at' => now()->toDateString()]);
                $this->flash('Asset dinonaktifkan — tanggal rusak dicatat.', 'warning');
            } else {
                $asset->update(['status' => 'active', 'damaged_at' => null]);
                $this->flash('Asset diaktifkan kembali.', 'success');
            }
        } catch (\Throwable $e) {
            $this->flash('Gagal mengubah status: ' . $e->getMessage(), 'danger');
        }
    }

    private function flash(string $message, string $type = 'success')
    {
        $this->flashMessage = $message;
        $this->flashType    = $type;
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
        ])->extends('adminlte::page')->section('content');
    }
}
