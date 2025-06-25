<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Mom;

class MomTable extends Component
{
    use WithPagination;

    public $search = '';
    protected $paginationTheme = 'bootstrap';

    protected $updatesQueryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $moms = Mom::with(['project', 'meeting'])
            ->byCompany(Auth::user()->company_id)
            ->where('mom_date', 'like', "%{$this->search}%")
            ->orWhereHas('project', fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('mom_date')
            ->paginate(10);

        return view('livewire.mom-table', compact('moms'));
    }
}