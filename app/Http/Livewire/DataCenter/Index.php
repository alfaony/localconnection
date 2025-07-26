<?php

namespace App\Http\Livewire\DataCenter;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

use App\Models\DataCenter;

class Index extends Component
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
        return view('livewire.data-center.index', [
            'dataCenters' => DataCenter::byCompany(Auth::user()->company_id)
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
        DataCenter::find($id)?->delete();

        $this->dispatchBrowserEvent('showDeleteNotification', [
            'message' => 'Data berhasil dihapus'
        ]);
    }

    public function confirmDelete($id)
    {
        $this->dispatchBrowserEvent('confirmDelete', ['id' => $id]);
    }

    public static function index()
    {
        
    }
}