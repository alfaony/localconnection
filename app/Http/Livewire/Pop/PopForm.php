<?php

namespace App\Http\Livewire\Pop;

use Livewire\Component;
use App\Models\Pop;
use App\Models\DataCenter;
use App\Models\PopEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PopForm extends Component
{
    public $pop;
    public $name;
    public $capacity_mb;
    public $is_multi_data_center = false;
    public $monthly_cost;
    public $lease_expiration_date;
    public $address;
    public $coordinates;
    public $entries = [];
    public $entryCount = 0;
    public $selectedDataCenters;
    public $latitude;
    public $longitude;

    protected function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'capacity_mb' => 'required|integer|min:1',
            'monthly_cost' => 'required',
            'lease_expiration_date' => 'required|date|after_or_equal:today',
            'address' => 'nullable|string|max:500',
            'entries.*.name' => 'nullable|string|max:255',
            'entries.*.capacity_mb' => 'nullable|integer|min:1',
        ];

        if ($this->pop) {
            $rules['name'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('pops')->ignore($this->pop->id)->where('company_id', Auth::user()->company_id)
            ];
        } else {
            $rules['name'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('pops')->where('company_id', Auth::user()->company_id)
            ];
        }

        return $rules;
    }

    protected $messages = [
        'entries.*.name.required_with' => 'Nama jalur wajib diisi.',
        'entries.*.capacity_mb.required_with' => 'Kapasitas jalur wajib diisi.',
        'lease_expiration_date.after_or_equal' => 'Tanggal perpanjang harus setelah atau sama dengan hari ini.',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $this->pop = Pop::byCompany(Auth::user()->company_id)->with('entries')->findOrFail($id);
            $this->name = $this->pop->name;
            $this->capacity_mb = $this->pop->capacity_mb;
            $this->is_multi_data_center = $this->pop->is_multi_data_center;
            $this->monthly_cost = number_format($this->pop->monthly_cost, 0, ',', '.');
            $this->lease_expiration_date = Carbon::parse($this->pop->lease_expiration_date)->format('Y-m-d');
            $this->address = $this->pop->address;
            $this->coordinates = $this->pop->coordinates;

            if (count($this->pop->entries) > 0) {
                foreach ($this->pop->entries as $entry) {
                    $this->entries[] = [
                        'id' => $entry->id,
                        'name' => $entry->name,
                        'capacity_mb' => $entry->capacity_mb
                    ];
                }
                $this->entryCount = count($this->entries);
            }

            $this->selectedDataCenters = $this->pop->dataCenters->pluck('id')->toArray();
        } else {
            $this->lease_expiration_date = now()->addMonth()->format('Y-m-d');
            $this->entryCount = 0;
        }
    }

    public function addEntry()
    {
        if ($this->entryCount < 5) {
            $this->entries[] = ['name' => '', 'capacity_mb' => ''];
            $this->entryCount++;
        }
    }

    public function removeEntry($index)
    {
        unset($this->entries[$index]);
        $this->entries = array_values($this->entries);
        $this->entryCount--;
    }

    public function getLocation()
    {
        $this->dispatchBrowserEvent('request-geolocation');
    }

    public function save()
    {
        $this->validate();

        // Format biaya
        
        $monthly_cost = (int) str_replace('.', '', $this->monthly_cost);

        $data = [
            'name' => $this->name,
            'capacity_mb' => $this->capacity_mb,
            'monthly_cost' => $monthly_cost,
            'lease_expiration_date' => $this->lease_expiration_date,
            'address' => $this->address,
            'company_id' => Auth::user()->company_id,
            'user_created_id' => Auth::id(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ];


        if ($this->pop) 
        {
            $this->pop->update($data);
            // Update entries
            $this->updateEntries();
            $this->pop->entries()->delete();
            $message = 'POP berhasil diperbarui!';
        } else 
        {
            $pop = Pop::create($data);
            $this->pop = $pop;
            $message = 'POP berhasil dibuat!';
        }
        
        foreach ($this->entries as $entry) 
        {
            $this->pop->entries()->create($entry);
        }
        $this->pop->dataCenters()->sync($this->selectedDataCenters);

        session()->flash('success', $message);
        return redirect()->route('pops.index');
    }

    protected function createEntries()
    {
        if(count($this->entries) == 0) {
            return;
        }
        foreach ($this->entries as $entry) {
            PopEntry::create([
                'pop_id' => $this->pop->id,
                'name' => $entry['name'],
                'capacity_mb' => $entry['capacity_mb']
            ]);
        }
    }

    protected function updateEntries()
    {
        if ($this->is_multi_data_center) {
            // Delete removed entries
            $existingIds = collect($this->entries)->pluck('id')->filter();
            $this->pop->entries()->whereNotIn('id', $existingIds)->delete();

            // Update or create entries
            foreach ($this->entries as $entry) {
                if (isset($entry['id'])) {
                    PopEntry::where('id', $entry['id'])->update([
                        'name' => $entry['name'],
                        'capacity_mb' => $entry['capacity_mb']
                    ]);
                } else {
                    PopEntry::create([
                        'pop_id' => $this->pop->id,
                        'name' => $entry['name'],
                        'capacity_mb' => $entry['capacity_mb']
                    ]);
                }
            }
        } else 
        {
            // Delete all entries if not multi data center
            $this->pop->entries()->delete();
        }
    }

    public function render()
    {
        return view('livewire.pop.form',
        [    
            'dataCenters' => DataCenter::byCompany(Auth::user()->company_id)->get(),
        ]
        )->extends('adminlte::page');
    }
}