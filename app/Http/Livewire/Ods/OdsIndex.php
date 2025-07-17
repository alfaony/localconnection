<?php

namespace App\Http\Livewire\Ods;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\OpticalDistribution;
use Illuminate\Support\Facades\Auth;

class OdsIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'name';
    public $sortDirection = 'asc';
    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['delete'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.Ods.ods-index', [
            'opticalDistributions' => OpticalDistribution::byCompany(Auth::user()->company_id)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage)
        ])->extends('adminlte::page');
    }

    public function delete($id)
    {
        // lakukan penghapusan
        $ods = OpticalDistribution::find($id);
        $ods->pops()->detach();
        $ods->delete();

        $this->dispatchBrowserEvent('showDeleteNotification', [
            'message' => 'Data berhasil dihapus'
        ]);
    }

    public function confirmDelete($id)
    {
        $this->dispatchBrowserEvent('confirmDelete', ['id' => $id]);
    }
}
