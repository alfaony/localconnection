<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use App\Models\SettingCompany;
use App\Models\User;
use App\Models\Meeting;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\GoogleService;
use App\Schemas\RoleSchema;

class MeetingTable extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $dateStart = '';
    public $dateEnd = '';
    public $userIds = [];
    public $meetingType = '';
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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingMeetingType()
    {
        $this->resetPage();
    }

    public function updatedUserIds()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search      = '';
        $this->dateStart   = '';
        $this->dateEnd     = '';
        $this->meetingType = '';
        $this->userIds     = [];
        $this->resetPage();
        $this->emit('filtersReset');
    }

    public function render()
    {
        $currentUser = Auth::user();
        $companyId   = $currentUser->company_id;

        $users = User::byCompany($companyId)->isActive()->orderBy('name')->get();

        $privilegedRoles = [RoleSchema::ADMIN, RoleSchema::ROOT, RoleSchema::DIRECTOR, RoleSchema::HR];
        $isPrivileged    = in_array($currentUser->role->name, $privilegedRoles);

        $meetingsQuery = Meeting::query()
            ->with(['meetingRecurrence', 'generatedFromRecurrence'])
            ->where(function ($q) {
                $q->where('meeting_name', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('meeting_agenda', 'LIKE', '%' . $this->search . '%');
            })
            ->when($this->meetingType, fn ($q) => $q->where('meeting_type', $this->meetingType))
            ->when(
                $this->dateStart && $this->dateEnd,
                fn ($q) => $q->whereBetween('start_date', [$this->dateStart, $this->dateEnd])
            )
            ->byCompany($companyId);

        if ($isPrivileged && !empty($this->userIds)) {
            $meetingsQuery->where(function ($q) {
                $q->whereIn('user_id', $this->userIds)
                  ->orWhereHas('participants', fn ($q2) => $q2->whereIn('users.id', $this->userIds));
            });
        }

        $meetings = $meetingsQuery
            ->orderBy('start_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(10);

        return view('livewire.meeting-table', compact('meetings', 'users'));
    }
}
