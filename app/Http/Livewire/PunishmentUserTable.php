<?php

namespace App\Http\Livewire;

use App\Models\PunishmentUser;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class PunishmentUserTable extends Component
{
    use WithPagination;

    public $search = '';
    public $startDate;
    public $endDate;
    public $perPage = 10;
    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function applyFilters()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function render()
    {
        $punishments = PunishmentUser::with(['user', 'dailytask'])
            ->search($this->search)
            ->dateRange($this->startDate, $this->endDate)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.punishment-user-table', [
            'punishments' => $punishments
        ])->extends('adminlte::page');
    }

    public function delete($id)
    {
        $punishment = PunishmentUser::find($id);
        if ($punishment) {
            $dailytask = $punishment->dailytask;
            if ($dailytask) 
            {
                $dailytask->delete();
            }
            $punishment->delete();
            $this->dispatchBrowserEvent('swal:modal', [
                'type' => 'success',
                'title' => 'Success!',
                'text' => 'Punishment record has been deleted.',
            ]);
        }
    }
}