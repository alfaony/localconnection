<?php

namespace App\Http\Livewire\Promo;

use Livewire\Component;
use App\Models\Promo;
use Livewire\WithPagination;

class PromoIndex extends Component
{
    use WithPagination;

    public $search = '';

    protected $listeners = ['promoSaved' => '$refresh'];

    public function delete($id)
    {
        Promo::findOrFail($id)->delete();
        session()->flash('message', 'Promo berhasil dihapus.');
    }

    public function render()
    {
        $promos = Promo::where('name', 'like', '%' . $this->search . '%')
            ->latest()->paginate(10);

        return view('livewire.promo.promo-index', compact('promos'))->extends('adminlte::page');
    }
}
