<?php

namespace App\Http\Livewire;

use App\Models\PunishmentUser;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class PunishmentUserTable extends Component
{
    use WithPagination;

    public $selectedUser = '';
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

    public function updatingSelectedUser()
    {
        $this->resetPage();
    }

    public function updatingStartDate()
    {
        $this->resetPage();
    }

    public function updatingEndDate()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->selectedUser = '';
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->resetPage();
        
        // Reset select2 value
        $this->dispatchBrowserEvent('resetSelect2');
    }

    public function render()
    {
        // Get users by company
        $companyUsers = User::byCompany(Auth::user())->get();
        
        $punishments = PunishmentUser::with(['user', 'dailytask'])->byCompany(Auth::user()->company_id)
            ->when($this->selectedUser, function($query) {
                $query->where('user_id', $this->selectedUser);
            })
            ->when($this->startDate && $this->endDate, function($query) {
                $query->whereBetween('created_at', [
                    $this->startDate . ' 00:00:00',
                    $this->endDate . ' 23:59:59'
                ]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.punishment-user-table', [
            'punishments' => $punishments,
            'companyUsers' => $companyUsers
        ])->extends('adminlte::page');
    }

    public function delete($id)
    {
        $punishment = PunishmentUser::find($id);
        if ($punishment) {
            $punishment->delete();
            
            $this->dispatchBrowserEvent('swal:modal', [
                'type' => 'success',
                'title' => 'Success!',
                'text' => 'Punishment record has been deleted.',
            ]);
            
            $this->emit('refreshComponent');
        }
    }
}