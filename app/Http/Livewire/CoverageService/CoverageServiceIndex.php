<?php

namespace App\Http\Livewire\CoverageService;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CoverageService;

class CoverageServiceIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    protected $queryString = ['search' => ['except' => ''], 'perPage'];
    protected $paginationTheme = 'bootstrap';

    public function delete($id)
    {
        $coverageServices = CoverageService::byCompany(auth()->user()->company_id)->findOrFail($id);
        $coverageServices->coverageServiceOds()->delete();
        $coverageServices->delete();

        $this->dispatchBrowserEvent('showDeleteNotification', [
            'message' => 'Data berhasil dihapus'
        ]);
    }

    public function confirmDelete($id)
    {
        $this->dispatchBrowserEvent('confirmDelete', ['id' => $id]);
    }
    public function render()
    {
        $coverageServices = CoverageService::with(['city', 'district', 'subdistrict'])
            ->when($this->search, function ($query) {
                return $query->whereHas('province', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                })->orWhereHas('city', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                })->orWhereHas('district', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                })->orWhereHas('subdistrict', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                })->orWhereHas('coverageServiceOds', function ($q) {
                    $q->whereHas('ods', function ($q) {
                        $q->where('name', 'like', "%{$this->search}%");
                    });
                });
            })
            ->byCompany(auth()->user()->company_id)
            ->paginate($this->perPage);

        return view('livewire.coverage-service.coverage-service-index', compact('coverageServices'))->extends('adminlte::page');
    }
}
