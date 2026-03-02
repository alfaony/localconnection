<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use App\Models\SettingCompany;

use App\Models\Meeting;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\GoogleService;

class MeetingTable extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $googleConnected = false;
    public $googleReadyChecked = false;

    public function mount()
    {
        $this->checkGoogleConnection();
    }

    public function checkGoogleConnection()
    {
        $companyId = Auth::user()->company_id;

        $settings = SettingCompany::byCompany($companyId)
            ->where('menu', 'google')
            ->get()
            ->pluck('field_value', 'field_title');
        $this->googleReadyChecked = !empty($settings['google_client_id']) && !empty($settings['google_client_secret']);
        $this->googleConnected = GoogleService::checkGoogleConnection($companyId);
    }

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
                ->paginate(10)
        ]);
    }

    public function updatingSearch(){
        $this->resetPage();
    }
}