<?php

namespace App\Http\Livewire\DataCenter;

use Livewire\Component;
use App\Models\DataCenter;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class Form extends Component
{
    public $dataCenterId;
    public $name;
    public $capacity_mb;
    public $cost_per_month;
    public $tanggal_tagihan;
    public $entries = [];
    public $selectedUsers = [];

    protected $rules = [
        'name' => 'required|min:3',
        'capacity_mb' => 'required|integer|min:1',
        'cost_per_month' => 'required|numeric|min:0',
        'tanggal_tagihan' => 'required|date',
        'entries.*.name' => 'required',
        'entries.*.capacity_mb' => 'required|integer|min:1',
    ];

    public function mount($id = null)
    {
        $this->dataCenterId = $id;

        if ($id) {
            $dataCenter = DataCenter::with('entries', 'users')->findOrFail($id);
            $this->name = $dataCenter->name;
            $this->capacity_mb = $dataCenter->capacity_mb;
            $this->cost_per_month = $dataCenter->cost_per_month;
            $this->tanggal_tagihan = $dataCenter->tanggal_tagihan;
            $this->entries = $dataCenter->entries->map(function ($e) {
                return ['name' => $e->name, 'capacity_mb' => $e->capacity_mb];
            })->toArray();
            $this->selectedUsers = $dataCenter->users->pluck('id')->toArray();
        } else {
            $this->addEntry(); // for create mode
        }
    }

    public function addEntry()
    {
        $this->entries[] = ['name' => '', 'capacity_mb' => ''];
    }

    public function removeEntry($index)
    {
        unset($this->entries[$index]);
        $this->entries = array_values($this->entries);
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'company_id' => Auth::user()->company_id,
            'capacity_mb' => $this->capacity_mb,
            'cost_per_month' => $this->cost_per_month,
            'tanggal_tagihan' => $this->tanggal_tagihan,
        ];

        if ($this->dataCenterId) {
            $dataCenter = DataCenter::findOrFail($this->dataCenterId);
            $dataCenter->update($data);
            $dataCenter->entries()->delete();
        } else {
            $dataCenter = DataCenter::create($data);
        }

        foreach ($this->entries as $entry) {
            $dataCenter->entries()->create($entry);
        }

        $dataCenter->users()->sync($this->selectedUsers);

        session()->flash('success', $this->dataCenterId ? 'Data Center updated.' : 'Data Center created.');
        return redirect()->route('data-centers.index');
    }

    public function render()
    {
        return view('livewire.data-center.form', [
            'users' => User::where('company_id', Auth::user()->company_id)->get()
        ])->extends('adminlte::page');
    }
}