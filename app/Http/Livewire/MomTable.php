<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Mom;
use App\Models\Project;
use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;

class MomTable extends Component
{
    use WithPagination;

    public $search    = '';
    public $projectId = '';
    public $meetingId = '';
    public $userId    = '';
    public $dateFrom  = '';
    public $dateTo    = '';
    public $sortField = 'mom_date';
    public $sortDir   = 'desc';
    public $perPage   = 10;

    protected $paginationTheme = 'bootstrap';
    protected $updatesQueryString = ['search', 'projectId', 'meetingId', 'userId'];
    protected $allowedSortFields  = ['mom_date', 'created_at', 'name'];

    // Hanya emit visual reset untuk daterangepicker
    protected $listeners = ['resetFiltersFromJs'];

    public function mount()
    {
        $this->userId   = Auth::id();
        $this->dateFrom = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $this->dateTo   = now()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
    }

    /* ── Updating hooks — dipanggil otomatis saat wire:model berubah ── */

    public function updatingSearch()    { $this->resetPage(); }
    public function updatingPerPage()   { $this->resetPage(); }
    public function updatingDateFrom()  { $this->resetPage(); }
    public function updatingDateTo()    { $this->resetPage(); }
    public function updatingUserId()    { $this->resetPage(); }
    public function updatingMeetingId() { $this->resetPage(); }

    public function updatingProjectId()
    {
        $this->meetingId = '';
        $this->resetPage();
    }

    /* ── Sort ── */

    public function sortBy($field)
    {
        if (!in_array($field, $this->allowedSortFields)) {
            return;
        }
        $this->sortDir   = ($this->sortField === $field && $this->sortDir === 'desc') ? 'asc' : 'desc';
        $this->sortField = $field;
    }

    /* ── Reset semua filter ── */

    public function resetFilters()
    {
        $this->search    = '';
        $this->projectId = '';
        $this->meetingId = '';
        $this->userId    = Auth::id();
        $this->dateFrom  = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $this->dateTo    = now()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
        $this->sortField = 'mom_date';
        $this->sortDir   = 'desc';
        $this->perPage   = 10;
        $this->resetPage();
        // Emit ke JS hanya untuk reset visual daterangepicker & Select2
        $this->emit('filtersReset', $this->dateFrom, $this->dateTo, $this->userId);
    }

    /* ── Render ── */

    public function render()
    {
        $currentUser = Auth::user();

        $projects = Project::byCompany($currentUser->company_id)
            ->orderBy('title')
            ->get(['id', 'title']);

        $meetings = Meeting::byCompany($currentUser->company_id)
            ->when($this->projectId, fn ($q) => $q->where('project_id', $this->projectId))
            ->orderBy('meeting_name')
            ->get(['id', 'meeting_name']);

        $divisionIds = $currentUser->divisions->pluck('id');

        $users = $divisionIds->isNotEmpty()
            ? User::whereHas('divisions', fn ($q) => $q->whereIn('divisions.id', $divisionIds))
                ->where('company_id', $currentUser->company_id)
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect([$currentUser]);

        $moms = Mom::with(['project', 'meeting', 'user'])
            ->byCompany($currentUser->company_id)
            ->when($this->search, fn ($q) => $q->where(function ($query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('notes', 'like', "%{$this->search}%")
                    ->orWhereHas('project', fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
                    ->orWhereHas('meeting', fn ($q) => $q->where('meeting_name', 'like', "%{$this->search}%"));
            }))
            ->when($this->projectId, fn ($q) => $q->where('project_id', $this->projectId))
            ->when($this->meetingId, fn ($q) => $q->where('meeting_id', $this->meetingId))
            ->when($this->userId,    fn ($q) => $q->where('user_id', $this->userId))
            ->when($this->dateFrom,  fn ($q) => $q->whereDate('mom_date', '>=', $this->dateFrom))
            ->when($this->dateTo,    fn ($q) => $q->whereDate('mom_date', '<=', $this->dateTo))
            ->orderBy($this->sortField, $this->sortDir)
            ->paginate($this->perPage);

        return view('livewire.mom-table', compact('moms', 'projects', 'meetings', 'users'));
    }
}
