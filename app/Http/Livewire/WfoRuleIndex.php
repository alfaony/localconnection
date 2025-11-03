<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\WfoRule;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class WfoRuleIndex extends Component
{
    use WithPagination;

    public $userId;
    public $entryTimeCheckin;
    public $timesCheckinInDay;
    public $pointCheckinInDay;
    public $editingId = null;

    protected $rules = [
        'userId' => 'required|exists:users,id',
        'entryTimeCheckin' => 'required|date_format:H:i',
        'timesCheckinInDay' => 'required|integer|min:1',
        'pointCheckinInDay' => 'required|integer|max:0',
    ];

    protected $validationAttributes = [
        'userId' => 'user',
        'entryTimeCheckin' => 'jam masuk check-in',
        'timesCheckinInDay' => 'jumlah check-in per hari',
        'pointCheckinInDay' => 'poin check-in per hari',
    ];

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $WfoRule = WfoRule::find($this->editingId);
            $WfoRule->update([
                'user_id' => $this->userId,
                'entry_time_checkin' => $this->entryTimeCheckin,
                'times_checkin_in_day' => $this->timesCheckinInDay,
                'point_checkin_in_day' => $this->pointCheckinInDay,
            ]);
            session()->flash('message', 'WFO Rules berhasil diupdate.');
        } else {
            WfoRule::create([
                'user_id' => $this->userId,
                'entry_time_checkin' => $this->entryTimeCheckin,
                'times_checkin_in_day' => $this->timesCheckinInDay,
                'point_checkin_in_day' => $this->pointCheckinInDay,
            ]);
            session()->flash('message', 'WFO Rules berhasil ditambahkan.');
        }

        $this->reset(['userId', 'entryTimeCheckin', 'timesCheckinInDay', 'pointCheckinInDay', 'editingId']);
    }

    public function edit($id)
    {
        $WfoRule = WfoRule::find($id);
        $this->editingId = $id;
        $this->userId = $WfoRule->user_id;
        $this->entryTimeCheckin = $WfoRule->entry_time_checkin->format('H:i');
        $this->timesCheckinInDay = $WfoRule->times_checkin_in_day;
        $this->pointCheckinInDay = $WfoRule->point_checkin_in_day;
    }

    public function delete($id)
    {
        WfoRule::find($id)->delete();
        session()->flash('message', 'WFO Rules berhasil dihapus.');
    }

    public function cancel()
    {
        $this->reset(['userId', 'entryTimeCheckin', 'timesCheckinInDay', 'pointCheckinInDay', 'editingId']);
    }

    public function render()
    {
        $users = User::byCompany(Auth::user()->company_id)
            ->whereHas('role.permissions', function ($q) {
                $q->where('method', 'generate')->where('table', 'barcodes');
            })
            ->whereDoesntHave('wfoRules', function ($q) {
                // Exclude users yang sudah punya WfoRule
            })
            ->get();

        // Jika sedang edit, sertakan user yang sedang diedit meskipun punya WfoRule
        if ($this->editingId) {
            $currentWfo = WfoRule::find($this->editingId);
            if ($currentWfo && $currentWfo->user_id) {
                $users = User::byCompany(Auth::user()->company_id)
                    ->whereHas('role.permissions', function ($q) {
                        $q->where('method', 'generate')->where('table', 'barcodes');
                    })
                    ->where(function ($q) use ($currentWfo) {
                        $q->whereDoesntHave('wfoRules')
                        ->orWhere('id', $currentWfo->user_id);
                    })
                    ->get();
            }
        }

        return view('livewire.wfo-rule-index', [
            'wfoRules' => WfoRule::with('user')->paginate(10),
            'users' => $users,
        ])->extends('adminlte::page');
    }
}