<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;

use App\Models\Meeting;
use Livewire\Component;
use Livewire\WithPagination;

class MeetingTable extends Component
{
    public $search = '';
    use WithPagination;
    protected $paginationTheme = 'bootstrap';
    public function render()
    {
        return view('livewire.meeting-table', [
            'meetings' => Meeting::query()
                ->where(function($query) {
                    $query->where('meeting_name', 'LIKE', '%'.$this->search.'%')
                        ->orWhere('meeting_agenda', 'LIKE', '%'.$this->search.'%');
                })
                ->byCompany(Auth::user()->company_id)
                ->orderBy('created_at', 'desc')
                ->orderBy('start_date', 'desc')
                ->orderBy('start_time', 'desc')
                ->paginate(5)
        ]);
    }

    public function updatingSearch(){
        $this->resetPage();
    }
}