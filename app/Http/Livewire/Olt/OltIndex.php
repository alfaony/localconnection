<?php

namespace App\Http\Livewire\Olt;

use App\Helpers\Access;
use App\Jobs\PollOltJob;
use App\Models\Olt;
use App\Models\OltOnu;
use Livewire\Component;
use Livewire\WithPagination;

class OltIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $status = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function refreshStatus(int $id): void
    {
        abort_unless(Access::can('index', 'olts'), 403);

        $olt = Olt::byCompany(auth()->user()->company_id)->findOrFail($id);

        PollOltJob::dispatch($olt->id)->onQueue(config('olt.queue', 'default'))->afterResponse();

        $this->dispatchBrowserEvent('olt-toast', [
            'type' => 'info',
            'message' => "Sinkronisasi {$olt->name} dijadwalkan.",
        ]);
    }

    public function delete(int $id): void
    {
        abort_unless(Access::can('destroy', 'olts'), 403);

        $olt = Olt::byCompany(auth()->user()->company_id)
            ->withCount(['onus as mapped_onus_count' => fn ($query) => $query->whereNotNull('internet_customer_id')])
            ->findOrFail($id);

        if ($olt->mapped_onus_count > 0) {
            session()->flash('error', 'OLT tidak dapat dihapus karena masih memiliki ONU yang terhubung ke pelanggan.');
            return;
        }

        $olt->delete();
        session()->flash('message', 'OLT berhasil dihapus.');
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $base = Olt::byCompany($companyId);

        $stats = [
            'total' => (clone $base)->count(),
            'up' => (clone $base)->where('status', Olt::STATUS_UP)->count(),
            'problem' => (clone $base)->whereIn('status', [Olt::STATUS_DOWN, Olt::STATUS_ERROR])->count(),
            'online_onus' => OltOnu::whereHas('olt', fn ($query) => $query->byCompany($companyId))
                ->where('status', OltOnu::STATUS_ONLINE)->count(),
            'los_onus' => OltOnu::whereHas('olt', fn ($query) => $query->byCompany($companyId))
                ->where('status', OltOnu::STATUS_LOS)->count(),
        ];

        $olts = Olt::byCompany($companyId)
            ->with('pop:id,name')
            ->withCount([
                'onus',
                'onus as online_onus_count' => fn ($query) => $query->where('status', OltOnu::STATUS_ONLINE),
                'onus as los_onus_count' => fn ($query) => $query->where('status', OltOnu::STATUS_LOS),
            ])
            ->when($this->search, function ($query) {
                $search = '%'.$this->search.'%';
                $query->where(function ($nested) use ($search) {
                    $nested->where('name', 'like', $search)
                        ->orWhere('host', 'like', $search)
                        ->orWhere('model', 'like', $search);
                });
            })
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->latest()
            ->paginate(10);

        return view('livewire.olt.olt-index', compact('olts', 'stats'))
            ->extends('adminlte::page')
            ->section('content');
    }
}
