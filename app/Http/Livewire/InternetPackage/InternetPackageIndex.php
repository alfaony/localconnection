<?php

namespace App\Http\Livewire\InternetPackage;

use App\Models\InternetPackage;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Access;

class InternetPackageIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $selectedType = '';
    public $activeFilter = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField',
        'sortDirection'
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete($id)
    {
        $package = InternetPackage::byCompany(Auth::user()->company_id)->findOrFail($id);
        $package->delete();
        session()->flash('message', 'Paket berhasil dihapus.');
    }

    public function toggleStatus($id)
    {
        $package = InternetPackage::byCompany(Auth::user()->company_id)->findOrFail($id);
        $package->update(['is_active' => !$package->is_active]);
        session()->flash('message', 'Status paket berhasil diperbarui.');
    }

    public function render()
    {
        $isCheck = Access::can('is_active','internet_packages');

        return view('livewire.internet-package.internet-package-index', [
            'isCheck' => $isCheck,
            'packages' => InternetPackage::query()
                ->byCompany(Auth::user()->company_id)
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                          ->orWhere('description', 'like', '%'.$this->search.'%')
                          ->orWhere('bandwidth', 'like', '%'.$this->search.'%');
                })
                ->when($this->selectedType, function ($query) {
                    $query->where('type', $this->selectedType);
                })
                ->when($this->activeFilter !== 'all', function ($query) {
                    $query->where('is_active', $this->activeFilter === 'active');
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage)
        ])->extends('adminlte::page');
    }
}
