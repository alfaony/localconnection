<?php

namespace App\Http\Livewire\Promo;

use Livewire\Component;
use App\Models\Promo;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PromoIndex extends Component
{
    use WithPagination;

    public $search = '';
    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deletePromo','toggleStatus'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->dispatchBrowserEvent('swal:confirm', [
            'id' => $id,
            'message' => 'Anda yakin ingin menghapus promo ini?'
        ]);
    }

    public function toggleStatus($id)
    {
        $package = Promo::byCompany(Auth::user()->company_id)->findOrFail($id);
        if (!$package->is_active) {
        // Sudah nonaktif, tidak boleh diaktifkan lagi
        session()->flash('error', 'Promo sudah nonaktif.');
        return;
    }
        $package->update(['is_active' => !$package->is_active]);
        session()->flash('success', 'Status paket berhasil diperbarui.');
    }

    public function deletePromo($id)
    {
        try {
            $promo = Promo::findOrFail($id);
            $promo->packageInternets()->detach();
            $promo->delete();
            
            $this->dispatchBrowserEvent('swal:deleted', [
                'message' => 'Promo berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            // dd($e);
            Log::error('Error deleting promo: ' . $e->getMessage());
            $this->dispatchBrowserEvent('swal:error', [
                'message' => 'Terjadi kesalahan saat menghapus promo'
            ]);
        }
    }

    public function render()
    {
        $promos = Promo::withCount('packageInternets')
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('type', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.promo.promo-index', compact('promos'))->extends('adminlte::page');
    }
}