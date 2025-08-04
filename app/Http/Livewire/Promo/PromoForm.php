<?php

namespace App\Http\Livewire\Promo;

use Livewire\Component;
use App\Models\Promo;
use Illuminate\Support\Str;
use App\Models\InternetPackage;
use Illuminate\Support\Facades\Auth;

class PromoForm extends Component
{
    public $promoId;
    public $name, $type = 'free_months', $value, $start_date, $end_date, $quota, $is_active = true, $register_date,$packageInternets;

    protected $rules = [
        'name' => 'required|string|min:3',
        'type' => 'required|in:free_months,discount_percent,discount_nominal',
        'value' => 'required|integer|min:1',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'quota' => 'nullable|integer|min:1',
        'is_active' => 'boolean',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $promo = Promo::findOrFail($id);
            
            if(!$promo->isAction())
            {
                return redirect()->route('promo.index')->with('error', 'Promo tidak dapat diubah.');
            }

            $this->promoId = $promo->id;
            $this->name = $promo->name;
            $this->type = $promo->type;
            $this->value = $promo->value;
            $this->start_date = $promo->start_date ? $promo->start_date->format('Y-m-d') : null;
            $this->end_date = $promo->end_date ? $promo->end_date->format('Y-m-d') : null;
            $this->quota = $promo->quota;
            $this->register_date = $promo->register_date ? $promo->register_date->format('Y-m-d') : null;
            $this->is_active = $promo->is_active;
            if(!$promo->packageInternets->isEmpty())
            {
                $this->packageInternets = $promo->packageInternets->pluck('id')->toArray();
            }
        }
    }

    public function save()
    {
        $this->validate();
        $promo = Promo::updateOrCreate(
            ['id' => $this->promoId],
            [
                'company_id' => Auth::user()->company_id,
                'name' => $this->name,
                'type' => $this->type,
                'value' => $this->value,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'register_date' => $this->register_date,
                'quota' => $this->quota,
                'is_active' => $this->is_active,
            ]
        );

         // Simpan relasi dengan package internet (jika ada)
        if (!empty($this->packageInternets)) {
            $promo->packageInternets()->sync($this->packageInternets);
        }
        
        session()->flash('success', 'Promo berhasil disimpan.');
        return redirect()->route('promo.index');
    }

    public function render()
    {
        $type = config('custom.promo_type');
        $allPackages = InternetPackage::where('is_active', true)
        ->byCompany(Auth::user()->company_id)
        ->where(function ($query) {
            $query->whereDoesntHave('promos', function ($q) {
                $q->where('is_active', true)
                ->when($this->promoId, fn($q) => $q->where('promos.id', '!=', $this->promoId));
            });

            // Jika sedang edit, pastikan relasi ke promo ini tetap muncul
            if ($this->promoId) {
                $query->orWhereHas('promos', function ($q) {
                    $q->where('promos.id', $this->promoId);
                });
            }
        })
        ->get();
        return view('livewire.promo.promo-form',compact('type','allPackages'))->extends('adminlte::page');
    }
}
