<?php

namespace App\Http\Livewire\Olt;

use App\Helpers\Access;
use App\Jobs\PollOltJob;
use App\Models\Olt;
use App\Models\OltOnu;
use Livewire\Component;
use Livewire\WithPagination;

class OltShow extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $olt;
    public $search = '';
    public $status = '';

    public function mount($olt): void
    {
        $this->olt = Olt::byCompany(auth()->user()->company_id)->findOrFail((int) $olt);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function synchronize(): void
    {
        abort_unless(Access::can('show', 'olts'), 403);

        PollOltJob::dispatch($this->olt->id)->onQueue(config('olt.queue', 'default'))->afterResponse();

        $this->dispatchBrowserEvent('olt-toast', [
            'type' => 'info',
            'message' => 'Sinkronisasi OLT dijadwalkan.',
        ]);
    }

    public function render()
    {
        $this->olt->refresh()->load('pop:id,name');

        $stats = [
            'total' => $this->olt->onus()->count(),
            'online' => $this->olt->onus()->where('status', OltOnu::STATUS_ONLINE)->count(),
            'offline' => $this->olt->onus()->where('status', OltOnu::STATUS_OFFLINE)->count(),
            'los' => $this->olt->onus()->where('status', OltOnu::STATUS_LOS)->count(),
        ];

        $onus = $this->olt->onus()
            ->with('customer:id,name,code')
            ->when($this->search, function ($query) {
                $search = '%'.$this->search.'%';
                $query->where(function ($nested) use ($search) {
                    $nested->where('serial_number', 'like', $search)
                        ->orWhere('name', 'like', $search)
                        ->orWhere('pon_port', 'like', $search)
                        ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', $search));
                });
            })
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->orderBy('pon_port')
            ->orderBy('onu_index')
            ->paginate(20);

        return view('livewire.olt.olt-show', compact('onus', 'stats'))
            ->extends('adminlte::page')
            ->section('content');
    }
}
