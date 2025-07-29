<?php

namespace App\Http\Livewire\Promo;

use Livewire\Component;
use App\Models\Promo;
use Illuminate\Support\Str;

class PromoForm extends Component
{
    public $promoId;
    public $name, $type = 'free_months', $value, $start_date, $end_date, $quota, $is_active = true, $register_date;

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
            $this->promoId = $promo->id;
            $this->name = $promo->name;
            $this->type = $promo->type;
            $this->value = $promo->value;
            $this->start_date = $promo->start_date;
            $this->end_date = $promo->end_date;
            $this->quota = $promo->quota;
            $this->is_active = $promo->is_active;
        }
    }

    public function save()
    {
        $this->validate();

        Promo::updateOrCreate(
            ['id' => $this->promoId ?? Str::uuid()],
            [
                'name' => $this->name,
                'type' => $this->type,
                'value' => $this->value,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'quota' => $this->quota,
                'is_active' => $this->is_active,
            ]
        );

        session()->flash('message', 'Promo berhasil disimpan.');
        return redirect()->route('promo.index');
    }

    public function render()
    {
        $type = config('custom.promo_type');
        $package = InternetPackage::where('is_active', true)->byCompany(Auth::user()->company_id)->get();
        return view('livewire.promo.promo-form',compact('type','package'))->extends('adminlte::page');
    }
}
