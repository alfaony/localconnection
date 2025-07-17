<?php

namespace App\Http\Livewire\InternetPackage;

use App\Models\InternetPackage;
use Livewire\Component;

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

    protected $rules = [
        'name' => 'required|string|max:255',
        'bandwidth' => 'required|integer|min:1',
        'type' => 'required|in:dedicated,broadband',
        'price' => 'required|numeric|min:0',
        'price_nett' => 'required|numeric|min:0',
        'description' => 'nullable|string',
        'is_active' => 'boolean'
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
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'bandwidth' => $this->bandwidth,
            'type' => $this->type,
            'price' => $this->price,
            'price_nett' => $this->price_nett,
            'description' => $this->description,
            'is_active' => $this->is_active
        ];

        if ($this->packageId) {
            InternetPackage::find($this->packageId)->update($data);
            session()->flash('message', 'Paket berhasil diperbarui.');
        } else {
            InternetPackage::create($data);
            session()->flash('message', 'Paket berhasil ditambahkan.');
        }

        return redirect()->route('internet-packages.index');
    }

    public function render()
    {
        return view('livewire.internet-package.internet-package-form')->extends('adminlte::page');
    }
}