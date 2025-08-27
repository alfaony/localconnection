<?php

namespace App\Http\Livewire\InternetPackage;

use App\Models\InternetPackage;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class InternetPackageForm extends Component
{
    public $packageId;
    public $name;
    public $bandwidth;
    public $type = 'dedicated';
    public $price;
    public $price_nett;
    public $description;
    public $is_active = true;
    public $access_type = 'pppoe';
    public $rate_down_mbps;
    public $rate_up_mbps;
    public $fup_rate_down_mbps = 0;
    public $fup_rate_up_mbps = 0;
    public $quota_bytes = 0;
    // public $version;
    // public $meta;

    protected $rules = [
        'name' => 'required|string|max:255',
        'bandwidth' => 'required|integer|min:1',
        'type' => 'required|in:dedicated,broadband',
        'price' => 'required|numeric|min:0',
        'price_nett' => 'required|numeric|min:0',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
        'access_type' => 'required|in:pppoe,hotspot,ipoe',
        'rate_down_mbps' => 'required|integer|min:1',
        'rate_up_mbps' => 'required|integer|min:1',
        'fup_rate_down_mbps' => 'nullable|integer|min:0',
        'fup_rate_up_mbps' => 'nullable|integer|min:0',
        'quota_bytes' => 'nullable|integer|min:0',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $package = InternetPackage::findOrFail($id);
            $this->packageId = $package->id;
            $this->name = $package->name;
            $this->bandwidth = $package->bandwidth;
            $this->type = $package->type;
            $this->price = $package->price;
            $this->price_nett = $package->price_nett;
            $this->description = $package->description;
            $this->is_active = $package->is_active;
            $this->access_type = $package->access_type;
            $this->rate_down_mbps = $package->rate_down_mbps ?? $this->bandwidth;
            $this->rate_up_mbps = $package->rate_up_mbps ?? $this->bandwidth;
            $this->fup_rate_down_mbps = $package->fup_rate_down_mbps;
            $this->fup_rate_up_mbps = $package->fup_rate_up_mbps;
            $this->quota_bytes = $package->quota_bytes;
            // $this->version = $package->version;
            // $this->meta = $package->meta;
            
        }
    }

    /**
     * Save the internet package.
     *
     * Validate the input and store the data into the database.
     * If the package already exists, update the existing data.
     * Otherwise, create a new package.
     *
     * Redirect to the package index page after saving.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save()
    {
        $this->validate();

        $data = [
            'company_id' => Auth::user()->company_id,
            'name' => $this->name,
            'bandwidth' => $this->bandwidth,
            'type' => $this->type,
            'price' => $this->price,
            'price_nett' => $this->price_nett,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'access_type' => $this->access_type,
            'rate_down_mbps' => $this->rate_down_mbps,
            'rate_up_mbps' => $this->rate_up_mbps,
            'fup_rate_down_mbps' => $this->fup_rate_down_mbps,
            'fup_rate_up_mbps' => 0,
            'quota_bytes' => 0,
            // 'version' => $this->version,
            // 'meta' => $this->meta
        ];

        if ($this->packageId) {
            InternetPackage::find($this->packageId)->update($data);
            session()->flash('message', 'Paket berhasil diperbarui.');
        } else {
            InternetPackage::create($data);
        }

        return redirect()->route('internet-package.index')->with('success', 'Paket berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.internet-package.internet-package-form')->extends('adminlte::page');
    }
}