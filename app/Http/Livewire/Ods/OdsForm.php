<?php

namespace App\Http\Livewire\Ods;

use Livewire\Component;
use App\Models\Pop;
use App\Models\User;
use App\Models\OpticalDistribution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class OdsForm extends Component
{
    use WithFileUploads;

    public $odsId;
    public $name;
    public $capacity_mb;
    public $address;
    public $selectedPop = [];
    public $latitude;
    public $longitude;
    public $location_photo;
    public $user_assign_id;
    public $temp_photo;
    public $isEdit = false;
    public $showMapModal = false;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'capacity_mb' => 'required|integer|min:1',
            'address' => 'nullable|string|max:500',
            'selectedPop' => 'required|array|min:1',
            'selectedPop.*' => 'exists:pops,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'user_assign_id' => 'required|exists:users,id',
            'location_photo' => 'nullable|image|max:10240',
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => 'Nama harus diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',

            'capacity_mb.required' => 'Kapasitas harus diisi.',
            'capacity_mb.integer' => 'Kapasitas harus berupa angka.',
            'capacity_mb.min' => 'Kapasitas minimal 1 MB.',

            'address.string' => 'Alamat harus berupa teks.',
            'address.max' => 'Alamat maksimal 500 karakter.',

            'selectedPop.required' => 'Pilih minimal satu POP.',
            'selectedPop.*.exists' => 'POP yang dipilih tidak valid.',

            'latitude.required' => 'Latitude harus diisi.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            
            'longitude.required' => 'Longitude harus diisi.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            
            'user_assign_id.required' => 'Pilih teknisi yang bertanggung jawab.',
            'user_assign_id.exists' => 'Teknisi yang dipilih tidak valid.',
            
            'location_photo.image' => 'File harus berupa gambar.',
            'location_photo.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }

    public function mount($id = null)
    {
        if ($id) {
            $this->isEdit = true;
            $this->odsId = $id;
            $ods = OpticalDistribution::findOrFail($id);
            
            $this->name = $ods->name;
            $this->capacity_mb = $ods->capacity_mb;
            $this->address = $ods->address;
            $this->selectedPop = $ods->pops->pluck('id')->toArray();
            $this->latitude = $ods->latitude;
            $this->longitude = $ods->longitude;
            $this->user_assign_id = $ods->user_assign_id;
            $this->temp_photo = $ods->location_photo;
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'company_id' => Auth::user()->company_id,
            'user_assign_id' => $this->user_assign_id,
            'name' => $this->name,
            'capacity_mb' => $this->capacity_mb,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];

        if ($this->location_photo) {
            $data['location_photo'] = $this->location_photo->store('ods-photos', 'public');
        }

        if ($this->isEdit) {
            $ods = OpticalDistribution::findOrFail($this->odsId);

            // Hapus foto lama jika ada foto baru dan foto sebelumnya ada
            if ($this->location_photo && $ods->location_photo) {
                Storage::delete($ods->location_photo);
            }

            // Simpan foto baru (jika ada)
            if ($this->location_photo) {
                $data['location_photo'] = $this->location_photo->store('ods-photos', 'public');
            } else {
                $data['location_photo'] = $ods->location_photo; // Tetap pakai foto lama
            }

            $ods->update($data);
            $ods->pops()->sync($this->selectedPop);

            session()->flash('success', 'ODS berhasil diperbarui!');
        } else {
            $ods = OpticalDistribution::create($data);
            $ods->pops()->attach($this->selectedPop);
            
            session()->flash('success', 'ODS berhasil ditambahkan!');
        }

        return redirect()->route('optical-distribution.index');
    }

    public function updatedLocationPhoto()
    {
        $this->validate([
            'location_photo' => 'image|max:2048',
        ]);
    }

    public function selectLocation()
    {
        $this->validate([
            'address' => 'required',
        ]);
        
        $this->showMapModal = true;
    }

    public function updateLocation($lat, $lng)
    {
        $this->latitude = $lat;
        $this->longitude = $lng;
        $this->showMapModal = false;
    }

    public function render()
    {
        return view('livewire.ods.ods-form', [
            'users' => User::where('company_id', Auth::user()->company_id)->get(),
            'pops' => Pop::byCompany(Auth::user()->company_id)->get()
        ])->extends('adminlte::page');
    }
}
