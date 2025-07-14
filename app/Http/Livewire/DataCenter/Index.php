<?php

namespace App\Http\Livewire\DataCenter;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

use App\Models\DataCenter;

class Index extends Component
{
    use WithPagination;
    public function render()
    {
        return view('livewire.data-center.index', [
            'dataCenters' => DataCenter::byCompany(Auth::user()->company_id)->paginate(10)
        ])->extends('adminlte::page');
    }

    public function delete($id)
    {
        DataCenter::find($id)->delete();
        session()->flash('success', 'Data Center deleted successfully');
    }
}